<?php
/**
 * auth-guard.php
 * Session authentication guard + logged-in user fetch.
 *
 * MUST be required at the very top of main-layout.php, before any
 * HTML/DOCTYPE output — this is the only safe place for header()
 * redirects to fire. Populates $currentUser, $patientId, and
 * $recentSearches for use by header.php further down the layout.
 */

// Start the session if one isn't already active. Without this,
// $_SESSION is always empty on this request regardless of what
// login.php previously set — which silently sends every visitor
// back to the login page, even right after a successful login,
// causing an infinite redirect loop between login.php and this guard.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Session Guard ──────────────────────────────────────────────
// The portal layout is always authenticated. If session is missing,
// redirect to login as a safety fallback.
require_once __DIR__ . '/../design-config.php'; // for BASE_PATH

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/auth/login.php');
    exit;
}

// ── Fetch logged-in user from DB ───────────────────────────────
require_once __DIR__ . '/../../../config/conn.php';

$headerUserId = (int) $_SESSION['user_id'];
$currentUser  = [];

try {
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, auth_provider
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$headerUserId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $fullName = trim($row['first_name'] . ' ' . $row['last_name']);
        $currentUser = [
            'name'         => $fullName,
            'role'         => 'Patient',
            'avatar_url'   => 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=003164&color=fff&size=64',
            'unread_count' => 0, // TODO: replace with real notifications count when notifications table exists
        ];
        $patientId = str_pad($row['id'], 5, '0', STR_PAD_LEFT); // e.g. 00042
    } else {
        // Fallback: session exists but user row not found — force logout
        session_destroy();
        header('Location: ' . BASE_PATH . '/auth/login.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Header DB Error: " . $e->getMessage());
    // Graceful degradation — show empty state rather than crashing the layout
    $currentUser = [
        'name'         => $_SESSION['user_name'] ?? 'Patient',
        'role'         => 'Patient',
        'avatar_url'   => '',
        'unread_count' => 0,
    ];
    $patientId = $headerUserId;
}

// ── Recent Searches ────────────────────────────────────────────
// Static for now — replace with DB-backed search history when available
$recentSearches = [];