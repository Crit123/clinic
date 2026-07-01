<?php
/**
 * dashboard-backend.php
 * Handles all backend actions for the Patient Dashboard.
 */

session_start();

if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, string $message, array $extra = []): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized.');
}

require_once __DIR__ . '/../../config/conn.php';
// 1. Include API Helpers for CSRF validation
require_once __DIR__ . '/../../api/helper/_api-helpers.php';

$userId = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        
        case 'get_profile':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.');
            
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, auth_provider, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$profile) {
                jsonResponse(false, 'Profile not found.');
            }
            
            jsonResponse(true, 'Profile fetched successfully.', ['profile' => $profile]);
            break;

        case 'get_booking_stats':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.');
            
            $stats = [
                'total' => 0,
                'upcoming' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];

            // Total
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats['total'] = (int) $stmt->fetchColumn();

            // Upcoming
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status IN ('pending','confirmed') AND appointment_date >= CURDATE()");
            $stmt->execute([$userId]);
            $stats['upcoming'] = (int) $stmt->fetchColumn();

            // Completed
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed' AND appointment_date < CURDATE()");
            $stmt->execute([$userId]);
            $stats['completed'] = (int) $stmt->fetchColumn();

            // Cancelled
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'cancelled'");
            $stmt->execute([$userId]);
            $stats['cancelled'] = (int) $stmt->fetchColumn();

            jsonResponse(true, 'Stats fetched successfully.', ['stats' => $stats]);
            break;

        case 'get_bookings':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.');
            
            $filter = $_GET['filter'] ?? 'all';
            $query = "SELECT id, reference_code, service_key, dentist_name, appointment_date, appointment_time, status, created_at FROM bookings WHERE user_id = :user_id";
            $params = [':user_id' => $userId];

            if ($filter === 'upcoming') {
                $query .= " AND status IN ('pending','confirmed') AND appointment_date >= CURDATE()";
            } elseif ($filter === 'past') {
                $query .= " AND status = 'confirmed' AND appointment_date < CURDATE()";
            } elseif ($filter === 'cancelled') {
                $query .= " AND status = 'cancelled'";
            }

            $query .= " ORDER BY appointment_date DESC, appointment_time DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            jsonResponse(true, 'Bookings fetched successfully.', ['bookings' => $bookings]);
            break;

        case 'get_booking_detail':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.');
            if (empty($_GET['ref'])) jsonResponse(false, 'Reference code is required.');
            
            $ref = trim($_GET['ref']);

            $stmtEmail = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmtEmail->execute([$userId]);
            $userEmail = $stmtEmail->fetchColumn();

            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE reference_code = ? AND (user_id = ? OR email = ?)");
            $stmt->execute([$ref, $userId, $userEmail]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                jsonResponse(false, 'Booking not found.');
            }

            jsonResponse(true, 'Booking fetched successfully.', ['booking' => $booking]);
            break;

        case 'cancel_booking':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Invalid request method.');
            
            // 2. Validate CSRF Token
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!validateCsrfToken($csrfToken)) {
                jsonResponse(false, 'Invalid or missing CSRF token.');
            }
            
            if (empty($_POST['ref']) && empty(json_decode(file_get_contents('php://input'), true)['reference_code'])) {
                jsonResponse(false, 'Reference code is required.');
            }

            // Support both multipart/form-data and application/json payloads
            $input = json_decode(file_get_contents('php://input'), true);
            $ref = trim($_POST['ref'] ?? $input['reference_code'] ?? '');

            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE reference_code = ? AND user_id = ?");
            $stmt->execute([$ref, $userId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                jsonResponse(false, 'Booking not found or access denied.');
            }

            if (!in_array($booking['status'], ['pending', 'confirmed'])) {
                jsonResponse(false, 'Booking is already cancelled or cannot be cancelled.');
            }

            $appointmentDate = $booking['appointment_date'];
            $appointmentTime = $booking['appointment_time'];
            $appointmentDateTime = new DateTime("$appointmentDate $appointmentTime");
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

            jsonResponse(true, 'Your appointment has been successfully cancelled.');
            break;

        case 'get_rebook_suggestion':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.');

            $stmt = $pdo->prepare("
                SELECT service_key, dentist_name 
                FROM bookings 
                WHERE user_id = ? AND (status = 'cancelled' OR appointment_date < CURDATE()) 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $suggestion = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($suggestion) {
                jsonResponse(true, 'Suggestion found.', $suggestion);
            } else {
                jsonResponse(true, 'No suggestion available.', ['suggestion' => null]);
            }
            break;

        case 'get_visit_frequency':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.');

            $stmt = $pdo->prepare("
                SELECT appointment_date, appointment_time, service_key, dentist_name
                FROM bookings
                WHERE user_id = ? AND status = 'confirmed' AND appointment_date < CURDATE()
                ORDER BY appointment_date ASC
            ");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalCompleted = count($rows);
            $lastVisit = $totalCompleted > 0 ? end($rows) : null;
            $averageGapDays = null;
            $daysSinceLastVisit = null;
            $nudgeStatus = 'no_visits';

            if ($totalCompleted > 0) {
                $now = new DateTime();
                $lastVisitDate = new DateTime($lastVisit['appointment_date']);
                $daysSinceLastVisit = $now->diff($lastVisitDate)->days;

                if ($daysSinceLastVisit <= 180) {
                    $nudgeStatus = 'on_track';
                } elseif ($daysSinceLastVisit <= 365) {
                    $nudgeStatus = 'due_soon';
                } else {
                    $nudgeStatus = 'overdue';
                }

                if ($totalCompleted >= 2) {
                    $totalGap = 0;
                    for ($i = 1; $i < $totalCompleted; $i++) {
                        $prev = new DateTime($rows[$i - 1]['appointment_date']);
                        $curr = new DateTime($rows[$i]['appointment_date']);
                        $totalGap += $curr->diff($prev)->days;
                    }
                    $averageGapDays = (int) round($totalGap / ($totalCompleted - 1));
                }
            }

            jsonResponse(true, 'Visit frequency fetched successfully.', [
                'visit_frequency' => [
                    'total_completed' => $totalCompleted,
                    'last_visit' => $lastVisit,
                    'days_since_last_visit' => $daysSinceLastVisit,
                    'average_gap_days' => $averageGapDays,
                    'nudge_status' => $nudgeStatus
                ]
            ]);
            break;

        case 'get_activity_timeline':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.');

            $stmt = $pdo->prepare("
                SELECT reference_code, service_key, dentist_name, appointment_date, status, created_at 
                FROM bookings 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            $timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);

            jsonResponse(true, 'Timeline fetched successfully.', ['timeline' => $timeline]);
            break;

        case 'update_profile':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Invalid request method.');

            // 2. Validate CSRF Token
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!validateCsrfToken($csrfToken)) {
                jsonResponse(false, 'Invalid or missing CSRF token.');
            }

            // Support both multipart/form-data and application/json payloads
            $input = json_decode(file_get_contents('php://input'), true);
            $firstName = trim($_POST['first_name'] ?? $input['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? $input['last_name'] ?? '');

            if (empty($firstName) || empty($lastName)) {
                jsonResponse(false, 'First name and last name are required.');
            }

            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $userId]);

            $_SESSION['user_name'] = $firstName;

            jsonResponse(true, 'Profile updated successfully.');
            break;

        case 'change_password':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Invalid request method.');

            // 2. Validate CSRF Token
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!validateCsrfToken($csrfToken)) {
                jsonResponse(false, 'Invalid or missing CSRF token.');
            }

            // Support both multipart/form-data and application/json payloads
            $input = json_decode(file_get_contents('php://input'), true);
            $currentPassword = $_POST['current_password'] ?? $input['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? $input['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? $input['confirm_password'] ?? '';

            $stmt = $pdo->prepare("SELECT auth_provider, password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                jsonResponse(false, 'User not found.');
            }

            if ($user['auth_provider'] === 'google') {
                jsonResponse(false, 'Password change is not available for Google Sign-In accounts.');
            }

            if (!password_verify($currentPassword, $user['password_hash'])) {
                jsonResponse(false, 'Current password is incorrect.');
            }

            if (strlen($newPassword) < 8) {
                jsonResponse(false, 'New password must be at least 8 characters.');
            }

            if ($newPassword !== $confirmPassword) {
                jsonResponse(false, 'New passwords do not match.');
            }

            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$newHash, $userId]);

            jsonResponse(true, 'Password updated successfully.');
            break;

        case 'get_notifications':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.');

            $notifications = [
                'upcoming_soon' => ['has_alert' => false, 'booking' => null],
                'pending_unconfirmed' => ['has_alert' => false, 'count' => 0]
            ];

            // Check upcoming within 48 hours
            $stmtUpcoming = $pdo->prepare("
                SELECT * FROM bookings 
                WHERE user_id = ? AND status IN ('pending', 'confirmed') AND appointment_date >= CURDATE()
                ORDER BY appointment_date ASC, appointment_time ASC
            ");
            $stmtUpcoming->execute([$userId]);
            $upcomingBookings = $stmtUpcoming->fetchAll(PDO::FETCH_ASSOC);

            $now = new DateTime();
            foreach ($upcomingBookings as $booking) {
                $dt = new DateTime($booking['appointment_date'] . ' ' . $booking['appointment_time']);
                $diffHours = ($dt->getTimestamp() - $now->getTimestamp()) / 3600;
                
                if ($diffHours >= 0 && $diffHours <= 48) {
                    $notifications['upcoming_soon']['has_alert'] = true;
                    $notifications['upcoming_soon']['booking'] = $booking;
                    break;
                }
            }

            // Check pending count
            $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'pending' AND appointment_date >= CURDATE()");
            $stmtPending->execute([$userId]);
            $pendingCount = (int) $stmtPending->fetchColumn();

            if ($pendingCount > 0) {
                $notifications['pending_unconfirmed']['has_alert'] = true;
                $notifications['pending_unconfirmed']['count'] = $pendingCount;
            }

            jsonResponse(true, 'Notifications fetched successfully.', ['notifications' => $notifications]);
            break;

        case 'claim_guest_booking':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Invalid request method.');
            
            // 2. Validate CSRF Token
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!validateCsrfToken($csrfToken)) {
                jsonResponse(false, 'Invalid or missing CSRF token.');
            }
            
            // Support both multipart/form-data and application/json payloads
            $input = json_decode(file_get_contents('php://input'), true);
            $ref = trim($_POST['reference_code'] ?? $input['reference_code'] ?? '');
            $inputEmail = trim($_POST['email'] ?? $input['email'] ?? '');

            if (empty($ref) || empty($inputEmail)) {
                jsonResponse(false, 'Reference code and email are required.');
            }

            $stmtUserEmail = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmtUserEmail->execute([$userId]);
            $actualEmail = $stmtUserEmail->fetchColumn();

            if (strtolower($inputEmail) !== strtolower($actualEmail)) {
                jsonResponse(false, 'No guest booking found with that reference code and email.');
            }

            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE reference_code = ? AND email = ?");
            $stmt->execute([$ref, $inputEmail]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                jsonResponse(false, 'No guest booking found with that reference code and email.');
            }

            if (!is_null($booking['user_id'])) {
                jsonResponse(false, 'This booking has already been linked to an account.');
            }

            $updateStmt = $pdo->prepare("UPDATE bookings SET user_id = ? WHERE id = ?");
            $updateStmt->execute([$userId, $booking['id']]);
            
            $booking['user_id'] = $userId; // Update object to return

            jsonResponse(true, 'Booking successfully linked to your account.', ['booking' => $booking]);
            break;

        default:
            jsonResponse(false, 'Unknown action.');
            break;
    }

} catch (PDOException $e) {
    error_log("Database Error in dashboard-backend.php: " . $e->getMessage());
    jsonResponse(false, 'An unexpected server error occurred.');
}