<?php
/**
 * DentalCare Pro - Patient Bookings API Endpoint
 *
 * Fetches historical and upcoming bookings associated with the logged-in user,
 * enriching them with human-readable service labels.
 */

// Start session and set safety headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Require shared API helpers (provides sendSuccess and sendError)
require_once __DIR__ . '/helper/_api-helpers.php';

// Verify authentication
if (!isset($_SESSION['user_id'])) {
    sendError('Unauthorized session access. Please log in first.', 'UNAUTHORIZED', 401);
}

// Require DB connection wrapper
try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    sendError('Database configuration or link failed.', 'DB_INIT_ERROR', 500, $e);
}

// Require single source of truth for service definitions
try {
    require_once __DIR__ . '/data/services-data.php';
} catch (Exception $e) {
    // If services-data.php is not found, we'll continue but degrade gracefully
}

// 1. Accepts GET only
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET.', 'METHOD_NOT_ALLOWED', 405);
}

// Ensure database driver is instantiated
if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

try {
    // 2. Fetch logged-in user's email securely from the database
    $sessionUserId = (int)$_SESSION['user_id'];

    $userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $userStmt->execute([$sessionUserId]);
    $userRow = $userStmt->fetch();

    if (!$userRow) {
        sendError('User record not found.', 'USER_NOT_FOUND', 404);
    }

    $email = $userRow['email'];

    // 3. Query bookings table using the authenticated user's email
    $stmt = $pdo->prepare("
        SELECT id, reference_code, first_name, last_name, email, 
               phone, service_key, dentist_name, appointment_date, 
               appointment_time, status, created_at
        FROM bookings
        WHERE email = :email
        ORDER BY appointment_date DESC
    ");
    
    $stmt->execute(['email' => $email]);
    $rows = $stmt->fetchAll();

    $enrichedBookings = [];

    // Helper definition to resolve service labels dynamically
    foreach ($rows as $row) {
        $serviceKey = $row['service_key'];
        $serviceLabel = $serviceKey; // Fallback to raw key

        // 4. Enrich with service_label using getServiceLabel() if available
        if (function_exists('getServiceLabel')) {
            $serviceLabel = getServiceLabel($serviceKey);
        }

        // Rename/format keys for frontend compatibility
        $enrichedBookings[] = [
            'id'             => (int)$row['id'],
            'reference_code' => str_replace('#', '', $row['reference_code']), // Normalise prefix matching
            'patient_name'   => $row['first_name'] . ' ' . $row['last_name'],
            'email'          => $row['email'],
            'phone'          => $row['phone'],
            'service'        => $serviceLabel,
            'service_key'    => $serviceKey,
            'dentist'        => str_replace('Dr. ', '', $row['dentist_name']), // Align with UI "Dr. {Name}" structure
            'date'           => $row['appointment_date'],
            'time'           => $row['appointment_time'],
            'status'         => $row['status'],
            'created_at'     => $row['created_at']
        ];
    }

    // 5. Return data (Wrapped in 'data' key to match _api-helpers.php payload behavior)
    sendSuccess([
        'data' => [
            'bookings' => $enrichedBookings,
            'total'    => count($enrichedBookings)
        ]
    ]);

} catch (Exception $e) {
    sendError('A database error occurred while retrieving bookings.', 'DB_QUERY_ERROR', 500, $e);
}