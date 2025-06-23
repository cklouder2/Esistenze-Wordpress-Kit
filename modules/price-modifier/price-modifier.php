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
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Admin hooks
        add_action('admin_init', array($this, 'register_settings'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 999);
        
        // Replace default price with custom price
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
        add_action('woocommerce_single_product_summary', array($this, 'custom_price_with_note'), 10);
    }
    
    public static function admin_page() {
        if (isset($_POST['submit'])) {
            update_option('esistenze_price_modifier_enabled', isset($_POST['enable_price_modifier']));
            update_option('esistenze_price_note', sanitize_textarea_field($_POST['price_note']));
            update_option('esistenze_price_note_color', sanitize_hex_color($_POST['note_color']));
            update_option('esistenze_price_bg_color', sanitize_hex_color($_POST['bg_color']));
            update_option('esistenze_price_border_color', sanitize_hex_color($_POST['border_color']));
            echo '<div class="notice notice-success"><p>Ayarlar kaydedildi!</p></div>';
        }
        
        $enabled = get_option('esistenze_price_modifier_enabled', true);
        $price_note = get_option('esistenze_price_note', '1000 adet için liste fiyatıdır. Özel fiyat için bizimle iletişime geçin.');
        $note_color = get_option('esistenze_price_note_color', '#e74c3c');
        $bg_color = get_option('esistenze_price_bg_color', '#e6f3e6');
        $border_color = get_option('esistenze_price_border_color', '#4CAF50');
        
        ?>
        <div class="wrap">
            <h1>Price Modifier Ayarları</h1>
            <p>WooCommerce ürün fiyatlarına özel notlar ve stiller ekler.</p>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Price Modifier'ı Etkinleştir</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_price_modifier" <?php checked($enabled); ?> />
                                Fiyat modifikasyonunu etkinleştir
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price_note">Fiyat Notu</label></th>
                        <td>
                            <textarea name="price_note" id="price_note" rows="3" class="large-text"><?php echo esc_textarea($price_note); ?></textarea>
                            <p class="description">Fiyatın yanında gösterilecek özel not.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="note_color">Not Rengi</label></th>
                        <td>
                            <input type="color" name="note_color" id="note_color" value="<?php echo esc_attr($note_color); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bg_color">Arka Plan Rengi</label></th>
                        <td>
                            <input type="color" name="bg_color" id="bg_color" value="<?php echo esc_attr($bg_color); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="border_color">Çerçeve Rengi</label></th>
                        <td>
                            <input type="color" name="border_color" id="border_color" value="<?php echo esc_attr($border_color); ?>">
                        </td>
                    </tr>
                </table>
                
                <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                    <h3>Önizleme:</h3>
                    <div style="display: flex; align-items: center; gap: 20px; background: <?php echo esc_attr($bg_color); ?>; padding: 15px 20px; border-radius: 10px; border: 2px solid <?php echo esc_attr($border_color); ?>;">
                        <span style="font-size: 1.8em; color: #2c3e50; font-weight: 700;">₺199.90</span>
                        <span style="font-size: 14px; color: <?php echo esc_attr($note_color); ?>; background-color: #ffffff; padding: 10px 15px; border-radius: 6px; border-left: 6px solid <?php echo esc_attr($note_color); ?>;"><?php echo esc_html($price_note); ?></span>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    public function register_settings() {
        register_setting('esistenze_price_modifier', 'esistenze_price_modifier_enabled');
        register_setting('esistenza_price_modifier', 'esistenze_price_note');
        register_setting('esistenze_price_modifier', 'esistenze_price_note_color');
        register_setting('esistenze_price_modifier', 'esistenze_price_bg_color');
        register_setting('esistenze_price_modifier', 'esistenze_price_border_color');
    }
    
    public function enqueue_styles() {
        if (!get_option('esistenze_price_modifier_enabled', true)) {
            return;
        }
        
        $note_color = get_option('esistenze_price_note_color', '#e74c3c');
        $bg_color = get_option('esistenze_price_bg_color', '#e6f3e6');
        $border_color = get_option('esistenze_price_border_color', '#4CAF50');
        
        $custom_css = '
            .esistenze-price-modifier-wrapper {
                display: flex;
                align-items: center;
                gap: 20px;
                margin: 20px 0;
                background: ' . esc_attr($bg_color) . ';
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                border: 2px solid ' . esc_attr($border_color) . ';
            }
            .esistenze-price-modifier-price {
                font-size: 1.8em;
                color: #2c3e50;
                font-weight: 700;
                font-family: "Montserrat", sans-serif;
            }
            .esistenze-price-modifier-note {
                font-size: 14px;
                color: ' . esc_attr($note_color) . ';
                background-color: #ffffff;
                padding: 10px 15px;
                border-radius: 6px;
                font-family: "Roboto", sans-serif;
                font-weight: 500;
                border-left: 6px solid ' . esc_attr($note_color) . ';
                box-shadow: 0 2px 10px rgba(' . $this->hexToRgb($note_color) . ', 0.1);
                transition: all 0.3s ease;
            }
            .esistenze-price-modifier-note:hover {
                background-color: #ffebee;
                transform: translateY(-2px);
            }
            .woocommerce-product-details__short-description {
                margin-top: 20px;
                font-size: 1.1em;
                color: #7f8c8d;
                line-height: 1.6;
            }
            @media (max-width: 768px) {
                .esistenze-price-modifier-wrapper {
                    flex-direction: column;
                    text-align: center;
                    gap: 15px;
                }
                .esistenze-price-modifier-price {
                    font-size: 1.5em;
                }
                .esistenze-price-modifier-note {
                    font-size: 13px;
                }
            }
        ';
        wp_add_inline_style('woocommerce-inline', $custom_css);
    }

    public function custom_price_with_note() {
        if (!get_option('esistenze_price_modifier_enabled', true)) {
            woocommerce_template_single_price();
            return;
        }
        
        global $product;
        if ($product && $product->get_price()) {
            $price_note = get_option('esistenze_price_note', '1000 adet için liste fiyatıdır. Özel fiyat için bizimle iletişime geçin.');
            $price_html = wc_price($product->get_price());
            echo '<div class="esistenze-price-modifier-wrapper">';
            echo '<span class="esistenze-price-modifier-price">' . $price_html . '</span>';
            echo '<span class="esistenze-price-modifier-note">' . esc_html($price_note) . '</span>';
            echo '</div>';
        }
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
EsistenzePriceModifier::getInstance();