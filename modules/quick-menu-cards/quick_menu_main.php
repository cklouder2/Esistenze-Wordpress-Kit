<?php
/*
 * Quick Menu Cards Module - Ana Dosya
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCards {
    
    private static $instance = null;
    private $module_path;
    private $module_url;
    
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
        
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        // Frontend sınıfını yükle
        require_once $this->module_path . 'includes/class-frontend.php';
        
        // Admin paneli sadece admin area'da yükle
        if (is_admin()) {
            require_once $this->module_path . 'includes/class-admin.php';
        }
        
        // AJAX handler'ları yükle
        require_once $this->module_path . 'includes/class-ajax.php';
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        
        // Frontend'i başlat
        new EsistenzeQuickMenuCardsFrontend($this->module_url);
        
        // Admin paneli başlat (sadece admin area'da)
        if (is_admin()) {
            new EsistenzeQuickMenuCardsAdmin($this->module_path, $this->module_url);
        }
        
        // AJAX handler'ları başlat
        new EsistenzeQuickMenuCardsAjax();
    }
    
    public function init() {
        // Genel başlatma işlemleri
        $this->register_shortcodes();
        $this->schedule_cleanup();
    }
    
    private function register_shortcodes() {
        add_shortcode('quick_menu_cards', array($this, 'render_shortcode'));
        add_shortcode('quick_menu_banner', array($this, 'render_banner_shortcode'));
        
        // Geriye uyumluluk için eski shortcode'lar
        add_shortcode('hizli_menu', array($this, 'render_shortcode'));
        add_shortcode('hizli_menu_banner', array($this, 'render_banner_shortcode'));
    }
    
    public function render_shortcode($atts) {
        $frontend = new EsistenzeQuickMenuCardsFrontend($this->module_url);
        return $frontend->render_cards_grid($atts);
    }
    
    public function render_banner_shortcode($atts) {
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
    
    public static function get_default_settings() {
        return array(
            'default_button_text' => 'Detayları Gör',
            'banner_button_text' => 'Ürünleri İncele',
            'enable_analytics' => true,
            'enable_lazy_loading' => true,
            'enable_schema_markup' => true,
            'cache_duration' => 3600
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
                    'url' => '#'
                ),
                array(
                    'title' => 'Örnek Kart 2',
                    'desc' => 'Bu ikinci örnek kart açıklamasıdır.',
                    'img' => '',
                    'url' => '#'
                )
            );
            
            update_option('esistenze_quick_menu_kartlari', array($default_group));
        }
        
        // Varsayılan ayarlar
        $settings = get_option('esistenze_quick_menu_settings', array());
        if (empty($settings)) {
            update_option('esistenze_quick_menu_settings', self::get_default_settings());
        }
    }
    
    // Deactivation hook
    public static function deactivate() {
        wp_clear_scheduled_hook('esistenze_quick_menu_cleanup');
    }
}

// Initialize
add_action('plugins_loaded', function() {
    EsistenzeQuickMenuCards::getInstance();
});

// Hooks
register_activation_hook(__FILE__, array('EsistenzeQuickMenuCards', 'activate'));
register_deactivation_hook(__FILE__, array('EsistenzeQuickMenuCards', 'deactivate'));

?>