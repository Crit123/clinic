<?php
/**
 * admin/pages/feedback.php
 * Moderation queue for patient feedback/testimonials. Admin only.
 * (No UI-level navigation guard — actual access control is enforced
 * PHP-side wherever this route is dispatched/included.)
 */

$activePage = 'feedback';
$pageTitle  = 'Feedback Moderation';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Feedback Moderation - DentalCare Pro Admin</title>
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

    .tab-strip-btn {
        border: 1.5px solid transparent; background: transparent; color: #424752;
        border-radius: 0.6rem; transition: all 0.2s ease; white-space: nowrap;
    }
    .tab-strip-btn.active-tab { background: #00478d; color: #ffffff; border-color: #00478d; }
    .tab-strip-btn:not(.active-tab):hover { background: rgba(0,71,141,0.06); border-color: rgba(0,71,141,0.2); }

    .feedback-card { transition: opacity 0.35s ease, transform 0.35s ease, max-height 0.35s ease; }
    .feedback-card.fading-out { opacity: 0; transform: scale(0.97) translateY(-4px); }

    .action-btn {
        display: inline-flex; align-items: center; gap: 4px; padding: 7px 14px;
        border-radius: 0.5rem; font-size: 12.5px; font-weight: 700; transition: all 0.15s ease;
    }
    .action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen">

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-5">

            <div class="flex items-center justify-between flex-wrap gap-3">
                <h1 class="text-xl sm:text-2xl font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600">rate_review</span>
                    Feedback Moderation
                </h1>
            </div>

            <!-- Status Tabs -->
            <div class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Filter by moderation status">
                <button class="tab-strip-btn active-tab px-4 py-2 text-sm font-bold" data-status="pending" onclick="switchTab('pending')">Pending</button>
                <button class="tab-strip-btn px-4 py-2 text-sm font-bold" data-status="approved" onclick="switchTab('approved')">Approved</button>
                <button class="tab-strip-btn px-4 py-2 text-sm font-bold" data-status="rejected" onclick="switchTab('rejected')">Rejected</button>
            </div>

            <!-- Queue -->
            <div id="queueSkeleton" class="space-y-4" aria-hidden="true">
                <div class="h-36 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
                <div class="h-36 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
                <div class="h-36 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
            </div>

            <div id="queueList" class="hidden space-y-4" aria-live="polite"></div>

            <div id="queueEmpty" class="hidden flex-col items-center justify-center py-16 px-6 text-center bg-surface-container-lowest rounded-2xl border border-surface-container">
                <span class="material-symbols-outlined text-4xl text-outline-variant mb-2" aria-hidden="true">forum</span>
                <p class="text-sm text-on-surface-variant font-medium" id="queueEmptyText">No feedback here.</p>
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

function formatDate(dateString) {
    if (!dateString) return '—';
    const d = new Date(dateString.replace(' ', 'T'));
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
}

/**
 * Renders a 1-5 star rating as filled/outline star icons (never a bare number).
 */
function renderStars(rating) {
    const r = Math.max(0, Math.min(5, Math.round(Number(rating) || 0)));
    let html = '<span class="inline-flex items-center gap-0.5" aria-label="' + r + ' out of 5 stars">';
    for (let i = 1; i <= 5; i++) {
        html += `<span class="material-symbols-outlined text-[18px] ${i <= r ? 'text-amber-500' : 'text-outline-variant/50'}" style="font-variation-settings:'FILL' ${i <= r ? 1 : 0}, 'wght' 500;">star</span>`;
    }
    html += '</span>';
    return html;
}

const statusLabels = { pending: 'Pending', approved: 'Approved', rejected: 'Rejected' };

const state = {
    activeTab: 'pending',
    items: [],
};

function switchTab(status) {
    state.activeTab = status;
    document.querySelectorAll('.tab-strip-btn').forEach(btn => {
        btn.classList.toggle('active-tab', btn.dataset.status === status);
    });
    loadFeedback();
}

async function loadFeedback() {
    const skeleton = document.getElementById('queueSkeleton');
    const list = document.getElementById('queueList');
    const empty = document.getElementById('queueEmpty');

    skeleton.classList.remove('hidden');
    list.classList.add('hidden');
    empty.classList.add('hidden');
    empty.classList.remove('flex');

    try {
        const params = new URLSearchParams({ status: state.activeTab });
        const res = await fetch(`../backend/feedback-list.php?${params.toString()}`);
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to load feedback.');

        state.items = data.feedback ?? [];
        renderQueue();

    } catch (err) {
        console.error('Error loading feedback:', err);
        skeleton.classList.add('hidden');
        empty.classList.remove('hidden');
        empty.classList.add('flex');
        document.getElementById('queueEmptyText').textContent = 'Unable to load feedback. Please try again.';
        showLocalToast('error', 'Could not load feedback.');
    }
}

function renderQueue() {
    const skeleton = document.getElementById('queueSkeleton');
    const list = document.getElementById('queueList');
    const empty = document.getElementById('queueEmpty');

    skeleton.classList.add('hidden');

    if (state.items.length === 0) {
        list.classList.add('hidden');
        empty.classList.remove('hidden');
        empty.classList.add('flex');
        document.getElementById('queueEmptyText').textContent = `No ${statusLabels[state.activeTab].toLowerCase()} feedback right now.`;
        return;
    }

    empty.classList.add('hidden');
    empty.classList.remove('flex');
    list.classList.remove('hidden');
    list.innerHTML = state.items.map(cardHtml).join('');
}

function cardHtml(f) {
    const isPending = f.status === 'pending' || state.activeTab === 'pending';

    return `
        <div id="feedbackCard-${f.id}" class="feedback-card bg-surface-container-lowest rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.06)] p-5 sm:p-6">
            <div class="flex items-start justify-between flex-wrap gap-3 mb-2">
                <div>
                    <h3 class="text-base font-bold text-on-surface">${escapeHtml(f.display_name)}</h3>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        ${renderStars(f.rating)}
                        ${f.service_key ? `<span class="text-[10px] font-bold uppercase tracking-wider text-sky-800 bg-sky-100 px-2 py-0.5 rounded-full">${escapeHtml(f.service_key)}</span>` : ''}
                    </div>
                </div>
                <p class="text-xs text-on-surface-variant/70 shrink-0">${formatDate(f.created_at)}</p>
            </div>

            <p class="text-sm text-on-surface-variant leading-relaxed bg-surface-container-low rounded-lg p-3 mt-2">${escapeHtml(f.comment)}</p>

            <div class="mt-4 flex items-center justify-end gap-2">
                ${isPending ? `
                    <button class="action-btn bg-red-50 text-red-700 hover:bg-red-100" onclick="moderateFeedback(${f.id}, 'rejected')" data-role="reject-btn-${f.id}">
                        <span class="material-symbols-outlined text-[15px]">close</span> Reject
                    </button>
                    <button class="action-btn bg-emerald-50 text-emerald-700 hover:bg-emerald-100" onclick="moderateFeedback(${f.id}, 'approved')" data-role="approve-btn-${f.id}">
                        <span class="material-symbols-outlined text-[15px]">check</span> Approve
                    </button>
                ` : `
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant/70 italic">
                        <span class="material-symbols-outlined text-[15px]">verified</span>
                        Moderated${f.moderated_by_name ? ' by ' + escapeHtml(f.moderated_by_name) : ''}${f.approved_at ? ' on ' + formatDate(f.approved_at) : ''}
                    </span>
                `}
            </div>
        </div>`;
}

async function moderateFeedback(id, newStatus) {
    const approveBtn = document.querySelector(`[data-role="approve-btn-${id}"]`);
    const rejectBtn = document.querySelector(`[data-role="reject-btn-${id}"]`);
    if (approveBtn) approveBtn.disabled = true;
    if (rejectBtn) rejectBtn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('feedback_id', id);
        formData.append('status', newStatus);

        const res = await fetch('../backend/feedback-moderate.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to update feedback.');

        // Fade the card out, then remove it from the current tab's view
        const card = document.getElementById(`feedbackCard-${id}`);
        if (card) {
            card.style.maxHeight = card.offsetHeight + 'px';
            requestAnimationFrame(() => {
                card.classList.add('fading-out');
                card.style.maxHeight = '0px';
                card.style.marginBottom = '0px';
                card.style.paddingTop = '0px';
                card.style.paddingBottom = '0px';
                card.style.overflow = 'hidden';
            });
            setTimeout(() => {
                card.remove();
                state.items = state.items.filter(item => item.id !== id);
                if (state.items.length === 0) renderQueue();
            }, 360);
        }

        showLocalToast('success', `Feedback ${newStatus === 'approved' ? 'approved' : 'rejected'}.`);

    } catch (err) {
        console.error(err);
        showLocalToast('error', err.message || 'Could not update feedback.');
        if (approveBtn) approveBtn.disabled = false;
        if (rejectBtn) rejectBtn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', loadFeedback);
</script>

</body>
</html>