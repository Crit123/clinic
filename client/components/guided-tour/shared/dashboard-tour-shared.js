/**
 * dashboard-tour-shared.js
 * Contains shared dashboard mock data injection and cleanup restoration logic.
 * Used identically by both compact and desktop tour concept files.
 */

window.injectDashboardMockData = function() {
    // Ideal scenario statistic counters
    document.getElementById('upcomingCountEl').textContent = '2';
    document.getElementById('completedCountEl').textContent = '4';
    document.getElementById('totalVisitsCountEl').textContent = '6';
    document.getElementById('recordsCountEl').textContent = '6';

    // Upcoming Appointment Hero Card
    document.getElementById('appointmentStatusEl').textContent = 'Confirmed';
    document.getElementById('doctorNameEl').textContent = 'Dr. Sarah Jenkins';
    document.getElementById('appointmentDateEl').innerHTML = `<span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">schedule</span> Tomorrow at 10:00 AM`;
    document.getElementById('appointmentDetailsEl').textContent = 'Your next appointment for a Routine Checkup is scheduled.';

    // Visit Frequency
    document.getElementById('lastVisitServiceEl').textContent = 'Routine Cleaning';
    document.getElementById('lastVisitDetailsEl').textContent = 'Dr. Sarah Jenkins · 5 months ago';
    
    // New Next Recommended Visit Injection
    document.getElementById('nextRecommendedServiceEl').textContent = 'Routine Cleaning';
    document.getElementById('nextRecommendedDueEl').textContent = 'Due in 35 Days';
    
    document.getElementById('daysSinceLastEl').textContent = '145';
    document.getElementById('avgGapEl').textContent = '180';

    const nudgeBadge = document.getElementById('visitNudgeBadgeEl');
    nudgeBadge.textContent = 'On Track';
    nudgeBadge.className = 'text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800';

    const nudgeBox = document.getElementById('visitNudgeMessageEl');
    nudgeBox.className = 'rounded-xl p-4 border text-sm font-body-md flex items-start gap-3 bg-emerald-50 border-emerald-200 text-emerald-800';
    document.getElementById('visitNudgeIconEl').className = 'material-symbols-outlined text-base mt-0.5 text-emerald-500';
    document.getElementById('visitNudgeIconEl').textContent = 'check_circle';
    document.getElementById('visitNudgeTextEl').textContent = "Great job! You're visiting regularly. Keep up the routine for optimal dental health.";

    // Inject Health Notes Mock Data
    const healthNotesListEl = document.getElementById('healthNotesListEl');
    if (healthNotesListEl) {
        healthNotesListEl.innerHTML = `
            <li class="p-4 rounded-xl bg-surface-container-low border-l-4 border-primary transition-all duration-200 hover:bg-surface-container-high">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary text-xl mt-0.5">dentistry</span>
                    <div>
                        <p class="text-sm text-on-surface font-semibold leading-snug">Switch to a soft-bristle brush</p>
                        <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">Dr. Jenkins recommends soft bristles to prevent receding gums around lower incisors.</p>
                        <span class="block text-[10px] text-outline font-medium uppercase mt-2">Dr. Sarah Jenkins · 1 month ago</span>
                    </div>
                </div>
            </li>
            <li class="p-4 rounded-xl bg-surface-container-low border-l-4 border-secondary transition-all duration-200 hover:bg-surface-container-high">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-secondary text-xl mt-0.5">water_drop</span>
                    <div>
                        <p class="text-sm text-on-surface font-semibold leading-snug">Enamel Care routine</p>
                        <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">Consider a gentle fluoride rinse before bed to help strengthen enamel in wear-prone areas.</p>
                        <span class="block text-[10px] text-outline font-medium uppercase mt-2">Dr. Sarah Jenkins · 1 month ago</span>
                    </div>
                </div>
            </li>
        `;
    }

    // Inject Clinic Announcements Mock Data
    const clinicAnnouncementsListEl = document.getElementById('clinicAnnouncementsListEl');
    if (clinicAnnouncementsListEl) {
        clinicAnnouncementsListEl.innerHTML = `
            <li class="p-4 rounded-xl bg-rose-50/30 border border-rose-100 transition-all duration-200 hover:bg-rose-50/50">
                <div class="flex items-start gap-3">
                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800 rounded uppercase tracking-wider mt-0.5 shrink-0">Alert</span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-rose-900">Holiday Closure Schedule</p>
                        <p class="text-xs text-rose-700/90 mt-1 leading-relaxed">The clinic will be closed from December 24th to December 26th. For urgent cases, please utilize the Emergency Care line.</p>
                    </div>
                </div>
            </li>
            <li class="p-4 rounded-xl bg-surface-container-low border border-outline-variant transition-all duration-200 hover:bg-surface-container-high">
                <div class="flex items-start gap-3">
                    <span class="relative flex h-2 w-2 mt-2 shrink-0">
                        <span class="animate-pulse absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded uppercase tracking-wider shrink-0">New</span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-on-surface">Now Offering Invisalign Aligners</p>
                        <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">Achieve your perfect smile discreetly. Ask Dr. Jenkins during your next visit if orthodontic aligners are right for you!</p>
                    </div>
                </div>
            </li>
        `;
    }

    // Inject Timeline Data
    const timelineEl = document.getElementById('activityTimelineEl');
    timelineEl.innerHTML = `
        <li class="mb-6 ml-6 relative">
            <span class="absolute flex items-center justify-center w-8 h-8 bg-surface-container-lowest rounded-full -left-10 ring-4 ring-surface-container-lowest text-surface-variant text-emerald-600">
                <span class="material-symbols-outlined text-sm" aria-hidden="true">event_available</span>
            </span>
            <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant">
                <h4 class="font-headline-sm text-sm font-bold text-on-surface mb-1">Appointment Confirmed</h4>
                <p class="font-body-sm text-xs text-on-surface-variant mb-2">Routine Checkup with Dr. Sarah Jenkins</p>
                <time class="block mb-2 text-[10px] font-medium leading-none text-outline uppercase tracking-wider">Just now</time>
            </div>
        </li>
    `;

    // Reveal populated layout and ensure conflicting states are hidden
    document.getElementById('emptyDashboardArea')?.classList.add('hidden');
    document.getElementById('skeletonLoaderArea')?.classList.add('hidden');
    document.getElementById('populatedDashboardArea')?.classList.remove('hidden', 'opacity-0');

    // Add Preview Mode Indicator
    if (!document.getElementById('tourPreviewBadge')) {
        const badge = document.createElement('div');
        badge.id = 'tourPreviewBadge';
        badge.className = 'fixed top-24 left-1/2 -translate-x-1/2 bg-amber-100 text-amber-800 border border-amber-200 px-4 py-1.5 rounded-full font-bold text-[11px] tracking-wider uppercase z-[9999] shadow-sm pointer-events-none flex items-center gap-1.5';
        badge.innerHTML = '<span class="material-symbols-outlined text-[14px]">visibility</span> Preview Mode';
        document.body.appendChild(badge);
    }
};

window.ensureMockViewActive = function() {
    const popArea = document.getElementById('populatedDashboardArea');
    if (popArea && popArea.classList.contains('hidden')) {
        window.injectDashboardMockData();
    }
};

window.handleTourCleanup = async function(currentStep) {
    document.getElementById('tourPreviewBadge')?.remove();

    // Restore Original Dashboard Data if they quit mid-tour
    // Renumbered steps bound: "Taking Action" is now step index 7, so cleanup if < 7
    if (currentStep >= 0 && currentStep < 7) {
        if (!window.isNewPatient) {
            document.getElementById('emptyDashboardArea').classList.add('hidden');
            document.getElementById('populatedDashboardArea').classList.add('hidden', 'opacity-0');
            document.getElementById('skeletonLoaderArea').classList.remove('hidden');
            if (typeof window.loadDashboardData === 'function') {
                await window.loadDashboardData();
            }
        } else {
            document.getElementById('populatedDashboardArea').classList.add('hidden', 'opacity-0');
            document.getElementById('emptyDashboardArea').classList.remove('hidden');
        }
    }
};