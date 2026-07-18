<?php
/**
 * DentalCare Pro - GET /client/backend/messages-auth/message-status.php?case=MSG-XXXX-XX
 *
 * Guest-safe: no login required. The case code itself is the credential --
 * it's random and unguessable (see generateCaseCode() in messages-guard.php),
 * so possession of the code is treated as proof of ownership, same as a
 * booking reference code elsewhere in this app.
 *
 * A logged-in user hitting their own case still works the same way; we
 * don't add a second, redundant ownership check here since the case code
 * is already the intended access model for this endpoint.
 */

require_once __DIR__ . '/messages-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Invalid request method.', [], 405);
}

$caseCode = trim($_GET['case'] ?? '');
if ($caseCode === '') {
    jsonResponse(false, 'Case code is required.', [], 400);
}

// Rate limit lookups by IP -- this endpoint is the one place someone could
// try to brute-force guess a valid case code.
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkRateLimit($pdo, $ip, 'message-status', 20, 300)) {
    jsonResponse(false, 'Too many lookups. Please wait a few minutes.', [], 429);
}

try {
    $stmt = $pdo->prepare(
        "SELECT case_code, category, subject, body, status, created_at, updated_at
         FROM messages
         WHERE case_code = ?"
    );
    $stmt->execute([$caseCode]);
    $message = $stmt->fetch();

    if (!$message) {
        jsonResponse(false, 'No message found for that case code.', [], 404);
    }

    $repliesStmt = $pdo->prepare(
        "SELECT sender_role, body, created_at
         FROM message_replies
         WHERE message_id = (SELECT id FROM messages WHERE case_code = ?)
         ORDER BY created_at ASC"
    );
    $repliesStmt->execute([$caseCode]);
    $replies = $repliesStmt->fetchAll();

    jsonResponse(true, 'Message found.', [
        'message' => $message,
        'replies' => $replies,
    ]);

} catch (PDOException $e) {
    error_log('message-status.php DB error: ' . $e->getMessage());
    jsonResponse(false, 'Unable to look up your case right now.', [], 500);
}