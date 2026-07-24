<?php
/**
 * admin/components/toast.php
 * Toast/notification container markup. Include this ONCE per page, near
 * the closing </body> tag of the admin layout — it just reserves a fixed
 * bottom-right stacking area that admin/components/toast.js populates.
 *
 * This file renders no visible content by itself; all toast HTML is
 * generated and appended by showToast() in toast.js at runtime.
 *
 * Usage:
 *   <?php include __DIR__ . '/../components/toast.php'; ?>
 *   <script src="../components/toast.js"></script>
 *   ...
 *   <script> showToast('Booking confirmed.', 'success'); </script>
 */
?>
<style>
    #toastContainer {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 300;
        display: flex;
        flex-direction: column-reverse; /* newest toast lands at the bottom, stack grows upward */
        gap: 0.6rem;
        max-width: min(360px, calc(100vw - 2rem));
        pointer-events: none; /* let clicks pass through the empty gaps between toasts */
    }

    .toast-item {
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 0.75rem;
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 600;
        line-height: 1.35;
        box-shadow: 0 12px 32px rgba(0,0,0,0.18);
        cursor: pointer;
        transform: translateX(120%);
        opacity: 0;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
    }
    .toast-item.toast-visible {
        transform: translateX(0);
        opacity: 1;
    }
    .toast-item.toast-leaving {
        transform: translateX(120%);
        opacity: 0;
    }

    .toast-item.toast-success { background-color: #16a34a; }
    .toast-item.toast-error   { background-color: #dc2626; }

    .toast-item .toast-icon {
        font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 20;
        font-size: 18px;
        line-height: 1;
        margin-top: 1px;
        flex-shrink: 0;
    }
    .toast-item .toast-message { flex: 1; min-width: 0; word-break: break-word; }
    .toast-item .toast-dismiss {
        flex-shrink: 0; opacity: 0.7; font-size: 16px; line-height: 1; margin-top: 1px;
    }
    .toast-item .toast-dismiss:hover { opacity: 1; }
</style>

<div id="toastContainer" aria-live="polite" aria-atomic="false"></div>