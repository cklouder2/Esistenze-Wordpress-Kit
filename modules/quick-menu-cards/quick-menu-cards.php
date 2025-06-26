<?php
/*
 * Quick Menu Cards Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('EsistenzeQuickMenuCards')) {
class EsistenzeQuickMenuCards {
    private static $instance = null;
    private $admin;
    private $frontend;
    private $shortcodes;
    private $ajax;
    private $module_path;
    private $module_url;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->module_path = plugin_dir_path(__FILE__);
        $this->module_url = plugin_dir_url(__FILE__);
        $this->load_dependencies();
        $this->init_classes();
    }

    private function load_dependencies() {
        require_once $this->module_path . 'includes/class-admin.php';
        require_once $this->module_path . 'includes/class-frontend.php';
        require_once $this->module_path . 'includes/class-shortcodes.php';
        require_once $this->module_path . 'includes/class-ajax.php';
    }

    private function init_classes() {
        $this->admin = new EsistenzeQuickMenuCardsAdmin($this->module_path, $this->module_url);
        $this->frontend = new EsistenzeQuickMenuCardsFrontend($this->module_url);
        $this->shortcodes = new EsistenzeQuickMenuCardsShortcodes($this->frontend);
        $this->ajax = new EsistenzeQuickMenuCardsAjax();
    }

    public static function get_default_settings() {
        return array(
            'default_button_text' => 'Detayları Gör',
            'banner_button_text' => 'Ürünleri İncele',
            'cache_duration' => 3600,
            'enable_lazy_loading' => true,
            'enable_analytics' => true,
            'custom_css' => '',
        );
    }
}
}

// Modül başlatıcı
add_action('plugins_loaded', function() {
    EsistenzeQuickMenuCards::getInstance();
});
