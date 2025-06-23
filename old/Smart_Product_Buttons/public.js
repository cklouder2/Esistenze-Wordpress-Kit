jQuery(document).ready(function($) {
    console.log('Script loaded at ' + new Date().toISOString()); // Hata ayıklama
    if ($('.form-popup-trigger').length) {
        console.log('Form triggers found:', $('.form-popup-trigger').length); // Hata ayıklama
        $('.form-popup-trigger').on('click', function() {
            console.log('Button clicked, ID:', $(this).data('id')); // Hata ayıklama
            var modalContent = $('#smart-form-modal-content').html();
            if (modalContent) {
                $('#smart-form-modal .smart-form-container').html(modalContent);
                $('#smart-form-modal').fadeIn();
                if (typeof window.wpcf7 === 'object' && typeof wpcf7.init === 'function') {
                    wpcf7.init(document.querySelector('#smart-form-modal .smart-form-container'));
                }
            } else {
                $('#smart-form-modal .smart-form-container').html('<p>Hata: Form yüklenemedi.</p>');
                $('#smart-form-modal').fadeIn();
                console.log('Modal content is empty'); // Hata ayıklama
            }
        });
    } else {
        console.log('No form-popup-trigger buttons found'); // Hata ayıklama
    }

    $('.smart-close').on('click', function() {
        $('#smart-form-modal').fadeOut();
        $('#smart-form-modal .smart-form-container').empty();
    });

    $(document).on('click', function(event) {
        if ($(event.target).is('#smart-form-modal')) {
            $('#smart-form-modal').fadeOut();
            $('#smart-form-modal .smart-form-container').empty();
        }
    });
});