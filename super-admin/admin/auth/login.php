<?php
// Standalone auth screen for staff/admin. No session bootstrapping here beyond
// redirecting already-authenticated admins away — actual authentication is
// handled by admin/backend/admin-login.php via fetch.
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: ../pages/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Staff & Admin Access - DentalCare Pro</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="../../assets/js/theme-config.js"></script>
<link rel="stylesheet" href="../../assets/css/theme-base.css">
<link rel="stylesheet" href="../../assets/css/responsive.css">
<style>
    /* Input base — reused from the patient-facing auth screen for consistency */
    .auth-input {
        width: 100%; padding: 11px 16px; border-radius: 0.5rem; background-color: #ffffff;
        border: 1.5px solid rgba(114, 119, 131, 0.3); color: #0b1c30; font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .auth-input:focus { border-color: #00478d; box-shadow: 0 0 0 4px rgba(0, 71, 141, 0.08); }
    .auth-input.input-error { border-color: #ba1a1a; box-shadow: 0 0 0 4px rgba(186, 26, 26, 0.07); }

    /* Decorative blobs */
    .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.12; pointer-events: none; }

    /* Staff badge accent stripe on the card — visually distinct from patient login */
    .staff-card-accent {
        background: linear-gradient(90deg, #00478d 0%, #0a5fb4 50%, #00478d 100%);
        height: 5px;
    }

    #submit-btn:disabled { opacity: 0.65; cursor: not-allowed; }

    @keyframes subtleShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
    }
    .shake-anim { animation: subtleShake 0.3s cubic-bezier(0.36, 0.07, 0.19, 0.97) both; }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen flex flex-col overflow-x-hidden">

<!-- No sidebar / nav: standalone auth screen -->
<main class="flex-grow flex items-center justify-center py-16 px-4 relative overflow-hidden">
    <!-- Decorative background blobs -->
    <div class="blob w-[500px] h-[500px] bg-primary top-0 -left-40 z-0"></div>
    <div class="blob w-[400px] h-[400px] bg-primary bottom-10 -right-32 z-0"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Card -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-[0_8px_40px_rgba(0,71,141,0.1)] border border-outline-variant/20 overflow-hidden">
            <div class="staff-card-accent"></div>

            <!-- Card header -->
            <div class="px-8 pt-8 pb-5 text-center border-b border-outline-variant/20">
                <!-- Shield icon circle to visually distinguish from patient login's tooth icon -->
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-3">
                    <span class="icon-line text-[26px]" style="color:#00478d;">shield_person</span>
                </div>
                <h1 class="text-lg font-bold text-on-surface">Staff &amp; Admin Access</h1>
                <p class="text-sm text-on-surface-variant mt-0.5">Sign in with your clinic-issued credentials</p>
            </div>

            <div class="px-8 pt-6 pb-8">
                <!-- Inline error banner for invalid credentials / backend errors -->
                <div id="login-error-banner" class="hidden mb-4 flex items-start gap-2 rounded-lg border border-error/30 bg-error/5 px-3.5 py-2.5 text-sm text-error font-medium">
                    <span class="icon-line text-[18px] mt-0.5">error</span>
                    <span id="login-error-text">Invalid email or password.</span>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="admin-email">Email Address</label>
                        <div class="relative">
                            <input class="auth-input pr-10" id="admin-email" type="email" autocomplete="username" placeholder="staff@dentalcarepro.com"/>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 icon-line text-on-surface-variant/40 text-[18px]">mail</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider" for="admin-password">Password</label>
                            <a href="#" class="text-xs font-semibold text-primary hover:underline">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <input class="auth-input pr-10" id="admin-password" type="password" autocomplete="current-password" placeholder="••••••••"/>
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 hover:text-on-surface-variant transition-colors focus:outline-none" onclick="togglePassword()">
                                <span class="icon-line text-[18px]" id="toggle-password-icon">visibility</span>
                            </button>
                        </div>
                    </div>

                    <button id="submit-btn" class="w-full h-11 rounded-lg bg-primary text-on-primary font-bold text-sm hover:bg-on-primary-fixed-variant active:scale-[0.99] transition-all duration-200 shadow-sm flex items-center justify-center gap-2 mt-1" onclick="handleAdminLogin()">
                        <span class="icon-line text-[18px]" id="submit-icon">login</span>
                        <span id="submit-label">Sign In</span>
                    </button>
                </div>

                <p class="text-center text-[11px] text-on-surface-variant/70 mt-6 leading-relaxed">
                    This portal is restricted to authorized clinic staff and administrators.<br/>
                    Unauthorized access attempts are logged.
                </p>
            </div>
        </div>
    </div>
</main>

<script>
    function togglePassword() {
        const field = document.getElementById('admin-password');
        const icon = document.getElementById('toggle-password-icon');
        const isHidden = field.type === 'password';
        field.type = isHidden ? 'text' : 'password';
        icon.textContent = isHidden ? 'visibility_off' : 'visibility';
    }

    function showError(message) {
        const banner = document.getElementById('login-error-banner');
        const text = document.getElementById('login-error-text');
        text.textContent = message;
        banner.classList.remove('hidden');

        banner.classList.remove('shake-anim');
        void banner.offsetWidth; // force reflow so the shake can re-trigger
        banner.classList.add('shake-anim');

        document.getElementById('admin-email').classList.add('input-error');
        document.getElementById('admin-password').classList.add('input-error');
    }

    function clearError() {
        document.getElementById('login-error-banner').classList.add('hidden');
        document.getElementById('admin-email').classList.remove('input-error');
        document.getElementById('admin-password').classList.remove('input-error');
    }

    function setLoading(isLoading) {
        const btn = document.getElementById('submit-btn');
        const icon = document.getElementById('submit-icon');
        const label = document.getElementById('submit-label');
        btn.disabled = isLoading;
        icon.textContent = isLoading ? 'progress_activity' : 'login';
        icon.classList.toggle('animate-spin', isLoading);
        label.textContent = isLoading ? 'Signing in…' : 'Sign In';
    }

    function handleAdminLogin() {
        clearError();

        const email = document.getElementById('admin-email').value.trim();
        const password = document.getElementById('admin-password').value;

        if (!email || !password) {
            showError('Please enter both your email and password.');
            return;
        }

        setLoading(true);

        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);

        fetch('../backend/admin-login.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '../pages/dashboard.php';
            } else {
                setLoading(false);
                showError(data.message || 'Invalid email or password.');
            }
        })
        .catch(() => {
            setLoading(false);
            showError('A connection error occurred. Please try again.');
        });
    }

    // Allow Enter key submission from either field
    document.getElementById('admin-email').addEventListener('keydown', e => {
        if (e.key === 'Enter') handleAdminLogin();
    });
    document.getElementById('admin-password').addEventListener('keydown', e => {
        if (e.key === 'Enter') handleAdminLogin();
    });
</script>

</body>
</html>