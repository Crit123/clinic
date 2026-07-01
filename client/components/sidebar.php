<?php
/**
 * sidebar.php
 * Reusable responsive left navigation sidebar for DentalCare Pro.
 * Optimized for compact heights, micro-interactions, local storage collapse, and responsive desktop/tablet/mobile handling.
 */
require_once __DIR__ . '/design-config.php';

if (!isset($activePage)) {
    $activePage = 'dashboard';
}

function nav_link_classes(string $key, string $activePage): string {
    $base = 'nav-item relative flex items-center px-4 py-3 rounded-xl transition-all duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1652a0] focus-visible:ring-offset-2';
    
    // Special subtle styling for the emergency entry point
    if ($key === 'emergency') {
        if ($activePage === 'emergency') {
            return $base . ' text-rose-700 font-bold bg-rose-50 shadow-sm border border-rose-100';
        }
        return $base . ' text-rose-600 hover:text-rose-800 hover:bg-rose-50 hover:translate-x-1 border-l-0';
    }

    if ($key === $activePage) {
        // SaaS-style rounded card with light blue background, subtle shadow, and medium font weight
        return $base . ' text-[#1652a0] font-semibold bg-[#e8f0fb] shadow-sm';
    }
    // Hover animation: 200ms ease background highlight and slight horizontal translation
    return $base . ' text-tertiary-text hover:text-slate-800 hover:bg-slate-50 hover:translate-x-1 border-l-0';
}
?>

<style>
  /* Collapse layout adjustments */
  #mainSidebar.w-20 .sidebar-logo-area { display: none !important; }
  #mainSidebar.w-20 #sidebarSearch { justify-content: center; padding-left: 0; padding-right: 0; }
  #mainSidebar.w-20 .sidebar-text { display: none !important; }
  #mainSidebar.w-20 .nav-tooltip { display: none; }
  #mainSidebar.w-20 .nav-item:hover .nav-tooltip { 
      display: block; 
      position: absolute; 
      left: 100%; 
      top: 50%; 
      transform: translateY(-50%); 
      margin-left: 12px; 
      background: #213145; 
      color: #fff; 
      padding: 6px 10px; 
      border-radius: 8px; 
      font-size: 11px; 
      font-weight: 500;
      white-space: nowrap; 
      z-index: 100; 
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }
  #mainSidebar.w-20 .nav-item:hover .nav-tooltip::before {
      content: '';
      position: absolute;
      right: 100%; top: 50%;
      transform: translateY(-50%);
      border: 5px solid transparent;
      border-right-color: #213145;
  }
</style>

<!-- MOBILE DRAWER BACKDROP -->
<div id="sidebarBackdrop"
     class="fixed inset-0 bg-[#001e31]/20 backdrop-blur-sm z-40 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"
     onclick="toggleMobileSidebar(false)"
     aria-hidden="true"></div>

<!-- PORTAL SIDEBAR -->
<nav id="mainSidebar"
     role="navigation"
     aria-label="Portal Navigation"
     class="sidebar-transition h-screen w-64 fixed left-0 top-0 bg-white shadow-[4px_0_24px_rgba(0,71,141,0.02)] border-r border-slate-100 -translate-x-full md:translate-x-0 md:flex flex-col py-6 px-4.5 z-50">

    <!-- BRAND HEADER -->
    <div class="mb-6 flex items-center justify-between px-3">
        <div class="sidebar-logo-area flex items-center gap-2.5 overflow-hidden">
            <!-- Medical Logo Mark -->
            <div class="w-7 h-7 bg-[#1652a0] rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm shadow-[#1652a0]/15">
                <svg class="w-4 h-4" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M20 8C16.5 8 14 10 12.5 12C11 10 9 9 7.5 10.5C6 12 6.5 15 8 17C9 18.5 10 20 10.5 22C11 24 11 28 13 30C14 31 15.5 31 16.5 30C17.5 29 17.5 26 18 24C18.5 22 19 21 20 21C21 21 21.5 22 22 24C22.5 26 22.5 29 23.5 30C24.5 31 26 31 27 30C29 28 29 24 29.5 22C30 20 31 18.5 32 17C33.5 15 34 12 32.5 10.5C31 9 29 10 27.5 12C26 10 23.5 8 20 8Z" fill="white"/>
                </svg>
            </div>
            <div>
                <h1 id="brandText" class="text-[14px] lg:text-[15px] font-bold text-slate-800 leading-none tracking-tight whitespace-nowrap transition-all duration-200">
                    <?php echo htmlspecialchars(SITE_NAME); ?>
                </h1>
                <p id="taglineText" class="hidden md:block text-[10px] lg:text-[11px] text-secondary-text font-semibold mt-0.5 uppercase tracking-widest whitespace-nowrap transition-all duration-200">
                    <?php echo htmlspecialchars(BRAND_TAGLINE); ?>
                </p>
            </div>
        </div>

        <button id="collapseBtn"
                onclick="toggleSidebarCollapse()"
                aria-label="Toggle Navigation Collapse"
                class="hidden md:flex p-1.5 rounded-lg text-slate-400 hover:text-[#1652a0] hover:bg-slate-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#1652a0]">
            <span class="material-symbols-outlined text-lg transition-transform duration-300" id="collapseIcon">menu_open</span>
        </button>
    </div>

    <!-- QUICK SEARCH BAR -->
    <div class="px-3 mb-5">
        <div class="flex items-center gap-2 bg-slate-50 border border-slate-50/50 rounded-xl p-2.5 cursor-text transition-all duration-200 hover:border-slate-100" id="sidebarSearch" onclick="document.getElementById('searchInput')?.focus()">
            <span class="material-symbols-outlined text-slate-400 text-base flex-shrink-0">search</span>
            <span class="sidebar-text text-[13px] lg:text-[14px] text-tertiary-text select-none font-medium">Quick search…</span>
        </div>
    </div>

    <!-- MAIN SAAS NAVIGATION ITEMS -->
    <div class="flex-1 overflow-y-auto overflow-x-visible space-y-1 px-1.5">
        <ul class="space-y-1">
            <li>
                <a class="<?php echo nav_link_classes('dashboard', $activePage); ?>"
                   href="<?php echo BASE_PATH; ?>/client/pages/dashboard.php"
                   aria-current="<?php echo $activePage === 'dashboard' ? 'page' : 'false'; ?>"
                   title="Dashboard">
                    <span class="material-symbols-outlined mr-3.5 flex-shrink-0 text-xl" aria-hidden="true">dashboard</span>
                    <span class="font-semibold text-[13px] lg:text-[14px] sidebar-text">Dashboard</span>
                    <span class="nav-tooltip" role="tooltip">Dashboard</span>
                </a>
            </li>
            <li>
                <a class="<?php echo nav_link_classes('appointments', $activePage); ?>"
                   href="<?php echo BASE_PATH; ?>/client/pages/appointments.php"
                   aria-current="<?php echo $activePage === 'appointments' ? 'page' : 'false'; ?>"
                   title="My Appointments">
                    <span class="material-symbols-outlined mr-3.5 flex-shrink-0 text-xl" aria-hidden="true">calendar_month</span>
                    <span class="font-semibold text-[13px] lg:text-[14px] sidebar-text">Appointments</span>
                    <span class="nav-tooltip" role="tooltip">Appointments</span>
                </a>
            </li>
            <li>
                <a class="<?php echo nav_link_classes('book', $activePage); ?>"
                   href="<?php echo BASE_PATH; ?>/client/pages/book-appointment.php"
                   aria-current="<?php echo $activePage === 'book' ? 'page' : 'false'; ?>"
                   title="Book Appointment">
                    <span class="material-symbols-outlined mr-3.5 flex-shrink-0 text-xl" aria-hidden="true">add_circle</span>
                    <span class="font-semibold text-[13px] lg:text-[14px] sidebar-text">Book Appointment</span>
                    <span class="nav-tooltip" role="tooltip">Book Appointment</span>
                </a>
            </li>
            <li>
                <a class="<?php echo nav_link_classes('records', $activePage); ?>"
                   href="<?php echo BASE_PATH; ?>/client/pages/dental-records.php"
                   aria-current="<?php echo $activePage === 'records' ? 'page' : 'false'; ?>"
                   title="Dental Records">
                    <span class="material-symbols-outlined mr-3.5 flex-shrink-0 text-xl" aria-hidden="true">medical_information</span>
                    <span class="font-semibold text-[13px] lg:text-[14px] sidebar-text">Dental Records</span>
                    <span class="nav-tooltip" role="tooltip">Dental Records</span>
                </a>
            </li>
        </ul>

        <!-- DIVIDER SEPARATOR LINE -->
        <div class="border-t border-slate-100 my-4 py-1"></div>

        <ul class="space-y-1">
            <!-- EMERGENCY CARE LINK -->
            <li>
                <a class="<?php echo nav_link_classes('emergency', $activePage); ?>"
                   href="<?php echo BASE_PATH; ?>/client/pages/support/emergency-care.php"
                   aria-current="<?php echo $activePage === 'emergency' ? 'page' : 'false'; ?>"
                   title="Emergency Care">
                    <span class="material-symbols-outlined mr-3.5 flex-shrink-0 text-xl" aria-hidden="true">emergency</span>
                    <span class="font-bold text-[13px] lg:text-[14px] sidebar-text">Emergency Care</span>
                    <span class="nav-tooltip" role="tooltip">Emergency Care</span>
                </a>
            </li>

            <li>
                <a class="<?php echo nav_link_classes('settings', $activePage); ?>"
                   href="<?php echo BASE_PATH; ?>/client/pages/profile-settings.php"
                   aria-current="<?php echo $activePage === 'settings' ? 'page' : 'false'; ?>"
                   title="Profile Settings">
                    <span class="material-symbols-outlined mr-3.5 flex-shrink-0 text-xl" aria-hidden="true">settings</span>
                    <span class="font-semibold text-[13px] lg:text-[14px] sidebar-text">Profile Settings</span>
                    <span class="nav-tooltip" role="tooltip">Profile Settings</span>
                </a>
            </li>
            <li>
                <a class="<?php echo nav_link_classes('support', $activePage); ?>"
                   href="<?php echo BASE_PATH; ?>/client/pages/support-center.php"
                   aria-current="<?php echo $activePage === 'support' ? 'page' : 'false'; ?>"
                   title="Support Center">
                    <span class="material-symbols-outlined mr-3.5 flex-shrink-0 text-xl" aria-hidden="true">help</span>
                    <span class="font-semibold text-[13px] lg:text-[14px] sidebar-text">Support Center</span>
                    <span class="nav-tooltip" role="tooltip">Support Center</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- BOTTOM ACTIONS -->
    <div class="border-t border-slate-100 pt-3 px-1.5 mt-auto">
        <button onclick="triggerLogout()"
                aria-label="Log out of <?php echo htmlspecialchars(SITE_NAME); ?>"
                class="relative w-full flex items-center px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-800 hover:translate-x-1 active:scale-98 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-slate-500">
            <span class="material-symbols-outlined mr-3.5 flex-shrink-0 text-slate-400 text-xl" aria-hidden="true">logout</span>
            <span class="font-bold text-[13px] lg:text-[14px] sidebar-text">Log Out</span>
            <span class="nav-tooltip" role="tooltip">Log Out</span>
        </button>
    </div>
</nav>

<script>
window.sidebarForceOpenLock = window.sidebarForceOpenLock || false;

function toggleMobileSidebar(open) {
    const sidebar = document.getElementById('mainSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const content = document.getElementById('contentContainer');

    if (open) {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('pointer-events-none', 'opacity-0');
        backdrop.classList.add('opacity-100');
        if (content) content.setAttribute('inert', '');
        sidebar.querySelector('a, button')?.focus();
    } else {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('pointer-events-none', 'opacity-0');
        if (content) content.removeAttribute('inert');
    }
}

function toggleSidebarCollapse() {
    const sidebar = document.getElementById('mainSidebar');
    const brandText = document.getElementById('brandText');
    const taglineText = document.getElementById('taglineText');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');
    const collapseIcon = document.getElementById('collapseIcon');

    const isExpanded = sidebar.classList.contains('w-64') || sidebar.classList.contains('w-[260px]');

    if (isExpanded) {
        sidebar.classList.remove('w-64', 'w-[260px]');
        sidebar.classList.add('w-20', 'sidebar-collapsed');
        if (brandText) brandText.classList.add('hidden');
        if (taglineText) taglineText.classList.add('hidden');
        collapseIcon.textContent = 'menu';
        sidebarTexts.forEach(el => el.classList.add('hidden'));
        _shiftContent('w-20');
        localStorage.setItem('dcpro-sidebar', 'collapsed');
    } else {
        sidebar.classList.remove('w-20', 'sidebar-collapsed');
        sidebar.classList.add('w-[260px]');
        if (brandText) brandText.classList.remove('hidden');
        if (taglineText) taglineText.classList.remove('hidden');
        collapseIcon.textContent = 'menu_open';
        sidebarTexts.forEach(el => el.classList.remove('hidden'));
        _shiftContent('w-64');
        localStorage.setItem('dcpro-sidebar', 'expanded');
    }
}

function _shiftContent(widthClass) {
    if (window.innerWidth < 768) return;
    const content = document.getElementById('contentContainer');
    if (!content) return;
    const mlMap = { 
        'w-64': ['md:ml-[260px]', 'md:ml-20'], 
        'w-20': ['md:ml-20', 'md:ml-[260px]'] 
    };
    const [add, remove] = mlMap[widthClass];
    content.classList.add(add);
    content.classList.remove(remove);
}

function handleResponsiveLayout() {
    if (window.sidebarForceOpenLock) return;
    if (window.innerWidth >= 768 && window.innerWidth < 1024) {
        const sidebar = document.getElementById('mainSidebar');
        if (sidebar && !sidebar.classList.contains('sidebar-collapsed')) {
            sidebar.style.transition = 'none';
            toggleSidebarCollapse();
            requestAnimationFrame(() => { sidebar.style.transition = ''; });
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const savedState = localStorage.getItem('dcpro-sidebar');
    const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
    
    if (savedState === 'collapsed' || isTablet) {
        const sidebar = document.getElementById('mainSidebar');
        if (sidebar && window.innerWidth >= 768) {
            sidebar.style.transition = 'none';
            sidebar.classList.remove('w-64', 'w-[260px]');
            sidebar.classList.add('w-20', 'sidebar-collapsed');
            const brandText = document.getElementById('brandText');
            const taglineText = document.getElementById('taglineText');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const collapseIcon = document.getElementById('collapseIcon');
            if (brandText) brandText.classList.add('hidden');
            if (taglineText) taglineText.classList.add('hidden');
            if (collapseIcon) collapseIcon.textContent = 'menu';
            sidebarTexts.forEach(el => el.classList.add('hidden'));
            
            const content = document.getElementById('contentContainer');
            if (content) {
                content.classList.add('md:ml-20');
                content.classList.remove('md:ml-64', 'md:ml-[260px]');
            }
            requestAnimationFrame(() => { sidebar.style.transition = ''; });
        }
    } else {
        const sidebar = document.getElementById('mainSidebar');
        if (sidebar && window.innerWidth >= 768) {
            sidebar.classList.add('w-[260px]');
            sidebar.classList.remove('w-64');
            const content = document.getElementById('contentContainer');
            if (content) {
                content.classList.add('md:ml-[260px]');
                content.classList.remove('md:ml-64');
            }
        }
    }
    
    window.addEventListener('resize', handleResponsiveLayout);
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') toggleMobileSidebar(false);
});

function triggerLogout() {
    if (typeof showGlobalToast === 'function') {
        showGlobalToast('info', 'Logging out safely… Redirecting in a moment.');
    }
    setTimeout(() => { window.location.href = '<?php echo BASE_PATH; ?>/client/pages/logout.php'; }, 1500);
}
</script>