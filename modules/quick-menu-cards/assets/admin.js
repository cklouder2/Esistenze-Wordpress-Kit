/*
 * Quick Menu Cards - Admin JavaScript
 * Basit admin işlevleri
 */

(function($) {
    'use strict';
    
    var EsistenzeQuickMenuAdmin = {
        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.initMediaUploader();
        },
        
        bindEvents: function() {
            // Kart ekleme
            $(document).on('click', '#add-card', this.addCard.bind(this));
            
            // Kart silme
            $(document).on('click', '.remove-card', this.removeCard.bind(this));
            
            // Medya seçici
            $(document).on('click', '.select-image', this.selectImage.bind(this));
            
            // Shortcode kopyalama
            $(document).on('click', '.copy-shortcode', this.copyShortcode.bind(this));
            
            // Form değişiklik uyarısı
            $(document).on('change', 'form input, form textarea, form select', this.markFormDirty);
            $(window).on('beforeunload', this.warnUnsavedChanges);
        },
        
        addCard: function(e) {
            e.preventDefault();
            
            var $container = $('#cards-container');
            var $template = $('#card-template');
            var cardIndex = $container.find('.card-editor').length;
            
            if (cardIndex >= 20) {
                alert('En fazla 20 kart ekleyebilirsiniz.');
                return;
            }
            
            var templateHtml = $template.html();
            templateHtml = templateHtml.replace(/\{\{INDEX\}\}/g, cardIndex);
            
            $container.append(templateHtml);
            this.updateCardNumbers();
        },
        
        removeCard: function(e) {
            e.preventDefault();
            
            if (!confirm('Bu kartı silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            $(e.currentTarget).closest('.card-editor').remove();
            this.updateCardNumbers();
        },
        
        updateCardNumbers: function() {
            $('#cards-container .card-editor').each(function(index) {
                $(this).find('h4').first().text('Kart #' + (index + 1) + ' ');
                $(this).find('h4').first().append('<button type="button" class="button-link remove-card">Sil</button>');
            });
        },
        
        selectImage: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var $input = $button.siblings('.image-url');
            
            var mediaUploader = wp.media({
                title: 'Görsel Seç',
                button: {
                    text: 'Seçilen Görseli Kullan'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $input.val(attachment.url);
            });
            
            mediaUploader.open();
        },
        
        copyShortcode: function(e) {
            e.preventDefault();
            
            var $code = $(e.currentTarget).siblings('code');
            var shortcode = $code.text();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shortcode).then(function() {
                    alert('Shortcode kopyalandı!');
                });
            } else {
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = shortcode;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Shortcode kopyalandı!');
            }
        },
        
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.color-picker').wpColorPicker();
            }
        },
        
        initMediaUploader: function() {
            // Media uploader already handled in selectImage method
        },
        
        markFormDirty: function() {
            window.formIsDirty = true;
        },
        
        warnUnsavedChanges: function(e) {
            if (window.formIsDirty) {
                var message = 'Kaydedilmemiş değişiklikleriniz var. Sayfadan çıkmak istediğinizden emin misiniz?';
                e.returnValue = message;
                return message;
            }
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        EsistenzeQuickMenuAdmin.init();
        
        // Clear dirty flag on successful form submit
        $('form').on('submit', function() {
            window.formIsDirty = false;
        });
    });
    
})(jQuery);