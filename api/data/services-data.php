<?php
/**
 * DentalCare Pro - Services Data (Single Source of Truth)
 *
 * Canonical registry of all clinical service offerings.
 * Every other file that needs service labels, durations, or key validation
 * should require_once this file instead of maintaining its own copy.
 *
 * Consumers:
 * - booking.php          → getAllServices() to render <select> options
 * - booking-create.php   → isValidServiceKey() to whitelist incoming payloads
 * - confirmed-booking.php → getServiceLabel() to display the human-readable name
 * - booking-lookup.php   → getServiceLabel() for any display/enrichment needs
 * - availability.php      → getClinicSlots() for the full daily schedule
 * - booking-create.php    → getClinicSlots() for conflict + next-available checks
 * - slot-stream.php       → getClinicSlots() for real-time slot polling
 * - index.php             → getAllServices() + getServiceDisplayMeta() for the homepage services grid
 */

/**
 * Internal registry — do not access directly outside this file.
 * Shape: [ service_key => [ 'label' => string, 'duration' => string, 'icon' => string, 'desc' => string, 'featured' => bool, 'image' => string ] ]
 *
 * Keys must exactly match the `service_key` values stored in the `bookings` table.
 */
const DENTAL_SERVICES = [
    'checkup'   => [
        'label' => 'Routine Checkup & Cleaning',
        'duration' => '30–45 Minutes',
        'icon' => 'health_and_safety',
        'desc' => 'Detailed diagnostic evaluations utilizing 3D imaging to monitor your oral health and prevent future complications before they arise.',
        'featured' => true,
        'image' => 'assets/img/services/checkup.png'
    ],
    'whitening' => [
        'label' => 'Teeth Whitening',
        'duration' => '45–60 Minutes',
        'icon' => 'flare',
        'desc' => 'Professional grade laser whitening treatments that safely lift stains for a noticeably brighter smile.',
        'featured' => false,
        'image' => 'assets/img/services/whitening.png'
    ],
    'implants'  => [
        'label' => 'Dental Implants Consultation',
        'duration' => '60 Minutes',
        'icon' => 'dentistry',
        'desc' => 'Advanced 3D CBCT imaging and consultation to determine the best treatment plan for permanent tooth replacement.',
        'featured' => false,
        'image' => 'assets/img/services/implants.png'
    ],
    'emergency' => [
        'label' => 'Emergency Care',
        'duration' => '30 Minutes',
        'icon' => 'medical_services',
        'desc' => 'Immediate priority care for severe pain, trauma, and urgent dental complications when you need it most.',
        'featured' => false,
        'image' => 'assets/img/services/emergency.png'
    ],
];

const CLINIC_SLOTS = [
    '09:00 AM',
    '10:00 AM',
    '11:00 AM',
    '01:00 PM',
    '02:00 PM',
    '03:00 PM',
    '04:00 PM'
];


// ---------------------------------------------------------------------------
// Public API
// ---------------------------------------------------------------------------

/**
 * Returns the full service registry.
 *
 * Suitable for rendering a <select> dropdown server-side or building
 * any other UI that needs all services at once.
 *
 * @return array<string, array{label: string, duration: string, icon: string, desc: string, featured: bool, image: string}>
 * e.g. [ 'checkup' => ['label' => '...', 'duration' => '...', ...], ... ]
 */
function getAllServices(): array
{
    return DENTAL_SERVICES;
}

/**
 * Returns the human-readable label for a given service key.
 *
 * @param  string      $key  e.g. 'checkup'
 * @return string|null       e.g. 'Routine Checkup & Cleaning', or null if unknown
 */
function getServiceLabel(string $key): ?string
{
    return DENTAL_SERVICES[$key]['label'] ?? null;
}

/**
 * Returns the expected appointment duration for a given service key.
 *
 * @param  string      $key  e.g. 'whitening'
 * @return string|null       e.g. '45–60 Minutes', or null if unknown
 */
function getServiceDuration(string $key): ?string
{
    return DENTAL_SERVICES[$key]['duration'] ?? null;
}

/**
 * Returns display metadata (icon, description, featured flag) for a given service key.
 * Used by index.php to render service cards without duplicating this data.
 *
 * @param  string $key  e.g. 'checkup'
 * @return array{icon: string, desc: string, featured: bool, image: string}|null
 */
function getServiceDisplayMeta(string $key): ?array
{
    if (!isset(DENTAL_SERVICES[$key])) {
        return null;
    }
    return [
        'icon'     => DENTAL_SERVICES[$key]['icon'],
        'desc'     => DENTAL_SERVICES[$key]['desc'],
        'featured' => DENTAL_SERVICES[$key]['featured'],
        'image'    => DENTAL_SERVICES[$key]['image'],
    ];
}

/**
 * Validates whether a service key exists in the canonical registry.
 *
 * Use this in booking-create.php before persisting a payload to the database —
 * it acts as the whitelist guard against arbitrary service_key values.
 *
 * @param  string $key  The service_key value from a booking request
 * @return bool         true if the key is recognised, false otherwise
 */
function isValidServiceKey(string $key): bool
{
    return array_key_exists($key, DENTAL_SERVICES);
}

/**
 * Returns the canonical list of bookable clinic timeslots.
 *
 * Single source of truth for standard operating hours' appointment slots.
 * Used by availability.php, booking-create.php, and slot-stream.php to
 * avoid duplicating this list across endpoints.
 *
 * @return string[] e.g. ['09:00 AM', '10:00 AM', ...]
 */
function getClinicSlots(): array
{
    return CLINIC_SLOTS;
}