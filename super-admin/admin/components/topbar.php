<?php
/**
 * admin/components/topbar.php
 * Reusable top bar for the admin panel. Included alongside sidebar.php on
 * every admin/pages/*.php page.
 *
 * Expects the including page to have already set (before this include):
 *   - $pageTitle       : string, e.g. "Bookings", "Emergency Requests"
 *   - $currentUser      : array, e.g. ['name' => 'Dr. Ana Reyes', 'role' => 'admin' | 'staff']
 *   - $deploymentMode   : 'single' | 'multi' — controls whether the branch
 *                          label renders AT ALL (not just visually hidden).
 *   - $branchName       : string, only relevant/used when $deploymentMode === 'multi'
 *
 * Defensive fallbacks below mirror the pattern used in the patient-facing
 * header.php, so this file doesn't throw undefined-variable warnings if a
 * page includes it without setting everything explicitly.
 */

$pageTitle      = $pageTitle      ?? 'Admin Panel';
$currentUser    = $currentUser    ?? ['name' => 'Staff User', 'role' => 'staff'];
$deploymentMode = $deploymentMode ?? 'single';
$branchName     = $branchName     ?? '';

$roleLabel = ucfirst($currentUser['role'] ?? 'staff');
$userInitial = strtoupper(substr($currentUser['name'] ?? 'S', 0, 1));
?>
<style>
    #adminTopbar {
        height: 64px; background-color: #ffffff; border-bottom: 1px solid rgba(114,119,131,0.15);
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 1.25rem; position: sticky; top: 0; z-index: 40;
    }
    .profile-chip {
        display: flex; align-items: center; gap: 8px; padding: 6px 10px 6px 6px;
        border-radius: 9999px; border: 1px solid rgba(114,119,131,0.2); background: #ffffff;
        transition: background-color 0.15s ease; cursor: pointer;
    }
    .profile-chip:hover { background-color: rgba(0,71,141,0.05); }
    .profile-avatar {
        width: 30px; height: 30px; border-radius: 9999px; background: #00478d; color: #ffffff;
        display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 700;
        flex-shrink: 0;
    }
    .branch-tag {
        font-size: 11px; font-weight: 700; color: #00478d; background: rgba(0,71,141,0.08);
        padding: 4px 10px; border-radius: 9999px; white-space: nowrap;
    }
    #profileDropdown {
        opacity: 0; pointer-events: none; transform: translateY(-6px) scale(0.98);
        transition: opacity 0.15s ease, transform 0.15s ease;
    }
    #profileDropdown.open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
</style>

<header id="adminTopbar">
    <!-- Left: Page title (leaves room for the mobile hamburger from sidebar.php) -->
    <h1 class="text-base sm:text-lg font-bold text-on-surface pl-12 md:pl-0 truncate">
        <?php echo htmlspecialchars($pageTitle); ?>
    </h1>

    <!-- Right: branch label (multi-mode only) + profile chip -->
    <div class="flex items-center gap-3 shrink-0">
        <?php if ($deploymentMode === 'multi' && $branchName !== ''): ?>
            <span class="branch-tag" title="Current branch">
                <?php echo htmlspecialchars($branchName); ?>
            </span>
        <?php endif; ?>

        <div class="relative">
            <button id="profileChipBtn" class="profile-chip" onclick="toggleProfileDropdown()" aria-haspopup="true" aria-expanded="false" aria-controls="profileDropdown">
                <span class="profile-avatar"><?php echo htmlspecialchars($userInitial); ?></span>
                <span class="hidden sm:flex flex-col items-start leading-tight text-left">
                    <span class="text-xs font-bold text-on-surface"><?php echo htmlspecialchars($currentUser['name'] ?? 'Staff User'); ?></span>
                    <span class="text-[10px] font-semibold text-on-surface-variant uppercase tracking-wider"><?php echo htmlspecialchars($roleLabel); ?></span>
                </span>
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant/60">expand_more</span>
            </button>

            <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-[0_12px_32px_rgba(0,0,0,0.12)] border border-outline-variant/15 overflow-hidden z-50">
                <a href="profile.php" class="flex items-center gap-2.5 px-4 py-3 text-sm font-semibold text-on-surface hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">account_circle</span>
                    Profile
                </a>
                <div class="border-t border-outline-variant/10"></div>
                <a href="#" class="flex items-center gap-2.5 px-4 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-50 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    Log Out
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleProfileDropdown(forceState) {
        const dropdown = document.getElementById('profileDropdown');
        const btn = document.getElementById('profileChipBtn');
        if (!dropdown) return;

        const shouldOpen = typeof forceState === 'boolean' ? forceState : !dropdown.classList.contains('open');
        dropdown.classList.toggle('open', shouldOpen);
        if (btn) btn.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    }

    // Close the dropdown when clicking anywhere outside of it
    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('profileDropdown');
        const chipBtn = document.getElementById('profileChipBtn');
        if (!dropdown || !chipBtn) return;
        if (!dropdown.contains(e.target) && !chipBtn.contains(e.target)) {
            toggleProfileDropdown(false);
        }
    });

    // Close on Escape for keyboard users
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') toggleProfileDropdown(false);
    });
</script>