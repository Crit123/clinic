<?php
/**
 * DentalCare Pro - Google Sign-In Verification Handler
 * Validates the GIS ID Token securely and manages the user session.
 */
session_start();
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../config/google-config.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, string $message, array $extra = []): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$credential = $_POST['credential'] ?? '';
if (empty($credential)) {
    jsonResponse(false, 'Missing Google credential.');
}

// 1. Verify the token using Google's public tokeninfo endpoint
$verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($credential);

// Use stream context to suppress PHP warnings if Google returns a 400 Bad Request (invalid token)
$context = stream_context_create(['http' => ['ignore_errors' => true]]);
$response = file_get_contents($verify_url, false, $context);
$payload = json_decode($response, true);

if (!$payload || isset($payload['error'])) {
    jsonResponse(false, 'Invalid or expired Google authentication token.');
}

// 2. Validate Audience (Security Check)
if (!isset($payload['aud']) || $payload['aud'] !== GOOGLE_CLIENT_ID) {
    jsonResponse(false, 'Security verification failed: Audience mismatch.');
}

// 3. Validate Email Verification Status
$is_verified = $payload['email_verified'] ?? false;
if ($is_verified !== 'true' && $is_verified !== true) {
    jsonResponse(false, 'Your Google email address must be verified to sign in.');
}

// 4. Extract required data
$google_id  = $payload['sub'] ?? '';
$email      = $payload['email'] ?? '';
$first_name = $payload['given_name'] ?? 'Google';
$last_name  = $payload['family_name'] ?? 'User';

if (empty($google_id) || empty($email)) {
    jsonResponse(false, 'Incomplete data received from Google.');
}

try {
    // 5. Account Management Logic
    $stmt = $pdo->prepare("SELECT id, first_name, google_id, auth_provider FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // --- EXISTING USER ---
        $updateFields = [];
        $updateParams = [];

        // Link Google ID if empty
        if (empty($user['google_id'])) {
            $updateFields[] = "google_id = ?";
            $updateParams[] = $google_id;
        }
        
        // Update provider identity
        if ($user['auth_provider'] === 'local') {
            $updateFields[] = "auth_provider = 'google'";
        }

        if (!empty($updateFields)) {
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $updateParams[] = $user['id'];
            $updateStmt = $pdo->prepare($sql);
            $updateStmt->execute($updateParams);
        }

        // Establish session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['first_name'];
        jsonResponse(true, 'Google Sign-In successful. Redirecting...', ['redirect' => '../client/pages/dashboard.php']);

    } else {
        // --- NEW USER ---
        // Generate a random, unguessable hash since password_hash is NOT NULL in the database
        $dummy_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password_hash, google_id, auth_provider) 
            VALUES (?, ?, ?, ?, ?, 'google')
        ");
        
        if ($stmt->execute([$first_name, $last_name, $email, $dummy_password, $google_id])) {
            // Establish session
            $_SESSION['user_id']   = $pdo->lastInsertId();
            $_SESSION['user_name'] = $first_name;
            jsonResponse(true, 'Account created successfully via Google. Welcome!', ['redirect' => '../client/pages/dashboard.php']);
        } else {
            jsonResponse(false, 'Failed to create user account. Please try again.');
        }
    }
} catch (PDOException $e) {
    // Secure error logging (does not expose DB details to the frontend)
    error_log("Google Auth DB Error: " . $e->getMessage());
    jsonResponse(false, 'A database error occurred during authentication.');
}