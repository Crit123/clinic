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
             class="hidden absolute top-full mt-2 left-0 w-full bg-white rounded-2xl shadow-[0_16px_36px_rgba(0,0,0,0.08)] border border-slate-100 p-4 z-50">
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
            <p id="searchNoResults" class="hidden text-[13px] text-tertiary-text text-center py-4 font-medium">
                <?php echo empty($recentSearches) ? 'No recent searches yet' : 'No matching results'; ?>
            </p>
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
                <!-- Unread badge overlapping bell icon — only shown when there's something unread -->
                <span id="unreadBellBadge"
                      class="hidden absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full ring-2 ring-white animate-pulse"
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
   SEARCH — real backend-driven search across the
   user's own bookings (service, dentist, reference code).
   Shared globally (window.runPortalSearch) so sidebar.php's
   own search box can reuse the exact same logic instead of
   just forwarding focus to this field.
   ════════════════════════════════════════════ */
let _searchCloseTimer = null;
let _searchDebounceTimer = null;
const _hasRecentSearches = <?php echo empty($recentSearches) ? 'false' : 'true'; ?>;

function openSearchDropdown() {
    document.getElementById('searchDropdown').classList.remove('hidden');
    document.getElementById('searchInput').setAttribute('aria-expanded', 'true');
    renderSearchState(document.getElementById('searchInput').value);
}

function scheduleCloseSearch() {
    _searchCloseTimer = setTimeout(() => {
        document.getElementById('searchDropdown').classList.add('hidden');
        document.getElementById('searchInput').setAttribute('aria-expanded', 'false');
    }, 200);
}

/**
 * Debounced entry point wired to the search input's oninput handler.
 * Empty query -> show recent searches (static, server-rendered).
 * Non-empty query -> hit search_global (search-backend.php) after a short debounce.
 */
function filterSearchSuggestions(query) {
    clearTimeout(_searchDebounceTimer);
    const q = query.trim();
    document.getElementById('searchClearBtn').classList.toggle('hidden', !q);

    if (!q) {
        renderSearchState('');
        return;
    }

    _searchDebounceTimer = setTimeout(() => runPortalSearch(q), 250);
}

/**
 * Renders the "recent searches" state (no active query) using the
 * statically-rendered list already in the DOM, with correct empty-state
 * copy depending on whether there IS any recent-search history at all.
 */
function renderSearchState(query) {
    const label = document.getElementById('searchDropdownLabel');
    const noResults = document.getElementById('searchNoResults');
    const items = document.querySelectorAll('#searchSuggestions .search-suggestion-item');

    if (query) return; // an active query is handled by runPortalSearch instead

    label.textContent = 'Recent Searches';
    items.forEach(btn => btn.closest('li').classList.remove('hidden'));
    noResults.textContent = _hasRecentSearches ? 'No matching results' : 'No recent searches yet';
    noResults.classList.toggle('hidden', items.length > 0);
}

/**
 * Global backend search — queries search-backend.php's search_global
 * action, which fans out across bookings, services, and static portal
 * pages and returns grouped results. Generalized to accept a target
 * element set so sidebar.php's independent search box calls this exact
 * same function against its own dropdown instead of duplicating the
 * fetch/render logic. Defaults to the header's own elements.
 */
async function runPortalSearch(query, els) {
    _searchHighlightIndex = -1; // reset any stale highlight from a previous query
    els = els || {
        list: 'searchSuggestions',
        label: 'searchDropdownLabel',
        noResults: 'searchNoResults'
    };
    const listEl = document.getElementById(els.list);
    const label = document.getElementById(els.label);
    const noResults = document.getElementById(els.noResults);
    if (!listEl || !label || !noResults) return;

    // Skeleton loading state — three pulsing placeholder rows instead of
    // a "Searching…" text label, while the backend request is in flight.
    label.textContent = '';
    noResults.classList.add('hidden');
    listEl.innerHTML = Array.from({ length: 3 }).map(() => `
        <li class="px-3 py-2.5 flex items-center gap-3 animate-pulse" aria-hidden="true">
            <div class="flex-1 space-y-1.5">
                <div class="h-3 w-3/4 bg-slate-200 rounded"></div>
                <div class="h-2.5 w-1/3 bg-slate-100 rounded"></div>
            </div>
            <div class="w-5 h-5 rounded bg-slate-100 shrink-0"></div>
        </li>
    `).join('');

    try {
        const res = await fetch(`../backend/shared/search-backend.php?action=search_global&q=${encodeURIComponent(query)}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data || !data.success) throw new Error(data?.message || 'Search failed.');

        const groups = Array.isArray(data.groups) ? data.groups : [];

        if (groups.length === 0) {
            listEl.innerHTML = '';
            label.textContent = 'Search Results';
            noResults.textContent = 'No matching results';
            noResults.classList.remove('hidden');
            return;
        }

        noResults.classList.add('hidden');
        label.textContent = 'Search Results';

        listEl.innerHTML = groups.map(group => `
            <li class="pointer-events-none px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                ${escapeHtml(group.label)}
            </li>
            ${group.items.map(item => `
                <li role="option">
                    <a href="${escapeHtml(item.url)}"
                       class="search-suggestion-item w-full text-left font-medium text-[13px] px-3 py-2.5 hover:bg-slate-50 rounded-xl transition-all flex items-center justify-between text-slate-600 hover:text-[#1652a0]">
                        <span class="search-label">
                            ${escapeHtml(item.label)}
                            ${item.sublabel ? `<span class="block text-[11px] text-slate-400 font-mono mt-0.5">${escapeHtml(item.sublabel)}</span>` : ''}
                        </span>
                        <span class="material-symbols-outlined text-base text-slate-400" aria-hidden="true">${escapeHtml(item.icon || 'chevron_right')}</span>
                    </a>
                </li>
            `).join('')}
        `).join('');
    } catch (err) {
        console.error('Search failed:', err);
        label.textContent = 'Search Results';
        listEl.innerHTML = '';
        noResults.textContent = 'Something went wrong searching. Please try again.';
        noResults.classList.remove('hidden');
    }
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = String(str ?? '');
    return div.innerHTML;
}

function clearSearch() {
    const input = document.getElementById('searchInput');
    input.value = '';
    renderSearchState('');
    input.focus();
}

function executeSearch(query) {
    clearTimeout(_searchCloseTimer);
    document.getElementById('searchInput').value = query;
    runPortalSearch(query);
}

/* ════════════════════════════════════════════
   SEARCH KEYBOARD SHORTCUTS
   - Ctrl/Cmd+K from anywhere on the page: jump to & open search.
   - Escape while search is focused: close dropdown and blur (doesn't
     touch Escape's other use in sidebar.php for the mobile drawer).
   - Arrow Up/Down: move a visual highlight through result rows.
   - Enter: activate the currently highlighted row.
   None of this calls preventDefault() on anything but these specific
   keys, so native text-editing shortcuts (Ctrl+A select-all, Ctrl+C/V,
   Home/End, etc.) inside the input are completely untouched and work
   exactly as the browser normally handles them.
   ════════════════════════════════════════════ */
let _searchHighlightIndex = -1;

function getSearchResultRows() {
    return Array.from(document.querySelectorAll('#searchSuggestions .search-suggestion-item'))
        .filter(el => !el.closest('li').classList.contains('hidden'));
}

function setSearchHighlight(index) {
    const rows = getSearchResultRows();
    rows.forEach(r => r.classList.remove('bg-slate-50', 'text-[#1652a0]'));
    if (index < 0 || index >= rows.length) {
        _searchHighlightIndex = -1;
        return;
    }
    _searchHighlightIndex = index;
    rows[index].classList.add('bg-slate-50', 'text-[#1652a0]');
    rows[index].scrollIntoView({ block: 'nearest' });
}

document.getElementById('searchInput')?.addEventListener('keydown', (e) => {
    const dropdown = document.getElementById('searchDropdown');
    const isOpen = !dropdown.classList.contains('hidden');

    if (e.key === 'Escape') {
        if (isOpen) {
            e.preventDefault();
            dropdown.classList.add('hidden');
            e.target.setAttribute('aria-expanded', 'false');
            e.target.blur();
        }
        return;
    }

    if (!isOpen) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        const rows = getSearchResultRows();
        setSearchHighlight(Math.min(_searchHighlightIndex + 1, rows.length - 1));
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        setSearchHighlight(Math.max(_searchHighlightIndex - 1, 0));
    } else if (e.key === 'Enter') {
        const rows = getSearchResultRows();
        if (_searchHighlightIndex >= 0 && rows[_searchHighlightIndex]) {
            e.preventDefault();
            rows[_searchHighlightIndex].click();
        }
    }
});

// Ctrl/Cmd+K from anywhere on the page jumps straight to search —
// only intercepts that one combination, everything else passes through untouched.
document.addEventListener('keydown', (e) => {
    const isSearchShortcut = (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k';
    if (!isSearchShortcut) return;

    const input = document.getElementById('searchInput');
    if (!input || input.offsetParent === null) return; // hidden on mobile, nothing to focus

    e.preventDefault();
    input.focus();
    openSearchDropdown();
});

// Reset highlight whenever new results render, so stale highlight state
// from a previous query doesn't carry over.
const _originalRunPortalSearch = runPortalSearch;
runPortalSearch = async function (query, els) {
    _searchHighlightIndex = -1;
    return _originalRunPortalSearch(query, els);
};

// Exposed globally so sidebar.php's own search input can drive the
// exact same dropdown + backend call instead of just re-focusing this field.
window.runPortalSearch = runPortalSearch;
window.filterSearchSuggestions = filterSearchSuggestions;
window.openSearchDropdown = openSearchDropdown;
window.scheduleCloseSearch = scheduleCloseSearch;
window.clearSearch = clearSearch;

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
    document.getElementById('unreadBellBadge')?.classList.add('hidden');
    showGlobalToast('success', 'All notifications marked as read.');
}

/**
 * Pulls real notification data from dashboard-backend.php (upcoming-soon
 * appointment alert + pending/unconfirmed booking count) and renders it
 * into the bell dropdown. Replaces the previously-static empty-state-only
 * dropdown. Falls back to the empty state silently on any fetch failure —
 * notifications are non-critical, so we don't want a failed request here
 * throwing errors elsewhere on the page.
 */
async function loadHeaderNotifications() {
    const listEl = document.getElementById('notificationList');
    const badgeEl = document.getElementById('unreadBellBadge');
    const headerCountEl = document.getElementById('notifUnreadHeader');
    if (!listEl) return;

    try {
        const res = await fetch('<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php?action=get_notifications');
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data || !data.success || !data.notifications) throw new Error(data?.message || 'Unknown error');

        const { upcoming_soon, pending_unconfirmed } = data.notifications;
        const items = [];

        if (upcoming_soon?.has_alert && upcoming_soon.booking) {
            const b = upcoming_soon.booking;
            items.push(`
                <div class="p-4 hover:bg-slate-50 transition-colors flex items-start gap-3">
                    <span class="material-symbols-outlined text-[#1652a0] text-xl mt-0.5" aria-hidden="true">event_upcoming</span>
                    <div>
                        <p class="text-[13px] font-semibold text-slate-800">Upcoming appointment soon</p>
                        <p class="text-[12px] text-slate-500 mt-0.5">${b.service_key ?? 'Your appointment'} on ${b.appointment_date} at ${b.appointment_time}</p>
                    </div>
                </div>
            `);
        }

        if (pending_unconfirmed?.has_alert && pending_unconfirmed.count > 0) {
            items.push(`
                <div class="p-4 hover:bg-slate-50 transition-colors flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-600 text-xl mt-0.5" aria-hidden="true">pending_actions</span>
                    <div>
                        <p class="text-[13px] font-semibold text-slate-800">Pending confirmation</p>
                        <p class="text-[12px] text-slate-500 mt-0.5">${pending_unconfirmed.count} booking${pending_unconfirmed.count > 1 ? 's' : ''} awaiting clinic confirmation.</p>
                    </div>
                </div>
            `);
        }

        const unreadCount = items.length;

        if (unreadCount > 0) {
            listEl.innerHTML = items.join('');
            badgeEl?.classList.remove('hidden');
        } else {
            listEl.innerHTML = `
                <div class="p-6 text-center text-slate-400" id="notifEmptyState">
                    <span class="material-symbols-outlined text-3xl mb-2 block" aria-hidden="true">notifications_none</span>
                    <p class="text-[13px] font-medium">No notifications yet</p>
                </div>
            `;
            badgeEl?.classList.add('hidden');
        }

        if (headerCountEl) {
            headerCountEl.textContent = `${unreadCount} unread notification${unreadCount === 1 ? '' : 's'}`;
        }
    } catch (err) {
        // Non-critical — leave the default empty state in place.
        console.error('Failed to load notifications:', err);
    }
}

document.addEventListener('DOMContentLoaded', loadHeaderNotifications);

/**
 * Fallback logout handler. sidebar.php defines its own triggerLogout()
 * with a toast + delayed redirect; if header.php is ever included on a
 * page without sidebar.php, this guarantees the logout button in the
 * account dropdown still works instead of silently doing nothing.
 */
if (typeof triggerLogout !== 'function') {
    function triggerLogout() {
        if (typeof showGlobalToast === 'function') {
            showGlobalToast('info', 'Logging out safely… Redirecting in a moment.');
        }
        setTimeout(() => { window.location.href = '<?php echo BASE_PATH; ?>/client/pages/logout.php'; }, 1500);
    }
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