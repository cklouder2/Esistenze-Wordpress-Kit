/* Meta Cards Module - Admin JavaScript */
document.addEventListener('DOMContentLoaded', function() {
    // Dinamik grup ID'sini al
    var groupId = window.metaCardsGroupId || 0;
    
    // Yeni kart ekleme butonu
    var addButton = document.getElementById('add-karti');
    if (addButton) {
        addButton.addEventListener('click', function() {
            var container = document.getElementById('kartlar-container');
            var index = container.children.length;
            var html = '';
            html += '<div class="kart-kutu">';
            html += '<label>Başlık:</label>';
            html += '<input type="text" name="hizli_menu_kartlari[' + groupId + '][' + index + '][title]" placeholder="Başlık">';
            html += '<label>Açıklama:</label>';
            html += '<input type="text" name="hizli_menu_kartlari[' + groupId + '][' + index + '][desc]" placeholder="Soru / Açıklama">';
            html += '<label>Görsel:</label>';
            html += '<input type="hidden" class="image-url" name="hizli_menu_kartlari[' + groupId + '][' + index + '][img]">';
            html += '<label>Bağlantı URL:</label>';
            html += '<input type="url" name="hizli_menu_kartlari[' + groupId + '][' + index + '][url]" placeholder="https://example.com">';
            html += '<button class="upload-image-button button">Görsel Yükle</button>';
            html += '<div class="preview"></div>';
            html += '</div>';
            container.insertAdjacentHTML('beforeend', html);
        });
    }

    // Görsel yükleme butonu event listener
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('upload-image-button')) {
            e.preventDefault();
            var button = e.target;
            
            // WordPress Media Uploader kontrolü
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                alert('WordPress Media Uploader yüklenemedi. Lütfen sayfayı yenileyin.');
                return;
            }
            
            var uploader = wp.media({
                title: 'Görsel Seç',
                button: { text: 'Seç' },
                multiple: false
            }).on('select', function() {
                var attachment = uploader.state().get('selection').first().toJSON();
                button.previousElementSibling.value = attachment.url;
                button.nextElementSibling.innerHTML = '<img src="' + attachment.url + '" style="max-width:100px; margin-top:10px;">';
            }).open();
        }
    });
}); 