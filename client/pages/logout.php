<?php
session_start();
require_once __DIR__ . '/../../config/conn.php';

// 1. Clear the remember token from the database if the user is currently logged in
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// 2. Unset all session variables
$_SESSION = [];

// 3. Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destroy the session itself
session_destroy();

// 5. Clear the "Remember Me" cookie by setting its expiration time to the past
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// 6. Redirect the user back to the login page (or homepage)
header("Location: ../../auth/login.php");
exit;