<?php
/**
 * client/components/branch-selector.php
 * Reusable "Choose a Location" step used at the start of the booking flow
 * and the emergency-request form.
 *
 * Renders NOTHING when $deploymentMode !== 'multi'. In 'single' mode, the
 * including page is expected to skip this step entirely and go straight to
 * the next one — this file enforces that by returning early rather than
 * relying on the caller to conditionally include it.
 *
 * NOTE: $deploymentMode is hardcoded below for preview purposes only. In
 * production this should come from a real config/env value set upstream
 * (e.g. config/deployment.php) and passed in before this include, the same
 * way admin/components/topbar.php expects it.
 *
 * NOTE: no `branches` table exists yet in the current schema (clinicdb.sql
 * only has users/bookings/etc.) — $branches below is stubbed with
 * name-only placeholder data. Once a real branches table exists, swap the
 * stub for a query result of the same shape: ['id' => ..., 'name' => ...,
 * 'address' => ...].
 *
 * Output contract:
 *   - Renders a single hidden <input type="hidden" name="branch_id" id="branchIdInput">
 *     inside a wrapper the parent form already contains (or this component
 *     creates its own <div> — either way, submitting the parent <form> as
 *     normal will include branch_id in the payload since the input lives in
 *     the DOM, not inside a nested <form>).
 *   - Exposes window.getSelectedBranchId() and a 'branchselected' custom
 *     event (detail: { branchId }) so the parent booking/emergency form can
 *     validate that a branch was chosen before letting the user proceed.
 */

// Hardcoded for preview only — see NOTE above.
$deploymentMode = 'multi';

if ($deploymentMode !== 'multi') {
    return; // Single-location deployments skip this step entirely.
}

// Stub data — replace with a real query once a branches table exists.
$branches = [
    ['id' => 1, 'name' => 'DentalCare Pro — Quezon City', 'address' => null],
    ['id' => 2, 'name' => 'DentalCare Pro — Makati',      'address' => null],
    ['id' => 3, 'name' => 'DentalCare Pro — San Jose del Monte', 'address' => null],
];
?>
<style>
    .branch-card {
        border: 1.5px solid rgba(114, 119, 131, 0.25);
        border-radius: 1rem;
        padding: 16px 18px;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        background-color: #ffffff;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .branch-card:hover { border-color: rgba(0, 71, 141, 0.4); background-color: rgba(0, 71, 141, 0.03); }
    .branch-card.branch-selected {
        border-color: #00478d;
        background-color: rgba(0, 71, 141, 0.06);
        box-shadow: 0 0 0 3px rgba(0, 71, 141, 0.1);
    }
    .branch-radio-dot {
        width: 20px; height: 20px; border-radius: 9999px; border: 2px solid rgba(114,119,131,0.35);
        flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: border-color 0.15s ease;
    }
    .branch-card.branch-selected .branch-radio-dot { border-color: #00478d; }
    .branch-radio-dot .branch-radio-fill {
        width: 10px; height: 10px; border-radius: 9999px; background-color: #00478d;
        transform: scale(0); transition: transform 0.15s ease;
    }
    .branch-card.branch-selected .branch-radio-fill { transform: scale(1); }
</style>

<div id="branchSelectorStep" class="space-y-3" data-component="branch-selector">
    <div>
        <h3 class="text-base font-bold text-on-surface mb-0.5">Choose a Location</h3>
        <p class="text-sm text-on-surface-variant">Select the DentalCare Pro branch you'd like this to be for.</p>
    </div>

    <div id="branchCardList" class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Choose a location">
        <?php foreach ($branches as $branch): ?>
            <div class="branch-card"
                 role="radio"
                 tabindex="0"
                 aria-checked="false"
                 data-branch-id="<?php echo (int) $branch['id']; ?>"
                 onclick="selectBranch(<?php echo (int) $branch['id']; ?>, this)"
                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault(); selectBranch(<?php echo (int) $branch['id']; ?>, this);}">
                <span class="branch-radio-dot"><span class="branch-radio-fill"></span></span>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-on-surface truncate"><?php echo htmlspecialchars($branch['name']); ?></p>
                    <?php if (!empty($branch['address'])): ?>
                        <p class="text-xs text-on-surface-variant truncate"><?php echo htmlspecialchars($branch['address']); ?></p>
                    <?php else: ?>
                        <p class="text-xs text-on-surface-variant/60 italic">Address coming soon</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p id="branchSelectionError" class="hidden text-xs font-semibold text-error flex items-center gap-1">
        <span class="material-symbols-outlined text-[15px]">error</span>
        Please choose a location before continuing.
    </p>

    <!-- Lives in the DOM (not a nested <form>), so the parent booking/
         emergency form picks this up automatically as part of its own
         submission payload. -->
    <input type="hidden" name="branch_id" id="branchIdInput" value="">
</div>

<script>
    /**
     * Selects a branch card, updates its visual state, writes the chosen
     * id into the hidden branch_id field, and notifies the parent form.
     */
    function selectBranch(branchId, cardEl) {
        document.querySelectorAll('#branchCardList .branch-card').forEach(card => {
            card.classList.remove('branch-selected');
            card.setAttribute('aria-checked', 'false');
        });

        cardEl.classList.add('branch-selected');
        cardEl.setAttribute('aria-checked', 'true');

        const hiddenInput = document.getElementById('branchIdInput');
        if (hiddenInput) hiddenInput.value = branchId;

        const errorEl = document.getElementById('branchSelectionError');
        if (errorEl) errorEl.classList.add('hidden');

        document.dispatchEvent(new CustomEvent('branchselected', { detail: { branchId } }));
    }

    /**
     * Returns the currently selected branch id (empty string if none
     * selected yet). The parent form should call this before advancing to
     * the next step and show #branchSelectionError if it's empty.
     */
    window.getSelectedBranchId = function () {
        const hiddenInput = document.getElementById('branchIdInput');
        return hiddenInput ? hiddenInput.value : '';
    };

    /**
     * Convenience validator the parent form can call directly: returns true
     * if a branch is selected, otherwise shows the inline error and
     * returns false.
     */
    window.validateBranchSelected = function () {
        const selected = window.getSelectedBranchId();
        const errorEl = document.getElementById('branchSelectionError');
        if (!selected) {
            if (errorEl) errorEl.classList.remove('hidden');
            return false;
        }
        return true;
    };
</script>