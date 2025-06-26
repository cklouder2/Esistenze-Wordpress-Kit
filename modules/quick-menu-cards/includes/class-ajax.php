<?php
/*
 * Quick Menu Cards - AJAX Class
 * Handles all AJAX requests
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsAjax {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Admin AJAX handlers
        add_action('wp_ajax_esistenze_save_card_group', array($this, 'save_card_group'));
        add_action('wp_ajax_esistenze_delete_card_group', array($this, 'delete_card_group'));
        add_action('wp_ajax_esistenze_duplicate_card_group', array($this, 'duplicate_card_group'));
        add_action('wp_ajax_esistenze_export_groups', array($this, 'export_groups'));
        add_action('wp_ajax_esistenze_import_groups', array($this, 'import_groups'));
        add_action('wp_ajax_esistenze_preview_card', array($this, 'preview_card'));
        
        // Public AJAX handlers (both logged in and not logged in users)
        add_action('wp_ajax_esistenze_track_card_click', array($this, 'track_card_click'));
        add_action('wp_ajax_nopriv_esistenze_track_card_click', array($this, 'track_card_click'));
        add_action('wp_ajax_esistenze_track_card_view', array($this, 'track_card_view'));
        add_action('wp_ajax_nopriv_esistenze_track_card_view', array($this, 'track_card_view'));
    }
    
    public function save_card_group() {
        // Nonce ve yetki kontrolü
        if (!$this->verify_nonce() || !current_user_can(esistenze_qmc_capability())) {
            wp_send_json_error('Yetkisiz erişim.');
        }
        
        $group_id = intval($_POST['group_id'] ?? 0);
        $cards_data = $_POST['cards_data'] ?? array();
        
        if (!is_array($cards_data)) {
            wp_send_json_error('Geçersiz kart verisi.');
        }
        
        // Kart verilerini sanitize et
        $sanitized_cards = array();
        foreach ($cards_data as $card_data) {
            $sanitized_card = $this->sanitize_card_data($card_data);
            
            // Validation
            $validation = $this->validate_card_data($sanitized_card);
            if ($validation !== true) {
                wp_send_json_error('Kart verisi hatası: ' . implode(', ', $validation));
            }
            
            $sanitized_cards[] = $sanitized_card;
        }
        
        // Veritabanına kaydet
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $kartlar[$group_id] = $sanitized_cards;
        
        $result = update_option('esistenze_quick_menu_kartlari', $kartlar);
        
        if ($result) {
            // Cache temizle
            $this->clear_cache();
            
            wp_send_json_success(array(
                'message' => 'Grup başarıyla kaydedildi!',
                'group_id' => $group_id,
                'card_count' => count($sanitized_cards),
                'redirect' => admin_url('admin.php?page=esistenze-quick-menu')
            ));
        } else {
            wp_send_json_error('Kayıt sırasında hata oluştu.');
        }
    }
    
    public function delete_card_group() {
        if (!$this->verify_nonce() || !current_user_can(esistenze_qmc_capability())) {
            wp_send_json_error('Yetkisiz erişim.');
        }
        
        $group_id = intval($_POST['group_id'] ?? -1);
        
        if ($group_id < 0) {
            wp_send_json_error('Geçersiz grup ID.');
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        if (!isset($kartlar[$group_id])) {
            wp_send_json_error('Grup bulunamadı.');
        }
        
        // Grubu sil
        unset($kartlar[$group_id]);
        
        // Array'i yeniden indexle
        $kartlar = array_values($kartlar);
        
        $result = update_option('esistenze_quick_menu_kartlari', $kartlar);
        
        if ($result) {
            // Analytics verilerini de temizle
            $this->cleanup_group_analytics($group_id);
            $this->clear_cache();
            
            wp_send_json_success('Grup başarıyla silindi!');
        } else {
            wp_send_json_error('Silme işlemi başarısız.');
        }
    }
    
    public function duplicate_card_group() {
        if (!$this->verify_nonce() || !current_user_can(esistenze_qmc_capability())) {
            wp_send_json_error('Yetkisiz erişim.');
        }
        
        $group_id = intval($_POST['group_id'] ?? -1);
        
        if ($group_id < 0) {
            wp_send_json_error('Geçersiz grup ID.');
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        if (!isset($kartlar[$group_id])) {
            wp_send_json_error('Grup bulunamadı.');
        }
        
        // Grubu kopyala
        $new_group = $kartlar[$group_id];
        
        // Kartların başlıklarına "Kopya" ekle
        foreach ($new_group as &$card) {
            if (!empty($card['title'])) {
                $card['title'] .= ' - Kopya';
            }
            $card['created_at'] = current_time('mysql');
            $card['updated_at'] = current_time('mysql');
        }
        
        $kartlar[] = $new_group;
        $new_group_id = count($kartlar) - 1;
        
        $result = update_option('esistenze_quick_menu_kartlari', $kartlar);
        
        if ($result) {
            $this->clear_cache();
            
            wp_send_json_success(array(
                'message' => 'Grup başarıyla kopyalandı!',
                'new_group_id' => $new_group_id
            ));
        } else {
            wp_send_json_error('Kopyalama işlemi başarısız.');
        }
    }
    
    public function export_groups() {
        if (!$this->verify_nonce() || !current_user_can(esistenze_qmc_capability())) {
            wp_send_json_error('Yetkisiz erişim.');
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $settings = get_option('esistenze_quick_menu_settings', array());
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        
        $export_data = array(
            'version' => $this->get_version(),
            'export_date' => current_time('mysql'),
            'site_url' => home_url(),
            'groups' => $kartlar,
            'settings' => $settings,
            'analytics' => $analytics
        );
        
        wp_send_json_success(array(
            'data' => $export_data,
            'filename' => 'quick-menu-cards-export-' . date('Y-m-d-H-i-s') . '.json'
        ));
    }
    
    public function import_groups() {
        if (!$this->verify_nonce() || !current_user_can(esistenze_qmc_capability())) {
            wp_send_json_error('Yetkisiz erişim.');
        }
        
        $import_data = $_POST['import_data'] ?? '';
        
        if (empty($import_data)) {
            wp_send_json_error('Import verisi boş.');
        }
        
        // JSON decode
        $data = json_decode(stripslashes($import_data), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Geçersiz JSON formatı.');
        }
        
        // Veri kontrolü
        if (!is_array($data) || empty($data['groups'])) {
            wp_send_json_error('Geçersiz veri formatı.');
        }
        
        // Güvenlik kontrolü
        foreach ($data['groups'] as $group) {
            if (!is_array($group)) {
                wp_send_json_error('Geçersiz grup formatı.');
            }
            
            foreach ($group as $card) {
                if (!is_array($card)) {
                    wp_send_json_error('Geçersiz kart formatı.');
                }
                
                // Sanitize card data
                $card = $this->sanitize_card_data($card);
            }
        }
        
        // Verileri kaydet
        $result1 = update_option('esistenze_quick_menu_kartlari', $data['groups']);
        
        $result2 = true;
        if (!empty($data['settings'])) {
            $result2 = update_option('esistenze_quick_menu_settings', $data['settings']);
        }
        
        if ($result1 && $result2) {
            $this->clear_cache();
            
            wp_send_json_success(array(
                'message' => 'Veriler başarıyla içe aktarıldı!',
                'group_count' => count($data['groups'])
            ));
        } else {
            wp_send_json_error('İçe aktarma sırasında hata oluştu.');
        }
    }
    
    public function preview_card() {
        if (!$this->verify_nonce()) {
            wp_send_json_error('Yetkisiz erişim.');
        }
        
        $card_data = $_POST['card_data'] ?? array();
        $preview_type = sanitize_text_field($_POST['preview_type'] ?? 'grid');
        
        $sanitized_card = $this->sanitize_card_data($card_data);
        
        // Önizleme HTML'i oluştur
        if ($preview_type === 'banner') {
            $html = $this->generate_banner_preview($sanitized_card);
        } else {
            $html = $this->generate_card_preview($sanitized_card);
        }
        
        wp_send_json_success(array(
            'html' => $html
        ));
    }
    
    public function track_card_click() {
        $group_id = intval($_POST['group_id'] ?? 0);
        $card_index = intval($_POST['card_index'] ?? 0);
        
        if ($group_id < 0 || $card_index < 0) {
            wp_send_json_error('Geçersiz parametreler.');
        }
        
        // Analytics kontrolü
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        if (empty($settings['enable_analytics'])) {
            wp_send_json_success('Analytics devre dışı.');
        }
        
        // Analytics verilerini güncelle
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        $analytics['total_clicks'] = ($analytics['total_clicks'] ?? 0) + 1;
        $analytics['group_clicks'][$group_id] = ($analytics['group_clicks'][$group_id] ?? 0) + 1;
        $analytics['card_clicks'][$group_id][$card_index] = ($analytics['card_clicks'][$group_id][$card_index] ?? 0) + 1;
        $analytics['last_click'] = current_time('mysql');
        
        // IP ve User Agent bilgilerini kaydet (GDPR uyumlu)
        if (!empty($settings['track_user_data'])) {
            $analytics['click_details'][] = array(
                'group_id' => $group_id,
                'card_index' => $card_index,
                'timestamp' => current_time('mysql'),
                'ip' => $this->get_client_ip(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
            );
            
            // Son 1000 detayı tut
            if (count($analytics['click_details']) > 1000) {
                $analytics['click_details'] = array_slice($analytics['click_details'], -1000);
            }
        }
        
        update_option('esistenze_quick_menu_analytics', $analytics);
        
        wp_send_json_success('Tıklama kaydedildi.');
    }
    
    public function track_card_view() {
        $group_id = intval($_POST['group_id'] ?? 0);
        
        if ($group_id < 0) {
            wp_send_json_error('Geçersiz grup ID.');
        }
        
        // Analytics kontrolü
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        if (empty($settings['enable_analytics'])) {
            wp_send_json_success('Analytics devre dışı.');
        }
        
        // Rate limiting - aynı IP'den 1 dakikada sadece 1 view
        $rate_limit_key = 'view_' . $group_id . '_' . $this->get_client_ip();
        if (get_transient($rate_limit_key)) {
            wp_send_json_success('Rate limited.');
        }
        set_transient($rate_limit_key, true, 60); // 1 dakika
        
        // Analytics verilerini güncelle
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        $analytics['total_views'] = ($analytics['total_views'] ?? 0) + 1;
        $analytics['group_views'][$group_id] = ($analytics['group_views'][$group_id] ?? 0) + 1;
        $analytics['last_view'] = current_time('mysql');
        
        update_option('esistenze_quick_menu_analytics', $analytics);
        
        wp_send_json_success('Görüntülenme kaydedildi.');
    }
    
    // Helper methods
    private function verify_nonce() {
        return wp_verify_nonce($_POST['nonce'] ?? '', 'esistenze_quick_menu_nonce');
    }
    
    private function sanitize_card_data($card_data) {
        return array(
            'title' => sanitize_text_field($card_data['title'] ?? ''),
            'desc' => sanitize_textarea_field($card_data['desc'] ?? ''),
            'img' => esc_url_raw($card_data['img'] ?? ''),
            'url' => esc_url_raw($card_data['url'] ?? ''),
            'order' => intval($card_data['order'] ?? 0),
            'enabled' => !empty($card_data['enabled']),
            'created_at' => $card_data['created_at'] ?? current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
    }
    
    private function validate_card_data($card_data) {
        $errors = array();
        
        if (empty($card_data['title'])) {
            $errors[] = 'Kart başlığı zorunludur.';
        }
        
        if (strlen($card_data['title']) > 100) {
            $errors[] = 'Kart başlığı 100 karakterden uzun olamaz.';
        }
        
        if (strlen($card_data['desc']) > 500) {
            $errors[] = 'Kart açıklaması 500 karakterden uzun olamaz.';
        }
        
        if (!empty($card_data['url']) && !filter_var($card_data['url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Geçerli bir URL giriniz.';
        }
        
        if (!empty($card_data['img']) && !filter_var($card_data['img'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Geçerli bir görsel URL\'i giriniz.';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    private function cleanup_group_analytics($group_id) {
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        
        // Grup ile ilgili analytics verilerini temizle
        if (isset($analytics['group_views'][$group_id])) {
            unset($analytics['group_views'][$group_id]);
        }
        
        if (isset($analytics['group_clicks'][$group_id])) {
            unset($analytics['group_clicks'][$group_id]);
        }
        
        if (isset($analytics['card_clicks'][$group_id])) {
            unset($analytics['card_clicks'][$group_id]);
        }
        
        update_option('esistenze_quick_menu_analytics', $analytics);
    }
    
    private function clear_cache() {
        // WordPress object cache
        wp_cache_delete('esistenze_quick_menu_cards', 'esistenze');
        wp_cache_delete('esistenze_quick_menu_settings', 'esistenze');
        
        // Transients
        delete_transient('esistenze_quick_menu_cards_cache');
        delete_transient('esistenze_quick_menu_schema_cache');
        
        // Page cache plugins
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        
        if (function_exists('wp_rocket_clean_domain')) {
            wp_rocket_clean_domain();
        }
    }
    
    private function generate_card_preview($card) {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        $html = '<div class="esistenze-quick-menu-wrapper preview-wrapper">';
        $html .= '<div class="esistenze-quick-menu-kart">';
        $html .= '<div class="esistenze-quick-menu-icerik">';
        
        if (!empty($card['img'])) {
            $html .= '<img src="' . esc_url($card['img']) . '" alt="' . esc_attr($card['title']) . '">';
        }
        
        $html .= '<div class="esistenze-quick-menu-yazi">';
        $html .= '<h4>' . esc_html($card['title'] ?: 'Başlık') . '</h4>';
        $html .= '<p>' . esc_html($card['desc'] ?: 'Açıklama') . '</p>';
        $html .= '</div></div>';
        $html .= '<div class="esistenze-quick-menu-buton">' . esc_html($settings['default_button_text']) . '</div>';
        $html .= '</div></div>';
        
        return $html;
    }
    
    private function generate_banner_preview($card) {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        if (empty($card['img'])) {
            return '<p>Banner görünüm için görsel gereklidir.</p>';
        }
        
        $html = '<div class="esistenze-quick-menu-banner-wrapper preview-wrapper">';
        $html .= '<div class="esistenze-quick-menu-banner">';
        $html .= '<div class="banner-img"><img src="' . esc_url($card['img']) . '" alt="' . esc_attr($card['title']) . '"></div>';
        $html .= '<div class="banner-text">';
        $html .= '<h4>' . esc_html($card['title'] ?: 'Başlık') . '</h4>';
        $html .= '<p>' . esc_html($card['desc'] ?: 'Açıklama') . '</p>';
        $html .= '</div>';
        $html .= '<div class="banner-button"><span>' . esc_html($settings['banner_button_text']) . '</span></div>';
        $html .= '</div></div>';
        
        return $html;
    }
    
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    private function get_version() {
        return defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0';
    }
}

?>