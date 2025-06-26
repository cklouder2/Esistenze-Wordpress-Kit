<?php
/**
 * Basit PHP Syntax Test
 * WordPress olmadan da çalışabilir
 */

echo "🧪 Esistenze WordPress Kit - PHP Syntax Test\n";
echo "==========================================\n\n";

// Test edilecek dosyalar
$test_files = array(
    'Ana Plugin' => '../esistenze_main_plugin.php',
    'QMC Ana Modül' => '../modules/quick-menu-cards/quick-menu-cards.php',
    'QMC Admin' => '../modules/quick-menu-cards/includes/class-admin.php',
    'QMC Frontend' => '../modules/quick-menu-cards/includes/class-frontend.php',
    'QMC Shortcodes' => '../modules/quick-menu-cards/includes/class-shortcodes.php',
    'QMC AJAX' => '../modules/quick-menu-cards/includes/class-ajax.php',
    'Smart Buttons' => '../modules/smart-product-buttons/smart-product-buttons.php',
    'Category Styler' => '../modules/category-styler/category-styler.php',
    'Custom Topbar' => '../modules/custom-topbar/custom-topbar.php',
    'Price Modifier' => '../modules/price-modifier/price-modifier.php'
);

$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;

foreach ($test_files as $name => $file) {
    $total_tests++;
    echo "📁 Test: $name\n";
    echo "   Dosya: $file\n";
    
    if (!file_exists(__DIR__ . '/' . $file)) {
        echo "   ❌ HATA: Dosya bulunamadı\n\n";
        $failed_tests++;
        continue;
    }
    
    // PHP syntax kontrolü
    $output = array();
    $return_code = 0;
    
    // php -l komutu ile syntax kontrolü (eğer php cli mevcutsa)
    exec("php -l " . escapeshellarg(__DIR__ . '/' . $file) . " 2>&1", $output, $return_code);
    
    if ($return_code === 0) {
        echo "   ✅ PHP Syntax: BAŞARILI\n";
        
        // Dosya içeriği kontrolü
        $content = file_get_contents(__DIR__ . '/' . $file);
        
        // Temel kontroller
        $checks = array(
            'PHP Tag' => strpos($content, '<?php') === 0,
            'Kısa Tag Yok' => strpos($content, '<?=') === false && substr_count($content, '<?') === substr_count($content, '<?php'),
            'Closing Tag Yok' => strpos($content, '?>') === false || strrpos($content, '?>') < strlen($content) - 10,
            'Security Check' => strpos($content, "if (!defined('ABSPATH'))") !== false || strpos($content, 'ABSPATH') !== false
        );
        
        foreach ($checks as $check_name => $result) {
            if ($result) {
                echo "   ✅ $check_name: BAŞARILI\n";
            } else {
                echo "   ⚠️  $check_name: UYARI\n";
            }
        }
        
        $passed_tests++;
    } else {
        echo "   ❌ PHP Syntax: HATA\n";
        echo "   Detay: " . implode("\n   ", $output) . "\n";
        $failed_tests++;
    }
    
    echo "\n";
}

// Sonuçlar
echo "📊 TEST SONUÇLARI\n";
echo "================\n";
echo "Toplam Test: $total_tests\n";
echo "Başarılı: $passed_tests ✅\n";
echo "Başarısız: $failed_tests ❌\n";
echo "Başarı Oranı: " . round(($passed_tests / $total_tests) * 100, 1) . "%\n\n";

if ($failed_tests === 0) {
    echo "🎉 TÜM TESTLER BAŞARILI!\n";
    echo "Eklenti PHP syntax açısından temiz görünüyor.\n\n";
} else {
    echo "⚠️  BAZI TESTLER BAŞARISIZ!\n";
    echo "Lütfen yukarıdaki hataları kontrol edin.\n\n";
}

// WordPress fonksiyon kontrolü (eğer WordPress yüklüyse)
if (function_exists('wp_version')) {
    echo "🔧 WORDPRESS KONTROLLERI\n";
    echo "========================\n";
    echo "WordPress Sürümü: " . get_bloginfo('version') . "\n";
    echo "PHP Sürümü: " . PHP_VERSION . "\n";
    echo "MySQL Sürümü: " . (function_exists('mysql_get_server_info') ? mysql_get_server_info() : 'Bilinmiyor') . "\n";
    
    // Eklenti kontrolü
    if (function_exists('is_plugin_active')) {
        $plugin_file = 'esistenze-wordpress-kit/esistenze_main_plugin.php';
        $is_active = is_plugin_active($plugin_file);
        echo "Eklenti Durumu: " . ($is_active ? 'Aktif ✅' : 'Pasif ❌') . "\n";
    }
    
    // Capability fonksionu kontrolü
    if (function_exists('esistenze_qmc_capability')) {
        $capability = esistenze_qmc_capability();
        echo "QMC Capability: $capability ✅\n";
    } else {
        echo "QMC Capability: Bulunamadı ❌\n";
    }
    
    // Sınıf kontrolü
    $classes = array(
        'EsistenzeWPKit',
        'EsistenzeQuickMenuCards',
        'EsistenzeQuickMenuCardsAdmin',
        'EsistenzeQuickMenuCardsFrontend',
        'EsistenzeQuickMenuCardsShortcodes',
        'EsistenzeQuickMenuCardsAjax'
    );
    
    echo "\nSınıf Kontrolleri:\n";
    foreach ($classes as $class) {
        $exists = class_exists($class);
        echo "- $class: " . ($exists ? 'Mevcut ✅' : 'Bulunamadı ❌') . "\n";
    }
    
    // Shortcode kontrolü
    global $shortcode_tags;
    $shortcodes = array('quick_menu_cards', 'quick_menu_banner', 'hizli_menu', 'hizli_menu_banner');
    
    echo "\nShortcode Kontrolleri:\n";
    foreach ($shortcodes as $shortcode) {
        $exists = isset($shortcode_tags[$shortcode]);
        echo "- [$shortcode]: " . ($exists ? 'Kayıtlı ✅' : 'Bulunamadı ❌') . "\n";
    }
    
} else {
    echo "ℹ️  WordPress fonksiyonları bulunamadı. Sadece PHP syntax kontrolü yapıldı.\n";
}

echo "\n🏁 Test tamamlandı!\n";
?> 