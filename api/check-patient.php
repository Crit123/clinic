<?php
/**
 * DentalCare Pro - Check Patient Record API Endpoint
 * Verifies if a user exists by email across users (registered) and bookings (guest history) tables.
 *
 * Security model:
 * - Registered accounts are only confirmed when a phone number in the bookings table
 *   also matches the caller's supplied phone. Without a matching second factor, a
 *   registered email is indistinguishable from an unknown one, preventing enumeration.
 * - Guest history requires the same two-factor match (email + phone) before returning
 *   any identifying information (first_name).
 */

// Set strict JSON content-type and security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

require_once __DIR__ . '/helper/_api-helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST.', 'METHOD_NOT_ALLOWED', 405);
}

try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    sendError('Database link failed', 'DB_INIT_ERROR', 500, $e);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Malformed JSON payload provided.', 'MALFORMED_JSON', 400);
}

if (!isset($input['email']) || !isset($input['phone'])) {
    sendError('Email and phone are required fields.', 'MISSING_FIELDS', 422);
}

$email = filter_var(trim((string)$input['email']), FILTER_VALIDATE_EMAIL);
if (!$email) {
    sendError('The provided email format is invalid.', 'INVALID_EMAIL', 422);
}

// Sanitize phone input: strip all non-digit characters
$inputPhoneSanitized = preg_replace('/\D/', '', (string)$input['phone']);

if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!checkRateLimit($pdo, $clientIp, 'check-patient', 10, 60)) {
    sendError('Too many requests. Please try again shortly.', 'RATE_LIMITED', 429);
}

try {
    // 1. Check `users` table to determine if the email belongs to a registered account.
    $stmtUsers = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmtUsers->execute(['email' => $email]);
    $userRow = $stmtUsers->fetch();

    if ($userRow) {
        // Email is registered. We must NOT reveal this unless the caller can also
        // supply a matching phone from a booking tied to this email. Without that
        // second factor, return the same generic response as 'new' so that
        // registered emails cannot be enumerated by probing this endpoint.
        $stmtRegBooking = $pdo->prepare(
            "SELECT phone FROM bookings WHERE email = :email ORDER BY created_at DESC LIMIT 1"
        );
        $stmtRegBooking->execute(['email' => $email]);
        $regBookingRow = $stmtRegBooking->fetch();

        if ($regBookingRow) {
            $dbPhoneSanitized = preg_replace('/\D/', '', (string)$regBookingRow['phone']);

            if ($dbPhoneSanitized === $inputPhoneSanitized) {
                // Two-factor confirmed: email is registered AND phone matches a booking.
                sendSuccess([
                    'account_status' => 'registered'
                ]);
            }
        }

        // Either no booking row exists for this registered email, or the phone did not
        // match. Fall through and respond as 'new' — same as an unknown email — so
        // callers learn nothing about whether the account exists.
        sendSuccess([
            'account_status' => 'new'
        ]);
    }

    // 2. Email not in users table. Fallback: check `bookings` for guest history.
    //    Logic below is unchanged from the original.
    $stmtBookings = $pdo->prepare(
        "SELECT first_name, last_name, email, phone FROM bookings WHERE email = :email ORDER BY created_at DESC LIMIT 1"
    );
    $stmtBookings->execute(['email' => $email]);
    $bookingRow = $stmtBookings->fetch();

    if (!$bookingRow) {
        // 3. Email exists in neither table.
        sendSuccess([
            'account_status' => 'new'
        ]);
    }

    // Email found in bookings only — perform phone matching logic.
    $dbPhoneSanitized = preg_replace('/\D/', '', (string)$bookingRow['phone']);

    if ($dbPhoneSanitized === $inputPhoneSanitized) {
        // Returning first_name here is secure because it requires a two-factor match:
        // the caller must already know BOTH the correct email and the exact phone number.
        // The rate limiting implemented above stops attackers from brute-forcing the phone.
        sendSuccess([
            'account_status' => 'guest_history',
            'match'          => 'full',
            'first_name'     => $bookingRow['first_name']
        ]);
    } else {
        sendSuccess([
            'account_status' => 'guest_history',
            'match'          => 'partial'
        ]);
    }

} catch (Exception $e) {
    sendError('A database error occurred. Please try again.', 'DB_QUERY_ERROR', 500, $e);
}