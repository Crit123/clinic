<?php
/**
 * services-data.php
 * Service categories and lookup utilities for the patient portal
 * (book-appointment.php's category/service picker).
 *
 * $serviceCategories is now DERIVED from the canonical registry in
 * api/data/services-data.php (getAllServices()) instead of being a
 * hardcoded/empty stub. Each canonical service becomes its own
 * single-service "category" here, since the canonical registry is
 * flat (no category grouping of its own). Add/edit services in
 * api/data/services-data.php only — this file will pick up the
 * change automatically.
 */

require_once __DIR__ . '/design-config.php';
require_once __DIR__ . '/../../api/data/services-data.php';

/**
 * Per-service accent color, keyed by service_key. Purely a portal UI
 * concern (icon background color for the category card) — not part
 * of the canonical registry since the marketing site has no use for it.
 * Falls back to a neutral color for any service key not listed here.
 */
const SERVICE_CATEGORY_COLORS = [
    'checkup'   => 'bg-blue-50 text-blue-600',
    'whitening' => 'bg-amber-50 text-amber-600',
    'implants'  => 'bg-emerald-50 text-emerald-600',
    'emergency' => 'bg-rose-50 text-rose-600',
];

/**
 * Builds $serviceCategories in the shape book-appointment.php expects:
 * one category per canonical service, each with a single nested service
 * entry (itself), so existing category-card + service-pill rendering
 * logic in book-appointment.php works unchanged.
 */
function buildServiceCategoriesFromCanonical(): array
{
    $categories = [];

    foreach (getAllServices() as $key => $service) {
        $categories[] = [
            'id'        => $key,
            'name'      => $service['label'],
            'desc'      => $service['desc'],
            'icon'      => $service['icon'],
            'color'     => SERVICE_CATEGORY_COLORS[$key] ?? 'bg-slate-50 text-slate-600',
            'pre_visit' => $service['pre_visit'] ?? '',
            'estimate'  => $service['estimate'] ?? $service['duration'],
            'services'  => [
                [
                    'id'   => $key,
                    'name' => $service['label'],
                ],
            ],
        ];
    }

    return $categories;
}

$serviceCategories = buildServiceCategoriesFromCanonical();

function flattenServiceLookup(array $serviceCategories): array {
    $flattened = [];
    foreach ($serviceCategories as $category) {
        if (isset($category['services']) && is_array($category['services'])) {
            foreach ($category['services'] as $service) {
                $flattened[$service['id']] = $service;
            }
        }
    }
    return $flattened;
}