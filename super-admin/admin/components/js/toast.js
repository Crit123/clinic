/**
 * admin/components/toast.js
 * Global showToast(message, type) function used across the admin panel.
 * Pairs with admin/components/toast.php, which must be included once per
 * page (near </body>) to provide the #toastContainer stacking area.
 *
 * Usage:
 *   showToast('Booking confirmed.', 'success');
 *   showToast('Could not save changes.', 'error');
 *
 * - Multiple toasts stack (newest appears at the bottom of the stack,
 *   pushing older ones up), since #toastContainer uses
 *   flex-direction: column-reverse.
 * - Each toast auto-dismisses after ~3 seconds.
 * - Clicking a toast dismisses it early.
 * - Toasts slide in from the right and fade in; they reverse the same
 *   animation on dismiss before being removed from the DOM.
 */

(function () {
    const AUTO_DISMISS_MS = 3000;
    const EXIT_ANIMATION_MS = 300;

    const iconByType = {
        success: 'check_circle',
        error: 'error',
    };

    let toastCounter = 0;

    function getContainer() {
        const container = document.getElementById('toastContainer');
        if (!container) {
            console.warn('showToast(): #toastContainer not found — make sure admin/components/toast.php is included on this page.');
        }
        return container;
    }

    /**
     * Removes a toast element, animating it out first.
     */
    function dismissToast(toastEl) {
        if (!toastEl || toastEl.dataset.dismissing === 'true') return;
        toastEl.dataset.dismissing = 'true';

        clearTimeout(toastEl._autoDismissTimer);
        toastEl.classList.remove('toast-visible');
        toastEl.classList.add('toast-leaving');

        setTimeout(() => {
            toastEl.remove();
        }, EXIT_ANIMATION_MS);
    }

    /**
     * Shows a toast with the given message and type ('success' | 'error').
     * Unrecognized types fall back to the 'success' color/icon so a typo
     * never results in a broken/invisible toast.
     */
    window.showToast = function (message, type) {
        const container = getContainer();
        if (!container) return;

        const normalizedType = (type === 'error') ? 'error' : 'success';
        const icon = iconByType[normalizedType];

        const toastEl = document.createElement('div');
        toastEl.className = `toast-item toast-${normalizedType}`;
        toastEl.id = `toast-${Date.now()}-${toastCounter++}`;
        toastEl.setAttribute('role', normalizedType === 'error' ? 'alert' : 'status');

        toastEl.innerHTML = `
            <span class="material-symbols-outlined toast-icon">${icon}</span>
            <span class="toast-message"></span>
            <span class="material-symbols-outlined toast-dismiss">close</span>
        `;
        // Set message via textContent (not innerHTML) so arbitrary strings
        // can never inject markup into the toast.
        toastEl.querySelector('.toast-message').textContent = message ?? '';

        toastEl.addEventListener('click', () => dismissToast(toastEl));

        container.appendChild(toastEl);

        // Trigger the enter animation on the next frame (can't animate from
        // a just-inserted element's initial state in the same tick).
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toastEl.classList.add('toast-visible');
            });
        });

        toastEl._autoDismissTimer = setTimeout(() => dismissToast(toastEl), AUTO_DISMISS_MS);
    };
})();