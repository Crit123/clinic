<?php
/**
 * DentalCare Pro - Messaging Notification Routing
 *
 * Requires PHPMailer via Composer:
 *   composer require phpmailer/phpmailer
 * (run from your project root -- this creates vendor/autoload.php)
 *
 * SMTP credentials come from environment variables, never hardcoded here --
 * same reasoning as RECORDS_ENC_KEY in records-guard.php: if this file were
 * ever served or leaked, hardcoded credentials would be a full compromise.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// Which inbox each category routes to. A plain array, not a DB table --
// this is a fixed operational decision, not data that changes per-request.
const CATEGORY_NOTIFY_EMAIL = [
    'emergency' => 'clinic-urgent@example.com',
    'inquiry'   => 'frontdesk@example.com',
    'billing'   => 'billing@example.com',
    'general'   => 'frontdesk@example.com',
];

const CATEGORY_SUBJECT_PREFIX = [
    'emergency' => '[URGENT]',
    'inquiry'   => '[Inquiry]',
    'billing'   => '[Billing]',
    'general'   => '[General]',
];

/**
 * Sends a notification email for a new message or reply. Never throws --
 * a notification failure must not block the message itself from being
 * saved and case-code-returned to the sender. Failures are logged instead.
 */
function sendMessageNotification(string $category, string $toEmail, string $subject, string $bodyHtml): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME') ?: '';
        $mail->Password   = getenv('SMTP_PASSWORD') ?: ''; // Gmail: use an App Password, not your real password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) (getenv('SMTP_PORT') ?: 587);

        $mail->setFrom(getenv('SMTP_FROM') ?: 'no-reply@dentalcarepro.example', 'DentalCare Pro');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);

        $prefix = CATEGORY_SUBJECT_PREFIX[$category] ?? '';
        $mail->Subject = trim($prefix . ' ' . $subject);
        $mail->Body    = $bodyHtml;
        $mail->AltBody  = strip_tags($bodyHtml);

        $mail->send();
        return true;

    } catch (PHPMailerException $e) {
        error_log("messages-notify.php: send failed for category={$category}: " . $mail->ErrorInfo);
        return false;
    }
}

function notifyStaffOfNewMessage(PDO $pdo, array $message): void {
    $toEmail = CATEGORY_NOTIFY_EMAIL[$message['category']] ?? CATEGORY_NOTIFY_EMAIL['general'];

    $bodyHtml = sprintf(
        '<p><strong>Case:</strong> %s</p><p><strong>Category:</strong> %s</p><p><strong>From:</strong> %s</p><p><strong>Subject:</strong> %s</p><hr><p>%s</p>',
        htmlspecialchars($message['case_code']),
        htmlspecialchars(ucfirst($message['category'])),
        htmlspecialchars($message['sender_display']),
        htmlspecialchars($message['subject']),
        nl2br(htmlspecialchars($message['body']))
    );

    sendMessageNotification(
        $message['category'],
        $toEmail,
        "New {$message['category']} message — {$message['case_code']}",
        $bodyHtml
    );
}

function notifySenderOfReply(array $message, string $replyBody): void {
    $toEmail = $message['sender_email'] ?? null;
    if (!$toEmail) {
        return; // no email on file (e.g. logged-in user with no email captured on the message row)
    }

    $bodyHtml = sprintf(
        '<p>You have a new reply on your case <strong>%s</strong>:</p><hr><p>%s</p><p style="margin-top:20px;color:#666;font-size:12px;">Check full status anytime at your case code above.</p>',
        htmlspecialchars($message['case_code']),
        nl2br(htmlspecialchars($replyBody))
    );

    sendMessageNotification(
        $message['category'],
        $toEmail,
        "Reply to your case — {$message['case_code']}",
        $bodyHtml
    );
}