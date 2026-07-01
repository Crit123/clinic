/**
 * dashboard-tour-config.js
 * Contains the dashboard-specific mock data, tour step definitions, and environment teardowns 
 * for DentalCare Pro. Plugs cleanly into the generic portal-tour.js engine.
 */

function injectDashboardMockData() {
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
}

// Ensure the Mock environment is active if navigating backwards from real data states
function ensureMockViewActive() {
    const popArea = document.getElementById('populatedDashboardArea');
    if (popArea && popArea.classList.contains('hidden')) {
        injectDashboardMockData();
    }
}

const dashboardTourConfig = {
    steps: [
        {
            title: 'Welcome',
            text: "Welcome to DentalCare Pro! Let's take a quick look at what your patient portal will look like once you start your smile journey.",
            target: null, // Full screen dim
            onEnter: () => {
                injectDashboardMockData();
            }
        },
        {
            title: 'The Hub',
            text: "Your Dashboard is your command center. Once you book visits, you'll easily track upcoming appointments, past visits, and overall clinical records here.",
            target: () => document.querySelector('section[aria-label="Appointment and record statistics"]'),
            onEnter: () => ensureMockViewActive()
        },
        {
            title: 'Staying on Track',
            text: "Never miss a checkup. Our intelligent system tracks your visit gaps and gently reminds you when you're due for a standard cleaning.",
            target: () => document.querySelector('section[aria-label="Treatment journey and recent activity"] > div:first-child'),
            onEnter: () => ensureMockViewActive()
        },
        {
            title: 'Explore More',
            text: () => window.matchMedia('(min-width: 768px)').matches 
                ? "Your medical history travels with you. Use the menu to access your Appointments, Dental Records, and Support Center anytime."
                : "Tap the menu icon (☰) anytime to find Appointments, Records, and Support.",
            target: () => window.matchMedia('(min-width: 768px)').matches 
                ? document.querySelector('#mainSidebar .space-y-1') 
                : document.querySelector('#mobileMenuBtn'),
            collapsibleNav: {
                isCollapsed: () => {
                    const sidebar = document.getElementById('mainSidebar');
                    return window.matchMedia('(min-width: 768px)').matches && sidebar && sidebar.classList.contains('w-20');
                },
                expand: () => {
                    if (typeof toggleSidebarCollapse === 'function') toggleSidebarCollapse();
                },
                collapse: () => {
                    if (typeof toggleSidebarCollapse === 'function') toggleSidebarCollapse();
                }
            },
            onEnter: () => ensureMockViewActive()
        },
        {
            title: 'Taking Action',
            text: "Ready to get started? Your portal is waiting. Click here to schedule your very first diagnostic evaluation.",
            target: () => {
                const emptyBtn = document.querySelector('#emptyDashboardArea a[href="book-appointment.php"]');
                if (emptyBtn && emptyBtn.offsetParent !== null) return emptyBtn;
                
                const sidebarBtn = document.querySelector('#mainSidebar a[href*="book-appointment.php"]');
                return sidebarBtn;
            },
            onEnter: async () => {
                document.getElementById('tourPreviewBadge')?.remove();
                
                if (window.isNewPatient) {
                    document.getElementById('populatedDashboardArea').classList.add('hidden', 'opacity-0');
                    document.getElementById('emptyDashboardArea').classList.remove('hidden');
                } else {
                    document.getElementById('emptyDashboardArea').classList.add('hidden');
                    document.getElementById('populatedDashboardArea').classList.add('hidden', 'opacity-0');
                    document.getElementById('skeletonLoaderArea').classList.remove('hidden');
                    
                    if (typeof window.loadDashboardData === 'function') {
                        await window.loadDashboardData();
                    }
                }
            }
        }
    ],

    onCleanup: async (currentStep) => {
        document.getElementById('tourPreviewBadge')?.remove();

        // Restore Original Dashboard Data if they quit mid-tour
        if (currentStep >= 0 && currentStep < 4) {
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
    }
};