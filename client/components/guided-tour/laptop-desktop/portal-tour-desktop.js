/**
 * portal-tour-desktop.js
 * Extends PortalTourCore for desktop/laptop (>=1024px).
 * Uses a floating tooltip with auto-placement and continuous scroll-tracking.
 */

class PortalTourDesktop extends PortalTourCore {
    constructor(config) {
        super(config);
        this.trackReq = null;
        // Bind to preserve context in rAF loop
        this.trackTarget = this.trackTarget.bind(this);
    }

    createElements() {
        // Invisible Click-Shield
        this.shield = document.createElement('div');
        this.shield.id = 'tourShield';
        this.shield.className = 'fixed inset-0 z-[9996] cursor-default';
        document.body.appendChild(this.shield);

        // Spotlight Element
        this.spotlight = document.createElement('div');
        this.spotlight.id = 'tourSpotlight';
        this.spotlight.className = 'fixed z-[9997] pointer-events-none rounded-2xl tour-spotlight-base tour-dim-inactive';
        document.body.appendChild(this.spotlight);

        // Interactive Tooltip Dialog - Floating Style
        this.tooltip = document.createElement('div');
        this.tooltip.id = 'tourTooltip';
        this.tooltip.className = 'fixed z-[9998] bg-surface-container-lowest shadow-2xl rounded-2xl p-6 w-[340px] max-w-[calc(100vw-32px)] tour-tooltip-base tour-tooltip-hidden';
        this.tooltip.setAttribute('role', 'dialog');
        this.tooltip.setAttribute('aria-modal', 'true');
        this.tooltip.setAttribute('aria-label', 'Portal Tour');
        document.body.appendChild(this.tooltip);
        
        this.tooltip.addEventListener('keydown', this.trapFocus);
    }

    positionTooltip(targetEl) {
        const tooltipRect = this.tooltip.getBoundingClientRect();
        const vW = window.innerWidth;
        const vH = window.innerHeight;
        const margin = 16;

        let top, left;
        let placed = false;
        // Tracks which branch below fired so the entrance transform (CSS,
        // keyed off data-placement) can slide the tooltip in FROM the side
        // it was actually placed on instead of always sliding up from below.
        let placement = 'center';

        if (!targetEl) {
            top = (vH / 2) - (tooltipRect.height / 2);
            left = (vW / 2) - (tooltipRect.width / 2);
            placed = true;
        } else {
            const rect = targetEl.getBoundingClientRect();
            const padding = 24; 

            // Auto-placement logic: Prefer Bottom -> Top -> Right -> Left
            if (rect.bottom + padding + tooltipRect.height < vH) {
                top = rect.bottom + padding;
                left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                placed = true;
                placement = 'bottom';
            } else if (rect.top - padding - tooltipRect.height > 0) {
                top = rect.top - padding - tooltipRect.height;
                left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                placed = true;
                placement = 'top';
            } else if (rect.right + padding + tooltipRect.width < vW) {
                top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.right + padding;
                placed = true;
                placement = 'right';
            } else if (rect.left - padding - tooltipRect.width > 0) {
                top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.left - padding - tooltipRect.width;
                placed = true;
                placement = 'left';
            }

            if (!placed) {
                // Desktop safe-center fallback
                top = (vH / 2) - (tooltipRect.height / 2);
                left = (vW / 2) - (tooltipRect.width / 2);
                placement = 'center';
            }
        }

        this.tooltip.dataset.placement = placement;

        // Clamp to screen edges
        if (left < margin) left = margin;
        if (left + tooltipRect.width > vW - margin) left = vW - tooltipRect.width - margin;
        if (top < margin) top = margin;
        if (top + tooltipRect.height > vH - margin) top = vH - tooltipRect.height - margin;

        this.tooltip.style.top = `${top}px`;
        this.tooltip.style.left = `${left}px`;
        // Deliberately NOT setting style.transform here. This inline style
        // used to hard-reset to 'none' on every call, which silently wins
        // over the .tour-tooltip-hidden[data-placement]/.tour-tooltip-visible
        // CSS transforms (inline style beats class rules regardless of
        // source order), permanently freezing the entrance slide-in at
        // "no transform" — the fade still worked, so it read as a bug in
        // the animation curve when it was actually this line overriding it
        // every time. Positioning is handled entirely by top/left above;
        // transform is left for the CSS classes to animate.
    }

    trackTarget() {
        if (!this.isActive) return;

        cancelAnimationFrame(this.trackReq);
        this._trackToken = (this._trackToken || 0) + 1; // invalidate any pending settle/drift callbacks from the previous step
        this.spotlight.classList.remove('tour-tracking');

        if (!this.currentTarget) {
            // Full screen dim center
            this.spotlight.style.width = '0px';
            this.spotlight.style.height = '0px';
            this.spotlight.style.left = '50%';
            this.spotlight.style.top = '50%';
            this.spotlight.style.transform = 'translate(-50%, -50%)';
            this.spotlight.style.borderRadius = '50%';
            return;
        }

        this.spotlight.style.transform = 'none';
        this.spotlight.style.borderRadius = '16px';

        const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.currentTarget.scrollIntoView({ behavior: isReducedMotion ? 'auto' : 'smooth', block: 'center' });

        if (isReducedMotion) {
            // No native scroll animation to wait out — position immediately.
            this._applyRect(this.currentTarget.getBoundingClientRect());
            return;
        }

        // Native scrollIntoView runs its own easing. Re-targeting the
        // spotlight's CSS transition every animation frame while that scroll
        // is still moving means two independent easing curves fight each
        // other continuously — the transition never gets to finish a clean
        // motion, which reads as rough/juddery rather than smooth. So:
        // freeze the spotlight where it is, wait for the target's rect to
        // stop changing between frames, then perform ONE clean eased move.
        this._settleThenMove(this._trackToken);
    }

    _settleThenMove(token) {
        let lastRect = null;
        let stableFrames = 0;
        let frames = 0;
        const maxWaitFrames = 60; // ~1s safety cap in case rect never fully settles

        const check = () => {
            if (!this.isActive || token !== this._trackToken || !this.currentTarget) return;

            const rect = this.currentTarget.getBoundingClientRect();
            frames++;

            const isStable = lastRect
                && Math.abs(rect.top - lastRect.top) < 0.5
                && Math.abs(rect.left - lastRect.left) < 0.5;
            stableFrames = isStable ? stableFrames + 1 : 0;
            lastRect = rect;

            if (stableFrames >= 3 || frames >= maxWaitFrames) {
                this._applyRect(rect); // the one clean eased move
                this._startDriftCorrection(token);
                return;
            }
            this.trackReq = requestAnimationFrame(check);
        };
        this.trackReq = requestAnimationFrame(check);
    }

    _startDriftCorrection(token) {
        // After the eased move above has had time to finish, keep the
        // spotlight glued to the target with no transition — purely to
        // correct for drift from things like images loading or layout
        // shifts while the tooltip sits still. Cancelled the instant
        // trackTarget() runs again for the next step (token mismatch).
        setTimeout(() => {
            if (!this.isActive || token !== this._trackToken) return;
            this.spotlight.classList.add('tour-tracking');

            const drift = () => {
                if (!this.isActive || token !== this._trackToken || !this.currentTarget) return;
                this._applyRect(this.currentTarget.getBoundingClientRect());
                this.trackReq = requestAnimationFrame(drift);
            };
            this.trackReq = requestAnimationFrame(drift);
        }, 480);
    }

    _applyRect(rect) {
        const padding = 16;
        this.spotlight.style.width = `${rect.width + padding * 2}px`;
        this.spotlight.style.height = `${rect.height + padding * 2}px`;
        this.spotlight.style.left = `${rect.left - padding}px`;
        this.spotlight.style.top = `${rect.top - padding}px`;
    }

    async cleanup() {
        cancelAnimationFrame(this.trackReq);
        await super.cleanup();
    }
}