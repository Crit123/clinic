<?php
/**
 * appointments.php
 * Dynamic Patient Appointments Directory for DentalCare Pro.
 * Fetches bookings asynchronously from the backend API for the logged-in user.
 */

require_once __DIR__ . '/../components/design-config.php';

// Set layout shell variables
$activePage = 'appointments';
$pageTitle  = 'My Appointments';

ob_start();
?>

<!-- Custom animations and styles for scrollbar suppression -->
<style>
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    .ease-portal { transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
</style>

<!-- HEADER SECTION -->
<div class="fade-in flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1.5">
            <span class="material-symbols-outlined text-[#1652a0] text-sm">verified</span>
            <span class="font-label-sm text-xs font-semibold text-slate-500 uppercase tracking-widest"><?php echo SITE_NAME; ?> Patient Portal</span>
        </div>
        <h2 class="font-headline-lg text-2xl md:text-3xl font-extrabold text-slate-900">Your Appointments</h2>
        <p class="font-body-md text-slate-600 mt-1">Manage and track your upcoming treatments and historical visits.</p>
    </div>
    <div>
        <a href="book-appointment.php"
           class="inline-flex items-center justify-center bg-[#1652a0] hover:bg-[#0f3a70] text-white font-bold text-sm py-3 px-6 rounded-xl transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1652a0] focus:ring-offset-2">
            <span class="material-symbols-outlined mr-2" aria-hidden="true">add_circle</span>
            Book New Visit
        </a>
    </div>
</div>

<!-- STATS ROW -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Upcoming Visits</span>
            <span id="stat-upcoming-count" class="block text-3xl font-extrabold text-slate-800 mt-1">0</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
            <span class="material-symbols-outlined text-2xl">event_upcoming</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Completed Care</span>
            <span id="stat-completed-count" class="block text-3xl font-extrabold text-slate-800 mt-1">0</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
            <span class="material-symbols-outlined text-2xl">check_circle</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Cancelled Slots</span>
            <span id="stat-cancelled-count" class="block text-3xl font-extrabold text-slate-800 mt-1">0</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
            <span class="material-symbols-outlined text-2xl">event_busy</span>
        </div>
    </div>
</div>

<!-- FILTER TABS -->
<div class="mt-6 bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <nav class="flex space-x-1 bg-slate-50 p-1 rounded-xl overflow-x-auto scrollbar-none" role="tablist">
        <button onclick="setFilter('upcoming')" id="tab-upcoming" class="px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 bg-[#e8f0fb] text-[#1652a0] shadow-sm">
            Upcoming
        </button>
        <button onclick="setFilter('completed')" id="tab-completed" class="px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 text-slate-500 hover:text-slate-800 hover:bg-slate-100/50">
            Completed
        </button>
        <button onclick="setFilter('cancelled')" id="tab-cancelled" class="px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 text-slate-500 hover:text-slate-800 hover:bg-slate-100/50">
            Cancelled
        </button>
    </nav>
    <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5 px-2">
        <span class="material-symbols-outlined text-sm text-[#1652a0]">info</span>
        <span>Times are shown in your local timezone.</span>
    </div>
</div>

<!-- MAIN BOOKINGS RENDER CONTAINER -->
<div id="bookings-wrapper" class="mt-6 min-h-[300px] relative">
    <!-- Skeleton Loading Screen -->
    <div id="loading-skeleton" class="space-y-4">
        <?php for ($i = 0; $i < 3; $i++): ?>
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm animate-pulse space-y-4">
                <div class="flex justify-between items-center">
                    <div class="h-4 w-32 bg-slate-200 rounded"></div>
                    <div class="h-6 w-20 bg-slate-200 rounded-full"></div>
                </div>
                <div class="space-y-2">
                    <div class="h-6 w-3/4 bg-slate-200 rounded"></div>
                    <div class="h-4 w-1/2 bg-slate-200 rounded"></div>
                </div>
                <div class="h-px bg-slate-100"></div>
                <div class="flex justify-between items-center pt-2">
                    <div class="h-8 w-24 bg-slate-200 rounded"></div>
                    <div class="h-8 w-20 bg-slate-200 rounded"></div>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <!-- Empty State -->
    <div id="empty-state" class="hidden bg-white border border-slate-100 rounded-2xl p-12 text-center shadow-sm flex flex-col items-center justify-center max-w-2xl mx-auto">
        <div class="w-16 h-16 rounded-full bg-blue-50 text-[#1652a0] flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-3xl">calendar_month</span>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">No Appointments Yet</h3>
        <p class="text-slate-500 text-sm mb-6 max-w-md">Book your first professional dental treatment to experience premium oral care customized for your dental goals.</p>
        <a href="book-appointment.php" class="inline-flex items-center justify-center bg-[#1652a0] hover:bg-[#0f3a70] text-white font-bold text-sm py-3 px-6 rounded-xl transition-all shadow-sm">
            Book Appointment
        </a>
    </div>

    <!-- Grid Layout of Bookings -->
    <div id="bookings-grid" class="hidden grid-cols-1 gap-4">
        <!-- Injected via JavaScript -->
    </div>
</div>

<!-- ========================================== -->
<!-- ACTION CONFIRMATION MODALS                 -->
<!-- ========================================== -->

<!-- Centered Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl border border-slate-100 overflow-hidden transform transition-all scale-100 duration-200 flex flex-col">
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Appointment Metadata</span>
                <h3 class="text-lg font-bold text-slate-800 mt-0.5">Booking Details</h3>
            </div>
            <button onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors" aria-label="Close dialog">
                <span class="material-symbols-outlined text-2xl block">close</span>
            </button>
        </div>
        <!-- Body -->
        <div class="p-6 space-y-4 text-sm text-slate-600 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Reference Code</p>
                    <p id="modalRefCode" class="font-mono text-base font-bold text-[#1652a0] mt-0.5"></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status</p>
                    <div id="modalStatusContainer" class="mt-1"></div>
                </div>
            </div>

            <div class="space-y-3.5 pt-2">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">medical_information</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Selected Treatment</p>
                        <p id="modalService" class="font-semibold text-slate-800 text-base"></p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">person</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Assigned Dentist</p>
                        <p id="modalDentist" class="font-medium text-slate-800"></p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">calendar_month</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Date & Scheduled Time</p>
                        <p id="modalDateTime" class="font-medium text-slate-800"></p>
                    </div>
                </div>

                <div class="h-px bg-slate-100 my-4"></div>

                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">account_circle</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Patient Registered Name</p>
                        <p id="modalPatientName" class="font-medium text-slate-800"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-slate-400 mt-0.5">mail</span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-400 uppercase">Email Address</p>
                            <p id="modalPatientEmail" class="font-medium text-slate-800 truncate"></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-slate-400 mt-0.5">call</span>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Contact Number</p>
                            <p id="modalPatientPhone" class="font-medium text-slate-800"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button onclick="closeDetailModal()" class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-sm rounded-xl transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-300">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="hidden fixed inset-0 z-[110] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl border border-slate-100 overflow-hidden transform transition-all scale-100 duration-200">
        <div class="p-6">
            <div class="flex items-center gap-3.5 text-rose-600 mb-4">
                <div class="w-10 h-10 rounded-full bg-rose-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Cancel Appointment?</h3>
            </div>
            <p class="text-slate-600 text-sm mb-4">
                Are you sure you want to cancel your scheduled session for <strong id="cancelServiceText" class="text-slate-800"></strong>? This action cannot be undone, and you will have to schedule a new time slot.
            </p>
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs font-mono text-slate-500 mb-4 flex items-center justify-between">
                <span>Reference:</span>
                <span id="cancelRefCode" class="font-bold text-[#1652a0]"></span>
            </div>
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
            <button onclick="closeCancelModal()" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-sm rounded-xl transition-all">
                Keep Appointment
            </button>
            <button id="confirmCancelBtn" onclick="executeCancel()" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">event_busy</span> Confirm Cancellation
            </button>
        </div>
    </div>
</div>

<!-- INTERACTION AND DATA MANAGEMENT JAVASCRIPT -->
<script>
let bookingsStore = [];
let activeFilter = 'upcoming'; // 'upcoming', 'completed', 'cancelled'
let activeCancelBooking = null;

document.addEventListener('DOMContentLoaded', async () => {
    try {
        await initFetchWorkflow();
    } catch (err) {
        console.error("Initiation workflow failed:", err);
        showLocalToast('error', 'Unable to authenticate or fetch appointments.');
        toggleDisplayStates(false, true); // Show empty/error state fallback
    }
});

/**
 * Orchestrates session validation and fetching corresponding appointments.
 */
async function initFetchWorkflow() {
    toggleDisplayStates(true, false); // Keep loading skeleton active

    // 1. Get Logged-In User Information
    const user = await fetchJSON('api/get-session-user.php');
    if (!user || !user.email) {
        showLocalToast('error', 'Authentication failed. Please re-login.');
        window.location.href = 'login.php';
        return;
    }

    // 2. Fetch Bookings associated with User Email
    const response = await fetchJSON(`api/user-bookings.php?email=${encodeURIComponent(user.email)}`);
    bookingsStore = Array.isArray(response) ? response : [];

    // 3. Update Visual Metric Overviews
    recalculateDashboardCounters();

    // 4. Draw filtered lists
    renderFilteredGrid();
}

/**
 * Calculates local counters for metrics row based on relative dates.
 */
function recalculateDashboardCounters() {
    const now = new Date();
    let upcoming = 0;
    let completed = 0;
    let cancelled = 0;

    bookingsStore.forEach(b => {
        const status = b.status ? b.status.toLowerCase() : 'pending';
        const isCancelled = status === 'cancelled';
        const dateObj = parseDateTime(b.date, b.time);
        const isFuture = dateObj > now;

        if (isCancelled) {
            cancelled++;
        } else if (status === 'confirmed' && !isFuture) {
            completed++;
        } else {
            // Either pending or future-confirmed counts as upcoming
            upcoming++;
        }
    });

    document.getElementById('stat-upcoming-count').innerText = upcoming;
    document.getElementById('stat-completed-count').innerText = completed;
    document.getElementById('stat-cancelled-count').innerText = cancelled;
}

/**
 * Filters the retrieved store and updates the UI grid.
 */
function renderFilteredGrid() {
    const grid = document.getElementById('bookings-grid');
    const now = new Date();

    // Clear previous elements
    grid.innerHTML = '';

    const filtered = bookingsStore.filter(b => {
        const status = b.status ? b.status.toLowerCase() : 'pending';
        const isCancelled = status === 'cancelled';
        const dateObj = parseDateTime(b.date, b.time);
        const isFuture = dateObj > now;

        if (activeFilter === 'cancelled') {
            return isCancelled;
        } else if (activeFilter === 'completed') {
            return !isCancelled && status === 'confirmed' && !isFuture;
        } else {
            // 'upcoming': confirmed or pending in the future or active pending
            return !isCancelled && (isFuture || status === 'pending');
        }
    });

    if (filtered.length === 0) {
        toggleDisplayStates(false, true); // Trigger empty state
        return;
    }

    toggleDisplayStates(false, false); // Display content grid

    filtered.forEach(booking => {
        const card = createBookingCardElement(booking);
        grid.appendChild(card);
    });
}

/**
 * Programmatically builds the individual Booking Card item.
 */
function createBookingCardElement(booking) {
    const card = document.createElement('div');
    card.className = "bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_4px_16px_rgba(0,71,141,0.02)] hover:shadow-[0_8px_24px_rgba(0,71,141,0.06)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between space-y-4";
    card.id = `card-${booking.id}`;

    // Compute badges and actions
    const status = booking.status ? booking.status.toLowerCase() : 'pending';
    const isCancelled = status === 'cancelled';
    const dateObj = parseDateTime(booking.date, booking.time);
    const isFuture = dateObj > new Date();

    let statusBadgeHTML = '';
    if (isCancelled) {
        statusBadgeHTML = `<span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 bg-rose-50 text-rose-700 rounded-full border border-rose-100">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Cancelled
        </span>`;
    } else if (status === 'confirmed') {
        statusBadgeHTML = `<span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-100">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Confirmed
        </span>`;
    } else {
        statusBadgeHTML = `<span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full border border-amber-100">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
        </span>`;
    }

    // Determine cancellation feasibility
    const showCancelButton = (status === 'confirmed' || status === 'pending') && isFuture && !isCancelled;

    card.innerHTML = `
        <div class="flex justify-between items-start gap-4">
            <div>
                <span class="font-mono text-xs font-bold text-[#1652a0] uppercase tracking-wide block mb-1">Ref: #${booking.reference_code}</span>
                <h4 class="text-lg font-bold text-slate-800 leading-tight">${booking.service || 'Dental Consultation'}</h4>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">medical_services</span> Dr. ${booking.dentist || 'Maria Santos'}
                </p>
            </div>
            ${statusBadgeHTML}
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-600 font-medium bg-slate-50 p-3 rounded-xl border border-slate-100">
            <span class="flex items-center text-slate-700">
                <span class="material-symbols-outlined text-base mr-1.5 text-slate-400">calendar_today</span>
                ${formatDisplayDate(booking.date)}
            </span>
            <span class="flex items-center text-slate-700">
                <span class="material-symbols-outlined text-base mr-1.5 text-slate-400">schedule</span>
                ${booking.time}
            </span>
        </div>

        <div class="h-px bg-slate-100"></div>

        <div class="flex items-center justify-between pt-1">
            <button onclick="openDetailModal(${booking.id})" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1 focus:ring-2 focus:ring-[#1652a0]/20">
                <span class="material-symbols-outlined text-sm">visibility</span> View Details
            </button>
            ${showCancelButton ? `
                <button onclick="requestCancel(${booking.id})" class="px-4 py-2 hover:bg-rose-50 text-rose-600 font-bold text-xs rounded-xl transition-all flex items-center gap-1 focus:ring-2 focus:ring-rose-200">
                    <span class="material-symbols-outlined text-sm">event_busy</span> Cancel
                </button>
            ` : ''}
        </div>
    `;

    return card;
}

/**
 * Handles Tab navigation visual resets.
 */
function setFilter(filterType) {
    activeFilter = filterType;
    
    const tabs = ['upcoming', 'completed', 'cancelled'];
    tabs.forEach(tab => {
        const tabEl = document.getElementById(`tab-${tab}`);
        if (tab === filterType) {
            tabEl.className = "px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 bg-[#e8f0fb] text-[#1652a0] shadow-sm";
        } else {
            tabEl.className = "px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 text-slate-500 hover:text-slate-800 hover:bg-slate-100/50";
        }
    });

    renderFilteredGrid();
}

/**
 * Modal detail trigger.
 */
function openDetailModal(bookingId) {
    const booking = bookingsStore.find(b => b.id === bookingId);
    if (!booking) return;

    document.getElementById('modalRefCode').innerText = `#${booking.reference_code}`;
    document.getElementById('modalService').innerText = booking.service || 'Routine General Consultation';
    document.getElementById('modalDentist').innerText = `Dr. ${booking.dentist || 'Maria Santos'}`;
    document.getElementById('modalDateTime').innerText = `${formatDisplayDate(booking.date)} at ${booking.time}`;
    document.getElementById('modalPatientName').innerText = booking.patient_name || 'Unspecified Name';
    document.getElementById('modalPatientEmail').innerText = booking.email || 'N/A';
    document.getElementById('modalPatientPhone').innerText = booking.phone || 'N/A';

    // Badge styling for Modal
    const status = booking.status ? booking.status.toLowerCase() : 'pending';
    const statusContainer = document.getElementById('modalStatusContainer');
    if (status === 'cancelled') {
        statusContainer.innerHTML = `<span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase px-2.5 py-0.5 bg-rose-50 text-rose-700 rounded border border-rose-100">Cancelled</span>`;
    } else if (status === 'confirmed') {
        statusContainer.innerHTML = `<span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase px-2.5 py-0.5 bg-emerald-50 text-emerald-700 rounded border border-emerald-100">Confirmed</span>`;
    } else {
        statusContainer.innerHTML = `<span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase px-2.5 py-0.5 bg-amber-50 text-amber-700 rounded border border-amber-100">Pending Activation</span>`;
    }

    document.getElementById('detailModal').classList.remove('hidden');
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

/**
 * Cancellation modal triggers.
 */
function requestCancel(bookingId) {
    const booking = bookingsStore.find(b => b.id === bookingId);
    if (!booking) return;

    activeCancelBooking = booking;
    document.getElementById('cancelServiceText').innerText = booking.service || 'Dental Consultation';
    document.getElementById('cancelRefCode').innerText = `#${booking.reference_code}`;
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    activeCancelBooking = null;
}

/**
 * Dispatches cancellations back to the persistence layer.
 */
async function executeCancel() {
    if (!activeCancelBooking) return;

    const cancelBtn = document.getElementById('confirmCancelBtn');
    cancelBtn.disabled = true;
    cancelBtn.innerText = "Processing...";

    try {
        const payload = {
            booking_id: activeCancelBooking.id,
            reference_code: activeCancelBooking.reference_code
        };

        const result = await fetchJSON('api/cancel-booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (result && result.success) {
            showLocalToast('success', 'Your appointment cancellation has been successfully submitted.');
            
            // Update local memory without full-page reloads
            const match = bookingsStore.find(b => b.id === activeCancelBooking.id);
            if (match) {
                match.status = 'cancelled';
            }

            closeCancelModal();
            recalculateDashboardCounters();
            renderFilteredGrid();
        } else {
            throw new Error(result?.message || 'Server rejected cancellation request.');
        }
    } catch (err) {
        console.error("Cancellation error:", err);
        showLocalToast('error', err.message || 'An error occurred. Please try again.');
    } finally {
        cancelBtn.disabled = false;
        cancelBtn.innerHTML = `<span class="material-symbols-outlined text-sm">event_busy</span> Confirm Cancellation`;
    }
}

/**
 * Coordinate screen transitions.
 */
function toggleDisplayStates(loading, showEmpty) {
    const skeleton = document.getElementById('loading-skeleton');
    const emptyState = document.getElementById('empty-state');
    const grid = document.getElementById('bookings-grid');

    if (loading) {
        skeleton.classList.remove('hidden');
        emptyState.classList.add('hidden');
        grid.classList.add('hidden');
    } else if (showEmpty) {
        skeleton.classList.add('hidden');
        emptyState.classList.remove('hidden');
        grid.classList.add('hidden');
    } else {
        skeleton.classList.add('hidden');
        emptyState.classList.add('hidden');
        grid.classList.remove('hidden');
        grid.classList.add('grid');
    }
}

/**
 * Utility: Standard JSON Fetch wrapper.
 */
async function fetchJSON(url, options = {}) {
    try {
        const res = await fetch(url, options);
        if (!res.ok) throw new Error(`HTTP Error Status: ${res.status}`);
        return await res.json();
    } catch (e) {
        console.error(`Request Failed [${url}]:`, e);
        throw e;
    }
}

/**
 * Utility: Time parser to correctly handle 12-hour/24-hour scenarios.
 */
function parseDateTime(dateStr, timeStr) {
    if (!timeStr) return new Date(`${dateStr}T00:00:00`);
    
    let normalizedTime = timeStr.trim();
    const isTwelveHour = /am|pm/i.test(normalizedTime);

    if (isTwelveHour) {
        const parts = normalizedTime.split(/\s+/);
        const modifier = parts[1]?.toLowerCase() || '';
        const timeParts = parts[0].split(':');
        let hours = parseInt(timeParts[0], 10);
        const minutes = timeParts[1] || '00';

        if (modifier === 'pm' && hours < 12) hours += 12;
        if (modifier === 'am' && hours === 12) hours = 0;

        const padHrs = String(hours).padStart(2, '0');
        normalizedTime = `${padHrs}:${minutes}:00`;
    } else {
        // Assume 24-hour
        if (normalizedTime.split(':').length === 2) {
            normalizedTime += ':00';
        }
    }

    return new Date(`${dateStr}T${normalizedTime}`);
}

/**
 * Utility: Pretty format dates.
 */
function formatDisplayDate(dateString) {
    try {
        const options = { year: 'numeric', month: 'short', day: '2-digit' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    } catch (e) {
        return dateString;
    }
}

/**
 * Fallback toast notification system if layout shell does not expose showGlobalToast
 */
function showLocalToast(type, msg) {
    if (typeof showGlobalToast === 'function') {
        showGlobalToast(type, msg);
        return;
    }
    
    // Fallback UI toast inside document space
    const toast = document.createElement('div');
    toast.className = `fixed bottom-5 right-5 px-5 py-3 rounded-xl text-white font-bold text-sm shadow-xl z-[200] flex items-center gap-2 transition-all transform translate-y-4 opacity-0 ${
        type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'
    }`;
    toast.innerHTML = `<span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : 'warning'}</span> ${msg}`;
    document.body.appendChild(toast);
    
    // Animate In
    setTimeout(() => {
        toast.classList.remove('translate-y-4', 'opacity-0');
    }, 50);

    // Fade Out
    setTimeout(() => {
        toast.classList.add('translate-y-4', 'opacity-0');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}
</script>

<?php
$pageContent = ob_get_clean();

// Capture everything and render layout
require_once __DIR__ . '/../components/layout/main-layout.php';
?>