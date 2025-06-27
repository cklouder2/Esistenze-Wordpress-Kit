<?php
/**
 * Smart Product Buttons Module
 * WooCommerce akıllı ürün butonları modülü
 */

if (!defined('ABSPATH')) exit;

class EsistenzaSmartProductButtons {
    
    private static $rendered = false;
    private static $modal_content = '';
    
    public function __construct() {
        // WooCommerce aktif mi kontrolü
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Admin hooks
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_post_smart_button_save', array($this, 'save_button'));
        add_action('admin_post_smart_button_delete', array($this, 'delete_button'));
        
        // Frontend hooks
        add_action('woocommerce_product_meta_end', array($this, 'render_buttons_on_product_page'), 10, 0);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_modal_container'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }
    
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>Akıllı Ürün Butonları:</strong> Bu modül WooCommerce gerektirir.</p></div>';
    }

    public function admin_menu() {
        add_submenu_page(
            'esistenze-toolkit',
            'Akıllı Ürün Butonları',
            'Ürün Butonları',
            'manage_options',
            'smart-product-buttons',
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        $buttons = get_option('smart_custom_buttons', []);
        ?>
        <div class="wrap">
            <h1>🔘 Akıllı Ürün Butonları</h1>
            <a href="<?php echo admin_url('admin.php?page=smart-product-buttons&new=1'); ?>" class="button button-primary">Yeni Buton Ekle</a>
            
            <?php if (isset($_GET['edit']) && isset($buttons[$_GET['edit']])): ?>
                <?php $this->render_button_form($buttons[$_GET['edit']], $_GET['edit']); ?>
            <?php elseif (isset($_GET['new'])): ?>
                <?php $this->render_button_form([], null); ?>
            <?php else: ?>
                <table class="widefat">
                    <thead><tr><th>Başlık</th><th>Tür</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php foreach ($buttons as $index => $button): ?>
                        <tr>
                            <td><?php echo esc_html($button['title'] ?? '-'); ?></td>
                            <td><?php echo esc_html($button['type'] ?? '-'); ?></td>
                            <td>
                                <a class="button" href="<?php echo admin_url('admin.php?page=smart-product-buttons&edit=' . $index); ?>">Düzenle</a>
                                <a class="button delete-button" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=smart_button_delete&index=' . $index), 'smart_button_delete_' . $index); ?>" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_button_form($button, $index = null) {
        ?>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="smart_button_save">
            <?php if ($index !== null): ?><input type="hidden" name="button_index" value="<?php echo esc_attr($index); ?>"><?php endif; ?>
            <?php wp_nonce_field('smart_button_save'); ?>
            <table class="form-table">
                <tr><th>Tür</th><td>
                    <select name="type" required>
                        <option value="phone" <?php selected($button['type'] ?? '', 'phone'); ?>>Telefon</option>
                        <option value="mail" <?php selected($button['type'] ?? '', 'mail'); ?>>Mail</option>
                        <option value="whatsapp" <?php selected($button['type'] ?? '', 'whatsapp'); ?>>WhatsApp</option>
                        <option value="form_trigger" <?php selected($button['type'] ?? '', 'form_trigger'); ?>>Form</option>
                    </select>
                </td></tr>
                <tr><th>Başlık</th><td><input name="title" type="text" value="<?php echo esc_attr($button['title'] ?? ''); ?>" required /></td></tr>
                <tr><th>Değer</th><td><input name="value" type="text" value="<?php echo esc_attr($button['value'] ?? ''); ?>" required /></td></tr>
                <tr><th>Mesaj</th><td><input name="message" type="text" value="<?php echo esc_attr($button['message'] ?? ''); ?>" /></td></tr>
                <tr><th>Başlangıç Rengi</th><td><input name="button_color_start" type="color" value="<?php echo esc_attr($button['button_color_start'] ?? '#4CAF50'); ?>" /></td></tr>
                <tr><th>Bitiş Rengi</th><td><input name="button_color_end" type="color" value="<?php echo esc_attr($button['button_color_end'] ?? '#45a049'); ?>" /></td></tr>
                <tr><th>Yazı Rengi</th><td><input name="text_color" type="color" value="<?php echo esc_attr($button['text_color'] ?? '#ffffff'); ?>" /></td></tr>
                <tr><th>İkon</th><td><input name="icon" type="text" value="<?php echo esc_attr($button['icon'] ?? ''); ?>" placeholder="fa-phone" /></td></tr>
            </table>
            <p><input type="submit" class="button-primary" value="Kaydet"></p>
        </form>
        <?php
    }

    public function save_button() {
        if (!current_user_can('manage_options') || !check_admin_referer('smart_button_save')) {
            wp_die('Yetkiniz yok.');
        }

        $buttons = get_option('smart_custom_buttons', []);
        $button_data = array(
            'type' => sanitize_text_field($_POST['type'] ?? ''),
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'value' => sanitize_text_field($_POST['value'] ?? ''),
            'message' => sanitize_text_field($_POST['message'] ?? ''),
            'button_color_start' => sanitize_hex_color($_POST['button_color_start'] ?? '#4CAF50'),
            'button_color_end' => sanitize_hex_color($_POST['button_color_end'] ?? '#45a049'),
            'text_color' => sanitize_hex_color($_POST['text_color'] ?? '#ffffff'),
            'icon' => sanitize_text_field($_POST['icon'] ?? ''),
            'font_size' => 14,
            'tracking_name' => sanitize_text_field($_POST['title'] ?? ''),
        );

        if (isset($_POST['button_index'])) {
            $buttons[$_POST['button_index']] = $button_data;
        } else {
            $buttons[] = $button_data;
        }

        update_option('smart_custom_buttons', $buttons);
        wp_redirect(admin_url('admin.php?page=smart-product-buttons'));
        exit;
    }

    public function delete_button() {
        if (!current_user_can('manage_options') || !isset($_GET['index']) || !check_admin_referer('smart_button_delete_' . $_GET['index'])) {
            wp_die('Yetkiniz yok.');
        }
        $buttons = get_option('smart_custom_buttons', []);
        unset($buttons[$_GET['index']]);
        update_option('smart_custom_buttons', array_values($buttons));
        wp_redirect(admin_url('admin.php?page=smart-product-buttons'));
        exit;
    }

    public function enqueue_assets() {
        if (!is_product()) return;
        
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
        
        wp_enqueue_style(
            'esistenza-smart-product-buttons-style',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/smart-product-buttons.css',
            array('woocommerce-general'),
            ESISTENZE_TOOLKIT_VERSION
        );
        
        wp_enqueue_script(
            'esistenza-smart-product-buttons-js',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/js/smart-product-buttons.js',
            array('jquery'),
            ESISTENZE_TOOLKIT_VERSION,
            true
        );
    }

    public function enqueue_admin_styles($hook) {
        // Sadece plugin sayfalarında yükle
        if (strpos($hook, 'smart-product-buttons') === false) {
            return;
        }
        
        wp_enqueue_style(
            'esistenza-smart-product-buttons-admin-style',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/dashboard-admin.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
    }

    public function render_buttons_on_product_page() {
        if (self::$rendered) return;

        $buttons = get_option('smart_custom_buttons', []);
        if (empty($buttons)) return;

        echo '<div class="smart-buttons-frontend">';
        foreach ($buttons as $button) {
            $type = $button['type'] ?? '';
            $title = $button['title'] ?? '';
            $value = $button['value'] ?? '';
            $message = $button['message'] ?? '';
            $color1 = $button['button_color_start'] ?? '#4CAF50';
            $color2 = $button['button_color_end'] ?? '#45a049';
            $text_color = $button['text_color'] ?? '#fff';
            $icon = $button['icon'] ?? '';
            $icon_html = $icon ? '<i class="fa ' . esc_attr($icon) . '"></i>' : '';
            $style = 'style="background: linear-gradient(45deg, ' . esc_attr($color1) . ', ' . esc_attr($color2) . '); color: ' . esc_attr($text_color) . ';"';

            switch ($type) {
                case 'phone':
                    echo '<a href="tel:' . esc_attr($value) . '" class="smart-btn" ' . $style . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'mail':
                    echo '<a href="mailto:' . esc_attr($value) . '" class="smart-btn" ' . $style . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'whatsapp':
                    $url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $value);
                    if ($message) $url .= '?text=' . urlencode($message);
                    echo '<a target="_blank" href="' . esc_url($url) . '" class="smart-btn" ' . $style . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'form_trigger':
                    if (function_exists('wpcf7_contact_form')) {
                        $shortcode = '[contact-form-7 id="' . esc_attr($value) . '"]';
                        self::$modal_content = do_shortcode($shortcode);
                        echo '<button class="smart-btn form-popup-trigger" ' . $style . '>' . $icon_html . ' ' . $title . '</button>';
                    }
                    break;
            }
        }
        echo '</div>';
        self::$rendered = true;
    }

    public function render_modal_container() {
        if (!is_product()) return;
        echo '<div id="smart-form-modal" class="smart-modal"><div class="smart-modal-content"><span class="smart-close">×</span><div class="smart-form-container"></div></div></div>';
        if (!empty(self::$modal_content)) {
            echo '<div id="smart-form-modal-content" style="display:none;">' . self::$modal_content . '</div>';
        }
    }
}

// Modülü başlat
new EsistenzaSmartProductButtons(); 