<?php
/**
 * DentalCare Pro - Feedback Listing API Endpoint
 *
 * Returns approved patient feedback, with optional sorting and
 * service filtering, plus a calculated average rating across all
 * approved entries. Public — no authentication required, since this
 * is the data backing the public testimonials/reviews experience.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

try {
    require_once __DIR__ . '/../config/conn.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database link failed', 'error_code' => 'DB_INIT_ERROR']);
    exit;
}

require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/data/services-data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET.', 'METHOD_NOT_ALLOWED', 405);
}

$sort = $_GET['sort'] ?? 'recent'; // 'recent' | 'highest' | 'lowest'
$serviceKey = isset($_GET['service_key']) ? trim((string)$_GET['service_key']) : null;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 20;

if ($serviceKey !== null && $serviceKey !== '' && !isValidServiceKey($serviceKey)) {
    sendError('The provided service key is not recognised.', 'INVALID_SERVICE_KEY', 422);
}

$orderClause = match ($sort) {
    'highest' => 'rating DESC, created_at DESC',
    'lowest'  => 'rating ASC, created_at DESC',
    default   => 'created_at DESC',
};

if (!isset($pdo) || !($pdo instanceof PDO)) {
    sendError('System database driver was initialized incorrectly.', 'DRIVER_FAULT', 500);
}

try {
    $params = [];
    $whereClause = "status = 'approved'";
    
    if (!empty($serviceKey)) {
        $whereClause .= " AND service_key = ?";
        $params[] = $serviceKey;
    }

    $stmt = $pdo->prepare("
        SELECT id, display_name, service_key, rating, comment, created_at
        FROM feedback
        WHERE $whereClause
        ORDER BY $orderClause
        LIMIT " . (int)$limit . "
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $enriched = array_map(function ($row) {
        $row['service_label'] = $row['service_key'] ? (getServiceLabel($row['service_key']) ?? $row['service_key']) : null;
        $row['is_new'] = (strtotime($row['created_at']) >= strtotime('-3 days'));
        return $row;
    }, $rows);

    // Calculated average across ALL approved feedback (not just this filtered/limited page)
    $avgStmt = $pdo->query("SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS total FROM feedback WHERE status = 'approved'");
    $avgRow = $avgStmt->fetch(PDO::FETCH_ASSOC);

    sendSuccess([
        'feedback' => $enriched,
        'average_rating' => $avgRow['avg_rating'] !== null ? (float)$avgRow['avg_rating'] : null,
        'total_approved' => (int)$avgRow['total']
    ]);

} catch (Exception $e) {
    sendError('A database error occurred while retrieving feedback.', 'DB_QUERY_ERROR', 500, $e);
}