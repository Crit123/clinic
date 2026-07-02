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

<!-- Material Symbols for Premium Icons -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

<style>
    /* Google Fonts Import for Premium Healthcare/Tech Feel */
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

    :root {
        --color-primary: #0057D9;
        --color-accent: #4DA8FF;
        --color-text: #0F172A;
        --color-text-secondary: #6B7280;
        --color-border: #E5E7EB;
        --font-family: 'Plus Jakarta Sans', sans-serif;
        /* Resting header heights for pages to offset their content against.
           Below lg: utility bar is hidden -> wrapper margins (12+12) + nav (80) = 104px, + breathing gap.
           At lg+: utility bar visible -> utility (40) + wrapper margins (24) + nav (80) = 144px, + breathing gap.
           Update these if header heights in Part 3 change again. */
        --header-offset-mobile: 120px;
        --header-offset: 164px;
    }

    body {
        font-family: var(--font-family);
    }

    /* Header must always render in Plus Jakarta Sans, regardless of whatever
       font-family the host page sets on <body> (e.g. booking.php / confirmed-booking.php
       apply their own Tailwind `font-sans` utility, which has higher specificity than
       the `body { font-family }` rule above and would otherwise silently override it here). */
    #main-header,
    #mobile-menu-overlay {
        font-family: var(--font-family);
    }

    /* Premium Underline Animation sliding from left */
    .nav-link-custom {
        position: relative;
        color: #1F2937;
        font-weight: 600;
        font-size: 17px;
        white-space: nowrap;
        transition: color 0.25s ease-in-out;
    }
    .nav-link-custom:hover {
        color: var(--color-primary);
    }
    .nav-link-custom::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--color-primary);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.25s ease-in-out;
    }
    .nav-link-custom:hover::after,
    .nav-link-custom.active::after {
        transform: scaleX(1);
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

    /* Forces the dropdown (and its chevron) closed on click, overriding :hover/:focus-within.
       Needed because #main-header is position:fixed, so the cursor stays over the trigger
       throughout a programmatic scroll — :hover never naturally ends on its own. */
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
        background-color: #F3F4F6;
        color: var(--color-primary);
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
        height: 74px !important; /* Fixed: using height for smooth tweening */
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
       PREMIUM STAGGERED SCROLL TRANSITIONS
       ========================================================================= */

    /* Utility bar — fades/collapses first, fastest */
    #utility-bar {
        transition: height 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                    border-bottom-width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Main nav container — the hero morph, slower with a decelerated "settle" */
    #main-nav-container {
        transition: height 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.05s,
                    margin 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.05s,
                    max-width 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.05s,
                    width 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.05s,
                    padding 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.05s,
                    border-radius 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.05s,
                    background-color 0.3s ease 0.1s,
                    backdrop-filter 0.3s ease 0.1s,
                    -webkit-backdrop-filter 0.3s ease 0.1s,
                    box-shadow 0.5s ease,
                    border-color 0.3s ease,
                    transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Logo — animates slightly after the container starts moving */
    .logo-img-wrapper,
    .logo-img {
        transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1) 0.1s,
                    height 0.35s cubic-bezier(0.16, 1, 0.3, 1) 0.1s;
    }
    
    .logo-text {
        transition: font-size 0.35s cubic-bezier(0.16, 1, 0.3, 1) 0.1s;
    }
    
    .logo-tagline {
        max-height: 20px;
        opacity: 1;
        overflow: hidden;
        transition: max-height 0.2s ease, opacity 0.2s ease, margin-top 0.2s ease;
    }

    /* Nav gap */
    nav.hidden.md\:flex {
        transition: gap 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0.1s;
    }

    /* Buttons — labels collapse smoothly, slight delay so container settles first */
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
        transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0.05s,
                    opacity 0.2s ease 0.05s,
                    margin 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0.05s;
    }
    
    #auth-btn,
    .booking-btn {
        transition: padding 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0.05s,
                    gap 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0.05s;
    }

    /* Mobile menu animations */
    #mobile-menu-overlay {
        transition: opacity 0.3s ease;
    }
    #mobile-menu-content {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<!-- Main Header wrapper (holds Section 1 & Section 2) -->
<header class="fixed top-0 w-full z-50 flex flex-col pointer-events-none" id="main-header">
    
    <!-- SECTION 1: Top Utility Bar (Pure White) -->
    <div id="utility-bar" class="w-full h-10 bg-white border-b border-[#EEF2F7] hidden lg:block pointer-events-auto overflow-hidden">
        <div class="max-w-[1280px] h-full mx-auto px-8 flex justify-between items-center text-sm font-medium text-[#6B7280]">
            <!-- Left Info Group -->
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-[#0057D9]" aria-hidden="true">schedule</span>
                    <span>Mon–Sat: <strong class="text-[#0F172A]">8:00 AM – 6:00 PM</strong></span>
                </div>
                <div class="w-[1px] h-[18px] bg-[#E5E7EB]"></div>
                <a href="tel:+15555550148" class="flex items-center gap-2 hover:text-[#0057D9] transition-colors">
                    <span class="material-symbols-outlined text-[18px] text-[#0057D9]" aria-hidden="true">call</span>
                    <span>(555) 555-0148</span>
                </a>
                <div class="w-[1px] h-[18px] bg-[#E5E7EB]"></div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-[#0057D9]" aria-hidden="true">location_on</span>
                    <span>123 Dental Suite, Medical District</span>
                </div>
            </div>

            <!-- Right Info/Social Group -->
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-2 text-red-600 font-semibold bg-red-50 px-3 py-1 rounded-full text-xs">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                    24/7 Dental Emergency Available
                </span>
                <div class="w-[1px] h-[18px] bg-[#E5E7EB]"></div>
                <div class="flex items-center gap-4 text-slate-500">
                    <a href="#" class="hover:text-[#0057D9] transition-colors opacity-90 hover:opacity-100" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"/></svg>
                    </a>
                    <a href="#" class="hover:text-[#0057D9] transition-colors opacity-90 hover:opacity-100" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="mailto:info@dentalcarepro.com" class="hover:text-[#0057D9] transition-colors opacity-90 hover:opacity-100" aria-label="Email">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: MAIN NAVIGATION -->
    <div class="w-full mt-3 mb-3 pointer-events-auto">
        <div id="main-nav-container" class="max-w-[1440px] 2xl:max-w-[1500px] w-full mx-auto h-20 bg-white border border-[#E5E7EB]/50 rounded-[24px] shadow-[0_12px_40px_rgba(15,23,42,0.08)] px-10 flex items-center justify-between">
            
            <!-- LEFT: Logo Block -->
            <a href="<?php echo $base_url; ?>/index.php#home" class="flex items-center gap-4 cursor-pointer group" data-section="home" aria-label="Go to homepage">
                <div class="logo-img-wrapper">
                    <svg class="logo-img h-10 w-10 text-[#0057D9]" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="#0057D9"/>
                        <path d="M24 10C19.8 10 16.8 12.4 15 14.8C13.2 12.4 10.8 11.2 9 13C7.2 14.8 7.8 18.4 9.6 20.8C10.8 22.6 12 24.4 12.6 26.8C13.2 29.2 13.2 34 15.6 36.4C16.8 37.6 18.6 37.6 19.8 36.4C21 35.2 21 31.6 21.6 29.2C22.2 26.8 22.8 25.6 24 25.6C25.2 25.6 25.8 26.8 26.4 29.2C27 31.6 27 35.2 28.2 36.4C29.4 37.6 31.2 37.6 32.4 36.4C34.8 34 34.8 29.2 35.4 26.8C36 24.4 37.2 22.6 38.4 20.8C40.2 18.4 40.8 14.8 39 13C37.2 11.2 34.8 12.4 33 14.8C31.2 12.4 28.2 10 24 10Z" fill="white"/>
                    </svg>
                </div>
                <div class="flex flex-col justify-center">
                    <span class="logo-text font-extrabold text-[22px] text-[#0F172A] leading-none tracking-tight">DentalCare Pro</span>
                    <span class="logo-tagline text-[13px] font-medium text-[#6B7280] mt-0.5">Healthy Smile, Confident You</span>
                </div>
            </a>

            <!-- CENTER: Navigation Menu -->
            <nav class="hidden md:flex items-center gap-10">
                <a class="nav-link-custom py-2" href="<?php echo $base_url; ?>/index.php#home" data-section="home">Home</a>
                <a class="nav-link-custom py-2" href="<?php echo $base_url; ?>/index.php#about" data-section="about">About</a>
                
                <!-- Services Dropdown (Dynamic) -->
                <div class="nav-dropdown-wrapper relative flex items-center h-full py-4" id="services-wrapper">
                    <a href="<?php echo $base_url; ?>/index.php#services" id="services-trigger" class="nav-link-custom flex items-center gap-1 py-2" data-section="services" aria-haspopup="true" aria-expanded="false">
                        Services
                        <span class="material-symbols-outlined text-[18px] text-[#6B7280] chevron-rotate" aria-hidden="true">expand_more</span>
                    </a>
                    
                    <div class="nav-dropdown absolute left-1/2 -translate-x-1/2 top-[calc(100%-8px)] w-[280px] bg-white rounded-[20px] shadow-xl border border-slate-100 z-50 p-2 pointer-events-auto">
                        <a href="<?php echo $base_url; ?>/index.php#dentists" class="dropdown-item flex items-center gap-3 px-4 py-3 text-[#1F2937] hover:text-[#0057D9] font-semibold text-[15px]" data-section="dentists">
                            <span class="material-symbols-outlined text-[20px] opacity-70" aria-hidden="true">medical_services</span>
                            Meet the Dentist
                        </a>
                        <div class="border-t border-slate-100 my-2 mx-2"></div>
                        <div class="px-4 pb-2 pt-2 text-[11px] font-bold tracking-widest text-[#6B7280] uppercase">Clinical Offerings</div>
                        
                        <?php foreach($services as $key => $service): ?>
                            <?php $isEmergency = stripos($key, 'emergency') !== false; ?>
                            
                            <a href="<?php echo $base_url; ?>/services/<?php echo urlencode($key); ?>.php" 
                               class="dropdown-item block px-4 py-3 font-semibold text-[15px] flex items-center justify-between group/item <?php echo $isEmergency ? 'is-emergency text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100' : 'text-[#1F2937] hover:text-[#0057D9]'; ?>" 
                               data-section="<?php echo htmlspecialchars($key); ?>">
                                
                                <span>
                                    <?php echo htmlspecialchars($service['label']); ?>
                                    <?php if($isEmergency): ?>
                                        <span class="inline-block text-[10px] font-bold tracking-wide text-red-500 opacity-90 ml-1">— Call Now</span>
                                    <?php endif; ?>
                                </span>
                                
                                <span class="material-symbols-outlined text-[16px] opacity-0 -translate-x-2 transition-all duration-200 group-hover/item:opacity-100 group-hover/item:translate-x-0" aria-hidden="true">
                                    <?php echo $isEmergency ? 'warning' : 'chevron_right'; ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                        
                        <div class="border-t border-slate-100 my-2 mx-2"></div>
                        
                        <a href="<?php echo $base_url; ?>/index.php#services" class="block px-3 py-3 bg-slate-50 hover:bg-blue-50 text-[#0057D9] text-center rounded-xl font-bold text-[14px] transition-colors">
                            View All Services
                        </a>
                    </div>
                </div>

                <!-- Swapped FAQ & Contact (Moved to be before Explore) -->
                <a class="nav-link-custom py-2" href="<?php echo $base_url; ?>/index.php#faq" data-section="faq">FAQ & Contact</a>

                <!-- Explore Dropdown -->
                <div class="nav-dropdown-wrapper relative flex items-center h-full py-4" id="explore-wrapper">
                    <button id="explore-trigger" class="nav-link-custom flex items-center gap-1 py-2" aria-haspopup="true" aria-expanded="false">
                        Explore
                        <span class="material-symbols-outlined text-[18px] text-[#6B7280] chevron-rotate" aria-hidden="true">expand_more</span>
                    </button>
                    
                    <div class="nav-dropdown absolute left-1/2 -translate-x-1/2 top-[calc(100%-8px)] w-[280px] bg-white rounded-[20px] shadow-xl border border-slate-100 z-50 p-2 pointer-events-auto">
                        <a href="<?= htmlspecialchars($base_url) ?>/index.php#dashboard" class="dropdown-item flex items-start gap-3 px-4 py-3 text-[#1F2937] hover:text-[#0057D9] transition-colors" data-section="dashboard">
                            <span class="material-symbols-outlined text-[20px] opacity-70 mt-0.5" aria-hidden="true">dashboard</span>
                            <div>
                                <div class="font-semibold text-[15px]">Patient Portal</div>
                                <div class="text-[12px] text-slate-500 font-normal mt-0.5">Preview your patient dashboard</div>
                            </div>
                        </a>
                        
                        <div class="border-t border-slate-100 my-1 mx-2"></div>
                        
                        <a href="<?= htmlspecialchars($base_url) ?>/index.php#ai-assistant" class="dropdown-item flex items-start gap-3 px-4 py-3 text-[#1F2937] hover:text-[#0057D9] transition-colors" data-section="ai-assistant">
                            <span class="material-symbols-outlined text-[20px] opacity-70 mt-0.5" aria-hidden="true">smart_toy</span>
                            <div>
                                <div class="font-semibold text-[15px]">AI Assistant</div>
                                <div class="text-[12px] text-slate-500 font-normal mt-0.5">Meet your 24/7 dental assistant</div>
                            </div>
                        </a>
                    </div>
                </div>

            </nav>

            <!-- RIGHT: Actions Area -->
            <div class="hidden md:flex items-center">
                
                <!-- Account Dropdown Wrapper -->
                <div class="auth-dropdown-wrapper relative mr-6">
                    <button id="auth-btn" class="flex items-center gap-3 h-12 px-5 bg-white border border-[#E5E7EB] hover:border-slate-300 rounded-full text-[#1F2937] font-semibold text-[15px] transition-colors" aria-haspopup="true" aria-expanded="false">
                        <span class="material-symbols-outlined text-[20px] text-slate-500" aria-hidden="true">account_circle</span>
                        <span class="auth-btn-label flex items-center gap-3">
                            <span>Account</span>
                            <span class="material-symbols-outlined text-[18px] text-[#6B7280] chevron-rotate" id="auth-chevron" aria-hidden="true">expand_more</span>
                        </span>
                    </button>

                    <div class="auth-dropdown absolute right-0 top-[calc(100%+8px)] w-56 bg-white rounded-[20px] shadow-xl border border-slate-100 z-50 p-2 pointer-events-auto">
                        <a href="<?php echo $base_url; ?>/auth/login.php?mode=login" class="dropdown-item flex items-center gap-3 px-4 py-3 text-[#1F2937] hover:text-[#0057D9] font-semibold text-[15px]">
                            <span class="material-symbols-outlined text-[20px] opacity-70" aria-hidden="true">login</span>
                            Log In
                        </a>
                        <div class="border-t border-slate-100 my-1 mx-2"></div>
                        <a href="<?php echo $base_url; ?>/auth/login.php?mode=register" class="dropdown-item flex items-center gap-3 px-4 py-3 text-[#1F2937] hover:text-[#0057D9] font-semibold text-[15px]">
                            <span class="material-symbols-outlined text-[20px] opacity-70" aria-hidden="true">person_add</span>
                            Sign Up
                        </a>
                    </div>
                </div>

                <!-- Book Appointment Premium Button -->
                <a href="<?php echo $base_url; ?>/booking.php" data-section="booking" class="booking-btn h-[54px] px-7 rounded-[14px] bg-gradient-to-r from-[#0057D9] to-[#1E73FF] text-white font-semibold text-[16px] shadow-[0_6px_20px_rgba(0,87,217,0.25)] hover:shadow-[0_8px_25px_rgba(0,87,217,0.35)] hover:-translate-y-0.5 active:translate-y-0 active:shadow-sm transition-all duration-250 flex items-center gap-3 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_month</span>
                    <span>Book&nbsp;<span class="booking-btn-full-label">Appointment</span></span>
                </a>
            </div>

            <!-- Mobile Controls -->
            <div class="md:hidden flex items-center gap-3">
                <!-- Mobile Booking Icon Link -->
                <a href="<?php echo $base_url; ?>/booking.php" class="flex items-center justify-center h-11 w-11 rounded-full bg-[#0057D9]/10 text-[#0057D9] hover:bg-[#0057D9]/20 transition-colors" aria-label="Book Appointment">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">calendar_month</span>
                </a>
                
                <!-- Hamburger Menu Toggle Button -->
                <button id="mobile-menu-btn" class="flex items-center justify-center h-11 w-11 rounded-full hover:bg-slate-100 transition-colors text-[#1F2937]" aria-label="Toggle mobile menu">
                    <span class="material-symbols-outlined text-3xl" aria-hidden="true">menu</span>
                </button>
            </div>

        </div>
    </div>
</header>

<!-- MOBILE MENU (Premium Slide-out Drawer with Blur Overlay) -->
<div id="mobile-menu-overlay" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm pointer-events-auto opacity-0 transition-opacity duration-300">
    <div id="mobile-menu-content" class="absolute right-0 top-0 h-full w-[320px] bg-white rounded-l-[24px] shadow-2xl flex flex-col justify-between p-6 transform translate-x-full transition-transform duration-300 ease-out">
        
        <div>
            <!-- Mobile Menu Header -->
            <div class="flex items-center justify-between pb-6 border-b border-slate-100 mb-6">
                <span class="font-bold text-[20px] text-[#0F172A]">Menu</span>
                <button id="mobile-menu-close" class="h-10 w-10 rounded-full flex items-center justify-center hover:bg-slate-100 transition-colors" aria-label="Close menu">
                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">close</span>
                </button>
            </div>

            <!-- Mobile Navigation Link Stack -->
            <div class="flex flex-col gap-1">
                <a class="nav-link-custom flex items-center justify-between h-14 px-4 rounded-xl hover:bg-slate-50 text-lg transition-colors" href="<?php echo $base_url; ?>/index.php#home" data-section="home">
                    <span>Home</span>
                    <span class="material-symbols-outlined text-slate-400">chevron_right</span>
                </a>
                <a class="nav-link-custom flex items-center justify-between h-14 px-4 rounded-xl hover:bg-slate-50 text-lg transition-colors" href="<?php echo $base_url; ?>/index.php#about" data-section="about">
                    <span>About</span>
                    <span class="material-symbols-outlined text-slate-400">chevron_right</span>
                </a>
                
                <!-- Mobile Services Sub-accordion -->
                <div class="flex flex-col">
                    <button id="mobile-services-btn" class="nav-link-custom flex items-center justify-between w-full h-14 px-4 rounded-xl hover:bg-slate-50 text-left text-lg transition-colors" data-section="services">
                        <span>Services</span>
                        <span class="material-symbols-outlined text-slate-400 transition-transform duration-300" id="mobile-services-chevron" aria-hidden="true">expand_more</span>
                    </button>
                    
                    <div id="mobile-services-menu" class="hidden flex-col gap-1 pl-4 mt-1 border-l-2 border-slate-100 ml-4 py-1">
                        <a class="dropdown-item block w-full px-4 py-3 font-semibold text-[15px] flex items-center gap-2 text-slate-700 hover:text-[#0057D9]"
                           href="<?php echo $base_url; ?>/index.php#dentists" data-section="dentists">
                            <span class="material-symbols-outlined text-[18px] opacity-70" aria-hidden="true">medical_services</span>
                            Meet the Dentist
                        </a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <?php foreach($services as $key => $service): ?>
                            <?php $isEmergency = stripos($key, 'emergency') !== false; ?>
                            
                            <a class="dropdown-item block w-full px-4 py-3 font-semibold text-[15px] flex items-center justify-between <?php echo $isEmergency ? 'is-emergency text-red-600 bg-red-50' : 'text-slate-700 hover:text-[#0057D9]'; ?>" 
                               href="<?php echo $base_url; ?>/services/<?php echo urlencode($key); ?>.php" 
                               data-section="<?php echo htmlspecialchars($key); ?>">
                                <span>
                                    <?php echo htmlspecialchars($service['label']); ?>
                                    <?php if($isEmergency): ?>
                                        <span class="inline-block text-[11px] font-bold text-red-50 ml-1">— Call</span>
                                    <?php endif; ?>
                                </span>
                                <?php if($isEmergency): ?>
                                    <span class="material-symbols-outlined text-[16px] text-red-500" aria-hidden="true">warning</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                        <a class="dropdown-item block w-full px-4 py-3 mt-1 text-[#0057D9] font-bold text-[14px]" href="<?php echo $base_url; ?>/index.php#services" data-section="services">
                            View All Services &rarr;
                        </a>
                    </div>
                </div>

                <!-- Swapped FAQ & Contact (Moved to be before Explore) -->
                <a class="nav-link-custom flex items-center justify-between h-14 px-4 rounded-xl hover:bg-slate-50 text-lg transition-colors" href="<?php echo $base_url; ?>/index.php#faq" data-section="faq">
                    <span>FAQ & Contact</span>
                    <span class="material-symbols-outlined text-slate-400">chevron_right</span>
                </a>

                <!-- Mobile Explore Sub-accordion -->
                <div class="flex flex-col">
                    <button id="mobile-explore-btn" class="nav-link-custom flex items-center justify-between w-full h-14 px-4 rounded-xl hover:bg-slate-50 text-left text-lg transition-colors">
                        <span>Explore</span>
                        <span class="material-symbols-outlined text-slate-400 transition-transform duration-300" id="mobile-explore-chevron" aria-hidden="true">expand_more</span>
                    </button>
                    
                    <div id="mobile-explore-menu" class="hidden flex-col gap-1 pl-4 mt-1 border-l-2 border-slate-100 ml-4 py-1">
                        <a class="dropdown-item block w-full px-4 py-3 flex items-start gap-3 text-slate-700 hover:text-[#0057D9]" href="<?= htmlspecialchars($base_url) ?>/index.php#dashboard" data-section="dashboard">
                            <span class="material-symbols-outlined text-[20px] opacity-70 mt-0.5" aria-hidden="true">dashboard</span>
                            <div>
                                <div class="font-semibold text-[15px]">Patient Portal</div>
                                <div class="text-[12px] text-slate-500 font-normal mt-0.5">Preview your patient dashboard</div>
                            </div>
                        </a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <a class="dropdown-item block w-full px-4 py-3 flex items-start gap-3 text-slate-700 hover:text-[#0057D9]" href="<?= htmlspecialchars($base_url) ?>/index.php#ai-assistant" data-section="ai-assistant">
                            <span class="material-symbols-outlined text-[20px] opacity-70 mt-0.5" aria-hidden="true">smart_toy</span>
                            <div>
                                <div class="font-semibold text-[15px]">AI Assistant</div>
                                <div class="text-[12px] text-slate-500 font-normal mt-0.5">Meet your 24/7 dental assistant</div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Mobile Account and CTA Bottom Block -->
        <div class="flex flex-col gap-4 border-t border-slate-100 pt-6">
            <div id="mobile-auth-section" class="flex flex-col gap-2">
                <a href="<?php echo $base_url; ?>/auth/login.php?mode=login" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-700 font-semibold text-base transition-colors">
                    <span class="material-symbols-outlined text-[22px] text-slate-500" aria-hidden="true">login</span> 
                    Log In
                </a>
                <a href="<?php echo $base_url; ?>/auth/login.php?mode=register" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-700 font-semibold text-base transition-colors">
                    <span class="material-symbols-outlined text-[22px] text-slate-500" aria-hidden="true">person_add</span> 
                    Sign Up
                </a>
            </div>

            <a href="<?php echo $base_url; ?>/booking.php" class="booking-btn h-14 rounded-2xl bg-gradient-to-r from-[#0057D9] to-[#1E73FF] text-white font-semibold text-base flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transition-all duration-200">
                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">calendar_month</span> 
                Book Appointment
            </a>
        </div>

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
                    
                    // Desktop Account Dropdown Button Replacement
                    const authBtn = document.getElementById('auth-btn');
                    if (authBtn) {
                        authBtn.innerHTML = `
                            <span class="material-symbols-outlined text-[20px] text-[#0057D9]" aria-hidden="true">account_circle</span>
                            <span class="auth-btn-label flex items-center gap-3">
                                <span class="truncate max-w-[120px] text-[#0F172A] font-semibold">Hi, ${user.first_name}</span>
                                <span class="material-symbols-outlined text-[18px] text-[#6B7280] chevron-rotate" id="auth-chevron" aria-hidden="true">expand_more</span>
                            </span>
                        `;
                    }

                    // Desktop Dropdown Re-population
                    const authDropdown = document.querySelector('.auth-dropdown');
                    if (authDropdown) {
                        authDropdown.innerHTML = `
                            <div class="px-4 py-2.5 text-[11px] font-bold tracking-wider text-slate-400 uppercase border-b border-slate-100 mb-1 mx-2">Hi, ${user.first_name}</div>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="dropdown-item flex items-center gap-3 px-4 py-3 text-[#1F2937] hover:text-[#0057D9] font-semibold text-[15px]">
                                <span class="material-symbols-outlined text-[20px] opacity-70" aria-hidden="true">dashboard</span> Dashboard
                            </a>
                            <a href="<?php echo $base_url; ?>/auth/logout.php" class="dropdown-item flex items-center gap-3 px-4 py-3 text-red-600 hover:text-red-700 hover:bg-red-50 font-semibold text-[15px]">
                                <span class="material-symbols-outlined text-[20px] opacity-70" aria-hidden="true">logout</span> Log Out
                            </a>
                        `;
                    }

                    // Mobile Menu Auth Section Re-population
                    const mobileAuthSection = document.getElementById('mobile-auth-section');
                    if (mobileAuthSection) {
                        mobileAuthSection.innerHTML = `
                            <div class="px-4 py-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-2">Hi, ${user.first_name}</div>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="dropdown-item flex items-center gap-3 px-4 py-3 text-slate-700 font-semibold text-base rounded-xl hover:bg-slate-50 transition-colors">
                                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">dashboard</span> Dashboard
                            </a>
                            <a href="<?php echo $base_url; ?>/auth/logout.php" class="dropdown-item flex items-center gap-3 px-4 py-3 text-red-600 font-semibold text-base rounded-xl hover:bg-red-50 transition-colors">
                                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">logout</span> Log Out
                            </a>
                        `;
                    }
                }
            })
            .catch(err => {
                // Keep default login/register layout unharmed if not logged in
            });


        // --- Premium Mobile Drawer Menu Toggle ---
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileMenuContent = document.getElementById('mobile-menu-content');

        const openMobileMenu = () => {
            mobileMenuOverlay.classList.remove('hidden');
            // Trigger layout flow before transitioning opacity/transforms
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
            handleScroll(); // Execute once on load in case page was scrolled after refresh
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
                        link.classList.add('ring-4', 'ring-[#0057D9]/40', 'ring-offset-2', 'ring-offset-white');
                    } else {
                        link.classList.remove('ring-4', 'ring-[#0057D9]/40', 'ring-offset-2', 'ring-offset-white');
                    }
                } else {
                    // Exact match checking for independent active state (satisfies Goal 1)
                    const isActive = link.getAttribute('data-section') === sectionId;
                    link.classList.toggle('active', isActive);
                    
                    if (link.id === 'mobile-services-btn' || link.id === 'mobile-explore-btn') {
                        link.classList.toggle('text-[#0057D9]', isActive);
                    }
                }
            });
            // Note: Automatic dropdown and mobile accordion expansion logic has been removed.
            // Dropdown visibility is now completely independent and trigger-based (hover/click).
        }

        // Intercept navigation triggers if navigating outside index.php
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

                // Close mobile menu drawer smoothly
                if (mobileMenuOverlay && !link.closest('nav') && window.innerWidth < 768) {
                    closeMobileMenu();
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

                    // Force-close any dropdown the clicked link lives in (or triggers), and
                    // drop focus, so it doesn't stay open via :hover for the whole scroll —
                    // #main-header is fixed, so the cursor stays over the trigger the entire time.
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

                    // Compute dynamic scroll offsets matching header heights
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
            // Use viewport percentage for accurate tracking during flow changes
            const headerOffset = window.innerHeight * 0.4; 
            let current = '';

            // Get all unique section IDs requested by navigation items
            const uniqueSectionIds = Array.from(allNavLinks)
                .map(link => link.getAttribute('data-section'))
                .filter((id, idx, arr) => id && arr.indexOf(id) === idx);

            // Filter out non-existent elements, map to current physical DOM layout, and sort chronologically (Goal 2)
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

            // Traverse sorted sections physically from top to bottom
            sortedSections.forEach(sec => {
                if (scrollY >= sec.top - headerOffset) {
                    current = sec.id;
                }
            });

            // Absolute bottom of page detection (Fixes 'dashboard' overriding 'ai-assistant')
            // Sets active state to the actual final section in the DOM flow.
            if ((window.innerHeight + Math.round(window.scrollY)) >= document.body.offsetHeight - 20) {
                if (sortedSections.length > 0) {
                    current = sortedSections[sortedSections.length - 1].id;
                }
            }

            if (current) setActive(current);
        }

        window.addEventListener('scroll', updateActiveNav, { passive: true });
        updateActiveNav(); 
    });
</script>