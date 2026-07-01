<?php
/**
 * design-config.php
 * Centralized Design Settings & Brand Configuration for DentalCare Pro.
 */

define('SITE_NAME', 'DentalCare Pro');
define('BRAND_TAGLINE', 'Premium Oral Care');

// Base path of the app relative to the web root.
// Local (XAMPP, subfolder): '/booking-system'
// Production (project IS the web root): ''
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/booking-system');
}

// Centralized Environment Flag: 'development' or 'production'
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development'); // Change to 'production' in live environment
}

// Clinic Contact Details (Client Portal Scope)
define('CLINIC_ADDRESS', ''); // TODO: load from backend
define('CLINIC_PHONE', ''); // TODO: load from backend
define('CLINIC_EMAIL', ''); // TODO: load from backend

// Semantic Color Constants for PHP reference (mirrored in Tailwind Config)
define('COLOR_SECONDARY_TEXT', '#475569'); // slate-600 (WCAG AA compliant for small text)
define('COLOR_TERTIARY_TEXT', '#64748b');  // slate-500 (WCAG AA compliant for medium text)

$icons = [
    'dashboard'      => 'dashboard',
    'appointments'   => 'calendar_month',
    'book'           => 'add_circle',
    'records'        => 'medical_information',
    'notifications'  => 'notifications',
    'settings'       => 'settings',
    'support'        => 'help',
    'logout'         => 'logout',
    'search'         => 'search',
    'clear'          => 'close',
    'upcoming'       => 'event_upcoming',
    'completed'      => 'check_circle',
    'history'        => 'history',
    'records_folder' => 'folder_shared',
    'menu'           => 'menu',
    'menu_open'      => 'menu_open',
    'arrow_forward'  => 'arrow_forward',
    'ai_assistant'   => 'smart_toy',
    'warning'        => 'warning',
    'info'           => 'info',
    'error'          => 'error',
];