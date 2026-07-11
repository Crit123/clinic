<?php
/**
 * appointments-backend.php
 * Handles all backend actions for the "My Appointments" page.
 *
 * Shared boilerplate (session check, jsonResponse, CSRF, DB/helper includes)
 * lives in shared/backend-common.php, alongside this file and
 * dashboard-backend.php. Any new page backend should follow the same
 * pattern: require the shared file, call requireLogin(), switch on
 * ?action=.
 */

require_once __DIR__ . '/shared/backend-common.php';

$userId = requireLogin();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // Returns ALL of the user's bookings in one call. The page classifies
        // them into upcoming/completed/cancelled client-side (same as the
        // existing appointments.php tab logic), so a single fetch covers the
        // stats row and all three tabs without refetching per tab.
        case 'get_bookings':
            requireMethod('GET');

            $stmt = $pdo->prepare("
                SELECT id, reference_code, service_key, dentist_name,
                       appointment_date, appointment_time, status,
                       first_name, last_name, email, phone, notes, created_at
                FROM bookings
                WHERE user_id = ?
                ORDER BY appointment_date DESC, appointment_time DESC
            ");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Shape rows to match what the front-end card/modal rendering
            // expects (date/time/service/dentist/patient_name keys).
            $bookings = array_map(function ($row) {
                return [
                    'id'               => (int) $row['id'],
                    'reference_code'   => $row['reference_code'],
                    'service'          => $row['service_key'],
                    'dentist'          => $row['dentist_name'],
                    'date'             => $row['appointment_date'],
                    'time'             => $row['appointment_time'],
                    'status'           => $row['status'],
                    'patient_name'     => trim($row['first_name'] . ' ' . $row['last_name']),
                    'email'            => $row['email'],
                    'phone'            => $row['phone'],
                    'notes'            => $row['notes'],
                    'created_at'       => $row['created_at'],
                ];
            }, $rows);

            jsonResponse(true, 'Bookings fetched successfully.', ['bookings' => $bookings]);
            break;

        // Single-booking detail lookup, scoped to the logged-in user, for
        // the "View Details" modal.
        case 'get_booking_detail':
            requireMethod('GET');
            if (empty($_GET['ref'])) jsonResponse(false, 'Reference code is required.');

            $ref = trim($_GET['ref']);

            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE reference_code = ? AND user_id = ?");
            $stmt->execute([$ref, $userId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                jsonResponse(false, 'Booking not found.');
            }

            jsonResponse(true, 'Booking fetched successfully.', ['booking' => $booking]);
            break;

        // Same cancellation rules as dashboard-backend.php's cancel_booking
        // (24h cutoff, 24-48h flagged as late_cancellation, 3-per-30-days cap),
        // kept in sync here since this page has its own cancel modal.
        case 'cancel_booking':
            requireMethod('POST');
            requireCsrf();

            $input = getRequestInput();
            $ref = trim($input['reference_code'] ?? '');
            $bookingId = isset($input['booking_id']) ? (int) $input['booking_id'] : 0;

            if (empty($ref) && !$bookingId) {
                jsonResponse(false, 'Reference code is required.');
            }

            if ($ref) {
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE reference_code = ? AND user_id = ?");
                $stmt->execute([$ref, $userId]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
                $stmt->execute([$bookingId, $userId]);
            }
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                jsonResponse(false, 'Booking not found or access denied.');
            }

            if (!in_array($booking['status'], ['pending', 'confirmed'])) {
                jsonResponse(false, 'Booking is already cancelled or cannot be cancelled.');
            }

            $appointmentDateTime = new DateTime("{$booking['appointment_date']} {$booking['appointment_time']}");
            $now = new DateTime();

            if ($appointmentDateTime < $now) {
                jsonResponse(false, 'Cannot cancel past appointments.');
            }

            $reason = 'user_requested';

            if ($booking['status'] === 'confirmed') {
                $hoursDiff = ($appointmentDateTime->getTimestamp() - $now->getTimestamp()) / 3600;

                if ($hoursDiff < 24) {
                    jsonResponse(false, 'Cancellations are not allowed within 24 hours of your appointment. Please call the clinic directly.');
                } elseif ($hoursDiff <= 48) {
                    $reason = 'late_cancellation';
                }
            }

            $stmtAbuse = $pdo->prepare("SELECT COUNT(*) FROM cancellation_logs WHERE user_id = ? AND cancelled_at >= NOW() - INTERVAL 30 DAY");
            $stmtAbuse->execute([$userId]);
            $cancelCount = (int) $stmtAbuse->fetchColumn();

            if ($cancelCount >= 3) {
                jsonResponse(false, 'You have reached the maximum cancellations allowed this month. Please contact the clinic directly.');
            }

            $pdo->beginTransaction();

            $updateStmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $updateStmt->execute([$booking['id']]);

            $logStmt = $pdo->prepare("INSERT INTO cancellation_logs (booking_id, user_id, reference_code, reason) VALUES (?, ?, ?, ?)");
            $logStmt->execute([$booking['id'], $userId, $booking['reference_code'], $reason]);

            $pdo->commit();

            jsonResponse(true, 'Your appointment cancellation has been successfully submitted.');
            break;

        default:
            jsonResponse(false, 'Unknown action.');
            break;
    }

} catch (PDOException $e) {
    error_log("Database Error in appointments-backend.php: " . $e->getMessage());
    jsonResponse(false, 'An unexpected server error occurred.');
}