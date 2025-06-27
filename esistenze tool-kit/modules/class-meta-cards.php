<?php
/**
 * Meta Cards Module
 * Hızlı Menü Kartları modülü
 */

if (!defined('ABSPATH')) exit;

class EsistenzaMetaCards {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_shortcode('hizli_menu', array($this, 'render_hizli_menu_group'));
        add_shortcode('hizli_menu_banner', array($this, 'render_hizli_menu_banner'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'esistenza-meta-cards-style', 
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/css/meta-cards.css',
            array(),
            ESISTENZE_TOOLKIT_VERSION
        );
    }

    public function enqueue_admin_scripts($hook) {
        // Sadece plugin sayfalarında yükle
        if (strpos($hook, 'hizli-menu') === false) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script(
            'esistenza-meta-cards-admin-js',
            ESISTENZE_TOOLKIT_PLUGIN_URL . 'assets/js/meta-cards.js',
            array('jquery', 'media-upload'),
            ESISTENZE_TOOLKIT_VERSION,
            true
        );
    }

    public function admin_menu() {
        add_submenu_page(
            'esistenze-toolkit',
            'Hızlı Menü Kartları',
            'Menü Kartları',
            'manage_options',
            'hizli-menu-karti',
            array($this, 'group_list_page')
        );
    }

    public function register_settings() {
        register_setting('hizli_menu_karti_group', 'hizli_menu_kartlari');
    }

    public function group_list_page() {
        $kartlar = get_option('hizli_menu_kartlari', array());
        if (!is_array($kartlar)) {
            $kartlar = array();
            update_option('hizli_menu_kartlari', $kartlar);
        }

        echo '<div class="wrap"><h1>Hızlı Menü Kart Grupları</h1>';
        echo '<div class="esistenza-module-header">';
        echo '<p class="description">Görsel, başlık, açıklama ve bağlantı içeren modern buton grupları oluşturun. Kısa kod ile ızgara veya banner görünümde ekleyebilirsiniz.</p>';
        echo '</div>';
        
        echo '<div class="shortcode-list">';
        echo '<h3>Mevcut Gruplar ve Kısa Kodları:</h3>';
        echo '<ul>';
        foreach ($kartlar as $group_id => $group_data) {
            echo '<li>';
            echo '<strong>Grup #' . esc_html($group_id) . ':</strong> ';
            echo '<code>[hizli_menu id=' . esc_html($group_id) . ']</code> | ';
            echo '<code>[hizli_menu_banner id=' . esc_html($group_id) . ']</code>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
        
        $next_id = count($kartlar);
        echo '<a href="?page=hizli-menu-karti&edit_group=' . $next_id . '" class="button button-primary">Yeni Grup Oluştur</a>';

        if (isset($_GET['edit_group'])) {
            $this->settings_page((int)$_GET['edit_group']);
        }
        echo '</div>';
    }

    public function settings_page($group_id = 0) {
        $kartlar = get_option('hizli_menu_kartlari', array());
        if (!isset($kartlar[$group_id]) || !is_array($kartlar[$group_id])) {
            $kartlar[$group_id] = array();
        }
        $group_data = $kartlar[$group_id];

        echo '<div class="esistenza-form-container">';
        echo '<h2>Grup #' . intval($group_id) . ' Düzenle</h2>';
        echo '<form method="post" action="options.php">';
        settings_fields('hizli_menu_karti_group');
        do_settings_sections('hizli_menu_karti_group');

        echo '<div id="kartlar-container">';
        foreach ($group_data as $index => $kart) {
            $title = isset($kart['title']) ? esc_attr($kart['title']) : '';
            $desc = isset($kart['desc']) ? esc_attr($kart['desc']) : '';
            $img = isset($kart['img']) ? esc_url($kart['img']) : '';
            $url = isset($kart['url']) ? esc_url($kart['url']) : '';
            echo '<div class="kart-kutu">';
            echo '<label>Başlık:</label>';
            echo '<input type="text" name="hizli_menu_kartlari[' . $group_id . '][' . $index . '][title]" value="' . $title . '" placeholder="Başlık">';
            echo '<label>Açıklama:</label>';
            echo '<input type="text" name="hizli_menu_kartlari[' . $group_id . '][' . $index . '][desc]" value="' . $desc . '" placeholder="Soru / Açıklama">';
            echo '<label>Görsel:</label>';
            echo '<input type="hidden" class="image-url" name="hizli_menu_kartlari[' . $group_id . '][' . $index . '][img]" value="' . $img . '">';
            echo '<label>Bağlantı URL:</label>';
            echo '<input type="url" name="hizli_menu_kartlari[' . $group_id . '][' . $index . '][url]" value="' . $url . '" placeholder="https://example.com">';
            echo '<button class="upload-image-button button">Görsel Yükle</button>';
            echo '<div class="preview">' . ($img ? '<img src="' . $img . '" style="max-width:100px; margin-top:10px;">' : '') . '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<button type="button" class="button" id="add-karti">Yeni Kart Ekle</button>';
        echo '<input type="hidden" name="option_page" value="hizli_menu_karti_group">';
        echo '<input type="hidden" name="action" value="update">';
        echo '<input type="submit" class="button-primary" value="Değişiklikleri Kaydet">';
        echo '</form></div>';
        
        // JavaScript için grup ID'sini window objesine aktar
        echo '<script>window.metaCardsGroupId = ' . intval($group_id) . ';</script>';
    }

    public function render_hizli_menu_group($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts);
        $group_id = (int)$atts['id'];
        $kartlar = get_option('hizli_menu_kartlari', array());
        $group = isset($kartlar[$group_id]) && is_array($kartlar[$group_id]) ? $kartlar[$group_id] : array();

        $output = '<div class="hizli-menu-wrapper">';
        foreach ($group as $kart) {
            $link_start = isset($kart['url']) && $kart['url'] ? '<a href="' . esc_url($kart['url']) . '" class="hizli-menu-kart" target="_blank">' : '<div class="hizli-menu-kart">';
            $link_end = isset($kart['url']) && $kart['url'] ? '</a>' : '</div>';
            $output .= $link_start;
            $output .= '<div class="hizli-menu-icerik">';
            $output .= '<img src="' . esc_url($kart['img']) . '" alt="' . esc_attr($kart['title']) . '">';
            $output .= '<div class="hizli-menu-yazi">';
            $output .= '<h4>' . esc_html($kart['title']) . '</h4>';
            $output .= '<p>' . esc_html($kart['desc']) . '</p>';
            $output .= '</div></div>';
            $output .= '<div class="hizli-menu-buton">Ürünleri İncele</div>';
            $output .= $link_end;
        }
        $output .= '</div>';
        return $output;
    }

    public function render_hizli_menu_banner($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts);
        $group_id = (int)$atts['id'];
        $kartlar = get_option('hizli_menu_kartlari', array());
        $group = isset($kartlar[$group_id]) && is_array($kartlar[$group_id]) ? $kartlar[$group_id] : array();

        $output = '<div class="hizli-menu-banner-wrapper">';
        foreach ($group as $kart) {
            if (empty($kart['img'])) continue;

            $url_start = !empty($kart['url']) ? '<a href="' . esc_url($kart['url']) . '" class="hizli-menu-banner" target="_blank">' : '<div class="hizli-menu-banner">';
            $url_end   = !empty($kart['url']) ? '</a>' : '</div>';

            $output .= $url_start;
            $output .= '<div class="banner-img"><img src="' . esc_url($kart['img']) . '" alt="' . esc_attr($kart['title']) . '"></div>';
            $output .= '<div class="banner-text">';
            $output .= '<h4>' . esc_html($kart['title']) . '</h4>';
            $output .= '<p>' . esc_html($kart['desc']) . '</p>';
            $output .= '</div>';
            $output .= '<div class="banner-button"><span>Ürünleri İncele</span></div>';
            $output .= $url_end;
        }
        $output .= '</div>';
        return $output;
    }
}

// Modülü başlat
new EsistenzaMetaCards(); 