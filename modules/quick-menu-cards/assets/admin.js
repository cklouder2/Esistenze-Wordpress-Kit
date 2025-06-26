/**
 * Quick Menu Cards Admin JavaScript
 * Yeniden yazılmış, basit ve çalışır versiyon
 * 
 * @package Esistenze WordPress Kit
 * @subpackage Quick Menu Cards
 * @version 2.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Global değişkenler
    let mediaUploader;
    let currentCardIndex = -1;
    let currentGroupId = '';
    
    // Ana fonksiyonları başlat
    initEvents();
    initSortable();
    
    /**
     * Event listener'ları başlat
     */
    function initEvents() {
        // Modal açma/kapama
        $(document).on('click', '#qmc-new-group, #qmc-add-card, #qmc-add-first-card', openCardModal);
        $(document).on('click', '.qmc-modal-close', closeModal);
        $(document).on('click', '.qmc-modal', function(e) {
            if (e.target === this) closeModal();
        });
        
        // Kart işlemleri
        $(document).on('click', '.qmc-edit-card', editCard);
        $(document).on('click', '.qmc-delete-card', deleteCard);
        $(document).on('click', '#qmc-save-card', saveCard);
        
        // Resim seçimi
        $(document).on('click', '#card-select-image', selectImage);
        $(document).on('click', '#card-remove-image', removeImage);
        
        // Shortcode kopyalama
        $(document).on('click', '.qmc-copy-shortcode', copyShortcode);
        
        // Form validasyonu
        $(document).on('input', '#group_id', validateGroupId);
        $(document).on('input', '#card-title', validateCardTitle);
        
        // ESC tuşu ile modal kapatma
        $(document).on('keyup', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    }
    
    /**
     * Sıralama (sortable) özelliğini başlat
     */
    function initSortable() {
        if ($('#qmc-cards-list').length) {
            $('#qmc-cards-list').sortable({
                handle: '.qmc-drag-handle',
                placeholder: 'qmc-card-placeholder',
                update: function(event, ui) {
                    updateCardOrder();
                }
            });
        }
    }
    
    /**
     * Modal aç
     */
    function openCardModal(e) {
        e.preventDefault();
        
        const buttonId = $(this).attr('id');
        const isNewCard = buttonId.includes('add') || buttonId.includes('new');
        
        if (isNewCard) {
            // Yeni kart/grup modal'ı
            if (buttonId.includes('group')) {
                $('#qmc-new-group-modal').show();
            } else {
                // Yeni kart
                currentCardIndex = -1;
                currentGroupId = $('#card-group-id').val() || getUrlParameter('group');
                $('#qmc-modal-title').text('Yeni Kart Ekle');
                clearCardForm();
                $('#qmc-card-modal').show();
            }
        }
        
        // Focus ilk input'a
        setTimeout(function() {
            $('.qmc-modal:visible input:first').focus();
        }, 100);
    }
    
    /**
     * Modal kapat
     */
    function closeModal() {
        $('.qmc-modal').hide();
        clearCardForm();
    }
    
    /**
     * Kart düzenle
     */
    function editCard(e) {
        e.preventDefault();
        
        const cardIndex = $(this).data('index');
        const cardItem = $(this).closest('.qmc-card-item');
        
        currentCardIndex = cardIndex;
        currentGroupId = getUrlParameter('group');
        
        // Kart verilerini modal'a yükle
        loadCardToForm(cardItem);
        
        $('#qmc-modal-title').text('Kart Düzenle');
        $('#qmc-card-modal').show();
    }
    
    /**
     * Kart sil
     */
    function deleteCard(e) {
        e.preventDefault();
        
        if (!confirm(qmc_ajax.strings.confirm_delete)) {
            return;
        }
        
        const cardIndex = $(this).data('index');
        const cardItem = $(this).closest('.qmc-card-item');
        
        // AJAX ile kart sil
        $.ajax({
            url: qmc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qmc_delete_card',
                nonce: qmc_ajax.nonce,
                group_id: getUrlParameter('group'),
                card_index: cardIndex
            },
            beforeSend: function() {
                cardItem.addClass('qmc-loading');
            },
            success: function(response) {
                if (response.success) {
                    cardItem.fadeOut(300, function() {
                        $(this).remove();
                        updateCardIndices();
                        checkEmptyCards();
                    });
                    showNotice(qmc_ajax.strings.success, 'success');
                } else {
                    showNotice(response.data || qmc_ajax.strings.error, 'error');
                }
            },
            error: function() {
                showNotice(qmc_ajax.strings.error, 'error');
            },
            complete: function() {
                cardItem.removeClass('qmc-loading');
            }
        });
    }
    
    /**
     * Kart kaydet
     */
    function saveCard(e) {
        e.preventDefault();
        
        if (!validateCardForm()) {
            return;
        }
        
        const cardData = getCardFormData();
        const isNewCard = currentCardIndex === -1;
        
        $.ajax({
            url: qmc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: isNewCard ? 'qmc_add_card' : 'qmc_update_card',
                nonce: qmc_ajax.nonce,
                group_id: currentGroupId,
                card_index: currentCardIndex,
                card_data: cardData
            },
            beforeSend: function() {
                $('#qmc-save-card').prop('disabled', true).text(qmc_ajax.strings.loading);
            },
            success: function(response) {
                if (response.success) {
                    if (isNewCard) {
                        addCardToList(response.data.card, response.data.index);
                    } else {
                        updateCardInList(currentCardIndex, cardData);
                    }
                    
                    closeModal();
                    showNotice(qmc_ajax.strings.success, 'success');
                    checkEmptyCards();
                } else {
                    showNotice(response.data || qmc_ajax.strings.error, 'error');
                }
            },
            error: function() {
                showNotice(qmc_ajax.strings.error, 'error');
            },
            complete: function() {
                $('#qmc-save-card').prop('disabled', false).text('Kaydet');
            }
        });
    }
    
    /**
     * Resim seç
     */
    function selectImage(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: 'Kart Resmi Seç',
            button: {
                text: 'Resmi Seç'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#card-image-id').val(attachment.id);
            $('#card-image-url').val(attachment.url);
        });
        
        mediaUploader.open();
    }
    
    /**
     * Resim kaldır
     */
    function removeImage(e) {
        e.preventDefault();
        $('#card-image-id').val('');
        $('#card-image-url').val('');
    }
    
    /**
     * Shortcode kopyala
     */
    function copyShortcode(e) {
        e.preventDefault();
        
        const shortcode = $(this).data('shortcode');
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(shortcode).then(function() {
                showNotice('Shortcode kopyalandı!', 'success');
            });
        } else {
            // Fallback için geçici textarea oluştur
            const textarea = $('<textarea>').val(shortcode).appendTo('body').select();
            document.execCommand('copy');
            textarea.remove();
            showNotice('Shortcode kopyalandı!', 'success');
        }
    }
    
    /**
     * Kart sırasını güncelle
     */
    function updateCardOrder() {
        const cardOrder = [];
        $('#qmc-cards-list .qmc-card-item').each(function(index) {
            $(this).data('index', index);
            cardOrder.push(index);
        });
        
        $.ajax({
            url: qmc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qmc_reorder_cards',
                nonce: qmc_ajax.nonce,
                group_id: getUrlParameter('group'),
                card_order: cardOrder
            },
            success: function(response) {
                if (response.success) {
                    updateCardIndices();
                }
            }
        });
    }
    
    /**
     * Form validasyonları
     */
    function validateGroupId() {
        const groupId = $(this).val();
        const isValid = /^[a-zA-Z0-9_-]+$/.test(groupId);
        
        $(this).toggleClass('error', !isValid);
        
        if (!isValid && groupId.length > 0) {
            showNotice('Grup ID sadece harf, rakam, tire ve alt çizgi içerebilir.', 'error');
        }
    }
    
    function validateCardTitle() {
        const title = $(this).val();
        const isValid = title.trim().length > 0;
        
        $(this).toggleClass('error', !isValid);
    }
    
    function validateCardForm() {
        const title = $('#card-title').val().trim();
        
        if (!title) {
            showNotice('Kart başlığı zorunludur!', 'error');
            $('#card-title').focus();
            return false;
        }
        
        return true;
    }
    
    /**
     * Yardımcı fonksiyonlar
     */
    function clearCardForm() {
        $('#card-title').val('');
        $('#card-description').val('');
        $('#card-url').val('');
        $('#card-image-id').val('');
        $('#card-image-url').val('');
        $('#card-button-text').val('');
        $('#card-index').val('');
    }
    
    function loadCardToForm(cardItem) {
        const title = cardItem.find('h4').text();
        const description = cardItem.find('p').text();
        const imageUrl = cardItem.find('img').attr('src') || '';
        
        $('#card-title').val(title);
        $('#card-description').val(description);
        $('#card-image-url').val(imageUrl);
        $('#card-index').val(currentCardIndex);
    }
    
    function getCardFormData() {
        return {
            title: $('#card-title').val().trim(),
            description: $('#card-description').val().trim(),
            url: $('#card-url').val().trim(),
            image: $('#card-image-url').val().trim(),
            image_id: $('#card-image-id').val(),
            button_text: $('#card-button-text').val().trim()
        };
    }
    
    function addCardToList(cardData, index) {
        const cardHtml = createCardHtml(cardData, index);
        
        if ($('#qmc-cards-list').length === 0) {
            $('.qmc-no-cards').replaceWith('<div id="qmc-cards-list" class="qmc-cards-list"></div>');
            initSortable();
        }
        
        $('#qmc-cards-list').append(cardHtml);
    }
    
    function updateCardInList(index, cardData) {
        const cardItem = $(`[data-index="${index}"]`);
        
        cardItem.find('h4').text(cardData.title);
        cardItem.find('p').text(cardData.description.substring(0, 50) + (cardData.description.length > 50 ? '...' : ''));
        
        if (cardData.image) {
            cardItem.find('.qmc-card-preview').html(`<img src="${cardData.image}" alt="${cardData.title}">`);
        } else {
            cardItem.find('.qmc-card-preview').html('<div class="qmc-card-no-image">Resim Yok</div>');
        }
    }
    
    function createCardHtml(cardData, index) {
        const imageHtml = cardData.image 
            ? `<img src="${cardData.image}" alt="${cardData.title}">` 
            : '<div class="qmc-card-no-image">Resim Yok</div>';
            
        const description = cardData.description.length > 50 
            ? cardData.description.substring(0, 50) + '...' 
            : cardData.description;
        
        return `
            <div class="qmc-card-item" data-index="${index}">
                <div class="qmc-card-preview">${imageHtml}</div>
                <div class="qmc-card-info">
                    <h4>${cardData.title}</h4>
                    <p>${description}</p>
                </div>
                <div class="qmc-card-actions">
                    <button type="button" class="button button-small qmc-edit-card" data-index="${index}">Düzenle</button>
                    <button type="button" class="button button-small qmc-delete-card" data-index="${index}">Sil</button>
                    <span class="qmc-drag-handle">⋮⋮</span>
                </div>
            </div>
        `;
    }
    
    function updateCardIndices() {
        $('#qmc-cards-list .qmc-card-item').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('.qmc-edit-card, .qmc-delete-card').attr('data-index', index);
        });
    }
    
    function checkEmptyCards() {
        if ($('#qmc-cards-list .qmc-card-item').length === 0) {
            $('#qmc-cards-list').replaceWith(`
                <div class="qmc-no-cards">
                    <p>Bu grupta henüz kart yok.</p>
                    <button type="button" class="button button-primary" id="qmc-add-first-card">İlk Kartı Ekle</button>
                </div>
            `);
        }
    }
    
    function showNotice(message, type = 'info') {
        const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
        const notice = $(`
            <div class="notice ${noticeClass} is-dismissible qmc-notice">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Bu bildirimi kapat.</span>
                </button>
            </div>
        `);
        
        $('.wrap h1').after(notice);
        
        // Otomatik kapat
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
        
        // Manuel kapat
        notice.on('click', '.notice-dismiss', function() {
            notice.fadeOut();
        });
    }
    
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    
    // Debug için global erişim
    window.QMCAdmin = {
        openCardModal,
        closeModal,
        showNotice,
        version: '2.0.0'
    };
}); 