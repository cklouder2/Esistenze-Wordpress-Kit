<?php
/**
 * Quick Menu Cards Frontend Sınıfı
 * Yeniden yazılmış, basit ve çalışır versiyon
 * 
 * @package Esistenze WordPress Kit
 * @subpackage Quick Menu Cards
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsFrontend {
    
    private $module_url;
    private $version = '2.0.0';
    
    public function __construct($module_url) {
        $this->module_url = $module_url;
        $this->init();
    }
    
    /**
     * Frontend'i başlat
     */
    private function init() {
        // CSS ve JS yükle
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Custom CSS ekle
        add_action('wp_head', array($this, 'add_custom_css'));
        
        // Debug log
        if (function_exists('qmc_log_error')) {
            qmc_log_error('QMC Frontend sınıfı başlatıldı');
        }
    }
    
    /**
     * Frontend scriptlerini yükle
     */
    public function enqueue_scripts() {
        // CSS yükle
        wp_enqueue_style(
            'qmc-frontend-style',
            $this->module_url . 'assets/style.css',
            array(),
            $this->version
        );
        
        // Ayarlara göre animasyon CSS'i ekle
        $settings = EsistenzeQuickMenuCards::get_settings();
        if ($settings['enable_animations']) {
            wp_add_inline_style('qmc-frontend-style', $this->get_animation_css());
        }
    }
    
    /**
     * Özel CSS'i head'e ekle
     */
    public function add_custom_css() {
        $settings = EsistenzeQuickMenuCards::get_settings();
        
        if (!empty($settings['custom_css'])) {
            echo '<style id="qmc-custom-css">' . wp_strip_all_tags($settings['custom_css']) . '</style>';
        }
        
        // Grid ayarlarını CSS değişkeni olarak ekle
        echo '<style id="qmc-grid-vars">';
        echo ':root {';
        echo '--qmc-grid-columns: ' . intval($settings['grid_columns']) . ';';
        echo '--qmc-card-spacing: ' . intval($settings['card_spacing'] ?? 20) . 'px;';
        echo '--qmc-border-radius: ' . intval($settings['border_radius'] ?? 8) . 'px;';
        echo '}';
        echo '</style>';
    }
    
    /**
     * Animasyon CSS'ini döndür
     */
    private function get_animation_css() {
        return '
        .qmc-card {
            transition: all 0.3s ease;
        }
        .qmc-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .qmc-card-button {
            transition: all 0.2s ease;
        }
        .qmc-card-button:hover {
            transform: scale(1.05);
        }
        .qmc-card-image img {
            transition: transform 0.3s ease;
        }
        .qmc-card:hover .qmc-card-image img {
            transform: scale(1.1);
        }
        ';
    }
    
    /**
     * Kartları render et
     */
    public function render_cards($group_id, $atts = array()) {
        // Varsayılan özellikler
        $defaults = array(
            'columns' => 0, // 0 = ayarlardan al
            'show_images' => null, // null = ayarlardan al
            'show_descriptions' => null, // null = ayarlardan al
            'custom_class' => '',
            'limit' => 0 // 0 = limitsiz
        );
        
        $atts = wp_parse_args($atts, $defaults);
        $settings = EsistenzeQuickMenuCards::get_settings();
        
        // Ayarları birleştir
        $columns = $atts['columns'] > 0 ? $atts['columns'] : $settings['grid_columns'];
        $show_images = $atts['show_images'] !== null ? $atts['show_images'] : $settings['show_images'];
        $show_descriptions = $atts['show_descriptions'] !== null ? $atts['show_descriptions'] : $settings['show_descriptions'];
        
        // Kart verilerini al
        $group_data = EsistenzeQuickMenuCards::get_cards($group_id);
        
        if (!$group_data || empty($group_data['cards'])) {
            return '<div class="qmc-no-cards">Bu grupta henüz kart bulunmuyor.</div>';
        }
        
        $cards = $group_data['cards'];
        
        // Limit uygula
        if ($atts['limit'] > 0) {
            $cards = array_slice($cards, 0, $atts['limit']);
        }
        
        // Container başlat
        $output = '<div class="qmc-cards-container ' . esc_attr($atts['custom_class']) . '" data-columns="' . $columns . '">';
        $output .= '<div class="qmc-cards-grid">';
        
        // Kartları render et
        foreach ($cards as $index => $card) {
            $output .= $this->render_single_card($card, $show_images, $show_descriptions, $settings);
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Tek kart render et
     */
    private function render_single_card($card, $show_images, $show_descriptions, $settings) {
        $card = wp_parse_args($card, array(
            'title' => '',
            'description' => '',
            'image' => '',
            'url' => '',
            'button_text' => $settings['default_button_text'],
            'type' => 'card'
        ));
        
        $output = '<div class="qmc-card">';
        
        // Resim
        if ($show_images && !empty($card['image'])) {
            $output .= '<div class="qmc-card-image">';
            $output .= '<img src="' . esc_url($card['image']) . '" alt="' . esc_attr($card['title']) . '" loading="lazy">';
            $output .= '</div>';
        }
        
        // İçerik
        $output .= '<div class="qmc-card-content">';
        
        // Başlık
        if (!empty($card['title'])) {
            $output .= '<h3 class="qmc-card-title">' . esc_html($card['title']) . '</h3>';
        }
        
        // Açıklama
        if ($show_descriptions && !empty($card['description'])) {
            $output .= '<p class="qmc-card-description">' . esc_html($card['description']) . '</p>';
        }
        
        // Buton
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
     * Banner kart render et
     */
    public function render_banner_card($card_data, $atts = array()) {
        $defaults = array(
            'full_width' => true,
            'custom_class' => 'qmc-banner'
        );
        
        $atts = wp_parse_args($atts, $defaults);
        $settings = EsistenzeQuickMenuCards::get_settings();
        
        $card = wp_parse_args($card_data, array(
            'title' => '',
            'description' => '',
            'image' => '',
            'url' => '',
            'button_text' => $settings['banner_button_text']
        ));
        
        $output = '<div class="qmc-banner-card ' . esc_attr($atts['custom_class']) . '">';
        
        if (!empty($card['image'])) {
            $output .= '<div class="qmc-banner-background" style="background-image: url(' . esc_url($card['image']) . ');">';
        } else {
            $output .= '<div class="qmc-banner-background">';
        }
        
        $output .= '<div class="qmc-banner-content">';
        
        if (!empty($card['title'])) {
            $output .= '<h2 class="qmc-banner-title">' . esc_html($card['title']) . '</h2>';
        }
        
        if (!empty($card['description'])) {
            $output .= '<p class="qmc-banner-description">' . esc_html($card['description']) . '</p>';
        }
        
        if (!empty($card['url'])) {
            $output .= '<a href="' . esc_url($card['url']) . '" class="qmc-banner-button">';
            $output .= esc_html($card['button_text']);
            $output .= '</a>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Liste görünümü render et
     */
    public function render_list_view($group_id, $atts = array()) {
        $defaults = array(
            'show_images' => true,
            'show_descriptions' => true,
            'custom_class' => 'qmc-list',
            'limit' => 0
        );
        
        $atts = wp_parse_args($atts, $defaults);
        
        // Kart verilerini al
        $group_data = EsistenzeQuickMenuCards::get_cards($group_id);
        
        if (!$group_data || empty($group_data['cards'])) {
            return '<div class="qmc-no-cards">Bu grupta henüz kart bulunmuyor.</div>';
        }
        
        $cards = $group_data['cards'];
        
        // Limit uygula
        if ($atts['limit'] > 0) {
            $cards = array_slice($cards, 0, $atts['limit']);
        }
        
        $output = '<div class="qmc-list-container ' . esc_attr($atts['custom_class']) . '">';
        $output .= '<ul class="qmc-list">';
        
        foreach ($cards as $card) {
            $output .= '<li class="qmc-list-item">';
            
            if ($atts['show_images'] && !empty($card['image'])) {
                $output .= '<div class="qmc-list-image">';
                $output .= '<img src="' . esc_url($card['image']) . '" alt="' . esc_attr($card['title']) . '">';
                $output .= '</div>';
            }
            
            $output .= '<div class="qmc-list-content">';
            
            if (!empty($card['title'])) {
                if (!empty($card['url'])) {
                    $output .= '<h4 class="qmc-list-title"><a href="' . esc_url($card['url']) . '">' . esc_html($card['title']) . '</a></h4>';
                } else {
                    $output .= '<h4 class="qmc-list-title">' . esc_html($card['title']) . '</h4>';
                }
            }
            
            if ($atts['show_descriptions'] && !empty($card['description'])) {
                $output .= '<p class="qmc-list-description">' . esc_html($card['description']) . '</p>';
            }
            
            $output .= '</div>';
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Masonry görünümü render et
     */
    public function render_masonry_view($group_id, $atts = array()) {
        $defaults = array(
            'columns' => 3,
            'show_images' => true,
            'show_descriptions' => true,
            'custom_class' => 'qmc-masonry'
        );
        
        $atts = wp_parse_args($atts, $defaults);
        
        // Normal grid render et (CSS ile masonry efekti)
        $atts['custom_class'] .= ' qmc-masonry-layout';
        
        return $this->render_cards($group_id, $atts);
    }
    
    /**
     * Slider görünümü render et
     */
    public function render_slider_view($group_id, $atts = array()) {
        $defaults = array(
            'slides_to_show' => 3,
            'auto_play' => false,
            'show_arrows' => true,
            'show_dots' => true,
            'custom_class' => 'qmc-slider'
        );
        
        $atts = wp_parse_args($atts, $defaults);
        
        // Kart verilerini al
        $group_data = EsistenzeQuickMenuCards::get_cards($group_id);
        
        if (!$group_data || empty($group_data['cards'])) {
            return '<div class="qmc-no-cards">Bu grupta henüz kart bulunmuyor.</div>';
        }
        
        $cards = $group_data['cards'];
        
        $output = '<div class="qmc-slider-container ' . esc_attr($atts['custom_class']) . '">';
        
        if ($atts['show_arrows']) {
            $output .= '<button class="qmc-slider-arrow qmc-slider-prev">&larr;</button>';
        }
        
        $output .= '<div class="qmc-slider-wrapper">';
        $output .= '<div class="qmc-slider-track">';
        
        foreach ($cards as $card) {
            $output .= '<div class="qmc-slider-slide">';
            $output .= $this->render_single_card($card, true, true, EsistenzeQuickMenuCards::get_settings());
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        if ($atts['show_arrows']) {
            $output .= '<button class="qmc-slider-arrow qmc-slider-next">&rarr;</button>';
        }
        
        if ($atts['show_dots']) {
            $output .= '<div class="qmc-slider-dots">';
            for ($i = 0; $i < count($cards); $i++) {
                $output .= '<button class="qmc-slider-dot' . ($i === 0 ? ' active' : '') . '" data-slide="' . $i . '"></button>';
            }
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Responsive ayarlarını al
     */
    public function get_responsive_settings() {
        return array(
            'mobile' => array(
                'breakpoint' => 768,
                'columns' => 1,
                'spacing' => 15
            ),
            'tablet' => array(
                'breakpoint' => 1024,
                'columns' => 2,
                'spacing' => 18
            ),
            'desktop' => array(
                'breakpoint' => 1200,
                'columns' => 3,
                'spacing' => 20
            )
        );
    }
    
    /**
     * Schema.org structured data ekle
     */
    public function add_structured_data($cards, $group_name = '') {
        if (empty($cards)) {
            return;
        }
        
        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $group_name,
            'numberOfItems' => count($cards),
            'itemListElement' => array()
        );
        
        foreach ($cards as $index => $card) {
            $item = array(
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => array(
                    '@type' => 'Thing',
                    'name' => $card['title'],
                    'description' => $card['description'],
                    'url' => $card['url']
                )
            );
            
            if (!empty($card['image'])) {
                $item['item']['image'] = $card['image'];
            }
            
            $structured_data['itemListElement'][] = $item;
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode($structured_data) . '</script>';
    }
}
?>