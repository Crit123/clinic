<?php
/**
 * services-data.php
 * Service categories and lookup utilities for DentalCare Pro.
 */

require_once __DIR__ . '/design-config.php';

// TODO: Replace with backend fetch — e.g. $serviceCategories = fetchServiceCategories();
$serviceCategories = [];

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