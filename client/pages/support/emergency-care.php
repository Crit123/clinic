<?php
/**
 * emergency-care.php
 * Dental Emergency Center for DentalCare Pro.
 * Submits real urgent requests to the messaging backend
 * (client/backend/messages-auth/message-submit.php) and tracks status
 * via message-status.php using the returned case code.
 */

// Include relies on standard internal system path resolution, undisturbed by nested sub-directories
require_once __DIR__ . '/../../components/design-config.php';
require_once __DIR__ . '/../../../api/helper/_api-helpers.php';

$activePage = 'emergency';
$pageTitle  = 'Emergency Care';

ob_start();
?>

<!-- Patient Action Center (Hidden by default, shown via JS if active case exists) -->
<div id="patientActionCenter" class="hidden fade-in mb-8 bg-surface-container-lowest border-2 border-rose-200 rounded-2xl p-6 shadow-md relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-400 to-rose-600"></div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="material-symbols-outlined text-rose-600" aria-hidden="true">emergency_home</span>
                <h2 class="font-headline-md text-xl text-slate-800 font-bold">Patient Action Center</h2>
                <span id="trackerPriorityBadge" class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border"></span>
            </div>
            <p class="text-sm text-slate-500">Active Emergency Request Tracking</p>
        </div>
        <div class="text-right">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Case ID</div>
            <div id="trackerCaseId" class="font-mono font-bold text-slate-700 text-lg">---</div>
            <div id="trackerTime" class="text-xs text-slate-500 mt-0.5">---</div>
        </div>
    </div>

    <!-- Progress Tracker -->
    <div class="relative pt-4 pb-2">
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-100 rounded-full"></div>
        <div id="trackerProgressBar" class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-rose-500 rounded-full transition-all duration-500" style="width: 0%;"></div>
        
        <div class="relative flex justify-between">
            <!-- Step 1 -->
            <div class="flex flex-col items-center gap-2 relative z-10 w-24">
                <div id="step1Icon" class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center border-4 border-white shadow-sm transition-colors duration-300">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">send</span>
                </div>
                <span class="text-xs font-bold text-slate-500 text-center">Submitted</span>
            </div>
            <!-- Step 2 -->
            <div class="flex flex-col items-center gap-2 relative z-10 w-24">
                <div id="step2Icon" class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center border-4 border-white shadow-sm transition-colors duration-300">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">visibility</span>
                </div>
                <span class="text-xs font-bold text-slate-500 text-center">Under Review</span>
            </div>
            <!-- Step 3 -->
            <div class="flex flex-col items-center gap-2 relative z-10 w-24">
                <div id="step3Icon" class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center border-4 border-white shadow-sm transition-colors duration-300">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">support_agent</span>
                </div>
                <span class="text-xs font-bold text-slate-500 text-center">Contacted</span>
            </div>
            <!-- Step 4 -->
            <div class="flex flex-col items-center gap-2 relative z-10 w-24">
                <div id="step4Icon" class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center border-4 border-white shadow-sm transition-colors duration-300">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">check_circle</span>
                </div>
                <span class="text-xs font-bold text-slate-500 text-center">Resolved</span>
            </div>
        </div>
    </div>
    
    <div class="mt-6 flex justify-end">
        <button onclick="clearEmergencyCase()" class="text-xs text-slate-400 hover:text-slate-600 font-medium underline">Stop tracking on this device</button>
    </div>
</div>

<div class="fade-in mb-8">
    <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">emergency</span>
        </div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-slate-800">Emergency Care</h2>
    </div>
    <p class="font-body-md text-slate-600 max-w-2xl">
        Stay calm, we're here to help. Our on-call team prioritizes urgent cases. Please call us directly for severe trauma, or submit an urgent request below.
    </p>
</div>

<div class="fade-in delay-100 grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
    
    <!-- Left Column: Immediate Actions & Triage Chat -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Call Clinic Action (Massive Button) -->
        <div class="bg-rose-600 rounded-2xl shadow-md overflow-hidden hover:bg-rose-700 transition-colors duration-200">
            <a href="tel:<?php echo htmlspecialchars(CLINIC_PHONE ?: '911'); ?>" class="block p-6 text-white focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-rose-300">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0 backdrop-blur-sm">
                        <span class="material-symbols-outlined text-3xl" aria-hidden="true">call</span>
                    </div>
                    <div>
                        <span class="block text-rose-100 text-sm font-bold uppercase tracking-wider mb-0.5">Call Clinic Now</span>
                        <span class="block font-headline-md text-2xl font-bold"><?php echo htmlspecialchars(CLINIC_PHONE ?: '(555) 123-4567'); ?></span>
                    </div>
                </div>
                <p class="text-rose-100 text-sm leading-relaxed">
                    For severe pain, active bleeding, or trauma. If this is a life-threatening medical emergency, call 911 immediately.
                </p>
            </a>
        </div>

        <!-- Emergency Triage Chat (Coming Soon) -->
        <div class="bg-surface-container-lowest border border-amber-100 rounded-2xl p-6 shadow-sm relative overflow-hidden group flex flex-col">
            <div class="absolute top-4 right-4">
                <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 px-2.5 py-1 rounded border border-amber-200">Coming Soon</span>
            </div>
            <div class="flex items-center gap-3 mb-4 opacity-70">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined" aria-hidden="true">support_agent</span>
                </div>
                <h3 class="font-headline-md text-lg text-slate-800 font-bold">Live Triage Chat</h3>
            </div>
            <p class="text-sm text-slate-500 opacity-90 mb-6 leading-relaxed">
                Our AI-assisted emergency triage will soon be available to assess your symptoms instantly and route you to the appropriate on-call specialist.
            </p>
            <button disabled aria-disabled="true" class="w-full py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 text-sm font-bold cursor-not-allowed flex items-center justify-center gap-2 transition-colors">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">chat</span>
                Start Assessment (Unavailable)
            </button>
        </div>
    </div>

    <!-- Right Column: Urgent Request Form -->
    <div class="lg:col-span-3 bg-surface-container-lowest border border-slate-200 rounded-2xl shadow-sm border-t-4 border-t-rose-500 p-6 md:p-8">
        <div class="mb-6">
            <h3 class="font-headline-md text-xl text-slate-800 font-bold mb-2">Request Urgent Appointment</h3>
            <p class="text-sm text-slate-600 mb-6">
                Select your primary symptom to receive immediate care tips and alert our on-call team.
            </p>
            
            <!-- Visual Emergency Cards -->
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">Select Primary Symptom <span class="text-rose-500">*</span></label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6" id="symptomGrid">
                <!-- Cards generated via JS -->
            </div>
            <p id="symptomError" class="hidden text-rose-500 text-xs font-bold mt-1 mb-4">Please select a symptom to proceed.</p>

            <!-- Dynamic Severity Assessment & Self-Care Tips Panel -->
            <div id="assessmentPanel" class="hidden mb-6 bg-slate-50 border border-slate-200 rounded-xl overflow-hidden transition-all duration-300">
                <div class="bg-white border-b border-slate-100 p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Priority Level:</span>
                        <span id="priorityBadge" class="text-xs font-bold px-2.5 py-1 rounded-full border"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-slate-400 text-lg" aria-hidden="true">info</span>
                        <span id="recommendedAction" class="font-bold text-slate-700"></span>
                    </div>
                </div>
                <div class="p-4 pl-5">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-rose-400 text-base" aria-hidden="true">medical_information</span>
                        Immediate Self-Care Tips
                    </h4>
                    <ul id="tipsList" class="list-disc pl-5 text-sm text-slate-600 space-y-1 marker:text-rose-400">
                        <!-- Tips injected via JS -->
                    </ul>
                </div>
            </div>
        </div>
        
        <form id="emergencyForm" onsubmit="handleEmergencySubmit(event)" class="space-y-5">
            <!-- Hidden inputs mapped from cards -->
            <input type="hidden" id="selectedSymptom" name="symptom" value="">
            <input type="hidden" id="selectedSeverity" name="severity" value="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">

            <!-- Description -->
            <div class="space-y-1.5">
                <label for="description" class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Brief Description <span class="text-rose-500">*</span></label>
                <textarea id="description" name="description" required aria-required="true" rows="3" placeholder="Tell us briefly what happened and how you are feeling." class="w-full border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:border-rose-500 focus:ring-rose-500 shadow-sm resize-none py-3"></textarea>
            </div>

            <!-- Contact Preference -->
            <div class="space-y-3 pt-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">How should we reach you? <span class="text-rose-500">*</span></label>
                <div class="flex flex-col sm:flex-row gap-4">
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors flex-1">
                        <input type="radio" name="contact_pref" value="call" required class="text-rose-600 focus:ring-rose-500 border-slate-300 w-4 h-4">
                        <span class="text-sm font-medium text-slate-700">Call me ASAP</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors flex-1">
                        <input type="radio" name="contact_pref" value="text" class="text-rose-600 focus:ring-rose-500 border-slate-300 w-4 h-4">
                        <span class="text-sm font-medium text-slate-700">Text me</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3.5 px-6 rounded-xl font-bold text-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 focus-visible:ring-offset-2 flex items-center justify-center gap-2 shadow-md">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">send_and_archive</span>
                    Submit Urgent Request
                </button>
            </div>
        </form>
    </div>

</div>

<script>
/**
 * Emergency Dictionary: Maps symptoms to priorities, actions, and self-care tips.
 */
const emergencyData = {
    toothache: {
        id: 'toothache', icon: 'sentiment_very_dissatisfied', label: 'Severe Toothache',
        priority: 'High', priorityClass: 'bg-rose-100 text-rose-700 border-rose-200',
        action: 'Immediate Clinic Attention',
        tips: [
            'Rinse mouth thoroughly with warm water',
            'Use dental floss to remove trapped food',
            'Take OTC pain relievers if needed',
            'NEVER put aspirin directly on the gums or tooth'
        ]
    },
    swelling: {
        id: 'swelling', icon: 'health_and_safety', label: 'Swelling',
        priority: 'High', priorityClass: 'bg-rose-100 text-rose-700 border-rose-200',
        action: 'Seek Urgent Care',
        tips: [
            'Apply a cold compress to the outside of your face',
            'Stay upright and hydrated',
            'If swelling affects breathing or swallowing, go to ER immediately'
        ]
    },
    broken: {
        id: 'broken', icon: 'dentistry', label: 'Broken Tooth',
        priority: 'Moderate', priorityClass: 'bg-amber-100 text-amber-700 border-amber-200',
        action: 'Schedule Visit Soon',
        tips: [
            'Save any broken fragments if possible',
            'Rinse your mouth gently with warm water',
            'Apply a cold compress if there is swelling',
            'Avoid chewing on that side of your mouth'
        ]
    },
    bleeding: {
        id: 'bleeding', icon: 'water_drop', label: 'Bleeding',
        priority: 'High', priorityClass: 'bg-rose-100 text-rose-700 border-rose-200',
        action: 'Urgent Care Required',
        tips: [
            'Apply firm pressure with clean gauze for 10-15 minutes',
            'Avoid vigorous rinsing or spitting',
            'Keep your head elevated',
            'If bleeding doesn\'t stop after 15 minutes, go to ER'
        ]
    },
    trauma: {
        id: 'trauma', icon: 'bolt', label: 'Trauma',
        priority: 'High', priorityClass: 'bg-rose-100 text-rose-700 border-rose-200',
        action: 'Call Clinic or ER',
        tips: [
            'Assess for head injury or concussion first (Go to ER if present)',
            'If a tooth is knocked out, hold it by the crown (not roots)',
            'Keep knocked out tooth moist (in milk or saliva)',
            'Apply cold compress to face'
        ]
    },
    filling: {
        id: 'filling', icon: 'extension', label: 'Lost Filling',
        priority: 'Low', priorityClass: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        action: 'Schedule within days',
        tips: [
            'Keep the area clean by brushing gently',
            'Avoid sticky, hard, or very sweet foods',
            'You may use OTC dental cement temporarily if sensitive',
            'Do not ignore, as decay can progress rapidly'
        ]
    }
};

let activeSymptom = null;
const STORAGE_KEY = 'dental_active_emergency_case';

/**
 * Initialize the Visual Cards
 */
function initSymptomCards() {
    const grid = document.getElementById('symptomGrid');
    Object.values(emergencyData).forEach(symptom => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = `card-${symptom.id}`;
        btn.className = `flex flex-col items-center justify-center p-4 gap-2 rounded-xl border border-slate-200 bg-white hover:border-rose-400 hover:shadow-md transition-all text-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500`;
        btn.innerHTML = `
            <span class="material-symbols-outlined text-3xl text-slate-500 mb-1" aria-hidden="true">${symptom.icon}</span>
            <span class="text-xs font-bold text-slate-700">${symptom.label}</span>
        `;
        btn.onclick = () => selectSymptom(symptom.id);
        grid.appendChild(btn);
    });
}

/**
 * Handle Symptom Selection
 */
function selectSymptom(id) {
    activeSymptom = id;
    const data = emergencyData[id];
    
    // Update Hidden Inputs
    document.getElementById('selectedSymptom').value = id;
    document.getElementById('selectedSeverity').value = data.priority;
    document.getElementById('symptomError').classList.add('hidden');

    // Update UI Cards
    Object.keys(emergencyData).forEach(key => {
        const card = document.getElementById(`card-${key}`);
        const icon = card.querySelector('.material-symbols-outlined');
        if (key === id) {
            card.classList.add('border-rose-500', 'ring-2', 'ring-rose-200', 'bg-rose-50');
            card.classList.remove('border-slate-200', 'bg-white');
            icon.classList.add('text-rose-600');
            icon.classList.remove('text-slate-500');
        } else {
            card.classList.remove('border-rose-500', 'ring-2', 'ring-rose-200', 'bg-rose-50');
            card.classList.add('border-slate-200', 'bg-white');
            icon.classList.remove('text-rose-600');
            icon.classList.add('text-slate-500');
        }
    });

    // Update & Show Assessment Panel
    const panel = document.getElementById('assessmentPanel');
    const badge = document.getElementById('priorityBadge');
    
    badge.className = `text-xs font-bold px-2.5 py-1 rounded-full border ${data.priorityClass}`;
    badge.textContent = `Priority: ${data.priority}`;
    
    document.getElementById('recommendedAction').textContent = data.action;
    
    const tipsList = document.getElementById('tipsList');
    tipsList.innerHTML = data.tips.map(tip => `<li>${tip}</li>`).join('');
    
    panel.classList.remove('hidden');
}

/**
 * Intercept form submission — sends a real request to the messaging
 * backend (category='emergency') and stores only the returned case_code
 * client-side, as a convenience pointer for this browser to re-check
 * status later. The case_code itself is not sensitive/secret in the way
 * a password is, but message-status.php still rate-limits lookups by IP.
 */
async function handleEmergencySubmit(e) {
    e.preventDefault();

    if (!activeSymptom) {
        document.getElementById('symptomError').classList.remove('hidden');
        return;
    }

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-lg">sync</span> Sending Alert...';
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-80', 'cursor-not-allowed');

    const symptomInfo = emergencyData[activeSymptom];
    const description = form.querySelector('#description').value.trim();
    const contactPref = form.querySelector('input[name="contact_pref"]:checked');

    const body = new URLSearchParams();
    body.set('category', 'emergency');
    body.set('subject', `${symptomInfo.label} (${symptomInfo.priority} priority)`);
    body.set('body', `${description}\n\nPreferred contact method: ${contactPref ? contactPref.value : 'not specified'}`);
    body.set('csrf_token', form.querySelector('input[name="csrf_token"]').value);

    try {
        const res = await fetch('<?php echo BASE_PATH; ?>/client/backend/messages-auth/message-submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });
        const data = await res.json();

        if (!data.success) {
            if (typeof showGlobalToast === 'function') {
                showGlobalToast('error', data.message || 'Unable to submit your request.');
            } else {
                alert(data.message || 'Unable to submit your request.');
            }
            return;
        }

        // Store only the case code — the actual record and its status
        // live server-side; this is just so this browser can re-check it.
        localStorage.setItem(STORAGE_KEY, data.case_code);

        if (typeof showGlobalToast === 'function') {
            showGlobalToast('success', `Emergency request received. Case ${data.case_code}.`);
        } else {
            alert(`Emergency request received! Case ${data.case_code}.`);
        }

        // Reset Form & UI
        form.reset();
        activeSymptom = null;
        document.getElementById('selectedSymptom').value = '';
        document.getElementById('selectedSeverity').value = '';
        document.getElementById('assessmentPanel').classList.add('hidden');
        Object.keys(emergencyData).forEach(key => {
            const card = document.getElementById(`card-${key}`);
            const icon = card.querySelector('.material-symbols-outlined');
            card.classList.remove('border-rose-500', 'ring-2', 'ring-rose-200', 'bg-rose-50');
            card.classList.add('border-slate-200', 'bg-white');
            icon.classList.remove('text-rose-600');
            icon.classList.add('text-slate-500');
        });

        await loadEmergencyCase();
        window.scrollTo({ top: 0, behavior: 'smooth' });

    } catch (err) {
        console.error('Emergency submit failed:', err);
        if (typeof showGlobalToast === 'function') {
            showGlobalToast('error', 'Network error — please try again.');
        }
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
    }
}

/**
 * Load and render the active case in the Action Center by asking the
 * backend for real status, using the case_code this browser remembers.
 * This is the only source of truth for status now — nothing here
 * fabricates progress client-side.
 */
async function loadEmergencyCase() {
    const caseCode = localStorage.getItem(STORAGE_KEY);
    const actionCenter = document.getElementById('patientActionCenter');

    if (!caseCode) {
        actionCenter.classList.add('hidden');
        return;
    }

    try {
        const res = await fetch(`<?php echo BASE_PATH; ?>/client/backend/messages-auth/message-status.php?case=${encodeURIComponent(caseCode)}`);
        const data = await res.json();

        if (!data.success) {
            // Case not found (e.g. cleared server-side) — stop pointing at it.
            localStorage.removeItem(STORAGE_KEY);
            actionCenter.classList.add('hidden');
            return;
        }

        const msg = data.message;
        actionCenter.classList.remove('hidden');

        document.getElementById('trackerCaseId').textContent = msg.case_code;

        const date = new Date(msg.created_at);
        document.getElementById('trackerTime').textContent =
            date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' - ' + date.toLocaleDateString();

        // Priority badge isn't stored server-side (it's derived from the
        // symptom picked at submit time, not a DB column), so we fall back
        // to a neutral label here rather than guessing.
        const badge = document.getElementById('trackerPriorityBadge');
        badge.className = 'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border bg-slate-100 text-slate-600 border-slate-200';
        badge.textContent = msg.category.toUpperCase();

        // Map the backend's 3 real statuses onto the 4 visual steps:
        // 'new' -> Submitted, 'in_progress' with no reply yet -> Under
        // Review, 'in_progress' with at least one staff reply -> Contacted,
        // 'resolved' -> Resolved.
        let step = 1;
        if (msg.status === 'in_progress') {
            step = (data.replies && data.replies.length > 0) ? 3 : 2;
        } else if (msg.status === 'resolved') {
            step = 4;
        }

        updateTrackerProgress(step);

    } catch (err) {
        console.error('Failed to load emergency case status:', err);
    }
}

/**
 * Update Progress Bar and Icons
 */
function updateTrackerProgress(status) {
    const widths = { 1: '0%', 2: '33%', 3: '66%', 4: '100%' };
    document.getElementById('trackerProgressBar').style.width = widths[status];

    for (let i = 1; i <= 4; i++) {
        const icon = document.getElementById(`step${i}Icon`);
        if (i < status) {
            // Completed
            icon.className = 'w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center border-4 border-white shadow-sm transition-colors duration-300';
            icon.innerHTML = '<span class="material-symbols-outlined text-sm" aria-hidden="true">check</span>';
        } else if (i === status) {
            // Active
            icon.className = 'w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center border-4 border-white shadow-sm transition-colors duration-300 ring-2 ring-rose-200';
            // Keep original innerHTML depending on step
        } else {
            // Pending
            icon.className = 'w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center border-4 border-white shadow-sm transition-colors duration-300';
        }
    }
}

/**
 * Stops this browser from tracking the case locally. Does NOT delete or
 * resolve the actual case server-side -- it only clears the local pointer,
 * same as closing a tab wouldn't cancel a real request.
 */
function clearEmergencyCase() {
    localStorage.removeItem(STORAGE_KEY);
    loadEmergencyCase();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initSymptomCards();
    loadEmergencyCase();

    // Re-check status periodically while a case is being tracked, so a
    // staff reply or status change shows up without a manual reload.
    // 30s is deliberately conservative -- this hits message-status.php's
    // rate limiter budget, not something to poll aggressively.
    setInterval(() => {
        if (localStorage.getItem(STORAGE_KEY)) {
            loadEmergencyCase();
        }
    }, 30000);
});
</script>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../components/layout/main-layout.php';
?>