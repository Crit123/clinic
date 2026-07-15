<?php
/**
 * search-backend.php
 * Global portal search — powers header.php's and sidebar.php's search
 * boxes identically via the shared window.runPortalSearch() JS function.
 *
 * Deliberately its own file, separate from dashboard-backend.php and
 * appointments-backend.php: search isn't tied to any single page, it's
 * used on every page via the header/sidebar. Folding it into
 * dashboard-backend.php would make that file's scope (its own docblock
 * says "Handles all backend actions for the Patient Dashboard") misleading.
 *
 * TO ADD A NEW SEARCHABLE SOURCE (e.g. once dental-records.php has real
 * data): write one search*() function below returning the same
 * [{label, sublabel, url, icon}, ...] shape, then register it as one
 * more $groups[] entry in the search_global case. Nothing else changes.
 */

require_once __DIR__ . '/backend-common.php';
require_once __DIR__ . '/../../../api/data/services-data.php'; // getAllServices()

$userId = requireLogin();
$action = $_GET['action'] ?? '';

/**
 * Static registry of portal destinations that aren't backed by a
 * database row — settings, support, booking entry points, etc.
 * Matched by simple case-insensitive substring against label + keywords.
 */
const SEARCHABLE_PAGES = [
    ['label' => 'Book New Appointment',  'url' => 'book-appointment.php',              'icon' => 'add_circle',           'keywords' => ['book', 'new', 'schedule', 'appointment']],
    ['label' => 'My Appointments',       'url' => 'appointments.php',                  'icon' => 'calendar_month',       'keywords' => ['appointments', 'upcoming', 'cancel', 'reschedule']],
    ['label' => 'Dental Records',        'url' => 'dental-records.php',                'icon' => 'medical_information',  'keywords' => ['records', 'history', 'xray', 'x-ray']],
    ['label' => 'Profile Settings',      'url' => 'profile-settings.php',              'icon' => 'account_circle',       'keywords' => ['profile', 'account', 'name', 'email']],
    ['label' => 'Account Security',      'url' => 'profile-settings.php?tab=security', 'icon' => 'lock',                 'keywords' => ['password', 'security', 'change password']],
    ['label' => 'Support Center',        'url' => 'support-center.php',                'icon' => 'help',                 'keywords' => ['help', 'support', 'contact', 'faq']],
    ['label' => 'Emergency Care',        'url' => 'support/emergency-care.php',        'icon' => 'emergency',            'keywords' => ['emergency', 'urgent', 'pain']],
    ['label' => 'Notifications',        'url' => 'notifications.php',                 'icon' => 'notifications',        'keywords' => ['notifications', 'alerts']],
];

/**
 * Matches against the user's own bookings (service, dentist, reference code).
 */
function searchBookings(PDO $pdo, int $userId, string $q): array
{
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT reference_code, service_key, dentist_name, appointment_date, appointment_time, status
        FROM bookings
        WHERE user_id = ?
          AND (service_key LIKE ? OR dentist_name LIKE ? OR reference_code LIKE ?)
        ORDER BY appointment_date DESC
        LIMIT 6
    ");
    $stmt->execute([$userId, $like, $like, $like]);

    return array_map(function ($b) {
        return [
            'label'    => ($b['service_key'] ?? 'Appointment') . ' — Dr. ' . $b['dentist_name'],
            'sublabel' => '#' . $b['reference_code'] . ' · ' . $b['appointment_date'],
            'url'      => 'appointments.php',
            'icon'     => 'calendar_month',
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/**
 * Matches against the canonical service registry (api/data/services-data.php),
 * surfacing "Book: <Service>" so searching e.g. "whitening" suggests
 * actually booking it, not just a bookings-table hit.
 */
function searchServices(string $q): array
{
    $needle = strtolower($q);
    $matches = [];

    foreach (getAllServices() as $key => $service) {
        $haystack = strtolower($service['label'] . ' ' . $service['desc']);
        if (str_contains($haystack, $needle)) {
            $matches[] = [
                'label'    => 'Book: ' . $service['label'],
                'sublabel' => $service['duration'],
                'url'      => 'book-appointment.php?service=' . urlencode($key),
                'icon'     => $service['icon'],
            ];
        }
    }

    return array_slice($matches, 0, 4);
}

/**
 * Matches against the static SEARCHABLE_PAGES registry above.
 */
function searchPages(string $q): array
{
    $needle = strtolower($q);
    $matches = [];

    foreach (SEARCHABLE_PAGES as $page) {
        $haystack = strtolower($page['label'] . ' ' . implode(' ', $page['keywords']));
        if (str_contains($haystack, $needle)) {
            $matches[] = [
                'label'    => $page['label'],
                'sublabel' => '',
                'url'      => $page['url'],
                'icon'     => $page['icon'],
            ];
        }
    }

    return array_slice($matches, 0, 4);
}

try {
    switch ($action) {

        case 'search_global':
            requireMethod('GET');

            $q = trim($_GET['q'] ?? '');
            if (strlen($q) < 2) {
                jsonResponse(true, 'Query too short.', ['groups' => []]);
            }

            $groups = [];

            if ($bookingResults = searchBookings($pdo, $userId, $q)) {
                $groups[] = ['label' => 'Appointments', 'items' => $bookingResults];
            }

            if ($serviceResults = searchServices($q)) {
                $groups[] = ['label' => 'Services', 'items' => $serviceResults];
            }

            if ($pageResults = searchPages($q)) {
                $groups[] = ['label' => 'Pages', 'items' => $pageResults];
            }

            jsonResponse(true, 'Search complete.', ['groups' => $groups]);
            break;

        default:
            jsonResponse(false, 'Unknown action.');
            break;
    }
} catch (PDOException $e) {
    error_log("Database Error in search-backend.php: " . $e->getMessage());
    jsonResponse(false, 'An unexpected server error occurred.');
}