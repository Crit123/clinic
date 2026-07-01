<?php
/**
 * DentalCare Pro - Shared API Helpers
 *
 * Common response formatting, CSRF protection, and rate-limiting
 * utilities used across all /api endpoints. Require this once near
 * the top of each endpoint, after session_start() and the DB connection.
 */

/**
 * Standardized Success Response Utility
 *
 * Note: JSON_PRETTY_PRINT was intentionally dropped from these shared 
 * versions to reduce unnecessary payload overhead in production (e.g. 
 * for the 10-second SSE loop in slot-stream.php).
 */
function sendSuccess(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

/**
 * Standardized Error Response Utility
 *
 * Logs the real error server-side but never leaks internal details
 * (exception messages, stack traces, schema info) to the client.
 * * Note: JSON_PRETTY_PRINT was intentionally dropped from this response as well.
 */
function sendError(string $message, string $errorCode, int $statusCode = 400, ?\Throwable $internal = null): void {
    if ($internal !== null) {
        error_log('[API ERROR][' . $errorCode . '] ' . $internal->getMessage());
    }
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_code' => $errorCode
    ]);
    exit;
}

/**
 * Generates (or reuses) a per-session CSRF token.
 * Call this from any page that renders a form/fetch call needing protection.
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a CSRF token sent by the client (e.g. via X-CSRF-Token header)
 * against the one stored in the session. Call this at the top of every
 * state-changing POST endpoint, after session_start().
 */
function validateCsrfToken(?string $submittedToken): bool {
    if (empty($_SESSION['csrf_token']) || empty($submittedToken)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submittedToken);
}

/**
 * Simple DB-backed rate limiter. Returns true if the request is allowed,
 * false if the identifier has exceeded maxAttempts within windowSeconds.
 * Requires the `rate_limits` table (see clinicdb.sql).
 */
function checkRateLimit(PDO $pdo, string $identifier, string $endpoint, int $maxAttempts = 10, int $windowSeconds = 60): bool {
    $stmt = $pdo->prepare("SELECT attempt_count, window_start FROM rate_limits WHERE identifier = ? AND endpoint = ?");
    $stmt->execute([$identifier, $endpoint]);
    $row = $stmt->fetch();

    $now = time();

    if (!$row) {
        $insert = $pdo->prepare("INSERT INTO rate_limits (identifier, endpoint, attempt_count, window_start) VALUES (?, ?, 1, NOW())");
        $insert->execute([$identifier, $endpoint]);
        return true;
    }

    $windowStart = strtotime($row['window_start']);

    if (($now - $windowStart) > $windowSeconds) {
        // Window expired, reset counter
        $reset = $pdo->prepare("UPDATE rate_limits SET attempt_count = 1, window_start = NOW() WHERE identifier = ? AND endpoint = ?");
        $reset->execute([$identifier, $endpoint]);
        return true;
    }

    if ((int)$row['attempt_count'] >= $maxAttempts) {
        return false;
    }

    $increment = $pdo->prepare("UPDATE rate_limits SET attempt_count = attempt_count + 1 WHERE identifier = ? AND endpoint = ?");
    $increment->execute([$identifier, $endpoint]);
    return true;
}