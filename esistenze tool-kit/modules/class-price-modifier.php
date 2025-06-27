<?php
/**
 * Price Modifier Module
 * WooCommerce fiyat düzenleyici modülü
 */

if (!defined('ABSPATH')) exit;

class EsistenzaPriceModifier {
    
    public function __construct() {
        // WooCommerce aktif mi kontrolü
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        
        // WooCommerce hooks
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
        add_action('woocommerce_single_product_summary', array($this, 'custom_price_with_note'), 10);
    }
    
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><strong>Fiyat Düzenleyici:</strong> Bu modül WooCommerce eklentisinin aktif olmasını gerektirir.</p>
        </div>
        <?php
    }

    public function admin_menu() {
        add_submenu_page(
            'esistenze-toolkit',
            'Fiyat Düzenleyici',
            'Fiyat Düzenleyici',
            'manage_options',
            'price-modifier',
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        register_setting('price_modifier_options', 'price_note');
    }

    public function settings_page() {
        if (isset($_POST['submit'])) {
            if (!current_user_can('manage_options')) {
                wp_die('Yetkiniz yok.');
            }
            update_option('price_note', sanitize_text_field($_POST['price_note']));
            echo '<div class="updated"><p>Ayarlar kaydedildi!</p></div>';
        }
        
        $price_note = get_option('price_note', '1000 adet için liste fiyatıdır. Özel fiyat için bizimle iletişime geçin.');
        ?>
        <div class="wrap esistenza-price-modifier">
            <div class="esistenza-module-header">
                <h1>💰 Fiyat Düzenleyici</h1>
                <p class="description">WooCommerce ürün sayfalarında fiyatın yanında gösterilecek özel notu ayarlayın.</p>
            </div>
            
            <div class="esistenza-form-container">
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="price_note">Fiyat Notu</label>
                            </th>
                            <td>
                                <textarea 
                                    name="price_note" 
                                    id="price_note" 
                                    rows="3"
                                    cols="50"
                                    class="large-text"
                                    placeholder="Örnek: 1000 adet için liste fiyatıdır. Özel fiyat için bizimle iletişime geçin."
                                ><?php echo esc_textarea($price_note); ?></textarea>
                                <p class="description">
                                    Bu metin WooCommerce ürün sayfalarında fiyatın yanında görüntülenecektir.
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="form-actions">
                        <?php submit_button('Ayarları Kaydet', 'primary', 'submit', false); ?>
                    </div>
                </form>
            </div>
            
            <div class="esistenza-preview-section">
                <h3>Önizleme</h3>
                <div class="price-modifier-wrapper">
                    <span class="price-modifier-price">₺299,00</span>
                    <span class="price-modifier-note"><?php echo esc_html($price_note); ?></span>
                </div>
                <p class="description">Yukarıdaki görünüm ürün sayfalarında nasıl görüneceğinin bir örneğidir.</p>
            </div>
        </div>
        <?php
    }

    public function enqueue_styles() {
        if (!is_product()) return;
        
        wp_enqueue_style(
            'esistenza-price-modifier-style',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/price-modifier.css',
            array('woocommerce-general'),
            ESISTENZE_TOOLKIT_VERSION
        );
    }

    public function enqueue_admin_styles($hook) {
        // Sadece plugin sayfalarında yükle
        if (strpos($hook, 'price-modifier') === false) {
            return;
        }
        
        wp_enqueue_style(
            'esistenza-price-modifier-admin-style',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/price-modifier.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
    }

    public function custom_price_with_note() {
        global $product;
        if ($product && $product->get_price()) {
            $price_note = get_option('price_note', '1000 adet için liste fiyatıdır. Özel fiyat için bizimle iletişime geçin.');
            $price_html = wc_price($product->get_price());
            echo '<div class="price-modifier-wrapper">';
            echo '<span class="price-modifier-price">' . $price_html . '</span>';
            if (!empty($price_note)) {
                echo '<span class="price-modifier-note">' . esc_html($price_note) . '</span>';
            }
            echo '</div>';
        }
    }
}

// Modülü başlat
new EsistenzaPriceModifier(); 