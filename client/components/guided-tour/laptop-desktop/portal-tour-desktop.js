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
            } else if (rect.top - padding - tooltipRect.height > 0) {
                top = rect.top - padding - tooltipRect.height;
                left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                placed = true;
            } else if (rect.right + padding + tooltipRect.width < vW) {
                top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.right + padding;
                placed = true;
            } else if (rect.left - padding - tooltipRect.width > 0) {
                top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.left - padding - tooltipRect.width;
                placed = true;
            }

            if (!placed) {
                // Desktop safe-center fallback
                top = (vH / 2) - (tooltipRect.height / 2);
                left = (vW / 2) - (tooltipRect.width / 2);
            }
        }

        // Clamp to screen edges
        if (left < margin) left = margin;
        if (left + tooltipRect.width > vW - margin) left = vW - tooltipRect.width - margin;
        if (top < margin) top = margin;
        if (top + tooltipRect.height > vH - margin) top = vH - tooltipRect.height - margin;

        this.tooltip.style.top = `${top}px`;
        this.tooltip.style.left = `${left}px`;
        this.tooltip.style.transform = 'none';
    }

    trackTarget() {
        if (!this.isActive) return;

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

        // Center target
        const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.currentTarget.scrollIntoView({ behavior: isReducedMotion ? 'auto' : 'smooth', block: 'center' });
        
        this.spotlight.style.transform = 'none';
        this.spotlight.style.borderRadius = '16px';
        
        // Start continuous tracking
        this._trackingLoop();
    }

    _trackingLoop() {
        if (!this.isActive || !this.currentTarget) return;
        
        const rect = this.currentTarget.getBoundingClientRect();
        const padding = 16;
        
        this.spotlight.style.width = `${rect.width + padding * 2}px`;
        this.spotlight.style.height = `${rect.height + padding * 2}px`;
        this.spotlight.style.left = `${rect.left - padding}px`;
        this.spotlight.style.top = `${rect.top - padding}px`;
        
        this.trackReq = requestAnimationFrame(() => this._trackingLoop());
    }

    async cleanup() {
        cancelAnimationFrame(this.trackReq);
        await super.cleanup();
    }
}