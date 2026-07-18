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
 * Hashes a raw PHP session id for storage in user_sessions.session_token.
 * Never store the raw session id -- a leaked row shouldn't be enough to
 * hijack a live session. Shared by login.php and dashboard-backend.php.
 */
function hashSessionId(string $rawSessionId): string {
    return hash('sha256', $rawSessionId);
}

/**
 * Produces a short, human-readable device label from the User-Agent
 * header, e.g. "Mac · Safari" or "iPhone · Chrome". Best-effort only --
 * falls back to "Unknown Device"/"Unknown Browser" when nothing matches.
 */
function parseDeviceLabel(string $userAgent): string {
    $device = 'Unknown Device';
    if (stripos($userAgent, 'iPhone') !== false) $device = 'iPhone';
    elseif (stripos($userAgent, 'iPad') !== false) $device = 'iPad';
    elseif (stripos($userAgent, 'Android') !== false) $device = 'Android Device';
    elseif (stripos($userAgent, 'Macintosh') !== false) $device = 'Mac';
    elseif (stripos($userAgent, 'Windows') !== false) $device = 'Windows PC';
    elseif (stripos($userAgent, 'Linux') !== false) $device = 'Linux PC';

    $browser = 'Unknown Browser';
    if (stripos($userAgent, 'Edg/') !== false) $browser = 'Edge';
    elseif (stripos($userAgent, 'CriOS') !== false) $browser = 'Chrome';
    elseif (stripos($userAgent, 'Chrome') !== false) $browser = 'Chrome';
    elseif (stripos($userAgent, 'Firefox') !== false) $browser = 'Firefox';
    elseif (stripos($userAgent, 'Safari') !== false) $browser = 'Safari';

    return "$device · $browser";
}

/**
 * Creates (or refreshes) the user_sessions row for the session that was
 * just established. Call this right after session_regenerate_id(true) on
 * every path that sets $_SESSION['user_id'] -- login, register, and the
 * remember-me auto-login in login.php -- so the Security tab's Active
 * Sessions list (profile-settings.php) reflects reality.
 *
 * Also opportunistically prunes sessions untouched for 30+ days so the
 * table doesn't grow unbounded with dead entries. Requires the
 * `user_sessions` table (see user-sessions.sql).
 */
function recordSessionLogin(PDO $pdo, int $userId): void {
    $tokenHash = hashSessionId(session_id());
    $deviceLabel = parseDeviceLabel($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO user_sessions (user_id, session_token, device_label, ip_address)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            device_label = VALUES(device_label),
            ip_address = VALUES(ip_address),
            last_active = NOW()
    ");
    $stmt->execute([$userId, $tokenHash, $deviceLabel, $ip]);

    $prune = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND last_active < NOW() - INTERVAL 30 DAY");
    $prune->execute([$userId]);
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