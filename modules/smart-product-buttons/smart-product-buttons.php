<?php
/*
 * Smart Product Buttons Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) exit;

class EsistenzeSmartButtons {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Admin hooks
        add_action('admin_post_esistenze_smart_button_save', array($this, 'save_button'));
        add_action('admin_post_esistenze_smart_button_delete', array($this, 'delete_button'));
        
        // Frontend hooks
        add_action('woocommerce_product_meta_end', array($this, 'render_buttons_on_product_page'), 10, 0);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('wp_footer', array($this, 'render_modal_container'));
    }
    
    public static function admin_page() {
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        echo '<div class="wrap">';
        echo '<h1>Smart Product Buttons</h1>';
        echo '<p>WooCommerce ürün sayfalarında görüntülenecek özelleştirilebilir butonları yönetin.</p>';
        echo '<a href="' . admin_url('admin.php?page=esistenze-smart-buttons&new=1') . '" class="button-primary">Yeni Buton Ekle</a>';
        
        if (isset($_GET['edit']) && isset($buttons[$_GET['edit']])) {
            self::render_button_form($buttons[$_GET['edit']], $_GET['edit']);
        } elseif (isset($_GET['new'])) {
            self::render_button_form([], null);
        } else {
            echo '<table class="widefat smart-button-table" style="margin-top: 20px;"><thead><tr><th>Başlık</th><th>Tür</th><th>Grup</th><th>İşlem</th></tr></thead><tbody>';
            foreach ($buttons as $index => $button) {
                echo '<tr>';
                echo '<td>' . esc_html($button['title'] ?? '-') . '</td>';
                echo '<td>' . esc_html($button['type'] ?? '-') . '</td>';
                echo '<td>' . esc_html($button['group'] ?? '-') . '</td>';
                echo '<td>
                        <a class="button" href="' . admin_url('admin.php?page=esistenze-smart-buttons&edit=' . $index) . '">Düzenle</a>
                        <a class="button delete-button" href="' . wp_nonce_url(admin_url('admin-post.php?action=esistenze_smart_button_delete&index=' . $index), 'esistenze_smart_button_delete_' . $index) . '" onclick="return confirm(\'Silmek istediğinize emin misiniz?\')">Sil</a>
                    </td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        echo '</div>';
    }
    
    private static function render_button_form($button, $index = null) {
        $action = admin_url('admin-post.php');
        ?>
        <form method="post" action="<?php echo esc_url($action); ?>" style="margin-top: 20px;">
            <input type="hidden" name="action" value="esistenze_smart_button_save">
            <?php if ($index !== null): ?><input type="hidden" name="button_index" value="<?php echo esc_attr($index); ?>"><?php endif; ?>
            <?php wp_nonce_field('esistenze_smart_button_save'); ?>
            <table class="form-table">
                <tr><th scope="row">Grup</th><td><input name="group" type="text" value="<?php echo esc_attr($button['group'] ?? ''); ?>" class="regular-text" /></td></tr>
                <tr><th scope="row">Tür</th><td>
                    <select name="type" class="regular-text">
                        <option value="phone" <?php selected($button['type'] ?? '', 'phone'); ?>>Telefon</option>
                        <option value="mail" <?php selected($button['type'] ?? '', 'mail'); ?>>Mail</option>
                        <option value="whatsapp" <?php selected($button['type'] ?? '', 'whatsapp'); ?>>WhatsApp</option>
                        <option value="add_to_cart" <?php selected($button['type'] ?? '', 'add_to_cart'); ?>>Sepete Ekle</option>
                        <option value="form_trigger" <?php selected($button['type'] ?? '', 'form_trigger'); ?>>Form</option>
                    </select>
                </td></tr>
                <tr><th scope="row">Başlık</th><td><input name="title" type="text" value="<?php echo esc_attr($button['title'] ?? ''); ?>" class="regular-text" required /></td></tr>
                <tr><th scope="row">Contact Form 7 ID / Değer</th><td><input name="value" type="text" value="<?php echo esc_attr($button['value'] ?? ''); ?>" class="regular-text" placeholder="Örnek: 61def4f" required /></td></tr>
                <tr><th scope="row">Mesaj</th><td><input name="message" type="text" value="<?php echo esc_attr($button['message'] ?? ''); ?>" class="regular-text" /></td></tr>
                <tr><th scope="row">Renk 1</th><td><input name="button_color_start" type="color" value="<?php echo esc_attr($button['button_color_start'] ?? '#000000'); ?>" required /></td></tr>
                <tr><th scope="row">Renk 2</th><td><input name="button_color_end" type="color" value="<?php echo esc_attr($button['button_color_end'] ?? '#000000'); ?>" required /></td></tr>
                <tr><th scope="row">Yazı Rengi</th><td><input name="text_color" type="color" value="<?php echo esc_attr($button['text_color'] ?? '#ffffff'); ?>" required /></td></tr>
                <tr><th scope="row">İkon (Font Awesome)</th><td><input name="icon" type="text" value="<?php echo esc_attr($button['icon'] ?? ''); ?>" class="regular-text" placeholder="fa-phone" /></td></tr>
                <tr><th scope="row">Font Boyutu</th><td>
                    <select name="font_size">
                        <?php for ($fs = 11; $fs <= 40; $fs++): ?>
                            <option value="<?php echo $fs; ?>" <?php selected($button['font_size'] ?? '', $fs); ?>><?php echo $fs; ?>px</option>
                        <?php endfor; ?>
                    </select>
                </td></tr>
                <tr><th scope="row">Takip Adı</th><td><input name="tracking_name" type="text" value="<?php echo esc_attr($button['tracking_name'] ?? ''); ?>" class="regular-text" /></td></tr>
            </table>
            <p class="submit"><input type="submit" class="button-primary" value="Kaydet"></p>
        </form>
        <?php
    }
    
    public function save_button() {
        if (!current_user_can('manage_options') || !check_admin_referer('esistenze_smart_button_save')) {
            wp_die('Yetkiniz yok.');
        }

        $buttons = get_option('esistenze_smart_custom_buttons', []);
        $button_data = array(
            'group' => sanitize_text_field($_POST['group'] ?? ''),
            'type' => sanitize_text_field($_POST['type'] ?? ''),
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'value' => sanitize_text_field($_POST['value'] ?? ''),
            'message' => sanitize_text_field($_POST['message'] ?? ''),
            'button_color_start' => sanitize_hex_color($_POST['button_color_start'] ?? '#000000'),
            'button_color_end' => sanitize_hex_color($_POST['button_color_end'] ?? '#000000'),
            'text_color' => sanitize_hex_color($_POST['text_color'] ?? '#ffffff'),
            'icon' => sanitize_text_field($_POST['icon'] ?? ''),
            'font_size' => intval($_POST['font_size'] ?? 14),
            'tracking_name' => sanitize_text_field($_POST['tracking_name'] ?? ''),
        );

        if (isset($_POST['button_index'])) {
            $buttons[$_POST['button_index']] = $button_data;
        } else {
            $buttons[] = $button_data;
        }

        update_option('esistenze_smart_custom_buttons', $buttons);
        wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons'));
        exit;
    }

    public function delete_button() {
        if (!current_user_can('manage_options') || !isset($_GET['index']) || !check_admin_referer('esistenze_smart_button_delete_' . $_GET['index'])) {
            wp_die('Yetkiniz yok.');
        }
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        unset($buttons[$_GET['index']]);
        update_option('esistenze_smart_custom_buttons', array_values($buttons));
        wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons'));
        exit;
    }
    
    public function enqueue_public_assets() {
        wp_enqueue_style('esistenze-smart-buttons-css', ESISTENZE_WP_KIT_URL . 'modules/smart-product-buttons/assets/style.css', array(), ESISTENZE_WP_KIT_VERSION);
        wp_enqueue_script('esistenze-smart-buttons-js', ESISTENZE_WP_KIT_URL . 'modules/smart-product-buttons/assets/script.js', array('jquery'), ESISTENZE_WP_KIT_VERSION, true);
    }

    public function render_buttons_on_product_page() {
        static $rendered = false;
        if ($rendered) return;

        $buttons = get_option('esistenze_smart_custom_buttons', []);
        if (empty($buttons)) return;

        ob_start();
        $rendered_form_trigger = false;

        echo '<div class="esistenze-smart-buttons-frontend">';
        foreach ($buttons as $index => $button) {
            $type = $button['type'] ?? '';
            if ($type === 'form_trigger' && $rendered_form_trigger) {
                continue;
            }

            $title = $button['title'] ?? '';
            $value = $button['value'] ?? '';
            $message = $button['message'] ?? '';
            $color1 = $button['button_color_start'] ?? '#ff0000';
            $color2 = $button['button_color_end'] ?? '#940000';
            $text_color = $button['text_color'] ?? '#fff';
            $font_size = intval($button['font_size'] ?? 16);
            $icon = $button['icon'] ?? '';
            $track = $button['tracking_name'] ?? ($type === 'form_trigger' ? 'form_teklif_al' : ($type . '_button_' . sanitize_title($title)));
            $data = 'data-track="' . esc_attr($track) . '" data-id="' . esc_attr($value) . '"';
            $icon_html = $icon ? '<i class="fa ' . esc_attr($icon) . '"></i>' : '';
            $style = 'style="background: linear-gradient(45deg, ' . esc_attr($color1) . ', ' . esc_attr($color2) . ') !important; color: ' . esc_attr($text_color) . ' !important; font-size: ' . min($font_size, 16) . 'px !important; padding: 10px 20px !important; box-shadow: 0 6px 15px rgba(' . $this->hexToRgb($color1) . ', 0.4) !important;"';

            switch ($type) {
                case 'phone':
                    echo '<a href="tel:' . esc_attr($value) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'mail':
                    echo '<a href="mailto:' . esc_attr($value) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'whatsapp':
                    $url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $value);
                    if ($message) $url .= '?text=' . urlencode($message);
                    echo '<a target="_blank" href="' . esc_url($url) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'form_trigger':
                    $shortcode = '[contact-form-7 id="' . esc_attr($value) . '" title="Fiyat Teklifi"]';
                    $rendered_shortcode = do_shortcode($shortcode);
                    if (!empty($rendered_shortcode)) {
                        echo '<button type="button" class="esistenze-smart-btn esistenze-form-popup-trigger" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . $title . '</button>';
                        $rendered_form_trigger = true;
                        echo '<div id="esistenze-form-modal-content" style="display:none;">' . $rendered_shortcode . '</div>';
                    }
                    break;
            }
        }
        echo '</div>';

        $output = ob_get_clean();
        if (!empty($output)) {
            echo $output;
            $rendered = true;
        }
    }

    public function render_modal_container() {
        echo '<div id="esistenze-form-modal" class="esistenze-smart-modal"><div class="esistenze-smart-modal-content"><span class="esistenze-smart-close">×</span><div class="esistenze-smart-form-container"></div></div></div>';
    }
    
    private function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "$r, $g, $b";
    }
}

// Initialize the module
EsistenzeSmartButtons::getInstance();