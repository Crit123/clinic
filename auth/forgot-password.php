<?php
/**
 * forgot-password.php
 * ---------------------------------------------------------------------
 * Self-contained "Forgot Password" component for DentalCare Pro.
 *
 * - Designed to be included inside login.php (`include 'forgot-password.php';`)
 * so its modal markup renders inside the login page.
 * - Also acts as its own tiny JSON API: the modal's JS talks to THIS
 * SAME file via fetch() with action=request_otp / verify_otp / reset_password.
 * Because of that, login.php must call session_start() before any output
 * (see the top of login.php) so both files share one session.
 *
 * Flow:
 * 1) User enters their registered email           -> request_otp
 * 2) A 6-digit OTP is emailed, user enters it      -> verify_otp
 * 3) User sets a new password                      -> reset_password
 *
 * Swap-in points for production are marked with "TODO (production)".
 * ---------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conn.php';

const OTP_LENGTH        = 6;
const OTP_TTL_SECONDS   = 300;  // 5 minutes before a code expires
const OTP_RESEND_COOLDOWN = 30; // seconds before "Resend code" is allowed again
const OTP_MAX_ATTEMPTS  = 5;    // wrong-code attempts before forcing a new OTP

/**
 * Checks whether an email belongs to an existing account.
 *
 * Uses the shared PDO instance from conn.php to query the users table.
 */
function isRegisteredEmail(string $email): bool
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return (bool) $stmt->fetch();
}

/**
 * Generates a zero-padded numeric OTP, e.g. "048213".
 */
function generateOtp(): string
{
    return str_pad((string) random_int(0, 10 ** OTP_LENGTH - 1), OTP_LENGTH, '0', STR_PAD_LEFT);
}

/**
 * Sends the OTP email.
 *
 * Works out of the box with PHP's built-in mail() so the demo runs with
 * zero dependencies. For real-world delivery to Gmail/Outlook inboxes,
 * swap this body for PHPMailer talking to Gmail's SMTP relay -- mail()
 * alone is frequently rejected or spam-filtered because it can't
 * authenticate with SPF/DKIM the way a real SMTP login can.
 *
 * TODO (production) - Gmail SMTP via PHPMailer:
 * composer require phpmailer/phpmailer
 *
 * $mail = new PHPMailer\PHPMailer\PHPMailer(true);
 * $mail->isSMTP();
 * $mail->Host       = 'smtp.gmail.com';
 * $mail->SMTPAuth   = true;
 * $mail->Username   = 'your-app@gmail.com';
 * $mail->Password   = getenv('GMAIL_APP_PASSWORD'); // 16-char App Password, not your login password
 * $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
 * $mail->Port       = 587;
 * $mail->setFrom('your-app@gmail.com', 'DentalCare Pro');
 * $mail->addAddress($toEmail);
 * $mail->isHTML(true);
 * $mail->Subject = 'Your DentalCare Pro verification code';
 * $mail->Body    = "Your code is <b>$otp</b>. It expires in 5 minutes.";
 * return $mail->send();
 */
function sendOtpEmail(string $toEmail, string $otp): bool
{
    $subject = 'Your DentalCare Pro verification code';
    $body  = "Hi,\r\n\r\n";
    $body .= "Your DentalCare Pro verification code is: {$otp}\r\n\r\n";
    $body .= "This code expires in 5 minutes. If you didn't request this, you can ignore this email.\r\n\r\n";
    $body .= "- DentalCare Pro\r\n";

    $headers  = "From: DentalCare Pro <no-reply@dentalcarepro.com>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // mail() returns true if the message was *accepted for delivery* by the
    // local MTA, not that it actually reached the inbox. Log failures.
    $sent = @mail($toEmail, $subject, $body, $headers);
    if (!$sent) {
        error_log("[forgot-password] mail() failed to queue OTP for {$toEmail}");
    }
    return $sent;
}

if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, string $message, array $extra = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }
}

/* ----------------------------------------------------------------------
 * AJAX API — only runs when this file is hit directly with a POST + action.
 * A normal `include` from login.php is a GET-time include, so this whole
 * block is skipped and only the HTML/CSS/JS below gets rendered.
 * -------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    switch ($_POST['action']) {

        case 'request_otp': {
            $email = trim((string) ($_POST['email'] ?? ''));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(false, 'Please enter a valid email address.');
            }

            if (!isRegisteredEmail($email)) {
                jsonResponse(false, 'We couldn\'t find an account with that email.');
            }

            if (
                isset($_SESSION['otp_sent_at'], $_SESSION['otp_email'])
                && $_SESSION['otp_email'] === $email
                && (time() - $_SESSION['otp_sent_at']) < OTP_RESEND_COOLDOWN
            ) {
                $wait = OTP_RESEND_COOLDOWN - (time() - $_SESSION['otp_sent_at']);
                jsonResponse(false, "Please wait {$wait}s before requesting another code.", ['retry_after' => $wait]);
            }

            $otp = generateOtp();
            $_SESSION['otp_code']     = $otp;
            $_SESSION['otp_email']    = $email;
            $_SESSION['otp_expiry']   = time() + OTP_TTL_SECONDS;
            $_SESSION['otp_sent_at']  = time();
            $_SESSION['otp_attempts'] = 0;
            $_SESSION['otp_verified'] = false;

            sendOtpEmail($email, $otp);

            jsonResponse(true, 'A 6-digit code has been sent to your email.', [
                'ttl'           => OTP_TTL_SECONDS,
                'resend_cooldown' => OTP_RESEND_COOLDOWN,
            ]);
        }

        case 'verify_otp': {
            $inputOtp = trim((string) ($_POST['otp'] ?? ''));

            if (empty($_SESSION['otp_code']) || empty($_SESSION['otp_expiry'])) {
                jsonResponse(false, 'Your session expired. Please request a new code.', ['restart' => true]);
            }

            if (time() > $_SESSION['otp_expiry']) {
                jsonResponse(false, 'That code has expired. Please request a new one.', ['restart' => true]);
            }

            $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
            if ($_SESSION['otp_attempts'] > OTP_MAX_ATTEMPTS) {
                jsonResponse(false, 'Too many incorrect attempts. Please request a new code.', ['restart' => true]);
            }

            if (!hash_equals($_SESSION['otp_code'], $inputOtp)) {
                $remaining = max(0, OTP_MAX_ATTEMPTS - $_SESSION['otp_attempts']);
                jsonResponse(false, "Incorrect code. {$remaining} attempt(s) left.");
            }

            $_SESSION['otp_verified'] = true;
            jsonResponse(true, 'Code verified.');
        }

        case 'resend_otp': {
            // Re-sends a code for whichever email is already in-flight on this session.
            $email = trim((string) ($_SESSION['otp_email'] ?? ''));
            if ($email === '') {
                jsonResponse(false, 'Please start over and enter your email again.', ['restart' => true]);
            }
            if (
                isset($_SESSION['otp_sent_at'])
                && (time() - $_SESSION['otp_sent_at']) < OTP_RESEND_COOLDOWN
            ) {
                $wait = OTP_RESEND_COOLDOWN - (time() - $_SESSION['otp_sent_at']);
                jsonResponse(false, "Please wait {$wait}s before requesting another code.", ['retry_after' => $wait]);
            }
            $otp = generateOtp();
            $_SESSION['otp_code']     = $otp;
            $_SESSION['otp_expiry']   = time() + OTP_TTL_SECONDS;
            $_SESSION['otp_sent_at']  = time();
            $_SESSION['otp_attempts'] = 0;
            $_SESSION['otp_verified'] = false;
            sendOtpEmail($email, $otp);
            jsonResponse(true, 'A new code has been sent.', ['ttl' => OTP_TTL_SECONDS, 'resend_cooldown' => OTP_RESEND_COOLDOWN]);
        }

        case 'reset_password': {
            if (empty($_SESSION['otp_verified']) || empty($_SESSION['otp_email'])) {
                jsonResponse(false, 'Your session expired. Please start over.', ['restart' => true]);
            }

            $password = (string) ($_POST['password'] ?? '');
            $confirm  = (string) ($_POST['confirm'] ?? '');

            if (strlen($password) < 8 || strlen($password) > 16) {
                jsonResponse(false, 'Password must be between 8 and 16 characters.');
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
            if ($password !== $confirm) {
                jsonResponse(false, 'Passwords do not match.');
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
            $stmt->execute([$hash, $_SESSION['otp_email']]);

            // Clean up any password_resets rows for this email
            $del = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
            $del->execute([$_SESSION['otp_email']]);

            unset(
                $_SESSION['otp_code'],
                $_SESSION['otp_email'],
                $_SESSION['otp_expiry'],
                $_SESSION['otp_sent_at'],
                $_SESSION['otp_attempts'],
                $_SESSION['otp_verified']
            );

            jsonResponse(true, 'Your password has been reset.');
        }

        default:
            jsonResponse(false, 'Unknown action.');
    }
}
?>
<!-- =====================================================================
     FORGOT PASSWORD — middle/center modal
     Expects to be included inside a page that already loads Tailwind CDN,
     the Inter / Material Symbols fonts, and the same tailwind.config color
     tokens as login.php (so .auth-input, colors like bg-primary, etc. match).
===================================================================== -->
<style>
    .forgot-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 70;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(11, 28, 48, 0.55);
        backdrop-filter: blur(4px);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s cubic-bezier(0.4,0,0.2,1), visibility 0.25s;
    }
    .forgot-modal-overlay.is-open {
        opacity: 1;
        visibility: visible;
    }
    .forgot-modal-card {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border-radius: 1rem;
        box-shadow: 0 24px 60px rgba(0,71,141,0.22);
        padding: 32px 28px 28px;
        position: relative;
        transform: scale(0.95) translateY(10px);
        transition: transform 0.25s cubic-bezier(0.16,1,0.3,1);
        max-height: calc(100vh - 32px);
        overflow-y: auto;
    }
    .forgot-modal-overlay.is-open .forgot-modal-card {
        transform: scale(1) translateY(0);
    }
    .forgot-modal-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        color: #424752;
        background: transparent;
        transition: background 0.15s, color 0.15s;
    }
    .forgot-modal-close:hover {
        background: rgba(11,28,48,0.06);
        color: #0b1c30;
    }

    /* Step indicator */
    .forgot-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-bottom: 22px;
    }
    .forgot-step {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .forgot-step span {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        border-radius: 9999px;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        transition: background 0.25s, color 0.25s;
    }
    .forgot-step.is-active,
    .forgot-step.is-done { color: #00478d; }
    .forgot-step.is-active span,
    .forgot-step.is-done span { background: #00478d; color: #ffffff; }
    .forgot-step.is-done span::before { content: '\2713'; }
    .forgot-step-line {
        width: 20px;
        height: 1.5px;
        background: #e2e8f0;
    }

    /* Stage panels */
    .forgot-stage { display: none; animation: forgotPanelIn 0.3s cubic-bezier(0.16,1,0.3,1) both; }
    .forgot-stage.is-active { display: block; }
    @keyframes forgotPanelIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* OTP input boxes */
    .otp-box-row { display: flex; gap: 10px; justify-content: center; }
    .otp-box {
        width: 44px;
        height: 52px;
        text-align: center;
        font-size: 1.35rem;
        font-weight: 700;
        color: #0b1c30;
        border-radius: 0.5rem;
        border: 1.5px solid rgba(114,119,131,0.3);
        background: #ffffff;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    }
    .otp-box:focus {
        border-color: #00478d;
        box-shadow: 0 0 0 4px rgba(0,71,141,0.08);
    }
    .otp-box.otp-filled { border-color: #00478d; }
    .otp-box.otp-error {
        border-color: #ba1a1a;
        box-shadow: 0 0 0 4px rgba(186,26,26,0.07);
        animation: otpShake 0.4s;
    }
    @keyframes otpShake {
        25%  { transform: translateX(-4px); }
        75%  { transform: translateX(4px); }
    }

    .forgot-btn-primary {
        width: 100%;
        height: 44px;
        border-radius: 0.5rem;
        background: #00478d;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.2s, opacity 0.2s, transform 0.1s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }
    .forgot-btn-primary:hover:not(:disabled) { background: #00366e; }
    .forgot-btn-primary:active:not(:disabled) { transform: scale(0.99); }
    .forgot-btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }
    .forgot-spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,0.4);
        border-top-color: #ffffff;
        border-radius: 9999px;
        animation: forgotSpin 0.6s linear infinite;
    }
    @keyframes forgotSpin { to { transform: rotate(360deg); } }
</style>

<div id="forgot-modal-overlay" class="forgot-modal-overlay" aria-hidden="true">
    <div class="forgot-modal-card" role="dialog" aria-modal="true" aria-labelledby="forgot-modal-title">
        <button type="button" class="forgot-modal-close" onclick="closeForgotModal()" aria-label="Close dialog">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>

        <!-- Step indicator -->
        <div class="forgot-steps" id="forgot-steps">
            <div class="forgot-step is-active" data-step="1"><span>1</span>Email</div>
            <div class="forgot-step-line"></div>
            <div class="forgot-step" data-step="2"><span>2</span>Verify</div>
            <div class="forgot-step-line"></div>
            <div class="forgot-step" data-step="3"><span>3</span>Reset</div>
        </div>

        <!-- STAGE 1 — Email -->
        <div class="forgot-stage is-active" id="forgot-stage-1">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-primary/10 mb-4">
                    <span class="material-symbols-outlined text-primary text-[28px]">lock_reset</span>
                </div>
                <h2 id="forgot-modal-title" class="text-lg font-bold text-on-background">Reset your password</h2>
                <p class="text-sm text-on-surface-variant mt-1">Enter the email on your account and we'll send a verification code.</p>
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="forgot-email">Email Address</label>
                <div class="relative">
                    <input class="auth-input pr-10" id="forgot-email" type="email" placeholder="you@example.com" autocomplete="email"/>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant/40 text-[18px]">mail</span>
                </div>
                <p class="text-[11px] text-error mt-1.5 hidden" id="forgot-email-error">Please enter a valid email address.</p>
            </div>
            <button type="button" class="forgot-btn-primary mt-5" id="forgot-send-btn" onclick="handleSendOtp()">
                <span class="material-symbols-outlined text-[18px]">send</span>
                <span>Send Code</span>
            </button>
        </div>

        <!-- STAGE 2 — OTP -->
        <div class="forgot-stage" id="forgot-stage-2">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-primary/10 mb-4">
                    <span class="material-symbols-outlined text-primary text-[28px]">mark_email_read</span>
                </div>
                <h2 class="text-lg font-bold text-on-background">Check your email</h2>
                <p class="text-sm text-on-surface-variant mt-1">
                    Enter the 6-digit code we sent to <span class="font-semibold text-on-background" id="forgot-masked-email">your email</span>.
                </p>
            </div>

            <div class="otp-box-row" id="otp-box-row">
                <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-otp-index="0"/>
                <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-otp-index="1"/>
                <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-otp-index="2"/>
                <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-otp-index="3"/>
                <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-otp-index="4"/>
                <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-otp-index="5"/>
            </div>
            <p class="text-[11px] text-error mt-3 text-center hidden" id="forgot-otp-error">Incorrect code.</p>

            <p class="text-center text-sm text-on-surface-variant mt-4" id="forgot-resend-wrap">
                Didn't get it?
                <button type="button" class="font-bold text-primary hover:underline disabled:opacity-50 disabled:no-underline" id="forgot-resend-btn" onclick="handleResendOtp()" disabled>
                    Resend code (<span id="forgot-resend-timer">30s</span>)
                </button>
            </p>

            <button type="button" class="forgot-btn-primary mt-4" id="forgot-verify-btn" onclick="handleVerifyOtp()">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span>Verify Code</span>
            </button>
            <button type="button" class="flex items-center justify-center gap-1.5 w-full mt-3 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors" onclick="goToStage(1)">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Use a different email
            </button>
        </div>

        <!-- STAGE 3 — New password -->
        <div class="forgot-stage" id="forgot-stage-3">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-primary/10 mb-4">
                    <span class="material-symbols-outlined text-primary text-[28px]">password</span>
                </div>
                <h2 class="text-lg font-bold text-on-background">Set a new password</h2>
                <p class="text-sm text-on-surface-variant mt-1">Make it something you haven't used before.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="forgot-new-password">New Password</label>
                    <div class="relative">
                        <input class="auth-input pr-10" id="forgot-new-password" type="password" placeholder="8–16 characters" autocomplete="new-password" maxlength="16" oninput="checkForgotStrength(this.value)" onkeydown="handleForgotPwKeydown(event)"/>
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 hover:text-on-surface-variant transition-colors focus:outline-none" onclick="togglePassword('forgot-new-password', this)">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-4 gap-1 mt-2">
                        <div class="strength-bar-seg" id="f-sb1"></div>
                        <div class="strength-bar-seg" id="f-sb2"></div>
                        <div class="strength-bar-seg" id="f-sb3"></div>
                        <div class="strength-bar-seg" id="f-sb4"></div>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-[11px] font-semibold text-on-surface-variant/60 transition-colors" id="f-strength-label">Enter a password</p>
                        <p class="text-[11px] font-semibold transition-colors" id="f-pw-counter" aria-live="polite"></p>
                    </div>
                    <p class="text-[11px] font-semibold text-error mt-0.5 hidden" id="f-pw-cap-msg">Maximum 16 characters reached.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5" for="forgot-confirm-password">Confirm Password</label>
                    <div class="relative">
                        <input class="auth-input pr-10" id="forgot-confirm-password" type="password" placeholder="Repeat password" autocomplete="new-password"/>
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 hover:text-on-surface-variant transition-colors focus:outline-none" onclick="togglePassword('forgot-confirm-password', this)">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-error mt-1.5 hidden" id="forgot-confirm-error">Passwords do not match.</p>
                </div>
            </div>

            <button type="button" class="forgot-btn-primary mt-5" id="forgot-reset-btn" onclick="handleResetPassword()">
                <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                <span>Reset Password</span>
            </button>
        </div>

        <!-- STAGE 4 — Success -->
        <div class="forgot-stage" id="forgot-stage-4">
            <div class="text-center py-2">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-50 mb-4">
                    <span class="material-symbols-outlined text-emerald-600 text-[32px]">check_circle</span>
                </div>
                <h2 class="text-lg font-bold text-on-background">Password reset</h2>
                <p class="text-sm text-on-surface-variant mt-1 mb-6">You can now log in with your new password.</p>
                <button type="button" class="forgot-btn-primary" onclick="continueToLogin()">
                    <span class="material-symbols-outlined text-[18px]">login</span>
                    <span>Back to Log In</span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    /* ── State ─────────────────────────────────────────────────────────── */
    let forgotEmail = '';
    let resendTimerId = null;
    const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

    /* ── Local toast fallback (used if login.php's showToast isn't loaded,
           e.g. when testing this file on its own) ───────────────────────── */
    function notify(message, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }
        // eslint-disable-next-line no-alert
        console.log(`[${type}] ${message}`);
    }

    /* ── Modal open / close ───────────────────────────────────────────── */
    window.openForgotModal = function (e) {
        if (e && e.preventDefault) e.preventDefault();
        resetForgotModal();
        const overlay = document.getElementById('forgot-modal-overlay');
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('forgot-email').focus(), 250);
    };

    window.closeForgotModal = function () {
        const overlay = document.getElementById('forgot-modal-overlay');
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        clearInterval(resendTimerId);
    };

    document.addEventListener('keydown', function (e) {
        const overlay = document.getElementById('forgot-modal-overlay');
        if (e.key === 'Escape' && overlay && overlay.classList.contains('is-open')) {
            closeForgotModal();
        }
    });

    function resetForgotModal() {
        document.getElementById('forgot-email').value = '';
        document.getElementById('forgot-email').classList.remove('input-error');
        document.getElementById('forgot-email-error').classList.add('hidden');
        document.getElementById('forgot-new-password').value = '';
        document.getElementById('forgot-confirm-password').value = '';
        document.getElementById('forgot-confirm-error').classList.add('hidden');
        // Reset password-field counter and cap-reached message
        const fCounter = document.getElementById('f-pw-counter');
        const fCapMsg  = document.getElementById('f-pw-cap-msg');
        if (fCounter) { fCounter.textContent = ''; fCounter.className = 'text-[11px] font-semibold transition-colors'; }
        if (fCapMsg)  { fCapMsg.classList.add('hidden'); }
        checkForgotStrength('');
        clearOtpBoxes();
        goToStage(1);
    }

    /* ── Stage / step switching ───────────────────────────────────────── */
    window.goToStage = function (n) {
        document.querySelectorAll('.forgot-stage').forEach(el => el.classList.remove('is-active'));
        document.getElementById('forgot-stage-' + n).classList.add('is-active');

        document.querySelectorAll('.forgot-step').forEach(el => {
            const step = parseInt(el.dataset.step, 10);
            el.classList.remove('is-active', 'is-done');
            if (step === n) el.classList.add('is-active');
            else if (step < n) el.classList.add('is-done');
        });
    };

    function maskEmail(email) {
        const [user, domain] = email.split('@');
        if (!domain) return email;
        const visible = user.slice(0, Math.min(2, user.length));
        return `${visible}${'*'.repeat(Math.max(1, user.length - visible.length))}@${domain}`;
    }

    /* ── Button loading helper ────────────────────────────────────────── */
    function setBtnLoading(btn, loading, loadingText) {
        if (loading) {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = `<span class="forgot-spinner"></span><span>${loadingText}</span>`;
            btn.disabled = true;
        } else {
            btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
            btn.disabled = false;
        }
    }

    /* ── Stage 1: send OTP ────────────────────────────────────────────── */
    window.handleSendOtp = function () {
        const input = document.getElementById('forgot-email');
        const errEl = document.getElementById('forgot-email-error');
        const email = input.value.trim();

        if (!email || !emailRegex.test(email)) {
            input.classList.add('input-error');
            errEl.classList.remove('hidden');
            notify('Please enter a valid email address.', 'error');
            return;
        }
        input.classList.remove('input-error');
        errEl.classList.add('hidden');

        const btn = document.getElementById('forgot-send-btn');
        setBtnLoading(btn, true, 'Sending…');

        fetch('forgot-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'request_otp', email }),
        })
            .then(r => r.json())
            .then(data => {
                setBtnLoading(btn, false);
                if (!data.success) {
                    notify(data.message, 'error');
                    return;
                }
                forgotEmail = email;
                document.getElementById('forgot-masked-email').textContent = maskEmail(email);
                notify(data.message, 'success');
                goToStage(2);
                clearOtpBoxes();
                startResendTimer(data.resend_cooldown || 30);
                focusOtpBox(0);
            })
            .catch(() => {
                setBtnLoading(btn, false);
                notify('Something went wrong. Please try again.', 'error');
            });
    };

    /* ── OTP boxes ─────────────────────────────────────────────────────── */
    const otpBoxes = () => Array.from(document.querySelectorAll('.otp-box'));

    function focusOtpBox(i) {
        const boxes = otpBoxes();
        if (boxes[i]) boxes[i].focus();
    }

    function clearOtpBoxes() {
        otpBoxes().forEach(b => {
            b.value = '';
            b.classList.remove('otp-filled', 'otp-error');
        });
        document.getElementById('forgot-otp-error').classList.add('hidden');
    }

    function getOtpValue() {
        return otpBoxes().map(b => b.value).join('');
    }

    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('otp-box')) return;
        const boxes = otpBoxes();
        const i = parseInt(e.target.dataset.otpIndex, 10);
        e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 1);
        e.target.classList.toggle('otp-filled', e.target.value !== '');
        e.target.classList.remove('otp-error');
        if (e.target.value && i < boxes.length - 1) boxes[i + 1].focus();
        if (getOtpValue().length === boxes.length) {
            // Auto-submit once all 6 digits are present.
            window.handleVerifyOtp();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (!e.target.classList || !e.target.classList.contains('otp-box')) return;
        const boxes = otpBoxes();
        const i = parseInt(e.target.dataset.otpIndex, 10);
        if (e.key === 'Backspace' && !e.target.value && i > 0) {
            boxes[i - 1].focus();
        }
    });

    document.addEventListener('paste', function (e) {
        if (!e.target.classList || !e.target.classList.contains('otp-box')) return;
        const text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        if (!text) return;
        e.preventDefault();
        const boxes = otpBoxes();
        text.split('').slice(0, boxes.length).forEach((ch, idx) => {
            boxes[idx].value = ch;
            boxes[idx].classList.add('otp-filled');
        });
        focusOtpBox(Math.min(text.length, boxes.length - 1));
        if (getOtpValue().length === boxes.length) window.handleVerifyOtp();
    });

    /* ── Resend timer ──────────────────────────────────────────────────── */
    function startResendTimer(seconds) {
        const btn = document.getElementById('forgot-resend-btn');
        const timerEl = document.getElementById('forgot-resend-timer');
        let remaining = seconds;
        btn.disabled = true;
        timerEl.textContent = remaining + 's';
        clearInterval(resendTimerId);
        resendTimerId = setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(resendTimerId);
                btn.disabled = false;
                btn.innerHTML = 'Resend code';
            } else {
                timerEl.textContent = remaining + 's';
            }
        }, 1000);
    }

    window.handleResendOtp = function () {
        const btn = document.getElementById('forgot-resend-btn');
        btn.disabled = true;
        fetch('forgot-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'resend_otp' }),
        })
            .then(r => r.json())
            .then(data => {
                notify(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    clearOtpBoxes();
                    focusOtpBox(0);
                    startResendTimer(data.resend_cooldown || 30);
                } else if (data.restart) {
                    goToStage(1);
                } else {
                    btn.disabled = false;
                }
            })
            .catch(() => {
                notify('Could not resend the code. Try again.', 'error');
                btn.disabled = false;
            });
    };

    /* ── Stage 2: verify OTP ──────────────────────────────────────────── */
    window.handleVerifyOtp = function () {
        const code = getOtpValue();
        if (code.length < 6) {
            notify('Please enter the full 6-digit code.', 'error');
            return;
        }
        const btn = document.getElementById('forgot-verify-btn');
        setBtnLoading(btn, true, 'Verifying…');

        fetch('forgot-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'verify_otp', otp: code }),
        })
            .then(r => r.json())
            .then(data => {
                setBtnLoading(btn, false);
                if (!data.success) {
                    otpBoxes().forEach(b => b.classList.add('otp-error'));
                    document.getElementById('forgot-otp-error').textContent = data.message;
                    document.getElementById('forgot-otp-error').classList.remove('hidden');
                    notify(data.message, 'error');
                    if (data.restart) {
                        setTimeout(() => goToStage(1), 1200);
                    } else {
                        clearOtpBoxes();
                        focusOtpBox(0);
                    }
                    return;
                }
                notify(data.message, 'success');
                goToStage(3);
                document.getElementById('forgot-new-password').focus();
            })
            .catch(() => {
                setBtnLoading(btn, false);
                notify('Something went wrong. Please try again.', 'error');
            });
    };

    /* ── Stage 3: password strength (scoped to this modal) ───────────────── */
    // 5 conditions matching registration: length 8-16, uppercase, lowercase, number, special char.
    // Each met condition advances the 4-bar score (length + any 3 of the remaining 4 = full strong).
    window.checkForgotStrength = function (val) {
        const MAX_PW = 16;
        const bars   = ['f-sb1', 'f-sb2', 'f-sb3', 'f-sb4'];
        const label  = document.getElementById('f-strength-label');
        const counter  = document.getElementById('f-pw-counter');
        const capMsg   = document.getElementById('f-pw-cap-msg');

        // ── Character counter ──────────────────────────────────────────────
        const len = val.length;
        if (len === 0) {
            counter.textContent = '';
            counter.className = 'text-[11px] font-semibold transition-colors';
        } else {
            counter.textContent = `${len}/${MAX_PW}`;
            const atCap = len >= MAX_PW;
            counter.className = 'text-[11px] font-semibold transition-colors ' +
                (atCap ? 'text-error' : (len >= MAX_PW - 2 ? 'text-amber-500' : 'text-on-surface-variant/60'));
        }

        // Cap-reached message (also triggered from keydown; hide when below cap)
        if (len < MAX_PW) capMsg.classList.add('hidden');

        // ── 5-condition strength score ─────────────────────────────────────
        // Scoring: length in [8,16] = 1pt; each of uppercase/lowercase/digit/special = 1pt each.
        // We map 5 conditions onto 4 bars: 0 met → all grey; 1 → 1 bar; 2 → 2; 3 → 3; 4-5 → 4.
        const c = {
            length:  val.length >= 8 && val.length <= MAX_PW,
            upper:   /[A-Z]/.test(val),
            lower:   /[a-z]/.test(val),
            digit:   /[0-9]/.test(val),
            special: /[^A-Za-z0-9]/.test(val),
        };
        const metCount = Object.values(c).filter(Boolean).length;
        // Map 5-point scale to 4 bars (0→0, 1→1, 2→2, 3→3, 4-5→4)
        const score = Math.min(metCount, 4);

        const colors     = ['bg-red-400', 'bg-orange-400', 'bg-amber-400', 'bg-emerald-500'];
        const labels     = ['Too weak', 'Fair', 'Good', 'Strong'];
        const textColors = ['text-red-500', 'text-orange-500', 'text-amber-500', 'text-emerald-600'];

        bars.forEach((id, i) => {
            const el = document.getElementById(id);
            el.className = 'strength-bar-seg ' + (i < score ? colors[score - 1] : 'bg-slate-200');
        });

        if (val.length === 0) {
            label.textContent = 'Enter a password';
            label.className = 'text-[11px] font-semibold text-on-surface-variant/60 transition-colors';
        } else {
            label.textContent = labels[score - 1] || 'Too weak';
            label.className = 'text-[11px] font-semibold transition-colors ' +
                (score > 0 ? textColors[score - 1] : 'text-red-500');
        }
    };

    // Keydown-level cap detection: fires before `input` so the user gets instant feedback
    // when they attempt to type past the 16-char ceiling (the browser silently swallows the key).
    window.handleForgotPwKeydown = function (e) {
        const input  = document.getElementById('forgot-new-password');
        const capMsg = document.getElementById('f-pw-cap-msg');
        const MAX_PW = 16;
        const isNavigationKey = e.key.length > 1; // arrows, Backspace, Delete, etc.
        if (!isNavigationKey && input.value.length >= MAX_PW) {
            capMsg.classList.remove('hidden');
        } else if (e.key === 'Backspace' || e.key === 'Delete') {
            capMsg.classList.add('hidden');
        }
    };

    /* ── Stage 3: submit new password ─────────────────────────────────── */
    window.handleResetPassword = function () {
        const pwInput = document.getElementById('forgot-new-password');
        const cfInput = document.getElementById('forgot-confirm-password');
        const cfError = document.getElementById('forgot-confirm-error');
        const password = pwInput.value;
        const confirm = cfInput.value;

        pwInput.classList.remove('input-error');
        cfInput.classList.remove('input-error');
        cfError.classList.add('hidden');

        if (password.length < 8 || password.length > 16) {
            pwInput.classList.add('input-error');
            notify('Password must be 8–16 characters.', 'error');
            return;
        }
        if (!/[A-Z]/.test(password)) {
            pwInput.classList.add('input-error');
            notify('Password must contain at least one uppercase letter.', 'error');
            return;
        }
        if (!/[a-z]/.test(password)) {
            pwInput.classList.add('input-error');
            notify('Password must contain at least one lowercase letter.', 'error');
            return;
        }
        if (!/[0-9]/.test(password)) {
            pwInput.classList.add('input-error');
            notify('Password must contain at least one number.', 'error');
            return;
        }
        if (!/[^A-Za-z0-9]/.test(password)) {
            pwInput.classList.add('input-error');
            notify('Password must contain at least one special character.', 'error');
            return;
        }
        if (password !== confirm) {
            cfInput.classList.add('input-error');
            cfError.classList.remove('hidden');
            notify('Passwords do not match.', 'error');
            return;
        }

        const btn = document.getElementById('forgot-reset-btn');
        setBtnLoading(btn, true, 'Resetting…');

        fetch('forgot-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'reset_password', password, confirm }),
        })
            .then(r => r.json())
            .then(data => {
                setBtnLoading(btn, false);
                if (!data.success) {
                    notify(data.message, 'error');
                    if (data.restart) goToStage(1);
                    return;
                }
                goToStage(4);
            })
            .catch(() => {
                setBtnLoading(btn, false);
                notify('Something went wrong. Please try again.', 'error');
            });
    };

    /* ── Stage 4: continue ─────────────────────────────────────────────── */
    window.continueToLogin = function () {
        closeForgotModal();
        if (typeof window.switchTab === 'function') window.switchTab('login');
        notify('Password reset! Please log in with your new password.', 'success');
    };
})();
</script>