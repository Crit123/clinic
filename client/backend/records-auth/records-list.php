<?php
/**
 * DentalCare Pro - GET /client/backend/records-list.php
 *
 * Returns the logged-in patient's own dental records only. Replaces the
 * hardcoded $records array in client/pages/dental-records.php.
 *
 * Data minimization: stored_filename is intentionally never selected here.
 * The frontend only needs enough to render the card + open the preview
 * modal; the actual on-disk name is an implementation detail the client
 * has no reason to see, and leaking it would make records-download.php's
 * random naming pointless.
 */

require_once __DIR__ . '/records-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Invalid request method.', [], 405);
}

$allowedFilters = ['all', 'xray', 'note', 'prescription'];
$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

try {
    $sql = "SELECT
                id, type, title, details, doctor_name, clinical_notes AS notes,
                next_action, rx_number, service_key, original_filename,
                record_date
            FROM dental_records
            WHERE user_id = ?";
    $params = [$currentUserId];

    if ($filter !== 'all') {
        $sql .= " AND type = ?";
        $params[] = $filter;
    }

    $sql .= " ORDER BY record_date DESC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    jsonResponse(true, 'Records retrieved.', ['records' => $records]);

} catch (PDOException $e) {
    error_log('records-list.php DB error: ' . $e->getMessage());
    jsonResponse(false, 'Unable to load records right now.', [], 500);
}