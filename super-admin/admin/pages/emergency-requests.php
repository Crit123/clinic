<?php
/**
 * admin/pages/emergency-requests.php
 * Triage queue for emergency requests submitted by patients/guests.
 */

$activePage = 'emergency-requests';
$pageTitle  = 'Emergency Requests';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Emergency Requests - DentalCare Pro Admin</title>
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

    /* Status tab strip */
    .tab-strip-btn {
        border: 1.5px solid transparent; background: transparent; color: #424752;
        border-radius: 0.6rem; transition: all 0.2s ease; white-space: nowrap;
    }
    .tab-strip-btn.active-tab { background: #00478d; color: #ffffff; border-color: #00478d; }
    .tab-strip-btn:not(.active-tab):hover { background: rgba(0,71,141,0.06); border-color: rgba(0,71,141,0.2); }

    .status-select {
        padding: 7px 10px; border-radius: 0.5rem; background-color: #ffffff;
        border: 1.5px solid rgba(114, 119, 131, 0.3); color: #0b1c30; font-size: 0.8125rem;
        font-weight: 700; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .status-select:focus { border-color: #00478d; box-shadow: 0 0 0 4px rgba(0, 71, 141, 0.08); }

    .emergency-card { transition: border-color 0.3s ease, background-color 0.3s ease; }
    .emergency-card.overdue { border-left-color: #dc2626 !important; background-color: rgba(220, 38, 38, 0.03); }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen">

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-5">

            <div class="flex items-center justify-between">
                <h1 class="text-xl sm:text-2xl font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-600">emergency</span>
                    Emergency Requests
                </h1>
                <button id="refreshBtn" onclick="loadRequests()" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">refresh</span> Refresh
                </button>
            </div>

            <!-- Status Tab Strip -->
            <div class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Filter by status">
                <button class="tab-strip-btn active-tab px-4 py-2 text-sm font-bold" data-status="submitted" onclick="switchTab('submitted')">Submitted</button>
                <button class="tab-strip-btn px-4 py-2 text-sm font-bold" data-status="under_review" onclick="switchTab('under_review')">Under Review</button>
                <button class="tab-strip-btn px-4 py-2 text-sm font-bold" data-status="contacted" onclick="switchTab('contacted')">Contacted</button>
                <button class="tab-strip-btn px-4 py-2 text-sm font-bold" data-status="resolved" onclick="switchTab('resolved')">Resolved</button>
                <button class="tab-strip-btn px-4 py-2 text-sm font-bold" data-status="all" onclick="switchTab('all')">All</button>
            </div>

            <!-- Queue -->
            <div id="queueSkeleton" class="space-y-4" aria-hidden="true">
                <div class="h-32 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
                <div class="h-32 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
                <div class="h-32 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
            </div>

            <div id="queueList" class="hidden space-y-4" aria-live="polite"></div>

            <div id="queueEmpty" class="hidden flex-col items-center justify-center py-16 px-6 text-center bg-surface-container-lowest rounded-2xl border border-surface-container">
                <span class="material-symbols-outlined text-4xl text-outline-variant mb-2" aria-hidden="true">check_circle</span>
                <p class="text-sm text-on-surface-variant font-medium" id="queueEmptyText">No emergency requests here.</p>
            </div>
        </main>
    </div>
</div>

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

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

/**
 * Relative time formatter, e.g. "12 minutes ago", "just now", "3 hours ago".
 */
function timeAgo(dateString) {
    const then = new Date(dateString.replace(' ', 'T'));
    const seconds = Math.floor((Date.now() - then.getTime()) / 1000);

    if (isNaN(seconds)) return dateString;
    if (seconds < 30) return 'just now';
    if (seconds < 60) return `${seconds} seconds ago`;

    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} minute${minutes === 1 ? '' : 's'} ago`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ago`;

    const days = Math.floor(hours / 24);
    return `${days} day${days === 1 ? '' : 's'} ago`;
}

function isOverdue(request) {
    if (request.status !== 'submitted') return false;
    const then = new Date(request.created_at.replace(' ', 'T'));
    const ageMs = Date.now() - then.getTime();
    return ageMs > (60 * 60 * 1000); // older than 1 hour
}

const statusLabels = {
    submitted:    'Submitted',
    under_review: 'Under Review',
    contacted:    'Contacted',
    resolved:     'Resolved',
};

const state = {
    activeTab: 'submitted',
    requests: [],
};

function switchTab(status) {
    state.activeTab = status;
    document.querySelectorAll('.tab-strip-btn').forEach(btn => {
        btn.classList.toggle('active-tab', btn.dataset.status === status);
    });
    loadRequests();
}

async function loadRequests() {
    const skeleton = document.getElementById('queueSkeleton');
    const list = document.getElementById('queueList');
    const empty = document.getElementById('queueEmpty');

    skeleton.classList.remove('hidden');
    list.classList.add('hidden');
    empty.classList.add('hidden');
    empty.classList.remove('flex');

    const params = new URLSearchParams({ status: state.activeTab });

    try {
        const res = await fetch(`../backend/emergency-list.php?${params.toString()}`);
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to load emergency requests.');

        // Oldest-first within each status queue
        state.requests = (data.requests ?? []).slice().sort(
            (a, b) => new Date(a.created_at.replace(' ', 'T')) - new Date(b.created_at.replace(' ', 'T'))
        );

        renderQueue();

    } catch (err) {
        console.error('Error loading emergency requests:', err);
        skeleton.classList.add('hidden');
        empty.classList.remove('hidden');
        empty.classList.add('flex');
        document.getElementById('queueEmptyText').textContent = 'Unable to load requests. Please try again.';
        showLocalToast('error', 'Could not load emergency requests.');
    }
}

function renderQueue() {
    const skeleton = document.getElementById('queueSkeleton');
    const list = document.getElementById('queueList');
    const empty = document.getElementById('queueEmpty');

    skeleton.classList.add('hidden');

    if (state.requests.length === 0) {
        list.classList.add('hidden');
        empty.classList.remove('hidden');
        empty.classList.add('flex');
        document.getElementById('queueEmptyText').textContent =
            state.activeTab === 'all' ? 'No emergency requests on record.' : `No ${statusLabels[state.activeTab]?.toLowerCase() ?? ''} requests right now.`;
        return;
    }

    empty.classList.add('hidden');
    empty.classList.remove('flex');
    list.classList.remove('hidden');
    list.innerHTML = state.requests.map(cardHtml).join('');
}

function cardHtml(r) {
    const overdue = isOverdue(r);
    return `
        <div id="emergencyCard-${r.id}"
             class="emergency-card bg-surface-container-lowest rounded-2xl border-l-4 ${overdue ? 'border-l-red-600 border-t border-r border-b border-outline-variant/20 overdue' : 'border-l-outline-variant/30 border-t border-r border-b border-outline-variant/20'} shadow-[0_4px_16px_rgba(0,71,141,0.06)] p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1.5">
                        <span class="font-mono text-xs font-bold text-on-surface-variant">${escapeHtml(r.case_code)}</span>
                        <span class="overdue-badge ${overdue ? '' : 'hidden'} inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-red-700 bg-red-100 px-2 py-0.5 rounded-full">
                            <span class="material-symbols-outlined text-[12px]">priority_high</span> Overdue &gt; 1hr
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-on-surface mb-1">${escapeHtml(r.first_name)} ${escapeHtml(r.last_name)}</h3>
                    <a href="tel:${escapeHtml(r.phone)}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline mb-3">
                        <span class="material-symbols-outlined text-[16px]">call</span> ${escapeHtml(r.phone)}
                    </a>
                    <p class="text-sm text-on-surface-variant leading-relaxed bg-surface-container-low rounded-lg p-3">${escapeHtml(r.symptom)}</p>
                    <p class="text-xs text-on-surface-variant/70 mt-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                        Submitted ${escapeHtml(timeAgo(r.created_at))}
                    </p>
                </div>
                <div class="shrink-0 sm:w-44 w-full">
                    <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1" for="statusSelect-${r.id}">Status</label>
                    <select id="statusSelect-${r.id}" class="status-select w-full" onchange="updateStatus(${r.id}, this.value)">
                        <option value="submitted" ${r.status === 'submitted' ? 'selected' : ''}>Submitted</option>
                        <option value="under_review" ${r.status === 'under_review' ? 'selected' : ''}>Under Review</option>
                        <option value="contacted" ${r.status === 'contacted' ? 'selected' : ''}>Contacted</option>
                        <option value="resolved" ${r.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                    </select>
                </div>
            </div>
        </div>`;
}

async function updateStatus(id, newStatus) {
    const select = document.getElementById(`statusSelect-${id}`);
    const request = state.requests.find(r => r.id === id);
    const previousStatus = request ? request.status : null;

    select.disabled = true;

    try {
        const formData = new FormData();
        formData.append('request_id', id);
        formData.append('status', newStatus);

        const res = await fetch('../backend/emergency-update-status.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to update status.');

        if (request) request.status = newStatus;

        // Update the card's border/highlight immediately, no reload
        const card = document.getElementById(`emergencyCard-${id}`);
        if (card) {
            const overdue = isOverdue({ status: newStatus, created_at: request?.created_at ?? new Date().toISOString() });
            card.classList.toggle('overdue', overdue);
            card.classList.toggle('border-l-red-600', overdue);
            card.classList.toggle('border-l-outline-variant/30', !overdue);

            const badge = card.querySelector('.overdue-badge');
            if (badge) badge.classList.toggle('hidden', !overdue);
        }

        // If we're viewing a single-status tab and the item moved out of it,
        // drop it from the current queue view without a full refetch.
        if (state.activeTab !== 'all' && newStatus !== state.activeTab) {
            state.requests = state.requests.filter(r => r.id !== id);
            renderQueue();
        }

        showLocalToast('success', `Case ${request?.case_code ?? ''} moved to ${statusLabels[newStatus]}.`);

    } catch (err) {
        console.error(err);
        showLocalToast('error', err.message || 'Could not update status.');
        select.value = previousStatus ?? select.value;
    } finally {
        select.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', loadRequests);
</script>

</body>
</html>