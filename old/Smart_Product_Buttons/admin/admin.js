jQuery(document).ready(function($) {
    $('.sortable-button-list').sortable({
        update: function(event, ui) {
            var order = $(this).sortable('toArray', { attribute: 'data-index' });
            console.log('New order:', order);
        }
    });

    $('#add-new-button').on('click', function () {
        var rowCount = $('.sortable-button-list tr').length;
        var newRow = $('.sortable-button-list tr:first').clone();

        newRow.find('input, select').each(function () {
            var name = $(this).attr('name');
            if (name) {
                var newName = name.replace(/\[\d+\]/, '[' + rowCount + ']');
                $(this).attr('name', newName);

                if ($(this).is(':checkbox') || $(this).is(':radio')) {
                    $(this).prop('checked', false);
                } else {
                    $(this).val('');
                }
            }
        });

        $('.sortable-button-list').append(newRow);
    });

    $(document).on('click', '.duplicate-btn', function () {
        var rowCount = $('.sortable-button-list tr').length;
        var clone = $(this).closest('tr').clone();

        clone.find('input, select').each(function () {
            var name = $(this).attr('name');
            if (name) {
                var newName = name.replace(/\[\d+\]/, '[' + rowCount + ']');
                $(this).attr('name', newName);
            }
        });

        $('.sortable-button-list').append(clone);
    });

    $(document).on('click', '.preview-btn', function () {
        var row = $(this).closest('tr');
        var title = row.find('input[name$="[title]"]').val();
        var type = row.find('select[name$="[type]"]').val();
        var value = row.find('input[name$="[value]"]').val();
        var color1 = row.find('input[name$="[button_color_start]"]').val();
        var color2 = row.find('input[name$="[button_color_end]"]').val();
        var textColor = row.find('input[name$="[text_color]"]').val();
        var icon = row.find('input[name$="[icon]"]').val();
        var fontSize = row.find('select[name$="[font_size]"]').val();

        var previewHtml = '<div class="custom-button-wrapper">';
        previewHtml += '<a href="#" class="custom-track-button" style="background:linear-gradient(45deg,' + color1 + ',' + color2 + ');color:' + textColor + ';font-size:' + fontSize + 'px;">';
        if (icon) previewHtml += '<i class="fa ' + icon + '"></i>';
        previewHtml += title + '</a>';
        previewHtml += '</div>';

        alert(previewHtml); // Basit bir önizleme (modal ile geliştirilebilir)
    });
});