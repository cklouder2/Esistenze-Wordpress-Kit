<?php
/*
 * Quick Menu Cards Module - Ana Dosya (Düzeltilmiş)
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('esistenze_qmc_capability')) {
    function esistenze_qmc_capability() {
        // Daha esnek yetkilendirme sistemi
        $capability = apply_filters('esistenze_quick_menu_capability', 'edit_pages');
        
        // Fallback kontrolleri
        if (!current_user_can($capability)) {
            if (current_user_can('manage_options')) {
                return 'manage_options';
            } elseif (current_user_can('edit_pages')) {
                return 'edit_pages';
            } elseif (current_user_can('edit_posts')) {
                return 'edit_posts';
            }
        }
        
        return $capability;
    }
}

class EsistenzeQuickMenuCards {
    
    private static $instance = null;
    private $module_path;
    private $module_url;
    private $capability;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->module_path = defined('ESISTENZE_WP_KIT_PATH') 
            ? ESISTENZE_WP_KIT_PATH . 'modules/quick-menu-cards/' 
            : plugin_dir_path(__FILE__);
            
        $this->module_url = defined('ESISTENZE_WP_KIT_URL') 
            ? ESISTENZE_WP_KIT_URL . 'modules/quick-menu-cards/' 
            : plugin_dir_url(__FILE__);
        
        // Hook'ları başlat
        add_action('init', array($this, 'early_init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_loaded', array($this, 'late_init'));
    }
    
    public function early_init() {
        // Capability'yi erkenden belirle
        $this->capability = esistenze_qmc_capability();
        
        // Shortcode'ları kaydet
        $this->register_shortcodes();
    }
    
    public function admin_init() {
        // Admin sınıfını yükle ve başlat
        if (is_admin() && current_user_can($this->capability)) {
            $this->load_admin_classes();
        }
    }
    
    public function late_init() {
        // Frontend ve AJAX handler'ları yükle
        $this->load_dependencies();
        $this->schedule_cleanup();
    }
    
    private function load_dependencies() {
        // Frontend sınıfını yükle
        if (file_exists($this->module_path . 'includes/class-frontend.php')) {
            require_once $this->module_path . 'includes/class-frontend.php';
            new EsistenzeQuickMenuCardsFrontend($this->module_url);
        }
        
        // AJAX handler'ları yükle
        if (file_exists($this->module_path . 'includes/class-ajax.php')) {
            require_once $this->module_path . 'includes/class-ajax.php';
            new EsistenzeQuickMenuCardsAjax();
        }
    }
    
    private function load_admin_classes() {
        if (file_exists($this->module_path . 'includes/class-admin.php')) {
            require_once $this->module_path . 'includes/class-admin.php';
            new EsistenzeQuickMenuCardsAdmin($this->module_path, $this->module_url);
        }
    }
    
    private function register_shortcodes() {
        add_shortcode('quick_menu_cards', array($this, 'render_shortcode'));
        add_shortcode('quick_menu_banner', array($this, 'render_banner_shortcode'));
        
        // Geriye uyumluluk için eski shortcode'lar
        add_shortcode('hizli_menu', array($this, 'render_shortcode'));
        add_shortcode('hizli_menu_banner', array($this, 'render_banner_shortcode'));
    }
    
    public function render_shortcode($atts) {
        if (!class_exists('EsistenzeQuickMenuCardsFrontend')) {
            return '<p>Quick Menu Cards modülü yüklenemedi.</p>';
        }
        
        $frontend = new EsistenzeQuickMenuCardsFrontend($this->module_url);
        return $frontend->render_cards_grid($atts);
    }
    
    public function render_banner_shortcode($atts) {
        if (!class_exists('EsistenzeQuickMenuCardsFrontend')) {
            return '<p>Quick Menu Cards modülü yüklenemedi.</p>';
        }
        
        $frontend = new EsistenzeQuickMenuCardsFrontend($this->module_url);
        return $frontend->render_banner_layout($atts);
    }
    
    private function schedule_cleanup() {
        if (!wp_next_scheduled('esistenze_quick_menu_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'esistenze_quick_menu_cleanup');
        }
        add_action('esistenze_quick_menu_cleanup', array($this, 'cleanup_old_data'));
    }
    
    public function cleanup_old_data() {
        // Eski analytics verilerini temizle (90 günden eski)
        $retention_days = 90;
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        // Cleanup işlemleri burada yapılabilir
        do_action('esistenze_quick_menu_cleanup', $cutoff_date);
    }
    
    // Utility methods
    public function get_module_path() {
        return $this->module_path;
    }
    
    public function get_module_url() {
        return $this->module_url;
    }
    
    public function get_version() {
        return defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0';
    }
    
    public function get_capability() {
        return $this->capability;
    }
    
    public static function get_default_settings() {
        return array(
            'default_button_text' => 'Detayları Gör',
            'banner_button_text' => 'Ürünleri İncele',
            'enable_analytics' => true,
            'enable_lazy_loading' => true,
            'enable_schema_markup' => true,
            'cache_duration' => 3600,
            'mobile_columns' => 2,
            'max_cards_per_group' => 20,
            'auto_save' => true,
            'debug_mode' => false
        );
    }
    
    // Activation hook
    public static function activate() {
        // Varsayılan grup oluştur
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        if (empty($kartlar)) {
            $default_group = array(
                array(
                    'title' => 'Örnek Kart 1',
                    'desc' => 'Bu bir örnek kart açıklamasıdır.',
                    'img' => '',
                    'url' => '#',
                    'enabled' => true,
                    'featured' => false,
                    'new_tab' => false,
                    'order' => 0,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'title' => 'Örnek Kart 2',
                    'desc' => 'Bu ikinci örnek kart açıklamasıdır.',
                    'img' => '',
                    'url' => '#',
                    'enabled' => true,
                    'featured' => false,
                    'new_tab' => false,
                    'order' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                )
            );
            
            update_option('esistenze_quick_menu_kartlari', array($default_group));
        }
        
        // Varsayılan ayarlar
        $settings = get_option('esistenze_quick_menu_settings', array());
        if (empty($settings)) {
            update_option('esistenze_quick_menu_settings', self::get_default_settings());
        }
        
        // Analytics tablosu oluştur
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        if (empty($analytics)) {
            update_option('esistenze_quick_menu_analytics', array(
                'total_views' => 0,
                'total_clicks' => 0,
                'group_views' => array(),
                'group_clicks' => array(),
                'card_clicks' => array(),
                'last_view' => '',
                'last_click' => '',
                'click_details' => array()
            ));
        }
    }
    
    // Deactivation hook
    public static function deactivate() {
        wp_clear_scheduled_hook('esistenze_quick_menu_cleanup');
    }
    
    // Debug fonksiyonu
    public function debug_info() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Quick Menu Cards Debug: Capability = ' . $this->capability);
            error_log('Quick Menu Cards Debug: Current User Can = ' . (current_user_can($this->capability) ? 'YES' : 'NO'));
            error_log('Quick Menu Cards Debug: Is Admin = ' . (is_admin() ? 'YES' : 'NO'));
        }
    }
}

// Initialize
add_action('plugins_loaded', function() {
    EsistenzeQuickMenuCards::getInstance();
}, 10);

// Hooks
register_activation_hook(__FILE__, array('EsistenzeQuickMenuCards', 'activate'));
register_deactivation_hook(__FILE__, array('EsistenzeQuickMenuCards', 'deactivate'));

