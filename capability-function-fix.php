<?php
/*
 * Bu kodu ana eklenti dosyasına veya functions.php'ye ekleyin
 * Eğer fonksiyon zaten varsa, mevcut olanı bu şekilde güncelleyin
 */

if (!function_exists('esistenze_qmc_capability')) {
    function esistenze_qmc_capability() {
        // Öncelikle en temel yetkileri deneyelim
        if (current_user_can('manage_options')) {
            return 'manage_options';
        }
        
        if (current_user_can('edit_pages')) {
            return 'edit_pages';
        }
        
        if (current_user_can('edit_posts')) {
            return 'edit_posts';
        }
        
        // Varsayılan olarak en düşük admin yetkisi
        return 'edit_posts';
    }
}

// Alternatif: Daha basit bir versiyon
if (!function_exists('esistenze_qmc_capability_simple')) {
    function esistenze_qmc_capability_simple() {
        return 'edit_posts'; // En temel yetki
    }
}

// Test için debug fonksiyonu
if (!function_exists('esistenze_debug_capabilities')) {
    function esistenze_debug_capabilities() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        $current_user = wp_get_current_user();
        
        echo '<div style="background: #fff; padding: 20px; border: 1px solid #ccc; margin: 20px;">';
        echo '<h3>Kullanıcı Yetki Bilgileri:</h3>';
        echo '<p><strong>Kullanıcı ID:</strong> ' . $current_user->ID . '</p>';
        echo '<p><strong>Kullanıcı Rolü:</strong> ' . implode(', ', $current_user->roles) . '</p>';
        echo '<p><strong>manage_options:</strong> ' . (current_user_can('manage_options') ? 'Evet' : 'Hayır') . '</p>';
        echo '<p><strong>edit_pages:</strong> ' . (current_user_can('edit_pages') ? 'Evet' : 'Hayır') . '</p>';
        echo '<p><strong>edit_posts:</strong> ' . (current_user_can('edit_posts') ? 'Evet' : 'Hayır') . '</p>';
        echo '<p><strong>esistenze_qmc_capability():</strong> ' . (function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'Fonksiyon tanımlı değil') . '</p>';
        echo '</div>';
    }
}

// Debug için admin sayfasına hook ekle
add_action('admin_notices', function() {
    if (isset($_GET['debug_esistenze']) && current_user_can('manage_options')) {
        esistenze_debug_capabilities();
    }
});