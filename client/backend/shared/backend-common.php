<?php
/**
 * backend-common.php
 *
 * Shared boilerplate reused by every "*-backend.php" endpoint file
 * (dashboard-backend.php, appointments-backend.php, and any future
 * page backend). Keeping this in one place means the session check,
 * JSON response shape, CSRF validation, and DB/helper includes stay
 * consistent across the whole app instead of being copy-pasted per file.
 *
 * USAGE — put this at the top of every *-backend.php file:
 *
 *   require_once __DIR__ . '/shared/backend-common.php';
 *   $userId = requireLogin();
 *   $action = $_GET['action'] ?? '';
 *
 *   try {
 *       switch ($action) {
 *           case 'some_get_action':
 *               requireMethod('GET');
 *               ...
 *               jsonResponse(true, 'OK', ['data' => $data]);
 *               break;
 *
 *           case 'some_post_action':
 *               requireMethod('POST');
 *               requireCsrf();
 *               $input = getRequestInput();
 *               ...
 *               jsonResponse(true, 'Saved.');
 *               break;
 *
 *           default:
 *               jsonResponse(false, 'Unknown action.');
 *       }
 *   } catch (PDOException $e) {
 *       error_log("Database Error in " . basename(__FILE__) . ": " . $e->getMessage());
 *       jsonResponse(false, 'An unexpected server error occurred.');
 *   }
 */

session_start();

// Shared DB connection + CSRF helpers (getCsrfToken / validateCsrfToken).
// Paths are relative to /client/backend/*.php, matching the existing
// dashboard-backend.php convention.
require_once __DIR__ . '/../../../config/conn.php';
require_once __DIR__ . '/../../../api/helper/_api-helpers.php';

/**
 * Standard JSON response envelope used by every endpoint.
 * Always exits, so nothing runs after it by mistake.
 */
if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, string $message, array $extra = []): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }
}

/**
 * Confirms the request is coming from a logged-in session and returns
 * the user's id. Call this first, before touching the database.
 */
if (!function_exists('requireLogin')) {
    function requireLogin(): int {
        if (!isset($_SESSION['user_id'])) {
            jsonResponse(false, 'Unauthorized.');
        }
        return (int) $_SESSION['user_id'];
    }
}

/**
 * Guards an action against being called with the wrong HTTP verb.
 */
if (!function_exists('requireMethod')) {
    function requireMethod(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            jsonResponse(false, 'Invalid request method.');
        }
    }
}

/**
 * Validates the CSRF token for state-changing (POST) actions.
 * Expects the client to send it as the 'X-CSRF-Token' header
 * (see CSRF_TOKEN bootstrap pattern used in login.php).
 */
if (!function_exists('requireCsrf')) {
    function requireCsrf(): void {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!validateCsrfToken($csrfToken)) {
            jsonResponse(false, 'Invalid or missing CSRF token.');
        }
    }
}

/**
 * Reads the request body supporting both multipart/form-data ($_POST)
 * and a raw application/json body, merged with $_POST taking priority
 * if a key exists in both. Mirrors the pattern already used in
 * dashboard-backend.php (cancel_booking, update_profile, etc).
 */
if (!function_exists('getRequestInput')) {
    function getRequestInput(): array {
        $json = json_decode(file_get_contents('php://input'), true);
        if (!is_array($json)) {
            $json = [];
        }
        return array_merge($json, $_POST);
    }
}