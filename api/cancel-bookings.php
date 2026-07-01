<?php
/**
 * DentalCare Pro - Appointment Cancellation API Endpoint
 *
 * Verifies caller identity and cancels a designated dental booking,
 * logging the operational event securely within the cancellation ledger.
 */

// 2. Start session and set safety headers
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Require shared API helpers (provides sendSuccess, sendError, validateCsrfToken, etc.)
require_once __DIR__ . '/helper/_api-helpers.php';

// Require DB connection wrapper
try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    sendError('Database link failed', 'DB_INIT_ERROR', 500, $e);
}

// 1. Accepts POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST.', 'METHOD_NOT_ALLOWED', 405);
}

// 3. Validate that $_SESSION['user_id'] exists — return 401 if not
if (!isset($_SESSION['user_id'])) {
    sendError('Unauthorized session access. Please log in first.', 'UNAUTHORIZED', 401);
}

// CSRF Protection Check
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!validateCsrfToken($csrfHeader)) {
    sendError('Invalid or missing security token. Please refresh the page and try again.', 'CSRF_VALIDATION_FAILED', 403);
}

$sessionUserId = (int)$_SESSION['user_id'];

// Parse incoming JSON body
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Malformed JSON payload provided.', 'MALFORMED_JSON', 400);
}

$bookingId = $input['booking_id'] ?? null;
$refCode   = isset($input['reference_code']) ? trim((string)$input['reference_code']) : '';

// 4. Validate booking_id is a positive integer
$cleanBookingId = filter_var($bookingId, FILTER_VALIDATE_INT);
if ($cleanBookingId === false || $cleanBookingId <= 0) {
    sendError('A valid positive integer booking ID is required.', 'INVALID_BOOKING_ID', 422);
}

// Clean reference code (ensure leading hash exists for database format comparison)
if ($refCode !== '' && $refCode[0] !== '#') {
    $refCode = '#' . $refCode;
}

// 5. Validate reference_code matches pattern: #DCP-[A-F0-9]{8}
if (!preg_match('/^#DCP-[A-F0-9]{8}$/i', $refCode)) {
    sendError('Invalid reference code format. Expected format: #DCP-XXXXXXXX', 'INVALID_REFERENCE_CODE', 422);
}

// Verify active DB connection
if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

try {
    // Retrieve the user email to perform authorization checks
    $userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $userStmt->execute([$sessionUserId]);
    $userRow = $userStmt->fetch();
    $sessionUserEmail = $userRow ? $userRow['email'] : null;

    // 6. Verify booking identity & user permissions
    $bookingStmt = $pdo->prepare("
        SELECT id, user_id, email, status, appointment_date 
        FROM bookings 
        WHERE id = :id AND reference_code = :ref 
        LIMIT 1
    ");
    $bookingStmt->execute([
        'id'  => $cleanBookingId,
        'ref' => $refCode
    ]);
    
    $booking = $bookingStmt->fetch();

    // 7. If not found → return 404
    if (!$booking) {
        sendError('Appointment booking not found.', 'BOOKING_NOT_FOUND', 404);
    }

    // Authorize caller: must be matching user_id OR matching clinical email profile
    $isOwner = ((int)$booking['user_id'] === $sessionUserId) || 
               ($sessionUserEmail !== null && strcasecmp($booking['email'], $sessionUserEmail) === 0);

    if (!$isOwner) {
        sendError('You do not have permission to modify this appointment.', 'FORBIDDEN_ACCESS', 403);
    }

    // 8. If status is already cancelled → return 409
    if (strtolower($booking['status']) === 'cancelled') {
        sendError('This booking is already cancelled.', 'ALREADY_CANCELLED', 409);
    }

    // 9. If appointment_date is in the past → return 422
    $appointmentTimestamp = strtotime($booking['appointment_date']);
    $todayTimestamp       = strtotime(date('Y-m-d'));
    
    if ($appointmentTimestamp < $todayTimestamp) {
        sendError('Cannot cancel a past appointment.', 'PAST_APPOINTMENT_REJECTED', 422);
    }

    // 10 & 11. Transaction boundaries to write status updates and logs
    $pdo->beginTransaction();

    // Perform Update
    $updateStmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = :id");
    $updateStmt->execute(['id' => $cleanBookingId]);

    // Insert Cancellation Record
    $logStmt = $pdo->prepare("
        INSERT INTO cancellation_logs (booking_id, user_id, reference_code, reason) 
        VALUES (:bid, :uid, :ref, 'user_requested')
    ");
    $logStmt->execute([
        'bid' => $cleanBookingId,
        'uid' => $sessionUserId,
        'ref' => $refCode
    ]);

    $pdo->commit();

    // 12. Return Success
    sendSuccess(['message' => 'Appointment cancelled.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Mask the raw exception detail from the client but pass it to sendError for server-side logging
    sendError('A database error occurred while cancelling the appointment. Please try again.', 'DB_TRANSACTION_ERROR', 500, $e);
}