<?php
/*
 * Quick Menu Cards Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) exit;

class EsistenzeQuickMenuCards {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Register shortcodes
        add_shortcode('esistenze_quick_menu', array($this, 'render_quick_menu_group'));
        add_shortcode('esistenze_quick_menu_banner', array($this, 'render_quick_menu_banner'));
        
        // Enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Admin init
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public static function admin_page() {
        $kartlar = get_option('esistenze_quick_menu_cards', array());
        if (!is_array($kartlar)) {
            $kartlar = array();
            update_option('esistenze_quick_menu_cards', $kartlar);
        }

        echo '<div class="wrap">';
        echo '<h1>Quick Menu Cards</h1>';
        echo '<p>Görsel, başlık, açıklama ve bağlantı içeren modern menü kartları oluşturun.</p>';
        
        echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>Mevcut Gruplar ve Kısa Kodlar:</h3>';
        echo '<ul>';
        foreach ($kartlar as $group_id => $group_data) {
            echo '<li><strong>Grup #' . esc_html($group_id) . ':</strong> ';
            echo '<code>[esistenze_quick_menu id="' . esc_html($group_id) . '"]</code> | ';
            echo '<code>[esistenze_quick_menu_banner id="' . esc_html($group_id) . '"]</code></li>';
        }
        echo '</ul>';
        echo '</div>';
        
        $next_id = count($kartlar);
        echo '<a href="?page=esistenze-quick-menu&edit_group=' . $next_id . '" class="button button-primary">Yeni Grup Oluştur</a>';

        if (isset($_GET['edit_group'])) {
            self::edit_group_page((int)$_GET['edit_group']);
        }
        echo '</div>';
    }
    
    private static function edit_group_page($group_id = 0) {
        $kartlar = get_option('esistenze_quick_menu_cards', array());
        if (!isset($kartlar[$group_id]) || !is_array($kartlar[$group_id])) {
            $kartlar[$group_id] = array();
        }
        $group_data = $kartlar[$group_id];

        echo '<div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">';
        echo '<h2>Grup #' . intval($group_id) . ' Düzenle</h2>';
        echo '<form method="post" action="options.php">';
        settings_fields('esistenze_quick_menu_cards_group');
        do_settings_sections('esistenze_quick_menu_cards_group');

        echo '<div id="kartlar-container">';
        foreach ($group_data as $index => $kart) {
            $title = isset($kart['title']) ? esc_attr($kart['title']) : '';
            $desc = isset($kart['desc']) ? esc_attr($kart['desc']) : '';
            $img = isset($kart['img']) ? esc_url($kart['img']) : '';
            $url = isset($kart['url']) ? esc_url($kart['url']) : '';
            echo '<div class="kart-kutu" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #fff;">';
            echo '<h4>Kart #' . ($index + 1) . '</h4>';
            echo '<table class="form-table">';
            echo '<tr><th><label>Başlık:</label></th><td><input type="text" name="esistenze_quick_menu_cards[' . $group_id . '][' . $index . '][title]" value="' . $title . '" placeholder="Başlık" class="regular-text"></td></tr>';
            echo '<tr><th><label>Açıklama:</label></th><td><input type="text" name="esistenze_quick_menu_cards[' . $group_id . '][' . $index . '][desc]" value="' . $desc . '" placeholder="Açıklama" class="regular-text"></td></tr>';
            echo '<tr><th><label>Görsel URL:</label></th><td><input type="hidden" class="image-url" name="esistenze_quick_menu_cards[' . $group_id . '][' . $index . '][img]" value="' . $img . '"><button class="upload-image-button button" type="button">Görsel Yükle</button></td></tr>';
            echo '<tr><th><label>Bağlantı URL:</label></th><td><input type="url" name="esistenze_quick_menu_cards[' . $group_id . '][' . $index . '][url]" value="' . $url . '" placeholder="https://example.com" class="regular-text"></td></tr>';
            echo '</table>';
            echo '<div class="preview">' . ($img ? '<img src="' . $img . '" style="max-width:100px; margin-top:10px;">' : '') . '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<button type="button" class="button" id="add-karti">Yeni Kart Ekle</button>';
        echo '<input type="hidden" name="option_page" value="esistenze_quick_menu_cards_group">';
        echo '<input type="hidden" name="action" value="update">';
        echo '<p class="submit"><input type="submit" class="button-primary" value="Değişiklikleri Kaydet"></p>';
        echo '</form>';

        // JavaScript for dynamic form
        ?>
        <script>
        jQuery(document).ready(function($) {
            var groupId = <?php echo $group_id; ?>;
            
            $('#add-karti').on('click', function() {
                var container = $('#kartlar-container');
                var index = container.children().length;
                var html = '<div class="kart-kutu" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #fff;">';
                html += '<h4>Kart #' + (index + 1) + '</h4>';
                html += '<table class="form-table">';
                html += '<tr><th><label>Başlık:</label></th><td><input type="text" name="esistenze_quick_menu_cards[' + groupId + '][' + index + '][title]" placeholder="Başlık" class="regular-text"></td></tr>';
                html += '<tr><th><label>Açıklama:</label></th><td><input type="text" name="esistenze_quick_menu_cards[' + groupId + '][' + index + '][desc]" placeholder="Açıklama" class="regular-text"></td></tr>';
                html += '<tr><th><label>Görsel URL:</label></th><td><input type="hidden" class="image-url" name="esistenze_quick_menu_cards[' + groupId + '][' + index + '][img]"><button class="upload-image-button button" type="button">Görsel Yükle</button></td></tr>';
                html += '<tr><th><label>Bağlantı URL:</label></th><td><input type="url" name="esistenze_quick_menu_cards[' + groupId + '][' + index + '][url]" placeholder="https://example.com" class="regular-text"></td></tr>';
                html += '</table>';
                html += '<div class="preview"></div>';
                html += '</div>';
                container.append(html);
            });

            $(document).on('click', '.upload-image-button', function(e) {
                e.preventDefault();
                var button = $(this);
                var uploader = wp.media({
                    title: 'Görsel Seç',
                    button: { text: 'Seç' },
                    multiple: false
                }).on('select', function() {
                    var attachment = uploader.state().get('selection').first().toJSON();
                    button.siblings('.image-url').val(attachment.url);
                    button.closest('.kart-kutu').find('.preview').html('<img src="' + attachment.url + '" style="max-width:100px; margin-top:10px;">');
                }).open();
            });
        });
        </script>
        <?php
        echo '</div>';
    }
    
    public function register_settings() {
        register_setting('esistenze_quick_menu_cards_group', 'esistenze_quick_menu_cards');
    }
    
    public function enqueue_styles() {
        wp_enqueue_style('esistenze-quick-menu-style', ESISTENZE_WP_KIT_URL . 'modules/quick-menu-cards/assets/style.css', array(), ESISTENZE_WP_KIT_VERSION);
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_media();
    }

    public function render_quick_menu_group($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts);
        $group_id = (int)$atts['id'];
        $kartlar = get_option('esistenze_quick_menu_cards', array());
        $group = isset($kartlar[$group_id]) && is_array($kartlar[$group_id]) ? $kartlar[$group_id] : array();

        $output = '<div class="esistenze-quick-menu-wrapper">';
        foreach ($group as $kart) {
            $link_start = isset($kart['url']) && $kart['url'] ? '<a href="' . esc_url($kart['url']) . '" class="esistenze-quick-menu-kart" target="_blank">' : '<div class="esistenze-quick-menu-kart">';
            $link_end = isset($kart['url']) && $kart['url'] ? '</a>' : '</div>';
            $output .= $link_start;
            $output .= '<div class="esistenze-quick-menu-icerik">';
            if (!empty($kart['img'])) {
                $output .= '<img src="' . esc_url($kart['img']) . '" alt="' . esc_attr($kart['title']) . '">';
            }
            $output .= '<div class="esistenze-quick-menu-yazi">';
            $output .= '<h4>' . esc_html($kart['title']) . '</h4>';
            $output .= '<p>' . esc_html($kart['desc']) . '</p>';
            $output .= '</div></div>';
            $output .= '<div class="esistenze-quick-menu-buton">Ürünleri İncele</div>';
            $output .= $link_end;
        }
        $output .= '</div>';
        return $output;
    }

    public function render_quick_menu_banner($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts);
        $group_id = (int)$atts['id'];
        $kartlar = get_option('esistenze_quick_menu_cards', array());
        $group = isset($kartlar[$group_id]) && is_array($kartlar[$group_id]) ? $kartlar[$group_id] : array();

        $output = '<div class="esistenze-quick-menu-banner-wrapper">';
        foreach ($group as $kart) {
            if (empty($kart['img'])) continue;

            $url_start = !empty($kart['url']) ? '<a href="' . esc_url($kart['url']) . '" class="esistenze-quick-menu-banner" target="_blank">' : '<div class="esistenze-quick-menu-banner">';
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

// Initialize the module
EsistenzeQuickMenuCards::getInstance();