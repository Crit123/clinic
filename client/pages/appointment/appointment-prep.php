<?php
/**
 * appointment-prep.php
 * Appointment Preparation Center for DentalCare Pro.
 * Frontend-only UI with hardcoded mock data.
 */

// 1. Require design-config manually
require_once __DIR__ . '/../../components/design-config.php';

// 2. Set the variables the layout shell needs
$activePage = 'appointments'; // Keep 'appointments' active in nav since this is a sub-page
$pageTitle  = 'Preparation Center';

// 3. Start intercepting standard output
ob_start();
?>

<!-- HEADER & NAVIGATION -->
<div class="fade-in mb-8">
    <a href="<?php echo BASE_PATH; ?>/client/pages/appointments.php" class="inline-flex items-center text-sm font-label-md text-on-surface-variant hover:text-primary transition-colors mb-4">
        <span class="material-symbols-outlined text-sm mr-1" aria-hidden="true">arrow_back</span>
        Back to Appointments
    </a>
    <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-primary">Preparation Center</h2>
    <p class="font-body-md text-on-surface-variant mt-1">Complete these steps to ensure a smooth, comfortable, and efficient visit.</p>
</div>

<!-- DEMO CONTROLS (Mock Data Switcher) -->
<div class="fade-in delay-100 bg-amber-50 border border-amber-200 p-4 rounded-xl mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div class="flex items-center gap-2 text-amber-900">
        <span class="material-symbols-outlined text-amber-600">science</span>
        <span class="text-sm font-bold">Demo Controls (Procedure Switcher)</span>
    </div>
    <div class="flex flex-wrap gap-2" role="tablist">
        <button onclick="loadMockData('cleaning')" id="btn-cleaning" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-amber-300 bg-white text-amber-900 hover:bg-amber-100 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500">Routine Cleaning</button>
        <button onclick="loadMockData('extraction')" id="btn-extraction" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-amber-300 bg-white text-amber-900 hover:bg-amber-100 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500">Tooth Extraction</button>
        <button onclick="loadMockData('root_canal')" id="btn-root_canal" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-amber-300 bg-white text-amber-900 hover:bg-amber-100 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500">Root Canal</button>
    </div>
</div>

<!-- PATIENT ACTION CENTER (Dashboard Integration & Global Readiness) -->
<div class="fade-in delay-150 bg-gradient-to-br from-primary-container/20 to-surface-container-lowest p-6 rounded-2xl border border-primary-container/30 mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <span class="text-xs font-bold text-primary uppercase tracking-wider block mb-1">Patient Action Center</span>
            <h3 class="font-headline-md text-lg text-on-surface">Pre-Appointment Readiness Status</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Your action status syncs directly with the dental clinic dashboard.</p>
        </div>
        <!-- Progress Gauge -->
        <div class="flex items-center gap-4 bg-white/80 p-3 rounded-xl border border-surface-variant/30 shadow-sm shrink-0">
            <div class="relative w-16 h-16 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="32" cy="32" r="28" stroke="currentColor" class="text-surface-variant" stroke-width="5" fill="transparent" />
                    <circle cx="32" cy="32" r="28" stroke="currentColor" class="text-primary transition-all duration-500" stroke-width="5" fill="transparent" stroke-dasharray="175.9" id="readiness-circle" stroke-linecap="round" />
                </svg>
                <span class="absolute text-sm font-bold text-on-surface" id="readiness-percent">0%</span>
            </div>
            <div>
                <p class="text-xs font-bold text-on-surface">Overall Readiness</p>
                <p class="text-[11px] text-on-surface-variant" id="readiness-status-label">Reviewing tasks</p>
            </div>
        </div>
    </div>

    <!-- Active Indicators Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <!-- Status Indicator 1 -->
        <div class="flex items-center gap-3 p-3 bg-white rounded-xl border border-surface-variant/40">
            <span id="badge-appt-confirmed-icon" class="material-symbols-outlined text-outline">pending</span>
            <div>
                <p class="text-xs font-bold text-on-surface">Appointment Confirmed</p>
                <span id="badge-appt-confirmed" class="text-[11px] text-on-surface-variant">Action Required</span>
            </div>
        </div>
        <!-- Status Indicator 2 -->
        <div class="flex items-center gap-3 p-3 bg-white rounded-xl border border-surface-variant/40">
            <span id="badge-med-history-icon" class="material-symbols-outlined text-outline">pending</span>
            <div>
                <p class="text-xs font-bold text-on-surface">Medical History Updated</p>
                <span id="badge-med-history" class="text-[11px] text-on-surface-variant">Action Required</span>
            </div>
        </div>
        <!-- Status Indicator 3 -->
        <div class="flex items-center gap-3 p-3 bg-white rounded-xl border border-surface-variant/40">
            <span id="badge-prep-checklist-icon" class="material-symbols-outlined text-outline">pending</span>
            <div>
                <p class="text-xs font-bold text-on-surface">Preparation Checklist</p>
                <span id="badge-prep-checklist" class="text-[11px] text-on-surface-variant">0% Complete</span>
            </div>
        </div>
    </div>
</div>

<!-- MAIN LAYOUT COLS -->
<div class="fade-in delay-200 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- LEFT SIDE: APPOINTMENT CONTEXT & CHECKLIST (2/3 cols) -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- Context Card -->
        <div class="bg-surface-container-lowest p-6 sm:p-8 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.06)] border border-surface-container">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-surface-variant pb-6 mb-6">
                <div>
                    <span class="text-xs font-bold text-primary uppercase tracking-wider block mb-1">Upcoming Appointment</span>
                    <h3 id="prep-title" class="font-headline-md text-xl text-on-surface mb-2">Loading...</h3>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-on-surface-variant">
                        <span class="flex items-center">
                            <span class="material-symbols-outlined text-base mr-1.5" aria-hidden="true">calendar_today</span>
                            <span id="prep-date">--</span>
                        </span>
                        <span class="flex items-center">
                            <span class="material-symbols-outlined text-base mr-1.5" aria-hidden="true">dentistry</span>
                            <span id="prep-doctor">--</span>
                        </span>
                    </div>
                </div>
                <div class="bg-primary-container/30 w-12 h-12 rounded-full flex items-center justify-center text-primary shrink-0">
                    <span id="prep-icon" class="material-symbols-outlined text-2xl">event</span>
                </div>
            </div>

            <!-- Field-Based Action Steps (Toggles) -->
            <div class="mb-6 p-4 bg-surface-container-low rounded-xl border border-surface-variant/40 space-y-4">
                <h4 class="text-xs font-bold text-primary uppercase tracking-wider">Step 1 &amp; 2: Core Preparations</h4>
                
                <!-- Toggle 1: Confirm Appointment -->
                <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-on-surface">Confirm Appointment Arrival</p>
                            <p class="text-xs text-on-surface-variant">Let the clinic know you are coming</p>
                        </div>
                    </div>
                    <input type="checkbox" id="chk-confirm-appt" onchange="updateReadiness()" class="w-5 h-5 accent-primary rounded border-outline-variant cursor-pointer">
                </label>

                <!-- Toggle 2: Medical History Updated -->
                <label class="flex items-center justify-between cursor-pointer group border-t border-surface-variant/40 pt-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-lg">clinical_notes</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-on-surface">Medical History Updated</p>
                            <p class="text-xs text-on-surface-variant">Required for patient safety standards</p>
                        </div>
                    </div>
                    <input type="checkbox" id="chk-med-history" onchange="updateReadiness()" class="w-5 h-5 accent-primary rounded border-outline-variant cursor-pointer">
                </label>
            </div>

            <!-- Progress Tracker -->
            <div class="mb-6">
                <div class="flex justify-between items-end mb-2">
                    <h4 class="font-body-lg font-bold text-on-surface">Step 3: Procedure Preparation Checklist</h4>
                    <span id="progress-text" class="text-xs font-bold text-primary bg-primary-container px-2.5 py-1 rounded-md">0 of 0 Completed</span>
                </div>
                <div class="w-full bg-surface-variant rounded-full h-2.5 overflow-hidden" aria-label="Checklist Progress">
                    <div id="progress-bar" class="bg-primary h-full rounded-full transition-all duration-500 ease-out" style="width: 0%;"></div>
                </div>
            </div>

            <!-- Dynamic Checklist Items -->
            <div id="checklist-container" class="space-y-3">
                <!-- Items injected by JS -->
            </div>

            <!-- Completion Message (Hidden by default) -->
            <div id="completion-message" class="hidden mt-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3 transition-all duration-500">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <span class="material-symbols-outlined text-lg">check_circle</span>
                </div>
                <div>
                    <p class="font-bold text-emerald-800 text-sm">All steps set!</p>
                    <p class="text-xs text-emerald-700 mt-0.5">You're fully prepared for your visit. See you soon!</p>
                </div>
            </div>
        </div>

        <!-- Preparation Timeline Card -->
        <div class="bg-surface-container-lowest p-6 sm:p-8 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.06)] border border-surface-container">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-primary text-2xl">timeline</span>
                <h3 class="font-headline-md text-lg text-on-surface">Preparation Timeline</h3>
            </div>
            
            <!-- Vertical Timeline Container -->
            <div id="timeline-container" class="relative pl-6 border-l-2 border-primary/20 space-y-6">
                <!-- Timeline items injected dynamically by JS -->
            </div>
        </div>

    </div>

    <!-- RIGHT SIDE: SYMPTOMS & ACTIONS (1/3 cols) -->
    <div class="space-y-8">

        <!-- Emergency Warning Detection Card -->
        <div class="bg-white p-6 rounded-2xl border border-red-100 shadow-[0_4px_16px_rgba(220,38,38,0.04)]">
            <div class="flex items-center gap-2 text-red-700 mb-3">
                <span class="material-symbols-outlined text-red-600">report_problem</span>
                <h4 class="font-bold text-sm uppercase tracking-wider">Emergency Assessment</h4>
            </div>
            <p class="text-xs text-on-surface-variant mb-4">Are you currently experiencing any of the following severe symptoms?</p>
            
            <div class="space-y-2.5 mb-5">
                <label class="flex items-center gap-2.5 text-xs font-medium text-on-surface cursor-pointer select-none">
                    <input type="checkbox" id="symp-pain" onchange="checkEmergencySymptoms()" class="rounded text-red-600 focus:ring-red-500 accent-red-600 w-4 h-4">
                    Severe Tooth Pain
                </label>
                <label class="flex items-center gap-2.5 text-xs font-medium text-on-surface cursor-pointer select-none">
                    <input type="checkbox" id="symp-swelling" onchange="checkEmergencySymptoms()" class="rounded text-red-600 focus:ring-red-500 accent-red-600 w-4 h-4">
                    Facial Swelling
                </label>
                <label class="flex items-center gap-2.5 text-xs font-medium text-on-surface cursor-pointer select-none">
                    <input type="checkbox" id="symp-bleeding" onchange="checkEmergencySymptoms()" class="rounded text-red-600 focus:ring-red-500 accent-red-600 w-4 h-4">
                    Continuous Bleeding
                </label>
                <label class="flex items-center gap-2.5 text-xs font-medium text-on-surface cursor-pointer select-none">
                    <input type="checkbox" id="symp-trauma" onchange="checkEmergencySymptoms()" class="rounded text-red-600 focus:ring-red-500 accent-red-600 w-4 h-4">
                    Dental Trauma
                </label>
            </div>

            <!-- Warning Banner (Conditional) -->
            <div id="emergency-alert" class="hidden bg-rose-50 border border-rose-200 rounded-xl p-4 transition-all duration-300">
                <p class="text-xs text-rose-800 font-bold mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">warning</span>
                    Your symptoms may require urgent attention.
                </p>
                <a href="<?php echo BASE_PATH; ?>/client/pages/emergency.php" class="inline-flex items-center justify-center w-full px-3 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 transition-colors rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    Go to Emergency Care
                </a>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.04)]">
            <h4 class="font-bold text-sm text-on-surface mb-4 uppercase tracking-wider">Quick Actions</h4>
            <div class="flex flex-col gap-3">
                <button onclick="triggerAction('update-history')" class="flex items-center gap-3 w-full p-3 text-left text-sm font-bold text-primary hover:bg-primary/5 rounded-xl border border-primary/20 transition-all focus:outline-none focus:ring-2 focus:ring-primary">
                    <span class="material-symbols-outlined">edit_note</span>
                    Update Medical History
                </button>
                <button onclick="triggerAction('contact-clinic')" class="flex items-center gap-3 w-full p-3 text-left text-sm font-bold text-on-surface hover:bg-surface-variant/45 rounded-xl border border-surface-variant transition-all focus:outline-none focus:ring-2 focus:ring-primary">
                    <span class="material-symbols-outlined">call</span>
                    Contact Clinic
                </button>
                <button onclick="triggerAction('reschedule')" class="flex items-center gap-3 w-full p-3 text-left text-sm font-bold text-on-surface hover:bg-surface-variant/45 rounded-xl border border-surface-variant transition-all focus:outline-none focus:ring-2 focus:ring-primary">
                    <span class="material-symbols-outlined">calendar_month</span>
                    Reschedule Appointment
                </button>
                <button onclick="triggerAction('view-details')" class="flex items-center gap-3 w-full p-3 text-left text-sm font-bold text-on-surface hover:bg-surface-variant/45 rounded-xl border border-surface-variant transition-all focus:outline-none focus:ring-2 focus:ring-primary">
                    <span class="material-symbols-outlined">info</span>
                    View Appointment Details
                </button>
            </div>
        </div>

        <!-- Bottom Help Link -->
        <p class="text-center text-xs text-on-surface-variant px-4">
            Have questions about your preparation? <a href="#" class="text-primary font-bold hover:underline">Contact the clinic</a>.
        </p>

    </div>

</div>

<!-- Custom Confirmation / Action Modal (Eliminates Browser alert usage) -->
<div id="info-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl">
        <h3 id="modal-title" class="font-headline-md text-lg text-on-surface mb-2">Notice</h3>
        <p id="modal-body" class="text-sm text-on-surface-variant mb-6"></p>
        <button onclick="closeModal()" class="w-full py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary-hover transition-colors">Close</button>
    </div>
</div>

<script>
// 1. Hardcoded Mock Data with checklist items & custom timelines
const prepData = {
    'cleaning': {
        title: 'Routine Bi-Annual Cleaning',
        date: 'Tomorrow at 2:30 PM',
        doctor: 'Dr. Maria Santos',
        icon: 'health_and_safety',
        items: [
            "Brush and floss thoroughly prior to your appointment.",
            "Bring your updated physical insurance card.",
            "Arrive 10 minutes early to sign updated consent forms.",
            "Note down any new tooth sensitivity to discuss with the hygienist."
        ],
        timeline: [
            {
                phase: 'Before Appointment',
                icon: 'edit_calendar',
                title: 'Review preparation guidelines',
                desc: 'Confirm your appointment arrival and update any changed dental insurance info.'
            },
            {
                phase: 'Appointment Day',
                icon: 'brush',
                title: 'Brush teeth & prepare notes',
                desc: 'Ensure oral hygiene is fresh. Note down any localized spots of pain or tooth sensitivity.'
            },
            {
                phase: 'At Clinic',
                icon: 'login',
                title: 'Check in at reception',
                desc: 'Bring physical cards. Arrive 10 minutes early to quickly finalize paperwork.'
            }
        ]
    },
    'extraction': {
        title: 'Tooth Extraction (Wisdom)',
        date: 'Oct 28, 2026 at 10:00 AM',
        doctor: 'Dr. Robert Chen (Oral Surgeon)',
        icon: 'personal_injury',
        items: [
            "Arrange for a friend or family member to drive you home (required for sedation).",
            "Fasting: Do not eat or drink anything for 8 hours prior to your surgery time.",
            "Wear loose, comfortable clothing with short sleeves.",
            "Pick up your prescribed post-op antibiotics from the pharmacy beforehand."
        ],
        timeline: [
            {
                phase: 'Before Appointment',
                icon: 'local_taxi',
                title: 'Chaperone & Rx pickup',
                desc: 'Arrange a designated safe ride home. Pre-purchase surgical post-op meds from the pharmacy.'
            },
            {
                phase: 'Appointment Day',
                icon: 'no_food',
                title: 'Fasting compliance & clothes',
                desc: 'Observe strict 8-hour pre-surgery fast. Wear roomy, short-sleeved shirts for easy IV access.'
            },
            {
                phase: 'At Clinic',
                icon: 'assignment_turned_in',
                title: 'Fasting status confirmation',
                desc: 'Inform nurses of complete compliance with food instructions and verify driver contact details.'
            }
        ]
    },
    'root_canal': {
        title: 'Root Canal Therapy (Phase 1)',
        date: 'Nov 02, 2026 at 11:15 AM',
        doctor: 'Dr. Maria Santos',
        icon: 'dentistry',
        items: [
            "Take your prescribed pre-medication exactly 1 hour before the visit.",
            "Eat a normal, hearty meal before you arrive (your mouth will be numb afterward).",
            "Bring your preferred headphones to listen to music during the lengthy procedure.",
            "Take ibuprofen 30 minutes before arrival to preemptively manage inflammation."
        ],
        timeline: [
            {
                phase: 'Before Appointment',
                icon: 'pill',
                title: 'Pre-medication preparation',
                desc: 'Review instructions for preemptive antibiotics or calming oral medication.'
            },
            {
                phase: 'Appointment Day',
                icon: 'restaurant',
                title: 'Eat standard meal & premedicate',
                desc: 'Have a solid, nourishing meal. Take your prescription exactly 60 minutes before arrival.'
            },
            {
                phase: 'At Clinic',
                icon: 'headset',
                title: 'Settle in & relax',
                desc: 'Let our team set up local anesthesia. Wear your headphones and enjoy your audio.'
            }
        ]
    }
};

let totalItems = 0;
let currentProcedureKey = 'cleaning';

// 2. Load Data and Render UI
function loadMockData(procedureKey) {
    currentProcedureKey = procedureKey;
    const data = prepData[procedureKey];
    if (!data) return;

    // Update Header & Main Card UI
    document.getElementById('prep-title').textContent = data.title;
    document.getElementById('prep-date').textContent = data.date;
    document.getElementById('prep-doctor').textContent = data.doctor;
    document.getElementById('prep-icon').textContent = data.icon;

    // Update Demo Buttons UI
    document.querySelectorAll('[role="tablist"] button').forEach(btn => {
        btn.classList.remove('bg-amber-600', 'text-white', 'border-amber-600');
        btn.classList.add('bg-white', 'text-amber-900', 'border-amber-300');
    });
    const activeBtn = document.getElementById(`btn-${procedureKey}`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-white', 'text-amber-900', 'border-amber-300');
        activeBtn.classList.add('bg-amber-600', 'text-white', 'border-amber-600');
    }

    // Render Checklist Items
    const container = document.getElementById('checklist-container');
    container.innerHTML = '';
    totalItems = data.items.length;

    data.items.forEach((itemText, index) => {
        const itemHtml = `
            <label class="group flex items-start gap-4 p-4 rounded-xl bg-surface-container-low hover:bg-surface-variant/40 cursor-pointer transition-colors border border-transparent focus-within:ring-2 focus-within:ring-primary focus-within:border-transparent">
                <div class="relative flex items-center mt-0.5 shrink-0">
                    <input type="checkbox" class="peer sr-only prep-checkbox" onchange="updateReadiness()" aria-label="Mark task as complete">
                    
                    <!-- Custom Checkbox Box -->
                    <div class="w-6 h-6 rounded border-2 border-outline-variant peer-checked:bg-primary peer-checked:border-primary transition-all duration-200 flex items-center justify-center group-hover:border-primary">
                        <span class="material-symbols-outlined text-on-primary text-[16px] opacity-0 peer-checked:opacity-100 transition-opacity duration-200 font-bold">check</span>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="font-body-md text-on-surface transition-all duration-200 select-none peer-checked:text-on-surface-variant peer-checked:line-through">${itemText}</p>
                </div>
            </label>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
    });

    // Render Timeline Items
    const timelineContainer = document.getElementById('timeline-container');
    timelineContainer.innerHTML = '';
    
    if (data.timeline) {
        data.timeline.forEach((step, idx) => {
            const timelineHtml = `
                <div class="relative group">
                    <!-- Timeline Node Pin -->
                    <span class="absolute -left-[35px] top-0.5 w-6 h-6 rounded-full bg-white border-2 border-primary flex items-center justify-center text-primary shrink-0 transition-all duration-300 group-hover:bg-primary group-hover:text-white">
                        <span class="material-symbols-outlined text-[14px]">${step.icon || 'circle'}</span>
                    </span>
                    <div>
                        <span class="text-[10px] font-bold text-primary uppercase tracking-wider block mb-0.5">${step.phase}</span>
                        <h4 class="font-bold text-sm text-on-surface">${step.title}</h4>
                        <p class="text-xs text-on-surface-variant mt-0.5">${step.desc}</p>
                    </div>
                </div>
            `;
            timelineContainer.insertAdjacentHTML('beforeend', timelineHtml);
        });
    }

    // Reset Core Checkboxes for demo realism
    document.getElementById('chk-confirm-appt').checked = true;
    document.getElementById('chk-med-history').checked = false;

    // Recalculate Readiness and Checkboxes
    updateReadiness();
}

// 3. Update Dynamic Field-Based Readiness Progress
function updateReadiness() {
    // A. Verify states
    const apptConfirmed = document.getElementById('chk-confirm-appt').checked;
    const medHistoryUpdated = document.getElementById('chk-med-history').checked;

    const checkboxes = document.querySelectorAll('.prep-checkbox');
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    
    // Checklist preparation completion percentage
    const checklistPercent = totalItems === 0 ? 100 : Math.round((checkedCount / totalItems) * 100);

    // Update Checklist text status & bar
    document.getElementById('progress-text').textContent = `${checkedCount} of ${totalItems} Completed`;
    document.getElementById('progress-bar').style.width = `${checklistPercent}%`;

    // B. Calculate weighted readiness score
    // Weightings: Appt Confirmed = 35%, Med History = 35%, Checklist Completed = 30%
    let overallScore = 0;
    if (apptConfirmed) overallScore += 35;
    if (medHistoryUpdated) overallScore += 35;
    overallScore += Math.round((checklistPercent / 100) * 30);

    // C. Update Dashboard Integration badging & indicators
    const circle = document.getElementById('readiness-circle');
    const label = document.getElementById('readiness-percent');
    const descLabel = document.getElementById('readiness-status-label');

    // Update percentage label
    label.textContent = `${overallScore}%`;

    // Dynamic stroke offset math
    // Dasharray is 175.9 (2 * pi * r=28)
    const strokeOffset = 175.9 - (175.9 * (overallScore / 100));
    circle.style.strokeDashoffset = strokeOffset;

    // Action status labels
    if (overallScore === 100) {
        descLabel.textContent = "Fully Prepared";
        descLabel.className = "text-[11px] text-emerald-600 font-bold";
    } else if (overallScore >= 70) {
        descLabel.textContent = "Mostly Prepared";
        descLabel.className = "text-[11px] text-primary font-bold";
    } else {
        descLabel.textContent = "Needs Attention";
        descLabel.className = "text-[11px] text-red-500 font-bold";
    }

    // Badge 1: Appt Confirmed
    const b1Icon = document.getElementById('badge-appt-confirmed-icon');
    const b1Txt = document.getElementById('badge-appt-confirmed');
    if (apptConfirmed) {
        b1Icon.textContent = "check_circle";
        b1Icon.className = "material-symbols-outlined text-emerald-600";
        b1Txt.textContent = "Confirmed";
        b1Txt.className = "text-[11px] text-emerald-600 font-bold";
    } else {
        b1Icon.textContent = "pending";
        b1Icon.className = "material-symbols-outlined text-outline";
        b1Txt.textContent = "Action Required";
        b1Txt.className = "text-[11px] text-on-surface-variant";
    }

    // Badge 2: Med History
    const b2Icon = document.getElementById('badge-med-history-icon');
    const b2Txt = document.getElementById('badge-med-history');
    if (medHistoryUpdated) {
        b2Icon.textContent = "check_circle";
        b2Icon.className = "material-symbols-outlined text-emerald-600";
        b2Txt.textContent = "Up to Date";
        b2Txt.className = "text-[11px] text-emerald-600 font-bold";
    } else {
        b2Icon.textContent = "pending";
        b2Icon.className = "material-symbols-outlined text-outline";
        b2Txt.textContent = "Action Required";
        b2Txt.className = "text-[11px] text-on-surface-variant";
    }

    // Badge 3: Checklist
    const b3Icon = document.getElementById('badge-prep-checklist-icon');
    const b3Txt = document.getElementById('badge-prep-checklist');
    b3Txt.textContent = `${checklistPercent}% Complete`;
    if (checklistPercent === 100) {
        b3Icon.textContent = "check_circle";
        b3Icon.className = "material-symbols-outlined text-emerald-600";
        b3Txt.className = "text-[11px] text-emerald-600 font-bold";
    } else {
        b3Icon.textContent = "pending";
        b3Icon.className = "material-symbols-outlined text-outline";
        b3Txt.className = "text-[11px] text-on-surface-variant";
    }

    // Show overall bottom success message only if completely finished
    const completionMsg = document.getElementById('completion-message');
    if (overallScore === 100) {
        completionMsg.classList.remove('hidden');
        setTimeout(() => {
            completionMsg.classList.add('opacity-100', 'translate-y-0');
            completionMsg.classList.remove('opacity-0', 'translate-y-2');
        }, 10);
    } else {
        completionMsg.classList.add('hidden', 'opacity-0', 'translate-y-2');
        completionMsg.classList.remove('opacity-100', 'translate-y-0');
    }
}

// 4. Emergency Symptom Assessment Warning
function checkEmergencySymptoms() {
    const sPain = document.getElementById('symp-pain').checked;
    const sSwelling = document.getElementById('symp-swelling').checked;
    const sBleeding = document.getElementById('symp-bleeding').checked;
    const sTrauma = document.getElementById('symp-trauma').checked;

    const alertBox = document.getElementById('emergency-alert');
    if (sPain || sSwelling || sBleeding || sTrauma) {
        alertBox.classList.remove('hidden');
        alertBox.classList.add('animate-pulse');
    } else {
        alertBox.classList.add('hidden');
        alertBox.classList.remove('animate-pulse');
    }
}

// 5. Action Hub Controller
function triggerAction(actionType) {
    const titleEl = document.getElementById('modal-title');
    const bodyEl = document.getElementById('modal-body');

    if (actionType === 'update-history') {
        titleEl.textContent = "Medical History Portal";
        bodyEl.textContent = "You are now loading your safe clinical intake workspace. Once saved, your status indicators will dynamically reflect medical clearance.";
        // Automatically check the medical history field box for demonstration purposes!
        document.getElementById('chk-med-history').checked = true;
        updateReadiness();
    } else if (actionType === 'contact-clinic') {
        titleEl.textContent = "Direct Office Line";
        bodyEl.textContent = "DentalCare Pro Helpdesk: (555) 019-2831. Our specialists are online Monday through Friday to address custom pre-procedural questions.";
    } else if (actionType === 'reschedule') {
        titleEl.textContent = "Reschedule Appointment";
        bodyEl.textContent = "To pick a new date or cancel without a fee, select a slot on the client appointment timeline, or contact customer support directly.";
    } else if (actionType === 'view-details') {
        const data = prepData[currentProcedureKey];
        titleEl.textContent = "Procedure Overview";
        bodyEl.textContent = `You are scheduled for the "${data.title}" under the expert care of ${data.doctor}. This appointment involves tailored sedation criteria. Check your patient portal for clinical details.`;
    }

    document.getElementById('info-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('info-modal').classList.add('hidden');
}

// Initialize default view on mount
document.addEventListener('DOMContentLoaded', () => {
    loadMockData('cleaning');
});
</script>

<?php
// 4. Close the buffer and save everything captured
$pageContent = ob_get_clean();

// 5. Require the layout shell
require_once __DIR__ . '/../../components/layout/main-layout.php';
?>