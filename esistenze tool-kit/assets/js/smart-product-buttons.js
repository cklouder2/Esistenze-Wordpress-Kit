/* Smart Product Buttons Module - Frontend JavaScript */

jQuery(document).ready(function($) {
    'use strict';
    
    // Form popup trigger
    $(".form-popup-trigger").click(function() {
        $("#smart-form-modal").show();
        $(".smart-form-container").html($("#smart-form-modal-content").html());
    });
    
    // Modal close functionality
    $(".smart-close, .smart-modal").click(function(e) {
        if (e.target === this) {
            $("#smart-form-modal").hide();
        }
    });
    
    // ESC key to close modal
    $(document).keyup(function(e) {
        if (e.keyCode === 27) { // ESC key
            $("#smart-form-modal").hide();
        }
    });
    
    // Prevent modal content click from closing modal
    $(".smart-modal-content").click(function(e) {
        e.stopPropagation();
    });
}); 