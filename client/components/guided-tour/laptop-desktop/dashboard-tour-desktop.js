/**
 * dashboard-tour-desktop.js
 * Tour configuration step definitions for desktop and laptop layouts (>=1024px).
 * Standardized 8-step sequential loop supporting corrected dashboard items.
 */

window.dashboardTourConfig = {
    steps: [
        {
            title: 'Welcome',
            text: "Welcome to DentalCare Pro! Let's take a quick look at what your patient portal will look like once you start your smile journey.",
            target: null, // Full screen dim
            onEnter: () => window.injectDashboardMockData()
        },
        {
            title: 'Quick Actions',
            text: "Jump straight to what you need — Emergency Care, your Preparation Center, Profile Settings, or Support, all in one place.",
            // BUG FIX: Targeting the inner ID instead of the outer section for a much cleaner spotlight boundary
            target: () => document.querySelector('#quickActionsContainerEl'),
            onEnter: () => window.ensureMockViewActive()
        },
        {
            title: 'The Hub',
            text: "Your Dashboard is your command center. Once you book visits, you'll easily track upcoming appointments, past visits, and overall clinical records here.",
            target: () => document.querySelector('section[aria-label="Appointment and record statistics"]'),
            onEnter: () => window.ensureMockViewActive()
        },
        {
            title: 'Staying on Track',
            text: "Never miss a checkup. Our intelligent system tracks your visit gaps and gently reminds you when you're due for a standard cleaning.",
            target: () => document.querySelector('section[aria-label="Care plan and frequency tracking"] > div:last-child'),
            onEnter: () => window.ensureMockViewActive()
        },
        {
            title: 'Personalized Care',
            text: "Your dentist can leave specific notes, recommendations, and oral care reminders here after your visits to help you maintain a perfect smile at home.",
            target: () => document.querySelector('div[aria-label="Personal Health Notes"]'),
            onEnter: () => window.ensureMockViewActive()
        },
        {
            title: 'Stay in the Loop',
            text: "Clinic-wide updates, closures, and new services appear here so you're always informed.",
            target: () => document.querySelector('div[aria-label="Clinic Announcements"]'),
            onEnter: () => window.ensureMockViewActive()
        },
        {
            title: 'Explore More',
            text: "Your medical history travels with you. Use the menu to access your Appointments, Dental Records, and Support Center anytime.",
            target: () => document.querySelector('#mainSidebar .space-y-1'), // No branching needed for >=1024px
            collapsibleNav: {
                isCollapsed: () => {
                    const sidebar = document.getElementById('mainSidebar');
                    return sidebar && sidebar.classList.contains('w-20');
                },
                expand: () => {
                    if (typeof toggleSidebarCollapse === 'function') toggleSidebarCollapse();
                },
                collapse: () => {
                    if (typeof toggleSidebarCollapse === 'function') toggleSidebarCollapse();
                }
            },
            onEnter: () => window.ensureMockViewActive()
        },
        {
            title: 'Taking Action',
            text: "Ready to get started? Your portal is waiting. Click here to schedule your very first diagnostic evaluation.",
            target: () => {
                const emptyBtn = document.querySelector('#emptyDashboardArea a[href="book-appointment.php"]');
                if (emptyBtn && emptyBtn.offsetParent !== null) return emptyBtn;
                return document.querySelector('#mainSidebar a[href*="book-appointment.php"]');
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
        await window.handleTourCleanup(currentStep);
    }
};