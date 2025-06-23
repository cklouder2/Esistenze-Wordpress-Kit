jQuery(document).ready(function($) {
    console.log('Esistenze Smart Buttons loaded at ' + new Date().toISOString());
    
    if ($('.esistenze-form-popup-trigger').length) {
        console.log('Form triggers found:', $('.esistenze-form-popup-trigger').length);
        
        $('.esistenze-form-popup-trigger').on('click', function() {
            console.log('Button clicked, ID:', $(this).data('id'));
            
            var modalContent = $('#esistenze-form-modal-content').html();
            if (modalContent) {
                $('#esistenze-form-modal .esistenze-smart-form-container').html(modalContent);
                $('#esistenze-form-modal').fadeIn();
                
                // Initialize Contact Form 7 if available
                if (typeof window.wpcf7 === 'object' && typeof wpcf7.init === 'function') {
                    wpcf7.init(document.querySelector('#esistenze-form-modal .esistenze-smart-form-container'));
                }
            } else {
                $('#esistenze-form-modal .esistenze-smart-form-container').html('<p>Hata: Form yüklenemedi.</p>');
                $('#esistenze-form-modal').fadeIn();
                console.log('Modal content is empty');
            }
        });
    } else {
        console.log('No form-popup-trigger buttons found');
    }

    // Close modal handlers
    $('.esistenze-smart-close').on('click', function() {
        $('#esistenza-form-modal').fadeOut();
        $('#esistenze-form-modal .esistenze-smart-form-container').empty();
    });

    $(document).on('click', function(event) {
        if ($(event.target).is('#esistenze-form-modal')) {
            $('#esistenze-form-modal').fadeOut();
            $('#esistenze-form-modal .esistenze-smart-form-container').empty();
        }
    });
});