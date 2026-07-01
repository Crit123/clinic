<?php
// Use the canonical services data as the single source of truth
require_once __DIR__ . '/api/data/services-data.php';
$canonicalServices = getAllServices();
?>
<!DOCTYPE html>
<html class="light scroll-smooth scroll-pt-[80px]" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>DentalCare Pro - Exceptional Dental Care</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= htmlspecialchars($base_url ?? '') ?>/assets/css/responsive.css">
    
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
                        "2xl": "1rem",
                        "3xl": "1.5rem",
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
                    "animation": {
                        "float": "float 6s ease-in-out infinite",
                        "float-delayed": "float 6s ease-in-out 3s infinite",
                        "pulse-slow": "pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite",
                        "bounce-slow": "bounce 3s infinite"
                    },
                    "keyframes": {
                        float: {
                            "0%, 100%": { transform: "translateY(0px)" },
                            "50%": { transform: "translateY(-15px)" }
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 1s cubic-bezier(0.16, 1, 0.3, 1), transform 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .fade-in-up.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .stagger-1 { transition-delay: 100ms; }
        .stagger-2 { transition-delay: 200ms; }
        .stagger-3 { transition-delay: 300ms; }
        .stagger-4 { transition-delay: 400ms; }
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.fill-icon {
            font-variation-settings: 'FILL' 1;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .dark .glass-panel {
            background: rgba(11, 28, 48, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .gradient-text {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Custom scrollbar for horizontal scrolling elements */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased overflow-x-hidden selection:bg-primary-container selection:text-on-primary-container">

<?php include 'components/header-component.php'; ?>

<main class="pt-20">
<!-- Combined Hero & Statistics wrappers under `#home` for dynamic load-time animation checks -->
<div id="home" class="scroll-mt-24">
    <!-- Enhanced Hero Section -->
    <section class="relative min-h-[85vh] flex items-center justify-center overflow-hidden fade-in-up">
        <div class="absolute inset-0 z-0">
            <!-- Enhanced overlay gradient -->
            <div class="absolute inset-0 bg-gradient-to-r from-surface via-surface/90 to-surface/40 z-10"></div>
            <img alt="Modern dental clinic interior with sophisticated equipment and a calm atmosphere." class="w-full h-full object-cover object-center scale-105 animate-[pulse-slow_10s_ease-in-out_infinite]" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA-nkk1M36aJZHiOeHITanF0DVeWv4Wogp6-ZWAK9wqE1qRDfqdGGHvWhI6Nov4j2Sp_AvhK3ZRSiSJaeR4CFq8f0SA8qJsAoEYqc1bX2YDSlWT86J35iG8oxIZW6iEoAeEPHVOB1DUzpqW34acOBAhqUTaipVgiJ3ShFFt49AENBiZBixQlJKS7Bn03z2mjfXZqU4_l4ePHHJXo9jaA88SYAAfETASgQfuaUdZNm9XpxgoY9Bj4vEt5HD6kINAyEuq002SMUivrRU"/>
        </div>
        
        <!-- Desktop Floating Glassmorphism Cards -->
        <!-- Card 1: AI Assistant (Clickable, links to AI Section) -->
        <a href="#ai-assistant" class="hero-floating-cards-desktop absolute top-[18%] right-[12%] z-20 flex items-center gap-4 glass-panel p-4 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] animate-float hover:scale-105 hover:shadow-[0_12px_40px_rgba(0,0,0,0.12)] hover:border-primary/30 transition-all duration-300 cursor-pointer group/card">
            <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center group-hover/card:bg-primary transition-colors duration-300">
                <span class="material-symbols-outlined text-primary group-hover/card:text-white fill-icon">smart_toy</span>
            </div>
            <div>
                <p class="font-label-md font-bold text-on-background group-hover/card:text-primary transition-colors duration-300">AI Dental Assistant</p>
                <p class="text-xs text-primary font-medium flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Active 24/7</p>
            </div>
        </a>
        
        <!-- Card 2: Safe & Accredited (Clickable, links to About Section) -->
        <a href="#about" class="hero-floating-cards-desktop absolute top-[48%] right-[18%] z-20 flex items-center gap-4 glass-panel p-4 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] animate-[float_7s_ease-in-out_1s_infinite] hover:scale-105 hover:shadow-[0_12px_40px_rgba(0,0,0,0.12)] hover:border-green-600/30 transition-all duration-300 cursor-pointer group/card">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center group-hover/card:bg-green-600 transition-colors duration-300">
                <span class="material-symbols-outlined text-green-700 group-hover/card:text-white fill-icon">health_and_safety</span>
            </div>
            <div>
                <p class="font-label-md font-bold text-on-background group-hover/card:text-primary transition-colors duration-300">Safe & Accredited</p>
                <p class="text-xs text-on-surface-variant font-medium">HIPAA Secure Care</p>
            </div>
        </a>

        <!-- Card 3: Online Booking (Clickable, links to booking page) -->
        <a href="<?= $base_url ?? '' ?>/booking.php" class="hero-floating-cards-desktop absolute bottom-[28%] right-[6%] z-20 flex items-center gap-4 glass-panel p-4 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] animate-float-delayed hover:scale-105 hover:shadow-[0_12px_40px_rgba(0,0,0,0.12)] hover:border-primary/30 transition-all duration-300 cursor-pointer group/card">
            <div class="w-12 h-12 rounded-full bg-tertiary-container flex items-center justify-center group-hover/card:bg-tertiary transition-colors duration-300">
                <span class="material-symbols-outlined text-on-tertiary-container group-hover/card:text-white fill-icon">event_available</span>
            </div>
            <div>
                <p class="font-label-md font-bold text-on-background group-hover/card:text-primary transition-colors duration-300">Online Booking</p>
                <p class="text-xs text-on-surface-variant">Instant Confirmation</p>
            </div>
        </a>

        <!-- Scroll down indicator -->
        <a href="#about" class="absolute bottom-8 left-1/2 -translate-x-1/2 z-30 flex flex-col items-center gap-2 text-on-surface-variant hover:text-primary transition-colors animate-bounce-slow">
            <span class="text-xs font-medium uppercase tracking-widest">Scroll</span>
            <span class="material-symbols-outlined">south</span>
        </a>

        <div class="relative z-20 w-full max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
            <div class="max-w-2xl">
                <!-- Google Review Badge -->
                <div class="inline-flex items-center gap-2 py-1.5 px-3 rounded-full bg-white shadow-sm border border-outline-variant/30 mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google" class="w-4 h-4"/>
                    <div class="flex gap-0.5">
                        <span class="material-symbols-outlined text-yellow-500 text-[14px] fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 text-[14px] fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 text-[14px] fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 text-[14px] fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 text-[14px] fill-icon">star</span>
                    </div>
                    <span class="text-xs font-bold text-on-surface dynamic-rating" data-suffix=" Rating">4.9 Rating</span>
                </div>
                
                <div class="mb-6"><span class="inline-block py-1 px-3 rounded-xl bg-primary-container/20 text-primary font-label-sm text-label-sm border border-primary/10 backdrop-blur-sm">Premium Care</span></div>
                
                <h1 class="font-headline-xl text-headline-xl text-on-background mb-6 leading-tight tracking-tight">
                    Exceptional Dental Care for a <span class="bg-gradient-to-r from-primary to-surface-tint gradient-text">Brighter Smile.</span>
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant mb-10 max-w-xl">
                    Welcome to DentalCare Pro, where precision meets comfort. Experience world-class dental treatments in a state-of-the-art, relaxing environment.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 mb-8">
                    <a href="booking.php" class="group h-12 px-8 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-[0_8px_20px_rgba(0,71,141,0.25)] hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2">
                        Book Appointment
                        <span class="material-symbols-outlined text-[20px] transition-transform duration-300 group-hover:translate-x-1">arrow_forward</span>
                    </a>
                    <a href="services/services.php" class="h-12 px-8 rounded-lg border-[1.5px] border-primary text-primary font-label-md text-label-md hover:bg-surface-container-low hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all duration-300 flex items-center justify-center bg-white/50 backdrop-blur-sm">
                        Our Services
                    </a>
                </div>

                <!-- Lightweight Mobile & Tablet Friendly Trust Row -->
                <div class="hero-floating-cards-mobile flex-wrap gap-3 mt-6 mb-10 border-t border-outline-variant/20 pt-6" style="display: none;">
                    <a href="#ai-assistant" class="flex-1 min-w-[140px] flex items-center gap-3 p-3 rounded-2xl bg-white/70 border border-outline-variant/30 hover:bg-white hover:shadow-sm active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-primary text-[22px] fill-icon">smart_toy</span>
                        <div class="text-left">
                            <p class="text-xs font-bold text-on-background leading-none">AI Support</p>
                            <p class="text-[10px] text-green-600 font-semibold mt-0.5">Active 24/7</p>
                        </div>
                    </a>
                    <a href="#about" class="flex-1 min-w-[140px] flex items-center gap-3 p-3 rounded-2xl bg-white/70 border border-outline-variant/30 hover:bg-white hover:shadow-sm active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-green-700 text-[22px] fill-icon">health_and_safety</span>
                        <div class="text-left">
                            <p class="text-xs font-bold text-on-background leading-none">Accredited</p>
                            <p class="text-[10px] text-on-surface-variant font-medium mt-0.5">HIPAA Secure</p>
                        </div>
                    </a>
                    <a href="<?= $base_url ?? '' ?>/booking.php" class="flex-1 min-w-[140px] flex items-center gap-3 p-3 rounded-2xl bg-white/70 border border-outline-variant/30 hover:bg-white hover:shadow-sm active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-primary text-[22px] fill-icon">event_available</span>
                        <div class="text-left">
                            <p class="text-xs font-bold text-on-background leading-none">Book Online</p>
                            <p class="text-[10px] text-on-surface-variant font-medium mt-0.5">Instant Slot</p>
                        </div>
                    </a>
                </div>
                
                <!-- Trust Indicators -->
                <div class="flex items-center gap-6 text-sm text-on-surface-variant font-medium">
                    <div class="flex items-center gap-2"><span class="material-symbols-outlined text-green-600 text-[18px]">verified</span> Verified Professionals</div>
                    <div class="flex items-center gap-2"><span class="material-symbols-outlined text-green-600 text-[18px]">verified</span> Modern Equipment</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section (Above the fold, load-animated) -->
    <section class="py-12 bg-surface-container-lowest border-b border-outline-variant/10 fade-in-up relative z-20 -mt-10 mx-margin-mobile md:mx-margin-desktop rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] max-w-[1200px] xl:mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 px-8 text-center divide-x divide-outline-variant/10">
            <div class="flex flex-col items-center">
                <span class="text-4xl font-headline-xl font-bold text-primary mb-1 counter" data-target="2400">0</span>
                <span class="text-sm font-label-sm text-on-surface-variant font-medium uppercase tracking-wider">Happy Patients</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-4xl font-headline-xl font-bold text-primary mb-1"><span class="counter" data-target="15">0</span>+</span>
                <span class="text-sm font-label-sm text-on-surface-variant font-medium uppercase tracking-wider">Years Experience</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-4xl font-headline-xl font-bold text-primary mb-1 counter dynamic-rating" data-target="4.9" data-decimal="true">0</span>
                <span class="text-sm font-label-sm text-on-surface-variant font-medium uppercase tracking-wider">Average Rating</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-4xl font-headline-xl font-bold text-primary mb-1"><span class="counter" data-target="98">0</span>%</span>
                <span class="text-sm font-label-sm text-on-surface-variant font-medium uppercase tracking-wider">Recommendation Rate</span>
            </div>
        </div>
    </section>
</div>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12 opacity-0" /></div>

<!-- About Section -->
<section class="py-xl bg-surface-container-lowest fade-in-up" id="about">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-xl items-center mb-16">
            <div>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-6 tracking-tight">Precision and Care at Our Core</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mb-6">
                    At DentalCare Pro, we believe that a healthy smile is the foundation of overall well-being. Our mission is to provide unparalleled dental care through a combination of advanced medical technology and a deeply empathetic approach to patient comfort.
                </p>
                <p class="font-body-md text-body-md text-on-surface-variant mb-8">
                    We have designed our clinic to feel less like a traditional medical facility and more like a wellness retreat. From the moment you walk in, our dedicated team ensures your experience is seamless, transparent, and entirely focused on your specific health needs.
                </p>
                <div class="flex items-center gap-4">
                    <div class="flex -space-x-4">
                        <div class="w-12 h-12 rounded-full bg-surface-container-high border-2 border-surface-container-lowest flex items-center justify-center text-primary font-bold">15+</div>
                        <div class="w-12 h-12 rounded-full bg-primary-container border-2 border-surface-container-lowest flex items-center justify-center text-on-primary-container material-symbols-outlined">verified</div>
                    </div>
                    <span class="font-label-md text-label-md text-on-surface">Years of Excellence</span>
                </div>
            </div>
            <div class="relative rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.1)] hover:shadow-[0_20px_50px_rgba(0,0,0,0.15)] transition-shadow duration-500 h-[500px]">
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent z-10 pointer-events-none"></div>
                <img class="w-full h-full object-cover hover:scale-105 transition-transform duration-700" alt="A bright, modern dental consultation room with sleek white cabinetry and advanced diagnostic displays" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDT8altnTWxHwseqGw5RHPcU8BxHFWTwlCmb6TQiLDc7KjAUpoUfem2y0ttn5dZT05WJqtn2F6pNre_Ph5KrLQIH0rkLlXblwuNH7P-gpj0B4Gzchwsqw3OLvndNAalFY-PpjYrc07F2800hGQJ1zZjMIWs_hNCXstcuKJRcYqST6fgQIVE1gAuALQtfax3GslfIMmlSqADU1xUfA1Zy19eAmgmsSlYJ9fQxcTASpN6u_mGazcv_wqVGvW_XVWfK6U7wQyez0vas6s"/>
            </div>
        </div>

        <!-- Feature Cards Below About -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 pt-12 border-t border-outline-variant/20">
            <div class="bg-surface rounded-2xl p-6 border border-outline-variant/30 hover:shadow-lg transition-all duration-300 group">
                <span class="material-symbols-outlined text-primary text-[32px] mb-4 group-hover:scale-110 transition-transform">precision_manufacturing</span>
                <h4 class="font-headline-md text-base font-semibold mb-2">Modern Equipment</h4>
                <p class="text-sm text-on-surface-variant">State-of-the-art diagnostic tools.</p>
            </div>
            <div class="bg-surface rounded-2xl p-6 border border-outline-variant/30 hover:shadow-lg transition-all duration-300 group">
                <span class="material-symbols-outlined text-primary text-[32px] mb-4 group-hover:scale-110 transition-transform">school</span>
                <h4 class="font-headline-md text-base font-semibold mb-2">Certified Expert</h4>
                <p class="text-sm text-on-surface-variant">Highly qualified lead practitioner.</p>
            </div>
            <div class="bg-surface rounded-2xl p-6 border border-outline-variant/30 hover:shadow-lg transition-all duration-300 group">
                <span class="material-symbols-outlined text-primary text-[32px] mb-4 group-hover:scale-110 transition-transform">folder_managed</span>
                <h4 class="font-headline-md text-base font-semibold mb-2">Digital Records</h4>
                <p class="text-sm text-on-surface-variant">Secure, easy-access dental history.</p>
            </div>
            <div class="bg-surface rounded-2xl p-6 border border-outline-variant/30 hover:shadow-lg transition-all duration-300 group">
                <span class="material-symbols-outlined text-primary text-[32px] mb-4 group-hover:scale-110 transition-transform">handshake</span>
                <h4 class="font-headline-md text-base font-semibold mb-2">Personalized Care</h4>
                <p class="text-sm text-on-surface-variant">Treatments tailored just for you.</p>
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12" /></div>

<!-- Services Section -->
<section class="py-xl bg-background fade-in-up scroll-mt-24" id="services">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">Comprehensive Dental Solutions</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Tailored treatments utilizing the latest in dental technology to ensure optimal outcomes and minimal discomfort.</p>
        </div>
        
        <!-- Services Grid Loop utilizing canonical keys for links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <?php foreach($canonicalServices as $key => $serviceData): ?>
                <?php 
                    // Retrieve dynamic metadata directly from canonical data source
                    $meta = getServiceDisplayMeta($key) ?? ['icon' => 'medical_services', 'desc' => '', 'featured' => false];
                    $isFeatured = $meta['featured']; 
                ?>
                <div class="bg-surface-container-lowest rounded-3xl p-8 border border-outline-variant/30 shadow-sm hover:shadow-[0_15px_40px_rgba(0,0,0,0.08)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col group relative overflow-hidden <?= $isFeatured ? 'md:col-span-2 justify-between' : '' ?>">
                    
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <?php if($isFeatured): ?>
                        <div class="absolute right-0 top-0 w-64 h-64 bg-primary/5 rounded-bl-[100px] -z-10 group-hover:scale-110 transition-transform duration-700"></div>
                    <?php endif; ?>

                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex justify-between items-start mb-6">
                            <div class="<?= $isFeatured ? 'w-16 h-16 bg-surface-container-high' : 'w-14 h-14 bg-surface-container-low' ?> rounded-2xl flex items-center justify-center group-hover:bg-primary-container transition-colors duration-300 shadow-sm">
                                <span class="material-symbols-outlined text-primary group-hover:text-on-primary-container text-[28px] <?= $isFeatured ? 'fill-icon' : '' ?> transition-colors duration-300"><?= htmlspecialchars($meta['icon']) ?></span>
                            </div>
                            <span class="px-3 py-1 bg-surface rounded-full text-xs font-medium text-on-surface-variant flex items-center gap-1 border border-outline-variant/20">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                <?= htmlspecialchars($serviceData['duration']) ?>
                            </span>
                        </div>
                        
                        <h3 class="font-headline-md text-headline-md text-on-background mb-3 font-semibold group-hover:text-primary transition-colors"><?= htmlspecialchars($serviceData['label']) ?></h3>
                        <p class="font-body-md text-body-md text-on-surface-variant flex-grow <?= $isFeatured ? 'max-w-md' : '' ?> leading-relaxed"><?= htmlspecialchars($meta['desc']) ?></p>
                        
                        <!-- Fixed Dynamic Link to Dedicated Service Page -->
                        <div class="mt-8 flex items-center">
                            <a href="<?= $base_url ?? '' ?>/services/<?= urlencode($key) ?>.php" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-surface-container-high text-primary font-label-md text-sm hover:bg-primary hover:text-white transition-all duration-300 w-full md:w-auto">
                                Learn more 
                                <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_right_alt</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-xl bg-surface-container-low fade-in-up" id="why-choose-us">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="inline-block py-1 px-3 rounded-xl bg-primary-container/20 text-primary font-label-sm text-label-sm mb-4 border border-primary/10">Clinic Advantages</span>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">The DentalCare Pro Difference</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">We combine cutting-edge technology with a patient-first approach to redefine your dental experience.</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Feature 1 -->
            <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 hover:border-primary/40 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">psychology</span>
                </div>
                <h4 class="font-bold text-on-background mb-2">AI-Assisted Diagnosis</h4>
                <p class="text-sm text-on-surface-variant">Precision insights for accurate and early treatment planning.</p>
            </div>
            <!-- Feature 2 -->
            <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 hover:border-primary/40 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">spa</span>
                </div>
                <h4 class="font-bold text-on-background mb-2">Comfortable Experience</h4>
                <p class="text-sm text-on-surface-variant">A spa-like environment designed to eliminate dental anxiety.</p>
            </div>
            <!-- Feature 3 -->
            <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 hover:border-primary/40 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">event_available</span>
                </div>
                <h4 class="font-bold text-on-background mb-2">Online Appointment</h4>
                <p class="text-sm text-on-surface-variant">Seamlessly book and manage your visits 24/7 online.</p>
            </div>
            <!-- Feature 4 -->
            <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 hover:border-primary/40 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">sanitizer</span>
                </div>
                <h4 class="font-bold text-on-background mb-2">Sterilized Environment</h4>
                <p class="text-sm text-on-surface-variant">Strict adherence to the highest international hygiene standards.</p>
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12 opacity-0" /></div>

<!-- Dentist Section -->
<section class="py-xl bg-surface-container-lowest fade-in-up" id="dentists">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <!-- Enhanced Featured Dentist -->
        <div class="flex flex-col lg:flex-row bg-surface-container-lowest rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-outline-variant/20 group relative">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000 pointer-events-none"></div>

            <div class="lg:w-2/5 relative h-96 lg:h-auto overflow-hidden">
                <img class="absolute inset-0 w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-1000" alt="Dr. Maria Santos, Lead Practitioner at DentalCare Pro" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBo_w78N2XULoEbVHqmSIePlmFD1bSVx8i_Rote2fYoyu5p0-5lFMoJmKOR-wq1Jel3bUzyUZFF_GGderejoknzrdxrqX6UNt5RCV225IaGT5t4CcLB6efdfW8jbpk_9-_bMONSju3RQ9YVk3rAq6VsupaIIDIR4--B9rlv9Cw-wdFfIH_DEhFndOTjWwnxIaIFxbuKCV-IROtQmUqfd8yMj6lR3-Vw3R6cHtXCmp9mrDr4zD8EBIaQX8BkXakX-H0u4en4ewDp1X4"/>
                <div class="absolute bottom-4 left-4 right-4 glass-panel rounded-xl p-3 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-primary">verified</span>
                    <span class="text-sm font-semibold text-on-background">Board Certified</span>
                </div>
            </div>
            <div class="lg:w-3/5 p-lg md:p-12 flex flex-col justify-center relative z-10">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="px-4 py-1.5 bg-primary-container/30 text-primary font-label-sm text-sm rounded-full font-semibold border border-primary/10">Lead Practitioner</span>
                    <span class="flex items-center gap-1 text-xs font-semibold text-on-surface-variant bg-surface px-3 py-1.5 rounded-full border border-outline-variant/20"><span class="material-symbols-outlined text-[14px]">school</span> DDS, MS</span>
                    <span class="flex items-center gap-1 text-xs font-semibold text-on-surface-variant bg-surface px-3 py-1.5 rounded-full border border-outline-variant/20"><span class="material-symbols-outlined text-[14px]">military_tech</span> 15+ Years Exp.</span>
                </div>
                
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-2 tracking-tight">Dr. Maria Santos</h2>
                <p class="font-label-md text-label-md text-primary mb-6">Restorative & Cosmetic Dentistry</p>
                
                <p class="text-lg font-medium text-on-background mb-4">"My goal is to give you a smile you're proud to share, in an environment where you feel completely at ease."</p>

                <p class="font-body-md text-body-md text-on-surface-variant mb-8 leading-relaxed">
                    Dr. Santos brings over 15 years of dedicated experience in advanced restorative procedures. Her philosophy centers on minimally invasive techniques and patient education, ensuring every individual understands their treatment plan completely. Her gentle approach has made her a favorite among patients with dental anxiety.
                </p>

                <!-- Clinic Values Grid -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-green-500 text-[20px]">check_circle</span>
                        <span class="text-sm text-on-surface-variant font-medium">Minimally Invasive</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-green-500 text-[20px]">check_circle</span>
                        <span class="text-sm text-on-surface-variant font-medium">Anxiety-Free Care</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-green-500 text-[20px]">check_circle</span>
                        <span class="text-sm text-on-surface-variant font-medium">Patient Education</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-green-500 text-[20px]">check_circle</span>
                        <span class="text-sm text-on-surface-variant font-medium">Advanced Aesthetics</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <a href="booking.php" class="w-full sm:w-auto h-12 px-8 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center">Book with Dr. Santos</a>
                    <a class="text-primary hover:text-on-primary-fixed-variant hover:underline font-label-md text-label-md flex items-center gap-1 group/link transition-colors" href="#">View Full Profile <span class="material-symbols-outlined text-[18px] group-hover/link:translate-x-1 group-hover/link:-translate-y-1 transition-transform duration-300">arrow_outward</span></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-xl bg-surface-container-low fade-in-up" id="gallery">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
            <div class="max-w-xl">
                <span class="inline-block py-1 px-3 rounded-xl bg-primary-container/20 text-primary font-label-sm text-label-sm mb-4 border border-primary/10">Clinic Tour</span>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background tracking-tight">Experience Our Environment</h2>
            </div>
            <a href="#" class="text-primary hover:underline font-medium flex items-center gap-1">View full gallery <span class="material-symbols-outlined text-[18px]">arrow_right_alt</span></a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 grid-rows-2 gap-4 h-[500px]">
            <div class="col-span-2 row-span-2 relative rounded-3xl overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1606811841689-23dfddce3e95?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Modern Dental Reception" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"/>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
                    <span class="text-white font-medium">Welcoming Reception</span>
                </div>
            </div>
            <div class="col-span-1 row-span-1 relative rounded-2xl overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="State of the art treatment room" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"/>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                    <span class="text-white font-medium text-sm">Treatment Rooms</span>
                </div>
            </div>
            <div class="col-span-1 row-span-1 relative rounded-2xl overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Advanced Dental Equipment" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"/>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                    <span class="text-white font-medium text-sm">Advanced Tech</span>
                </div>
            </div>
            <div class="col-span-2 md:col-span-2 row-span-1 relative rounded-2xl overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1537368910025-700350fe46c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Comfortable Waiting Area" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700"/>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                    <span class="text-white font-medium text-sm">Relaxing Waiting Area</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12 opacity-0" /></div>

<!-- Testimonials Section -->
<section class="py-xl bg-surface-container-lowest fade-in-up" id="testimonials">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="inline-block py-1 px-3 rounded-xl bg-primary-container/20 text-primary font-label-sm text-label-sm mb-4 border border-primary/10">Patient Stories</span>
            
            <!-- Google Reviews Summary Header -->
            <div class="flex flex-col items-center justify-center gap-2 mb-6">
                <div class="flex items-center gap-1">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google" class="w-6 h-6 mr-2"/>
                    <span class="text-xl font-bold dynamic-rating">4.9</span>
                    <div class="flex gap-0.5 mx-1">
                        <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    </div>
                </div>
                <p class="text-sm text-on-surface-variant">Based on hundreds of satisfied patients</p>
            </div>

            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">What Our Patients Say</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Real experiences from real patients who trusted us with their smiles.</p>
        </div>
        
        <!-- Fallback Label -->
        <div id="fallback-label-container" class="hidden text-center mb-8">
            <span class="inline-block py-2 px-4 rounded-xl bg-surface-container-high text-primary font-label-sm text-sm font-medium border border-primary/20 shadow-sm">Sample reviews — be the first to share your experience!</span>
        </div>
        
        <!-- Live Reviews Container -->
        <div id="testimonials-grid" class="grid grid-cols-1 md:grid-cols-3 gap-gutter"></div>

        <!-- Fallback Static Reviews (Visible initially, hidden by JS if live reviews exist) -->
        <div id="fallback-testimonials" class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <div class="testimonial-card bg-surface rounded-3xl p-8 border border-outline-variant/30 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col gap-4 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-full -z-0 group-hover:scale-125 transition-transform duration-500"></div>
                <div class="flex gap-1 relative z-10">
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant italic flex-grow relative z-10 leading-relaxed">"I've always dreaded the dentist, but Dr. Santos completely changed that. The clinic feels calming, the team is incredibly kind, and my teeth have never looked better. I actually look forward to my cleanings now!"</p>
                <div class="flex items-center gap-3 pt-4 border-t border-outline-variant/20 relative z-10">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">AC</div>
                    <div>
                        <p class="font-label-md text-label-md text-on-surface font-semibold">Andrea Cruz</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px] text-green-600">verified</span> Verified Patient · Teeth Whitening</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card bg-primary rounded-3xl p-8 shadow-[0_15px_30px_rgba(0,71,141,0.25)] hover:shadow-[0_20px_40px_rgba(0,71,141,0.3)] hover:-translate-y-1 transition-all duration-300 flex flex-col gap-4 relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-tr-full"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-bl-full"></div>
                <div class="flex gap-1 relative z-10">
                    <span class="material-symbols-outlined text-yellow-300 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-300 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-300 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-300 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-300 text-[20px] fill-icon">star</span>
                </div>
                <p class="font-body-md text-body-md text-on-primary italic flex-grow relative z-10 leading-relaxed">"The orthodontic treatment here is world-class. The team monitored my progress every step of the way and answered all my questions patiently. My alignment is perfect and the whole experience was smooth from start to finish."</p>
                <div class="flex items-center gap-3 pt-4 border-t border-white/20 relative z-10">
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-on-primary font-bold text-sm flex-shrink-0">MR</div>
                    <div>
                        <p class="font-label-md text-label-md text-on-primary font-semibold">Marco Reyes</p>
                        <p class="font-label-sm text-label-sm text-on-primary/80 flex items-center gap-1"><span class="material-symbols-outlined text-[14px] text-white">verified</span> Verified Patient · Orthodontics</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card bg-surface rounded-3xl p-8 border border-outline-variant/30 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col gap-4 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-full -z-0 group-hover:scale-125 transition-transform duration-500"></div>
                <div class="flex gap-1 relative z-10">
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[20px] fill-icon">star</span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant italic flex-grow relative z-10 leading-relaxed">"I needed an emergency extraction and they fit me in the same morning. The procedure was painless and the post-care instructions were clear and thorough. Truly exceptional service when I needed it most."</p>
                <div class="flex items-center gap-3 pt-4 border-t border-outline-variant/20 relative z-10">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">SL</div>
                    <div>
                        <p class="font-label-md text-label-md text-on-surface font-semibold">Sofia Lim</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px] text-green-600">verified</span> Verified Patient · Emergency</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- CTA Links -->
        <div class="mt-12 flex flex-col items-center justify-center gap-4">
            <a href="reviews.php" class="h-12 px-8 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(0,71,141,0.25)] transition-all duration-300 flex items-center justify-center shadow-sm">
                See All Reviews
            </a>
            <a href="api/feedback/submit-feedback.php" class="text-primary hover:text-on-primary-fixed-variant hover:underline font-label-sm text-sm font-medium transition-colors">
                Share Your Experience
            </a>
        </div>

        <!-- Scroll-triggered Stats block (gently requiring scroll interaction) -->
        <div class="mt-12 bg-surface-container rounded-3xl p-8 grid grid-cols-1 md:grid-cols-3 gap-8 border border-outline-variant/20 shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-white/40 via-transparent to-transparent pointer-events-none"></div>
            
            <div class="flex flex-col items-center text-center gap-2 relative z-10">
                <span class="font-headline-xl text-headline-xl text-primary font-bold dynamic-rating">4.9</span>
                <div class="flex gap-1">
                    <span class="material-symbols-outlined text-yellow-500 text-[16px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[16px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[16px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[16px] fill-icon">star</span>
                    <span class="material-symbols-outlined text-yellow-500 text-[16px] fill-icon">star</span>
                </div>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold">Average Rating</p>
            </div>
            <div class="flex flex-col items-center text-center gap-2 relative z-10 border-t md:border-t-0 md:border-l border-outline-variant/20 pt-6 md:pt-0 pl-0 md:pl-8">
                <span class="font-headline-xl text-headline-xl text-primary font-bold">2,400+</span>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold">Happy Patients</p>
            </div>
            <div class="flex flex-col items-center text-center gap-2 relative z-10 border-t md:border-t-0 md:border-l border-outline-variant/20 pt-6 md:pt-0 pl-0 md:pl-8">
                <span class="font-headline-xl text-headline-xl text-primary font-bold">98%</span>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold">Would Recommend Us</p>
            </div>
        </div>
        
        <!-- Clinic Achievements Banner -->
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <div class="bg-surface px-4 py-2 rounded-full border border-outline-variant/20 text-sm font-medium flex items-center gap-2 text-on-surface-variant shadow-sm hover:shadow-md transition-shadow">
                <span class="material-symbols-outlined text-primary text-[18px]">trophy</span>
                Award Winning Care
            </div>
            <div class="bg-surface px-4 py-2 rounded-full border border-outline-variant/20 text-sm font-medium flex items-center gap-2 text-on-surface-variant shadow-sm hover:shadow-md transition-shadow">
                <span class="material-symbols-outlined text-primary text-[18px]">favorite</span>
                10,000+ Successful Treatments
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12 opacity-0" /></div>

<!-- FAQ & Contact Section -->
<section class="py-xl bg-background fade-in-up scroll-mt-24" id="faq">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-xl">
            <!-- FAQ Side -->
            <div>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">Frequently Asked Questions</h2>
                
                <!-- Search FAQ Field -->
                <div class="relative mb-8 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-on-surface-variant group-focus-within:text-primary transition-colors">search</span>
                    </div>
                    <input type="text" placeholder="Search for answers..." class="w-full pl-12 pr-4 py-3.5 bg-surface-container-lowest border border-outline-variant/30 rounded-xl text-on-background placeholder:text-on-surface-variant/60 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                </div>

                <!-- Existing FAQs -->
                <div class="space-y-4">
                    <div class="border border-outline-variant/30 rounded-2xl bg-surface-container-lowest overflow-hidden hover:border-primary/40 shadow-sm transition-all duration-300">
                        <button class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-surface-container-low transition-colors duration-300 group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.icon').textContent = this.nextElementSibling.classList.contains('hidden') ? 'add' : 'remove'; this.querySelector('.icon').classList.toggle('rotate-180');">
                            <span class="font-headline-md text-base font-semibold text-on-background group-hover:text-primary transition-colors">Do you accept my insurance?</span>
                            <span class="material-symbols-outlined icon text-on-surface-variant transition-transform duration-300 bg-surface w-8 h-8 flex items-center justify-center rounded-full group-hover:bg-primary-container group-hover:text-primary">add</span>
                        </button>
                        <div class="hidden px-6 pb-6 pt-1 text-on-surface-variant font-body-md text-body-md leading-relaxed">
                            We accept most major PPO insurance plans. Our front desk team will happily verify your benefits before your appointment to ensure transparent pricing.
                        </div>
                    </div>
                    <div class="border border-outline-variant/30 rounded-2xl bg-surface-container-lowest overflow-hidden hover:border-primary/40 shadow-sm transition-all duration-300">
                        <button class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-surface-container-low transition-colors duration-300 group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.icon').textContent = this.nextElementSibling.classList.contains('hidden') ? 'add' : 'remove'; this.querySelector('.icon').classList.toggle('rotate-180');">
                            <span class="font-headline-md text-base font-semibold text-on-background group-hover:text-primary transition-colors">What should I expect during my first visit?</span>
                            <span class="material-symbols-outlined icon text-on-surface-variant transition-transform duration-300 bg-surface w-8 h-8 flex items-center justify-center rounded-full group-hover:bg-primary-container group-hover:text-primary">add</span>
                        </button>
                        <div class="hidden px-6 pb-6 pt-1 text-on-surface-variant font-body-md text-body-md leading-relaxed">
                            Your initial visit includes a comprehensive exam, 3D digital x-rays, and a consultation with the dentist to discuss your oral health goals and establish a personalized care plan.
                        </div>
                    </div>
                    <div class="border border-outline-variant/30 rounded-2xl bg-surface-container-lowest overflow-hidden hover:border-primary/40 shadow-sm transition-all duration-300">
                        <button class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-surface-container-low transition-colors duration-300 group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.icon').textContent = this.nextElementSibling.classList.contains('hidden') ? 'add' : 'remove'; this.querySelector('.icon').classList.toggle('rotate-180');">
                            <span class="font-headline-md text-base font-semibold text-on-background group-hover:text-primary transition-colors">Do you offer emergency dental services?</span>
                            <span class="material-symbols-outlined icon text-on-surface-variant transition-transform duration-300 bg-surface w-8 h-8 flex items-center justify-center rounded-full group-hover:bg-primary-container group-hover:text-primary">add</span>
                        </button>
                        <div class="hidden px-6 pb-6 pt-1 text-on-surface-variant font-body-md text-body-md leading-relaxed">
                            Yes, we reserve slots daily for dental emergencies. If you are experiencing severe pain or trauma, please call our clinic immediately for priority scheduling.
                        </div>
                    </div>
                </div>

                <!-- "Still have questions?" section -->
                <div class="mt-8 p-6 bg-surface-container rounded-2xl border border-primary/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h4 class="font-semibold text-on-background mb-1">Still have questions?</h4>
                        <p class="text-sm text-on-surface-variant">Our AI assistant or team can help.</p>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-primary-container text-on-primary-container rounded-lg text-sm font-medium hover:bg-primary hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-[18px]">smart_toy</span> Chat AI
                        </button>
                        <a href="#contact" class="flex-1 sm:flex-none flex items-center justify-center px-4 py-2 bg-white border border-outline-variant/30 text-on-surface rounded-lg text-sm font-medium hover:bg-surface transition-colors">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Side -->
            <div id="contact" style="transition-delay: 0.2s;">
                <div class="bg-surface-container-lowest rounded-3xl p-8 lg:p-10 border border-outline-variant/20 h-full flex flex-col shadow-[0_10px_40px_rgba(0,0,0,0.04)] hover:shadow-[0_10px_40px_rgba(0,0,0,0.08)] transition-shadow duration-300 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-primary/5 to-transparent pointer-events-none"></div>

                    <h3 class="font-headline-lg text-2xl text-on-background mb-8 tracking-tight">Visit Our Clinic</h3>
                    
                    <div class="space-y-8 relative z-10 flex-grow">
                        <div class="flex items-start gap-5 group">
                            <div class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center flex-shrink-0 group-hover:bg-primary group-hover:shadow-lg group-hover:-translate-y-1 transition-all duration-300">
                                <span class="material-symbols-outlined text-primary group-hover:text-white text-[28px] transition-colors">location_on</span>
                            </div>
                            <div>
                                <h4 class="font-label-md text-base text-on-surface font-semibold mb-1">Location</h4>
                                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">742 Willowbrook Lane, Suite 200</p>
                                <p class="text-xs text-on-surface-variant/80 mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">local_parking</span> Free parking available at the rear.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-5 group">
                            <div class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center flex-shrink-0 group-hover:bg-primary group-hover:shadow-lg group-hover:-translate-y-1 transition-all duration-300">
                                <span class="material-symbols-outlined text-primary group-hover:text-white text-[28px] transition-colors">schedule</span>
                            </div>
                            <div class="w-full">
                                <h4 class="font-label-md text-base text-on-surface font-semibold mb-3">Operating Hours</h4>
                                <div class="grid grid-cols-2 gap-x-4 gap-y-2 font-body-md text-sm text-on-surface-variant bg-surface p-4 rounded-xl border border-outline-variant/10">
                                    <span class="font-medium text-on-surface">Mon - Fri:</span>
                                    <span>8:00 AM - 6:00 PM</span>
                                    <span class="font-medium text-on-surface">Saturday:</span>
                                    <span>9:00 AM - 2:00 PM</span>
                                    <span class="font-medium text-on-surface">Sunday:</span>
                                    <span class="text-error/80 font-medium">Closed</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start gap-5 group">
                            <div class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center flex-shrink-0 group-hover:bg-primary group-hover:shadow-lg group-hover:-translate-y-1 transition-all duration-300">
                                <span class="material-symbols-outlined text-primary group-hover:text-white text-[28px] transition-colors">call</span>
                            </div>
                            <div class="w-full">
                                <h4 class="font-label-md text-base text-on-surface font-semibold mb-1">Contact</h4>
                                <p class="font-body-md text-body-md text-on-surface-variant">555-0148</p>
                                <p class="font-body-md text-body-md text-on-surface-variant mb-2">clinic@dentalcarepro.example</p>
                                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-outline-variant/10">
                                    <span class="text-xs font-semibold px-2 py-1 bg-error/10 text-error rounded flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">emergency</span> 24/7 Emergency</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Embedded Map Placeholder -->
                    <div class="mt-8 h-40 w-full bg-surface-container rounded-2xl overflow-hidden relative group cursor-pointer border border-outline-variant/20">
                        <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Map View" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500 filter sepia-[.2] hue-rotate-[190deg] saturate-50"/>
                        <div class="absolute inset-0 flex items-center justify-center bg-black/10 group-hover:bg-black/0 transition-colors">
                            <div class="bg-white/90 backdrop-blur px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 transform group-hover:-translate-y-1 transition-transform">
                                <span class="material-symbols-outlined text-primary">directions</span>
                                <span class="text-sm font-semibold text-on-background">Get Directions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12 opacity-0" /></div>

<!-- Dashboard Section -->
<section class="py-xl bg-surface-container-low fade-in-up scroll-mt-24" id="dashboard">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="inline-block py-1 px-3 rounded-xl bg-primary-container/20 text-primary font-label-sm text-label-sm mb-4 border border-primary/10">Digital Health</span>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">Patient Dashboard</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Manage your appointments, view dental records, and track your treatment history — all in one secure place.</p>
        </div>
        
        <!-- Existing Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-10">
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/30 shadow-sm flex flex-col gap-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center border border-outline-variant/20">
                        <span class="material-symbols-outlined text-primary fill-icon text-[24px]">calendar_month</span>
                    </div>
                    <h3 class="font-headline-md text-lg text-on-background font-semibold">Upcoming</h3>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">No upcoming appointments.</p>
                <a href="booking.php" class="mt-auto h-12 px-4 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-[0_4px_15px_rgba(0,71,141,0.2)] transition-all flex items-center justify-center font-medium">Book Now</a>
            </div>
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/30 shadow-sm flex flex-col gap-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center border border-outline-variant/20">
                        <span class="material-symbols-outlined text-primary fill-icon text-[24px]">history</span>
                    </div>
                    <h3 class="font-headline-md text-lg text-on-background font-semibold">Past Visits</h3>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant">Your visit history will appear here once you've had your first appointment.</p>
            </div>
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/30 shadow-sm flex flex-col gap-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center border border-outline-variant/20">
                        <span class="material-symbols-outlined text-primary fill-icon text-[24px]">folder_open</span>
                    </div>
                    <h3 class="font-headline-md text-lg text-on-background font-semibold">Dental Records</h3>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant">X-rays, treatment notes, and prescriptions will be stored securely here.</p>
            </div>
        </div>

        <!-- Existing Dashboard Login Banner -->
        <div class="bg-surface-container rounded-3xl p-8 border border-primary/10 flex flex-col md:flex-row items-center gap-6 text-center md:text-left mb-16 shadow-[0_10px_30px_rgba(0,0,0,0.02)]">
            <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <span class="material-symbols-outlined text-primary fill-icon text-[32px]">account_circle</span>
            </div>
            <div class="flex-grow">
                <h3 class="font-headline-md text-xl text-on-background mb-1 font-semibold">Sign In to Access Your Dashboard</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">Create an account or log in to manage your full dental profile. <strong>Booked as a guest?</strong> Sign up using the same email address to automatically link your past appointments!</p>
            </div>
            <div class="flex gap-3 flex-shrink-0 w-full md:w-auto">
                <a href="login.php?mode=login" class="flex-1 md:flex-none h-12 px-6 rounded-xl border-2 border-primary text-primary font-label-md text-label-md hover:bg-primary/5 transition-colors flex items-center justify-center font-medium">Log In</a>
                <a href="login.php?mode=register" class="flex-1 md:flex-none h-12 px-6 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-[0_4px_15px_rgba(0,71,141,0.2)] transition-all flex items-center justify-center font-medium">Sign Up</a>
            </div>
        </div>

        <!-- Realistic Dashboard Visual Preview -->
        <div class="mt-8 relative mx-auto max-w-5xl rounded-[2rem] border border-outline-variant/30 bg-surface-container-lowest p-2 shadow-[0_20px_60px_rgba(0,0,0,0.08)] overflow-hidden">
            <!-- Mac window dots -->
            <div class="absolute top-4 left-6 flex gap-2 z-20">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
            </div>
            
            <div class="rounded-[1.5rem] bg-background w-full h-[500px] flex overflow-hidden border border-outline-variant/10 relative">
                <!-- Sidebar -->
                <div class="w-64 bg-surface-container-lowest border-r border-outline-variant/10 p-6 flex-col gap-6 hidden md:flex pt-14">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-full bg-primary-container text-primary flex items-center justify-center font-bold">JD</div>
                        <div>
                            <p class="font-semibold text-sm">John Doe</p>
                            <p class="text-xs text-on-surface-variant">Patient Portal</p>
                        </div>
                    </div>
                    <nav class="space-y-2">
                        <div class="flex items-center gap-3 px-3 py-2 bg-primary/10 text-primary rounded-lg font-medium text-sm"><span class="material-symbols-outlined text-[20px]">home</span> Overview</div>
                        <div class="flex items-center gap-3 px-3 py-2 text-on-surface-variant hover:bg-surface rounded-lg font-medium text-sm"><span class="material-symbols-outlined text-[20px]">event</span> Appointments</div>
                        <div class="flex items-center gap-3 px-3 py-2 text-on-surface-variant hover:bg-surface rounded-lg font-medium text-sm"><span class="material-symbols-outlined text-[20px]">medical_information</span> Records & X-Rays</div>
                        <div class="flex items-center gap-3 px-3 py-2 text-on-surface-variant hover:bg-surface rounded-lg font-medium text-sm"><span class="material-symbols-outlined text-[20px]">prescriptions</span> Prescriptions</div>
                    </nav>
                </div>
                
                <!-- Main Content Area -->
                <div class="flex-1 p-6 md:p-10 pt-14 md:pt-10 overflow-y-auto hide-scrollbar bg-background">
                    <h4 class="text-2xl font-bold text-on-background mb-6">Good morning, John</h4>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <!-- Upcoming Appt Widget -->
                        <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/20 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-full pointer-events-none"></div>
                            <div class="flex justify-between items-start mb-4">
                                <h5 class="font-semibold flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">notification_important</span> Next Appointment</h5>
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-medium">Confirmed</span>
                            </div>
                            <div class="flex gap-4 items-center mb-6">
                                <div class="bg-surface text-center rounded-xl p-3 min-w-[70px] border border-outline-variant/20">
                                    <p class="text-xs text-on-surface-variant font-bold uppercase">Oct</p>
                                    <p class="text-2xl font-bold text-primary">14</p>
                                </div>
                                <div>
                                    <p class="font-bold text-on-background text-lg">Routine Checkup & Cleaning</p>
                                    <p class="text-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">schedule</span> 10:00 AM - 10:45 AM</p>
                                    <p class="text-sm text-on-surface-variant flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[16px]">person</span> Dr. Maria Santos</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <button class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg shadow-sm">Reschedule</button>
                                <button class="px-4 py-2 border border-outline-variant/30 text-on-surface text-sm font-medium rounded-lg">Add to Calendar</button>
                            </div>
                        </div>

                        <!-- Timeline/Progress Widget -->
                        <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/20 shadow-sm">
                            <h5 class="font-semibold mb-4">Treatment Plan</h5>
                            <div class="space-y-4">
                                <div class="flex gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center"><span class="material-symbols-outlined text-[14px]">check</span></div>
                                        <div class="w-0.5 h-6 bg-green-500 my-1"></div>
                                    </div>
                                    <div class="pb-2">
                                        <p class="text-sm font-bold">Initial Consult</p>
                                        <p class="text-xs text-on-surface-variant">Completed Sep 1</p>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="w-6 h-6 rounded-full bg-primary flex items-center justify-center border-2 border-primary-container text-white"><span class="w-2 h-2 bg-white rounded-full"></span></div>
                                        <div class="w-0.5 h-6 bg-outline-variant/30 my-1"></div>
                                    </div>
                                    <div class="pb-2">
                                        <p class="text-sm font-bold text-primary">Deep Cleaning</p>
                                        <p class="text-xs text-on-surface-variant">Scheduled Oct 14</p>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="w-6 h-6 rounded-full bg-surface border-2 border-outline-variant/30"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-on-surface-variant">Whitening Session</p>
                                        <p class="text-xs text-on-surface-variant">Pending</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Documents -->
                    <h5 class="font-semibold mb-4">Recent Records</h5>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/20 flex flex-col items-center justify-center text-center gap-2 hover:border-primary/50 cursor-pointer">
                            <span class="material-symbols-outlined text-primary text-[32px]">radiology</span>
                            <span class="text-xs font-medium">Panoramic X-Ray</span>
                        </div>
                        <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/20 flex flex-col items-center justify-center text-center gap-2 hover:border-primary/50 cursor-pointer">
                            <span class="material-symbols-outlined text-primary text-[32px]">description</span>
                            <span class="text-xs font-medium">Treatment Plan.pdf</span>
                        </div>
                    </div>
                </div>

                <!-- AI Chat Widget Overlay (Visual Only) -->
                <div class="absolute bottom-6 right-6 w-14 h-14 bg-on-background rounded-full shadow-xl flex items-center justify-center cursor-pointer animate-pulse-slow z-30">
                    <span class="material-symbols-outlined text-white text-[28px]">smart_toy</span>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-error rounded-full border-2 border-background"></span>
                </div>
            </div>

            <!-- Overlay to imply it's a preview -->
            <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent flex items-end justify-center pb-8 z-40 pointer-events-none">
                <span class="bg-background/80 backdrop-blur px-6 py-2 rounded-full border border-outline-variant/20 text-sm font-medium shadow-sm text-on-surface">Interactive Preview</span>
            </div>
        </div>
    </div>
</section>

<!-- AI Assistant Section -->
<section class="py-xl bg-surface-container-lowest border-y border-outline-variant/10 fade-in-up" id="ai-assistant">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-xl items-center">
            <div class="order-2 lg:order-1 relative">
                <!-- Abstract visual representation of AI chat -->
                <div class="relative w-full max-w-md mx-auto aspect-[4/5] bg-surface rounded-[2.5rem] border-[8px] border-surface-container-highest shadow-2xl overflow-hidden p-6 flex flex-col gap-4">
                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-outline-variant/10">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-[20px]">smart_toy</span>
                        </div>
                        <div>
                            <p class="font-bold text-sm">ProCare AI</p>
                            <p class="text-xs text-green-500">Online</p>
                        </div>
                    </div>
                    
                    <div class="bg-surface-container p-4 rounded-2xl rounded-tl-sm text-sm self-start max-w-[85%]">
                        Hello! I'm your DentalCare Pro AI Assistant. How can I help you with your smile today?
                    </div>
                    <div class="bg-primary text-white p-4 rounded-2xl rounded-tr-sm text-sm self-end max-w-[85%]">
                        I have some mild sensitivity when drinking cold water. Should I be worried?
                    </div>
                    <div class="bg-surface-container p-4 rounded-2xl rounded-tl-sm text-sm self-start max-w-[85%] shadow-sm relative">
                        <div class="flex gap-1 mb-2">
                            <span class="w-1.5 h-1.5 bg-primary/40 rounded-full animate-bounce"></span>
                            <span class="w-1.5 h-1.5 bg-primary/60 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                            <span class="w-1.5 h-1.5 bg-primary rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                        </div>
                        While mild sensitivity is common, it could indicate worn enamel or a minor cavity. I recommend scheduling a brief checkup. Would you like to see Dr. Santos's availability this week?
                    </div>
                    
                    <!-- decorative gradient overlay -->
                    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-surface to-transparent"></div>
                </div>
                
                <!-- Floating badges -->
                <div class="absolute top-1/4 -right-4 lg:-right-12 glass-panel px-4 py-3 rounded-2xl shadow-lg flex items-center gap-3 animate-float-delayed">
                    <span class="material-symbols-outlined text-green-500">lock</span>
                    <span class="text-xs font-bold">HIPAA Secure</span>
                </div>
            </div>
            
            <div class="order-1 lg:order-2">
                <span class="inline-block py-1 px-3 rounded-xl bg-primary-container/20 text-primary font-label-sm text-label-sm mb-4 border border-primary/10">Next-Gen Support</span>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-6 tracking-tight">Meet Your AI Dental Assistant</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mb-8 leading-relaxed">
                    Experience healthcare innovation. Our secure AI assistant is available 24/7 to answer questions, assess minor symptoms, remind you of medications, and help manage your appointments effortlessly.
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                        <span class="material-symbols-outlined text-primary text-[24px]">troubleshoot</span>
                        <div>
                            <h5 class="font-bold text-sm mb-1">Symptom Checker</h5>
                            <p class="text-xs text-on-surface-variant">Instant preliminary guidance for oral discomfort.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                        <span class="material-symbols-outlined text-primary text-[24px]">edit_calendar</span>
                        <div>
                            <h5 class="font-bold text-sm mb-1">Smart Scheduling</h5>
                            <p class="text-xs text-on-surface-variant">Find the perfect slot and book in seconds.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                        <span class="material-symbols-outlined text-primary text-[24px]">tips_and_updates</span>
                        <div>
                            <h5 class="font-bold text-sm mb-1">Dental Care Tips</h5>
                            <p class="text-xs text-on-surface-variant">Personalized hygiene advice post-treatment.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                        <span class="material-symbols-outlined text-primary text-[24px]">history</span>
                        <div>
                            <h5 class="font-bold text-sm mb-1">Context Aware</h5>
                            <p class="text-xs text-on-surface-variant">Remembers your history for better assistance.</p>
                        </div>
                    </div>
                </div>
                
                <a href="#" class="inline-flex items-center gap-2 h-12 px-8 rounded-xl bg-on-background text-background font-label-md hover:opacity-90 hover:-translate-y-0.5 transition-all shadow-lg">
                    Try AI Assistant
                    <span class="material-symbols-outlined text-[20px]">chat</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Final Call to Action -->
<section class="relative py-24 overflow-hidden fade-in-up mt-12 mx-margin-mobile md:mx-margin-desktop max-w-[1200px] xl:mx-auto rounded-3xl">
    <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary to-surface-tint z-0"></div>
    <!-- Abstract shapes -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-black/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>
    
    <div class="relative z-10 text-center px-6 md:px-12 max-w-3xl mx-auto flex flex-col items-center">
        <h2 class="font-headline-xl text-3xl md:text-5xl text-white mb-6 font-bold tracking-tight">Ready for a healthier smile?</h2>
        <p class="text-primary-container text-lg md:text-xl mb-10 max-w-2xl text-white/90">
            Join thousands of satisfied patients. Book your appointment today and experience the future of modern dental care.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <a href="booking.php" class="h-14 px-8 rounded-xl bg-white text-primary font-bold text-base hover:shadow-[0_0_30px_rgba(255,255,255,0.4)] hover:-translate-y-1 transition-all duration-300 flex items-center justify-center w-full sm:w-auto">
                Book Appointment
            </a>
            <a href="#contact" class="h-14 px-8 rounded-xl border border-white/30 text-white font-bold text-base hover:bg-white/10 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center w-full sm:w-auto backdrop-blur-sm">
                Contact Us
            </a>
        </div>
    </div>
</section>

<!-- Enhanced Footer -->
<footer class="w-full py-xl bg-surface-container-highest mt-xl fade-in-up border-t border-outline-variant/10">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            <!-- Brand Column -->
            <div class="flex flex-col gap-4">
                <span class="font-headline-md text-2xl font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[32px]">dentistry</span> DentalCare Pro
                </span>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-xs mt-2">
                    Exceptional, precision-driven dental care in a state-of-the-art, relaxing environment.
                </p>
                <div class="flex gap-4 mt-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant/30 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-colors">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant/30 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-colors">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant/30 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-colors">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="flex flex-col gap-4">
                <h4 class="font-bold text-on-background">Quick Navigation</h4>
                <nav class="flex flex-col gap-2">
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#home">Home</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#about">About Us</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="services/services.php">Our Services</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#dentists">Meet the Doctor</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#testimonials">Patient Stories</a>
                </nav>
            </div>

            <!-- Patient Resources -->
            <div class="flex flex-col gap-4">
                <h4 class="font-bold text-on-background">Patient Resources</h4>
                <nav class="flex flex-col gap-2">
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#dashboard">Patient Portal</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#faq">FAQ</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#">Privacy Policy</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#">Terms of Service</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#">HIPAA Compliance</a>
                </nav>
            </div>

            <!-- Contact Info -->
            <div class="flex flex-col gap-4">
                <h4 class="font-bold text-on-background">Get in Touch</h4>
                <div class="flex flex-col gap-3">
                    <p class="text-sm text-on-surface-variant flex items-start gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">location_on</span>
                        742 Willowbrook Lane, Suite 200
                    </p>
                    <p class="text-sm text-on-surface-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">call</span>
                        555-0148
                    </p>
                    <p class="text-sm text-on-surface-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">mail</span>
                        clinic@dentalcarepro.example
                    </p>
                    <div class="mt-2 p-3 bg-error-container/50 rounded-lg border border-error/20 inline-flex items-center gap-2 w-fit">
                        <span class="material-symbols-outlined text-error text-[18px]">emergency</span>
                        <span class="text-xs font-bold text-error">24/7 Emergency Dental Care</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-outline-variant/20 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <p class="font-body-md text-sm text-on-surface-variant">© 2026 DentalCare Pro Clinic. All rights reserved.</p>
            <p class="font-body-md text-sm text-on-surface-variant flex items-center gap-1">Designed with <span class="material-symbols-outlined text-[14px] text-error fill-icon">favorite</span> for exceptional care</p>
        </div>
    </div>
</footer>

<script>
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    document.addEventListener('DOMContentLoaded', () => {
        // --- Cross-Page Hash Scrolling ---
        if (window.location.hash) {
            const targetId = window.location.hash.substring(1);
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                // Let the page settle (images, layout) before animating
                setTimeout(() => {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            }
        }

        // --- Fetch Live Testimonials & Stats ---
        const initHomeCounters = () => {
            document.querySelectorAll('#home .counter').forEach(counter => {
                const target = parseFloat(counter.getAttribute('data-target'));
                const isDecimal = counter.getAttribute('data-decimal') === 'true';
                const duration = 1500;
                const startTime = performance.now();

                function update(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const value = target * progress;
                    counter.textContent = isDecimal ? value.toFixed(1) : Math.round(value);
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        counter.textContent = isDecimal ? target.toFixed(1) : target;
                    }
                }
                requestAnimationFrame(update);
            });
        };

        fetch('api/feedback/feedback-list.php?sort=recent&limit=3')
            .then(res => res.json())
            .then(data => {
                const avgRating = data.average_rating ? parseFloat(data.average_rating).toFixed(1) : '4.9';
                
                // Update dynamic ratings around the homepage
                document.querySelectorAll('.dynamic-rating').forEach(el => {
                    if (el.classList.contains('counter')) {
                        el.setAttribute('data-target', avgRating);
                    } else {
                        const suffix = el.getAttribute('data-suffix') || '';
                        el.textContent = avgRating + suffix;
                    }
                });

                const grid = document.getElementById('testimonials-grid');
                const fallbackGrid = document.getElementById('fallback-testimonials');
                const fallbackLabel = document.getElementById('fallback-label-container');

                if (data.reviews && data.reviews.length > 0) {
                    fallbackGrid.style.display = 'none';
                    
                    data.reviews.forEach((review, index) => {
                        const isMiddle = index === 1;
                        const bgClass = isMiddle ? 'bg-primary' : 'bg-surface';
                        const textClass = isMiddle ? 'text-on-primary' : 'text-on-surface-variant';
                        const nameClass = isMiddle ? 'text-on-primary' : 'text-on-surface';
                        const verifiedClass = isMiddle ? 'text-white' : 'text-green-600';
                        const starClass = isMiddle ? 'text-yellow-300' : 'text-yellow-500';
                        
                        let starsHtml = '';
                        const ratingNum = parseInt(review.rating, 10) || 5;
                        for(let i = 1; i <= 5; i++) {
                            const activeClass = i <= ratingNum ? starClass : (isMiddle ? 'text-white/30' : 'text-outline-variant/50');
                            starsHtml += `<span class="material-symbols-outlined ${activeClass} text-[20px] fill-icon">star</span>`;
                        }
                        
                        const badgeHtml = review.is_new ? `<span class="bg-tertiary text-on-tertiary text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider ml-2">New</span>` : '';
                        const serviceHtml = review.service_received ? ` · ${review.service_received}` : '';
                        const patientName = review.patient_name || 'Anonymous';
                        const initial = patientName.substring(0, 2).toUpperCase();

                        const card = `
                            <div class="testimonial-card ${bgClass} rounded-3xl p-8 ${isMiddle ? 'shadow-[0_15px_30px_rgba(0,71,141,0.25)] hover:shadow-[0_20px_40px_rgba(0,71,141,0.3)]' : 'border border-outline-variant/30 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-xl'} hover:-translate-y-1 transition-all duration-300 flex flex-col gap-4 relative overflow-hidden group">
                                ${isMiddle ? 
                                    `<div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-tr-full"></div>
                                     <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-bl-full"></div>` : 
                                    `<div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-full -z-0 group-hover:scale-125 transition-transform duration-500"></div>`
                                }
                                <div class="flex gap-1 relative z-10 items-center">
                                    ${starsHtml}
                                </div>
                                <p class="font-body-md text-body-md ${textClass} italic flex-grow relative z-10 leading-relaxed">"${review.comment}"</p>
                                <div class="flex items-center gap-3 pt-4 border-t ${isMiddle ? 'border-white/20' : 'border-outline-variant/20'} relative z-10">
                                    <div class="w-12 h-12 rounded-full ${isMiddle ? 'bg-white/20 text-on-primary' : 'bg-primary-container text-primary'} flex items-center justify-center font-bold text-sm flex-shrink-0">${initial}</div>
                                    <div>
                                        <p class="font-label-md text-label-md ${nameClass} font-semibold flex items-center">${patientName} ${badgeHtml}</p>
                                        <p class="font-label-sm text-label-sm ${isMiddle ? 'text-on-primary/80' : 'text-on-surface-variant'} flex items-center gap-1"><span class="material-symbols-outlined text-[14px] ${verifiedClass}">verified</span> Verified Patient${serviceHtml}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        grid.insertAdjacentHTML('beforeend', card);
                    });
                } else {
                    fallbackLabel.classList.remove('hidden');
                }
            })
            .catch(err => {
                console.error("Error fetching feedback:", err);
                const fallbackLabel = document.getElementById('fallback-label-container');
                if (fallbackLabel) fallbackLabel.classList.remove('hidden');
            })
            .finally(() => {
                initHomeCounters();
            });

        // --- Fade-in on scroll & Scroll counters ---
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    
                    // Trigger scroll-dependent number counters ONLY if present and outside #home
                    const counters = entry.target.querySelectorAll('.counter:not(#home .counter)');
                    counters.forEach(counter => {
                        const updateCount = () => {
                            const target = parseFloat(counter.getAttribute('data-target'));
                            const count = parseFloat(counter.innerText);
                            const isDecimal = counter.hasAttribute('data-decimal');
                            
                            const speed = 200; // Lower is faster
                            const inc = target / speed;

                            if (count < target) {
                                let nextValue = count + inc;
                                if(isDecimal) {
                                    counter.innerText = (nextValue > target ? target : nextValue).toFixed(1);
                                } else {
                                    counter.innerText = Math.ceil(nextValue);
                                }
                                setTimeout(updateCount, 15);
                            } else {
                                counter.innerText = target + (isDecimal && target % 1 === 0 ? '.0' : '');
                            }
                        };
                        
                        // Prevent re-running if already done
                        if (counter.innerText === "0") {
                            updateCount();
                        }
                    });

                    obs.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
    });
</script>
</body>
</html>