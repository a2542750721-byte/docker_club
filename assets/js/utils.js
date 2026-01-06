/**
 * Global Utility Functions
 * Loaded early in <head> to ensure availability
 */

/**
 * Unified Scroll Manager for modals and containers
 */
const ScrollManager = {
    init() {
        // Initialize for existing containers
        this.attachToExisting();
        
        // Use MutationObserver to handle dynamically added containers
        const observer = new MutationObserver((mutations) => {
            this.attachToExisting();
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
    },

    attachToExisting() {
        const selector = '.comp-view-body, .ai-output-area, .ai-lab-main, .history-list, .comp-nav-list';
        const containers = document.querySelectorAll(selector);
        containers.forEach(container => {
            if (!container._scrollInitialized) {
                container.addEventListener('wheel', this.handleWheel.bind(this), { passive: false });
                container._scrollInitialized = true;
            }
        });
    },

    handleWheel(e) {
        const container = e.currentTarget;
        const { scrollTop, scrollHeight, clientHeight } = container;
        
        // Bounce Effect at boundaries
        if (scrollTop === 0 && e.deltaY < 0) {
            this.applyBounce(container, 'top');
            e.preventDefault();
        } else if (Math.ceil(scrollTop + clientHeight) >= scrollHeight && e.deltaY > 0) {
            this.applyBounce(container, 'bottom');
            e.preventDefault();
        }
    },

    applyBounce(el, direction) {
        if (el._bouncing) return;
        el._bouncing = true;
        
        const offset = direction === 'top' ? 10 : -10;
        el.style.transition = 'transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        el.style.transform = `translateY(${offset}px)`;
        
        setTimeout(() => {
            el.style.transform = 'translateY(0)';
            setTimeout(() => {
                el.style.transition = '';
                el._bouncing = false;
            }, 200);
        }, 150);
    }
};

// Initialize early
document.addEventListener('DOMContentLoaded', () => ScrollManager.init());

/**
 * Safe function caller to prevent ReferenceErrors
 * @param {string} fnPath - Dot-separated path to function (e.g. "ideManager.saveFile")
 * @param {...any} args - Arguments to pass to the function
 */
function safeCall(fnPath, ...args) {
    const parts = fnPath.split('.');
    let context = window;
    let fn = window;

    for (const part of parts) {
        if (context === undefined || context === null) break;
        context = context[part];
    }
    
    fn = context;
    // Reset context to the parent object for apply
    context = window;
    if (parts.length > 1) {
        context = window;
        for (let i = 0; i < parts.length - 1; i++) {
            context = context[parts[i]];
        }
    }

    if (typeof fn === 'function') {
        return fn.apply(context, args);
    } else {
        console.warn(`[Utils] ${fnPath} is not defined yet or not a function.`);
    }
}
