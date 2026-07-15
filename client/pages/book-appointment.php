<?php
/**
 * book-appointment.php
 * Premium Interactive Booking Portal for DentalCare Pro.
 * Tailored for single-practitioner portal (Dr. Maria Santos).
 */

// 1. design-config.php provides BASE_PATH, SITE_NAME, and other shared constants
require_once __DIR__ . '/../components/design-config.php';
require_once __DIR__ . '/../../api/helper/_api-helpers.php';

// Session must be active before getCsrfToken() touches $_SESSION — same
// reasoning as appointments.php: main-layout.php (required at the bottom
// of this file, via auth-guard.php) also calls session_start(), but
// that's too late for this line.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token for the booking-create.php POST submission
$csrfToken = getCsrfToken();

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

    /* Mini inline calendar */
    .cal-day-btn {
        width: 100%;
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
        border-radius: 8px;
        color: #334155;
        background: transparent;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }
    .cal-day-btn:hover:not(:disabled) {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
    }
    .cal-day-btn:disabled {
        color: #cbd5e1;
        cursor: not-allowed;
    }
    .cal-day-btn.cal-day-active {
        background-color: #003164 !important;
        color: #ffffff !important;
        border-color: #003164 !important;
        box-shadow: 0 2px 8px rgba(0, 49, 100, 0.25);
    }
    .cal-day-btn.cal-day-today:not(.cal-day-active) {
        border-color: #1652a0;
        color: #1652a0;
    }
    .cal-nav-btn {
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: #64748b;
        transition: all 0.15s ease;
        border: 1px solid transparent;
    }
    .cal-nav-btn:hover:not(:disabled) {
        background-color: #f1f5f9;
        color: #1652a0;
    }
    .cal-nav-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    /* Patient details form enhancements */
    .field-input-icon-wrap {
        position: relative;
    }
    .field-input-icon-wrap .field-icon {
        position: absolute;
        left: 12px;
        top: 13px;
        color: #94a3b8;
        font-size: 18px;
        pointer-events: none;
        transition: color 0.15s ease;
    }
    .field-input-icon-wrap textarea ~ .field-icon {
        top: 14px;
    }
    .field-input-icon-wrap input:focus ~ .field-icon,
    .field-input-icon-wrap textarea:focus ~ .field-icon {
        color: #1652a0;
    }
    .field-input-icon-wrap input,
    .field-input-icon-wrap textarea {
        padding-left: 40px !important;
    }
    .field-input-icon-wrap input {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
    .field-input-icon-wrap textarea {
        padding-top: 0.7rem !important;
        padding-bottom: 0.7rem !important;
        line-height: 1.5;
    }
</style>


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

<!-- MAIN LAYOUT: Split Forms and Booking Summary Live Ticket -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left & Center: Interactive Steps -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- STEP 1: Treatment Category Selection -->
        <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_4px_16px_rgba(0,71,141,0.02)] space-y-4">
            <div class="flex items-center space-x-3 border-b border-slate-50 pb-3">
                <span class="w-7 h-7 bg-blue-50 text-[#1652a0] rounded-full flex items-center justify-center font-bold text-xs">1</span>
                <h3 class="font-headline-md text-base text-slate-800 font-bold">Select Treatment</h3>
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
                            data-estimate="<?php echo htmlspecialchars($cat['estimate']); ?>"
                            <?php if (!empty($cat['services'][0])): ?>
                            data-default-service-id="<?php echo $cat['services'][0]['id']; ?>"
                            data-default-service-name="<?php echo htmlspecialchars($cat['services'][0]['name'], ENT_QUOTES); ?>"
                            <?php endif; ?>>
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

        </section>

        <!-- STEP 2: Choose Date & Available Slot -->
        <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_4px_16px_rgba(0,71,141,0.02)] space-y-5">
            <div class="flex items-center space-x-3 border-b border-slate-50 pb-3">
                <span class="w-7 h-7 bg-blue-50 text-[#1652a0] rounded-full flex items-center justify-center font-bold text-xs">2</span>
                <h3 class="font-headline-md text-base text-slate-800 font-bold">Choose Date & Time</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date Picker: Inline Mini Calendar -->
                <div>
                    <label class="block text-xs font-bold text-secondary-text mb-2.5 uppercase tracking-wide">Preferred Date</label>
                    <div class="border border-slate-200 rounded-xl p-3 bg-white shadow-sm select-none">
                        <!-- Month navigation header -->
                        <div class="flex items-center justify-between mb-2 px-0.5">
                            <button type="button" id="calPrevBtn" onclick="changeCalendarMonth(-1)" class="cal-nav-btn" aria-label="Previous month">
                                <span class="material-symbols-outlined text-base">chevron_left</span>
                            </button>
                            <span id="calendarMonthLabel" class="text-xs font-bold text-slate-700 tracking-wide"></span>
                            <button type="button" id="calNextBtn" onclick="changeCalendarMonth(1)" class="cal-nav-btn" aria-label="Next month">
                                <span class="material-symbols-outlined text-base">chevron_right</span>
                            </button>
                        </div>
                        <!-- Weekday labels -->
                        <div class="grid grid-cols-7 gap-1 mb-1">
                            <?php foreach (['S','M','T','W','T','F','S'] as $wd): ?>
                                <div class="text-center text-[10px] font-bold text-slate-400"><?php echo $wd; ?></div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Day grid (populated by JS) -->
                        <div id="calendarDaysGrid" class="grid grid-cols-7 gap-1"></div>

                        <!-- Selected date readout -->
                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                            <span class="material-symbols-outlined text-sm text-[#1652a0]">event</span>
                            <span id="calendarSelectedReadout">No date selected yet</span>
                        </div>
                    </div>
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
            <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                <div class="flex items-center space-x-3">
                    <span class="w-7 h-7 bg-blue-50 text-[#1652a0] rounded-full flex items-center justify-center font-bold text-xs">3</span>
                    <h3 class="font-headline-md text-base text-slate-800 font-bold">Patient Details Form</h3>
                </div>
                <span class="hidden sm:flex items-center gap-1 text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                    <span class="material-symbols-outlined text-sm text-slate-300">lock</span>
                    Secure
                </span>
            </div>

            <div class="space-y-5">
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
                        <div class="field-input-icon-wrap">
                            <input type="text" 
                                   id="patient-name" 
                                   placeholder="Alex Johnson"
                                   autocomplete="name"
                                   class="w-full border-slate-200 rounded-xl text-sm text-slate-700 py-3 focus:border-primary focus:ring-primary transition-all"/>
                            <span class="material-symbols-outlined field-icon" aria-hidden="true">person</span>
                        </div>
                    </div>
                    <div>
                        <label for="patient-phone" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Contact Number</label>
                        <div class="field-input-icon-wrap">
                            <input type="tel" 
                                   id="patient-phone" 
                                   placeholder="(555) 019-2834"
                                   autocomplete="tel"
                                   class="w-full border-slate-200 rounded-xl text-sm text-slate-700 py-3 focus:border-primary focus:ring-primary transition-all"/>
                            <span class="material-symbols-outlined field-icon" aria-hidden="true">call</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="patient-email" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Email Address</label>
                    <div class="field-input-icon-wrap">
                        <input type="email" 
                               id="patient-email" 
                               placeholder="alex@example.com"
                               autocomplete="email"
                               class="w-full border-slate-200 rounded-xl text-sm text-slate-700 py-3 focus:border-primary focus:ring-primary transition-all"/>
                        <span class="material-symbols-outlined field-icon" aria-hidden="true">mail</span>
                    </div>
                    <p id="email-hint" class="hidden text-[11px] text-indigo-600 font-medium mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">info</span>
                        Confirmation will be sent to your email.
                    </p>
                </div>

                <div>
                    <label for="visit-reason" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Reason for Visit (Notes)</label>
                    <div class="field-input-icon-wrap">
                        <textarea id="visit-reason" 
                                  rows="3" 
                                  placeholder="Please write down any symptoms or dental history Dr. Santos should review."
                                  class="w-full border-slate-200 rounded-xl text-sm text-slate-700 focus:border-primary focus:ring-primary transition-all"></textarea>
                        <span class="material-symbols-outlined field-icon" aria-hidden="true">edit_note</span>
                    </div>
                </div>

                <!-- Reassurance strip -->
                <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-400 pt-1">
                    <span class="material-symbols-outlined text-sm text-slate-300">shield</span>
                    <span>Your details are encrypted and only shared with Dr. Santos' clinic staff.</span>
                </div>
            </div>
        </section>

    </div>

    <!-- Right: Interactive Live Booking Receipt Summary -->
    <div class="space-y-6 self-start lg:sticky lg:top-24">
        
        <aside class="bg-white rounded-2xl border border-slate-100 shadow-[0_4px_24px_rgba(0,71,141,0.03)] overflow-hidden">
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
const CSRF_TOKEN = <?php echo json_encode($csrfToken); ?>;

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

// Minimum bookable date is tomorrow
const calMinDate = new Date();
calMinDate.setDate(calMinDate.getDate() + 1);
calMinDate.setHours(0, 0, 0, 0);

// Tracks which month/year the mini calendar is currently displaying
let calendarViewDate = new Date(calMinDate.getFullYear(), calMinDate.getMonth(), 1);

document.addEventListener('DOMContentLoaded', () => {
    // Build the initial mini calendar view
    renderCalendar();

    // Set default category to preventative to seed dynamic instruction experience
    selectCategory('checkup');

    // Load active logged-in user profile from session API
    fetch('../../api/get-session-user.php')
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
            document.getElementById('summaryTreatmentName').textContent = card.dataset.name;
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

            // With the specific-service picker removed, auto-assign the
            // category's primary service so booking submission still works.
            if (card.dataset.defaultServiceId) {
                selectService(card.dataset.defaultServiceId, card.dataset.defaultServiceName);
            }
        }
    });
}

function selectService(serviceId, name) {
    selectedService = serviceId;
    selectedServiceName = name;
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

/* ---------------------------------------------------------- */
/* Inline Mini Calendar: render, navigate, select              */
/* ---------------------------------------------------------- */
function toLocalDateString(dateObj) {
    const y = dateObj.getFullYear();
    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
    const d = String(dateObj.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function changeCalendarMonth(delta) {
    calendarViewDate.setMonth(calendarViewDate.getMonth() + delta);
    renderCalendar();
}

function renderCalendar() {
    const grid = document.getElementById('calendarDaysGrid');
    const label = document.getElementById('calendarMonthLabel');
    const prevBtn = document.getElementById('calPrevBtn');
    if (!grid || !label) return;

    const year = calendarViewDate.getFullYear();
    const month = calendarViewDate.getMonth();

    label.textContent = calendarViewDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

    // Disable "previous" navigation once we're on the min-date's month
    if (prevBtn) {
        const isMinMonth = (year === calMinDate.getFullYear() && month === calMinDate.getMonth());
        prevBtn.disabled = isMinMonth;
    }

    const firstDayOfMonth = new Date(year, month, 1);
    const startOffset = firstDayOfMonth.getDay(); // 0 = Sunday
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let html = '';

    // Empty leading cells to align first day of the month
    for (let i = 0; i < startOffset; i++) {
        html += `<div></div>`;
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const cellDate = new Date(year, month, day);
        cellDate.setHours(0, 0, 0, 0);
        const dateStr = toLocalDateString(cellDate);

        const isPast = cellDate < calMinDate;
        const isSelected = (selectedDate === dateStr);
        const isToday = (cellDate.getTime() === today.getTime());

        const classes = ['cal-day-btn'];
        if (isSelected) classes.push('cal-day-active');
        if (isToday) classes.push('cal-day-today');

        if (isPast) {
            html += `<button type="button" disabled class="${classes.join(' ')}">${day}</button>`;
        } else {
            html += `<button type="button" class="${classes.join(' ')}" onclick="selectCalendarDate('${dateStr}')">${day}</button>`;
        }
    }

    grid.innerHTML = html;
}

function selectCalendarDate(dateStr) {
    // Re-render to move the highlighted state, then run shared date-change logic
    handleDateChange(dateStr);
    renderCalendar();

    const readout = document.getElementById('calendarSelectedReadout');
    if (readout) {
        const dateObj = new Date(dateStr + 'T00:00:00');
        readout.textContent = dateObj.toLocaleDateString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
        });
    }
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

    fetch(`../../api/availability.php?date=${dateString}`)
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
    fetch('../../api/booking-create.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
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
</script>

<?php
// 4. Close the buffer and save everything captured so far into $pageContent
$pageContent = ob_get_clean();

// 5. Require the layout shell, which will handle wrapping $pageContent
require_once __DIR__ . '/../components/layout/main-layout.php';
?>