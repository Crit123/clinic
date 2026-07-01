<?php
/**
 * dental-records.php
 * Patient Dental Records Directory for DentalCare Pro.
 * Houses radiological imaging (X-rays), clinical treatment notes, and digital prescriptions.
 */

// 1. We require design-config manually at the top so $APP_ENV is available for our logic
require_once __DIR__ . '/../components/design-config.php';

// 2. Set the variables the layout shell needs
$activePage = 'records';
$pageTitle  = 'Dental Records';

// Determine current initial category filter via PHP query parameter
$currentFilter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
if (!in_array($currentFilter, ['all', 'xray', 'note', 'prescription'])) {
    $currentFilter = 'all';
}

// Pre-defined records dataset — fully integrated with appointments.php clinical notes and treatments
$records = [
    [
        'id'          => 'rec-101',
        'title'       => 'Invisalign Progress 3D Scan',
        'type'        => 'xray',
        'display_date'=> 'Oct 24, 2026',
        'appointment' => 'From your Oct 24 Invisalign Progress Check',
        'card_icon'   => 'photo_library',
        'icon_color'  => 'text-indigo-600 bg-indigo-50 border-indigo-100',
        'details'     => 'Intraoral iTero 3D digital scan modeling progressive alignment shift.',
        'doctor'      => 'Dr. Maria Santos',
        'notes'       => '',
        'download_file'=> 'invisalign-progress-scan-2026.pdf'
    ],
    [
        'id'          => 'rec-102',
        'title'       => 'Full Mouth Panoramic Dental X-Ray',
        'type'        => 'xray',
        'display_date'=> 'Sep 14, 2026',
        'appointment' => 'From your Sep 14 Invisalign Initial Fitting',
        'card_icon'   => 'settings_overscan',
        'icon_color'  => 'text-indigo-600 bg-indigo-50 border-indigo-100',
        'details'     => 'Comprehensive low-dose digital panoramic radiograph of both dental arches.',
        'doctor'      => 'Dr. Maria Santos',
        'notes'       => '',
        'download_file'=> 'panoramic-xray-sept2026.png'
    ],
    [
        'id'          => 'rec-103',
        'title'       => 'Clinical Notes: Invisalign Initial Fitting',
        'type'        => 'note',
        'display_date'=> 'Sep 14, 2026',
        'appointment' => 'From your Sep 14 Invisalign Initial Fitting',
        'card_icon'   => 'description',
        'icon_color'  => 'text-blue-600 bg-blue-50 border-blue-100',
        'details'     => 'Orthodontic diagnostic analysis and tray installation summary notes.',
        'doctor'      => 'Dr. Maria Santos',
        'notes'       => 'Initial Invisalign tray fitting completed successfully. Attachments are securely bonded on teeth 6, 8, and 11. Patient demonstrated good technique for tray insertion and removal. Scheduled progress checks.',
        'next_action' => 'Progress review in 4 weeks.',
        'download_file'=> 'clinical-notes-fitting.pdf'
    ],
    [
        'id'          => 'rec-104',
        'title'       => 'Clinical Notes: Professional Laser Whitening',
        'type'        => 'note',
        'display_date'=> 'Aug 20, 2026',
        'appointment' => 'From your Aug 20 Professional Laser Whitening',
        'card_icon'   => 'description',
        'icon_color'  => 'text-blue-600 bg-blue-50 border-blue-100',
        'details'     => 'In-office therapeutic summary and baseline shade evaluation documentation.',
        'doctor'      => 'Dr. Maria Santos',
        'notes'       => 'Completed in-office professional whitening. Initial shade A3, final shade achieved B1. Post-op instructions given. Recommended sensitivity toothpaste for next 48 hours.',
        'next_action' => 'Follow white diet rules for 48 hours; next checkup in 6 months.',
        'download_file'=> 'whitening-notes-aug2026.pdf'
    ],
    [
        'id'          => 'rec-105',
        'title'       => 'Post-Operative Prescription: Ibuprofen 600mg',
        'type'        => 'prescription',
        'display_date'=> 'Jun 05, 2026',
        'appointment' => 'From your Jun 05 Composite Filling & Polish',
        'card_icon'   => 'medication',
        'icon_color'  => 'text-rose-600 bg-rose-50 border-rose-100',
        'details'     => 'Rx: Ibuprofen 600mg. Dispense 15 tablets. Take 1 tablet by mouth every 6 hours as needed for mild to moderate discomfort.',
        'doctor'      => 'Dr. Maria Santos',
        'rx_number'   => 'Rx #984120',
        'notes'       => '',
        'download_file'=> 'rx-ibuprofen-filling.pdf'
    ],
    [
        'id'          => 'rec-106',
        'title'       => 'Clinical Notes: Composite Filling & Polish',
        'type'        => 'note',
        'display_date'=> 'Jun 05, 2026',
        'appointment' => 'From your Jun 05 Composite Filling & Polish',
        'card_icon'   => 'description',
        'icon_color'  => 'text-blue-600 bg-blue-50 border-blue-100',
        'details'     => 'Cavity preparation, restorative composite lining, and occlusion analysis.',
        'doctor'      => 'Dr. Maria Santos',
        'notes'       => 'Administered local anesthetic. Restored tooth 14-DO with composite resin. Restored margins are smooth, occlusion is balanced and verified with articulating paper.',
        'next_action' => 'Avoid extremely cold beverages for 24 hours; next checkup in 6 months.',
        'download_file'=> 'filling-notes-jun2026.pdf'
    ]
];

// 3. Start intercepting standard output (Output Buffering)
ob_start();
?>

<style>
    /* Completely removes the top NProgress-style transition loading bar under the domain */
    #nprogress-bar {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        height: 0px !important;
    }
</style>

<!-- DIAGNOSTIC PANEL (DEVELOPMENT ONLY) -->
<?php if (APP_ENV === 'development'): ?>
<section class="bg-surface-container-low rounded-2xl p-4 border border-outline-variant flex flex-wrap gap-4 items-center justify-between"
         aria-label="Developer diagnostic panel">
    <div class="space-y-1">
        <h4 class="font-headline-md text-sm font-semibold text-primary">Dental Records Diagnostic Console
            <span class="ml-2 text-[10px] bg-amber-100 text-amber-800 px-2 py-0.5 rounded font-bold uppercase tracking-wide">Dev only</span>
        </h4>
        <p class="font-body-sm text-xs text-on-surface-variant">Test client-side category filtering, record searches, diagnostic loaders, and adaptive layouts.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button onclick="simulateRecLoading()"
                class="bg-surface-container-highest hover:bg-surface-variant text-primary font-label-md text-xs py-2 px-4 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary flex items-center">
            <span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">hourglass_empty</span> Simulate Loader
        </button>
        <button onclick="forceEmptyRecordsToggle()"
                class="bg-surface-container-highest hover:bg-surface-variant text-primary font-label-md text-xs py-2 px-4 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary flex items-center">
            <span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">auto_delete</span> Force Empty State
        </button>
    </div>
</section>
<?php endif; ?>

<!-- INTRO HERO BLOCK -->
<div class="fade-in flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1.5">
            <span class="material-symbols-outlined text-secondary text-sm">folder_shared</span>
            <span class="font-label-sm text-xs font-semibold text-secondary-text uppercase tracking-widest">Medical History & Assets</span>
        </div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-primary">Dental Records</h2>
        <p class="font-body-md text-on-surface-variant mt-1">X-rays, treatment notes, and prescriptions from your visits, all in one place.</p>
    </div>
    <!-- Total count badge -->
    <div class="inline-flex items-center gap-2.5 bg-[#e8f0fb] px-4 py-3 rounded-xl border border-blue-100 text-[#1652a0] font-bold text-xs">
        <span class="material-symbols-outlined text-lg leading-none" aria-hidden="true">folder_open</span>
        <span>Active Documents: <span id="recordsTotalBadge"><?php echo count($records); ?></span></span>
    </div>
</div>

<!-- RE-ARRANGEABLE SEARCH & FILTER TABS BAR -->
<div class="fade-in delay-100 bg-white p-4 rounded-2xl border border-slate-100 shadow-[0_4px_16px_rgba(0,71,141,0.02)] flex flex-col md:flex-row md:items-center justify-between gap-4">
    
    <!-- Accessible Navigation Links styled as Pill Tabs -->
    <nav class="flex space-x-1 bg-slate-50 p-1 rounded-xl w-full md:w-auto overflow-x-auto scrollbar-none" role="tablist" aria-label="Dental Record Categories">
        <a id="tab-all"
           href="dental-records.php?filter=all"
           onclick="setRecordFilter('all', event)"
           role="tab"
           aria-selected="<?php echo $currentFilter === 'all' ? 'true' : 'false'; ?>"
           aria-controls="records-list-wrapper"
           class="tab-pill whitespace-nowrap px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 <?php echo $currentFilter === 'all' ? 'bg-[#e8f0fb] text-[#1652a0] shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'; ?>">
            All Records
        </a>
        <a id="tab-xray"
           href="dental-records.php?filter=xray"
           onclick="setRecordFilter('xray', event)"
           role="tab"
           aria-selected="<?php echo $currentFilter === 'xray' ? 'true' : 'false'; ?>"
           aria-controls="records-list-wrapper"
           class="tab-pill whitespace-nowrap px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 <?php echo $currentFilter === 'xray' ? 'bg-[#e8f0fb] text-[#1652a0] shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'; ?>">
            X-Rays & Scans
        </a>
        <a id="tab-note"
           href="dental-records.php?filter=note"
           onclick="setRecordFilter('note', event)"
           role="tab"
           aria-selected="<?php echo $currentFilter === 'note' ? 'true' : 'false'; ?>"
           aria-controls="records-list-wrapper"
           class="tab-pill whitespace-nowrap px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 <?php echo $currentFilter === 'note' ? 'bg-[#e8f0fb] text-[#1652a0] shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'; ?>">
            Treatment Notes
        </a>
        <a id="tab-prescription"
           href="dental-records.php?filter=prescription"
           onclick="setRecordFilter('prescription', event)"
           role="tab"
           aria-selected="<?php echo $currentFilter === 'prescription' ? 'true' : 'false'; ?>"
           aria-controls="records-list-wrapper"
           class="tab-pill whitespace-nowrap px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 <?php echo $currentFilter === 'prescription' ? 'bg-[#e8f0fb] text-[#1652a0] shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'; ?>">
            Prescriptions
        </a>
    </nav>

    <!-- Right Align Container: Search Badge + Manual Refresh Trigger -->
    <div class="flex items-center justify-between md:justify-end gap-3 w-full md:w-auto">
        <div id="searchBadgeIndicator" class="hidden flex items-center bg-blue-50 text-blue-700 px-3.5 py-1.5 rounded-xl border border-blue-100 text-xs font-bold">
            <span class="material-symbols-outlined text-sm mr-1.5" aria-hidden="true">search</span>
            Filtered: "<span id="searchBadgeQuery"></span>"
            <button onclick="clearRecordsSearch()" class="ml-2 hover:text-blue-900 focus:outline-none" aria-label="Clear record search">
                <span class="material-symbols-outlined text-xs align-middle">close</span>
            </button>
        </div>

        <button onclick="simulateRecLoading()"
                class="p-2.5 bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-[#1652a0] rounded-xl border border-slate-100 transition-all flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                title="Refresh Documents"
                aria-label="Refresh dental records list">
            <span class="material-symbols-outlined text-lg leading-none select-none" aria-hidden="true">refresh</span>
        </button>
    </div>
</div>

<!-- SKELETON SHIMMER LOADER -->
<div id="skeletonLoaderArea" class="hidden space-y-6" aria-hidden="true">
    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6 h-36 animate-shimmer"></div>
    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6 h-36 animate-shimmer"></div>
</div>

<!-- MAIN RECORDS WRAPPER -->
<div id="records-list-wrapper" class="space-y-6 transition-opacity duration-300">
    
    <div id="recordsCardGrid" class="grid grid-cols-1 gap-6">
        <?php foreach ($records as $rec): ?>
            <!-- Record Card matching design style of appointments.php -->
            <div class="record-card bg-surface-container-lowest rounded-2xl p-6 shadow-[0_4px_16px_rgba(0,71,141,0.04)] hover:shadow-[0_8px_24px_rgba(0,71,141,0.08)] hover:-translate-y-0.5 border border-slate-100 transition-all duration-300 flex flex-col lg:flex-row lg:items-center justify-between gap-6"
                 data-type="<?php echo $rec['type']; ?>"
                 data-title="<?php echo htmlspecialchars(strtolower($rec['title'])); ?>"
                 data-details="<?php echo htmlspecialchars(strtolower($rec['details'])); ?>"
                 data-appointment="<?php echo htmlspecialchars(strtolower($rec['appointment'])); ?>">
                
                <!-- Left Information Stack -->
                <div class="flex items-start space-x-5 flex-1">
                    
                    <!-- Category-specific icon block -->
                    <div class="w-14 h-14 rounded-2xl border flex items-center justify-center flex-shrink-0 <?php echo $rec['icon_color']; ?>">
                        <span class="material-symbols-outlined text-3xl" aria-hidden="true"><?php echo $rec['card_icon']; ?></span>
                    </div>

                    <div class="space-y-1 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <!-- Dynamic uppercase tag for record categories -->
                            <span class="text-[11px] uppercase tracking-wider text-secondary-text font-bold px-2 py-0.5 bg-slate-50 rounded border border-slate-100">
                                <?php 
                                    if ($rec['type'] === 'xray') echo 'X-Ray / Scan';
                                    else if ($rec['type'] === 'note') echo 'Treatment Note';
                                    else if ($rec['type'] === 'prescription') echo 'Rx Prescription';
                                ?>
                            </span>
                            <span class="text-[11px] uppercase tracking-wider text-[#1652a0] font-bold px-2 py-0.5 bg-blue-50/50 rounded border border-blue-100/30 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[10px]" aria-hidden="true">lock</span> Digital Sign-off
                            </span>
                        </div>

                        <h3 class="font-headline-md text-lg text-slate-800 font-bold"><?php echo htmlspecialchars($rec['title']); ?></h3>
                        
                        <!-- Meta fields -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-tertiary-text font-medium pt-1">
                            <span class="flex items-center text-slate-700">
                                <span class="material-symbols-outlined text-base mr-1.5 text-slate-400" aria-hidden="true">calendar_today</span>
                                <time><?php echo $rec['display_date']; ?></time>
                            </span>
                            <span class="flex items-center text-slate-700">
                                <span class="material-symbols-outlined text-base mr-1.5 text-slate-400" aria-hidden="true">badge</span>
                                <?php echo htmlspecialchars($rec['doctor']); ?>
                            </span>
                        </div>

                        <!-- Connected Appointment linkage referencing appointments.php -->
                        <p class="text-xs text-slate-600 flex items-center gap-1.5 mt-2.5">
                            <span class="material-symbols-outlined text-sm text-indigo-500">link</span>
                            <span><?php echo htmlspecialchars($rec['appointment']); ?></span>
                        </p>
                    </div>
                </div>

                <!-- Action Controls on the Right -->
                <div class="flex flex-col sm:flex-row lg:flex-col items-start sm:items-center lg:items-end justify-between lg:justify-center gap-3 border-t lg:border-t-0 border-slate-50 pt-4 lg:pt-0">
                    <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                        <button onclick="previewDocument('<?php echo $rec['id']; ?>')"
                                class="flex-1 sm:flex-initial px-5 py-2.5 bg-[#e8f0fb] hover:bg-[#d0e3fc] text-[#1652a0] font-bold text-xs rounded-xl transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                            <span>View Record</span>
                        </button>
                        <button onclick="triggerFileDownload('<?php echo htmlspecialchars($rec['download_file'], ENT_QUOTES); ?>')"
                                class="flex-1 sm:flex-initial px-5 py-2.5 border border-outline-variant hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            <span class="material-symbols-outlined text-[16px]">download</span>
                            <span>Download</span>
                        </button>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- EMPTY STATES SECTION -->
    <div id="emptyRecordsArea" class="hidden transition-all duration-300">
        <div class="bg-surface-container-lowest rounded-2xl p-8 text-center border border-slate-100 shadow-[0_4px_16px_rgba(0,0,0,0.02)] max-w-2xl mx-auto py-16">
            <div class="mx-auto flex justify-center mb-6" aria-hidden="true">
                <span class="material-symbols-outlined text-slate-300 text-7xl select-none">folder_off</span>
            </div>
            <h3 id="emptyStateTitle" class="font-headline-lg text-xl text-primary mb-3">No Dental Records Found</h3>
            <p id="emptyStateDescription" class="font-body-md text-sm text-on-surface-variant max-w-md mx-auto mb-8">
                No clinical reports, diagnostic x-rays, or prescriptions currently exist in this category matching your criteria.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="clearRecordsFilters()"
                        class="bg-primary hover:bg-primary-container text-on-primary py-3 px-6 rounded-xl font-bold text-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    Reset Category Filters
                </button>
            </div>
        </div>
    </div>

</div>

<!-- DOCUMENT PREVIEW MODAL -->
<div id="recordPreviewModal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalTitle"
     class="hidden fixed inset-0 z-50 bg-[#001e31]/40 backdrop-blur-sm flex items-center justify-center p-4">
    
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-[0_24px_64px_rgba(0,0,0,0.16)] border border-slate-100 overflow-hidden transform transition-all scale-95 opacity-0 duration-300"
         id="modalPanel">
        
        <div class="p-6">
            <!-- Modal header -->
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-4">
                <div>
                    <span id="previewCategoryTag" class="text-[10px] uppercase tracking-wider text-secondary-text font-bold px-2 py-0.5 bg-slate-100 rounded border border-slate-200">Category</span>
                    <h3 id="modalTitle" class="font-headline-md text-lg text-primary font-bold mt-1.5">Record Preview</h3>
                </div>
                <button onclick="closeRecordModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none" aria-label="Close record preview">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <!-- PREVIEW CONTAINER BODY -->
            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
                
                <!-- X-Ray Visual Placeholder Element -->
                <div id="previewXrayContent" class="hidden space-y-4">
                    <p class="text-xs text-secondary-text">Diagnostic dental imaging authorized for clinical viewing on this device.</p>
                    <!-- Structured high-fidelity SVG graphic for radiograph mock -->
                    <div class="w-full bg-slate-900 rounded-xl border border-slate-800 p-4 relative overflow-hidden flex flex-col justify-between" style="min-height: 280px;">
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                            <span>PATIENT: JOHNSON, ALEX</span>
                            <span>IMAGE ID: XRP-94021-B</span>
                        </div>
                        
                        <!-- Simulated Orthodontic Orthopantomogram diagram overlay -->
                        <div class="flex-1 flex items-center justify-center py-6 opacity-80">
                            <svg class="w-full max-w-md h-32 stroke-blue-400 fill-none" viewBox="0 0 400 100">
                                <path stroke-dasharray="3,3" stroke="#475569" d="M 10 50 Q 200 110, 390 50" />
                                <!-- Teeth approximations -->
                                <path d="M 50 40 Q 50 60, 55 58 M 70 38 Q 72 61, 75 58 M 90 39 Q 93 62, 95 59 M 110 40 Q 112 62, 116 59 M 130 42 Q 131 63, 137 60 M 150 44 Q 152 64, 159 61" />
                                <path d="M 350 40 Q 350 60, 345 58 M 330 38 Q 328 61, 325 58 M 310 39 Q 307 62, 305 59 M 290 40 Q 288 62, 284 59 M 270 42 Q 269 63, 263 60 M 250 44 Q 248 64, 241 61" />
                                <path d="M 180 47 Q 182 66, 189 63 M 200 48 Q 200 67, 203 64 M 220 47 Q 218 66, 211 63" />
                                <!-- Mandible bone tracing -->
                                <path stroke-width="1.5" stroke="#1e293b" d="M 15 25 C 40 95, 360 95, 385 25" />
                                <!-- Standard medical cross -->
                                <g transform="translate(190, 10) scale(0.6)" class="stroke-slate-700">
                                    <line x1="10" y1="0" x2="10" y2="20" stroke-width="3"/>
                                    <line x1="0" y1="10" x2="20" y2="10" stroke-width="3"/>
                                </g>
                            </svg>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono">
                            <span>DR. SANTOS CLINICAL UNIT</span>
                            <span>CONTRAST: COMPLIANT</span>
                        </div>
                    </div>
                </div>

                <!-- Clinical Treatment Notes Text -->
                <div id="previewNotesContent" class="hidden space-y-4">
                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 flex gap-3">
                        <span class="material-symbols-outlined text-[#1652a0]">clinical_notes</span>
                        <div>
                            <p class="text-xs font-bold text-[#1652a0] uppercase tracking-wide">Attending Practitioner</p>
                            <p class="text-sm text-slate-800 font-bold" id="previewNotesDoctor">Dr. Maria Santos</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wide">Summary Narrative</h4>
                        <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100 italic" id="previewNotesText">
                            Treatment plan summary description text goes here.
                        </p>
                    </div>
                    <div class="space-y-1 pt-1" id="previewNotesActionWrapper">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wide">Next Recommended Action</h4>
                        <p class="text-sm text-slate-800 font-semibold" id="previewNotesNextAction">None planned.</p>
                    </div>
                </div>

                <!-- Prescription Simple Detail Content -->
                <div id="previewPrescriptionContent" class="hidden space-y-4">
                    <div class="bg-rose-50 border border-rose-100 rounded-xl p-5 relative overflow-hidden">
                        <!-- Background watermark Rx sign -->
                        <div class="absolute right-4 bottom-1 text-rose-100/50 text-8xl font-serif font-bold select-none pointer-events-none">Rx</div>
                        
                        <div class="border-b border-rose-100 pb-3 mb-4 flex justify-between items-center text-xs font-semibold text-rose-800">
                            <span>PRESCRIPTION DETAILS</span>
                            <span id="previewRxNumber">Rx #904221</span>
                        </div>

                        <div class="space-y-3 relative">
                            <div>
                                <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Doctor Signature</span>
                                <span class="text-sm font-bold text-slate-800">Dr. Maria Santos, DDS</span>
                            </div>
                            <div class="border-t border-rose-100/40 my-2"></div>
                            <div>
                                <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Instruction & Dosage</span>
                                <p class="text-base font-bold text-slate-800" id="previewRxInstruction">Ibuprofen 600mg</p>
                                <p class="text-xs text-slate-600 mt-1" id="previewRxDetails">Dispense 15 tablets. Take 1 tablet by mouth every 6 hours as needed for discomfort.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600">
                        <span class="material-symbols-outlined text-sm text-slate-400">info</span>
                        <span>This prescription has been transmitted to your designated local pharmacy directly.</span>
                    </div>
                </div>

                <!-- Global linked appointment reference metadata -->
                <div class="border-t border-slate-100 pt-4 mt-2">
                    <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-500 uppercase tracking-wider">Linked Treatment Visit</span>
                        <span class="font-medium text-slate-800" id="previewLinkedAppointment"></span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal action triggers -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-500" id="previewDateBadge">File Date: Oct 24, 2026</span>
            <div class="flex items-center space-x-2">
                <button onclick="closeRecordModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    Close
                </button>
                <button id="modalActionBtn" onclick="submitDownloadAction()" class="px-4 py-2 text-xs font-bold text-white bg-[#1652a0] hover:bg-primary-container rounded-lg transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">download</span> Download PDF
                </button>
            </div>
        </div>

    </div>
</div>

<!-- APPOINTMENTS PAGE INTERACTION CONTROLLER -->
<script>
let activeRecFilter = "<?php echo $currentFilter; ?>";
let activeRecQuery = "";
let selectedRecordId = "";
let recordsData = <?php echo json_encode($records); ?>;

document.addEventListener('DOMContentLoaded', () => {
    applyRecordsFilters();

    // Tie-in header search live input directly to Records page filtering
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterRecordsByQuery(e.target.value);
        });
        searchInput.addEventListener('focus', () => {
            searchInput.placeholder = "Search dental records (e.g. X-Ray)...";
        });
    }

    // Capture global search execution triggers
    const originalExecuteSearch = window.executeSearch;
    window.executeSearch = function(query) {
        if (originalExecuteSearch) originalExecuteSearch(query);
        filterRecordsByQuery(query);
    };
});

function applyRecordsFilters() {
    const cards = document.querySelectorAll('.record-card');
    const container = document.getElementById('recordsCardGrid');
    const emptyArea = document.getElementById('emptyRecordsArea');
    const query = activeRecQuery.trim().toLowerCase();

    let visibleCount = 0;

    cards.forEach(card => {
        const cardType = card.dataset.type;
        const title = card.dataset.title;
        const details = card.dataset.details;
        const linkedAppt = card.dataset.appointment || "";

        const matchesTab = (activeRecFilter === 'all') || (cardType === activeRecFilter);
        const matchesQuery = !query || title.includes(query) || details.includes(query) || linkedAppt.includes(query);

        if (matchesTab && matchesQuery) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    if (visibleCount === 0) {
        container.classList.add('hidden');
        emptyArea.classList.remove('hidden');
        updateEmptyRecordsMessage();
    } else {
        container.classList.remove('hidden');
        emptyArea.classList.add('hidden');
    }

    // Render active state on tab pills
    document.querySelectorAll('.tab-pill').forEach(pill => {
        const isSelected = pill.id === `tab-${activeRecFilter}`;
        pill.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        if (isSelected) {
            pill.className = "tab-pill px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 bg-[#e8f0fb] text-[#1652a0] shadow-sm";
        } else {
            pill.className = "tab-pill px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-200 text-slate-500 hover:text-slate-800 hover:bg-slate-100/50";
        }
    });
}

function updateEmptyRecordsMessage() {
    const titleEl = document.getElementById('emptyStateTitle');
    const descEl = document.getElementById('emptyStateDescription');

    if (activeRecQuery) {
        titleEl.textContent = "No Search Results Found";
        descEl.textContent = `We couldn't find any documents or clinic records matching "${activeRecQuery}".`;
        return;
    }

    switch(activeRecFilter) {
        case 'xray':
            titleEl.textContent = "No Orthodontic X-Rays Yet";
            descEl.textContent = "Your digital panoramic imaging scans and diagnostic 3D assets will appear here immediately after your medical sign-off.";
            break;
        case 'note':
            titleEl.textContent = "No Clinical Notes Available";
            descEl.textContent = "Your past treatment diaries and clinical consultation notes written by Dr. Santos are compiled here.";
            break;
        case 'prescription':
            titleEl.textContent = "No Active Prescriptions";
            descEl.textContent = "Digital medication authorization logs and pharmacotherapy instructions are not found in your medical profile.";
            break;
        default:
            titleEl.textContent = "No Dental Records Found";
            descEl.textContent = "Your healthcare files timeline is empty. Schedule your diagnostic evaluation to start accumulating clinical notes.";
            break;
    }
}

function setRecordFilter(filter, event) {
    if (event) event.preventDefault();
    activeRecFilter = filter;
    applyRecordsFilters();
}

function filterRecordsByQuery(query) {
    activeRecQuery = query;
    const badge = document.getElementById('searchBadgeIndicator');
    const badgeText = document.getElementById('searchBadgeQuery');

    if (query.trim()) {
        badge.classList.remove('hidden');
        badge.classList.add('inline-flex');
        badgeText.textContent = query;
    } else {
        badge.classList.remove('inline-flex');
        badge.classList.add('hidden');
    }
    applyRecordsFilters();
}

function clearRecordsSearch() {
    activeRecQuery = "";
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = "";
    document.getElementById('searchBadgeIndicator').classList.add('hidden');
    applyRecordsFilters();
}

function clearRecordsFilters() {
    clearRecordsSearch();
    activeRecFilter = 'all';
    applyRecordsFilters();
}

function simulateRecLoading() {
    const skeleton = document.getElementById('skeletonLoaderArea');
    const content = document.getElementById('records-list-wrapper');
    content.classList.add('opacity-0', 'hidden');
    skeleton.classList.remove('hidden');
    
    setTimeout(() => {
        skeleton.classList.add('hidden');
        content.classList.remove('hidden', 'opacity-0');
        showGlobalToast('success', 'Dental records synced with clinical server database.');
    }, 1100);
}

function forceEmptyRecordsToggle() {
    const cards = document.querySelectorAll('.record-card');
    const container = document.getElementById('recordsCardGrid');
    const emptyArea = document.getElementById('emptyRecordsArea');
    
    if (emptyArea.classList.contains('hidden')) {
        cards.forEach(card => card.classList.add('hidden'));
        container.classList.add('hidden');
        emptyArea.classList.remove('hidden');
    } else {
        applyRecordsFilters();
    }
}

function triggerFileDownload(filename) {
    showGlobalToast('info', `Initializing download sequence: ${filename}...`);
    setTimeout(() => {
        showGlobalToast('success', `File transfer complete. "${filename}" saved successfully.`);
    }, 1400);
}

/* Modal Preview Management */
function previewDocument(recordId) {
    const record = recordsData.find(r => r.id === recordId);
    if (!record) return;

    selectedRecordId = recordId;

    // Reset preview states
    document.getElementById('previewXrayContent').classList.add('hidden');
    document.getElementById('previewNotesContent').classList.add('hidden');
    document.getElementById('previewPrescriptionContent').classList.add('hidden');

    // Load static contents dynamically
    document.getElementById('modalTitle').textContent = record.title;
    document.getElementById('previewDateBadge').textContent = `File Date: ${record.display_date}`;
    document.getElementById('previewLinkedAppointment').textContent = record.appointment;

    // Populate category indicators
    const tag = document.getElementById('previewCategoryTag');
    if (record.type === 'xray') {
        tag.textContent = 'Radiograph / Diagnostic Scan';
        tag.className = 'text-[10px] uppercase tracking-wider text-indigo-700 font-bold px-2 py-0.5 bg-indigo-50 rounded border border-indigo-100';
        document.getElementById('previewXrayContent').classList.remove('hidden');
    } else if (record.type === 'note') {
        tag.textContent = 'Doctor Treatment Notes';
        tag.className = 'text-[10px] uppercase tracking-wider text-blue-700 font-bold px-2 py-0.5 bg-blue-50 rounded border border-blue-100';
        document.getElementById('previewNotesDoctor').textContent = record.doctor;
        document.getElementById('previewNotesText').textContent = `"${record.notes}"`;
        if (record.next_action) {
            document.getElementById('previewNotesNextAction').textContent = record.next_action;
            document.getElementById('previewNotesActionWrapper').classList.remove('hidden');
        } else {
            document.getElementById('previewNotesActionWrapper').classList.add('hidden');
        }
        document.getElementById('previewNotesContent').classList.remove('hidden');
    } else if (record.type === 'prescription') {
        tag.textContent = 'E-Prescription Records';
        tag.className = 'text-[10px] uppercase tracking-wider text-rose-700 font-bold px-2 py-0.5 bg-rose-50 rounded border border-rose-100';
        document.getElementById('previewRxNumber').textContent = record.rx_number || 'Rx #';
        document.getElementById('previewRxInstruction').textContent = record.title;
        document.getElementById('previewRxDetails').textContent = record.details;
        document.getElementById('previewPrescriptionContent').classList.remove('hidden');
    }

    const modal = document.getElementById('recordPreviewModal');
    const panel = document.getElementById('modalPanel');
    modal.classList.remove('hidden');
    setTimeout(() => {
        panel.classList.remove('scale-95', 'opacity-0');
        panel.classList.add('scale-100', 'opacity-100');
    }, 50);
}

function closeRecordModal() {
    const modal = document.getElementById('recordPreviewModal');
    const panel = document.getElementById('modalPanel');
    panel.classList.remove('scale-100', 'opacity-100');
    panel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

function submitDownloadAction() {
    const record = recordsData.find(r => r.id === selectedRecordId);
    if (record) {
        closeRecordModal();
        triggerFileDownload(record.download_file);
    }
}
</script>

<?php
// 4. Close the buffer and save everything captured so far into $pageContent
$pageContent = ob_get_clean();

// 5. Require the layout shell, which will handle wrapping $pageContent
require_once __DIR__ . '/../components/layout/main-layout.php';
?>