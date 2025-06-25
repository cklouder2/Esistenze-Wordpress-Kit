<?php
/*
 * Quick Menu Cards - Admin Tools View
 * Araçlar ve bakım sayfası - Eksik PHP kısımları
 */

if (!defined('ABSPATH')) {
    exit;
}

// Bu dosya admin-tools.php'nin eksik kalan PHP kısımları

// AJAX handlers için functions
add_action('wp_ajax_esistenze_export_data', 'esistenze_handle_export_data');
add_action('wp_ajax_esistenze_import_data', 'esistenze_handle_import_data');
add_action('wp_ajax_esistenze_clear_cache', 'esistenze_handle_clear_cache');
add_action('wp_ajax_esistenze_db_action', 'esistenze_handle_db_action');
add_action('wp_ajax_esistenze_check_error_log', 'esistenze_handle_check_error_log');
add_action('wp_ajax_esistenze_test_conflicts', 'esistenze_handle_test_conflicts');
add_action('wp_ajax_esistenze_performance_test', 'esistenze_handle_performance_test');
add_action('wp_ajax_esistenze_maintenance_mode', 'esistenze_handle_maintenance_mode');
add_action('wp_ajax_esistenze_get_debug_log', 'esistenze_handle_get_debug_log');
add_action('wp_ajax_esistenze_clear_debug_log', 'esistenze_handle_clear_debug_log');
add_action('wp_ajax_esistenze_download_system_info', 'esistenze_handle_download_system_info');

function esistenze_handle_export_data() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $export_type = sanitize_text_field($_POST['export_type']);
    $export_data = array();
    
    switch ($export_type) {
        case 'all':
            $export_data = array(
                'version' => defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0',
                'export_date' => current_time('mysql'),
                'site_url' => home_url(),
                'groups' => get_option('esistenze_quick_menu_kartlari', array()),
                'settings' => get_option('esistenze_quick_menu_settings', array()),
                'analytics' => get_option('esistenze_quick_menu_analytics', array())
            );
            $filename = 'quick-menu-cards-full-export-' . date('Y-m-d-H-i-s') . '.json';
            break;
            
        case 'groups':
            $export_data = array(
                'version' => defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0',
                'export_date' => current_time('mysql'),
                'groups' => get_option('esistenze_quick_menu_kartlari', array())
            );
            $filename = 'quick-menu-cards-groups-' . date('Y-m-d-H-i-s') . '.json';
            break;
            
        case 'settings':
            $export_data = array(
                'version' => defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0',
                'export_date' => current_time('mysql'),
                'settings' => get_option('esistenze_quick_menu_settings', array())
            );
            $filename = 'quick-menu-cards-settings-' . date('Y-m-d-H-i-s') . '.json';
            break;
            
        default:
            wp_send_json_error('Geçersiz export tipi.');
    }
    
    wp_send_json_success(array(
        'data' => $export_data,
        'filename' => $filename
    ));
}

function esistenze_handle_import_data() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $import_data = $_POST['import_data'] ?? '';
    
    if (empty($import_data)) {
        wp_send_json_error('Import verisi boş.');
    }
    
    // JSON decode
    $data = json_decode(stripslashes($import_data), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Geçersiz JSON formatı: ' . json_last_error_msg());
    }
    
    // Veri kontrolü
    if (!is_array($data)) {
        wp_send_json_error('Geçersiz veri formatı.');
    }
    
    $imported_items = 0;
    $errors = array();
    
    // Grupları içe aktar
    if (!empty($data['groups']) && is_array($data['groups'])) {
        $sanitized_groups = array();
        foreach ($data['groups'] as $group_id => $group) {
            if (!is_array($group)) {
                $errors[] = "Grup #{$group_id}: Geçersiz format";
                continue;
            }
            
            $sanitized_group = array();
            foreach ($group as $card_index => $card) {
                if (!is_array($card)) {
                    $errors[] = "Grup #{$group_id}, Kart #{$card_index}: Geçersiz format";
                    continue;
                }
                
                // Sanitize card data
                $sanitized_card = array(
                    'title' => sanitize_text_field($card['title'] ?? ''),
                    'desc' => sanitize_textarea_field($card['desc'] ?? ''),
                    'img' => esc_url_raw($card['img'] ?? ''),
                    'url' => esc_url_raw($card['url'] ?? ''),
                    'order' => intval($card['order'] ?? 0),
                    'enabled' => !empty($card['enabled']),
                    'created_at' => $card['created_at'] ?? current_time('mysql'),
                    'updated_at' => current_time('mysql')
                );
                
                $sanitized_group[] = $sanitized_card;
            }
            
            if (!empty($sanitized_group)) {
                $sanitized_groups[] = $sanitized_group;
                $imported_items++;
            }
        }
        
        if (!empty($sanitized_groups)) {
            update_option('esistenze_quick_menu_kartlari', $sanitized_groups);
        }
    }
    
    // Ayarları içe aktar
    if (!empty($data['settings']) && is_array($data['settings'])) {
        $defaults = EsistenzeQuickMenuCards::get_default_settings();
        $sanitized_settings = array();
        
        foreach ($defaults as $key => $default_value) {
            if (isset($data['settings'][$key])) {
                switch ($key) {
                    case 'default_button_text':
                    case 'banner_button_text':
                        $sanitized_settings[$key] = sanitize_text_field($data['settings'][$key]);
                        break;
                    case 'custom_css':
                        $sanitized_settings[$key] = wp_strip_all_tags($data['settings'][$key]);
                        break;
                    case 'enable_analytics':
                    case 'enable_lazy_loading':
                    case 'enable_schema_markup':
                    case 'enable_gpu_acceleration':
                    case 'enable_dark_mode':
                        $sanitized_settings[$key] = !empty($data['settings'][$key]);
                        break;
                    case 'mobile_columns':
                    case 'cache_duration':
                        $sanitized_settings[$key] = max(0, intval($data['settings'][$key]));
                        break;
                    default:
                        $sanitized_settings[$key] = $default_value;
                        break;
                }
            } else {
                $sanitized_settings[$key] = $default_value;
            }
        }
        
        update_option('esistenze_quick_menu_settings', $sanitized_settings);
    }
    
    // Cache temizle
    esistenze_clear_plugin_cache();
    
    if ($imported_items > 0) {
        $message = "İçe aktarma başarılı! {$imported_items} grup içe aktarıldı.";
        if (!empty($errors)) {
            $message .= " Hatalar: " . implode(', ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= " ve " . (count($errors) - 3) . " daha...";
            }
        }
        wp_send_json_success(array(
            'message' => $message,
            'imported_count' => $imported_items,
            'error_count' => count($errors)
        ));
    } else {
        wp_send_json_error('İçe aktarılacak geçerli veri bulunamadı. Hatalar: ' . implode(', ', $errors));
    }
}

function esistenze_handle_clear_cache() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $cache_type = sanitize_text_field($_POST['cache_type']);
    
    switch ($cache_type) {
        case 'plugin':
            esistenze_clear_plugin_cache();
            wp_send_json_success('Plugin cache temizlendi.');
            break;
            
        case 'all':
            esistenze_clear_all_cache();
            wp_send_json_success('Tüm cache temizlendi.');
            break;
            
        case 'preload':
            esistenze_clear_plugin_cache();
            esistenze_preload_cache();
            wp_send_json_success('Cache yeniden oluşturuldu.');
            break;
            
        default:
            wp_send_json_error('Geçersiz cache tipi.');
    }
}

function esistenze_clear_plugin_cache() {
    // Plugin cache'i temizle
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_qmc_%' OR option_name LIKE '_transient_timeout_qmc_%'");
    
    // Object cache temizle
    wp_cache_delete('esistenze_quick_menu_cards', 'esistenze');
    wp_cache_delete('esistenze_quick_menu_settings', 'esistenze');
    wp_cache_delete('esistenze_quick_menu_analytics', 'esistenze');
    
    do_action('esistenze_quick_menu_cache_cleared');
}

function esistenze_clear_all_cache() {
    // Plugin cache
    esistenze_clear_plugin_cache();
    
    // WordPress object cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Rewrite rules
    flush_rewrite_rules();
    
    // External cache plugins
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }
    
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }
    
    if (function_exists('wp_rocket_clean_domain')) {
        wp_rocket_clean_domain();
    }
    
    if (function_exists('litespeed_purge_all')) {
        litespeed_purge_all();
    }
}

function esistenze_preload_cache() {
    // En popüler sayfaları preload et
    $home_url = home_url();
    $pages_to_preload = array(
        $home_url,
        $home_url . '/hakkimizda',
        $home_url . '/iletisim',
        $home_url . '/hizmetler'
    );
    
    foreach ($pages_to_preload as $url) {
        wp_remote_get($url, array('timeout' => 5));
    }
}

function esistenze_handle_db_action() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $db_action = sanitize_text_field($_POST['db_action']);
    
    switch ($db_action) {
        case 'optimize':
            $result = esistenze_optimize_database();
            if ($result) {
                wp_send_json_success('Veritabanı optimize edildi.');
            } else {
                wp_send_json_error('Veritabanı optimizasyonu başarısız.');
            }
            break;
            
        case 'cleanup':
            $cleaned = esistenze_cleanup_old_data();
            wp_send_json_success("Eski veriler temizlendi. {$cleaned} kayıt silindi.");
            break;
            
        case 'reset_analytics':
            delete_option('esistenze_quick_menu_analytics');
            wp_send_json_success('Analytics verileri sıfırlandı.');
            break;
            
        case 'reset_all':
            delete_option('esistenze_quick_menu_kartlari');
            delete_option('esistenze_quick_menu_settings');
            delete_option('esistenze_quick_menu_analytics');
            esistenze_clear_plugin_cache();
            wp_send_json_success('Tüm veriler sıfırlandı.');
            break;
            
        default:
            wp_send_json_error('Geçersiz veritabanı işlemi.');
    }
}

function esistenze_optimize_database() {
    global $wpdb;
    
    // Options tablosunu optimize et
    $wpdb->query("OPTIMIZE TABLE {$wpdb->options}");
    
    // Transient temizliği
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_value = ''");
    
    // Orphaned metadata temizliği
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})");
    
    return true;
}

function esistenze_cleanup_old_data() {
    global $wpdb;
    
    $cleaned = 0;
    
    // 90 günden eski analytics verilerini temizle
    $analytics = get_option('esistenze_quick_menu_analytics', array());
    
    if (!empty($analytics['click_details'])) {
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-90 days'));
        $original_count = count($analytics['click_details']);
        
        $analytics['click_details'] = array_filter($analytics['click_details'], function($detail) use ($cutoff_date) {
            return isset($detail['timestamp']) && $detail['timestamp'] > $cutoff_date;
        });
        
        $cleaned = $original_count - count($analytics['click_details']);
        
        if ($cleaned > 0) {
            update_option('esistenze_quick_menu_analytics', $analytics);
        }
    }
    
    // Eski transient'ları temizle
    $cleaned += $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < UNIX_TIMESTAMP()");
    
    return $cleaned;
}

function esistenze_handle_check_error_log() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $error_log_path = ini_get('error_log');
    $html = '<div class="error-log-results">';
    
    if (empty($error_log_path) || !file_exists($error_log_path)) {
        $html .= '<p class="no-errors">Error log dosyası bulunamadı veya error logging devre dışı.</p>';
    } else {
        $file_size = filesize($error_log_path);
        $html .= '<p><strong>Error Log:</strong> ' . $error_log_path . '</p>';
        $html .= '<p><strong>Dosya Boyutu:</strong> ' . size_format($file_size) . '</p>';
        
        if ($file_size > 0) {
            // Son 50 satırı oku
            $lines = array();
            $file = new SplFileObject($error_log_path);
            $file->seek(PHP_INT_MAX);
            $total_lines = $file->key();
            
            if ($total_lines > 50) {
                $file->seek($total_lines - 50);
            } else {
                $file->seek(0);
            }
            
            while (!$file->eof()) {
                $line = trim($file->fgets());
                if (!empty($line)) {
                    $lines[] = $line;
                }
            }
            
            if (!empty($lines)) {
                $html .= '<h4>Son Hatalar:</h4>';
                $html .= '<div class="error-log-content">';
                foreach (array_slice($lines, -10) as $line) {
                    $html .= '<div class="log-line">' . esc_html($line) . '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<p class="no-errors">Son zamanlarda hata kaydı yok.</p>';
            }
        } else {
            $html .= '<p class="no-errors">Error log boş.</p>';
        }
    }
    
    $html .= '</div>';
    
    wp_send_json_success(array('html' => $html));
}

function esistenze_handle_test_conflicts() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $html = '<div class="conflict-test-results">';
    
    // JavaScript hatalarını kontrol et
    $html .= '<h4>JavaScript Kontrolleri:</h4>';
    
    // jQuery kontrolü
    if (wp_script_is('jquery', 'enqueued')) {
        $html .= '<p class="test-pass">✅ jQuery yüklü</p>';
    } else {
        $html .= '<p class="test-fail">❌ jQuery yüklü değil</p>';
    }
    
    // Media scripts kontrolü
    if (wp_script_is('media-upload', 'enqueued') || wp_script_is('wp-media-utils', 'enqueued')) {
        $html .= '<p class="test-pass">✅ Media scripts yüklü</p>';
    } else {
        $html .= '<p class="test-warn">⚠️ Media scripts yüklenmemiş olabilir</p>';
    }
    
    // Plugin çakışması kontrolü
    $html .= '<h4>Plugin Kontrolleri:</h4>';
    $active_plugins = get_option('active_plugins', array());
    $potential_conflicts = array(
        'elementor/elementor.php' => 'Elementor',
        'js_composer/js_composer.php' => 'WPBakery Page Builder',
        'revslider/revslider.php' => 'Revolution Slider',
        'LayerSlider/layerslider.php' => 'LayerSlider'
    );
    
    $conflicts_found = false;
    foreach ($potential_conflicts as $plugin_path => $plugin_name) {
        if (in_array($plugin_path, $active_plugins)) {
            $html .= '<p class="test-warn">⚠️ ' . $plugin_name . ' aktif (potansiyel çakışma)</p>';
            $conflicts_found = true;
        }
    }
    
    if (!$conflicts_found) {
        $html .= '<p class="test-pass">✅ Bilinen çakışan plugin bulunamadı</p>';
    }
    
    // Tema kontrolü
    $html .= '<h4>Tema Kontrolleri:</h4>';
    $current_theme = wp_get_theme();
    $html .= '<p><strong>Aktif Tema:</strong> ' . $current_theme->get('Name') . ' v' . $current_theme->get('Version') . '</p>';
    
    if ($current_theme->get('TextDomain') === 'twentytwentythree' || 
        $current_theme->get('TextDomain') === 'twentytwentytwo' || 
        $current_theme->get('TextDomain') === 'twentytwentyone') {
        $html .= '<p class="test-pass">✅ WordPress varsayılan teması</p>';
    } else {
        $html .= '<p class="test-warn">⚠️ Özel tema kullanılıyor</p>';
    }
    
    $html .= '</div>';
    
    wp_send_json_success(array('html' => $html));
}

function esistenze_handle_performance_test() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $start_time = microtime(true);
    
    // Veritabanı sorgu testi
    $db_start = microtime(true);
    $kartlar = get_option('esistenze_quick_menu_kartlari', array());
    $db_time = round((microtime(true) - $db_start) * 1000, 2);
    
    // Memory kullanımı
    $memory_usage = memory_get_usage(true);
    $memory_peak = memory_get_peak_usage(true);
    
    // Disk yazma testi
    $write_start = microtime(true);
    $test_data = array('test' => time());
    set_transient('qmc_performance_test', $test_data, 60);
    $write_time = round((microtime(true) - $write_start) * 1000, 2);
    
    // Disk okuma testi
    $read_start = microtime(true);
    get_transient('qmc_performance_test');
    $read_time = round((microtime(true) - $read_start) * 1000, 2);
    
    // Test verilerini temizle
    delete_transient('qmc_performance_test');
    
    $total_time = round((microtime(true) - $start_time) * 1000, 2);
    
    $html = '<div class="performance-results">';
    $html .= '<h4>Performans Test Sonuçları:</h4>';
    $html .= '<p><strong>Toplam Süre:</strong> ' . $total_time . 'ms</p>';
    $html .= '<p><strong>Veritabanı Okuma:</strong> ' . $db_time . 'ms</p>';
    $html .= '<p><strong>Cache Yazma:</strong> ' . $write_time . 'ms</p>';
    $html .= '<p><strong>Cache Okuma:</strong> ' . $read_time . 'ms</p>';
    $html .= '<p><strong>Memory Kullanımı:</strong> ' . size_format($memory_usage) . '</p>';
    $html .= '<p><strong>Peak Memory:</strong> ' . size_format($memory_peak) . '</p>';
    
    // Performans değerlendirmesi
    if ($total_time < 50) {
        $html .= '<p class="test-pass">✅ Mükemmel performans</p>';
    } elseif ($total_time < 100) {
        $html .= '<p class="test-warn">⚠️ İyi performans</p>';
    } else {
        $html .= '<p class="test-fail">❌ Yavaş performans - optimizasyon gerekli</p>';
    }
    
    $html .= '</div>';
    
    wp_send_json_success(array('html' => $html));
}

function esistenze_handle_maintenance_mode() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $mode_action = sanitize_text_field($_POST['mode_action']);
    
    if ($mode_action === 'enable') {
        $message = sanitize_textarea_field($_POST['message']);
        $duration = intval($_POST['duration']);
        
        $maintenance_data = array(
            'enabled' => true,
            'message' => $message,
            'duration' => $duration,
            'start_time' => current_time('timestamp'),
            'end_time' => current_time('timestamp') + ($duration * 60)
        );
        
        update_option('esistenze_maintenance_mode', $maintenance_data);
        wp_send_json_success('Bakım modu aktif edildi.');
        
    } elseif ($mode_action === 'disable') {
        delete_option('esistenze_maintenance_mode');
        wp_send_json_success('Bakım modu kapatıldı.');
        
    } else {
        wp_send_json_error('Geçersiz bakım modu işlemi.');
    }
}

function esistenze_handle_get_debug_log() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    $log_content = '';
    
    // WP_DEBUG_LOG kontrolü
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        
        if (file_exists($debug_log_path)) {
            $file_size = filesize($debug_log_path);
            
            if ($file_size > 0) {
                // Son 100 satırı al
                $lines = array();
                $file = new SplFileObject($debug_log_path);
                $file->seek(PHP_INT_MAX);
                $total_lines = $file->key();
                
                if ($total_lines > 100) {
                    $file->seek($total_lines - 100);
                } else {
                    $file->seek(0);
                }
                
                while (!$file->eof()) {
                    $line = $file->fgets();
                    if (trim($line)) {
                        $lines[] = $line;
                    }
                }
                
                $log_content = implode('', $lines);
            } else {
                $log_content = 'Debug log dosyası boş.';
            }
        } else {
            $log_content = 'Debug log dosyası bulunamadı.';
        }
    } else {
        $log_content = 'WP_DEBUG_LOG aktif değil.';
    }
    
    wp_send_json_success(array('content' => $log_content));
}

function esistenze_handle_clear_debug_log() {
    if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Yetkisiz erişim.');
    }
    
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        
        if (file_exists($debug_log_path)) {
            if (file_put_contents($debug_log_path, '') !== false) {
                wp_send_json_success('Debug log temizlendi.');
            } else {
                wp_send_json_error('Debug log temizlenemedi.');
            }
        } else {
            wp_send_json_error('Debug log dosyası bulunamadı.');
        }
    } else {
        wp_send_json_error('WP_DEBUG_LOG aktif değil.');
    }
}

function esistenze_handle_download_system_info() {
    if (!wp_verify_nonce($_GET['nonce'], 'esistenze_quick_menu_nonce') || !current_user_can('manage_options')) {
        wp_die('Yetkisiz erişim.');
    }
    
    global $wp_version, $wpdb;
    
    $system_info = array(
        'Site Bilgileri' => array(
            'Site URL' => home_url(),
            'WordPress Version' => $wp_version,
            'WordPress Language' => get_locale(),
            'WordPress Multisite' => is_multisite() ? 'Evet' : 'Hayır',
            'WordPress Memory Limit' => WP_MEMORY_LIMIT,
            'WordPress Debug Mode' => (defined('WP_DEBUG') && WP_DEBUG) ? 'Aktif' : 'Pasif'
        ),
        'Server Bilgileri' => array(
            'PHP Version' => PHP_VERSION,
            'MySQL Version' => $wpdb->get_var("SELECT VERSION()"),
            'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor',
            'PHP Memory Limit' => ini_get('memory_limit'),
            'PHP Time Limit' => ini_get('max_execution_time') . ' saniye',
            'PHP Max Upload Size' => ini_get('upload_max_filesize'),
            'PHP Post Max Size' => ini_get('post_max_size'),
            'PHP Max Input Vars' => ini_get('max_input_vars')
        ),
        'Plugin Bilgileri' => array(
            'Aktif Plugin Sayısı' => count(get_option('active_plugins', array())),
            'Quick Menu Cards Version' => defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0'
        ),
        'Tema Bilgileri' => array(
            'Aktif Tema' => wp_get_theme()->get('Name'),
            'Tema Versiyonu' => wp_get_theme()->get('Version'),
            'Tema Klasörü' => get_template(),
            'Child Tema' => is_child_theme() ? 'Evet' : 'Hayır'
        ),
        'Quick Menu Cards Verileri' => array(
            'Toplam Grup Sayısı' => count(get_option('esistenze_quick_menu_kartlari', array())),
            'Toplam Kart Sayısı' => esistenze_get_total_cards_count(),
            'Analytics Aktif' => (get_option('esistenze_quick_menu_settings', array())['enable_analytics'] ?? false) ? 'Evet' : 'Hayır',
            'Cache Aktif' => (get_option('esistenze_quick_menu_settings', array())['cache_duration'] ?? 0) > 0 ? 'Evet' : 'Hayır'
        )
    );
    
    $filename = 'quick-menu-cards-system-info-' . date('Y-m-d-H-i-s') . '.txt';
    
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    echo "Quick Menu Cards - Sistem Bilgileri\n";
    echo "Oluşturulma Tarihi: " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat('=', 50) . "\n\n";
    
    foreach ($system_info as $section => $data) {
        echo $section . "\n";
        echo str_repeat('-', strlen($section)) . "\n";
        
        foreach ($data as $key => $value) {
            echo sprintf("%-25s: %s\n", $key, $value);
        }
        
        echo "\n";
    }
    
    // Aktif plugin listesi
    echo "Aktif Pluginler\n";
    echo str_repeat('-', 15) . "\n";
    
    $active_plugins = get_option('active_plugins', array());
    foreach ($active_plugins as $plugin) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        echo sprintf("%-40s: %s\n", $plugin_data['Name'], $plugin_data['Version']);
    }
    
    exit;
}

// Helper function
function esistenze_get_total_cards_count() {
    $kartlar = get_option('esistenze_quick_menu_kartlari', array());
    $total = 0;
    
    foreach ($kartlar as $group) {
        if (is_array($group)) {
            $total += count($group);
        }
    }
    
    return $total;
}

// Bakım modu kontrolü için hook
add_action('template_redirect', 'esistenze_check_maintenance_mode');

function esistenze_check_maintenance_mode() {
    // Admin kullanıcıları bakım modundan muaf
    if (current_user_can('manage_options')) {
        return;
    }
    
    $maintenance = get_option('esistenze_maintenance_mode', false);
    
    if (!empty($maintenance['enabled'])) {
        // Süre kontrolü
        if (!empty($maintenance['end_time']) && current_time('timestamp') > $maintenance['end_time']) {
            // Süre dolmuş, bakım modunu kapat
            delete_option('esistenze_maintenance_mode');
            return;
        }
        
        // Bakım modu sayfasını göster
        esistenze_show_maintenance_page($maintenance);
    }
}

function esistenze_show_maintenance_page($maintenance) {
    $message = $maintenance['message'] ?? 'Site şu anda bakımda. Lütfen daha sonra tekrar deneyin.';
    $end_time = $maintenance['end_time'] ?? null;
    
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 3600');
    
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bakım Modu - <?php bloginfo('name'); ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #333;
            }
            
            .maintenance-container {
                background: white;
                border-radius: 20px;
                padding: 60px 40px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.2);
                max-width: 500px;
                width: 90%;
            }
            
            .maintenance-icon {
                font-size: 80px;
                margin-bottom: 30px;
            }
            
            .maintenance-title {
                font-size: 32px;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 20px;
            }
            
            .maintenance-message {
                font-size: 18px;
                color: #7f8c8d;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            
            .maintenance-timer {
                background: #ecf0f1;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 30px;
            }
            
            .timer-label {
                font-size: 14px;
                color: #7f8c8d;
                margin-bottom: 10px;
            }
            
            .timer-value {
                font-size: 24px;
                font-weight: 600;
                color: #e74c3c;
            }
            
            .maintenance-contact {
                font-size: 14px;
                color: #95a5a6;
            }
            
            .maintenance-contact a {
                color: #3498db;
                text-decoration: none;
            }
            
            @media (max-width: 600px) {
                .maintenance-container {
                    padding: 40px 20px;
                }
                
                .maintenance-title {
                    font-size: 24px;
                }
                
                .maintenance-icon {
                    font-size: 60px;
                }
            }
        </style>
    </head>
    <body>
        <div class="maintenance-container">
            <div class="maintenance-icon">🔧</div>
            <h1 class="maintenance-title">Bakım Modu</h1>
            <p class="maintenance-message"><?php echo esc_html($message); ?></p>
            
            <?php if ($end_time): ?>
                <div class="maintenance-timer">
                    <div class="timer-label">Tahmini tamamlanma süresi:</div>
                    <div class="timer-value" id="countdown-timer">Hesaplanıyor...</div>
                </div>
                
                <script>
                    var endTime = <?php echo $end_time; ?> * 1000;
                    
                    function updateCountdown() {
                        var now = new Date().getTime();
                        var distance = endTime - now;
                        
                        if (distance > 0) {
                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            
                            document.getElementById('countdown-timer').innerHTML = 
                                (hours > 0 ? hours + 's ' : '') + 
                                (minutes > 0 ? minutes + 'd ' : '') + 
                                seconds + 's';
                        } else {
                            document.getElementById('countdown-timer').innerHTML = 'Yenileniyor...';
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                    }
                    
                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                </script>
            <?php endif; ?>
            
            <div class="maintenance-contact">
                Acil durumlar için: 
                <a href="mailto:<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// CSS için error-log stilleri
function esistenze_admin_tools_css() {
    ?>
    <style>
    .error-log-results {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .error-log-content {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .log-line {
        padding: 2px 0;
        border-bottom: 1px solid #f0f0f0;
        word-break: break-all;
    }
    
    .log-line:last-child {
        border-bottom: none;
    }
    
    .no-errors {
        color: #00a32a;
        font-weight: 600;
        text-align: center;
        padding: 20px;
    }
    
    .test-pass {
        color: #00a32a;
        font-weight: 600;
    }
    
    .test-warn {
        color: #dba617;
        font-weight: 600;
    }
    
    .test-fail {
        color: #d63638;
        font-weight: 600;
    }
    
    .conflict-test-results,
    .performance-results {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
    }
    
    .conflict-test-results h4,
    .performance-results h4 {
        margin: 0 0 15px;
        color: #1d2327;
        font-size: 16px;
    }
    
    .conflict-test-results p,
    .performance-results p {
        margin: 8px 0;
        padding: 5px 0;
    }
    </style>
    <?php
}

// Admin sayfalarında CSS'i ekle
add_action('admin_head', function() {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'esistenze-quick-menu') !== false) {
        esistenze_admin_tools_css();
    }
});

// Cron job için eski verileri temizleme
add_action('esistenze_quick_menu_cleanup', 'esistenze_scheduled_cleanup');

function esistenze_scheduled_cleanup() {
    esistenze_cleanup_old_data();
    esistenze_optimize_database();
}

// Deactivation hook - cleanup scheduled events
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('esistenze_quick_menu_cleanup');
    delete_option('esistenze_maintenance_mode');
});

?>