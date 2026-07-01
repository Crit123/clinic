<?php
/**
 * DentalCare Pro - Database Connection Wrapper
 *
 * Instantiates a secure, exception-enabled PDO connection using utf8mb4 encoding
 * and disables emulated prepares.
 */

require_once __DIR__ . '/config.php';

try {
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s",
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // Return structured JSON error if a request expects JSON, or output secure error message
    if (isset($_GET['action']) || (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please try again later.'
        ]);
        exit;
    }
    
    // Fallback safe error message for general requests
    die("Database connection failed. Please check your configuration.");
}