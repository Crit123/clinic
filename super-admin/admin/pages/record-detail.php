<?php
/**
 * admin/pages/record-detail.php?id=X
 * Single dental record detail view, admin/staff only.
 *
 * NOTE: Every time this page's file is viewed inline or downloaded, that
 * action should be logged to record_access_logs (who, IP, timestamp).
 * That insert is NOT done here — it happens backend-side, inside
 * admin/backend/records-access-log.php (or a dedicated file-serving
 * endpoint), so the log stays authoritative even if this page is bypassed.
 */

$activePage = 'records';
$pageTitle  = 'Record Detail';
$recordId   = isset($_GET['id']) ? (int) $_GET['id'] : 0;
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Record Detail - DentalCare Pro Admin</title>
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
    .type-pill {
        display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700;
        padding: 4px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.03em;
    }
    .access-log-item { border-left: 2px solid rgba(114,119,131,0.25); }
    .access-log-item:last-child { border-left-color: transparent; }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen">

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-5">

            <a href="records.php" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:underline">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Records
            </a>

            <!-- Skeleton -->
            <div id="detailSkeleton" class="grid grid-cols-1 lg:grid-cols-3 gap-6" aria-hidden="true">
                <div class="lg:col-span-2 h-96 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
                <div class="h-96 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
            </div>

            <!-- Error state -->
            <div id="detailError" class="hidden bg-surface-container-lowest rounded-2xl border border-error/30 p-8 text-center">
                <span class="material-symbols-outlined text-4xl text-error mb-2">error</span>
                <p class="text-sm font-medium text-on-surface" id="detailErrorText">Could not load this record.</p>
            </div>

            <!-- Content -->
            <div id="detailContent" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Main record panel -->
                <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.06)] p-6 space-y-5">
                    <div class="flex items-start justify-between flex-wrap gap-3">
                        <div>
                            <span id="recordTypePill" class="type-pill mb-2"></span>
                            <h1 id="recordTitle" class="text-xl font-bold text-on-surface">—</h1>
                            <p id="recordPatientName" class="text-sm text-on-surface-variant mt-0.5">—</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-b border-outline-variant/15 py-4">
                        <div>
                            <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Doctor</p>
                            <p id="recordDoctor" class="text-sm text-on-surface">—</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Record Date</p>
                            <p id="recordDate" class="text-sm text-on-surface">—</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Details</p>
                        <p id="recordDetails" class="text-sm text-on-surface-variant leading-relaxed bg-surface-container-low rounded-lg p-3">—</p>
                    </div>

                    <!-- Conditional: clinical notes (type = note) -->
                    <div id="clinicalNotesBlock" class="hidden">
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Clinical Notes</p>
                        <p id="recordClinicalNotes" class="text-sm text-on-surface-variant leading-relaxed bg-surface-container-low rounded-lg p-3 whitespace-pre-line">—</p>
                    </div>

                    <!-- Conditional: rx number (type = prescription) -->
                    <div id="rxNumberBlock" class="hidden">
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Rx Number</p>
                        <p id="recordRxNumber" class="text-sm font-mono font-bold text-on-surface bg-surface-container-low inline-block rounded-lg px-3 py-1.5">—</p>
                    </div>

                    <!-- File preview / download -->
                    <div id="fileBlock" class="hidden">
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Attached File</p>
                        <div id="filePreviewArea" class="bg-surface-container-low rounded-xl p-4"></div>
                    </div>
                </div>

                <!-- Access History sidebar -->
                <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.06)] p-5 lg:sticky lg:top-6 self-start">
                    <h3 class="font-bold text-sm text-on-surface flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-primary text-[18px]">history</span>
                        Access History
                    </h3>
                    <div id="accessLogSkeleton" class="space-y-3">
                        <div class="h-12 rounded-lg bg-surface-container-low animate-shimmer"></div>
                        <div class="h-12 rounded-lg bg-surface-container-low animate-shimmer"></div>
                        <div class="h-12 rounded-lg bg-surface-container-low animate-shimmer"></div>
                    </div>
                    <ul id="accessLogList" class="hidden space-y-0"></ul>
                    <p id="accessLogEmpty" class="hidden text-sm text-on-surface-variant/70 italic">No access recorded yet.</p>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
const RECORD_ID = <?php echo json_encode($recordId); ?>;

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function timeAgo(dateString) {
    if (!dateString) return '';
    const then = new Date(dateString.replace(' ', 'T'));
    const seconds = Math.floor((Date.now() - then.getTime()) / 1000);
    if (isNaN(seconds)) return dateString;
    if (seconds < 30) return 'just now';
    if (seconds < 60) return `${seconds}s ago`;
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} minute${minutes === 1 ? '' : 's'} ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ago`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `${days} day${days === 1 ? '' : 's'} ago`;
    return then.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
}

function exactTimestamp(dateString) {
    if (!dateString) return '';
    const d = new Date(dateString.replace(' ', 'T'));
    return d.toLocaleString('en-US', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

const typePillStyles = {
    xray:         'bg-sky-100 text-sky-800',
    note:         'bg-violet-100 text-violet-800',
    prescription: 'bg-emerald-100 text-emerald-800',
};
const typeLabels = { xray: 'X-Ray', note: 'Note', prescription: 'Prescription' };

const actionIcons = {
    viewed:      'visibility',
    downloaded:  'download',
};

async function loadRecordDetail() {
    const skeleton = document.getElementById('detailSkeleton');
    const errorBox = document.getElementById('detailError');
    const content = document.getElementById('detailContent');

    if (!RECORD_ID) {
        skeleton.classList.add('hidden');
        errorBox.classList.remove('hidden');
        document.getElementById('detailErrorText').textContent = 'No record ID was provided.';
        return;
    }

    try {
        const res = await fetch(`../backend/records-access-log.php?record_id=${encodeURIComponent(RECORD_ID)}`);
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to load record.');

        renderRecord(data.record ?? {});
        renderAccessLog(data.access_log ?? []);

        skeleton.classList.add('hidden');
        content.classList.remove('hidden');
        content.classList.add('grid');

        // NOTE: this page load itself (and any inline preview/download click
        // below) represents a "view" of the record. The corresponding row in
        // record_access_logs is inserted by the backend endpoint above, not
        // by this script — this comment is just documenting that expectation.

    } catch (err) {
        console.error('Error loading record detail:', err);
        skeleton.classList.add('hidden');
        errorBox.classList.remove('hidden');
        document.getElementById('detailErrorText').textContent = err.message || 'Could not load this record.';
    }
}

function renderRecord(record) {
    const type = record.type ?? 'note';

    const pill = document.getElementById('recordTypePill');
    pill.textContent = typeLabels[type] ?? type;
    pill.className = `type-pill mb-2 ${typePillStyles[type] ?? 'bg-gray-100 text-gray-700'}`;

    document.getElementById('recordTitle').textContent = record.title ?? 'Untitled Record';
    document.getElementById('recordPatientName').textContent = record.patient_name ? `Patient: ${record.patient_name}` : '';
    document.getElementById('recordDoctor').textContent = record.doctor_name ?? '—';
    document.getElementById('recordDate').textContent = record.record_date ?? '—';
    document.getElementById('recordDetails').textContent = record.details && record.details.trim() ? record.details : 'No additional details provided.';

    if (type === 'note' && record.clinical_notes) {
        document.getElementById('clinicalNotesBlock').classList.remove('hidden');
        document.getElementById('recordClinicalNotes').textContent = record.clinical_notes;
    }

    if (type === 'prescription' && record.rx_number) {
        document.getElementById('rxNumberBlock').classList.remove('hidden');
        document.getElementById('recordRxNumber').textContent = record.rx_number;
    }

    renderFileBlock(record);
}

/**
 * Renders the attached file. Always displays record.original_filename to the
 * user — never record.stored_filename, which is only an internal disk/path
 * identifier and should never be shown or leaked to the client.
 */
function renderFileBlock(record) {
    const fileBlock = document.getElementById('fileBlock');
    const previewArea = document.getElementById('filePreviewArea');

    if (!record.file_url) {
        fileBlock.classList.add('hidden');
        return;
    }

    fileBlock.classList.remove('hidden');
    const displayName = record.original_filename || 'attached-file';
    const ext = (displayName.split('.').pop() || '').toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
    const isPdf = ext === 'pdf';

    if (isImage) {
        previewArea.innerHTML = `
            <img src="${record.file_url}" alt="${escapeHtml(displayName)}" class="max-w-full rounded-lg border border-outline-variant/20 mb-3" loading="lazy">
            <a href="${record.file_url}" download="${escapeHtml(displayName)}" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:underline">
                <span class="material-symbols-outlined text-[16px]">download</span> Download ${escapeHtml(displayName)}
            </a>`;
    } else if (isPdf) {
        previewArea.innerHTML = `
            <iframe src="${record.file_url}" class="w-full h-96 rounded-lg border border-outline-variant/20 mb-3" title="${escapeHtml(displayName)}"></iframe>
            <a href="${record.file_url}" download="${escapeHtml(displayName)}" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:underline">
                <span class="material-symbols-outlined text-[16px]">download</span> Download ${escapeHtml(displayName)}
            </a>`;
    } else {
        previewArea.innerHTML = `
            <a href="${record.file_url}" download="${escapeHtml(displayName)}" class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                <span class="material-symbols-outlined text-[20px]">description</span> ${escapeHtml(displayName)}
            </a>`;
    }
}

function renderAccessLog(entries) {
    const skeleton = document.getElementById('accessLogSkeleton');
    const list = document.getElementById('accessLogList');
    const empty = document.getElementById('accessLogEmpty');

    skeleton.classList.add('hidden');

    if (!entries || entries.length === 0) {
        empty.classList.remove('hidden');
        return;
    }

    list.classList.remove('hidden');
    // Chronological, most recent first
    const sorted = entries.slice().sort((a, b) => new Date(b.accessed_at.replace(' ', 'T')) - new Date(a.accessed_at.replace(' ', 'T')));

    list.innerHTML = sorted.map(entry => `
        <li class="access-log-item pl-4 pb-4 relative">
            <span class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full bg-primary"></span>
            <p class="text-sm font-bold text-on-surface">${escapeHtml(entry.user_name ?? 'Unknown user')}</p>
            <p class="text-xs text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">${actionIcons[entry.action] ?? 'visibility'}</span>
                ${escapeHtml(entry.action ?? 'viewed')} &middot; ${escapeHtml(entry.ip_address ?? 'unknown IP')}
            </p>
            <p class="text-[11px] text-on-surface-variant/60 mt-0.5" title="${escapeHtml(exactTimestamp(entry.accessed_at))}">
                ${escapeHtml(timeAgo(entry.accessed_at))}
            </p>
        </li>
    `).join('');
}

document.addEventListener('DOMContentLoaded', loadRecordDetail);
</script>

</body>
</html>