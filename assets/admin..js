// Esistenze WordPress Kit - Admin Panel JavaScript

jQuery(document).ready(function($) {
    
    // Module card hover effects
    $('.module-card').hover(
        function() {
            $(this).find('.dashicons').css('transform', 'scale(1.2)');
        },
        function() {
            $(this).find('.dashicons').css('transform', 'scale(1)');
        }
    );
    
    // Smart Buttons functionality
    if ($('.sortable-button-list').length) {
        $('.sortable-button-list').sortable({
            update: function(event, ui) {
                var order = $(this).sortable('toArray', { attribute: 'data-index' });
                console.log('New order:', order);
            }
        });
    }

    // Add new button functionality for Smart Buttons
    $('#add-new-button').on('click', function () {
        var rowCount = $('.sortable-button-list tr').length;
        var newRow = $('.sortable-button-list tr:first').clone();

        newRow.find('input, select').each(function () {
            var name = $(this).attr('name');
            if (name) {
                var newName = name.replace(/\[\d+\]/, '[' + rowCount + ']');
                $(this).attr('name', newName);

                if ($(this).is(':checkbox') || $(this).is(':radio')) {
                    $(this).prop('checked', false);
                } else {
                    $(this).val('');
                }
            }
        });

        $('.sortable-button-list').append(newRow);
    });

    // Duplicate button functionality
    $(document).on('click', '.duplicate-btn', function () {
        var rowCount = $('.sortable-button-list tr').length;
        var clone = $(this).closest('tr').clone();

        clone.find('input, select').each(function () {
            var name = $(this).attr('name');
            if (name) {
                var newName = name.replace(/\[\d+\]/, '[' + rowCount + ']');
                $(this).attr('name', newName);
            }
        });

        $('.sortable-button-list').append(clone);
    });

    // Preview button functionality
    $(document).on('click', '.preview-btn', function () {
        var row = $(this).closest('tr');
        var title = row.find('input[name$="[title]"]').val();
        var type = row.find('select[name$="[type]"]').val();
        var value = row.find('input[name$="[value]"]').val();
        var color1 = row.find('input[name$="[button_color_start]"]').val();
        var color2 = row.find('input[name$="[button_color_end]"]').val();
        var textColor = row.find('input[name$="[text_color]"]').val();
        var icon = row.find('input[name$="[icon]"]').val();
        var fontSize = row.find('select[name$="[font_size]"]').val();

        var previewHtml = '<div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 10px 0;">';
        previewHtml += '<h4>Buton Önizlemesi:</h4>';
        previewHtml += '<a href="#" style="background:linear-gradient(45deg,' + color1 + ',' + color2 + '); color:' + textColor + '; font-size:' + fontSize + 'px; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600;">';
        if (icon) previewHtml += '<i class="fa ' + icon + '" style="margin-right: 5px;"></i>';
        previewHtml += title + '</a>';
        previewHtml += '</div>';

        // Create modal or alert
        if ($('#button-preview-modal').length === 0) {
            $('body').append('<div id="button-preview-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999;"><div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%;"><span id="close-preview" style="position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; color: #999;">&times;</span><div id="preview-content"></div></div></div>');
        }
        
        $('#preview-content').html(previewHtml);
        $('#button-preview-modal').fadeIn();
    });

    // Close preview modal
    $(document).on('click', '#close-preview, #button-preview-modal', function(e) {
        if (e.target.id === 'close-preview' || e.target.id === 'button-preview-modal') {
            $('#button-preview-modal').fadeOut();
        }
    });

    // Color picker change handlers
    $('input[type="color"]').on('change', function() {
        $(this).closest('td').css('background-color', $(this).val() + '20');
        setTimeout(function() {
            $(this).closest('td').css('background-color', '');
        }.bind(this), 1000);
    });

    // Form validation
    $('form').on('submit', function(e) {
        var hasError = false;
        var errorMessage = '';

        // Check required fields
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val()) {
                hasError = true;
                $(this).css('border-color', '#f44336');
                errorMessage += '- ' + $(this).closest('tr').find('th').text() + ' alanı zorunludur.\n';
            } else {
                $(this).css('border-color', '#ddd');
            }
        });

        // Check email format
        $(this).find('input[type="email"]').each(function() {
            if ($(this).val() && !isValidEmail($(this).val())) {
                hasError = true;
                $(this).css('border-color', '#f44336');
                errorMessage += '- Geçerli bir e-posta adresi girin.\n';
            }
        });

        // Check URL format
        $(this).find('input[type="url"]').each(function() {
            if ($(this).val() && !isValidUrl($(this).val())) {
                hasError = true;
                $(this).css('border-color', '#f44336');
                errorMessage += '- Geçerli bir URL girin (http:// veya https:// ile başlamalı).\n';
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('Lütfen aşağıdaki hataları düzeltin:\n\n' + errorMessage);
        }
    });

    // Helper functions
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidUrl(url) {
        var urlRegex = /^https?:\/\/.+/;
        return urlRegex.test(url);
    }

    // Auto-save functionality (optional)
    var autoSaveTimer;
    $('input, select, textarea').on('input change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Add visual indicator that changes are being saved
            showSaveIndicator();
        }, 2000);
    });

    function showSaveIndicator() {
        if ($('#auto-save-indicator').length === 0) {
            $('body').append('<div id="auto-save-indicator" style="position: fixed; top: 32px; right: 20px; background: #4CAF50; color: white; padding: 10px 15px; border-radius: 4px; z-index: 9999; display: none;">Değişiklikler kaydediliyor...</div>');
        }
        $('#auto-save-indicator').fadeIn().delay(2000).fadeOut();
    }

    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 50
            }, 500);
        }
    });

    // Add tooltips to help icons
    $('[data-tooltip]').hover(
        function() {
            var tooltip = $('<div class="admin-tooltip">' + $(this).data('tooltip') + '</div>');
            $('body').append(tooltip);
            tooltip.css({
                position: 'absolute',
                top: $(this).offset().top - tooltip.outerHeight() - 10,
                left: $(this).offset().left,
                background: '#333',
                color: 'white',
                padding: '5px 10px',
                borderRadius: '4px',
                fontSize: '12px',
                zIndex: 9999
            }).fadeIn();
        },
        function() {
            $('.admin-tooltip').remove();
        }
    );

    // Initialize any existing sortables
    if (typeof $.fn.sortable !== 'undefined') {
        $('.sortable').sortable({
            placeholder: 'sortable-placeholder',
            update: function(event, ui) {
                // Handle sort update
                console.log('Items reordered');
            }
        });
    }

    console.log('Esistenze WP Kit Admin Scripts Loaded');
});