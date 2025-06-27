<?php
/**
 * Custom Topbar Module
 */

if (!defined('ABSPATH')) exit;

class EsistenzaCustomTopbar {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        
        if (has_action('wp_body_open')) {
            add_action('wp_body_open', array($this, 'display_topbar'));
        } else {
            add_action('wp_head', array($this, 'display_topbar'), 1);
        }
    }

    public function admin_menu() {
        add_submenu_page('esistenze-toolkit', 'Özel Üst Çubuk', 'Üst Çubuk', 'manage_options', 'xai-top-bar-settings', array($this, 'settings_page'));
    }

    public function register_settings() {
        register_setting('xai-top-bar-settings-group', 'xai_top_bar_menu');
        register_setting('xai-top-bar-settings-group', 'xai_top_bar_phone');
        register_setting('xai-top-bar-settings-group', 'xai_top_bar_email');
        register_setting('xai-top-bar-settings-group', 'xai_top_bar_bg_color');
        register_setting('xai-top-bar-settings-group', 'xai_top_bar_text_color');
    }

    public function settings_page() {
        if (isset($_POST['xai_top_bar_save'])) {
            if (!current_user_can('manage_options')) {
                wp_die('Yetkiniz yok.');
            }
            update_option('xai_top_bar_menu', sanitize_text_field($_POST['menu']));
            update_option('xai_top_bar_phone', sanitize_text_field($_POST['phone']));
            update_option('xai_top_bar_email', sanitize_email($_POST['email']));
            update_option('xai_top_bar_bg_color', sanitize_hex_color($_POST['bg_color']));
            update_option('xai_top_bar_text_color', sanitize_hex_color($_POST['text_color']));
            echo '<div class="updated"><p>Ayarlar kaydedildi.</p></div>';
        }

        $selected_menu = get_option('xai_top_bar_menu', '');
        $phone = get_option('xai_top_bar_phone', '');
        $email = get_option('xai_top_bar_email', '');
        $bg_color = get_option('xai_top_bar_bg_color', '#4CAF50');
        $text_color = get_option('xai_top_bar_text_color', '#fff');
        $menus = get_terms('nav_menu', array('hide_empty' => false));
        ?>
        <div class="wrap">
            <h1>📊 Özel Üst Çubuk</h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th><label for="menu">Menü Seçimi</label></th>
                        <td>
                            <select name="menu" id="menu">
                                <option value="">-- Menü Seçiniz --</option>
                                <?php foreach ($menus as $menu): ?>
                                    <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($selected_menu, $menu->term_id); ?>>
                                        <?php echo esc_html($menu->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="phone">Telefon</label></th>
                        <td><input type="text" name="phone" id="phone" value="<?php echo esc_attr($phone); ?>" placeholder="+90 123 456 7890"></td>
                    </tr>
                    <tr>
                        <th><label for="email">E-posta</label></th>
                        <td><input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>" placeholder="ornek@ornek.com"></td>
                    </tr>
                    <tr>
                        <th><label for="bg_color">Arka Plan Rengi</label></th>
                        <td><input type="color" name="bg_color" id="bg_color" value="<?php echo esc_attr($bg_color); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="text_color">Metin Rengi</label></th>
                        <td><input type="color" name="text_color" id="text_color" value="<?php echo esc_attr($text_color); ?>"></td>
                    </tr>
                </table>
                <p><input type="submit" name="xai_top_bar_save" class="button-primary" value="Ayarları Kaydet"></p>
            </form>
        </div>
        <?php
    }

    public function enqueue_styles() {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
        
        wp_enqueue_style(
            'esistenza-custom-topbar-style',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/custom-topbar.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
        
        // Dinamik renkler için CSS variables
        $bg_color = get_option('xai_top_bar_bg_color', '#4CAF50');
        $text_color = get_option('xai_top_bar_text_color', '#ffffff');
        
        $custom_css = "
        :root {
            --topbar-bg-color: {$bg_color};
            --topbar-text-color: {$text_color};
        }
        ";
        
        wp_add_inline_style('esistenza-custom-topbar-style', $custom_css);
    }

    public function enqueue_admin_styles($hook) {
        // Sadece plugin sayfalarında yükle
        if (strpos($hook, 'xai-top-bar-settings') === false) {
            return;
        }
        
        wp_enqueue_style(
            'esistenza-custom-topbar-admin-style',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/dashboard-admin.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
    }

    public function display_topbar() {
        $selected_menu = get_option('xai_top_bar_menu', '');
        $phone = get_option('xai_top_bar_phone', '');
        $email = get_option('xai_top_bar_email', '');

        if (empty($selected_menu) && empty($phone) && empty($email)) {
            return;
        }

        echo '<div class="xai-top-bar">';
        echo '<div class="xai-top-bar-left">';
        if (!empty($selected_menu)) {
            wp_nav_menu(array(
                'menu' => $selected_menu,
                'menu_class' => 'xai-top-bar-menu',
                'container' => false,
                'depth' => 1,
                'fallback_cb' => '__return_empty_string'
            ));
        }
        echo '</div>';

        echo '<div class="xai-top-bar-right">';
        echo '<ul class="xai-top-bar-contact">';
        if (!empty($phone)) {
            echo '<li><a href="tel:' . esc_attr($phone) . '"><i class="fa fa-phone"></i>' . esc_html($phone) . '</a></li>';
        }
        if (!empty($email)) {
            echo '<li><a href="mailto:' . esc_attr($email) . '"><i class="fa fa-envelope"></i>' . esc_html($email) . '</a></li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';
    }
}

// Modülü başlat
new EsistenzaCustomTopbar(); 