<?php
/*
 * Quick Menu Cards - Frontend Class
 * Handles all frontend functionality with modern architecture
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsFrontend {
    
    private $module_url;
    private $settings;
    private $cache_enabled;
    
    public function __construct($module_url) {
        $this->module_url = $module_url;
        $this->settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        $this->cache_enabled = !empty($this->settings['cache_duration']);
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_footer', array($this, 'add_tracking_script'), 999);
        add_action('wp_head', array($this, 'add_schema_markup'));
        
        // Lazy loading için filter
        if (!empty($this->settings['enable_lazy_loading'])) {
            add_filter('wp_get_attachment_image_attributes', array($this, 'add_lazy_loading'), 10, 3);
        }
    }
    
    public function enqueue_styles() {
        // Ana stil dosyası
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
        
        // Custom CSS
        if (!empty($this->settings['custom_css'])) {
            wp_add_inline_style('esistenze-quick-menu-cards', $this->settings['custom_css']);
        }
    }
    
    public function render_cards_grid($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'limit' => 0,
            'columns' => 4,
            'show_button' => 'yes',
            'button_text' => '',
            'order' => 'asc',
            'class' => ''
        ), $atts);
        
        $group_id = (int)$atts['id'];
        $limit = (int)$atts['limit'];
        $columns = max(1, min(6, (int)$atts['columns'])); // 1-6 arası sınırla
        
        // Cache kontrolü
        $cache_key = 'qmc_grid_' . md5(serialize($atts));
        if ($this->cache_enabled) {
            $cached_output = get_transient($cache_key);
            if ($cached_output !== false) {
                return $cached_output;
            }
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $group = isset($kartlar[$group_id]) && is_array($kartlar[$group_id]) ? $kartlar[$group_id] : array();
        
        if (empty($group)) {
            return '<p class="quick-menu-empty">Bu grupta henüz kart bulunmuyor.</p>';
        }
        
        // Filtreleme ve sıralama
        $group = $this->filter_and_sort_cards($group, $atts);
        
        // Limit uygula
        if ($limit > 0) {
            $group = array_slice($group, 0, $limit);
        }
        
        // Analytics tracking
        $this->track_group_view($group_id);
        
        $button_text = !empty($atts['button_text']) ? $atts['button_text'] : ($this->settings['default_button_text'] ?? 'Detayları Gör');
        $wrapper_class = 'esistenze-quick-menu-wrapper' . (!empty($atts['class']) ? ' ' . esc_attr($atts['class']) : '');
        
        $output = '<div class="' . $wrapper_class . '" data-group-id="' . $group_id . '" data-columns="' . $columns . '">';
        
        foreach ($group as $index => $kart) {
            if (empty($kart['enabled']) && $kart['enabled'] !== null) {
                continue; // Devre dışı kartları atla
            }
            
            $output .= $this->render_single_card($kart, $group_id, $index, $button_text, $atts);
        }
        
        $output .= '</div>';
        
        // Cache'e kaydet
        if ($this->cache_enabled) {
            set_transient($cache_key, $output, $this->settings['cache_duration']);
        }
        
        return $output;
    }
    
    public function render_banner_layout($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'limit' => 0,
            'button_text' => '',
            'columns' => 2,
            'order' => 'asc',
            'class' => ''
        ), $atts);
        
        $group_id = (int)$atts['id'];
        $limit = (int)$atts['limit'];
        $columns = max(1, min(3, (int)$atts['columns'])); // Banner için 1-3 sütun
        
        // Cache kontrolü
        $cache_key = 'qmc_banner_' . md5(serialize($atts));
        if ($this->cache_enabled) {
            $cached_output = get_transient($cache_key);
            if ($cached_output !== false) {
                return $cached_output;
            }
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $group = isset($kartlar[$group_id]) && is_array($kartlar[$group_id]) ? $kartlar[$group_id] : array();
        
        if (empty($group)) {
            return '<p class="quick-menu-empty">Bu grupta henüz kart bulunmuyor.</p>';
        }
        
        // Banner için sadece görseli olan kartları al
        $group = array_filter($group, function($card) {
            return !empty($card['img']) && (empty($card['enabled']) || $card['enabled'] !== false);
        });
        
        if (empty($group)) {
            return '<p class="quick-menu-empty">Banner görünümü için görseli olan kartlar gereklidir.</p>';
        }
        
        // Filtreleme ve sıralama
        $group = $this->filter_and_sort_cards($group, $atts);
        
        // Limit uygula
        if ($limit > 0) {
            $group = array_slice($group, 0, $limit);
        }
        
        // Analytics tracking
        $this->track_group_view($group_id);
        
        $button_text = !empty($atts['button_text']) ? $atts['button_text'] : ($this->settings['banner_button_text'] ?? 'Ürünleri İncele');
        $wrapper_class = 'esistenze-quick-menu-banner-wrapper' . (!empty($atts['class']) ? ' ' . esc_attr($atts['class']) : '');
        
        $output = '<div class="' . $wrapper_class . '" data-group-id="' . $group_id . '" data-columns="' . $columns . '">';
        
        foreach ($group as $index => $kart) {
            $output .= $this->render_single_banner($kart, $group_id, $index, $button_text, $atts);
        }
        
        $output .= '</div>';
        
        // Cache'e kaydet
        if ($this->cache_enabled) {
            set_transient($cache_key, $output, $this->settings['cache_duration']);
        }
        
        return $output;
    }
    
    private function filter_and_sort_cards($group, $atts) {
        // Sıralama
        if ($atts['order'] === 'desc') {
            $group = array_reverse($group);
        } elseif ($atts['order'] === 'random') {
            shuffle($group);
        } elseif ($atts['order'] === 'title') {
            usort($group, function($a, $b) {
                return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
            });
        }
        
        return $group;
    }
    
    private function render_single_card($kart, $group_id, $index, $button_text, $atts) {
        $has_url = !empty($kart['url']);
        $card_class = 'esistenze-quick-menu-kart';
        $onclick = $has_url ? 'onclick="trackCardClick(' . $group_id . ', ' . $index . ')"' : '';
        
        // Erişilebilirlik için ARIA etiketleri
        $aria_label = 'aria-label="' . esc_attr($kart['title'] ?? 'Kart') . ($has_url ? ' - Tıklayarak detayları görün' : '') . '"';
        
        $link_start = $has_url ? 
            '<a href="' . esc_url($kart['url']) . '" class="' . $card_class . '" target="_blank" ' . $onclick . ' ' . $aria_label . '>' : 
            '<div class="' . $card_class . '" ' . $aria_label . '>';
        $link_end = $has_url ? '</a>' : '</div>';
        
        $output = $link_start;
        $output .= '<div class="esistenze-quick-menu-icerik">';
        
        // Görsel
        if (!empty($kart['img'])) {
            $img_attrs = $this->get_image_attributes($kart, $index);
            $alt_text = !empty($kart['title']) ? $kart['title'] : 'Kart görseli';
            $output .= '<img src="' . esc_url($kart['img']) . '" alt="' . esc_attr($alt_text) . '"' . $img_attrs . '>';
        }
        
        // Metin içeriği
        $output .= '<div class="esistenze-quick-menu-yazi">';
        $output .= '<h4>' . esc_html($kart['title'] ?? 'Başlıksız') . '</h4>';
        if (!empty($kart['desc'])) {
            $output .= '<p>' . esc_html($kart['desc']) . '</p>';
        }
        $output .= '</div></div>';
        
        // Buton (sadece gösterilmesi isteniyorsa)
        if ($atts['show_button'] !== 'no') {
            $output .= '<div class="esistenze-quick-menu-buton">' . esc_html($button_text) . '</div>';
        }
        
        $output .= $link_end;
        
        return $output;
    }
    
    private function render_single_banner($kart, $group_id, $index, $button_text, $atts) {
        $has_url = !empty($kart['url']);
        $onclick = $has_url ? 'onclick="trackCardClick(' . $group_id . ', ' . $index . ')"' : '';
        
        // Erişilebilirlik
        $aria_label = 'aria-label="' . esc_attr($kart['title'] ?? 'Banner') . ($has_url ? ' - Tıklayarak detayları görün' : '') . '"';
        
        $url_start = $has_url ? 
            '<a href="' . esc_url($kart['url']) . '" class="esistenze-quick-menu-banner" target="_blank" ' . $onclick . ' ' . $aria_label . '>' : 
            '<div class="esistenze-quick-menu-banner" ' . $aria_label . '>';
        $url_end = $has_url ? '</a>' : '</div>';
        
        $output = $url_start;
        
        // Banner görseli
        $img_attrs = $this->get_image_attributes($kart, $index);
        $alt_text = !empty($kart['title']) ? $kart['title'] : 'Banner görseli';
        $output .= '<div class="banner-img">';
        $output .= '<img src="' . esc_url($kart['img']) . '" alt="' . esc_attr($alt_text) . '"' . $img_attrs . '>';
        $output .= '</div>';
        
        // Banner metni
        $output .= '<div class="banner-text">';
        $output .= '<h4>' . esc_html($kart['title'] ?? 'Başlıksız') . '</h4>';
        if (!empty($kart['desc'])) {
            $output .= '<p>' . esc_html($kart['desc']) . '</p>';
        }
        $output .= '</div>';
        
        // Banner butonu (sadece gösterilmesi isteniyorsa)
        if ($atts['show_button'] !== 'no') {
            $output .= '<div class="banner-button"><span>' . esc_html($button_text) . '</span></div>';
        }
        
        $output .= $url_end;
        
        return $output;
    }
    
    private function get_image_attributes($kart, $index) {
        $attrs = '';
        
        // Lazy loading
        if (!empty($this->settings['enable_lazy_loading']) && $index > 2) {
            $attrs .= ' loading="lazy"';
        } else {
            $attrs .= ' loading="eager"'; // İlk birkaç görsel hemen yüklensin
        }
        
        // Erişilebilirlik ve performans
        $attrs .= ' decoding="async"';
        
        // WebP desteği kontrolü (gelecek için)
        if (function_exists('wp_image_add_srcset_and_sizes')) {
            // WordPress otomatik srcset ekleyecek
        }
        
        return $attrs;
    }
    
    public function add_lazy_loading($attr, $attachment, $size) {
        if (!empty($this->settings['enable_lazy_loading'])) {
            $attr['loading'] = 'lazy';
        }
        return $attr;
    }
    
    private function track_group_view($group_id) {
        if (empty($this->settings['enable_analytics'])) {
            return;
        }
        
        // Rate limiting - aynı session'da aynı grup için sadece 1 kez
        $session_key = 'qmc_viewed_' . $group_id;
        if (isset($_SESSION[$session_key])) {
            return;
        }
        
        // Session başlatılmamışsa başlat
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[$session_key] = true;
        
        // Analytics verilerini güncelle
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        $analytics['total_views'] = ($analytics['total_views'] ?? 0) + 1;
        $analytics['group_views'][$group_id] = ($analytics['group_views'][$group_id] ?? 0) + 1;
        $analytics['last_view'] = current_time('mysql');
        
        update_option('esistenze_quick_menu_analytics', $analytics);
    }
    
    public function add_tracking_script() {
        if (empty($this->settings['enable_analytics'])) {
            return;
        }
        
        ?>
        <script>
        // Quick Menu Cards Analytics
        (function() {
            'use strict';
            
            var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var tracked = new Set(); // Aynı kartın birden fazla tracking'ini önle
            
            // Tıklama tracking fonksiyonu
            window.trackCardClick = function(groupId, cardIndex) {
                var key = groupId + '_' + cardIndex;
                if (tracked.has(key)) return;
                tracked.add(key);
                
                // Analytics isteği gönder (async, non-blocking)
                if (typeof fetch !== 'undefined') {
                    var formData = new FormData();
                    formData.append('action', 'esistenze_track_card_click');
                    formData.append('group_id', groupId);
                    formData.append('card_index', cardIndex);
                    
                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData,
                        keepalive: true
                    }).catch(function() {
                        // Hata durumunda sessizce devam et
                    });
                } else if (typeof jQuery !== 'undefined') {
                    jQuery.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'esistenze_track_card_click',
                            group_id: groupId,
                            card_index: cardIndex
                        },
                        timeout: 1000
                    });
                }
            };
            
            // Intersection Observer ile görüntülenme takibi
            if (typeof IntersectionObserver !== 'undefined') {
                var viewObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                            var wrapper = entry.target;
                            var groupId = wrapper.getAttribute('data-group-id');
                            
                            if (groupId && !wrapper.classList.contains('qmc-viewed')) {
                                wrapper.classList.add('qmc-viewed');
                                viewObserver.unobserve(wrapper);
                                
                                // View tracking
                                if (typeof fetch !== 'undefined') {
                                    var formData = new FormData();
                                    formData.append('action', 'esistenze_track_card_view');
                                    formData.append('group_id', groupId);
                                    
                                    fetch(ajaxUrl, {
                                        method: 'POST',
                                        body: formData,
                                        keepalive: true
                                    }).catch(function() {
                                        // Hata durumunda sessizce devam et
                                    });
                                }
                            }
                        }
                    });
                }, { 
                    threshold: 0.5,
                    rootMargin: '50px'
                });
                
                // Tüm kart wrapper'larını gözlemle
                document.querySelectorAll('.esistenze-quick-menu-wrapper, .esistenze-quick-menu-banner-wrapper').forEach(function(wrapper) {
                    viewObserver.observe(wrapper);
                });
            }
            
            // Performance monitoring (sadece development'da)
            <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
            console.log('Quick Menu Cards Analytics loaded');
            <?php endif; ?>
        })();
        </script>
        <?php
    }
    
    public function add_schema_markup() {
        if (empty($this->settings['enable_schema_markup'])) {
            return;
        }
        
        // Sadece kartlar olan sayfalarda schema ekle
        if (!$this->page_has_cards()) {
            return;
        }
        
        $schema = $this->generate_schema_markup();
        if (!empty($schema)) {
            echo $schema;
        }
    }
    
    private function page_has_cards() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Shortcode kontrolü
        $shortcodes = array('quick_menu_cards', 'quick_menu_banner', 'hizli_menu', 'hizli_menu_banner');
        
        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                return true;
            }
        }
        
        // Widget kontrolü (gelecek için)
        if (is_active_widget(false, false, 'esistenze_quick_menu_widget')) {
            return true;
        }
        
        return false;
    }
    
    private function generate_schema_markup() {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        if (empty($kartlar)) {
            return '';
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => get_bloginfo('name') . ' - Quick Menu',
            'description' => 'Site içi hızlı navigasyon menüsü',
            'itemListElement' => array()
        );
        
        $position = 1;
        foreach ($kartlar as $group_id => $group) {
            if (!is_array($group)) continue;
            
            foreach ($group as $kart) {
                if (empty($kart['title']) || (isset($kart['enabled']) && $kart['enabled'] === false)) {
                    continue;
                }
                
                $item = array(
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $kart['title']
                );
                
                if (!empty($kart['desc'])) {
                    $item['description'] = $kart['desc'];
                }
                
                if (!empty($kart['url'])) {
                    $item['url'] = $kart['url'];
                }
                
                if (!empty($kart['img'])) {
                    $item['image'] = $kart['img'];
                }
                
                $schema['itemListElement'][] = $item;
            }
        }
        
        if (empty($schema['itemListElement'])) {
            return '';
        }
        
        return '<script type="application/ld+json">' . 
               wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . 
               '</script>' . "\n";
    }
    
    private function generate_dynamic_css() {
        $css = '';
        
        // Responsive ayarlar
        if (!empty($this->settings['mobile_columns'])) {
            $mobile_cols = max(1, min(3, intval($this->settings['mobile_columns'])));
            $css .= '@media (max-width: 768px) {';
            $css .= '.esistenze-quick-menu-wrapper { grid-template-columns: repeat(' . $mobile_cols . ', 1fr) !important; }';
            $css .= '}';
        }
        
        // Performance optimizasyonları
        if (!empty($this->settings['enable_gpu_acceleration'])) {
            $css .= '.esistenze-quick-menu-kart { transform: translateZ(0); will-change: transform; }';
            $css .= '.esistenze-quick-menu-banner { transform: translateZ(0); will-change: transform; }';
        }
        
        // Dark mode support (gelecek için)
        if (!empty($this->settings['enable_dark_mode'])) {
            $css .= '@media (prefers-color-scheme: dark) {';
            $css .= '.esistenze-quick-menu-kart { background: linear-gradient(to bottom, #2a2a2a, #1a1a1a); color: #fff; }';
            $css .= '.esistenze-quick-menu-banner { background: linear-gradient(to right, #2a2a2a, #1a1a1a); color: #fff; }';
            $css .= '}';
        }
        
        return $css;
    }
    
    private function get_version() {
        return defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0';
    }
    
    // Cache helper methods
    public function clear_cache() {
        // Transient cache temizle
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_qmc_%' OR option_name LIKE '_transient_timeout_qmc_%'");
        
        // Object cache temizle
        wp_cache_delete('esistenze_quick_menu_cards', 'esistenze');
        wp_cache_delete('esistenze_quick_menu_settings', 'esistenze');
        
        do_action('esistenze_quick_menu_cache_cleared');
    }
    
    public function get_cache_stats() {
        global $wpdb;
        $transients = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_qmc_%'");
        
        return array(
            'transient_count' => intval($transients),
            'cache_enabled' => $this->cache_enabled,
            'cache_duration' => $this->settings['cache_duration'] ?? 0
        );
    }
}

?>