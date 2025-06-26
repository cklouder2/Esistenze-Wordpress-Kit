/*
 * Quick Menu Cards - Admin Panel JavaScript
 * Temel admin işlevleri ve grup yönetimi
 */

(function($) {
    'use strict';
    
    // Global namespace
    window.EsistenzeQuickMenuAdmin = {
        // Configuration
        config: {
            ajaxUrl: '',
            nonce: '',
            postUrl: '',
            strings: {},
            settings: {},
            debug: false
        },
        
        // State management
        state: {
            currentPage: '',
            selectedGroups: [],
            isLoading: false,
            modals: {
                preview: null,
                import: null,
                export: null,
                delete: null
            }
        },
        
        // Initialize
        init: function(config) {
            this.config = $.extend(this.config, config);
            this.bindEvents();
            this.initPage();
            this.setupModals();
            this.initTooltips();
            
            if (this.config.debug) {
                console.log('Quick Menu Cards Admin initialized', this.config);
            }
        },
        
        // Bind all events
        bindEvents: function() {
            var self = this;
            
            // Page navigation
            $(document).on('click', '.nav-tab', this.handleTabSwitch.bind(this));
            
            // Group management
            $(document).on('click', '.delete-group', this.confirmDeleteGroup.bind(this));
            $(document).on('click', '.duplicate-group', this.duplicateGroup.bind(this));
            $(document).on('click', '.export-group', this.exportSingleGroup.bind(this));
            
            // Shortcode operations
            $(document).on('click', '.copy-shortcode', this.copyShortcode.bind(this));
            
            // Bulk operations
            $(document).on('click', '#select-all-groups', this.toggleSelectAll.bind(this));
            $(document).on('change', '.group-select', this.updateBulkActions.bind(this));
            $(document).on('click', '#bulk-delete', this.bulkDeleteGroups.bind(this));
            $(document).on('click', '#bulk-export', this.bulkExportGroups.bind(this));
            
            // Import/Export
            $(document).on('click', '#export-all-groups', this.exportAllGroups.bind(this));
            $(document).on('click', '#import-groups', this.showImportModal.bind(this));
            $(document).on('change', '#import-file', this.handleFileImport.bind(this));
            $(document).on('click', '#import-from-text', this.importFromText.bind(this));
            
            // Preview
            $(document).on('click', '.preview-group', this.previewGroup.bind(this));
            $(document).on('click', '.preview-tab', this.switchPreviewType.bind(this));
            
            // Modal controls
            $(document).on('click', '.modal-close', this.closeModal.bind(this));
            $(document).on('click', '.modal-overlay', this.closeModalOnOverlay.bind(this));
            
            // Settings
            $(document).on('click', '#reset-defaults', this.resetSettingsToDefaults.bind(this));
            
            // Analytics
            $(document).on('click', '#refresh-analytics', this.refreshAnalytics.bind(this));
            $(document).on('click', '#clear-analytics', this.clearAnalytics.bind(this));
            $(document).on('click', '#export-analytics', this.exportAnalytics.bind(this));
            
            // Tools
            $(document).on('click', '#clear-cache', this.clearCache.bind(this));
            $(document).on('click', '#optimize-db', this.optimizeDatabase.bind(this));
            $(document).on('click', '#check-conflicts', this.checkConflicts.bind(this));
            $(document).on('click', '#performance-test', this.performanceTest.bind(this));
            
            // Keyboard shortcuts
            $(document).on('keydown', this.handleKeyboardShortcuts.bind(this));
            
            // Auto-save warning
            $(window).on('beforeunload', this.handleBeforeUnload.bind(this));
            
            // AJAX error handling
            $(document).ajaxError(this.handleAjaxError.bind(this));
        },
        
        // Initialize page-specific functionality
        initPage: function() {
            this.state.currentPage = this.getCurrentPage();
            
            switch (this.state.currentPage) {
                case 'groups':
                    this.initGroupsPage();
                    break;
                case 'settings':
                    this.initSettingsPage();
                    break;
                case 'analytics':
                    this.initAnalyticsPage();
                    break;
                case 'tools':
                    this.initToolsPage();
                    break;
            }
        },
        
        // Get current page from URL
        getCurrentPage: function() {
            var urlParams = new URLSearchParams(window.location.search);
            var page = urlParams.get('page');
            
            if (page === 'esistenze-quick-menu-settings') return 'settings';
            if (page === 'esistenze-quick-menu-analytics') return 'analytics';
            if (page === 'esistenze-quick-menu-tools') return 'tools';
            
            return 'groups';
        },
        
        // Initialize groups page
        initGroupsPage: function() {
            this.updateGroupStats();
            this.initGroupSorting();
            this.loadRecentActivity();
        },
        
        // Initialize settings page
        initSettingsPage: function() {
            this.initColorPickers();
            this.initTabSwitching();
            this.bindSettingsValidation();
        },
        
        // Initialize analytics page
        initAnalyticsPage: function() {
            this.loadAnalyticsData();
            this.initAnalyticsCharts();
            this.setupAnalyticsFilters();
        },
        
        // Initialize tools page
        initToolsPage: function() {
            this.checkSystemStatus();
            this.loadDebugInfo();
        },
        
        // Tab switching
        handleTabSwitch: function(e) {
            e.preventDefault();
            
            var $tab = $(e.currentTarget);
            var targetTab = $tab.data('tab');
            
            if (!targetTab) return;
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');
            
            // Update content
            $('.tab-content').removeClass('active');
            $('#' + targetTab).addClass('active');
            
            // Update URL hash
            window.location.hash = targetTab;
        },
        
        // Group operations
        confirmDeleteGroup: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $button = $(e.currentTarget);
            var groupId = $button.data('group-id');
            var $groupCard = $button.closest('.group-card');
            
            if (!this.confirm(this.config.strings.delete_confirm)) {
                return;
            }
            
            this.deleteGroup(groupId, $groupCard);
        },
        
        deleteGroup: function(groupId, $groupCard) {
            var self = this;
            
            this.showLoading($groupCard);
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_delete_card_group',
                    nonce: this.config.nonce,
                    group_id: groupId
                },
                success: function(response) {
                    self.hideLoading($groupCard);
                    
                    if (response.success) {
                        self.showNotice('success', self.config.strings.delete_success || 'Grup başarıyla silindi!');
                        
                        $groupCard.fadeOut(300, function() {
                            $(this).remove();
                            self.updateGroupStats();
                            self.checkEmptyState();
                        });
                    } else {
                        self.showNotice('error', response.data || self.config.strings.delete_error);
                    }
                },
                error: function(xhr, status, error) {
                    self.hideLoading($groupCard);
                    self.showNotice('error', 'Bağlantı hatası: ' + error);
                }
            });
        },
        
        duplicateGroup: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var self = this;
            var $button = $(e.currentTarget);
            var groupId = $button.data('group-id');
            var $groupCard = $button.closest('.group-card');
            
            this.showLoading($groupCard);
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_duplicate_card_group',
                    nonce: this.config.nonce,
                    group_id: groupId
                },
                success: function(response) {
                    self.hideLoading($groupCard);
                    
                    if (response.success) {
                        self.showNotice('success', 'Grup başarıyla kopyalandı!');
                        // Reload page to show new group
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        self.showNotice('error', response.data || 'Kopyalama işlemi başarısız!');
                    }
                },
                error: function(xhr, status, error) {
                    self.hideLoading($groupCard);
                    self.showNotice('error', 'Bağlantı hatası: ' + error);
                }
            });
        },
        
        // Shortcode operations
        copyShortcode: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var shortcode = $button.data('shortcode');
            
            if (!shortcode) return;
            
            this.copyToClipboard(shortcode, $button);
        },
        
        copyToClipboard: function(text, $button) {
            var self = this;
            
            // Modern clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    self.showCopySuccess($button);
                }).catch(function() {
                    self.copyToClipboardFallback(text, $button);
                });
            } else {
                this.copyToClipboardFallback(text, $button);
            }
        },
        
        copyToClipboardFallback: function(text, $button) {
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                this.showCopySuccess($button);
            } catch (err) {
                this.showNotice('info', 'Shortcode: ' + text);
            }
            
            document.body.removeChild(textArea);
        },
        
        showCopySuccess: function($button) {
            var originalContent = $button.html();
            var originalClass = $button.attr('class');
            
            $button.html('<span class="dashicons dashicons-yes"></span>');
            $button.addClass('copy-success');
            
            setTimeout(function() {
                $button.html(originalContent);
                $button.attr('class', originalClass);
            }, 1500);
        },
        
        // Bulk operations
        toggleSelectAll: function(e) {
            var isChecked = $(e.currentTarget).prop('checked');
            $('.group-select').prop('checked', isChecked);
            this.updateBulkActions();
        },
        
        updateBulkActions: function() {
            var selectedCount = $('.group-select:checked').length;
            var $bulkActions = $('.bulk-actions');
            
            if (selectedCount > 0) {
                $bulkActions.show();
                $('.selected-count').text(selectedCount);
            } else {
                $bulkActions.hide();
            }
        },
        
        bulkDeleteGroups: function(e) {
            e.preventDefault();
            
            var selectedIds = this.getSelectedGroupIds();
            
            if (selectedIds.length === 0) {
                this.showNotice('warning', 'Lütfen silmek istediğiniz grupları seçin.');
                return;
            }
            
            if (!this.confirm('Seçili ' + selectedIds.length + ' grubu silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            this.bulkOperation('delete', selectedIds);
        },
        
        bulkExportGroups: function(e) {
            e.preventDefault();
            
            var selectedIds = this.getSelectedGroupIds();
            
            if (selectedIds.length === 0) {
                this.showNotice('warning', 'Lütfen dışa aktarmak istediğiniz grupları seçin.');
                return;
            }
            
            this.bulkOperation('export', selectedIds);
        },
        
        getSelectedGroupIds: function() {
            return $('.group-select:checked').map(function() {
                return $(this).val();
            }).get();
        },
        
        bulkOperation: function(operation, groupIds) {
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_bulk_group_operation',
                    nonce: this.config.nonce,
                    operation: operation,
                    group_ids: groupIds
                },
                success: function(response) {
                    if (response.success) {
                        if (operation === 'delete') {
                            self.showNotice('success', response.data.message);
                            // Remove deleted groups from DOM
                            groupIds.forEach(function(groupId) {
                                $('.group-card[data-group-id="' + groupId + '"]').fadeOut(300, function() {
                                    $(this).remove();
                                });
                            });
                            self.updateGroupStats();
                        } else if (operation === 'export') {
                            self.downloadJSON(response.data.data, response.data.filename);
                        }
                    } else {
                        self.showNotice('error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', 'İşlem başarısız: ' + error);
                }
            });
        },
        
        // Import/Export operations
        exportAllGroups: function(e) {
            e.preventDefault();
            
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_export_groups',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.downloadJSON(response.data.data, response.data.filename);
                        self.showNotice('success', 'Veriler başarıyla dışa aktarıldı!');
                    } else {
                        self.showNotice('error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', 'Dışa aktarma başarısız: ' + error);
                }
            });
        },
        
        downloadJSON: function(data, filename) {
            var blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },
        
        showImportModal: function(e) {
            e.preventDefault();
            this.openModal('import');
        },
        
        handleFileImport: function(e) {
            var file = e.target.files[0];
            
            if (!file) return;
            
            if (file.type !== 'application/json') {
                this.showNotice('error', 'Lütfen geçerli bir JSON dosyası seçin.');
                return;
            }
            
            var self = this;
            var reader = new FileReader();
            
            reader.onload = function(e) {
                try {
                    var data = JSON.parse(e.target.result);
                    self.importData(data);
                } catch (error) {
                    self.showNotice('error', 'Geçersiz JSON dosyası.');
                }
            };
            
            reader.readAsText(file);
        },
        
        importFromText: function(e) {
            e.preventDefault();
            
            var jsonText = $('#import-text').val().trim();
            
            if (!jsonText) {
                this.showNotice('warning', 'Lütfen JSON verilerini girin.');
                return;
            }
            
            try {
                var data = JSON.parse(jsonText);
                this.importData(data);
            } catch (error) {
                this.showNotice('error', 'Geçersiz JSON formatı.');
            }
        },
        
        importData: function(data) {
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_import_groups',
                    nonce: this.config.nonce,
                    import_data: JSON.stringify(data)
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.data.message);
                        self.closeModal('import');
                        // Reload page to show imported groups
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        self.showNotice('error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', 'İçe aktarma başarısız: ' + error);
                }
            });
        },
        
        // Preview functionality
        previewGroup: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var groupId = $button.data('group-id');
            
            this.openModal('preview');
            this.loadGroupPreview(groupId, 'grid');
            
            // Store current group ID for tab switching
            this.state.currentPreviewGroup = groupId;
        },
        
        switchPreviewType: function(e) {
            e.preventDefault();
            
            var $tab = $(e.currentTarget);
            var previewType = $tab.data('type');
            
            $('.preview-tab').removeClass('active');
            $tab.addClass('active');
            
            if (this.state.currentPreviewGroup) {
                this.loadGroupPreview(this.state.currentPreviewGroup, previewType);
            }
        },
        
        loadGroupPreview: function(groupId, type) {
            var self = this;
            var $previewContent = $('#preview-content');
            
            $previewContent.html('<div class="loading-spinner">Yükleniyor...</div>');
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_preview_group',
                    nonce: this.config.nonce,
                    group_id: groupId,
                    preview_type: type
                },
                success: function(response) {
                    if (response.success) {
                        $previewContent.html(response.data.html);
                    } else {
                        $previewContent.html('<p class="error">Önizleme yüklenemedi.</p>');
                    }
                },
                error: function() {
                    $previewContent.html('<p class="error">Bağlantı hatası.</p>');
                }
            });
        },
        
        // Modal management
        setupModals: function() {
            this.createModal('preview', 'Grup Önizlemesi', this.getPreviewModalContent());
            this.createModal('import', 'Grup İçe Aktar', this.getImportModalContent());
            this.createModal('export', 'Grup Dışa Aktar', this.getExportModalContent());
        },
        
        createModal: function(id, title, content) {
            var modalHtml = 
                '<div id="modal-' + id + '" class="esistenze-modal" style="display: none;">' +
                    '<div class="modal-overlay"></div>' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<h3>' + title + '</h3>' +
                            '<button type="button" class="modal-close" data-modal="' + id + '">&times;</button>' +
                        '</div>' +
                        '<div class="modal-body">' + content + '</div>' +
                    '</div>' +
                '</div>';
            
            $('body').append(modalHtml);
            this.state.modals[id] = $('#modal-' + id);
        },
        
        getPreviewModalContent: function() {
            return '<div class="preview-tabs">' +
                       '<button class="preview-tab active" data-type="grid">Izgara Görünüm</button>' +
                       '<button class="preview-tab" data-type="banner">Banner Görünüm</button>' +
                   '</div>' +
                   '<div id="preview-content" class="preview-content"></div>';
        },
        
        getImportModalContent: function() {
            return '<div class="import-methods">' +
                       '<div class="import-method">' +
                           '<h4>JSON Dosyası Yükle</h4>' +
                           '<input type="file" id="import-file" accept=".json">' +
                           '<p class="description">Daha önce dışa aktarılmış JSON dosyasını seçin.</p>' +
                       '</div>' +
                       '<div class="import-method">' +
                           '<h4>JSON Metni Yapıştır</h4>' +
                           '<textarea id="import-text" rows="10" placeholder="JSON verilerini buraya yapıştırın..."></textarea>' +
                           '<button type="button" id="import-from-text" class="button button-primary">İçe Aktar</button>' +
                       '</div>' +
                   '</div>';
        },
        
        getExportModalContent: function() {
            return '<div class="export-options">' +
                       '<p>Hangi verileri dışa aktarmak istiyorsunuz?</p>' +
                       '<label><input type="checkbox" id="export-groups" checked> Kart Grupları</label>' +
                       '<label><input type="checkbox" id="export-settings"> Ayarlar</label>' +
                       '<label><input type="checkbox" id="export-analytics"> Analytics Verileri</label>' +
                       '<button type="button" id="start-export" class="button button-primary">Dışa Aktar</button>' +
                   '</div>';
        },
        
        openModal: function(modalId) {
            if (this.state.modals[modalId]) {
                this.state.modals[modalId].show();
                $('body').addClass('modal-open');
            }
        },
        
        closeModal: function(modalId) {
            if (typeof modalId === 'object') {
                // Called from event handler
                modalId = $(modalId.currentTarget).data('modal');
            }
            
            if (this.state.modals[modalId]) {
                this.state.modals[modalId].hide();
                $('body').removeClass('modal-open');
            }
        },
        
        closeModalOnOverlay: function(e) {
            if (e.target === e.currentTarget) {
                var modalId = $(e.currentTarget).closest('.esistenze-modal').attr('id').replace('modal-', '');
                this.closeModal(modalId);
            }
        },
        
        // Utility functions
        showLoading: function($element) {
            $element = $element || $('body');
            $element.addClass('loading').append('<div class="loading-overlay"><div class="spinner"></div></div>');
        },
        
        hideLoading: function($element) {
            $element = $element || $('body');
            $element.removeClass('loading').find('.loading-overlay').remove();
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
        
        confirm: function(message) {
            return window.confirm(message);
        },
        
        updateGroupStats: function() {
            var totalGroups = $('.group-card').length;
            var totalCards = 0;
            
            $('.group-card').each(function() {
                var cardCount = parseInt($(this).find('.card-count').text()) || 0;
                totalCards += cardCount;
            });
            
            $('.stat-groups .stat-number').text(totalGroups);
            $('.stat-cards .stat-number').text(totalCards);
        },
        
        checkEmptyState: function() {
            if ($('.group-card').length === 0) {
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }
        },
        
        initTooltips: function() {
            $('[title]').each(function() {
                var $element = $(this);
                var title = $element.attr('title');
                
                $element.removeAttr('title').hover(
                    function() {
                        $('<div class="tooltip">' + title + '</div>').appendTo('body').fadeIn();
                    },
                    function() {
                        $('.tooltip').remove();
                    }
                );
            });
        },
        
        // Keyboard shortcuts
        handleKeyboardShortcuts: function(e) {
            // Ctrl/Cmd + S for save
            if ((e.ctrlKey || e.metaKey) && e.which === 83) {
                e.preventDefault();
                var $saveButton = $('.button-primary:contains("Kaydet")');
                if ($saveButton.length) {
                    $saveButton.click();
                }
            }
            
            // ESC to close modals
            if (e.which === 27) {
                $('.esistenze-modal:visible').each(function() {
                    var modalId = $(this).attr('id').replace('modal-', '');
                    this.closeModal(modalId);
                }.bind(this));
            }
        },
        
        // Before unload warning
        handleBeforeUnload: function(e) {
            if (this.hasUnsavedChanges()) {
                var message = this.config.strings.unsaved_changes || 'Kaydedilmemiş değişiklikleriniz var.';
                e.originalEvent.returnValue = message;
                return message;
            }
        },
        
        hasUnsavedChanges: function() {
            return $('.form-dirty').length > 0;
        },
        
        // AJAX error handling
        handleAjaxError: function(event, xhr, settings, error) {
            if (xhr.status === 403) {
                this.showNotice('error', 'Yetki hatası. Lütfen sayfayı yenileyin.');
            } else if (xhr.status === 500) {
                this.showNotice('error', 'Sunucu hatası oluştu.');
            } else if (xhr.status === 0) {
                // Ignore - probably page unload
                return;
            } else {
                this.showNotice('error', 'Beklenmeyen hata: ' + error);
            }
            
            if (this.config.debug) {
                console.error('AJAX Error:', xhr, settings, error);
            }
        },
        
        // Settings specific functions
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.color-picker').wpColorPicker();
            }
        },
        
        initTabSwitching: function() {
            // Hash-based tab switching for settings page
            if (window.location.hash) {
                var hash = window.location.hash.substring(1);
                $('[data-tab="' + hash + '"]').trigger('click');
            }
        },
        
        bindSettingsValidation: function() {
            $('form').on('change', 'input, select, textarea', function() {
                $(this).closest('form').addClass('form-dirty');
            });
        },
        
        resetSettingsToDefaults: function(e) {
            e.preventDefault();
            
            if (!this.confirm('Tüm ayarları varsayılan değerlerine sıfırlamak istediğinizden emin misiniz?')) {
                return;
            }
            
            // Reset form fields to defaults
            var defaults = this.config.settings.defaults || {};
            
            Object.keys(defaults).forEach(function(key) {
                var $field = $('[name*="[' + key + ']"]');
                var value = defaults[key];
                
                if ($field.attr('type') === 'checkbox') {
                    $field.prop('checked', !!value);
                } else {
                    $field.val(value);
                }
            });
            
            this.showNotice('info', 'Ayarlar varsayılan değerlere sıfırlandı.');
        },
        
        // Analytics functions
        loadAnalyticsData: function() {
            // This would load analytics data via AJAX
            console.log('Loading analytics data...');
        },
        
        initAnalyticsCharts: function() {
            // Chart initialization would go here
            console.log('Initializing analytics charts...');
        },
        
        setupAnalyticsFilters: function() {
            var self = this;
            
            $('.analytics-filter').on('change', function() {
                self.filterAnalyticsData();
            });
        },
        
        filterAnalyticsData: function() {
            var dateRange = $('#date-filter').val();
            var groupFilter = $('#group-filter').val();
            
            // Reload analytics with filters
            this.loadAnalyticsData({
                date_range: dateRange,
                group_filter: groupFilter
            });
        },
        
        refreshAnalytics: function(e) {
            e.preventDefault();
            
            var self = this;
            var $button = $(e.currentTarget);
            
            $button.prop('disabled', true).find('.dashicons').addClass('spin');
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_refresh_analytics',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', 'Analytics verileri yenilendi!');
                        window.location.reload();
                    } else {
                        self.showNotice('error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', 'Yenileme başarısız: ' + error);
                },
                complete: function() {
                    $button.prop('disabled', false).find('.dashicons').removeClass('spin');
                }
            });
        },
        
        clearAnalytics: function(e) {
            e.preventDefault();
            
            if (!this.confirm('Tüm analytics verilerini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }
            
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_clear_analytics',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', 'Analytics verileri temizlendi!');
                        window.location.reload();
                    } else {
                        self.showNotice('error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', 'Temizleme başarısız: ' + error);
                }
            });
        },
        
        exportAnalytics: function(e) {
            e.preventDefault();
            
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_export_analytics',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.downloadJSON(response.data.data, response.data.filename);
                        self.showNotice('success', 'Analytics verileri dışa aktarıldı!');
                    } else {
                        self.showNotice('error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', 'Dışa aktarma başarısız: ' + error);
                }
            });
        },
        
        // Tools functions
        checkSystemStatus: function() {
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_check_system_status',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#system-status').html(response.data.html);
                    }
                },
                error: function() {
                    $('#system-status').html('<p class="error">Sistem durumu kontrol edilemedi.</p>');
                }
            });
        },
        
        loadDebugInfo: function() {
            if (!this.config.debug) return;
            
            var debugInfo = {
                'WordPress Version': window.wp ? window.wp.version : 'Bilinmiyor',
                'jQuery Version': $.fn.jquery,
                'User Agent': navigator.userAgent,
                'Screen Resolution': screen.width + 'x' + screen.height,
                'Current Time': new Date().toLocaleString('tr-TR')
            };
            
            var debugHtml = '<h4>Debug Bilgileri:</h4><ul>';
            Object.keys(debugInfo).forEach(function(key) {
                debugHtml += '<li><strong>' + key + ':</strong> ' + debugInfo[key] + '</li>';
            });
            debugHtml += '</ul>';
            
            $('#debug-info').html(debugHtml);
        },
        
        clearCache: function(e) {
            e.preventDefault();
            
            var self = this;
            var cacheType = $(e.currentTarget).data('cache-type') || 'plugin';
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_clear_cache',
                    nonce: this.config.nonce,
                    cache_type: cacheType
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.data);
                    } else {
                        self.showNotice('error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', 'Cache temizleme başarısız: ' + error);
                }
            });
        },
        
        optimizeDatabase: function(e) {
            e.preventDefault();
            
            if (!this.confirm('Veritabanını optimize etmek istediğinizden emin misiniz?')) {
                return;
            }
            
            var self = this;
            var $button = $(e.currentTarget);
            
            $button.prop('disabled', true).text('Optimize ediliyor...');
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_db_action',
                    nonce: this.config.nonce,
                    db_action: 'optimize'
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.data);
                    } else {
                        self.showNotice('error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', 'Optimizasyon başarısız: ' + error);
                },
                complete: function() {
                    $button.prop('disabled', false).text('Veritabanını Optimize Et');
                }
            });
        },
        
        checkConflicts: function(e) {
            e.preventDefault();
            
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_test_conflicts',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#conflict-results').html(response.data.html);
                    } else {
                        $('#conflict-results').html('<p class="error">Çakışma kontrolü başarısız.</p>');
                    }
                },
                error: function() {
                    $('#conflict-results').html('<p class="error">Bağlantı hatası.</p>');
                }
            });
        },
        
        performanceTest: function(e) {
            e.preventDefault();
            
            var self = this;
            var $button = $(e.currentTarget);
            
            $button.prop('disabled', true).text('Test ediliyor...');
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_performance_test',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#performance-results').html(response.data.html);
                    } else {
                        $('#performance-results').html('<p class="error">Performans testi başarısız.</p>');
                    }
                },
                error: function() {
                    $('#performance-results').html('<p class="error">Bağlantı hatası.</p>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Performans Testi Çalıştır');
                }
            });
        },
        
        // Group sorting
        initGroupSorting: function() {
            if (!$.fn.sortable) return;
            
            var self = this;
            
            $('.groups-grid').sortable({
                handle: '.group-drag-handle',
                placeholder: 'group-placeholder',
                tolerance: 'pointer',
                update: function(event, ui) {
                    self.saveGroupOrder();
                }
            });
        },
        
        saveGroupOrder: function() {
            var groupOrder = [];
            
            $('.group-card').each(function(index) {
                groupOrder.push({
                    id: $(this).data('group-id'),
                    order: index
                });
            });
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_save_group_order',
                    nonce: this.config.nonce,
                    group_order: groupOrder
                },
                success: function(response) {
                    if (!response.success) {
                        console.error('Group order save failed:', response.data);
                    }
                }
            });
        },
        
        loadRecentActivity: function() {
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'esistenze_get_recent_activity',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#recent-activity').html(response.data.html);
                    }
                }
            });
        },
        
        // Form helpers
        serializeFormData: function($form) {
            var formData = {};
            
            $form.find('input, select, textarea').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                var value = $field.val();
                
                if (!name) return;
                
                if ($field.attr('type') === 'checkbox') {
                    value = $field.prop('checked');
                } else if ($field.attr('type') === 'radio') {
                    if (!$field.prop('checked')) return;
                }
                
                formData[name] = value;
            });
            
            return formData;
        },
        
        populateForm: function($form, data) {
            Object.keys(data).forEach(function(key) {
                var $field = $form.find('[name="' + key + '"]');
                var value = data[key];
                
                if ($field.length === 0) return;
                
                if ($field.attr('type') === 'checkbox') {
                    $field.prop('checked', !!value);
                } else if ($field.attr('type') === 'radio') {
                    $field.filter('[value="' + value + '"]').prop('checked', true);
                } else {
                    $field.val(value);
                }
            });
        },
        
        // URL helpers
        updateURL: function(params) {
            var url = new URL(window.location);
            
            Object.keys(params).forEach(function(key) {
                if (params[key] === null || params[key] === '') {
                    url.searchParams.delete(key);
                } else {
                    url.searchParams.set(key, params[key]);
                }
            });
            
            window.history.replaceState({}, '', url);
        },
        
        getURLParam: function(param) {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        },
        
        // Feature detection
        supportsLocalStorage: function() {
            try {
                var test = 'test';
                localStorage.setItem(test, test);
                localStorage.removeItem(test);
                return true;
            } catch(e) {
                return false;
            }
        },
        
        supportsFileAPI: function() {
            return window.File && window.FileReader && window.FileList && window.Blob;
        },
        
        // Browser compatibility
        isModernBrowser: function() {
            return 'fetch' in window && 'Promise' in window && 'Map' in window;
        },
        
        // Cleanup
        destroy: function() {
            $(document).off('.esistenze');
            $(window).off('.esistenze');
            
            Object.keys(this.state.modals).forEach(function(modalId) {
                if (this.state.modals[modalId]) {
                    this.state.modals[modalId].remove();
                }
            }.bind(this));
            
            $('.tooltip').remove();
        }
    };
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        // Check if we're on the right page
        if (typeof esistenzeAdmin !== 'undefined') {
            window.EsistenzeQuickMenuAdmin.init(esistenzeAdmin);
        }
    });
    
    // Expose to global scope for external access
    window.EQMC = window.EsistenzeQuickMenuAdmin;
    
})(jQuery);

// CSS animations and styles
jQuery(document).ready(function($) {
    // Add dynamic styles
    var dynamicCSS = `
        <style id="esistenze-admin-dynamic-css">
            .loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            }
            
            .spinner {
                border: 3px solid #f3f3f3;
                border-top: 3px solid #0073aa;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .copy-success {
                background-color: #00a32a !important;
                border-color: #00a32a !important;
            }
            
            .tooltip {
                position: absolute;
                background: #333;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                white-space: nowrap;
                z-index: 10000;
                pointer-events: none;
            }
            
            .esistenze-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 100000;
                overflow-y: auto;
            }
            
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
            }
            
            .modal-content {
                position: relative;
                background: white;
                margin: 50px auto;
                max-width: 800px;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            }
            
            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px;
                border-bottom: 1px solid #ddd;
            }
            
            .modal-header h3 {
                margin: 0;
            }
            
            .modal-close {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #666;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .modal-close:hover {
                color: #333;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .loading-spinner {
                text-align: center;
                padding: 40px;
                color: #666;
            }
            
            .loading-spinner::before {
                content: '';
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #0073aa;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-right: 10px;
                vertical-align: middle;
            }
            
            .group-placeholder {
                height: 200px;
                background: #f0f0f0;
                border: 2px dashed #ccc;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #999;
            }
            
            .group-placeholder::before {
                content: 'Grubu buraya bırakın';
            }
            
            body.modal-open {
                overflow: hidden;
            }
            
            .preview-tabs {
                display: flex;
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
            }
            
            .preview-tab {
                background: none;
                border: none;
                padding: 10px 20px;
                cursor: pointer;
                border-bottom: 2px solid transparent;
                transition: all 0.2s;
            }
            
            .preview-tab:hover {
                background: #f0f0f0;
            }
            
            .preview-tab.active {
                border-bottom-color: #0073aa;
                color: #0073aa;
            }
            
            .preview-content {
                min-height: 200px;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 20px;
            }
            
            .import-methods {
                display: grid;
                gap: 30px;
            }
            
            .import-method h4 {
                margin: 0 0 15px;
                color: #1d2327;
            }
            
            .import-method input[type="file"] {
                margin-bottom: 10px;
                padding: 5px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            
            .import-method textarea {
                width: 100%;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 10px;
                font-family: 'Monaco', 'Menlo', monospace;
                font-size: 12px;
                resize: vertical;
            }
            
            .description {
                font-size: 13px;
                color: #646970;
                margin: 5px 0 0;
                font-style: italic;
            }
            
            .notice.is-dismissible {
                position: relative;
                margin: 5px 0 15px;
                animation: slideDown 0.3s ease;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .dashicons.spin {
                animation: spin 1s linear infinite;
            }
        </style>
    `;
    
    $('head').append(dynamicCSS);
});