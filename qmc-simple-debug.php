<?php
/**
 * QMC Simple Debug Tool
 * Bu dosyayı WordPress ana dizinine kopyalayın ve çalıştırın.
 * Örnek: https://siteniz.com/qmc-simple-debug.php
 * 
 * @version 1.0.0
 */

// WordPress ortamını yükle
if (file_exists('./wp-load.php')) {
    require_once('./wp-load.php');
} else {
    die('<h1>Hata</h1><p>Bu dosyayı WordPress ana dizinine kopyalayın ve çalıştırın.</p>');
}

// Güvenlik kontrolü
if (!current_user_can('manage_options')) {
    wp_die('Bu sayfaya erişim yetkiniz yok. Lütfen admin olarak giriş yapın.');
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>QMC Basit Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #23282d; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        .status-ok { color: #46b450; font-weight: bold; }
        .status-error { color: #dc3232; font-weight: bold; }
        .info-box { background: #f9f9f9; border-left: 4px solid #0073aa; padding: 15px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f1f1f1; }
        code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Quick Menu Cards - Basit Debug</h1>
        
        <div class="info-box">
            <strong>Not:</strong> Bu araç, Esistenze WordPress Kit eklentisinin durumunu kontrol eder.
        </div>

        <h2>📊 Temel Bilgiler</h2>
        <table>
            <tr>
                <th>Özellik</th>
                <th>Değer</th>
                <th>Durum</th>
            </tr>
            <tr>
                <td>WordPress Versiyonu</td>
                <td><?php echo get_bloginfo('version'); ?></td>
                <td><span class="status-ok">✅</span></td>
            </tr>
            <tr>
                <td>PHP Versiyonu</td>
                <td><?php echo phpversion(); ?></td>
                <td><span class="status-ok">✅</span></td>
            </tr>
            <tr>
                <td>Aktif Tema</td>
                <td><?php echo wp_get_theme()->get('Name'); ?></td>
                <td><span class="status-ok">✅</span></td>
            </tr>
            <tr>
                <td>Kullanıcı</td>
                <td><?php echo wp_get_current_user()->user_login; ?> (<?php echo implode(', ', wp_get_current_user()->roles); ?>)</td>
                <td><span class="status-ok">✅</span></td>
            </tr>
        </table>

        <h2>🔌 Eklenti Durumu</h2>
        <table>
            <?php
            $plugin_file = 'Esistenze-Wordpress-Kit/esistenze_main_plugin.php';
            $plugin_file_alt = 'esistenze-wordpress-kit/esistenze_main_plugin.php';
            
            $is_active = is_plugin_active($plugin_file) || is_plugin_active($plugin_file_alt);
            $plugin_path = WP_PLUGIN_DIR . '/' . ($is_plugin_active($plugin_file) ? $plugin_file : $plugin_file_alt);
            ?>
            <tr>
                <td>Esistenze Kit Aktif mi?</td>
                <td><?php echo $is_active ? 'Evet' : 'Hayır'; ?></td>
                <td><?php echo $is_active ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>'; ?></td>
            </tr>
            <tr>
                <td>Eklenti Yolu</td>
                <td><code><?php echo $plugin_path; ?></code></td>
                <td><?php echo file_exists($plugin_path) ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>'; ?></td>
            </tr>
        </table>

        <h2>📁 Dosya Yapısı</h2>
        <table>
            <?php
            $plugin_dir = dirname($plugin_path) . '/';
            $files_to_check = [
                'Ana Plugin' => 'esistenze_main_plugin.php',
                'QMC Modülü' => 'modules/quick-menu-cards/quick-menu-cards.php',
                'QMC Admin' => 'modules/quick-menu-cards/includes/class-admin.php',
                'QMC Frontend' => 'modules/quick-menu-cards/includes/class-frontend.php',
                'QMC AJAX' => 'modules/quick-menu-cards/includes/class-ajax.php',
                'QMC Shortcodes' => 'modules/quick-menu-cards/includes/class-shortcodes.php',
            ];
            
            foreach ($files_to_check as $label => $file) {
                $full_path = $plugin_dir . $file;
                $exists = file_exists($full_path);
                echo '<tr>';
                echo '<td>' . $label . '</td>';
                echo '<td><code>' . $file . '</code></td>';
                echo '<td>' . ($exists ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>') . '</td>';
                echo '</tr>';
            }
            ?>
        </table>

        <h2>🎯 Sınıf Durumu</h2>
        <table>
            <?php
            $classes_to_check = [
                'EsistenzeWPKit' => 'Ana Plugin Sınıfı',
                'EsistenzeQuickMenuCards' => 'QMC Ana Sınıf',
                'EsistenzeQuickMenuCardsAdmin' => 'QMC Admin Sınıfı',
                'EsistenzeQuickMenuCardsFrontend' => 'QMC Frontend Sınıfı',
                'EsistenzeQuickMenuCardsAjax' => 'QMC AJAX Sınıfı',
                'EsistenzeQuickMenuCardsShortcodes' => 'QMC Shortcodes Sınıfı',
            ];
            
            foreach ($classes_to_check as $class => $label) {
                $exists = class_exists($class);
                echo '<tr>';
                echo '<td>' . $label . '</td>';
                echo '<td><code>' . $class . '</code></td>';
                echo '<td>' . ($exists ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>') . '</td>';
                echo '</tr>';
            }
            ?>
        </table>

        <h2>📋 WordPress Menü</h2>
        <table>
            <?php
            global $menu, $submenu;
            $main_menu_exists = isset($submenu['esistenze-wp-kit']);
            ?>
            <tr>
                <td>Ana Menü (esistenze-wp-kit)</td>
                <td><?php echo $main_menu_exists ? 'Mevcut' : 'Bulunamadı'; ?></td>
                <td><?php echo $main_menu_exists ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>'; ?></td>
            </tr>
            <?php if ($main_menu_exists): ?>
                <?php foreach ($submenu['esistenze-wp-kit'] as $item): ?>
                <tr>
                    <td>Alt Menü: <?php echo $item[0]; ?></td>
                    <td><code><?php echo $item[2]; ?></code></td>
                    <td><span class="status-ok">✅</span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>

        <h2>🔧 Öneriler</h2>
        <div class="info-box">
            <?php if (!$is_active): ?>
                <p><strong>❌ Eklenti aktif değil:</strong> WordPress admin panelinden "Esistenze WordPress Kit" eklentisini aktifleştirin.</p>
            <?php endif; ?>
            
            <?php if (!$main_menu_exists): ?>
                <p><strong>❌ Menü bulunamadı:</strong> Eklentiyi deaktif edip tekrar aktifleştirmeyi deneyin.</p>
            <?php endif; ?>
            
            <?php
            $missing_classes = [];
            foreach ($classes_to_check as $class => $label) {
                if (!class_exists($class)) {
                    $missing_classes[] = $class;
                }
            }
            if (!empty($missing_classes)):
            ?>
                <p><strong>❌ Eksik sınıflar:</strong> <?php echo implode(', ', $missing_classes); ?> sınıfları yüklenemedi. Dosya izinlerini kontrol edin.</p>
            <?php endif; ?>
            
            <p><strong>💡 İpucu:</strong> Sorun devam ediyorsa, eklentiyi tamamen kaldırıp tekrar yükleyin.</p>
        </div>

        <hr>
        <p><small>Debug zamanı: <?php echo date('Y-m-d H:i:s'); ?></small></p>
    </div>
</body>
</html> 