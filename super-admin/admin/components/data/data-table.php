<?php
/**
 * admin/components/data-table.php
 * Reusable table shell with built-in pagination controls, used by any admin
 * page that lists paginated records (bookings today; staff directory later).
 *
 * This component only renders markup — it does not fetch data or handle
 * clicks itself. Prev/Next and page-number buttons call a JS function (by
 * name, configurable) that the including page must define; this keeps the
 * component agnostic to whether that page fetches new data via AJAX or does
 * a full page reload.
 *
 * Expects the including page to set, before including this file:
 *
 *   - $columns          : array of ['label' => string, 'key' => string, 'raw' => bool (optional)]
 *                          'key' looks up the value in each row array.
 *                          'raw' => true means the value is already-safe HTML
 *                          and should NOT be escaped (e.g. a pre-rendered
 *                          status-badge include). Defaults to false (escaped).
 *   - $rows             : array of associative arrays, one per row. Keys
 *                          should match $columns' 'key' values. May also
 *                          include '_actions' => 'raw html string' per row —
 *                          see $showActionsColumn below.
 *   - $currentPage      : int, 1-based current page number
 *   - $totalPages       : int, total number of pages
 *   - $showActionsColumn : bool, whether to render a trailing "Actions"
 *                          column (default true). When true, each row's
 *                          '_actions' key is echoed AS-IS (raw HTML) — the
 *                          including page is fully responsible for building
 *                          those buttons (Confirm/Cancel for bookings,
 *                          Edit/Deactivate for staff directory, etc.). This
 *                          component never hardcodes action buttons.
 *   - $actionsColumnLabel : string, header label for the actions column
 *                          (default 'Actions')
 *   - $emptyStateText    : string, message shown when $rows is empty
 *                          (default 'No results found.')
 *   - $paginationCallback : string, name of a JS function to call with the
 *                          target page number, e.g. 'goToPage' (default).
 *                          The including page must define:
 *                              function goToPage(page) { ... }
 *   - $tableId          : optional string, DOM id prefix for this table
 *                          instance (default 'dataTable'). Useful if a page
 *                          ever needs two tables side by side.
 *
 * Usage sketch (bookings.php style):
 *
 *   $columns = [
 *       ['label' => 'Reference', 'key' => 'reference_code'],
 *       ['label' => 'Patient',   'key' => 'patient_name'],
 *       ['label' => 'Status',    'key' => 'status_badge', 'raw' => true],
 *   ];
 *   $rows = array_map(function ($b) {
 *       ob_start();
 *       $status = $b['status'];
 *       include __DIR__ . '/status-badge.php';
 *       $badgeHtml = ob_get_clean();
 *
 *       return [
 *           'reference_code' => $b['reference_code'],
 *           'patient_name'   => $b['first_name'] . ' ' . $b['last_name'],
 *           'status_badge'   => $badgeHtml,
 *           '_actions'       => '<button onclick="confirmBooking(' . $b['id'] . ')">Confirm</button>',
 *       ];
 *   }, $bookingsFromBackend);
 *   $currentPage = 1;
 *   $totalPages = 4;
 *   include __DIR__ . '/../components/data-table.php';
 */

$columns             = $columns ?? [];
$rows                = $rows ?? [];
$currentPage         = max(1, (int) ($currentPage ?? 1));
$totalPages          = max(1, (int) ($totalPages ?? 1));
$showActionsColumn   = $showActionsColumn ?? true;
$actionsColumnLabel  = $actionsColumnLabel ?? 'Actions';
$emptyStateText      = $emptyStateText ?? 'No results found.';
$paginationCallback  = $paginationCallback ?? 'goToPage';
$tableId             = $tableId ?? 'dataTable';

/**
 * Builds a windowed list of page numbers to display, with null entries
 * standing in for an ellipsis. Keeps the pagination bar from becoming
 * unbounded on tables with many pages.
 */
function dataTablePageWindow(int $current, int $total, int $windowSize = 1): array
{
    $pages = [];
    $start = max(1, $current - $windowSize);
    $end = min($total, $current + $windowSize);

    if ($start > 1) {
        $pages[] = 1;
        if ($start > 2) $pages[] = null; // ellipsis
    }
    for ($i = $start; $i <= $end; $i++) {
        $pages[] = $i;
    }
    if ($end < $total) {
        if ($end < $total - 1) $pages[] = null; // ellipsis
        $pages[] = $total;
    }
    return $pages;
}
?>
<div id="<?php echo htmlspecialchars($tableId); ?>-wrapper"
     class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-[0_4px_16px_rgba(0,71,141,0.06)] overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="<?php echo htmlspecialchars($tableId); ?>">
            <thead>
                <tr class="border-b border-outline-variant/20 bg-surface-container-low/50">
                    <?php foreach ($columns as $col): ?>
                        <th class="text-left px-4 py-3 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                            <?php echo htmlspecialchars($col['label'] ?? ''); ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if ($showActionsColumn): ?>
                        <th class="text-right px-4 py-3 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                            <?php echo htmlspecialchars($actionsColumnLabel); ?>
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
                <?php if (empty($rows)): ?>
                    <!-- Empty state rendered as a single full-width row, handled below -->
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <?php foreach ($columns as $col): ?>
                                <?php
                                    $key = $col['key'] ?? '';
                                    $value = $row[$key] ?? '';
                                    $isRaw = $col['raw'] ?? false;
                                ?>
                                <td class="px-4 py-3 text-on-surface align-top">
                                    <?php echo $isRaw ? $value : htmlspecialchars((string) $value); ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if ($showActionsColumn): ?>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex justify-end items-center gap-1.5 flex-wrap">
                                        <?php echo $row['_actions'] ?? ''; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($rows)): ?>
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <span class="material-symbols-outlined text-4xl text-outline-variant mb-2" aria-hidden="true">inbox</span>
            <p class="text-sm text-on-surface-variant font-medium"><?php echo htmlspecialchars($emptyStateText); ?></p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-t border-outline-variant/20 flex-wrap gap-3">
        <p class="text-xs text-on-surface-variant">
            Page <?php echo (int) $currentPage; ?> of <?php echo (int) $totalPages; ?>
        </p>
        <div class="flex items-center gap-1.5 flex-wrap">
            <button type="button"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-surface-container-low text-on-surface-variant hover:bg-surface-container transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    onclick="<?php echo htmlspecialchars($paginationCallback); ?>(<?php echo (int) $currentPage - 1; ?>)"
                    <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
                <span class="material-symbols-outlined text-[15px]">chevron_left</span> Prev
            </button>

            <?php foreach (dataTablePageWindow($currentPage, $totalPages) as $pageNum): ?>
                <?php if ($pageNum === null): ?>
                    <span class="px-2 text-xs text-on-surface-variant/50">&hellip;</span>
                <?php else: ?>
                    <button type="button"
                            class="min-w-[32px] px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors <?php echo $pageNum === $currentPage ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'; ?>"
                            onclick="<?php echo htmlspecialchars($paginationCallback); ?>(<?php echo (int) $pageNum; ?>)"
                            <?php echo $pageNum === $currentPage ? 'aria-current="page"' : ''; ?>>
                        <?php echo (int) $pageNum; ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>

            <button type="button"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-surface-container-low text-on-surface-variant hover:bg-surface-container transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    onclick="<?php echo htmlspecialchars($paginationCallback); ?>(<?php echo (int) $currentPage + 1; ?>)"
                    <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>
                Next <span class="material-symbols-outlined text-[15px]">chevron_right</span>
            </button>
        </div>
    </div>
</div>