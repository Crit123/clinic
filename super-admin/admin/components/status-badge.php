<?php
/**
 * admin/components/status-badge.php
 * Small reusable colored pill/badge for status values. Used across
 * bookings.php, emergency-requests.php, and feedback.php wherever a status
 * needs to be rendered server-side (e.g. inside a PHP loop over rows before
 * any JS hydration, or in server-rendered emails/exports).
 *
 * Usage:
 *   $status = $row['status'];
 *   include __DIR__ . '/../components/status-badge.php';
 *
 * Note: this file expects $status to already be set by the including page
 * and echoes markup directly — it does not return a value. If you need this
 * as a callable instead of an include, wrap the body below in a function.
 *
 * Color map:
 *   - pending, submitted, under_review   -> yellow
 *   - confirmed, contacted, approved, resolved -> green
 *   - cancelled, rejected                -> red
 *   - anything else                      -> gray (raw value still shown, no throw)
 */

$status = $status ?? '';

$statusColorMap = [
    'pending'      => 'yellow',
    'submitted'    => 'yellow',
    'under_review' => 'yellow',
    'confirmed'    => 'green',
    'contacted'    => 'green',
    'approved'     => 'green',
    'resolved'     => 'green',
    'cancelled'    => 'red',
    'rejected'     => 'red',
];

$colorClasses = [
    'yellow' => 'bg-amber-100 text-amber-800',
    'green'  => 'bg-emerald-100 text-emerald-800',
    'red'    => 'bg-red-100 text-red-800',
    'gray'   => 'bg-gray-100 text-gray-700',
];

// Fail gracefully: unrecognized statuses fall back to gray, raw value kept.
$color = $statusColorMap[$status] ?? 'gray';
$classes = $colorClasses[$color] ?? $colorClasses['gray'];

// "under_review" -> "Under Review"; falls back to the raw value if $status is empty.
$label = $status !== '' ? ucwords(str_replace('_', ' ', $status)) : 'Unknown';
?>
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold <?php echo $classes; ?>">
    <?php echo htmlspecialchars($label); ?>
</span>