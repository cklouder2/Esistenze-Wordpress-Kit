<?php
/**
 * Quick Menu Cards AJAX Sınıfı
 * Yeniden yazılmış, basit ve çalışır versiyon
 * 
 * @package Esistenze WordPress Kit
 * @subpackage Quick Menu Cards
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsAjax {
    
    public function __construct() {
        $this->init();
    }
    
    /**
     * AJAX hook'larını başlat
     */
    private function init() {
        // Kart işlemleri
        add_action('wp_ajax_qmc_add_card', array($this, 'add_card'));
        add_action('wp_ajax_qmc_update_card', array($this, 'update_card'));
        add_action('wp_ajax_qmc_delete_card', array($this, 'delete_card'));
        add_action('wp_ajax_qmc_reorder_cards', array($this, 'reorder_cards'));
        
        // Grup işlemleri
        add_action('wp_ajax_qmc_delete_group', array($this, 'delete_group'));
        
        // Veri işlemleri
        add_action('wp_ajax_qmc_get_cards', array($this, 'get_cards'));
        add_action('wp_ajax_qmc_export_data', array($this, 'export_data'));
        add_action('wp_ajax_qmc_import_data', array($this, 'import_data'));
        
        // Debug log
        if (function_exists('qmc_log_error')) {
            qmc_log_error('QMC AJAX sınıfı başlatıldı');
        }
    }
    
    /**
     * Yeni kart ekle
     */
    public function add_card() {
        // Güvenlik kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'qmc_nonce') || !current_user_can('edit_posts')) {
            wp_send_json_error('Yetkiniz yok!');
        }
        
        $group_id = sanitize_text_field($_POST['group_id']);
        $card_data = $this->sanitize_card_data($_POST['card_data']);
        
        if (empty($group_id) || empty($card_data['title'])) {
            wp_send_json_error('Gerekli alanlar eksik!');
        }
        
        // Mevcut kartları al
        $cards = EsistenzeQuickMenuCards::get_cards();
        
        if (!isset($cards[$group_id])) {
            wp_send_json_error('Grup bulunamadı!');
        }
        
        // Yeni kartı ekle
        if (!isset($cards[$group_id]['cards'])) {
            $cards[$group_id]['cards'] = array();
        }
        
        $cards[$group_id]['cards'][] = $card_data;
        $card_index = count($cards[$group_id]['cards']) - 1;
        
        // Kaydet
        if (EsistenzeQuickMenuCards::save_cards($cards)) {
            wp_send_json_success(array(
                'message' => 'Kart başarıyla eklendi!',
                'card' => $card_data,
                'index' => $card_index
            ));
        } else {
            wp_send_json_error('Kart kaydedilemedi!');
        }
    }
    
    /**
     * Kartı güncelle
     */
    public function update_card() {
        // Güvenlik kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'qmc_nonce') || !current_user_can('edit_posts')) {
            wp_send_json_error('Yetkiniz yok!');
        }
        
        $group_id = sanitize_text_field($_POST['group_id']);
        $card_index = intval($_POST['card_index']);
        $card_data = $this->sanitize_card_data($_POST['card_data']);
        
        if (empty($group_id) || $card_index < 0 || empty($card_data['title'])) {
            wp_send_json_error('Gerekli alanlar eksik!');
        }
        
        // Mevcut kartları al
        $cards = EsistenzeQuickMenuCards::get_cards();
        
        if (!isset($cards[$group_id]['cards'][$card_index])) {
            wp_send_json_error('Kart bulunamadı!');
        }
        
        // Kartı güncelle
        $cards[$group_id]['cards'][$card_index] = $card_data;
        
        // Kaydet
        if (EsistenzeQuickMenuCards::save_cards($cards)) {
            wp_send_json_success(array(
                'message' => 'Kart başarıyla güncellendi!',
                'card' => $card_data
            ));
        } else {
            wp_send_json_error('Kart güncellenemedi!');
        }
    }
    
    /**
     * Kart sil
     */
    public function delete_card() {
        // Güvenlik kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'qmc_nonce') || !current_user_can('edit_posts')) {
            wp_send_json_error('Yetkiniz yok!');
        }
        
        $group_id = sanitize_text_field($_POST['group_id']);
        $card_index = intval($_POST['card_index']);
        
        if (empty($group_id) || $card_index < 0) {
            wp_send_json_error('Gerekli alanlar eksik!');
        }
        
        // Mevcut kartları al
        $cards = EsistenzeQuickMenuCards::get_cards();
        
        if (!isset($cards[$group_id]['cards'][$card_index])) {
            wp_send_json_error('Kart bulunamadı!');
        }
        
        // Kartı sil
        unset($cards[$group_id]['cards'][$card_index]);
        
        // Array'i yeniden indeksle
        $cards[$group_id]['cards'] = array_values($cards[$group_id]['cards']);
        
        // Kaydet
        if (EsistenzeQuickMenuCards::save_cards($cards)) {
            wp_send_json_success(array(
                'message' => 'Kart başarıyla silindi!',
                'remaining_count' => count($cards[$group_id]['cards'])
            ));
        } else {
            wp_send_json_error('Kart silinemedi!');
        }
    }
    
    /**
     * Kart sırasını değiştir
     */
    public function reorder_cards() {
        // Güvenlik kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'qmc_nonce') || !current_user_can('edit_posts')) {
            wp_send_json_error('Yetkiniz yok!');
        }
        
        $group_id = sanitize_text_field($_POST['group_id']);
        $card_order = array_map('intval', $_POST['card_order']);
        
        if (empty($group_id) || empty($card_order)) {
            wp_send_json_error('Gerekli alanlar eksik!');
        }
        
        // Mevcut kartları al
        $cards = EsistenzeQuickMenuCards::get_cards();
        
        if (!isset($cards[$group_id]['cards'])) {
            wp_send_json_error('Grup bulunamadı!');
        }
        
        $original_cards = $cards[$group_id]['cards'];
        $reordered_cards = array();
        
        // Yeni sıraya göre kartları düzenle
        foreach ($card_order as $old_index) {
            if (isset($original_cards[$old_index])) {
                $reordered_cards[] = $original_cards[$old_index];
            }
        }
        
        $cards[$group_id]['cards'] = $reordered_cards;
        
        // Kaydet
        if (EsistenzeQuickMenuCards::save_cards($cards)) {
            wp_send_json_success(array(
                'message' => 'Kart sırası güncellendi!',
                'new_order' => $card_order
            ));
        } else {
            wp_send_json_error('Sıralama kaydedilemedi!');
        }
    }
    
    /**
     * Grup sil
     */
    public function delete_group() {
        // Güvenlik kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'qmc_nonce') || !current_user_can('edit_posts')) {
            wp_send_json_error('Yetkiniz yok!');
        }
        
        $group_id = sanitize_text_field($_POST['group_id']);
        
        if (empty($group_id)) {
            wp_send_json_error('Grup ID gerekli!');
        }
        
        // Mevcut kartları al
        $cards = EsistenzeQuickMenuCards::get_cards();
        
        if (!isset($cards[$group_id])) {
            wp_send_json_error('Grup bulunamadı!');
        }
        
        // Grubu sil
        unset($cards[$group_id]);
        
        // Kaydet
        if (EsistenzeQuickMenuCards::save_cards($cards)) {
            wp_send_json_success(array(
                'message' => 'Grup başarıyla silindi!',
                'remaining_groups' => count($cards)
            ));
        } else {
            wp_send_json_error('Grup silinemedi!');
        }
    }
    
    /**
     * Kartları al
     */
    public function get_cards() {
        // Güvenlik kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'qmc_nonce') || !current_user_can('edit_posts')) {
            wp_send_json_error('Yetkiniz yok!');
        }
        
        $group_id = sanitize_text_field($_POST['group_id']);
        
        if (empty($group_id)) {
            // Tüm kartları döndür
            $cards = EsistenzeQuickMenuCards::get_cards();
        } else {
            // Belirli grubu döndür
            $cards = EsistenzeQuickMenuCards::get_cards($group_id);
        }
        
        wp_send_json_success(array(
            'cards' => $cards,
            'count' => is_array($cards) ? count($cards) : 0
        ));
    }
    
    /**
     * Veri dışa aktar
     */
    public function export_data() {
        // Güvenlik kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'qmc_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Yetkiniz yok!');
        }
        
        $cards = EsistenzeQuickMenuCards::get_cards();
        $settings = EsistenzeQuickMenuCards::get_settings();
        
        $export_data = array(
            'version' => '2.0.0',
            'export_date' => current_time('mysql'),
            'cards' => $cards,
            'settings' => $settings
        );
        
        wp_send_json_success(array(
            'data' => $export_data,
            'filename' => 'qmc-export-' . date('Y-m-d-H-i-s') . '.json'
        ));
    }
    
    /**
     * Veri içe aktar
     */
    public function import_data() {
        // Güvenlik kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'qmc_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Yetkiniz yok!');
        }
        
        if (!isset($_FILES['import_file'])) {
            wp_send_json_error('Dosya seçilmedi!');
        }
        
        $file = $_FILES['import_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('Dosya yüklenirken hata oluştu!');
        }
        
        $file_content = file_get_contents($file['tmp_name']);
        $import_data = json_decode($file_content, true);
        
        if (!$import_data || !isset($import_data['cards'])) {
            wp_send_json_error('Geçersiz dosya formatı!');
        }
        
        // Verileri kaydet
        $cards_saved = EsistenzeQuickMenuCards::save_cards($import_data['cards']);
        $settings_saved = true;
        
        if (isset($import_data['settings'])) {
            $settings_saved = EsistenzeQuickMenuCards::save_settings($import_data['settings']);
        }
        
        if ($cards_saved && $settings_saved) {
            wp_send_json_success(array(
                'message' => 'Veriler başarıyla içe aktarıldı!',
                'imported_groups' => count($import_data['cards']),
                'version' => $import_data['version'] ?? 'unknown'
            ));
        } else {
            wp_send_json_error('Veriler kaydedilemedi!');
        }
    }
    
    /**
     * Kart verilerini sanitize et
     */
    private function sanitize_card_data($data) {
        if (!is_array($data)) {
            return array();
        }
        
        return array(
            'title' => sanitize_text_field($data['title'] ?? ''),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'url' => esc_url_raw($data['url'] ?? ''),
            'image' => esc_url_raw($data['image'] ?? ''),
            'image_id' => intval($data['image_id'] ?? 0),
            'button_text' => sanitize_text_field($data['button_text'] ?? ''),
            'type' => 'card',
            'created' => current_time('mysql'),
            'updated' => current_time('mysql')
        );
    }
    
    /**
     * Hata logla
     */
    private function log_error($message, $data = array()) {
        if (function_exists('qmc_log_error')) {
            qmc_log_error('QMC AJAX Error: ' . $message, $data);
        }
    }
}
?>