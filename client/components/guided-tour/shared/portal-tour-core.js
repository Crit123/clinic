/**
 * portal-tour-core.js
 * Shared interactive onboarding engine base class.
 * Handles state machine, focus trapping, keyboard nav, skip/complete state,
 * step navigation, and sidebar lock orchestration.
 * Extended by compact (mobile/tablet) and desktop subclasses.
 */

class PortalTourCore {
    constructor(config = {}) {
        this.steps = config.steps || [];
        this.onCleanup = config.onCleanup || (async () => {});
        this.currentStep = 0;
        this.isActive = false;
        this.triggerElement = null;
        this.activeNavCollapse = null;
        this.currentTarget = null;

        // Bindings to preserve 'this' context in event listeners
        this.handleKeyDown = this.handleKeyDown.bind(this);
        this.handleResize  = this.handleResize.bind(this);
        this.trapFocus     = this.trapFocus.bind(this);

        this._injectStyles();
    }

    _injectStyles() {
        if (document.getElementById('portal-tour-animations')) return;
        
        const style = document.createElement('style');
        style.id = 'portal-tour-animations';
        style.textContent = `
            .tour-spotlight-base {
                transition: box-shadow 350ms cubic-bezier(0.34, 1.56, 0.64, 1),
                            width 450ms cubic-bezier(0.34, 1.2, 0.64, 1),
                            height 450ms cubic-bezier(0.34, 1.2, 0.64, 1),
                            left 450ms cubic-bezier(0.34, 1.2, 0.64, 1),
                            top 450ms cubic-bezier(0.34, 1.2, 0.64, 1),
                            border-radius 350ms cubic-bezier(0.4, 0, 0.2, 1);
            }
            .tour-tooltip-base {
                transition: opacity 320ms cubic-bezier(0.4, 0, 0.2, 1),
                            transform 320ms cubic-bezier(0.4, 0, 0.2, 1);
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
                /* Entrance easing lives on .tour-spotlight-base above — NOT here.
                   Declaring a separate transition shorthand on this class would
                   silently replace the whole transition list on .tour-spotlight-base
                   (both classes sit on the element at once for the entire tour),
                   killing every width/height/left/top transition for the whole
                   session. Keep this class value-only. */
            }
            .tour-dim-inactive {
                box-shadow: 0 0 0 9999px rgba(0, 30, 49, 0);
            }
            /* Entrance direction mirrors which side positionTooltip() actually
               placed the tooltip on (desktop sets data-placement to match the
               bottom -> top -> right -> left auto-placement branch that fired),
               so the tooltip slides in FROM the target rather than always
               sliding up from below regardless of where it landed. Compact
               tour never sets data-placement, so it falls through to the
               translateY(12px) default below untouched. */
            .tour-tooltip-hidden {
                opacity: 0;
                transform: translateY(12px);
            }
            .tour-tooltip-hidden[data-placement="bottom"] {
                transform: translateY(-12px);
            }
            .tour-tooltip-hidden[data-placement="top"] {
                transform: translateY(12px);
            }
            .tour-tooltip-hidden[data-placement="left"] {
                transform: translateX(12px);
            }
            .tour-tooltip-hidden[data-placement="right"] {
                transform: translateX(-12px);
            }
            .tour-tooltip-hidden[data-placement="center"] {
                transform: translateY(12px);
            }
            .tour-tooltip-visible {
                opacity: 1;
                transform: translate(0, 0);
            }
            /* Direct-follow mode used only during the post-settle drift-correction
               loop (desktop), once the initial eased move has already finished —
               never during the move itself, which needs the eased transition above. */
            .tour-tracking {
                transition: none !important;
            }
            /* Decouples text-swap fade from the tooltip container's slide/opacity,
               so content doesn't "pop" the instant the container starts fading in. */
            .tour-content {
                transition: opacity 180ms ease-out;
            }
            .tour-content-hidden {
                opacity: 0;
            }
            .tour-content-visible {
                opacity: 1;
            }
            /* Progress dot gets a small scale pulse on activation so step changes
               feel acknowledged rather than just recolored. */
            .tour-dot {
                transition: background-color 300ms cubic-bezier(0.4, 0, 0.2, 1),
                            width 300ms cubic-bezier(0.4, 0, 0.2, 1),
                            transform 220ms cubic-bezier(0.34, 1.56, 0.64, 1);
            }
            .tour-dot-active {
                transform: scale(1.15);
            }
            /* iOS-style bottom sheet curve: decelerates hard at rest, reads as
               "settled" rather than mechanically eased. Compact tour only.
               NOTE: this class sits on the tooltip alongside .tour-tooltip-base
               and .tour-tooltip-exit for the entire compact tour, and all three
               declare the transition shorthand. Shorthand replaces the whole
               property rather than merging, so whichever rule is declared LAST
               in this stylesheet wins outright. Keep this rule after both of
               those so the spring curve is what actually applies. */
            .tour-sheet-spring {
                transition: transform 320ms cubic-bezier(0.32, 0.72, 0, 1),
                            opacity 250ms ease;
            }
            @media (prefers-reduced-motion: reduce) {
                .tour-spotlight-base, .tour-tooltip-base, .tour-fade-out,
                .tour-dim-active, .tour-content, .tour-dot, .tour-sheet-spring,
                .tour-tracking {
                    transition-duration: 0.01ms !important;
                }
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

        this.createElements(); // Implemented by subclass
        
        this.triggerElement?.blur();
        
        document.addEventListener('keydown', this.handleKeyDown);
        window.addEventListener('resize', this.handleResize);

        // Hide main content sections from screen readers
        document.getElementById('contentContainer')?.setAttribute('aria-hidden', 'true');
        document.getElementById('mainSidebar')?.setAttribute('aria-hidden', 'true');
        document.getElementById('header')?.setAttribute('aria-hidden', 'true');

        // Force reflow before applying active classes to trigger entrance transition
        this.spotlight.offsetHeight;
        this.spotlight.classList.remove('tour-dim-inactive');
        this.spotlight.classList.add('tour-dim-active');

        await this.renderStep(0);
    }

    // Abstract methods to be overridden by subclasses
    createElements() { throw new Error('createElements must be implemented by subclass'); }
    trackTarget() { throw new Error('trackTarget must be implemented by subclass'); }
    positionTooltip(targetEl) { throw new Error('positionTooltip must be implemented by subclass'); }

    async renderStep(index) {
        const step = this.steps[index];
        const isTransitioning = this.tooltip.classList.contains('tour-tooltip-visible');

        // Close nav if we are leaving a step that opened it
        if (this.activeNavCollapse && (!step.collapsibleNav || step.collapsibleNav !== this.activeNavCollapse)) {
            this.activeNavCollapse.collapse();
            window.sidebarForceOpenLock = false; // Release lock
            this.activeNavCollapse = null;
            await new Promise(r => setTimeout(r, 350));
        }

        const prevStep = this.steps[this.currentStep];
        if (this.currentStep !== index && prevStep && prevStep.onExit) {
            await prevStep.onExit();
        }

        if (isTransitioning) {
            this.tooltip.classList.remove('tour-tooltip-base', 'tour-tooltip-visible');
            this.tooltip.classList.add('tour-tooltip-exit', 'tour-tooltip-hidden');
            await new Promise(r => setTimeout(r, 150));
        }

        this.currentStep = index;

        // Open nav if this step requires it
        if (step.collapsibleNav && step.collapsibleNav.isCollapsed()) {
            window.sidebarForceOpenLock = true; // Set lock before expanding
            this.activeNavCollapse = step.collapsibleNav;
            this.activeNavCollapse.expand();
            await new Promise(r => setTimeout(r, 350));
        }

        if (step.onEnter) {
            await step.onEnter();
        }

        const targetEl = typeof step.target === 'function' ? step.target() : step.target;
        this.currentTarget = targetEl;

        // Visual tracking/scrolling handled by subclasses
        this.trackTarget();

        // Render Tooltip DOM
        const textContent = typeof step.text === 'function' ? step.text() : step.text;
        this.tooltip.innerHTML = this.buildTooltipHTML(step, index, this.steps.length, textContent);

        // Bind tooltip controls
        this.tooltip.querySelector('.tour-skip-text-btn')?.addEventListener('click', () => this.skip());
        this.tooltip.querySelector('.tour-next-btn')?.addEventListener('click', () => this.nextStep());
        this.tooltip.querySelector('.tour-back-btn')?.addEventListener('click', () => this.prevStep());

        // Spotlight starts moving now (styles already set above by trackTarget()).
        // Delay the tooltip's own fade-in slightly so the two movements read as
        // a sequence — spotlight leads, tooltip follows — rather than everything
        // appearing to happen in the same instant.
        setTimeout(() => {
            requestAnimationFrame(() => {
                this.positionTooltip(targetEl);
                this.tooltip.classList.remove('tour-tooltip-exit', 'tour-tooltip-hidden');
                this.tooltip.classList.add('tour-tooltip-base', 'tour-tooltip-visible');

                // Fade the text content in slightly after the container starts moving,
                // so the swap doesn't read as an instant pop.
                const contentEl = this.tooltip.querySelector('.tour-content');
                setTimeout(() => {
                    contentEl?.classList.remove('tour-content-hidden');
                    contentEl?.classList.add('tour-content-visible');
                }, 90);

                setTimeout(() => {
                    const nextBtn = this.tooltip.querySelector('.tour-next-btn');
                    if (nextBtn) nextBtn.focus();
                }, 150);
            });
        }, 90);
    }

    nextStep() {
        if (this.currentStep === this.steps.length - 1) this.complete();
        else this.renderStep(this.currentStep + 1);
    }

    prevStep() {
        if (this.currentStep > 0) this.renderStep(this.currentStep - 1);
    }

    buildTooltipHTML(step, index, total, text) {
        let dots = '';
        for (let i = 0; i < total; i++) {
            const active = i === index ? 'bg-primary w-4 tour-dot-active' : 'bg-outline-variant w-2';
            dots += `<div class="tour-dot h-2 rounded-full ${active}"></div>`;
        }

        const isLast = index === total - 1;
        const nextText = isLast ? 'Finish' : 'Next';
        
        const skipTextBtn = `<button class="tour-skip-text-btn text-on-surface-variant hover:text-on-surface font-label-md px-2 sm:px-3 py-2 hover:bg-surface-container-low rounded-lg transition-colors focus:ring-2 focus:ring-primary outline-none" aria-label="Skip">Skip</button>`;
        const backBtn = index > 0 
            ? `<button class="tour-back-btn text-primary font-label-md px-3 py-2 hover:bg-surface-container-low rounded-lg transition-colors focus:ring-2 focus:ring-primary outline-none">Back</button>` 
            : `<div></div>`;

        return `
            <div class="tour-content tour-content-hidden">
                <h3 class="font-headline-md text-lg text-primary mb-2">${step.title}</h3>
                <p class="font-body-md text-sm text-on-surface-variant leading-relaxed">${text}</p>
            </div>
            <div class="flex flex-col mt-4 pt-2 gap-4">
                <div class="flex gap-1.5 items-center justify-center w-full" aria-hidden="true">
                    ${dots}
                </div>
                <div class="flex items-center justify-between w-full">
                    ${skipTextBtn}
                    <div class="flex gap-1 sm:gap-2">
                        ${backBtn}
                        <button class="tour-next-btn bg-primary hover:bg-primary-container text-on-primary font-label-md px-4 sm:px-5 py-2 rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-primary focus:ring-offset-2 outline-none">
                            ${nextText}
                        </button>
                    </div>
                </div>
            </div>
        `;
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
        
        document.removeEventListener('keydown', this.handleKeyDown);
        window.removeEventListener('resize', this.handleResize);

        const step = this.steps[this.currentStep];
        if (step && step.onExit) await step.onExit();

        if (this.activeNavCollapse) {
            this.activeNavCollapse.collapse();
            window.sidebarForceOpenLock = false; // Release lock
            this.activeNavCollapse = null;
        }

        this.spotlight.classList.remove('tour-spotlight-base', 'tour-dim-active');
        this.tooltip.classList.remove('tour-tooltip-base', 'tour-tooltip-visible');

        this.spotlight.classList.add('tour-fade-out', 'tour-dim-inactive');
        this.tooltip.classList.add('tour-fade-out', 'tour-tooltip-hidden');

        await new Promise(r => setTimeout(r, 200));

        this.shield?.remove();
        this.spotlight?.remove();
        this.tooltip?.remove();

        document.getElementById('contentContainer')?.removeAttribute('aria-hidden');
        document.getElementById('mainSidebar')?.removeAttribute('aria-hidden');
        document.getElementById('header')?.removeAttribute('aria-hidden');

        if (this.onCleanup) {
            await this.onCleanup(this.currentStep);
        }

        if (this.triggerElement) {
            this.triggerElement.focus();
        }
    }
}