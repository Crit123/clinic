<?php
/**
 * layout.php
 * Reusable application shell that combines the top layout, sidebar, header, 
 * page content <main>, floating elements, and bottom scripts.
 * * Expects $pageContent, $activePage, and $pageTitle to be set by the calling file.
 */

// MUST run first, before any output: session check + DB user fetch.
// header.php (included later) relies on $currentUser/$patientId/$recentSearches
// being already set here, and may redirect via header() if unauthenticated —
// that can only succeed if nothing has been echoed yet.
require_once __DIR__ . '/../auth/auth-guard.php';

require_once __DIR__ . '/../design-config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- preconnect before fonts so DNS resolves in parallel -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>

    <!-- Material Symbols font request -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"/>

    <!-- Portal typography base (Plus Jakarta Sans / Fraunces + .stat-number utility) -->
    <link rel="stylesheet" href="../../assets/css/theme-base.css"/>

    <!-- Tailwind CDN (loaded in <head>, never inside <body> components) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- TODO: Replace with compiled Tailwind CSS build asset for production -->
    <!-- <link rel="stylesheet" href="/assets/css/app.css"/> -->

    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary":                    "#003164",
                        "error-container":            "#ffdad6",
                        "on-secondary-fixed":         "#001e31",
                        "on-tertiary-fixed":          "#0f1d25",
                        "surface-container-highest":  "#d3e4fe",
                        "secondary-fixed":            "#cce5ff",
                        "surface-tint":               "#295ea5",
                        "on-surface-variant":         "#424751",
                        "secondary-fixed-dim":        "#91cdff",
                        "secondary-container":        "#75c3ff",
                        "primary-fixed":              "#d6e3ff",
                        "primary-fixed-dim":          "#a9c7ff",
                        "error":                      "#ba1a1a",
                        "surface-container-lowest":   "#ffffff",
                        "secondary":                  "#006496",
                        "tertiary-container":         "#3c4a53",
                        "on-primary-container":       "#8db8ff",
                        "tertiary-fixed-dim":         "#bac9d3",
                        "inverse-on-surface":         "#eaf1ff",
                        "on-surface":                 "#0b1c30",
                        "on-error":                   "#ffffff",
                        "on-tertiary-container":      "#aab9c3",
                        "outline-variant":            "#c2c6d2",
                        "primary-container":          "#00478d",
                        "on-secondary":               "#ffffff",
                        "on-primary-fixed":           "#001b3d",
                        "on-primary-fixed-variant":   "#00468b",
                        "on-secondary-container":     "#004f79",
                        "surface-container-low":      "#eff4ff",
                        "tertiary-fixed":             "#d6e5ef",
                        "background":                 "#f8f9ff",
                        "on-background":              "#0b1c30",
                        "inverse-primary":            "#a9c7ff",
                        "surface-container":          "#e5eeff",
                        "on-primary":                 "#ffffff",
                        "tertiary":                   "#26333c",
                        "on-error-container":         "#93000a",
                        "surface-variant":            "#d3e4fe",
                        "outline":                    "#737782",
                        "on-tertiary-fixed-variant":  "#3b4951",
                        "surface-bright":             "#f8f9ff",
                        "surface-dim":                "#cbdbf5",
                        "surface-container-high":     "#dce9ff",
                        "on-tertiary":                "#ffffff",
                        "surface":                    "#f8f9ff",
                        "inverse-surface":            "#213145",
                        "on-secondary-fixed-variant": "#004b72",
                        
                        // WCAG AA Compliant Text Contrast Tokens
                        "secondary-text":             "#475569", 
                        "tertiary-text":              "#64748b"  
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg":      "0.5rem",
                        "xl":      "0.75rem",
                        "2xl":     "1rem",
                        "full":    "9999px"
                    },
                    spacing: {
                        "gutter":             "24px",
                        "container-max-width":"1440px",
                        "margin-mobile":      "16px",
                        "unit":               "8px",
                        "margin-desktop":     "40px"
                    },
                    fontFamily: {
                        "label-md":          ["Plus Jakarta Sans"],
                        "headline-lg":       ["Plus Jakarta Sans"],
                        "body-lg":           ["Plus Jakarta Sans"],
                        "body-md":           ["Plus Jakarta Sans"],
                        "body-sm":           ["Plus Jakarta Sans"],
                        "headline-lg-mobile":["Plus Jakarta Sans"],
                        "headline-md":       ["Plus Jakarta Sans"],
                        "display-lg":        ["Fraunces"]
                    },
                    fontSize: {
                        "label-md":          ["12px",  { "lineHeight":"16px",  "letterSpacing":"0.05em", "fontWeight":"600" }],
                        "headline-lg":       ["32px",  { "lineHeight":"40px",  "letterSpacing":"-0.01em","fontWeight":"600" }],
                        "body-lg":           ["18px",  { "lineHeight":"28px",  "fontWeight":"400" }],
                        "body-md":           ["16px",  { "lineHeight":"24px",  "fontWeight":"400" }],
                        "body-sm":           ["14px",  { "lineHeight":"20px",  "fontWeight":"400" }],
                        "headline-lg-mobile":["24px",  { "lineHeight":"32px",  "fontWeight":"600" }],
                        "headline-md":       ["24px",  { "lineHeight":"32px",  "fontWeight":"600" }],
                        "display-lg":        ["48px",  { "lineHeight":"60px",  "letterSpacing":"-0.02em","fontWeight":"700" }]
                    }
                }
            }
        }
    </script>

    <style>
        /* ── Material Symbols ── */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }

        /* ── Sidebar & content smooth transitions ── */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes shimmer {
            0%   { background-position: -200% 0; }
            100% { background-position:  200% 0; }
        }
        .animate-shimmer {
            background: linear-gradient(
                90deg,
                var(--shimmer-a, #e5eeff) 25%,
                var(--shimmer-b, #f0f5ff) 50%,
                var(--shimmer-a, #e5eeff) 75%
            );
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* ── NProgress-style page transition bar ── */
        #nprogress-bar {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            background: var(--color-primary, #295ea5);
            z-index: 9999;
            transition: width 0.25s ease, opacity 0.4s ease;
            width: 0%;
            opacity: 0;
            border-radius: 0 2px 2px 0;
            pointer-events: none;
        }
        #nprogress-bar.active { opacity: 1; }

        /* ── Collapsed sidebar icon tooltips ── */
        .sidebar-collapsed .nav-tooltip { display: flex; }
        .nav-tooltip {
            display: none;
            position: absolute;
            left: calc(100% + 14px);
            top: 50%;
            transform: translateY(-50%);
            background: #213145;
            color: #eaf1ff;
            font-size: 12px;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 200;
        }
        .nav-tooltip::before {
            content: '';
            position: absolute;
            right: 100%; top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #213145;
        }

        /* ── Fade-in page animations ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0);   }
        }
        .fade-in          { animation: fadeInUp 0.35s ease both; }
        .delay-100        { animation-delay: 0.10s; }
        .delay-200        { animation-delay: 0.20s; }

        /* ── Respect prefers-reduced-motion ── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration:   0.01ms !important;
                transition-duration:  0.01ms !important;
            }
        }

        /* ── Focus-visible ring for high-contrast keyboard nav ── */
        :focus-visible { outline: 2px solid #295ea5; outline-offset: 2px; }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex">
<!-- NProgress page-transition bar -->
<div id="nprogress-bar" role="progressbar" aria-hidden="true"></div>

<!-- SIDEBAR PORTAL COMPONENT -->
<?php include __DIR__ . '/../sidebar.php'; ?>

<!-- MAIN CONTENT CONTAINER -->
<div id="contentContainer" class="flex-1 ml-0 md:ml-64 flex flex-col min-h-screen relative sidebar-transition">
    
    <!-- SHARED GLOBAL HEADER -->
    <?php include __DIR__ . '/../header.php'; ?>

    <!-- PAGE SPECIFIC CONTENT INJECTED HERE -->
    <main class="flex-1 p-gutter md:p-margin-desktop max-w-container-max-width mx-auto w-full space-y-8 overflow-x-hidden">
        <?php echo isset($pageContent) ? $pageContent : ''; ?>
    </main>

    <!-- FLOATING AI ASSISTANT BUTTON (Global to all portal pages) -->
    <div class="fixed bottom-8 right-8 z-50 group" role="complementary" aria-label="AI Assistant">
        <span class="absolute right-0 bottom-20 bg-inverse-surface text-inverse-on-surface text-xs font-semibold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none duration-300 whitespace-nowrap shadow-md border border-outline-variant"
              role="tooltip" id="ai-tooltip">
            DentalCare AI Assist (Coming Soon)
        </span>
        <button disabled
                aria-label="AI Assistant — coming soon"
                aria-describedby="ai-tooltip"
                class="w-16 h-16 bg-outline text-surface-container-lowest rounded-full cursor-not-allowed shadow-md opacity-60 flex items-center justify-center">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">smart_toy</span>
        </button>
    </div>

</div>

<script>
/* ════════════════════════════════════════════════════
   Centralised app state
   ════════════════════════════════════════════════════ */
const AppState = {
    unreadCount: 0, // TODO: seed from backend session data

    setUnreadCount(n) {
        this.unreadCount = Math.max(0, n);
        this._syncBadges();
    },

    _syncBadges() {
        const n = this.unreadCount;
        const bellBadge = document.getElementById('unreadBellBadge');
        if (bellBadge) bellBadge.classList.toggle('hidden', n === 0);

        const pill = document.getElementById('sidebarNotifBadge');
        if (pill) {
            pill.classList.toggle('hidden', n === 0);
            if (n > 0) pill.textContent = n;
        }
    }
};

/* ════════════════════════════════════════════
   NProgress-style page transition bar
   ════════════════════════════════════════════ */
const NProgress = {
    _iv: null,
    start() {
        const bar = document.getElementById('nprogress-bar');
        if (!bar) return;
        clearInterval(this._iv);
        bar.style.width = '0%';
        bar.classList.add('active');
        let w = 0;
        this._iv = setInterval(() => {
            w = Math.min(w + Math.random() * 14, 85);
            bar.style.width = w + '%';
        }, 130);
    },
    done() {
        const bar = document.getElementById('nprogress-bar');
        if (!bar) return;
        clearInterval(this._iv);
        bar.style.width = '100%';
        setTimeout(() => {
            bar.classList.remove('active');
            bar.style.width = '0%';
        }, 420);
    }
};

/* ════════════════════════════════════════════
   Global toast (ARIA live region support added)
   ════════════════════════════════════════════ */
function showGlobalToast(type, text) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.className = 'flex items-center space-x-3 p-4 rounded-xl shadow-lg border text-on-surface bg-surface-container-lowest transition-all transform translate-x-12 opacity-0 duration-300 pointer-events-auto';

    const MAP = {
        success: { icon: 'check_circle', cls: 'border-emerald-500 text-emerald-600' },
        error:   { icon: 'error',        cls: 'border-error text-error'             },
        warning: { icon: 'warning',      cls: 'border-amber-500 text-amber-600'     },
        info:    { icon: 'info',         cls: 'border-primary text-primary'         },
    };
    const { icon, cls } = MAP[type] || MAP.info;

    toast.innerHTML = `
        <div class="${cls}" aria-hidden="true">
            <span class="material-symbols-outlined">${icon}</span>
        </div>
        <div class="flex-1 font-body-sm text-sm text-on-background">${text}</div>
        <button onclick="this.parentElement.remove()" aria-label="Dismiss notification"
                class="text-outline hover:text-on-surface">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
        </button>`;

    container.appendChild(toast);
    requestAnimationFrame(() => {
        requestAnimationFrame(() => toast.classList.remove('translate-x-12', 'opacity-0'));
    });
    setTimeout(() => {
        toast.classList.add('translate-x-12', 'opacity-0');
        setTimeout(() => toast.remove(), 320);
    }, 4200);
}

/* ── Boot on DOMContentLoaded ── */
document.addEventListener('DOMContentLoaded', () => {
    AppState._syncBadges();

    /* Hook internal links to NProgress */
    document.querySelectorAll('a[href]').forEach(a => {
        const h = a.getAttribute('href') || '';
        if (h.startsWith('#') || h.startsWith('http') || h.startsWith('mailto') || h.startsWith('javascript')) return;
        a.addEventListener('click', () => NProgress.start());
    });
});

window.addEventListener('pageshow', () => NProgress.done());
</script>
</body>
</html>