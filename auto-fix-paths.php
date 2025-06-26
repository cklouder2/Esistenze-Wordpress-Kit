<?php
/**
 * Otomatik Yol Düzeltme Aracı
 * Plugin yollarını otomatik olarak düzeltir
 */

if (!defined('ABSPATH')) {
    $wp_load_paths = array('../../../wp-load.php', '../../wp-load.php', '../wp-load.php', 'wp-load.php');
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

if (!current_user_can('manage_options')) {
    wp_die('Yetkiniz yok.');
}

// Düzeltme işlemi
if (isset($_POST['fix_paths'])) {
    $results = array();
    
    // 1. Plugin klasörünü tespit et
    $plugin_file = __FILE__;
    $plugin_dir = dirname($plugin_file);
    
    // 2. WordPress plugin dizinindeki symlink oluştur
    $wp_plugin_target = WP_PLUGIN_DIR . '/esistenze-wordpress-kit';
    
    if (!is_dir($wp_plugin_target)) {
        // Sembolik link oluştur
        if (function_exists('symlink')) {
            if (symlink($plugin_dir, $wp_plugin_target)) {
                $results[] = '✅ Sembolik link oluşturuldu: ' . $wp_plugin_target;
            } else {
                $results[] = '❌ Sembolik link oluşturulamadı';
            }
        } else {
            // Kopyalama yöntemi
            if (wp_mkdir_p($wp_plugin_target)) {
                $results[] = '✅ Plugin dizini oluşturuldu: ' . $wp_plugin_target;
                $results[] = '⚠️ Dosyaları manuel olarak kopyalamanız gerekiyor';
            } else {
                $results[] = '❌ Plugin dizini oluşturulamadı';
            }
        }
    } else {
        $results[] = '✅ Plugin dizini zaten mevcut: ' . $wp_plugin_target;
    }
    
    // 3. Plugin'i yeniden aktive et
    $plugin_slug = 'esistenze-wordpress-kit/esistenze_main_plugin.php';
    if (!is_plugin_active($plugin_slug)) {
        $result = activate_plugin($plugin_slug);
        if (is_wp_error($result)) {
            $results[] = '❌ Plugin aktive edilemedi: ' . $result->get_error_message();
        } else {
            $results[] = '✅ Plugin başarıyla aktive edildi';
        }
    } else {
        $results[] = '✅ Plugin zaten aktif';
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Otomatik Yol Düzeltme</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .wrap { background: #fff; padding: 20px; border-radius: 5px; max-width: 800px; }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .button { display: inline-block; padding: 10px 20px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .button-danger { background: #dc3232; }
        .notice { padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa; background: #f9f9f9; }
        .notice-success { border-left-color: #46b450; background: #f0f8f0; }
        .notice-error { border-left-color: #dc3232; background: #fdf0f0; }
        .code { font-family: monospace; background: #f8f8f8; padding: 2px 5px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>🔧 Otomatik Yol Düzeltme Aracı</h1>
    
    <?php if (isset($results)): ?>
        <div class="notice notice-success">
            <h3>Düzeltme Sonuçları:</h3>
            <ul>
                <?php foreach ($results as $result): ?>
                    <li><?php echo $result; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <p><a href="<?php echo admin_url('admin.php?page=esistenze-qmc-test'); ?>" class="button">Testi Tekrar Çalıştır</a></p>
    <?php else: ?>
        
        <div class="notice">
            <h3>🎯 Bu Araç Ne Yapar?</h3>
            <p>Plugin dosyalarının WordPress tarafından doğru konumda bulunması için gerekli düzeltmeleri yapar:</p>
            <ul>
                <li>WordPress plugin dizininde sembolik link oluşturur</li>
                <li>Plugin'i yeniden aktive eder</li>
                <li>Dosya yollarını düzeltir</li>
            </ul>
        </div>
        
        <?php
        // Mevcut durum analizi
        $current_dir = __DIR__;
        $wp_plugin_dir = WP_PLUGIN_DIR . '/esistenze-wordpress-kit';
        
        echo '<h3>📊 Mevcut Durum</h3>';
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="border-bottom: 1px solid #ddd;"><th style="padding: 8px; text-align: left;">Kontrol</th><th style="padding: 8px; text-align: left;">Durum</th></tr>';
        echo '<tr><td>Mevcut Dizin</td><td class="code">' . $current_dir . '</td></tr>';
        echo '<tr><td>WordPress Plugin Dizini</td><td class="code">' . $wp_plugin_dir . '</td></tr>';
        echo '<tr><td>Plugin Dizini Mevcut?</td><td>' . (is_dir($wp_plugin_dir) ? '<span class="success">✅ Evet</span>' : '<span class="error">❌ Hayır</span>') . '</td></tr>';
        echo '<tr><td>Ana Plugin Dosyası</td><td>' . (file_exists($current_dir . '/esistenze_main_plugin.php') ? '<span class="success">✅ Mevcut</span>' : '<span class="error">❌ Eksik</span>') . '</td></tr>';
        echo '<tr><td>QMC Modülü</td><td>' . (file_exists($current_dir . '/modules/quick-menu-cards/quick-menu-cards.php') ? '<span class="success">✅ Mevcut</span>' : '<span class="error">❌ Eksik</span>') . '</td></tr>';
        echo '</table>';
        
        // Önerilen eylem
        if (!is_dir($wp_plugin_dir) && file_exists($current_dir . '/esistenze_main_plugin.php')) {
            echo '<div class="notice notice-error">';
            echo '<h4>⚠️ Sorun Tespit Edildi</h4>';
            echo '<p>Plugin dosyaları doğru konumda değil. Otomatik düzeltme önerilir.</p>';
            echo '</div>';
            
            echo '<form method="post">';
            echo '<input type="hidden" name="fix_paths" value="1">';
            echo '<p><button type="submit" class="button button-danger">🔧 Otomatik Düzelt</button></p>';
            echo '</form>';
        } else {
            echo '<div class="notice notice-success">';
            echo '<h4>✅ Her Şey Yolunda</h4>';
            echo '<p>Plugin dosyaları doğru konumda görünüyor.</p>';
            echo '</div>';
        }
        ?>
        
    <?php endif; ?>
    
    <h3>🔗 Hızlı Linkler</h3>
    <p>
        <a href="<?php echo admin_url('admin.php?page=esistenze-qmc-test'); ?>" class="button">QMC Test</a>
        <a href="<?php echo admin_url('plugins.php'); ?>" class="button">Plugin Yöneticisi</a>
        <a href="<?php echo plugin_dir_url(__FILE__) . 'file-structure-check.php'; ?>" class="button">Dosya Kontrolü</a>
    </p>
    
</div>
</body>
</html> 