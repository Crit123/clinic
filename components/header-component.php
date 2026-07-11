<?php 
// Ensure the canonical services registry is available for dynamic rendering
if (!isset($canonicalServices)) {
    require_once __DIR__ . '/../api/data/services-data.php';
    $canonicalServices = getAllServices();
}
$services = $canonicalServices;

// Base URL ensures links work correctly even when included from subdirectories like /auth
if (!isset($base_url)) {
    require_once __DIR__ . '/../config/app.php';
}
?>

<style>
    /* Resting header heights for pages to offset their content against. */
    :root {
        --header-offset-mobile: 120px;
        --header-offset: 164px;
    }

    .nav-link-custom {
        position: relative;
        color: var(--color-on-surface, #1F1C18);
        font-weight: 600;
        font-size: 17px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0em;
        transform: translateY(0);
        transition: color 0.24s cubic-bezier(0.4, 0, 0.2, 1),
                    transform 0.24s cubic-bezier(0.4, 0, 0.2, 1),
                    letter-spacing 0.24s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .nav-link-custom:hover {
        color: var(--color-primary, #05449E);
        transform: translateY(-2px);
        letter-spacing: 0.02em;
    }

    .nav-link-custom.active {
        color: var(--color-primary, #05449E);
        /* Active state stays flat unless specifically hovered */
        transform: translateY(0);
        letter-spacing: 0em;
    }

    /* Shared Sliding Nav Indicator */
    #nav-active-indicator {
        position: absolute;
        height: 2px;
        background-color: var(--color-primary, #05449E);
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                    width 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                    top 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.2s ease;
        pointer-events: none;
        border-radius: 9999px;
    }

    /* Premium Dropdowns - Elegant Fade & Scale */
    .auth-dropdown, .nav-dropdown {
        opacity: 0;
        visibility: hidden;
        transform: translateY(12px) scale(0.97);
        transform-origin: top center;
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 20px 50px -12px rgba(15, 23, 42, 0.12), 0 4px 12px -4px rgba(15, 23, 42, 0.04);
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

    .nav-dropdown-wrapper.force-close .nav-dropdown,
    .auth-dropdown-wrapper.force-close .auth-dropdown {
        opacity: 0 !important;
        visibility: hidden !important;
        transform: translateY(12px) scale(0.97) !important;
    }
    
    .nav-dropdown-wrapper.force-close .chevron-rotate,
    .auth-dropdown-wrapper.force-close .chevron-rotate {
        transform: rotate(0deg) !important;
    }

    .dropdown-item {
        border-radius: 0.75rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dropdown-item:not(.is-emergency):hover {
        transform: translateX(4px);
    }
    .dropdown-item.is-emergency:hover {
        transform: translateX(4px);
    }

    /* Chevron rotation */
    .chevron-rotate {
        transition: transform 0.25s ease-in-out;
    }
    .auth-dropdown-wrapper:hover .chevron-rotate,
    .auth-dropdown-wrapper:focus-within .chevron-rotate,
    .nav-dropdown-wrapper:hover .chevron-rotate,
    .nav-dropdown-wrapper:focus-within .chevron-rotate {
        transform: rotate(180deg);
    }

    /* Floating Header Scroll Adjustments (Target States) */
    #main-header.is-scrolled #utility-bar {
        height: 0px !important;
        opacity: 0;
        pointer-events: none;
        border-bottom-width: 0px;
    }
    
    #main-header.is-scrolled #main-nav-container {
        margin-top: 18px !important;
        margin-bottom: 18px !important;
        max-width: 1200px !important;
        width: 92% !important;
        height: 74px !important; 
        border-radius: 22px !important;
        background-color: rgba(255, 255, 255, 0.82) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        padding-left: 28px !important;
        padding-right: 28px !important;
        transform: scale(1);
    }

    #main-header:not(.is-scrolled) #main-nav-container {
        transform: scale(0.99); /* Subtle materialize effect */
    }

    #main-header.is-scrolled .logo-img {
        width: 32px !important;
        height: 32px !important;
    }

    #main-header.is-scrolled .logo-text {
        font-size: 18px !important;
    }

    #main-header.is-scrolled .logo-tagline {
        max-height: 0 !important;
        opacity: 0 !important;
        margin-top: 0 !important;
    }

    /* Reduces gap between navigation items ONLY in scrolled state */
    #main-header.is-scrolled nav.hidden.md\:flex {
        gap: 1.5rem !important; 
    }

    /* Scrolled state modifications for Buttons */
    #main-header.is-scrolled .auth-btn-label {
        max-width: 0 !important;
        opacity: 0 !important;
        margin-left: 0 !important;
    }
    
    #main-header.is-scrolled #auth-btn {
        padding: 0.75rem !important;
        gap: 0 !important;
    }

    #main-header.is-scrolled .booking-btn-full-label {
        max-width: 0 !important;
        opacity: 0 !important;
        margin-left: 0 !important;
    }

    #main-header.is-scrolled .booking-btn {
        padding-left: 1.25rem !important;
        padding-right: 1.25rem !important;
    }

    /* =========================================================================
       UNIFIED SCROLL TRANSITIONS (Synced to 0.35s Cubic Bezier)
       ========================================================================= */

    #utility-bar {
        transition: height 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    border-bottom-width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #main-nav-container {
        transition: height 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    margin 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    width 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    padding 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    border-radius 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    background-color 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    backdrop-filter 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    -webkit-backdrop-filter 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    box-shadow 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    border-color 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .logo-img-wrapper,
    .logo-img {
        transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .logo-text {
        transition: font-size 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .logo-tagline {
        max-height: 20px;
        opacity: 1;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    margin-top 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    nav.hidden.md\:flex {
        transition: gap 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .booking-btn-full-label {
        display: inline-block;
        vertical-align: bottom; /* Prevents text jumping during collapse */
    }

    .auth-btn-label,
    .booking-btn-full-label {
        max-width: 200px;
        opacity: 1;
        overflow: hidden;
        white-space: nowrap;
        transition: max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    margin 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    #auth-btn,
    .booking-btn {
        transition: padding 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    gap 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Mobile menu animations */
    #mobile-menu-overlay {
        transition: opacity 0.3s ease;
    }
    #mobile-menu-content {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<!-- Main Header wrapper -->
<header class="fixed top-0 w-full z-50 flex flex-col pointer-events-none font-sans" id="main-header">
    
    <!-- SECTION 1: Top Utility Bar -->
    <div id="utility-bar" class="w-full h-10 bg-surface-container-lowest border-b border-outline-variant hidden lg:block pointer-events-auto overflow-hidden">
        <div class="max-w-[1280px] h-full mx-auto px-8 flex justify-between items-center text-sm font-medium text-on-surface-variant">
            <!-- Left Info Group -->
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <span class="icon-line text-[18px] text-primary" aria-hidden="true">schedule</span>
                    <span>Mon–Sat: <strong class="text-on-surface">8:00 AM – 6:00 PM</strong></span>
                </div>
                <div class="w-[1px] h-[18px] bg-outline-variant"></div>
                <a href="tel:+15555550148" class="flex items-center gap-2 hover:text-primary transition-colors">
                    <span class="icon-line text-[18px] text-primary" aria-hidden="true">call</span>
                    <span>(555) 555-0148</span>
                </a>
                <div class="w-[1px] h-[18px] bg-outline-variant"></div>
                <div class="flex items-center gap-2">
                    <span class="icon-line text-[18px] text-primary" aria-hidden="true">location_on</span>
                    <span>123 Dental Suite, Medical District</span>
                </div>
            </div>

            <!-- Right Info/Social Group -->
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-2 text-error font-semibold bg-error-container px-3 py-1 rounded-full text-xs">
                    <span class="inline-block w-2 h-2 rounded-full bg-error animate-pulse"></span>
                    24/7 Dental Emergency Available
                </span>
                <div class="w-[1px] h-[18px] bg-outline-variant"></div>
                <div class="flex items-center gap-4 text-on-surface-variant">
                    <a href="#" class="hover:text-primary transition-colors opacity-90 hover:opacity-100" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"/></svg>
                    </a>
                    <a href="#" class="hover:text-primary transition-colors opacity-90 hover:opacity-100" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="mailto:info@dentalcarepro.com" class="hover:text-primary transition-colors opacity-90 hover:opacity-100" aria-label="Email">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: MAIN NAVIGATION -->
    <div class="w-full mt-3 mb-3 pointer-events-auto">
        <div id="main-nav-container" class="max-w-[1440px] 2xl:max-w-[1500px] w-full mx-auto h-20 bg-surface-container-lowest border border-outline-variant/50 rounded-[24px] shadow-[0_12px_40px_rgba(15,23,42,0.08)] px-10 flex items-center justify-between">
            
            <!-- LEFT: Logo Block -->
            <a href="<?php echo $base_url; ?>/index.php#home" class="flex items-center gap-4 cursor-pointer group" data-section="home" aria-label="Go to homepage">
                <div class="logo-img-wrapper">
                    <img src="<?php echo $base_url; ?>/assets/img/brand-logo.png" alt="DentalCare Pro Logo" class="logo-img h-10 w-10 object-contain" />
                </div>
                <div class="flex flex-col justify-center">
                    <span class="logo-text font-display font-extrabold text-[22px] text-on-surface leading-none tracking-tight">DentalCare Pro</span>
                    <span class="logo-tagline text-[13px] font-medium text-on-surface-variant mt-0.5">Healthy Smile, Confident You</span>
                </div>
            </a>

            <!-- CENTER: Navigation Menu -->
            <nav id="desktop-nav" class="hidden md:flex items-center gap-10 relative">
                
                <!-- Shared Active Sliding Indicator -->
                <div id="nav-active-indicator" style="width: 0; left: 0; opacity: 0; top: 0;"></div>

                <a class="nav-link-custom py-2" href="<?php echo $base_url; ?>/index.php#home" data-section="home">Home</a>
                <a class="nav-link-custom py-2" href="<?php echo $base_url; ?>/index.php#about" data-section="about">About</a>
                
                <!-- Services Dropdown (Dynamic) -->
                <div class="nav-dropdown-wrapper relative flex items-center h-full py-4" id="services-wrapper">
                    <a href="<?php echo $base_url; ?>/index.php#services" id="services-trigger" class="nav-link-custom flex items-center gap-1 py-2" data-section="services" aria-haspopup="true" aria-expanded="false">
                        Services
                        <span class="icon-line text-[18px] text-on-surface-variant chevron-rotate" aria-hidden="true">expand_more</span>
                    </a>
                    
                    <div class="nav-dropdown absolute left-1/2 -translate-x-1/2 top-[calc(100%-8px)] w-[280px] bg-surface-container-lowest rounded-[20px] shadow-xl border border-surface-variant z-50 p-2 pointer-events-auto">
                        <a href="<?php echo $base_url; ?>/index.php#dentists" class="dropdown-item flex items-center gap-3 px-4 py-3 text-on-surface hover:text-primary hover:bg-surface-container-low font-semibold text-[15px]" data-section="dentists">
                            <span class="icon-line text-[20px] opacity-70" aria-hidden="true">stethoscope</span>
                            Meet the Dentist
                        </a>
                        <div class="border-t border-surface-variant my-2 mx-2"></div>
                        <div class="px-4 pb-2 pt-2 text-[11px] font-bold tracking-widest text-outline uppercase">Clinical Offerings</div>
                        
                        <?php foreach($services as $key => $service): ?>
                            <?php $isEmergencyItem = stripos($key, 'emergency') !== false; ?>
                            
                            <a href="<?php echo $base_url; ?>/services/<?php echo urlencode($key); ?>.php" 
                               class="dropdown-item block px-4 py-3 font-semibold text-[15px] flex items-center justify-between group/item <?php echo $isEmergencyItem ? 'is-emergency text-error hover:text-error bg-error-container/20 hover:bg-error-container' : 'text-on-surface hover:text-primary hover:bg-surface-container-low'; ?>" 
                               data-section="<?php echo htmlspecialchars($key); ?>">
                                
                                <div class="flex flex-col gap-0.5">
                                    <span class="flex items-center gap-2">
                                        <?php echo htmlspecialchars($service['label']); ?>
                                        <?php if($isEmergencyItem): ?>
                                            <span class="inline-block text-[10px] font-bold tracking-wide text-error opacity-90">— Call Now</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-[11px] font-medium text-on-surface-variant flex items-center gap-1 group-hover/item:opacity-90 transition-opacity">
                                        <span class="icon-line text-[12px]">schedule</span> <?php echo htmlspecialchars($service['duration']); ?>
                                    </span>
                                </div>
                                
                                <span class="icon-line text-[16px] opacity-0 -translate-x-2 transition-all duration-200 group-hover/item:opacity-100 group-hover/item:translate-x-0" aria-hidden="true"><?php echo $isEmergencyItem ? 'warning' : 'chevron_right'; ?></span>
                            </a>
                        <?php endforeach; ?>
                        
                        <div class="border-t border-surface-variant my-2 mx-2"></div>
                        
                        <a href="<?php echo $base_url; ?>/index.php#services" class="block px-3 py-3 bg-surface-container-low hover:bg-primary-container text-primary text-center rounded-xl font-bold text-[14px] transition-colors">
                            View All Services
                        </a>
                    </div>
                </div>

                <a class="nav-link-custom py-2" href="<?php echo $base_url; ?>/index.php#faq" data-section="faq">FAQ & Contact</a>

                <!-- Explore Dropdown -->
                <div class="nav-dropdown-wrapper relative flex items-center h-full py-4" id="explore-wrapper">
                    <button id="explore-trigger" class="nav-link-custom flex items-center gap-1 py-2" aria-haspopup="true" aria-expanded="false">
                        Explore
                        <span class="icon-line text-[18px] text-on-surface-variant chevron-rotate" aria-hidden="true">expand_more</span>
                    </button>
                    
                    <div class="nav-dropdown absolute left-1/2 -translate-x-1/2 top-[calc(100%-8px)] w-[280px] bg-surface-container-lowest rounded-[20px] shadow-xl border border-surface-variant z-50 p-2 pointer-events-auto">
                        <a href="<?= htmlspecialchars($base_url) ?>/index.php#dashboard" class="dropdown-item flex items-start gap-3 px-4 py-3 text-on-surface hover:text-primary hover:bg-surface-container-low transition-colors" data-section="dashboard">
                            <span class="icon-line text-[20px] opacity-70 mt-0.5" aria-hidden="true">dashboard</span>
                            <div>
                                <div class="font-semibold text-[15px]">Patient Portal</div>
                                <div class="text-[12px] text-on-surface-variant font-normal mt-0.5">Preview your patient dashboard</div>
                            </div>
                        </a>
                        
                        <div class="border-t border-surface-variant my-1 mx-2"></div>
                        
                        <a href="<?= htmlspecialchars($base_url) ?>/index.php#ai-assistant" class="dropdown-item flex items-start gap-3 px-4 py-3 text-on-surface hover:text-primary hover:bg-surface-container-low transition-colors" data-section="ai-assistant">
                            <span class="icon-line text-[20px] opacity-70 mt-0.5" aria-hidden="true">smart_toy</span>
                            <div>
                                <div class="font-semibold text-[15px]">AI Assistant</div>
                                <div class="text-[12px] text-on-surface-variant font-normal mt-0.5">Meet your 24/7 dental assistant</div>
                            </div>
                        </a>
                    </div>
                </div>

            </nav>

            <!-- RIGHT: Actions Area -->
            <div class="hidden md:flex items-center">
                
                <!-- Account Dropdown Wrapper -->
                <div class="auth-dropdown-wrapper relative mr-6">
                    <button id="auth-btn" class="flex items-center gap-3 h-12 px-5 bg-surface-container-lowest border border-outline-variant hover:border-outline rounded-full text-on-surface font-semibold text-[15px] transition-colors" aria-haspopup="true" aria-expanded="false">
                        <span class="icon-line text-[20px] text-outline" aria-hidden="true">person</span>
                        <span class="auth-btn-label flex items-center gap-3">
                            <span>Account</span>
                            <span class="icon-line text-[18px] text-on-surface-variant chevron-rotate" id="auth-chevron" aria-hidden="true">expand_more</span>
                        </span>
                    </button>

                    <div class="auth-dropdown absolute right-0 top-[calc(100%+8px)] w-56 bg-surface-container-lowest rounded-[20px] shadow-xl border border-surface-variant z-50 p-2 pointer-events-auto">
                        <a href="<?php echo $base_url; ?>/auth/login.php?mode=login" class="dropdown-item flex items-center gap-3 px-4 py-3 text-on-surface hover:text-primary hover:bg-surface-container-low font-semibold text-[15px]">
                            <span class="icon-line text-[20px] opacity-70" aria-hidden="true">login</span>
                            Log In
                        </a>
                        <div class="border-t border-surface-variant my-1 mx-2"></div>
                        <a href="<?php echo $base_url; ?>/auth/login.php?mode=register" class="dropdown-item flex items-center gap-3 px-4 py-3 text-on-surface hover:text-primary hover:bg-surface-container-low font-semibold text-[15px]">
                            <span class="icon-line text-[20px] opacity-70" aria-hidden="true">person_add</span>
                            Sign Up
                        </a>
                    </div>
                </div>

                <!-- Book Appointment Premium Button -->
                <a href="<?php echo $base_url; ?>/booking.php" data-section="booking" class="booking-btn h-[54px] px-7 rounded-[14px] bg-primary text-on-primary font-semibold text-[16px] shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/35 hover:-translate-y-0.5 active:translate-y-0 active:shadow-sm transition-all duration-250 flex items-center gap-3 whitespace-nowrap">
                    <span class="icon-line text-[20px]" aria-hidden="true">calendar_month</span>
                    <span>Book&nbsp;<span class="booking-btn-full-label">Appointment</span></span>
                </a>
            </div>

            <!-- Mobile Controls -->
            <div class="md:hidden flex items-center gap-3">
                <!-- Mobile Booking Icon Link -->
                <a href="<?php echo $base_url; ?>/booking.php" class="flex items-center justify-center h-11 w-11 rounded-full bg-primary/10 text-primary hover:bg-primary/20 transition-colors" aria-label="Book Appointment">
                    <span class="icon-line text-[22px]" aria-hidden="true">calendar_month</span>
                </a>
                
                <!-- Hamburger Menu Toggle Button -->
                <button id="mobile-menu-btn" class="flex items-center justify-center h-11 w-11 rounded-full hover:bg-surface-container transition-colors text-on-surface" aria-label="Toggle mobile menu">
                    <span class="icon-line text-[24px]" aria-hidden="true">menu</span>
                </button>
            </div>

        </div>
    </div>
</header>

<!-- MOBILE MENU (Premium Slide-out Drawer with Blur Overlay) -->
<div id="mobile-menu-overlay" class="hidden fixed inset-0 z-50 bg-inverse-surface/40 backdrop-blur-sm pointer-events-auto opacity-0 transition-opacity duration-300 font-sans">
    <div id="mobile-menu-content" class="absolute right-0 top-0 h-full w-[320px] bg-surface-container-lowest rounded-l-[24px] shadow-2xl flex flex-col justify-between p-6 transform translate-x-full transition-transform duration-300 ease-out">
        
        <div>
            <!-- Mobile Menu Header -->
            <div class="flex items-center justify-between pb-6 border-b border-surface-variant mb-6">
                <span class="font-bold text-[20px] text-on-surface">Menu</span>
                <button id="mobile-menu-close" class="h-10 w-10 rounded-full flex items-center justify-center hover:bg-surface-container-low transition-colors text-on-surface" aria-label="Close menu">
                    <span class="icon-line text-[24px]" aria-hidden="true">close</span>
                </button>
            </div>

            <!-- Mobile Navigation Link Stack -->
            <div class="flex flex-col gap-1">
                <a class="nav-link-custom flex items-center justify-between h-14 px-4 rounded-xl hover:bg-surface-container-low text-lg transition-colors" href="<?php echo $base_url; ?>/index.php#home" data-section="home">
                    <span>Home</span>
                    <span class="icon-line text-outline text-[20px]">chevron_right</span>
                </a>
                <a class="nav-link-custom flex items-center justify-between h-14 px-4 rounded-xl hover:bg-surface-container-low text-lg transition-colors" href="<?php echo $base_url; ?>/index.php#about" data-section="about">
                    <span>About</span>
                    <span class="icon-line text-outline text-[20px]">chevron_right</span>
                </a>
                
                <!-- Mobile Services Sub-accordion -->
                <div class="flex flex-col">
                    <button id="mobile-services-btn" class="nav-link-custom flex items-center justify-between w-full h-14 px-4 rounded-xl hover:bg-surface-container-low text-left text-lg transition-colors" data-section="services">
                        <span>Services</span>
                        <span class="icon-line text-outline text-[20px] transition-transform duration-300" id="mobile-services-chevron" aria-hidden="true">expand_more</span>
                    </button>
                    
                    <div id="mobile-services-menu" class="hidden flex-col gap-1 pl-4 mt-1 border-l-2 border-surface-variant ml-4 py-1">
                        <a class="dropdown-item block w-full px-4 py-3 font-semibold text-[15px] flex items-center gap-2 text-on-surface hover:text-primary hover:bg-surface-container-low"
                           href="<?php echo $base_url; ?>/index.php#dentists" data-section="dentists">
                            <span class="icon-line text-[18px] opacity-70" aria-hidden="true">stethoscope</span>
                            Meet the Dentist
                        </a>
                        <div class="border-t border-surface-variant my-1"></div>
                        <?php foreach($services as $key => $service): ?>
                            <?php $isEmergencyItem = stripos($key, 'emergency') !== false; ?>
                            
                            <a class="dropdown-item block w-full px-4 py-3 font-semibold text-[15px] flex items-center justify-between <?php echo $isEmergencyItem ? 'is-emergency text-error bg-error-container/20 hover:bg-error-container' : 'text-on-surface hover:text-primary hover:bg-surface-container-low'; ?>" 
                               href="<?php echo $base_url; ?>/services/<?php echo urlencode($key); ?>.php" 
                               data-section="<?php echo htmlspecialchars($key); ?>">
                                
                                <div class="flex flex-col gap-0.5">
                                    <span class="flex items-center gap-2">
                                        <?php echo htmlspecialchars($service['label']); ?>
                                        <?php if($isEmergencyItem): ?>
                                            <span class="inline-block text-[11px] font-bold text-error ml-1">— Call</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-[11px] font-medium text-on-surface-variant flex items-center gap-1">
                                        <span class="icon-line text-[12px]">schedule</span> <?php echo htmlspecialchars($service['duration']); ?>
                                    </span>
                                </div>

                                <?php if($isEmergencyItem): ?>
                                    <span class="icon-line text-[16px] text-error" aria-hidden="true">warning</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                        <a class="dropdown-item block w-full px-4 py-3 mt-1 text-primary hover:bg-surface-container-low font-bold text-[14px]" href="<?php echo $base_url; ?>/index.php#services" data-section="services">
                            View All Services &rarr;
                        </a>
                    </div>
                </div>

                <a class="nav-link-custom flex items-center justify-between h-14 px-4 rounded-xl hover:bg-surface-container-low text-lg transition-colors" href="<?php echo $base_url; ?>/index.php#faq" data-section="faq">
                    <span>FAQ & Contact</span>
                    <span class="icon-line text-outline text-[20px]">chevron_right</span>
                </a>

                <!-- Mobile Explore Sub-accordion -->
                <div class="flex flex-col">
                    <button id="mobile-explore-btn" class="nav-link-custom flex items-center justify-between w-full h-14 px-4 rounded-xl hover:bg-surface-container-low text-left text-lg transition-colors">
                        <span>Explore</span>
                        <span class="icon-line text-outline text-[20px] transition-transform duration-300" id="mobile-explore-chevron" aria-hidden="true">expand_more</span>
                    </button>
                    
                    <div id="mobile-explore-menu" class="hidden flex-col gap-1 pl-4 mt-1 border-l-2 border-surface-variant ml-4 py-1">
                        <a class="dropdown-item block w-full px-4 py-3 flex items-start gap-3 text-on-surface hover:text-primary hover:bg-surface-container-low" href="<?= htmlspecialchars($base_url) ?>/index.php#dashboard" data-section="dashboard">
                            <span class="icon-line text-[20px] opacity-70 mt-0.5" aria-hidden="true">dashboard</span>
                            <div>
                                <div class="font-semibold text-[15px]">Patient Portal</div>
                                <div class="text-[12px] text-on-surface-variant font-normal mt-0.5">Preview your dashboard</div>
                            </div>
                        </a>
                        <div class="border-t border-surface-variant my-1"></div>
                        <a class="dropdown-item block w-full px-4 py-3 flex items-start gap-3 text-on-surface hover:text-primary hover:bg-surface-container-low" href="<?= htmlspecialchars($base_url) ?>/index.php#ai-assistant" data-section="ai-assistant">
                            <span class="icon-line text-[20px] opacity-70 mt-0.5" aria-hidden="true">smart_toy</span>
                            <div>
                                <div class="font-semibold text-[15px]">AI Assistant</div>
                                <div class="text-[12px] text-on-surface-variant font-normal mt-0.5">Meet your 24/7 assistant</div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Mobile Account and CTA Bottom Block -->
        <div class="flex flex-col gap-4 border-t border-surface-variant pt-6">
            <div id="mobile-auth-section" class="flex flex-col gap-2">
                <a href="<?php echo $base_url; ?>/auth/login.php?mode=login" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low text-on-surface font-semibold text-base transition-colors">
                    <span class="icon-line text-[22px] text-outline" aria-hidden="true">login</span> 
                    Log In
                </a>
                <a href="<?php echo $base_url; ?>/auth/login.php?mode=register" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container-low text-on-surface font-semibold text-base transition-colors">
                    <span class="icon-line text-[22px] text-outline" aria-hidden="true">person_add</span> 
                    Sign Up
                </a>
            </div>

            <a href="<?php echo $base_url; ?>/booking.php" class="booking-btn h-14 rounded-2xl bg-primary text-on-primary font-semibold text-base flex items-center justify-center gap-3 shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/35 transition-all duration-200">
                <span class="icon-line text-[22px]" aria-hidden="true">calendar_month</span> 
                Book Appointment
            </a>
        </div>

    </div>
</div>

<script>
    // Bridge PHP service context to JavaScript
    window.__currentServiceKey = <?= isset($currentServiceKey) ? json_encode($currentServiceKey) : 'null' ?>;

    // --- Helper function for Shared Sliding Nav Indicator ---
    function updateActiveIndicator() {
        const nav = document.getElementById('desktop-nav');
        const indicator = document.getElementById('nav-active-indicator');
        if (!nav || !indicator) return;

        // Locate the active link inside the desktop navigation flex wrapper
        const activeLink = nav.querySelector('.nav-link-custom.active');
        
        if (activeLink) {
            const navRect = nav.getBoundingClientRect();
            const linkRect = activeLink.getBoundingClientRect();

            indicator.style.left = `${linkRect.left - navRect.left}px`;
            indicator.style.width = `${linkRect.width}px`;
            // Calculate top position exactly 6px below the link text bounding box
            indicator.style.top = `${(linkRect.bottom - navRect.top) + 6}px`;
            indicator.style.opacity = '1';
        } else {
            indicator.style.opacity = '0';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {

        // Setup resize and transition recalculations to keep the sliding underline perfectly aligned
        window.addEventListener('resize', updateActiveIndicator);
        const mainHeaderElement = document.getElementById('main-header');
        if (mainHeaderElement) {
            mainHeaderElement.addEventListener('transitionend', (e) => {
                if (e.propertyName === 'height' || e.propertyName === 'gap' || e.propertyName === 'padding') {
                    updateActiveIndicator();
                }
            });
        }

        // --- Fetch Session For Login-Aware State ---
        fetch('<?php echo $base_url; ?>/api/get-session-user.php')
            .then(res => {
                if (!res.ok) throw new Error('Not logged in');
                return res.json();
            })
            .then(response => {
                if (response.status === 'success' && response.data) {
                    const user = response.data;
                    
                    const authBtn = document.getElementById('auth-btn');
                    if (authBtn) {
                        authBtn.innerHTML = `
                            <span class="icon-line text-[20px] text-primary" aria-hidden="true">person</span>
                            <span class="auth-btn-label flex items-center gap-3">
                                <span class="truncate max-w-[120px] text-on-surface font-semibold">Hi, ${user.first_name}</span>
                                <span class="icon-line text-[18px] text-on-surface-variant chevron-rotate" id="auth-chevron" aria-hidden="true">expand_more</span>
                            </span>
                        `;
                    }

                    const authDropdown = document.querySelector('.auth-dropdown');
                    if (authDropdown) {
                        authDropdown.innerHTML = `
                            <div class="px-4 py-2.5 text-[11px] font-bold tracking-wider text-outline uppercase border-b border-surface-variant mb-1 mx-2">Hi, ${user.first_name}</div>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="dropdown-item flex items-center gap-3 px-4 py-3 text-on-surface hover:text-primary hover:bg-surface-container-low font-semibold text-[15px]">
                                <span class="icon-line text-[20px] opacity-70" aria-hidden="true">dashboard</span> Dashboard
                            </a>
                            <a href="<?php echo $base_url; ?>/auth/logout.php" class="dropdown-item flex items-center gap-3 px-4 py-3 text-error hover:text-error hover:bg-error-container/50 font-semibold text-[15px]">
                                <span class="icon-line text-[20px] opacity-70" aria-hidden="true">logout</span> Log Out
                            </a>
                        `;
                    }

                    const mobileAuthSection = document.getElementById('mobile-auth-section');
                    if (mobileAuthSection) {
                        mobileAuthSection.innerHTML = `
                            <div class="px-4 py-2 text-[11px] font-bold tracking-wider text-outline uppercase mb-2">Hi, ${user.first_name}</div>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="dropdown-item flex items-center gap-3 px-4 py-3 text-on-surface font-semibold text-base rounded-xl hover:bg-surface-container-low transition-colors">
                                <span class="icon-line text-[22px]" aria-hidden="true">dashboard</span> Dashboard
                            </a>
                            <a href="<?php echo $base_url; ?>/auth/logout.php" class="dropdown-item flex items-center gap-3 px-4 py-3 text-error font-semibold text-base rounded-xl hover:bg-error-container/50 transition-colors">
                                <span class="icon-line text-[22px]" aria-hidden="true">logout</span> Log Out
                            </a>
                        `;
                    }
                }
            })
            .catch(err => {});


        // --- Premium Mobile Drawer Menu Toggle ---
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileMenuContent = document.getElementById('mobile-menu-content');

        const openMobileMenu = () => {
            mobileMenuOverlay.classList.remove('hidden');
            void mobileMenuOverlay.offsetWidth;
            mobileMenuOverlay.style.opacity = '1';
            mobileMenuContent.style.transform = 'translateX(0)';
        };

        const closeMobileMenu = () => {
            mobileMenuOverlay.style.opacity = '0';
            mobileMenuContent.style.transform = 'translateX(100%)';
            setTimeout(() => {
                mobileMenuOverlay.classList.add('hidden');
            }, 300);
        };

        if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileMenu);
        if (mobileMenuClose) mobileMenuClose.addEventListener('click', closeMobileMenu);
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', (e) => {
                if (e.target === mobileMenuOverlay) closeMobileMenu();
            });
        }


        // --- Setting up Nav Interaction Handlers... ---
        // --- Services Desktop Dropdown Interactions ---
        const navWrapper  = document.getElementById('services-wrapper');
        const navTrigger  = document.getElementById('services-trigger');

        const expandNav = () => {
            if (navTrigger) navTrigger.setAttribute('aria-expanded', 'true');
        };
        
        const collapseNav = () => {
            if (navWrapper && !navWrapper.classList.contains('force-show')) {
                if (navTrigger) navTrigger.setAttribute('aria-expanded', 'false');
            }
        };
        
        if (navWrapper) {
            navWrapper.addEventListener('mouseenter', expandNav);
            navWrapper.addEventListener('mouseleave', () => {
                collapseNav();
                navWrapper.classList.remove('force-close');
            });
            navWrapper.addEventListener('focusin', expandNav);
            navWrapper.addEventListener('focusout', collapseNav);
        }
        
        // --- Explore Desktop Dropdown Interactions ---
        const exploreWrapper  = document.getElementById('explore-wrapper');
        const exploreTrigger  = document.getElementById('explore-trigger');

        const expandExplore = () => {
            if (exploreTrigger) exploreTrigger.setAttribute('aria-expanded', 'true');
        };
        
        const collapseExplore = () => {
            if (exploreWrapper && !exploreWrapper.classList.contains('force-show')) {
                if (exploreTrigger) exploreTrigger.setAttribute('aria-expanded', 'false');
            }
        };
        
        if (exploreWrapper) {
            exploreWrapper.addEventListener('mouseenter', expandExplore);
            exploreWrapper.addEventListener('mouseleave', () => {
                collapseExplore();
                exploreWrapper.classList.remove('force-close');
            });
            exploreWrapper.addEventListener('focusin', expandExplore);
            exploreWrapper.addEventListener('focusout', collapseExplore);
        }


        // --- Mobile Services Sub-accordion Toggle ---
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

        // --- Mobile Explore Sub-accordion Toggle ---
        const mobileExploreBtn     = document.getElementById('mobile-explore-btn');
        const mobileExploreMenu    = document.getElementById('mobile-explore-menu');
        const mobileExploreChevron = document.getElementById('mobile-explore-chevron');

        if (mobileExploreBtn && mobileExploreMenu && mobileExploreChevron) {
            mobileExploreBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const isHidden = mobileExploreMenu.classList.contains('hidden');
                mobileExploreMenu.classList.toggle('hidden', !isHidden);
                mobileExploreMenu.classList.toggle('flex', isHidden);
                mobileExploreChevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            });
        }


        // --- Sticky & Floating Transformation on Scroll ---
        const header = document.getElementById('main-header');
        
        if (header) {
            const handleScroll = () => {
                if (window.scrollY > 40) {
                    header.classList.add('is-scrolled');
                } else {
                    header.classList.remove('is-scrolled');
                }
            };
            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll();
        }


        // --- Scrollspy & Active Navigation Setup ---
        const allNavLinks  = document.querySelectorAll('a.nav-link-custom[data-section], a.dropdown-item[data-section], button.nav-link-custom[data-section], a.booking-btn[data-section]');
        const currentPage  = window.location.pathname.split('/').pop();
        const currentHash  = window.location.hash.replace('#', '');
        let isClickScrolling  = false;
        let clickScrollTimeout;

        function setActive(sectionId) {
            allNavLinks.forEach(link => {
                if (link.classList.contains('booking-btn')) {
                    if (link.getAttribute('data-section') === sectionId) {
                        link.classList.add('ring-4', 'ring-primary/40', 'ring-offset-2', 'ring-offset-surface-container-lowest');
                    } else {
                        link.classList.remove('ring-4', 'ring-primary/40', 'ring-offset-2', 'ring-offset-surface-container-lowest');
                    }
                } else {
                    const isActive = link.getAttribute('data-section') === sectionId;
                    link.classList.toggle('active', isActive);
                    
                    if (link.id === 'mobile-services-btn' || link.id === 'mobile-explore-btn') {
                        link.classList.toggle('text-primary', isActive);
                    }

                    // FIX 1: If a dropdown child item is active (e.g. Dentists section inside Services), 
                    // ensure the parent top-level trigger also receives the active highlight.
                    if (isActive) {
                        const parentDropdown = link.closest('.nav-dropdown-wrapper');
                        if (parentDropdown) {
                            const trigger = parentDropdown.querySelector('.nav-link-custom');
                            if (trigger) trigger.classList.add('active');
                        }
                        
                        const mobileMenuParent = link.closest('#mobile-services-menu, #mobile-explore-menu');
                        if (mobileMenuParent) {
                            const btnId = mobileMenuParent.id.replace('-menu', '-btn');
                            const btn = document.getElementById(btnId);
                            if (btn) btn.classList.add('text-primary', 'active');
                        }
                    }
                }
            });

            // FIX 3: Invoke calculation to slide the shared indicator to the new actively designated link
            updateActiveIndicator();
        }

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
            if (link.hasAttribute('href') && !link.getAttribute('href').includes('index.php#')) return;

            link.addEventListener('click', (e) => {
                const sectionId = link.getAttribute('data-section');
                const target    = document.getElementById(sectionId);

                if (mobileMenuOverlay && !link.closest('nav') && window.innerWidth < 768) {
                    closeMobileMenu();
                }

                if (target) {
                    e.preventDefault();
                    
                    // Update the URL hash to reflect the current section for correct refresh behavior
                    if (history.pushState) {
                        history.pushState(null, null, '#' + sectionId);
                    } else {
                        window.location.hash = '#' + sectionId;
                    }

                    isClickScrolling = true;
                    clearTimeout(clickScrollTimeout);
                    clickScrollTimeout = setTimeout(() => {
                        isClickScrolling = false;
                        updateActiveNav();
                    }, 800);

                    setActive(sectionId);

                    const ownDropdownWrapper = link.closest('.nav-dropdown-wrapper, .auth-dropdown-wrapper');
                    [ownDropdownWrapper, navWrapper, exploreWrapper].forEach(wrapper => {
                        if (!wrapper) return;
                        wrapper.classList.remove('force-show');
                        wrapper.classList.add('force-close');
                        setTimeout(() => wrapper.classList.remove('force-close'), 800);
                    });
                    if (navTrigger) navTrigger.setAttribute('aria-expanded', 'false');
                    if (exploreTrigger) exploreTrigger.setAttribute('aria-expanded', 'false');
                    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();

                    const headerOffset = header.classList.contains('is-scrolled') ? 92 : 128;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.scrollY - headerOffset;
                    
                    window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                }
            });
        });

        function updateActiveNav() {
            if (isClickScrolling) return;
            const scrollY = window.scrollY;
            const headerOffset = window.innerHeight * 0.4; 
            let current = '';

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

            if (sortedSections.length > 0) {
                current = sortedSections[0].id;
            }

            sortedSections.forEach(sec => {
                if (scrollY >= sec.top - headerOffset) {
                    current = sec.id;
                }
            });

            if ((window.innerHeight + Math.round(window.scrollY)) >= document.body.offsetHeight - 20) {
                if (sortedSections.length > 0) {
                    current = sortedSections[sortedSections.length - 1].id;
                }
            }

            if (current) setActive(current);
        }

        window.addEventListener('scroll', updateActiveNav, { passive: true });
        updateActiveNav(); 
        
        // Initial setup for the sliding underline positioning
        setTimeout(updateActiveIndicator, 50);
    });
</script>