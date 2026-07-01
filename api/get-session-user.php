<?php
/**
 * DentalCare Pro - Get Session User API Endpoint
 * * Returns the currently authenticated user's details.
 */

session_start();

// Require shared helpers for sendSuccess() and sendError()
require_once __DIR__ . '/helper/_api-helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET.', 'METHOD_NOT_ALLOWED', 405);
}

if (!isset($_SESSION['user_id'])) {
    sendError('Not authenticated.', 'UNAUTHORIZED', 401);
}

try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    sendError('Database link failed', 'DB_LINK_FAILED', 500, $e);
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('Database driver fault', 'DB_DRIVER_FAULT', 500);
}

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone, auth_provider FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        sendSuccess([
            'data' => [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'auth_provider' => $user['auth_provider']
            ]
        ], 200);
    } else {
        sendError('User record not found', 'USER_NOT_FOUND', 404);
    }
} catch (Exception $e) {
    sendError('Database query error', 'DB_QUERY_ERROR', 500, $e);
}