<?php
// Global app config — defines $base_url
require_once __DIR__ . '/config/app.php';
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
    <script src="<?= htmlspecialchars($base_url) ?>/assets/js/theme-config.js"></script>
    <link rel="stylesheet" href="<?= htmlspecialchars($base_url) ?>/assets/css/theme-base.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base_url) ?>/assets/css/responsive.css">
    
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
        .stagger-5 { transition-delay: 500ms; }
        .stagger-6 { transition-delay: 600ms; }
        .stagger-7 { transition-delay: 700ms; }
        .stagger-8 { transition-delay: 800ms; }

        /* --- Hero Photo Carousel --- */
        .hero-carousel-viewport {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }
        .hero-carousel-track {
            display: flex;
            width: 100%;
            height: 100%;
            will-change: transform;
            /* Transition is now handled inline dynamically via JS for seamless looping */
        }
        .hero-slide {
            flex: 0 0 100%;
            width: 100%;
            height: 100%;
            position: relative;
        }
        .hero-slide img {
            transform: scale(1.15) translateY(0);
            will-change: transform;
        }
        @media (prefers-reduced-motion: reduce) {
            .hero-slide img { transform: scale(1.15) translateY(0) !important; }
        }
        .hero-arrow {
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            background: var(--tw-color-surface-container-lowest, #fff);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border: 1px solid rgba(194, 198, 212, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0b1c30;
            transition: all 0.3s ease;
        }
        .hero-arrow:hover {
            background: #00478d;
            color: #fff;
            border-color: #00478d;
        }
        .hero-arrow:disabled { cursor: default; opacity: 0.3; pointer-events: none; }
        .hero-dot {
            width: 7px;
            height: 7px;
            border-radius: 9999px;
            background: transparent;
            border: 1.5px solid var(--tw-color-primary, #00478d);
            border-color: rgba(0, 71, 141, 0.5);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hero-dot.is-active {
            width: 20px;
            background: #00478d;
            border-color: #00478d;
        }

        /* --- Hero per-slide text & badge crossfade --- */
        .hero-fade-swap {
            transition: opacity 0.45s ease;
        }
        .hero-fade-swap.is-swapping {
            opacity: 0;
        }
        #hero-headline {
            font-weight: 700;
        }
        @media (min-width: 768px) {
            #hero-headline { font-size: 3.75rem; line-height: 1.1; }
        }

        /* --- Premium Services Section (Apple-Inspired) --- */
        .premium-services-section {
            --services-bg: #f8f9ff;
            --blob-1: rgba(0, 93, 182, 0.12);
            --blob-2: rgba(105, 242, 181, 0.12);
            background-color: var(--services-bg);
            transition: background-color 1.2s cubic-bezier(.22,.61,.36,1);
            position: relative;
            overflow: hidden;
        }
        .premium-blob-1, .premium-blob-2 {
            position: absolute;
            filter: blur(120px);
            border-radius: 50%;
            pointer-events: none;
            transition: background 1.2s cubic-bezier(.22,.61,.36,1);
            z-index: 0;
        }
        .premium-blob-1 {
            width: 600px; height: 600px; top: -100px; right: -100px;
            background: var(--blob-1);
            animation: floatBlob1 25s ease-in-out infinite alternate;
        }
        .premium-blob-2 {
            width: 800px; height: 800px; bottom: -200px; left: -200px;
            background: var(--blob-2);
            animation: floatBlob2 30s ease-in-out infinite alternate-reverse;
        }
        @keyframes floatBlob1 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(-50px, 30px) scale(1.05) rotate(5deg); }
            100% { transform: translate(20px, -40px) scale(0.95) rotate(-5deg); }
        }
        @keyframes floatBlob2 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(40px, -30px) scale(1.02) rotate(-3deg); }
            100% { transform: translate(-20px, 40px) scale(0.98) rotate(3deg); }
        }
        .premium-large-num {
            position: absolute;
            top: 40%; left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20rem;
            font-weight: 800;
            color: var(--tw-color-primary);
            opacity: 0.03;
            z-index: 1;
            pointer-events: none;
            line-height: 1;
            transition: all 0.8s cubic-bezier(.22,.61,.36,1);
        }
        @media (min-width: 768px) {
            .premium-large-num {
                font-size: 35rem;
            }
        }
        .premium-large-num.animating {
            transform: translate(-50%, -55%);
            opacity: 0;
        }
        .premium-carousel-track {
            display: flex;
            gap: 24px;
            padding-left: 0;
            will-change: transform;
            position: relative;
            z-index: 10;
            touch-action: pan-y;
            cursor: grab;
        }
        .premium-carousel-track:active {
            cursor: grabbing;
        }
        @media (min-width: 768px) {
            .premium-carousel-track {
                padding-left: 12.5%; /* Centers the 75% wide active card, revealing 25% of the next */
            }
        }
        .premium-card {
            flex: 0 0 100%;
            width: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        @media (min-width: 768px) {
            .premium-card {
                flex: 0 0 75%;
                width: 75%;
            }
        }
        /* Cinematic Image Animations */
        .premium-img-wrap {
            transform: scale(0.96);
            border-radius: 2rem;
            overflow: hidden;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.9s cubic-bezier(.22,.61,.36,1);
        }
        .premium-img {
            width: 100%; height: 100%;
            object-fit: cover;
            transform: scale(1.08);
            filter: blur(4px);
            opacity: 0.7;
            transition: all 0.9s cubic-bezier(.22,.61,.36,1);
        }
        /* Sequential Text Animations */
        .premium-text-wrap > * {
            opacity: 0;
            transform: translateY(24px);
            transition: all 0.7s cubic-bezier(.22,.61,.36,1);
        }
        /* Active States */
        .premium-card.is-active .premium-img-wrap {
            transform: scale(1);
            box-shadow: 0 25px 60px rgba(0,71,141,0.15);
        }
        .premium-card.is-active .premium-img {
            transform: scale(1);
            filter: blur(0);
            opacity: 1;
        }
        .premium-card.is-active .premium-text-wrap > * {
            opacity: 1;
            transform: translateY(0);
        }
        .premium-card.is-active .anim-1 { transition-delay: 150ms; }
        .premium-card.is-active .anim-2 { transition-delay: 250ms; }
        .premium-card.is-active .anim-3 { transition-delay: 350ms; }
        .premium-card.is-active .anim-4 { transition-delay: 450ms; }

        /* Premium CTA Button */
        .premium-cta-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(.22,.61,.36,1);
        }
        .premium-cta-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(255,255,255,0.2), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .premium-cta-btn:hover {
            box-shadow: 0 15px 30px rgba(0, 71, 141, 0.25), 0 0 0 2px rgba(0, 71, 141, 0.1);
            transform: translateY(-2px); /* Slight 3D lift without generic scaling */
        }
        .premium-cta-btn:hover::before {
            opacity: 1;
        }
        .premium-cta-btn:hover .cta-icon {
            transform: translateX(4px);
        }

        /* Decorative Floating Element */
        .premium-deco-icon {
            position: absolute;
            font-size: 40rem;
            color: var(--tw-color-primary);
            opacity: 0.02;
            top: 20%;
            left: -10%;
            pointer-events: none;
            z-index: 1;
            animation: decoFloat 20s ease-in-out infinite alternate;
        }
        @keyframes decoFloat {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-50px) rotate(15deg); }
        }

        /* --- Section kicker (readable section header, shown above each section title) --- */
        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
            font-size: 0.8125rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #00478d;
        }
        .section-kicker::before {
            content: '';
            display: inline-block;
            width: 28px;
            height: 3px;
            border-radius: 2px;
            background: #00478d;
        }

        /* --- "Learn more" hover border-trail --- */
        .learn-more-loop-border {
            position: relative;
            z-index: 0;
        }
        .learn-more-loop-border .border-trail-svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
            overflow: visible;
        }
        .learn-more-loop-border:hover .border-trail-svg {
            opacity: 1;
        }
        .border-trail-rect {
            fill: none;
            stroke: var(--tw-color-primary, #00478d);
            stroke-width: 1.5;
            stroke-linecap: round;
            stroke-dasharray: 18 82;
            animation: border-trail-move 1.6s linear infinite;
        }
        @keyframes border-trail-move {
            to { stroke-dashoffset: -100; }
        }

        /* --- Why Choose Us Sticky Heading --- */
        @media (min-width: 1024px) {
            .why-choose-sticky-heading {
                position: sticky;
                top: 104px;
                align-self: start;
            }
        }

        /* --- Stats: tick-rule dividers (mono stat row) --- */
        .stat-divider {
            position: relative;
            min-width: 1px;
            width: 1px;
            align-self: stretch;
            background: rgba(114, 119, 131, 0.22);
        }
        .stat-divider::before,
        .stat-divider::after {
            content: '';
            position: absolute;
            left: 50%;
            width: 10px;
            height: 1px;
            background: rgba(114, 119, 131, 0.4);
            transform: translateX(-50%);
        }
        .stat-divider::before { top: 0; }
        .stat-divider::after { bottom: 0; }
        
        /* --- FAQ accordion toggle: plus that rotates into an x on open --- */
        .faq-toggle-icon {
            position: relative;
            display: inline-block;
            width: 14px;
            height: 14px;
            transition: transform 0.35s cubic-bezier(0.65, 0, 0.35, 1);
        }
        .faq-toggle-icon::before,
        .faq-toggle-icon::after {
            content: '';
            position: absolute;
            background: currentColor;
            border-radius: 1px;
        }
        .faq-toggle-icon::before {
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            transform: translateY(-50%);
        }
        .faq-toggle-icon::after {
            left: 50%;
            top: 0;
            width: 2px;
            height: 100%;
            transform: translateX(-50%);
        }
        .faq-toggle-icon.is-open {
            transform: rotate(45deg);
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
    <section class="relative min-h-[calc(100vh-80px)] flex items-center justify-center overflow-hidden fade-in-up">
        <div class="absolute inset-0 z-0">
            <!-- Strengthened overlay gradient scrim (keeps text legible over any slide) -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/10 z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/20 to-transparent z-10"></div>
            <div class="hero-carousel-viewport">
                <div id="hero-carousel-track" class="hero-carousel-track">
                    <div class="hero-slide" data-slide-index="0">
                        <img src="assets/img/hero-slide-1.png" alt="Modern dental clinic reception and waiting area" class="absolute inset-0 w-full h-full object-cover object-center"/>
                    </div>
                    <div class="hero-slide" data-slide-index="1">
                        <img src="assets/img/hero-slide-2.png" alt="Dental treatment room with chair and equipment" class="absolute inset-0 w-full h-full object-cover object-center"/>
                    </div>
                    <div class="hero-slide" data-slide-index="2">
                        <img src="assets/img/hero-slide-3.png" alt="Close-up of dental tools and equipment" class="absolute inset-0 w-full h-full object-cover object-center"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero Carousel Arrows -->
        <button type="button" id="hero-prev" class="hero-arrow absolute left-3 md:left-6 top-1/2 -translate-y-1/2 z-30" aria-label="Previous slide">
            <span class="icon-line">chevron_left</span>
        </button>
        <button type="button" id="hero-next" class="hero-arrow absolute right-3 md:right-6 top-1/2 -translate-y-1/2 z-30" aria-label="Next slide">
            <span class="icon-line">chevron_right</span>
        </button>

        <!-- Hero Carousel Dot Indicators -->
        <div class="absolute bottom-20 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 glass-panel py-2 px-3 rounded-full shadow-sm" id="hero-carousel-dots" aria-hidden="true">
            <span class="hero-dot is-active"></span>
            <span class="hero-dot"></span>
            <span class="hero-dot"></span>
        </div>
        
        <!-- Desktop Floating Glassmorphism Cards -->
        <a href="#ai-assistant" class="hero-floating-cards-desktop absolute top-[18%] right-[12%] z-20 flex items-center gap-4 glass-panel p-4 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] animate-float hover:scale-105 hover:shadow-[0_12px_40px_rgba(0,0,0,0.12)] hover:border-primary/30 transition-all duration-300 cursor-pointer group/card">
            <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center group-hover/card:bg-primary transition-colors duration-300">
                <span class="icon-line text-primary group-hover/card:text-white fill-icon">smart_toy</span>
            </div>
            <div>
                <p class="font-label-md font-bold text-on-background group-hover/card:text-primary transition-colors duration-300">ProCare AI</p>
                <p class="text-xs text-primary font-medium flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Active 24/7</p>
            </div>
        </a>
        
        <div class="relative z-20 w-full max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
            <div class="max-w-2xl">
                <!-- Google Review Badge -->
                <div class="inline-flex items-center gap-2 py-1.5 px-3 rounded-full bg-white shadow-sm border border-outline-variant/30 mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google" class="w-4 h-4"/>
                    <div class="flex gap-0.5">
                        <span class="icon-line text-yellow-500 text-[14px] fill-icon">star</span>
                        <span class="icon-line text-yellow-500 text-[14px] fill-icon">star</span>
                        <span class="icon-line text-yellow-500 text-[14px] fill-icon">star</span>
                        <span class="icon-line text-yellow-500 text-[14px] fill-icon">star</span>
                        <span class="icon-line text-yellow-500 text-[14px] fill-icon">star</span>
                    </div>
                    <span class="text-xs font-bold text-on-surface dynamic-rating" data-suffix=" Rating">4.9 Rating</span>
                </div>

                <!-- Per-slide text block -->
                <div id="hero-text-block" class="hero-fade-swap">
                    <div class="mb-6"><span id="hero-eyebrow" class="inline-block py-1 px-3 rounded-xl bg-white/20 text-white font-label-sm text-label-sm border border-white/20 backdrop-blur-md">Premium Care</span></div>

                    <h1 id="hero-headline" class="font-display text-headline-xl text-white mb-6 leading-tight tracking-tight">
                        Exceptional Dental Care for a <span class="text-accent">Brighter Smile.</span>
                    </h1>
                    <p id="hero-subtext" class="font-body-lg text-body-lg text-white/80 mb-10 max-w-xl">
                        Welcome to DentalCare Pro, where precision meets comfort. Experience world-class dental treatments in a state-of-the-art, relaxing environment.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 mb-8">
                    <a href="<?= htmlspecialchars($base_url) ?>/booking.php" class="group h-12 px-8 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-[0_8px_20px_rgba(0,71,141,0.25)] hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2">
                        Book Appointment
                        <span class="icon-line text-[20px] transition-transform duration-300 group-hover:translate-x-1">arrow_forward</span>
                    </a>
                    <a href="#services" class="h-12 px-8 rounded-lg border-[1.5px] border-primary text-primary font-label-md text-label-md hover:bg-surface-container-low hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all duration-300 flex items-center justify-center bg-white/50 backdrop-blur-sm">
                        Our Services
                    </a>
                </div>
                
                <div id="hero-trust-tags" class="hero-fade-swap flex items-center gap-6 text-sm text-white/90 font-medium">
                    <div class="flex items-center gap-2"><span class="icon-line text-green-600 text-[18px]">verified</span> <span data-trust-tag="1">Verified Professionals</span></div>
                    <div class="flex items-center gap-2"><span class="icon-line text-green-600 text-[18px]">verified</span> <span data-trust-tag="2">Warm, Comfortable Care</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-12 bg-surface-container-lowest border-b border-outline-variant/10 fade-in-up relative z-20 -mt-16 md:-mt-20 mx-margin-mobile md:mx-margin-desktop rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.12)] max-w-[1200px] xl:mx-auto">
        <div class="stats-row grid grid-cols-2 md:flex md:flex-row md:justify-between items-stretch gap-8 md:gap-0 px-4 md:px-8 text-center" id="stats-row">
            <div class="stat-cell flex-1 flex flex-col items-center justify-center gap-1 md:px-6">
                <span class="font-mono text-3xl md:text-4xl font-bold text-primary tabular-nums"><span class="counter" data-target="2400">0</span>+</span>
                <span class="text-xs font-label-sm text-on-surface-variant font-medium uppercase tracking-widest">Happy Patients</span>
            </div>
            <div class="stat-divider hidden md:block"></div>
            <div class="stat-cell flex-1 flex flex-col items-center justify-center gap-1 md:px-6">
                <span class="font-mono text-3xl md:text-4xl font-bold text-primary tabular-nums"><span class="counter" data-target="15">0</span>+</span>
                <span class="text-xs font-label-sm text-on-surface-variant font-medium uppercase tracking-widest">Years Experience</span>
            </div>
            <div class="stat-divider hidden md:block"></div>
            <div class="stat-cell flex-1 flex flex-col items-center justify-center gap-1 md:px-6">
                <span class="font-mono text-3xl md:text-4xl font-bold text-primary tabular-nums counter dynamic-rating" data-target="4.9" data-decimal="true">0</span>
                <span class="text-xs font-label-sm text-on-surface-variant font-medium uppercase tracking-widest">Average Rating</span>
            </div>
            <div class="stat-divider hidden md:block"></div>
            <div class="stat-cell flex-1 flex flex-col items-center justify-center gap-1 md:px-6">
                <span class="font-mono text-3xl md:text-4xl font-bold text-primary tabular-nums"><span class="counter" data-target="98">0</span>%</span>
                <span class="text-xs font-label-sm text-on-surface-variant font-medium uppercase tracking-widest">Recommendation Rate</span>
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
                <span class="section-kicker">Mission &amp; Vision</span>
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
                        <div class="w-12 h-12 rounded-full bg-primary-container border-2 border-surface-container-lowest flex items-center justify-center text-on-primary-container icon-line">verified</div>
                    </div>
                    <span class="font-label-md text-label-md text-on-surface">Years of Excellence</span>
                </div>
            </div>
            <div class="relative rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.1)] hover:shadow-[0_20px_50px_rgba(0,0,0,0.15)] transition-shadow duration-500 h-[500px]">
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent z-10 pointer-events-none"></div>
                <img class="w-full h-full object-cover hover:scale-105 transition-transform duration-700" alt="A bright, modern dental consultation room with sleek white cabinetry and advanced diagnostic displays" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDT8altnTWxHwseqGw5RHPcU8BxHFWTwlCmb6TQiLDc7KjAUpoUfem2y0ttn5dZT05WJqtn2F6pNre_Ph5KrLQIH0rkLlXblwuNH7P-gpj0B4Gzchwsqw3OLvndNAalFY-PpjYrc07F2800hGQJ1zZjMIWs_hNCXstcuKJRcYqST6fgQIVE1gAuALQtfax3GslfIMmlSqADU1xUfA1Zy19eAmgmsSlYJ9fQxcTASpN6u_mGazcv_wqVGvW_XVWfK6U7wQyez0vas6s"/>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 pt-12 border-t border-outline-variant/20">
            <div class="bg-surface rounded-2xl p-6 border border-outline-variant/30 hover:shadow-lg transition-all duration-300 group">
                <span class="icon-line text-primary text-[32px] mb-4 group-hover:scale-110 transition-transform">precision_manufacturing</span>
                <h4 class="font-headline-md text-base font-semibold mb-2">Modern Equipment</h4>
                <p class="text-sm text-on-surface-variant">State-of-the-art diagnostic tools.</p>
            </div>
            <div class="bg-surface rounded-2xl p-6 border border-outline-variant/30 hover:shadow-lg transition-all duration-300 group">
                <span class="icon-line text-primary text-[32px] mb-4 group-hover:scale-110 transition-transform">school</span>
                <h4 class="font-headline-md text-base font-semibold mb-2">Certified Expert</h4>
                <p class="text-sm text-on-surface-variant">Highly qualified lead practitioner.</p>
            </div>
            <div class="bg-surface rounded-2xl p-6 border border-outline-variant/30 hover:shadow-lg transition-all duration-300 group">
                <span class="icon-line text-primary text-[32px] mb-4 group-hover:scale-110 transition-transform">folder_managed</span>
                <h4 class="font-headline-md text-base font-semibold mb-2">Digital Records</h4>
                <p class="text-sm text-on-surface-variant">Secure, easy-access dental history.</p>
            </div>
            <div class="bg-surface rounded-2xl p-6 border border-outline-variant/30 hover:shadow-lg transition-all duration-300 group">
                <span class="icon-line text-primary text-[32px] mb-4 group-hover:scale-110 transition-transform">handshake</span>
                <h4 class="font-headline-md text-base font-semibold mb-2">Personalized Care</h4>
                <p class="text-sm text-on-surface-variant">Treatments tailored just for you.</p>
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12" /></div>

<?php
// Pre-process categories and setup data for premium showcase
$categories = [];
$processedServices = [];

// Fallback category mapping to ensure all services have a category
$categoryMap = [
    'checkup' => 'General', 'cleaning' => 'General',
    'whitening' => 'Cosmetic', 'veneers' => 'Cosmetic',
    'implants' => 'Restorative', 'crowns' => 'Restorative',
    'braces' => 'Orthodontics', 'invisalign' => 'Orthodontics',
    'extraction' => 'Emergency', 'root-canal' => 'Emergency'
];

foreach($canonicalServices as $key => $serviceData) {
    $meta = getServiceDisplayMeta($key) ?? ['icon' => 'medical_services', 'desc' => '', 'featured' => false];

    // Inject category if missing
    if (!isset($meta['category'])) {
        $meta['category'] = $categoryMap[$key] ?? 'General';
    }
    if (!in_array($meta['category'], $categories)) {
        $categories[] = $meta['category'];
    }

    $processedServices[$key] = [
        'data' => $serviceData,
        'meta' => $meta
    ];
}
sort($categories);
?>

<!-- Premium Services Section (Apple-Inspired Cinematic Layout) -->
<section class="py-xl fade-in-up scroll-mt-24 premium-services-section" id="services">
    <!-- Dynamic Background Blobs -->
    <div class="premium-blob-1"></div>
    <div class="premium-blob-2"></div>
    
    <!-- Large Background Number -->
    <div id="services-large-num" class="premium-large-num">01</div>
    
    <!-- Subtle Decorative Outline Element -->
    <span class="icon-line premium-deco-icon fill-icon">health_and_safety</span>

    <div class="relative z-10 max-w-[1400px] mx-auto px-margin-mobile md:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-12 md:mb-16 relative z-20">
            <span class="section-kicker">Our Treatments</span>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">Comprehensive Dental Solutions</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Tailored treatments utilizing the latest in dental technology to ensure optimal outcomes and minimal discomfort.</p>
        </div>

        <!-- Carousel Track Wrapper -->
        <div class="relative w-full overflow-hidden" id="premium-services-wrapper">
            <div class="premium-carousel-track" id="services-grid">
                <?php 
                $serviceIndex = 1;
                foreach($processedServices as $key => $item): 
                    $serviceData = $item['data'];
                    $meta = $item['meta'];
                    $category = $meta['category'];
                ?>
                <div class="premium-card service-card group" data-category="<?= htmlspecialchars($category) ?>">
                    <?php $isReversed = ($serviceIndex % 2 === 0); ?>
                    <div class="flex flex-col lg:flex-row <?= $isReversed ? 'lg:flex-row-reverse' : '' ?> gap-8 md:gap-10 h-full">
                        
                        <!-- Featured Cinematic Image -->
                        <div class="w-full lg:w-2/5 relative rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.05)] h-64 lg:h-auto premium-img-wrap cursor-pointer group-hover:-translate-y-1 transition-transform duration-500">
                            <img src="<?= htmlspecialchars($base_url) ?>/<?= htmlspecialchars($meta['image']) ?>" alt="<?= htmlspecialchars($serviceData['label']) ?>" class="absolute inset-0 w-full h-full object-cover premium-img" />
                            <div class="absolute bottom-4 <?= $isReversed ? 'right-4' : 'left-4' ?> glass-panel rounded-xl p-3 flex items-center gap-2">
                                <span class="icon-line text-primary">schedule</span>
                                <span class="text-sm font-semibold text-on-background"><?= htmlspecialchars($serviceData['duration']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Sequential Animated Content -->
                        <div class="w-full lg:w-3/5 flex flex-col justify-center premium-text-wrap p-lg md:p-12">
                            <span class="anim-1 px-4 py-1.5 bg-primary/10 text-primary rounded-full font-mono text-xs font-bold uppercase tracking-widest mb-4 border border-primary/20 shadow-sm backdrop-blur-md w-fit">
                                <?= htmlspecialchars($category) ?>
                            </span>
                            
                            <h3 class="anim-2 font-headline-xl text-3xl md:text-5xl text-on-background mb-4 font-bold tracking-tight">
                                <?= htmlspecialchars($serviceData['label']) ?>
                            </h3>
                            
                            <p class="anim-3 font-body-md text-on-surface-variant text-base md:text-lg max-w-2xl leading-relaxed mb-8">
                                <?= htmlspecialchars($meta['desc']) ?>
                            </p>
                            
                            <a href="<?= htmlspecialchars($base_url) ?>/services/<?= urlencode($key) ?>.php" class="anim-4 premium-cta-btn inline-flex items-center justify-center gap-3 px-8 py-4 rounded-full bg-primary text-white font-label-md text-base font-semibold shadow-[0_8px_20px_rgba(0,71,141,0.2)] w-fit">
                                Explore Treatment 
                                <span class="icon-line text-[20px] cta-icon transition-transform duration-300">arrow_forward</span>
                            </a>
                        </div>
                        
                    </div>
                </div>
                <?php 
                $serviceIndex++;
                endforeach; ?>
            </div>
        </div>

        <!-- Progress Navigation & Controls -->
        <div class="flex items-center justify-between max-w-2xl mx-auto mt-12 md:mt-20 px-4 relative z-20">
            <button type="button" id="services-prev" class="w-12 h-12 rounded-full border border-outline-variant/40 flex items-center justify-center text-on-background hover:bg-surface-container-lowest hover:shadow-md hover:border-primary transition-all duration-300 active:scale-95 group bg-surface/50 backdrop-blur-sm" aria-label="Previous service">
                <span class="icon-line group-hover:-translate-x-1 transition-transform">arrow_back</span>
            </button>
            
            <div class="flex items-center gap-4 text-sm font-bold text-primary tracking-widest font-label-md">
                <span id="services-prog-current" class="w-5 text-right">01</span>
                <div class="h-[3px] bg-outline-variant/30 rounded-full w-32 md:w-64 overflow-hidden relative">
                    <div id="services-prog-fill" class="absolute top-0 left-0 h-full bg-primary transition-all duration-700 ease-[cubic-bezier(.22,.61,.36,1)]" style="width: 25%;"></div>
                </div>
                <span id="services-prog-total" class="w-5 text-left">0<?= count($processedServices) ?></span>
            </div>

            <button type="button" id="services-next" class="w-12 h-12 rounded-full border border-outline-variant/40 flex items-center justify-center text-on-background hover:bg-surface-container-lowest hover:shadow-md hover:border-primary transition-all duration-300 active:scale-95 group bg-surface/50 backdrop-blur-sm" aria-label="Next service">
                <span class="icon-line group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </button>
        </div>
        
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-xl bg-surface-container-low fade-in-up" id="why-choose-us">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] gap-xl items-start">
            <div class="why-choose-sticky-heading text-center lg:text-left mb-4 lg:mb-0">
                <span class="section-kicker">Clinic Advantages</span>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">The DentalCare Pro Difference</h2>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto lg:mx-0">We combine cutting-edge technology with a patient-first approach to redefine your dental experience.</p>
            </div>

        <div class="flex flex-col divide-y divide-outline-variant/20" id="why-choose-grid">
            <div class="flex items-start gap-5 py-6 md:py-7 group">
                <span class="font-mono text-xs text-primary/50 pt-1.5 w-6 flex-shrink-0">01</span>
                <span class="icon-line text-primary text-[26px] pt-0.5 flex-shrink-0 group-hover:translate-x-1 transition-transform duration-300">psychology</span>
                <div>
                    <h4 class="font-headline-md text-lg font-semibold text-on-background mb-1">AI-Assisted Diagnosis</h4>
                    <p class="text-sm text-on-surface-variant">Precision insights for accurate and early treatment planning.</p>
                </div>
            </div>
            <div class="flex items-start gap-5 py-6 md:py-7 md:pl-8 group">
                <span class="font-mono text-xs text-primary/50 pt-1.5 w-6 flex-shrink-0">02</span>
                <span class="icon-line text-primary text-[26px] pt-0.5 flex-shrink-0 group-hover:translate-x-1 transition-transform duration-300">spa</span>
                <div>
                    <h4 class="font-headline-md text-lg font-semibold text-on-background mb-1">Comfortable Experience</h4>
                    <p class="text-sm text-on-surface-variant">A spa-like environment designed to eliminate dental anxiety.</p>
                </div>
            </div>
            <div class="flex items-start gap-5 py-6 md:py-7 md:pl-16 group">
                <span class="font-mono text-xs text-primary/50 pt-1.5 w-6 flex-shrink-0">03</span>
                <span class="icon-line text-primary text-[26px] pt-0.5 flex-shrink-0 group-hover:translate-x-1 transition-transform duration-300">event_available</span>
                <div>
                    <h4 class="font-headline-md text-lg font-semibold text-on-background mb-1">Online Appointment</h4>
                    <p class="text-sm text-on-surface-variant">Seamlessly book and manage your visits 24/7 online.</p>
                </div>
            </div>
            <div class="flex items-start gap-5 py-6 md:py-7 md:pl-24 group">
                <span class="font-mono text-xs text-primary/50 pt-1.5 w-6 flex-shrink-0">04</span>
                <span class="icon-line text-primary text-[26px] pt-0.5 flex-shrink-0 group-hover:translate-x-1 transition-transform duration-300">sanitizer</span>
                <div>
                    <h4 class="font-headline-md text-lg font-semibold text-on-background mb-1">Sterilized Environment</h4>
                    <p class="text-sm text-on-surface-variant">Strict adherence to the highest international hygiene standards.</p>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12 opacity-0" /></div>

<!-- Dentist Section -->
<section class="py-xl bg-surface-container-lowest fade-in-up" id="dentists">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="section-kicker">Our Team</span>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">Meet Your Dental Specialist</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Dedicated to providing you with the highest standard of personalized and compassionate dental care.</p>
        </div>
        <div class="flex flex-col lg:flex-row bg-surface-container-lowest rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-outline-variant/20 group relative">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000 pointer-events-none"></div>

            <div class="lg:w-2/5 relative h-96 lg:h-auto overflow-hidden">
                <img class="absolute inset-0 w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-1000" alt="Dr. Maria Santos, Lead Practitioner at DentalCare Pro" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBo_w78N2XULoEbVHqmSIePlmFD1bSVx8i_Rote2fYoyu5p0-5lFMoJmKOR-wq1Jel3bUzyUZFF_GGderejoknzrdxrqX6UNt5RCV225IaGT5t4CcLB6efdfW8jbpk_9-_bMONSju3RQ9YVk3rAq6VsupaIIDIR4--B9rlv9Cw-wdFfIH_DEhFndOTjWwnxIaIFxbuKCV-IROtQmUqfd8yMj6lR3-Vw3R6cHtXCmp9mrDr4zD8EBIaQX8BkXakX-H0u4en4ewDp1X4"/>
                <div class="absolute bottom-4 left-4 right-4 glass-panel rounded-xl p-3 flex items-center justify-center gap-2">
                    <span class="icon-line text-primary">verified</span>
                    <span class="text-sm font-semibold text-on-background">Board Certified</span>
                </div>
            </div>
            <div class="lg:w-3/5 p-lg md:p-12 flex flex-col justify-center relative z-10">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="px-4 py-1.5 bg-primary-container/30 text-primary font-label-sm text-sm rounded-full font-semibold border border-primary/10">Lead Practitioner</span>
                    <span class="flex items-center gap-1 text-xs font-semibold text-on-surface-variant bg-surface px-3 py-1.5 rounded-full border border-outline-variant/20"><span class="icon-line text-[14px]">school</span> DDS, MS</span>
                    <span class="flex items-center gap-1 text-xs font-semibold text-on-surface-variant bg-surface px-3 py-1.5 rounded-full border border-outline-variant/20"><span class="icon-line text-[14px]">military_tech</span> 15+ Years Exp.</span>
                </div>
                
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-2 tracking-tight">Dr. Maria Santos</h2>
                <p class="font-label-md text-label-md text-primary mb-6">Restorative & Cosmetic Dentistry</p>
                
                <div class="relative mb-6 pl-8">
                    <span class="absolute -top-4 left-0 font-display text-7xl leading-none text-primary/20 select-none" aria-hidden="true">&ldquo;</span>
                    <p class="font-display italic text-xl md:text-2xl text-on-background relative z-10 leading-snug">My goal is to give you a smile you're proud to share, in an environment where you feel completely at ease.</p>
                </div>

                <p class="font-body-md text-body-md text-on-surface-variant mb-8 leading-relaxed">
                    Dr. Santos brings over 15 years of dedicated experience in advanced restorative procedures. Her philosophy centers on minimally invasive techniques and patient education, ensuring every individual understands their treatment plan completely. Her gentle approach has made her a favorite among patients with dental anxiety.
                </p>

                <div class="grid grid-cols-2 gap-4 mb-8" id="dentist-values-grid">
                    <div class="flex items-start gap-2">
                        <span class="icon-line text-green-500 text-[20px]">check_circle</span>
                        <span class="text-sm text-on-surface-variant font-medium">Minimally Invasive</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="icon-line text-green-500 text-[20px]">check_circle</span>
                        <span class="text-sm text-on-surface-variant font-medium">Anxiety-Free Care</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="icon-line text-green-500 text-[20px]">check_circle</span>
                        <span class="text-sm text-on-surface-variant font-medium">Patient Education</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="icon-line text-green-500 text-[20px]">check_circle</span>
                        <span class="text-sm text-on-surface-variant font-medium">Advanced Aesthetics</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <a href="<?= htmlspecialchars($base_url) ?>/booking.php" class="w-full sm:w-auto h-12 px-8 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center">Book with Dr. Santos</a>

                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12 opacity-0" /></div>

<!-- Testimonials Section -->
<section class="py-xl bg-surface-container-lowest fade-in-up" id="reviews">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="section-kicker">Patient Stories</span>
            
            <div class="flex flex-col items-center justify-center gap-2 mb-6">
                <div class="flex items-center gap-1">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google" class="w-6 h-6 mr-2"/>
                    <span class="text-xl font-bold dynamic-rating font-mono">4.9</span>
                    <div class="flex gap-0.5 mx-1 font-mono">
                        <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                        <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                        <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                        <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                        <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    </div>
                </div>
                <p class="text-sm text-on-surface-variant">Based on hundreds of satisfied patients</p>
            </div>

            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">What Our Patients Say</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Real experiences from real patients who trusted us with their smiles.</p>
        </div>
        
        <div id="fallback-label-container" class="hidden text-center mb-8">
            <span class="inline-block py-2 px-4 rounded-xl bg-surface-container-high text-primary font-label-sm text-sm font-medium border border-primary/20 shadow-sm">Sample reviews — be the first to share your experience!</span>
        </div>
        
        <div id="testimonials-grid" class="grid grid-cols-1 md:grid-cols-3 gap-gutter"></div>

        <div id="fallback-testimonials" class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <div class="testimonial-card bg-surface rounded-3xl p-8 border border-outline-variant/30 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col gap-4 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-full -z-0 group-hover:scale-125 transition-transform duration-500"></div>
                <div class="flex gap-1 relative z-10 font-mono">
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant italic flex-grow relative z-10 leading-relaxed">"I've always dreaded the dentist, but Dr. Santos completely changed that. The clinic feels calming, the team is incredibly kind, and my teeth have never looked better. I actually look forward to my cleanings now!"</p>
                <div class="flex items-center gap-3 pt-4 border-t border-outline-variant/20 relative z-10">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">AC</div>
                    <div>
                        <p class="font-label-md text-label-md text-on-surface font-semibold">Andrea Cruz</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1"><span class="icon-line text-[14px] text-green-600">verified</span> Verified Patient · Teeth Whitening</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card bg-primary rounded-3xl p-8 shadow-[0_15px_30px_rgba(0,71,141,0.25)] hover:shadow-[0_20px_40px_rgba(0,71,141,0.3)] hover:-translate-y-1 transition-all duration-300 flex flex-col gap-4 relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-tr-full"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-bl-full"></div>
                <div class="flex gap-1 relative z-10 font-mono">
                    <span class="icon-line text-yellow-300 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-300 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-300 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-300 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-300 text-[20px] fill-icon">star</span>
                </div>
                <p class="font-body-md text-body-md text-on-primary italic flex-grow relative z-10 leading-relaxed">"The orthodontic treatment here is world-class. The team monitored my progress every step of the way and answered all my questions patiently. My alignment is perfect and the whole experience was smooth from start to finish."</p>
                <div class="flex items-center gap-3 pt-4 border-t border-white/20 relative z-10">
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-on-primary font-bold text-sm flex-shrink-0">MR</div>
                    <div>
                        <p class="font-label-md text-label-md text-on-primary font-semibold">Marco Reyes</p>
                        <p class="font-label-sm text-label-sm text-on-primary/80 flex items-center gap-1"><span class="icon-line text-[14px] text-white">verified</span> Verified Patient · Orthodontics</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card bg-surface rounded-3xl p-8 border border-outline-variant/30 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col gap-4 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-full -z-0 group-hover:scale-125 transition-transform duration-500"></div>
                <div class="flex gap-1 relative z-10 font-mono">
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[20px] fill-icon">star</span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant italic flex-grow relative z-10 leading-relaxed">"I needed an emergency extraction and they fit me in the same morning. The procedure was painless and the post-care instructions were clear and thorough. Truly exceptional service when I needed it most."</p>
                <div class="flex items-center gap-3 pt-4 border-t border-outline-variant/20 relative z-10">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">SL</div>
                    <div>
                        <p class="font-label-md text-label-md text-on-surface font-semibold">Sofia Lim</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1"><span class="icon-line text-[14px] text-green-600">verified</span> Verified Patient · Emergency</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-12 flex flex-col items-center justify-center gap-4">
            <a href="<?= htmlspecialchars($base_url) ?>/reviews.php" class="h-12 px-8 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(0,71,141,0.25)] transition-all duration-300 flex items-center justify-center shadow-sm">
                See All Reviews
            </a>
            <a href="<?= htmlspecialchars($base_url) ?>/reviews.php" class="text-primary hover:text-on-primary-fixed-variant hover:underline font-label-sm text-sm font-medium transition-colors">
                Share Your Experience
            </a>
        </div>

        <div class="mt-12 bg-surface-container rounded-3xl p-8 grid grid-cols-1 md:grid-cols-3 gap-8 border border-outline-variant/20 shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-white/40 via-transparent to-transparent pointer-events-none"></div>
            
            <div class="flex flex-col items-center text-center gap-2 relative z-10">
                <span class="font-headline-xl text-headline-xl text-primary font-bold dynamic-rating font-mono">4.9</span>
                <div class="flex gap-1 font-mono">
                    <span class="icon-line text-yellow-500 text-[16px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[16px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[16px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[16px] fill-icon">star</span>
                    <span class="icon-line text-yellow-500 text-[16px] fill-icon">star</span>
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
        
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <div class="bg-surface px-4 py-2 rounded-full border border-outline-variant/20 text-sm font-medium flex items-center gap-2 text-on-surface-variant shadow-sm hover:shadow-md transition-shadow">
                <span class="icon-line text-primary text-[18px]">trophy</span>
                Award Winning Care
            </div>
            <div class="bg-surface px-4 py-2 rounded-full border border-outline-variant/20 text-sm font-medium flex items-center gap-2 text-on-surface-variant shadow-sm hover:shadow-md transition-shadow">
                <span class="icon-line text-primary text-[18px]">favorite</span>
                10,000+ Successful Treatments
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop"><hr class="border-t border-outline-variant/20 my-8 md:my-12 opacity-0" /></div>

<!-- FAQ & Contact Section -->
<section class="py-xl bg-background fade-in-up scroll-mt-24" id="faq">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="section-kicker">FAQ &amp; Contact</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-xl">
            <div>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">Frequently Asked Questions</h2>
                
                <div class="space-y-4">
                    <div class="border border-outline-variant/30 rounded-2xl bg-surface-container-lowest overflow-hidden hover:border-primary/40 shadow-sm transition-all duration-300">
                        <button class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-surface-container-low transition-colors duration-300 group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.faq-toggle-icon').classList.toggle('is-open');">
                            <span class="font-headline-md text-base font-semibold text-on-background group-hover:text-primary transition-colors">Do you accept my insurance?</span>
                            <span class="faq-toggle-icon-wrap text-on-surface-variant bg-surface w-8 h-8 flex items-center justify-center rounded-full flex-shrink-0 group-hover:bg-primary-container group-hover:text-primary transition-colors duration-300"><span class="faq-toggle-icon"></span></span>
                        </button>
                        <div class="hidden px-6 pb-6 pt-1 text-on-surface-variant font-body-md text-body-md leading-relaxed">
                            We accept most major PPO insurance plans. Our front desk team will happily verify your benefits before your appointment to ensure transparent pricing.
                        </div>
                    </div>
                    <div class="border border-outline-variant/30 rounded-2xl bg-surface-container-lowest overflow-hidden hover:border-primary/40 shadow-sm transition-all duration-300">
                        <button class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-surface-container-low transition-colors duration-300 group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.faq-toggle-icon').classList.toggle('is-open');">
                            <span class="font-headline-md text-base font-semibold text-on-background group-hover:text-primary transition-colors">What should I expect during my first visit?</span>
                            <span class="faq-toggle-icon-wrap text-on-surface-variant bg-surface w-8 h-8 flex items-center justify-center rounded-full flex-shrink-0 group-hover:bg-primary-container group-hover:text-primary transition-colors duration-300"><span class="faq-toggle-icon"></span></span>
                        </button>
                        <div class="hidden px-6 pb-6 pt-1 text-on-surface-variant font-body-md text-body-md leading-relaxed">
                            Your initial visit includes a comprehensive exam, 3D digital x-rays, and a consultation with the dentist to discuss your oral health goals and establish a personalized care plan.
                        </div>
                    </div>
                    <div class="border border-outline-variant/30 rounded-2xl bg-surface-container-lowest overflow-hidden hover:border-primary/40 shadow-sm transition-all duration-300">
                        <button class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-surface-container-low transition-colors duration-300 group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.faq-toggle-icon').classList.toggle('is-open');">
                            <span class="font-headline-md text-base font-semibold text-on-background group-hover:text-primary transition-colors">Do you offer emergency dental services?</span>
                            <span class="faq-toggle-icon-wrap text-on-surface-variant bg-surface w-8 h-8 flex items-center justify-center rounded-full flex-shrink-0 group-hover:bg-primary-container group-hover:text-primary transition-colors duration-300"><span class="faq-toggle-icon"></span></span>
                        </button>
                        <div class="hidden px-6 pb-6 pt-1 text-on-surface-variant font-body-md text-body-md leading-relaxed">
                            Yes, we reserve slots daily for dental emergencies. If you are experiencing severe pain or trauma, please call our clinic immediately for priority scheduling.
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-6 bg-surface-container rounded-2xl border border-primary/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h4 class="font-semibold text-on-background mb-1">Still have questions?</h4>
                        <p class="text-sm text-on-surface-variant">Our AI assistant or team can help.</p>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <a href="<?= htmlspecialchars($base_url) ?>/auth/login.php?mode=register" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-primary-container text-on-primary-container rounded-lg text-sm font-medium hover:bg-primary hover:text-white transition-colors">
                            <span class="icon-line text-[18px]">smart_toy</span> Chat AI
                        </a>
                        <a href="#contact" class="flex-1 sm:flex-none flex items-center justify-center px-4 py-2 bg-white border border-outline-variant/30 text-on-surface rounded-lg text-sm font-medium hover:bg-surface transition-colors">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>

            <div id="contact" style="transition-delay: 0.2s;">
                <div class="bg-surface-container-lowest rounded-3xl p-8 lg:p-10 border border-outline-variant/20 h-full flex flex-col shadow-[0_10px_40px_rgba(0,0,0,0.04)] hover:shadow-[0_10px_40px_rgba(0,0,0,0.08)] transition-shadow duration-300 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-primary/5 to-transparent pointer-events-none"></div>

                    <h3 class="font-headline-lg text-2xl text-on-background mb-8 tracking-tight">Visit Our Clinic</h3>
                    
                    <div class="space-y-8 relative z-10 flex-grow">
                        <div class="flex items-start gap-5 group">
                            <div class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center flex-shrink-0 group-hover:bg-primary group-hover:shadow-lg group-hover:-translate-y-1 transition-all duration-300">
                                <span class="icon-line text-primary group-hover:text-white text-[28px] transition-colors">location_on</span>
                            </div>
                            <div>
                                <h4 class="font-label-md text-base text-on-surface font-semibold mb-1">Location</h4>
                                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">742 Willowbrook Lane, Suite 200</p>
                                <p class="text-xs text-on-surface-variant/80 mt-1 flex items-center gap-1"><span class="icon-line text-[14px]">local_parking</span> Free parking available at the rear.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-5 group">
                            <div class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center flex-shrink-0 group-hover:bg-primary group-hover:shadow-lg group-hover:-translate-y-1 transition-all duration-300">
                                <span class="icon-line text-primary group-hover:text-white text-[28px] transition-colors">schedule</span>
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
                                <span class="icon-line text-primary group-hover:text-white text-[28px] transition-colors">call</span>
                            </div>
                            <div class="w-full">
                                <h4 class="font-label-md text-base text-on-surface font-semibold mb-1">Contact</h4>
                                <p class="font-body-md text-body-md text-on-surface-variant">555-0148</p>
                                <p class="font-body-md text-body-md text-on-surface-variant mb-2">clinic@dentalcarepro.example</p>
                                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-outline-variant/10">
                                    <span class="text-xs font-semibold px-2 py-1 bg-error/10 text-error rounded flex items-center gap-1"><span class="icon-line text-[14px]">emergency</span> 24/7 Emergency</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 h-40 w-full bg-surface-container rounded-2xl overflow-hidden relative group cursor-pointer border border-outline-variant/20">
                        <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Map View" class="w-full h-full object-cover grayscale contrast-125 opacity-90 group-hover:scale-105 transition-all duration-500"/>
                        <div class="absolute inset-0 bg-primary mix-blend-color opacity-70 group-hover:opacity-55 transition-opacity duration-500 pointer-events-none"></div>
                        <div class="absolute inset-0 flex items-center justify-center bg-black/10 group-hover:bg-black/0 transition-colors">
                            <div class="bg-white/90 backdrop-blur px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 transform group-hover:-translate-y-1 transition-transform">
                                <span class="icon-line text-primary">directions</span>
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
            <span class="section-kicker">Digital Health</span>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">Patient Dashboard</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Manage your appointments, view dental records, and track your treatment history — all in one secure place.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-10">
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/30 shadow-sm flex flex-col gap-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center border border-outline-variant/20">
                        <span class="icon-line text-primary fill-icon text-[24px]">calendar_month</span>
                    </div>
                    <h3 class="font-headline-md text-lg text-on-background font-semibold">Upcoming</h3>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">No upcoming appointments.</p>
                <a href="<?= htmlspecialchars($base_url) ?>/booking.php" class="mt-auto h-12 px-4 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-[0_4px_15px_rgba(0,71,141,0.2)] transition-all flex items-center justify-center font-medium">Book Now</a>
            </div>
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/30 shadow-sm flex flex-col gap-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center border border-outline-variant/20">
                        <span class="icon-line text-primary fill-icon text-[24px]">history</span>
                    </div>
                    <h3 class="font-headline-md text-lg text-on-background font-semibold">Past Visits</h3>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">Your visit history will appear here once you've had your first appointment.</p>
                <a href="<?= htmlspecialchars($base_url) ?>/auth/login.php?mode=login" class="mt-auto h-12 px-4 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-[0_4px_15px_rgba(0,71,141,0.2)] transition-all flex items-center justify-center font-medium">Sign In to View</a>
            </div>
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/30 shadow-sm flex flex-col gap-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center border border-outline-variant/20">
                        <span class="icon-line text-primary fill-icon text-[24px]">folder_open</span>
                    </div>
                    <h3 class="font-headline-md text-lg text-on-background font-semibold">Dental Records</h3>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">X-rays, treatment notes, and prescriptions will be stored securely here.</p>
                <a href="<?= htmlspecialchars($base_url) ?>/auth/login.php?mode=login" class="mt-auto h-12 px-4 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-[0_4px_15px_rgba(0,71,141,0.2)] transition-all flex items-center justify-center font-medium">Sign In to View</a>
            </div>
        </div>

        <div class="bg-surface-container rounded-3xl p-8 border border-primary/10 flex flex-col md:flex-row items-center gap-6 text-center md:text-left mb-16 shadow-[0_10px_30px_rgba(0,0,0,0.02)]">
            <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <span class="icon-line text-primary fill-icon text-[32px]">account_circle</span>
            </div>
            <div class="flex-grow">
                <h3 class="font-headline-md text-xl text-on-background mb-1 font-semibold">Sign In to Access Your Dashboard</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">Create an account or log in to manage your full dental profile. <strong>Booked as a guest?</strong> Sign up using the same email address to automatically link your past appointments!</p>
            </div>
            <div class="flex gap-3 flex-shrink-0 w-full md:w-auto">
                <a href="<?= htmlspecialchars($base_url) ?>/auth/login.php?mode=login" class="flex-1 md:flex-none h-12 px-6 rounded-xl border-2 border-primary text-primary font-label-md text-label-md hover:bg-primary/5 transition-colors flex items-center justify-center font-medium">Log In</a>
                <a href="<?= htmlspecialchars($base_url) ?>/auth/login.php?mode=register" class="flex-1 md:flex-none h-12 px-6 rounded-xl bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant hover:shadow-[0_4px_15px_rgba(0,71,141,0.2)] transition-all flex items-center justify-center font-medium">Sign Up</a>
            </div>
        </div>

        <div class="mt-8 relative mx-auto max-w-5xl rounded-[2rem] border border-outline-variant/30 bg-surface-container-lowest p-2 shadow-[0_20px_60px_rgba(0,0,0,0.08)] overflow-hidden">
            <div class="absolute top-4 left-6 flex gap-2 z-20">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
            </div>
            
            <div class="rounded-[1.5rem] bg-background w-full h-[500px] flex overflow-hidden border border-outline-variant/10 relative">
                <div class="w-64 bg-surface-container-lowest border-r border-outline-variant/10 p-6 flex-col gap-6 hidden md:flex pt-14">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-full bg-primary-container text-primary flex items-center justify-center font-bold">JD</div>
                        <div>
                            <p class="font-semibold text-sm">John Doe</p>
                            <p class="text-xs text-on-surface-variant">Patient Dashboard</p>
                        </div>
                    </div>
                    <nav class="space-y-2">
                        <div class="flex items-center gap-3 px-3 py-2 bg-primary/10 text-primary rounded-lg font-medium text-sm"><span class="icon-line text-[20px]">home</span> Overview</div>
                        <div class="flex items-center gap-3 px-3 py-2 text-on-surface-variant hover:bg-surface rounded-lg font-medium text-sm"><span class="icon-line text-[20px]">event</span> Appointments</div>
                        <div class="flex items-center gap-3 px-3 py-2 text-on-surface-variant hover:bg-surface rounded-lg font-medium text-sm"><span class="icon-line text-[20px]">medical_information</span> Records & X-Rays</div>
                        <div class="flex items-center gap-3 px-3 py-2 text-on-surface-variant hover:bg-surface rounded-lg font-medium text-sm"><span class="icon-line text-[20px]">prescriptions</span> Prescriptions</div>
                    </nav>
                </div>
                
                <div class="flex-1 p-6 md:p-10 pt-14 md:pt-10 overflow-y-auto hide-scrollbar bg-background">
                    <h4 class="text-2xl font-bold text-on-background mb-6">Good morning, John</h4>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/20 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-full pointer-events-none"></div>
                            <div class="flex justify-between items-start mb-4">
                                <h5 class="font-semibold flex items-center gap-2"><span class="icon-line text-primary text-[20px]">notification_important</span> Next Appointment</h5>
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-medium">Confirmed</span>
                            </div>
                            <div class="flex gap-4 items-center mb-6">
                                <div class="bg-surface text-center rounded-xl p-3 min-w-[70px] border border-outline-variant/20">
                                    <p class="text-xs text-on-surface-variant font-bold uppercase">Oct</p>
                                    <p class="text-2xl font-bold text-primary">14</p>
                                </div>
                                <div>
                                    <p class="font-bold text-on-background text-lg">Routine Checkup & Cleaning</p>
                                    <p class="text-sm text-on-surface-variant flex items-center gap-1"><span class="icon-line text-[16px]">schedule</span> 10:00 AM - 10:45 AM</p>
                                    <p class="text-sm text-on-surface-variant flex items-center gap-1 mt-1"><span class="icon-line text-[16px]">person</span> Dr. Maria Santos</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <button class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg shadow-sm">Reschedule</button>
                                <button class="px-4 py-2 border border-outline-variant/30 text-on-surface text-sm font-medium rounded-lg">Add to Calendar</button>
                            </div>
                        </div>

                        <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/20 shadow-sm">
                            <h5 class="font-semibold mb-4">Treatment Plan</h5>
                            <div class="space-y-4">
                                <div class="flex gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center"><span class="icon-line text-[14px]">check</span></div>
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
                    
                    <h5 class="font-semibold mb-4">Recent Records</h5>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/20 flex flex-col items-center justify-center text-center gap-2 hover:border-primary/50 cursor-pointer">
                            <span class="icon-line text-primary text-[32px]">radiology</span>
                            <span class="text-xs font-medium">Panoramic X-Ray</span>
                        </div>
                        <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/20 flex flex-col items-center justify-center text-center gap-2 hover:border-primary/50 cursor-pointer">
                            <span class="icon-line text-primary text-[32px]">description</span>
                            <span class="text-xs font-medium">Treatment Plan.pdf</span>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-6 right-6 w-14 h-14 bg-on-background rounded-full shadow-xl flex items-center justify-center cursor-pointer animate-pulse-slow z-30">
                    <span class="icon-line text-white text-[28px]">smart_toy</span>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-error rounded-full border-2 border-background"></span>
                </div>
            </div>

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
                <div class="relative w-full max-w-md mx-auto aspect-[4/5] bg-surface rounded-[2.5rem] border-[8px] border-surface-container-highest shadow-2xl overflow-hidden p-6 flex flex-col gap-4">
                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-outline-variant/10">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                            <span class="icon-line text-white text-[20px]">smart_toy</span>
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
                    
                    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-surface to-transparent"></div>
                </div>
                
                <div class="absolute top-1/4 -right-4 lg:-right-12 glass-panel px-4 py-3 rounded-2xl shadow-lg flex items-center gap-3 animate-float-delayed">
                    <span class="icon-line text-green-500">lock</span>
                    <span class="text-xs font-bold">HIPAA Secure</span>
                </div>
            </div>
            
            <div class="order-1 lg:order-2">
                <span class="section-kicker">Next-Gen Support</span>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-6 tracking-tight">Meet ProCare AI</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mb-8 leading-relaxed">
                    Experience healthcare innovation. Our secure AI assistant is available 24/7 to answer questions, assess minor symptoms, remind you of medications, and help manage your appointments effortlessly.
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                        <span class="icon-line text-primary text-[24px]">troubleshoot</span>
                        <div>
                            <h5 class="font-bold text-sm mb-1">Symptom Checker</h5>
                            <p class="text-xs text-on-surface-variant">Instant preliminary guidance for oral discomfort.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                        <span class="icon-line text-primary text-[24px]">edit_calendar</span>
                        <div>
                            <h5 class="font-bold text-sm mb-1">Smart Scheduling</h5>
                            <p class="text-xs text-on-surface-variant">Find the perfect slot and book in seconds.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                        <span class="icon-line text-primary text-[24px]">tips_and_updates</span>
                        <div>
                            <h5 class="font-bold text-sm mb-1">Dental Care Tips</h5>
                            <p class="text-xs text-on-surface-variant">Personalized hygiene advice post-treatment.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                        <span class="icon-line text-primary text-[24px]">history</span>
                        <div>
                            <h5 class="font-bold text-sm mb-1">Context Aware</h5>
                            <p class="text-xs text-on-surface-variant">Remembers your history for better assistance.</p>
                        </div>
                    </div>
                </div>
                
                <a href="<?= htmlspecialchars($base_url) ?>/auth/login.php?mode=register" class="inline-flex items-center gap-2 h-12 px-8 rounded-xl bg-on-background text-background font-label-md hover:opacity-90 hover:-translate-y-0.5 transition-all shadow-lg">
                    Try AI Assistant
                    <span class="icon-line text-[20px]">chat</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Final Call to Action -->
<section class="relative py-24 overflow-hidden fade-in-up mt-12 mx-margin-mobile md:mx-margin-desktop max-w-[1200px] xl:mx-auto rounded-3xl">
    <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary to-surface-tint z-0"></div>
    <div class="absolute inset-0 z-0 opacity-40 mix-blend-soft-light pointer-events-none" style="background-image: radial-gradient(circle at 15% 20%, rgba(255,255,255,0.5), transparent 40%), radial-gradient(circle at 85% 80%, rgba(255,255,255,0.35), transparent 45%), radial-gradient(circle at 60% 10%, rgba(255,255,255,0.25), transparent 35%);"></div>
    <svg class="absolute inset-0 w-full h-full z-0 opacity-[0.07] pointer-events-none" aria-hidden="true" focusable="false">
        <defs>
            <pattern id="cta-tooth-pattern" width="86" height="86" patternUnits="userSpaceOnUse" patternTransform="rotate(8)">
                <path d="M18 6c-4.5 0-7.5 3.6-7.5 8.1 0 2.7.9 4.5.9 7.2 0 3.6-1.8 5.4-1.8 9 0 2.7 1.8 4.5 3.6 4.5s2.7-2.7 3.6-5.4c.9-2.7 1.8-3.6 2.7-3.6s1.8.9 2.7 3.6c.9 2.7 1.8 5.4 3.6 5.4s3.6-1.8 3.6-4.5c0-3.6-1.8-5.4-1.8-9 0-2.7.9-4.5.9-7.2 0-4.5-3-8.1-7.2-8.1-1.8 0-2.7.9-3.6 1.8-.9-.9-1.8-1.8-3.6-1.8z" fill="none" stroke="white" stroke-width="1.25"/>
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#cta-tooth-pattern)"></rect>
    </svg>
    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-black/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>
    
    <div class="relative z-10 text-center px-6 md:px-12 max-w-3xl mx-auto flex flex-col items-center">
        <h2 class="font-headline-xl text-3xl md:text-5xl text-white mb-6 font-bold tracking-tight">Ready for a healthier smile?</h2>
        <p class="text-primary-container text-lg md:text-xl mb-10 max-w-2xl text-white/90">
            Join thousands of satisfied patients. Book your appointment today and experience the future of modern dental care.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <a href="<?= htmlspecialchars($base_url) ?>/booking.php" class="h-14 px-8 rounded-xl bg-white text-primary font-bold text-base hover:shadow-[0_0_30px_rgba(255,255,255,0.4)] hover:-translate-y-1 transition-all duration-300 flex items-center justify-center w-full sm:w-auto">
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
            <div class="flex flex-col gap-4">
                <span class="font-headline-md text-2xl font-bold text-primary flex items-center gap-2">
                    <span class="icon-line text-[32px]">dentistry</span> DentalCare Pro
                </span>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-xs mt-2">
                    Exceptional, precision-driven dental care in a state-of-the-art, relaxing environment.
                </p>
                <div class="flex gap-4 mt-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant/30 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-colors">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant/30 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-colors">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.163-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant/30 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-colors">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                    </a>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <h4 class="font-bold text-on-background">Quick Navigation</h4>
                <nav class="flex flex-col gap-2">
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#home">Home</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#about">About Us</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#services">Our Services</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#dentists">Meet the Doctor</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all font-label-sm text-sm inline-block w-fit" href="#reviews">Patient Stories</a>
                </nav>
            </div>

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

            <div class="flex flex-col gap-4">
                <h4 class="font-bold text-on-background">Get in Touch</h4>
                <div class="flex flex-col gap-3">
                    <p class="text-sm text-on-surface-variant flex items-start gap-2">
                        <span class="icon-line text-[18px] text-primary">location_on</span>
                        742 Willowbrook Lane, Suite 200
                    </p>
                    <p class="text-sm text-on-surface-variant flex items-center gap-2">
                        <span class="icon-line text-[18px] text-primary">call</span>
                        555-0148
                    </p>
                    <p class="text-sm text-on-surface-variant flex items-center gap-2">
                        <span class="icon-line text-[18px] text-primary">mail</span>
                        clinic@dentalcarepro.example
                    </p>
                    <div class="mt-2 p-3 bg-error-container/50 rounded-lg border border-error/20 inline-flex items-center gap-2 w-fit">
                        <span class="icon-line text-error text-[18px]">emergency</span>
                        <span class="text-xs font-bold text-error">24/7 Emergency Dental Care</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-outline-variant/20 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <p class="font-body-md text-sm text-on-surface-variant">© 2026 DentalCare Pro Clinic. All rights reserved.</p>
            <p class="font-body-md text-sm text-on-surface-variant flex items-center gap-1">Designed with <span class="icon-line text-[14px] text-error fill-icon">favorite</span> for exceptional care</p>
        </div>
    </div>
</footer>

<script>
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    document.addEventListener('DOMContentLoaded', () => {
        // --- Hero Photo Carousel ---
        const heroTrack = document.getElementById('hero-carousel-track');
        const heroSlideEls = heroTrack ? Array.from(heroTrack.querySelectorAll('.hero-slide')) : [];
        const heroDots = Array.from(document.querySelectorAll('#hero-carousel-dots .hero-dot'));
        const heroPrevBtn = document.getElementById('hero-prev');
        const heroNextBtn = document.getElementById('hero-next');
        const heroTextBlock = document.getElementById('hero-text-block');
        const heroEyebrow = document.getElementById('hero-eyebrow');
        const heroHeadline = document.getElementById('hero-headline');
        const heroSubtext = document.getElementById('hero-subtext');
        const heroTrustTagsWrap = document.getElementById('hero-trust-tags');
        const heroTrustTag1 = heroTrustTagsWrap ? heroTrustTagsWrap.querySelector('[data-trust-tag="1"]') : null;
        const heroTrustTag2 = heroTrustTagsWrap ? heroTrustTagsWrap.querySelector('[data-trust-tag="2"]') : null;
        const heroBadge1 = document.getElementById('hero-badge-1');
        const heroBadge2 = document.getElementById('hero-badge-2');

        const HERO_SLIDE_DURATION = 5500;
        const HERO_TEXT_DELAY = 180;
        const HERO_FADE_OUT_MS = 260;

        const heroContent = [
            {
                eyebrow: 'Premium Care',
                headlineHTML: 'Exceptional Dental Care for a <span class="text-accent">Brighter Smile.</span>',
                subtext: 'Welcome to DentalCare Pro, where precision meets comfort. Experience world-class dental treatments in a state-of-the-art, relaxing environment.',
                trustTags: ['Verified Professionals', 'Warm, Comfortable Care'],
                badge1: { icon: 'event_available', title: 'Easy Online Booking' },
                badge2: { icon: 'groups', title: '2,400+ Happy Patients' }
            },
            {
                eyebrow: 'Modern Facility',
                headlineHTML: 'Advanced Technology, <span class="text-accent">Gentle Hands.</span>',
                subtext: 'Relax in a treatment room equipped with the latest dental technology, thoughtfully designed around your comfort.',
                trustTags: ['Modern Equipment', 'HIPAA Secure Care'],
                badge1: { icon: 'health_and_safety', title: 'HIPAA Secure Care' },
                badge2: { icon: 'precision_manufacturing', title: 'Modern Equipment' }
            },
            {
                eyebrow: 'Precision Care',
                headlineHTML: 'Every Detail, <span class="text-accent">Done with Precision.</span>',
                subtext: 'Every instrument is meticulously sterilized, and every procedure is guided by years of clinical expertise.',
                trustTags: ['Sterile Instruments', '15+ Years Experience'],
                badge1: { icon: 'verified', title: 'Verified Professionals' },
                badge2: { icon: 'cleaning_services', title: 'Sterile, Precision Care' }
            }
        ];

        const heroSlideCount = heroSlideEls.length || heroContent.length;
        let heroActiveIndex = 0;
        let heroTimerId = null;
        let heroTextTimeoutId = null;
        let isHeroTransitioning = false;

        const setHeroTrackPosition = (index, animate = true) => {
            if (heroTrack) {
                heroTrack.style.transition = animate ? 'transform 0.55s cubic-bezier(0.4, 0, 0.2, 1)' : 'none';
                heroTrack.style.transform = `translateX(-${(index + 1) * 100}%)`;
            }
        };

        const applyHeroBadge = (badgeEl, data) => {
            if (!badgeEl || !data) return;
            const iconEl = badgeEl.querySelector('[data-badge-icon]');
            const titleEl = badgeEl.querySelector('[data-badge-title]');
            const subtitleEl = badgeEl.querySelector('[data-badge-subtitle]');
            if (iconEl) iconEl.textContent = data.icon;
            if (titleEl) titleEl.textContent = data.title;
            if (subtitleEl) subtitleEl.style.display = 'none';
        };

        const swapHeroContent = (index) => {
            const data = heroContent[index % heroContent.length];
            if (heroEyebrow) heroEyebrow.textContent = data.eyebrow;
            if (heroHeadline) heroHeadline.innerHTML = data.headlineHTML;
            if (heroSubtext) heroSubtext.textContent = data.subtext;
            if (heroTrustTag1) heroTrustTag1.textContent = data.trustTags[0];
            if (heroTrustTag2) heroTrustTag2.textContent = data.trustTags[1];
            applyHeroBadge(heroBadge1, data.badge1);
            applyHeroBadge(heroBadge2, data.badge2);
        };

        const crossfadeHeroContent = (index) => {
            const fadeEls = [heroTextBlock, heroTrustTagsWrap, heroBadge1, heroBadge2].filter(Boolean);
            clearTimeout(heroTextTimeoutId);
            heroTextTimeoutId = setTimeout(() => {
                fadeEls.forEach(el => el.classList.add('is-swapping'));
                setTimeout(() => {
                    swapHeroContent(index);
                    fadeEls.forEach(el => el.classList.remove('is-swapping'));
                }, HERO_FADE_OUT_MS);
            }, HERO_TEXT_DELAY);
        };

        const goToHeroSlide = (index, { crossfade = true } = {}) => {
            heroActiveIndex = index;
            setHeroTrackPosition(heroActiveIndex, true);
            isHeroTransitioning = true;
            
            let realIndex = heroActiveIndex;
            if (realIndex >= heroSlideCount) realIndex = 0;
            if (realIndex < 0) realIndex = heroSlideCount - 1;

            heroDots.forEach((dot, i) => dot.classList.toggle('is-active', i === realIndex));
            if (crossfade) {
                crossfadeHeroContent(realIndex);
            }
        };

        const advanceHeroSlide = () => { if (!isHeroTransitioning) goToHeroSlide(heroActiveIndex + 1); };
        const reverseHeroSlide = () => { if (!isHeroTransitioning) goToHeroSlide(heroActiveIndex - 1); };

        const startHeroCarousel = () => {
            if (heroTimerId || heroSlideCount < 2) return;
            heroTimerId = setInterval(advanceHeroSlide, HERO_SLIDE_DURATION);
        };

        const stopHeroCarousel = () => {
            if (heroTimerId) { clearInterval(heroTimerId); heroTimerId = null; }
        };

        const resetHeroTimer = () => {
            stopHeroCarousel();
            startHeroCarousel();
        };

        if (heroTrack && heroSlideEls.length) {
            const firstClone = heroSlideEls[0].cloneNode(true);
            const lastClone = heroSlideEls[heroSlideEls.length - 1].cloneNode(true);
            firstClone.setAttribute('aria-hidden', 'true');
            lastClone.setAttribute('aria-hidden', 'true');
            heroTrack.appendChild(firstClone);
            heroTrack.insertBefore(lastClone, heroSlideEls[0]);

            heroTrack.addEventListener('transitionend', (e) => {
                if (e.target !== heroTrack || e.propertyName !== 'transform') return;
                isHeroTransitioning = false;
                if (heroActiveIndex >= heroSlideCount) {
                    heroActiveIndex = 0;
                    setHeroTrackPosition(heroActiveIndex, false);
                } else if (heroActiveIndex < 0) {
                    heroActiveIndex = heroSlideCount - 1;
                    setHeroTrackPosition(heroActiveIndex, false);
                }
            });

            setHeroTrackPosition(0, false);
            heroDots.forEach((dot, i) => dot.classList.toggle('is-active', i === 0));
            swapHeroContent(0);
            startHeroCarousel();

            if (heroPrevBtn) heroPrevBtn.addEventListener('click', () => { reverseHeroSlide(); resetHeroTimer(); });
            if (heroNextBtn) heroNextBtn.addEventListener('click', () => { advanceHeroSlide(); resetHeroTimer(); });

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) stopHeroCarousel();
                else startHeroCarousel();
            });
        }

        // --- Premium Cinematic Services Carousel (Apple-Inspired) ---
        const servicesSection = document.getElementById('services');
        const carouselTrack = document.getElementById('services-grid');
        const carouselSlideEls = carouselTrack ? Array.from(carouselTrack.querySelectorAll('.premium-card')) : [];
        const carouselPrevBtn = document.getElementById('services-prev');
        const carouselNextBtn = document.getElementById('services-next');
        const progCurrent = document.getElementById('services-prog-current');
        const progFill = document.getElementById('services-prog-fill');
        const progTotal = document.getElementById('services-prog-total');
        const largeNum = document.getElementById('services-large-num');

        // Cinematic Category Background Colors Mapping
        const categoryColors = {
            'General': { bg: '#f8f9ff', blob1: 'rgba(0, 94, 184, 0.12)', blob2: 'rgba(169, 199, 255, 0.15)' },
            'Cosmetic': { bg: '#f2f8fc', blob1: 'rgba(0, 188, 212, 0.1)', blob2: 'rgba(255, 255, 255, 0.4)' },
            'Restorative': { bg: '#e8ecef', blob1: 'rgba(0, 43, 94, 0.1)', blob2: 'rgba(114, 119, 131, 0.15)' },
            'Orthodontics': { bg: '#eefbfb', blob1: 'rgba(0, 150, 136, 0.1)', blob2: 'rgba(101, 242, 181, 0.15)' },
            'Emergency': { bg: '#fff5f2', blob1: 'rgba(233, 30, 99, 0.08)', blob2: 'rgba(255, 152, 0, 0.12)' }
        };

        if (carouselTrack && carouselSlideEls.length > 0) {
            const originalCount = carouselSlideEls.length;
            
            if(progTotal) progTotal.textContent = String(originalCount).padStart(2, '0');

            // Setup clones for seamless infinite cinematic loop while maintaining momentum
            const firstClone = carouselSlideEls[0].cloneNode(true);
            const lastClone = carouselSlideEls[originalCount - 1].cloneNode(true);
            firstClone.setAttribute('aria-hidden', 'true');
            lastClone.setAttribute('aria-hidden', 'true');
            firstClone.classList.add('is-clone');
            lastClone.classList.add('is-clone');
            
            carouselTrack.appendChild(firstClone);
            carouselTrack.insertBefore(lastClone, carouselSlideEls[0]);

            const allSlides = Array.from(carouselTrack.querySelectorAll('.premium-card'));
            let servicesActiveIndex = 1; // Start at first real slide
            let isServicesTransitioning = false;
            let cardWidth = 0;
            let gap = 0;

            const updateDimensions = () => {
                if(allSlides.length === 0) return;
                cardWidth = allSlides[0].offsetWidth;
                gap = parseFloat(window.getComputedStyle(carouselTrack).gap) || 0;
                carouselTrack.style.transition = 'none';
                carouselTrack.style.transform = `translate3d(${-(servicesActiveIndex * (cardWidth + gap))}px, 0, 0)`;
                carouselTrack.offsetHeight; // Force reflow
            };

            const setTrackPosition = (index, animate = true) => {
                carouselTrack.style.transition = animate ? 'transform 0.8s cubic-bezier(.22,.61,.36,1)' : 'none';
                carouselTrack.style.transform = `translate3d(${-(index * (cardWidth + gap))}px, 0, 0)`;
            };

            const updatePremiumUI = (realIndex) => {
                const activeSlide = allSlides[servicesActiveIndex];
                const category = activeSlide.getAttribute('data-category') || 'General';
                
                // Toggle active classes to trigger internal sequential transitions
                allSlides.forEach((slide, i) => {
                    if(i === servicesActiveIndex) {
                        slide.classList.add('is-active');
                    } else {
                        slide.classList.remove('is-active');
                    }
                });

                // Smooth background and blob updates
                if (servicesSection && categoryColors[category]) {
                    const colors = categoryColors[category];
                    servicesSection.style.setProperty('--services-bg', colors.bg);
                    servicesSection.style.setProperty('--blob-1', colors.blob1);
                    servicesSection.style.setProperty('--blob-2', colors.blob2);
                }

                // Smooth Progress Navigation Update
                const displayNum = String(realIndex + 1).padStart(2, '0');
                if(progCurrent) progCurrent.textContent = displayNum;
                if(progFill) {
                    const pct = ((realIndex + 1) / originalCount) * 100;
                    progFill.style.width = `${pct}%`;
                }

                // Premium Floating Number Crossfade
                if(largeNum) {
                    largeNum.classList.add('animating');
                    setTimeout(() => {
                        largeNum.textContent = displayNum;
                        largeNum.classList.remove('animating');
                    }, 400); // Trigger mid-transition
                }
            };

            const goToSlide = (index) => {
                if (isServicesTransitioning) return;
                servicesActiveIndex = index;
                setTrackPosition(servicesActiveIndex, true);
                isServicesTransitioning = true;

                let realIndex = servicesActiveIndex - 1;
                if (realIndex >= originalCount) realIndex = 0;
                if (realIndex < 0) realIndex = originalCount - 1;
                
                updatePremiumUI(realIndex);
            };

            carouselTrack.addEventListener('transitionend', (e) => {
                if (e.target !== carouselTrack) return;
                isServicesTransitioning = false;
                
                // Seamless momentum reset for infinite loop
                let didReset = false;
                if (servicesActiveIndex === originalCount + 1) {
                    servicesActiveIndex = 1;
                    setTrackPosition(servicesActiveIndex, false);
                    didReset = true;
                } else if (servicesActiveIndex === 0) {
                    servicesActiveIndex = originalCount;
                    setTrackPosition(servicesActiveIndex, false);
                    didReset = true;
                }

                // Re-sync the is-active class to the real slide now in view —
                // otherwise the clone keeps the class and the real card's
                // text (title/description/CTA) stays hidden after the snap.
                if (didReset) {
                    allSlides.forEach((slide, i) => {
                        slide.classList.toggle('is-active', i === servicesActiveIndex);
                    });
                }
            });

            window.addEventListener('resize', updateDimensions);
            setTimeout(() => {
                updateDimensions();
                updatePremiumUI(0);
            }, 50);

            if(carouselPrevBtn) carouselPrevBtn.addEventListener('click', () => { if(!isServicesTransitioning) goToSlide(servicesActiveIndex - 1); });
            if(carouselNextBtn) carouselNextBtn.addEventListener('click', () => { if(!isServicesTransitioning) goToSlide(servicesActiveIndex + 1); });

            // Momentum Swiping support
            let isDragging = false;
            let startPos = 0;
            let dragStartTime = 0;
            let isClickPrevented = false;

            carouselTrack.addEventListener('pointerdown', (e) => {
                if (e.pointerType === 'mouse' && e.button !== 0) return;
                if (isServicesTransitioning) return;
                isDragging = true;
                isClickPrevented = false;
                startPos = e.clientX;
                dragStartTime = Date.now();
                carouselTrack.setPointerCapture(e.pointerId);
                carouselTrack.style.transition = 'none';
            });

            carouselTrack.addEventListener('pointermove', (e) => {
                if (!isDragging) return;
                const diff = e.clientX - startPos;
                if (Math.abs(diff) > 5) isClickPrevented = true;
                const baseTranslate = -(servicesActiveIndex * (cardWidth + gap));
                carouselTrack.style.transform = `translate3d(${baseTranslate + diff}px, 0, 0)`;
            });

            const endDrag = (e) => {
                if (!isDragging) return;
                isDragging = false;
                const movedBy = e.clientX - startPos;
                const timeTaken = Date.now() - dragStartTime;
                const speed = Math.abs(movedBy) / timeTaken;
                
                if (Math.abs(movedBy) > cardWidth / 4 || speed > 0.4) {
                    if (movedBy < 0) goToSlide(servicesActiveIndex + 1);
                    else goToSlide(servicesActiveIndex - 1);
                } else {
                    goToSlide(servicesActiveIndex);
                }
            };

            carouselTrack.addEventListener('pointerup', endDrag);
            carouselTrack.addEventListener('pointercancel', endDrag);
            carouselTrack.addEventListener('click', (e) => {
                if (isClickPrevented) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
            
            // Cursor spotlight logic specifically mapped for premium-cta-btn
            const supportsHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            if (supportsHover) {
                document.querySelectorAll('.premium-cta-btn').forEach(btn => {
                    btn.addEventListener('mousemove', (e) => {
                        const rect = btn.getBoundingClientRect();
                        const x = ((e.clientX - rect.left) / rect.width) * 100;
                        const y = ((e.clientY - rect.top) / rect.height) * 100;
                        btn.style.setProperty('--mouse-x', `${x}%`);
                        btn.style.setProperty('--mouse-y', `${y}%`);
                    });
                });
            }
        }

        // --- Cross-Page Hash Scrolling ---
        if (window.location.hash) {
            const targetId = window.location.hash.substring(1);
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                setTimeout(() => {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            }
        }

        // --- Fetch Live Testimonials & Stats ---
        const RING_CIRCUMFERENCE = 276.46;
        const setRingProgress = (ring, fraction) => {
            if (!ring) return;
            const clamped = Math.max(0, Math.min(1, fraction));
            ring.style.strokeDashoffset = String(RING_CIRCUMFERENCE * (1 - clamped));
        };

        const initHomeCounters = () => {
            document.querySelectorAll('#home .counter').forEach(counter => {
                const target = parseFloat(counter.getAttribute('data-target'));
                const isDecimal = counter.getAttribute('data-decimal') === 'true';
                const duration = 1500;
                const startTime = performance.now();
                const ringWrap = counter.closest('.stat-ring-wrap');
                const ring = ringWrap ? ringWrap.querySelector('[data-ring]') : null;
                const ringMode = ring ? ring.getAttribute('data-ring-mode') : null;

                function update(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const value = target * progress;
                    counter.textContent = isDecimal ? value.toFixed(1) : Math.round(value);
                    if (ring) {
                        setRingProgress(ring, ringMode === 'percent' ? (value / 100) : progress);
                    }
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        counter.textContent = isDecimal ? target.toFixed(1) : target;
                        if (ring) setRingProgress(ring, ringMode === 'percent' ? (target / 100) : 1);
                    }
                }
                requestAnimationFrame(update);
            });
        };

        const statsRowEl = document.getElementById('stats-row');
        const statsSectionEl = statsRowEl ? statsRowEl.closest('section') : null;
        let statsCountersStarted = false;
        const startStatsCountersOnce = () => {
            if (statsCountersStarted) return;
            statsCountersStarted = true;
            initHomeCounters();
        };
        if (statsSectionEl) {
            const statsObserver = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        startStatsCountersOnce();
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });
            statsObserver.observe(statsSectionEl);
        } else {
            startStatsCountersOnce();
        }

        fetch('api/feedback/feedback-list.php?sort=recent&limit=3')
            .then(res => res.json())
            .then(data => {
                const avgRating = data.average_rating ? parseFloat(data.average_rating).toFixed(1) : '4.9';
                
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
                            starsHtml += `<span class="icon-line ${activeClass} text-[20px] fill-icon">star</span>`;
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
                                <div class="flex gap-1 relative z-10 items-center font-mono">
                                    ${starsHtml}
                                </div>
                                <p class="font-body-md text-body-md ${textClass} italic flex-grow relative z-10 leading-relaxed">"${review.comment}"</p>
                                <div class="flex items-center gap-3 pt-4 border-t ${isMiddle ? 'border-white/20' : 'border-outline-variant/20'} relative z-10">
                                    <div class="w-12 h-12 rounded-full ${isMiddle ? 'bg-white/20 text-on-primary' : 'bg-primary-container text-primary'} flex items-center justify-center font-bold text-sm flex-shrink-0">${initial}</div>
                                    <div>
                                        <p class="font-label-md text-label-md ${nameClass} font-semibold flex items-center">${patientName} ${badgeHtml}</p>
                                        <p class="font-label-sm text-label-sm ${isMiddle ? 'text-on-primary/80' : 'text-on-surface-variant'} flex items-center gap-1"><span class="icon-line text-[14px] ${verifiedClass}">verified</span> Verified Patient${serviceHtml}</p>
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
                if (statsCountersStarted) {
                    initHomeCounters();
                }
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
                        
                        if (counter.innerText === "0") {
                            updateCount();
                        }
                    });

                    obs.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));

        // --- Staggered card animations for grids/carousels ---
        const STAGGER_STEP_MS = 90;
        const applyStagger = (container) => {
            if (!container) return;
            Array.from(container.children).forEach((child, index) => {
                if (child.dataset.staggered === 'true') return;
                child.dataset.staggered = 'true';
                child.classList.add('fade-in-up');
                const staggerClass = `stagger-${Math.min(index + 1, 8)}`;
                child.classList.add(staggerClass);
                if (index >= 8) {
                    child.style.transitionDelay = `${(index + 1) * STAGGER_STEP_MS}ms`;
                }
                observer.observe(child);
            });
        };

        applyStagger(document.getElementById('why-choose-grid'));
        applyStagger(document.getElementById('dentist-values-grid'));
        applyStagger(document.getElementById('fallback-testimonials'));
        
        const testimonialsGrid = document.getElementById('testimonials-grid');
        if (testimonialsGrid) {
            const testimonialsObserver = new MutationObserver(() => applyStagger(testimonialsGrid));
            testimonialsObserver.observe(testimonialsGrid, { childList: true });
        }

        // --- Subtle hero background parallax drift on scroll ---
        const heroSlideImgs = document.querySelectorAll('.hero-slide img');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (heroSlideImgs.length && !prefersReducedMotion) {
            let parallaxTicking = false;
            const applyHeroParallax = () => {
                const offset = Math.max(-40, Math.min(60, window.scrollY * 0.08));
                heroSlideImgs.forEach(img => {
                    img.style.transform = `scale(1.15) translateY(${offset}px)`;
                });
                parallaxTicking = false;
            };
            window.addEventListener('scroll', () => {
                if (!parallaxTicking) {
                    parallaxTicking = true;
                    requestAnimationFrame(applyHeroParallax);
                }
            }, { passive: true });
            applyHeroParallax();
        }
    });
</script>
</body>
</html>