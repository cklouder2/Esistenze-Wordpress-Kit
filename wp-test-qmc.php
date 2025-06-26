<?php
/**
 * WordPress Ortamında Quick Menu Cards Test
 * Bu dosyayı WordPress admin dizinine koyup çalıştırın
 */

// WordPress yükleme kontrolü
if (!defined('ABSPATH')) {
    // WordPress'i yüklemeye çalış
    $wp_load_paths = array(
        '../../../wp-load.php',
        '../../wp-load.php',
        '../wp-load.php',
        'wp-load.php'
    );
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('WordPress bulunamadı! Bu dosyayı WordPress admin dizinine koyun.');
    }
}

// Admin kontrolü
if (!is_admin()) {
    wp_redirect(admin_url('admin.php?page=wp-test-qmc'));
    exit;
}

// Yetki kontrolü
if (!current_user_can('manage_options')) {
    wp_die('Bu teste erişim yetkiniz yok.');
}

// HTML başlat
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Menu Cards Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .wrap { background: #fff; padding: 20px; border-radius: 5px; max-width: 1200px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #00a0d2; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f9f9f9; }
        .status-ok { color: #46b450; font-weight: bold; }
        .status-error { color: #dc3232; font-weight: bold; }
        .status-warning { color: #ffb900; font-weight: bold; }
        .button { display: inline-block; padding: 8px 16px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 3px; margin: 5px; }
        .button:hover { background: #005a87; color: #fff; }
        .code-block { background: #f8f8f8; padding: 10px; border-radius: 3px; font-family: monospace; overflow-x: auto; }
        .debug-info { background: #e8f4fd; padding: 10px; border-left: 4px solid #00a0d2; margin: 10px 0; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>🧪 Quick Menu Cards - WordPress Test</h1>
    <p><strong>Test Zamanı:</strong> <?php echo current_time('Y-m-d H:i:s'); ?></p>
    
    <?php
    // Test başlat
    $test_results = array();
    $start_time = microtime(true);
    
    // 1. WordPress Ortam Kontrolü
    echo '<div class="test-section">';
    echo '<h3>1. WordPress Ortam Kontrolü</h3>';
    
    echo '<table>';
    echo '<tr><th>Kontrol</th><th>Durum</th><th>Değer</th></tr>';
    echo '<tr><td>WordPress Sürümü</td><td class="status-ok">✅ OK</td><td>' . get_bloginfo('version') . '</td></tr>';
    echo '<tr><td>PHP Sürümü</td><td class="status-ok">✅ OK</td><td>' . PHP_VERSION . '</td></tr>';
    echo '<tr><td>Admin Panel</td><td class="status-ok">✅ OK</td><td>' . (is_admin() ? 'Evet' : 'Hayır') . '</td></tr>';
    echo '<tr><td>Site URL</td><td class="status-ok">✅ OK</td><td>' . home_url() . '</td></tr>';
    echo '<tr><td>Admin URL</td><td class="status-ok">✅ OK</td><td>' . admin_url() . '</td></tr>';
    echo '</table>';
    
    echo '</div>';
    
    // 2. Kullanıcı ve Yetki Kontrolü
    echo '<div class="test-section">';
    echo '<h3>2. Kullanıcı ve Yetki Kontrolü</h3>';
    
    $current_user = wp_get_current_user();
    
    echo '<div class="debug-info">';
    echo '<strong>Mevcut Kullanıcı:</strong><br>';
    echo 'ID: ' . $current_user->ID . '<br>';
    echo 'Kullanıcı Adı: ' . $current_user->user_login . '<br>';
    echo 'Email: ' . $current_user->user_email . '<br>';
    echo 'Roller: ' . implode(', ', $current_user->roles) . '<br>';
    echo '</div>';
    
    $capabilities_to_test = array('read', 'edit_posts', 'manage_options', 'upload_files', 'edit_pages');
    
    echo '<table>';
    echo '<tr><th>Yetki</th><th>Durum</th><th>Açıklama</th></tr>';
    
    foreach ($capabilities_to_test as $cap) {
        $has_cap = current_user_can($cap);
        echo '<tr>';
        echo '<td><code>' . $cap . '</code></td>';
        echo '<td class="' . ($has_cap ? 'status-ok">✅ VAR' : 'status-error">❌ YOK') . '</td>';
        echo '<td>' . ($has_cap ? 'Kullanıcı bu yetkiye sahip' : 'Kullanıcı bu yetkiye sahip değil') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '</div>';
    
    // 3. Plugin Durumu Kontrolü
    echo '<div class="test-section">';
    echo '<h3>3. Esistenze Plugin Durumu</h3>';
    
    // Plugin dosyası kontrolü
    $plugin_file = WP_PLUGIN_DIR . '/esistenze-wordpress-kit/esistenze_main_plugin.php';
    $plugin_exists = file_exists($plugin_file);
    
    echo '<table>';
    echo '<tr><th>Kontrol</th><th>Durum</th><th>Detay</th></tr>';
    echo '<tr><td>Plugin Dosyası</td><td class="' . ($plugin_exists ? 'status-ok">✅ Mevcut' : 'status-error">❌ Bulunamadı') . '</td><td>' . $plugin_file . '</td></tr>';
    
    // Plugin aktiflik kontrolü
    $active_plugins = get_option('active_plugins', array());
    $plugin_active = false;
    foreach ($active_plugins as $plugin) {
        if (strpos($plugin, 'esistenze') !== false) {
            $plugin_active = true;
            echo '<tr><td>Plugin Aktif</td><td class="status-ok">✅ Aktif</td><td>' . $plugin . '</td></tr>';
            break;
        }
    }
    
    if (!$plugin_active) {
        echo '<tr><td>Plugin Aktif</td><td class="status-error">❌ Pasif</td><td>Esistenze plugin aktif değil</td></tr>';
    }
    
    echo '</table>';
    
    echo '</div>';
    
    // 4. Sınıf ve Fonksiyon Kontrolü
    echo '<div class="test-section">';
    echo '<h3>4. Sınıf ve Fonksiyon Kontrolü</h3>';
    
    $classes_to_check = array(
        'EsistenzeWPKit' => 'Ana plugin sınıfı',
        'EsistenzeQuickMenuCards' => 'QMC ana sınıfı',
        'EsistenzeQuickMenuCardsAdmin' => 'QMC admin sınıfı',
        'EsistenzeQuickMenuCardsFrontend' => 'QMC frontend sınıfı',
        'EsistenzeQuickMenuCardsShortcodes' => 'QMC shortcodes sınıfı',
        'EsistenzeQuickMenuCardsAjax' => 'QMC AJAX sınıfı'
    );
    
    echo '<table>';
    echo '<tr><th>Sınıf</th><th>Durum</th><th>Açıklama</th></tr>';
    
    foreach ($classes_to_check as $class => $description) {
        $exists = class_exists($class);
        echo '<tr>';
        echo '<td><code>' . $class . '</code></td>';
        echo '<td class="' . ($exists ? 'status-ok">✅ Mevcut' : 'status-error">❌ Bulunamadı') . '</td>';
        echo '<td>' . $description . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // Fonksiyon kontrolü
    $functions_to_check = array(
        'esistenze_qmc_capability' => 'QMC yetki fonksiyonu'
    );
    
    echo '<h4>Fonksiyon Kontrolü</h4>';
    echo '<table>';
    echo '<tr><th>Fonksiyon</th><th>Durum</th><th>Dönen Değer</th></tr>';
    
    foreach ($functions_to_check as $function => $description) {
        $exists = function_exists($function);
        echo '<tr>';
        echo '<td><code>' . $function . '</code></td>';
        echo '<td class="' . ($exists ? 'status-ok">✅ Mevcut' : 'status-error">❌ Bulunamadı') . '</td>';
        
        if ($exists) {
            try {
                $result = call_user_func($function);
                echo '<td><code>' . esc_html($result) . '</code></td>';
            } catch (Exception $e) {
                echo '<td class="error">Hata: ' . esc_html($e->getMessage()) . '</td>';
            }
        } else {
            echo '<td>-</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    
    echo '</div>';
    
    // 5. WordPress Menü Sistemi Kontrolü
    echo '<div class="test-section">';
    echo '<h3>5. WordPress Menü Sistemi Kontrolü</h3>';
    
    global $menu, $submenu;
    
    echo '<h4>Ana Menüler</h4>';
    if (isset($menu) && is_array($menu)) {
        echo '<table>';
        echo '<tr><th>Menü Başlığı</th><th>Slug</th><th>Yetki</th></tr>';
        
        $esistenze_menu_found = false;
        foreach ($menu as $menu_item) {
            if (isset($menu_item[2]) && strpos($menu_item[2], 'esistenze') !== false) {
                $esistenze_menu_found = true;
                echo '<tr>';
                echo '<td>' . esc_html($menu_item[0]) . '</td>';
                echo '<td><code>' . esc_html($menu_item[2]) . '</code></td>';
                echo '<td><code>' . esc_html($menu_item[1]) . '</code></td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        
        if (!$esistenze_menu_found) {
            echo '<p class="error">❌ Esistenze ana menüsü bulunamadı!</p>';
        }
    } else {
        echo '<p class="error">❌ WordPress menü sistemi yüklenemedi!</p>';
    }
    
    echo '<h4>Alt Menüler (Submenu)</h4>';
    if (isset($submenu) && is_array($submenu)) {
        $qmc_submenus_found = false;
        
        foreach ($submenu as $parent_slug => $submenu_items) {
            if (strpos($parent_slug, 'esistenze') !== false) {
                echo '<h5>Ana Menü: ' . esc_html($parent_slug) . '</h5>';
                echo '<table>';
                echo '<tr><th>Alt Menü Başlığı</th><th>Slug</th><th>Yetki</th></tr>';
                
                foreach ($submenu_items as $item) {
                    echo '<tr>';
                    echo '<td>' . esc_html($item[0]) . '</td>';
                    echo '<td><code>' . esc_html($item[2]) . '</code></td>';
                    echo '<td><code>' . esc_html($item[1]) . '</code></td>';
                    echo '</tr>';
                    
                    if (strpos($item[2], 'quick-menu') !== false) {
                        $qmc_submenus_found = true;
                    }
                }
                echo '</table>';
            }
        }
        
        if (!$qmc_submenus_found) {
            echo '<p class="warning">⚠️ Quick Menu Cards alt menüleri bulunamadı!</p>';
        } else {
            echo '<p class="success">✅ Quick Menu Cards alt menüleri bulundu!</p>';
        }
    } else {
        echo '<p class="error">❌ WordPress submenu sistemi yüklenemedi!</p>';
    }
    
    echo '</div>';
    
    // 6. QMC Admin Sınıfı Test
    echo '<div class="test-section">';
    echo '<h3>6. Quick Menu Cards Admin Sınıfı Testi</h3>';
    
    if (class_exists('EsistenzeQuickMenuCardsAdmin')) {
        echo '<p class="success">✅ EsistenzeQuickMenuCardsAdmin sınıfı mevcut</p>';
        
        // Admin sayfası erişim testi
        $qmc_page_url = admin_url('admin.php?page=esistenze-quick-menu');
        echo '<p><strong>QMC Admin Sayfası:</strong> <a href="' . $qmc_page_url . '" class="button" target="_blank">Test Et</a></p>';
        
        // Capability testi
        if (function_exists('esistenze_qmc_capability')) {
            $required_cap = esistenze_qmc_capability();
            $user_has_cap = current_user_can($required_cap);
            
            echo '<div class="debug-info">';
            echo '<strong>Yetki Testi:</strong><br>';
            echo 'Gerekli Yetki: <code>' . $required_cap . '</code><br>';
            echo 'Kullanıcı Yetkisi: ' . ($user_has_cap ? '<span class="success">✅ VAR</span>' : '<span class="error">❌ YOK</span>') . '<br>';
            echo '</div>';
            
            if (!$user_has_cap) {
                echo '<p class="error">❌ Kullanıcınız gerekli yetkiye sahip değil! Admin olarak giriş yapın.</p>';
            }
        }
        
    } else {
        echo '<p class="error">❌ EsistenzeQuickMenuCardsAdmin sınıfı bulunamadı!</p>';
    }
    
    echo '</div>';
    
    // 7. Veritabanı ve Options Testi
    echo '<div class="test-section">';
    echo '<h3>7. Veritabanı ve Options Testi</h3>';
    
    // QMC verilerini kontrol et
    $qmc_data = get_option('esistenze_quick_menu_kartlari', array());
    $qmc_settings = get_option('esistenze_quick_menu_settings', array());
    
    echo '<table>';
    echo '<tr><th>Option</th><th>Durum</th><th>Veri</th></tr>';
    echo '<tr><td>esistenze_quick_menu_kartlari</td><td class="' . (empty($qmc_data) ? 'status-warning">⚠️ Boş' : 'status-ok">✅ Mevcut') . '</td><td>' . count($qmc_data) . ' grup</td></tr>';
    echo '<tr><td>esistenze_quick_menu_settings</td><td class="' . (empty($qmc_settings) ? 'status-warning">⚠️ Boş' : 'status-ok">✅ Mevcut') . '</td><td>' . count($qmc_settings) . ' ayar</td></tr>';
    echo '</table>';
    
    // Test verisi oluştur
    if (isset($_GET['create_test_data'])) {
        $test_data = array(
            time() => array(
                'name' => 'Test Grup - ' . date('Y-m-d H:i:s'),
                'cards' => array(
                    array(
                        'title' => 'Test Kart 1',
                        'description' => 'Bu bir test kartıdır',
                        'image' => '',
                        'link' => home_url(),
                        'button_text' => 'Test Et',
                        'type' => 'card'
                    )
                ),
                'created' => current_time('mysql'),
                'updated' => current_time('mysql')
            )
        );
        
        $existing_data = get_option('esistenze_quick_menu_kartlari', array());
        $merged_data = array_merge($existing_data, $test_data);
        
        if (update_option('esistenze_quick_menu_kartlari', $merged_data)) {
            echo '<p class="success">✅ Test verisi başarıyla oluşturuldu!</p>';
        } else {
            echo '<p class="error">❌ Test verisi oluşturulamadı!</p>';
        }
    }
    
    echo '<p><a href="?create_test_data=1" class="button">Test Verisi Oluştur</a></p>';
    
    echo '</div>';
    
    // 8. Shortcode Testi
    echo '<div class="test-section">';
    echo '<h3>8. Shortcode Testi</h3>';
    
    global $shortcode_tags;
    
    $qmc_shortcodes = array(
        'quick_menu_cards',
        'quick_menu_banner',
        'hizli_menu',
        'hizli_menu_banner'
    );
    
    echo '<table>';
    echo '<tr><th>Shortcode</th><th>Durum</th><th>Test</th></tr>';
    
    foreach ($qmc_shortcodes as $shortcode) {
        $is_registered = isset($shortcode_tags[$shortcode]);
        echo '<tr>';
        echo '<td><code>[' . $shortcode . ']</code></td>';
        echo '<td class="' . ($is_registered ? 'status-ok">✅ Kayıtlı' : 'status-error">❌ Kayıtlı Değil') . '</td>';
        
        if ($is_registered && !empty($qmc_data)) {
            $first_group_id = array_keys($qmc_data)[0];
            $test_shortcode = '[' . $shortcode . ' id="' . $first_group_id . '"]';
            echo '<td><code>' . esc_html($test_shortcode) . '</code></td>';
        } else {
            echo '<td>-</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    
    echo '</div>';
    
    // 9. Özet ve Öneriler
    echo '<div class="test-section">';
    echo '<h3>9. Test Özeti ve Öneriler</h3>';
    
    $total_time = microtime(true) - $start_time;
    
    echo '<div class="debug-info">';
    echo '<strong>Test Özeti:</strong><br>';
    echo 'Test Süresi: ' . number_format($total_time, 3) . ' saniye<br>';
    echo 'WordPress Sürümü: ' . get_bloginfo('version') . '<br>';
    echo 'PHP Sürümü: ' . PHP_VERSION . '<br>';
    echo 'Kullanıcı: ' . $current_user->user_login . ' (' . implode(', ', $current_user->roles) . ')<br>';
    echo '</div>';
    
    // Sorun tespiti
    $issues = array();
    
    if (!$plugin_active) {
        $issues[] = 'Esistenze plugin aktif değil';
    }
    
    if (!class_exists('EsistenzeQuickMenuCardsAdmin')) {
        $issues[] = 'QMC Admin sınıfı yüklenemedi';
    }
    
    if (!function_exists('esistenze_qmc_capability')) {
        $issues[] = 'Capability fonksiyonu eksik';
    }
    
    $required_cap = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'edit_posts';
    if (!current_user_can($required_cap)) {
        $issues[] = 'Kullanıcı gerekli yetkiye sahip değil: ' . $required_cap;
    }
    
    if (empty($issues)) {
        echo '<p class="success">🎉 <strong>Tüm testler başarılı!</strong> Quick Menu Cards çalışmaya hazır.</p>';
        echo '<p><a href="' . admin_url('admin.php?page=esistenze-quick-menu') . '" class="button">Quick Menu Cards\'a Git</a></p>';
    } else {
        echo '<p class="error"><strong>Tespit Edilen Sorunlar:</strong></p>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li class="error">' . esc_html($issue) . '</li>';
        }
        echo '</ul>';
        
        echo '<p><strong>Öneriler:</strong></p>';
        echo '<ul>';
        if (!$plugin_active) {
            echo '<li>Esistenze WordPress Kit pluginini aktifleştirin</li>';
        }
        if (!current_user_can($required_cap)) {
            echo '<li>Administrator rolüne sahip bir kullanıcı ile giriş yapın</li>';
        }
        echo '<li>Plugin dosyalarının doğru yüklendiğinden emin olun</li>';
        echo '<li>WordPress ve PHP sürümlerinin güncel olduğunu kontrol edin</li>';
        echo '</ul>';
    }
    
    echo '</div>';
    ?>
    
    <div class="test-section">
        <h3>Hızlı Linkler</h3>
        <p>
            <a href="<?php echo admin_url('plugins.php'); ?>" class="button">Plugin Yöneticisi</a>
            <a href="<?php echo admin_url('admin.php?page=esistenze-wp-kit'); ?>" class="button">Esistenze Dashboard</a>
            <a href="<?php echo admin_url('admin.php?page=esistenze-quick-menu'); ?>" class="button">Quick Menu Cards</a>
            <a href="<?php echo admin_url('users.php'); ?>" class="button">Kullanıcı Yönetimi</a>
        </p>
    </div>
    
</div>
</body>
</html> 