<?php
// Shared session so this page and forgot-password.php's AJAX endpoints
// (request_otp / verify_otp / reset_password) see the same OTP state.
session_start();
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../api/helper/_api-helpers.php';

// Generate CSRF token for the page
$csrfToken = getCsrfToken();

// Attempt to load Google Config for the Frontend Client ID
$googleClientId = '290710438488-6o84anqq9a8oauq0t5sodn90ae7ou3km.apps.googleusercontent.com'; // Fallback Placeholder
if (file_exists(__DIR__ . '/../config/google-auth.php')) {
    require_once __DIR__ . '/../config/google-auth.php';
    if (defined('GOOGLE_CLIENT_ID')) {
        $googleClientId = GOOGLE_CLIENT_ID;
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, string $message, array $extra = []): void {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }
}

// ── POST Handlers (AJAX Endpoints) ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];

    // Validate CSRF Token for all state-changing actions
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        jsonResponse(false, 'Invalid or missing CSRF token.');
    }

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Rate limiting: max 5 attempts per 5 minutes (300 seconds)
        if (!checkRateLimit($pdo, $email, 'login', 5, 300)) {
            jsonResponse(false, 'Too many login attempts. Please try again in 5 minutes.');
        }

        $stmt = $pdo->prepare("SELECT id, first_name, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            jsonResponse(true, 'Login successful', ['redirect' => '../client/pages/dashboard.php']);
        } else {
            jsonResponse(false, 'Invalid email or password.');
        }
    }

    if ($action === 'register') {
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            jsonResponse(false, 'Email is already registered.');
        }

        // Server-side password policy: 8-16 chars, uppercase, lowercase, number, special character
        $pwLen = strlen($password);
        if ($pwLen < 8 || $pwLen > 16) {
            jsonResponse(false, 'Password must be 8–16 characters long.');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            jsonResponse(false, 'Password must contain at least one uppercase letter.');
        }
        if (!preg_match('/[a-z]/', $password)) {
            jsonResponse(false, 'Password must contain at least one lowercase letter.');
        }
        if (!preg_match('/[0-9]/', $password)) {
            jsonResponse(false, 'Password must contain at least one number.');
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            jsonResponse(false, 'Password must contain at least one special character.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$firstName, $lastName, $email, $hash])) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $firstName;

            // Auto-link existing guest bookings under this email (case-insensitive)
            try {
                $updateStmt = $pdo->prepare("UPDATE bookings SET user_id = ? WHERE LOWER(email) = LOWER(?) AND user_id IS NULL");
                $updateStmt->execute([$_SESSION['user_id'], $email]);
            } catch (PDOException $e) {
                // Log failure but do not break or block the user's successful registration flow
                error_log("Failed to link guest bookings for email " . $email . " on registration: " . $e->getMessage());
            }

            jsonResponse(true, 'Registration successful', ['redirect' => '../client/pages/login/dashboard.php']);
        } else {
            jsonResponse(false, 'Registration failed.');
        }
    }

    if ($action === 'remember_me') {
        if (isset($_SESSION['user_id'])) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            
            // Store the hashed token and set an explicit expiry date (+30 days)
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, remember_token_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?");
            $stmt->execute([$tokenHash, $_SESSION['user_id']]);
            
            // Send the raw token to the user
            setcookie('remember_token', $token, time() + 60*60*24*30, '/', '', true, true);
            
            jsonResponse(true, 'Remember token set');
        }
        jsonResponse(false, 'Not logged in');
    }
}

// ── GET Page Load Checks ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If already logged in, redirect away from login page
    if (isset($_SESSION['user_id'])) {
        header("Location: ../client/pages/dashboard.php");
        exit;
    } 
    // Check "Remember Me" Cookie
    elseif (isset($_COOKIE['remember_token'])) {
        $tokenHash = hash('sha256', $_COOKIE['remember_token']);
        
        // Ensure token exists and hasn't expired
        $stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE remember_token = ? AND remember_token_expires_at > NOW() LIMIT 1");
        $stmt->execute([$tokenHash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            header("Location: ../client/pages/dashboard.php");
            exit;
        } else {
            // Token invalid or expired: clear the cookie and remove from DB
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            $clearStmt = $pdo->prepare("UPDATE users SET remember_token = NULL, remember_token_expires_at = NULL WHERE remember_token = ?");
            $clearStmt->execute([$tokenHash]);
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login / Register - DentalCare Pro</title>

<!-- Google Identity Services Script -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                "colors": {
                    "on-background": "#0b1c30",
                    "on-primary-fixed": "#001b3d",
                    "surface-container-high": "#dce9ff",
                    "on-secondary-fixed": "#191c1e",
                    "on-surface": "#0b1c30",
                    "secondary-fixed-dim": "#c4c7c9",
                    "on-primary-fixed-variant": "#00468c",
                    "secondary": "#5c5f61",
                    "surface-container-lowest": "#ffffff",
                    "background": "#f8f9ff",
                    "surface-tint": "#005db6",
                    "on-tertiary-fixed": "#002113",
                    "error": "#ba1a1a",
                    "primary-container": "#005eb8",
                    "inverse-primary": "#a9c7ff",
                    "surface-container": "#e5eeff",
                    "on-tertiary-container": "#65f2b5",
                    "surface-container-highest": "#d3e4fe",
                    "outline": "#727783",
                    "on-primary": "#ffffff",
                    "outline-variant": "#c2c6d4",
                    "inverse-on-surface": "#eaf1ff",
                    "primary-fixed-dim": "#a9c7ff",
                    "surface-bright": "#f8f9ff",
                    "on-primary-container": "#c8daff",
                    "on-tertiary-fixed-variant": "#005236",
                    "primary": "#00478d",
                    "error-container": "#ffdad6",
                    "on-secondary": "#ffffff",
                    "surface-container-low": "#eff4ff",
                    "tertiary-fixed": "#6ffbbe",
                    "on-surface-variant": "#424752",
                    "on-tertiary": "#ffffff",
                    "tertiary-fixed-dim": "#4edea3",
                    "tertiary": "#005237",
                    "surface": "#f8f9ff",
                    "on-error-container": "#93000a",
                    "surface-variant": "#d3e4fe",
                    "surface-dim": "#cbdbf5",
                    "on-secondary-fixed-variant": "#444749",
                    "primary-fixed": "#d6e3ff",
                    "inverse-surface": "#213145",
                    "on-error": "#ffffff",
                    "secondary-fixed": "#e0e3e5",
                    "secondary-container": "#e0e3e5",
                    "tertiary-container": "#006d4a",
                    "on-secondary-container": "#626567"
                },
                "fontFamily": {
                    "body-md":  ["Inter"]
                }
            }
        }
    }
</script>
<style>
    /* Navigation Underline Animation */
    .nav-link { position: relative; text-decoration: none; padding-bottom: 4px; }
    .nav-link::after {
        content: ''; position: absolute; width: 100%; height: 2px; bottom: 0; left: 0;
        background-color: #00478d; transform: scaleX(0); transform-origin: right;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .nav-link:hover::after, .nav-link.active::after { transform: scaleX(1); transform-origin: left; }

    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

    /* Tab indicator */
    .auth-tab {
        border: 1.5px solid transparent;
        background: transparent;
        color: #424752;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }
    .auth-tab.active-tab {
        background: #00478d;
        color: #ffffff;
        border-color: #00478d;
    }
    .auth-tab:not(.active-tab):hover {
        background: rgba(0,71,141,0.06);
        border-color: rgba(0,71,141,0.2);
    }

    /* Form panel fade */
    .auth-panel { display: none; animation: panelIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .auth-panel.active-panel { display: block; }
    @keyframes panelIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    /* Input base */
    .auth-input {
        width: 100%; padding: 11px 16px; border-radius: 0.5rem; background-color: #ffffff;
        border: 1.5px solid rgba(114, 119, 131, 0.3); color: #0b1c30; font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .auth-input:focus { border-color: #00478d; box-shadow: 0 0 0 4px rgba(0, 71, 141, 0.08); }
    .auth-input.input-error { border-color: #ba1a1a; box-shadow: 0 0 0 4px rgba(186, 26, 26, 0.07); }
    .auth-input.input-valid { border-color: #10b981; }

    /* Divider */
    .or-divider { display: flex; align-items: center; gap: 12px; color: #94a3b8; font-size: 0.75rem; font-weight: 600; }
    .or-divider::before, .or-divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

    /* Decorative blobs */
    .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.12; pointer-events: none; }

    /* Legal prose styling */
    .legal-prose h3 { font-size: 1.05rem; font-weight: 700; color: #0b1c30; margin-top: 1.5rem; margin-bottom: 0.5rem; }
    .legal-prose h3:first-child { margin-top: 0; }
    .legal-prose p { margin-bottom: 1rem; line-height: 1.6; font-size: 0.9rem; }
    .legal-prose ul { list-style-type: disc; padding-left: 1.25rem; margin-bottom: 1rem; font-size: 0.9rem; }
    .legal-prose li { margin-bottom: 0.35rem; }
    .legal-prose strong { color: #0b1c30; font-weight: 600; }

    /* Checkbox Validation Enhancements */
    @keyframes subtleShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
    }
    .shake-anim { animation: subtleShake 0.3s cubic-bezier(0.36, 0.07, 0.19, 0.97) both; }

    .terms-tooltip {
        position: absolute;
        bottom: calc(100% + 4px);
        left: 50%;
        transform: translateX(-50%) translateY(8px);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        background-color: #ba1a1a;
        color: #ffffff;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(186, 26, 26, 0.2);
        z-index: 20;
        pointer-events: none;
    }
    @media (max-width: 420px) {
        .terms-tooltip {
            white-space: normal;
            width: max-content;
            max-width: 280px;
            text-align: center;
        }
    }
    .terms-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: #ba1a1a transparent transparent transparent;
    }
    .terms-tooltip.show-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }
</style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen flex flex-col overflow-x-hidden">

<!-- Google Sign-In Initializer hidden div -->
<div id="g_id_onload"
     data-client_id="<?php echo htmlspecialchars($googleClientId); ?>"
     data-context="signin"
     data-ux_mode="popup"
     data-callback="handleGoogleResponse"
     data-auto_prompt="false">
</div>

<main class="flex-grow flex items-center justify-center pt-28 pb-16 px-4 relative overflow-hidden">
    <!-- Decorative background blobs -->
    <div class="blob w-[500px] h-[500px] bg-primary top-0 -left-40 z-0"></div>
    <div class="blob w-[400px] h-[400px] bg-primary bottom-10 -right-32 z-0"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Card -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-[0_8px_40px_rgba(0,71,141,0.1)] border border-outline-variant/20 overflow-hidden">
            
            <!-- Card header -->
            <div class="px-8 pt-8 pb-5 text-center border-b border-outline-variant/20">
                <!-- Logo icon circle -->
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-3">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" aria-hidden="true">
                        <path d="M20 8C16.5 8 14 10 12.5 12C11 10 9 9 7.5 10.5C6 12 6.5 15 8 17C9 18.5 10 20 10.5 22C11 24 11 28 13 30C14 31 15.5 31 16.5 30C17.5 29 17.5 26 18 24C18.5 22 19 21 20 21C21 21 21.5 22 22 24C22.5 26 22.5 29 23.5 30C24.5 31 26 31 27 30C29 28 29 24 29.5 22C30 20 31 18.5 32 17C33.5 15 34 12 32.5 10.5C31 9 29 10 27.5 12C26 10 23.5 8 20 8Z" fill="#00478d"/>
                    </svg>
                </div>
                <!-- Title and tagline swap based on active tab via JS -->
                <h1 id="auth-header-title" class="text-lg font-bold text-on-surface">Welcome back</h1>
                <p id="auth-header-tagline" class="text-sm text-on-surface-variant mt-0.5">Log in to manage your appointments</p>
            </div>

            <!-- Tab switcher -->
            <div class="flex gap-2 px-8 pt-5 pb-1 bg-surface-container-lowest">
                <button id="tab-login" class="auth-tab active-tab flex-1 py-2 rounded-lg text-sm font-bold focus:outline-none" onclick="switchTab('login')">Log In</button>
                <button id="tab-register" class="auth-tab flex-1 py-2 rounded-lg text-sm font-bold focus:outline-none" onclick="switchTab('register')">Sign Up</button>
            </div>

            <div class="px-8 pt-6 pb-8">
                <!-- ── LOGIN PANEL ── -->
                <div class="auth-panel active-panel" id="panel-login">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="login-email">Email Address</label>
                            <div class="relative">
                                <input class="auth-input pr-10" id="login-email" type="email" placeholder="you@example.com"/>
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant/40 text-[18px]">mail</span>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider" for="login-password">Password</label>
                                <a href="#" class="text-xs font-semibold text-primary hover:underline" onclick="openForgotModal(event)">Forgot password?</a>
                            </div>
                            <div class="relative">
                                <input class="auth-input pr-10" id="login-password" type="password" placeholder="••••••••"/>
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 hover:text-on-surface-variant transition-colors focus:outline-none" onclick="togglePassword('login-password', this)">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </button>
                            </div>
                        </div>
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" class="accent-primary w-4 h-4 rounded" id="login-remember"/>
                            <span class="text-sm text-on-surface-variant font-medium">Keep me logged in</span>
                        </label>
                        <button class="w-full h-11 rounded-lg bg-primary text-on-primary font-bold text-sm hover:bg-on-primary-fixed-variant active:scale-[0.99] transition-all duration-200 shadow-sm flex items-center justify-center gap-2 mt-1" onclick="handleLogin()">
                            <span class="material-symbols-outlined text-[18px]">login</span> Log In
                        </button>
                    </div>

                    <div class="or-divider my-5">or</div>

                    <div class="flex justify-center w-full">
                        <!-- Official Google Sign-In Button Container -->
                        <div class="g_id_signin w-full flex justify-center"
                             data-type="standard"
                             data-shape="rectangular"
                             data-theme="outline"
                             data-text="continue_with"
                             data-size="large"
                             data-logo_alignment="center"
                             data-width="340">
                        </div>
                    </div>

                    <p class="text-center text-sm text-on-surface-variant mt-5">
                        Don't have an account? <button class="font-bold text-primary hover:underline" onclick="switchTab('register')">Sign up free</button>
                    </p>
                </div>

                <!-- ── REGISTER PANEL ── -->
                <div class="auth-panel" id="panel-register">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="reg-first">First Name</label>
                                <input class="auth-input" id="reg-first" type="text" placeholder="John"/>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="reg-last">Last Name</label>
                                <input class="auth-input" id="reg-last" type="text" placeholder="Doe"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="reg-email">Email Address</label>
                            <div class="relative">
                                <input class="auth-input pr-10" id="reg-email" type="email" placeholder="you@example.com"/>
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant/40 text-[18px]" id="reg-email-icon">mail</span>
                            </div>
                            <p class="text-[11px] text-error mt-1.5 hidden" id="reg-email-error">Please enter a valid email address.</p>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider" for="reg-password">Password</label>
                                <span id="reg-password-counter" class="text-[11px] font-medium text-on-surface-variant/60 transition-colors duration-300">0 / 16</span>
                            </div>
                            <div class="relative">
                                <input class="auth-input pr-10" id="reg-password" type="password" placeholder="8–16 characters" maxlength="16" oninput="checkStrength(this.value)"/>
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 hover:text-on-surface-variant transition-colors focus:outline-none" onclick="togglePassword('reg-password', this)">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-error mt-1.5 hidden" id="reg-password-max-error">Maximum length is 16 characters.</p>
                            
                            <!-- Password Checklist -->
                            <ul id="pw-checklist" class="mt-2 space-y-1">
                                <li id="rule-length" class="flex items-center gap-1.5 text-[11px] font-medium text-on-surface-variant/60 transition-colors duration-300">
                                    <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                    8–16 characters
                                </li>
                                <li id="rule-upper" class="flex items-center gap-1.5 text-[11px] font-medium text-on-surface-variant/60 transition-colors duration-300">
                                    <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                    1 uppercase letter
                                </li>
                                <li id="rule-lower" class="flex items-center gap-1.5 text-[11px] font-medium text-on-surface-variant/60 transition-colors duration-300">
                                    <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                    1 lowercase letter
                                </li>
                                <li id="rule-number" class="flex items-center gap-1.5 text-[11px] font-medium text-on-surface-variant/60 transition-colors duration-300">
                                    <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                    1 number
                                </li>
                                <li id="rule-special" class="flex items-center gap-1.5 text-[11px] font-medium text-on-surface-variant/60 transition-colors duration-300">
                                    <span class="material-symbols-outlined text-[14px]">radio_button_unchecked</span>
                                    1 special character
                                </li>
                            </ul>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="reg-confirm">Confirm Password</label>
                            <div class="relative">
                                <input class="auth-input pr-10" id="reg-confirm" type="password" placeholder="Repeat password"/>
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 hover:text-on-surface-variant transition-colors focus:outline-none" onclick="togglePassword('reg-confirm', this)">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-error mt-1.5 hidden" id="reg-confirm-error">Passwords do not match.</p>
                        </div>

                        <!-- MODIFIED: Wrapper Container with Tooltip for Checkbox Validation -->
                        <div id="terms-container" class="relative rounded-lg transition-all duration-300 p-2 -mx-2 border border-transparent">
                            <!-- Tooltip -->
                            <div id="terms-tooltip" class="terms-tooltip" role="tooltip" aria-hidden="true">
                                Please accept the Terms of Service and Privacy Policy to continue.
                            </div>

                            <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" class="accent-primary w-4 h-4 rounded mt-0.5 flex-shrink-0" id="reg-terms" onchange="handleTermsChange(this)"/>
                                <span class="text-sm text-on-surface-variant font-medium leading-snug">
                                    I agree to the <a href="#" onclick="event.preventDefault(); openLegalModal('terms');" class="text-primary font-bold hover:underline">Terms of Service</a> and <a href="#" onclick="event.preventDefault(); openLegalModal('privacy');" class="text-primary font-bold hover:underline">Privacy Policy</a>
                                </span>
                            </label>
                        </div>

                        <div>
                            <button id="btn-register" class="w-full h-11 rounded-lg bg-primary text-on-primary font-bold text-sm hover:bg-on-primary-fixed-variant active:scale-[0.99] transition-all duration-200 shadow-sm flex items-center justify-center gap-2 mt-1" onclick="handleRegister()">
                                <span class="material-symbols-outlined text-[18px]">person_add</span> Create Account
                            </button>
                        </div>
                    </div>

                    <div class="or-divider my-5">or</div>

                    <div class="flex justify-center w-full">
                        <!-- Official Google Sign-In Button Container -->
                        <div class="g_id_signin w-full flex justify-center"
                             data-type="standard"
                             data-shape="rectangular"
                             data-theme="outline"
                             data-text="continue_with"
                             data-size="large"
                             data-logo_alignment="center"
                             data-width="340">
                        </div>
                    </div>

                    <p class="text-center text-sm text-on-surface-variant mt-5">
                        Already have an account? <button class="font-bold text-primary hover:underline" onclick="switchTab('login')">Log in</button>
                    </p>
                </div>
            </div>
        </div>

        <p class="text-center mt-5 text-sm text-on-surface-variant">
            <a href="../index.php" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to homepage
            </a>
        </p>
    </div>
</main>

<!-- Toast container -->
<div id="toast-container" class="fixed top-24 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

<!-- Screen Reader Live Region for Announcements -->
<div id="a11y-announcer" class="sr-only" aria-live="assertive"></div>

<!-- ── LEGAL MODAL OVERLAY ── -->
<div id="legal-modal-overlay" class="fixed inset-0 bg-on-background/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4 sm:p-6" onclick="handleModalBackdropClick(event)">
    <div id="legal-modal-container" class="bg-surface-container-lowest w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col max-h-[85vh] transform scale-95 transition-transform duration-300 relative border border-outline-variant/30">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-outline-variant/20 flex justify-between items-center bg-surface-container-low rounded-t-2xl">
            <h2 id="legal-modal-title" class="text-lg font-bold text-on-background flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">gavel</span>
                Document Title
            </h2>
            <button onclick="closeLegalModal()" class="text-on-surface-variant hover:text-error transition-colors focus:outline-none rounded-full p-1 hover:bg-error/10">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div id="legal-modal-body" class="p-6 sm:p-8 overflow-y-auto flex-grow legal-prose text-on-surface-variant">
            <!-- Dynamic content injected here via JS -->
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-outline-variant/20 bg-surface-container-low rounded-b-2xl flex justify-between items-center">
            <p class="text-xs text-on-surface-variant/60 font-medium">Last Updated: June 2026</p>
            <button onclick="closeLegalModal()" class="px-6 py-2.5 bg-primary text-on-primary rounded-lg font-bold text-sm hover:bg-on-primary-fixed-variant transition-colors shadow-sm active:scale-[0.98]">
                I Understand
            </button>
        </div>
    </div>
</div>

<script>
    // ── Inject CSRF Token into Javascript ─────────────────────────────────────
    const CSRF_TOKEN = <?php echo json_encode($csrfToken); ?>;

    // ── Google Authentication Handler ─────────────────────────────────────────
    function handleGoogleResponse(response) {
        if (!response.credential) {
            showToast('Google authentication failed.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('credential', response.credential);

        fetch('auth/google-auth.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.href = data.redirect || '../client/pages/dashboard.php', 1200);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(err => {
            console.error('Google Auth Error:', err);
            showToast('A network error occurred. Please try again.', 'error');
        });
    }

    // ── Legal Document Content ────────────────────────────────────────────────
    const legalContent = {
        terms: {
            title: "Terms of Service",
            icon: "description",
            body: `
                <h3>1. Acceptance of Terms</h3>
                <p>By creating an account and utilizing the DentalCare Pro platform ("Platform"), you agree to be bound by these Terms of Service. If you do not agree to these terms, you may not use our scheduling or communication services.</p>
                
                <h3>2. Medical Disclaimer</h3>
                <p><strong>DentalCare Pro is a technology platform, not a healthcare provider.</strong> The software is designed to facilitate appointment scheduling and communication between you and your dental professional. No content on this platform should be construed as medical advice, diagnosis, or treatment.</p>
                <ul>
                    <li>Always seek the advice of your dentist or other qualified health provider with any questions you may have regarding a medical condition.</li>
                    <li>In the event of a dental emergency, contact your local emergency services or visit the nearest emergency room immediately.</li>
                </ul>

                <h3>3. User Accounts & Security</h3>
                <p>You are responsible for safeguarding the password that you use to access the Platform. You agree not to disclose your password to any third party and to take sole responsibility for any activities or actions under your account, whether or not you have authorized such activities.</p>

                <h3>4. Appointments and Cancellations</h3>
                <p>While our Platform allows you to schedule appointments, all scheduling is subject to the availability and policies of the respective dental clinic. <strong>Late cancellations or no-shows may be subject to fees</strong> as determined by your healthcare provider, not DentalCare Pro.</p>

                <h3>5. Acceptable Use</h3>
                <p>You agree not to misuse the Platform. For example, you must not:</p>
                <ul>
                    <li>Interfere with or disrupt the access of any user, host, or network.</li>
                    <li>Attempt to access unauthorized data or PHI (Protected Health Information) belonging to other users.</li>
                    <li>Submit false or misleading information when creating an account or booking an appointment.</li>
                </ul>

                <h3>6. Limitation of Liability</h3>
                <p>To the maximum extent permitted by law, DentalCare Pro shall not be liable for any indirect, incidental, special, consequential or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from your access to or use of the platform.</p>
            `
        },
        privacy: {
            title: "Privacy Policy",
            icon: "shield_person",
            body: `
                <h3>1. Introduction to Your Privacy</h3>
                <p>At DentalCare Pro, protecting your personal and health-related information is our highest priority. This Privacy Policy outlines how we collect, use, and safeguard the data you provide to us while using our scheduling platform.</p>

                <h3>2. Information We Collect</h3>
                <p>To provide our services efficiently, we collect the following types of information:</p>
                <ul>
                    <li><strong>Personally Identifiable Information (PII):</strong> Your full name, email address, phone number, and demographic information required for creating an account.</li>
                    <li><strong>Appointment Data:</strong> Dates, times, preferred providers, and general reasons for visits (e.g., "routine cleaning", "toothache") that you submit through the Platform.</li>
                    <li><strong>Technical Data:</strong> IP addresses, browser types, and usage metrics to help us ensure system stability and security.</li>
                </ul>

                <h3>3. How We Use Your Information</h3>
                <p>Your data is used strictly for operational purposes:</p>
                <ul>
                    <li>Facilitating appointment scheduling with your chosen dental clinic.</li>
                    <li>Sending transactional communications, such as booking confirmations, SMS reminders, and password reset links.</li>
                </ul>

                <h3>4. Information Sharing and HIPAA Compliance</h3>
                <p>We do not sell your personal data. We only share your information with:</p>
                <ul>
                    <li><strong>Your Dental Provider:</strong> We securely transmit your scheduling data to the specific clinic you are booking with. This data handling complies with applicable health privacy frameworks (such as HIPAA in the US) where we act as a Business Associate.</li>
                    <li><strong>Essential Service Providers:</strong> Trusted third-party infrastructure partners (like secure cloud hosting) under strict confidentiality agreements.</li>
                </ul>

                <h3>5. Data Security Measures</h3>
                <p>We implement industry-standard security measures, including encryption in transit (HTTPS/TLS) and at rest, secure password hashing, and rigorous access controls to prevent unauthorized access to your information.</p>

                <h3>6. Your Rights and Choices</h3>
                <p>You retain full control over your account data. You may update your personal information through your account settings or request a complete deletion of your DentalCare Pro account by contacting our support team. Note that your dental clinic may retain their own separate medical records independently of our Platform.</p>
            `
        }
    };

    // ── Modal Interaction Logic ───────────────────────────────────────────────
    function openLegalModal(type) {
        const overlay = document.getElementById('legal-modal-overlay');
        const container = document.getElementById('legal-modal-container');
        const titleEl = document.getElementById('legal-modal-title');
        const bodyEl = document.getElementById('legal-modal-body');

        // Populate content
        const data = legalContent[type];
        titleEl.innerHTML = `<span class="material-symbols-outlined text-primary">${data.icon}</span> ${data.title}`;
        bodyEl.innerHTML = data.body;

        // Show modal & prevent background scrolling
        document.body.style.overflow = 'hidden';
        overlay.classList.remove('hidden');
        
        // Trigger reflow to ensure animation works
        void overlay.offsetWidth; 
        
        overlay.classList.remove('opacity-0');
        overlay.classList.add('opacity-100');
        container.classList.remove('scale-95');
        container.classList.add('scale-100');
    }

    function closeLegalModal() {
        const overlay = document.getElementById('legal-modal-overlay');
        const container = document.getElementById('legal-modal-container');

        // Animate out
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');

        // Hide after transition & restore scrolling
        setTimeout(() => {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300); // matches the duration-300 class
    }

    function handleModalBackdropClick(e) {
        // Close if clicking directly on the overlay background, not the modal itself
        if (e.target === document.getElementById('legal-modal-overlay')) {
            closeLegalModal();
        }
    }

    // ── Tab / Panel switching ─────────────────────────────────────────────────
    function switchTab(tab) {
        document.querySelectorAll('.auth-panel').forEach(p => p.classList.remove('active-panel'));
        document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active-tab'));

        if (tab === 'login') {
            document.getElementById('panel-login').classList.add('active-panel');
            document.getElementById('tab-login').classList.add('active-tab');
        } else if (tab === 'register') {
            document.getElementById('panel-register').classList.add('active-panel');
            document.getElementById('tab-register').classList.add('active-tab');
        }

        const headerData = {
            login:    { title: 'Welcome back',          tagline: 'Log in to manage your appointments' },
            register: { title: 'Create your account',   tagline: 'Free — no credit card needed' }
        };
        document.getElementById('auth-header-title').textContent   = headerData[tab].title;
        document.getElementById('auth-header-tagline').textContent = headerData[tab].tagline;
    }

    // ── Toggle password visibility ────────────────────────────────────────────
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon  = btn.querySelector('.material-symbols-outlined');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }

    // ── Password strength ─────────────────────────────────────────────────────
    function checkStrength(val) {
        const rules = [
            { id: 'rule-length',  met: val.length >= 8 && val.length <= 16 },
            { id: 'rule-upper',   met: /[A-Z]/.test(val) },
            { id: 'rule-lower',   met: /[a-z]/.test(val) },
            { id: 'rule-number',  met: /[0-9]/.test(val) },
            { id: 'rule-special', met: /[^A-Za-z0-9]/.test(val) }
        ];

        const isEmpty = val.length === 0;

        rules.forEach(rule => {
            const el = document.getElementById(rule.id);
            const icon = el.querySelector('span');

            if (isEmpty) {
                el.className = 'flex items-center gap-1.5 text-[11px] font-medium text-on-surface-variant/60 transition-colors duration-300';
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

    // ── Email real-time validation (register) ─────────────────────────────────
    document.getElementById('reg-email').addEventListener('input', function() {
        const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
        const icon  = document.getElementById('reg-email-icon');
        const error = document.getElementById('reg-email-error');
        if (this.value === '') {
            icon.textContent = 'mail';
            this.classList.remove('input-error', 'input-valid');
            error.classList.add('hidden');
        } else if (emailRegex.test(this.value)) {
            icon.textContent = 'check_circle';
            icon.className = 'absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-emerald-500 text-[18px]';
            this.classList.remove('input-error');
            this.classList.add('input-valid');
            error.classList.add('hidden');
        } else {
            icon.textContent = 'cancel';
            icon.className = 'absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-error text-[18px]';
            this.classList.add('input-error');
            this.classList.remove('input-valid');
            error.classList.remove('hidden');
        }
    });

    // ── Checkbox Change Handler ───────────────────────────────────────────────
    function handleTermsChange(checkbox) {
        const container = document.getElementById('terms-container');
        const tooltip = document.getElementById('terms-tooltip');
        const announcer = document.getElementById('a11y-announcer');

        if (checkbox.checked) {
            // Remove error styling
            container.classList.remove('border-error', 'bg-error/5', 'shadow-[0_0_0_4px_rgba(186,26,26,0.07)]');
            container.classList.add('border-transparent');
            
            // Hide tooltip gracefully
            tooltip.classList.remove('show-tooltip');
            tooltip.setAttribute('aria-hidden', 'true');
            
            // Clear screen reader text
            announcer.textContent = '';
        }
    }

    // ── Toast helper ──────────────────────────────────────────────────────────
    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const id = 'toast-' + Date.now();
        const icons    = { success: 'check_circle', error: 'error', info: 'info', warning: 'warning' };
        const palettes = {
            success: 'text-emerald-700 bg-emerald-50 border-emerald-200',
            error:   'text-red-700 bg-red-50 border-red-200',
            warning: 'text-amber-700 bg-amber-50 border-amber-200',
            info:    'text-primary bg-primary/5 border-primary/20',
        };

        const toast = document.createElement('div');
        toast.id = id;
        toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg text-sm font-semibold transition-all duration-300 translate-x-10 opacity-0 ${palettes[type]}`;
        toast.innerHTML = `
            <span class="material-symbols-outlined text-[18px] flex-shrink-0">${icons[type]}</span>
            <span class="flex-grow">${message}</span>
            <button class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity" onclick="document.getElementById('${id}').remove()">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>`;
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.remove('translate-x-10', 'opacity-0'));
        setTimeout(() => {
            toast.classList.add('translate-x-10', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // ── Login handler ─────────────────────────────────────────────────────────
    function handleLogin() {
        const email    = document.getElementById('login-email').value.trim();
        const password = document.getElementById('login-password').value;
        const remember = document.getElementById('login-remember').checked;
        const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

        if (!email || !emailRegex.test(email)) {
            document.getElementById('login-email').classList.add('input-error');
            showToast('Please enter a valid email address.', 'error');
            return;
        }
        if (!password) {
            document.getElementById('login-password').classList.add('input-error');
            showToast('Please enter your password.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);
        formData.append('csrf_token', CSRF_TOKEN);

        fetch('login.php?action=login', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Login successful! Redirecting...', 'success');
                if (remember) {
                    const rememberData = new FormData();
                    rememberData.append('csrf_token', CSRF_TOKEN);
                    fetch('login.php?action=remember_me', { method: 'POST', body: rememberData })
                        .then(() => setTimeout(() => window.location.href = data.redirect, 1200));
                } else {
                    setTimeout(() => window.location.href = data.redirect, 1200);
                }
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(() => showToast('An error occurred. Please try again.', 'error'));
    }

    // ── Register handler ──────────────────────────────────────────────────────
    function handleRegister() {
        const first    = document.getElementById('reg-first').value.trim();
        const last     = document.getElementById('reg-last').value.trim();
        const email    = document.getElementById('reg-email').value.trim();
        const password = document.getElementById('reg-password').value;
        const confirm  = document.getElementById('reg-confirm').value;
        const terms    = document.getElementById('reg-terms').checked;
        const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

        let hasError = false;
        
        if (!first || !last) {
            showToast('Please enter your full name.', 'error');
            hasError = true;
        }
        if (!email || !emailRegex.test(email)) {
            document.getElementById('reg-email').classList.add('input-error');
            showToast('Please enter a valid email address.', 'error');
            hasError = true;
        }
        const pwRules = [
            { test: val => val.length >= 8 && val.length <= 16, msg: 'Password must be 8\u201316 characters long.' },
            { test: val => /[A-Z]/.test(val),                   msg: 'Password must contain at least one uppercase letter.' },
            { test: val => /[a-z]/.test(val),                   msg: 'Password must contain at least one lowercase letter.' },
            { test: val => /[0-9]/.test(val),                   msg: 'Password must contain at least one number.' },
            { test: val => /[^A-Za-z0-9]/.test(val),           msg: 'Password must contain at least one special character.' },
        ];
        const pwField = document.getElementById('reg-password');
        const failedRule = pwRules.find(r => !r.test(password));
        if (failedRule) {
            pwField.classList.add('input-error');
            showToast(failedRule.msg, 'error');
            hasError = true;
        } else {
            pwField.classList.remove('input-error');
        }
        if (password !== confirm) {
            document.getElementById('reg-confirm').classList.add('input-error');
            document.getElementById('reg-confirm-error').classList.remove('hidden');
            showToast('Passwords do not match.', 'error');
            hasError = true;
        } else {
            document.getElementById('reg-confirm-error').classList.add('hidden');
        }
        
        // ── Custom Terms Validations Enhancements ──
        if (!terms) {
            const container = document.getElementById('terms-container');
            const tooltip = document.getElementById('terms-tooltip');
            const checkbox = document.getElementById('reg-terms');
            const announcer = document.getElementById('a11y-announcer');

            // 1. Add Highlight/Glow
            container.classList.remove('border-transparent');
            container.classList.add('border-error', 'bg-error/5', 'shadow-[0_0_0_4px_rgba(186,26,26,0.07)]');
            
            // 2. Show Tooltip with Arrow
            tooltip.classList.add('show-tooltip');
            tooltip.setAttribute('aria-hidden', 'false');

            // 3. Trigger Shake Animation (reset to allow re-triggering)
            container.classList.remove('shake-anim');
            void container.offsetWidth; // Force DOM reflow
            container.classList.add('shake-anim');

            // 4. Accessibility update & Focus check box
            announcer.textContent = "Please accept the Terms of Service and Privacy Policy to continue.";
            checkbox.focus();

            // 5. Scroll to checkbox if outside the viewport
            const rect = container.getBoundingClientRect();
            const inViewport = (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
            if (!inViewport) {
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            hasError = true;
        }

        if (hasError) return;

        const formData = new FormData();
        formData.append('first_name', first);
        formData.append('last_name', last);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('csrf_token', CSRF_TOKEN);

        fetch('login.php?action=register', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Account created! Welcome to DentalCare Pro.', 'success');
                setTimeout(() => window.location.href = data.redirect, 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(() => showToast('An error occurred. Please try again.', 'error'));
    }

    // ── Read URL param to pre-select tab ─────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const mode   = params.get('mode');
        const email  = params.get('email');

        if (mode === 'register') switchTab('register');
        else switchTab('login');

        // Prefill fields from booking flow redirect
        const firstName = params.get('first_name');
        const lastName  = params.get('last_name');

        if (email) {
            const loginEmailEl = document.getElementById('login-email');
            const regEmailEl   = document.getElementById('reg-email');
            if (loginEmailEl) loginEmailEl.value = decodeURIComponent(email);
            if (regEmailEl)   regEmailEl.value   = decodeURIComponent(email);
            loginEmailEl?.dispatchEvent(new Event('input'));
            regEmailEl?.dispatchEvent(new Event('input'));
        }
        if (firstName) {
            const el = document.getElementById('reg-first');
            if (el) { el.value = decodeURIComponent(firstName); el.dispatchEvent(new Event('input')); }
        }
        if (lastName) {
            const el = document.getElementById('reg-last');
            if (el) { el.value = decodeURIComponent(lastName); el.dispatchEvent(new Event('input')); }
        }

        // ── Password Max Length Feedback Initialization ────────────────────────
        const regPasswordEl = document.getElementById('reg-password');
        const regPasswordCounter = document.getElementById('reg-password-counter');
        const regPasswordMaxError = document.getElementById('reg-password-max-error');
        const announcer = document.getElementById('a11y-announcer');

        if (regPasswordEl && regPasswordCounter && regPasswordMaxError) {
            const updatePasswordCounter = () => {
                const len = regPasswordEl.value.length;
                regPasswordCounter.textContent = `${len} / 16`;

                if (len >= 14) {
                    regPasswordCounter.classList.remove('text-on-surface-variant/60');
                    regPasswordCounter.classList.add('text-amber-600');
                } else {
                    regPasswordCounter.classList.remove('text-amber-600');
                    regPasswordCounter.classList.add('text-on-surface-variant/60');
                }

                if (len < 16) {
                    regPasswordMaxError.classList.add('hidden');
                }
            };

            // Initial call to set correct state based on any pre-filled value
            updatePasswordCounter();

            regPasswordEl.addEventListener('input', updatePasswordCounter);

            regPasswordEl.addEventListener('keydown', (e) => {
                const len = regPasswordEl.value.length;
                const isControlKey = e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' ||
                                     e.key === 'Enter' || e.key.startsWith('Arrow') || e.ctrlKey || e.metaKey || e.altKey;

                if (len === 16 && !isControlKey) {
                    if (regPasswordMaxError.classList.contains('hidden')) {
                        regPasswordMaxError.classList.remove('hidden');
                        announcer.textContent = 'Maximum length is 16 characters.';
                    }
                }
            });

            regPasswordEl.addEventListener('paste', () => {
                setTimeout(() => {
                    updatePasswordCounter();
                    if (regPasswordEl.value.length === 16 && regPasswordMaxError.classList.contains('hidden')) {
                        regPasswordMaxError.classList.remove('hidden');
                        announcer.textContent = 'Maximum length is 16 characters.';
                    }
                }, 0);
            });
        }
    });
</script>

<?php include __DIR__ . '/forgot-password.php'; ?>

</body>
</html>