<?php
/**
 * Dosya Yapısı Kontrol Aracı
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

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dosya Yapısı Kontrolü</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .wrap { background: #fff; padding: 20px; border-radius: 5px; max-width: 1200px; }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f9f9f9; }
        .code { font-family: monospace; background: #f8f8f8; padding: 2px 5px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>📁 Dosya Yapısı Kontrolü</h1>
    
    <?php
    // Plugin bilgilerini al
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/esistenze-wordpress-kit/esistenze_main_plugin.php');
    $plugin_dir = WP_PLUGIN_DIR . '/esistenze-wordpress-kit/';
    
    echo '<h3>🔌 Plugin Bilgileri</h3>';
    echo '<table>';
    echo '<tr><th>Bilgi</th><th>Değer</th></tr>';
    echo '<tr><td>Plugin Adı</td><td>' . ($plugin_data['Name'] ?? 'Bilinmiyor') . '</td></tr>';
    echo '<tr><td>Sürüm</td><td>' . ($plugin_data['Version'] ?? 'Bilinmiyor') . '</td></tr>';
    echo '<tr><td>Plugin Dizini</td><td class="code">' . $plugin_dir . '</td></tr>';
    echo '<tr><td>Dizin Var mı?</td><td>' . (is_dir($plugin_dir) ? '<span class="success">✅ Evet</span>' : '<span class="error">❌ Hayır</span>') . '</td></tr>';
    echo '</table>';
    
    // Gerekli dosyaları kontrol et
    $required_files = array(
        'Ana Plugin' => 'esistenze_main_plugin.php',
        'QMC Modülü' => 'modules/quick-menu-cards/quick-menu-cards.php',
        'QMC Admin' => 'modules/quick-menu-cards/includes/class-admin.php',
        'QMC Frontend' => 'modules/quick-menu-cards/includes/class-frontend.php',
        'QMC Shortcodes' => 'modules/quick-menu-cards/includes/class-shortcodes.php',
        'QMC AJAX' => 'modules/quick-menu-cards/includes/class-ajax.php',
        'QMC CSS' => 'modules/quick-menu-cards/assets/admin.css',
        'QMC JS' => 'modules/quick-menu-cards/assets/admin.js'
    );
    
    echo '<h3>📄 Gerekli Dosyalar</h3>';
    echo '<table>';
    echo '<tr><th>Dosya</th><th>Yol</th><th>Durum</th><th>Boyut</th></tr>';
    
    $missing_files = 0;
    foreach ($required_files as $name => $relative_path) {
        $full_path = $plugin_dir . $relative_path;
        echo '<tr>';
        echo '<td>' . $name . '</td>';
        echo '<td class="code">' . $relative_path . '</td>';
        
        if (file_exists($full_path)) {
            echo '<td class="success">✅ Mevcut</td>';
            echo '<td>' . number_format(filesize($full_path)) . ' byte</td>';
        } else {
            echo '<td class="error">❌ Eksik</td>';
            echo '<td>-</td>';
            $missing_files++;
        }
        echo '</tr>';
    }
    echo '</table>';
    
    // Alternatif yolları kontrol et
    if ($missing_files > 0) {
        echo '<h3>🔍 Alternatif Yol Araması</h3>';
        
        // Mevcut dizindeki dosyaları listele
        $current_dir = __DIR__;
        echo '<p><strong>Mevcut Dizin:</strong> <span class="code">' . $current_dir . '</span></p>';
        
        if (file_exists($current_dir . '/modules/quick-menu-cards/quick-menu-cards.php')) {
            echo '<div class="success">';
            echo '<h4>✅ Dosyalar Mevcut Dizinde Bulundu!</h4>';
            echo '<p>Plugin dosyaları şu konumda: <span class="code">' . $current_dir . '</span></p>';
            echo '<p><strong>Çözüm:</strong> Plugin yollarını güncellemek gerekiyor.</p>';
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<h4>❌ Dosyalar Bulunamadı</h4>';
            echo '<p>Plugin dosyaları ne WordPress plugin dizininde ne de mevcut dizinde bulunuyor.</p>';
            echo '</div>';
        }
    }
    
    // WordPress plugin sistemi
    echo '<h3>🔌 WordPress Plugin Sistemi</h3>';
    $active_plugins = get_option('active_plugins', array());
    
    echo '<table>';
    echo '<tr><th>Kontrol</th><th>Durum</th><th>Detay</th></tr>';
    echo '<tr><td>WP_PLUGIN_DIR</td><td class="code">' . WP_PLUGIN_DIR . '</td><td>' . (is_dir(WP_PLUGIN_DIR) ? '✅ Mevcut' : '❌ Yok') . '</td></tr>';
    echo '<tr><td>Aktif Plugin Sayısı</td><td>' . count($active_plugins) . '</td><td>-</td></tr>';
    
    // Esistenze plugin kontrolü
    $esistenze_active = false;
    foreach ($active_plugins as $plugin) {
        if (strpos($plugin, 'esistenze') !== false) {
            $esistenze_active = $plugin;
            break;
        }
    }
    
    echo '<tr><td>Esistenze Plugin</td><td>' . ($esistenze_active ? '<span class="success">✅ Aktif</span>' : '<span class="error">❌ Pasif</span>') . '</td><td>' . ($esistenze_active ? $esistenze_active : '-') . '</td></tr>';
    echo '</table>';
    
    // Öneriler
    echo '<h3>💡 Öneriler</h3>';
    
    if ($missing_files == 0) {
        echo '<div class="success">';
        echo '<h4>🎉 Tüm Dosyalar Mevcut!</h4>';
        echo '<p>Plugin dosya yapısı doğru görünüyor.</p>';
        echo '</div>';
    } else {
        echo '<div class="error">';
        echo '<h4>🔧 Yapılması Gerekenler:</h4>';
        echo '<ol>';
        echo '<li><strong>Plugin Yeniden Yükleme:</strong> Plugin\'i deaktive edip tekrar aktive edin</li>';
        echo '<li><strong>Dosya İzinleri:</strong> Plugin dizininin okuma izinlerini kontrol edin</li>';
        echo '<li><strong>Manuel Yükleme:</strong> Plugin dosyalarını manuel olarak doğru konuma kopyalayın</li>';
        echo '</ol>';
        echo '</div>';
    }
    
    echo '<h3>🔗 Hızlı Linkler</h3>';
    echo '<p>';
    echo '<a href="' . admin_url('plugins.php') . '" class="button">Plugin Yöneticisi</a> ';
    echo '<a href="' . admin_url('admin.php?page=esistenze-wp-kit') . '" class="button">Esistenze Dashboard</a> ';
    echo '<a href="javascript:location.reload()" class="button">Yenile</a>';
    echo '</p>';
    ?>
    
</div>
</body>
</html> 