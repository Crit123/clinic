<?php
/**
 * admin/pages/dashboard.php
 * Landing page for the staff/admin panel after login.
 */

$activePage = 'dashboard';
$pageTitle  = 'Admin Dashboard';
$isAdmin    = ($_SESSION['role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Dashboard - DentalCare Pro Admin</title>
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
    .fade-in { animation: fadeIn 0.4s ease both; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen">

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-8">

            <!-- SKELETON (shown while dashboard-stats.php is loading) -->
            <div id="skeletonLoaderArea" class="space-y-8" aria-hidden="true">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-28 animate-shimmer"></div>
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-28 animate-shimmer"></div>
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-28 animate-shimmer"></div>
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-28 animate-shimmer"></div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-80 animate-shimmer"></div>
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-80 animate-shimmer"></div>
                </div>
            </div>

            <!-- POPULATED DASHBOARD -->
            <div id="dashboardContent" class="hidden opacity-0 space-y-8 transition-opacity duration-300">

                <!-- Stat Cards -->
                <section class="fade-in grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6" aria-label="Dashboard statistics">
                    <a href="appointments.php?filter=today" class="block bg-surface-container-lowest p-5 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300 border border-surface-container">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 rounded-full bg-primary-fixed flex items-center justify-center text-primary" aria-hidden="true">
                                <span class="material-symbols-outlined text-xl">event_available</span>
                            </div>
                        </div>
                        <p class="text-[11px] sm:text-xs text-on-surface-variant uppercase tracking-wider font-bold mb-1">Today's Bookings</p>
                        <p id="statTodayBookings" class="text-2xl sm:text-3xl font-bold text-primary">0</p>
                    </a>

                    <a href="emergency-requests.php?filter=pending" class="block bg-surface-container-lowest p-5 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300 border border-surface-container">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 rounded-full bg-rose-100 flex items-center justify-center text-rose-700" aria-hidden="true">
                                <span class="material-symbols-outlined text-xl">emergency</span>
                            </div>
                        </div>
                        <p class="text-[11px] sm:text-xs text-on-surface-variant uppercase tracking-wider font-bold mb-1">Pending Emergencies</p>
                        <p id="statPendingEmergencies" class="text-2xl sm:text-3xl font-bold text-primary">0</p>
                    </a>

                    <?php if ($isAdmin): ?>
                    <a href="feedback.php?filter=pending" id="statPendingFeedbackCard" class="block bg-surface-container-lowest p-5 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300 border border-surface-container">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 rounded-full bg-amber-100 flex items-center justify-center text-amber-700" aria-hidden="true">
                                <span class="material-symbols-outlined text-xl">rate_review</span>
                            </div>
                        </div>
                        <p class="text-[11px] sm:text-xs text-on-surface-variant uppercase tracking-wider font-bold mb-1">Pending Feedback</p>
                        <p id="statPendingFeedback" class="text-2xl sm:text-3xl font-bold text-primary">0</p>
                    </a>
                    <?php endif; ?>

                    <a href="appointments.php?filter=week_confirmed" class="block bg-surface-container-lowest p-5 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300 border border-surface-container">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700" aria-hidden="true">
                                <span class="material-symbols-outlined text-xl">calendar_month</span>
                            </div>
                        </div>
                        <p class="text-[11px] sm:text-xs text-on-surface-variant uppercase tracking-wider font-bold mb-1">Confirmed This Week</p>
                        <p id="statWeekConfirmed" class="text-2xl sm:text-3xl font-bold text-primary">0</p>
                    </a>
                </section>

                <!-- Recent Panels -->
                <section class="fade-in grid grid-cols-1 lg:grid-cols-2 gap-6" aria-label="Recent activity">

                    <!-- Recent Bookings -->
                    <div class="bg-surface-container-lowest rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] border border-surface-container flex flex-col">
                        <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-outline-variant/20">
                            <h3 class="font-bold text-lg text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary" aria-hidden="true">event_note</span>
                                Recent Bookings
                            </h3>
                            <a href="appointments.php" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                View all <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        <ul id="recentBookingsList" class="divide-y divide-outline-variant/15 flex-1" aria-live="polite">
                            <!-- Populated by JS -->
                        </ul>
                        <div id="recentBookingsEmpty" class="hidden flex-1 flex flex-col items-center justify-center py-12 px-6 text-center">
                            <span class="material-symbols-outlined text-4xl text-outline-variant mb-2" aria-hidden="true">event_busy</span>
                            <p class="text-sm text-on-surface-variant font-medium">No bookings yet today.</p>
                        </div>
                    </div>

                    <!-- Recent Emergency Requests -->
                    <div class="bg-surface-container-lowest rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] border border-surface-container flex flex-col">
                        <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-outline-variant/20">
                            <h3 class="font-bold text-lg text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined text-rose-600" aria-hidden="true">emergency</span>
                                Recent Emergency Requests
                            </h3>
                            <a href="emergency-requests.php" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                View all <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        <ul id="recentEmergencyList" class="divide-y divide-outline-variant/15 flex-1" aria-live="polite">
                            <!-- Populated by JS -->
                        </ul>
                        <div id="recentEmergencyEmpty" class="hidden flex-1 flex flex-col items-center justify-center py-12 px-6 text-center">
                            <span class="material-symbols-outlined text-4xl text-outline-variant mb-2" aria-hidden="true">check_circle</span>
                            <p class="text-sm text-on-surface-variant font-medium">No emergency requests right now.</p>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>

<script>
/**
 * Shared status badge component.
 * Reuse `getStatusBadge(status)` on other admin pages (appointments.php,
 * emergency-requests.php, feedback.php, etc.) so status colors stay
 * consistent across the whole admin panel. Consider moving this into a
 * shared /admin/assets/js/status-badge.js include if reused site-wide.
 */
function getStatusBadge(status) {
    const map = {
        // Bookings
        pending:       { label: 'Pending',   cls: 'bg-amber-100 text-amber-800' },
        confirmed:     { label: 'Confirmed', cls: 'bg-emerald-100 text-emerald-800' },
        cancelled:     { label: 'Cancelled', cls: 'bg-red-100 text-red-800' },
        // Emergency requests
        submitted:     { label: 'Submitted',    cls: 'bg-gray-100 text-gray-700' },
        under_review:  { label: 'Under Review', cls: 'bg-amber-100 text-amber-800' },
        contacted:     { label: 'Contacted',    cls: 'bg-sky-100 text-sky-800' },
        resolved:      { label: 'Resolved',     cls: 'bg-emerald-100 text-emerald-800' },
        // Feedback
        approved:      { label: 'Approved', cls: 'bg-emerald-100 text-emerald-800' },
        rejected:      { label: 'Rejected', cls: 'bg-red-100 text-red-800' },
    };
    const cfg = map[status] ?? { label: status ?? 'Unknown', cls: 'bg-gray-100 text-gray-700' };
    return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold capitalize ${cfg.cls}">${cfg.label.replace(/_/g, ' ')}</span>`;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function renderBookingRow(b) {
    return `
        <li class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-surface-container-low transition-colors">
            <div class="min-w-0">
                <p class="text-sm font-bold text-on-surface truncate">${escapeHtml(b.first_name)} ${escapeHtml(b.last_name)}</p>
                <p class="text-xs text-on-surface-variant truncate">${escapeHtml(b.service_key)} &middot; ${escapeHtml(b.appointment_date)} ${escapeHtml(b.appointment_time)}</p>
            </div>
            ${getStatusBadge(b.status)}
        </li>`;
}

function renderEmergencyRow(r) {
    const excerpt = (r.symptom ?? '').length > 60 ? r.symptom.slice(0, 60) + '…' : (r.symptom ?? '');
    return `
        <li class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-surface-container-low transition-colors">
            <div class="min-w-0">
                <p class="text-sm font-bold text-on-surface truncate">${escapeHtml(r.first_name)} ${escapeHtml(r.last_name)}</p>
                <p class="text-xs text-on-surface-variant truncate">${escapeHtml(excerpt)}</p>
            </div>
            ${getStatusBadge(r.status)}
        </li>`;
}

async function loadDashboard() {
    const skeleton = document.getElementById('skeletonLoaderArea');
    const content  = document.getElementById('dashboardContent');

    try {
        const res = await fetch('../backend/dashboard-stats.php');
        const data = await res.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to load dashboard data.');
        }

        const stats = data.stats ?? {};
        document.getElementById('statTodayBookings').textContent      = stats.today_bookings ?? 0;
        document.getElementById('statPendingEmergencies').textContent = stats.pending_emergencies ?? 0;
        document.getElementById('statWeekConfirmed').textContent      = stats.week_confirmed ?? 0;

        const feedbackStat = document.getElementById('statPendingFeedback');
        if (feedbackStat) {
            feedbackStat.textContent = stats.pending_feedback ?? 0;
        }

        // Recent bookings
        const bookings = data.recent_bookings ?? [];
        const bookingsList  = document.getElementById('recentBookingsList');
        const bookingsEmpty = document.getElementById('recentBookingsEmpty');
        if (bookings.length === 0) {
            bookingsList.classList.add('hidden');
            bookingsEmpty.classList.remove('hidden');
        } else {
            bookingsList.innerHTML = bookings.map(renderBookingRow).join('');
            bookingsList.classList.remove('hidden');
            bookingsEmpty.classList.add('hidden');
        }

        // Recent emergency requests
        const emergencies = data.recent_emergencies ?? [];
        const emergencyList  = document.getElementById('recentEmergencyList');
        const emergencyEmpty = document.getElementById('recentEmergencyEmpty');
        if (emergencies.length === 0) {
            emergencyList.classList.add('hidden');
            emergencyEmpty.classList.remove('hidden');
        } else {
            emergencyList.innerHTML = emergencies.map(renderEmergencyRow).join('');
            emergencyList.classList.remove('hidden');
            emergencyEmpty.classList.add('hidden');
        }

        skeleton.classList.add('hidden');
        content.classList.remove('hidden', 'opacity-0');

    } catch (err) {
        console.error('Error loading admin dashboard:', err);
        skeleton.classList.add('hidden');
        content.classList.remove('hidden', 'opacity-0');
        // Fall back to empty states so the layout doesn't look broken
        document.getElementById('recentBookingsList').classList.add('hidden');
        document.getElementById('recentBookingsEmpty').classList.remove('hidden');
        document.getElementById('recentEmergencyList').classList.add('hidden');
        document.getElementById('recentEmergencyEmpty').classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>

</body>
</html>