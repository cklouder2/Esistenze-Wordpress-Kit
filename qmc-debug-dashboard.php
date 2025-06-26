<?php
/**
 * Quick Menu Cards - Debug Dashboard
 * Hızlı sorun tespiti için basit dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

// Sadece admin
if (!current_user_can('manage_options')) {
    wp_die('Yetkiniz yok.');
}

?>
<div class="wrap">
    <h1>🔍 Quick Menu Cards - Debug Dashboard</h1>
    
    <div class="notice notice-info">
        <p><strong>Hızlı Durum Kontrolü:</strong> Bu sayfa QMC'nin durumunu anlık olarak kontrol eder.</p>
    </div>
    
    <?php
    $errors = array();
    $warnings = array();
    $success = array();
    
    // 1. Plugin Aktiflik
    $active_plugins = get_option('active_plugins', array());
    $plugin_active = false;
    foreach ($active_plugins as $plugin) {
        if (strpos($plugin, 'esistenze') !== false) {
            $plugin_active = true;
            $success[] = 'Plugin aktif: ' . $plugin;
            break;
        }
    }
    if (!$plugin_active) {
        $errors[] = 'Esistenze plugin aktif değil!';
    }
    
    // 2. Sınıf Kontrolü
    $classes = array('EsistenzeWPKit', 'EsistenzeQuickMenuCards', 'EsistenzeQuickMenuCardsAdmin');
    foreach ($classes as $class) {
        if (class_exists($class)) {
            $success[] = "Sınıf yüklü: $class";
        } else {
            $errors[] = "Sınıf eksik: $class";
        }
    }
    
    // 3. Fonksiyon Kontrolü
    if (function_exists('esistenze_qmc_capability')) {
        $cap = esistenze_qmc_capability();
        $success[] = "Capability fonksiyonu çalışıyor: $cap";
        
        if (current_user_can($cap)) {
            $success[] = "Kullanıcı gerekli yetkiye sahip: $cap";
        } else {
            $errors[] = "Kullanıcı gerekli yetkiye sahip değil: $cap";
        }
    } else {
        $errors[] = 'esistenze_qmc_capability fonksiyonu bulunamadı';
    }
    
    // 4. Menü Kontrolü
    global $submenu;
    if (isset($submenu['esistenze-wp-kit'])) {
        $success[] = 'Esistenze ana menüsü mevcut';
        
        $qmc_found = false;
        foreach ($submenu['esistenze-wp-kit'] as $item) {
            if (strpos($item[2], 'quick-menu') !== false) {
                $qmc_found = true;
                break;
            }
        }
        
        if ($qmc_found) {
            $success[] = 'QMC alt menüleri bulundu';
        } else {
            $warnings[] = 'QMC alt menüleri bulunamadı';
        }
    } else {
        $errors[] = 'Esistenze ana menüsü bulunamadı';
    }
    
    // 5. Kullanıcı Bilgileri
    $current_user = wp_get_current_user();
    echo '<h3>👤 Kullanıcı Bilgileri</h3>';
    echo '<table class="widefat">';
    echo '<tr><th>Bilgi</th><th>Değer</th></tr>';
    echo '<tr><td>Kullanıcı</td><td>' . $current_user->user_login . '</td></tr>';
    echo '<tr><td>Roller</td><td>' . implode(', ', $current_user->roles) . '</td></tr>';
    echo '<tr><td>ID</td><td>' . $current_user->ID . '</td></tr>';
    echo '</table>';
    
    // Sonuçları göster
    if (!empty($errors)) {
        echo '<h3 style="color: red;">🚨 Kritik Hatalar</h3>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li style="color: red;">❌ ' . esc_html($error) . '</li>';
        }
        echo '</ul>';
    }
    
    if (!empty($warnings)) {
        echo '<h3 style="color: orange;">⚠️ Uyarılar</h3>';
        echo '<ul>';
        foreach ($warnings as $warning) {
            echo '<li style="color: orange;">⚠️ ' . esc_html($warning) . '</li>';
        }
        echo '</ul>';
    }
    
    if (!empty($success)) {
        echo '<h3 style="color: green;">✅ Başarılı Kontroller</h3>';
        echo '<ul>';
        foreach ($success as $s) {
            echo '<li style="color: green;">✅ ' . esc_html($s) . '</li>';
        }
        echo '</ul>';
    }
    
    // Sonuç
    if (empty($errors)) {
        echo '<div class="notice notice-success">';
        echo '<h3>🎉 Harika! Hiç kritik hata yok!</h3>';
        echo '<p>Quick Menu Cards çalışmaya hazır görünüyor.</p>';
        echo '<p><a href="' . admin_url('admin.php?page=esistenze-quick-menu') . '" class="button button-primary">Quick Menu Cards\'a Git</a></p>';
        echo '</div>';
    } else {
        echo '<div class="notice notice-error">';
        echo '<h3>🔧 Sorunlar Tespit Edildi</h3>';
        echo '<p>Yukarıdaki hataları çözmeniz gerekiyor.</p>';
        echo '<p><a href="' . admin_url('admin.php?page=esistenze-qmc-debug') . '" class="button">Detaylı Debug</a></p>';
        echo '</div>';
    }
    ?>
    
    <h3>🔗 Hızlı Linkler</h3>
    <p>
        <a href="<?php echo admin_url('admin.php?page=esistenze-quick-menu'); ?>" class="button button-primary">Quick Menu Cards</a>
        <a href="<?php echo admin_url('admin.php?page=esistenze-qmc-debug'); ?>" class="button">Gelişmiş Debug</a>
        <a href="<?php echo admin_url('plugins.php'); ?>" class="button">Plugin Yöneticisi</a>
    </p>
    
</div> 