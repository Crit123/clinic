<?php
session_start();
// Global app config — defines $base_url
require_once __DIR__ . '/config/app.php';
// Require single source of truth for service definitions
require_once __DIR__ . '/api/data/services-data.php';
// Include API helpers to access CSRF token logic
require_once __DIR__ . '/api/helper/_api-helpers.php'; 

// Generate/retrieve the session's CSRF token
$csrfToken = getCsrfToken();

// Check if user is logged in via session
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html class="light scroll-smooth scroll-pt-[80px]" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Book Appointment - DentalCare Pro</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="<?= htmlspecialchars($base_url) ?>/assets/js/theme-config.js"></script>
<link rel="stylesheet" href="<?= htmlspecialchars($base_url) ?>/assets/css/theme-base.css">
<link rel="stylesheet" href="<?= htmlspecialchars($base_url) ?>/assets/css/responsive.css">
<style>
        :root {
            --primary-color: #00478d;
            --surface-container-lowest: #ffffff;
        }

        /* Page Transitions */
        .page-exit {
            animation: pageExit 250ms ease-in forwards;
        }
        @keyframes pageExit {
            from { opacity: 1; transform: translateY(0); }
            to   { opacity: 0; transform: translateY(-12px); }
        }
        .page-enter {
            animation: pageEnter 400ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes pageEnter {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Review Step Staggered Animations */
        .review-animate-header {
            opacity: 0;
            transform: translateY(8px) scale(0.98);
            transition: opacity 300ms ease-out, transform 300ms ease-out;
        }
        .review-animate-header.visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .review-animate-block {
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 300ms ease-out, transform 300ms ease-out;
        }
        .review-animate-block.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Fixed Containing Block Trap: Step animation uses opacity only */
        .step-content {
            display: none;
        }
        .step-content.active {
            display: block;
        }
        
        /* Step Transition Animations */
        .slide-exit-left { animation: slideOutLeft 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .slide-exit-right { animation: slideOutRight 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .slide-enter-right { animation: slideInRight 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .slide-enter-left { animation: slideInLeft 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards; }

        @keyframes slideOutLeft {
            0% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(-40px); display: none; }
        }
        @keyframes slideOutRight {
            0% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(40px); display: none; }
        }
        @keyframes slideInRight {
            0% { opacity: 0; transform: translateX(40px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideInLeft {
            0% { opacity: 0; transform: translateX(-40px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        /* Inner content gets the subtle slide-up effect.
           NOTE: We animate .step-inner-animate (non-sticky children only)
           to avoid breaking position:sticky inside the grid. */
        .step-inner-animate {
            animation: slideUpInner 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes slideUpInner {
            0% { transform: translateY(12px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0.95); opacity: 0.8; }
            100% { transform: scale(1.04); opacity: 1; }
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 0.5rem;
            background-color: var(--surface-container-lowest);
            border: 1px solid rgba(114, 119, 131, 0.3);
            color: #0b1c30;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 71, 141, 0.08);
        }
        
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(114, 119, 131, 0.2);
            border-radius: 10px;
        }
        
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            user-select: none;
            background-color: transparent;
        }
        
        .calendar-day:not(.disabled):not(.empty-day):not(.cursor-not-allowed):hover {
            background-color: #eff6ff;
            transform: translateY(-2.5px);
            box-shadow: 0 6px 12px rgba(0, 71, 141, 0.09);
            cursor: pointer;
        }

        .calendar-day.selected {
            background-color: var(--primary-color) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 6px 16px rgba(0, 71, 141, 0.25) !important;
            animation: scaleIn 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            z-index: 10;
        }

        .calendar-day .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-top: 4px;
            transition: all 0.2s ease;
        }

        /* Tooltips */
        .tooltip-container { position: relative; }
        .tooltip-container .tooltip {
            pointer-events: none;
            opacity: 0;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translate(-50%, 0px) scale(0.95);
            background: #1e293b;
            color: #fff;
            font-size: 10px;
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
            z-index: 50;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-weight: 500;
        }
        .tooltip-container:hover .tooltip {
            opacity: 1;
            transform: translate(-50%, -6px) scale(1);
        }
        @media (max-width: 768px) {
            .tooltip-container:active .tooltip { opacity: 1; transform: translate(-50%, -6px) scale(1); }
        }

        /* Stepper Animations & Enhancements */
        .step-node {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .step-node .node-number {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 1;
            transform: scale(1);
        }
        
        .step-node .node-check {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            transform: scale(0.5) rotate(-20deg);
        }

        .step-node.completed .node-number {
            opacity: 0; 
            transform: scale(0.5); 
        }
        
        .step-node.completed .node-check { 
            opacity: 1; 
            transform: scale(1) rotate(0deg); 
            color: white; 
        }
        
        .pulse-ring { border-color: currentColor; }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 0.4; border-color: currentColor; }
            100% { transform: scale(1.6); opacity: 0; border-color: currentColor; }
        }
        
        /* Node Hover Tooltips */
        .stepper-node-container:hover .node-tooltip {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        /* Error Shake Animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }
        .shake-error {
            animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
        }

        .slot-item { transition: all 0.2s ease-in-out; }
        
        /* Shimmer Animation for Progress Bar */
        .shimmer {
            position: relative;
            overflow: hidden;
        }
        .shimmer::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            transform: translateX(-100%);
            animation: shimmer-slide 0.6s ease-out forwards;
        }
        @keyframes shimmer-slide {
            100% { transform: translateX(100%); }
        }
        
        .fade-scale-in {
            animation: fadeScaleIn 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        @keyframes fadeScaleIn {
            0% { opacity: 0; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        /* Custom modal and focus states */
        .slot-item:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Phone country code + input flex fix */
        .phone-flex-row {
            display: flex;
            gap: 8px;
            align-items: stretch;
        }

        /* Navigation Underline Animation */
        .nav-link {
            position: relative;
            text-decoration: none;
            padding-bottom: 4px;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #00478d;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* Centered Modal specific animations */
        @keyframes fadeScale {
            from { opacity: 0; transform: scale(0.93); }
            to   { opacity: 1; transform: scale(1); }
        }
        .animate-fade-scale { animation: fadeScale 0.2s ease-out forwards; }

        /* Summary Text Swap Animation */
        .summary-value-update {
            transition: opacity 0.15s ease, transform 0.15s ease;
        }
        .summary-value-update.fade-out {
            opacity: 0;
            transform: translateY(-4px);
        }
        .summary-value-update.fade-in {
            opacity: 1;
            transform: translateY(0);
        }

        /* Summary Row Flash Animation */
        @keyframes flashRow {
            0% { background-color: transparent; }
            10% { background-color: rgba(0, 71, 141, 0.05); }
            100% { background-color: transparent; }
        }
        .flash-highlight {
            animation: flashRow 0.6s ease-out;
        }

        /* Ready Badge Bounce */
        @keyframes readyBounce {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-ready {
            animation: readyBounce 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Toast Animation */
        @keyframes slideDownToast {
            from { transform: translate(-50%, -100%); opacity: 0; }
            to { transform: translate(-50%, 0); opacity: 1; }
        }
        @keyframes slideUpToast {
            from { transform: translate(-50%, 0); opacity: 1; }
            to { transform: translate(-50%, -100%); opacity: 0; }
        }
        .toast-enter { animation: slideDownToast 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .toast-exit { animation: slideUpToast 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        /* Service Grid Card Active State */
        .service-card { transition: all 0.2s ease; }
        .service-card.active {
            border-color: var(--primary-color);
            border-width: 2px;
            background-color: rgba(0, 71, 141, 0.05);
        }
        .service-card.active .check-badge { opacity: 1; transform: scale(1); }
        .service-card .check-badge { opacity: 0; transform: scale(0.5); transition: all 0.2s ease; }
    </style>
</head>
<body class="bg-background text-on-background antialiased min-h-screen flex flex-col font-sans">

<?php include 'components/header-component.php'; ?>

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-24 left-1/2 z-[100] w-max max-w-[90vw] pointer-events-none"></div>

<main class="flex-grow pt-[var(--header-offset-mobile)] lg:pt-[var(--header-offset)] pb-24 sm:pb-16 px-4 md:px-8 max-w-[1440px] mx-auto w-full page-enter">
    
    <!-- Centered Status Modal -->
    <div id="status-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center" aria-live="assertive" role="alert">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="modal-backdrop"></div>
        <div id="modal-card" class="relative bg-white rounded-2xl shadow-2xl border border-slate-200
             w-full max-w-md mx-4 p-6 animate-fade-scale">
            <div id="modal-icon-wrap" class="flex justify-center mb-4">
                <span id="modal-icon" class="icon-line text-[48px]">check_circle</span>
            </div>
            <h3 id="modal-title" class="text-lg font-bold text-slate-900 text-center mb-1"></h3>
            <p id="modal-message" class="text-sm text-slate-600 text-center mb-4"></p>
            <div id="modal-alternatives" class="hidden mb-4">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 text-center">
                    Available Today</p>
                <div id="alternatives-list" class="flex flex-wrap gap-2 justify-center"></div>
            </div>
            <div id="modal-next" class="hidden bg-primary/5 border border-primary/20 rounded-xl
                  p-4 mb-4 text-center">
                <p class="text-xs text-slate-500 mb-1">Next available appointment</p>
                <p id="next-available-text" class="font-bold text-slate-800 text-sm mb-3"></p>
                <button id="btn-book-next" class="bg-primary text-white text-xs font-bold px-4 py-2
                        rounded-lg hover:bg-primary/90 transition-all">Book This Instead</button>
            </div>
            <div id="modal-actions" class="flex flex-col sm:flex-row gap-2 justify-center mt-2"></div>
            <p id="modal-note" class="text-xs text-slate-500 text-center mt-4 hidden"></p>
        </div>
    </div>

    <div class="mb-2 text-center max-w-2xl mx-auto mt-2 sm:mt-4">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-900 mb-2">Book Your Clinical Appointment</h1>
        <p class="text-xs sm:text-sm text-slate-600">Complete the clinical details and perform final review validation.</p>
    </div>

    <!-- Enhanced Dynamic Stepper Progress Indicator (Now Sticky) -->
    <div class="sticky top-[80px] z-40 bg-background/95 backdrop-blur-md pb-4 pt-4 sm:pt-6 -mx-4 px-4 md:-mx-8 md:px-8 mb-6 sm:mb-8 transition-all duration-300 border-b border-slate-200/50 shadow-sm" id="stepper-wrapper">
        <div class="max-w-3xl mx-auto w-full transition-all duration-300" id="form-stepper">
            
            <!-- Mobile Compact Stepper -->
            <div class="block sm:hidden bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold mb-2">
                    <span id="mobile-step-title" class="text-slate-700 tracking-wide transition-colors duration-300">Step 1 of 3 &mdash; Patient Info</span>
                    <span id="mobile-step-pct" class="text-slate-500 font-mono">0% complete</span>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full w-full overflow-hidden">
                    <div id="mobile-step-progress" class="h-full bg-blue-300 rounded-full transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)]" style="width: 0%;"></div>
                </div>
            </div>
            
            <!-- Desktop Full Stepper -->
            <div class="hidden sm:flex justify-between items-start relative px-4 sm:px-8 max-w-2xl mx-auto">
                <!-- Connecting Lines Proportional Fill -->
                <div class="absolute top-4 sm:top-5 left-[60px] sm:left-[70px] right-[60px] sm:right-[70px] h-1 flex -z-10 gap-0">
                    <div class="w-1/2 h-full bg-slate-200 rounded-l-full overflow-hidden relative">
                        <div id="line-1-progress" class="h-full bg-blue-300 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] absolute left-0 top-0" style="width: 0%;"></div>
                    </div>
                    <div class="w-1/2 h-full bg-slate-200 rounded-r-full overflow-hidden relative">
                        <div id="line-2-progress" class="h-full bg-blue-300 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] absolute left-0 top-0" style="width: 0%;"></div>
                    </div>
                </div>
                
                <!-- Node 1 -->
                <div class="flex flex-col items-center gap-2 stepper-node-container w-24 sm:w-28 relative" id="stepper-node-1">
                    <div class="step-node active flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 bg-white relative z-10 transition-colors duration-300 shadow-sm border-primary text-primary">
                        <span class="node-number text-sm font-bold absolute">1</span>
                        <span class="node-check icon-line text-[16px] sm:text-[20px] absolute">check</span>
                        <div class="pulse-ring absolute -inset-2 rounded-full border-2 opacity-0 scale-90" style="animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></div>
                    </div>
                    <div class="node-tooltip absolute bottom-[calc(100%+8px)] left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[11px] rounded-lg px-3 py-1.5 opacity-0 pointer-events-none transition-all duration-150 translate-y-1 whitespace-nowrap z-50 font-medium tracking-wide shadow-md">Not started yet</div>
                    <div class="text-center w-full mt-1">
                        <p class="hidden md:block text-[11px] font-bold text-primary uppercase tracking-wider step-title transition-colors duration-300">Patient Info</p>
                        <div class="h-1 bg-slate-100 rounded-full w-full overflow-hidden mt-1.5 mb-1 relative">
                            <div class="node-progress-bar absolute top-0 left-0 h-full bg-blue-300 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)]" style="width: 0%;"></div>
                        </div>
                        <p class="text-[10px] font-mono font-bold text-primary step-pct-text transition-colors duration-300">0%</p>
                    </div>
                </div>
                
                <!-- Node 2 -->
                <div class="flex flex-col items-center gap-2 stepper-node-container w-24 sm:w-28 relative" id="stepper-node-2">
                    <div class="step-node flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 bg-white relative z-10 transition-colors duration-300 shadow-sm border-slate-300 text-slate-400">
                        <span class="node-number text-sm font-bold absolute">2</span>
                        <span class="node-check icon-line text-[16px] sm:text-[20px] absolute">check</span>
                        <div class="pulse-ring absolute -inset-2 rounded-full border-2 opacity-0 scale-90 hidden" style="animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></div>
                    </div>
                    <div class="node-tooltip absolute bottom-[calc(100%+8px)] left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[11px] rounded-lg px-3 py-1.5 opacity-0 pointer-events-none transition-all duration-150 translate-y-1 whitespace-nowrap z-50 font-medium tracking-wide shadow-md">Not started yet</div>
                    <div class="text-center w-full mt-1">
                        <p class="hidden md:block text-[11px] font-bold text-slate-400 uppercase tracking-wider step-title transition-colors duration-300">Schedule</p>
                        <div class="h-1 bg-slate-100 rounded-full w-full overflow-hidden mt-1.5 mb-1 relative">
                            <div class="node-progress-bar absolute top-0 left-0 h-full bg-blue-300 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)]" style="width: 0%;"></div>
                        </div>
                        <p class="text-[10px] font-mono font-bold text-slate-300 step-pct-text transition-colors duration-300">0%</p>
                    </div>
                </div>
                
                <!-- Node 3 -->
                <div class="flex flex-col items-center gap-2 stepper-node-container w-24 sm:w-28 relative" id="stepper-node-3">
                    <div class="step-node flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 bg-white relative z-10 transition-colors duration-300 shadow-sm border-slate-300 text-slate-400">
                        <span class="node-number text-sm font-bold absolute">3</span>
                        <span class="node-check icon-line text-[16px] sm:text-[20px] absolute">check</span>
                        <div class="pulse-ring absolute -inset-2 rounded-full border-2 opacity-0 scale-90 hidden" style="animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></div>
                    </div>
                    <div class="node-tooltip absolute bottom-[calc(100%+8px)] left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[11px] rounded-lg px-3 py-1.5 opacity-0 pointer-events-none transition-all duration-150 translate-y-1 whitespace-nowrap z-50 font-medium tracking-wide shadow-md">Not started yet</div>
                    <div class="text-center w-full mt-1">
                        <p class="hidden md:block text-[11px] font-bold text-slate-400 uppercase tracking-wider step-title transition-colors duration-300">Review</p>
                        <div class="h-1 bg-slate-100 rounded-full w-full overflow-hidden mt-1.5 mb-1 relative">
                            <div class="node-progress-bar absolute top-0 left-0 h-full bg-blue-300 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)]" style="width: 0%;"></div>
                        </div>
                        <p class="text-[10px] font-mono font-bold text-slate-300 step-pct-text transition-colors duration-300">0%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="booking-form" novalidate>
        <!-- STEP 1: Personal Info -->
        <div class="step-content active" id="step-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 md:p-8 max-w-4xl mx-auto overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 pb-4 border-b border-slate-100 gap-2">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="icon-line text-primary text-[22px]">patient_list</span> Personal Information
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Please fill in your details to complete your appointment registration.</p>
                    </div>
                    <span id="pi-status" class="text-xs font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 transition-all duration-150 self-start sm:self-auto">Not Started</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="firstName">First Name <span class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <input class="form-input text-sm pr-10" id="firstName" name="firstName" placeholder="e.g., John" required type="text" autocomplete="given-name"/>
                            <span class="micro-check icon-line text-emerald-500 absolute right-3 text-[18px] opacity-0 scale-75 transition-all duration-200 pointer-events-none">check_circle</span>
                        </div>
                        <span class="error-msg text-red-600 text-xs mt-1.5 hidden flex items-center gap-1"><span class="icon-line text-sm">warning</span> First name is required.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="lastName">Last Name <span class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <input class="form-input text-sm pr-10" id="lastName" name="lastName" placeholder="e.g., Doe" required type="text" autocomplete="family-name"/>
                            <span class="micro-check icon-line text-emerald-500 absolute right-3 text-[18px] opacity-0 scale-75 transition-all duration-200 pointer-events-none">check_circle</span>
                        </div>
                        <span class="error-msg text-red-600 text-xs mt-1.5 hidden flex items-center gap-1"><span class="icon-line text-sm">warning</span> Last name is required.</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="email">Email Address <span class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <input class="form-input text-sm pr-10" id="email" name="email" placeholder="e.g., john.doe@example.com" required type="email" autocomplete="email"/>
                            <div id="email-indicator" class="absolute right-3 flex items-center justify-center transition-all duration-200 pointer-events-none"></div>
                        </div>
                        <span class="error-msg text-red-600 text-xs mt-1.5 hidden flex items-center gap-1"><span class="icon-line text-sm">warning</span> Valid email is required.</span>
                        <div id="email-error-msg" class="text-red-500 text-xs mt-1.5 opacity-0 transition-opacity duration-200 hidden flex items-center gap-1"><span class="icon-line text-[14px]">error</span> Please enter a valid email address</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="phone">Phone Number <span class="text-red-500">*</span></label>
                        <div class="form-input flex items-center pr-10 relative">
                            <span class="flex-shrink-0 flex items-center gap-1.5 text-sm font-medium text-slate-500 select-none pr-2 mr-2 border-r border-slate-200" aria-hidden="false">
                                <span aria-hidden="true">🇺🇸</span> +1
                            </span>
                            <input class="text-sm flex-1 bg-transparent border-0 outline-none p-0 min-w-0" id="phone" name="phone" placeholder="(555) 123-4567" required type="tel" autocomplete="tel-national"/>
                            <span class="micro-check icon-line text-emerald-500 absolute right-3 text-[18px] opacity-0 scale-75 transition-all duration-200 pointer-events-none">check_circle</span>
                        </div>
                        <span class="error-msg text-red-600 text-xs mt-1.5 hidden flex items-center gap-1"><span class="icon-line text-sm">warning</span> Phone number is required.</span>
                    </div>
                </div>

                <!-- Visit notes -->
                <div class="mt-4 mb-5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="notes-step1">
                        Reason for Visit / Notes
                        <span class="normal-case font-normal text-slate-400 text-[10px] ml-1">(optional)</span>
                    </label>
                    <textarea class="form-input text-sm resize-none bg-slate-50 focus:bg-white notes-sync" id="notes-step1" maxlength="200" placeholder="e.g., sensitivity on upper left molar, hasn't had a checkup in 2 years…" rows="3"></textarea>
                    <div class="flex justify-end mt-1"><span class="text-[10px] font-mono font-medium text-slate-400" id="char-count-step1">0/200</span></div>
                </div>

                <!-- Divider -->
                <div class="border-t border-slate-100 my-5"></div>

                <!-- HIPAA trust banner -->
                <div class="flex items-start gap-3 bg-primary/5 border border-primary/15 rounded-xl px-4 py-3 mb-5">
                    <span class="icon-line text-primary text-[20px] flex-shrink-0 mt-0.5">lock</span>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Your information is encrypted and used only to manage your appointment.
                        We are fully <strong class="text-slate-700 font-semibold">HIPAA-compliant</strong> and will never sell your data.
                    </p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col-reverse sm:flex-row justify-between pt-4 border-t border-slate-100 gap-4">
                    <button class="text-slate-600 border border-slate-300 text-sm font-semibold px-6 py-3 rounded-lg hover:bg-slate-50 active:scale-[0.99] transition-all flex items-center justify-center gap-2 w-full sm:w-auto" type="button" onclick="window.history.back()">
                        <span class="icon-line text-base">arrow_back</span> Back
                    </button>
                    <button class="bg-primary text-white text-sm font-semibold px-6 py-3 rounded-lg hover:bg-primary/95 shadow-sm active:scale-[0.99] transition-all flex items-center justify-center gap-2 w-full sm:w-auto relative" type="button" id="btn-goto-step-2">
                        <span id="btn-goto-step-2-text">Continue to Details &amp; Date</span> <span class="icon-line text-base" id="btn-goto-step-2-icon">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 2: Service, Scheduler & Sticky Summary -->
        <div class="step-content" id="step-2">
            <div class="max-w-6xl w-full mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch mb-6">
                
                <!-- Left Input Fields & Calendars -->
                <div class="lg:col-span-7 space-y-6 step-inner-animate">
                    
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 md:p-8">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 pb-4 border-b border-slate-100 gap-2">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="icon-line text-primary text-[22px]">calendar_today</span> Appointment Scheduling
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">Please choose your target clinical treatment plan and preferred schedule.</p>
                            </div>
                            <span id="as-status" class="text-xs font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 transition-all duration-150 self-start sm:self-auto">Not Started</span>
                        </div>
                        
                        <!-- Service Card Grid -->
                        <div class="mb-8" id="service-grid-container">
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-semibold text-slate-700">What brings you in today?</label>
                                <span id="service-required-badge" class="text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-600 border border-red-100 px-2 py-0.5 rounded animate-pulse">Required</span>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <?php
                                foreach ($canonicalServices as $key => $svc):
                                    $icon = $svc['icon'] ?? 'medical_services';
                                ?>
                                <button type="button" class="service-card relative flex items-start p-4 border border-slate-200 rounded-xl bg-white text-left hover:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/30" onclick="selectServiceCard('<?= htmlspecialchars($key, ENT_QUOTES) ?>')">
                                    <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-primary shrink-0 mr-3">
                                        <span class="icon-line text-[24px]"><?= $icon ?></span>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-sm font-bold text-slate-800 leading-tight mb-1"><?= htmlspecialchars($svc['label'], ENT_QUOTES) ?></h4>
                                        <div class="inline-flex items-center text-[11px] font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                                            <span class="icon-line text-[12px] mr-1">schedule</span> <?= htmlspecialchars($svc['duration'], ENT_QUOTES) ?>
                                        </div>
                                    </div>
                                    <div class="check-badge absolute top-3 right-3 text-primary bg-white rounded-full leading-none shadow-sm">
                                        <span class="icon-line text-[20px]">check_circle</span>
                                    </div>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Hidden select for form logic compatibility -->
                            <select class="hidden" id="service" name="service" required>
                                <option disabled selected value="">Select a clinical service</option>
                                <?php foreach ($canonicalServices as $key => $svc): ?>
                                <option value="<?= htmlspecialchars($key, ENT_QUOTES) ?>" data-duration="<?= htmlspecialchars($svc['duration'], ENT_QUOTES) ?>"><?= htmlspecialchars($svc['label'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="error-msg text-red-600 text-xs mt-1.5 hidden flex items-center gap-1" id="service-error"><span class="icon-line text-sm">warning</span> Please select a service.</span>
                        </div>

                        <div class="mb-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Preferred Date & Time <span class="text-red-500">*</span></label>
                            
                            <!-- Legend -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 bg-slate-50/70 p-3 rounded-xl border border-slate-200/60 mb-5">
                                <div class="flex items-center gap-2 px-2 py-1.5 sm:px-2.5 sm:py-2 bg-white rounded-lg border border-emerald-100 shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-emerald-100 shrink-0"></span>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] sm:text-[11px] font-bold text-emerald-800 leading-none">Available</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 px-2 py-1.5 sm:px-2.5 sm:py-2 bg-white rounded-lg border border-amber-100 shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-amber-100 shrink-0"></span>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] sm:text-[11px] font-bold text-amber-800 leading-none">Limited</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 px-2 py-1.5 sm:px-2.5 sm:py-2 bg-white rounded-lg border border-red-100 shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 ring-2 ring-red-100 shrink-0"></span>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] sm:text-[11px] font-bold text-red-800 leading-none">Full</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 px-2 py-1.5 sm:px-2.5 sm:py-2 bg-white rounded-lg border border-slate-200 shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-300 ring-2 ring-slate-100 shrink-0"></span>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] sm:text-[11px] font-bold text-slate-600 leading-none">N/A</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Calendar -->
                            <div class="w-full max-w-xl">
                                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm flex flex-col transition-all duration-300 w-full" id="calendar-container-wrapper">
                                    <div class="flex justify-between items-center p-3 sm:p-4 border-b border-slate-100 bg-slate-50/50">
                                        <button type="button" class="p-1.5 hover:bg-slate-200 rounded-lg transition-colors text-slate-500" id="prev-month">
                                            <span class="icon-line text-lg block">chevron_left</span>
                                        </button>
                                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider" id="calendar-month-year">...</h3>
                                        <button type="button" class="p-1.5 hover:bg-slate-200 rounded-lg transition-colors text-slate-500" id="next-month">
                                            <span class="icon-line text-lg block">chevron_right</span>
                                        </button>
                                    </div>
                                    <div id="next-available-hint" class="hidden px-4 py-2 bg-emerald-50 hover:bg-emerald-100 transition-colors cursor-pointer border-b border-emerald-100 text-[11px] text-emerald-700 font-semibold flex items-center justify-between group">
                                        <div class="flex items-center gap-1.5">
                                            <span class="icon-line text-sm">event_available</span>
                                            <span>Next available: <span id="next-available-date" class="font-bold underline decoration-emerald-300 underline-offset-2">--</span></span>
                                        </div>
                                        <span class="icon-line text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
                                    </div>
                                    <div class="p-3 sm:p-4 flex-grow w-full">
                                        <div class="grid grid-cols-7 gap-1 min-[1440px]:gap-1.5 text-center text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                                            <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                                        </div>
                                        <div id="calendar-grid-container" class="relative overflow-hidden transition-[height] duration-300 w-full px-1.5 -mx-1.5 py-2 -my-2">
                                            <div id="calendar-grid" class="grid grid-cols-7 gap-1 min-[1440px]:gap-1.5 absolute top-2 left-1.5 right-1.5">
                                                <!-- Javascript fills dynamically -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden inputs tracking selection -->
                            <input id="date" name="date" required type="hidden"/>
                            <input id="time" name="time" required type="hidden"/>
                            <input id="notes-hidden" name="notes" type="hidden"/>
                        </div>
                    </div>
                    
                    <!-- Mobile & Tablet Additional Notes Card (Hidden on lg desktop) -->
                    <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-6 block lg:hidden">
                        <div class="flex flex-col gap-1.5 sm:gap-2.5 mb-3 sm:mb-4">
                            <div class="flex items-center gap-2">
                                <span class="icon-line text-slate-400 text-[18px] sm:text-[20px]">description</span>
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">Additional Notes <span class="text-xs text-slate-400 font-medium ml-1">(Optional)</span></h3>
                            </div>
                            <p class="text-xs text-slate-500">Provide medical details, sensitivities, or insurance notes.</p>
                        </div>
                        <div class="flex justify-end items-end mb-1">
                            <span class="text-xs font-mono font-medium text-slate-400" id="char-count-mobile">0/200</span>
                        </div>
                        <textarea class="form-input text-sm resize-none bg-slate-50 focus:bg-white notes-sync" id="notes-mobile" maxlength="200" placeholder="e.g., Sensitive teeth, history of braces..." rows="3"></textarea>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 md:p-8">
                        <div class="flex flex-col-reverse md:flex-row justify-between items-center gap-4">
                            <button class="btn-prev text-slate-600 border border-slate-300 text-sm font-bold px-6 py-3 rounded-xl hover:bg-slate-50 active:scale-[0.99] transition-all flex items-center justify-center gap-2 w-full md:w-auto" type="button" id="btn-backto-step-1">
                                <span class="icon-line text-base">arrow_back</span> Back
                            </button>
                            <button class="bg-primary text-white text-sm font-bold px-8 py-3 rounded-xl shadow-sm hover:bg-primary/95 active:scale-[0.99] transition-all flex items-center justify-center gap-2 w-full md:w-auto" type="button" id="btn-goto-step-3">
                                Review Appointment <span class="icon-line text-base">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STICKY SUMMARY CARD (Desktop lg+ only) -->
                <div class="hidden lg:flex lg:col-span-5 flex-col" id="summary-col">
                    <div id="summary-sticky-card" style="position: sticky; top: 0; z-index: 20; align-self: flex-start;">
                        <div class="space-y-6 h-fit">
                        <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 overflow-hidden transition-all duration-300 flex flex-col">
                            <div class="p-5 bg-slate-50 border-b border-slate-200/60 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="bg-primary/10 text-primary p-2 rounded-lg shrink-0">
                                        <span class="icon-line block text-[18px]">receipt_long</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-slate-900 leading-tight">Appointment Summary</h3>
                                        <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wider" id="mock-ref-number-wrapper">REF: <span id="mock-ref-number">Pending...</span></p>
                                    </div>
                                </div>
                                <div id="clean-status-display" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold shrink-0 transition-all duration-300">
                                </div>
                            </div>
                            
                            <div class="p-6 space-y-5 overflow-y-auto custom-scrollbar" id="summary-card-body" style="max-height: calc(100vh - 320px)">
                                <div class="pt-1">
                                    <div class="flex justify-between items-center mb-1.5">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Required Field Progress</p>
                                        <span class="text-xs font-mono font-bold text-primary summary-value-update" id="live-progress-pct">0%</span>
                                    </div>
                                    <div class="relative h-2 bg-slate-100 rounded-full overflow-hidden mb-2">
                                        <div class="absolute top-0 left-0 h-full bg-primary rounded-full transition-all duration-500 ease-out" id="live-progress-bar-fill" style="width: 0%"></div>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-medium summary-value-update" id="live-progress-count">0 of 7 Required Fields Completed</p>
                                </div>

                                <div class="border-t border-slate-100 pt-4 space-y-4">
                                    <div class="flex items-start justify-between gap-4 py-1 px-2 -mx-2 rounded-lg" id="summary-row-name">
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Patient Name</p>
                                            <p class="text-sm font-bold text-slate-800 mt-0.5 summary-value-update" id="live-name">--</p>
                                        </div>
                                        <span class="icon-line text-slate-300 text-lg shrink-0 mt-1">person</span>
                                    </div>

                                    <div class="flex items-start justify-between gap-4 py-1 px-2 -mx-2 rounded-lg" id="summary-row-service">
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Service</p>
                                            <p class="text-sm font-bold text-slate-800 mt-0.5 summary-value-update" id="live-service">--</p>
                                        </div>
                                        <span class="icon-line text-slate-300 text-lg shrink-0 mt-1">medical_services</span>
                                    </div>

                                    <div class="flex items-start justify-between gap-4 py-1 px-2 -mx-2 rounded-lg" id="summary-row-date">
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Appointment Date</p>
                                            <p class="text-sm font-bold text-slate-800 mt-0.5 summary-value-update" id="live-date">--</p>
                                        </div>
                                        <span class="icon-line text-slate-300 text-lg shrink-0 mt-1">calendar_today</span>
                                    </div>

                                    <div class="flex items-start justify-between gap-4 py-1 px-2 -mx-2 rounded-lg" id="summary-row-time">
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Appointment Time</p>
                                            <p class="text-sm font-bold text-slate-800 mt-0.5 summary-value-update" id="live-time">--</p>
                                        </div>
                                        <span class="icon-line text-slate-300 text-lg shrink-0 mt-1">schedule</span>
                                    </div>

                                    <div class="flex items-start justify-between gap-4 py-1 px-2 -mx-2 rounded-lg" id="summary-row-duration">
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Estimated Duration</p>
                                            <p class="text-sm font-bold text-slate-800 mt-0.5 summary-value-update" id="live-duration">--</p>
                                        </div>
                                        <span class="icon-line text-slate-300 text-lg shrink-0 mt-1">hourglass_empty</span>
                                    </div>
                                </div>
                                
                                <div id="missing-info-section" class="border-t border-slate-100 pt-4 transition-all duration-300 hidden">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-2.5">Missing Information</p>
                                    <div class="space-y-1.5" id="missing-info-list">
                                        <!-- Missing parameters listed here -->
                                    </div>
                                </div>
                                
                                <!-- Desktop Additional Notes placed cleanly in sidebar -->
                                <div class="border-t border-slate-100 pt-5">
                                    <div class="flex justify-between items-end mb-2">
                                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider" for="notes-desktop">Additional Notes <span class="text-slate-400 font-medium normal-case ml-1">(Optional)</span></label>
                                        <span class="text-[10px] font-mono font-medium text-slate-400" id="char-count-desktop">0/200</span>
                                    </div>
                                    <textarea class="form-input text-sm resize-none bg-slate-50 focus:bg-white notes-sync min-[1440px]:rows-4" id="notes-desktop" maxlength="200" placeholder="e.g., Sensitive teeth, history of braces..." rows="3"></textarea>
                                </div>
                            </div><!-- end p-6 body -->
                        </div><!-- end card -->
                        </div><!-- end space-y-6 -->
                    </div><!-- end summary-sticky-card -->
                </div><!-- end summary-col -->
            </div><!-- end grid -->
            </div><!-- end max-w-6xl -->
        </div><!-- end step-2 -->

        <!-- STEP 3: Dedicated Review Appointment Step -->
        <div class="step-content" id="step-3">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 md:p-8 max-w-4xl mx-auto overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 pb-4 border-b border-slate-100 gap-2">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="icon-line text-primary text-[22px]">verified</span> Review Your Appointment
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Please execute final validation of your clinic schedule data below.</p>
                    </div>
                    <span id="rv-status" class="text-xs font-bold px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 border border-amber-100 transition-all duration-150 self-start sm:self-auto">Pending Verification</span>
                </div>

                <!-- Comprehensive Information Summaries -->
                <div class="space-y-4 sm:space-y-6">
                    
                    <!-- Compact Header Summary -->
                    <div class="review-animate-header bg-primary/5 border border-primary/20 rounded-xl p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 sm:w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                <span class="icon-line text-[20px] sm:text-[24px]">calendar_month</span>
                            </div>
                            <div>
                                <p class="text-sm sm:text-base font-bold text-slate-900" id="review-header-title">--</p>
                                <p class="text-xs sm:text-[13px] font-medium text-slate-600 mt-0.5" id="review-header-subtitle">--</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 sm:self-center self-start bg-white border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 shadow-sm shrink-0">
                            <span class="icon-line text-[16px] text-slate-400">schedule</span>
                            <span id="review-header-duration">--</span>
                        </div>
                    </div>

                    <div class="review-animate-block bg-slate-50 p-3 sm:p-4 rounded-xl border border-slate-200/60 flex justify-between items-center">
                        <span class="text-[11px] sm:text-xs text-slate-500 font-semibold uppercase tracking-wider">Reference Security Code</span>
                        <span id="review-ref-display" class="text-xs font-mono font-bold text-primary bg-primary/10 px-2.5 py-1 rounded">Pending...</span>
                    </div>

                    <!-- Patient Information Display block -->
                    <div class="review-animate-block border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="bg-slate-50/70 px-4 py-3 border-b border-slate-200 flex items-center gap-2 text-slate-800 font-bold text-[11px] sm:text-xs uppercase tracking-wider">
                            <span class="icon-line text-sm text-primary">person</span> Patient Information
                        </div>
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Full Name</span>
                                <p class="font-bold text-slate-800 mt-0.5" id="review-name">--</p>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Email Address</span>
                                <p class="font-medium text-slate-700 mt-0.5 break-all" id="review-email">--</p>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Phone Number</span>
                                <p class="font-medium text-slate-700 mt-0.5" id="review-phone">--</p>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Details Display block -->
                    <div class="review-animate-block border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="bg-slate-50/70 px-4 py-3 border-b border-slate-200 flex items-center gap-2 text-slate-800 font-bold text-[11px] sm:text-xs uppercase tracking-wider">
                            <span class="icon-line text-sm text-primary">medical_services</span> Appointment Details
                        </div>
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Clinical Service</span>
                                <p class="font-bold text-slate-800 mt-0.5" id="review-service">--</p>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Date</span>
                                <p class="font-bold text-slate-800 mt-0.5" id="review-date">--</p>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Time</span>
                                <p class="font-bold text-primary mt-0.5" id="review-time">--</p>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Estimated Duration</span>
                                <p class="font-medium text-slate-700 mt-0.5" id="review-duration">--</p>
                            </div>
                        </div>
                        <div class="px-4 pb-4 border-t border-slate-100 pt-3">
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Additional Notes</span>
                            <p class="text-sm text-slate-600 italic leading-relaxed" id="review-notes">No additional notes provided for this session.</p>
                        </div>
                    </div>

                    <!-- Notification Preferences Display block -->
                    <div class="review-animate-block border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="bg-slate-50/70 px-4 py-3 border-b border-slate-200 flex items-center gap-2 text-slate-800 font-bold text-[11px] sm:text-xs uppercase tracking-wider">
                            <span class="icon-line text-sm text-primary">notifications_active</span> Notification Preferences
                        </div>
                        <div class="p-4 flex flex-col gap-4">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input class="mt-0.5 flex-shrink-0 accent-primary w-4 h-4" type="checkbox" id="pref-sms" checked/>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-primary transition-colors">Send me an SMS reminder</p>
                                    <p class="text-xs text-slate-500 mt-0.5">You'll get a reminder 24 hours before your appointment.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input class="mt-0.5 flex-shrink-0 accent-primary w-4 h-4" type="checkbox" id="pref-email" checked/>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-primary transition-colors">Send me a confirmation email</p>
                                    <p class="text-xs text-slate-500 mt-0.5">A booking summary with clinic directions will be sent.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input class="mt-0.5 flex-shrink-0 accent-primary w-4 h-4" type="checkbox" id="pref-marketing"/>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-primary transition-colors">Receive occasional health tips &amp; promotions</p>
                                    <p class="text-xs text-slate-500 mt-0.5">You can unsubscribe at any time.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Secure Submission Trust Notice -->
                    <div class="review-animate-block flex items-start gap-3 p-4 bg-primary/5 rounded-xl border border-primary/10 text-xs text-slate-600 mb-20 sm:mb-0">
                        <span class="icon-line text-primary text-[20px] mt-0.5 shrink-0">shield_with_heart</span>
                        <div>
                            <p class="font-bold text-slate-900">Premium Clinical Data Standards</p>
                            <p class="text-slate-500 mt-0.5 leading-relaxed">By clicking confirm below, your slot will be locked instantly into our medical calendar database and encrypted via HIPAA compliant secure portal safeguards.</p>
                        </div>
                    </div>
                </div>

                <!-- Review Action Interaction Footers -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 sm:gap-4 mt-0 sm:mt-8 pt-0 sm:pt-6 border-t-0 sm:border-t border-slate-100 sm:relative sm:p-0 sm:bg-transparent fixed bottom-0 left-0 right-0 bg-white border-t p-4 z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] sm:shadow-none">
                    <div class="flex flex-row w-full sm:w-auto gap-3">
                        <button class="flex-1 sm:flex-none text-slate-600 border border-slate-300 text-sm font-bold px-4 py-3 rounded-xl hover:bg-slate-50 active:scale-[0.99] transition-all flex items-center justify-center gap-2" type="button" id="review-back-btn">
                            <span class="icon-line text-base">arrow_back</span> <span class="hidden sm:inline">Back</span>
                        </button>
                        <button class="flex-1 sm:flex-none text-primary border border-primary/30 text-sm font-bold px-4 py-3 rounded-xl hover:bg-primary/5 active:scale-[0.99] transition-all flex items-center justify-center gap-2" type="button" id="review-edit-btn">
                            <span class="icon-line text-base">edit</span> <span class="hidden sm:inline">Edit Info</span><span class="inline sm:hidden">Edit</span>
                        </button>
                    </div>
                    <button class="w-full sm:w-auto bg-primary text-white text-sm font-bold px-8 py-3 rounded-xl shadow-md hover:bg-primary/95 active:scale-[0.99] transition-all flex items-center justify-center gap-2" type="button" id="review-confirm-btn">
                        Confirm <span class="hidden sm:inline">Appointment</span> <span class="icon-line text-base">check_circle</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</main>

<!-- Time Slot Modal / Bottom Sheet -->
<div id="time-modal-backdrop" class="fixed inset-0 z-[60] hidden opacity-0 transition-opacity duration-200 flex items-end sm:items-center justify-center touch-none">
    <!-- Overlay background with dark backdrop -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" aria-hidden="true"></div>
    
    <div id="time-modal-container" class="relative max-w-[100%] sm:max-w-lg w-full bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl flex flex-col z-[70] transform transition-transform duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] sm:scale-95 sm:opacity-0 translate-y-full sm:translate-y-0 max-h-[80vh] sm:max-h-[85vh] h-auto" role="dialog" aria-modal="true" aria-labelledby="time-modal-date-title">
        
        <!-- Mobile drag handle -->
        <div id="bottom-sheet-drag-handle" class="sm:hidden w-full pt-3 pb-1 flex justify-center cursor-grab touch-none">
            <div class="w-10 h-1 bg-slate-300 rounded-full shrink-0"></div>
        </div>
        
        <!-- Header -->
        <div class="px-5 sm:px-6 pt-1 sm:pt-6 pb-4 border-b border-slate-100 relative shrink-0">
            <button id="time-modal-close-x" class="absolute top-3 sm:top-5 right-4 sm:right-5 text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary/50 hidden sm:block"><span class="icon-line block">close</span></button>
            <h2 id="time-modal-date-title" class="text-lg sm:text-xl font-bold text-slate-900 mb-1 pr-8">Date</h2>
            <p id="time-modal-hours-subtitle" class="text-xs sm:text-sm text-slate-500 mb-2.5 font-medium">Hours</p>
            <div class="flex items-center justify-between">
                <span id="time-modal-slot-count" class="text-[11px] sm:text-xs text-slate-400 font-semibold tracking-wide uppercase">-- slots available</span>
                <div id="time-modal-status-pill" class="px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-bold tracking-wide uppercase">Status</div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5 sm:p-6 overflow-y-auto custom-scrollbar flex-grow bg-slate-50/30">
            <div id="timeline-container" class="space-y-4"></div>
        </div>

        <!-- Footer -->
        <div class="px-5 sm:px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-white sm:bg-slate-50/50 rounded-b-2xl shrink-0 gap-3 pb-safe">
            <p id="time-modal-selected-text" class="text-xs sm:text-sm font-bold text-slate-400 truncate flex-grow">No time selected</p>
            <div class="flex gap-2 sm:gap-3 shrink-0">
                <button id="time-modal-cancel" class="text-xs sm:text-sm font-bold text-slate-600 px-3 sm:px-4 py-2 hover:bg-slate-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300">Cancel</button>
                <button id="time-modal-confirm" class="text-xs sm:text-sm font-bold text-white bg-primary px-4 sm:px-5 py-2 rounded-lg shadow-sm hover:bg-primary/95 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed transition-all focus:outline-none focus:ring-2 focus:ring-primary/50 ring-offset-1" disabled>Confirm Time</button>
            </div>
        </div>
    </div>
</div>

<footer class="w-full py-10 bg-slate-100 mt-auto px-6 md:px-8 border-t border-slate-200 text-slate-500">
    <div class="max-w-[1440px] mx-auto flex flex-col md:flex-row justify-between items-center gap-6 text-center md:text-left">
        <div>
            <div class="font-bold text-slate-800 text-md tracking-tight flex items-center justify-center md:justify-start gap-2">
                <span class="icon-line text-primary text-[20px]">dentistry</span> DentalCare Pro
            </div>
            <p class="text-xs text-slate-500 mt-1">Providing state-of-the-art dental procedures with dynamic scheduling workflows.</p>
        </div>
        <p class="text-xs text-slate-400 font-medium">© 2026 DentalCare Pro Clinic. All rights reserved.</p>
        <div class="flex flex-wrap justify-center gap-6 text-xs font-semibold">
            <a class="text-slate-500 hover:text-primary hover:underline transition-all" href="#">Privacy Policy</a>
            <a class="text-slate-500 hover:text-primary hover:underline transition-all" href="#">Terms of Service</a>
            <a class="text-slate-500 hover:text-primary hover:underline transition-all" href="#">HIPAA Compliance</a>
        </div>
    </div>
</footer>

<script>
    // Make variables accessible to JS
    const IS_LOGGED_IN = <?= json_encode($isLoggedIn) ?>;
    const CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    const PHONE_COUNTRY_CODE = '+1';

    document.addEventListener('DOMContentLoaded', () => {
        const steps = document.querySelectorAll('.step-content');
        const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
        
        // Tomorrow limit initialization
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];
        document.querySelectorAll('input[type="date"]').forEach(el => el.min = tomorrowStr);
        
        // Navigation Buttons
        const btnGotoStep2 = document.getElementById('btn-goto-step-2');
        const btnBacktoStep1 = document.getElementById('btn-backto-step-1');
        const btnGotoStep3 = document.getElementById('btn-goto-step-3');
        const reviewBackBtn = document.getElementById('review-back-btn');
        const reviewEditBtn = document.getElementById('review-edit-btn');
        const reviewConfirmBtn = document.getElementById('review-confirm-btn');

        // Form Inputs Mapping
        const inputs = {
            firstName: document.getElementById('firstName'),
            lastName: document.getElementById('lastName'),
            email: document.getElementById('email'),
            phone: document.getElementById('phone'),
            service: document.getElementById('service'),
            date: document.getElementById('date'),
            time: document.getElementById('time'),
            notes: document.getElementById('notes-hidden')
        };

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            let bgClass = type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-800 border-slate-700 text-white';
            let icon = type === 'success' ? '<span class="icon-line text-emerald-500 mr-2 text-[20px]">check_circle</span>' : '';
            
            toast.className = `flex items-center px-4 py-3 rounded-xl border shadow-lg font-medium text-sm toast-enter pointer-events-auto ${bgClass}`;
            toast.innerHTML = `${icon}${message}`;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Sync Notes Textareas
        const notesMobile = document.getElementById('notes-mobile');
        const notesDesktop = document.getElementById('notes-desktop');
        const notesStep1 = document.getElementById('notes-step1');
        const charCountMobile = document.getElementById('char-count-mobile');
        const charCountDesktop = document.getElementById('char-count-desktop');
        const charCountStep1 = document.getElementById('char-count-step1');

        function updateNotesSync(e) {
            // Allow passing the event or a direct string value for forced syncs
            const val = (e && e.target !== undefined) ? e.target.value : (e || '');
            inputs.notes.value = val;
            
            if (notesMobile && (!e || e.target !== notesMobile)) notesMobile.value = val;
            if (notesDesktop && (!e || e.target !== notesDesktop)) notesDesktop.value = val;
            if (notesStep1 && (!e || e.target !== notesStep1)) notesStep1.value = val;
            
            const cnt = `${val.length}/200`;
            if (charCountMobile) charCountMobile.textContent = cnt;
            if (charCountDesktop) charCountDesktop.textContent = cnt;
            if (charCountStep1) charCountStep1.textContent = cnt;
        }

        if (notesMobile) notesMobile.addEventListener('input', updateNotesSync);
        if (notesDesktop) notesDesktop.addEventListener('input', updateNotesSync);
        if (notesStep1) notesStep1.addEventListener('input', updateNotesSync);

        // Fail-safe sync right before step transitions and submission
        function forceSyncNotes() {
            let activeVal = '';
            if (currentStep === 1 && notesStep1) {
                activeVal = notesStep1.value;
            } else if (currentStep === 2) {
                // Sniff which textarea is visually accessible/in-use
                if (notesDesktop && notesDesktop.offsetWidth > 0) {
                    activeVal = notesDesktop.value;
                } else if (notesMobile && notesMobile.offsetWidth > 0) {
                    activeVal = notesMobile.value;
                } else {
                    activeVal = inputs.notes.value;
                }
            } else {
                activeVal = inputs.notes.value;
            }
            updateNotesSync(activeVal); // Forces all elements and `inputs.notes` to hold this specific value
        }

        // Service Card Selection Logic
        function selectServiceCard(val) {
            // Update hidden select
            inputs.service.value = val;
            
            // Update UI cards
            document.querySelectorAll('.service-card').forEach(card => {
                card.classList.remove('active');
            });
            const selectedCard = document.querySelector(`.service-card[onclick="selectServiceCard('${val}')"]`);
            if (selectedCard) {
                selectedCard.classList.add('active');
            }
            
            // Remove pulsing badge
            const badge = document.getElementById('service-required-badge');
            if (badge) badge.classList.add('hidden');
            
            const error = document.getElementById('service-error');
            if(error) error.classList.add('hidden');

            saveFormData();
            updateLiveSummary();
            updateFieldBasedProgress();
            calculateProgress(2);
        }
        window.selectServiceCard = selectServiceCard;

        let currentStep = 1;
        let stepPercentages = { 1: 0, 2: 0, 3: 0 };

        const formatDateLocal = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };

        const actualToday = new Date();
        const todayStr = formatDateLocal(actualToday);
        const baseDateObj = new Date(actualToday.getFullYear(), actualToday.getMonth(), actualToday.getDate());
        let currentCalendarYear = baseDateObj.getFullYear();
        let currentCalendarMonth = baseDateObj.getMonth();

        const day1 = new Date(baseDateObj);
        const day2 = new Date(baseDateObj); day2.setDate(day2.getDate() + 1);
        const day3 = new Date(baseDateObj); day3.setDate(day3.getDate() + 2);

        const keyDay1 = formatDateLocal(day1);
        const keyDay2 = formatDateLocal(day2);
        const keyDay3 = formatDateLocal(day3);

        const mockScheduleData = {
            [keyDay1]: {
                hours: "09:00 AM – 12:00 PM",
                period: "Morning Only",
                status: "limited", 
                slots: {
                    "09:00 AM": "past", "09:30 AM": "available", "10:00 AM": "booked", "10:30 AM": "available",
                    "11:00 AM": "booked", "11:30 AM": "available"
                }
            },
            [keyDay2]: {
                hours: "01:00 PM – 05:00 PM",
                period: "Afternoon Only",
                status: "available",
                slots: {
                    "01:00 PM": "available", "01:30 PM": "available", "02:00 PM": "booked", "02:30 PM": "available",
                    "03:00 PM": "available", "03:30 PM": "available", "04:00 PM": "booked", "04:30 PM": "available"
                }
            },
            [keyDay3]: {
                hours: "09:00 AM – 05:00 PM",
                period: "Full Day",
                status: "full",
                slots: {
                    "09:00 AM": "booked", "09:30 AM": "booked", "10:00 AM": "booked", "10:30 AM": "booked",
                    "01:00 PM": "booked", "01:30 PM": "booked", "02:00 PM": "booked", "02:30 PM": "booked"
                }
            },
            "default": {
                hours: "09:00 AM – 05:00 PM",
                period: "Full Day",
                status: "available",
                slots: {
                    "09:00 AM": "available", "09:30 AM": "available", "10:00 AM": "booked", "10:30 AM": "available",
                    "11:00 AM": "available", "11:30 AM": "booked",
                    "01:00 PM": "available", "01:30 PM": "available", "02:00 PM": "booked", "02:30 PM": "available",
                    "03:00 PM": "available", "03:30 PM": "available", "04:00 PM": "booked", "04:30 PM": "available"
                }
            }
        };

        const timelineContainer = document.getElementById('timeline-container');
        const calendarGridContainer = document.getElementById('calendar-grid-container');
        const calendarMonthYear = document.getElementById('calendar-month-year');
        let calendarDirection = '';

        if(calendarMonthYear) calendarMonthYear.textContent = new Date(currentCalendarYear, currentCalendarMonth).toLocaleDateString(undefined, { month: 'long', year: 'numeric' });

        function calculateProgress(stepNumber) {
            let validCount = 0;
            let totalFields = 0;
            let pct = 0;

            if (stepNumber === 1) {
                totalFields = 4;
                if (inputs.firstName.value.trim() !== '') validCount++;
                if (inputs.lastName.value.trim() !== '') validCount++;
                if (inputs.email.value.trim() !== '' && emailRegex.test(inputs.email.value)) validCount++;
                if (inputs.phone.value.trim() !== '') validCount++;
                pct = Math.round((validCount / totalFields) * 100);
            } else if (stepNumber === 2) {
                if (inputs.service.value) pct += 33;
                if (inputs.date.value) pct += 33;
                if (inputs.time.value) pct += 34;
            }

            animateProgressBar(stepNumber, pct);
            updateStepBadge(stepNumber, pct);
        }

        function animateProgressBar(stepNumber, percentage) {
            const nodeContainer = document.getElementById(`stepper-node-${stepNumber}`);
            if (!nodeContainer) return;

            const progressBar = nodeContainer.querySelector('.node-progress-bar');
            const pctText = nodeContainer.querySelector('.step-pct-text');
            const node = nodeContainer.querySelector('.step-node');
            const title = nodeContainer.querySelector('.step-title');
            const tooltip = nodeContainer.querySelector('.node-tooltip');

            if (tooltip) {
                if (percentage === 100) tooltip.textContent = "Step complete — click to review";
                else if (percentage > 0) tooltip.textContent = `${percentage}% complete`;
                else tooltip.textContent = "Not started yet";
            }

            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
                progressBar.className = `node-progress-bar absolute top-0 left-0 h-full transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)]`;
                if (percentage < 50) progressBar.classList.add('bg-blue-300');
                else if (percentage < 100) progressBar.classList.add('bg-primary');
                else progressBar.classList.add('bg-emerald-500');

                const startPct = stepPercentages[stepNumber] || 0;
                if (percentage === 100 && startPct !== 100 && !progressBar.classList.contains('shimmer-played')) {
                    progressBar.classList.add('shimmer', 'shimmer-played');
                    setTimeout(() => progressBar.classList.remove('shimmer'), 600);
                } else if (percentage < 100) {
                    progressBar.classList.remove('shimmer-played');
                }
            }

            const startPct = stepPercentages[stepNumber] || 0;
            const endPct = percentage;
            stepPercentages[stepNumber] = percentage;

            if (startPct !== endPct) {
                let startTimestamp = null;
                const duration = 400; 
                const tick = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const ease = 1 - Math.pow(1 - progress, 3);
                    const current = Math.floor(startPct + (endPct - startPct) * ease);
                    if (pctText) pctText.textContent = `${current}%`;
                    if (progress < 1) window.requestAnimationFrame(tick);
                    else if (pctText) pctText.textContent = `${endPct}%`;
                };
                window.requestAnimationFrame(tick);
            }

            if (percentage === 100) {
                node.classList.add('completed', 'bg-emerald-500', 'border-emerald-500');
                node.classList.remove('active', 'border-primary', 'border-slate-300', 'bg-white', 'text-slate-400', 'text-primary');
                title.classList.add('text-slate-800');
                title.classList.remove('text-slate-400', 'text-primary');
                pctText.classList.add('text-emerald-500');
                pctText.classList.remove('text-slate-300', 'text-primary');
                const ring = node.querySelector('.pulse-ring');
                if (ring) ring.style.display = 'none';
            } else if (currentStep === stepNumber || percentage > 0) { 
                node.classList.remove('completed', 'bg-emerald-500', 'border-emerald-500', 'border-slate-300', 'text-slate-400');
                node.classList.add('active', 'border-primary', 'bg-white', 'text-primary');
                title.classList.add('text-primary');
                title.classList.remove('text-slate-400', 'text-slate-800');
                pctText.classList.add('text-primary');
                pctText.classList.remove('text-slate-300', 'text-emerald-500');
                const ring = node.querySelector('.pulse-ring');
                if (ring) ring.style.display = currentStep === stepNumber ? 'block' : 'none';
            } else { 
                node.classList.remove('completed', 'active', 'border-primary', 'bg-emerald-500', 'text-primary');
                node.classList.add('border-slate-300', 'bg-white', 'text-slate-400');
                title.classList.remove('text-slate-800', 'text-primary');
                title.classList.add('text-slate-400');
                pctText.classList.remove('text-primary', 'text-emerald-500');
                pctText.classList.add('text-slate-300');
                const ring = node.querySelector('.pulse-ring');
                if (ring) ring.style.display = 'none';
            }

            const line1 = document.getElementById('line-1-progress');
            const line2 = document.getElementById('line-2-progress');
            
            if (line1 && stepNumber === 1) {
                line1.style.width = `${percentage}%`;
                line1.className = `h-full absolute top-0 left-0 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${percentage < 50 ? 'bg-blue-300' : percentage < 100 ? 'bg-primary' : 'bg-emerald-500'}`;
            }
            if (line2 && stepNumber === 2) {
                line2.style.width = `${percentage}%`;
                line2.className = `h-full absolute top-0 left-0 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${percentage < 50 ? 'bg-blue-300' : percentage < 100 ? 'bg-primary' : 'bg-emerald-500'}`;
            }

            if (currentStep === stepNumber) {
                const mobileTitleEl = document.getElementById('mobile-step-title');
                const mobilePctEl = document.getElementById('mobile-step-pct');
                const mobileProgressEl = document.getElementById('mobile-step-progress');
                
                let stepName = stepNumber === 1 ? 'Patient Info' : stepNumber === 2 ? 'Schedule' : 'Review';
                if(mobileTitleEl) {
                    mobileTitleEl.innerHTML = `Step ${stepNumber} of 3 &mdash; ${stepName}`;
                }
                if(mobilePctEl) {
                    mobilePctEl.textContent = `${percentage}% complete`;
                }
                
                if(mobileProgressEl) {
                    mobileProgressEl.style.width = `${percentage}%`;
                    mobileProgressEl.className = `h-full rounded-full transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${percentage < 50 ? 'bg-blue-300' : percentage < 100 ? 'bg-primary' : 'bg-emerald-500'}`;
                }
            }
        }

        function updateStepBadge(stepNumber, percentage) {
            let badgeId = '';
            if (stepNumber === 1) badgeId = 'pi-status';
            else if (stepNumber === 2) badgeId = 'as-status';
            else if (stepNumber === 3) badgeId = 'rv-status';

            const badge = document.getElementById(badgeId);
            if (!badge) return;

            badge.style.opacity = '0';
            setTimeout(() => {
                if (percentage === 0) {
                    badge.textContent = 'Not Started';
                    badge.className = 'text-[11px] font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 transition-opacity duration-150 border border-transparent self-start sm:self-auto';
                } else if (percentage > 0 && percentage < 100) {
                    badge.textContent = `In Progress ${percentage}%`;
                    badge.className = 'text-[11px] font-bold px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-100 transition-opacity duration-150 self-start sm:self-auto';
                } else if (percentage === 100) {
                    badge.textContent = '✓ Complete';
                    badge.className = 'text-[11px] font-bold px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 transition-opacity duration-150 self-start sm:self-auto';
                }
                badge.style.opacity = '1';
            }, 150);
        }

        function setProgress(stepNumber, percentage) {
            animateProgressBar(stepNumber, percentage);
            updateStepBadge(stepNumber, percentage);
        }

        function shakeStep(stepNumber) {
            const nodeContainer = document.getElementById(`stepper-node-${stepNumber}`);
            if (nodeContainer) {
                nodeContainer.classList.add('shake-error');
                
                const progressBar = nodeContainer.querySelector('.node-progress-bar');
                let originalColorClass = 'bg-primary';
                if (progressBar) {
                    const match = progressBar.className.match(/bg-[a-z]+-[0-9]+/);
                    if (match) originalColorClass = match[0];
                    
                    progressBar.classList.remove('bg-primary', 'bg-blue-300', 'bg-emerald-500');
                    progressBar.classList.add('bg-red-400');
                }
                
                setTimeout(() => {
                    nodeContainer.classList.remove('shake-error');
                    if (progressBar) {
                        progressBar.classList.remove('bg-red-400');
                        progressBar.classList.add(originalColorClass);
                        calculateProgress(stepNumber);
                    }
                }, 400);
            }
        }

        const step1StandardFields = ['firstName', 'lastName', 'phone'];
        step1StandardFields.forEach(id => {
            const el = inputs[id];
            el.addEventListener('input', () => {
                const check = el.parentElement.querySelector('.micro-check');
                if (check) {
                    if (el.value.trim() !== '') {
                        check.classList.remove('opacity-0', 'scale-75');
                        check.classList.add('opacity-100', 'scale-100');
                    } else {
                        check.classList.add('opacity-0', 'scale-75');
                        check.classList.remove('opacity-100', 'scale-100');
                    }
                }
                calculateProgress(1);
                
                // Real-time update to summary on input (with debounce logic if heavy, but simple is fine here)
                if (currentStep === 1) updateLiveSummaryValue('name');
            });
        });

        let emailHasBeenValid = false;
        inputs.email.addEventListener('input', (e) => {
            const val = e.target.value.trim();
            const indicator = document.getElementById('email-indicator');
            const errorMsg = document.getElementById('email-error-msg');
            const defaultError = e.target.parentElement.nextElementSibling;
            
            if (val === '') {
                indicator.innerHTML = '';
                errorMsg.classList.add('opacity-0');
                setTimeout(() => errorMsg.classList.add('hidden'), 200);
            } else if (emailRegex.test(val)) {
                indicator.innerHTML = '<span class="icon-line text-emerald-500 text-[20px] fade-scale-in">check_circle</span>';
                emailHasBeenValid = true;
                errorMsg.classList.add('opacity-0');
                setTimeout(() => errorMsg.classList.add('hidden'), 200);
                if(defaultError) defaultError.classList.add('hidden'); 
            } else {
                if (emailHasBeenValid) {
                    indicator.innerHTML = '<span class="icon-line text-red-500 text-[20px] fade-scale-in">cancel</span>';
                    errorMsg.classList.remove('hidden');
                    setTimeout(() => errorMsg.classList.remove('opacity-0'), 10);
                } else {
                    indicator.innerHTML = '<span class="text-slate-400 font-bold tracking-widest text-xs mt-1 fade-scale-in">•••</span>';
                }
            }
            calculateProgress(1);
        });

        // Helper function to clear Step 1 contact details
        window.clearContactDetails = function() {
            inputs.email.value = '';
            inputs.phone.value = '';
            const emailIndicator = document.getElementById('email-indicator');
            if (emailIndicator) emailIndicator.innerHTML = '';
            const phoneCheck = inputs.phone.parentElement.querySelector('.micro-check');
            if (phoneCheck) phoneCheck.classList.add('opacity-0', 'scale-75');
            calculateProgress(1);
            updateFieldBasedProgress();
        };

        // Dynamic status modal setup
        function showModal(type, title, message, options = {}) {
            const modal = document.getElementById('status-modal');
            const titleEl = document.getElementById('modal-title');
            const messageEl = document.getElementById('modal-message');
            const iconEl = document.getElementById('modal-icon');
            const altContainer = document.getElementById('modal-alternatives');
            const altList = document.getElementById('alternatives-list');
            const nextContainer = document.getElementById('modal-next');
            const nextText = document.getElementById('next-available-text');
            const actions = document.getElementById('modal-actions');
            const btnBookNext = document.getElementById('btn-book-next');
            const noteEl = document.getElementById('modal-note');

            titleEl.textContent = title;
            messageEl.textContent = message;

            // Reset state
            iconEl.className = 'icon-line text-[48px]';
            altContainer.classList.add('hidden');
            nextContainer.classList.add('hidden');
            actions.innerHTML = '';
            btnBookNext.onclick = null;
            noteEl.classList.add('hidden');
            noteEl.textContent = '';

            if (type === 'success') {
                iconEl.classList.add('text-emerald-500');
                iconEl.textContent = 'check_circle';
                actions.innerHTML = '<button class="bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-primary/90 transition-all cursor-pointer w-full sm:w-auto" onclick="closeModal()">Done</button>';
            } else if (type === 'auth-check') {
                iconEl.classList.add('text-primary');
                iconEl.textContent = 'account_circle';
                
                // Grab the email the user typed so we can prefill the auth page
                const _authEmail = encodeURIComponent(inputs.email.value.trim());
                const _authFirst = encodeURIComponent(inputs.firstName.value.trim());
                const _authLast  = encodeURIComponent(inputs.lastName.value.trim());
                const _regParams = `mode=register&email=${_authEmail}&first_name=${_authFirst}&last_name=${_authLast}`;

                if (options.matchType === 'registered') {
                    // Hard gate — user MUST log in
                    iconEl.textContent = 'lock_person';
                    actions.innerHTML = `
                        <a href="auth/login.php?email=${_authEmail}"
                           class="bg-primary text-white px-6 py-2.5 rounded-lg font-bold hover:bg-primary/90 transition-all text-center w-full sm:w-auto">
                            Log In
                        </a>
                        <a href="auth/login.php?mode=login&forgot=1&email=${_authEmail}"
                           class="text-primary bg-primary/10 border border-primary/20 px-6 py-2.5 rounded-lg font-bold hover:bg-primary/20 transition-all text-center w-full sm:w-auto mt-2 sm:mt-0">
                            Forgot Password?
                        </a>
                    `;
                    // Add a small note so it's clear there's no bypass
                    noteEl.textContent = 'You must log in to make another booking.';
                    noteEl.classList.remove('hidden');
                } else if (options.matchType === 'guest_history_full') {
                    // Hard gate — user MUST register to link past bookings
                    iconEl.textContent = 'lock_person';
                    actions.innerHTML = `
                        <a href="auth/login.php?${_regParams}"
                           class="bg-primary text-white px-6 py-2.5 rounded-lg font-bold hover:bg-primary/90 transition-all text-center w-full sm:w-auto">
                            Create Account
                        </a>
                    `;
                    // Add a small note so it's clear there's no bypass
                    noteEl.textContent = 'You must create an account to make another booking.';
                    noteEl.classList.remove('hidden');
                } else if (options.matchType === 'guest_history_partial') {
                    // Soft identity check — let the user decide
                    iconEl.textContent = 'help_center';
                    actions.innerHTML = `
                        <a href="auth/login.php?${_regParams}"
                           class="bg-primary text-white px-6 py-2.5 rounded-lg font-bold hover:bg-primary/90 transition-all text-center w-full sm:w-auto order-1 sm:order-2">
                            Yes — Log In / Register
                        </a>
                        <button class="text-slate-600 border border-slate-200 hover:bg-slate-50 px-6 py-2.5 rounded-lg font-bold transition-all cursor-pointer w-full sm:w-auto order-2 sm:order-1"
                                onclick="proceedToStep2(); closeModal();">
                            No — Continue as Guest
                        </button>
                    `;
                }
            } else if (type === 'conflict') {
                iconEl.classList.add('text-amber-500');
                iconEl.textContent = 'warning';

                const hasAlternatives = options.alternatives && options.alternatives.length > 0;
                const hasNextAvailable = !!options.next_available;

                if (!hasAlternatives && !hasNextAvailable) {
                    messageEl.textContent = "No slots available in the next 14 days. Please call the clinic directly.";
                    actions.innerHTML = '<button class="bg-slate-500 text-white px-6 py-2 rounded-lg font-bold hover:bg-slate-600 transition-all cursor-pointer w-full sm:w-auto" onclick="closeModal()">Close</button>';
                } else {
                    if (hasAlternatives) {
                        altContainer.classList.remove('hidden');
                        altList.innerHTML = options.alternatives.map(time =>
                            `<button class="bg-slate-100 hover:bg-primary/10 hover:text-primary hover:border-primary/50 text-slate-700 font-medium px-3 py-1.5 rounded-md text-xs border border-slate-200 transition-all cursor-pointer" onclick="selectAlternative('${options.date}', '${time}')">${time}</button>`
                        ).join('');
                    }

                    if (hasNextAvailable) {
                        nextContainer.classList.remove('hidden');
                        const nextD = new Date(options.next_available.date + "T12:00:00");
                        const dateFormatted = nextD.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
                        nextText.textContent = `${dateFormatted} at ${options.next_available.time}`;
                        
                        btnBookNext.textContent = `Book ${dateFormatted} at ${options.next_available.time} instead`;
                        btnBookNext.onclick = () => {
                            selectAlternative(options.next_available.date, options.next_available.time);
                        };
                    }

                    actions.innerHTML = '<button class="text-slate-500 hover:bg-slate-100 px-6 py-2 rounded-lg font-bold transition-all cursor-pointer w-full sm:w-auto" onclick="closeModal()">Cancel</button>';
                }
            } else {
                iconEl.classList.add('text-red-500');
                iconEl.textContent = 'error';
                actions.innerHTML = '<button class="bg-red-50 text-red-600 border border-red-100 px-6 py-2 rounded-lg font-bold hover:bg-red-100 transition-all cursor-pointer w-full sm:w-auto" onclick="closeModal()">Try Again</button>';
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('status-modal').classList.add('hidden');
        }
        window.closeModal = closeModal;

        window.selectAlternative = function(dateStr, timeStr) {
            inputs.date.value = dateStr;
            inputs.time.value = timeStr;
            closeModal();
            updateLiveSummary();
            updateFieldBasedProgress();
            
            if (currentStep === 3) {
                populateReviewStep(); 
            } else {
                const d = new Date(dateStr + "T12:00:00");
                currentCalendarYear = d.getFullYear();
                currentCalendarMonth = d.getMonth();
                calendarDirection = '';
                renderCalendar(currentCalendarYear, currentCalendarMonth);
                calculateProgress(2);
            }
        };

        function updateFieldBasedProgress() {
            const requiredFields = [
                { id: 'firstName', name: 'First Name' },
                { id: 'lastName', name: 'Last Name' },
                { id: 'email', name: 'Email Address' },
                { id: 'phone', name: 'Phone Number' },
                { id: 'service', name: 'Service Selected' },
                { id: 'date', name: 'Appointment Date' },
                { id: 'time', name: 'Appointment Time' }
            ];
            
            let completedCount = 0;
            const missingRequired = [];

            requiredFields.forEach(field => {
                const el = inputs[field.id];
                const isValid = el && el.value.trim() !== '' && 
                               (el.type !== 'email' || emailRegex.test(el.value));
                if (isValid) {
                    completedCount++;
                } else {
                    missingRequired.push(field);
                }
            });

            const percentage = Math.round((completedCount / requiredFields.length) * 100);
            
            const progressBarFill = document.getElementById('live-progress-bar-fill');
            const progressPct = document.getElementById('live-progress-pct');
            const progressCount = document.getElementById('live-progress-count');

            if (progressBarFill) progressBarFill.style.width = `${percentage}%`;
            if (progressPct) animateTextSwap(progressPct, `${percentage}%`);
            if (progressCount) animateTextSwap(progressCount, `${completedCount} of 7 Required Fields Completed`);

            const statusDisplay = document.getElementById('clean-status-display');
            if (statusDisplay) {
                if (completedCount === 7) {
                    if (statusDisplay.innerHTML.indexOf('Ready') === -1) {
                        statusDisplay.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 shrink-0 transition-colors duration-300 animate-ready';
                        statusDisplay.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Ready`;
                        setTimeout(() => statusDisplay.classList.remove('animate-ready'), 400);
                    }
                } else if (completedCount === 0) {
                    statusDisplay.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200 shrink-0 transition-colors duration-300';
                    statusDisplay.innerHTML = `<span class="w-2 h-2 rounded-full bg-slate-400"></span> Not Started`;
                } else {
                    statusDisplay.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-100 shrink-0 transition-colors duration-300';
                    statusDisplay.innerHTML = `<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> ${percentage}% Done`;
                }
            }

            const missingSection = document.getElementById('missing-info-section');
            const missingList = document.getElementById('missing-info-list');
            if (missingSection && missingList) {
                if (missingRequired.length > 0) {
                    missingSection.classList.remove('hidden');
                    missingList.innerHTML = '';
                    missingRequired.forEach(field => {
                        const row = document.createElement('div');
                        row.className = 'flex items-center justify-between text-xs bg-red-50 text-red-700 font-semibold px-3 py-2 rounded-lg border border-red-100/60 transition-all duration-200';
                        row.innerHTML = `
                            <span>⚠ ${field.name} Required</span>
                            <span class="text-[10px] font-bold text-red-500 tracking-wide uppercase">Missing</span>
                        `;
                        missingList.appendChild(row);
                    });
                } else {
                    missingSection.classList.add('hidden');
                }
            }
        }

        let modalPreviousDate = '';
        let modalPreviousTime = '';
        let modalKeydownHandler = null;
        let bottomSheetDragData = { isDragging: false, startY: 0, currentY: 0, startTime: 0 };

        function setupModalKeyboardNav() {
            modalKeydownHandler = (e) => {
                const focusable = Array.from(document.querySelectorAll('#timeline-container .slot-item:not(.cursor-not-allowed)'));
                if(e.key === 'Escape') {
                    e.preventDefault();
                    closeTimeModal(false);
                    return;
                }
                
                if(focusable.length === 0) return;
                let currentIdx = focusable.indexOf(document.activeElement);
                
                if(e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    let next = currentIdx + 1;
                    if(next >= focusable.length) next = 0;
                    focusable[next].focus();
                } else if(e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    let prev = currentIdx - 1;
                    if(prev < 0) prev = focusable.length - 1;
                    focusable[prev].focus();
                } else if(e.key === 'Enter' && currentIdx !== -1) {
                    e.preventDefault();
                    focusable[currentIdx].click();
                }
            };
            document.addEventListener('keydown', modalKeydownHandler);
        }

        function cleanupModalKeyboardNav() {
            if(modalKeydownHandler) {
                document.removeEventListener('keydown', modalKeydownHandler);
                modalKeydownHandler = null;
            }
        }

        function updateModalFooter() {
            const btn = document.getElementById('time-modal-confirm');
            const text = document.getElementById('time-modal-selected-text');
            if(inputs.time.value) {
                btn.disabled = false;
                text.textContent = `Selected: ${inputs.time.value}`;
                text.classList.add('text-primary');
                text.classList.remove('text-slate-400');
            } else {
                btn.disabled = true;
                text.textContent = `No time selected`;
                text.classList.remove('text-primary');
                text.classList.add('text-slate-400');
            }
        }

        function renderSlots(availableSlots) {
            const dateStr = inputs.date.value;
            const preSelectedTime = inputs.time.value;

            const container = document.getElementById('timeline-container');
            container.innerHTML = '';

            const allSlots = ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM'];

            if (preSelectedTime && !availableSlots.includes(preSelectedTime)) {
                inputs.time.value = '';
                updateLiveSummary();
                updateFieldBasedProgress();
                calculateProgress(2);
                updateModalFooter();

                showModal('conflict',
                    'Your Slot Was Just Taken',
                    'Someone booked your selected time. Choose another:',
                    { alternatives: availableSlots, date: dateStr }
                );
            }

            const morningSlots = allSlots.filter(t => t.endsWith('AM'));
            const afternoonSlots = allSlots.filter(t => t.endsWith('PM'));

            function buildSlotButton(time) {
                const isAvailable = availableSlots.includes(time);
                const isSelected = (inputs.time.value === time);
                const item = document.createElement('button');
                item.type = 'button';
                item.setAttribute('tabindex', isAvailable ? '0' : '-1');

                if (isSelected) {
                    item.className = 'slot-item relative bg-primary text-white border-2 border-primary shadow-md cursor-pointer rounded-xl px-4 py-2.5 text-sm font-semibold scale-[1.03] transition-all focus:outline-none focus:ring-2 focus:ring-primary/50 ring-offset-1';
                    item.innerHTML = `<span class="flex items-center gap-1.5"><span class="icon-line text-[14px]">check_circle</span>${time}</span>`;
                    item.onclick = () => { inputs.time.value = time; renderSlots(availableSlots); updateModalFooter(); };
                } else if (isAvailable) {
                    item.className = 'slot-item bg-white border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary/5 hover:shadow-sm cursor-pointer rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 transition-all focus:outline-none focus:ring-2 focus:ring-primary/50 ring-offset-1';
                    item.innerHTML = `<span class="flex items-center gap-1.5"><span class="icon-line text-[14px] text-slate-300">schedule</span>${time}</span>`;
                    item.onclick = () => { inputs.time.value = time; renderSlots(availableSlots); updateModalFooter(); };
                } else {
                    item.className = 'slot-item bg-slate-50 text-slate-300 line-through cursor-not-allowed rounded-xl px-4 py-2.5 text-sm font-medium pointer-events-none border border-slate-100';
                    item.innerHTML = `<span class="flex items-center gap-1.5"><span class="icon-line text-[14px]">block</span>${time}</span>`;
                    item.disabled = true;
                }
                return item;
            }

            function buildSection(label, icon, slots, colorClass) {
                const section = document.createElement('div');
                section.className = 'mb-1';

                const header = document.createElement('div');
                header.className = 'flex items-center gap-2 mb-3';
                header.innerHTML = `
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="icon-line text-[15px] ${colorClass}">${icon}</span>
                        <span class="text-[11px] font-bold uppercase tracking-widest ${colorClass}">${label}</span>
                    </div>
                    <div class="flex-1 h-px bg-slate-200/80"></div>
                    <span class="text-[10px] font-semibold text-slate-400 shrink-0 tabular-nums">
                        ${slots.filter(t => availableSlots.includes(t)).length} open
                    </span>`;
                section.appendChild(header);

                const grid = document.createElement('div');
                grid.className = 'flex flex-wrap gap-2';
                slots.forEach(time => grid.appendChild(buildSlotButton(time)));
                section.appendChild(grid);

                return section;
            }

            if (morningSlots.length > 0) {
                container.appendChild(buildSection('Morning', 'wb_sunny', morningSlots, 'text-amber-500'));
            }

            if (morningSlots.length > 0 && afternoonSlots.length > 0) {
                const spacer = document.createElement('div');
                spacer.className = 'my-4';
                container.appendChild(spacer);
            }

            if (afternoonSlots.length > 0) {
                container.appendChild(buildSection('Afternoon', 'partly_cloudy_day', afternoonSlots, 'text-blue-500'));
            }
        }

        function loadSlots(date) {
            return fetch(`api/availability.php?date=${date}`)
                .then(r => r.json())
                .then(result => {
                    if (result.success) {
                        renderSlots(result.data[date]);
                    } else {
                        renderSlots([]);
                    }
                })
                .catch(() => renderSlots([]));
        }

        function openSlotStream(date) {
            if (window._slotStream) window._slotStream.close();
            window._slotStream = new EventSource(`api/slot-stream.php?date=${date}`);

            window._slotStream.onmessage = (e) => {
                const payload = JSON.parse(e.data);
                if (payload.error) {
                    loadSlots(date);
                    return;
                }
                renderSlots(payload.available);
                const selectedTime = inputs.time.value;
                if (selectedTime && payload.taken.includes(selectedTime)) {
                    showModal('conflict',
                        'Your Slot Was Just Taken',
                        'Someone booked your selected time. Choose another:',
                        { alternatives: payload.available, date: date }
                    );
                    inputs.time.value = '';
                    updateLiveSummary();
                    updateFieldBasedProgress();
                    calculateProgress(2);
                }
            };

            window._slotStream.onerror = () => {
                loadSlots(date);
            };
        }

        // Bottom Sheet Drag Logic
        const sheetHandle = document.getElementById('bottom-sheet-drag-handle');
        const sheetContainer = document.getElementById('time-modal-container');
        
        function handleDragStart(e) {
            if (window.innerWidth >= 640) return; // Desktop uses modal
            bottomSheetDragData.isDragging = true;
            bottomSheetDragData.startY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            bottomSheetDragData.startTime = Date.now();
            sheetContainer.style.transition = 'none';
        }

        function handleDragMove(e) {
            if (!bottomSheetDragData.isDragging) return;
            const y = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            const deltaY = y - bottomSheetDragData.startY;
            
            if (deltaY > 0) { // Only allow dragging down
                bottomSheetDragData.currentY = deltaY;
                sheetContainer.style.transform = `translateY(${deltaY}px)`;
            }
        }

        function handleDragEnd() {
            if (!bottomSheetDragData.isDragging) return;
            bottomSheetDragData.isDragging = false;
            sheetContainer.style.transition = 'transform 0.3s cubic-bezier(0.34,1.56,0.64,1)';
            
            const timeDiff = Date.now() - bottomSheetDragData.startTime;
            const velocity = bottomSheetDragData.currentY / timeDiff;

            if (bottomSheetDragData.currentY > 80 || velocity > 0.5) {
                closeTimeModal(false);
            } else {
                sheetContainer.style.transform = ''; // snap back
            }
            bottomSheetDragData.currentY = 0;
        }

        sheetHandle.addEventListener('touchstart', handleDragStart, { passive: true });
        document.addEventListener('touchmove', handleDragMove, { passive: true });
        document.addEventListener('touchend', handleDragEnd);
        
        // Mouse fallback for testing
        sheetHandle.addEventListener('mousedown', handleDragStart);
        document.addEventListener('mousemove', handleDragMove);
        document.addEventListener('mouseup', handleDragEnd);

        function openTimeModal(dateStr) {
            // Track the active trigger element to restore focus afterwards
            window._triggerElement = document.activeElement;

            modalPreviousDate = inputs.date.value;
            modalPreviousTime = inputs.time.value;
            
            inputs.date.value = dateStr;
            
            const dateObj = new Date(dateStr + "T12:00:00");
            document.getElementById('time-modal-date-title').textContent = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
            
            const dayData = mockScheduleData[dateStr] || mockScheduleData['default'];
            document.getElementById('time-modal-hours-subtitle').textContent = `${dayData.hours} · ${dayData.period}`;
            
            const pill = document.getElementById('time-modal-status-pill');
            let availCount = Object.values(dayData.slots).filter(s => s === 'available').length;
            let totalCount = Object.keys(dayData.slots).length;
            
            document.getElementById('time-modal-slot-count').textContent = `${availCount} of ${totalCount} slots available`;
            
            pill.className = 'px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-bold tracking-wide uppercase border ';
            if(dayData.status === 'available') { pill.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200'); pill.textContent = 'Available'; }
            else if(dayData.status === 'limited') { pill.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200'); pill.textContent = 'Limited'; }
            else { pill.classList.add('bg-red-50', 'text-red-700', 'border-red-200'); pill.textContent = 'Full'; }

            let preSelect = (dateStr === modalPreviousDate) ? modalPreviousTime : null;
            if(!preSelect) inputs.time.value = ''; 
            
            document.getElementById('timeline-container').innerHTML = '<div class="text-center py-8 text-slate-400">Loading slots...</div>';
            
            loadSlots(dateStr).then(() => {
                openSlotStream(dateStr);
                setupModalKeyboardNav();
                updateModalFooter();
            });
            
            const backdrop = document.getElementById('time-modal-backdrop');
            const container = document.getElementById('time-modal-container');
            backdrop.classList.remove('hidden');
            backdrop.removeAttribute('aria-hidden'); // Ensure backdrop does not block accessible accessibility focus tree

            // Reset transform for animation
            container.style.transform = '';
            
            requestAnimationFrame(() => {
                backdrop.classList.remove('opacity-0');
                container.classList.remove('sm:scale-95', 'sm:opacity-0', 'translate-y-full');
                container.classList.add('sm:scale-100', 'sm:opacity-100', 'translate-y-0');

                // Shift focus sequentially to accessible element once animated
                setTimeout(() => {
                    const firstFocusable = container.querySelector('#time-modal-close-x:not(.hidden), .slot-item:not(.disabled), #time-modal-cancel');
                    if (firstFocusable) {
                        firstFocusable.focus();
                    }
                }, 150);
            });
        }

        function closeTimeModal(save = false) {
            if (window._slotStream) {
                window._slotStream.close();
                window._slotStream = null;
            }

            const backdrop = document.getElementById('time-modal-backdrop');
            const container = document.getElementById('time-modal-container');
            
            if(!save) {
                inputs.date.value = modalPreviousDate;
                inputs.time.value = modalPreviousTime;
            } else {
                saveFormData();
                updateLiveSummary();
                updateFieldBasedProgress();
                calculateProgress(2);
            }
            
            backdrop.classList.add('opacity-0');
            container.classList.remove('sm:scale-100', 'sm:opacity-100', 'translate-y-0');
            container.classList.add('sm:scale-95', 'sm:opacity-0', 'translate-y-full');
            
            // Clean up inline styles from drag
            setTimeout(() => { container.style.transform = ''; }, 300);

            setTimeout(() => {
                backdrop.classList.add('hidden');
                backdrop.setAttribute('aria-hidden', 'true'); // Only set aria-hidden after close animation fully completes
                cleanupModalKeyboardNav();
                renderCalendar(currentCalendarYear, currentCalendarMonth);

                // Restore focus to the origin element that triggered the modal open
                if (window._triggerElement && typeof window._triggerElement.focus === 'function') {
                    window._triggerElement.focus();
                }
            }, 300);
        }

        document.getElementById('time-modal-cancel').addEventListener('click', () => closeTimeModal(false));
        document.getElementById('time-modal-close-x').addEventListener('click', () => closeTimeModal(false));
        document.getElementById('time-modal-confirm').addEventListener('click', () => closeTimeModal(true));
        
        document.getElementById('time-modal-backdrop').addEventListener('click', (e) => {
            if(e.target === document.getElementById('time-modal-backdrop') || e.target.closest('.absolute.inset-0')) {
                closeTimeModal(false);
            }
        });

        function renderCalendar(year, month) {
            const oldGrid = document.getElementById('calendar-grid');
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevMonthDays = new Date(year, month, 0).getDate();
            
            if (calendarMonthYear) calendarMonthYear.textContent = new Date(year, month).toLocaleDateString(undefined, { month: 'long', year: 'numeric' });

            const newGrid = document.createElement('div');
            newGrid.id = 'calendar-grid';
            newGrid.className = 'grid grid-cols-7 gap-1 min-[1440px]:gap-1.5 w-full absolute top-2 left-1.5 right-1.5 transition-all duration-300 transform';
            
            if (calendarDirection === 'next') newGrid.classList.add('translate-x-full', 'opacity-0');
            else if (calendarDirection === 'prev') newGrid.classList.add('-translate-x-full', 'opacity-0');
            else {
                newGrid.classList.add('translate-x-0', 'opacity-100');
                newGrid.style.position = 'relative';
                newGrid.style.top = '0';
                newGrid.style.left = '0';
            }

            for (let i = 0; i < firstDay; i++) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'calendar-day empty-day disabled border border-slate-100 border-dashed text-slate-300 bg-slate-50/50 cursor-not-allowed text-xs font-semibold opacity-60';
                emptyDiv.textContent = prevMonthDays - firstDay + i + 1;
                newGrid.appendChild(emptyDiv);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isPast = dateStr < tomorrowStr;
                const isToday = dateStr === todayStr;
                
                const dayDiv = document.createElement('div');
                let dayStatus = 'unavailable';
                let dayData = mockScheduleData['default'];

                if (!isPast) {
                    dayData = mockScheduleData[dateStr] || mockScheduleData['default'];
                    dayStatus = dayData.status;
                }

                dayDiv.dataset.date = dateStr;

                if (isPast) {
                    dayDiv.className = 'calendar-day disabled border border-slate-100 border-dashed text-slate-300 line-through bg-slate-50/50 cursor-not-allowed text-xs font-semibold opacity-60 pointer-events-none';
                    dayDiv.innerHTML = `<span>${day}</span><div class="status-dot bg-slate-300"></div>`;
                } else if (dayStatus === 'full') {
                    dayDiv.className = 'calendar-day tooltip-container border border-slate-100 text-slate-400 line-through bg-slate-50 cursor-not-allowed text-xs font-semibold';
                    dayDiv.innerHTML = `<span>${day}</span><div class="status-dot bg-red-400"></div><div class="tooltip hidden sm:block">Fully booked</div>`;
                } else {
                    dayDiv.className = 'calendar-day tooltip-container border border-slate-100 text-xs font-semibold text-slate-700 bg-white shadow-sm transition-all duration-300';
                    const availSlots = Object.values(dayData.slots).filter(s => s === 'available').length;
                    
                    if (dayStatus === 'limited') {
                        dayDiv.innerHTML = `<span>${day}</span><div class="status-dot bg-amber-500"></div><div class="tooltip hidden sm:block">Only ${availSlots} slots left</div>`;
                    } else {
                        dayDiv.innerHTML = `<span>${day}</span><div class="status-dot bg-emerald-500"></div><div class="tooltip hidden sm:block">${availSlots} slots available</div>`;
                    }

                    if (inputs.date.value === dateStr) {
                        dayDiv.classList.add('selected');
                        
                        if(inputs.time.value) {
                            const dot = dayDiv.querySelector('.status-dot');
                            if(dot) dot.remove();
                            dayDiv.classList.remove('bg-primary', 'text-white');
                            dayDiv.classList.add('ring-2', 'ring-emerald-400', 'ring-offset-1', 'bg-emerald-50', 'text-emerald-800');
                            
                            const shortTime = inputs.time.value.replace(' AM','a').replace(' PM','p');
                            dayDiv.innerHTML = `<span class="font-bold">${day}</span><div class="absolute bottom-[3px] sm:bottom-1 text-[9px] font-bold text-emerald-700 leading-none flex items-center justify-center w-full"><span class="icon-line text-[10px] mr-[1px]">schedule</span>${shortTime}</div>`;
                        } else {
                            const dot = dayDiv.querySelector('.status-dot');
                            if (dot) {
                                dot.className = 'status-dot border border-white ring-1 ring-white/50';
                                if (dayStatus === 'available') dot.classList.add('bg-emerald-400');
                                else if (dayStatus === 'limited') dot.classList.add('bg-amber-400');
                            }
                        }
                    }

                    dayDiv.addEventListener('click', () => {
                        openTimeModal(dateStr);
                    });
                }

                if (isToday && !dayDiv.classList.contains('selected') && !inputs.time.value) {
                    dayDiv.classList.add('ring-2', 'ring-primary/20', 'ring-offset-1');
                }

                newGrid.appendChild(dayDiv);
            }
            
            const totalCells = firstDay + daysInMonth;
            const remainingCells = (totalCells % 7 === 0) ? 0 : 7 - (totalCells % 7);
            for (let i = 1; i <= remainingCells; i++) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'calendar-day empty-day disabled border border-slate-100 border-dashed text-slate-300 bg-slate-50/50 cursor-not-allowed text-xs font-semibold opacity-60';
                emptyDiv.textContent = i;
                newGrid.appendChild(emptyDiv);
            }

            calendarGridContainer.appendChild(newGrid);
            calendarGridContainer.style.height = `${newGrid.scrollHeight}px`;

            if (calendarDirection) {
                oldGrid.classList.add('transition-all', 'duration-300', 'transform', 'absolute');
                oldGrid.classList.remove('relative');

                if (calendarDirection === 'next') oldGrid.classList.add('-translate-x-full', 'opacity-0');
                else oldGrid.classList.add('translate-x-full', 'opacity-0');

                requestAnimationFrame(() => {
                    newGrid.classList.remove('translate-x-full', '-translate-x-full', 'opacity-0');
                    newGrid.classList.add('translate-x-0', 'opacity-100');
                });

                setTimeout(() => {
                    if(oldGrid && oldGrid.parentNode) oldGrid.remove();
                    newGrid.classList.remove('absolute');
                    newGrid.classList.add('relative');
                    newGrid.style.top = '0';
                    newGrid.style.left = '0';
                    calendarGridContainer.style.height = 'auto';
                }, 300);
            } else {
                if(oldGrid && oldGrid !== newGrid) oldGrid.remove();
                newGrid.classList.remove('absolute');
                newGrid.classList.add('relative');
                newGrid.style.top = '0';
                newGrid.style.left = '0';
                calendarGridContainer.style.height = 'auto';
            }
            calendarDirection = '';
        }

        document.getElementById('prev-month').addEventListener('click', () => {
            currentCalendarMonth--;
            if(currentCalendarMonth < 0) { currentCalendarMonth = 11; currentCalendarYear--; }
            calendarDirection = 'prev';
            renderCalendar(currentCalendarYear, currentCalendarMonth);
        });
        document.getElementById('next-month').addEventListener('click', () => {
            currentCalendarMonth++;
            if(currentCalendarMonth > 11) { currentCalendarMonth = 0; currentCalendarYear++; }
            calendarDirection = 'next';
            renderCalendar(currentCalendarYear, currentCalendarMonth);
        });

        // Step Transitions and Credential Check Logic
        btnGotoStep2.addEventListener('click', async () => {
            if (validateStep(1)) {
                // Activate loading state on button
                const btnIcon = document.getElementById('btn-goto-step-2-icon');
                btnIcon.classList.remove('arrow_forward');
                btnIcon.classList.add('sync', 'animate-spin');
                btnGotoStep2.disabled = true;

                const phoneVal = `${PHONE_COUNTRY_CODE} ${inputs.phone.value}`;

                try {
                    const res = await fetch('api/check-patient.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': CSRF_TOKEN
                        },
                        body: JSON.stringify({ email: inputs.email.value, phone: phoneVal })
                    });
                    
                    const data = await res.json();
                    
                    // Revert loading state
                    btnIcon.classList.remove('sync', 'animate-spin');
                    btnIcon.classList.add('arrow_forward');
                    btnGotoStep2.disabled = false;
                    
                    const firstNameVal = data.first_name || inputs.firstName.value || 'there';

                    // Match based on updated API spec
                    if (data.account_status === 'registered') {
                        showModal(
                            'auth-check',
                            `Welcome back, ${firstNameVal}!`,
                            'You already have a registered account under this email. Please log in to continue your booking.',
                            { matchType: 'registered' }
                        );
                        return; // Hard gate
                    } else if (data.account_status === 'guest_history' && data.match === 'full') {
                        showModal(
                            'auth-check',
                            `Welcome back, ${firstNameVal}!`,
                            'We found past bookings under this email and phone number. Please create an account to manage your appointments and continue.',
                            { matchType: 'guest_history_full' }
                        );
                        return; // Hard gate
                    } else if (data.account_status === 'guest_history' && data.match === 'partial') {
                        showModal(
                            'auth-check',
                            'Is this your account?',
                            "We found existing bookings under this email, but the phone number doesn't match. Is this you?",
                            { matchType: 'guest_history_partial' }
                        );
                        return; // Stop flow, wait for user's identity decision
                    } else {
                        // account_status === 'new' or otherwise unrecognized but valid response -> proceed seamlessly
                        proceedToStep2();
                    }
                } catch (e) {
                    console.error('Check patient error', e);
                    // Revert loading state and silently proceed on error (fail-open)
                    btnIcon.classList.remove('sync', 'animate-spin');
                    btnIcon.classList.add('arrow_forward');
                    btnGotoStep2.disabled = false;
                    proceedToStep2();
                }
            }
        });

        function proceedToStep2() {
            forceSyncNotes();
            saveFormData();
            updateStepWithAnimation(1, 2);
            if(inputs.date.value) {
                const d = new Date(inputs.date.value + "T12:00:00");
                currentCalendarYear = d.getFullYear();
                currentCalendarMonth = d.getMonth();
            }
            calendarDirection = '';
            renderCalendar(currentCalendarYear, currentCalendarMonth);
        }
        window.proceedToStep2 = proceedToStep2; // Export for modal buttons

        btnBacktoStep1.addEventListener('click', () => {
            updateStepWithAnimation(2, 1);
        });

        btnGotoStep3.addEventListener('click', () => {
            forceSyncNotes();
            if (validateStep(2)) {
                saveFormData();
                populateReviewStep();
                updateStepWithAnimation(2, 3);
            }
        });

        reviewBackBtn.addEventListener('click', () => {
            updateStepWithAnimation(3, 2);
        });

        reviewEditBtn.addEventListener('click', () => {
            updateStepWithAnimation(3, 1);
        });

        function updateStepWithAnimation(from, to) {
            const outStep = document.getElementById(`step-${from}`);
            const inStep = document.getElementById(`step-${to}`);
            
            // Clean up old animation classes
            outStep.classList.remove('slide-exit-left', 'slide-exit-right', 'slide-enter-left', 'slide-enter-right');
            inStep.classList.remove('slide-exit-left', 'slide-exit-right', 'slide-enter-left', 'slide-enter-right');

            const isForward = to > from;

            outStep.classList.add(isForward ? 'slide-exit-left' : 'slide-exit-right');
            
            setTimeout(() => {
                outStep.classList.remove('active');
                inStep.classList.add('active');
                inStep.classList.add(isForward ? 'slide-enter-right' : 'slide-enter-left');
                
                currentStep = to;
                updateFieldBasedProgress();
                calculateProgress(1);
                calculateProgress(2);
                if (currentStep === 3) {
                    setProgress(3, 100);
                    
                    // Stagger animate review blocks
                    const header = inStep.querySelector('.review-animate-header');
                    const blocks = inStep.querySelectorAll('.review-animate-block');
                    
                    if (header) header.classList.remove('visible');
                    blocks.forEach(b => b.classList.remove('visible'));
                    
                    setTimeout(() => {
                        if (header) header.classList.add('visible');
                        blocks.forEach((block, index) => {
                            setTimeout(() => {
                                block.classList.add('visible');
                            }, (index + 1) * 80); // 80ms stagger between blocks
                        });
                    }, 50); // slight initial delay to ensure DOM is ready
                }
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
                setStickyTop();
            }, 300); // slightly less than CSS animation duration to prevent flicker
        }

        reviewConfirmBtn.addEventListener('click', () => {
            forceSyncNotes();
            
            reviewConfirmBtn.innerHTML = '<span class="icon-line animate-spin text-base block">refresh</span> <span class="hidden sm:inline">Processing Booking...</span>';
            reviewConfirmBtn.disabled = true;
            
            const formData = {
                firstName: inputs.firstName.value,
                lastName: inputs.lastName.value,
                email: inputs.email.value,
                phone: `${PHONE_COUNTRY_CODE} ${inputs.phone.value}`,
                service: inputs.service.value,
                date: inputs.date.value,
                time: inputs.time.value,
                notes: inputs.notes.value,
                preferences: {
                    sms: document.getElementById('pref-sms')?.checked || false,
                    email: document.getElementById('pref-email')?.checked || false,
                    marketing: document.getElementById('pref-marketing')?.checked || false
                }
            };

            fetch('api/booking-create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify(formData)
            })
            .then(async response => {
                if (response.status === 409) {
                    const result = await response.json();
                    showModal('conflict',
                        'Slot No Longer Available',
                        'This time was just booked by someone else.',
                        {
                            alternatives: result.alternatives || [],
                            next_available: result.next_available || null,
                            date: formData.date
                        }
                    );
                    resetConfirmBtn();
                    return null;
                }
                if (!response.ok) {
                    const res = await response.json().catch(()=>({}));
                    throw new Error(res.message || "An error occurred");
                }
                return response.json();
            })
            .then(result => {
                if(!result) return;
                if(result.success) {
                    formData.referenceCode = result.reference_code || (result.data ? result.data.reference_code : ''); 
                    
                    // 1a. Store reference code in sessionStorage
                    sessionStorage.setItem('last_booking_ref', formData.referenceCode);

                    // CLEAR SENSITIVE DATA
                    localStorage.removeItem('dentalBookingData');

                    // 1b & 1c. Trigger fluid page exit animation, then redirect
                    document.querySelector('main').classList.add('page-exit');
                    setTimeout(() => {
                        window.location.href = 'confirmed-booking.php?ref=' + encodeURIComponent(formData.referenceCode);
                    }, 250);

                } else {
                    showModal('error', 'Booking Failed', result.message || "An error occurred while booking.");
                    resetConfirmBtn();
                }
            })
            .catch(error => {
                showModal('error', 'Network Error', error.message || "Network error. Please try again.");
                resetConfirmBtn();
            });
        });

        function resetConfirmBtn() {
            reviewConfirmBtn.innerHTML = 'Confirm <span class="hidden sm:inline">Appointment</span> <span class="icon-line text-base">check_circle</span>';
            reviewConfirmBtn.disabled = false;
        }

        function validateStep(step) {
            let isValid = true;
            let firstInvalidEl = null;
            const stepElement = document.getElementById(`step-${step}`);
            const requiredInputs = stepElement.querySelectorAll('[required]');

            requiredInputs.forEach(input => {
                // For custom hidden inputs like service, date, time
                if (input.type === 'hidden' && !input.id) return; 

                const wrapper = input.closest('.relative') || input.parentElement;
                const errorMsg = wrapper?.nextElementSibling;
                
                let isFieldValid = true;
                if (!input.value.trim()) {
                    isFieldValid = false;
                } else if (input.type === 'email' && !emailRegex.test(input.value)) {
                    isFieldValid = false;
                }

                if (!isFieldValid) {
                    isValid = false;
                    if (!firstInvalidEl) firstInvalidEl = input;
                    
                    if (input.tagName !== 'SELECT' && input.type !== 'hidden') {
                        input.classList.add('border-red-500');
                    }
                    if (errorMsg && errorMsg.classList.contains('error-msg')) errorMsg.classList.remove('hidden');
                    
                    // Specific logic for service grid
                    if (input.id === 'service') {
                        const badge = document.getElementById('service-required-badge');
                        if(badge) badge.classList.remove('hidden');
                        const error = document.getElementById('service-error');
                        if(error) error.classList.remove('hidden');
                        if (!firstInvalidEl) firstInvalidEl = document.getElementById('service-grid-container');
                    }
                } else {
                    if (input.tagName !== 'SELECT' && input.type !== 'hidden') {
                        input.classList.remove('border-red-500');
                    }
                    if (errorMsg && errorMsg.classList.contains('error-msg')) errorMsg.classList.add('hidden');
                }
            });

            if (!isValid) {
                showModal('error', 'Incomplete Form', "Please fill in all required fields before proceeding.");
                shakeStep(step);
                
                // Scroll to first invalid element
                if (firstInvalidEl) {
                    let targetEl = firstInvalidEl;
                    if (firstInvalidEl.id === 'date' || firstInvalidEl.id === 'time') {
                        targetEl = document.getElementById('calendar-container-wrapper');
                    }
                    
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    setTimeout(() => {
                        if (targetEl.tagName === 'INPUT' && targetEl.type !== 'hidden') {
                            targetEl.focus();
                        } else if (firstInvalidEl.id === 'time' && inputs.date.value) {
                            // If time is missing but date is set, open modal
                            openTimeModal(inputs.date.value);
                        } else if (targetEl.id === 'calendar-container-wrapper') {
                            // Just highlight calendar
                            targetEl.classList.add('ring-2', 'ring-red-400', 'ring-offset-2');
                            setTimeout(() => targetEl.classList.remove('ring-2', 'ring-red-400', 'ring-offset-2'), 1500);
                        }
                    }, 300);
                }
            }

            return isValid;
        }

        function saveFormData() {
            const data = {
                firstName: inputs.firstName.value,
                lastName: inputs.lastName.value,
                email: inputs.email.value,
                phone: `${PHONE_COUNTRY_CODE} ${inputs.phone.value}`,
                service: inputs.service.value,
                date: inputs.date.value,
                time: inputs.time.value,
                // notes are deliberately excluded from localStorage to prevent sensitive info persistence
                preferences: {
                    sms: document.getElementById('pref-sms')?.checked || false,
                    email: document.getElementById('pref-email')?.checked || false,
                    marketing: document.getElementById('pref-marketing')?.checked || false
                }
            };
            localStorage.setItem('dentalBookingData', JSON.stringify(data));
        }

        // Live Summary Micro-animations
        function animateTextSwap(element, newText) {
            if (!element || element.textContent === newText) return;
            element.classList.add('fade-out');
            setTimeout(() => {
                element.textContent = newText;
                element.classList.remove('fade-out');
                element.classList.add('fade-in');
                setTimeout(() => element.classList.remove('fade-in'), 150);
            }, 150);
        }

        function flashSummaryRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.classList.remove('flash-highlight');
                // Trigger reflow
                void row.offsetWidth;
                row.classList.add('flash-highlight');
            }
        }

        function updateLiveSummaryValue(type) {
            const nameEl = document.getElementById('live-name');
            const serviceEl = document.getElementById('live-service');
            const dateEl = document.getElementById('live-date');
            const timeEl = document.getElementById('live-time');
            const durationEl = document.getElementById('live-duration');

            if (type === 'name' || type === 'all') {
                let fullName = (inputs.firstName.value + " " + inputs.lastName.value).trim() || "--";
                if (nameEl && nameEl.textContent !== fullName) {
                    animateTextSwap(nameEl, fullName);
                    flashSummaryRow('summary-row-name');
                }
            }
            if (type === 'service' || type === 'all') {
                let svcText = inputs.service.options[inputs.service.selectedIndex]?.text || "--";
                if (inputs.service.value === '') svcText = "--";
                if (serviceEl && serviceEl.textContent !== svcText) {
                    animateTextSwap(serviceEl, svcText);
                    flashSummaryRow('summary-row-service');
                }
                
                let durText = inputs.service.options[inputs.service.selectedIndex]?.dataset.duration || "--";
                if (durationEl && durationEl.textContent !== durText) {
                    animateTextSwap(durationEl, durText);
                    flashSummaryRow('summary-row-duration');
                }
            }
            if (type === 'date' || type === 'all') {
                let rawDate = inputs.date.value;
                let dateText = rawDate ? new Date(rawDate + "T12:00:00").toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : "--";
                if (dateEl && dateEl.textContent !== dateText) {
                    animateTextSwap(dateEl, dateText);
                    flashSummaryRow('summary-row-date');
                }
            }
            if (type === 'time' || type === 'all') {
                let timeText = inputs.time.value || "--";
                if (timeEl && timeEl.textContent !== timeText) {
                    animateTextSwap(timeEl, timeText);
                    flashSummaryRow('summary-row-time');
                }
            }
        }

        function updateLiveSummary() {
            updateLiveSummaryValue('all');
        }

        function populateReviewStep() {
            document.getElementById('review-name').textContent = `${inputs.firstName.value} ${inputs.lastName.value}`;
            document.getElementById('review-email').textContent = inputs.email.value;
            document.getElementById('review-phone').textContent = `${PHONE_COUNTRY_CODE} ${inputs.phone.value}`;
            
            const svcOpt = inputs.service.options[inputs.service.selectedIndex];
            document.getElementById('review-service').textContent = svcOpt.text;
            document.getElementById('review-duration').textContent = svcOpt.dataset.duration;
            document.getElementById('review-header-title').textContent = svcOpt.text;
            document.getElementById('review-header-duration').textContent = svcOpt.dataset.duration;

            const dateObj = new Date(inputs.date.value + "T12:00:00");
            document.getElementById('review-date').textContent = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
            document.getElementById('review-time').textContent = inputs.time.value;
            document.getElementById('review-header-subtitle').textContent = `${dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })} at ${inputs.time.value}`;

            const notesEl = document.getElementById('review-notes');
            if (inputs.notes.value.trim()) {
                notesEl.textContent = `"${inputs.notes.value.trim()}"`;
                notesEl.classList.remove('italic', 'text-slate-400');
                notesEl.classList.add('text-slate-700');
            } else {
                notesEl.textContent = "No additional notes provided for this session.";
                notesEl.classList.add('italic', 'text-slate-400');
                notesEl.classList.remove('text-slate-700');
            }
        }

        updateFieldBasedProgress();

        // Sticky Summary Card positioning
        function setStickyTop() {
            const stepper = document.getElementById('stepper-wrapper');
            const card    = document.getElementById('summary-sticky-card');
            const body    = document.getElementById('summary-card-body');
            if (!stepper || !card) return;

            // The stepper has a top-[80px] sticky offset. 
            // The card's sticky top should be this offset + the stepper's height + a gap.
            const headerOffset = 80;
            const gap = 16;
            const calculatedTop = headerOffset + stepper.offsetHeight + gap;
            
            card.style.top = calculatedTop + 'px';

            if (body) {
                body.style.maxHeight = Math.max(window.innerHeight - calculatedTop - 24, 80) + 'px';
            }
        }

        requestAnimationFrame(() => requestAnimationFrame(setStickyTop));

        window.addEventListener('resize', setStickyTop);
        document.addEventListener('stepChanged', setStickyTop);

        if (window.ResizeObserver) {
            new ResizeObserver(() => requestAnimationFrame(setStickyTop))
                .observe(document.getElementById('stepper-wrapper') || document.body);
        }
    });
</script>
</body>
</html>