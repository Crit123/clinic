<?php
/**
 * admin/components/sidebar.php
 * Reusable left-nav sidebar for the admin panel. Included via PHP include
 * on every admin/pages/*.php page.
 *
 * Expects the including page to have already set (before this include):
 *   - $currentRole  : 'admin' | 'staff'   (role of the logged-in user)
 *   - $currentPage  : optional string key matching one of the nav items
 *                      below (e.g. 'dashboard', 'bookings'). If not set,
 *                      this file falls back to matching against
 *                      $_SERVER['PHP_SELF'].
 *
 * Access map (nav visibility by role) — admin sees everything staff sees,
 * plus the two admin-only sections called out elsewhere in this build
 * (Patient Records and Feedback Moderation):
 *   - Dashboard             : admin, staff
 *   - Bookings              : admin, staff
 *   - Emergency Requests    : admin, staff
 *   - Patient Records       : admin only
 *   - Feedback Moderation   : admin only
 *   - My Profile            : admin, staff
 */

$currentRole = $currentRole ?? 'staff';
$isAdmin = $currentRole === 'admin';

// Fall back to inferring the active page from the script path if the
// including page didn't explicitly set $currentPage (e.g. via $activePage
// used on earlier pages in this build).
if (!isset($currentPage)) {
    $currentPage = $activePage ?? basename($_SERVER['PHP_SELF'], '.php');
}

$navItems = [
    ['key' => 'dashboard',           'label' => 'Dashboard',            'href' => 'dashboard.php',           'icon' => 'space_dashboard', 'roles' => ['admin', 'staff']],
    ['key' => 'bookings',            'label' => 'Bookings',             'href' => 'bookings.php',            'icon' => 'event_available',  'roles' => ['admin', 'staff']],
    ['key' => 'emergency-requests',  'label' => 'Emergency Requests',   'href' => 'emergency-requests.php',  'icon' => 'emergency',       'roles' => ['admin', 'staff']],
    ['key' => 'records',             'label' => 'Patient Records',      'href' => 'records.php',             'icon' => 'folder_shared',   'roles' => ['admin']],
    ['key' => 'feedback',            'label' => 'Feedback Moderation',  'href' => 'feedback.php',            'icon' => 'rate_review',     'roles' => ['admin']],
    ['key' => 'profile',             'label' => 'My Profile',           'href' => 'profile.php',             'icon' => 'account_circle',  'roles' => ['admin', 'staff']],
];
?>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0" rel="stylesheet"/>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20; }

    #adminSidebar {
        width: 260px; background-color: #ffffff; border-right: 1px solid rgba(114,119,131,0.15);
        display: flex; flex-direction: column; height: 100vh; position: sticky; top: 0;
        transition: transform 0.25s ease; z-index: 60;
    }
    .admin-nav-link {
        display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 0.65rem;
        font-size: 13.5px; font-weight: 600; color: #424752; transition: all 0.15s ease;
    }
    .admin-nav-link:hover { background-color: rgba(0, 71, 141, 0.06); color: #00478d; }
    .admin-nav-link.active-nav {
        background-color: #00478d; color: #ffffff;
        box-shadow: 0 4px 12px rgba(0,71,141,0.25);
    }
    .admin-nav-link.active-nav .material-symbols-outlined { color: #ffffff; }

    /* Mobile behavior: off-canvas drawer below the md breakpoint (768px),
       matching the site's existing md:hidden / md:flex conventions. */
    @media (max-width: 767px) {
        #adminSidebar {
            position: fixed; left: 0; top: 0; transform: translateX(-100%);
            box-shadow: 0 20px 48px rgba(0,0,0,0.18);
        }
        #adminSidebar.mobile-open { transform: translateX(0); }
        #adminSidebarOverlay {
            display: none; position: fixed; inset: 0; background: rgba(11,28,48,0.4);
            z-index: 55; opacity: 0; transition: opacity 0.2s ease;
        }
        #adminSidebarOverlay.visible { display: block; opacity: 1; }
    }
    @media (min-width: 768px) {
        #adminMobileToggle { display: none; }
        #adminSidebarOverlay { display: none !important; }
    }
</style>

<!-- Mobile hamburger toggle (visible below md breakpoint) -->
<button id="adminMobileToggle"
        onclick="toggleAdminSidebar(true)"
        aria-label="Open Navigation Menu"
        aria-expanded="false"
        aria-controls="adminSidebar"
        class="md:hidden fixed top-4 left-4 z-50 p-2.5 rounded-xl bg-white border border-outline-variant/30 text-on-surface-variant shadow-md hover:bg-surface-container-low transition-all focus:outline-none focus:ring-2 focus:ring-primary">
    <span class="material-symbols-outlined">menu</span>
</button>

<!-- Overlay for mobile drawer -->
<div id="adminSidebarOverlay" onclick="toggleAdminSidebar(false)" aria-hidden="true"></div>

<aside id="adminSidebar" aria-label="Admin navigation">
    <!-- Logo / Clinic Name -->
    <div class="flex items-center justify-between px-5 py-6 border-b border-outline-variant/15">
        <a href="dashboard.php" class="flex items-center gap-2.5">
            <span class="material-symbols-outlined text-primary text-[28px]" aria-hidden="true">dentistry</span>
            <span class="font-bold text-on-surface text-[15px] leading-tight">
                DentalCare Pro
                <span class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/60">Staff Panel</span>
            </span>
        </a>
        <button onclick="toggleAdminSidebar(false)" aria-label="Close Navigation Menu" class="md:hidden p-1.5 rounded-lg text-on-surface-variant/50 hover:bg-surface-container-low">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
    </div>

    <!-- Nav links -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <?php foreach ($navItems as $item): ?>
            <?php if (!in_array($currentRole, $item['roles'], true)) continue; ?>
            <?php $isActive = $currentPage === $item['key']; ?>
            <a href="<?php echo htmlspecialchars($item['href']); ?>"
               class="admin-nav-link <?php echo $isActive ? 'active-nav' : ''; ?>"
               <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                <span class="material-symbols-outlined text-[20px]"><?php echo htmlspecialchars($item['icon']); ?></span>
                <?php echo htmlspecialchars($item['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Log Out (pinned to bottom; link only, no logic yet) -->
    <div class="px-3 py-4 border-t border-outline-variant/15">
        <a href="#" class="admin-nav-link text-rose-700 hover:bg-rose-50 hover:text-rose-700">
            <span class="material-symbols-outlined text-[20px]">logout</span>
            Log Out
        </a>
    </div>
</aside>

<script>
    /**
     * Toggles the mobile off-canvas sidebar. Exposed globally so a topbar
     * component (or any other page markup) can also trigger it if it has
     * its own hamburger button, mirroring the defensive-fallback pattern
     * used by the patient-facing header/sidebar.
     */
    function toggleAdminSidebar(open) {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('adminSidebarOverlay');
        const toggleBtn = document.getElementById('adminMobileToggle');
        if (!sidebar) return;

        sidebar.classList.toggle('mobile-open', open);
        if (overlay) overlay.classList.toggle('visible', open);
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    // Close the drawer automatically if the viewport grows past the mobile breakpoint
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) toggleAdminSidebar(false);
    });
</script>