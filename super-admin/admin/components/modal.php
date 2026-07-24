<?php
/**
 * admin/components/modal.php
 * Reusable modal HTML shell/backdrop. Renders one modal instance per include.
 * Pairs with admin/components/modal.js for open/close/focus-trap behavior.
 *
 * This file does NOT know what goes inside the modal — the including page
 * builds the body markup and passes it in as a string via $modalBodyHtml.
 *
 * Expects the including page to set, before including this file:
 *   - $modalId        : string, unique DOM id for this modal (required)
 *   - $modalTitle     : string, shown in the header area (required)
 *   - $modalBodyHtml   : string, raw HTML for the body area (required)
 *   - $modalMaxWidth   : optional Tailwind max-width class, defaults to 'max-w-lg'
 *
 * See modal.js for a full usage example (HTML + trigger + wiring).
 */

$modalId = $modalId ?? null;
$modalTitle = $modalTitle ?? '';
$modalBodyHtml = $modalBodyHtml ?? '';
$modalMaxWidth = $modalMaxWidth ?? 'max-w-lg';

if (!$modalId) {
    // Fail loudly in a way that's easy to spot during development, but
    // never let a missing id silently render a broken/unreachable modal.
    echo '<!-- admin/components/modal.php: $modalId is required, modal not rendered -->';
    return;
}
?>
<div id="<?php echo htmlspecialchars($modalId); ?>"
     class="modal-overlay"
     data-modal
     aria-hidden="true"
     role="dialog"
     aria-modal="true"
     aria-labelledby="<?php echo htmlspecialchars($modalId); ?>-title">

    <div class="modal-card bg-surface-container-lowest rounded-2xl shadow-2xl w-full <?php echo htmlspecialchars($modalMaxWidth); ?> max-h-[88vh] overflow-y-auto">

        <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-outline-variant/15 sticky top-0 bg-surface-container-lowest z-10">
            <h3 id="<?php echo htmlspecialchars($modalId); ?>-title" class="text-lg font-bold text-on-surface">
                <?php echo htmlspecialchars($modalTitle); ?>
            </h3>
            <button type="button"
                    class="text-on-surface-variant/50 hover:text-on-surface-variant transition-colors rounded-lg p-1"
                    onclick="closeModal('<?php echo htmlspecialchars($modalId); ?>')"
                    aria-label="Close dialog">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="px-6 py-5">
            <?php echo $modalBodyHtml; ?>
        </div>
    </div>
</div>