<?php 
// Ensure the canonical services registry is available for dynamic rendering
require_once __DIR__ . '/../api/data/services-data.php';
$services = getAllServices();

// Base URL ensures links work correctly even when included from subdirectories like /auth
$base_url = '/booking-system'; 
?>
<style>
    /* Premium Navigation Pill & Underline Animation */
    .nav-link {
        position: relative;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 9999px; /* Pill shape */
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: transparent;
    }
    
    .nav-link:hover, 
    .nav-link:focus-visible {
        background-color: rgba(0, 71, 141, 0.04);
        color: #00478d;
    }
    
    .nav-link.active {
        background-color: rgba(0, 71, 141, 0.08);
        color: #00478d;
    }

    /* Subtle centered underline indicator inside the pill */
    .nav-link::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 2px;
        bottom: 6px;
        left: 50%;
        border-radius: 2px;
        background-color: #00478d;
        transform: translateX(-50%) scaleX(0);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .nav-link.active::after {
        transform: translateX(-50%) scaleX(1);
    }

    /* Dropdowns - Premium Fade & Slide */
    .auth-dropdown, .nav-dropdown {
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px) scale(0.98);
        transform-origin: top center;
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 12px 40px -10px rgba(0, 0, 0, 0.08), 0 4px 12px -4px rgba(0, 0, 0, 0.04);
        border-radius: 1.25rem;
        transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                    transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                    visibility 0.25s;
    }
    
    .auth-dropdown-wrapper:hover .auth-dropdown,
    .auth-dropdown-wrapper:focus-within .auth-dropdown,
    .nav-dropdown-wrapper:hover .nav-dropdown,
    .nav-dropdown-wrapper:focus-within .nav-dropdown,
    .nav-dropdown-wrapper.force-show .nav-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    /* Dropdown specific item styling (no pill underline needed here) */
    .dropdown-item {
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }
    .dropdown-item:not(.is-emergency):hover {
        background-color: rgba(0, 71, 141, 0.04);
        color: #00478d;
        transform: translateX(2px);
    }
    .dropdown-item.is-emergency:hover {
        transform: translateX(2px);
    }

    /* Logo Micro-interactions */
    .logo-container:hover .logo-icon rect {
        fill: #00366b;
        transition: fill 0.3s ease;
    }
    .logo-container:hover .logo-icon {
        transform: scale(1.04) rotate(-1deg);
    }
    .logo-icon, .logo-icon rect {
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
</style>

<!-- Header: Added transition-all for smooth height/padding changes on scroll -->
<header class="fixed top-0 w-full z-50 bg-surface/70 backdrop-blur-xl border-b border-transparent transition-all duration-500" id="main-header">
    <div id="header-container" class="flex justify-between items-center h-20 md:h-24 px-4 md:px-8 max-w-[1200px] mx-auto transition-all duration-500">

        <!-- Logo -->
        <a href="<?php echo $base_url; ?>/index.php#home" class="logo-container flex items-center gap-3 cursor-pointer group" data-section="home" aria-label="Go to homepage">
            <svg class="logo-icon h-10 w-10 md:h-11 md:w-11 shadow-sm rounded-xl" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect width="40" height="40" rx="10" fill="#00478d"/>
                <path d="M20 8C16.5 8 14 10 12.5 12C11 10 9 9 7.5 10.5C6 12 6.5 15 8 17C9 18.5 10 20 10.5 22C11 24 11 28 13 30C14 31 15.5 31 16.5 30C17.5 29 17.5 26 18 24C18.5 22 19 21 20 21C21 21 21.5 22 22 24C22.5 26 22.5 29 23.5 30C24.5 31 26 31 27 30C29 28 29 24 29.5 22C30 20 31 18.5 32 17C33.5 15 34 12 32.5 10.5C31 9 29 10 27.5 12C26 10 23.5 8 20 8Z" fill="white"/>
            </svg>
            <span class="font-headline-md text-xl md:text-2xl font-extrabold text-primary tracking-tight transition-colors group-hover:text-[#00366b]">DentalCare Pro</span>
        </a>

        <!-- Desktop Nav -->
        <nav class="hidden md:flex items-center gap-2 lg:gap-4">
            <a class="nav-link w-max whitespace-nowrap text-on-surface-variant font-label-md text-[15px]" href="<?php echo $base_url; ?>/index.php#home" data-section="home">Home</a>
            
            <!-- Services Dropdown Wrapper -->
            <div class="nav-dropdown-wrapper relative flex items-center h-full py-2" id="services-wrapper">
                <a href="<?php echo $base_url; ?>/services/services.php" id="services-trigger" class="nav-link whitespace-nowrap text-on-surface-variant font-label-md text-[15px] flex items-center gap-1" data-section="services" aria-haspopup="true" aria-expanded="false">
                    Services
                    <span class="material-symbols-outlined text-[16px] text-outline opacity-70 transition-transform duration-300 ease-out" id="services-chevron" aria-hidden="true">expand_more</span>
                </a>
                
                <div class="nav-dropdown absolute -left-6 top-[calc(100%-4px)] w-[260px] bg-surface/95 backdrop-blur-3xl overflow-hidden z-50 p-2">
                    <div class="px-3 pb-2 pt-1 text-[11px] font-bold tracking-wider text-outline uppercase">Clinical Offerings</div>
                    
                    <!-- Dynamically generated services from data source -->
                    <?php foreach($services as $key => $service): ?>
                        <?php $isEmergency = stripos($key, 'emergency') !== false; ?>
                        
                        <a href="<?php echo $base_url; ?>/services/<?php echo urlencode($key); ?>.php" 
                           class="dropdown-item block px-3 py-2.5 font-label-md text-[14px] flex items-center justify-between group/item <?php echo $isEmergency ? 'is-emergency text-red-700 hover:text-red-800 bg-red-50/60 hover:bg-red-100/60' : 'text-on-surface hover:text-primary'; ?>" 
                           data-section="<?php echo htmlspecialchars($key); ?>">
                            
                            <span>
                                <?php echo htmlspecialchars($service['label']); ?>
                                <?php if($isEmergency): ?>
                                    <span class="inline-block text-[11px] font-bold tracking-wide text-red-600 opacity-90 ml-1">— Call Now</span>
                                <?php endif; ?>
                            </span>
                            
                            <span class="material-symbols-outlined text-[14px] opacity-0 -translate-x-2 transition-all duration-300 group-hover/item:opacity-100 group-hover/item:translate-x-0" aria-hidden="true">
                                <?php echo $isEmergency ? 'warning' : 'chevron_right'; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                    
                    <div class="border-t border-outline-variant/20 my-2 mx-2"></div>
                    
                    <!-- Dynamically links to Dedicated Services list page -->
                    <a href="<?php echo $base_url; ?>/services/services.php" class="block px-3 py-2.5 bg-surface-container-lowest hover:bg-primary/5 text-primary text-center rounded-lg font-semibold text-[13px] transition-colors">
                        View All Services
                    </a>
                </div>
            </div>

            <a class="nav-link w-max whitespace-nowrap text-on-surface-variant font-label-md text-[15px]" href="<?php echo $base_url; ?>/index.php#faq" data-section="faq">FAQ & Contact</a>
            <a class="nav-link w-max whitespace-nowrap text-on-surface-variant font-label-md text-[15px]" href="<?php echo $base_url; ?>/index.php#dashboard" data-section="dashboard">Dashboard</a>
        </nav>

        <!-- Right-side Actions -->
        <div class="flex items-center gap-3 lg:gap-4">

            <!-- Click-to-call phone number -->
            <a href="tel:+15550148" class="hidden lg:flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-primary/80 transition-colors mr-1">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">call</span>
                (555) 555-0148
            </a>

            <!-- Login / Sign Up dropdown (desktop) -->
            <div class="auth-dropdown-wrapper relative hidden md:block">
                <!-- Premium Account Button -->
                <button class="flex items-center gap-1.5 h-10 px-4 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-[14px] font-medium transition-all duration-300 whitespace-nowrap" aria-haspopup="true" aria-expanded="false" id="auth-btn">
                    <span class="material-symbols-outlined text-[18px] opacity-80" aria-hidden="true">account_circle</span>
                    <span class="truncate max-w-[100px]">Account</span>
                    <span class="material-symbols-outlined text-[16px] text-outline opacity-70 transition-transform duration-300 ease-out" id="auth-chevron" aria-hidden="true">expand_more</span>
                </button>

                <div class="auth-dropdown absolute right-0 top-[calc(100%+6px)] w-52 bg-surface/95 backdrop-blur-3xl overflow-hidden z-50 p-2">
                    <a href="<?php echo $base_url; ?>/auth/login.php?mode=login" class="dropdown-item flex items-center gap-3 px-3 py-2.5 text-on-surface hover:text-primary font-label-md text-[14px]">
                        <span class="material-symbols-outlined text-[18px] opacity-80" aria-hidden="true">login</span>
                        Log In
                    </a>
                    <div class="border-t border-outline-variant/20 my-1 mx-2"></div>
                    <a href="<?php echo $base_url; ?>/auth/login.php?mode=register" class="dropdown-item flex items-center gap-3 px-3 py-2.5 text-on-surface hover:text-primary font-label-md text-[14px]">
                        <span class="material-symbols-outlined text-[18px] opacity-80" aria-hidden="true">person_add</span>
                        Sign Up
                    </a>
                </div>
            </div>

            <!-- Book Appointment CTA (desktop) -->
            <a href="<?php echo $base_url; ?>/booking.php" data-section="booking" class="booking-btn hidden md:flex h-10 px-6 rounded-full bg-primary text-on-primary font-label-md text-[14px] font-medium shadow-[0_4px_14px_rgba(0,71,141,0.25)] hover:shadow-[0_6px_20px_rgba(0,71,141,0.4)] hover:-translate-y-0.5 active:translate-y-0 active:shadow-sm transition-all duration-300 items-center gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">calendar_month</span>
                Book Appointment
            </a>

            <!-- Mobile Controls -->
            <div class="md:hidden flex items-center gap-2">
                <!-- Persistent Mobile Booking Icon -->
                <a href="<?php echo $base_url; ?>/booking.php" class="mobile-booking-btn flex items-center justify-center h-10 w-10 rounded-full bg-primary/10 text-primary hover:bg-primary/20 transition-colors" aria-label="Book Appointment">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_month</span>
                </a>
                
                <!-- Hamburger Menu -->
                <button id="mobile-menu-btn" class="flex items-center justify-center h-10 w-10 rounded-full hover:bg-surface-container transition-colors text-on-surface-variant" aria-label="Toggle mobile menu">
                    <span class="material-symbols-outlined text-2xl transition-transform duration-300" aria-hidden="true">menu</span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Menu Layout Refined into Logical Sections -->
<div id="mobile-menu" class="hidden md:hidden fixed top-[72px] left-0 right-0 z-40 bg-surface/98 backdrop-blur-xl shadow-2xl border-b border-outline-variant/10 px-6 py-8 flex flex-col gap-8 max-h-[calc(100vh-72px)] overflow-y-auto">
    
    <!-- Navigation Section -->
    <div class="flex flex-col gap-2">
        <p class="text-[11px] font-bold text-outline tracking-wider uppercase mb-1 px-3">Menu</p>
        <a class="nav-link w-max text-on-surface-variant text-base" href="<?php echo $base_url; ?>/index.php#home" data-section="home">Home</a>
        
        <!-- Mobile Services Accordion (Dynamic) -->
        <div class="flex flex-col">
            <button id="mobile-services-btn" class="nav-link flex items-center justify-between w-full text-left text-on-surface-variant text-base" data-section="services">
                Services
                <span class="material-symbols-outlined text-[20px] transition-transform duration-300" id="mobile-services-chevron" aria-hidden="true">expand_more</span>
            </button>
            <div id="mobile-services-menu" class="hidden flex-col gap-1 pl-4 mt-2 border-l-2 border-outline-variant/20 ml-4 py-1">
                <?php foreach($services as $key => $service): ?>
                    <?php $isEmergency = stripos($key, 'emergency') !== false; ?>
                    
                    <a class="dropdown-item block w-full px-3 py-2 font-label-md text-[14px] flex items-center justify-between <?php echo $isEmergency ? 'is-emergency text-red-700 hover:text-red-800 bg-red-50/50' : 'text-on-surface-variant hover:text-primary'; ?>" 
                       href="<?php echo $base_url; ?>/services/<?php echo urlencode($key); ?>.php" 
                       data-section="<?php echo htmlspecialchars($key); ?>">
                        <span>
                            <?php echo htmlspecialchars($service['label']); ?>
                            <?php if($isEmergency): ?>
                                <span class="inline-block text-[11px] opacity-90 font-bold ml-1 text-red-600">— Call Now</span>
                            <?php endif; ?>
                        </span>
                        <?php if($isEmergency): ?>
                            <span class="material-symbols-outlined text-[16px] text-red-600" aria-hidden="true">warning</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
                <!-- Dynamically links to Dedicated Services list page -->
                <a class="dropdown-item block w-full px-3 py-2 mt-1 text-primary font-semibold text-[13px]" href="<?php echo $base_url; ?>/services/services.php">
                    View All Services &rarr;
                </a>
            </div>
        </div>

        <a class="nav-link w-max text-on-surface-variant text-base" href="<?php echo $base_url; ?>/index.php#faq" data-section="faq">FAQ & Contact</a>
        <a class="nav-link w-max text-on-surface-variant text-base" href="<?php echo $base_url; ?>/index.php#dashboard" data-section="dashboard">Dashboard</a>
    </div>

    <!-- Account Section -->
    <div class="flex flex-col gap-2 pt-2 border-t border-outline-variant/10" id="mobile-auth-section">
        <p class="text-[11px] font-bold text-outline tracking-wider uppercase mb-1 px-3">Account</p>
        <a href="<?php echo $base_url; ?>/auth/login.php?mode=login" class="dropdown-item flex items-center gap-3 px-3 py-2.5 text-on-surface-variant text-base">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">login</span> Log In
        </a>
        <a href="<?php echo $base_url; ?>/auth/login.php?mode=register" class="dropdown-item flex items-center gap-3 px-3 py-2.5 text-on-surface-variant text-base">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person_add</span> Sign Up
        </a>
    </div>

    <!-- Primary Action Section -->
    <div class="pt-2">
        <a href="<?php echo $base_url; ?>/booking.php" data-section="booking" class="booking-btn flex items-center justify-center gap-2 h-12 w-full rounded-full bg-primary text-on-primary font-label-md text-base shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_month</span> Book Appointment
        </a>
    </div>
</div>

<script>
    // Bridge PHP service context to JavaScript
    window.__currentServiceKey = <?= isset($currentServiceKey) ? json_encode($currentServiceKey) : 'null' ?>;

    document.addEventListener('DOMContentLoaded', () => {

        // --- Fetch Session For Login-Aware State ---
        fetch('<?php echo $base_url; ?>/api/get-session-user.php')
            .then(res => {
                if (!res.ok) throw new Error('Not logged in');
                return res.json();
            })
            .then(response => {
                if (response.status === 'success' && response.data) {
                    const user = response.data;
                    
                    // Desktop Account Dropdown Trigger Modification
                    const authBtn = document.getElementById('auth-btn');
                    if (authBtn) {
                        authBtn.innerHTML = `
                            <span class="material-symbols-outlined text-[18px] opacity-80" aria-hidden="true">account_circle</span>
                            <span class="truncate max-w-[100px]">${user.first_name}</span>
                            <span class="material-symbols-outlined text-[16px] text-outline opacity-70 transition-transform duration-300 ease-out" id="auth-chevron" aria-hidden="true">expand_more</span>
                        `;
                        
                        // Re-bind hover logic for the new chevron
                        const authChevron = document.getElementById('auth-chevron');
                        const authWrapper = authBtn.closest('.auth-dropdown-wrapper');
                        if (authWrapper && authChevron) {
                            authWrapper.addEventListener('mouseenter', () => authChevron.style.transform = 'rotate(180deg)');
                            authWrapper.addEventListener('mouseleave', () => authChevron.style.transform = 'rotate(0deg)');
                        }
                    }

                    // Desktop Dropdown Population
                    const authDropdown = document.querySelector('.auth-dropdown');
                    if (authDropdown) {
                        authDropdown.innerHTML = `
                            <div class="px-3 py-2 text-[11px] font-bold tracking-wider text-outline uppercase border-b border-outline-variant/10 mb-1 mx-2">Hi, ${user.first_name}</div>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="dropdown-item flex items-center gap-3 px-3 py-2.5 text-on-surface hover:text-primary font-label-md text-[14px]">
                                <span class="material-symbols-outlined text-[18px] opacity-80" aria-hidden="true">dashboard</span> Dashboard
                            </a>
                            <a href="<?php echo $base_url; ?>/auth/logout.php" class="dropdown-item flex items-center gap-3 px-3 py-2.5 text-red-600 hover:text-red-700 font-label-md text-[14px]">
                                <span class="material-symbols-outlined text-[18px] opacity-80" aria-hidden="true">logout</span> Log Out
                            </a>
                        `;
                    }

                    // Mobile Menu Auth Section Population
                    const mobileAuthSection = document.getElementById('mobile-auth-section');
                    if (mobileAuthSection) {
                        mobileAuthSection.innerHTML = `
                            <p class="text-[11px] font-bold text-outline tracking-wider uppercase mb-1 px-3">Hi, ${user.first_name}</p>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="dropdown-item flex items-center gap-3 px-3 py-2.5 text-on-surface-variant text-base">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">dashboard</span> Dashboard
                            </a>
                            <a href="<?php echo $base_url; ?>/auth/logout.php" class="dropdown-item flex items-center gap-3 px-3 py-2.5 text-red-600 text-base">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span> Log Out
                            </a>
                        `;
                    }
                }
            })
            .catch(err => {
                // If endpoint returns an error (401), keep default login/register layout unharmed
            });


        // --- Mobile Menu Toggle ---
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu    = document.getElementById('mobile-menu');
        const menuIcon      = mobileMenuBtn ? mobileMenuBtn.querySelector('span') : null;

        if (mobileMenuBtn && mobileMenu && menuIcon) {
            mobileMenuBtn.addEventListener('click', () => {
                const isHidden = mobileMenu.classList.contains('hidden');
                mobileMenu.classList.toggle('hidden');
                
                // Micro-animation on icon
                menuIcon.style.transform = 'scale(0.8) rotate(-90deg)';
                setTimeout(() => {
                    menuIcon.textContent = isHidden ? 'close' : 'menu';
                    menuIcon.style.transform = 'scale(1) rotate(0deg)';
                }, 150);
            });
        }

        // --- Auth Dropdown chevron rotation (Initial Load binding) ---
        const authBtn     = document.getElementById('auth-btn');
        const authChevron = document.getElementById('auth-chevron');
        const authWrapper = authBtn ? authBtn.closest('.auth-dropdown-wrapper') : null;
        if (authWrapper && authChevron) {
            authWrapper.addEventListener('mouseenter', () => authChevron.style.transform = 'rotate(180deg)');
            authWrapper.addEventListener('mouseleave', () => authChevron.style.transform = 'rotate(0deg)');
        }

        // --- Nav Dropdown Accessibility & Animation ---
        const navWrapper  = document.getElementById('services-wrapper');
        const navChevron  = document.getElementById('services-chevron');
        const navTrigger  = document.getElementById('services-trigger');

        const expandNav = () => {
            if (navChevron && navTrigger) {
                navChevron.style.transform = 'rotate(180deg)';
                navTrigger.setAttribute('aria-expanded', 'true');
            }
        };
        
        const collapseNav = () => {
            if (navWrapper && !navWrapper.classList.contains('force-show')) {
                if (navChevron && navTrigger) {
                    navChevron.style.transform = 'rotate(0deg)';
                    navTrigger.setAttribute('aria-expanded', 'false');
                }
            }
        };
        
        if (navWrapper) {
            navWrapper.addEventListener('mouseenter', expandNav);
            navWrapper.addEventListener('mouseleave', collapseNav);
            navWrapper.addEventListener('focusin', expandNav);
            navWrapper.addEventListener('focusout', collapseNav);
        }

        // --- Mobile Services Accordion Toggle ---
        const mobileServicesBtn     = document.getElementById('mobile-services-btn');
        const mobileServicesMenu    = document.getElementById('mobile-services-menu');
        const mobileServicesChevron = document.getElementById('mobile-services-chevron');

        if (mobileServicesBtn && mobileServicesMenu && mobileServicesChevron) {
            mobileServicesBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const isHidden = mobileServicesMenu.classList.contains('hidden');
                mobileServicesMenu.classList.toggle('hidden', !isHidden);
                mobileServicesMenu.classList.toggle('flex', isHidden);
                mobileServicesChevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            });
        }

        // --- Premium Sticky Header Background & Resize Shift ---
        const header = document.getElementById('main-header');
        const headerContainer = document.getElementById('header-container');
        
        if (header && headerContainer) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 20) {
                    // Scrolled state
                    header.classList.add('shadow-[0_4px_24px_rgba(0,0,0,0.03)]', 'bg-surface/90', 'border-outline-variant/15', 'backdrop-blur-2xl');
                    header.classList.remove('bg-surface/70', 'border-transparent', 'backdrop-blur-xl');
                    headerContainer.classList.add('h-16', 'md:h-16');
                    headerContainer.classList.remove('h-20', 'md:h-24');
                } else {
                    // Top of page state
                    header.classList.remove('shadow-[0_4px_24px_rgba(0,0,0,0.03)]', 'bg-surface/90', 'border-outline-variant/15', 'backdrop-blur-2xl');
                    header.classList.add('bg-surface/70', 'border-transparent', 'backdrop-blur-xl');
                    headerContainer.classList.remove('h-16', 'md:h-16');
                    headerContainer.classList.add('h-20', 'md:h-24');
                }
            }, { passive: true });
        }

        // --- Smooth Scrolling & Active Nav (Scrollspy) Logic ---
        // Included .booking-btn so we can apply specific ring styles to it natively
        const allNavLinks  = document.querySelectorAll('a.nav-link[data-section], a.dropdown-item[data-section], button.nav-link[data-section], a.booking-btn[data-section]');
        const currentPage  = window.location.pathname.split('/').pop();
        const currentHash  = window.location.hash.replace('#', '');
        let isClickScrolling  = false;
        let clickScrollTimeout;

        // Populate dynamic sub-sections from PHP output dynamically
        const serviceKeys = <?php echo json_encode(array_keys($services)); ?>;

        function setActive(sectionId) {
            allNavLinks.forEach(link => {
                if (link.classList.contains('booking-btn')) {
                    if (link.getAttribute('data-section') === sectionId) {
                        link.classList.add('ring-4', 'ring-primary/40', 'ring-offset-2', 'ring-offset-surface');
                    } else {
                        link.classList.remove('ring-4', 'ring-primary/40', 'ring-offset-2', 'ring-offset-surface');
                    }
                } else {
                    link.classList.toggle('active', link.getAttribute('data-section') === sectionId);
                }
            });

            const isService = serviceKeys.includes(sectionId) || sectionId === 'services';
            const isSubChild = serviceKeys.includes(sectionId);
            
            // Auto-show dropdown logic for desktop
            if (navWrapper && navChevron) {
                if (isSubChild) {
                    navWrapper.classList.add('force-show');
                    navChevron.style.transform = 'rotate(180deg)';
                } else {
                    navWrapper.classList.remove('force-show');
                    if (!navWrapper.matches(':hover')) {
                        navChevron.style.transform = 'rotate(0deg)';
                    }
                }
            }

            // Mobile Accordion state handling
            if (mobileServicesBtn && mobileServicesMenu && mobileServicesChevron) {
                mobileServicesBtn.classList.toggle('text-primary', isService);
                mobileServicesBtn.classList.toggle('active', isService);
                
                if (isSubChild) {
                    mobileServicesMenu.classList.remove('hidden');
                    mobileServicesMenu.classList.add('flex');
                    mobileServicesChevron.style.transform = 'rotate(180deg)';
                } else {
                    mobileServicesMenu.classList.add('hidden');
                    mobileServicesMenu.classList.remove('flex');
                    mobileServicesChevron.style.transform = 'rotate(0deg)';
                }
            }
        }

        // Intercept logic if traversing inner-pages dynamically (instead of index.php hash routes)
        if (currentPage && currentPage !== 'index.php' && currentPage !== '') {
            const currentPath = window.location.pathname;
            
            if (currentPath.includes('/services/')) {
                setActive('services'); 
            } else if (currentPath.includes('booking.php')) {
                setActive('booking');
            } else {
                setActive('');
            }
            return; 
        }

        if (currentHash) setActive(currentHash);

        allNavLinks.forEach(link => {
            // Exclude links that intentionally go to other pages 
            if (link.hasAttribute('href') && !link.getAttribute('href').includes('index.php#')) return;

            link.addEventListener('click', (e) => {
                const sectionId = link.getAttribute('data-section');
                const target    = document.getElementById(sectionId);

                // Close mobile menu gracefully
                if (mobileMenu && !link.closest('nav') && window.innerWidth < 768) {
                    mobileMenu.classList.add('hidden');
                    if (menuIcon) {
                        menuIcon.textContent = 'menu';
                        menuIcon.style.transform = 'scale(1) rotate(0deg)';
                    }
                }

                if (target) {
                    e.preventDefault();
                    isClickScrolling = true;
                    clearTimeout(clickScrollTimeout);
                    clickScrollTimeout = setTimeout(() => {
                        isClickScrolling = false;
                        updateActiveNav();
                    }, 800);

                    setActive(sectionId);

                    const headerOffset   = header ? header.offsetHeight : 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.scrollY - headerOffset;
                    
                    window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                }
            });
        });

        function updateActiveNav() {
            if (isClickScrolling) return;
            const scrollY = window.scrollY;
            const headerOffset = Math.max(250, window.innerHeight * 0.35); 
            let current = 'home';

            const uniqueSectionIds = Array.from(allNavLinks)
                .map(link => link.getAttribute('data-section'))
                .filter((id, idx, arr) => id && arr.indexOf(id) === idx);

            const sortedSections = uniqueSectionIds
                .map(id => {
                    const el = document.getElementById(id);
                    return el ? { id, top: el.getBoundingClientRect().top + window.scrollY } : null;
                })
                .filter(Boolean)
                .sort((a, b) => a.top - b.top);

            sortedSections.forEach(sec => {
                if (scrollY >= sec.top - headerOffset) {
                    current = sec.id;
                }
            });

            if ((window.innerHeight + Math.round(window.scrollY)) >= document.body.offsetHeight - 20) {
                current = 'dashboard';
            }

            setActive(current);
        }

        window.addEventListener('scroll', updateActiveNav, { passive: true });
        updateActiveNav(); 
    });
</script>