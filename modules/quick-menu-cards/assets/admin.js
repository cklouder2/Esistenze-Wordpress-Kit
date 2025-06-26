/*
 * Quick Menu Cards - Admin JavaScript (Basitleştirilmiş)
 */

(function($) {
    'use strict';
    
    var EsistenzeQuickMenuAdmin = {
        cardIndex: 0,
        
        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.initSortable();
            this.updateCardIndexes();
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
            
            // Form gönderme
            $('form').on('submit', this.validateForm.bind(this));
        },
        
        addCard: function(e) {
            e.preventDefault();
            
            var template = $('#card-template').html();
            if (!template) {
                console.error('Card template not found');
                return;
            }
            
            var newIndex = this.getNextCardIndex();
            var cardHtml = template.replace(/\{\{INDEX\}\}/g, newIndex);
            
            $('#cards-container').append(cardHtml);
            this.updateCardIndexes();
            this.initColorPickers();
            
            // Yeni karta scroll
            var newCard = $('#cards-container .card-editor').last();
            $('html, body').animate({
                scrollTop: newCard.offset().top - 100
            }, 500);
        },
        
        removeCard: function(e) {
            e.preventDefault();
            
            if (confirm('Bu kartı silmek istediğinizden emin misiniz?')) {
                $(e.target).closest('.card-editor').remove();
                this.updateCardIndexes();
            }
        },
        
        selectImage: function(e) {
            e.preventDefault();
            
            var button = $(e.target);
            var input = button.siblings('.image-url');
            
            // WordPress medya kütüphanesi
            var mediaUploader = wp.media({
                title: 'Görsel Seç',
                button: {
                    text: 'Seç'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                input.val(attachment.url);
            });
            
            mediaUploader.open();
        },
        
        copyShortcode: function(e) {
            e.preventDefault();
            
            var button = $(e.target);
            var shortcode = button.data('shortcode');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shortcode).then(function() {
                    button.text('Kopyalandı!');
                    setTimeout(function() {
                        button.html('<span class="dashicons dashicons-clipboard"></span>');
                    }, 2000);
                });
            } else {
                // Fallback
                var textArea = document.createElement('textarea');
                textArea.value = shortcode;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                button.text('Kopyalandı!');
                setTimeout(function() {
                    button.html('<span class="dashicons dashicons-clipboard"></span>');
                }, 2000);
            }
        },
        
        validateForm: function(e) {
            var groupName = $('#group_name').val().trim();
            if (!groupName) {
                alert('Grup adı boş olamaz!');
                e.preventDefault();
                return false;
            }
            
            var hasValidCard = false;
            $('.card-editor').each(function() {
                var title = $(this).find('input[name*="[title]"]').val().trim();
                if (title) {
                    hasValidCard = true;
                    return false;
                }
            });
            
            if (!hasValidCard) {
                alert('En az bir kart başlığı girilmelidir!');
                e.preventDefault();
                return false;
            }
            
            return true;
        },
        
        initColorPickers: function() {
            $('.color-picker').wpColorPicker();
        },
        
        initSortable: function() {
            $('#cards-container').sortable({
                handle: '.card-drag-handle',
                placeholder: 'card-placeholder',
                update: function() {
                    EsistenzeQuickMenuAdmin.updateCardIndexes();
                }
            });
        },
        
        updateCardIndexes: function() {
            $('#cards-container .card-editor').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('h4').first().text('Kart #' + (index + 1) + ' ');
                $(this).find('h4').first().append('<button type="button" class="button-link remove-card" style="color: red;">Sil</button>');
                
                // Update input names
                $(this).find('input, textarea, select').each(function() {
                    var name = $(this).attr('name');
                    if (name && name.indexOf('cards[') === 0) {
                        var newName = name.replace(/cards\[\d+\]/, 'cards[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        },
        
        getNextCardIndex: function() {
            var maxIndex = -1;
            $('#cards-container .card-editor').each(function() {
                var index = parseInt($(this).attr('data-index'));
                if (index > maxIndex) {
                    maxIndex = index;
                }
            });
            return maxIndex + 1;
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        EsistenzeQuickMenuAdmin.init();
    });
    
})(jQuery);