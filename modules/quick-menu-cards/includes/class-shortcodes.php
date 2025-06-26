<?php
/**
 * Quick Menu Cards Shortcodes Sınıfı
 * Yeniden yazılmış, basit ve çalışır versiyon
 * 
 * @package Esistenze WordPress Kit
 * @subpackage Quick Menu Cards
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsShortcodes {
    
    private $frontend;
    
    public function __construct() {
        $this->init();
    }
    
    /**
     * Shortcode'ları başlat
     */
    private function init() {
        // Ana shortcode'ları kaydet
        add_shortcode('quick_menu_cards', array($this, 'render_cards_shortcode'));
        add_shortcode('qmc_cards', array($this, 'render_cards_shortcode')); // Kısa versiyon
        add_shortcode('qmc_banner', array($this, 'render_banner_shortcode'));
        add_shortcode('qmc_list', array($this, 'render_list_shortcode'));
        add_shortcode('qmc_masonry', array($this, 'render_masonry_shortcode'));
        add_shortcode('qmc_slider', array($this, 'render_slider_shortcode'));
        
        // Eski uyumluluk için
        add_shortcode('esistenze_quick_menu', array($this, 'render_cards_shortcode'));
        
        // Frontend sınıfını başlat
        $this->frontend = new EsistenzeQuickMenuCardsFrontend(plugin_dir_url(__DIR__));
        
        // Debug log
        if (function_exists('qmc_log_error')) {
            qmc_log_error('QMC Shortcodes sınıfı başlatıldı');
        }
    }
    
    /**
     * Ana kart shortcode'u
     * [quick_menu_cards group="ana-menu" columns="3" show_images="true" show_descriptions="true"]
     */
    public function render_cards_shortcode($atts, $content = null) {
        // Varsayılan özellikler
        $defaults = array(
            'group' => '', // Grup ID (zorunlu)
            'id' => '', // Eski uyumluluk için
            'columns' => 0, // 0 = ayarlardan al
            'show_images' => '', // true/false/'' = ayarlardan al
            'show_descriptions' => '', // true/false/'' = ayarlardan al
            'limit' => 0, // Gösterilecek kart sayısı (0 = tümü)
            'class' => '', // Özel CSS sınıfı
            'style' => 'grid' // grid, list, masonry, slider
        );
        
        $atts = shortcode_atts($defaults, $atts, 'quick_menu_cards');
        
        // Eski uyumluluk - id parametresi varsa group olarak kullan
        if (empty($atts['group']) && !empty($atts['id'])) {
            $atts['group'] = $atts['id'];
        }
        
        // Grup ID kontrolü
        if (empty($atts['group'])) {
            return '<div class="qmc-error">Hata: Grup ID belirtilmedi. Kullanım: [quick_menu_cards group="grup-id"]</div>';
        }
        
        // Boolean değerleri dönüştür
        $show_images = $this->parse_boolean($atts['show_images']);
        $show_descriptions = $this->parse_boolean($atts['show_descriptions']);
        
        // Render parametrelerini hazırla
        $render_atts = array(
            'columns' => intval($atts['columns']),
            'show_images' => $show_images,
            'show_descriptions' => $show_descriptions,
            'limit' => intval($atts['limit']),
            'custom_class' => sanitize_html_class($atts['class'])
        );
        
        // Stil türüne göre render et
        switch ($atts['style']) {
            case 'list':
                return $this->frontend->render_list_view($atts['group'], $render_atts);
            case 'masonry':
                return $this->frontend->render_masonry_view($atts['group'], $render_atts);
            case 'slider':
                return $this->frontend->render_slider_view($atts['group'], $render_atts);
            case 'grid':
            default:
                return $this->frontend->render_cards($atts['group'], $render_atts);
        }
    }
    
    /**
     * Banner shortcode'u
     * [qmc_banner group="banner-grup" card="0"]
     */
    public function render_banner_shortcode($atts, $content = null) {
        $defaults = array(
            'group' => '',
            'card' => 0, // Hangi kart (index)
            'class' => 'qmc-banner',
            'full_width' => 'true'
        );
        
        $atts = shortcode_atts($defaults, $atts, 'qmc_banner');
        
        if (empty($atts['group'])) {
            return '<div class="qmc-error">Hata: Banner grup ID belirtilmedi.</div>';
        }
        
        // Grup verilerini al
        $group_data = EsistenzeQuickMenuCards::get_cards($atts['group']);
        
        if (!$group_data || empty($group_data['cards'])) {
            return '<div class="qmc-error">Banner grup bulunamadı veya boş.</div>';
        }
        
        $card_index = intval($atts['card']);
        
        if (!isset($group_data['cards'][$card_index])) {
            return '<div class="qmc-error">Belirtilen banner kart bulunamadı.</div>';
        }
        
        $card_data = $group_data['cards'][$card_index];
        
        $render_atts = array(
            'full_width' => $this->parse_boolean($atts['full_width']),
            'custom_class' => sanitize_html_class($atts['class'])
        );
        
        return $this->frontend->render_banner_card($card_data, $render_atts);
    }
    
    /**
     * Liste shortcode'u
     * [qmc_list group="liste-grup" show_images="true" limit="5"]
     */
    public function render_list_shortcode($atts, $content = null) {
        $defaults = array(
            'group' => '',
            'show_images' => 'true',
            'show_descriptions' => 'true',
            'limit' => 0,
            'class' => 'qmc-list'
        );
        
        $atts = shortcode_atts($defaults, $atts, 'qmc_list');
        
        if (empty($atts['group'])) {
            return '<div class="qmc-error">Hata: Liste grup ID belirtilmedi.</div>';
        }
        
        $render_atts = array(
            'show_images' => $this->parse_boolean($atts['show_images']),
            'show_descriptions' => $this->parse_boolean($atts['show_descriptions']),
            'limit' => intval($atts['limit']),
            'custom_class' => sanitize_html_class($atts['class'])
        );
        
        return $this->frontend->render_list_view($atts['group'], $render_atts);
    }
    
    /**
     * Masonry shortcode'u
     * [qmc_masonry group="masonry-grup" columns="4"]
     */
    public function render_masonry_shortcode($atts, $content = null) {
        $defaults = array(
            'group' => '',
            'columns' => 3,
            'show_images' => 'true',
            'show_descriptions' => 'true',
            'class' => 'qmc-masonry'
        );
        
        $atts = shortcode_atts($defaults, $atts, 'qmc_masonry');
        
        if (empty($atts['group'])) {
            return '<div class="qmc-error">Hata: Masonry grup ID belirtilmedi.</div>';
        }
        
        $render_atts = array(
            'columns' => intval($atts['columns']),
            'show_images' => $this->parse_boolean($atts['show_images']),
            'show_descriptions' => $this->parse_boolean($atts['show_descriptions']),
            'custom_class' => sanitize_html_class($atts['class'])
        );
        
        return $this->frontend->render_masonry_view($atts['group'], $render_atts);
    }
    
    /**
     * Slider shortcode'u
     * [qmc_slider group="slider-grup" slides_to_show="3" auto_play="true"]
     */
    public function render_slider_shortcode($atts, $content = null) {
        $defaults = array(
            'group' => '',
            'slides_to_show' => 3,
            'auto_play' => 'false',
            'show_arrows' => 'true',
            'show_dots' => 'true',
            'class' => 'qmc-slider'
        );
        
        $atts = shortcode_atts($defaults, $atts, 'qmc_slider');
        
        if (empty($atts['group'])) {
            return '<div class="qmc-error">Hata: Slider grup ID belirtilmedi.</div>';
        }
        
        $render_atts = array(
            'slides_to_show' => intval($atts['slides_to_show']),
            'auto_play' => $this->parse_boolean($atts['auto_play']),
            'show_arrows' => $this->parse_boolean($atts['show_arrows']),
            'show_dots' => $this->parse_boolean($atts['show_dots']),
            'custom_class' => sanitize_html_class($atts['class'])
        );
        
        return $this->frontend->render_slider_view($atts['group'], $render_atts);
    }
    
    /**
     * Tek kart shortcode'u
     * [qmc_single_card group="grup" card="0"]
     */
    public function render_single_card_shortcode($atts, $content = null) {
        $defaults = array(
            'group' => '',
            'card' => 0,
            'show_image' => 'true',
            'show_description' => 'true',
            'class' => 'qmc-single'
        );
        
        $atts = shortcode_atts($defaults, $atts, 'qmc_single_card');
        
        if (empty($atts['group'])) {
            return '<div class="qmc-error">Hata: Grup ID belirtilmedi.</div>';
        }
        
        // Grup verilerini al
        $group_data = EsistenzeQuickMenuCards::get_cards($atts['group']);
        
        if (!$group_data || empty($group_data['cards'])) {
            return '<div class="qmc-error">Grup bulunamadı veya boş.</div>';
        }
        
        $card_index = intval($atts['card']);
        
        if (!isset($group_data['cards'][$card_index])) {
            return '<div class="qmc-error">Belirtilen kart bulunamadı.</div>';
        }
        
        $card = $group_data['cards'][$card_index];
        $settings = EsistenzeQuickMenuCards::get_settings();
        
        $show_image = $this->parse_boolean($atts['show_image']);
        $show_description = $this->parse_boolean($atts['show_description']);
        
        $output = '<div class="qmc-single-card ' . esc_attr($atts['class']) . '">';
        
        // Resim
        if ($show_image && !empty($card['image'])) {
            $output .= '<div class="qmc-card-image">';
            $output .= '<img src="' . esc_url($card['image']) . '" alt="' . esc_attr($card['title']) . '" loading="lazy">';
            $output .= '</div>';
        }
        
        // İçerik
        $output .= '<div class="qmc-card-content">';
        
        if (!empty($card['title'])) {
            $output .= '<h3 class="qmc-card-title">' . esc_html($card['title']) . '</h3>';
        }
        
        if ($show_description && !empty($card['description'])) {
            $output .= '<p class="qmc-card-description">' . esc_html($card['description']) . '</p>';
        }
        
        if (!empty($card['url'])) {
            $button_text = !empty($card['button_text']) ? $card['button_text'] : $settings['default_button_text'];
            $output .= '<a href="' . esc_url($card['url']) . '" class="qmc-card-button">';
            $output .= esc_html($button_text);
            $output .= '</a>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Grup bilgisi shortcode'u
     * [qmc_group_info group="grup-id"]
     */
    public function render_group_info_shortcode($atts, $content = null) {
        $defaults = array(
            'group' => '',
            'show' => 'all', // all, name, count, description
            'class' => 'qmc-group-info'
        );
        
        $atts = shortcode_atts($defaults, $atts, 'qmc_group_info');
        
        if (empty($atts['group'])) {
            return '<div class="qmc-error">Hata: Grup ID belirtilmedi.</div>';
        }
        
        $group_data = EsistenzeQuickMenuCards::get_cards($atts['group']);
        
        if (!$group_data) {
            return '<div class="qmc-error">Grup bulunamadı.</div>';
        }
        
        $output = '<div class="' . esc_attr($atts['class']) . '">';
        
        $show_items = explode(',', $atts['show']);
        $show_items = array_map('trim', $show_items);
        
        if (in_array('all', $show_items) || in_array('name', $show_items)) {
            $output .= '<h4 class="qmc-group-name">' . esc_html($group_data['name']) . '</h4>';
        }
        
        if (in_array('all', $show_items) || in_array('count', $show_items)) {
            $card_count = count($group_data['cards'] ?? array());
            $output .= '<p class="qmc-group-count">' . $card_count . ' kart</p>';
        }
        
        if ((in_array('all', $show_items) || in_array('description', $show_items)) && !empty($group_data['description'])) {
            $output .= '<p class="qmc-group-description">' . esc_html($group_data['description']) . '</p>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Boolean değeri parse et
     */
    private function parse_boolean($value) {
        if ($value === '') {
            return null; // Ayarlardan al
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Shortcode'ları listele (debug için)
     */
    public function list_available_shortcodes() {
        return array(
            'quick_menu_cards' => 'Ana kart shortcode\'u - [quick_menu_cards group="grup-id"]',
            'qmc_cards' => 'Kısa versiyon - [qmc_cards group="grup-id"]',
            'qmc_banner' => 'Banner kart - [qmc_banner group="grup-id" card="0"]',
            'qmc_list' => 'Liste görünümü - [qmc_list group="grup-id"]',
            'qmc_masonry' => 'Masonry görünümü - [qmc_masonry group="grup-id"]',
            'qmc_slider' => 'Slider görünümü - [qmc_slider group="grup-id"]',
            'qmc_single_card' => 'Tek kart - [qmc_single_card group="grup-id" card="0"]',
            'qmc_group_info' => 'Grup bilgisi - [qmc_group_info group="grup-id"]',
            'esistenze_quick_menu' => 'Eski uyumluluk - [esistenze_quick_menu id="grup-id"]'
        );
    }
    
    /**
     * Shortcode yardım metni
     */
    public function get_shortcode_help() {
        $help = '<div class="qmc-shortcode-help">';
        $help .= '<h3>Quick Menu Cards Shortcode Kullanımı</h3>';
        
        $help .= '<h4>Ana Shortcode:</h4>';
        $help .= '<code>[quick_menu_cards group="grup-id"]</code><br>';
        $help .= '<strong>Parametreler:</strong><br>';
        $help .= '• <code>group</code> - Grup ID (zorunlu)<br>';
        $help .= '• <code>columns</code> - Sütun sayısı (varsayılan: ayarlardan)<br>';
        $help .= '• <code>show_images</code> - Resimleri göster (true/false)<br>';
        $help .= '• <code>show_descriptions</code> - Açıklamaları göster (true/false)<br>';
        $help .= '• <code>limit</code> - Kart sayısı limiti (0 = tümü)<br>';
        $help .= '• <code>class</code> - Özel CSS sınıfı<br>';
        $help .= '• <code>style</code> - Görünüm türü (grid/list/masonry/slider)<br><br>';
        
        $help .= '<h4>Diğer Shortcode\'lar:</h4>';
        $help .= '<code>[qmc_banner group="grup-id" card="0"]</code> - Banner kart<br>';
        $help .= '<code>[qmc_list group="grup-id"]</code> - Liste görünümü<br>';
        $help .= '<code>[qmc_masonry group="grup-id" columns="4"]</code> - Masonry görünümü<br>';
        $help .= '<code>[qmc_slider group="grup-id" slides_to_show="3"]</code> - Slider görünümü<br>';
        
        $help .= '</div>';
        
        return $help;
    }
}

// Tek kart shortcode'unu da kaydet
add_shortcode('qmc_single_card', array('EsistenzeQuickMenuCardsShortcodes', 'render_single_card_shortcode'));
add_shortcode('qmc_group_info', array('EsistenzeQuickMenuCardsShortcodes', 'render_group_info_shortcode'));
?>