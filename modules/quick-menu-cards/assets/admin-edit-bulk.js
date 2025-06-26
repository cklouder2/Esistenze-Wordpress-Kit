/*
 * Quick Menu Cards - Admin Bulk Operations JavaScript
 * Toplu işlemler için JavaScript fonksiyonları
 */

(function($) {
    'use strict';
    
    var EsistenzeBulkOperations = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Toplu seçim
            $(document).on('change', '#cb-select-all-1, #cb-select-all-2', this.toggleSelectAll.bind(this));
            $(document).on('change', '.group-checkbox', this.updateBulkActions.bind(this));
            
            // Toplu işlem uygulama
            $(document).on('click', '#doaction, #doaction2', this.applyBulkAction.bind(this));
            
            // Toplu silme onayı
            $(document).on('click', '.bulk-delete', this.confirmBulkDelete.bind(this));
            
            // Toplu export
            $(document).on('click', '.bulk-export', this.bulkExport.bind(this));
            
            // Toplu import
            $(document).on('click', '.bulk-import', this.showImportDialog.bind(this));
        },
        
        toggleSelectAll: function(e) {
            var checked = $(e.target).prop('checked');
            $('.group-checkbox').prop('checked', checked);
            this.updateBulkActions();
        },
        
        updateBulkActions: function() {
            var selectedCount = $('.group-checkbox:checked').length;
            var $bulkActions = $('.bulk-actions select');
            
            if (selectedCount > 0) {
                $bulkActions.prop('disabled', false);
                $('.bulk-action-info').text(selectedCount + ' grup seçildi');
            } else {
                $bulkActions.prop('disabled', true);
                $('.bulk-action-info').text('');
            }
        },
        
        applyBulkAction: function(e) {
            e.preventDefault();
            
            var action = $(e.target).siblings('select').val();
            var selectedIds = this.getSelectedIds();
            
            if (!action || action === '-1') {
                alert('Lütfen bir işlem seçin.');
                return;
            }
            
            if (selectedIds.length === 0) {
                alert('Lütfen en az bir grup seçin.');
                return;
            }
            
            switch (action) {
                case 'delete':
                    this.bulkDelete(selectedIds);
                    break;
                case 'export':
                    this.bulkExport(selectedIds);
                    break;
                case 'duplicate':
                    this.bulkDuplicate(selectedIds);
                    break;
                case 'activate':
                    this.bulkActivate(selectedIds);
                    break;
                case 'deactivate':
                    this.bulkDeactivate(selectedIds);
                    break;
                default:
                    alert('Bilinmeyen işlem: ' + action);
            }
        },
        
        getSelectedIds: function() {
            var ids = [];
            $('.group-checkbox:checked').each(function() {
                ids.push($(this).val());
            });
            return ids;
        },
        
        bulkDelete: function(ids) {
            if (!confirm('Seçili ' + ids.length + ' grubu silmek istediğinizden emin misiniz?\n\nBu işlem geri alınamaz!')) {
                return;
            }
            
            this.showProgress('Gruplar siliniyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_bulk_delete_groups',
                    nonce: esistenzeAdmin.nonce,
                    group_ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + (response.data || 'Bilinmeyen hata'));
                    }
                },
                error: function() {
                    alert('AJAX hatası oluştu.');
                },
                complete: function() {
                    EsistenzeBulkOperations.hideProgress();
                }
            });
        },
        
        bulkExport: function(ids) {
            if (ids && ids.length > 0) {
                // Seçili grupları export et
                var exportIds = ids;
            } else {
                // Tüm grupları export et
                var exportIds = this.getAllGroupIds();
            }
            
            if (exportIds.length === 0) {
                alert('Export edilecek grup bulunamadı.');
                return;
            }
            
            this.showProgress('Gruplar export ediliyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_export_groups',
                    nonce: esistenzeAdmin.nonce,
                    group_ids: exportIds
                },
                success: function(response) {
                    if (response.success) {
                        // JSON dosyasını indir
                        var dataStr = JSON.stringify(response.data, null, 2);
                        var dataBlob = new Blob([dataStr], {type: 'application/json'});
                        var url = URL.createObjectURL(dataBlob);
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = 'quick-menu-cards-export-' + new Date().toISOString().slice(0, 10) + '.json';
                        link.click();
                        URL.revokeObjectURL(url);
                    } else {
                        alert('Export hatası: ' + (response.data || 'Bilinmeyen hata'));
                    }
                },
                error: function() {
                    alert('Export sırasında AJAX hatası oluştu.');
                },
                complete: function() {
                    EsistenzeBulkOperations.hideProgress();
                }
            });
        },
        
        bulkDuplicate: function(ids) {
            if (!confirm('Seçili ' + ids.length + ' grubu kopyalamak istediğinizden emin misiniz?')) {
                return;
            }
            
            this.showProgress('Gruplar kopyalanıyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_bulk_duplicate_groups',
                    nonce: esistenzeAdmin.nonce,
                    group_ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Kopyalama hatası: ' + (response.data || 'Bilinmeyen hata'));
                    }
                },
                error: function() {
                    alert('AJAX hatası oluştu.');
                },
                complete: function() {
                    EsistenzeBulkOperations.hideProgress();
                }
            });
        },
        
        bulkActivate: function(ids) {
            this.bulkToggleStatus(ids, true, 'Gruplar aktif hale getiriliyor...');
        },
        
        bulkDeactivate: function(ids) {
            this.bulkToggleStatus(ids, false, 'Gruplar pasif hale getiriliyor...');
        },
        
        bulkToggleStatus: function(ids, status, message) {
            this.showProgress(message);
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_bulk_toggle_status',
                    nonce: esistenzeAdmin.nonce,
                    group_ids: ids,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Durum değiştirme hatası: ' + (response.data || 'Bilinmeyen hata'));
                    }
                },
                error: function() {
                    alert('AJAX hatası oluştu.');
                },
                complete: function() {
                    EsistenzeBulkOperations.hideProgress();
                }
            });
        },
        
        showImportDialog: function(e) {
            e.preventDefault();
            
            var dialog = $('<div id="import-dialog" title="Grup İçe Aktar">' +
                '<p>JSON formatında export edilmiş grup dosyasını seçin:</p>' +
                '<input type="file" id="import-file" accept=".json" />' +
                '<div id="import-preview" style="margin-top: 15px; display: none;">' +
                    '<h4>Önizleme:</h4>' +
                    '<div id="import-info"></div>' +
                '</div>' +
                '</div>');
            
            dialog.dialog({
                modal: true,
                width: 500,
                height: 300,
                buttons: {
                    'İçe Aktar': function() {
                        EsistenzeBulkOperations.processImport();
                    },
                    'İptal': function() {
                        $(this).dialog('close');
                    }
                },
                close: function() {
                    $(this).remove();
                }
            });
            
            // Dosya seçildiğinde önizleme göster
            $('#import-file').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            var data = JSON.parse(e.target.result);
                            EsistenzeBulkOperations.showImportPreview(data);
                        } catch (error) {
                            alert('Geçersiz JSON dosyası.');
                        }
                    };
                    reader.readAsText(file);
                }
            });
        },
        
        showImportPreview: function(data) {
            var groupCount = Object.keys(data).length;
            var totalCards = 0;
            
            for (var groupId in data) {
                if (data[groupId].cards) {
                    totalCards += data[groupId].cards.length;
                }
            }
            
            var info = '<strong>' + groupCount + '</strong> grup, <strong>' + totalCards + '</strong> kart içe aktarılacak.';
            $('#import-info').html(info);
            $('#import-preview').show();
        },
        
        processImport: function() {
            var file = $('#import-file')[0].files[0];
            if (!file) {
                alert('Lütfen bir dosya seçin.');
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var data = JSON.parse(e.target.result);
                    EsistenzeBulkOperations.uploadImportData(data);
                } catch (error) {
                    alert('Dosya okunamadı: ' + error.message);
                }
            };
            reader.readAsText(file);
        },
        
        uploadImportData: function(data) {
            $('#import-dialog').dialog('close');
            this.showProgress('Veriler içe aktarılıyor...');
            
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_import_groups',
                    nonce: esistenzeAdmin.nonce,
                    import_data: JSON.stringify(data)
                },
                success: function(response) {
                    if (response.success) {
                        alert('İçe aktarma başarılı! ' + response.data.imported + ' grup eklendi.');
                        location.reload();
                    } else {
                        alert('İçe aktarma hatası: ' + (response.data || 'Bilinmeyen hata'));
                    }
                },
                error: function() {
                    alert('AJAX hatası oluştu.');
                },
                complete: function() {
                    EsistenzeBulkOperations.hideProgress();
                }
            });
        },
        
        getAllGroupIds: function() {
            var ids = [];
            $('.group-checkbox').each(function() {
                ids.push($(this).val());
            });
            return ids;
        },
        
        showProgress: function(message) {
            if ($('#bulk-progress').length === 0) {
                $('body').append('<div id="bulk-progress" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; z-index: 9999;"><div class="spinner is-active" style="float: left; margin-right: 10px;"></div><span class="message"></span></div>');
            }
            $('#bulk-progress .message').text(message);
            $('#bulk-progress').show();
        },
        
        hideProgress: function() {
            $('#bulk-progress').hide();
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        EsistenzeBulkOperations.init();
    });
    
})(jQuery); 