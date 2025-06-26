<?php
/**
 * Quick Menu Cards Module
 * Yeniden yazılmış, basit ve çalışır versiyon
 * 
 * @package Esistenze WordPress Kit
 * @subpackage Quick Menu Cards
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quick Menu Cards Ana Sınıfı
 */
class EsistenzeQuickMenuCards {
    
    private static $instance = null;
    private $module_path;
    private $module_url;
    private $version = '2.0.0';
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->module_path = plugin_dir_path(__FILE__);
        $this->module_url = plugin_dir_url(__FILE__);
        
        $this->init();
    }
    
    /**
     * Modülü başlat
     */
    private function init() {
        // Sınıfları yükle
        $this->load_classes();
        
        // Hook'ları ekle
        $this->add_hooks();
        
        // Debug log
        if (function_exists('qmc_log_error')) {
            qmc_log_error('Quick Menu Cards modülü başlatıldı', array(
                'version' => $this->version,
                'path' => $this->module_path,
                'url' => $this->module_url
            ));
        }
    }
    
    /**
     * Gerekli sınıfları yükle
     */
    private function load_classes() {
        $classes = array(
            'admin' => 'includes/class-admin.php',
            'frontend' => 'includes/class-frontend.php',
            'shortcodes' => 'includes/class-shortcodes.php',
            'ajax' => 'includes/class-ajax.php'
        );
        
        foreach ($classes as $name => $file) {
            $file_path = $this->module_path . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
                
                if (function_exists('qmc_log_error')) {
                    qmc_log_error("QMC sınıf yüklendi: $name", array('file' => $file));
                }
            } else {
                if (function_exists('qmc_log_error')) {
                    qmc_log_error("QMC sınıf dosyası bulunamadı: $name", array(
                        'expected_file' => $file_path,
                        'exists' => false
                    ));
                }
            }
        }
    }
    
    /**
     * WordPress hook'larını ekle
     */
    private function add_hooks() {
        // Admin paneli
        if (is_admin()) {
            if (class_exists('EsistenzeQuickMenuCardsAdmin')) {
                new EsistenzeQuickMenuCardsAdmin($this->module_path, $this->module_url);
            }
            
            if (class_exists('EsistenzeQuickMenuCardsAjax')) {
                new EsistenzeQuickMenuCardsAjax();
            }
        }
        
        // Frontend
        if (class_exists('EsistenzeQuickMenuCardsFrontend')) {
            new EsistenzeQuickMenuCardsFrontend($this->module_url);
        }
        
        // Shortcodes
        if (class_exists('EsistenzeQuickMenuCardsShortcodes')) {
            new EsistenzeQuickMenuCardsShortcodes();
        }
        
        // Aktivasyon/Deaktivasyon
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Modül aktivasyonu
     */
    public function activate() {
        // Default ayarları oluştur
        $default_settings = $this->get_default_settings();
        if (!get_option('esistenze_quick_menu_settings')) {
            add_option('esistenze_quick_menu_settings', $default_settings);
        }
        
        // Boş kart verisi oluştur
        if (!get_option('esistenze_quick_menu_kartlari')) {
            add_option('esistenze_quick_menu_kartlari', array());
        }
        
        if (function_exists('qmc_log_error')) {
            qmc_log_error('Quick Menu Cards modülü aktive edildi');
        }
    }
    
    /**
     * Modül deaktivasyonu
     */
    public function deactivate() {
        // Cache temizle
        wp_cache_delete('qmc_cards_cache');
        delete_transient('qmc_cards_transient');
        
        if (function_exists('qmc_log_error')) {
            qmc_log_error('Quick Menu Cards modülü deaktive edildi');
        }
    }
    
    /**
     * Default ayarları döndür
     */
    public static function get_default_settings() {
        return array(
            'default_button_text' => 'Detayları Gör',
            'banner_button_text' => 'Keşfet',
            'enable_animations' => true,
            'enable_lazy_loading' => false,
            'enable_analytics' => false,
            'cache_duration' => 3600,
            'custom_css' => '',
            'grid_columns' => 3,
            'card_spacing' => 20,
            'border_radius' => 8,
            'show_descriptions' => true,
            'show_images' => true
        );
    }
    
    /**
     * Modül bilgilerini döndür
     */
    public function get_module_info() {
        return array(
            'name' => 'Quick Menu Cards',
            'version' => $this->version,
            'description' => 'Görsel menü kartları oluşturun',
            'path' => $this->module_path,
            'url' => $this->module_url
        );
    }
    
    /**
     * Kart verilerini al
     */
    public static function get_cards($group_id = null) {
        $cards = get_option('esistenze_quick_menu_kartlari', array());
        
        if ($group_id !== null) {
            return isset($cards[$group_id]) ? $cards[$group_id] : null;
        }
        
        return $cards;
    }
    
    /**
     * Kart verilerini kaydet
     */
    public static function save_cards($cards) {
        return update_option('esistenze_quick_menu_kartlari', $cards);
    }
    
    /**
     * Ayarları al
     */
    public static function get_settings() {
        $defaults = self::get_default_settings();
        $settings = get_option('esistenze_quick_menu_settings', array());
        
        return wp_parse_args($settings, $defaults);
    }
    
    /**
     * Ayarları kaydet
     */
    public static function save_settings($settings) {
        return update_option('esistenze_quick_menu_settings', $settings);
    }
}

// Modülü başlat
if (class_exists('EsistenzeQuickMenuCards')) {
    EsistenzeQuickMenuCards::getInstance();
}
?>
