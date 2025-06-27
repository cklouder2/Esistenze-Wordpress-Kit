<?php
/*
Plugin Name: Esistenze Tool-Kit
Plugin URI: https://esistenze.com
Description: WordPress için kapsamlı araç seti. Hızlı menü kartları, fiyat düzenleyici, akıllı ürün butonları, kategori stilleyici ve özel üst çubuk özelliklerini içerir.
Version: 1.5.1
Author: Cem Karabulut - Esistenze
Author URI: https://esistenze.com
Text Domain: esistenze-toolkit
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Network: false
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Doğrudan erişimi engelle
if (!defined('ABSPATH')) {
    exit;
}

// Plugin sabitleri
define('ESISTENZE_TOOLKIT_VERSION', '1.5.1');
define('ESISTENZE_TOOLKIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ESISTENZE_TOOLKIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ESISTENZE_TOOLKIT_PLUGIN_FILE', __FILE__);

/**
 * Ana Plugin Sınıfı
 */
class EsistenzaToolkit {
    
    private static $instance = null;
    
    /**
     * Singleton pattern
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Plugin başlatma
     */
    public function init() {
        // Dil dosyalarını yükle
        load_plugin_textdomain('esistenze-toolkit', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Önce ana menüyü kaydet (öncelik ile)
        add_action('admin_menu', array($this, 'add_admin_menu'), 5);
        
        // Sonra modülleri yükle (alt menüler ana menüden sonra kayıt olsun)
        add_action('admin_menu', array($this, 'load_modules'), 10);
        
        // Stil ve scriptleri yükle
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Modülleri yükle
     */
    public function load_modules() {
        $modules = array(
            'meta-cards' => ESISTENZE_TOOLKIT_PLUGIN_DIR . 'modules/class-meta-cards.php',
            'price-modifier' => ESISTENZE_TOOLKIT_PLUGIN_DIR . 'modules/class-price-modifier.php',
            'smart-product-buttons' => ESISTENZE_TOOLKIT_PLUGIN_DIR . 'modules/class-smart-product-buttons.php',
            'category-styler' => ESISTENZE_TOOLKIT_PLUGIN_DIR . 'modules/class-category-styler.php',
            'custom-topbar' => ESISTENZE_TOOLKIT_PLUGIN_DIR . 'modules/class-custom-topbar.php'
        );
        
        foreach ($modules as $module => $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
    
    /**
     * Ana admin menüsü
     */
    public function add_admin_menu() {
        // Ana menü
        add_menu_page(
            'Esistenze Tool-Kit',
            'Esistenze Tools',
            'manage_options',
            'esistenze-toolkit',
            array($this, 'admin_dashboard'),
            'dashicons-admin-tools',
            30
        );
        
        // Alt menüler
        add_submenu_page(
            'esistenze-toolkit',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'esistenze-toolkit',
            array($this, 'admin_dashboard')
        );
    }
    
    /**
     * Ana dashboard sayfası
     */
    public function admin_dashboard() {
        ?>
        <div class="wrap esistenza-dashboard">
            <div class="dashboard-header">
                <div class="header-content">
                    <h1>Esistenza Tool-Kit</h1>
                    <span class="version-badge">v<?php echo ESISTENZE_TOOLKIT_VERSION; ?></span>
                </div>
                <p class="header-subtitle">WordPress için modern araç seti</p>
            </div>
            
            <div class="debug-panel">
                 <h4>Debug Bilgileri - Yetki Kontrolü</h4>
                 <div class="debug-grid">
                     <div><strong>Kullanıcı:</strong> <?php echo wp_get_current_user()->display_name; ?></div>
                     <div><strong>Admin Yetkisi:</strong> <?php echo current_user_can('manage_options') ? '✅ Aktif' : '❌ Pasif'; ?></div>
                     <div><strong>Kullanıcı ID:</strong> <?php echo get_current_user_id(); ?></div>
                     <div><strong>Rol:</strong> <?php echo implode(', ', wp_get_current_user()->roles); ?></div>
                     <div><strong>Super Admin:</strong> <?php echo is_super_admin() ? '✅ Evet' : '❌ Hayır'; ?></div>
                     <div><strong>Blog ID:</strong> <?php echo get_current_blog_id(); ?></div>
                 </div>
                 <?php if (!current_user_can('manage_options')): ?>
                 <div style="margin-top: 15px; padding: 12px; background: #fee; border: 1px solid #fcc; border-radius: 4px; color: #c33;">
                     <strong>⚠️ Yetki Sorunu Tespit Edildi!</strong><br>
                     Bu kullanıcının 'manage_options' yetkisi yok. Lütfen WordPress admin yetkilerini kontrol edin.
                 </div>
                 <?php endif; ?>
             </div>
            
            <div class="modules-grid">
                <div class="module-card" data-module="meta-cards">
                    <div class="card-header">
                        <span class="card-icon">⊞</span>
                        <h3>Menü Kartları</h3>
                    </div>
                    <p>Modern görsel kart grupları oluşturun</p>
                    <a href="<?php echo admin_url('admin.php?page=hizli-menu-karti'); ?>" class="card-button">Düzenle</a>
                </div>
                
                <div class="module-card" data-module="price-modifier">
                    <div class="card-header">
                        <span class="card-icon">₺</span>
                        <h3>Fiyat Düzenleyici</h3>
                    </div>
                    <p>Ürün fiyatlarına özel notlar ekleyin</p>
                    <a href="<?php echo admin_url('admin.php?page=price-modifier'); ?>" class="card-button">Düzenle</a>
                </div>
                
                <div class="module-card" data-module="smart-buttons">
                    <div class="card-header">
                        <span class="card-icon">◉</span>
                        <h3>Akıllı Butonlar</h3>
                    </div>
                    <p>Ürün sayfalarına akıllı butonlar ekleyin</p>
                    <a href="<?php echo admin_url('admin.php?page=smart-product-buttons'); ?>" class="card-button">Düzenle</a>
                </div>
                
                <div class="module-card" data-module="category-styler">
                    <div class="card-header">
                        <span class="card-icon">▦</span>
                        <h3>Kategori Stilleyici</h3>
                    </div>
                    <p>Kategorileri modern tasarımla stilleyin</p>
                    <a href="<?php echo admin_url('admin.php?page=category-styler'); ?>" class="card-button">Ayarlar</a>
                </div>
                
                <div class="module-card" data-module="topbar">
                    <div class="card-header">
                        <span class="card-icon">▬</span>
                        <h3>Üst Çubuk</h3>
                    </div>
                    <p>Özelleştirilebilir üst navigasyon çubuğu</p>
                    <a href="<?php echo admin_url('admin.php?page=xai-top-bar-settings'); ?>" class="card-button">Düzenle</a>
                </div>
                
                                 <div class="module-card info-card" data-module="info">
                     <div class="card-header">
                         <span class="card-icon">i</span>
                         <h3>Sistem Bilgisi</h3>
                     </div>
                     <div class="info-content">
                         <div class="info-row">
                             <span>Versiyon</span>
                             <strong><?php echo ESISTENZE_TOOLKIT_VERSION; ?></strong>
                         </div>
                         <div class="info-row">
                             <span>WordPress</span>
                             <strong><?php echo get_bloginfo('version'); ?></strong>
                         </div>
                         <div class="info-row">
                             <span>PHP</span>
                             <strong><?php echo PHP_VERSION; ?></strong>
                         </div>
                         <div class="info-row">
                             <span>Admin Yetkisi</span>
                             <strong style="color: <?php echo current_user_can('manage_options') ? '#10b981' : '#ef4444'; ?>">
                                 <?php echo current_user_can('manage_options') ? '✅ Aktif' : '❌ Yetersiz'; ?>
                             </strong>
                         </div>
                         <div class="info-row">
                             <span>Destek</span>
                             <a href="https://esistenza.com" target="_blank">esistenza.com</a>
                         </div>
                         <?php if (!current_user_can('manage_options')): ?>
                         <div class="troubleshoot-notice" style="margin-top: 12px; padding: 8px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; font-size: 0.75rem; color: #dc2626;">
                             <strong>Sorun:</strong> Admin yetkisine sahip değilsiniz. Site yöneticisiyle iletişime geçin.
                         </div>
                         <?php endif; ?>
                     </div>
                 </div>
            </div>
        </div>
        
        <style>
        .esistenza-dashboard {
            background: #fafafa;
            margin: 0 -20px -10px 0;
            padding: 32px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .dashboard-header {
            background: #ffffff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 32px;
            text-align: center;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 8px;
        }
        
        .dashboard-header h1 {
            color: #1a202c;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .version-badge {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            color: #4a5568;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .header-subtitle {
            color: #718096;
            font-size: 1rem;
            margin: 0;
        }
        
        .debug-panel {
            background: #fffaf0;
            border: 1px solid #fbd38d;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        
        .debug-panel h4 {
            color: #744210;
            margin: 0 0 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .debug-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
            font-size: 0.875rem;
            color: #744210;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            max-width: 1200px;
        }
        
        .module-card {
            background: #ffffff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 24px;
            transition: border-color 0.2s ease, transform 0.2s ease;
            position: relative;
        }
        
        .module-card:hover {
            border-color: #cbd5e0;
            transform: translateY(-2px);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #4a5568;
            font-weight: 600;
        }
        
        .module-card h3 {
            color: #2d3748;
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .module-card p {
            color: #718096;
            line-height: 1.5;
            margin: 0 0 20px;
            font-size: 0.875rem;
        }
        
        .card-button {
            background: #667eea;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease;
        }
        
        .card-button:hover {
            background: #5a67d8;
            color: #ffffff;
        }
        
        .info-card {
            background: #f8fafc;
            border-color: #e2e8f0;
        }
        
        .info-content {
            font-size: 0.875rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-row span {
            color: #718096;
        }
        
        .info-row strong {
            color: #2d3748;
            font-weight: 600;
        }
        
        .info-row a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .info-row a:hover {
            color: #5a67d8;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .esistenza-dashboard {
                padding: 16px;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                padding: 24px 16px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 8px;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Frontend stil ve scriptleri
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'esistenza-toolkit-public',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/toolkit-public.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
        
        wp_enqueue_script(
            'esistenza-toolkit-public',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/js/toolkit-public.js',
            array('jquery'),
            ESISTENZE_TOOLKIT_VERSION,
            true
        );
    }
    
    /**
     * Admin stil ve scriptleri
     */
    public function enqueue_admin_scripts($hook) {
        // Sadece plugin sayfalarında yükle
        if (strpos($hook, 'esistenze') === false && 
            strpos($hook, 'hizli-menu') === false && 
            strpos($hook, 'price-modifier') === false && 
            strpos($hook, 'smart-product') === false && 
            strpos($hook, 'category-styler') === false &&
            strpos($hook, 'top-bar') === false) {
            return;
        }
        
        wp_enqueue_style(
            'esistenza-toolkit-admin',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/dashboard-admin.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
        
        wp_enqueue_style(
            'esistenza-debug-panel',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/debug-panel.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
        
        wp_enqueue_script(
            'esistenza-toolkit-admin',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/js/dashboard-admin.js',
            array('jquery'),
            ESISTENZE_TOOLKIT_VERSION,
            true
        );
        
        // Media uploader için
        wp_enqueue_media();
    }
    
    /**
     * Plugin aktivasyonu
     */
    public function activate() {
        // Varsayılan ayarları oluştur
        $default_options = array(
            'version' => ESISTENZE_TOOLKIT_VERSION,
            'activated_modules' => array(
                'meta-cards' => true,
                'price-modifier' => true,
                'smart-product-buttons' => true,
                'category-styler' => true,
                'custom-topbar' => true
            )
        );
        
        add_option('esistenza_toolkit_options', $default_options);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deaktivasyonu
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Plugin'i başlat
EsistenzaToolkit::get_instance(); 