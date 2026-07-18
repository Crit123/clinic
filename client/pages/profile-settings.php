<?php
/**
 * profile-settings.php
 * Patient Profile and Account Settings for DentalCare Pro.
 * Handles Profile, Security (including login/password), and Notifications.
 */

// 0. Make sure the real, cookie-backed session is active before anything
// below touches $_SESSION (e.g. getCsrfToken()). Without this, writes to
// $_SESSION on this page are never persisted to the actual session store,
// so the CSRF token rendered into the forms below would silently diverge
// from the one dashboard-backend.php checks against -- guarded with
// session_status() so this is a no-op if a session is already active
// (e.g. started upstream by design-config.php or main-layout.php).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. We require design-config manually at the top so $APP_ENV is available for our logic
require_once __DIR__ . '/../components/design-config.php';
require_once __DIR__ . '/../../api/helper/_api-helpers.php';

// 2. Set the variables the layout shell needs
$activePage = 'settings';
$pageTitle  = 'Profile Settings';

// Determine current initial tab via PHP query parameter
$validTabs = ['profile', 'security', 'notifications'];
$currentTab = isset($_GET['tab']) && in_array(strtolower($_GET['tab']), $validTabs) ? strtolower($_GET['tab']) : 'profile';

// Safely resolve patient session information (Mirrored from header.php).
// These are only immediate placeholders shown before loadProfileData()
// (below) overwrites every field with real values fetched from
// dashboard-backend.php?action=get_profile — nothing here is persisted
// or trusted as the source of truth.
$patientName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['patient_name']) ? $_SESSION['patient_name'] : 'Patient');
$patientId = isset($_SESSION['patient_id']) ? $_SESSION['patient_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '');

$avatarInitials = '';
if (!empty($patientName)) {
    $parts = explode(' ', $patientName);
    $avatarInitials .= strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1 && !empty($parts[count($parts) - 1])) {
        $avatarInitials .= strtoupper(substr($parts[count($parts) - 1], 0, 1));
    } else {
        $avatarInitials = strtoupper(substr($patientName, 0, 2));
    }
}
if (empty($avatarInitials)) {
    $avatarInitials = 'AJ';
}

// 3. Start intercepting standard output (Output Buffering)
ob_start();
?>

<!-- INTRO HERO BLOCK -->
<div class="fade-in">
    <div class="flex items-center gap-2 mb-1.5">
        <span class="material-symbols-outlined text-secondary text-sm">settings</span>
        <span class="font-label-sm text-xs font-semibold text-secondary-text uppercase tracking-widest">Account Management</span>
    </div>
    <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-primary">Settings</h2>
    <p class="font-body-md text-on-surface-variant mt-1">Manage your personal information, security preferences, and clinical notifications.</p>
</div>

<div class="flex flex-col lg:flex-row gap-8 fade-in delay-100">
    
    <!-- LEFT: VERTICAL TABS NAVIGATION -->
    <nav class="w-full lg:w-64 flex-shrink-0 flex flex-row lg:flex-col gap-2 overflow-x-auto scrollbar-none pb-2 lg:pb-0" aria-label="Settings Navigation" role="tablist">
        <button onclick="switchTab('profile', event)" 
                id="nav-profile"
                role="tab"
                aria-selected="<?php echo $currentTab === 'profile' ? 'true' : 'false'; ?>"
                aria-controls="pane-profile"
                class="tab-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 whitespace-nowrap lg:whitespace-normal text-left <?php echo $currentTab === 'profile' ? 'bg-[#e8f0fb] text-[#1652a0] shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'; ?>">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_circle</span>
            Public Profile
        </button>
        
        <button onclick="switchTab('security', event)" 
                id="nav-security"
                role="tab"
                aria-selected="<?php echo $currentTab === 'security' ? 'true' : 'false'; ?>"
                aria-controls="pane-security"
                class="tab-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 whitespace-nowrap lg:whitespace-normal text-left <?php echo $currentTab === 'security' ? 'bg-[#e8f0fb] text-[#1652a0] shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'; ?>">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock</span>
            Security & Login
        </button>

        <button onclick="switchTab('notifications', event)" 
                id="nav-notifications"
                role="tab"
                aria-selected="<?php echo $currentTab === 'notifications' ? 'true' : 'false'; ?>"
                aria-controls="pane-notifications"
                class="tab-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 whitespace-nowrap lg:whitespace-normal text-left <?php echo $currentTab === 'notifications' ? 'bg-[#e8f0fb] text-[#1652a0] shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'; ?>">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">notifications_active</span>
            Notifications
        </button>
    </nav>

    <!-- RIGHT: TAB CONTENT PANES -->
    <div class="flex-1 bg-surface-container-lowest rounded-2xl border border-slate-100 shadow-[0_4px_16px_rgba(0,71,141,0.02)] overflow-hidden min-h-[500px]">
        
        <!-- PANE 1: PROFILE -->
        <div id="pane-profile" class="tab-pane p-6 md:p-8 space-y-8 <?php echo $currentTab === 'profile' ? '' : 'hidden'; ?>" role="tabpanel" aria-labelledby="nav-profile">
            <div>
                <h3 class="font-headline-md text-xl text-primary font-bold mb-1">Personal Information</h3>
                <p class="text-sm text-on-surface-variant">Update your photo and personal details here.</p>
            </div>

            <form id="profileForm" onsubmit="handleProfileSubmit(event)" class="space-y-8">
                <!-- Avatar Section -->
                <div class="flex items-center gap-6 pb-6 border-b border-slate-100">
                    <div class="relative">
                        <img id="profileAvatarImg"
                             alt="<?php echo htmlspecialchars($patientName); ?> Profile Photo"
                             class="w-20 h-20 rounded-full object-cover ring-4 ring-slate-50 shadow-sm"
                             src="https://ui-avatars.com/api/?name=<?php echo urlencode($patientName); ?>&background=003164&color=fff&size=80"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($patientName); ?>&background=003164&color=fff&size=80'"/>
                        <button type="button" onclick="document.getElementById('avatarFileInput').click()" class="absolute bottom-0 right-0 w-7 h-7 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-600 hover:text-primary hover:border-primary shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary">
                            <span class="material-symbols-outlined text-[14px]">edit</span>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <input type="file" id="avatarFileInput" accept="image/jpeg,image/png,image/gif" class="hidden" onchange="handleAvatarUpload(event)">
                        <button type="button" onclick="document.getElementById('avatarFileInput').click()" class="px-4 py-2 border border-outline-variant hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1">
                            Change Photo
                        </button>
                        <p class="text-[11px] text-slate-500">JPG, GIF or PNG. 1MB max.</p>
                    </div>
                </div>

                <!-- Fields Grid -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="profileName" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Full Name</label>
                        <input type="text" id="profileName" name="full_name" value="<?php echo htmlspecialchars($patientName); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" required>
                    </div>
                    <div>
                        <label for="profileId" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Patient ID</label>
                        <input type="text" id="profileId" value="#<?php echo htmlspecialchars($patientId); ?>" disabled class="w-full bg-slate-100/50 border border-slate-100 rounded-xl px-4 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label for="profileEmail" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Email Address</label>
                        <input type="email" id="profileEmail" name="email" value="" placeholder="Loading…" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" required>
                    </div>
                    <div>
                        <label for="profilePhone" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Phone Number</label>
                        <input type="tel" id="profilePhone" name="phone" value="" placeholder="Loading…" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors">
                    </div>
                    <div class="md:col-span-2">
                        <label for="profileDob" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Date of Birth</label>
                        <input type="date" id="profileDob" name="date_of_birth" value="" class="w-full md:w-1/2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors">
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="bg-[#1652a0] hover:bg-primary-container text-white font-bold text-sm py-2.5 px-6 rounded-xl transition-all shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- PANE 2: SECURITY -->
        <div id="pane-security" class="tab-pane p-6 md:p-8 space-y-8 <?php echo $currentTab === 'security' ? '' : 'hidden'; ?>" role="tabpanel" aria-labelledby="nav-security">
            <div>
                <h3 class="font-headline-md text-xl text-primary font-bold mb-1">Security & Login</h3>
                <p class="text-sm text-on-surface-variant">Manage your password and active portal sessions.</p>
            </div>

            <!-- Change Password -->
            <form id="passwordForm" onsubmit="handlePasswordSubmit(event)" class="space-y-5 pb-8 border-b border-slate-100">
                <h4 class="font-semibold text-slate-800 text-base mb-4">Change Password</h4>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                <div class="space-y-4 max-w-md">
                    <div>
                        <label for="currentPassword" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Current Password</label>
                        <input type="password" id="currentPassword" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" required>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="newPassword" class="block text-xs font-bold text-secondary-text uppercase tracking-wide">New Password</label>
                            <span id="newPasswordCounter" class="text-[11px] font-medium text-slate-400 transition-colors duration-300">0 / 16</span>
                        </div>
                        <input type="password" id="newPassword" maxlength="16" placeholder="8–16 characters" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" oninput="checkNewPasswordStrength(this.value)" required>

                        <!-- Password Checklist (mirrors the registration page's live checklist) -->
                        <ul id="newPwChecklist" class="mt-2 space-y-1">
                            <li id="npw-rule-length" class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400 transition-colors duration-300">
                                <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                8–16 characters
                            </li>
                            <li id="npw-rule-upper" class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400 transition-colors duration-300">
                                <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                1 uppercase letter
                            </li>
                            <li id="npw-rule-lower" class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400 transition-colors duration-300">
                                <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                1 lowercase letter
                            </li>
                            <li id="npw-rule-number" class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400 transition-colors duration-300">
                                <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                1 number
                            </li>
                            <li id="npw-rule-special" class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400 transition-colors duration-300">
                                <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                1 special character
                            </li>
                        </ul>
                    </div>
                    <div>
                        <label for="confirmPassword" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Confirm New Password</label>
                        <input type="password" id="confirmPassword" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" required>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs py-2.5 px-5 rounded-xl transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 focus-visible:ring-offset-2">
                        Update Password
                    </button>
                </div>
            </form>

            <!-- Active Sessions -->
            <div class="space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <h4 class="font-semibold text-slate-800 text-base">Active Sessions</h4>
                        <p class="text-xs text-slate-500 mt-1">Devices currently logged into your patient portal.</p>
                    </div>
                    <button type="button" id="logoutOtherDevicesBtn" onclick="handleLogoutOtherSessions()" class="px-4 py-2 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-60 disabled:cursor-not-allowed">
                        Log Out All Other Devices
                    </button>
                </div>

                <div id="sessionsList" class="space-y-3">
                    <p class="text-xs text-slate-400">Loading sessions…</p>
                </div>
            </div>
        </div>

        <!-- PANE 3: NOTIFICATIONS -->
        <div id="pane-notifications" class="tab-pane p-6 md:p-8 space-y-8 <?php echo $currentTab === 'notifications' ? '' : 'hidden'; ?>" role="tabpanel" aria-labelledby="nav-notifications">
            <div>
                <h3 class="font-headline-md text-xl text-primary font-bold mb-1">Notification Preferences</h3>
                <p class="text-sm text-on-surface-variant">Control how and when the clinic communicates with you.</p>
            </div>

            <form id="notificationsForm" onsubmit="handleNotificationsSubmit(event)" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                <div class="space-y-6">
                    <!-- Appointment Reminders -->
                    <div>
                        <h4 class="font-bold text-xs text-secondary-text uppercase tracking-wider mb-4">Appointment Reminders</h4>
                        <div class="space-y-4">
                            <label class="flex items-start justify-between cursor-pointer group">
                                <div class="pr-4">
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-[#1652a0] transition-colors">Email Reminders</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Receive an email 48 hours before your scheduled visit.</p>
                                </div>
                                <div class="relative inline-flex items-center mt-1">
                                    <input type="checkbox" id="prefEmailReminders" name="email_reminders" class="sr-only peer">
                                    <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#1652a0]/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#1652a0]"></div>
                                </div>
                            </label>
                            
                            <label class="flex items-start justify-between cursor-pointer group">
                                <div class="pr-4">
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-[#1652a0] transition-colors">SMS Text Messages</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Get day-of text reminders and waitlist alerts on your mobile.</p>
                                </div>
                                <div class="relative inline-flex items-center mt-1">
                                    <input type="checkbox" id="prefSmsReminders" name="sms_reminders" class="sr-only peer">
                                    <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#1652a0]/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#1652a0]"></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <!-- Clinical Updates -->
                    <div>
                        <h4 class="font-bold text-xs text-secondary-text uppercase tracking-wider mb-4">Clinical Updates & Records</h4>
                        <div class="space-y-4">
                            <label class="flex items-start justify-between cursor-pointer group">
                                <div class="pr-4">
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-[#1652a0] transition-colors">Post-Visit Summaries</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Notify me when Dr. Santos uploads new clinical notes or X-Rays.</p>
                                </div>
                                <div class="relative inline-flex items-center mt-1">
                                    <input type="checkbox" id="prefPostVisitSummaries" name="post_visit_summaries" class="sr-only peer">
                                    <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#1652a0]/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#1652a0]"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="bg-[#1652a0] hover:bg-primary-container text-white font-bold text-sm py-2.5 px-6 rounded-xl transition-all shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- CONFIRMATION MODAL (reused for single-device and all-other-devices logout) -->
<div id="confirmModalOverlay" class="hidden fixed inset-0 bg-slate-900/40 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
        <h3 id="confirmModalTitle" class="font-bold text-base text-slate-800 mb-2">Are you sure?</h3>
        <p id="confirmModalMessage" class="text-sm text-slate-500 mb-6">Are you sure?</p>
        <div class="flex justify-end gap-3">
            <button type="button" id="confirmModalCancelBtn" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300">
                Cancel
            </button>
            <button type="button" id="confirmModalConfirmBtn" class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-red-500">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- SETTINGS PAGE INTERACTION CONTROLLER -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Initialization handled by PHP setting the active tab, but we ensure URL matches
    const currentTab = "<?php echo htmlspecialchars($currentTab, ENT_QUOTES); ?>";
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') !== currentTab) {
        urlParams.set('tab', currentTab);
        window.history.replaceState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
    }

    loadProfileData();
    loadSessions();
});

function switchTab(tabId, event) {
    if (event) event.preventDefault();
    
    // Hide all panes
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.add('hidden');
    });
    
    // Deactivate all links
    document.querySelectorAll('.tab-link').forEach(link => {
        link.setAttribute('aria-selected', 'false');
        link.className = "tab-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 whitespace-nowrap lg:whitespace-normal text-left text-slate-500 hover:text-slate-800 hover:bg-slate-50";
    });

    // Show active pane
    const activePane = document.getElementById(`pane-${tabId}`);
    if (activePane) activePane.classList.remove('hidden');

    // Activate active link
    const activeLink = document.getElementById(`nav-${tabId}`);
    if (activeLink) {
        activeLink.setAttribute('aria-selected', 'true');
        activeLink.className = "tab-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 whitespace-nowrap lg:whitespace-normal text-left bg-[#e8f0fb] text-[#1652a0] shadow-sm";
    }

    // Update URL without reloading
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.pushState({}, '', url);
}

/**
 * Populates the Profile tab and Notifications tab with real data from
 * dashboard-backend.php on page load. The PHP-rendered values above are
 * only immediate placeholders (name/id from session) shown before this
 * runs -- this is the actual source of truth.
 */
async function loadProfileData() {
    const base = '<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php';

    try {
        const [profileRes, prefsRes] = await Promise.all([
            fetch(`${base}?action=get_profile`),
            fetch(`${base}?action=get_notification_prefs`)
        ]);
        const profileData = await profileRes.json();
        const prefsData = await prefsRes.json();

        if (profileData.success && profileData.profile) {
            const p = profileData.profile;
            document.getElementById('profileName').value = `${p.first_name} ${p.last_name}`.trim();
            document.getElementById('profileEmail').value = p.email ?? '';
            document.getElementById('profileEmail').placeholder = '';
            document.getElementById('profilePhone').value = p.phone ?? '';
            document.getElementById('profilePhone').placeholder = '';
            document.getElementById('profileDob').value = p.date_of_birth ?? '';
            if (p.avatar_filename) {
                document.getElementById('profileAvatarImg').src = `<?php echo BASE_PATH; ?>/assets/uploads/avatars/${p.avatar_filename}`;
            }
        } else if (typeof showGlobalToast === 'function') {
            showGlobalToast('error', 'Unable to load your profile.');
        }

        if (prefsData.success && prefsData.preferences) {
            const prefs = prefsData.preferences;
            document.getElementById('prefEmailReminders').checked = !!Number(prefs.email_reminders);
            document.getElementById('prefSmsReminders').checked = !!Number(prefs.sms_reminders);
            document.getElementById('prefPostVisitSummaries').checked = !!Number(prefs.post_visit_summaries);
        }
    } catch (err) {
        console.error('Failed to load profile data:', err);
        if (typeof showGlobalToast === 'function') {
            showGlobalToast('error', 'Unable to load your profile right now.');
        }
    }
}

/**
 * Shared submit-button loading-state helper for the three real forms below.
 * Returns a restore() function to call in a finally block.
 */
function setButtonLoading(form, loadingText) {
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="material-symbols-outlined animate-spin text-sm mr-2">sync</span> ${loadingText}`;
    btn.classList.add('opacity-80', 'cursor-wait');
    return () => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        btn.classList.remove('opacity-80', 'cursor-wait');
    };
}

async function handleProfileSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const restore = setButtonLoading(form, 'Saving...');

    const body = new URLSearchParams();
    body.set('csrf_token', form.querySelector('input[name="csrf_token"]').value);
    body.set('first_name', document.getElementById('profileName').value.trim().split(' ')[0] || '');
    body.set('last_name', document.getElementById('profileName').value.trim().split(' ').slice(1).join(' ') || '');
    body.set('email', document.getElementById('profileEmail').value.trim());
    body.set('phone', document.getElementById('profilePhone').value.trim());
    body.set('date_of_birth', document.getElementById('profileDob').value);

    try {
        const res = await fetch('<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php?action=update_profile', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });
        const data = await res.json();
        showGlobalToast?.(data.success ? 'success' : 'error', data.message || (data.success ? 'Profile updated.' : 'Update failed.'));
    } catch (err) {
        console.error('Profile update failed:', err);
        showGlobalToast?.('error', 'Network error — please try again.');
    } finally {
        restore();
    }
}

/**
 * Live password-requirement checklist for the New Password field --
 * mirrors checkStrength() on the registration page (login.php) exactly,
 * just retargeted to this form's element ids.
 */
function checkNewPasswordStrength(val) {
    const counter = document.getElementById('newPasswordCounter');
    counter.textContent = `${val.length} / 16`;
    counter.classList.toggle('text-amber-600', val.length >= 14);
    counter.classList.toggle('text-slate-400', val.length < 14);

    const rules = [
        { id: 'npw-rule-length',  met: val.length >= 8 && val.length <= 16 },
        { id: 'npw-rule-upper',   met: /[A-Z]/.test(val) },
        { id: 'npw-rule-lower',   met: /[a-z]/.test(val) },
        { id: 'npw-rule-number',  met: /[0-9]/.test(val) },
        { id: 'npw-rule-special', met: /[^A-Za-z0-9]/.test(val) }
    ];

    const isEmpty = val.length === 0;

    rules.forEach(rule => {
        const el = document.getElementById(rule.id);
        const icon = el.querySelector('span');

        if (isEmpty) {
            el.className = 'flex items-center gap-1.5 text-[11px] font-medium text-slate-400 transition-colors duration-300';
            icon.textContent = 'radio_button_unchecked';
        } else if (rule.met) {
            el.className = 'flex items-center gap-1.5 text-[11px] font-medium text-emerald-600 transition-colors duration-300';
            icon.textContent = 'check_circle';
        } else {
            el.className = 'flex items-center gap-1.5 text-[11px] font-medium text-red-500 transition-colors duration-300';
            icon.textContent = 'cancel';
        }
    });
}

async function handlePasswordSubmit(event) {
    event.preventDefault();
    const form = event.target;

    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        showGlobalToast?.('error', 'New passwords do not match.');
        return;
    }

    // Same policy as registration (login.php) -- checked client-side first
    // so the person gets immediate feedback, but the backend re-checks
    // all of this too since client-side validation is never trusted alone.
    const pwRules = [
        { test: val => val.length >= 8 && val.length <= 16, msg: 'New password must be 8–16 characters long.' },
        { test: val => /[A-Z]/.test(val), msg: 'New password must contain at least one uppercase letter.' },
        { test: val => /[a-z]/.test(val), msg: 'New password must contain at least one lowercase letter.' },
        { test: val => /[0-9]/.test(val), msg: 'New password must contain at least one number.' },
        { test: val => /[^A-Za-z0-9]/.test(val), msg: 'New password must contain at least one special character.' },
    ];
    const failedRule = pwRules.find(r => !r.test(newPassword));
    if (failedRule) {
        showGlobalToast?.('error', failedRule.msg);
        return;
    }

    const restore = setButtonLoading(form, 'Updating...');

    const body = new URLSearchParams();
    body.set('csrf_token', form.querySelector('input[name="csrf_token"]').value);
    body.set('current_password', document.getElementById('currentPassword').value);
    body.set('new_password', newPassword);
    body.set('confirm_password', confirmPassword);

    try {
        const res = await fetch('<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php?action=change_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });
        const data = await res.json();
        showGlobalToast?.(data.success ? 'success' : 'error', data.message || (data.success ? 'Password updated.' : 'Update failed.'));
        if (data.success) {
            form.reset();
            checkNewPasswordStrength('');
        }
    } catch (err) {
        console.error('Password update failed:', err);
        showGlobalToast?.('error', 'Network error — please try again.');
    } finally {
        restore();
    }
}

/**
 * Promise-based confirmation modal, reused by both the per-device
 * "Log Out" button and "Log Out All Other Devices". Resolves true if the
 * person confirms, false if they cancel or dismiss.
 */
function showConfirmModal(title, message) {
    const overlay = document.getElementById('confirmModalOverlay');
    document.getElementById('confirmModalTitle').textContent = title;
    document.getElementById('confirmModalMessage').textContent = message;
    overlay.classList.remove('hidden');

    return new Promise(resolve => {
        const confirmBtn = document.getElementById('confirmModalConfirmBtn');
        const cancelBtn = document.getElementById('confirmModalCancelBtn');

        const cleanup = (result) => {
            overlay.classList.add('hidden');
            confirmBtn.removeEventListener('click', onConfirm);
            cancelBtn.removeEventListener('click', onCancel);
            overlay.removeEventListener('click', onOverlayClick);
            resolve(result);
        };
        const onConfirm = () => cleanup(true);
        const onCancel = () => cleanup(false);
        const onOverlayClick = (e) => { if (e.target === overlay) cleanup(false); };

        confirmBtn.addEventListener('click', onConfirm);
        cancelBtn.addEventListener('click', onCancel);
        overlay.addEventListener('click', onOverlayClick);
    });
}

/**
 * Formats a MySQL TIMESTAMP string as a friendly relative time
 * ("Active now", "5 minutes ago", "3 days ago"), matching the tone of
 * the previous hardcoded copy ("Active now" / "Last active 2 hours ago").
 */
function formatRelativeTime(timestamp) {
    const then = new Date(timestamp.replace(' ', 'T') + 'Z');
    const diffMs = Date.now() - then.getTime();
    const diffMin = Math.floor(diffMs / 60000);

    if (diffMin < 2) return 'Active now';
    if (diffMin < 60) return `Last active ${diffMin} minutes ago`;
    const diffHr = Math.floor(diffMin / 60);
    if (diffHr < 24) return `Last active ${diffHr} hour${diffHr === 1 ? '' : 's'} ago`;
    const diffDay = Math.floor(diffHr / 24);
    return `Last active ${diffDay} day${diffDay === 1 ? '' : 's'} ago`;
}

function renderSessions(sessions) {
    const container = document.getElementById('sessionsList');
    if (!sessions || sessions.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-400">No active sessions found.</p>';
        return;
    }

    container.innerHTML = sessions.map(s => {
        const label = s.device_label || 'Unknown Device';
        const icon = /iphone|ipad|android/i.test(label) ? 'smartphone' : 'computer';
        const currentBadge = s.is_current
            ? '<span class="ml-2 text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded uppercase tracking-wider">This Device</span>'
            : '';
        const bg = s.is_current ? 'bg-slate-50/50' : 'bg-white';
        const logoutBtn = s.is_current ? '' : `
            <button type="button" onclick="handleLogoutSingleSession(${s.id}, '${escapeHtml(label).replace(/'/g, "\\'")}')" class="px-3 py-1.5 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-[11px] rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 flex-shrink-0">
                Log Out
            </button>`;

        return `
            <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 ${bg}">
                <span class="material-symbols-outlined text-3xl text-slate-400">${icon}</span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-slate-800">${escapeHtml(label)}${currentBadge}</p>
                    <p class="text-xs text-slate-500 mt-0.5">${formatRelativeTime(s.last_active)}</p>
                </div>
                ${logoutBtn}
            </div>
        `;
    }).join('');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

async function loadSessions() {
    const base = '<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php';
    try {
        const res = await fetch(`${base}?action=get_sessions`);
        const data = await res.json();
        if (data.success) {
            renderSessions(data.sessions);
            const hasOtherDevices = (data.sessions || []).some(s => !s.is_current);
            document.getElementById('logoutOtherDevicesBtn').disabled = !hasOtherDevices;
        } else {
            document.getElementById('sessionsList').innerHTML = '<p class="text-xs text-red-500">Unable to load sessions.</p>';
        }
    } catch (err) {
        console.error('Failed to load sessions:', err);
        document.getElementById('sessionsList').innerHTML = '<p class="text-xs text-red-500">Unable to load sessions.</p>';
    }
}

async function handleLogoutSingleSession(sessionRowId, deviceLabel) {
    const confirmed = await showConfirmModal(
        'Log out this device?',
        `This will end the active session on "${deviceLabel}". They'll need to sign in again on that device.`
    );
    if (!confirmed) return;

    const csrfToken = document.querySelector('#passwordForm input[name="csrf_token"]').value;
    const body = new URLSearchParams();
    body.set('csrf_token', csrfToken);
    body.set('session_id', sessionRowId);

    try {
        const res = await fetch('<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php?action=logout_session', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });
        const data = await res.json();
        showGlobalToast?.(data.success ? 'success' : 'error', data.message || (data.success ? 'Device logged out.' : 'Failed to log out device.'));
        if (data.success) loadSessions();
    } catch (err) {
        console.error('Failed to log out session:', err);
        showGlobalToast?.('error', 'Network error — please try again.');
    }
}

async function handleLogoutOtherSessions() {
    const confirmed = await showConfirmModal(
        'Log out all other devices?',
        'This will end every active session except the one you\'re using right now. Those devices will need to sign in again.'
    );
    if (!confirmed) return;

    const btn = document.getElementById('logoutOtherDevicesBtn');
    const csrfToken = document.querySelector('#passwordForm input[name="csrf_token"]').value;

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm mr-2">sync</span> Logging out...';

    const body = new URLSearchParams();
    body.set('csrf_token', csrfToken);

    try {
        const res = await fetch('<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php?action=logout_other_sessions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });
        const data = await res.json();
        showGlobalToast?.(data.success ? 'success' : 'error', data.message || (data.success ? 'Logged out of all other devices.' : 'Failed to log out other devices.'));
        if (data.success) loadSessions();
    } catch (err) {
        console.error('Failed to log out other sessions:', err);
        showGlobalToast?.('error', 'Network error — please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function handleAvatarUpload(event) {
    const input = event.target;
    const file = input.files[0];
    if (!file) return;

    const maxBytes = 1 * 1024 * 1024;
    if (file.size > maxBytes) {
        showGlobalToast?.('error', 'Image must be 1MB or smaller.');
        input.value = '';
        return;
    }
    if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
        showGlobalToast?.('error', 'Only JPG, GIF, or PNG images are allowed.');
        input.value = '';
        return;
    }

    const img = document.getElementById('profileAvatarImg');
    const previousSrc = img.src;
    img.style.opacity = '0.5';

    const csrfToken = document.querySelector('#profileForm input[name="csrf_token"]').value;
    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('csrf_token', csrfToken);

    try {
        const res = await fetch('<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php?action=upload_avatar', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success && data.avatar_filename) {
            img.src = `<?php echo BASE_PATH; ?>/assets/uploads/avatars/${data.avatar_filename}?t=${Date.now()}`;
            showGlobalToast?.('success', 'Profile photo updated.');
        } else {
            img.src = previousSrc;
            showGlobalToast?.('error', data.message || 'Upload failed.');
        }
    } catch (err) {
        console.error('Avatar upload failed:', err);
        img.src = previousSrc;
        showGlobalToast?.('error', 'Network error — please try again.');
    } finally {
        img.style.opacity = '1';
        input.value = '';
    }
}

async function handleNotificationsSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const restore = setButtonLoading(form, 'Saving...');

    const body = new URLSearchParams();
    body.set('csrf_token', form.querySelector('input[name="csrf_token"]').value);
    // Checkbox absence means "off" -- only append when checked, matching
    // how update_notification_prefs interprets a missing key as 0.
    if (document.getElementById('prefEmailReminders').checked) body.set('email_reminders', '1');
    if (document.getElementById('prefSmsReminders').checked) body.set('sms_reminders', '1');
    if (document.getElementById('prefPostVisitSummaries').checked) body.set('post_visit_summaries', '1');

    try {
        const res = await fetch('<?php echo BASE_PATH; ?>/client/backend/dashboard-backend.php?action=update_notification_prefs', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });
        const data = await res.json();
        showGlobalToast?.(data.success ? 'success' : 'error', data.message || (data.success ? 'Preferences saved.' : 'Save failed.'));
    } catch (err) {
        console.error('Notification prefs update failed:', err);
        showGlobalToast?.('error', 'Network error — please try again.');
    } finally {
        restore();
    }
}
</script>

<?php
// 4. Close the buffer and save everything captured so far into $pageContent
$pageContent = ob_get_clean();

// 5. Require the layout shell, which will handle wrapping $pageContent
require_once __DIR__ . '/../components/layout/main-layout.php';
?>