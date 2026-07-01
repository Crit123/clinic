/**
 * portal-tour.js
 * Generic, reusable interactive onboarding and tour experience engine.
 * Handles state machine, spotlight tracking, focus trapping, and transitions.
 * (Dashboard-specific content has been externalized).
 */

class PortalTour {
    constructor(config = {}) {
        this.steps = config.steps || [];
        this.onCleanup = config.onCleanup || (async () => {});
        this.currentStep = 0;
        this.isActive = false;
        this.triggerElement = null;
        this.activeNavCollapse = null;
        this.currentTarget = null;
        this.trackReq = null;

        // Bindings to preserve 'this' context in event listeners
        this.handleKeyDown = this.handleKeyDown.bind(this);
        this.handleResize  = this.handleResize.bind(this);
        this.trapFocus     = this.trapFocus.bind(this);
        this.trackTarget   = this.trackTarget.bind(this);

        this._injectStyles();
    }

    _injectStyles() {
        if (document.getElementById('portal-tour-animations')) return;
        
        const style = document.createElement('style');
        style.id = 'portal-tour-animations';
        
        // Define animation classes using the standard easing curve.
        // No !important on transitions to ensure the global prefers-reduced-motion override works perfectly.
        style.textContent = `
            .tour-spotlight-base {
                transition: box-shadow 250ms cubic-bezier(0.4, 0, 0.2, 1),
                            width 400ms cubic-bezier(0.4, 0, 0.2, 1),
                            height 400ms cubic-bezier(0.4, 0, 0.2, 1),
                            left 300ms cubic-bezier(0.4, 0, 0.2, 1),
                            top 300ms cubic-bezier(0.4, 0, 0.2, 1),
                            border-radius 300ms cubic-bezier(0.4, 0, 0.2, 1);
            }
            .tour-tooltip-base {
                transition: opacity 250ms cubic-bezier(0.4, 0, 0.2, 1),
                            transform 250ms cubic-bezier(0.4, 0, 0.2, 1);
            }
            .tour-tooltip-exit {
                transition: opacity 150ms cubic-bezier(0.4, 0, 0.2, 1),
                            transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
            }
            .tour-fade-out {
                transition: box-shadow 200ms cubic-bezier(0.4, 0, 0.2, 1),
                            opacity 200ms cubic-bezier(0.4, 0, 0.2, 1),
                            transform 200ms cubic-bezier(0.4, 0, 0.2, 1);
            }
            .tour-dim-active {
                box-shadow: 0 0 0 9999px rgba(0, 30, 49, 0.75);
            }
            .tour-dim-inactive {
                box-shadow: 0 0 0 9999px rgba(0, 30, 49, 0);
            }
            .tour-tooltip-hidden {
                opacity: 0;
                transform: translateY(12px);
            }
            .tour-tooltip-visible {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(style);
    }

    async start(triggerElement) {
        if (this.isActive || this.steps.length === 0) return;

        this.isActive = true;
        this.triggerElement = triggerElement;
        this.currentStep = 0;
        this.activeNavCollapse = null;

        this.createElements();
        
        document.addEventListener('keydown', this.handleKeyDown);
        window.addEventListener('resize', this.handleResize);

        // Hide main content sections from screen readers
        document.getElementById('contentContainer')?.setAttribute('aria-hidden', 'true');
        document.getElementById('mainSidebar')?.setAttribute('aria-hidden', 'true');
        
        const headerEl = document.getElementById('header');
        if (headerEl) {
            headerEl.setAttribute('aria-hidden', 'true');
        }

        // Force reflow before applying active classes to trigger entrance transition
        this.spotlight.offsetHeight;
        this.spotlight.classList.remove('tour-dim-inactive');
        this.spotlight.classList.add('tour-dim-active');

        await this.renderStep(0);
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

        // Interactive Tooltip Dialog
        this.tooltip = document.createElement('div');
        this.tooltip.id = 'tourTooltip';
        this.tooltip.className = 'fixed z-[9998] bg-surface-container-lowest shadow-2xl rounded-2xl p-6 w-[340px] max-w-[calc(100vw-32px)] tour-tooltip-base tour-tooltip-hidden';
        this.tooltip.setAttribute('role', 'dialog');
        this.tooltip.setAttribute('aria-modal', 'true');
        this.tooltip.setAttribute('aria-label', 'Portal Tour');
        document.body.appendChild(this.tooltip);
        
        this.tooltip.addEventListener('keydown', this.trapFocus);
    }

    async renderStep(index) {
        const step = this.steps[index];
        const isTransitioning = this.tooltip.classList.contains('tour-tooltip-visible');

        // Generalised check: Close nav if we are leaving a step that opened it, and next step doesn't need it.
        if (this.activeNavCollapse && (!step.collapsibleNav || step.collapsibleNav !== this.activeNavCollapse)) {
            this.activeNavCollapse.collapse();
            this.activeNavCollapse = null;
            await new Promise(r => setTimeout(r, 350));
        }

        // Execute exit logic of the prior step
        const prevStep = this.steps[this.currentStep];
        if (this.currentStep !== index && prevStep && prevStep.onExit) {
            await prevStep.onExit();
        }

        // Transition out tooltip smoothly (if visible)
        if (isTransitioning) {
            this.tooltip.classList.remove('tour-tooltip-base', 'tour-tooltip-visible');
            this.tooltip.classList.add('tour-tooltip-exit', 'tour-tooltip-hidden');
            await new Promise(r => setTimeout(r, 150));
        }

        this.currentStep = index;

        // Generalised check: Open nav if this step requires it.
        if (step.collapsibleNav && step.collapsibleNav.isCollapsed()) {
            this.activeNavCollapse = step.collapsibleNav;
            this.activeNavCollapse.expand();
            await new Promise(r => setTimeout(r, 350));
        }

        if (step.onEnter) {
            await step.onEnter();
        }

        const targetEl = typeof step.target === 'function' ? step.target() : step.target;
        this.currentTarget = targetEl;

        // Ensure target is in view
        if (targetEl) {
            const isMobile = window.innerWidth < 768;
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            targetEl.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: isMobile ? 'start' : 'center' });
        }

        // Apply Spotlight tracking
        cancelAnimationFrame(this.trackReq);
        if (targetEl) {
            this.spotlight.style.transform = 'none';
            this.spotlight.style.borderRadius = '16px';
            this.trackTarget();
        } else {
            // Full screen dim center
            this.spotlight.style.width = '0px';
            this.spotlight.style.height = '0px';
            this.spotlight.style.left = '50%';
            this.spotlight.style.top = '50%';
            this.spotlight.style.transform = 'translate(-50%, -50%)';
            this.spotlight.style.borderRadius = '50%';
        }

        // Render Tooltip DOM
        const textContent = typeof step.text === 'function' ? step.text() : step.text;
        this.tooltip.innerHTML = this.buildTooltipHTML(step, index, this.steps.length, textContent);

        // Bind tooltip controls
        this.tooltip.querySelector('.tour-skip-btn')?.addEventListener('click', () => this.skip());
        this.tooltip.querySelector('.tour-skip-text-btn')?.addEventListener('click', () => this.skip());
        this.tooltip.querySelector('.tour-next-btn')?.addEventListener('click', () => {
            if (this.currentStep === this.steps.length - 1) this.complete();
            else this.renderStep(this.currentStep + 1);
        });
        this.tooltip.querySelector('.tour-back-btn')?.addEventListener('click', () => {
            if (this.currentStep > 0) this.renderStep(this.currentStep - 1);
        });

        // Position & Fade in Tooltip
        requestAnimationFrame(() => {
            this.positionTooltip(targetEl);
            this.tooltip.classList.remove('tour-tooltip-exit', 'tour-tooltip-hidden');
            this.tooltip.classList.add('tour-tooltip-base', 'tour-tooltip-visible');
            
            // Move focus into the tooltip's primary action after a brief delay
            setTimeout(() => {
                const nextBtn = this.tooltip.querySelector('.tour-next-btn');
                if (nextBtn) nextBtn.focus();
            }, 50);
        });
    }

    buildTooltipHTML(step, index, total, text) {
        let dots = '';
        for (let i = 0; i < total; i++) {
            const active = i === index ? 'bg-primary w-4' : 'bg-outline-variant w-2';
            dots += `<div class="h-2 rounded-full transition-all duration-300 ${active}"></div>`;
        }

        const isLast = index === total - 1;
        const nextText = isLast ? 'Finish' : 'Next';
        
        const skipTextBtn = `<button class="tour-skip-text-btn text-on-surface-variant hover:text-on-surface font-label-md px-2 sm:px-3 py-2 hover:bg-surface-container-low rounded-lg transition-colors focus:ring-2 focus:ring-primary outline-none" aria-label="Skip">Skip</button>`;
        const backBtn = index > 0 
            ? `<button class="tour-back-btn text-primary font-label-md px-3 py-2 hover:bg-surface-container-low rounded-lg transition-colors focus:ring-2 focus:ring-primary outline-none">Back</button>` 
            : `<div></div>`;

        return `
            <button class="tour-skip-btn absolute top-3 right-3 w-8 h-8 flex items-center justify-center text-outline hover:text-on-surface hover:bg-surface-container-low rounded-full transition-colors focus:ring-2 focus:ring-primary outline-none" aria-label="Close Tour">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
            <div class="pr-6">
                <h3 class="font-headline-md text-lg text-primary mb-2">${step.title}</h3>
                <p class="font-body-md text-sm text-on-surface-variant leading-relaxed">${text}</p>
            </div>
            <div class="flex items-center justify-between mt-4 pt-2">
                <div class="flex gap-1.5 items-center" aria-hidden="true">
                    ${dots}
                </div>
                <div class="flex gap-1 sm:gap-2">
                    ${skipTextBtn}
                    ${backBtn}
                    <button class="tour-next-btn bg-primary hover:bg-primary-container text-on-primary font-label-md px-4 sm:px-5 py-2 rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-primary focus:ring-offset-2 outline-none">
                        ${nextText}
                    </button>
                </div>
            </div>
        `;
    }

    // Continuously matches spotlight to the scrolling target via rAF
    trackTarget() {
        if (!this.isActive || !this.currentTarget) return;
        const rect = this.currentTarget.getBoundingClientRect();
        const padding = 16;
        
        this.spotlight.style.width = `${rect.width + padding * 2}px`;
        this.spotlight.style.height = `${rect.height + padding * 2}px`;
        this.spotlight.style.left = `${rect.left - padding}px`;
        this.spotlight.style.top = `${rect.top - padding}px`;
        
        this.trackReq = requestAnimationFrame(this.trackTarget);
    }

    positionTooltip(targetEl) {
        const tooltipRect = this.tooltip.getBoundingClientRect();
        const vW = window.innerWidth;
        const vH = window.innerHeight;
        const margin = 16;
        const isMobile = window.innerWidth < 768;

        let top, left;
        let placed = false;

        if (!targetEl) {
            top = (vH / 2) - (tooltipRect.height / 2);
            left = (vW / 2) - (tooltipRect.width / 2);
            placed = true;
        } else {
            const rect = targetEl.getBoundingClientRect();
            const padding = 24; 

            if (!isMobile) {
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
            }

            if (!placed) {
                if (isMobile) {
                    // Responsive dock: Bottom-sheet style 
                    top = vH - tooltipRect.height - margin;
                    left = (vW / 2) - (tooltipRect.width / 2); 
                } else {
                    // Desktop safe-center fallback
                    top = (vH / 2) - (tooltipRect.height / 2);
                    left = (vW / 2) - (tooltipRect.width / 2);
                }
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

    handleKeyDown(e) {
        if (e.key === 'Escape') this.skip();
    }

    handleResize() {
        if (!this.isActive) return;
        this.positionTooltip(this.currentTarget);
    }

    trapFocus(e) {
        const focusable = this.tooltip.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable.length === 0) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === first) {
                    last.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === last) {
                    first.focus();
                    e.preventDefault();
                }
            }
        }
    }

    async skip() {
        await this.cleanup();
    }

    async complete() {
        localStorage.setItem('dcpro_tour_completed', 'true');
        await this.cleanup();
    }

    async cleanup() {
        this.isActive = false;
        cancelAnimationFrame(this.trackReq);
        
        document.removeEventListener('keydown', this.handleKeyDown);
        window.removeEventListener('resize', this.handleResize);

        // Exit active step
        const step = this.steps[this.currentStep];
        if (step && step.onExit) await step.onExit();

        // Restore navigation structure safely if it was left expanded
        if (this.activeNavCollapse) {
            this.activeNavCollapse.collapse();
            this.activeNavCollapse = null;
        }

        // Swap to 200ms fadeout exit transition
        this.spotlight.classList.remove('tour-spotlight-base', 'tour-dim-active');
        this.tooltip.classList.remove('tour-tooltip-base', 'tour-tooltip-visible');

        this.spotlight.classList.add('tour-fade-out', 'tour-dim-inactive');
        this.tooltip.classList.add('tour-fade-out', 'tour-tooltip-hidden');

        await new Promise(r => setTimeout(r, 200));

        // Teardown injected DOM elements
        this.shield?.remove();
        this.spotlight?.remove();
        this.tooltip?.remove();

        // Restore screen reader access & header state
        document.getElementById('contentContainer')?.removeAttribute('aria-hidden');
        document.getElementById('mainSidebar')?.removeAttribute('aria-hidden');
        
        const headerEl = document.getElementById('header');
        if (headerEl) {
            headerEl.removeAttribute('aria-hidden');
        }

        // External configuration cleanup callback
        if (this.onCleanup) {
            await this.onCleanup(this.currentStep);
        }

        // Return focus safely
        if (this.triggerElement) {
            this.triggerElement.focus();
        }
    }
}