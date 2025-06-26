<?php
/**
 * Esistenze WordPress Kit Test Çalıştırıcısı
 * Bu dosyayı komut satırından veya tarayıcıdan çalıştırabilirsiniz
 */

// CLI mi web mi kontrol et
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    echo "<pre style='background: #1e1e1e; color: #00ff00; padding: 20px; font-family: monospace;'>";
}

echo "🚀 Esistenze WordPress Kit - Test Çalıştırıcısı\n";
echo "===============================================\n\n";

// Temel PHP bilgileri
echo "📋 SİSTEM BİLGİLERİ\n";
echo "==================\n";
echo "PHP Sürümü: " . PHP_VERSION . "\n";
echo "İşletim Sistemi: " . PHP_OS . "\n";
echo "Bellek Limiti: " . ini_get('memory_limit') . "\n";
echo "Maksimum Yürütme Süresi: " . ini_get('max_execution_time') . "s\n";
echo "Çalışma Dizini: " . getcwd() . "\n\n";

// Ana plugin dosyası kontrolü
echo "🔍 ANA PLUGIN KONTROLÜ\n";
echo "======================\n";

$main_plugin = 'esistenze_main_plugin.php';
if (file_exists($main_plugin)) {
    echo "✅ Ana plugin dosyası bulundu: $main_plugin\n";
    
    // Dosya boyutu
    $size = filesize($main_plugin);
    echo "📏 Dosya boyutu: " . number_format($size) . " byte\n";
    
    // Son değişiklik tarihi
    $modified = date('Y-m-d H:i:s', filemtime($main_plugin));
    echo "📅 Son değişiklik: $modified\n";
    
    // Dosya içeriği temel kontrolü
    $content = file_get_contents($main_plugin);
    echo "📝 Satır sayısı: " . substr_count($content, "\n") . "\n";
    echo "🔧 Plugin Adı: " . (preg_match('/Plugin Name:\s*(.+)/', $content, $matches) ? trim($matches[1]) : 'Bulunamadı') . "\n";
    echo "📦 Versiyon: " . (preg_match('/Version:\s*(.+)/', $content, $matches) ? trim($matches[1]) : 'Bulunamadı') . "\n";
    
} else {
    echo "❌ Ana plugin dosyası bulunamadı: $main_plugin\n";
}

echo "\n";

// Modül kontrolü
echo "📂 MODÜL KONTROLÜ\n";
echo "=================\n";

$modules_dir = 'modules';
if (is_dir($modules_dir)) {
    $modules = scandir($modules_dir);
    $module_count = 0;
    
    foreach ($modules as $module) {
        if ($module === '.' || $module === '..' || $module === 'index.php') continue;
        
        $module_path = $modules_dir . '/' . $module;
        if (is_dir($module_path)) {
            $module_count++;
            echo "📁 $module: ";
            
            // Ana modül dosyasını kontrol et
            $main_file = $module_path . '/' . $module . '.php';
            if (file_exists($main_file)) {
                echo "✅ Mevcut\n";
                
                // Includes klasörü kontrolü
                $includes_dir = $module_path . '/includes';
                if (is_dir($includes_dir)) {
                    $includes = scandir($includes_dir);
                    $class_count = 0;
                    foreach ($includes as $include) {
                        if (strpos($include, 'class-') === 0 && pathinfo($include, PATHINFO_EXTENSION) === 'php') {
                            $class_count++;
                        }
                    }
                    echo "   📚 Sınıf dosyası: $class_count adet\n";
                }
                
                // Assets klasörü kontrolü
                $assets_dir = $module_path . '/assets';
                if (is_dir($assets_dir)) {
                    $assets = scandir($assets_dir);
                    $css_count = 0;
                    $js_count = 0;
                    foreach ($assets as $asset) {
                        $ext = pathinfo($asset, PATHINFO_EXTENSION);
                        if ($ext === 'css') $css_count++;
                        if ($ext === 'js') $js_count++;
                    }
                    echo "   🎨 CSS: $css_count, JS: $js_count\n";
                }
                
            } else {
                echo "❌ Ana dosya bulunamadı\n";
            }
        }
    }
    
    echo "\nToplam modül: $module_count\n";
} else {
    echo "❌ Modüller dizini bulunamadı: $modules_dir\n";
}

echo "\n";

// Test dosyalarını çalıştır
echo "🧪 TEST ÇALIŞTIRMA\n";
echo "==================\n";

$test_files = array(
    'Syntax Check' => 'tests/test-syntax-check.php'
);

foreach ($test_files as $test_name => $test_file) {
    echo "🔬 $test_name testi çalıştırılıyor...\n";
    echo "-----------------------------------\n";
    
    if (file_exists($test_file)) {
        // Test dosyasını include et
        ob_start();
        include $test_file;
        $output = ob_get_clean();
        
        echo $output;
        echo "\n";
    } else {
        echo "❌ Test dosyası bulunamadı: $test_file\n\n";
    }
}

// WordPress entegrasyonu (eğer mevcutsa)
echo "🔗 WORDPRESS ENTEGRASYONU\n";
echo "=========================\n";

// WordPress'in yüklü olup olmadığını kontrol et
$wp_config_paths = array(
    '../wp-config.php',
    '../../wp-config.php',
    '../../../wp-config.php',
    'wp-config.php'
);

$wp_found = false;
foreach ($wp_config_paths as $wp_config) {
    if (file_exists($wp_config)) {
        echo "✅ WordPress bulundu: $wp_config\n";
        $wp_found = true;
        break;
    }
}

if (!$wp_found) {
    echo "ℹ️  WordPress bulunamadı. Standalone test modunda çalışıyor.\n";
}

// Capability fonksiyonu test et
echo "\n🔐 YETKİ SİSTEMİ KONTROLÜ\n";
echo "========================\n";

// Ana plugin dosyasını include et (WordPress olmadan)
if (file_exists($main_plugin)) {
    // WordPress fonksiyonlarını taklit et
    if (!function_exists('current_user_can')) {
        function current_user_can($capability) {
            return true; // Test için
        }
    }
    
    // Plugin dosyasını include et
    ob_start();
    include_once $main_plugin;
    ob_end_clean();
    
    if (function_exists('esistenze_qmc_capability')) {
        $capability = esistenze_qmc_capability();
        echo "✅ Capability fonksiyonu çalışıyor: '$capability'\n";
    } else {
        echo "❌ Capability fonksiyonu bulunamadı\n";
    }
} else {
    echo "❌ Ana plugin dosyası yüklenemedi\n";
}

echo "\n";

// Özet
echo "📊 TEST ÖZETİ\n";
echo "=============\n";
echo "✅ Temel dosya yapısı kontrol edildi\n";
echo "✅ Modül yapısı kontrol edildi\n";
echo "✅ PHP syntax kontrolleri yapıldı\n";
echo "✅ Yetki sistemi kontrol edildi\n";

if (!$is_cli) {
    echo "</pre>";
}

echo "\n🏁 Testler tamamlandı!\n";
?> 