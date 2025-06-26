<?php
/**
 * QMC Advanced Debug Tool
 * Bu araç, Quick Menu Cards modülündeki sorunları tespit etmek için detaylı sistem analizi yapar.
 * Kendi başına çalışabilir ve WordPress ortamını yükler.
 * 
 * @version 2.1.0
 */

// WordPress ortamını yükle - Daha güvenilir yöntem
function find_wordpress_root() {
    $dir = __DIR__;
    $max_attempts = 10; // Sonsuz döngüyü önlemek için
    $attempts = 0;
    
    while ($attempts < $max_attempts) {
        if (file_exists($dir . '/wp-load.php')) {
            return $dir . '/wp-load.php';
        }
        
        // Bir üst dizine çık
        $parent_dir = dirname($dir);
        
        // Eğer kök dizine ulaştıysak dur
        if ($parent_dir === $dir) {
            break;
        }
        
        $dir = $parent_dir;
        $attempts++;
    }
    
    return false;
}

$wp_load_path = find_wordpress_root();
if ($wp_load_path && file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    // Alternatif yolları dene
    $alternative_paths = [
        __DIR__ . '/../../../../wp-load.php',
        __DIR__ . '/../../../wp-load.php',
        __DIR__ . '/../../wp-load.php',
        __DIR__ . '/../wp-load.php',
        __DIR__ . '/wp-load.php',
    ];
    
    $loaded = false;
    foreach ($alternative_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $loaded = true;
            break;
        }
    }
    
    if (!$loaded) {
        echo '<h1>WordPress Yüklenemedi</h1>';
        echo '<p>wp-load.php dosyası bulunamadı. Denenen yollar:</p>';
        echo '<ul>';
        if ($wp_load_path) {
            echo '<li><code>' . htmlspecialchars($wp_load_path) . '</code> (otomatik tespit)</li>';
        }
        foreach ($alternative_paths as $path) {
            echo '<li><code>' . htmlspecialchars(realpath($path) ?: $path) . '</code></li>';
        }
        echo '</ul>';
        echo '<p><strong>Çözüm:</strong> Bu dosyayı WordPress ana dizininde çalıştırın veya eklentinin doğru klasörde olduğundan emin olun.</p>';
        exit;
    }
}

// Güvenlik kontrolü - sadece adminler erişebilir
if (!current_user_can('manage_options')) {
    wp_die('Bu sayfaya erişim yetkiniz yok.');
}

// Hata raporlamayı aç (sadece bu sayfa için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

class QMC_Advanced_Debugger {

    private $results = [];
    private $plugin_dir;
    private $plugin_url;
    private $real_plugin_folder_name;

    public function __construct() {
        // Ana plugin dosyasının yolundan plugin dizinini ve URL'sini bul
        $this->plugin_dir = plugin_dir_path(dirname(__FILE__));
        $this->plugin_url = plugin_dir_url(dirname(__FILE__));
        $this->real_plugin_folder_name = basename($this->plugin_dir);
    }

    public function run_tests() {
        $this->test_environment();
        $this->test_paths_and_constants();
        $this->test_file_structure();
        $this->test_class_loading();
        $this->test_admin_menu();
        $this->test_qmc_admin_instance();
        $this->test_user_capabilities();
        $this->test_database();
        $this->generate_recommendations();
    }

    public function render() {
        echo '<!DOCTYPE html>';
        echo '<html lang="tr">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<title>QMC Gelişmiş Debug</title>';
        $this->render_styles();
        echo '</head>';
        echo '<body>';
        echo '<div class="container">';
        echo '<h1><span class="dashicons dashicons-shield"></span> Quick Menu Cards - Gelişmiş Debug Aracı</h1>';
        echo '<p>Bu araç, QMC modülünün kurulumunu ve yapılandırmasını detaylı bir şekilde analiz eder.</p>';
        
        foreach ($this->results as $group => $data) {
            echo '<div class="test-group">';
            echo '<h2>' . htmlspecialchars($data['title']) . '</h2>';
            if (isset($data['description'])) {
                echo '<p class="description">' . htmlspecialchars($data['description']) . '</p>';
            }
            echo '<table class="results-table">';
            foreach ($data['tests'] as $test) {
                $status_icon = $test['status'] ? '<span class="icon-success">✅</span>' : '<span class="icon-error">❌</span>';
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($test['label']) . '</strong></td>';
                echo '<td><div class="value">' . $test['value'] . '</div></td>';
                echo '<td class="status">' . $status_icon . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    // --- TEST FONKSİYONLARI ---

    private function test_environment() {
        global $wp_version;
        $this->add_test('Ortam', 'WordPress Versiyonu', $wp_version, version_compare($wp_version, '5.8', '>='));
        $this->add_test('Ortam', 'PHP Versiyonu', phpversion(), version_compare(phpversion(), '7.4', '>='));
        $this->add_test('Ortam', 'MySQL Versiyonu', $GLOBALS['wpdb']->db_version(), true);
    }

    private function test_paths_and_constants() {
        $this->add_test('Yollar ve Sabitler', 'Eklenti Klasör Adı (Gerçek)', '<code>' . $this->real_plugin_folder_name . '</code>', true);
        $this->add_test('Yollar ve Sabitler', 'Beklenen Klasör Adı', '<code>esistenze-wordpress-kit</code>', strtolower($this->real_plugin_folder_name) === 'esistenze-wordpress-kit');
        $this->add_test('Yollar ve Sabitler', 'plugin_dir_path', '<code>' . $this->plugin_dir . '</code>', true);
        $this->add_test('Yollar ve Sabitler', 'WP_PLUGIN_DIR', '<code>' . WP_PLUGIN_DIR . '</code>', true);
    }

    private function test_file_structure() {
        $files_to_check = [
            'Ana Plugin Dosyası' => 'esistenze_main_plugin.php',
            'QMC Ana Modül' => 'modules/quick-menu-cards/quick-menu-cards.php',
            'QMC Admin Sınıfı' => 'modules/quick-menu-cards/includes/class-admin.php',
            'QMC Frontend Sınıfı' => 'modules/quick-menu-cards/includes/class-frontend.php',
            'QMC Shortcodes Sınıfı' => 'modules/quick-menu-cards/includes/class-shortcodes.php',
            'QMC AJAX Sınıfı' => 'modules/quick-menu-cards/includes/class-ajax.php',
        ];

        foreach ($files_to_check as $label => $file_path) {
            $full_path = $this->plugin_dir . $file_path;
            $exists = file_exists($full_path);
            $this->add_test('Dosya Yapısı', $label, '<code>' . $full_path . '</code>', $exists);
        }
    }

    private function test_class_loading() {
        // Sınıfların yüklendiğinden emin olmak için ana dosyaları bir kez daha dahil et
        if (file_exists($this->plugin_dir . 'esistenze_main_plugin.php')) include_once($this->plugin_dir . 'esistenze_main_plugin.php');
        if (file_exists($this->plugin_dir . 'modules/quick-menu-cards/quick-menu-cards.php')) include_once($this->plugin_dir . 'modules/quick-menu-cards/quick-menu-cards.php');
        
        $classes_to_check = [
            'EsistenzeWPKit',
            'EsistenzeQuickMenuCards',
            'EsistenzeQuickMenuCardsAdmin',
        ];

        foreach ($classes_to_check as $class) {
            $exists = class_exists($class);
            $this->add_test('Sınıf Yükleme', '<code>' . $class . '</code> sınıfı yüklü mü?', $exists ? 'Evet' : 'Hayır', $exists);
        }
    }
    
    private function test_qmc_admin_instance() {
        if (!class_exists('EsistenzeQuickMenuCardsAdmin')) {
            $this->add_test('QMC Admin Sınıfı Detaylı Test', 'Admin sınıfı instance', 'Sınıf bulunamadığı için test edilemedi.', false);
            return;
        }

        try {
            $module_path = $this->plugin_dir . 'modules/quick-menu-cards/';
            $module_url = $this->plugin_url . 'modules/quick-menu-cards/';
            
            $path_exists = is_dir($module_path);

            $this->add_test('QMC Admin Sınıfı Detaylı Test', 'Module Path', '<code>' . $module_path . '</code>', $path_exists);
            $this->add_test('QMC Admin Sınıfı Detaylı Test', 'Module URL', '<code>' . $module_url . '</code>', true);
            $this->add_test('QMC Admin Sınıfı Detaylı Test', 'Path Exists', $path_exists ? 'Evet' : 'Hayır', $path_exists);

            if (!$path_exists) return;

            $admin_instance = new EsistenzeQuickMenuCardsAdmin($module_path, $module_url);
            $this->add_test('QMC Admin Sınıfı Detaylı Test', 'Admin sınıfı instance oluşturuldu', 'Başarılı', true);

            $methods = ['add_admin_menu', 'admin_page_cards', 'admin_page_settings'];
            foreach($methods as $method) {
                $exists = method_exists($admin_instance, $method);
                $this->add_test('QMC Admin Sınıfı Detaylı Test', 'Metod: <code>' . $method . '</code>', $exists ? 'Mevcut' : 'Eksik', $exists);
            }

        } catch (Exception $e) {
            $this->add_test('QMC Admin Sınıfı Detaylı Test', 'Admin sınıfı instance', 'Hata: ' . $e->getMessage(), false);
        }
    }

    private function test_admin_menu() {
        global $submenu;
        $main_menu_slug = 'esistenze-wp-kit';
        
        $main_menu_exists = isset($submenu[$main_menu_slug]);
        $this->add_test('WordPress Menü Sistemi', 'Esistenze ana menüsü mevcut mu?', $main_menu_exists ? 'Evet' : 'Hayır', $main_menu_exists);

        if (!$main_menu_exists) return;

        $qmc_menu_found = false;
        $qmc_menu_details = 'Bulunamadı';
        
        foreach ($submenu[$main_menu_slug] as $item) {
            if ($item[2] === 'quick-menu-cards') {
                $qmc_menu_found = true;
                $qmc_menu_details = 'Başlık: ' . $item[0] . ' | Yetki: ' . $item[1] . ' | Slug: ' . $item[2];
                break;
            }
        }
        $this->add_test('WordPress Menü Sistemi', 'QMC alt menüsü kayıtlı mı?', $qmc_menu_details, $qmc_menu_found);
    }

    private function test_user_capabilities() {
        $current_user = wp_get_current_user();
        $this->add_test('Yetki Kontrolü', 'Kullanıcı Adı', $current_user->user_login, true);
        $this->add_test('Yetki Kontrolü', 'Kullanıcı Rolleri', implode(', ', $current_user->roles), true);
        
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'edit_posts';
        $this->add_test('Yetki Kontrolü', 'Gereken Yetki (capability)', '<code>' . $capability . '</code>', true);
        
        $user_has_cap = current_user_can($capability);
        $this->add_test('Yetki Kontrolü', 'Kullanıcı bu yetkiye sahip mi?', $user_has_cap ? 'Evet' : 'Hayır', $user_has_cap);
    }
    
    private function test_database() {
        $cards_option = get_option('esistenze_quick_menu_kartlari');
        $settings_option = get_option('esistenze_quick_menu_settings');

        $this->add_test('Veritabanı', 'Kartlar seçeneği (esistenze_quick_menu_kartlari)', $cards_option !== false ? 'Mevcut' : 'Mevcut Değil', $cards_option !== false);
        $this->add_test('Veritabanı', 'Ayarlar seçeneği (esistenze_quick_menu_settings)', $settings_option !== false ? 'Mevcut' : 'Mevcut Değil', $settings_option !== false);
    }

    private function generate_recommendations() {
        $recommendations = [];
        // Örnek: Yol uyuşmazlığı varsa öneri ekle
        if (strtolower($this->real_plugin_folder_name) !== 'esistenze-wordpress-kit') {
            $recommendations[] = [
                'label' => 'Klasör Adı Uyuşmazlığı',
                'value' => 'Eklenti klasör adı <code>' . $this->real_plugin_folder_name . '</code> olarak ayarlanmış. Tavsiye edilen <code>esistenze-wordpress-kit</code> şeklindedir. Bu durum dosya yolu hatalarına neden olabilir. Çözüm: Eklentiyi devre dışı bırakın, FTP/SSH ile klasör adını küçük harfe çevirin ve tekrar etkinleştirin.',
                'status' => false
            ];
        }

        // Örnek: Yetki sorunu varsa
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'edit_posts';
        if (!current_user_can($capability)) {
            $recommendations[] = [
                'label' => 'Yetki Sorunu',
                'value' => 'Mevcut kullanıcı rolünüz (<code>' . implode(', ', wp_get_current_user()->roles) . '</code>), QMC sayfalarına erişmek için gereken <code>' . $capability . '</code> yetkisine sahip değil. Çözüm: Yönetici (Administrator) hesabıyla giriş yapın veya kullanıcı rolünüzün yetkilerini güncelleyin.',
                'status' => false
            ];
        }

        if (!empty($recommendations)) {
            $this->results['recommendations'] = [
                'title' => 'Otomatik Çözüm Önerileri',
                'description' => 'Aşağıdaki adımlar sorunları çözmenize yardımcı olabilir.',
                'tests' => $recommendations
            ];
        }
    }
    
    // --- YARDIMCI FONKSİYONLAR ---

    private function add_test($group_title, $label, $value, $status) {
        if (!isset($this->results[$group_title])) {
            $this->results[$group_title] = ['title' => $group_title, 'tests' => []];
        }
        $this->results[$group_title]['tests'][] = [
            'label' => $label,
            'value' => $value,
            'status' => (bool) $status,
        ];
    }
    
    private function render_styles() {
        echo '<style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; background-color: #f0f0f1; color: #444; margin: 0; }
            .container { max-width: 1200px; margin: 20px auto; background: #fff; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 4px; }
            h1 { font-size: 24px; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; }
            h1 .dashicons { font-size: 30px; vertical-align: middle; color: #0073aa; }
            h2 { font-size: 18px; color: #2c3e50; background-color: #f9f9f9; padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin-top: 30px; }
            p.description { font-style: italic; color: #666; }
            .test-group { margin-bottom: 20px; }
            .results-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            .results-table td { padding: 12px; border: 1px solid #e5e5e5; vertical-align: top; }
            .results-table tr:nth-child(even) { background-color: #f9f9f9; }
            .results-table td:first-child { width: 30%; }
            .results-table td:last-child { width: 50px; text-align: center; }
            .value { word-break: break-all; }
            .value code { background-color: #eef; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
            .status { font-size: 20px; }
            .icon-success { color: #4caf50; }
            .icon-error { color: #f44336; }
        </style>';
    }
}

$debugger = new QMC_Advanced_Debugger();
$debugger->run_tests();
$debugger->render();
?> 