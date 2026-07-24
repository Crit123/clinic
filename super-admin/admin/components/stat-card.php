<?php
/**
 * admin/components/stat-card.php
 * Small reusable card for displaying a single stat (e.g. "Today's Bookings: 12"),
 * used in a grid on dashboard-style pages.
 *
 * Purely presentational — this file does not fetch, compute, or format
 * anything beyond basic display escaping. The including page is responsible
 * for supplying already-fetched values.
 *
 * Expects the including page to set, before including this file:
 *
 *   - $statLabel        : string, e.g. "Today's Bookings" (required)
 *   - $statValue        : string|int, e.g. 12 (required)
 *   - $statIcon         : optional Material Symbols icon name, e.g.
 *                          'event_available'. Omit entirely for no icon.
 *   - $statIconColorClass : optional Tailwind classes for the icon circle's
 *                          background + text color, e.g.
 *                          'bg-rose-100 text-rose-700'. Defaults to a
 *                          primary-tinted circle if an icon is given.
 *   - $statTrend        : optional string, e.g. "+3 from yesterday" or
 *                          "-2 from last week". Omit entirely (leave unset
 *                          or null) to render no trend line at all.
 *   - $statTrendType    : optional 'positive' | 'negative' | 'neutral'.
 *                          If omitted, this is inferred from $statTrend's
 *                          leading character ('+' -> positive, '-' -> negative,
 *                          anything else -> neutral/gray) so callers aren't
 *                          required to pass it explicitly.
 *   - $statHref         : optional URL. If set, the whole card becomes a
 *                          clickable link (e.g. to a filtered list view).
 *                          If omitted, renders as a plain non-interactive div.
 *
 * Usage:
 *   $statLabel = "Today's Bookings";
 *   $statValue = 12;
 *   $statIcon = 'event_available';
 *   $statTrend = '+3 from yesterday';
 *   $statHref = 'appointments.php?filter=today';
 *   include __DIR__ . '/../components/stat-card.php';
 */

$statLabel          = $statLabel ?? '';
$statValue          = $statValue ?? '0';
$statIcon           = $statIcon ?? null;
$statIconColorClass = $statIconColorClass ?? 'bg-primary-fixed text-primary';
$statTrend          = $statTrend ?? null;
$statTrendType      = $statTrendType ?? null;
$statHref           = $statHref ?? null;

// Infer trend color/type from the leading character if not explicitly given.
if ($statTrend !== null && $statTrendType === null) {
    $firstChar = substr(trim((string) $statTrend), 0, 1);
    if ($firstChar === '+') {
        $statTrendType = 'positive';
    } elseif ($firstChar === '-') {
        $statTrendType = 'negative';
    } else {
        $statTrendType = 'neutral';
    }
}

$trendColorClasses = [
    'positive' => 'text-emerald-700',
    'negative' => 'text-red-600',
    'neutral'  => 'text-on-surface-variant',
];
$trendIcon = [
    'positive' => 'trending_up',
    'negative' => 'trending_down',
    'neutral'  => 'trending_flat',
];

$tag = $statHref ? 'a' : 'div';
$hrefAttr = $statHref ? 'href="' . htmlspecialchars($statHref) . '"' : '';
?>
<<?php echo $tag; ?> <?php echo $hrefAttr; ?>
    class="block bg-surface-container-lowest p-5 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] border border-surface-container <?php echo $statHref ? 'hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300' : ''; ?>">

    <?php if ($statIcon): ?>
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-full <?php echo htmlspecialchars($statIconColorClass); ?> flex items-center justify-center" aria-hidden="true">
                <span class="material-symbols-outlined text-xl"><?php echo htmlspecialchars($statIcon); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <p class="text-[11px] sm:text-xs text-on-surface-variant uppercase tracking-wider font-bold mb-1">
        <?php echo htmlspecialchars($statLabel); ?>
    </p>
    <p class="text-2xl sm:text-3xl font-bold text-primary">
        <?php echo htmlspecialchars((string) $statValue); ?>
    </p>

    <?php if ($statTrend !== null && $statTrend !== ''): ?>
        <p class="flex items-center gap-1 text-xs font-semibold mt-2 <?php echo $trendColorClasses[$statTrendType] ?? $trendColorClasses['neutral']; ?>">
            <span class="material-symbols-outlined text-[15px]"><?php echo $trendIcon[$statTrendType] ?? $trendIcon['neutral']; ?></span>
            <?php echo htmlspecialchars($statTrend); ?>
        </p>
    <?php endif; ?>

</<?php echo $tag; ?>>