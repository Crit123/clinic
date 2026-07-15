<?php
/**
 * DentalCare Pro - Dental Records Backend Guard
 *
 * Not a new auth system. This is a thin checkpoint reused by every
 * records-*.php endpoint: it confirms the session already established by
 * ../../auth/login.php or ../../auth/google-auth.php is present and valid,
 * then exposes $currentUserId. Every query in this folder must filter by
 * $currentUserId -- never by an ID taken from the client.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/conn.php';
require_once __DIR__ . '/../../../api/helper/_api-helpers.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, string $message, array $extra = [], int $httpCode = 200): void {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }
}

// Every records endpoint requires an authenticated session.
if (empty($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized.', [], 401);
}

$currentUserId = (int) $_SESSION['user_id'];

/**
 * Records-specific settings. Deliberately not merged into config/config.php:
 * this feature's storage path and encryption key have nothing to do with
 * the landing page or booking system that also load that root config.
 *
 * RECORDS_ENCRYPTION_KEY must come from an environment variable, not a
 * literal in this file -- if this file were ever served or leaked, a
 * hardcoded key would decrypt every stored record.
 */

define('RECORDS_STORAGE_PATH', realpath(__DIR__ . '/../../../../../storage/records'));
define('RECORDS_ENCRYPTION_KEY', getenv('RECORDS_ENC_KEY') ?: '');

if (RECORDS_STORAGE_PATH === false) {
    error_log('records-guard.php: storage/records directory not found. Expected it outside htdocs, e.g. C:\\xampp\\storage\\records.');
    jsonResponse(false, 'Records storage is not configured.', [], 500);
}

/**
 * Logs a view/download for the audit trail. Insert-only by design --
 * the DB user this connects as should not have UPDATE/DELETE on this table.
 */
function logRecordAccess(PDO $pdo, int $userId, int $recordId, string $action): void {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO record_access_logs (user_id, record_id, action, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $userId,
            $recordId,
            $action,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (PDOException $e) {
        // Never let logging failure block the actual request.
        error_log('records-guard.php: failed to write access log: ' . $e->getMessage());
    }
}