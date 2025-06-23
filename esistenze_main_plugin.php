<?php
/*
Plugin Name: Esistenze WordPress Kit
Description: Kapsamlı WordPress eklenti paketi - Smart Product Buttons, Category Styler, Custom Topbar, Hızlı Menü Kartları ve Price Modifier modüllerini içerir.
Version: 1.0.0
Author: Cem Karabulut - Esistenze
Text Domain: esistenze-wp-kit
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Direct access prevention
}

// Plugin constants
define('ESISTENZE_WP_KIT_VERSION', '1.0.0');
define('ESISTENZE_WP_KIT_PATH', plugin_dir_path(__FILE__));
define('ESISTENZE_WP_KIT_URL', plugin_dir_url(__FILE__));

class EsistenzeWPKit {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load modules
        $this->load_modules();
        
        // Admin menu
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
    }
    
    private function load_modules() {
        // Smart Product Buttons Module
        require_once ESISTENZE_WP_KIT_PATH . 'modules/smart-product-buttons/smart-product-buttons.php';
        
        // Category Styler Module
        require_once ESISTENZE_WP_KIT_PATH . 'modules/category-styler/category-styler.php';
        
        // Custom Topbar Module
        require_once ESISTENZE_WP_KIT_PATH . 'modules/custom-topbar/custom-topbar.php';
        
        // Quick Menu Cards Module
        require_once ESISTENZE_WP_KIT_PATH . 'modules/quick-menu-cards/quick-menu-cards.php';
        
        // Price Modifier Module
        require_once ESISTENZE_WP_KIT_PATH . 'modules/price-modifier/price-modifier.php';
    }
    
    public function admin_menu() {
        // Main menu page
        add_menu_page(
            'Esistenze WP Kit',
            'Esistenze Kit',
            'manage_options',
            'esistenze-wp-kit',
            array($this, 'admin_dashboard'),
            'dashicons-admin-tools',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'esistenze-wp-kit',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'esistenze-wp-kit',
            array($this, 'admin_dashboard')
        );
        
        // Module submenus
        add_submenu_page(
            'esistenze-wp-kit',
            'Smart Buttons',
            'Smart Buttons',
            'manage_options',
            'esistenze-smart-buttons',
            array('EsistenzeSmartButtons', 'admin_page')
        );
        
        add_submenu_page(
            'esistenze-wp-kit',
            'Category Styler',
            'Category Styler',
            'manage_options',
            'esistenze-category-styler',
            array('EsistenzeCategoryStyler', 'admin_page')
        );
        
        add_submenu_page(
            'esistenze-wp-kit',
            'Custom Topbar',
            'Custom Topbar',
            'manage_options',
            'esistenze-custom-topbar',
            array('EsistenzeCustomTopbar', 'admin_page')
        );
        
        add_submenu_page(
            'esistenze-wp-kit',
            'Quick Menu Cards',
            'Quick Menu Cards',
            'manage_options',
            'esistenze-quick-menu',
            array('EsistenzeQuickMenuCards', 'admin_page')
        );
        
        add_submenu_page(
            'esistenze-wp-kit',
            'Price Modifier',
            'Price Modifier',
            'manage_options',
            'esistenze-price-modifier',
            array('EsistenzePriceModifier', 'admin_page')
        );
    }
    
    public function admin_dashboard() {
        ?>
        <div class="wrap esistenze-dashboard">
            <h1>Esistenze WordPress Kit Dashboard</h1>
            
            <div class="esistenze-welcome-panel">
                <h2>Hoş Geldiniz!</h2>
                <p>Esistenze WordPress Kit, web sitenizi güçlendirmek için tasarlanmış 5 farklı modülü içerir.</p>
            </div>
            
            <div class="esistenze-modules-grid">
                <div class="module-card">
                    <h3><span class="dashicons dashicons-button"></span> Smart Product Buttons</h3>
                    <p>WooCommerce ürün sayfalarında özelleştirilebilir, animasyonlu butonlar ekler.</p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons'); ?>" class="button button-primary">Ayarlar</a>
                </div>
                
                <div class="module-card">
                    <h3><span class="dashicons dashicons-category"></span> Category Styler</h3>
                    <p>WooCommerce kategorilerini modern ve lüks bir görünümle stilize eder.</p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-category-styler'); ?>" class="button button-primary">Ayarlar</a>
                </div>
                
                <div class="module-card">
                    <h3><span class="dashicons dashicons-admin-customizer"></span> Custom Topbar</h3>
                    <p>Site üstüne özelleştirilebilir menü ve iletişim bilgileri çubuğu ekler.</p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-custom-topbar'); ?>" class="button button-primary">Ayarlar</a>
                </div>
                
                <div class="module-card">
                    <h3><span class="dashicons dashicons-grid-view"></span> Quick Menu Cards</h3>
                    <p>Görsel, başlık ve bağlantı içeren modern menü kartları oluşturur.</p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-quick-menu'); ?>" class="button button-primary">Ayarlar</a>
                </div>
                
                <div class="module-card">
                    <h3><span class="dashicons dashicons-tag"></span> Price Modifier</h3>
                    <p>WooCommerce ürün fiyatlarına özel notlar ve stiller ekler.</p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-price-modifier'); ?>" class="button button-primary">Ayarlar</a>
                </div>
            </div>
            
            <div class="esistenze-info-panel">
                <h3>Kısa Kodlar ve Kullanım</h3>
                <div class="shortcode-list">
                    <code>[display_categories]</code> - Stilize edilmiş kategorileri gösterir<br>
                    <code>[hizli_menu id="0"]</code> - Hızlı menü kartlarını ızgara görünümde gösterir<br>
                    <code>[hizli_menu_banner id="0"]</code> - Hızlı menü kartlarını banner görünümde gösterir
                </div>
            </div>
        </div>
        
        <style>
        .esistenze-dashboard {
            background: #f1f1f1;
            padding: 20px;
        }
        
        .esistenze-welcome-panel {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .esistenze-modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .module-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #4CAF50;
        }
        
        .module-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .module-card .dashicons {
            color: #4CAF50;
            margin-right: 8px;
        }
        
        .esistenze-info-panel {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .shortcode-list {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            line-height: 1.8;
        }
        
        .shortcode-list code {
            background: #4CAF50;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            margin-right: 10px;
        }
        </style>
        <?php
    }
    
    public function admin_assets($hook) {
        if (strpos($hook, 'esistenze') !== false) {
            wp_enqueue_style('esistenze-admin-style', ESISTENZE_WP_KIT_URL . 'assets/admin.css', array(), ESISTENZE_WP_KIT_VERSION);
            wp_enqueue_script('esistenze-admin-script', ESISTENZE_WP_KIT_URL . 'assets/admin.js', array('jquery'), ESISTENZE_WP_KIT_VERSION, true);
        }
    }
    
    public function activate() {
        // Activation tasks
        flush_rewrite_rules();
        
        // Set default options
        $default_options = array(
            'esistenze_kit_version' => ESISTENZE_WP_KIT_VERSION,
            'smart_buttons_enabled' => true,
            'category_styler_enabled' => true,
            'custom_topbar_enabled' => true,
            'quick_menu_enabled' => true,
            'price_modifier_enabled' => true
        );
        
        foreach ($default_options as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    public function deactivate() {
        // Deactivation tasks
        flush_rewrite_rules();
    }
}

// Initialize the plugin
EsistenzeWPKit::getInstance();