<?php
/**
 * admin/pages/bookings.php
 * Table view of all clinic bookings for admin/staff to manage.
 */

$activePage = 'bookings';
$pageTitle  = 'Bookings';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bookings - DentalCare Pro Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="../../assets/js/theme-config.js"></script>
<link rel="stylesheet" href="../../assets/css/theme-base.css">
<link rel="stylesheet" href="../../assets/css/responsive.css">
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20; }
    @keyframes shimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
    .animate-shimmer {
        background: linear-gradient(90deg, rgba(0,0,0,0.03) 25%, rgba(0,0,0,0.06) 37%, rgba(0,0,0,0.03) 63%);
        background-size: 400px 100%;
        animation: shimmer 1.4s ease-in-out infinite;
    }
    .filter-input {
        padding: 9px 12px; border-radius: 0.5rem; background-color: #ffffff;
        border: 1.5px solid rgba(114, 119, 131, 0.3); color: #0b1c30; font-size: 0.8125rem;
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .filter-input:focus { border-color: #00478d; box-shadow: 0 0 0 4px rgba(0, 71, 141, 0.08); }

    .row-action-btn {
        display: inline-flex; align-items: center; gap: 4px; padding: 6px 10px;
        border-radius: 0.5rem; font-size: 11.5px; font-weight: 700; white-space: nowrap;
        transition: all 0.15s ease; border: 1.5px solid transparent;
    }
    .row-action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Modal */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(11, 28, 48, 0.45); z-index: 100;
        display: flex; align-items: center; justify-content: center; padding: 1rem;
        opacity: 0; pointer-events: none; transition: opacity 0.2s ease;
    }
    .modal-overlay.open { opacity: 1; pointer-events: auto; }
    .modal-card {
        transform: translateY(12px) scale(0.98); transition: transform 0.2s cubic-bezier(0.16,1,0.3,1);
    }
    .modal-overlay.open .modal-card { transform: translateY(0) scale(1); }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen">

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-5">

            <div class="flex items-center justify-between">
                <h1 class="text-xl sm:text-2xl font-bold text-primary">Bookings</h1>
            </div>

            <!-- Filter Bar -->
            <section class="bg-surface-container-lowest rounded-2xl border border-surface-container p-4 sm:p-5 shadow-[0_4px_16px_rgba(0,71,141,0.06)]">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="lg:col-span-1">
                        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1" for="filterDateFrom">From</label>
                        <input type="date" id="filterDateFrom" class="filter-input w-full">
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1" for="filterDateTo">To</label>
                        <input type="date" id="filterDateTo" class="filter-input w-full">
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1" for="filterStatus">Status</label>
                        <select id="filterStatus" class="filter-input w-full">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1" for="filterSearch">Search</label>
                        <div class="relative">
                            <input type="text" id="filterSearch" placeholder="Patient name or reference code…" class="filter-input w-full pr-9">
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 text-[18px]">search</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-3">
                    <button id="clearFiltersBtn" class="text-xs font-bold text-on-surface-variant hover:text-primary px-3 py-2 rounded-lg transition-colors">Clear filters</button>
                    <button id="applyFiltersBtn" class="bg-primary text-on-primary text-xs font-bold px-4 py-2 rounded-lg hover:bg-on-primary-fixed-variant transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">filter_alt</span> Apply
                    </button>
                </div>
            </section>

            <!-- Table Card -->
            <section class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.06)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant/20 bg-surface-container-low/50">
                                <th class="text-left px-4 py-3 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Reference</th>
                                <th class="text-left px-4 py-3 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Patient</th>
                                <th class="text-left px-4 py-3 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Service</th>
                                <th class="text-left px-4 py-3 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Date / Time</th>
                                <th class="text-left px-4 py-3 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Status</th>
                                <th class="text-right px-4 py-3 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bookingsTableBody" class="divide-y divide-outline-variant/10">
                            <!-- Skeleton rows (default) -->
                            <tr id="skeletonRow1"><td colspan="6" class="px-4 py-4"><div class="h-5 rounded bg-surface-container-low animate-shimmer"></div></td></tr>
                            <tr id="skeletonRow2"><td colspan="6" class="px-4 py-4"><div class="h-5 rounded bg-surface-container-low animate-shimmer"></div></td></tr>
                            <tr id="skeletonRow3"><td colspan="6" class="px-4 py-4"><div class="h-5 rounded bg-surface-container-low animate-shimmer"></div></td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div id="bookingsEmptyState" class="hidden flex-col items-center justify-center py-16 px-6 text-center">
                    <span class="material-symbols-outlined text-4xl text-outline-variant mb-2" aria-hidden="true">event_busy</span>
                    <p class="text-sm text-on-surface-variant font-medium">No bookings match these filters.</p>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-t border-outline-variant/20">
                    <p id="paginationSummary" class="text-xs text-on-surface-variant">Showing 0 of 0</p>
                    <div class="flex items-center gap-1.5">
                        <button id="prevPageBtn" class="row-action-btn bg-surface-container-low text-on-surface-variant hover:bg-surface-container">
                            <span class="material-symbols-outlined text-[16px]">chevron_left</span> Prev
                        </button>
                        <span id="pageIndicator" class="text-xs font-bold text-on-surface px-2">Page 1</span>
                        <button id="nextPageBtn" class="row-action-btn bg-surface-container-low text-on-surface-variant hover:bg-surface-container">
                            Next <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div id="cancelModal" class="modal-overlay" aria-hidden="true">
    <div class="modal-card bg-surface-container-lowest rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-on-surface">Cancel Booking</h3>
            <button onclick="closeModal('cancelModal')" class="text-on-surface-variant/50 hover:text-on-surface-variant">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <p class="text-sm text-on-surface-variant mb-4">
            Cancelling <span id="cancelModalReference" class="font-bold text-on-surface">—</span> for <span id="cancelModalPatient" class="font-bold text-on-surface">—</span>.
        </p>
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="cancelReason">Reason</label>
                <select id="cancelReason" class="filter-input w-full">
                    <option value="user_requested">User requested</option>
                    <option value="late_cancellation">Late cancellation</option>
                    <option value="no_show">No show</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="cancelNotes">Notes (optional)</label>
                <textarea id="cancelNotes" rows="3" class="filter-input w-full resize-none" placeholder="Additional context for this cancellation…"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <button onclick="closeModal('cancelModal')" class="text-sm font-bold text-on-surface-variant hover:text-on-surface px-4 py-2 rounded-lg transition-colors">Back</button>
            <button id="confirmCancelBtn" onclick="submitCancellation()" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">cancel</span> Confirm Cancellation
            </button>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="detailsModal" class="modal-overlay" aria-hidden="true">
    <div class="modal-card bg-surface-container-lowest rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-on-surface">Booking Details</h3>
            <button onclick="closeModal('detailsModal')" class="text-on-surface-variant/50 hover:text-on-surface-variant">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div id="detailsModalBody" class="space-y-5">
            <!-- Populated by JS -->
        </div>
    </div>
</div>

<!-- Toast container (fallback if layout shell doesn't already expose showGlobalToast) -->
<script>
function showLocalToast(type, msg) {
    if (typeof showGlobalToast === 'function') {
        showGlobalToast(type, msg);
        return;
    }
    const toast = document.createElement('div');
    toast.className = `fixed bottom-5 right-5 px-5 py-3 rounded-xl text-white font-bold text-sm shadow-xl z-[200] flex items-center gap-2 transition-all transform translate-y-4 opacity-0 ${
        type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'
    }`;
    toast.innerHTML = `<span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : 'warning'}</span> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-y-4', 'opacity-0'), 50);
    setTimeout(() => {
        toast.classList.add('translate-y-4', 'opacity-0');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

/**
 * Shared status badge component — kept consistent with dashboard.php.
 * Reuse getStatusBadge(status) on any admin page that renders these statuses.
 */
function getStatusBadge(status) {
    const map = {
        pending:       { label: 'Pending',   cls: 'bg-amber-100 text-amber-800' },
        confirmed:     { label: 'Confirmed', cls: 'bg-emerald-100 text-emerald-800' },
        cancelled:     { label: 'Cancelled', cls: 'bg-red-100 text-red-800' },
    };
    const cfg = map[status] ?? { label: status ?? 'Unknown', cls: 'bg-gray-100 text-gray-700' };
    return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold capitalize ${cfg.cls}">${cfg.label}</span>`;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

// ── State ────────────────────────────────────────────────────────────────
const state = {
    page: 1,
    perPage: 20,
    totalRows: 0,
    bookings: [],      // current page of rows, keyed by id for in-place refresh
    pendingCancelId: null,
};

// ── Fetch & Render ──────────────────────────────────────────────────────
async function loadBookings() {
    const tbody = document.getElementById('bookingsTableBody');
    const emptyState = document.getElementById('bookingsEmptyState');

    // Show skeleton rows while fetching
    tbody.innerHTML = `
        <tr><td colspan="6" class="px-4 py-4"><div class="h-5 rounded bg-surface-container-low animate-shimmer"></div></td></tr>
        <tr><td colspan="6" class="px-4 py-4"><div class="h-5 rounded bg-surface-container-low animate-shimmer"></div></td></tr>
        <tr><td colspan="6" class="px-4 py-4"><div class="h-5 rounded bg-surface-container-low animate-shimmer"></div></td></tr>`;
    emptyState.classList.add('hidden');
    emptyState.classList.remove('flex');

    const params = new URLSearchParams({
        page: state.page,
        per_page: state.perPage,
        status: document.getElementById('filterStatus').value,
        date_from: document.getElementById('filterDateFrom').value,
        date_to: document.getElementById('filterDateTo').value,
        search: document.getElementById('filterSearch').value.trim(),
    });

    try {
        const res = await fetch(`../backend/bookings-list.php?${params.toString()}`);
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to load bookings.');

        state.bookings = data.bookings ?? [];
        state.totalRows = data.total ?? state.bookings.length;

        renderTable();
        renderPagination();

    } catch (err) {
        console.error('Error loading bookings:', err);
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        emptyState.classList.add('flex');
        document.querySelector('#bookingsEmptyState p').textContent = 'Unable to load bookings. Please try again.';
        showLocalToast('error', 'Could not load bookings.');
    }
}

function renderTable() {
    const tbody = document.getElementById('bookingsTableBody');
    const emptyState = document.getElementById('bookingsEmptyState');

    if (state.bookings.length === 0) {
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        emptyState.classList.add('flex');
        return;
    }

    emptyState.classList.add('hidden');
    emptyState.classList.remove('flex');
    tbody.innerHTML = state.bookings.map(rowHtml).join('');
}

function rowHtml(b) {
    return `
        <tr id="bookingRow-${b.id}" class="hover:bg-surface-container-low/60 transition-colors">
            <td class="px-4 py-3 font-mono text-xs text-on-surface-variant">${escapeHtml(b.reference_code)}</td>
            <td class="px-4 py-3 font-semibold text-on-surface">${escapeHtml(b.first_name)} ${escapeHtml(b.last_name)}</td>
            <td class="px-4 py-3 text-on-surface-variant">${escapeHtml(b.service_key)}</td>
            <td class="px-4 py-3 text-on-surface-variant whitespace-nowrap">${escapeHtml(b.appointment_date)}<br><span class="text-xs">${escapeHtml(b.appointment_time)}</span></td>
            <td class="px-4 py-3" id="statusCell-${b.id}">${getStatusBadge(b.status)}</td>
            <td class="px-4 py-3">
                <div class="flex justify-end items-center gap-1.5 flex-wrap">
                    <button class="row-action-btn bg-emerald-50 text-emerald-700 hover:bg-emerald-100 ${b.status !== 'pending' ? 'hidden' : ''}"
                            onclick="confirmBooking(${b.id})" data-role="confirm-btn-${b.id}">
                        <span class="material-symbols-outlined text-[14px]">check</span> Confirm
                    </button>
                    <button class="row-action-btn bg-red-50 text-red-700 hover:bg-red-100 ${b.status === 'cancelled' ? 'hidden' : ''}"
                            onclick="openCancelModal(${b.id})" data-role="cancel-btn-${b.id}">
                        <span class="material-symbols-outlined text-[14px]">cancel</span> Cancel
                    </button>
                    <button class="row-action-btn bg-surface-container-low text-on-surface-variant hover:bg-surface-container"
                            onclick="openDetailsModal(${b.id})">
                        <span class="material-symbols-outlined text-[14px]">visibility</span> View
                    </button>
                </div>
            </td>
        </tr>`;
}

function renderPagination() {
    const totalPages = Math.max(1, Math.ceil(state.totalRows / state.perPage));
    const start = state.totalRows === 0 ? 0 : (state.page - 1) * state.perPage + 1;
    const end = Math.min(state.page * state.perPage, state.totalRows);

    document.getElementById('paginationSummary').textContent = `Showing ${start}–${end} of ${state.totalRows}`;
    document.getElementById('pageIndicator').textContent = `Page ${state.page} of ${totalPages}`;
    document.getElementById('prevPageBtn').disabled = state.page <= 1;
    document.getElementById('nextPageBtn').disabled = state.page >= totalPages;
}

// ── Filter interactions ─────────────────────────────────────────────────
document.getElementById('applyFiltersBtn').addEventListener('click', () => {
    state.page = 1;
    loadBookings();
});
document.getElementById('clearFiltersBtn').addEventListener('click', () => {
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterStatus').value = 'all';
    document.getElementById('filterSearch').value = '';
    state.page = 1;
    loadBookings();
});
document.getElementById('filterSearch').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        state.page = 1;
        loadBookings();
    }
});
document.getElementById('prevPageBtn').addEventListener('click', () => {
    if (state.page > 1) { state.page--; loadBookings(); }
});
document.getElementById('nextPageBtn').addEventListener('click', () => {
    const totalPages = Math.max(1, Math.ceil(state.totalRows / state.perPage));
    if (state.page < totalPages) { state.page++; loadBookings(); }
});

// ── Row actions ──────────────────────────────────────────────────────────
async function confirmBooking(id) {
    const btn = document.querySelector(`[data-role="confirm-btn-${id}"]`);
    if (btn) btn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('booking_id', id);
        formData.append('action', 'confirm');

        const res = await fetch('../backend/bookings-update-status.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to confirm booking.');

        updateRowStatus(id, 'confirmed');
        showLocalToast('success', `Booking ${data.reference_code ?? ''} confirmed.`);

    } catch (err) {
        console.error(err);
        showLocalToast('error', err.message || 'Could not confirm booking.');
        if (btn) btn.disabled = false;
    }
}

function openCancelModal(id) {
    const booking = state.bookings.find(b => b.id === id);
    if (!booking) return;

    state.pendingCancelId = id;
    document.getElementById('cancelModalReference').textContent = booking.reference_code;
    document.getElementById('cancelModalPatient').textContent = `${booking.first_name} ${booking.last_name}`;
    document.getElementById('cancelReason').value = 'user_requested';
    document.getElementById('cancelNotes').value = '';
    openModal('cancelModal');
}

async function submitCancellation() {
    const id = state.pendingCancelId;
    if (!id) return;

    const reason = document.getElementById('cancelReason').value;
    const notes = document.getElementById('cancelNotes').value.trim();
    const confirmBtn = document.getElementById('confirmCancelBtn');
    confirmBtn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('booking_id', id);
        formData.append('action', 'cancel');
        formData.append('reason', reason);
        formData.append('notes', notes);

        const res = await fetch('../backend/bookings-update-status.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to cancel booking.');

        updateRowStatus(id, 'cancelled');
        showLocalToast('success', `Booking ${data.reference_code ?? ''} cancelled.`);
        closeModal('cancelModal');

    } catch (err) {
        console.error(err);
        showLocalToast('error', err.message || 'Could not cancel booking.');
    } finally {
        confirmBtn.disabled = false;
    }
}

function updateRowStatus(id, newStatus) {
    const booking = state.bookings.find(b => b.id === id);
    if (booking) booking.status = newStatus;

    const statusCell = document.getElementById(`statusCell-${id}`);
    if (statusCell) statusCell.innerHTML = getStatusBadge(newStatus);

    const confirmBtn = document.querySelector(`[data-role="confirm-btn-${id}"]`);
    const cancelBtn = document.querySelector(`[data-role="cancel-btn-${id}"]`);
    if (confirmBtn) confirmBtn.classList.toggle('hidden', newStatus !== 'pending');
    if (cancelBtn) cancelBtn.classList.toggle('hidden', newStatus === 'cancelled');
}

// ── Details modal ────────────────────────────────────────────────────────
async function openDetailsModal(id) {
    const body = document.getElementById('detailsModalBody');
    body.innerHTML = `<div class="h-40 rounded-xl bg-surface-container-low animate-shimmer"></div>`;
    openModal('detailsModal');

    const booking = state.bookings.find(b => b.id === id);
    if (!booking) return;

    // Basic info renders immediately from the row data already in hand;
    // richer detail (patient history, full contact info) comes from the
    // list endpoint's per-booking payload if provided, else a dedicated
    // detail fetch could be added here (e.g. bookings-list.php?id=).
    const history = booking.patient_history ?? [];

    body.innerHTML = `
        <div>
            <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Reference</p>
            <p class="text-sm font-mono text-on-surface">${escapeHtml(booking.reference_code)}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Patient</p>
                <p class="text-sm text-on-surface">${escapeHtml(booking.first_name)} ${escapeHtml(booking.last_name)}</p>
            </div>
            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Status</p>
                ${getStatusBadge(booking.status)}
            </div>
            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Email</p>
                <p class="text-sm text-on-surface break-all">${escapeHtml(booking.email)}</p>
            </div>
            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Phone</p>
                <p class="text-sm text-on-surface">${escapeHtml(booking.phone)}</p>
            </div>
            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Service</p>
                <p class="text-sm text-on-surface">${escapeHtml(booking.service_key)}</p>
            </div>
            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Dentist</p>
                <p class="text-sm text-on-surface">${escapeHtml(booking.dentist_name)}</p>
            </div>
            <div class="col-span-2">
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Date / Time</p>
                <p class="text-sm text-on-surface">${escapeHtml(booking.appointment_date)} at ${escapeHtml(booking.appointment_time)}</p>
            </div>
        </div>
        <div>
            <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Patient Notes</p>
            <p class="text-sm text-on-surface-variant bg-surface-container-low rounded-lg p-3">${booking.notes ? escapeHtml(booking.notes) : '<span class="italic text-on-surface-variant/60">No notes provided.</span>'}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-2">Booking History</p>
            ${history.length === 0
                ? '<p class="text-sm text-on-surface-variant/70 italic">No other bookings on record for this patient.</p>'
                : `<ul class="space-y-2">${history.map(h => `
                    <li class="flex items-center justify-between gap-3 bg-surface-container-low rounded-lg px-3 py-2">
                        <span class="text-xs text-on-surface">${escapeHtml(h.appointment_date)} &middot; ${escapeHtml(h.service_key)}</span>
                        ${getStatusBadge(h.status)}
                    </li>`).join('')}</ul>`
            }
        </div>
    `;
}

// ── Modal helpers ─────────────────────────────────────────────────────────
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.add('open');
    el.setAttribute('aria-hidden', 'false');
}
function closeModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('open');
    el.setAttribute('aria-hidden', 'true');
}
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal(overlay.id);
    });
});

document.addEventListener('DOMContentLoaded', loadBookings);
</script>

</body>
</html>