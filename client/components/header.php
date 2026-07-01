<?php
/**
 * header.php
 * Reusable layout header for SITE_NAME.
 * Includes a modern SaaS-style account cluster with interactive dropdown and responsive search.
 *
 * Auth guard + $currentUser / $patientId / $recentSearches are normally
 * populated upstream by auth-guard.php (required at the top of
 * main-layout.php, before any HTML output). This file is markup-only.
 *
 * Defensive fallback: if this file is ever reached without going through
 * the guard (e.g. a page includes header.php directly), default these to
 * safe empty values instead of throwing undefined-variable warnings.
 */
require_once __DIR__ . '/design-config.php';

$currentUser    = $currentUser    ?? ['name' => '', 'role' => '', 'avatar_url' => '', 'unread_count' => 0];
$patientId      = $patientId      ?? '';
$recentSearches = $recentSearches ?? [];
?>

<style>
    /* CRITICAL STICKY FIX: 
       Ancestors with overflow-x: hidden break position: sticky on child elements. 
       Changing this to 'clip' safely preserves the layout boundaries while allowing the header to stick perfectly as you scroll. */
    html, body, #root, .main-layout-wrapper, #contentContainer {
        overflow-x: clip !important;
    }
</style>

<header id="header" class="flex justify-between items-center h-20 px-6 w-full bg-white text-slate-800 z-40 sticky top-0 self-start border-b border-slate-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)]" style="position: sticky; top: 0;">

    <!-- LEFT: Brand + Mobile Hamburger -->
    <div class="flex items-center space-x-4">
        <button id="mobileMenuBtn"
                onclick="toggleMobileSidebar(true)"
                aria-label="Open Navigation Drawer"
                aria-expanded="false"
                aria-controls="mainSidebar"
                class="md:hidden p-2.5 rounded-xl text-slate-600 hover:bg-slate-50 transition-all focus:outline-none focus:ring-2 focus:ring-[#1652a0]">
            <span class="material-symbols-outlined" aria-hidden="true">menu</span>
        </button>
        <div class="flex items-center md:hidden">
            <h1 class="font-semibold text-lg text-[#1652a0] tracking-tight">
                <?php echo htmlspecialchars(SITE_NAME); ?>
            </h1>
        </div>
    </div>

    <!-- CENTER: Smart Search with live filtering -->
    <div class="hidden md:flex flex-1 max-w-md relative transition-all duration-300" id="searchContainer">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xl" aria-hidden="true">search</span>

        <!-- Search input: Responsive typography (Mobile/Tablet 14px -> Desktop 15px) & color contrast placeholder-tertiary-text pass -->
        <input id="searchInput"
               type="text"
               autocomplete="off"
               role="combobox"
               aria-expanded="false"
               aria-autocomplete="list"
               aria-controls="searchSuggestions"
               aria-label="Search appointments, records, services"
               placeholder="Search appointments, records…"
               onfocus="openSearchDropdown()"
               onblur="scheduleCloseSearch()"
               oninput="filterSearchSuggestions(this.value)"
               class="w-full bg-slate-50 border border-slate-100 rounded-xl pl-11 pr-10 py-2.5 font-medium text-[14px] lg:text-[15px] text-slate-700 placeholder-tertiary-text focus:outline-none focus:border-[#1652a0]/50 focus:ring-4 focus:ring-[#1652a0]/5 focus:bg-white transition-all duration-200"/>

        <!-- Clear button (kept decorative) -->
        <button id="searchClearBtn"
                onclick="clearSearch()"
                aria-label="Clear search"
                class="hidden absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
        </button>

        <!-- Search dropdown -->
        <div id="searchDropdown"
             class="hidden absolute top-13 left-0 w-full bg-white rounded-2xl shadow-[0_16px_36px_rgba(0,0,0,0.08)] border border-slate-100 p-4 z-50">
            <!-- Label: micro-label 11px & accessible slate-600 contrast text -->
            <p id="searchDropdownLabel" class="text-[11px] font-bold text-secondary-text mb-2.5 uppercase tracking-wider">Recent Searches</p>
            <ul id="searchSuggestions" role="listbox" aria-label="Search suggestions" class="space-y-1">
                <?php foreach ($recentSearches as $s): ?>
                <li role="option">
                    <!-- Suggestion: responsive typography to 13px -->
                    <button type="button"
                            data-search-term="<?php echo htmlspecialchars(strtolower($s['label'])); ?>"
                            onclick="executeSearch('<?php echo htmlspecialchars($s['label'], ENT_QUOTES); ?>')"
                            class="search-suggestion-item w-full text-left font-medium text-[13px] px-3 py-2.5 hover:bg-slate-50 rounded-xl transition-all flex items-center justify-between text-slate-600 hover:text-[#1652a0]">
                        <span class="search-label"><?php echo htmlspecialchars($s['label']); ?></span>
                        <span class="material-symbols-outlined text-base text-slate-400" aria-hidden="true"><?php echo $s['icon']; ?></span>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
            <p id="searchNoResults" class="hidden text-[13px] text-tertiary-text text-center py-4 font-medium">No matching results</p>
        </div>
    </div>

    <!-- RIGHT: Notifications & SaaS Account Cluster -->
    <div class="flex items-center space-x-4 ml-auto">

        <!-- Notification bell (Circular button style, hover scale effect, soft shadow) -->
        <div class="relative">
            <button id="bellTriggerBtn"
                    onclick="toggleNotificationDropdown()"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="Open notifications panel"
                    class="p-2.5 text-slate-500 hover:text-[#1652a0] hover:bg-slate-50 hover:scale-105 active:scale-95 rounded-full transition-all relative focus:outline-none focus:ring-2 focus:ring-[#1652a0] shadow-sm bg-slate-50/50 border border-slate-100">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">notifications</span>
                <!-- Unread badge overlapping bell icon -->
                <span id="unreadBellBadge"
                      class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full ring-2 ring-white animate-pulse"
                      aria-hidden="true"></span>
            </button>

            <!-- Notification dropdown -->
            <div id="notificationDropdown"
                 role="dialog"
                 aria-label="Notifications"
                 class="hidden absolute right-0 mt-3 w-80 md:w-96 bg-white rounded-2xl shadow-[0_20px_48px_rgba(0,0,0,0.12)] border border-slate-100 overflow-hidden z-50 transition-all transform origin-top-right">

                <div class="p-4 bg-[#1652a0] text-white flex items-center justify-between">
                    <div>
                        <h4 class="font-semibold text-sm">Your Updates</h4>
                        <!-- Micro-label 11px standard -->
                        <p class="text-[11px] text-blue-100 mt-0.5" id="notifUnreadHeader"><?php echo intval($currentUser['unread_count'] ?? 0); ?> unread notifications</p>
                    </div>
                    <button onclick="markAllNotificationsRead()"
                            class="text-[11px] font-bold bg-white/10 hover:bg-white/20 transition-colors px-2.5 py-1.5 rounded-lg focus:outline-none">
                        Mark all read
                    </button>
                </div>

                <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto" role="list" id="notificationList">
                    <div class="p-6 text-center text-slate-400" id="notifEmptyState">
                        <span class="material-symbols-outlined text-3xl mb-2 block" aria-hidden="true">notifications_none</span>
                        <p class="text-[13px] font-medium">No notifications yet</p>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 text-center border-t border-slate-100">
                    <a href="notifications.php" class="text-[13px] font-semibold text-[#1652a0] hover:underline">View all notification history</a>
                </div>
            </div>
        </div>

        <!-- SaaS Account Cluster Dropdown Trigger -->
        <div class="relative" id="profileDropdownContainer">
            <button id="profileClusterBtn"
                    onclick="toggleProfileDropdown()"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-label="User Account Menu"
                    class="flex items-center space-x-3 p-1.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-slate-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#1652a0] select-none">
                
                <!-- Avatar Left -->
                <div class="relative flex-shrink-0">
                    <img alt="<?php echo htmlspecialchars($currentUser['name'] ?? ''); ?> — <?php echo htmlspecialchars($currentUser['role'] ?? ''); ?> Profile"
                         class="w-8 h-8 rounded-lg object-cover ring-2 ring-white"
                         src="<?php echo htmlspecialchars($currentUser['avatar_url'] ?? ''); ?>"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['name'] ?? ''); ?>&background=003164&color=fff'"/>
                </div>

                <!-- Name and Role stacked vertically -->
                <div class="hidden sm:block text-left pr-1 leading-tight">
                    <p class="font-semibold text-xs text-slate-800"><?php echo htmlspecialchars($currentUser['name'] ?? ''); ?></p>
                    <!-- Role: micro-label 11px & accessible secondary text slate-600 contrast -->
                    <p class="text-[11px] text-secondary-text font-medium"><?php echo htmlspecialchars($currentUser['role'] ?? ''); ?></p>
                </div>

                <!-- Dropdown chevron icon (kept decorative) -->
                <span class="material-symbols-outlined text-slate-400 text-lg transition-transform duration-200" id="profileChevron" aria-hidden="true">expand_more</span>
            </button>

            <!-- SaaS Interactive Dropdown Menu -->
            <div id="profileDropdown"
                 role="menu"
                 aria-label="User Actions"
                 class="hidden absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-[0_20px_48px_rgba(0,0,0,0.12)] border border-slate-100 overflow-hidden z-50 transition-all transform origin-top-right">
                
                <div class="p-4 border-b border-slate-50 bg-slate-50/40">
                    <p class="font-semibold text-xs text-slate-800"><?php echo htmlspecialchars($currentUser['name'] ?? ''); ?></p>
                    <!-- Patient ID: micro-label 11px & slate-600 contrast color -->
                    <p class="text-[11px] text-secondary-text font-medium mt-0.5">ID: #<?php echo htmlspecialchars($patientId); ?></p>
                </div>

                <!-- Profile Dropdown Link Items: typography scale set to 13px -->
                <div class="p-2 space-y-0.5">
                    <a href="profile-settings.php" role="menuitem" class="flex items-center px-3 py-2.5 rounded-xl text-[13px] font-semibold text-slate-600 hover:text-[#1652a0] hover:bg-[#e8f0fb]/40 transition-colors">
                        <span class="material-symbols-outlined text-lg mr-3 text-slate-400" aria-hidden="true">account_circle</span>
                        My Profile
                    </a>
                    <a href="profile-settings.php?tab=security" role="menuitem" class="flex items-center px-3 py-2.5 rounded-xl text-[13px] font-semibold text-slate-600 hover:text-[#1652a0] hover:bg-[#e8f0fb]/40 transition-colors">
                        <span class="material-symbols-outlined text-lg mr-3 text-slate-400" aria-hidden="true">settings</span>
                        Account Settings
                    </a>
                    <a href="support.php" role="menuitem" class="flex items-center px-3 py-2.5 rounded-xl text-[13px] font-semibold text-slate-600 hover:text-[#1652a0] hover:bg-[#e8f0fb]/40 transition-colors">
                        <span class="material-symbols-outlined text-lg mr-3 text-slate-400" aria-hidden="true">help</span>
                        Help Center
                    </a>
                </div>

                <div class="p-2 border-t border-slate-50">
                    <button onclick="triggerLogout()" role="menuitem" class="w-full flex items-center px-3 py-2.5 rounded-xl text-[13px] font-bold text-red-600 hover:bg-red-50 transition-colors">
                        <span class="material-symbols-outlined text-lg mr-3 text-red-500" aria-hidden="true">logout</span>
                        Logout
                    </button>
                </div>
            </div>
        </div>

    </div>
</header>

<div id="toastContainer"
     aria-live="polite"
     aria-atomic="false"
     class="fixed top-24 right-6 space-y-3 z-[60] w-full max-w-sm pointer-events-none"></div>

<script>
/* ════════════════════════════════════════════
   SEARCH — live client-side filtering
   ════════════════════════════════════════════ */
let _searchCloseTimer = null;

function openSearchDropdown() {
    document.getElementById('searchDropdown').classList.remove('hidden');
    document.getElementById('searchInput').setAttribute('aria-expanded', 'true');
    filterSearchSuggestions(document.getElementById('searchInput').value);
}

function scheduleCloseSearch() {
    _searchCloseTimer = setTimeout(() => {
        document.getElementById('searchDropdown').classList.add('hidden');
        document.getElementById('searchInput').setAttribute('aria-expanded', 'false');
    }, 200);
}

function filterSearchSuggestions(query) {
    const q = query.trim().toLowerCase();
    const items = document.querySelectorAll('.search-suggestion-item');
    const label = document.getElementById('searchDropdownLabel');
    const noResults = document.getElementById('searchNoResults');

    let visibleCount = 0;
    items.forEach(btn => {
        const term = btn.dataset.searchTerm || '';
        const matches = !q || term.includes(q);
        btn.closest('li').classList.toggle('hidden', !matches);
        if (matches) visibleCount++;
    });

    label.textContent = q ? 'Suggestions' : 'Recent Searches';
    noResults.classList.toggle('hidden', visibleCount > 0);
    document.getElementById('searchClearBtn').classList.toggle('hidden', !q);
}

function clearSearch() {
    const input = document.getElementById('searchInput');
    input.value = '';
    filterSearchSuggestions('');
    input.focus();
}

function executeSearch(query) {
    clearTimeout(_searchCloseTimer);
    document.getElementById('searchInput').value = query;
    filterSearchSuggestions(query);
    document.getElementById('searchDropdown').classList.add('hidden');
    showGlobalToast('info', `Searching for "${query}"…`);
}

/* ════════════════════════════════════════════
   NOTIFICATIONS
   ════════════════════════════════════════════ */
function toggleNotificationDropdown() {
    const dd = document.getElementById('notificationDropdown');
    const trigger = document.getElementById('bellTriggerBtn');
    const isOpen = !dd.classList.contains('hidden');
    
    // Close profile dropdown if open
    closeProfileDropdown();
    
    dd.classList.toggle('hidden', isOpen);
    trigger.setAttribute('aria-expanded', String(!isOpen));
}

function markAllNotificationsRead() {
    if (typeof AppState !== 'undefined' && AppState.setUnreadCount) {
        AppState.setUnreadCount(0);
    }
    const headerUnread = document.getElementById('notifUnreadHeader');
    if (headerUnread) headerUnread.textContent = '0 unread notifications';
    showGlobalToast('success', 'All notifications marked as read.');
}

/* ════════════════════════════════════════════
   SAAS ACCOUNT DROPDOWN
   ════════════════════════════════════════════ */
function toggleProfileDropdown() {
    const dd = document.getElementById('profileDropdown');
    const btn = document.getElementById('profileClusterBtn');
    const chev = document.getElementById('profileChevron');
    const isOpen = !dd.classList.contains('hidden');

    // Close notification dropdown if open
    const notifDd = document.getElementById('notificationDropdown');
    if (notifDd) notifDd.classList.add('hidden');

    if (isOpen) {
        closeProfileDropdown();
    } else {
        dd.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        chev.style.transform = 'rotate(180deg)';
    }
}

function closeProfileDropdown() {
    const dd = document.getElementById('profileDropdown');
    const btn = document.getElementById('profileClusterBtn');
    const chev = document.getElementById('profileChevron');
    if (dd) dd.classList.add('hidden');
    if (btn) btn.setAttribute('aria-expanded', 'false');
    if (chev) chev.style.transform = 'rotate(0deg)';
}

/* Close dropdowns on outside click */
document.addEventListener('click', e => {
    const bell = document.getElementById('bellTriggerBtn');
    const bellDd = document.getElementById('notificationDropdown');
    if (bell && bellDd && !bell.contains(e.target) && !bellDd.contains(e.target)) {
        bellDd.classList.add('hidden');
        bell.setAttribute('aria-expanded', 'false');
    }

    const cluster = document.getElementById('profileClusterBtn');
    const clusterDd = document.getElementById('profileDropdown');
    if (cluster && clusterDd && !cluster.contains(e.target) && !clusterDd.contains(e.target)) {
        closeProfileDropdown();
    }
});
</script>