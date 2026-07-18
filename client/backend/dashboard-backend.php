<?php
/**
 * dashboard-backend.php
 * Handles all backend actions for the Patient Dashboard.
 *
 * Shared boilerplate (session check, jsonResponse, CSRF, DB/helper includes)
 * lives in shared/backend-common.php — see that file for the pattern any
 * new page backend should follow.
 */

require_once __DIR__ . '/shared/backend-common.php';

$userId = requireLogin();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'get_profile':
            requireMethod('GET');

            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, avatar_filename, date_of_birth, auth_provider, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                jsonResponse(false, 'Profile not found.');
            }

            jsonResponse(true, 'Profile fetched successfully.', ['profile' => $profile]);
            break;

        case 'upload_avatar':
            requireMethod('POST');
            requireCsrf();

            if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
                jsonResponse(false, 'Please choose a photo to upload.');
            }

            $file = $_FILES['avatar'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                jsonResponse(false, 'Upload failed. Please try again.');
            }

            // 1MB max, matching the limit shown in the UI.
            $maxBytes = 1 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                jsonResponse(false, 'Image must be 1MB or smaller.');
            }

            // Validate the ACTUAL file content, never trust the client-supplied
            // extension or MIME type -- someone could rename a .php file to .jpg.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($file['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

            if (!isset($allowed[$detectedMime])) {
                jsonResponse(false, 'Only JPG, GIF, or PNG images are allowed.');
            }
            $ext = $allowed[$detectedMime];

            // Random on-disk filename -- never the original uploaded filename --
            // same pattern as dental_records.stored_filename (records.sql).
            $newFilename = bin2hex(random_bytes(16)) . '.' . $ext;
            $uploadDir = __DIR__ . '/../../assets/uploads/avatars/';

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                jsonResponse(false, 'Server storage is not available. Please try again later.');
            }

            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
                jsonResponse(false, 'Could not save the uploaded image. Please try again.');
            }

            // Clean up the previous avatar file so uploads don't pile up on disk.
            $stmt = $pdo->prepare("SELECT avatar_filename FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $oldFilename = $stmt->fetchColumn();

            $stmt = $pdo->prepare("UPDATE users SET avatar_filename = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newFilename, $userId]);

            if (!empty($oldFilename) && is_file($uploadDir . $oldFilename)) {
                @unlink($uploadDir . $oldFilename);
            }

            jsonResponse(true, 'Profile photo updated.', ['avatar_filename' => $newFilename]);
            break;

        case 'get_booking_stats':
            requireMethod('GET');

            $stats = [
                'total' => 0,
                'upcoming' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats['total'] = (int) $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status IN ('pending','confirmed') AND appointment_date >= CURDATE()");
            $stmt->execute([$userId]);
            $stats['upcoming'] = (int) $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed' AND appointment_date < CURDATE()");
            $stmt->execute([$userId]);
            $stats['completed'] = (int) $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'cancelled'");
            $stmt->execute([$userId]);
            $stats['cancelled'] = (int) $stmt->fetchColumn();

            jsonResponse(true, 'Stats fetched successfully.', ['stats' => $stats]);
            break;

        case 'get_bookings':
            requireMethod('GET');

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
            requireMethod('GET');
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
            requireMethod('POST');
            requireCsrf();

            $input = getRequestInput();
            $ref = trim($input['ref'] ?? $input['reference_code'] ?? '');

            if (empty($ref)) {
                jsonResponse(false, 'Reference code is required.');
            }

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
            requireMethod('GET');

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
            requireMethod('GET');

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
            requireMethod('GET');

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
            requireMethod('POST');
            requireCsrf();

            $input = getRequestInput();
            $firstName = trim($input['first_name'] ?? '');
            $lastName = trim($input['last_name'] ?? '');
            $email = trim($input['email'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $dob = trim($input['date_of_birth'] ?? '');

            if (empty($firstName) || empty($lastName)) {
                jsonResponse(false, 'First name and last name are required.');
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(false, 'A valid email address is required.');
            }

            // Email uniqueness check, excluding the current user's own row --
            // otherwise re-saving your own unchanged email would incorrectly
            // fail as "already taken".
            $emailCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $emailCheck->execute([$email, $userId]);
            if ($emailCheck->fetch()) {
                jsonResponse(false, 'That email address is already in use by another account.');
            }

            if ($dob !== '') {
                $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
                if (!$dobDate || $dobDate->format('Y-m-d') !== $dob || $dobDate > new DateTime()) {
                    jsonResponse(false, 'Please enter a valid date of birth.');
                }
            } else {
                $dob = null;
            }

            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, date_of_birth = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $email, $phone ?: null, $dob, $userId]);

            $_SESSION['user_name'] = $firstName;

            jsonResponse(true, 'Profile updated successfully.');
            break;

        case 'get_sessions':
            requireMethod('GET');

            $currentTokenHash = hashSessionId(session_id());

            $stmt = $pdo->prepare("
                SELECT id, device_label, ip_address, last_active, created_at,
                       (session_token = ?) AS is_current
                FROM user_sessions
                WHERE user_id = ?
                ORDER BY is_current DESC, last_active DESC
            ");
            $stmt->execute([$currentTokenHash, $userId]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($sessions as &$s) {
                $s['is_current'] = (bool) $s['is_current'];
            }
            unset($s);

            jsonResponse(true, 'Sessions fetched successfully.', ['sessions' => $sessions]);
            break;

        case 'logout_session':
            requireMethod('POST');
            requireCsrf();

            $input = getRequestInput();
            $sessionRowId = (int) ($input['session_id'] ?? 0);

            if ($sessionRowId <= 0) {
                jsonResponse(false, 'A session must be specified.');
            }

            $stmt = $pdo->prepare("SELECT session_token FROM user_sessions WHERE id = ? AND user_id = ?");
            $stmt->execute([$sessionRowId, $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                jsonResponse(false, 'Session not found.');
            }

            // Logging out the device you're currently on needs to actually end
            // this PHP session too, which is a different action than just
            // deleting a tracking row -- keep this endpoint scoped to *other*
            // devices and point the person to the real logout instead.
            if (hash_equals($row['session_token'], hashSessionId(session_id()))) {
                jsonResponse(false, 'To log out of this device, use the Log Out option in the menu instead.');
            }

            $deleteStmt = $pdo->prepare("DELETE FROM user_sessions WHERE id = ? AND user_id = ?");
            $deleteStmt->execute([$sessionRowId, $userId]);

            jsonResponse(true, 'Device logged out.');
            break;

        case 'logout_other_sessions':
            requireMethod('POST');
            requireCsrf();

            $currentTokenHash = hashSessionId(session_id());

            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_token != ?");
            $stmt->execute([$userId, $currentTokenHash]);

            // Also invalidate "Remember Me" so a logged-out device can't
            // silently re-authenticate via its remember_token cookie.
            $clearRemember = $pdo->prepare("UPDATE users SET remember_token = NULL, remember_token_expires_at = NULL WHERE id = ?");
            $clearRemember->execute([$userId]);

            jsonResponse(true, 'Logged out of all other devices.');
            break;

        case 'get_notification_prefs':
            requireMethod('GET');

            $stmt = $pdo->prepare("SELECT email_reminders, sms_reminders, post_visit_summaries FROM notification_preferences WHERE user_id = ?");
            $stmt->execute([$userId]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);

            // No row yet (never saved) -- default everything to on, matching
            // the hardcoded "checked" state the frontend previously showed.
            if (!$prefs) {
                $prefs = [
                    'email_reminders' => 1,
                    'sms_reminders' => 1,
                    'post_visit_summaries' => 1,
                ];
            }

            jsonResponse(true, 'Notification preferences fetched successfully.', ['preferences' => $prefs]);
            break;

        case 'update_notification_prefs':
            requireMethod('POST');
            requireCsrf();

            $input = getRequestInput();
            // Checkbox semantics: a checkbox not present in the submitted
            // data means "unchecked", not "leave unchanged" -- so absence
            // is treated as 0, same as an HTML form naturally behaves.
            $emailReminders = !empty($input['email_reminders']) ? 1 : 0;
            $smsReminders = !empty($input['sms_reminders']) ? 1 : 0;
            $postVisitSummaries = !empty($input['post_visit_summaries']) ? 1 : 0;

            $stmt = $pdo->prepare("
                INSERT INTO notification_preferences (user_id, email_reminders, sms_reminders, post_visit_summaries)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    email_reminders = VALUES(email_reminders),
                    sms_reminders = VALUES(sms_reminders),
                    post_visit_summaries = VALUES(post_visit_summaries)
            ");
            $stmt->execute([$userId, $emailReminders, $smsReminders, $postVisitSummaries]);

            jsonResponse(true, 'Notification preferences saved.');
            break;

        case 'change_password':
            requireMethod('POST');
            requireCsrf();

            $input = getRequestInput();
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? '';

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

            if ($newPassword !== $confirmPassword) {
                jsonResponse(false, 'New passwords do not match.');
            }

            // Same policy enforced at registration (login.php action=register):
            // 8-16 chars, at least one uppercase, one lowercase, one number,
            // one special character.
            $pwLen = strlen($newPassword);
            if ($pwLen < 8 || $pwLen > 16) {
                jsonResponse(false, 'New password must be 8–16 characters long.');
            }
            if (!preg_match('/[A-Z]/', $newPassword)) {
                jsonResponse(false, 'New password must contain at least one uppercase letter.');
            }
            if (!preg_match('/[a-z]/', $newPassword)) {
                jsonResponse(false, 'New password must contain at least one lowercase letter.');
            }
            if (!preg_match('/[0-9]/', $newPassword)) {
                jsonResponse(false, 'New password must contain at least one number.');
            }
            if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
                jsonResponse(false, 'New password must contain at least one special character.');
            }
            if (password_verify($newPassword, $user['password_hash'])) {
                jsonResponse(false, 'New password must be different from your current password.');
            }

            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$newHash, $userId]);

            jsonResponse(true, 'Password updated successfully.');
            break;

        case 'get_notifications':
            requireMethod('GET');

            $notifications = [
                'upcoming_soon' => ['has_alert' => false, 'booking' => null],
                'pending_unconfirmed' => ['has_alert' => false, 'count' => 0]
            ];

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
            requireMethod('POST');
            requireCsrf();

            $input = getRequestInput();
            $ref = trim($input['reference_code'] ?? '');
            $inputEmail = trim($input['email'] ?? '');

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

            $booking['user_id'] = $userId;

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