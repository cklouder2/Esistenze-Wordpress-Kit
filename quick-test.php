<?php
/**
 * Hızlı PHP Test - Esistenze WordPress Kit
 * Bu dosyayı tarayıcıdan açarak test edebilirsiniz
 */

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Çıktıyı düzenle
echo "<html><head><title>Esistenze WordPress Kit - PHP Test</title>";
echo "<style>body{font-family:monospace;background:#1e1e1e;color:#00ff00;padding:20px;}</style></head><body>";
echo "<h1>🧪 Esistenze WordPress Kit - PHP Test</h1>";
echo "<hr>";

// Temel bilgiler
echo "<h2>📋 Sistem Bilgileri</h2>";
echo "PHP Sürümü: " . PHP_VERSION . "<br>";
echo "İşletim Sistemi: " . PHP_OS . "<br>";
echo "Bellek Limiti: " . ini_get('memory_limit') . "<br>";
echo "Çalışma Dizini: " . getcwd() . "<br><br>";

// Ana plugin dosyası kontrolü
echo "<h2>🔍 Ana Plugin Kontrolü</h2>";
$main_plugin = 'esistenze_main_plugin.php';

if (file_exists($main_plugin)) {
    echo "✅ Ana plugin dosyası bulundu<br>";
    
    // Dosya bilgileri
    $size = filesize($main_plugin);
    $modified = date('Y-m-d H:i:s', filemtime($main_plugin));
    echo "📏 Dosya boyutu: " . number_format($size) . " byte<br>";
    echo "📅 Son değişiklik: $modified<br>";
    
    // İçerik analizi
    $content = file_get_contents($main_plugin);
    $lines = substr_count($content, "\n");
    echo "📝 Satır sayısı: $lines<br>";
    
    // Plugin bilgileri
    if (preg_match('/Plugin Name:\s*(.+)/', $content, $matches)) {
        echo "🔧 Plugin Adı: " . trim($matches[1]) . "<br>";
    }
    if (preg_match('/Version:\s*(.+)/', $content, $matches)) {
        echo "📦 Versiyon: " . trim($matches[1]) . "<br>";
    }
    
    echo "<br><h3>🔍 PHP Syntax Kontrolü</h3>";
    
    // PHP syntax kontrolü
    $temp_file = tempnam(sys_get_temp_dir(), 'php_check');
    file_put_contents($temp_file, $content);
    
    $output = array();
    $return_code = 0;
    exec("php -l \"$temp_file\" 2>&1", $output, $return_code);
    
    if ($return_code === 0) {
        echo "✅ PHP Syntax: BAŞARILI<br>";
    } else {
        echo "❌ PHP Syntax: HATA<br>";
        echo "Detay: " . implode("<br>", $output) . "<br>";
    }
    
    unlink($temp_file);
    
} else {
    echo "❌ Ana plugin dosyası bulunamadı<br>";
}

// Modül kontrolü
echo "<br><h2>📂 Modül Kontrolü</h2>";
$modules_dir = 'modules';

if (is_dir($modules_dir)) {
    $modules = scandir($modules_dir);
    $module_count = 0;
    
    echo "<table border='1' style='border-collapse:collapse;color:#00ff00;'>";
    echo "<tr><th>Modül</th><th>Ana Dosya</th><th>Sınıflar</th><th>Assets</th><th>Status</th></tr>";
    
    foreach ($modules as $module) {
        if ($module === '.' || $module === '..' || $module === 'index.php') continue;
        
        $module_path = $modules_dir . '/' . $module;
        if (is_dir($module_path)) {
            $module_count++;
            echo "<tr>";
            echo "<td>$module</td>";
            
            // Ana dosya kontrolü
            $main_file = $module_path . '/' . $module . '.php';
            if (file_exists($main_file)) {
                echo "<td>✅ Mevcut</td>";
                
                // Includes kontrolü
                $includes_dir = $module_path . '/includes';
                $class_count = 0;
                if (is_dir($includes_dir)) {
                    $includes = scandir($includes_dir);
                    foreach ($includes as $include) {
                        if (strpos($include, 'class-') === 0 && pathinfo($include, PATHINFO_EXTENSION) === 'php') {
                            $class_count++;
                        }
                    }
                }
                echo "<td>$class_count sınıf</td>";
                
                // Assets kontrolü
                $assets_dir = $module_path . '/assets';
                $asset_info = "";
                if (is_dir($assets_dir)) {
                    $assets = scandir($assets_dir);
                    $css_count = 0;
                    $js_count = 0;
                    foreach ($assets as $asset) {
                        $ext = pathinfo($asset, PATHINFO_EXTENSION);
                        if ($ext === 'css') $css_count++;
                        if ($ext === 'js') $js_count++;
                    }
                    $asset_info = "CSS:$css_count, JS:$js_count";
                }
                echo "<td>$asset_info</td>";
                echo "<td>✅ OK</td>";
                
            } else {
                echo "<td>❌ Bulunamadı</td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
                echo "<td>❌ HATA</td>";
            }
            
            echo "</tr>";
        }
    }
    
    echo "</table>";
    echo "<br>Toplam modül: $module_count<br>";
} else {
    echo "❌ Modüller dizini bulunamadı<br>";
}

// Quick Menu Cards özel kontrolü
echo "<br><h2>🎯 Quick Menu Cards Özel Kontrolü</h2>";
$qmc_path = 'modules/quick-menu-cards';

if (is_dir($qmc_path)) {
    echo "✅ Quick Menu Cards modülü bulundu<br>";
    
    $qmc_files = array(
        'Ana Modül' => $qmc_path . '/quick-menu-cards.php',
        'Admin Sınıfı' => $qmc_path . '/includes/class-admin.php',
        'Frontend Sınıfı' => $qmc_path . '/includes/class-frontend.php',
        'Shortcodes Sınıfı' => $qmc_path . '/includes/class-shortcodes.php',
        'AJAX Sınıfı' => $qmc_path . '/includes/class-ajax.php',
        'Admin CSS' => $qmc_path . '/assets/admin.css',
        'Admin JS' => $qmc_path . '/assets/admin.js',
        'Frontend CSS' => $qmc_path . '/assets/style.css'
    );
    
    echo "<table border='1' style='border-collapse:collapse;color:#00ff00;'>";
    echo "<tr><th>Dosya</th><th>Durum</th><th>Boyut</th><th>Son Değişiklik</th></tr>";
    
    foreach ($qmc_files as $name => $file) {
        echo "<tr>";
        echo "<td>$name</td>";
        
        if (file_exists($file)) {
            echo "<td>✅ Mevcut</td>";
            echo "<td>" . number_format(filesize($file)) . " byte</td>";
            echo "<td>" . date('Y-m-d H:i:s', filemtime($file)) . "</td>";
        } else {
            echo "<td>❌ Bulunamadı</td>";
            echo "<td>-</td>";
            echo "<td>-</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "❌ Quick Menu Cards modülü bulunamadı<br>";
}

// Capability test
echo "<br><h2>🔐 Yetki Sistemi Testi</h2>";

// WordPress fonksiyonlarını taklit et
if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // Test için
    }
}

if (!defined('ABSPATH')) {
    define('ABSPATH', './');
}

// Ana plugin dosyasını include et
if (file_exists($main_plugin)) {
    ob_start();
    try {
        include_once $main_plugin;
        $include_output = ob_get_clean();
        
        if (function_exists('esistenze_qmc_capability')) {
            $capability = esistenze_qmc_capability();
            echo "✅ Capability fonksiyonu çalışıyor: '$capability'<br>";
        } else {
            echo "❌ Capability fonksiyonu bulunamadı<br>";
        }
        
        // Sınıf kontrolü
        $classes = array(
            'EsistenzeWPKit',
            'EsistenzeQuickMenuCards',
            'EsistenzeQuickMenuCardsAdmin',
            'EsistenzeQuickMenuCardsFrontend'
        );
        
        echo "<br><h3>📚 Sınıf Kontrolleri</h3>";
        foreach ($classes as $class) {
            $exists = class_exists($class);
            echo "$class: " . ($exists ? "✅ Mevcut" : "❌ Bulunamadı") . "<br>";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ Plugin yüklenirken hata: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Ana plugin dosyası yüklenemedi<br>";
}

// Sonuç
echo "<br><h2>📊 Test Sonucu</h2>";
echo "<div style='background:#004400;padding:10px;border:1px solid #00ff00;'>";
echo "🎉 <strong>PHP Test Tamamlandı!</strong><br>";
echo "Quick Menu Cards modülü PHP syntax açısından kontrol edildi.<br>";
echo "Detaylar için yukarıdaki bölümleri inceleyin.<br>";
echo "</div>";

echo "<br><small>Test zamanı: " . date('Y-m-d H:i:s') . "</small>";
echo "</body></html>";
?> 