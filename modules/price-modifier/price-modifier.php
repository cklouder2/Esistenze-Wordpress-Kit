<?php
/*
 * Price Modifier Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzePriceModifier {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('woocommerce_single_product_summary', array($this, 'modify_product_price'), 15);
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        register_setting('esistenze_price_modifier', 'esistenze_price_modifier_settings');
    }
    
    public function modify_product_price() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $settings = get_option('esistenze_price_modifier_settings', array());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        $custom_text = isset($settings['custom_text']) ? $settings['custom_text'] : '';
        $text_color = isset($settings['text_color']) ? $settings['text_color'] : '#333';
        $font_size = isset($settings['font_size']) ? $settings['font_size'] : '14';
        
        if (!empty($custom_text)) {
            echo '<div class="esistenze-price-modifier" style="color: ' . esc_attr($text_color) . '; font-size: ' . esc_attr($font_size) . 'px; margin: 10px 0;">';
            echo esc_html($custom_text);
            echo '</div>';
        }
    }
    
    public static function admin_page() {
        if (!current_user_can(esistenze_qmc_capability())) {
            wp_die(__('Bu sayfaya erişmenize izin verilmiyor.', 'esistenze-wp-kit'));
        }
        
        $settings = get_option('esistenze_price_modifier_settings', array());
        
        // Default values
        $defaults = array(
            'enabled' => false,
            'custom_text' => '',
            'text_color' => '#333333',
            'font_size' => 14
        );
        
        $settings = array_merge($defaults, $settings);
        ?>
        <div class="wrap">
            <h1><?php _e('Price Modifier Ayarları', 'esistenze-wp-kit'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('esistenze_price_modifier'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php _e('Eklentiyi Etkinleştir', 'esistenze-wp-kit'); ?></th>
                        <td>
                            <input type="checkbox" name="esistenze_price_modifier_settings[enabled]" value="1" <?php checked($settings['enabled']); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="custom_text"><?php _e('Özel Metin', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="text" id="custom_text" name="esistenze_price_modifier_settings[custom_text]" value="<?php echo esc_attr($settings['custom_text']); ?>" class="regular-text" />
                            <p class="description"><?php _e('Fiyat altında gösterilecek özel metin.', 'esistenze-wp-kit'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="text_color"><?php _e('Metin Rengi', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="color" id="text_color" name="esistenze_price_modifier_settings[text_color]" value="<?php echo esc_attr($settings['text_color']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="font_size"><?php _e('Yazı Boyutu', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="number" class="small-text" id="font_size" name="esistenze_price_modifier_settings[font_size]" value="<?php echo esc_attr($settings['font_size']); ?>" /> px
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <h2><?php _e('Nasıl Kullanılır', 'esistenze-wp-kit'); ?></h2>
            <p><?php _e('Bu modül WooCommerce ürün sayfalarında fiyat bilgisinin altına özel metin ekler.', 'esistenze-wp-kit'); ?></p>
            <p><?php _e('Örnek kullanım alanları:', 'esistenze-wp-kit'); ?></p>
            <ul>
                <li><?php _e('KDV dahil/hariç bilgisi', 'esistenze-wp-kit'); ?></li>
                <li><?php _e('Ücretsiz kargo bilgisi', 'esistenze-wp-kit'); ?></li>
                <li><?php _e('İndirim bilgisi', 'esistenze-wp-kit'); ?></li>
                <li><?php _e('Ödeme seçenekleri', 'esistenze-wp-kit'); ?></li>
            </ul>
        </div>
        <?php
    }
}

// Initialize the class
EsistenzePriceModifier::getInstance();