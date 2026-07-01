<?php
/**
 * DentalCare Pro - Server-Sent Events (SSE) Endpoint
 *
 * Streams real-time slot availability to the frontend.
 */

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
// REMOVED: header('Access-Control-Allow-Origin: *');

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

$maxRuntimeSeconds = 300; // 5 minutes
$startTime = time();

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
        $stmt = $pdo->prepare("SELECT appointment_time FROM bookings WHERE appointment_date = ? AND status IN ('pending','confirmed')");
        $stmt->execute([$date]);
        
        // Fetch all taken times directly into a 1D array
        $takenSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Compute the difference to find available slots
        $availableSlots = array_diff($allSlots, $takenSlots);

        echo "data: " . json_encode([
            'date'      => $date,
            'taken'     => $takenSlots,
            'available' => array_values($availableSlots)
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