<?php
/*
Plugin Name: Esistenze WordPress Kit
Description: Kapsamlı WordPress eklenti paketi - Smart Product Buttons, Category Styler, Custom Topbar ve Price Modifier modüllerini içerir.
Version: 2.1.0
Author: Cem Karabulut - Esistenze
Text Domain: esistenze-wp-kit
Domain Path: /languages
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Network: false
License: MIT
License URI: https://opensource.org/licenses/MIT
*/

if (!defined('ABSPATH')) {
    exit; // Direct access prevention
}

// Plugin constants. Make sure they are defined only once to avoid warnings
if (!defined('ESISTENZE_WP_KIT_VERSION')) {
    define('ESISTENZE_WP_KIT_VERSION', '2.1.0');
}
if (!defined('ESISTENZE_WP_KIT_PATH')) {
    define('ESISTENZE_WP_KIT_PATH', plugin_dir_path(__FILE__));
}
if (!defined('ESISTENZE_WP_KIT_URL')) {
    define('ESISTENZE_WP_KIT_URL', plugin_dir_url(__FILE__));
}

// Prevent class redeclaration if the plugin is included twice
if (!class_exists('EsistenzeWPKit')) {
class EsistenzeWPKit {
    
    private static $instance = null;
    private $loaded_modules = array();
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Error logging için hook ekleyelim
        add_action('admin_notices', array($this, 'display_admin_notices'));
        
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load textdomain for translations
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Core functionality
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('esistenze-wp-kit', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function init() {
        // Load migration handler
        $this->load_migration_handler();
        
        // Load modules in a safe way
        $this->safe_load_modules();
        
        // Admin menu
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
    }
    
    private function load_migration_handler() {
        $migration_file = ESISTENZE_WP_KIT_PATH . 'includes/class-migration.php';
        if (file_exists($migration_file)) {
            require_once $migration_file;
        }
        
        // Load base module class and traits
        $base_module_file = ESISTENZE_WP_KIT_PATH . 'includes/class-base-module.php';
        if (file_exists($base_module_file)) {
            require_once $base_module_file;
        }
    }
    
    private function safe_load_modules() {
        try {
            // Plugin dizinini dinamik olarak tespit et
            $plugin_dir = plugin_dir_path(__FILE__);
            $modules_path = $plugin_dir . 'modules/';

            // Modülleri ve ana sınıflarını tanımla
            $modules_to_load = array(
                'smart-product-buttons' => 'EsistenzeSmartButtons',
                'category-styler' => 'EsistenzeCategoryStyler',
                'custom-topbar' => 'EsistenzeCustomTopbar',
                'price-modifier' => 'EsistenzePriceModifier'
            );

            // Modules dizininin var olup olmadığını kontrol et
            if (!is_dir($modules_path)) {
                $this->loaded_modules['error'] = 'Modules directory not found: ' . $modules_path;
                return;
            }

            // Gerçek klasör adlarını bulmak için modules dizinini tara
            $module_folders = glob($modules_path . '*', GLOB_ONLYDIR);
            
            if (empty($module_folders)) {
                $this->loaded_modules['error'] = 'No module folders found in: ' . $modules_path;
                return;
            }

            // Gerçek klasör adlarını küçük harfli halleriyle eşleştir
            $actual_folder_map = array();
            foreach($module_folders as $full_path) {
                $folder_name = basename($full_path);
                $actual_folder_map[strtolower($folder_name)] = $folder_name;
            }

            foreach ($modules_to_load as $module_key => $class_name) {
                try {
                    // Eşleşen klasör adını bul
                    if (isset($actual_folder_map[$module_key])) {
                        $actual_folder = $actual_folder_map[$module_key];
                        // Dosya adını da modül anahtarıyla eşleştir (genellikle aynıdır)
                        $module_file = $modules_path . $actual_folder . '/' . $module_key . '.php';
                    } else {
                        $this->loaded_modules[$module_key] = 'Klasör bulunamadı (aranan: ' . $module_key . ')';
                        continue;
                    }
                    
                    if (file_exists($module_file) && is_readable($module_file)) {
                        // Dosya güvenli bir şekilde include et
                        include_once $module_file;
                        
                        // Check if class exists before initializing
                        if (class_exists($class_name)) {
                            // Add to loaded modules for debugging
                            $this->loaded_modules[$module_key] = true;
                        } else {
                            // Log error if class doesn't exist
                            $this->loaded_modules[$module_key] = 'Class not found: ' . $class_name;
                        }
                    } else {
                        // Log error if file doesn't exist or isn't readable
                        $this->loaded_modules[$module_key] = 'File not found or not readable: ' . $module_file;
                    }
                } catch (Exception $module_error) {
                    $this->loaded_modules[$module_key] = 'Module error: ' . $module_error->getMessage();
                }
            }
        } catch (Exception $e) {
            // Log exception for debugging
            $this->loaded_modules['error'] = 'General error: ' . $e->getMessage();
        }
    }
    
    public function display_admin_notices() {
        // Show notices for module loading errors
        $error_count = 0;
        $error_messages = array();
        
        foreach ($this->loaded_modules as $module => $status) {
            if ($status !== true) {
                $error_count++;
                $error_messages[] = $module . ': ' . $status;
            }
        }
        
        if ($error_count > 0) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>Esistenze WordPress Kit:</strong> ' . $error_count . ' modül yüklenemedi.</p>';
            echo '<ul>';
            foreach ($error_messages as $message) {
                echo '<li>' . esc_html($message) . '</li>';
            }
            echo '</ul>';
            echo '<p>Eklentiyi güncelleyin veya tekrar yükleyin. Sorun devam ederse destek ekibiyle iletişime geçin.</p>';
            echo '</div>';
        }
    }
    
    public function admin_menu() {
        // Admin paneli için güvenli yetki seviyesi - modüllerle uyumlu olması için manage_options kullanıyoruz
        $cap = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        
        // Main menu page
        add_menu_page(
            'Esistenze WP Kit',
            'Esistenze Kit',
            $cap,
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
            $cap,
            'esistenze-wp-kit',
            array($this, 'admin_dashboard')
        );
        
        // Module submenus are now registered by modules themselves in their init() methods
        // This provides better encapsulation and allows for more flexible menu management
    }
    
    public function admin_dashboard() {
        ?>
        <div class="wrap esistenze-dashboard">
            <h1><?php _e('Esistenze WordPress Kit Dashboard', 'esistenze-wp-kit'); ?></h1>
            
            <div class="esistenze-welcome-panel">
                <h2><?php _e('Hoş Geldiniz!', 'esistenze-wp-kit'); ?></h2>
                <p><?php _e('Esistenze WordPress Kit, web sitenizi güçlendirmek için tasarlanmış 4 farklı modülü içerir.', 'esistenze-wp-kit'); ?></p>
                <p><?php _e('Version:', 'esistenze-wp-kit'); ?> <?php echo ESISTENZE_WP_KIT_VERSION; ?></p>
            </div>
            
            <div class="esistenze-modules-grid">
                <div class="module-card">
                    <h3><span class="dashicons dashicons-button"></span> <?php _e('Smart Product Buttons', 'esistenze-wp-kit'); ?></h3>
                    <p><?php _e('WooCommerce ürün sayfalarında özelleştirilebilir, animasyonlu butonlar ekler.', 'esistenze-wp-kit'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons'); ?>" class="button button-primary"><?php _e('Ayarlar', 'esistenze-wp-kit'); ?></a>
                    <div class="module-status <?php echo (isset($this->loaded_modules['smart-product-buttons']) && $this->loaded_modules['smart-product-buttons'] === true) ? 'active' : 'inactive'; ?>">
                        <?php echo (isset($this->loaded_modules['smart-product-buttons']) && $this->loaded_modules['smart-product-buttons'] === true) ? __('Aktif', 'esistenze-wp-kit') : __('Devre Dışı', 'esistenze-wp-kit'); ?>
                    </div>
                </div>
                
                <div class="module-card">
                    <h3><span class="dashicons dashicons-category"></span> <?php _e('Category Styler', 'esistenze-wp-kit'); ?></h3>
                    <p><?php _e('WooCommerce kategorilerini modern ve lüks bir görünümle stilize eder.', 'esistenze-wp-kit'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-category-styler'); ?>" class="button button-primary"><?php _e('Ayarlar', 'esistenze-wp-kit'); ?></a>
                    <div class="module-status <?php echo (isset($this->loaded_modules['category-styler']) && $this->loaded_modules['category-styler'] === true) ? 'active' : 'inactive'; ?>">
                        <?php echo (isset($this->loaded_modules['category-styler']) && $this->loaded_modules['category-styler'] === true) ? __('Aktif', 'esistenze-wp-kit') : __('Devre Dışı', 'esistenze-wp-kit'); ?>
                    </div>
                </div>
                
                <div class="module-card">
                    <h3><span class="dashicons dashicons-admin-customizer"></span> <?php _e('Custom Topbar', 'esistenze-wp-kit'); ?></h3>
                    <p><?php _e('Site üstüne özelleştirilebilir menü ve iletişim bilgileri çubuğu ekler.', 'esistenze-wp-kit'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-custom-topbar'); ?>" class="button button-primary"><?php _e('Ayarlar', 'esistenze-wp-kit'); ?></a>
                    <div class="module-status <?php echo (isset($this->loaded_modules['custom-topbar']) && $this->loaded_modules['custom-topbar'] === true) ? 'active' : 'inactive'; ?>">
                        <?php echo (isset($this->loaded_modules['custom-topbar']) && $this->loaded_modules['custom-topbar'] === true) ? __('Aktif', 'esistenze-wp-kit') : __('Devre Dışı', 'esistenze-wp-kit'); ?>
                    </div>
                </div>
                
                <div class="module-card">
                    <h3><span class="dashicons dashicons-tag"></span> <?php _e('Price Modifier', 'esistenze-wp-kit'); ?></h3>
                    <p><?php _e('WooCommerce ürün fiyatlarına özel notlar ve stiller ekler.', 'esistenze-wp-kit'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-price-modifier'); ?>" class="button button-primary"><?php _e('Ayarlar', 'esistenze-wp-kit'); ?></a>
                    <div class="module-status <?php echo (isset($this->loaded_modules['price-modifier']) && $this->loaded_modules['price-modifier'] === true) ? 'active' : 'inactive'; ?>">
                        <?php echo (isset($this->loaded_modules['price-modifier']) && $this->loaded_modules['price-modifier'] === true) ? __('Aktif', 'esistenze-wp-kit') : __('Devre Dışı', 'esistenze-wp-kit'); ?>
                    </div>
                </div>
            </div>
            
            <div class="esistenze-info-panel">
                <h3><?php _e('Kısa Kodlar ve Kullanım', 'esistenze-wp-kit'); ?></h3>
                <div class="shortcode-list">
                    <code>[display_categories]</code> - <?php _e('Stilize edilmiş kategorileri gösterir', 'esistenze-wp-kit'); ?>
                </div>
            </div>
        </div>
        
        <style>
        .esistenze-dashboard {
            background: #f5f5f5;
            padding: 20px;
            max-width: 1400px;
        }
        
        .esistenze-welcome-panel {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #4CAF50;
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        .module-card h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.2em;
        }
        
        .module-card .dashicons {
            color: #4CAF50;
            margin-right: 8px;
            font-size: 20px;
        }
        
        .module-card p {
            color: #666;
            line-height: 1.6;
            margin: 10px 0 15px;
        }
        
        .esistenze-info-panel {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .esistenze-info-panel {
            border-left: 4px solid #2196F3;
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
            font-weight: 600;
        }
        
        .module-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .module-status.active {
            background: #e8f5e8;
            color: #388e3c;
        }
        
        .module-status.inactive {
            background: #ffebee;
            color: #d32f2f;
        }
        </style>
        <?php
    }
    
    public function admin_assets($hook) {
        if (!empty($hook) && strpos($hook, 'esistenze') !== false) {
            wp_enqueue_style('esistenze-admin-style', ESISTENZE_WP_KIT_URL . 'assets/admin.css', array(), ESISTENZE_WP_KIT_VERSION);
            wp_enqueue_script('esistenze-admin-script', ESISTENZE_WP_KIT_URL . 'assets/admin.js', array('jquery', 'jquery-ui-sortable'), ESISTENZE_WP_KIT_VERSION, true);
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

}

/**
 * Get capability for quality management control
 * @return string
 */
function esistenze_qmc_capability() {
    return 'manage_options'; // WordPress admin capability
}
