/*
 * Quick Menu Cards - Admin Edit Page JavaScript (Temel İşlevler)
 * Kart düzenleme sayfası - temel kart yönetimi işlevleri
 */

(function($) {
    'use strict';
    
    // Global namespace
    window.EsistenzeEditPage = {
        // Configuration
        config: {
            groupId: 0,
            moduleUrl: '',
            ajaxUrl: '',
            nonce: '',
            maxCards: 20,
            autoSave: true,
            debugMode: false
        },
        
        // State management
        state: {
            currentCards: 0,
            hasUnsavedChanges: false,
            dragIndex: -1,
            autoSaveTimer: null,
            lastSaveTime: null
        },
        
        // Initialize
        init: function(config) {
            this.config = $.extend(this.config, config);
            this.bindEvents();
            this.initCardEditor();
            this.initSortable();
            this.initAutoSave();
            this.setupValidation();
            
            if (this.config.debugMode) {
                console.log('Edit Page initialized', this.config);
            }
        },
        
        // Bind events
        bindEvents: function() {
            var self = this;
            
            // Card management
            $(document).on('click', '#add-card', this.addNewCard.bind(this));
            $(document).on('click', '.remove-card', this.removeCard.bind(this));
            $(document).on('click', '.duplicate-card', this.duplicateCard.bind(this));
            $(document).on('click', '.toggle-card', this.toggleCard.bind(this));
            
            // Form interactions
            $(document).on('input change', '.card-editor input, .card-editor textarea', this.handleFieldChange.bind(this));
            $(document).on('input', '.card-title', this.updateCardTitle.bind(this));
            $(document).on('input', '.card-title, .card-desc', this.updateCharacterCount.bind(this));
            $(document).on('change', '.card-enabled', this.toggleCardEnabled.bind(this));
            
            // Save operations
            $(document).on('click', '#save-group', this.saveGroup.bind(this));
            $(document).on('click', '#save-and-continue', this.saveAndContinue.bind(this));
            
            // URL validation and suggestions
            $(document).on('click', '.url-suggestion', this.selectUrlSuggestion.bind(this));
            $(document).on('input', '.card-url', this.validateUrl.bind(this));
            
            // Keyboard shortcuts
            $(document).on('keydown', this.handleKeyboardShortcuts.bind(this));
            
            // Before unload warning
            $(window).on('beforeunload', this.handleBeforeUnload.bind(this));
            
            // Visibility change for auto-save
            $(document).on('visibilitychange', this.handleVisibilityChange.bind(this));
        },
        
        // Initialize card editor
        initCardEditor: function() {
            this.updateCardIndices();
            this.initializeExistingCards();
            this.state.currentCards = $('.card-editor').length;
        },
        
        // Initialize existing cards
        initializeExistingCards: function() {
            var self = this;
            
            $('.card-editor').each(function(index) {
                var $card = $(this);
                self.initializeCard($card, index);
            });
        },
        
        // Initialize single card
        initializeCard: function($card, index) {
            $card.attr('data-index', index);
            this.updateCharacterCount($card.find('.card-title, .card-desc'));
            this.validateUrl($card.find('.card-url'));
        },
        
        // Add new card
        addNewCard: function(e) {
            e.preventDefault();
            
            if (this.state.currentCards >= this.config.maxCards) {
                this.showNotice('warning', 'En fazla ' + this.config.maxCards + ' kart ekleyebilirsiniz.');
                return;
            }
            
            var newIndex = this.state.currentCards;
            var cardHtml = this.getCardEditorTemplate(newIndex);
            
            $('#cards-list').append(cardHtml);
            
            var $newCard = $('#cards-list .card-editor:last');
            this.initializeCard($newCard, newIndex);
            
            // Scroll to new card
            $('html, body').animate({
                scrollTop: $newCard.offset().top - 100
            }, 500);
            
            // Focus on title field
            $newCard.find('.card-title').focus();
            
            this.state.currentCards++;
            this.updateCardIndices();
            this.markAsChanged();
            
            // Hide empty state if visible
            $('.no-cards-message').hide();
            
            this.showNotice('success', 'Yeni kart eklendi!');
        },
        
        // Remove card
        removeCard: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $card = $(e.currentTarget).closest('.card-editor');
            var cardTitle = $card.find('.card-title').val() || 'Başlıksız kart';
            
            if (!confirm('Bu kartı silmek istediğinizden emin misiniz?\n\n"' + cardTitle + '"')) {
                return;
            }
            
            var self = this;
            
            $card.fadeOut(300, function() {
                $(this).remove();
                self.state.currentCards--;
                self.updateCardIndices();
                self.markAsChanged();
                self.checkEmptyState();
            });
            
            this.showNotice('success', 'Kart silindi!');
        },
        
        // Duplicate card
        duplicateCard: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (this.state.currentCards >= this.config.maxCards) {
                this.showNotice('warning', 'En fazla ' + this.config.maxCards + ' kart ekleyebilirsiniz.');
                return;
            }
            
            var $originalCard = $(e.currentTarget).closest('.card-editor');
            var originalData = this.getCardData($originalCard);
            
            // Modify title to indicate it's a copy
            originalData.title = (originalData.title || 'Kart') + ' - Kopya';
            
            var newIndex = this.state.currentCards;
            var cardHtml = this.getCardEditorTemplate(newIndex, originalData);
            
            $originalCard.after(cardHtml);
            
            var $newCard = $originalCard.next('.card-editor');
            this.initializeCard($newCard, newIndex);
            
            this.state.currentCards++;
            this.updateCardIndices();
            this.markAsChanged();
            
            this.showNotice('success', 'Kart başarıyla kopyalandı!');
        },
        
        // Toggle card visibility
        toggleCard: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $card = $(e.currentTarget).closest('.card-editor');
            var $content = $card.find('.card-editor-content');
            var $icon = $(e.currentTarget).find('.dashicons');
            
            if ($content.is(':visible')) {
                $content.slideUp(300);
                $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                $card.addClass('collapsed');
            } else {
                $content.slideDown(300);
                $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
                $card.removeClass('collapsed');
            }
        },
        
        // Handle field changes
        handleFieldChange: function(e) {
            var $field = $(e.currentTarget);
            
            this.markAsChanged();
            
            // Trigger auto-save
            if (this.config.autoSave) {
                this.scheduleAutoSave();
            }
        },
        
        // Update card title in header
        updateCardTitle: function(e) {
            var $input = $(e.currentTarget);
            var $card = $input.closest('.card-editor');
            var title = $input.val() || 'Yeni Kart';
            
            $card.find('.card-title-preview').text(title);
        },
        
        // Update character count
        updateCharacterCount: function($field) {
            if ($field.target) {
                $field = $(e.currentTarget);
            }
            
            var maxLength = parseInt($field.attr('maxlength')) || 0;
            var currentLength = $field.val().length;
            var $counter = $field.siblings('.field-meta').find('.character-count .current');
            
            if ($counter.length === 0) return;
            
            $counter.text(currentLength);
            
            // Color coding
            var $characterCount = $counter.parent();
            $characterCount.removeClass('warning danger');
            
            if (maxLength > 0) {
                var percentage = (currentLength / maxLength) * 100;
                if (percentage >= 90) {
                    $characterCount.addClass('danger');
                } else if (percentage >= 80) {
                    $characterCount.addClass('warning');
                }
            }
        },
        
        // Toggle card enabled state
        toggleCardEnabled: function(e) {
            var $checkbox = $(e.currentTarget);
            var $card = $checkbox.closest('.card-editor');
            var isEnabled = $checkbox.prop('checked');
            
            // Update main enabled checkbox
            $card.find('.card-enabled-main').prop('checked', isEnabled);
            
            // Update visual indicators
            var $header = $card.find('.card-editor-header');
            var $visibilityIcon = $header.find('.visibility-icon');
            var $disabledBadge = $header.find('.disabled-badge');
            
            if (isEnabled) {
                $visibilityIcon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                $disabledBadge.hide();
                $card.removeClass('card-disabled');
            } else {
                $visibilityIcon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                if ($disabledBadge.length === 0) {
                    $header.find('.card-title-preview').after('<span class="disabled-badge" title="Pasif kart">🚫</span>');
                } else {
                    $disabledBadge.show();
                }
                $card.addClass('card-disabled');
            }
            
            this.markAsChanged();
        },
        
        // URL suggestions and validation
        selectUrlSuggestion: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var $card = $button.closest('.card-editor');
            var url = $button.data('url');
            
            $card.find('.card-url').val(url);
            this.validateUrl($card.find('.card-url'));
            this.markAsChanged();
        },
        
        validateUrl: function(e) {
            var $input = $(e.currentTarget || e);
            var $card = $input.closest('.card-editor');
            var $validation = $card.find('.url-validation');
            var url = $input.val().trim();
            
            if (!url) {
                $validation.hide();
                $input.removeClass('valid invalid');
                return;
            }
            
            var isValid = this.isValidUrl(url);
            
            if (isValid) {
                $input.removeClass('invalid').addClass('valid');
                $validation.hide();
            } else {
                $input.removeClass('valid').addClass('invalid');
                $validation.show().html(
                    '<span class="validation-icon dashicons dashicons-warning"></span>' +
                    '<span class="validation-message">Geçersiz URL formatı</span>'
                );
            }
        },
        
        // Save operations
        saveGroup: function(e) {
            e.preventDefault();
            
            if (!this.validateForm()) {
                return;
            }
            
            this.performSave(false);
        },
        
        saveAndContinue: function(e) {
            e.preventDefault();
            
            if (!this.validateForm()) {
                return;
            }
            
            this.performSave(true);
        },
        
        performSave: function(continueEditing) {
            var self = this;
            var cardsData = this.getAllCardsData();
            var $saveButton = $('#save-group, #save-and-continue');
            
            $saveButton.prop('disabled', true);
            this.showLoading();
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_save_card_group',
                    nonce: this.config.nonce,
                    group_id: this.config.groupId,
                    cards_data: cardsData
                },
                success: function(response) {
                    self.hideLoading();
                    $saveButton.prop('disabled', false);
                    
                    if (response.success) {
                        self.state.hasUnsavedChanges = false;
                        self.state.lastSaveTime = new Date();
                        self.showNotice('success', response.data.message || 'Grup başarıyla kaydedildi!');
                        
                        if (!continueEditing) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect || self.getGroupsPageUrl();
                            }, 1500);
                        } else {
                            // Update group ID if this was a new group
                            if (response.data.group_id && self.config.groupId !== response.data.group_id) {
                                self.config.groupId = response.data.group_id;
                                self.updateUrl();
                            }
                        }
                    } else {
                        self.showNotice('error', response.data || 'Kayıt sırasında hata oluştu.');
                    }
                },
                error: function(xhr, status, error) {
                    self.hideLoading();
                    $saveButton.prop('disabled', false);
                    self.showNotice('error', 'Bağlantı hatası: ' + error);
                }
            });
        },
        
        // Auto-save functionality
        initAutoSave: function() {
            if (!this.config.autoSave) return;
            
            var self = this;
            
            // Auto-save every 60 seconds
            setInterval(function() {
                if (self.state.hasUnsavedChanges && self.validateForm(true)) {
                    self.performAutoSave();
                }
            }, 60000);
        },
        
        scheduleAutoSave: function() {
            if (!this.config.autoSave) return;
            
            var self = this;
            
            clearTimeout(this.state.autoSaveTimer);
            this.state.autoSaveTimer = setTimeout(function() {
                if (self.state.hasUnsavedChanges && self.validateForm(true)) {
                    self.performAutoSave();
                }
            }, 10000); // 10 seconds after last change
        },
        
        performAutoSave: function() {
            var cardsData = this.getAllCardsData();
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_auto_save_group',
                    nonce: this.config.nonce,
                    group_id: this.config.groupId,
                    cards_data: cardsData
                },
                success: function(response) {
                    if (response.success) {
                        self.state.lastSaveTime = new Date();
                        self.showAutoSaveIndicator();
                    }
                }
            });
        },
        
        showAutoSaveIndicator: function() {
            var $indicator = $('.auto-save-indicator');
            if ($indicator.length === 0) {
                $('body').append('<div class="auto-save-indicator">Otomatik kaydedildi</div>');
                $indicator = $('.auto-save-indicator');
            }
            
            $indicator.fadeIn(200).delay(2000).fadeOut(300);
        },
        
        // Sortable functionality
        initSortable: function() {
            if (!$.fn.sortable) return;
            
            var self = this;
            
            $('#cards-list').sortable({
                handle: '.card-drag-handle',
                placeholder: 'card-placeholder',
                tolerance: 'pointer',
                opacity: 0.8,
                start: function(event, ui) {
                    self.state.dragIndex = ui.item.index();
                    ui.placeholder.height(ui.item.height());
                },
                update: function(event, ui) {
                    self.updateCardIndices();
                    self.markAsChanged();
                    
                    var newIndex = ui.item.index();
                    if (self.state.dragIndex !== newIndex) {
                        self.showNotice('info', 'Kart sırası güncellendi.');
                    }
                }
            });
        },
        
        // Update card indices
        updateCardIndices: function() {
            $('#cards-list .card-editor').each(function(index) {
                var $card = $(this);
                $card.attr('data-index', index);
                $card.find('.card-number').text('#' + (index + 1));
                
                // Update form field names
                $card.find('input, textarea, select').each(function() {
                    var $field = $(this);
                    var name = $field.attr('name');
                    if (name && name.indexOf('cards[') === 0) {
                        var newName = name.replace(/cards\[\d+\]/, 'cards[' + index + ']');
                        $field.attr('name', newName);
                    }
                });
            });
        },
        
        // Form validation
        validateForm: function(silent) {
            var isValid = true;
            var errors = [];
            
            $('.card-editor').each(function() {
                var $card = $(this);
                var cardData = this.getCardData($card);
                var cardIndex = $card.data('index');
                
                // Required title
                if (!cardData.title || cardData.title.trim() === '') {
                    isValid = false;
                    errors.push('Kart #' + (cardIndex + 1) + ': Başlık zorunludur.');
                    $card.find('.card-title').addClass('invalid');
                } else {
                    $card.find('.card-title').removeClass('invalid');
                }
                
                // URL validation
                if (cardData.url && !this.isValidUrl(cardData.url)) {
                    isValid = false;
                    errors.push('Kart #' + (cardIndex + 1) + ': Geçersiz URL formatı.');
                    $card.find('.card-url').addClass('invalid');
                } else {
                    $card.find('.card-url').removeClass('invalid');
                }
            }.bind(this));
            
            if (!isValid && !silent) {
                this.showNotice('error', 'Form hatası:\n' + errors.join('\n'));
            }
            
            return isValid;
        },
        
        // Data management
        getAllCardsData: function() {
            var cards = [];
            
            $('.card-editor').each(function() {
                cards.push(this.getCardData($(this)));
            }.bind(this));
            
            return cards;
        },
        
        getCardData: function($card) {
            return {
                title: $card.find('.card-title').val() || '',
                desc: $card.find('.card-desc').val() || '',
                img: $card.find('.card-img').val() || '',
                url: $card.find('.card-url').val() || '',
                order: parseInt($card.find('.card-order').val()) || 0,
                enabled: $card.find('.card-enabled-main').prop('checked'),
                featured: $card.find('.card-featured').prop('checked'),
                new_tab: $card.find('.card-new-tab').prop('checked'),
                analytics: $card.find('.card-analytics').prop('checked'),
                created_at: $card.find('input[name*="[created_at]"]').val() || this.getCurrentDateTime(),
                updated_at: this.getCurrentDateTime()
            };
        },
        
        // Template generation
        getCardEditorTemplate: function(index, data) {
            data = data || {};
            
            var template = `
                <div class="card-editor" data-index="${index}">
                    <!-- Card Header -->
                    <div class="card-editor-header">
                        <div class="card-handle">
                            <span class="card-drag-handle dashicons dashicons-sort" title="Sürükleyerek sıralayın"></span>
                            <span class="card-number">#${index + 1}</span>
                            <span class="card-title-preview">${data.title || 'Yeni Kart'}</span>
                            ${data.featured ? '<span class="featured-badge" title="Öne çıkan kart">⭐</span>' : ''}
                            ${!data.enabled ? '<span class="disabled-badge" title="Pasif kart">🚫</span>' : ''}
                        </div>
                        
                        <div class="card-controls">
                            <label class="card-visibility-toggle" title="Kartı aktif/pasif yap">
                                <input type="checkbox" class="card-enabled" ${data.enabled !== false ? 'checked' : ''}>
                                <span class="visibility-icon dashicons ${data.enabled !== false ? 'dashicons-visibility' : 'dashicons-hidden'}"></span>
                            </label>
                            
                            <button class="card-action duplicate-card" type="button" title="Kartı kopyala">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                            
                            <button class="card-action toggle-card" type="button" title="Kartı aç/kapat">
                                <span class="dashicons dashicons-arrow-up-alt2"></span>
                            </button>
                            
                            <button class="card-action remove-card" type="button" title="Kartı sil">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="card-editor-content">
                        <div class="card-form-grid">
                            <!-- Left Column: Basic Info -->
                            <div class="form-column form-column-main">
                                <!-- Title -->
                                <div class="field-group">
                                    <label class="field-label required">
                                        <span class="dashicons dashicons-text-page"></span>
                                        Kart Başlığı
                                        <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" 
                                           class="card-title widefat" 
                                           name="cards[${index}][title]"
                                           value="${data.title || ''}" 
                                           placeholder="Örn: Hizmetlerimiz" 
                                           maxlength="100" 
                                           required>
                                    <div class="field-meta">
                                        <div class="field-help">Kart üzerinde görünecek ana başlık</div>
                                        <div class="character-count">
                                            <span class="current">${(data.title || '').length}</span>/100
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                <div class="field-group">
                                    <label class="field-label">
                                        <span class="dashicons dashicons-text"></span>
                                        Açıklama
                                    </label>
                                    <textarea class="card-desc widefat" 
                                              name="cards[${index}][desc]"
                                              placeholder="Kart açıklaması..." 
                                              rows="4" 
                                              maxlength="500">${data.desc || ''}</textarea>
                                    <div class="field-meta">
                                        <div class="field-help">Kart başlığının altında görünecek açıklama metni</div>
                                        <div class="character-count">
                                            <span class="current">${(data.desc || '').length}</span>/500
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- URL -->
                                <div class="field-group">
                                    <label class="field-label">
                                        <span class="dashicons dashicons-admin-links"></span>
                                        Bağlantı URL
                                    </label>
                                    <input type="url" 
                                           class="card-url widefat" 
                                           name="cards[${index}][url]"
                                           value="${data.url || ''}" 
                                           placeholder="https://example.com">
                                    
                                    <!-- URL Suggestions -->
                                    <div class="url-suggestions">
                                        <div class="url-suggestion-group">
                                            <span class="suggestion-label">Hızlı Seçim:</span>
                                            <button type="button" class="url-suggestion" data-url="${window.location.origin}">🏠 Ana Sayfa</button>
                                            <button type="button" class="url-suggestion" data-url="${window.location.origin}/hakkimizda">ℹ️ Hakkımızda</button>
                                            <button type="button" class="url-suggestion" data-url="${window.location.origin}/iletisim">📞 İletişim</button>
                                            <button type="button" class="url-suggestion" data-url="${window.location.origin}/hizmetler">🔧 Hizmetler</button>
                                        </div>
                                    </div>
                                    
                                    <div class="field-meta">
                                        <div class="field-help">Karta tıklandığında gidilecek sayfa (boş bırakılabilir)</div>
                                        <div class="url-validation" style="display: none;"></div>
                                    </div>
                                </div>
                                
                                <!-- URL Options -->
                                <div class="field-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" 
                                                   class="card-new-tab" 
                                                   name="cards[${index}][new_tab]"
                                                   value="1"
                                                   ${data.new_tab ? 'checked' : ''}>
                                            <span class="checkbox-text">
                                                <span class="dashicons dashicons-external"></span>
                                                Yeni sekmede aç
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: Image and Settings -->
                            <div class="form-column form-column-settings">
                                <!-- Image URL -->
                                <div class="field-group">
                                    <label class="field-label">
                                        <span class="dashicons dashicons-format-image"></span>
                                        Görsel URL
                                    </label>
                                    <input type="url" 
                                           class="card-img widefat" 
                                           name="cards[${index}][img]"
                                           value="${data.img || ''}" 
                                           placeholder="https://example.com/image.jpg">
                                    <div class="field-help">
                                        Kart görseli URL'i (JPG, PNG, WebP)
                                    </div>
                                </div>
                                
                                <!-- Card Settings -->
                                <div class="field-group">
                                    <label class="field-label">
                                        <span class="dashicons dashicons-admin-settings"></span>
                                        Kart Ayarları
                                    </label>
                                    
                                    <div class="settings-grid">
                                        <div class="setting-item">
                                            <label class="setting-label">Sıra:</label>
                                            <input type="number" 
                                                   class="card-order small-text" 
                                                   name="cards[${index}][order]"
                                                   value="${data.order || index}" 
                                                   min="0" 
                                                   step="1">
                                        </div>
                                        
                                        <div class="setting-item">
                                            <label class="checkbox-label">
                                                <input type="checkbox" 
                                                       class="card-featured" 
                                                       name="cards[${index}][featured]"
                                                       value="1"
                                                       ${data.featured ? 'checked' : ''}>
                                                <span class="checkbox-text">
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    Öne Çıkan
                                                </span>
                                            </label>
                                        </div>
                                        
                                        <div class="setting-item">
                                            <label class="checkbox-label">
                                                <input type="checkbox" 
                                                       class="card-enabled-main" 
                                                       name="cards[${index}][enabled]"
                                                       value="1"
                                                       ${data.enabled !== false ? 'checked' : ''}>
                                                <span class="checkbox-text">
                                                    <span class="dashicons dashicons-visibility"></span>
                                                    Kart Aktif
                                                </span>
                                            </label>
                                        </div>
                                        
                                        <div class="setting-item">
                                            <label class="checkbox-label">
                                                <input type="checkbox" 
                                                       class="card-analytics" 
                                                       name="cards[${index}][analytics]"
                                                       value="1"
                                                       checked>
                                                <span class="checkbox-text">
                                                    <span class="dashicons dashicons-chart-bar"></span>
                                                    Analytics
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" name="cards[${index}][created_at]" value="${data.created_at || this.getCurrentDateTime()}">
                    <input type="hidden" name="cards[${index}][updated_at]" value="${this.getCurrentDateTime()}">
                    <input type="hidden" class="card-index" value="${index}">
                </div>
            `;
            
            return template;
        },
        
        // Setup form validation
        setupValidation: function() {
            var self = this;
            
            // Real-time validation
            $(document).on('blur', '.card-title', function() {
                var $field = $(this);
                var value = $field.val().trim();
                
                if (!value) {
                    $field.addClass('invalid');
                    self.showFieldError($field, 'Başlık zorunludur');
                } else {
                    $field.removeClass('invalid');
                    self.hideFieldError($field);
                }
            });
            
            $(document).on('blur', '.card-url', function() {
                var $field = $(this);
                var value = $field.val().trim();
                
                if (value && !self.isValidUrl(value)) {
                    $field.addClass('invalid');
                    self.validateUrl($field);
                } else {
                    $field.removeClass('invalid');
                    $field.closest('.card-editor').find('.url-validation').hide();
                }
            });
            
            // Character count validation
            $(document).on('input', '.card-title, .card-desc', function() {
                var $field = $(this);
                var maxLength = parseInt($field.attr('maxlength'));
                var currentLength = $field.val().length;
                
                $field.removeClass('warning danger');
                
                if (maxLength && currentLength > maxLength * 0.9) {
                    $field.addClass('warning');
                }
                
                if (maxLength && currentLength >= maxLength) {
                    $field.addClass('danger');
                }
            });
        },
        
        // Utility functions
        markAsChanged: function() {
            this.state.hasUnsavedChanges = true;
            $('.form-save-indicator').addClass('unsaved');
            
            // Update page title
            if (!document.title.includes('*')) {
                document.title = '* ' + document.title;
            }
        },
        
        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch (e) {
                return false;
            }
        },
        
        getCurrentDateTime: function() {
            return new Date().toISOString().slice(0, 19).replace('T', ' ');
        },
        
        checkEmptyState: function() {
            if ($('.card-editor').length === 0) {
                $('.no-cards-message').show();
            } else {
                $('.no-cards-message').hide();
            }
        },
        
        showFieldError: function($field, message) {
            var $error = $field.siblings('.field-error');
            if ($error.length === 0) {
                $field.after('<div class="field-error">' + message + '</div>');
            } else {
                $error.text(message);
            }
        },
        
        hideFieldError: function($field) {
            $field.siblings('.field-error').remove();
        },
        
        showLoading: function() {
            $('body').addClass('saving').append(
                '<div class="save-overlay">' +
                    '<div class="save-spinner"></div>' +
                    '<p>Kaydediliyor...</p>' +
                '</div>'
            );
        },
        
        hideLoading: function() {
            $('body').removeClass('saving');
            $('.save-overlay').remove();
        },
        
        showNotice: function(type, message) {
            var noticeClass = 'notice notice-' + type;
            var noticeHtml = '<div class="' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>';
            
            $('.wrap').prepend(noticeHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.notice').fadeOut();
            }, 5000);
        },
        
        getGroupsPageUrl: function() {
            return window.location.href.split('&tab=')[0];
        },
        
        updateUrl: function() {
            var url = new URL(window.location);
            url.searchParams.set('edit_group', this.config.groupId);
            window.history.replaceState({}, '', url);
        },
        
        // Keyboard shortcuts
        handleKeyboardShortcuts: function(e) {
            // Ctrl/Cmd + S for save
            if ((e.ctrlKey || e.metaKey) && e.which === 83) {
                e.preventDefault();
                $('#save-group').click();
                return false;
            }
            
            // Ctrl/Cmd + Shift + S for save and continue
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.which === 83) {
                e.preventDefault();
                $('#save-and-continue').click();
                return false;
            }
            
            // Ctrl/Cmd + D for duplicate (when focused on card)
            if ((e.ctrlKey || e.metaKey) && e.which === 68) {
                var $focusedCard = $(e.target).closest('.card-editor');
                if ($focusedCard.length > 0) {
                    e.preventDefault();
                    $focusedCard.find('.duplicate-card').click();
                    return false;
                }
            }
            
            // ESC to close any open dialogs
            if (e.which === 27) {
                $('.modal, .dialog').hide();
            }
        },
        
        // Before unload warning
        handleBeforeUnload: function(e) {
            if (this.state.hasUnsavedChanges) {
                var message = 'Kaydedilmemiş değişiklikleriniz var. Sayfadan çıkmak istediğinizden emin misiniz?';
                e.originalEvent.returnValue = message;
                return message;
            }
        },
        
        // Visibility change handler for auto-save
        handleVisibilityChange: function() {
            if (document.hidden && this.state.hasUnsavedChanges && this.config.autoSave) {
                this.performAutoSave();
            }
        },
        
        // Cleanup and destroy
        destroy: function() {
            // Clear timers
            if (this.state.autoSaveTimer) {
                clearTimeout(this.state.autoSaveTimer);
            }
            
            // Remove event listeners
            $(document).off('.editPage');
            $(window).off('.editPage');
            
            // Clean up sortable
            if ($('#cards-list').hasClass('ui-sortable')) {
                $('#cards-list').sortable('destroy');
            }
            
            // Reset state
            this.state = {
                currentCards: 0,
                hasUnsavedChanges: false,
                dragIndex: -1,
                autoSaveTimer: null,
                lastSaveTime: null
            };
        }
    };
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        // Check if we're on the edit page
        if (typeof esistenzeAdmin !== 'undefined' && window.location.search.includes('tab=edit')) {
            // Wait for page-specific config
            if (typeof editPageConfig !== 'undefined') {
                window.EsistenzeEditPage.init($.extend(esistenzeAdmin, editPageConfig));
            }
        }
    });
    
    // Expose to global scope
    window.EQMC_Edit = window.EsistenzeEditPage;
    
})(jQuery);

// CSS Styles for Edit Page
jQuery(document).ready(function($) {
    var editPageCSS = `
        <style id="esistenze-edit-dynamic-css">
            /* Card Editor Styles */
            .card-editor {
                border: 1px solid #ddd;
                border-radius: 8px;
                margin-bottom: 20px;
                background: white;
                transition: all 0.3s ease;
                position: relative;
            }
            
            .card-editor:hover {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .card-editor.collapsed .card-editor-content {
                display: none;
            }
            
            .card-editor.card-disabled {
                opacity: 0.7;
                background: #f9f9f9;
            }
            
            .card-editor-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 20px;
                border-bottom: 1px solid #eee;
                background: #fafafa;
                border-radius: 8px 8px 0 0;
                cursor: move;
            }
            
            .card-handle {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
            }
            
            .card-drag-handle {
                cursor: move;
                color: #666;
                font-size: 16px;
                padding: 2px;
            }
            
            .card-drag-handle:hover {
                color: #0073aa;
            }
            
            .card-number {
                font-weight: 600;
                color: #0073aa;
                background: #e7f3ff;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 12px;
                min-width: 35px;
                text-align: center;
            }
            
            .card-title-preview {
                font-weight: 500;
                color: #1d2327;
                max-width: 300px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .featured-badge, .disabled-badge {
                font-size: 12px;
                margin-left: 5px;
            }
            
            .card-controls {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .card-action {
                background: none;
                border: none;
                padding: 5px;
                cursor: pointer;
                border-radius: 3px;
                transition: background 0.2s;
                color: #646970;
            }
            
            .card-action:hover {
                background: #f0f0f0;
                color: #135e96;
            }
            
            .card-visibility-toggle {
                display: flex;
                align-items: center;
                cursor: pointer;
            }
            
            .card-visibility-toggle input {
                margin: 0 5px 0 0;
            }
            
            .card-editor-content {
                padding: 20px;
            }
            
            .card-form-grid {
                display: grid;
                grid-template-columns: 1fr 300px;
                gap: 30px;
            }
            
            @media (max-width: 1200px) {
                .card-form-grid {
                    grid-template-columns: 1fr;
                    gap: 20px;
                }
            }
            
            .form-column {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            
            .field-group {
                position: relative;
            }
            
            .field-label {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
                margin-bottom: 8px;
                color: #1d2327;
                font-size: 14px;
            }
            
            .field-label .dashicons {
                font-size: 16px;
                color: #646970;
            }
            
            .required-indicator {
                color: #d63638;
                font-weight: 700;
            }
            
            .field-meta {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-top: 5px;
                gap: 10px;
            }
            
            .field-help {
                font-size: 13px;
                color: #646970;
                font-style: italic;
                flex: 1;
            }
            
            .character-count {
                font-size: 12px;
                color: #646970;
                white-space: nowrap;
            }
            
            .character-count.warning {
                color: #dba617;
                font-weight: 600;
            }
            
            .character-count.danger {
                color: #d63638;
                font-weight: 700;
            }
            
            .widefat {
                width: 100%;
                box-sizing: border-box;
                padding: 8px 12px;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .widefat:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
                outline: none;
            }
            
            .card-title.invalid,
            .card-url.invalid {
                border-color: #d63638;
                box-shadow: 0 0 0 1px #d63638;
            }
            
            .card-title.valid,
            .card-url.valid {
                border-color: #00a32a;
                box-shadow: 0 0 0 1px #00a32a;
            }
            
            .card-title.warning,
            .card-desc.warning {
                border-color: #dba617;
            }
            
            .card-title.danger,
            .card-desc.danger {
                border-color: #d63638;
                background-color: #fef7f0;
            }
            
            .field-error {
                color: #d63638;
                font-size: 12px;
                margin-top: 4px;
                display: block;
            }
            
            .url-suggestions {
                margin-top: 8px;
            }
            
            .url-suggestion-group {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                align-items: center;
            }
            
            .suggestion-label {
                font-size: 12px;
                color: #646970;
                margin-right: 5px;
            }
            
            .url-suggestion {
                background: #f6f7f7;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 4px 8px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .url-suggestion:hover {
                background: #e0e7ed;
                border-color: #8c8f94;
            }
            
            .url-validation {
                display: flex;
                align-items: center;
                gap: 5px;
                color: #d63638;
                font-size: 12px;
                margin-top: 4px;
            }
            
            .validation-icon {
                font-size: 14px;
            }
            
            .checkbox-group {
                margin-top: 10px;
            }
            
            .checkbox-label {
                display: flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
                font-size: 14px;
            }
            
            .checkbox-text {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .settings-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-top: 10px;
            }
            
            .setting-item {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .setting-label {
                font-size: 12px;
                color: #646970;
                font-weight: 600;
            }
            
            .small-text {
                width: 60px;
            }
            
            /* Sortable placeholder */
            .card-placeholder {
                height: 200px;
                background: #f0f6fc;
                border: 2px dashed #8c8f94;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #646970;
                font-size: 14px;
                margin-bottom: 20px;
            }
            
            .card-placeholder::before {
                content: 'Kartı buraya bırakın';
            }
            
            /* Loading states */
            .save-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 100000;
            }
            
            .save-spinner {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #0073aa;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
                margin-bottom: 20px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .save-overlay p {
                font-size: 16px;
                color: #1d2327;
                margin: 0;
            }
            
            /* Auto-save indicator */
            .auto-save-indicator {
                position: fixed;
                top: 32px;
                right: 20px;
                background: #00a32a;
                color: white;
                padding: 8px 15px;
                border-radius: 4px;
                font-size: 13px;
                z-index: 10000;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                display: none;
            }
            
            /* Empty state */
            .no-cards-message {
                text-align: center;
                padding: 60px 20px;
                color: #646970;
                background: #f9f9f9;
                border: 2px dashed #c3c4c7;
                border-radius: 8px;
                margin: 20px 0;
            }
            
            .no-cards-message h4 {
                color: #1d2327;
                margin-bottom: 10px;
            }
            
            /* Notice animations */
            .notice {
                animation: slideIn 0.3s ease;
                margin: 5px 0 15px;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .card-editor-header {
                    padding: 10px 15px;
                }
                
                .card-handle {
                    gap: 8px;
                }
                
                .card-title-preview {
                    max-width: 150px;
                }
                
                .card-editor-content {
                    padding: 15px;
                }
                
                .url-suggestion-group {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .settings-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    `;
    
    $('head').append(editPageCSS);
});