<?php
/**
 * DentalCare Pro - Booking Creation API Endpoint
 *
 * Receives dental booking payloads, validates structural inputs,
 * and prepares structured medical ledger storage transactions.
 * Prevents double booking and handles race conditions.
 */

// Set strict JSON content-type and security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require the shared API helpers for responses & CSRF
require_once __DIR__ . '/helper/_api-helpers.php';

// Require the existing central database connection
try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    sendError('Database link failed', 'DB_INIT_ERROR', 500, $e);
}

// Require single source of truth for service definitions
require_once __DIR__ . '/data/services-data.php';

// Verify HTTP request method matches expected clinical transaction specifications
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST.', 'METHOD_NOT_ALLOWED', 405);
}

// Validate CSRF token before performing any business logic or DB calls
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!validateCsrfToken($csrfHeader)) {
    sendError('Invalid or missing security token. Please refresh the page and try again.', 'CSRF_VALIDATION_FAILED', 403);
}

// Rate limiting: allow max 5 booking submissions per IP per 10 minutes.
// This prevents scripted slot-spamming or denial-of-service via appointment flooding.
// Placed after CSRF (rejects unauthenticated bots cheaply first) and before any
// payload parsing or DB work, so the counter only increments on plausible requests.
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit($pdo, $clientIp, 'booking_create', 5, 600)) {
    sendError(
        'Too many booking attempts from your connection. Please wait a few minutes and try again.',
        'RATE_LIMITED',
        429
    );
}

// Parse input payload
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Malformed JSON payload provided.', 'MALFORMED_JSON', 400);
}

// Validate required registration variables
$requiredFields = ['firstName', 'lastName', 'email', 'phone', 'service', 'date', 'time'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || trim((string)$input[$field]) === '') {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    sendError(
        'Required clinical fields missing: ' . implode(', ', $missingFields),
        'VALIDATION_FAILED',
        422
    );
}

// Extract and sanitize input targets
$firstName = trim((string)$input['firstName']);
$lastName  = trim((string)$input['lastName']);
$email     = filter_var(trim((string)$input['email']), FILTER_VALIDATE_EMAIL);
$phone     = trim((string)$input['phone']);

// Light normalization and validation of the phone number
$phoneDigits = preg_replace('/\D/', '', $phone);
if (strlen($phoneDigits) < 7) {
    sendError('The provided phone number appears to be invalid.', 'INVALID_PHONE', 422);
}

$service   = trim((string)$input['service']);
$date      = trim((string)$input['date']);
$time      = trim((string)$input['time']);
$notes     = isset($input['notes']) ? trim((string)$input['notes']) : '';
$userId    = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// Validate structured patterns
if (!$email) {
    sendError('The provided email format is invalid.', 'INVALID_EMAIL', 422);
}

// Validate service key against the canonical whitelist from services-data.php
if (!isValidServiceKey($service)) {
    sendError(
        'The provided service key is not recognised. Valid keys: ' . implode(', ', array_keys(getAllServices())),
        'INVALID_SERVICE_KEY',
        422
    );
}

// Validate date is standard ISO 8601 YYYY-MM-DD and not in the past
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    sendError('The provided date must use YYYY-MM-DD format.', 'INVALID_DATE_FORMAT', 422);
}

$selectedTimestamp = strtotime($date);
$todayTimestamp    = strtotime(date('Y-m-d'));
if ($selectedTimestamp <= $todayTimestamp) {
    sendError('Appointments must be scheduled at least one day in advance.', 'SAME_DAY_REJECTED', 422);
}

// Validate time slot against the canonical slots listing
$clinicSlots = getClinicSlots();
if (!in_array($time, $clinicSlots, true)) {
    sendError('The selected time slot is not valid.', 'INVALID_TIME_SLOT', 422);
}

// Foundation Check: Ensure the PDO resource is available for active query planning
if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

// Generate Unique Reference Code
$refCode     = '#DCP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
$dentistName = "Dr. Maria Santos"; // Default dentist selection

function findNextAvailableSlot(PDO $pdo, string $fromDate): ?array {
    $allSlots = getClinicSlots();
    for ($i = 1; $i <= 14; $i++) {
        $checkDate = date('Y-m-d', strtotime($fromDate . " +{$i} days"));
        $stmt = $pdo->prepare("
            SELECT appointment_time FROM bookings
            WHERE appointment_date = ? AND status IN ('pending','confirmed')
        ");
        $stmt->execute([$checkDate]);
        $taken = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $free  = array_values(array_diff($allSlots, $taken));
        if (!empty($free)) {
            return ['date' => $checkDate, 'time' => $free[0]];
        }
    }
    return null;
}

try {
    // 1. Begin Database Transaction to prevent race conditions
    $pdo->beginTransaction();

    // 2. Check for existing active bookings (Double Booking Validation)
    $checkStmt = $pdo->prepare("
        SELECT 1
        FROM bookings
        WHERE appointment_date = ?
          AND appointment_time = ?
          AND dentist_name = ?
          AND status IN ('pending', 'confirmed')
        FOR UPDATE
    ");
    $checkStmt->execute([$date, $time, $dentistName]);

    // If a record exists, slot is taken
    if ($checkStmt->fetch()) {
        $allSlots = getClinicSlots();

        $altStmt = $pdo->prepare("
            SELECT appointment_time
            FROM bookings
            WHERE appointment_date = ?
              AND dentist_name = ?
              AND status IN ('pending', 'confirmed')
        ");
        $altStmt->execute([$date, $dentistName]);
        $bookedSlots = $altStmt->fetchAll(PDO::FETCH_COLUMN);

        $availableSlots = array_values(array_diff($allSlots, $bookedSlots));

        $nextAvailable = findNextAvailableSlot($pdo, $date);
        
        // Rollback transaction immediately
        $pdo->rollBack();

        // Return expected conflict response
        http_response_code(409); // 409 Conflict
        echo json_encode([
            'success'      => false,
            'error_code'   => 'SLOT_TAKEN',
            'message'      => 'Selected slot is no longer available.',
            'alternatives' => $availableSlots,
            'next_available' => $nextAvailable
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    // 3. Execute insertion logic
    $stmt = $pdo->prepare("
        INSERT INTO bookings
        (user_id, reference_code, first_name, last_name, email, phone, notes, service_key, dentist_name, appointment_date, appointment_time, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
    ");
    $stmt->execute([$userId, $refCode, $firstName, $lastName, $email, $phone, $notes, $service, $dentistName, $date, $time]);

    // 4. Commit transaction
    $pdo->commit();

    // Respond back to frontend application
    sendSuccess([
        'reference_code' => $refCode,
        'summary'        => [
            'patient_name'     => $firstName . ' ' . $lastName,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'service_label'    => getServiceLabel($service),
        ]
    ]);

} catch (Exception $e) {
    // Rollback changes if an error occurs during transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Prevent sensitive DB schema leakage
    sendError('A database error occurred while creating the booking. Please try again.', 'DB_INSERT_ERROR', 500, $e);
}