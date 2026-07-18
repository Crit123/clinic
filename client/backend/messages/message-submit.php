<?php
/**
 * DentalCare Pro - POST /client/backend/messages-auth/message-submit.php
 *
 * Accepts a new message from either a logged-in client or a guest. This is
 * a public-facing form (emergency contact especially must not require
 * login), so every field is validated and the endpoint is rate-limited by
 * IP + email using the existing rate_limits table.
 */

require_once __DIR__ . '/messages-guard.php';
require_once __DIR__ . '/messages-notify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.', [], 405);
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, 'Invalid session, please refresh and try again.', [], 403);
}

$allowedCategories = ['emergency', 'inquiry', 'billing', 'general'];
$category = $_POST['category'] ?? '';
if (!in_array($category, $allowedCategories, true)) {
    jsonResponse(false, 'Invalid category.', [], 400);
}

$subject = trim($_POST['subject'] ?? '');
$body    = trim($_POST['body'] ?? '');

if ($subject === '' || mb_strlen($subject) > 200) {
    jsonResponse(false, 'Subject is required and must be under 200 characters.', [], 400);
}
if ($body === '') {
    jsonResponse(false, 'Message body is required.', [], 400);
}

// Guest fields only matter if not logged in -- a logged-in user's identity
// comes from the session, never from client-supplied name/email fields,
// so those can't be spoofed to impersonate another account.
$guestName  = null;
$guestEmail = null;
$guestPhone = null;

if ($currentUserId === null) {
    $guestName  = trim($_POST['name'] ?? '');
    $guestEmail = trim($_POST['email'] ?? '');
    $guestPhone = trim($_POST['phone'] ?? '');

    if ($guestName === '' || $guestEmail === '' || $guestPhone === '') {
        jsonResponse(false, 'Name, email, and phone are required for guest submissions.', [], 400);
    }
    if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Please enter a valid email address.', [], 400);
    }
}

// Rate limit by email (guest) or user id (logged in) + endpoint name.
// Emergency submissions get a slightly higher allowance since a patient
// might reasonably submit, realize a detail is wrong, and resubmit.
$rateLimitKey = $currentUserId !== null ? "user:{$currentUserId}" : "guest:{$guestEmail}";
$rateLimitMax = $category === 'emergency' ? 5 : 3;

if (!checkRateLimit($pdo, $rateLimitKey, 'message-submit', $rateLimitMax, 600)) {
    jsonResponse(false, 'Too many submissions. Please wait a few minutes before trying again.', [], 429);
}

try {
    $caseCode = generateCaseCode($pdo);

    $stmt = $pdo->prepare(
        "INSERT INTO messages (case_code, category, sender_user_id, guest_name, guest_email, guest_phone, subject, body)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $caseCode,
        $category,
        $currentUserId,
        $guestName,
        $guestEmail,
        $guestPhone,
        $subject,
        $body,
    ]);

    // Resolve a display name + email for the notification regardless of
    // whether this was a guest or logged-in submission.
    if ($currentUserId !== null) {
        $userStmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
        $userStmt->execute([$currentUserId]);
        $user = $userStmt->fetch();
        $senderDisplay = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Registered patient';
        $senderEmail   = $user['email'] ?? null;
    } else {
        $senderDisplay = $guestName . ' (guest)';
        $senderEmail   = $guestEmail;
    }

    notifyStaffOfNewMessage($pdo, [
        'case_code'      => $caseCode,
        'category'       => $category,
        'subject'        => $subject,
        'body'           => $body,
        'sender_display' => $senderDisplay,
        'sender_email'   => $senderEmail,
    ]);

    jsonResponse(true, 'Your message has been submitted.', ['case_code' => $caseCode]);

} catch (PDOException $e) {
    error_log('message-submit.php DB error: ' . $e->getMessage());
    jsonResponse(false, 'Unable to submit your message right now. Please try again.', [], 500);
}