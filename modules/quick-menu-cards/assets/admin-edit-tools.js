/*
 * Quick Menu Cards - Admin Tools JavaScript
 * Araçlar sayfası için JavaScript fonksiyonları
 */

(function($) {
    'use strict';
    
    var EsistenzeTools = {
        
        init: function() {
            this.bindEvents();
            this.initTabs();
        },
        
        bindEvents: function() {
            // Cache temizleme
            $(document).on('click', '#clear-cache', this.clearCache.bind(this));
            
            // Database temizleme
            $(document).on('click', '#cleanup-database', this.cleanupDatabase.bind(this));
            
            // Backup oluşturma
            $(document).on('click', '#create-backup', this.createBackup.bind(this));
            
            // Backup restore
            $(document).on('click', '#restore-backup', this.showRestoreDialog.bind(this));
            
            // Reset ayarları
            $(document).on('click', '#reset-settings', this.resetSettings.bind(this));
            
            // Debug bilgisi export
            $(document).on('click', '#export-debug', this.exportDebugInfo.bind(this));
            
            // Regenerate thumbnails
            $(document).on('click', '#regenerate-thumbnails', this.regenerateThumbnails.bind(this));
            
            // Migrate data
            $(document).on('click', '#migrate-data', this.migrateData.bind(this));
            
            // Performance test
            $(document).on('click', '#performance-test', this.runPerformanceTest.bind(this));
        },
        
        initTabs: function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                $('.tab-content').hide();
                $(target).show();
            });
        },
        
        clearCache: function(e) {
            e.preventDefault();
            
            if (!confirm('Tüm cache verilerini temizlemek istediğinizden emin misiniz?')) {
                return;
            }
            
            this.showProgress('Cache temizleniyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_clear_cache',
                    nonce: esistenzeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EsistenzeTools.showNotice('Cache başarıyla temizlendi!', 'success');
                    } else {
                        EsistenzeTools.showNotice('Cache temizleme hatası: ' + response.data, 'error');
                    }
                },
                error: function() {
                    EsistenzeTools.showNotice('AJAX hatası oluştu.', 'error');
                },
                complete: function() {
                    EsistenzeTools.hideProgress();
                }
            });
        },
        
        cleanupDatabase: function(e) {
            e.preventDefault();
            
            if (!confirm('Kullanılmayan verileri temizlemek istediğinizden emin misiniz?')) {
                return;
            }
            
            this.showProgress('Database temizleniyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_cleanup_database',
                    nonce: esistenzeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EsistenzeTools.showNotice('Database temizleme tamamlandı!', 'success');
                    } else {
                        EsistenzeTools.showNotice('Database temizleme hatası: ' + response.data, 'error');
                    }
                },
                error: function() {
                    EsistenzeTools.showNotice('AJAX hatası oluştu.', 'error');
                },
                complete: function() {
                    EsistenzeTools.hideProgress();
                }
            });
        },
        
        createBackup: function(e) {
            e.preventDefault();
            
            this.showProgress('Backup oluşturuluyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_create_backup',
                    nonce: esistenzeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EsistenzeTools.showNotice('Backup başarıyla oluşturuldu!', 'success');
                    } else {
                        EsistenzeTools.showNotice('Backup oluşturma hatası: ' + response.data, 'error');
                    }
                },
                error: function() {
                    EsistenzeTools.showNotice('AJAX hatası oluştu.', 'error');
                },
                complete: function() {
                    EsistenzeTools.hideProgress();
                }
            });
        },
        
        showRestoreDialog: function(e) {
            e.preventDefault();
            alert('Restore özelliği yakında eklenecek.');
        },
        
        resetSettings: function(e) {
            e.preventDefault();
            
            if (!confirm('Tüm ayarları sıfırlamak istediğinizden emin misiniz?')) {
                return;
            }
            
            this.showProgress('Ayarlar sıfırlanıyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_reset_settings',
                    nonce: esistenzeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EsistenzeTools.showNotice('Ayarlar başarıyla sıfırlandı!', 'success');
                    } else {
                        EsistenzeTools.showNotice('Ayar sıfırlama hatası: ' + response.data, 'error');
                    }
                },
                error: function() {
                    EsistenzeTools.showNotice('AJAX hatası oluştu.', 'error');
                },
                complete: function() {
                    EsistenzeTools.hideProgress();
                }
            });
        },
        
        exportDebugInfo: function(e) {
            e.preventDefault();
            
            this.showProgress('Debug bilgisi hazırlanıyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_export_debug_info',
                    nonce: esistenzeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EsistenzeTools.showNotice('Debug bilgisi başarıyla export edildi!', 'success');
                    } else {
                        EsistenzeTools.showNotice('Debug export hatası: ' + response.data, 'error');
                    }
                },
                error: function() {
                    EsistenzeTools.showNotice('AJAX hatası oluştu.', 'error');
                },
                complete: function() {
                    EsistenzeTools.hideProgress();
                }
            });
        },
        
        regenerateThumbnails: function(e) {
            e.preventDefault();
            
            if (!confirm('Tüm görsel thumbnail\'larını yeniden oluşturmak istediğinizden emin misiniz?\n\nBu işlem uzun sürebilir.')) {
                return;
            }
            
            this.showProgress('Thumbnail\'lar yeniden oluşturuluyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_regenerate_thumbnails',
                    nonce: esistenzeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EsistenzeTools.showNotice('Thumbnail\'lar başarıyla yeniden oluşturuldu! ' + response.data.processed + ' görsel işlendi.', 'success');
                    } else {
                        EsistenzeTools.showNotice('Thumbnail oluşturma hatası: ' + response.data, 'error');
                    }
                },
                error: function() {
                    EsistenzeTools.showNotice('AJAX hatası oluştu.', 'error');
                },
                complete: function() {
                    EsistenzeTools.hideProgress();
                }
            });
        },
        
        migrateData: function(e) {
            e.preventDefault();
            
            if (!confirm('Eski versiyon verilerini yeni formata migrate etmek istediğinizden emin misiniz?')) {
                return;
            }
            
            this.showProgress('Veriler migrate ediliyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_migrate_data',
                    nonce: esistenzeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EsistenzeTools.showNotice('Veri migration tamamlandı! ' + response.data.migrated + ' kayıt güncellendi.', 'success');
                    } else {
                        EsistenzeTools.showNotice('Migration hatası: ' + response.data, 'error');
                    }
                },
                error: function() {
                    EsistenzeTools.showNotice('AJAX hatası oluştu.', 'error');
                },
                complete: function() {
                    EsistenzeTools.hideProgress();
                }
            });
        },
        
        runPerformanceTest: function(e) {
            e.preventDefault();
            
            this.showProgress('Performance testi çalışıyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_performance_test',
                    nonce: esistenzeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EsistenzeTools.showNotice('Performance testi tamamlandı!', 'success');
                    } else {
                        EsistenzeTools.showNotice('Performance testi hatası: ' + response.data, 'error');
                    }
                },
                error: function() {
                    EsistenzeTools.showNotice('AJAX hatası oluştu.', 'error');
                },
                complete: function() {
                    EsistenzeTools.hideProgress();
                }
            });
        },
        
        showProgress: function(message) {
            if ($('#tools-progress').length === 0) {
                $('body').append('<div id="tools-progress" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; z-index: 9999;"><div class="spinner is-active" style="float: left; margin-right: 10px;"></div><span class="message"></span></div>');
            }
            $('#tools-progress .message').text(message);
            $('#tools-progress').show();
        },
        
        hideProgress: function() {
            $('#tools-progress').hide();
        },
        
        showNotice: function(message, type) {
            var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.wrap h1').after(notice);
            
            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        EsistenzeTools.init();
    });
    
})(jQuery); 