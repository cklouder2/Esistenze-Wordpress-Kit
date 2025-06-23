<?php
/*
 * Category Styler Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class EsistenzeCategoryStyler {
    
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
        // Register shortcode
        add_shortcode('esistenze_display_categories', array($this, 'display_styled_categories'));
        
        // Enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 999);
        
        // Admin init
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public static function admin_page() {
        if (isset($_POST['submit'])) {
            update_option('esistenze_category_styler_enabled', isset($_POST['enable_category_styler']));
            update_option('esistenze_hide_price_hover', isset($_POST['hide_price_hover']));
            update_option('esistenze_custom_category_css', sanitize_textarea_field($_POST['custom_css']));
            echo '<div class="notice notice-success"><p>Ayarlar kaydedildi!</p></div>';
        }
        
        $enabled = get_option('esistenze_category_styler_enabled', true);
        $hide_price_hover = get_option('esistenze_hide_price_hover', true);
        $custom_css = get_option('esistenze_custom_category_css', '');
        
        ?>
        <div class="wrap">
            <h1>Category Styler Ayarları</h1>
            <p>WooCommerce kategorilerini ve ürünlerini modern bir görünümle stilize eder.</p>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Category Styler'ı Etkinleştir</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_category_styler" <?php checked($enabled); ?> />
                                Kategori stillendirmesini etkinleştir
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Price Hover'ı Gizle</th>
                        <td>
                            <label>
                                <input type="checkbox" name="hide_price_hover" <?php checked($hide_price_hover); ?> />
                                Kategori sayfalarında price-hover-wrap elementini gizle
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Özel CSS</th>
                        <td>
                            <textarea name="custom_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
                            <p class="description">Kategori stillerine eklemek istediğiniz özel CSS kodlarını buraya ekleyin.</p>
                        </td>
                    </tr>
                </table>
                
                <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                    <h3>Kısa Kodlar:</h3>
                    <code>[esistenze_display_categories]</code> - Stilize edilmiş kategorileri grid görünümde gösterir
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    public function register_settings() {
        register_setting('esistenze_category_styler', 'esistenze_category_styler_enabled');
        register_setting('esistenze_category_styler', 'esistenze_hide_price_hover');
        register_setting('esistenze_category_styler', 'esistenze_custom_category_css');
    }
    
    public function display_styled_categories($atts) {
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => 0,
        );
        $categories = get_terms($args);

        if (empty($categories)) {
            return '<p>Hiç kategori bulunamadı.</p>';
        }

        ob_start();
        ?>
        <div class="esistenze-category-styler-grid">
            <?php foreach ($categories as $category) : ?>
                <div class="esistenze-category-styler-item">
                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                        <div class="esistenze-category-styler-image" style="background-image: url('<?php echo esc_url(wp_get_attachment_url(get_term_meta($category->term_id, 'thumbnail_id', true))); ?>');"></div>
                        <h3 class="esistenze-category-styler-title"><?php echo esc_html($category->name); ?></h3>
                        <p class="esistenze-category-styler-description"><?php echo esc_html($category->description); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function enqueue_styles() {
        if (!get_option('esistenze_category_styler_enabled', true)) {
            return;
        }
        
        wp_enqueue_style('esistenze-category-styler', ESISTENZE_WP_KIT_URL . 'modules/category-styler/assets/style.css', array(), ESISTENZE_WP_KIT_VERSION);
        
        // Add custom CSS
        $custom_css = get_option('esistenze_custom_category_css', '');
        if (!empty($custom_css)) {
            wp_add_inline_style('esistenze-category-styler', $custom_css);
        }
        
        // Hide price hover if enabled
        if (get_option('esistenze_hide_price_hover', true)) {
            $hide_css = '.woocommerce-products-header ~ .products .price-hover-wrap { display: none !important; }';
            wp_add_inline_style('esistenze-category-styler', $hide_css);
        }
    }
}

// Initialize the module
EsistenzeCategoryStyler::getInstance();