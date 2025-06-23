<?php
// Eklenti güvenliği için doğrudan erişimi engelle
if (!defined('ABSPATH')) {
    exit;
}

// Yönetim paneli menüsünü ekle
function xai_top_bar_admin_menu() {
    add_menu_page(
        'WP Custom Topbar Ayarları',
        'Top Bar',
        'manage_options',
        'xai-top-bar-settings',
        'xai_top_bar_settings_page',
        'dashicons-admin-generic'
    );
}
add_action('admin_menu', 'xai_top_bar_admin_menu');

// Ayarlar sayfasını oluştur
function xai_top_bar_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Ayarları kaydet
    if (isset($_POST['xai_top_bar_save'])) {
        update_option('xai_top_bar_menu', sanitize_text_field($_POST['menu']));
        update_option('xai_top_bar_phone', sanitize_text_field($_POST['phone']));
        update_option('xai_top_bar_email', sanitize_email($_POST['email']));
        update_option('xai_top_bar_bg_color', sanitize_hex_color($_POST['bg_color']));
        update_option('xai_top_bar_font_size', sanitize_text_field($_POST['font_size']));
        update_option('xai_top_bar_text_color', sanitize_hex_color($_POST['text_color']));
        update_option('xai_top_bar_padding', sanitize_text_field($_POST['padding']));
        update_option('xai_top_bar_height', sanitize_text_field($_POST['height']));
        echo '<div class="updated"><p>Ayarlar kaydedildi.</p></div>';
    }

    $selected_menu = get_option('xai_top_bar_menu', '');
    $phone = get_option('xai_top_bar_phone', '');
    $email = get_option('xai_top_bar_email', '');
    $bg_color = get_option('xai_top_bar_bg_color', '#333');
    $font_size = get_option('xai_top_bar_font_size', '16');
    $text_color = get_option('xai_top_bar_text_color', '#fff');
    $padding = get_option('xai_top_bar_padding', '5');
    $height = get_option('xai_top_bar_height', '50');

    // Mevcut menüleri al
    $menus = get_terms('nav_menu', array('hide_empty' => false));
    ?>
    <div class="wrap">
        <h1>WP Custom Topbar Ayarları</h1>
        <form method="post" action="">
            <h2>Menü Seçimi</h2>
            <p>WordPress'te daha önce oluşturduğunuz menülerden birini seçin.</p>
            <select name="menu" id="menu">
                <option value="">-- Menü Seçiniz --</option>
                <?php
                foreach ($menus as $menu) {
                    printf('<option value="%s" %s>%s</option>',
                        esc_attr($menu->term_id),
                        selected($selected_menu, $menu->term_id, false),
                        esc_html($menu->name)
                    );
                }
                ?>
            </select>

            <h2>İletişim Bilgileri</h2>
            <p>Telefon numarasını ve e-posta adresini girin.</p>
            <p>
                <label for="phone">Telefon Numarası:</label><br>
                <input type="text" name="phone" id="phone" value="<?php echo esc_attr($phone); ?>" placeholder="+90 123 456 7890">
            </p>
            <p>
                <label for="email">E-posta Adresi:</label><br>
                <input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>" placeholder="ornek@ornek.com">
            </p>

            <h2>Stil Ayarları</h2>
            <p>Top bar'ın görünümünü özelleştirin.</p>
            <p>
                <label for="bg_color">Arka Plan Rengi:</label><br>
                <input type="color" name="bg_color" id="bg_color" value="<?php echo esc_attr($bg_color); ?>">
            </p>
			<p>
    			<label for="bg_color">Arka Plan Rengi:</label><br>
    			<input type="color" name="bg_color" id="bg_color" value="<?php echo esc_attr($bg_color); ?>">
			</p>
            <p>
                <label for="text_color">Metin Rengi:</label><br>
                <input type="color" name="text_color" id="text_color" value="<?php echo esc_attr($text_color); ?>">
            </p>
            <p>
                <label for="font_size">Font Boyutu (px):</label><br>
                <input type="number" name="font_size" id="font_size" value="<?php echo esc_attr($font_size); ?>" min="10" max="30" step="1">
            </p>
            <p>
                <label for="padding">İç Boşluk (Padding, %):</label><br>
                <input type="number" name="padding" id="padding" value="<?php echo esc_attr($padding); ?>" min="0" max="20" step="1">%
            </p>
            <p>
                <label for="height">Yükseklik (px):</label><br>
                <input type="number" name="height" id="height" value="<?php echo esc_attr($height); ?>" min="30" max="100" step="1">
            </p>

            <p><input type="submit" name="xai_top_bar_save" class="button button-primary" value="Ayarları Kaydet"></p>
        </form>
    </div>
    <?php
}

// Ayarları kaydetmek için başlatıcı
function xai_top_bar_register_settings() {
    register_setting('xai-top-bar-settings-group', 'xai_top_bar_menu');
    register_setting('xai-top-bar-settings-group', 'xai_top_bar_phone');
    register_setting('xai-top-bar-settings-group', 'xai_top_bar_email');
    register_setting('xai-top-bar-settings-group', 'xai_top_bar_bg_color');
    register_setting('xai_top_bar-settings-group', 'xai_top_bar_font_size');
    register_setting('xai-top-bar-settings-group', 'xai_top_bar_text_color');
    register_setting('xai-top-bar-settings-group', 'xai_top_bar_padding');
    register_setting('xai-top-bar-settings-group', 'xai_top_bar_height');
}
add_action('admin_init', 'xai_top_bar_register_settings');
?>