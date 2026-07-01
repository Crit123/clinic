<?php
/**
 * DentalCare Pro - Booking Lookup API Endpoint
 *
 * Verifies dental appointment details dynamically from the ledger
 * based on unique reference validation tokens.
 * * NOTE: This endpoint uses a deliberate two-factor design (reference code + email) 
 * to prevent PII exposure from a leaked reference code alone.
 */

// Set strict JSON content-type and security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Require shared API helpers (provides sendSuccess, sendError, and checkRateLimit)
require_once __DIR__ . '/helper/_api-helpers.php';

// Require the existing central database connection
try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    sendError('Database link failed', 'DB_INIT_ERROR', 500);
}

// Require single source of truth for service label resolution
require_once __DIR__ . '/data/services-data.php';

// Verify HTTP request method matches expected ledger querying specification
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET.', 'METHOD_NOT_ALLOWED', 405);
}

// Extract lookup token parameters, supporting both `ref` and `reference_code`
$refCode = isset($_GET['ref'])
    ? trim((string)$_GET['ref'])
    : (isset($_GET['reference_code']) ? trim((string)$_GET['reference_code']) : '');

if (empty($refCode)) {
    sendError('An active booking reference code token is required.', 'REFERENCE_REQUIRED', 400);
}

// Ensure the second factor (email) is provided
$verifyEmail = isset($_GET['email']) ? trim((string)$_GET['email']) : '';

if (empty($verifyEmail)) {
    sendError('Please also provide the email address used for this booking.', 'VERIFICATION_REQUIRED', 422);
}

// Append # if missing to match the database standard saving format
if (!str_starts_with($refCode, '#') && str_starts_with(strtoupper($refCode), 'DCP-')) {
    $refCode = '#' . strtoupper($refCode);
}

// Enforce standard token formatting (e.g. #DCP-XXXXXXXX or simply DCP-XXXXXXXX)
if (!preg_match('/^#?DCP-[A-F0-9]{8}$/i', $refCode)) {
    sendError('The provided booking reference token format is structurally invalid.', 'INVALID_REFERENCE_FORMAT', 422);
}

// Foundation Check: Ensure the PDO instance exists
if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

// Apply rate limiting based on client IP
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!checkRateLimit($pdo, $clientIp, 'booking-lookup', 15, 60)) {
    sendError('Too many lookup attempts. Please try again shortly.', 'RATE_LIMITED', 429);
}

try {
    // Perform Real Database Lookup based on the reference code
    $stmt = $pdo->prepare("
        SELECT id, reference_code, first_name, last_name, email, phone,
               service_key, dentist_name, appointment_date, appointment_time,
               status, created_at
        FROM bookings
        WHERE reference_code = :ref
        LIMIT 1
    ");
    $stmt->execute(['ref' => $refCode]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    // Intentionally return the exact same generic BOOKING_NOT_FOUND error 
    // whether the reference code doesn't exist or the email doesn't match
    if (!$booking) {
        sendError('No booking found matching the provided details.', 'BOOKING_NOT_FOUND', 404);
    }

    if (strcasecmp($booking['email'], $verifyEmail) !== 0) {
        sendError('No booking found matching the provided details.', 'BOOKING_NOT_FOUND', 404);
    }

    // Enrich the booking record with the canonical service label from services-data.php
    $booking['service_label'] = getServiceLabel($booking['service_key']) ?? $booking['service_key'];

    // Wrap in a 'data' array so the shared sendSuccess outputs the exact same JSON 
    // payload structure as the old locally-defined sendSuccess function did.
    sendSuccess([
        'data' => [
            'lookup_status' => 'VERIFIED',
            'booking'       => $booking
        ]
    ]);

} catch (PDOException $e) {
    sendError('Database query failed while looking up ledger entry.', 'DB_QUERY_ERROR', 500);
}