/**
 * portal-tour-compact.js
 * Extends PortalTourCore for mobile and tablet (<1024px).
 * Uses a fixed bottom-sheet tooltip, one-time highlight calculation, and swipe gestures.
 */

class PortalTourCompact extends PortalTourCore {
    constructor(config) {
        super(config);
        this.touchStartX = 0;
        this.touchEndX = 0;
    }

    createElements() {
        // Invisible Click-Shield
        this.shield = document.createElement('div');
        this.shield.id = 'tourShield';
        this.shield.className = 'fixed inset-0 z-[9996] cursor-default';
        document.body.appendChild(this.shield);

        // Spotlight Element (Dims background via massive box-shadow)
        this.spotlight = document.createElement('div');
        this.spotlight.id = 'tourSpotlight';
        this.spotlight.className = 'fixed z-[9997] pointer-events-none rounded-2xl tour-spotlight-base tour-dim-inactive';
        document.body.appendChild(this.spotlight);

        // Interactive Tooltip Dialog - Bottom Sheet Style
        this.tooltip = document.createElement('div');
        this.tooltip.id = 'tourTooltip';
        // Fixed bottom anchor, safe padding, top rounding
        this.tooltip.className = 'fixed z-[9998] bg-surface-container-lowest shadow-[0_-10px_40px_rgba(0,0,0,0.15)] bottom-0 left-1/2 -translate-x-1/2 w-full md:w-[600px] rounded-t-3xl p-6 pb-safe mb-0 tour-tooltip-base tour-tooltip-hidden tour-sheet-spring';
        this.tooltip.setAttribute('role', 'dialog');
        this.tooltip.setAttribute('aria-modal', 'true');
        this.tooltip.setAttribute('aria-label', 'Portal Tour');
        
        // Add Swipe Listeners
        this.tooltip.addEventListener('touchstart', (e) => {
            this.touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        this.tooltip.addEventListener('touchend', (e) => {
            this.touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe();
        }, { passive: true });

        document.body.appendChild(this.tooltip);
        this.tooltip.addEventListener('keydown', this.trapFocus);
    }

    handleSwipe() {
        const threshold = 50;
        if (this.touchEndX < this.touchStartX - threshold) {
            this.nextStep(); // Swiped left
        } else if (this.touchEndX > this.touchStartX + threshold) {
            this.prevStep(); // Swiped right
        }
    }

    positionTooltip(targetEl) {
        // For the compact layout, the bottom-sheet is positioned purely via CSS.
        // We only reset any transforms applied by the base animations if needed.
        this.tooltip.style.transform = 'translate(-50%, 0)'; 
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

        // Get sheet height to offset scroll
        const sheetHeight = this.tooltip.getBoundingClientRect().height || 250;
        
        // Temporarily adjust scroll margin to prevent target landing under the sheet
        this.currentTarget.style.scrollMarginBottom = `${sheetHeight + 32}px`;
        this.currentTarget.style.scrollMarginTop = `32px`;

        const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        this.currentTarget.scrollIntoView({ 
            behavior: isReducedMotion ? 'auto' : 'smooth', 
            block: 'center' 
        });

        // Clear margin
        this.currentTarget.style.scrollMarginBottom = '';
        this.currentTarget.style.scrollMarginTop = '';

        // Wait for smooth scroll to finish, then calculate and apply highlight once
        setTimeout(() => {
            if (!this.isActive || !this.currentTarget) return;
            const rect = this.currentTarget.getBoundingClientRect();
            const padding = 16;
            
            this.spotlight.style.transform = 'none';
            this.spotlight.style.borderRadius = '16px';
            this.spotlight.style.width = `${rect.width + padding * 2}px`;
            this.spotlight.style.height = `${rect.height + padding * 2}px`;
            this.spotlight.style.left = `${rect.left - padding}px`;
            this.spotlight.style.top = `${rect.top - padding}px`;
        }, isReducedMotion ? 50 : 350);
    }
}