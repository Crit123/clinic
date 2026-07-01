<?php
/**
 * DentalCare Pro - Server-Sent Events (SSE) Endpoint
 *
 * Streams real-time slot availability to the frontend.
 *
 * Protection: per-IP concurrent connection limit enforced via PID lock files.
 * A lock file is created on connection open and deleted via register_shutdown_function,
 * so it is always cleaned up regardless of how the process exits.
 * Stale files (older than MAX_RUNTIME + 60s buffer) are pruned on each check.
 */

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
// REMOVED: header('Access-Control-Allow-Origin: *');

// ─── Per-IP concurrent connection limit ───────────────────────────────────────

define('SSE_MAX_CONNECTIONS_PER_IP', 3);
define('SSE_MAX_RUNTIME_SECONDS',    300); // must match the loop below
define('SSE_LOCK_DIR', sys_get_temp_dir() . '/dentalcare_sse');
define('SSE_STALE_AFTER_SECONDS',    SSE_MAX_RUNTIME_SECONDS + 60); // 6-minute stale cutoff

/**
 * Returns the temp directory used for SSE lock files, creating it if needed.
 */
function sseLockDir(): string
{
    $dir = SSE_LOCK_DIR;
    if (!is_dir($dir)) {
        // 0700: only the web-server user can read/write — no directory listing exposure
        mkdir($dir, 0700, true);
    }
    return $dir;
}

/**
 * Strips characters that are unsafe in filenames, so an IPv6 address
 * like "::1" doesn't produce a path traversal or a hidden file.
 */
function sseIpToFilesafe(string $ip): string
{
    return preg_replace('/[^a-zA-Z0-9._\-]/', '_', $ip);
}

/**
 * Counts active lock files for $safeIp, pruning stale ones along the way.
 * "Stale" means the file's mtime is older than SSE_STALE_AFTER_SECONDS —
 * which can only happen if a PHP process was killed hard without running
 * its shutdown function (e.g. SIGKILL, server restart).
 */
function sseCountActiveConnections(string $lockDir, string $safeIp): int
{
    $staleCutoff = time() - SSE_STALE_AFTER_SECONDS;
    $files       = glob($lockDir . '/' . $safeIp . '_*.lock') ?: [];
    $active      = 0;

    foreach ($files as $file) {
        if (@filemtime($file) < $staleCutoff) {
            @unlink($file); // stale — remove silently
        } else {
            $active++;
        }
    }

    return $active;
}

// ── Resolve real client IP (handle common reverse-proxy headers) ──────────────
$rawIp    = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$clientIp = trim(explode(',', $rawIp)[0]); // first entry in a proxy chain is the original client
$safeIp   = sseIpToFilesafe($clientIp);

// ── Enforce the limit ─────────────────────────────────────────────────────────
// Note: there is a narrow TOCTOU race window between counting and creating the
// lock file. For a low-traffic clinic site this is acceptable; a hard guarantee
// would require flock() on a per-IP mutex file, which adds complexity for no
// real benefit here.
$lockDir = sseLockDir();

if (sseCountActiveConnections($lockDir, $safeIp) >= SSE_MAX_CONNECTIONS_PER_IP) {
    echo "data: " . json_encode(['error' => 'TOO_MANY_CONNECTIONS']) . "\n\n";
    flush();
    exit;
}

// ── Register this connection ──────────────────────────────────────────────────
$lockFile = $lockDir . '/' . $safeIp . '_' . getmypid() . '.lock';
file_put_contents($lockFile, time()); // mtime is what we age-check; content is informational

// Always remove the lock file, even on fatal errors or connection aborts.
// register_shutdown_function fires after the script ends for *any* reason.
register_shutdown_function(function () use ($lockFile): void {
    if (file_exists($lockFile)) {
        @unlink($lockFile);
    }
});

// ─── Existing streaming logic (unchanged) ─────────────────────────────────────

$date = isset($_GET['date']) ? trim($_GET['date']) : '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo "data: {\"error\":\"INVALID_DATE\"}\n\n";
    exit;
}

set_time_limit(0);

// FIXED: Path to conn.php updated to match /config/ directory structure
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/data/services-data.php';

$allSlots = getClinicSlots();

$maxRuntimeSeconds = SSE_MAX_RUNTIME_SECONDS; // 5 minutes
$startTime         = time();

while (true) {
    if (connection_aborted()) {
        exit;
    }

    if ((time() - $startTime) >= $maxRuntimeSeconds) {
        echo "data: " . json_encode(['reconnect' => true]) . "\n\n";
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            "SELECT appointment_time FROM bookings WHERE appointment_date = ? AND status IN ('pending','confirmed')"
        );
        $stmt->execute([$date]);

        // Fetch all taken times directly into a 1D array
        $takenSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Compute the difference to find available slots
        $availableSlots = array_diff($allSlots, $takenSlots);

        echo "data: " . json_encode([
            'date'      => $date,
            'taken'     => $takenSlots,
            'available' => array_values($availableSlots),
        ]) . "\n\n";

        if (ob_get_level()) {
            ob_flush();
        }
        flush();

    } catch (PDOException $e) {
        echo "data: {\"error\":\"DB_ERROR\"}\n\n";
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
        break;
    }

    sleep(10);
}