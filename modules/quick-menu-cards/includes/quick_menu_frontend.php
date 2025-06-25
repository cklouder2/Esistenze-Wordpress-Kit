<?php
/*
 * Quick Menu Cards - Frontend Class
 * Handles all frontend functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsFrontend {
    
    private $module_url;
    
    public function __construct($module_url) {
        $this->module_url = $module_url;
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_footer', array($this, 'add_tracking_script'), 999);
        add_action('wp_head', array($this, 'add_schema_markup'));
    }
    
    public function enqueue_styles() {
        wp_enqueue_style(
            'esistenze-quick-menu-cards',
            $this->module_url . 'assets/style.css',
            array(),
            $this->get_version()
        );
        
        // Dinamik CSS ekle
        $dynamic_css = $this->generate_dynamic_css();
        if (!empty($dynamic_css)) {
            wp_add_inline_style('esistenze-quick-menu-cards', $dynamic_css);
        }
    }
    
    public function render_cards_grid($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts);
        $group_id = (int)$atts['id'];
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $group = isset($kartlar[$group_id]) && is_array($kartlar[$group_id]) ? $kartlar[$group_id] : array();
        
        if (empty($group)) {
            return '<p class="quick-menu-empty">Bu grupta henüz kart bulunmuyor.</p>';
        }
        
        // Analytics tracking
        $this->track_group_view($group_id);
        
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        $button_text = $settings['default_button_text'] ?? 'Detayları Gör';
        
        $output = '<div class="hizli-menu-wrapper" data-group-id="' . $group_id . '">';
        
        foreach ($group as $index => $kart) {
            $output .= $this->render_single_card($kart, $group_id, $index, $button_text, $settings);
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    public function render_banner_layout($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts);
        $group_id = (int)$atts['id'];
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $group = isset($kartlar[$group_id]) && is_array($kartlar[$group_id]) ? $kartlar[$group_id] : array();
        
        if (empty($group)) {
            return '<p class="quick-menu-empty">Bu grupta henüz kart bulunmuyor.</p>';
        }
        
        // Analytics tracking
        $this->track_group_view($group_id);
        
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        $button_text = $settings['banner_button_text'] ?? 'Ürünleri İncele';
        
        $output = '<div class="hizli-menu-banner-wrapper" data-group-id="' . $group_id . '">';
        
        foreach ($group as $index => $kart) {
            if (empty($kart['img'])) continue; // Banner için görsel zorunlu
            
            $output .= $this->render_single_banner($kart, $group_id, $index, $button_text, $settings);
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    private function render_single_card($kart, $group_id, $index, $button_text, $settings) {
        $has_url = !empty($kart['url']);
        $card_class = 'hizli-menu-kart';
        $onclick = $has_url ? 'onclick="trackCardClick(' . $group_id . ', ' . $index . ')"' : '';
        
        $link_start = $has_url ? 
            '<a href="' . esc_url($kart['url']) . '" class="' . $card_class . '" target="_blank" ' . $onclick . '>' : 
            '<div class="' . $card_class . '">';
        $link_end = $has_url ? '</a>' : '</div>';
        
        $output = $link_start;
        $output .= '<div class="hizli-menu-icerik">';
        
        // Görsel
        if (!empty($kart['img'])) {
            $img_attrs = $this->get_image_attributes($settings);
            $output .= '<img src="' . esc_url($kart['img']) . '" alt="' . esc_attr($kart['title'] ?? '') . '"' . $img_attrs . '>';
        }
        
        // Metin içeriği
        $output .= '<div class="hizli-menu-yazi">';
        $output .= '<h4>' . esc_html($kart['title'] ?? 'Başlıksız') . '</h4>';
        $output .= '<p>' . esc_html($kart['desc'] ?? '') . '</p>';
        $output .= '</div></div>';
        
        // Buton
        $output .= '<div class="hizli-menu-buton">' . esc_html($button_text) . '</div>';
        $output .= $link_end;
        
        return $output;
    }
    
    private function render_single_banner($kart, $group_id, $index, $button_text, $settings) {
        $has_url = !empty($kart['url']);
        $onclick = $has_url ? 'onclick="trackCardClick(' . $group_id . ', ' . $index . ')"' : '';
        
        $url_start = $has_url ? 
            '<a href="' . esc_url($kart['url']) . '" class="hizli-menu-banner" target="_blank" ' . $onclick . '>' : 
            '<div class="hizli-menu-banner">';
        $url_end = $has_url ? '</a>' : '</div>';
        
        $output = $url_start;
        
        // Banner görseli
        $img_attrs = $this->get_image_attributes($settings);
        $output .= '<div class="banner-img">';
        $output .= '<img src="' . esc_url($kart['img']) . '" alt="' . esc_attr($kart['title'] ?? '') . '"' . $img_attrs . '>';
        $output .= '</div>';
        
        // Banner metni
        $output .= '<div class="banner-text">';
        $output .= '<h4>' . esc_html($kart['title'] ?? 'Başlıksız') . '</h4>';
        $output .= '<p>' . esc_html($kart['desc'] ?? '') . '</p>';
        $output .= '</div>';
        
        // Banner butonu
        $output .= '<div class="banner-button"><span>' . esc_html($button_text) . '</span></div>';
        $output .= $url_end;
        
        return $output;
    }
    
    private function get_image_attributes($settings) {
        $attrs = '';
        
        // Lazy loading
        if (!empty($settings['enable_lazy_loading'])) {
            $attrs .= ' loading="lazy"';
        }
        
        // Accessibility
        $attrs .= ' decoding="async"';
        
        return $attrs;
    }
    
    private function track_group_view($group_id) {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        if (empty($settings['enable_analytics'])) {
            return;
        }
        
        // Analytics verilerini güncelle
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        $analytics['total_views'] = ($analytics['total_views'] ?? 0) + 1;
        $analytics['group_views'][$group_id] = ($analytics['group_views'][$group_id] ?? 0) + 1;
        $analytics['last_view'] = current_time('mysql');
        
        update_option('esistenze_quick_menu_analytics', $analytics);
    }
    
    public function add_tracking_script() {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        if (empty($settings['enable_analytics'])) {
            return;
        }
        
        ?>
        <script>
        function trackCardClick(groupId, cardIndex) {
            if (typeof jQuery !== 'undefined') {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'esistenze_track_card_click',
                        group_id: groupId,
                        card_index: cardIndex
                    },
                    timeout: 2000 // 2 saniye timeout
                });
            }
        }
        
        // Intersection Observer ile görüntülenme takibi
        if (typeof IntersectionObserver !== 'undefined') {
            const cardObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const wrapper = entry.target;
                        const groupId = wrapper.getAttribute('data-group-id');
                        if (groupId && !wrapper.classList.contains('viewed')) {
                            wrapper.classList.add('viewed');
                            // Bu görüntülenme zaten PHP tarafında track ediliyor
                        }
                    }
                });
            }, { 
                threshold: 0.5,
                rootMargin: '50px'
            });
            
            // Tüm kart wrapper'larını gözlemle
            document.querySelectorAll('.hizli-menu-wrapper, .hizli-menu-banner-wrapper').forEach(function(wrapper) {
                cardObserver.observe(wrapper);
            });
        }
        </script>
        <?php
    }
    
    public function add_schema_markup() {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        if (empty($settings['enable_schema_markup'])) {
            return;
        }
        
        // Sadece ana sayfa veya kart içeren sayfalarda schema ekle
        if (!is_front_page() && !is_home() && !$this->page_has_cards()) {
            return;
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        if (empty($kartlar)) {
            return;
        }
        
        $schema = $this->generate_schema_markup($kartlar);
        if (!empty($schema)) {
            echo $schema;
        }
    }
    
    private function page_has_cards() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Post içeriğinde shortcode var mı kontrol et
        $shortcodes = array('quick_menu_cards', 'quick_menu_banner', 'hizli_menu', 'hizli_menu_banner');
        
        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function generate_schema_markup($kartlar) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Quick Menu Cards',
            'description' => 'Interactive navigation cards for website sections',
            'itemListElement' => array()
        );
        
        $position = 1;
        foreach ($kartlar as $group_id => $group) {
            if (!is_array($group)) continue;
            
            foreach ($group as $kart) {
                if (empty($kart['title'])) continue;
                
                $schema['itemListElement'][] = array(
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $kart['title'],
                    'description' => $kart['desc'] ?? '',
                    'url' => $kart['url'] ?? '',
                    'image' => $kart['img'] ?? ''
                );
            }
        }
        
        if (empty($schema['itemListElement'])) {
            return '';
        }
        
        return '<script type="application/ld+json">' . 
               wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . 
               '</script>' . "\n";
    }
    
    private function generate_dynamic_css() {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        $css = '';
        
        // Özel CSS ayarları buraya eklenebilir
        if (!empty($settings['custom_css'])) {
            $css .= $settings['custom_css'];
        }
        
        // Responsive ayarlar
        if (!empty($settings['mobile_columns'])) {
            $css .= '@media (max-width: 768px) {';
            $css .= '.hizli-menu-wrapper { grid-template-columns: repeat(' . intval($settings['mobile_columns']) . ', 1fr); }';
            $css .= '}';
        }
        
        return $css;
    }
    
    private function get_version() {
        return defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0';
    }
}

?>