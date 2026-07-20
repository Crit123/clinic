<?php
/**
 * support-backend.php
 * Handles all backend actions for the Support Center page
 * (client/pages/support-center.php) -- currently just ticket submission.
 *
 * Follows the same shared-boilerplate pattern as dashboard-backend.php
 * and search-backend.php via backend-common.php.
 */

require_once __DIR__ . '/backend-common.php';

$userId = requireLogin();
$action = $_GET['action'] ?? '';

/**
 * Categories the frontend <select> actually offers (support-center.php).
 * 'emergency', 'inquiry', 'billing', and 'general' also exist in the DB
 * enum for other callers, but aren't valid choices from this form --
 * emergencies have their own dedicated flow (support/emergency-care.php +
 * emergency_requests table), and billing/payments are out of scope here.
 */
const SUPPORT_TICKET_CATEGORIES = ['appointments', 'records', 'account', 'other'];

/**
 * Generates a short, human-shareable case code in the same family as
 * bookings.reference_code ("#DCP-8X92-ALQ") and
 * emergency_requests.case_code ("EMG-8X92-ALQ"), retrying on the
 * (extremely unlikely) chance of a collision against messages.case_code.
 */
function generateCaseCode(PDO $pdo): string {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I to avoid confusion when read aloud/typed

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $code = 'SUP-';
        for ($i = 0; $i < 4; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        $code .= '-';
        for ($i = 0; $i < 3; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];

        $check = $pdo->prepare("SELECT 1 FROM messages WHERE case_code = ?");
        $check->execute([$code]);
        if (!$check->fetch()) {
            return $code;
        }
    }

    throw new RuntimeException('Could not generate a unique case code.');
}

try {
    switch ($action) {

        case 'submit_ticket':
            requireMethod('POST');
            requireCsrf();

            // Same rate_limits table used by login.php, feedback-create.php,
            // etc. -- keyed per-user here since the person is authenticated.
            if (!checkRateLimit($pdo, (string) $userId, 'support-ticket-create', 5, 300)) {
                jsonResponse(false, 'Too many tickets submitted recently. Please wait a few minutes and try again.');
            }

            $input = getRequestInput();
            $category = trim($input['category'] ?? '');
            $subject  = trim($input['subject'] ?? '');
            $message  = trim($input['message'] ?? '');

            if (!in_array($category, SUPPORT_TICKET_CATEGORIES, true)) {
                jsonResponse(false, 'Please select a valid category.');
            }
            if ($subject === '' || mb_strlen($subject) > 200) {
                jsonResponse(false, 'Subject is required and must be 200 characters or fewer.');
            }
            if ($message === '') {
                jsonResponse(false, 'Message is required.');
            }
            if (mb_strlen($message) > 5000) {
                jsonResponse(false, 'Message must be 5000 characters or fewer.');
            }

            $caseCode = generateCaseCode($pdo);

            $stmt = $pdo->prepare("
                INSERT INTO messages (case_code, category, sender_user_id, subject, body, status)
                VALUES (?, ?, ?, ?, ?, 'new')
            ");
            $stmt->execute([$caseCode, $category, $userId, $subject, $message]);

            jsonResponse(true, 'Your support ticket has been submitted. We will contact you shortly.', [
                'case_code' => $caseCode
            ]);
            break;

        default:
            jsonResponse(false, 'Unknown action.');
            break;
    }
} catch (PDOException $e) {
    error_log("Database Error in support-backend.php: " . $e->getMessage());
    jsonResponse(false, 'An unexpected server error occurred.');
} catch (RuntimeException $e) {
    error_log("Error in support-backend.php: " . $e->getMessage());
    jsonResponse(false, 'An unexpected server error occurred.');
}