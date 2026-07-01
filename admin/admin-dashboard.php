<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Portal - DentalCare Pro</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-background": "#0b1c30",
                        "on-primary-fixed": "#001b3d",
                        "surface-container-high": "#dce9ff",
                        "on-secondary-fixed": "#191c1e",
                        "on-surface": "#0b1c30",
                        "secondary-fixed-dim": "#c4c7c9",
                        "on-primary-fixed-variant": "#00468c",
                        "secondary": "#5c5f61",
                        "surface-container-lowest": "#ffffff",
                        "background": "#f8f9ff",
                        "surface-tint": "#005db6",
                        "on-tertiary-fixed": "#002113",
                        "error": "#ba1a1a",
                        "primary-container": "#005eb8",
                        "inverse-primary": "#a9c7ff",
                        "surface-container": "#e5eeff",
                        "on-tertiary-container": "#65f2b5",
                        "surface-container-highest": "#d3e4fe",
                        "outline": "#727783",
                        "on-primary": "#ffffff",
                        "outline-variant": "#c2c6d4",
                        "inverse-on-surface": "#eaf1ff",
                        "primary-fixed-dim": "#a9c7ff",
                        "surface-bright": "#f8f9ff",
                        "on-primary-container": "#c8daff",
                        "on-tertiary-fixed-variant": "#005236",
                        "primary": "#00478d",
                        "error-container": "#ffdad6",
                        "on-secondary": "#ffffff",
                        "surface-container-low": "#eff4ff",
                        "tertiary-fixed": "#6ffbbe",
                        "on-surface-variant": "#424752",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed-dim": "#4edea3",
                        "tertiary": "#005237",
                        "surface": "#f8f9ff",
                        "on-error-container": "#93000a",
                        "surface-variant": "#d3e4fe",
                        "surface-dim": "#cbdbf5",
                        "on-secondary-fixed-variant": "#444749",
                        "primary-fixed": "#d6e3ff",
                        "inverse-surface": "#213145",
                        "on-error": "#ffffff",
                        "secondary-fixed": "#e0e3e5",
                        "secondary-container": "#e0e3e5",
                        "tertiary-container": "#006d4a",
                        "on-secondary-container": "#626567"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "gutter": "24px",
                        "md": "24px",
                        "margin-mobile": "16px",
                        "xs": "4px",
                        "lg": "48px",
                        "xl": "80px",
                        "margin-desktop": "64px",
                        "sm": "12px",
                        "base": "8px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-xl": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg-mobile": ["Inter"],
                        "headline-lg": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    "fontSize": {
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "headline-xl": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["28px", { "lineHeight": "36px", "fontWeight": "600" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }]
                    }
                }
            }
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .icon-fill {
            font-variation-settings: 'FILL' 1;
        }
        
        /* Custom scrollbar for table */
        .custom-scrollbar::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: theme('colors.surface-container-low');
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: theme('colors.outline-variant');
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: theme('colors.outline');
        }

        /* Status Badges */
        .badge-pending {
            @apply bg-surface-variant text-on-surface-variant;
        }
        .badge-confirmed {
            @apply bg-tertiary-container text-on-tertiary-container;
        }
        .badge-cancelled {
            @apply bg-error-container text-on-error-container;
        }
        .badge-completed {
            @apply bg-primary-container text-on-primary-container;
        }

        /* Toast animation */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .toast-enter {
            animation: slideInRight 0.3s ease-out forwards;
        }
        .toast-exit {
            animation: fadeOut 0.3s ease-in forwards;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md h-screen overflow-hidden flex">
<!-- Mobile Top App Bar (Visible on mobile, hidden on lg) -->
<header class="lg:hidden fixed top-0 w-full z-50 bg-surface/80 dark:bg-on-background/80 backdrop-blur-md bg-surface-container-low dark:bg-on-background shadow-sm shadow-[0px_4px_20px_rgba(0,0,0,0.05)] flex justify-between items-center h-20 px-margin-mobile">
<button class="p-2 text-on-surface-variant hover:text-primary transition-colors" id="mobileMenuBtn">
<span class="material-symbols-outlined">menu</span>
</button>
<div class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed-dim">DentalCare Pro</div>
<div class="w-10"></div> <!-- Spacer -->
</header>
<!-- SideNavBar (Hidden on mobile by default, visible on lg) -->
<nav class="bg-surface-container-low dark:bg-on-background border-r border-outline-variant/30 h-screen w-64 fixed left-0 top-0 hidden lg:flex flex-col z-40 transition-transform duration-300" id="sideNav">
<div class="flex flex-col py-md gap-base h-full">
<div class="px-md mb-md flex items-center justify-between lg:justify-start">
<div class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed-dim">DentalCare Pro</div>
<button class="lg:hidden p-2 text-on-surface-variant" id="closeMobileNav">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<!-- Profile Info -->
<div class="px-md mb-lg flex items-center gap-sm">
<img alt="Dr. Smith Profile" class="w-12 h-12 rounded-full object-cover border border-outline-variant" data-alt="A professional headshot of Dr. Smith, a smiling, confident clinic manager in a pristine white medical coat. The background is a softly blurred modern dental office with high-key lighting, emphasizing cleanliness and premium healthcare quality. Soft, even lighting highlights the subject's approachability." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC10AurNJSSqIYUANTJGGytr8sD9k9qw66DrD-3BBKWKS7cbqLtOIcXX6fSH-Eyj64ZCGC42b75RhEJDvvLCR39Y1o0NW7XNpb-gCelY2B8KA_PrBtb4iEK7jPZxTSUBScvD6zf0X_KqIu_-MxTMqSGTDHElVvUcPFQaPcJCN8ThvkZhinc2tL5hVp_SjZGm42dvZwoKH3OXUvxvx6wwEhJVlUDN5B9xA880szTbmJutOteLOuf8erL356Q3A3h1laWLwNeWzBgwo0"/>
<div>
<div class="font-headline-md text-headline-md text-sm leading-tight">Admin Portal</div>
<div class="font-label-sm text-label-sm text-on-surface-variant font-normal">Clinic Manager</div>
</div>
</div>
<div class="px-md mb-md">
<button class="w-full bg-primary text-on-primary font-label-md text-label-md py-3 rounded-lg hover:scale-[0.98] hover:shadow-[0px_4px_20px_rgba(0,0,0,0.1)] transition-all flex justify-center items-center gap-2">
<span class="material-symbols-outlined icon-fill">add</span>
                    New Appointment
                </button>
</div>
<!-- Main Links -->
<div class="flex-1 overflow-y-auto custom-scrollbar">
<a class="flex items-center gap-3 px-4 py-3 mx-2 bg-primary-container dark:bg-primary text-on-primary-container dark:text-on-primary rounded-lg font-label-md text-label-md scale-[0.98] transition-transform" href="#">
<span class="material-symbols-outlined icon-fill">dashboard</span>
                    Overview
                </a>
<a class="flex items-center gap-3 px-4 py-3 mx-2 text-on-surface-variant dark:text-outline-variant hover:bg-surface-container-high dark:hover:bg-surface-variant rounded-lg font-label-md text-label-md transition-colors mt-1" href="#">
<span class="material-symbols-outlined">calendar_today</span>
                    Appointments
                </a>
<a class="flex items-center gap-3 px-4 py-3 mx-2 text-on-surface-variant dark:text-outline-variant hover:bg-surface-container-high dark:hover:bg-surface-variant rounded-lg font-label-md text-label-md transition-colors mt-1" href="#">
<span class="material-symbols-outlined">group</span>
                    Patients
                </a>
<a class="flex items-center gap-3 px-4 py-3 mx-2 text-on-surface-variant dark:text-outline-variant hover:bg-surface-container-high dark:hover:bg-surface-variant rounded-lg font-label-md text-label-md transition-colors mt-1" href="#">
<span class="material-symbols-outlined">medical_services</span>
                    Staff
                </a>
<a class="flex items-center gap-3 px-4 py-3 mx-2 text-on-surface-variant dark:text-outline-variant hover:bg-surface-container-high dark:hover:bg-surface-variant rounded-lg font-label-md text-label-md transition-colors mt-1" href="#">
<span class="material-symbols-outlined">settings</span>
                    Settings
                </a>
</div>
<!-- Footer Links -->
<div class="mt-auto border-t border-outline-variant/30 pt-4">
<a class="flex items-center gap-3 px-4 py-3 mx-2 text-on-surface-variant dark:text-outline-variant hover:bg-surface-container-high dark:hover:bg-surface-variant rounded-lg font-label-md text-label-md transition-colors" href="#">
<span class="material-symbols-outlined">help</span>
                    Support
                </a>
<a class="flex items-center gap-3 px-4 py-3 mx-2 text-on-surface-variant dark:text-outline-variant hover:bg-surface-container-high dark:hover:bg-surface-variant rounded-lg font-label-md text-label-md transition-colors mt-1" href="#">
<span class="material-symbols-outlined">logout</span>
                    Logout
                </a>
</div>
</div>
</nav>
<!-- Main Content Area -->
<main class="flex-1 lg:ml-64 h-screen overflow-y-auto custom-scrollbar pt-24 lg:pt-0 pb-xl bg-background">
<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop py-lg">
<header class="mb-lg flex justify-between items-end">
<div>
<h1 class="font-headline-xl text-headline-xl text-on-background mb-2">Dashboard Overview</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">Today's snapshot and upcoming schedules.</p>
</div>
<div class="hidden md:flex gap-sm">
<button class="w-10 h-10 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center hover:bg-surface-variant transition-colors">
<span class="material-symbols-outlined">notifications</span>
</button>
</div>
</header>
<!-- Stats Grid (Bento Style) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-xl">
<!-- Total -->
<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-md shadow-[0px_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.05)] transition-shadow relative overflow-hidden">
<div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/5 rounded-full blur-xl"></div>
<div class="flex justify-between items-start mb-4 relative z-10">
<div class="w-12 h-12 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center">
<span class="material-symbols-outlined icon-fill">calendar_month</span>
</div>
<span class="font-label-sm text-label-sm text-tertiary flex items-center bg-tertiary-container/30 px-2 py-1 rounded-full">
<span class="material-symbols-outlined text-[14px] mr-1">trending_up</span> +12%
                        </span>
</div>
<div class="relative z-10">
<div class="font-body-md text-body-md text-on-surface-variant mb-1">Total Appointments</div>
<div class="font-headline-xl text-headline-xl text-on-background">142</div>
</div>
</div>
<!-- Pending -->
<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-md shadow-[0px_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.05)] transition-shadow relative overflow-hidden">
<div class="flex justify-between items-start mb-4 relative z-10">
<div class="w-12 h-12 rounded-lg bg-surface-variant text-on-surface-variant flex items-center justify-center">
<span class="material-symbols-outlined icon-fill">pending_actions</span>
</div>
</div>
<div class="relative z-10">
<div class="font-body-md text-body-md text-on-surface-variant mb-1">Pending Review</div>
<div class="font-headline-xl text-headline-xl text-on-background">28</div>
</div>
</div>
<!-- Confirmed -->
<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-md shadow-[0px_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.05)] transition-shadow relative overflow-hidden lg:col-span-2">
<div class="absolute right-0 top-0 w-full h-full bg-gradient-to-l from-tertiary-container/10 to-transparent pointer-events-none"></div>
<div class="flex justify-between items-start mb-4 relative z-10">
<div class="w-12 h-12 rounded-lg bg-tertiary-container text-on-tertiary-container flex items-center justify-center">
<span class="material-symbols-outlined icon-fill">check_circle</span>
</div>
<div class="text-right">
<div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">This Week</div>
<div class="font-body-sm text-tertiary">85% Capacity</div>
</div>
</div>
<div class="relative z-10 flex items-end gap-md">
<div>
<div class="font-body-md text-body-md text-on-surface-variant mb-1">Confirmed</div>
<div class="font-headline-xl text-headline-xl text-on-background">104</div>
</div>
<div class="flex-1 h-2 bg-surface-container-high rounded-full overflow-hidden mb-2">
<div class="h-full bg-tertiary rounded-full w-[85%]"></div>
</div>
</div>
</div>
</div>
<!-- Appointments Section -->
<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.02)] overflow-hidden">
<!-- Toolbar -->
<div class="p-md border-b border-outline-variant/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-md bg-surface-container-low/50">
<h2 class="font-headline-md text-headline-md text-on-background">Recent Appointments</h2>
<div class="flex flex-col sm:flex-row gap-sm w-full md:w-auto">
<!-- Search -->
<div class="relative w-full sm:w-64">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
<input class="w-full pl-10 pr-4 py-2 bg-background border border-outline-variant rounded-lg font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-on-surface-variant/70" id="searchInput" placeholder="Search patient..." type="text"/>
</div>
<!-- Filter -->
<div class="relative w-full sm:w-auto">
<select class="w-full appearance-none pl-4 pr-10 py-2 bg-background border border-outline-variant rounded-lg font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all cursor-pointer" id="statusFilter">
<option value="all">All Statuses</option>
<option value="pending">Pending</option>
<option value="confirmed">Confirmed</option>
<option value="completed">Completed</option>
<option value="cancelled">Cancelled</option>
</select>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">arrow_drop_down</span>
</div>
</div>
</div>
<!-- Table -->
<div class="overflow-x-auto custom-scrollbar">
<table class="w-full text-left border-collapse min-w-[800px]">
<thead>
<tr class="bg-surface-container-lowest border-b border-outline-variant/50">
<th class="py-4 px-md font-label-md text-label-md text-on-surface-variant cursor-pointer hover:text-primary transition-colors group" onclick="sortTable(0)">
                                    Patient <span class="material-symbols-outlined text-[16px] inline-block align-text-bottom opacity-0 group-hover:opacity-100 transition-opacity">arrow_drop_down</span>
</th>
<th class="py-4 px-md font-label-md text-label-md text-on-surface-variant cursor-pointer hover:text-primary transition-colors group" onclick="sortTable(1)">
                                    Date &amp; Time <span class="material-symbols-outlined text-[16px] inline-block align-text-bottom opacity-0 group-hover:opacity-100 transition-opacity">arrow_drop_down</span>
</th>
<th class="py-4 px-md font-label-md text-label-md text-on-surface-variant">Service</th>
<th class="py-4 px-md font-label-md text-label-md text-on-surface-variant">Status</th>
<th class="py-4 px-md font-label-md text-label-md text-on-surface-variant text-right">Actions</th>
</tr>
</thead>
<tbody class="font-body-md text-body-md" id="appointmentTableBody">
<!-- Rows generated by JS -->
</tbody>
</table>
</div>
<div class="p-md border-t border-outline-variant/30 flex justify-between items-center text-on-surface-variant font-label-md text-label-md">
<span>Showing <span id="visibleCount">0</span> of <span id="totalCount">0</span></span>
<div class="flex gap-2">
<button class="p-1 rounded hover:bg-surface-container-high transition-colors disabled:opacity-50" disabled=""><span class="material-symbols-outlined">chevron_left</span></button>
<button class="p-1 rounded hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined">chevron_right</span></button>
</div>
</div>
</div>
</div>
</main>
<!-- Modals Overlay -->
<div class="fixed inset-0 bg-on-background/40 backdrop-blur-sm z-50 hidden items-center justify-center p-margin-mobile opacity-0 transition-opacity duration-300" id="modalOverlay">
<!-- View Details Modal -->
<div class="bg-surface-container-lowest rounded-xl w-full max-w-2xl shadow-[0px_10px_30px_rgba(0,0,0,0.08)] transform scale-95 transition-transform duration-300 hidden flex-col max-h-[921px]" id="detailsModal">
<div class="p-md border-b border-outline-variant/30 flex justify-between items-center bg-surface-container-low/50 rounded-t-xl">
<h3 class="font-headline-md text-headline-md text-on-background">Appointment Details</h3>
<button class="close-modal p-2 rounded-full hover:bg-surface-container-high text-on-surface-variant transition-colors">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<div class="p-md overflow-y-auto custom-scrollbar flex-1 space-y-lg">
<!-- Patient Info -->
<div class="flex items-start gap-md">
<div class="w-16 h-16 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-headline-lg text-headline-lg shrink-0">
<span id="modalInitial"></span>
</div>
<div>
<h4 class="font-headline-md text-headline-md text-on-background mb-1" id="modalPatientName"></h4>
<div class="font-body-md text-body-md text-on-surface-variant flex items-center gap-2 mb-1">
<span class="material-symbols-outlined text-[18px]">phone</span> +1 (555) 123-4567
                        </div>
<div class="font-body-md text-body-md text-on-surface-variant flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">mail</span> patient@example.com
                        </div>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-md bg-surface p-md rounded-lg border border-outline-variant/30">
<div>
<div class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase tracking-wider">Date &amp; Time</div>
<div class="font-body-md text-body-md text-on-background font-medium" id="modalDateTime"></div>
</div>
<div>
<div class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase tracking-wider">Service Requested</div>
<div class="font-body-md text-body-md text-on-background font-medium" id="modalService"></div>
</div>
</div>
<div>
<div class="font-label-sm text-label-sm text-on-surface-variant mb-2 uppercase tracking-wider">Patient Notes</div>
<div class="bg-surface-container-low p-md rounded-lg font-body-md text-body-md text-on-background border border-outline-variant/30 min-h-[100px]" id="modalNotes">
<!-- Notes populated by JS -->
</div>
</div>
</div>
<div class="p-md border-t border-outline-variant/30 flex justify-end gap-sm bg-surface-container-lowest rounded-b-xl">
<button class="close-modal px-6 py-2 rounded-lg font-label-md text-label-md text-primary border-2 border-primary hover:bg-surface-container-low transition-colors">Close</button>
<button class="px-6 py-2 rounded-lg font-label-md text-label-md text-on-primary bg-primary hover:scale-[0.98] hover:shadow-md transition-all" id="modalActionBtn">Confirm Appointment</button>
</div>
</div>
<!-- Delete Confirmation Modal -->
<div class="bg-surface-container-lowest rounded-xl w-full max-w-md shadow-[0px_10px_30px_rgba(0,0,0,0.08)] transform scale-95 transition-transform duration-300 hidden flex-col" id="deleteModal">
<div class="p-lg flex flex-col items-center text-center">
<div class="w-16 h-16 rounded-full bg-error-container text-on-error-container flex items-center justify-center mb-md">
<span class="material-symbols-outlined text-[32px]">warning</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-background mb-2">Cancel Appointment?</h3>
<p class="font-body-md text-body-md text-on-surface-variant mb-lg">Are you sure you want to cancel the appointment for <strong class="text-on-background" id="deletePatientName"></strong>? This action cannot be undone.</p>
<div class="w-full flex gap-sm">
<button class="close-modal flex-1 px-4 py-2 rounded-lg font-label-md text-label-md text-on-surface-variant bg-surface-container-high hover:bg-surface-variant transition-colors">No, Keep It</button>
<button class="flex-1 px-4 py-2 rounded-lg font-label-md text-label-md text-on-error bg-error hover:scale-[0.98] transition-all" id="confirmDeleteBtn">Yes, Cancel It</button>
</div>
</div>
</div>
</div>
<!-- Toast Container -->
<div class="fixed bottom-margin-mobile right-margin-mobile md:bottom-margin-desktop md:right-margin-desktop z-[60] flex flex-col gap-2" id="toastContainer"></div>
<script>
        // Mock Data
        const mockAppointments = [
            { id: 1, name: "Sarah Jenkins", date: "2024-05-15", time: "09:00 AM", service: "Routine Checkup", status: "confirmed", notes: "Patient requested a female hygienist if possible." },
            { id: 2, name: "Michael Chen", date: "2024-05-15", time: "10:30 AM", service: "Root Canal", status: "pending", notes: "Experiencing mild pain in lower left molar." },
            { id: 3, name: "Emily Rodriguez", date: "2024-05-15", time: "01:15 PM", service: "Teeth Whitening", status: "completed", notes: "" },
            { id: 4, name: "David Kim", date: "2024-05-16", time: "11:00 AM", service: "Consultation", status: "cancelled", notes: "Called to cancel, will reschedule next week." },
            { id: 5, name: "Jessica Taylor", date: "2024-05-16", time: "02:00 PM", service: "Cavity Filling", status: "pending", notes: "Nervous patient, needs extra time." },
            { id: 6, name: "Robert Wilson", date: "2024-05-17", time: "09:30 AM", service: "Crown Placement", status: "confirmed", notes: "" },
        ];

        let currentData = [...mockAppointments];
        let currentSort = { col: 1, asc: true }; // Default sort by date

        // DOM Elements
        const tableBody = document.getElementById('appointmentTableBody');
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const visibleCount = document.getElementById('visibleCount');
        const totalCount = document.getElementById('totalCount');
        
        // Modal Elements
        const modalOverlay = document.getElementById('modalOverlay');
        const detailsModal = document.getElementById('detailsModal');
        const deleteModal = document.getElementById('deleteModal');
        const closeBtns = document.querySelectorAll('.close-modal');

        // Render Table
        function renderTable() {
            tableBody.innerHTML = '';
            
            if (currentData.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-xl text-center text-on-surface-variant font-body-md">
                            <span class="material-symbols-outlined text-[48px] mb-2 opacity-50 block">search_off</span>
                            No appointments found matching criteria.
                        </td>
                    </tr>
                `;
            }

            currentData.forEach(appt => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-outline-variant/30 hover:bg-surface-container-low/30 transition-colors group';
                
                let badgeClass = '';
                let statusText = appt.status;
                switch(appt.status) {
                    case 'pending': badgeClass = 'badge-pending'; break;
                    case 'confirmed': badgeClass = 'badge-confirmed'; break;
                    case 'cancelled': badgeClass = 'badge-cancelled'; break;
                    case 'completed': badgeClass = 'badge-completed'; break;
                }

                // Format date for display
                const dateObj = new Date(appt.date);
                const dateStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

                tr.innerHTML = `
                    <td class="py-4 px-md">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-label-md font-bold text-[12px]">
                                ${appt.name.charAt(0)}
                            </div>
                            <span class="font-body-md text-on-background font-medium">${appt.name}</span>
                        </div>
                    </td>
                    <td class="py-4 px-md">
                        <div class="font-body-md text-on-background">${dateStr}</div>
                        <div class="font-label-sm text-on-surface-variant">${appt.time}</div>
                    </td>
                    <td class="py-4 px-md font-body-md text-on-surface-variant">${appt.service}</td>
                    <td class="py-4 px-md">
                        <span class="px-3 py-1 rounded-full font-label-sm capitalize ${badgeClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="py-4 px-md text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="openDetails(${appt.id})" class="p-2 rounded hover:bg-surface-container-high text-primary transition-colors" title="View Details">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </button>
                            ${appt.status === 'pending' ? `
                                <button onclick="quickConfirm(${appt.id})" class="p-2 rounded hover:bg-tertiary-container/50 text-tertiary transition-colors" title="Confirm">
                                    <span class="material-symbols-outlined text-[20px]">check</span>
                                </button>
                            ` : ''}
                            ${appt.status !== 'cancelled' && appt.status !== 'completed' ? `
                                <button onclick="openDelete(${appt.id})" class="p-2 rounded hover:bg-error-container/50 text-error transition-colors" title="Cancel">
                                    <span class="material-symbols-outlined text-[20px]">close</span>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            visibleCount.textContent = currentData.length;
            totalCount.textContent = mockAppointments.length;
        }

        // Filtering Logic
        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusTerm = statusFilter.value;

            currentData = mockAppointments.filter(appt => {
                const matchesSearch = appt.name.toLowerCase().includes(searchTerm) || appt.service.toLowerCase().includes(searchTerm);
                const matchesStatus = statusTerm === 'all' || appt.status === statusTerm;
                return matchesSearch && matchesStatus;
            });

            // Re-apply sort
            sortData(currentSort.col, currentSort.asc);
            renderTable();
        }

        searchInput.addEventListener('input', applyFilters);
        statusFilter.addEventListener('change', applyFilters);

        // Sorting Logic
        window.sortTable = function(colIndex) {
            if (currentSort.col === colIndex) {
                currentSort.asc = !currentSort.asc;
            } else {
                currentSort.col = colIndex;
                currentSort.asc = true;
            }
            sortData(colIndex, currentSort.asc);
            renderTable();
        };

        function sortData(colIndex, asc) {
            currentData.sort((a, b) => {
                let valA, valB;
                if (colIndex === 0) {
                    valA = a.name.toLowerCase();
                    valB = b.name.toLowerCase();
                } else if (colIndex === 1) {
                    valA = new Date(a.date + ' ' + a.time);
                    valB = new Date(b.date + ' ' + b.time);
                }

                if (valA < valB) return asc ? -1 : 1;
                if (valA > valB) return asc ? 1 : -1;
                return 0;
            });
        }

        // Modal Logic
        let currentApptId = null;

        function showModal(modalEl) {
            modalOverlay.classList.remove('hidden');
            modalOverlay.classList.add('flex');
            // Small delay for transition
            setTimeout(() => {
                modalOverlay.classList.remove('opacity-0');
                modalEl.classList.remove('hidden');
                modalEl.classList.add('flex');
                setTimeout(() => {
                    modalEl.classList.remove('scale-95');
                }, 10);
            }, 10);
        }

        function hideModals() {
            detailsModal.classList.add('scale-95');
            deleteModal.classList.add('scale-95');
            modalOverlay.classList.add('opacity-0');
            
            setTimeout(() => {
                detailsModal.classList.add('hidden');
                detailsModal.classList.remove('flex');
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
                modalOverlay.classList.add('hidden');
                modalOverlay.classList.remove('flex');
            }, 300);
        }

        closeBtns.forEach(btn => btn.addEventListener('click', hideModals));
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) hideModals();
        });

        window.openDetails = function(id) {
            const appt = mockAppointments.find(a => a.id === id);
            if(!appt) return;
            
            currentApptId = id;
            document.getElementById('modalInitial').textContent = appt.name.charAt(0);
            document.getElementById('modalPatientName').textContent = appt.name;
            document.getElementById('modalDateTime').textContent = `${new Date(appt.date).toLocaleDateString()} at ${appt.time}`;
            document.getElementById('modalService').textContent = appt.service;
            document.getElementById('modalNotes').textContent = appt.notes || "No additional notes provided.";

            const actionBtn = document.getElementById('modalActionBtn');
            if (appt.status === 'pending') {
                actionBtn.textContent = 'Confirm Appointment';
                actionBtn.className = 'px-6 py-2 rounded-lg font-label-md text-label-md text-on-primary bg-primary hover:scale-[0.98] hover:shadow-md transition-all';
                actionBtn.style.display = 'block';
                actionBtn.onclick = () => { quickConfirm(id); hideModals(); };
            } else if (appt.status === 'confirmed') {
                actionBtn.textContent = 'Mark Completed';
                actionBtn.className = 'px-6 py-2 rounded-lg font-label-md text-label-md text-on-tertiary-container bg-tertiary-container hover:scale-[0.98] transition-all';
                actionBtn.style.display = 'block';
                actionBtn.onclick = () => { changeStatus(id, 'completed'); hideModals(); };
            } else {
                actionBtn.style.display = 'none';
            }

            showModal(detailsModal);
        };

        window.openDelete = function(id) {
            const appt = mockAppointments.find(a => a.id === id);
            if(!appt) return;
            currentApptId = id;
            document.getElementById('deletePatientName').textContent = appt.name;
            showModal(deleteModal);
        };

        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            changeStatus(currentApptId, 'cancelled');
            hideModals();
            showToast('Appointment cancelled successfully.', 'error');
        });

        window.quickConfirm = function(id) {
            changeStatus(id, 'confirmed');
            showToast('Appointment confirmed successfully.', 'success');
        };

        function changeStatus(id, newStatus) {
            const index = mockAppointments.findIndex(a => a.id === id);
            if(index !== -1) {
                mockAppointments[index].status = newStatus;
                applyFilters(); // Re-render table
            }
        }

        // Toast Notification System
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            
            let bgClass, icon;
            if (type === 'success') { bgClass = 'bg-tertiary-container text-on-tertiary-container'; icon = 'check_circle'; }
            else if (type === 'error') { bgClass = 'bg-error-container text-on-error-container'; icon = 'error'; }
            else { bgClass = 'bg-inverse-surface text-inverse-on-surface'; icon = 'info'; }

            toast.className = `flex items-center gap-3 px-4 py-3 rounded-lg shadow-[0px_10px_30px_rgba(0,0,0,0.1)] font-body-md text-body-md toast-enter ${bgClass}`;
            toast.innerHTML = `
                <span class="material-symbols-outlined">${icon}</span>
                <span>${message}</span>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.replace('toast-enter', 'toast-exit');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Mobile Nav Logic
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeMobileNav = document.getElementById('closeMobileNav');
        const sideNav = document.getElementById('sideNav');

        mobileMenuBtn.addEventListener('click', () => {
            sideNav.classList.remove('hidden');
            // Force reflow
            void sideNav.offsetWidth;
            sideNav.classList.remove('-translate-x-full');
        });

        closeMobileNav.addEventListener('click', () => {
            sideNav.classList.add('-translate-x-full');
            setTimeout(() => {
                sideNav.classList.add('hidden');
            }, 300);
        });

        // Initialize mobile state
        if (window.innerWidth < 1024) {
            sideNav.classList.add('-translate-x-full');
        }

        // Initial Render
        renderTable();

    </script>
</body></html>