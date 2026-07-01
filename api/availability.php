<?php
/**
 * DentalCare Pro - Scheduler Availability API Endpoint
 *
 * Calculates available clinics, practitioner resources, and empty timeslots
 * on given dates to avoid double bookings.
 */

// Set strict JSON content-type and security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Require shared helpers and services registry (Single Source of Truth)
require_once __DIR__ . '/helper/_api-helpers.php';
require_once __DIR__ . '/data/services-data.php';

// Require the existing central database connection
try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    sendError('Database link failed', 'DB_INIT_ERROR', 500);
}

// Verify HTTP request method matches expected schedule retrieval specifications
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET.', 'METHOD_NOT_ALLOWED', 405);
}

// Retrieve search query targets
$date = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
$serviceKey = isset($_GET['service_key']) ? trim((string)$_GET['service_key']) : '';

if (empty($date)) {
    sendError('An active date parameter is required.', 'DATE_REQUIRED', 400);
}

// Validate date is standard ISO 8601 (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    sendError('The provided date must use YYYY-MM-DD format.', 'INVALID_DATE_FORMAT', 422);
}

// Foundation Check: Ensure the PDO instance is ready to look up available records
if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

// Define standard clinic operating hours / timeslots from the single source of truth
$allSlots = getClinicSlots();

try {
    // Query the database for active bookings on the requested date
    // Ignore cancelled statuses so those slots can be booked again
    $stmt = $pdo->prepare("SELECT appointment_time FROM bookings WHERE appointment_date = :date AND status IN ('pending', 'confirmed')");
    $stmt->execute(['date' => $date]);
    $bookedSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Calculate available slots by excluding booked times from the full schedule
    $availableSlots = array_values(array_diff($allSlots, $bookedSlots));

    // Return structured payload mirroring the previous mockScheduleData format
    // Ex: { "success": true, "data": { "2023-11-01": ["09:00 AM", "10:00 AM"] } }
    sendSuccess([
        'data' => [
            $date => $availableSlots
        ]
    ]);

} catch (PDOException $e) {
    sendError('Failed to query schedule availability.', 'DB_QUERY_ERROR', 500);
}