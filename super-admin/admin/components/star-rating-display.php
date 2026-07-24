<?php
/**
 * client/components/star-rating-display.php
 * Small read-only component that renders a numeric rating (1-5) as
 * filled/outline star icons. Purely visual — no interactivity, no click
 * handlers, no editable state.
 *
 * Used in: reviews.php, index.php's testimonials section, and reused as-is
 * inside admin/pages/feedback.php's moderation cards.
 *
 * Expects the including page to set, before including this file:
 *   - $rating : int, 1-5. Values outside that range are clamped (e.g. a
 *               stray 0 or 6 from bad data still renders a sensible star
 *               row instead of breaking the loop).
 *
 * Usage:
 *   $rating = $feedback['rating'];
 *   include __DIR__ . '/../components/star-rating-display.php';
 */

$rating = (int) ($rating ?? 0);
$rating = max(0, min(5, $rating));
?>
<span class="inline-flex items-center gap-0.5" role="img" aria-label="<?php echo $rating; ?> out of 5 stars">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <?php $filled = $i <= $rating; ?>
        <span class="material-symbols-outlined text-[18px] <?php echo $filled ? 'text-amber-500' : 'text-outline-variant/50'; ?>"
              style="font-variation-settings: 'FILL' <?php echo $filled ? 1 : 0; ?>, 'wght' 500;"
              aria-hidden="true">star</span>
    <?php endfor; ?>
</span>