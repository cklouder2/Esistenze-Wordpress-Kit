/**
 * Esistenze Tool-Kit Public JavaScript
 */

(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        EsistenzaToolkit.init();
    });

    // Main toolkit object
    window.EsistenzaToolkit = {
        
        init: function() {
            this.bindEvents();
            this.addAnimations();
        },

        bindEvents: function() {
            // Add any common event bindings here
            $('.esistenze-tooltip').on('mouseenter', this.showTooltip);
            $('.esistenze-tooltip').on('mouseleave', this.hideTooltip);
        },

        addAnimations: function() {
            // Add fade-in class to elements as they come into view
            $(window).on('scroll', function() {
                $('.esistenze-animate-on-scroll').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).addClass('esistenze-fade-in');
                    }
                });
            });
        },

        showTooltip: function() {
            // Tooltip functionality if needed
        },

        hideTooltip: function() {
            // Tooltip functionality if needed
        },

        utils: {
            debounce: function(func, wait, immediate) {
                var timeout;
                return function() {
                    var context = this, args = arguments;
                    var later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    var callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            }
        }
    };

})(jQuery); 