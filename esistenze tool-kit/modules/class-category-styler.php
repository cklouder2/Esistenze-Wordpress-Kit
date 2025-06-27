<?php
/**
 * Category Styler Module
 * WooCommerce kategori stilleyici modülü
 */

if (!defined('ABSPATH')) exit;

class EsistenzaCategoryStyler {
    
    public function __construct() {
        // WooCommerce aktif mi kontrolü
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        add_action('admin_menu', array($this, 'admin_menu'));
        add_shortcode('display_categories', array($this, 'display_styled_categories'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }
    
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>Kategori Stilleyici:</strong> Bu modül WooCommerce gerektirir.</p></div>';
    }

    public function admin_menu() {
        add_submenu_page(
            'esistenze-toolkit',
            'Kategori Stilleyici',
            'Kategori Stilleyici',
            'manage_options',
            'category-styler',
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        ?>
        <div class="wrap esistenza-category-styler">
            <div class="esistenza-module-header">
                <h1>🎨 Kategori Stilleyici</h1>
                <p class="description">WooCommerce kategorilerinizi modern ve lüks bir görünümle stilleyin.</p>
            </div>
            
            <div class="form-container">
                <h3>Özellikleri:</h3>
                <ul>
                    <li>✅ Kategori ızgarası görüntüleme</li>
                    <li>✅ Sidebar menü stilleri</li>
                    <li>✅ Sayfa başlığı düzenleme</li>
                    <li>✅ Ürün kartları stilizasyonu</li>
                    <li>✅ Otomatik responsive tasarım</li>
                </ul>
                
                <h3>Kullanım:</h3>
                <p>Kategorileri grid formatında göstermek için kısa kod kullanın:</p>
                <div class="shortcode-highlight">
                    <code>[display_categories]</code>
                </div>
                
                <h3>Özelleştirmeler:</h3>
                <p>Bu modül otomatik olarak aşağıdaki alanları stilleyecektir:</p>
                <ul>
                    <li><strong>Kategoriler:</strong> Modern grid düzeni</li>
                    <li><strong>Ürünler:</strong> Gelişmiş kart tasarımı</li>
                    <li><strong>Sidebar:</strong> #nav_menu-7 ve #nav_menu-3 widget'ları</li>
                    <li><strong>Sayfa Başlıkları:</strong> #page-header-wrap alanı</li>
                </ul>
            </div>
            
            <div class="status-container">
                <h3>✅ Durum: Aktif</h3>
                <p>Kategori stilleyici modülü aktif ve çalışıyor. Herhangi bir ayar gerekmez.</p>
            </div>
        </div>
        <?php
    }

    public function display_styled_categories() {
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
        <div class="category-styler-grid">
            <?php foreach ($categories as $category) : ?>
                <div class="category-styler-item">
                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                        <div class="category-styler-image" data-bg-url="<?php echo esc_url(wp_get_attachment_url(get_term_meta($category->term_id, 'thumbnail_id', true))); ?>"></div>
                        <h3 class="category-styler-title"><?php echo esc_html($category->name); ?></h3>
                        <p class="category-styler-description"><?php echo esc_html($category->description); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'esistenza-category-styler-style',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/category-styler.css',
            array('woocommerce-general'),
            ESISTENZE_TOOLKIT_VERSION
        );
        
        // Dinamik background image için JS
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                $(".category-styler-image[data-bg-url]").each(function() {
                    var bgUrl = $(this).data("bg-url");
                    if (bgUrl) {
                        $(this).css("background-image", "url(" + bgUrl + ")");
                    }
                });
            });
        ');
    }

    public function enqueue_admin_styles($hook) {
        // Sadece plugin sayfalarında yükle
        if (strpos($hook, 'category-styler') === false) {
            return;
        }
        
        wp_enqueue_style(
            'esistenza-category-styler-admin-style',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/category-styler.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
    }
}

// Modülü başlat
new EsistenzaCategoryStyler(); 