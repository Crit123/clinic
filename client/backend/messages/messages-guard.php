<?php
/**
 * DentalCare Pro - Messaging System Backend Guard
 *
 * Unlike records-guard.php, login is NOT required here -- guests can submit
 * messages (emergency, inquiry, billing, general) without an account, same
 * as guest bookings already work in this app. Endpoints that need to
 * restrict to staff/admin call requireStaff() explicitly.
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

// Present but optional. Logged-in users get their message tied to their
// account automatically; guests fall back to case-code lookup instead.
$currentUserId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$currentUserRole = null;

if ($currentUserId !== null) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$currentUserId]);
        $currentUserRole = $stmt->fetchColumn() ?: 'client';
    } catch (PDOException $e) {
        error_log('messages-guard.php: failed to load role: ' . $e->getMessage());
        $currentUserRole = 'client';
    }
}

/**
 * Call this at the top of any endpoint restricted to staff/admin.
 * Never trust a role claimed by the client -- always re-derive it from
 * the DB via the session, as above.
 */
function requireStaff(): void {
    global $currentUserRole;
    if (!in_array($currentUserRole, ['staff', 'admin'], true)) {
        jsonResponse(false, 'Unauthorized.', [], 401);
    }
}

function requireAdmin(): void {
    global $currentUserRole;
    if ($currentUserRole !== 'admin') {
        jsonResponse(false, 'Unauthorized.', [], 401);
    }
}

/**
 * Generates a case code in the same style as bookings.reference_code
 * (e.g. MSG-8X92-ALQ), so guests have something short and rememberable
 * to check status with, while still being unguessable enough to serve
 * as a lookup credential.
 */
function generateCaseCode(PDO $pdo): string {
    do {
        $code = 'MSG-' . strtoupper(bin2hex(random_bytes(2))) . '-' . strtoupper(bin2hex(random_bytes(1)));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE case_code = ?");
        $stmt->execute([$code]);
    } while ((int) $stmt->fetchColumn() > 0);

    return $code;
}