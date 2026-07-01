<?php
/**
 * DentalCare Pro - Feedback Submission API Endpoint
 *
 * Accepts patient feedback/ratings. Submitters must be matched against
 * an existing completed booking (by email) — this is the "verification"
 * mechanism, in place of requiring full account registration. All
 * submissions start as 'pending' and require approval (see
 * feedback-moderate.php) before appearing publicly.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database link failed', 'error_code' => 'DB_INIT_ERROR']);
    exit;
}

require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/data/services-data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST.', 'METHOD_NOT_ALLOWED', 405);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Malformed JSON payload provided.', 'MALFORMED_JSON', 400);
}

$email = filter_var(trim((string)($input['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$rating = filter_var($input['rating'] ?? null, FILTER_VALIDATE_INT);
$comment = trim((string)($input['comment'] ?? ''));
$serviceKey = isset($input['service_key']) && trim((string)$input['service_key']) !== '' ? trim((string)$input['service_key']) : null;
$displayName = trim((string)($input['display_name'] ?? ''));

if (!$email) {
    sendError('A valid email address is required.', 'INVALID_EMAIL', 422);
}
if ($rating === false || $rating < 1 || $rating > 5) {
    sendError('Rating must be a whole number between 1 and 5.', 'INVALID_RATING', 422);
}
if (mb_strlen($comment) < 10) {
    sendError('Feedback must be at least 10 characters long.', 'COMMENT_TOO_SHORT', 422);
}
if (mb_strlen($comment) > 1000) {
    sendError('Feedback must be under 1000 characters.', 'COMMENT_TOO_LONG', 422);
}
if ($serviceKey !== null && !isValidServiceKey($serviceKey)) {
    sendError('The provided service key is not recognised.', 'INVALID_SERVICE_KEY', 422);
}
if ($displayName === '') {
    sendError('A display name is required.', 'DISPLAY_NAME_REQUIRED', 422);
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

// Rate limit by email — prevents one person spamming many submissions
if (!checkRateLimit($pdo, $email, 'feedback-create', 3, 86400)) {
    sendError('You have reached the maximum number of feedback submissions allowed per day.', 'RATE_LIMITED', 429);
}

try {
    // Verify the submitter has a real completed booking on file
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE email = ? AND status = 'confirmed' LIMIT 1");
    $stmt->execute([$email]);
    $bookingMatch = $stmt->fetch();

    if (!$bookingMatch) {
        sendError('We could not find a completed appointment matching this email. Feedback can only be submitted by patients with a booking on file.', 'NO_MATCHING_BOOKING', 403);
    }

    $insert = $pdo->prepare("
        INSERT INTO feedback (booking_id, display_name, email, service_key, rating, comment, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");
    $insert->execute([
        $bookingMatch['id'],
        $displayName,
        $email,
        $serviceKey,
        $rating,
        $comment
    ]);

    sendSuccess([
        'message' => 'Thank you! Your feedback has been submitted and will appear once reviewed.'
    ], 201);

} catch (Exception $e) {
    sendError('A database error occurred while submitting your feedback.', 'DB_INSERT_ERROR', 500, $e);
}