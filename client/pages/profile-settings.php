<?php
/**
 * profile-settings.php
 * Patient Profile and Account Settings for DentalCare Pro.
 * Handles Profile, Security, Notifications, and Billing preferences.
 */

// 1. We require design-config manually at the top so $APP_ENV is available for our logic
require_once __DIR__ . '/../components/design-config.php';

// 2. Set the variables the layout shell needs
$activePage = 'settings';
$pageTitle  = 'Profile Settings';

// Determine current initial tab via PHP query parameter
$validTabs = ['profile', 'security', 'notifications', 'billing'];
$currentTab = isset($_GET['tab']) && in_array(strtolower($_GET['tab']), $validTabs) ? strtolower($_GET['tab']) : 'profile';

// Safely resolve patient session information (Mirrored from header.php)
$patientName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['patient_name']) ? $_SESSION['patient_name'] : 'Alex Johnson');
$patientId = isset($_SESSION['patient_id']) ? $_SESSION['patient_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '00421');
$patientEmail = 'alex.johnson@example.com';
$patientPhone = '(555) 123-4567';
$patientDOB = '1985-08-15';

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

        <button onclick="switchTab('billing', event)" 
                id="nav-billing"
                role="tab"
                aria-selected="<?php echo $currentTab === 'billing' ? 'true' : 'false'; ?>"
                aria-controls="pane-billing"
                class="tab-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 whitespace-nowrap lg:whitespace-normal text-left <?php echo $currentTab === 'billing' ? 'bg-[#e8f0fb] text-[#1652a0] shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'; ?>">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">credit_card</span>
            Insurance & Billing
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

            <form onsubmit="handleFormSubmit(event, 'Profile updated successfully.')" class="space-y-8">
                <!-- Avatar Section -->
                <div class="flex items-center gap-6 pb-6 border-b border-slate-100">
                    <div class="relative">
                        <img alt="<?php echo htmlspecialchars($patientName); ?> Profile Photo"
                             class="w-20 h-20 rounded-full object-cover ring-4 ring-slate-50 shadow-sm"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuCiz9deCcKfGUICFRnsYwNVqYTsXtGQ3mO_BIBIxErj4QlJjjxEV32msP9qneqfld9owK6LCudOo3dgY5nsss9xXjZgi9RCZEhx8YUhfgB_WS6mafkEiTA_Cn7XojPOAVhWnD9IWtULTsZd2q43GUWYoPnfL_kw2GVZ1KdlpqTvBO3vu1Y1X81RFqdrtpc4A-OSeKxWKWMDsAm656Y2Xoe4UnfYWML486gVL3ZE91gFTPZoSwvBuhQV4uJXkzRT3PKwSzIu70PTPuQ0"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($patientName); ?>&background=003164&color=fff&size=80'"/>
                        <button type="button" class="absolute bottom-0 right-0 w-7 h-7 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-600 hover:text-primary hover:border-primary shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary">
                            <span class="material-symbols-outlined text-[14px]">edit</span>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <button type="button" onclick="showGlobalToast('info', 'File browser simulated.')" class="px-4 py-2 border border-outline-variant hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1">
                            Change Photo
                        </button>
                        <p class="text-[11px] text-slate-500">JPG, GIF or PNG. 1MB max.</p>
                    </div>
                </div>

                <!-- Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="profileName" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Full Name</label>
                        <input type="text" id="profileName" value="<?php echo htmlspecialchars($patientName); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" required>
                    </div>
                    <div>
                        <label for="profileId" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Patient ID</label>
                        <input type="text" id="profileId" value="#<?php echo htmlspecialchars($patientId); ?>" disabled class="w-full bg-slate-100/50 border border-slate-100 rounded-xl px-4 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label for="profileEmail" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Email Address</label>
                        <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($patientEmail); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" required>
                    </div>
                    <div>
                        <label for="profilePhone" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Phone Number</label>
                        <input type="tel" id="profilePhone" value="<?php echo htmlspecialchars($patientPhone); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors">
                    </div>
                    <div class="md:col-span-2">
                        <label for="profileDob" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Date of Birth</label>
                        <input type="date" id="profileDob" value="<?php echo htmlspecialchars($patientDOB); ?>" class="w-full md:w-1/2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors">
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
            <form onsubmit="handleFormSubmit(event, 'Password successfully updated.')" class="space-y-5 pb-8 border-b border-slate-100">
                <h4 class="font-semibold text-slate-800 text-base mb-4">Change Password</h4>
                <div class="space-y-4 max-w-md">
                    <div>
                        <label for="currentPassword" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">Current Password</label>
                        <input type="password" id="currentPassword" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" required>
                    </div>
                    <div>
                        <label for="newPassword" class="block text-xs font-bold text-secondary-text mb-1.5 uppercase tracking-wide">New Password</label>
                        <input type="password" id="newPassword" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1652a0] focus:ring-2 focus:ring-[#1652a0]/20 transition-colors" required>
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
                    <button type="button" onclick="showGlobalToast('success', 'Logged out of all other devices.')" class="px-4 py-2 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500">
                        Log Out All Other Devices
                    </button>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="material-symbols-outlined text-3xl text-slate-400">computer</span>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-800">MacBook Pro · Safari <span class="ml-2 text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded uppercase tracking-wider">This Device</span></p>
                            <p class="text-xs text-slate-500 mt-0.5">San Francisco, CA · Active now</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 bg-white">
                        <span class="material-symbols-outlined text-3xl text-slate-400">smartphone</span>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-800">iPhone 14 Pro · Chrome</p>
                            <p class="text-xs text-slate-500 mt-0.5">San Francisco, CA · Last active 2 hours ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANE 3: NOTIFICATIONS -->
        <div id="pane-notifications" class="tab-pane p-6 md:p-8 space-y-8 <?php echo $currentTab === 'notifications' ? '' : 'hidden'; ?>" role="tabpanel" aria-labelledby="nav-notifications">
            <div>
                <h3 class="font-headline-md text-xl text-primary font-bold mb-1">Notification Preferences</h3>
                <p class="text-sm text-on-surface-variant">Control how and when the clinic communicates with you.</p>
            </div>

            <form onsubmit="handleFormSubmit(event, 'Notification preferences saved.')" class="space-y-8">
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
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#1652a0]/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#1652a0]"></div>
                                </div>
                            </label>
                            
                            <label class="flex items-start justify-between cursor-pointer group">
                                <div class="pr-4">
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-[#1652a0] transition-colors">SMS Text Messages</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Get day-of text reminders and waitlist alerts on your mobile.</p>
                                </div>
                                <div class="relative inline-flex items-center mt-1">
                                    <input type="checkbox" class="sr-only peer" checked>
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
                                    <input type="checkbox" class="sr-only peer" checked>
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

        <!-- PANE 4: INSURANCE & BILLING -->
        <div id="pane-billing" class="tab-pane p-6 md:p-8 space-y-8 <?php echo $currentTab === 'billing' ? '' : 'hidden'; ?>" role="tabpanel" aria-labelledby="nav-billing">
            <div>
                <h3 class="font-headline-md text-xl text-primary font-bold mb-1">Insurance & Billing</h3>
                <p class="text-sm text-on-surface-variant">View your active coverage and payment methods on file.</p>
            </div>

            <!-- Primary Insurance Card (Styling mimics appointments.php cost_status) -->
            <div class="space-y-4 pb-6 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-slate-800 text-base">Primary Insurance Coverage</h4>
                    <button type="button" onclick="showGlobalToast('info', 'Feature simulated. Would open insurance upload form.')" class="text-xs font-bold text-[#1652a0] hover:text-primary-container transition-colors">Update Insurance</button>
                </div>
                
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <!-- Decorative background shape -->
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center border border-indigo-100">
                                <span class="material-symbols-outlined">health_and_safety</span>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-800 text-lg leading-tight">SmileShield Premium</h5>
                                <p class="text-xs text-slate-500">PPO Dental Network</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Member ID</p>
                                <p class="font-medium text-slate-700">SSP-982-4551</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Group Number</p>
                                <p class="font-medium text-slate-700">1042-A</p>
                            </div>
                        </div>

                        <!-- Emulating the appointments.php badge -->
                        <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100/50">
                            <span class="material-symbols-outlined text-sm text-emerald-600">verified</span>
                            <span>Status: Active & Verified</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-slate-800 text-base">Payment Methods</h4>
                    <button type="button" onclick="showGlobalToast('info', 'Feature simulated. Would open add card modal.')" class="text-xs font-bold text-[#1652a0] hover:text-primary-container transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span> Add Card
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-8 bg-slate-800 rounded flex items-center justify-center text-white font-bold text-xs italic shadow-sm">
                            VISA
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Visa ending in 4242</p>
                            <p class="text-xs text-slate-500">Expires 10/28</p>
                        </div>
                    </div>
                    <button type="button" onclick="showGlobalToast('error', 'Cannot remove primary payment method.')" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors focus:outline-none" aria-label="Remove card">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>
            </div>
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

function handleFormSubmit(event, successMessage) {
    event.preventDefault();
    // Simulate API delay
    const btn = event.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm mr-2">sync</span> Saving...';
    btn.classList.add('opacity-80', 'cursor-wait');

    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        btn.classList.remove('opacity-80', 'cursor-wait');
        
        // Use layout.php's global toast function
        if (typeof showGlobalToast === 'function') {
            showGlobalToast('success', successMessage);
        } else {
            alert(successMessage); // Fallback
        }
    }, 600);
}
</script>

<?php
// 4. Close the buffer and save everything captured so far into $pageContent
$pageContent = ob_get_clean();

// 5. Require the layout shell, which will handle wrapping $pageContent
require_once __DIR__ . '/../components/layout/main-layout.php';
?>