/**
 * Esistenze Tool-Kit Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        EsistenzaAdmin.init();
    });

    window.EsistenzaAdmin = {
        
        init: function() {
            this.initMediaUploader();
            this.initFormValidation();
            this.initTooltips();
            this.addAnimations();
        },

        initMediaUploader: function() {
            // Media uploader for image fields
            $(document).on('click', '.upload-image-button', function(e) {
                e.preventDefault();
                
                var button = $(this);
                var imageField = button.siblings('.image-url');
                var preview = button.siblings('.preview');
                
                var uploader = wp.media({
                    title: 'Görsel Seç',
                    button: { text: 'Seç' },
                    multiple: false
                }).on('select', function() {
                    var attachment = uploader.state().get('selection').first().toJSON();
                    imageField.val(attachment.url);
                    preview.html('<img src="' + attachment.url + '" style="max-width:100px; margin-top:10px;">');
                }).open();
            });
        },

        initFormValidation: function() {
            // Basic form validation
            $('form').on('submit', function() {
                var isValid = true;
                
                $(this).find('[required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('error');
                    } else {
                        $(this).removeClass('error');
                    }
                });
                
                if (!isValid) {
                    alert('Lütfen tüm zorunlu alanları doldurun.');
                    return false;
                }
            });
            
            // Remove error class on input
            $('[required]').on('input change', function() {
                if ($(this).val()) {
                    $(this).removeClass('error');
                }
            });
        },

        initTooltips: function() {
            // Simple tooltip functionality
            $('.has-tooltip').hover(
                function() {
                    var tooltip = $(this).data('tooltip');
                    $('<div class="esistenza-tooltip">' + tooltip + '</div>')
                        .appendTo('body')
                        .fadeIn('fast');
                },
                function() {
                    $('.esistenza-tooltip').remove();
                }
            ).mousemove(function(e) {
                $('.esistenza-tooltip').css({
                    top: e.pageY + 10,
                    left: e.pageX + 10
                });
            });
        },

        addAnimations: function() {
            // Add entrance animations to admin cards
            $('.module-card').each(function(index) {
                $(this).css({
                    'animation-delay': (index * 0.1) + 's'
                }).addClass('esistenze-fade-in');
            });
        },

        showNotification: function(message, type) {
            type = type || 'success';
            var notification = $('<div class="esistenza-notification ' + type + '">' + message + '</div>');
            
            $('body').append(notification);
            
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        },

        confirmDelete: function(message) {
            return confirm(message || 'Bu işlemi gerçekleştirmek istediğinize emin misiniz?');
        }
    };

    // Add error styling
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .error {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
            .esistenza-tooltip {
                position: absolute;
                background: #333;
                color: #fff;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 9999;
                pointer-events: none;
            }
            .esistenza-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 4px;
                z-index: 9999;
                font-weight: 600;
            }
            .esistenza-notification.success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .esistenza-notification.error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
        `)
        .appendTo('head');

})(jQuery); 