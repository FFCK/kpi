/**
 * Bootstrap 5 Tooltip Initialization
 *
 * Initializes all Bootstrap 5 tooltips in the application.
 * Replaces jquery.tooltip.js with native Bootstrap 5 functionality.
 *
 * Migration from jQuery Tooltip to Bootstrap 5:
 * - jQuery Tooltip: jq("*").tooltip({ showURL: false })
 * - Bootstrap 5: Automatic initialization via data-bs-toggle="tooltip"
 *
 * Usage in HTML:
 * <button type="button"
 *         class="btn btn-secondary"
 *         data-bs-toggle="tooltip"
 *         data-bs-placement="top"
 *         title="Tooltip text">
 *   Hover me
 * </button>
 *
 * Or for any element with a title attribute:
 * <span title="This will show as a tooltip">Hover over me</span>
 *
 * @requires Bootstrap 5.3+
 * @author Claude Code
 * @date November 2025
 */

(function() {
    'use strict';

    /**
     * Initialize tooltips on DOM ready
     */
    function initTooltips() {
        // Get all elements with data-bs-toggle="tooltip"
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');

        // Initialize Bootstrap tooltips for explicit triggers
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover focus'  // Show on hover and focus (accessibility)
            });
        });

        // OPTIONAL: Auto-initialize tooltips for ALL elements with title attribute
        // This mimics jQuery tooltip's "*" selector behavior
        // Uncomment if you want automatic tooltip for all title attributes
        /*
        const allElementsWithTitle = document.querySelectorAll('[title]:not([data-bs-toggle="tooltip"])');
        const autoTooltipList = [...allElementsWithTitle].map(element => {
            // Don't initialize tooltips on inputs (can interfere with native browser tooltips)
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
                return null;
            }

            return new bootstrap.Tooltip(element, {
                trigger: 'hover focus'
            });
        }).filter(Boolean);
        */

        console.log('Bootstrap 5 Tooltips initialized:', tooltipList.length, 'tooltips');
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTooltips);
    } else {
        // DOM is already ready
        initTooltips();
    }

    /**
     * Re-initialize tooltips for dynamically added content
     * Call this function after AJAX content loading
     */
    window.reinitializeTooltips = function() {
        // Dispose existing tooltips first to avoid duplicates
        const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        existingTooltips.forEach(element => {
            const tooltip = bootstrap.Tooltip.getInstance(element);
            if (tooltip) {
                tooltip.dispose();
            }
        });

        // Re-initialize
        initTooltips();
    };

})();
