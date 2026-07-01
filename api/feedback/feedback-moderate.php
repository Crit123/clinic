<?php
/**
 * DentalCare Pro - Feedback Moderation API Endpoint
 *
 * Approves or rejects a pending feedback submission. Requires an
 * authenticated admin/staff session ($_SESSION['is_admin']).
 *
 * NOTE: This endpoint assumes an admin authentication mechanism exists
 * elsewhere in the application. If it doesn't yet, this endpoint will
 * correctly reject all requests until that piece is built.
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST.', 'METHOD_NOT_ALLOWED', 405);
}

if (empty($_SESSION['is_admin'])) {
    sendError('Unauthorized. Admin session required.', 'UNAUTHORIZED', 401);
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!validateCsrfToken($csrfHeader)) {
    sendError('Invalid or missing security token.', 'CSRF_VALIDATION_FAILED', 403);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Malformed JSON payload provided.', 'MALFORMED_JSON', 400);
}

$feedbackId = filter_var($input['feedback_id'] ?? null, FILTER_VALIDATE_INT);
$action = $input['action'] ?? '';

if ($feedbackId === false || $feedbackId <= 0) {
    sendError('A valid feedback ID is required.', 'INVALID_FEEDBACK_ID', 422);
}
if (!in_array($action, ['approve', 'reject'], true)) {
    sendError("Action must be 'approve' or 'reject'.", 'INVALID_ACTION', 422);
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

try {
    $stmt = $pdo->prepare("
        UPDATE feedback
        SET status = ?, approved_at = IF(? = 'approved', NOW(), NULL), moderated_by = ?
        WHERE id = ?
    ");
    $stmt->execute([$newStatus, $newStatus, $_SESSION['user_id'] ?? null, $feedbackId]);

    if ($stmt->rowCount() === 0) {
        sendError('Feedback entry not found.', 'FEEDBACK_NOT_FOUND', 404);
    }

    sendSuccess(['message' => "Feedback {$newStatus}."]);

} catch (Exception $e) {
    sendError('A database error occurred while moderating feedback.', 'DB_UPDATE_ERROR', 500, $e);
}