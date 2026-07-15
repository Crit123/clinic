<?php
/**
 * DentalCare Pro - GET /client/backend/records-download.php?id=123
 *
 * Streams a single record's file to the logged-in owner only. This is the
 * highest-risk endpoint in the feature (direct access to X-rays/PDFs), so:
 *   - ownership is re-checked here even though records-list.php already
 *     scoped the list to the current user (never trust the client to only
 *     ask for its own IDs)
 *   - a record that exists but belongs to someone else returns 404, not
 *     403, so this endpoint can't be used to enumerate valid record IDs
 *   - the file is served from outside the webroot and streamed through
 *     PHP, never via a public URL
 *   - every successful and failed attempt is rate-limited and logged
 */

require_once __DIR__ . '/records-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Invalid request method.', [], 405);
}

// Rate limit by user, not just IP -- a compromised session shouldn't be
// able to mass-download every record even from a single browser.
if (!checkRateLimit($pdo, (string) $currentUserId, 'records-download', 30, 300)) {
    jsonResponse(false, 'Too many download requests. Please wait a few minutes.', [], 429);
}

$recordId = (int) ($_GET['id'] ?? 0);
if ($recordId <= 0) {
    jsonResponse(false, 'Invalid record id.', [], 400);
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, stored_filename, original_filename, mime_type
         FROM dental_records
         WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$recordId, $currentUserId]);
    $record = $stmt->fetch();

    // Record belongs to someone else, or doesn't exist -- identical
    // response either way so this can't be used to probe valid IDs.
    if (!$record || empty($record['stored_filename'])) {
        jsonResponse(false, 'Record not found.', [], 404);
    }

    $filePath = RECORDS_STORAGE_PATH . '/' . $currentUserId . '/' . $record['stored_filename'];
    $realPath = realpath($filePath);

    // Path-traversal guard: the resolved path must still live inside this
    // user's own storage subdirectory. stored_filename is a random name we
    // generated ourselves, so this should never trip -- but if it ever
    // does (e.g. future bug in the upload code), refuse rather than serve.
    $userStorageDir = realpath(RECORDS_STORAGE_PATH . '/' . $currentUserId);
    if ($realPath === false || $userStorageDir === false || strpos($realPath, $userStorageDir) !== 0) {
        error_log("records-download.php: path traversal guard tripped for record {$recordId}");
        jsonResponse(false, 'Record not found.', [], 404);
    }

    logRecordAccess($pdo, $currentUserId, $recordId, 'download');

    $mime = $record['mime_type'] ?: 'application/octet-stream';
    $downloadName = basename($record['original_filename'] ?: 'record');

    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
    header('Content-Length: ' . filesize($realPath));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
    header('Content-Security-Policy: default-src \'none\'');

    // Stream rather than load into memory -- keeps this safe for larger
    // scans/PDFs without spiking PHP's memory limit.
    readfile($realPath);
    exit;

} catch (PDOException $e) {
    error_log('records-download.php DB error: ' . $e->getMessage());
    jsonResponse(false, 'Unable to process download right now.', [], 500);
}