<?php
/**
 * admin/pages/profile.php
 * Account settings for the logged-in admin/staff user themselves
 * (not a patient record). Deliberately simple: identity + password only.
 */

$activePage = 'profile';
$pageTitle  = 'My Profile';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>My Profile - DentalCare Pro Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="../../assets/js/theme-config.js"></script>
<link rel="stylesheet" href="../../assets/css/theme-base.css">
<link rel="stylesheet" href="../../assets/css/responsive.css">
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20; }
    @keyframes shimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
    .animate-shimmer {
        background: linear-gradient(90deg, rgba(0,0,0,0.03) 25%, rgba(0,0,0,0.06) 37%, rgba(0,0,0,0.03) 63%);
        background-size: 400px 100%;
        animation: shimmer 1.4s ease-in-out infinite;
    }
    .form-input {
        width: 100%; padding: 10px 12px; border-radius: 0.5rem; background-color: #ffffff;
        border: 1.5px solid rgba(114, 119, 131, 0.3); color: #0b1c30; font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .form-input:focus { border-color: #00478d; box-shadow: 0 0 0 4px rgba(0, 71, 141, 0.08); }
    .form-input.input-error { border-color: #ba1a1a; box-shadow: 0 0 0 4px rgba(186, 26, 26, 0.07); }
    .form-input:disabled { background-color: #f2f2f5; color: #6b7280; cursor: not-allowed; }

    .field-error { color: #ba1a1a; font-size: 12px; font-weight: 600; margin-top: 4px; display: none; }
    .field-error.visible { display: flex; align-items: center; gap: 3px; }

    /* Collapsible password section */
    .collapse-toggle .chevron { transition: transform 0.2s ease; }
    .collapse-toggle.collapsed .chevron { transform: rotate(-90deg); }
    .collapse-body { overflow: hidden; display: grid; grid-template-rows: 1fr; transition: grid-template-rows 0.25s ease; }
    .collapse-body.collapsed { grid-template-rows: 0fr; }
    .collapse-body > div { overflow: hidden; min-height: 0; }

    button[disabled] { opacity: 0.65; cursor: not-allowed; }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen">

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-2xl space-y-6">

            <h1 class="text-xl sm:text-2xl font-bold text-primary">My Profile</h1>

            <!-- Loading skeleton -->
            <div id="profileSkeleton" class="space-y-4" aria-hidden="true">
                <div class="h-40 rounded-2xl bg-surface-container-lowest border border-surface-container animate-shimmer"></div>
            </div>

            <!-- Identity Form -->
            <form id="profileForm" class="hidden bg-surface-container-lowest rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.06)] p-6 space-y-4">
                <h2 class="font-bold text-base text-on-surface flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-primary text-[20px]">badge</span>
                    Account Details
                </h2>

                <div id="profileSuccessBanner" class="hidden flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-800 font-medium">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span> Profile updated successfully.
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="firstName">First Name</label>
                        <input type="text" id="firstName" class="form-input" required>
                        <p class="field-error" id="firstNameError"><span class="material-symbols-outlined text-[14px]">error</span><span></span></p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="lastName">Last Name</label>
                        <input type="text" id="lastName" class="form-input" required>
                        <p class="field-error" id="lastNameError"><span class="material-symbols-outlined text-[14px]">error</span><span></span></p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="email">Email Address</label>
                    <input type="email" id="email" class="form-input" disabled>
                    <p class="text-[11px] text-on-surface-variant/70 mt-1">Your email is tied to your account and can't be changed here. Contact an administrator if this needs to be updated.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="phone">Phone</label>
                    <input type="tel" id="phone" class="form-input" placeholder="e.g. 0917 123 4567">
                    <p class="field-error" id="phoneError"><span class="material-symbols-outlined text-[14px]">error</span><span></span></p>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" id="saveProfileBtn" class="bg-primary text-on-primary text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-on-primary-fixed-variant transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">save</span> Save Changes
                    </button>
                </div>
            </form>

            <!-- Change Password (separate, collapsible sub-form) -->
            <div id="passwordSection" class="hidden bg-surface-container-lowest rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.06)] overflow-hidden">
                <button type="button" class="collapse-toggle collapsed w-full flex items-center justify-between px-6 py-5 hover:bg-surface-container-low/60 transition-colors" onclick="togglePasswordSection(this)">
                    <span class="font-bold text-base text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">lock</span>
                        Change Password
                    </span>
                    <span class="material-symbols-outlined chevron text-on-surface-variant text-[20px]">expand_more</span>
                </button>
                <div class="collapse-body collapsed">
                    <div>
                        <form id="passwordForm" class="px-6 pb-6 space-y-4 border-t border-outline-variant/15 pt-5">

                            <div id="passwordSuccessBanner" class="hidden flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-800 font-medium">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span> Password changed successfully.
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" class="form-input" autocomplete="current-password" required>
                                <p class="field-error" id="currentPasswordError"><span class="material-symbols-outlined text-[14px]">error</span><span></span></p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="newPassword">New Password</label>
                                <input type="password" id="newPassword" class="form-input" autocomplete="new-password" required>
                                <p class="field-error" id="newPasswordError"><span class="material-symbols-outlined text-[14px]">error</span><span></span></p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" class="form-input" autocomplete="new-password" required>
                                <p class="field-error" id="confirmPasswordError"><span class="material-symbols-outlined text-[14px]">error</span><span></span></p>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="submit" id="savePasswordBtn" class="bg-primary text-on-primary text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-on-primary-fixed-variant transition-colors flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">key</span> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function showLocalToast(type, msg) {
    if (typeof showGlobalToast === 'function') {
        showGlobalToast(type, msg);
        return;
    }
    const toast = document.createElement('div');
    toast.className = `fixed bottom-5 right-5 px-5 py-3 rounded-xl text-white font-bold text-sm shadow-xl z-[200] flex items-center gap-2 transition-all transform translate-y-4 opacity-0 ${
        type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'
    }`;
    toast.innerHTML = `<span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : 'warning'}</span> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-y-4', 'opacity-0'), 50);
    setTimeout(() => {
        toast.classList.add('translate-y-4', 'opacity-0');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

function togglePasswordSection(btn) {
    btn.classList.toggle('collapsed');
    btn.nextElementSibling.classList.toggle('collapsed');
}

/**
 * Clears all inline field errors within a given form element.
 */
function clearFieldErrors(formEl) {
    formEl.querySelectorAll('.field-error').forEach(el => {
        el.classList.remove('visible');
        el.querySelector('span:last-child').textContent = '';
    });
    formEl.querySelectorAll('.form-input').forEach(el => el.classList.remove('input-error'));
}

/**
 * Applies field-level validation errors returned by the backend.
 * Expects `errors` as { field_id: "message", ... } — e.g.
 * { current_password: "Current password is incorrect." }
 */
function applyFieldErrors(formEl, errors) {
    const fieldMap = {
        first_name: 'firstName',
        last_name: 'lastName',
        phone: 'phone',
        current_password: 'currentPassword',
        new_password: 'newPassword',
        confirm_password: 'confirmPassword',
    };

    Object.entries(errors || {}).forEach(([field, message]) => {
        const inputId = fieldMap[field] ?? field;
        const input = document.getElementById(inputId);
        const errorEl = document.getElementById(`${inputId}Error`);
        if (input) input.classList.add('input-error');
        if (errorEl) {
            errorEl.querySelector('span:last-child').textContent = message;
            errorEl.classList.add('visible');
        }
    });
}

// ── Load current profile ──────────────────────────────────────────────────
async function loadProfile() {
    const skeleton = document.getElementById('profileSkeleton');
    const form = document.getElementById('profileForm');
    const passwordSection = document.getElementById('passwordSection');

    try {
        const res = await fetch('../backend/admin-profile-update.php?action=fetch');
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to load profile.');

        const admin = data.admin ?? {};
        document.getElementById('firstName').value = admin.first_name ?? '';
        document.getElementById('lastName').value = admin.last_name ?? '';
        document.getElementById('email').value = admin.email ?? '';
        document.getElementById('phone').value = admin.phone ?? '';

        skeleton.classList.add('hidden');
        form.classList.remove('hidden');
        passwordSection.classList.remove('hidden');

    } catch (err) {
        console.error('Error loading profile:', err);
        skeleton.classList.add('hidden');
        showLocalToast('error', err.message || 'Could not load your profile.');
    }
}

// ── Save identity fields ───────────────────────────────────────────────────
document.getElementById('profileForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;
    clearFieldErrors(form);
    document.getElementById('profileSuccessBanner').classList.add('hidden');

    const saveBtn = document.getElementById('saveProfileBtn');
    saveBtn.disabled = true;

    const formData = new FormData();
    formData.append('action', 'update_profile');
    formData.append('first_name', document.getElementById('firstName').value.trim());
    formData.append('last_name', document.getElementById('lastName').value.trim());
    formData.append('phone', document.getElementById('phone').value.trim());

    try {
        const res = await fetch('../backend/admin-profile-update.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) {
            if (data.errors) {
                applyFieldErrors(form, data.errors);
            } else {
                showLocalToast('error', data.message || 'Could not update your profile.');
            }
            return;
        }

        document.getElementById('profileSuccessBanner').classList.remove('hidden');
        showLocalToast('success', 'Profile updated successfully.');

    } catch (err) {
        console.error(err);
        showLocalToast('error', 'A connection error occurred. Please try again.');
    } finally {
        saveBtn.disabled = false;
    }
});

// ── Change password (independent sub-form) ────────────────────────────────
document.getElementById('passwordForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;
    clearFieldErrors(form);
    document.getElementById('passwordSuccessBanner').classList.add('hidden');

    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        applyFieldErrors(form, { confirm_password: 'New password and confirmation do not match.' });
        return;
    }

    const saveBtn = document.getElementById('savePasswordBtn');
    saveBtn.disabled = true;

    const formData = new FormData();
    formData.append('action', 'change_password');
    formData.append('current_password', document.getElementById('currentPassword').value);
    formData.append('new_password', newPassword);
    formData.append('confirm_password', confirmPassword);

    try {
        const res = await fetch('../backend/admin-profile-update.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) {
            if (data.errors) {
                applyFieldErrors(form, data.errors);
            } else {
                showLocalToast('error', data.message || 'Could not update your password.');
            }
            return;
        }

        document.getElementById('passwordSuccessBanner').classList.remove('hidden');
        form.reset();
        showLocalToast('success', 'Password changed successfully.');

    } catch (err) {
        console.error(err);
        showLocalToast('error', 'A connection error occurred. Please try again.');
    } finally {
        saveBtn.disabled = false;
    }
});

document.addEventListener('DOMContentLoaded', loadProfile);
</script>

</body>
</html>