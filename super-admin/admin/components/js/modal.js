/**
 * admin/components/modal.js
 * Open/close logic for the reusable modal shell in admin/components/modal.php.
 *
 * ── USAGE EXAMPLE ────────────────────────────────────────────────────────
 *
 * 1. In your PHP page, build the body markup and include the shell:
 *
 *    <?php
 *    $modalId = 'cancelBookingModal';
 *    $modalTitle = 'Cancel Booking';
 *    $modalBodyHtml = '
 *        <p class="text-sm text-on-surface-variant mb-4">
 *            Are you sure you want to cancel this booking?
 *        </p>
 *        <div class="flex justify-end gap-2">
 *            <button type="button" class="px-4 py-2 text-sm font-bold text-on-surface-variant"
 *                    onclick="closeModal(\'cancelBookingModal\')">Back</button>
 *            <button type="button" class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-lg"
 *                    onclick="submitCancellation()">Confirm Cancel</button>
 *        </div>
 *    ';
 *    include __DIR__ . '/../components/modal.php';
 *    ?>
 *
 * 2. Include this script once per page (after modal.php includes) and load
 *    the modal-overlay/.modal-card CSS shown below (or your page's existing
 *    equivalent — several admin pages already define this same pattern).
 *
 * 3. Trigger it from anywhere:
 *
 *    <button onclick="openModal('cancelBookingModal')">Cancel Booking</button>
 *
 * Multiple named modals can coexist on the same page — each is looked up by
 * its own id, so openModal('a') never affects modal 'b'.
 *
 * ── REQUIRED CSS (include once per page, or in a shared stylesheet) ───────
 *
 *   .modal-overlay {
 *       position: fixed; inset: 0; background: rgba(11, 28, 48, 0.45); z-index: 100;
 *       display: flex; align-items: center; justify-content: center; padding: 1rem;
 *       opacity: 0; pointer-events: none; transition: opacity 0.2s ease;
 *   }
 *   .modal-overlay.open { opacity: 1; pointer-events: auto; }
 *   .modal-card {
 *       transform: translateY(12px) scale(0.98);
 *       transition: transform 0.2s cubic-bezier(0.16,1,0.3,1);
 *   }
 *   .modal-overlay.open .modal-card { transform: translateY(0) scale(1); }
 *
 * ──────────────────────────────────────────────────────────────────────────
 */

(function () {
    // Tracks the element that had focus before a modal opened, per modal id,
    // so focus can be restored to the trigger button on close.
    const previouslyFocused = {};

    const FOCUSABLE_SELECTOR = [
        'a[href]', 'button:not([disabled])', 'textarea:not([disabled])',
        'input:not([disabled])', 'select:not([disabled])',
        '[tabindex]:not([tabindex="-1"])'
    ].join(',');

    function getModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.warn(`openModal/closeModal: no element found with id "${modalId}"`);
        }
        return modal;
    }

    function getFocusableElements(modal) {
        return Array.from(modal.querySelectorAll(FOCUSABLE_SELECTOR))
            .filter(el => el.offsetParent !== null); // visible only
    }

    /**
     * Opens the modal with the given id, animates it in, and traps focus
     * inside it until closed.
     */
    window.openModal = function (modalId) {
        const modal = getModal(modalId);
        if (!modal) return;

        previouslyFocused[modalId] = document.activeElement;

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open-no-scroll');
        document.body.style.overflow = 'hidden';

        // Move focus into the modal once the fade-in has started.
        requestAnimationFrame(() => {
            const focusable = getFocusableElements(modal);
            (focusable[0] || modal).focus();
        });

        modal.addEventListener('keydown', trapFocus);
    };

    /**
     * Closes the modal with the given id, animates it out, and restores
     * focus to whatever triggered it.
     */
    window.closeModal = function (modalId) {
        const modal = getModal(modalId);
        if (!modal) return;

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        modal.removeEventListener('keydown', trapFocus);

        // Only restore body scroll if no other modal is still open.
        const anyOtherOpen = document.querySelectorAll('.modal-overlay.open').length > 0;
        if (!anyOtherOpen) {
            document.body.classList.remove('modal-open-no-scroll');
            document.body.style.overflow = '';
        }

        const toRestore = previouslyFocused[modalId];
        if (toRestore && typeof toRestore.focus === 'function') {
            toRestore.focus();
        }
        delete previouslyFocused[modalId];
    };

    /**
     * Basic focus trap: keeps Tab/Shift+Tab cycling within the modal's
     * focusable elements while it's open. Also closes on Escape.
     */
    function trapFocus(e) {
        const modal = e.currentTarget;

        if (e.key === 'Escape') {
            closeModal(modal.id);
            return;
        }

        if (e.key !== 'Tab') return;

        const focusable = getFocusableElements(modal);
        if (focusable.length === 0) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }

    // Backdrop click closes the modal (clicking the card itself should not).
    document.addEventListener('click', (e) => {
        const overlay = e.target.closest('.modal-overlay');
        if (overlay && e.target === overlay) {
            closeModal(overlay.id);
        }
    });

    // Global Escape fallback in case a modal is open but somehow lost its
    // own keydown listener (e.g. focus moved outside it programmatically).
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const openModalEl = document.querySelector('.modal-overlay.open');
        if (openModalEl) closeModal(openModalEl.id);
    });
})();