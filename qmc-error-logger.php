<?php
/**
 * Quick Menu Cards - Hata Yakalama Sistemi
 */

if (!defined('ABSPATH')) {
    exit;
}

class QMCErrorLogger {
    
    private static $instance = null;
    private $log_file;
    private $errors = array();
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/qmc-debug.log';
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // PHP hata yakalama
        set_error_handler(array($this, 'handle_php_error'));
        
        // WordPress hata yakalama
        add_action('wp_die_handler', array($this, 'handle_wp_die'));
        
        // Admin notices
        add_action('admin_notices', array($this, 'show_admin_notices'));
        
        // AJAX hata yakalama
        add_action('wp_ajax_qmc_get_errors', array($this, 'ajax_get_errors'));
    }
    
    public function log_error($message, $context = array()) {
        $timestamp = current_time('Y-m-d H:i:s');
        $user = wp_get_current_user();
        
        $error_data = array(
            'timestamp' => $timestamp,
            'message' => $message,
            'context' => $context,
            'user_id' => $user->ID,
            'user_login' => $user->user_login,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        );
        
        // Memory'de sakla
        $this->errors[] = $error_data;
        
        // Dosyaya yaz
        $log_entry = sprintf(
            "[%s] QMC ERROR: %s | User: %s | URL: %s\n",
            $timestamp,
            $message,
            $user->user_login,
            $_SERVER['REQUEST_URI'] ?? ''
        );
        
        if (is_writable(dirname($this->log_file))) {
            file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        }
        
        // WordPress debug.log'a da yaz
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('QMC: ' . $message);
        }
    }
    
    public function handle_php_error($severity, $message, $file, $line) {
        // Sadece QMC ile ilgili hataları yakala
        if (strpos($file, 'quick-menu-cards') !== false || strpos($file, 'esistenze') !== false) {
            $error_msg = sprintf('PHP Error [%s]: %s in %s:%d', $severity, $message, $file, $line);
            $this->log_error($error_msg, array('type' => 'php_error', 'severity' => $severity));
        }
        
        // Orijinal error handler'ı çağır
        return false;
    }
    
    public function handle_wp_die($message) {
        if (is_string($message) && (strpos($message, 'quick') !== false || strpos($message, 'esistenze') !== false)) {
            $this->log_error('WordPress Die: ' . $message, array('type' => 'wp_die'));
        }
        return $message;
    }
    
    public function show_admin_notices() {
        // Sadece QMC sayfalarında göster
        $current_screen = get_current_screen();
        if (!$current_screen || strpos($current_screen->id, 'esistenze') === false) {
            return;
        }
        
        // Son 5 dakikadaki hataları göster
        $recent_errors = $this->get_recent_errors(5);
        
        if (!empty($recent_errors)) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<h4>🚨 QMC Debug: Son Hatalar</h4>';
            echo '<ul>';
            foreach (array_slice($recent_errors, -3) as $error) {
                echo '<li><strong>' . esc_html($error['timestamp']) . ':</strong> ' . esc_html($error['message']) . '</li>';
            }
            echo '</ul>';
            echo '<p><a href="' . admin_url('admin.php?page=esistenze-qmc-debug') . '">Tüm Hataları Görüntüle</a></p>';
            echo '</div>';
        }
    }
    
    public function get_recent_errors($minutes = 60) {
        $cutoff_time = time() - ($minutes * 60);
        $recent_errors = array();
        
        foreach ($this->errors as $error) {
            $error_time = strtotime($error['timestamp']);
            if ($error_time >= $cutoff_time) {
                $recent_errors[] = $error;
            }
        }
        
        return $recent_errors;
    }
    
    public function ajax_get_errors() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Yetkisiz erişim');
        }
        
        $errors = $this->get_recent_errors(60);
        wp_send_json_success($errors);
    }
    
    public function get_log_file_content() {
        if (file_exists($this->log_file) && is_readable($this->log_file)) {
            return file_get_contents($this->log_file);
        }
        return false;
    }
    
    public function clear_log() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        $this->errors = array();
        return true;
    }
    
    public function test_qmc_functionality() {
        $test_results = array();
        
        // 1. Plugin aktiflik testi
        $active_plugins = get_option('active_plugins', array());
        $plugin_active = false;
        foreach ($active_plugins as $plugin) {
            if (strpos($plugin, 'esistenze') !== false) {
                $plugin_active = true;
                break;
            }
        }
        $test_results['plugin_active'] = $plugin_active;
        
        // 2. Sınıf varlık testi
        $test_results['classes'] = array(
            'EsistenzeWPKit' => class_exists('EsistenzeWPKit'),
            'EsistenzeQuickMenuCards' => class_exists('EsistenzeQuickMenuCards'),
            'EsistenzeQuickMenuCardsAdmin' => class_exists('EsistenzeQuickMenuCardsAdmin')
        );
        
        // 3. Fonksiyon testi
        $test_results['functions'] = array(
            'esistenze_qmc_capability' => function_exists('esistenze_qmc_capability')
        );
        
        // 4. Yetki testi
        if (function_exists('esistenze_qmc_capability')) {
            $required_cap = esistenze_qmc_capability();
            $test_results['capability'] = array(
                'required' => $required_cap,
                'user_has' => current_user_can($required_cap)
            );
        }
        
        // 5. Menü testi
        global $submenu;
        $test_results['menu'] = array(
            'esistenze_menu_exists' => isset($submenu['esistenze-wp-kit']),
            'qmc_submenu_exists' => false
        );
        
        if (isset($submenu['esistenze-wp-kit'])) {
            foreach ($submenu['esistenze-wp-kit'] as $item) {
                if (strpos($item[2], 'quick-menu') !== false) {
                    $test_results['menu']['qmc_submenu_exists'] = true;
                    break;
                }
            }
        }
        
        return $test_results;
    }
}

// Global fonksiyon
function qmc_log_error($message, $context = array()) {
    QMCErrorLogger::getInstance()->log_error($message, $context);
}

// Başlat
if (is_admin()) {
    QMCErrorLogger::getInstance();
}
?> 