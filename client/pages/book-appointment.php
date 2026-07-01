<?php
/**
 * book-appointment.php
 * Premium Interactive Booking Portal for DentalCare Pro.
 * Tailored for single-practitioner portal (Dr. Maria Santos).
 */

// 1. We require design-config manually at the top so $APP_ENV is available for our logic
require_once __DIR__ . '/../components/design-config.php';

// Shared service taxonomy (replaces local array)
require_once __DIR__ . '/../components/services-data.php';

// 2. Set the variables the layout shell needs
$activePage = 'book';
$pageTitle  = 'Book Appointment';

// 3. Start intercepting standard output (Output Buffering)
ob_start();
?>

<style>
    /* Clean interactive focus states */
    .slot-pill-active {
        background-color: #003164 !important;
        color: #ffffff !important;
        border-color: #003164 !important;
    }
    .category-card-active {
        border-color: #003164 !important;
        background-color: #f8f9ff !important;
        box-shadow: 0 4px 20px rgba(0, 49, 100, 0.05);
    }
</style>

<!-- DIAGNOSTIC PANEL (DEVELOPMENT ONLY) -->
<?php if (APP_ENV === 'development'): ?>
<section class="bg-surface-container-low rounded-2xl p-4 border border-outline-variant flex flex-wrap gap-4 items-center justify-between"
         aria-label="Developer diagnostic panel">
    <div class="space-y-1">
        <h4 class="font-headline-md text-sm font-semibold text-primary">Booking Engine Diagnostic Console
            <span class="ml-2 text-[10px] bg-amber-100 text-amber-800 px-2 py-0.5 rounded font-bold uppercase tracking-wide">Dev only</span>
        </h4>
        <p class="font-body-sm text-xs text-on-surface-variant">Verify client-side form validation, pre-visit condition switching, and empty availability states.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button onclick="autofillDemoData()"
                class="bg-surface-container-highest hover:bg-surface-variant text-primary font-label-md text-xs py-2 px-4 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary flex items-center">
            <span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">assignment_turned_in</span> Autofill Form
        </button>
        <button onclick="simulateFullyBookedDate()"
                class="bg-surface-container-highest hover:bg-surface-variant text-primary font-label-md text-xs py-2 px-4 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary flex items-center">
            <span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">block</span> Force "No Slots" Date
        </button>
    </div>
</section>
<?php endif; ?>

<!-- HERO HEADER -->
<div class="fade-in flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1.5">
            <span class="material-symbols-outlined text-secondary text-sm">verified</span>
            <span class="font-label-sm text-xs font-semibold text-secondary-text uppercase tracking-widest">Dr. Maria Santos</span>
        </div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-primary">Book Appointment</h2>
        <p class="font-body-md text-on-surface-variant mt-1">Schedule your next dental visit and sync clinic preparations seamlessly.</p>
    </div>
</div>

<!-- PERSISTENT CLINICAL ADDRESS BAR -->
<div class="fade-in bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs font-semibold text-slate-700">
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[#1652a0] text-sm" aria-hidden="true">location_on</span>
            <span><?php echo htmlspecialchars(CLINIC_ADDRESS); ?></span>
        </div>
        <div class="hidden sm:block text-slate-300">·</div>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[#1652a0] text-sm" aria-hidden="true">contact_support</span>
            <span>Front Desk: <?php echo htmlspecialchars(CLINIC_PHONE); ?> · <?php echo htmlspecialchars(CLINIC_EMAIL); ?></span>
        </div>
    </div>
</div>

<!-- MAIN LAYOUT: Split Forms and Booking Summary Live Ticket -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left & Center: Interactive Steps -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- STEP 1: Treatment Category & Specific Service Selection -->
        <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_4px_16px_rgba(0,71,141,0.02)] space-y-4">
            <div class="flex items-center space-x-3 border-b border-slate-50 pb-3">
                <span class="w-7 h-7 bg-blue-50 text-[#1652a0] rounded-full flex items-center justify-center font-bold text-xs">1</span>
                <h3 class="font-headline-md text-base text-slate-800 font-bold">Select Treatment & Service</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="categoryGrid">
                <?php foreach ($serviceCategories as $cat): ?>
                    <button type="button" 
                            onclick="selectCategory('<?php echo $cat['id']; ?>')"
                            id="cat-card-<?php echo $cat['id']; ?>"
                            class="category-card text-left p-4 rounded-xl border border-slate-100 hover:border-slate-300 transition-all flex items-start gap-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                            data-category-id="<?php echo $cat['id']; ?>"
                            data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                            data-pre-visit="<?php echo htmlspecialchars($cat['pre_visit']); ?>"
                            data-estimate="<?php echo htmlspecialchars($cat['estimate']); ?>">
                        <div class="w-12 h-12 rounded-xl border flex items-center justify-center flex-shrink-0 <?php echo $cat['color']; ?>">
                            <span class="material-symbols-outlined text-2xl" aria-hidden="true"><?php echo $cat['icon']; ?></span>
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="font-bold text-sm text-slate-800"><?php echo htmlspecialchars($cat['name']); ?></h4>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed"><?php echo htmlspecialchars($cat['desc']); ?></p>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Specific Service Selection (Hidden until category is picked) -->
            <div id="serviceSelection" class="hidden pt-4 mt-2 border-t border-slate-50">
                <label class="block text-xs font-bold text-secondary-text mb-3 uppercase tracking-wide">Specific Service</label>
                <?php foreach ($serviceCategories as $cat): ?>
                    <div id="services-for-<?php echo $cat['id']; ?>" class="service-group hidden flex-wrap gap-2">
                        <?php foreach ($cat['services'] as $svc): ?>
                            <button type="button"
                                    onclick="selectService('<?php echo $svc['id']; ?>', '<?php echo htmlspecialchars($svc['name'], ENT_QUOTES); ?>', this)"
                                    class="service-pill bg-slate-50 hover:bg-slate-100 border border-slate-100/60 rounded-lg py-2.5 px-4 text-xs text-center text-slate-700 font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-primary"
                                    data-service-id="<?php echo $svc['id']; ?>">
                                <?php echo htmlspecialchars($svc['name']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

        <!-- STEP 2: Choose Date & Available Slot -->
        <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_4px_16px_rgba(0,71,141,0.02)] space-y-5">
            <div class="flex items-center space-x-3 border-b border-slate-50 pb-3">
                <span class="w-7 h-7 bg-blue-50 text-[#1652a0] rounded-full flex items-center justify-center font-bold text-xs">2</span>
                <h3 class="font-headline-md text-base text-slate-800 font-bold">Choose Date & Time</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date Picker -->
                <div>
                    <label for="booking-date" class="block text-xs font-bold text-secondary-text mb-2.5 uppercase tracking-wide">Preferred Date</label>
                    <input type="date" 
                           id="booking-date" 
                           onchange="handleDateChange(this.value)"
                           class="w-full border-slate-200 rounded-xl text-sm text-slate-700 py-3 focus:border-primary focus:ring-primary transition-all shadow-sm"/>
                </div>

                <!-- Time Slot Picker -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-secondary-text uppercase tracking-wide">Available Sessions</label>
                    
                    <!-- Dynamic / Re-rendered Time Container -->
                    <div id="slotsGrid" class="space-y-4">
                        <div class="py-6 text-center text-xs font-semibold text-slate-400">
                            Please select a preferred date first.
                        </div>
                    </div>

                    <!-- "No Slots" fallback visual element -->
                    <div id="noSlotsMessage" class="hidden p-4 rounded-xl bg-red-50 border border-red-100/50 text-center">
                        <span class="material-symbols-outlined text-red-500 text-3xl mb-1" aria-hidden="true">event_busy</span>
                        <h5 class="text-xs font-bold text-red-800">Date Fully Booked</h5>
                        <p class="text-[11px] text-red-600 mt-0.5">Please check standard business operating days or use our diagnostics toggle.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- STEP 3: Patient Profile Details -->
        <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_4px_16px_rgba(0,71,141,0.02)] space-y-5">
            <div class="flex items-center space-x-3 border-b border-slate-50 pb-3">
                <span class="w-7 h-7 bg-blue-50 text-[#1652a0] rounded-full flex items-center justify-center font-bold text-xs">3</span>
                <h3 class="font-headline-md text-base text-slate-800 font-bold">Patient Details Form</h3>
            </div>

            <div class="space-y-4">
                <!-- Booking For Someone Else Toggle Pill -->
                <div>
                    <button type="button" 
                            id="btn-book-someone-else"
                            onclick="toggleBookingForOthers()"
                            class="w-full py-3 px-4 text-xs font-bold rounded-xl border border-slate-200 transition-all text-center focus:outline-none bg-slate-50 text-slate-700 hover:bg-slate-100 flex items-center justify-center gap-2">
                        <span id="someone-else-check-icon" class="material-symbols-outlined text-base text-slate-400">check_box_outline_blank</span>
                        <span>Booking for someone else?</span>
                    </button>
                </div>

                <!-- Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="patient-name" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Full Name</label>
                        <input type="text" 
                               id="patient-name" 
                               placeholder="Alex Johnson"
                               class="w-full border-slate-200 rounded-xl text-sm text-slate-700 focus:border-primary focus:ring-primary transition-all"/>
                    </div>
                    <div>
                        <label for="patient-phone" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Contact Number</label>
                        <input type="tel" 
                               id="patient-phone" 
                               placeholder="(555) 019-2834"
                               class="w-full border-slate-200 rounded-xl text-sm text-slate-700 focus:border-primary focus:ring-primary transition-all"/>
                    </div>
                </div>

                <div>
                    <label for="patient-email" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Email Address</label>
                    <input type="email" 
                           id="patient-email" 
                           placeholder="alex@example.com"
                           class="w-full border-slate-200 rounded-xl text-sm text-slate-700 focus:border-primary focus:ring-primary transition-all"/>
                    <p id="email-hint" class="hidden text-[11px] text-indigo-600 font-medium mt-1">
                        Confirmation will be sent to your email.
                    </p>
                </div>

                <div>
                    <label for="visit-reason" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Reason for Visit (Notes)</label>
                    <textarea id="visit-reason" 
                              rows="3" 
                              placeholder="Please write down any symptoms or dental history Dr. Santos should review."
                              class="w-full border-slate-200 rounded-xl text-sm text-slate-700 focus:border-primary focus:ring-primary transition-all"></textarea>
                </div>
            </div>
        </section>

    </div>

    <!-- Right: Interactive Live Booking Receipt Summary -->
    <div class="space-y-6">
        
        <aside class="bg-white rounded-2xl border border-slate-100 shadow-[0_4px_24px_rgba(0,71,141,0.03)] overflow-hidden sticky top-24">
            <div class="bg-[#003164] p-5 text-white">
                <div class="flex items-center gap-2 text-xs font-bold text-blue-200 uppercase tracking-widest">
                    <span class="material-symbols-outlined text-sm">schedule</span>
                    <span>Visit Summary Ticket</span>
                </div>
                <h4 class="font-headline-lg text-lg text-white mt-1">Live Appointment Card</h4>
            </div>

            <div class="p-6 space-y-6">
                
                <!-- Client & Doctor Meta -->
                <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3 border border-slate-100/50">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBo_w78N2XULoEbVHqmSIePlmFD1bSVx8i_Rote2fYoyu5p0-5lFMoJmKOR-wq1Jel3bUzyUZFF_GGderejoknzrdxrqX6UNt5RCV225IaGT5t4CcLB6efdfW8jbpk_9-_bMONSju3RQ9YVk3rAq6VsupaIIDIR4--B9rlv9Cw-wdFfIH_DEhFndOTjWwnxIaIFxbuKCV-IROtQmUqfd8yMj6lR3-Vw3R6cHtXCmp9mrDr4zD8EBIaQX8BkXakX-H0u4en4ewDp1X4" 
                         class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm flex-shrink-0" 
                         alt="Dr. Maria Santos Profile Photo">
                    <div class="leading-tight">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Dental Practitioner</span>
                        <span class="text-xs font-bold text-slate-700 block">Dr. Maria Santos, DDS</span>
                    </div>
                </div>

                <!-- Ticket Details Line Items -->
                <div class="space-y-3.5 border-b border-slate-100 pb-5">
                    <div class="flex justify-between items-start text-xs font-semibold">
                        <span class="text-slate-400">Treatment:</span>
                        <span id="summaryTreatmentName" class="text-slate-700 text-right w-2/3">No category chosen</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-400">Scheduled Date:</span>
                        <span id="summaryDate" class="text-slate-700">Not selected</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-400">Scheduled Time:</span>
                        <span id="summaryTime" class="text-slate-700">Not selected</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-400">Booking Mode:</span>
                        <span id="summaryBookingMode" class="text-slate-700 font-bold">Self Appointment</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-400">Patient Name:</span>
                        <span id="summaryPatientName" class="text-slate-700">Alex Johnson</span>
                    </div>
                </div>

                <!-- Cost Estimate Box -->
                <div class="p-3.5 bg-slate-50/75 border border-slate-100/50 rounded-xl space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Financial Evaluation</span>
                    <span id="summaryCostEstimate" class="text-xs font-bold text-[#1652a0] block leading-snug">Choose category for initial pricing estimates.</span>
                </div>

                <!-- Dynamic Pre-Visit Instruction Prompt -->
                <div id="summaryPreVisitBox" class="hidden p-3.5 bg-indigo-50/50 border border-indigo-100/50 rounded-xl space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-900">
                        <span class="material-symbols-outlined text-sm text-indigo-600">tips_and_updates</span>
                        <span>Pre-Visit Instruction:</span>
                    </div>
                    <p id="summaryPreVisitText" class="text-[11px] text-slate-600 leading-normal font-medium"></p>
                </div>

                <!-- CANCELLATION NOTICE -->
                <div class="text-[11px] text-slate-400 font-semibold leading-relaxed flex gap-2">
                    <span class="material-symbols-outlined text-sm text-slate-300 mt-0.5" aria-hidden="true">info</span>
                    <span>Please provide at least 24 hours' notice for cancellations or rescheduling to avoid standard lab preparation fees.</span>
                </div>

                <!-- Finalize Submission Trigger -->
                <button type="button"
                        id="submit-booking-btn"
                        onclick="submitBooking()"
                        class="w-full bg-[#1652a0] hover:bg-primary-container text-white font-bold text-sm py-3.5 px-6 rounded-xl transition-all shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">how_to_reg</span>
                    Confirm & Request Visit
                </button>

            </div>
        </aside>

    </div>

</div>

<!-- PAGE INTERACTIVE BUSINESS LOGIC CONTROLLER -->
<script>
let selectedCategory = "";
let selectedService = "";
let selectedServiceName = "";
let selectedDate = "";
let selectedTime = "";

// Session Pre-fill Storage Variables
let authProvider = "";
let sessionUser = {
    firstName: "",
    lastName: "",
    phone: "",
    email: ""
};
let isBookingForOthers = false;

// Minimum date calculation (can book starting tomorrow)
document.addEventListener('DOMContentLoaded', () => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dateInput = document.getElementById('booking-date');
    if (dateInput) {
        dateInput.min = tomorrow.toISOString().split('T')[0];
    }

    // Set default category to preventative to seed dynamic instruction experience
    selectCategory('prevent');

    // Load active logged-in user profile from session API
    fetch('api/get-session-user.php')
        .then(res => {
            if (res.status === 401) throw new Error('Unauthenticated');
            return res.json();
        })
        .then(result => {
            if (result.success && result.data) {
                const user = result.data;
                sessionUser.firstName = user.first_name || "";
                sessionUser.lastName = user.last_name || "";
                sessionUser.phone = user.phone || "";
                sessionUser.email = user.email || "";
                authProvider = user.auth_provider || "";

                // Pre-fill fields
                const nameField = document.getElementById('patient-name');
                const phoneField = document.getElementById('patient-phone');
                const emailField = document.getElementById('patient-email');

                if (nameField) nameField.value = (sessionUser.firstName + " " + sessionUser.lastName).trim();
                if (phoneField) phoneField.value = sessionUser.phone;
                if (emailField) {
                    emailField.value = sessionUser.email;
                    // Enforce lock/read-only mode on email address for security
                    emailField.readOnly = true;
                    emailField.classList.add('bg-slate-50', 'text-slate-500', 'cursor-not-allowed');
                }

                updateSummaryPatientName();
            }
        })
        .catch(err => {
            console.warn("Session retrieval bypassed or returned status code other than success.", err);
        });

    // Replace old sync syncLiveSummary with live input event listener on Name field
    const nameInput = document.getElementById('patient-name');
    if (nameInput) {
        nameInput.addEventListener('input', () => {
            updateSummaryPatientName();
        });
    }
});

function updateSummaryPatientName() {
    const inputVal = document.getElementById('patient-name').value.trim();
    const summarySpan = document.getElementById('summaryPatientName');
    
    if (isBookingForOthers) {
        summarySpan.textContent = inputVal ? inputVal : "Someone else";
    } else {
        const fallbackName = (sessionUser.firstName + " " + sessionUser.lastName).trim();
        summarySpan.textContent = inputVal ? inputVal : (fallbackName || "Alex Johnson");
    }
}

function selectCategory(catId) {
    selectedCategory = catId;
    selectedService = "";
    selectedServiceName = "";
    
    // Highlight active card
    document.querySelectorAll('.category-card').forEach(card => {
        card.classList.remove('category-card-active');
        if (card.dataset.categoryId === catId) {
            card.classList.add('category-card-active');
            
            // Sync live ticket summaries
            document.getElementById('summaryTreatmentName').textContent = card.dataset.name + " (Please pick a service)";
            document.getElementById('summaryCostEstimate').textContent = card.dataset.estimate;
            
            // Sync dynamic pre-visit instruction
            const preVisitBox = document.getElementById('summaryPreVisitBox');
            const preVisitText = document.getElementById('summaryPreVisitText');
            if (card.dataset.preVisit) {
                preVisitBox.classList.remove('hidden');
                preVisitText.textContent = card.dataset.preVisit;
            } else {
                preVisitBox.classList.add('hidden');
            }
        }
    });

    // Reveal specific service sub-step and filter correctly
    document.getElementById('serviceSelection').classList.remove('hidden');
    document.querySelectorAll('.service-group').forEach(group => {
        group.classList.add('hidden');
    });
    const targetGroup = document.getElementById('services-for-' + catId);
    if (targetGroup) {
        targetGroup.classList.remove('hidden');
    }

    // Clear any previously highlighted service pills
    document.querySelectorAll('.service-pill').forEach(pill => pill.classList.remove('slot-pill-active'));
}

function selectService(serviceId, name, element = null) {
    selectedService = serviceId;
    selectedServiceName = name;

    // Highlight active service pill
    document.querySelectorAll('.service-pill').forEach(p => p.classList.remove('slot-pill-active'));
    
    if (element) {
        element.classList.add('slot-pill-active');
    } else {
        const targetElement = document.querySelector(`.service-pill[data-service-id="${serviceId}"]`);
        if (targetElement) targetElement.classList.add('slot-pill-active');
    }

    // Update live summary
    const catCard = document.querySelector(`.category-card[data-category-id="${selectedCategory}"]`);
    if (catCard) {
        document.getElementById('summaryTreatmentName').textContent = catCard.dataset.name + ' — ' + name;
    }
}

function handleDateChange(value) {
    selectedDate = value;
    
    // Format visual display
    if (value) {
        const dateObj = new Date(value + 'T00:00:00');
        const formatted = dateObj.toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
        document.getElementById('summaryDate').textContent = formatted;
    } else {
        document.getElementById('summaryDate').textContent = "Not selected";
        return;
    }

    // Clear previously selected slot when date changes
    selectedTime = "";
    document.getElementById('summaryTime').textContent = "Not selected";

    // Fetch live availability from API
    fetchAvailability(value);
}

function fetchAvailability(dateString) {
    const slotsGrid = document.getElementById('slotsGrid');
    const noSlotsMessage = document.getElementById('noSlotsMessage');
    
    // Render dynamic visual loader spinner
    slotsGrid.classList.remove('hidden');
    noSlotsMessage.classList.add('hidden');
    slotsGrid.innerHTML = `
        <div class="col-span-3 py-8 flex flex-col items-center justify-center gap-2 text-slate-400">
            <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs font-semibold">Loading available slots...</span>
        </div>
    `;

    fetch(`api/availability.php?date=${dateString}`)
        .then(res => {
            if (!res.ok) throw new Error('Failed to retrieve slot availability.');
            return res.json();
        })
        .then(result => {
            if (result.success && result.data) {
                const slotsForDate = result.data[dateString] || [];
                renderSlots(slotsForDate);
            } else {
                throw new Error(result.message || 'Error occurred while loading data.');
            }
        })
        .catch(err => {
            console.error(err);
            slotsGrid.innerHTML = `
                <div class="col-span-3 p-4 rounded-xl bg-red-50 border border-red-100 text-center">
                    <p class="text-xs font-bold text-red-800">Could not load slots. Please try again.</p>
                </div>
            `;
            document.getElementById('summaryTime').textContent = "Not selected";
        });
}

function renderSlots(availableSlots) {
    const slotsGrid = document.getElementById('slotsGrid');
    const noSlotsMessage = document.getElementById('noSlotsMessage');

    slotsGrid.innerHTML = '';
    
    // Safety check for empty slot array returned by API
    if (!availableSlots || availableSlots.length === 0) {
        slotsGrid.classList.add('hidden');
        noSlotsMessage.classList.remove('hidden');
        return;
    }
    
    slotsGrid.classList.remove('hidden');
    noSlotsMessage.classList.add('hidden');

    // Standard baseline lists
    const morningStandard = ['09:00 AM', '10:00 AM', '11:00 AM'];
    const afternoonStandard = ['01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM'];

    // Combine standard list with any custom availability formats returned by endpoint
    const allMorning = [...morningStandard];
    const allAfternoon = [...afternoonStandard];

    availableSlots.forEach(slot => {
        if (slot.includes('AM')) {
            if (!allMorning.includes(slot)) allMorning.push(slot);
        } else if (slot.includes('PM')) {
            if (!allAfternoon.includes(slot)) allAfternoon.push(slot);
        }
    });

    // Helper to evaluate slot HTML
    const renderSlotButton = (slot, isAvailable) => {
        if (isAvailable) {
            const isSelected = (selectedTime === slot);
            const activeClass = isSelected ? 'bg-[#003164] text-white border-2 border-[#003164] slot-pill-active' : 'bg-white text-slate-700 border-slate-200 hover:border-[#1652a0]';
            const iconHtml = isSelected ? '<span class="material-symbols-outlined text-xs mr-1">check_circle</span>' : '';
            return `
                <button type="button" 
                        onclick="selectTimeSlot('${slot}', this)"
                        class="slot-btn border rounded-lg py-2.5 text-xs text-center font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-primary flex items-center justify-center gap-1 ${activeClass}">
                    ${iconHtml}<span>${slot}</span>
                </button>
            `;
        } else {
            return `
                <button type="button" 
                        disabled
                        class="bg-slate-50 border-slate-300 text-slate-300 line-through rounded-lg py-2.5 text-xs text-center font-semibold flex items-center justify-center gap-1 cursor-not-allowed">
                    <span class="material-symbols-outlined text-xs">block</span>
                    <span>${slot}</span>
                </button>
            `;
        }
    };

    // Calculate open counts
    const morningOpen = allMorning.filter(s => availableSlots.includes(s)).length;
    const afternoonOpen = allAfternoon.filter(s => availableSlots.includes(s)).length;

    // Morning Block
    const morningBlock = document.createElement('div');
    morningBlock.className = "space-y-2";
    morningBlock.innerHTML = `
        <div class="flex items-center justify-between gap-3 text-[11px] font-bold text-amber-600">
            <div class="flex items-center gap-1.5 uppercase tracking-wide">
                <span class="material-symbols-outlined text-sm">wb_sunny</span>
                <span>Morning</span>
            </div>
            <div class="flex-grow border-t border-dashed border-slate-200 mx-2"></div>
            <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded text-[10px]">${morningOpen} open</span>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-1.5">
            ${allMorning.map(slot => renderSlotButton(slot, availableSlots.includes(slot))).join('')}
        </div>
    `;

    // Afternoon Block
    const afternoonBlock = document.createElement('div');
    afternoonBlock.className = "space-y-2 mt-4";
    afternoonBlock.innerHTML = `
        <div class="flex items-center justify-between gap-3 text-[11px] font-bold text-[#1652a0]">
            <div class="flex items-center gap-1.5 uppercase tracking-wide">
                <span class="material-symbols-outlined text-sm">partly_cloudy_day</span>
                <span>Afternoon</span>
            </div>
            <div class="flex-grow border-t border-dashed border-slate-200 mx-2"></div>
            <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded text-[10px]">${afternoonOpen} open</span>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-1.5">
            ${allAfternoon.map(slot => renderSlotButton(slot, availableSlots.includes(slot))).join('')}
        </div>
    `;

    slotsGrid.appendChild(morningBlock);
    slotsGrid.appendChild(afternoonBlock);
}

function selectTimeSlot(time, element) {
    selectedTime = time;
    
    // Clear styles from all active slot buttons
    document.querySelectorAll('.slot-btn').forEach(btn => {
        btn.className = "slot-btn border rounded-lg py-2.5 text-xs text-center font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-primary flex items-center justify-center gap-1 bg-white text-slate-700 border-slate-200 hover:border-[#1652a0]";
        const checkingIcon = btn.querySelector('.material-symbols-outlined');
        if (checkingIcon) checkingIcon.remove();
    });

    // Apply selected active parameters to element
    element.className = "slot-btn border rounded-lg py-2.5 text-xs text-center font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-primary flex items-center justify-center gap-1 bg-[#003164] text-white border-2 border-[#003164] slot-pill-active";
    
    const iconSpan = document.createElement('span');
    iconSpan.className = "material-symbols-outlined text-xs mr-1";
    iconSpan.textContent = "check_circle";
    element.insertBefore(iconSpan, element.firstChild);

    document.getElementById('summaryTime').textContent = time;
}

function toggleBookingForOthers() {
    isBookingForOthers = !isBookingForOthers;
    
    const btn = document.getElementById('btn-book-someone-else');
    const icon = document.getElementById('someone-else-check-icon');
    const nameField = document.getElementById('patient-name');
    const phoneField = document.getElementById('patient-phone');
    const emailHint = document.getElementById('email-hint');
    const modeSummary = document.getElementById('summaryBookingMode');

    if (isBookingForOthers) {
        // Toggle Active Styling State
        btn.classList.add('bg-indigo-50', 'border-indigo-300', 'text-indigo-700');
        btn.classList.remove('bg-slate-50', 'border-slate-200', 'text-slate-700');
        icon.textContent = "check_box";
        icon.classList.add('text-indigo-600');
        icon.classList.remove('text-slate-400');

        // Reset user parameters for proxy patient data
        nameField.value = "";
        phoneField.value = "";

        // UI Helpers
        emailHint.classList.remove('hidden');
        nameField.focus();

        modeSummary.textContent = "For Someone Else";
        modeSummary.classList.add('text-indigo-600');
    } else {
        // Toggle Normal Styling State
        btn.classList.remove('bg-indigo-50', 'border-indigo-300', 'text-indigo-700');
        btn.classList.add('bg-slate-50', 'border-slate-200', 'text-slate-700');
        icon.textContent = "check_box_outline_blank";
        icon.classList.remove('text-indigo-600');
        icon.classList.add('text-slate-400');

        // Restore prefilled session user values
        nameField.value = (sessionUser.firstName + " " + sessionUser.lastName).trim();
        phoneField.value = sessionUser.phone;

        emailHint.classList.add('hidden');

        modeSummary.textContent = "Self Appointment";
        modeSummary.classList.remove('text-indigo-600');
    }

    updateSummaryPatientName();
}

/* Dynamic Submission to API Endpoint */
function submitBooking() {
    const name = document.getElementById('patient-name').value.trim();
    const phone = document.getElementById('patient-phone').value.trim();
    const email = document.getElementById('patient-email').value.trim();
    const notes = document.getElementById('visit-reason').value.trim();

    // Validation checks
    if (!selectedCategory) {
        showGlobalToast('error', 'Validation Error: Please select a Treatment Category (Step 1).');
        return;
    }
    if (!selectedService) {
        showGlobalToast('error', 'Validation Error: Please select a specific service (Step 1).');
        return;
    }
    if (!selectedDate) {
        showGlobalToast('error', 'Validation Error: Please choose a Preferred Date (Step 2).');
        return;
    }
    if (!selectedTime) {
        showGlobalToast('error', 'Validation Error: Please pick an Available Session Time Slot (Step 2).');
        return;
    }
    if (!name) {
        showGlobalToast('error', 'Validation Error: Patient Full Name is required.');
        return;
    }
    if (!phone && !email) {
        showGlobalToast('error', 'Validation Error: Provide at least one form of communication (Phone or Email).');
        return;
    }

    // Process name parsing
    const nameParts = name.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || '';

    // Lock Submit button to block concurrent click attempts and render dynamic spinner
    const submitBtn = document.getElementById('submit-booking-btn');
    const originalBtnHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin h-4 w-4 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Processing Request...</span>
    `;

    // Make standard post request with application JSON parameters
    fetch('api/booking-create.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            firstName,
            lastName,
            email,
            phone,
            service: selectedService,
            date: selectedDate,
            time: selectedTime,
            notes
        })
    })
    .then(async res => {
        const result = await res.json();
        
        // Handle slot conflict (409)
        if (res.status === 409 || (result && result.error_code === 'SLOT_TAKEN')) {
            showGlobalToast('error', result.message || 'The selected slot has been booked. Please choose a different session.');
            fetchAvailability(selectedDate); // Re-fetch availability instantly to refresh screen
            throw new Error('Slot taken.');
        }

        if (!res.ok || !result.success) {
            throw new Error(result.message || 'Booking failed to save.');
        }

        return result;
    })
    .then(result => {
        // Successful booking execution
        if (result.data && result.data.reference_code) {
            sessionStorage.setItem('last_booking_ref', result.data.reference_code);
        }
        showGlobalToast('success', 'Appointment confirmed!');
        setTimeout(() => {
            location.href = '../client/pages/appointments.php';
        }, 1500);
    })
    .catch(err => {
        console.error(err);
        if (err.message !== 'Slot taken.') {
            showGlobalToast('error', err.message || 'Booking error. Please check your inputs and try again.');
        }
        // Unlock submit button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHTML;
    });
}

/* Diagnostic simulation helpers */
function autofillDemoData() {
    document.getElementById('patient-name').value = "Alex Johnson";
    document.getElementById('patient-phone').value = "555-0199";
    
    if (!document.getElementById('patient-email').value) {
        document.getElementById('patient-email').value = "alex.johnson@example.com";
    }
    
    document.getElementById('visit-reason').value = "Consultation for routine Invisalign cleaning adjustments.";
    
    selectCategory('prevent');
    selectService('checkup', 'Routine Checkup & Cleaning');
    
    // Automatically set a date (2 weeks out)
    const targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + 14);
    const dateInput = document.getElementById('booking-date');
    dateInput.value = targetDate.toISOString().split('T')[0];
    selectedDate = dateInput.value;

    const dateObj = new Date(selectedDate + 'T00:00:00');
    document.getElementById('summaryDate').textContent = dateObj.toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });

    // Populate mock demo sessions
    renderSlots(['09:00 AM', '10:00 AM', '01:00 PM']);

    // Auto select first generated demo session 
    setTimeout(() => {
        const firstSlotBtn = document.querySelector('.slot-btn');
        if (firstSlotBtn) {
            selectTimeSlot('09:00 AM', firstSlotBtn);
        }
    }, 50);

    updateSummaryPatientName();
    showGlobalToast('info', 'Demo parameters loaded. Preview complete booking details.');
}

function simulateFullyBookedDate() {
    renderSlots([]); // Triggers immediate screen conversion into full booking fallback
    selectedTime = "";
    document.getElementById('summaryTime').textContent = "Not selected";
    showGlobalToast('warning', 'Simulated State: Current chosen date has 0 open slots.');
}
</script>

<?php
// 4. Close the buffer and save everything captured so far into $pageContent
$pageContent = ob_get_clean();

// 5. Require the layout shell, which will handle wrapping $pageContent
require_once __DIR__ . '/../components/layout/main-layout.php';
?>