<?php
/**
 * admin/pages/records.php
 * Patient search + dental record browser. Admin only.
 * (No UI-level navigation guard — actual access control is enforced
 * PHP-side wherever this route is dispatched/included.)
 */

$activePage = 'records';
$pageTitle  = 'Patient Records';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Patient Records - DentalCare Pro Admin</title>
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
    .form-input {
        width: 100%; padding: 9px 12px; border-radius: 0.5rem; background-color: #ffffff;
        border: 1.5px solid rgba(114, 119, 131, 0.3); color: #0b1c30; font-size: 0.8125rem;
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .form-input:focus { border-color: #00478d; box-shadow: 0 0 0 4px rgba(0, 71, 141, 0.08); }

    .patient-result-row { transition: background-color 0.15s ease; cursor: pointer; }
    .patient-result-row:hover { background-color: rgba(0,71,141,0.05); }
    .patient-result-row.selected { background-color: rgba(0,71,141,0.08); border-color: #00478d; }

    /* Collapsible sections */
    .section-toggle .chevron { transition: transform 0.2s ease; }
    .section-toggle.collapsed .chevron { transform: rotate(-90deg); }
    .section-body { overflow: hidden; transition: grid-template-rows 0.25s ease; display: grid; grid-template-rows: 1fr; }
    .section-body.collapsed { grid-template-rows: 0fr; }
    .section-body > div { overflow: hidden; min-height: 0; }

    .modal-overlay {
        position: fixed; inset: 0; background: rgba(11, 28, 48, 0.45); z-index: 100;
        display: flex; align-items: center; justify-content: center; padding: 1rem;
        opacity: 0; pointer-events: none; transition: opacity 0.2s ease;
    }
    .modal-overlay.open { opacity: 1; pointer-events: auto; }
    .modal-card { transform: translateY(12px) scale(0.98); transition: transform 0.2s cubic-bezier(0.16,1,0.3,1); }
    .modal-overlay.open .modal-card { transform: translateY(0) scale(1); }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen">

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-bold text-primary">Patient Records</h1>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-amber-800 bg-amber-100 px-2.5 py-1 rounded-full">
                        <span class="material-symbols-outlined text-[13px]">shield_lock</span> Admin-only
                    </span>
                </div>
            </div>

            <!-- Patient Search -->
            <section class="bg-surface-container-lowest rounded-2xl border border-surface-container p-4 sm:p-5 shadow-[0_4px_16px_rgba(0,71,141,0.06)]">
                <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1.5" for="patientSearchInput">Search Patients</label>
                <div class="relative">
                    <input type="text" id="patientSearchInput" placeholder="Search by name or email…" class="form-input pl-10 text-sm">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 text-[20px]">search</span>
                </div>

                <div id="patientSearchSkeleton" class="hidden mt-3 space-y-2">
                    <div class="h-12 rounded-lg bg-surface-container-low animate-shimmer"></div>
                    <div class="h-12 rounded-lg bg-surface-container-low animate-shimmer"></div>
                </div>

                <ul id="patientResultsList" class="hidden mt-3 divide-y divide-outline-variant/10 border border-outline-variant/15 rounded-xl overflow-hidden"></ul>

                <p id="patientSearchHint" class="text-xs text-on-surface-variant/70 mt-2">Type at least 2 characters to search.</p>
                <p id="patientSearchEmpty" class="hidden text-sm text-on-surface-variant mt-2 italic">No patients matched your search.</p>
            </section>

            <!-- Selected Patient + Records -->
            <section id="recordsSection" class="hidden space-y-4">
                <div class="bg-surface-container-lowest rounded-2xl border border-surface-container p-5 shadow-[0_4px_16px_rgba(0,71,141,0.06)] flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-0.5">Viewing records for</p>
                        <h2 id="selectedPatientName" class="text-lg font-bold text-on-surface">—</h2>
                        <p id="selectedPatientEmail" class="text-sm text-on-surface-variant">—</p>
                    </div>
                    <button onclick="openAddRecordModal()" class="bg-primary text-on-primary text-sm font-bold px-4 py-2.5 rounded-lg hover:bg-on-primary-fixed-variant transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">add</span> Add Record
                    </button>
                </div>

                <div id="recordsSkeleton" class="space-y-3">
                    <div class="h-16 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
                    <div class="h-16 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
                </div>

                <div id="recordGroups" class="hidden space-y-4"></div>
            </section>
        </main>
    </div>
</div>

<!-- Add Record Modal -->
<div id="addRecordModal" class="modal-overlay" aria-hidden="true">
    <div class="modal-card bg-surface-container-lowest rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[88vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-on-surface">Add Dental Record</h3>
            <button onclick="closeModal('addRecordModal')" class="text-on-surface-variant/50 hover:text-on-surface-variant">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div id="addRecordError" class="hidden mb-3 flex items-start gap-2 rounded-lg border border-error/30 bg-error/5 px-3.5 py-2.5 text-sm text-error font-medium">
            <span class="material-symbols-outlined text-[18px] mt-0.5">error</span>
            <span id="addRecordErrorText">Something went wrong.</span>
        </div>

        <form id="addRecordForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="recordType">Type</label>
                <select id="recordType" class="form-input" onchange="handleTypeChange()">
                    <option value="xray">X-Ray</option>
                    <option value="note">Note</option>
                    <option value="prescription">Prescription</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="recordTitle">Title</label>
                <input type="text" id="recordTitle" class="form-input" placeholder="e.g. Panoramic X-Ray — Upper Left Molar" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="recordDoctor">Doctor Name</label>
                    <input type="text" id="recordDoctor" class="form-input" placeholder="Dr. Santos" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="recordDate">Record Date</label>
                    <input type="date" id="recordDate" class="form-input" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="recordDetails">Details</label>
                <textarea id="recordDetails" rows="3" class="form-input resize-none" placeholder="General description of the record…"></textarea>
            </div>

            <!-- Conditional: Notes -->
            <div id="fieldGroupNote" class="hidden">
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="recordClinicalNotes">Clinical Notes</label>
                <textarea id="recordClinicalNotes" rows="4" class="form-input resize-none" placeholder="Detailed clinical observations…"></textarea>
            </div>

            <!-- Conditional: Prescriptions -->
            <div id="fieldGroupPrescription" class="hidden">
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="recordRxNumber">Rx Number</label>
                <input type="text" id="recordRxNumber" class="form-input" placeholder="e.g. RX-2026-00123">
            </div>

            <!-- Conditional: X-Rays -->
            <div id="fieldGroupXray" class="hidden">
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="recordFile">X-Ray File</label>
                <input type="file" id="recordFile" accept="image/*,.pdf" class="form-input file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary">
                <p class="text-[11px] text-on-surface-variant/70 mt-1">Accepts image files or PDF scans.</p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('addRecordModal')" class="text-sm font-bold text-on-surface-variant hover:text-on-surface px-4 py-2 rounded-lg transition-colors">Cancel</button>
                <button type="submit" id="submitRecordBtn" class="bg-primary text-on-primary text-sm font-bold px-4 py-2 rounded-lg hover:bg-on-primary-fixed-variant transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">save</span> Save Record
                </button>
            </div>
        </form>
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

const state = {
    selectedPatientId: null,
    selectedPatient: null,
    searchDebounce: null,
};

const typeLabels = { xray: 'X-Rays', note: 'Notes', prescription: 'Prescriptions' };
const typeOrder = ['xray', 'note', 'prescription'];

// ── Patient Search ────────────────────────────────────────────────────────
document.getElementById('patientSearchInput').addEventListener('input', (e) => {
    clearTimeout(state.searchDebounce);
    const query = e.target.value.trim();

    if (query.length < 2) {
        document.getElementById('patientResultsList').classList.add('hidden');
        document.getElementById('patientSearchEmpty').classList.add('hidden');
        document.getElementById('patientSearchHint').classList.remove('hidden');
        return;
    }

    document.getElementById('patientSearchHint').classList.add('hidden');
    state.searchDebounce = setTimeout(() => searchPatients(query), 300);
});

async function searchPatients(query) {
    const skeleton = document.getElementById('patientSearchSkeleton');
    const list = document.getElementById('patientResultsList');
    const empty = document.getElementById('patientSearchEmpty');

    skeleton.classList.remove('hidden');
    list.classList.add('hidden');
    empty.classList.add('hidden');

    try {
        const params = new URLSearchParams({ action: 'search_patients', q: query });
        const res = await fetch(`../backend/records-list.php?${params.toString()}`);
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Search failed.');

        skeleton.classList.add('hidden');
        const patients = data.patients ?? [];

        if (patients.length === 0) {
            list.classList.add('hidden');
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        list.classList.remove('hidden');
        list.innerHTML = patients.map(p => `
            <li class="patient-result-row px-4 py-3 flex items-center justify-between gap-3 border-l-2 border-transparent"
                onclick="selectPatient(${p.id}, '${escapeHtml(p.first_name)} ${escapeHtml(p.last_name)}'.replace(/&#039;/g, \"'\"), '${escapeHtml(p.email)}')">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-on-surface truncate">${escapeHtml(p.first_name)} ${escapeHtml(p.last_name)}</p>
                    <p class="text-xs text-on-surface-variant truncate">${escapeHtml(p.email)} &middot; ${escapeHtml(p.phone ?? 'No phone on file')}</p>
                </div>
                <span class="material-symbols-outlined text-on-surface-variant/40 text-[18px]">chevron_right</span>
            </li>
        `).join('');

    } catch (err) {
        console.error('Patient search error:', err);
        skeleton.classList.add('hidden');
        showLocalToast('error', 'Could not search patients.');
    }
}

function selectPatient(id, name, email) {
    state.selectedPatientId = id;
    state.selectedPatient = { id, name, email };

    document.querySelectorAll('.patient-result-row').forEach(row => row.classList.remove('selected'));
    event?.currentTarget?.classList.add('selected');

    document.getElementById('selectedPatientName').textContent = name;
    document.getElementById('selectedPatientEmail').textContent = email;
    document.getElementById('recordsSection').classList.remove('hidden');

    loadRecords(id);
}

// ── Records ───────────────────────────────────────────────────────────────
async function loadRecords(patientId) {
    const skeleton = document.getElementById('recordsSkeleton');
    const groups = document.getElementById('recordGroups');

    skeleton.classList.remove('hidden');
    groups.classList.add('hidden');

    try {
        const params = new URLSearchParams({ action: 'patient_records', patient_id: patientId });
        const res = await fetch(`../backend/records-list.php?${params.toString()}`);
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to load records.');

        renderRecordGroups(data.records ?? []);

    } catch (err) {
        console.error('Error loading records:', err);
        skeleton.classList.add('hidden');
        showLocalToast('error', 'Could not load patient records.');
    }
}

function renderRecordGroups(records) {
    const skeleton = document.getElementById('recordsSkeleton');
    const groups = document.getElementById('recordGroups');

    skeleton.classList.add('hidden');
    groups.classList.remove('hidden');

    const byType = { xray: [], note: [], prescription: [] };
    records.forEach(r => { if (byType[r.type]) byType[r.type].push(r); });

    groups.innerHTML = typeOrder.map(type => groupHtml(type, byType[type])).join('');
}

function groupHtml(type, records) {
    const icon = type === 'xray' ? 'medical_information' : type === 'note' ? 'sticky_note_2' : 'medication';
    return `
        <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.06)] overflow-hidden">
            <button class="section-toggle w-full flex items-center justify-between px-5 py-4 hover:bg-surface-container-low/60 transition-colors" onclick="toggleSection(this)">
                <span class="flex items-center gap-2 font-bold text-on-surface text-sm">
                    <span class="material-symbols-outlined text-primary text-[20px]">${icon}</span>
                    ${typeLabels[type]}
                    <span class="text-xs font-normal text-on-surface-variant">(${records.length})</span>
                </span>
                <span class="material-symbols-outlined chevron text-on-surface-variant text-[20px]">expand_more</span>
            </button>
            <div class="section-body">
                <div>
                    ${records.length === 0
                        ? `<p class="px-5 pb-4 text-sm text-on-surface-variant/70 italic">No ${typeLabels[type].toLowerCase()} on file.</p>`
                        : `<ul class="divide-y divide-outline-variant/10 border-t border-outline-variant/10">
                            ${records.map(r => `
                                <li class="px-5 py-3 flex items-center justify-between gap-3 flex-wrap">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-on-surface truncate">${escapeHtml(r.title)}</p>
                                        <p class="text-xs text-on-surface-variant">${escapeHtml(r.doctor_name)} &middot; ${escapeHtml(r.record_date)}</p>
                                    </div>
                                    <a href="record-detail.php?id=${r.id}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1 shrink-0">
                                        View <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                    </a>
                                </li>
                            `).join('')}
                        </ul>`
                    }
                </div>
            </div>
        </div>`;
}

function toggleSection(btn) {
    btn.classList.toggle('collapsed');
    btn.nextElementSibling.classList.toggle('collapsed');
}

// ── Add Record Modal ──────────────────────────────────────────────────────
function openAddRecordModal() {
    document.getElementById('addRecordForm').reset();
    document.getElementById('addRecordError').classList.add('hidden');
    handleTypeChange();
    openModal('addRecordModal');
}

function handleTypeChange() {
    const type = document.getElementById('recordType').value;
    document.getElementById('fieldGroupNote').classList.toggle('hidden', type !== 'note');
    document.getElementById('fieldGroupPrescription').classList.toggle('hidden', type !== 'prescription');
    document.getElementById('fieldGroupXray').classList.toggle('hidden', type !== 'xray');
}

document.getElementById('addRecordForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!state.selectedPatientId) {
        showLocalToast('error', 'Select a patient before adding a record.');
        return;
    }

    const submitBtn = document.getElementById('submitRecordBtn');
    const errorBanner = document.getElementById('addRecordError');
    const errorText = document.getElementById('addRecordErrorText');
    errorBanner.classList.add('hidden');
    submitBtn.disabled = true;

    const type = document.getElementById('recordType').value;

    const formData = new FormData();
    formData.append('patient_id', state.selectedPatientId);
    formData.append('type', type);
    formData.append('title', document.getElementById('recordTitle').value.trim());
    formData.append('doctor_name', document.getElementById('recordDoctor').value.trim());
    formData.append('record_date', document.getElementById('recordDate').value);
    formData.append('details', document.getElementById('recordDetails').value.trim());

    if (type === 'note') {
        formData.append('clinical_notes', document.getElementById('recordClinicalNotes').value.trim());
    }
    if (type === 'prescription') {
        formData.append('rx_number', document.getElementById('recordRxNumber').value.trim());
    }
    if (type === 'xray') {
        const fileInput = document.getElementById('recordFile');
        if (fileInput.files[0]) formData.append('file', fileInput.files[0]);
    }

    try {
        const res = await fetch('../backend/records-create.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to save record.');

        showLocalToast('success', 'Record added successfully.');
        closeModal('addRecordModal');
        loadRecords(state.selectedPatientId);

    } catch (err) {
        console.error(err);
        errorText.textContent = err.message || 'Something went wrong. Please try again.';
        errorBanner.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
    }
});

// ── Modal helpers ──────────────────────────────────────────────────────────
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
</script>

</body>
</html>