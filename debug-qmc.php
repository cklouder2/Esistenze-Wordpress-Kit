<?php
/**
 * Quick Menu Cards Debug Tool
 * WordPress ortamında detaylı test ve debug
 */

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTML başlat
echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Quick Menu Cards Debug</title>";
echo "<style>";
echo "body { font-family: monospace; background: #1e1e1e; color: #00ff00; padding: 20px; }";
echo ".success { color: #00ff00; }";
echo ".error { color: #ff6b6b; }";
echo ".warning { color: #ffa500; }";
echo ".info { color: #87ceeb; }";
echo "table { border-collapse: collapse; width: 100%; margin: 10px 0; }";
echo "th, td { border: 1px solid #444; padding: 8px; text-align: left; }";
echo "th { background: #333; }";
echo ".status-ok { color: #00ff00; }";
echo ".status-error { color: #ff6b6b; }";
echo ".status-warning { color: #ffa500; }";
echo "</style>";
echo "</head><body>";

echo "<h1>🧪 Quick Menu Cards - WordPress Debug Tool</h1>";
echo "<hr>";

// WordPress kontrol
$is_wordpress = defined('ABSPATH') && function_exists('wp_get_current_user');

if (!$is_wordpress) {
    echo "<p class='warning'>⚠️ WordPress ortamında değil - Standalone mod</p>";
    
    // WordPress fonksiyonlarını taklit et
    if (!function_exists('current_user_can')) {
        function current_user_can($capability) { return true; }
    }
    if (!function_exists('wp_get_current_user')) {
        function wp_get_current_user() {
            return (object) array(
                'user_login' => 'test_user',
                'roles' => array('administrator')
            );
        }
    }
    if (!function_exists('admin_url')) {
        function admin_url($path) { return 'http://localhost/wp-admin/' . $path; }
    }
    if (!function_exists('menu_page_url')) {
        function menu_page_url($menu_slug, $echo = true) { return false; }
    }
    if (!function_exists('add_menu_page')) {
        function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) {
            return 'hook_' . $menu_slug;
        }
    }
    if (!function_exists('add_submenu_page')) {
        function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '') {
            return 'hook_' . $menu_slug;
        }
    }
    if (!function_exists('get_option')) {
        function get_option($option, $default = false) { return $default; }
    }
    if (!function_exists('update_option')) {
        function update_option($option, $value) { return true; }
    }
    if (!function_exists('wp_die')) {
        function wp_die($message) { die($message); }
    }
} else {
    echo "<p class='success'>✅ WordPress ortamında çalışıyor</p>";
}

// Test başlangıç zamanı
$start_time = microtime(true);

// 1. Temel Dosya Kontrolü
echo "<h2>📁 1. DOSYA YAPISI KONTROLÜ</h2>";
$files_to_check = array(
    'Ana Plugin' => 'esistenze_main_plugin.php',
    'QMC Ana Modül' => 'modules/quick-menu-cards/quick-menu-cards.php',
    'QMC Admin Sınıfı' => 'modules/quick-menu-cards/includes/class-admin.php',
    'QMC Frontend Sınıfı' => 'modules/quick-menu-cards/includes/class-frontend.php',
    'QMC Shortcodes Sınıfı' => 'modules/quick-menu-cards/includes/class-shortcodes.php',
    'QMC AJAX Sınıfı' => 'modules/quick-menu-cards/includes/class-ajax.php'
);

echo "<table>";
echo "<tr><th>Dosya</th><th>Durum</th><th>Boyut</th><th>Son Değişiklik</th></tr>";

$file_errors = 0;
foreach ($files_to_check as $name => $file) {
    echo "<tr>";
    echo "<td>$name</td>";
    
    if (file_exists($file)) {
        echo "<td class='status-ok'>✅ Mevcut</td>";
        echo "<td>" . number_format(filesize($file)) . " byte</td>";
        echo "<td>" . date('Y-m-d H:i:s', filemtime($file)) . "</td>";
    } else {
        echo "<td class='status-error'>❌ Bulunamadı</td>";
        echo "<td>-</td><td>-</td>";
        $file_errors++;
    }
    echo "</tr>";
}
echo "</table>";

// 2. Plugin Yükleme Testi
echo "<h2>🔗 2. PLUGIN YÜKLEME TESTİ</h2>";

if (file_exists('esistenze_main_plugin.php')) {
    echo "<p class='info'>Ana plugin dosyası yükleniyor...</p>";
    
    ob_start();
    try {
        include_once 'esistenze_main_plugin.php';
        $plugin_output = ob_get_clean();
        echo "<p class='success'>✅ Ana plugin başarıyla yüklendi</p>";
        
        // Sınıf kontrolleri
        echo "<h3>📚 Sınıf Kontrolleri</h3>";
        $classes = array(
            'EsistenzeWPKit' => 'Ana eklenti sınıfı',
            'EsistenzeQuickMenuCards' => 'QMC ana sınıfı',
            'EsistenzeQuickMenuCardsAdmin' => 'QMC admin sınıfı'
        );
        
        echo "<table>";
        echo "<tr><th>Sınıf</th><th>Durum</th><th>Açıklama</th></tr>";
        
        $class_errors = 0;
        foreach ($classes as $class => $description) {
            echo "<tr>";
            echo "<td>$class</td>";
            if (class_exists($class)) {
                echo "<td class='status-ok'>✅ Mevcut</td>";
                echo "<td>$description</td>";
            } else {
                echo "<td class='status-error'>❌ Bulunamadı</td>";
                echo "<td class='error'>$description - YÜKLENEMEDİ</td>";
                $class_errors++;
            }
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p class='error'>❌ Plugin yükleme hatası: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// 3. Capability Fonksiyon Testi
echo "<h2>🔧 3. YETKİ FONKSİYONU TESTİ</h2>";

if (function_exists('esistenze_qmc_capability')) {
    try {
        $capability = esistenze_qmc_capability();
        echo "<p class='success'>✅ esistenze_qmc_capability() çalışıyor</p>";
        echo "<p class='info'>Döndürülen yetki: '<strong>$capability</strong>'</p>";
        
        // Yetki testleri
        $test_capabilities = array('read', 'edit_posts', 'manage_options');
        echo "<table>";
        echo "<tr><th>Test Yetkisi</th><th>Sonuç</th></tr>";
        foreach ($test_capabilities as $cap) {
            $can = current_user_can($cap);
            echo "<tr>";
            echo "<td>$cap</td>";
            echo "<td class='" . ($can ? "status-ok'>✅ VAR" : "status-error'>❌ YOK") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Capability fonksiyon hatası: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='error'>❌ esistenze_qmc_capability() fonksiyonu bulunamadı!</p>";
}

// 4. Admin Sınıfı Testi
echo "<h2>📋 4. ADMİN SINIFI TESTİ</h2>";

if (class_exists('EsistenzeQuickMenuCardsAdmin')) {
    echo "<p class='info'>QMC Admin sınıfı test ediliyor...</p>";
    
    try {
        // Admin sınıfını başlat
        $module_path = __DIR__ . '/modules/quick-menu-cards/';
        $module_url = 'http://localhost/wp-content/plugins/esistenze-wp-kit/modules/quick-menu-cards/';
        
        $admin = new EsistenzeQuickMenuCardsAdmin($module_path, $module_url);
        echo "<p class='success'>✅ Admin sınıfı başarıyla oluşturuldu</p>";
        
        // Menü fonksiyonunu test et
        echo "<p class='info'>admin_menu() fonksiyonu test ediliyor...</p>";
        ob_start();
        $admin->admin_menu();
        $menu_output = ob_get_clean();
        echo "<p class='success'>✅ admin_menu() fonksiyonu çalıştı</p>";
        
        // Admin sayfası fonksiyonunu test et
        echo "<p class='info'>admin_page() fonksiyonu test ediliyor...</p>";
        $_GET['tab'] = 'groups'; // Test için
        
        ob_start();
        try {
            $admin->admin_page();
            $page_output = ob_get_clean();
            echo "<p class='success'>✅ admin_page() fonksiyonu başarıyla çalıştı</p>";
            echo "<p class='info'>Sayfa çıktısı uzunluğu: " . strlen($page_output) . " karakter</p>";
            
            // Çıktıda önemli elementleri kontrol et
            $checks = array(
                'wrap class' => strpos($page_output, 'class="wrap"') !== false,
                'Quick Menu Cards title' => strpos($page_output, 'Quick Menu Cards') !== false,
                'Debug info' => strpos($page_output, 'Debug:') !== false,
                'Tab navigation' => strpos($page_output, 'nav-tab-wrapper') !== false
            );
            
            echo "<table>";
            echo "<tr><th>Kontrol</th><th>Durum</th></tr>";
            foreach ($checks as $check => $result) {
                echo "<tr>";
                echo "<td>$check</td>";
                echo "<td class='" . ($result ? "status-ok'>✅ VAR" : "status-error'>❌ YOK") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } catch (Exception $e) {
            ob_end_clean();
            echo "<p class='error'>❌ admin_page() hatası: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Admin sınıfı test hatası: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='error'>❌ EsistenzeQuickMenuCardsAdmin sınıfı bulunamadı!</p>";
}

// 5. WordPress Menü Simülasyonu
echo "<h2>📋 5. WORDPRESS MENÜ SİMÜLASYONU</h2>";

if ($is_wordpress) {
    echo "<p class='info'>WordPress menü sistemi test ediliyor...</p>";
    
    // Global menü değişkenlerini kontrol et
    global $menu, $submenu, $admin_page_hooks;
    
    echo "<table>";
    echo "<tr><th>Menü Değişkeni</th><th>Durum</th><th>İçerik</th></tr>";
    echo "<tr><td>\$menu</td><td>" . (isset($menu) ? "✅ Mevcut" : "❌ Yok") . "</td><td>" . (isset($menu) ? count($menu) . " item" : "-") . "</td></tr>";
    echo "<tr><td>\$submenu</td><td>" . (isset($submenu) ? "✅ Mevcut" : "❌ Yok") . "</td><td>" . (isset($submenu) ? count($submenu) . " parent" : "-") . "</td></tr>";
    echo "<tr><td>\$admin_page_hooks</td><td>" . (isset($admin_page_hooks) ? "✅ Mevcut" : "❌ Yok") . "</td><td>" . (isset($admin_page_hooks) ? count($admin_page_hooks) . " hook" : "-") . "</td></tr>";
    echo "</table>";
    
    // Esistenze menüsünü kontrol et
    if (isset($submenu) && isset($submenu['esistenze-wp-kit'])) {
        echo "<p class='success'>✅ 'esistenze-wp-kit' ana menüsü bulundu</p>";
        echo "<table>";
        echo "<tr><th>Submenu</th><th>Başlık</th><th>Yetki</th><th>Slug</th></tr>";
        foreach ($submenu['esistenze-wp-kit'] as $item) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item[0]) . "</td>";
            echo "<td>" . htmlspecialchars($item[3]) . "</td>";
            echo "<td>" . htmlspecialchars($item[1]) . "</td>";
            echo "<td>" . htmlspecialchars($item[2]) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ 'esistenze-wp-kit' menüsü bulunamadı</p>";
    }
    
} else {
    echo "<p class='info'>WordPress ortamında değil - menü simülasyonu yapılıyor</p>";
    
    // Basit menü simülasyonu
    $simulated_menu = array();
    
    if (class_exists('EsistenzeQuickMenuCardsAdmin')) {
        $admin = new EsistenzeQuickMenuCardsAdmin(__DIR__ . '/modules/quick-menu-cards/', 'http://localhost/');
        $simulated_menu['quick-menu'] = $admin->admin_menu();
        echo "<p class='success'>✅ Menü simülasyonu başarılı</p>";
    }
}

// 6. Özet ve Tanı
echo "<h2>📊 6. TANI VE ÖNERİLER</h2>";

$total_time = microtime(true) - $start_time;

echo "<table>";
echo "<tr><th>Test</th><th>Durum</th><th>Açıklama</th></tr>";
echo "<tr><td>Dosya Yapısı</td><td class='status-" . ($file_errors == 0 ? "ok'>✅ TAMAM" : "error'>❌ SORUN") . "</td><td>" . ($file_errors == 0 ? "Tüm dosyalar mevcut" : "$file_errors dosya eksik") . "</td></tr>";
echo "<tr><td>Plugin Yükleme</td><td class='status-" . (isset($class_errors) && $class_errors == 0 ? "ok'>✅ TAMAM" : "error'>❌ SORUN") . "</td><td>" . (isset($class_errors) && $class_errors == 0 ? "Sınıflar yüklendi" : "Sınıf yükleme hatası") . "</td></tr>";
echo "<tr><td>WordPress Entegrasyon</td><td class='status-" . ($is_wordpress ? "ok'>✅ TAMAM" : "warning'>⚠️ SİMÜLE") . "</td><td>" . ($is_wordpress ? "WordPress ortamında" : "Standalone test") . "</td></tr>";
echo "<tr><td>Test Süresi</td><td class='status-info'>" . number_format($total_time, 3) . "s</td><td>Toplam test süresi</td></tr>";
echo "</table>";

// Sorun tespiti
echo "<h3>🔍 SORUN TESPİTİ</h3>";

$potential_issues = array();

if ($file_errors > 0) {
    $potential_issues[] = "Eksik dosyalar mevcut";
}

if (isset($class_errors) && $class_errors > 0) {
    $potential_issues[] = "Sınıf yükleme sorunları";
}

if (!function_exists('esistenze_qmc_capability')) {
    $potential_issues[] = "Capability fonksiyonu eksik";
}

if (!$is_wordpress) {
    $potential_issues[] = "WordPress ortamında test edilmeli";
}

if (empty($potential_issues)) {
    echo "<p class='success'>🟢 Teknik sorun tespit edilmedi!</p>";
    echo "<p class='info'>🔵 Sorun WordPress yetki sistemi veya menü entegrasyonunda olabilir</p>";
    
    echo "<h4>💡 WordPress'te Kontrol Edilecekler:</h4>";
    echo "<ul>";
    echo "<li>Kullanıcı rolü ve yetkileri</li>";
    echo "<li>Plugin aktiflik durumu</li>";
    echo "<li>WordPress admin menü sistemi</li>";
    echo "<li>Hook çalışma sırası</li>";
    echo "<li>Diğer plugin çakışmaları</li>";
    echo "</ul>";
} else {
    echo "<p class='error'>🔴 Tespit edilen sorunlar:</p>";
    echo "<ul>";
    foreach ($potential_issues as $issue) {
        echo "<li class='error'>$issue</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Debug tamamlandı!</strong> Test zamanı: " . date('Y-m-d H:i:s') . "</p>";
echo "<p><small>Bu dosyayı WordPress admin dizininde çalıştırarak daha detaylı test yapabilirsiniz.</small></p>";
echo "</body></html>";
?> 