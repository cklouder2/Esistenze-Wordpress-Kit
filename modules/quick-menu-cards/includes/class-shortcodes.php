<?php
/*
 * Quick Menu Cards - Frontend Shortcode Handlers
 * includes/class-shortcodes.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsShortcodes {
    
    private $frontend;
    private $cache_enabled;
    private $settings;
    
    /**
     * Constructor
     * @param EsistenzeQuickMenuCardsFrontend $frontend
     */
    public function __construct(EsistenzeQuickMenuCardsFrontend $frontend) {
        $this->frontend = $frontend;
        $this->settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        $this->cache_enabled = !empty($this->settings['cache_duration']);
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Modern shortcodes
        add_shortcode('quick_menu_cards', array($this, 'render_cards_shortcode'));
        add_shortcode('quick_menu_banner', array($this, 'render_banner_shortcode'));
        
        // Legacy shortcodes (geriye uyumluluk)
        add_shortcode('hizli_menu', array($this, 'render_cards_shortcode'));
        add_shortcode('hizli_menu_banner', array($this, 'render_banner_shortcode'));
        
        // Preview shortcode (admin önizleme için)
        add_action('init', array($this, 'handle_preview_mode'));
        
        // Shortcode ile ilgili AJAX işlemler
        add_action('wp_ajax_get_shortcode_preview', array($this, 'ajax_get_shortcode_preview'));
        add_action('wp_ajax_nopriv_get_shortcode_preview', array($this, 'ajax_get_shortcode_preview'));
    }
    
    /**
     * Render cards shortcode
     * @param array $atts
     * @param string|null $content
     * @return string
     */
    public function render_cards_shortcode(array $atts, $content = null): string {
        $atts = shortcode_atts(array(
            'id' => 0,
            'limit' => 0,
            'columns' => 0, // 0 = varsayılan
            'order' => 'asc',
            'show_button' => 'yes',
            'button_text' => '',
            'class' => '',
            'style' => '', // grid, list, masonry
            'animation' => '', // fade, slide, bounce
            'responsive' => 'yes'
        ), $atts, 'quick_menu_cards');
        
        return $this->frontend->render_cards_grid($atts);
    }
    
    /**
     * Render banner shortcode
     * @param array $atts
     * @param string|null $content
     * @return string
     */
    public function render_banner_shortcode(array $atts, $content = null): string {
        $atts = shortcode_atts(array(
            'id' => 0,
            'limit' => 0,
            'columns' => 2,
            'order' => 'asc',
            'show_button' => 'yes',
            'button_text' => '',
            'class' => '',
            'height' => '', // auto, small, medium, large
            'animation' => '',
            'responsive' => 'yes'
        ), $atts, 'quick_menu_banner');
        
        return $this->frontend->render_banner_layout($atts);
    }
    
    /**
     * Advanced cards shortcode with more options
     * @param array $atts
     * @param string|null $content
     * @return string
     */
    public function render_advanced_cards(array $atts, $content = null): string {
        $atts = shortcode_atts(array(
            'id' => 0,
            'limit' => 0,
            'columns' => 4,
            'order' => 'asc',
            'filter' => '', // featured, category, tag
            'exclude' => '', // Card IDs to exclude
            'include' => '', // Only include these card IDs
            'show_button' => 'yes',
            'button_text' => '',
            'show_description' => 'yes',
            'truncate_description' => 0,
            'image_size' => 'medium',
            'hover_effect' => 'scale',
            'loading' => 'lazy',
            'schema' => 'yes',
            'class' => '',
            'css_animation' => '',
            'animation_delay' => 0
        ), $atts, 'quick_menu_advanced');
        
        // Advanced rendering logic burada olacak
        return $this->render_advanced_layout($atts);
    }
    
    /**
     * Handle preview mode
     * @return void
     */
    public function handle_preview_mode(): void {
        if (!isset($_GET['quick_menu_preview']) || !is_admin() || !current_user_can(esistenze_qmc_capability())) {
            return;
        }
        
        $group_id = intval($_GET['quick_menu_preview']);
        $type = sanitize_text_field($_GET['type'] ?? 'grid');
        
        // Preview sayfası template'i
        add_action('template_redirect', function() use ($group_id, $type) {
            $this->render_preview_page($group_id, $type);
            exit;
        });
    }
    
    /**
     * Render preview page
     */
    private function render_preview_page($group_id, $type) {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $group = $kartlar[$group_id] ?? array();
        
        if (empty($group)) {
            wp_die('Grup bulunamadı.');
        }
        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Quick Menu Preview - Grup #<?php echo $group_id; ?></title>
            <?php wp_head(); ?>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background: #f0f0f0;
                }
                .preview-container {
                    max-width: 1200px;
                    margin: 0 auto;
                    background: white;
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .preview-header {
                    text-align: center;
                    margin-bottom: 40px;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #eee;
                }
                .preview-types {
                    display: flex;
                    justify-content: center;
                    gap: 10px;
                    margin: 20px 0;
                }
                .preview-type {
                    padding: 10px 20px;
                    border: 1px solid #ddd;
                    background: white;
                    border-radius: 4px;
                    text-decoration: none;
                    color: #333;
                }
                .preview-type.active {
                    background: #0073aa;
                    color: white;
                    border-color: #0073aa;
                }
                .admin-bar-spacer {
                    height: 32px;
                }
            </style>
        </head>
        <body <?php body_class(); ?>>
            <?php if (is_admin_bar_showing()): ?>
                <div class="admin-bar-spacer"></div>
            <?php endif; ?>
            
            <div class="preview-container">
                <div class="preview-header">
                    <h1>Quick Menu Cards - Grup #<?php echo $group_id; ?> Önizlemesi</h1>
                    <p><?php echo count($group); ?> kart bulunuyor</p>
                    
                    <div class="preview-types">
                        <a href="?quick_menu_preview=<?php echo $group_id; ?>&type=grid" 
                           class="preview-type <?php echo $type === 'grid' ? 'active' : ''; ?>">
                            Izgara Görünüm
                        </a>
                        <a href="?quick_menu_preview=<?php echo $group_id; ?>&type=banner" 
                           class="preview-type <?php echo $type === 'banner' ? 'active' : ''; ?>">
                            Banner Görünüm
                        </a>
                    </div>
                </div>
                
                <div class="preview-content">
                    <?php
                    if ($type === 'banner') {
                        echo do_shortcode('[quick_menu_banner id="' . $group_id . '"]');
                    } else {
                        echo do_shortcode('[quick_menu_cards id="' . $group_id . '"]');
                    }
                    ?>
                </div>
                
                <div style="margin-top: 40px; text-align: center; color: #666; font-size: 14px;">
                    <p>Bu bir önizlemedir. Gerçek sitede görünüm farklı olabilir.</p>
                </div>
            </div>
            
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }
    
    /**
     * AJAX: Get shortcode preview
     * @return void
     */
    public function ajax_get_shortcode_preview(): void {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'esistenze_quick_menu_nonce')) {
            wp_send_json_error('Nonce doğrulaması başarısız.');
        }
        
        $shortcode = sanitize_text_field($_POST['shortcode'] ?? '');
        
        if (empty($shortcode)) {
            wp_send_json_error('Shortcode boş.');
        }
        
        // Shortcode'u çalıştır
        $output = do_shortcode($shortcode);
        
        if (empty($output)) {
            wp_send_json_error('Shortcode çıktı üretmedi.');
        }
        
        wp_send_json_success(array(
            'html' => $output,
            'shortcode' => $shortcode
        ));
    }
    
    /**
     * Advanced layout rendering
     */
    private function render_advanced_layout($atts) {
        $group_id = intval($atts['id']);
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $group = $kartlar[$group_id] ?? array();
        
        if (empty($group)) {
            return '<p class="quick-menu-empty">Bu grupta kart bulunmuyor.</p>';
        }
        
        // Filter cards based on advanced options
        $filtered_cards = $this->filter_cards($group, $atts);
        
        // Generate unique wrapper class
        $wrapper_class = 'esistenze-quick-menu-advanced-wrapper';
        $wrapper_class .= !empty($atts['class']) ? ' ' . esc_attr($atts['class']) : '';
        
        // Build HTML
        $output = '<div class="' . $wrapper_class . '" data-group-id="' . $group_id . '">';
        
        // Add CSS animations
        if (!empty($atts['css_animation'])) {
            $output .= '<style>';
            $output .= $this->generate_animation_css($atts['css_animation'], $atts['animation_delay']);
            $output .= '</style>';
        }
        
        foreach ($filtered_cards as $index => $card) {
            $output .= $this->render_advanced_card($card, $index, $atts, $group_id);
        }
        
        $output .= '</div>';
        
        // Add schema markup if enabled
        if ($atts['schema'] === 'yes') {
            $output .= $this->generate_card_schema($filtered_cards, $group_id);
        }
        
        return $output;
    }
    
    /**
     * Filter cards based on criteria
     */
    private function filter_cards($cards, $atts) {
        $filtered = $cards;
        
        // Include/Exclude logic
        if (!empty($atts['include'])) {
            $include_ids = array_map('intval', explode(',', $atts['include']));
            $filtered = array_filter($filtered, function($card, $index) use ($include_ids) {
                return in_array($index, $include_ids);
            }, ARRAY_FILTER_USE_BOTH);
        }
        
        if (!empty($atts['exclude'])) {
            $exclude_ids = array_map('intval', explode(',', $atts['exclude']));
            $filtered = array_filter($filtered, function($card, $index) use ($exclude_ids) {
                return !in_array($index, $exclude_ids);
            }, ARRAY_FILTER_USE_BOTH);
        }
        
        // Filter by featured
        if ($atts['filter'] === 'featured') {
            $filtered = array_filter($filtered, function($card) {
                return !empty($card['featured']);
            });
        }
        
        // Sort cards
        if ($atts['order'] === 'desc') {
            $filtered = array_reverse($filtered, true);
        } elseif ($atts['order'] === 'random') {
            shuffle($filtered);
        } elseif ($atts['order'] === 'title') {
            uasort($filtered, function($a, $b) {
                return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
            });
        }
        
        // Apply limit
        if ($atts['limit'] > 0) {
            $filtered = array_slice($filtered, 0, intval($atts['limit']), true);
        }
        
        return $filtered;
    }
    
    /**
     * Render single advanced card
     */
    private function render_advanced_card($card, $index, $atts, $group_id) {
        $has_url = !empty($card['url']);
        $card_class = 'esistenze-quick-menu-advanced-card';
        
        // Add hover effect class
        if (!empty($atts['hover_effect'])) {
            $card_class .= ' hover-effect-' . esc_attr($atts['hover_effect']);
        }
        
        // Animation delay
        $animation_delay = 0;
        if (!empty($atts['css_animation']) && $atts['animation_delay'] > 0) {
            $animation_delay = $index * intval($atts['animation_delay']);
        }
        
        $onclick = $has_url ? 'onclick="trackCardClick(' . $group_id . ', ' . $index . ')"' : '';
        $aria_label = 'aria-label="' . esc_attr($card['title'] ?? 'Kart') . ($has_url ? ' - Tıklayarak detayları görün' : '') . '"';
        
        $link_start = $has_url ? 
            '<a href="' . esc_url($card['url']) . '" class="' . $card_class . '" target="' . (!empty($card['new_tab']) ? '_blank' : '_self') . '" ' . $onclick . ' ' . $aria_label . ' style="animation-delay: ' . $animation_delay . 'ms;">' : 
            '<div class="' . $card_class . '" ' . $aria_label . ' style="animation-delay: ' . $animation_delay . 'ms;">';
        $link_end = $has_url ? '</a>' : '</div>';
        
        $output = $link_start;
        
        // Image
        if (!empty($card['img']) && $atts['show_image'] !== 'no') {
            $img_size = $atts['image_size'] ?? 'medium';
            $loading = $atts['loading'] === 'lazy' ? 'loading="lazy"' : '';
            $alt_text = !empty($card['title']) ? $card['title'] : 'Kart görseli';
            
            $output .= '<div class="card-image-wrapper">';
            $output .= '<img src="' . esc_url($card['img']) . '" alt="' . esc_attr($alt_text) . '" ' . $loading . ' decoding="async">';
            $output .= '</div>';
        }
        
        // Content
        $output .= '<div class="card-content-wrapper">';
        $output .= '<h4 class="card-title">' . esc_html($card['title'] ?? 'Başlıksız') . '</h4>';
        
        if (!empty($card['desc']) && $atts['show_description'] === 'yes') {
            $description = $card['desc'];
            if ($atts['truncate_description'] > 0) {
                $description = wp_trim_words($description, intval($atts['truncate_description']));
            }
            $output .= '<p class="card-description">' . esc_html($description) . '</p>';
        }
        $output .= '</div>';
        
        // Button
        if ($atts['show_button'] === 'yes') {
            $button_text = !empty($atts['button_text']) ? $atts['button_text'] : ($this->settings['default_button_text'] ?? 'Detayları Gör');
            $output .= '<div class="card-button-wrapper">';
            $output .= '<span class="card-button">' . esc_html($button_text) . '</span>';
            $output .= '</div>';
        }
        
        $output .= $link_end;
        
        return $output;
    }
    
    /**
     * Generate animation CSS
     */
    private function generate_animation_css($animation, $delay) {
        $css = '';
        
        switch ($animation) {
            case 'fadeIn':
                $css .= '.esistenze-quick-menu-advanced-card { opacity: 0; animation: fadeIn 0.6s ease forwards; }';
                $css .= '@keyframes fadeIn { to { opacity: 1; } }';
                break;
                
            case 'slideUp':
                $css .= '.esistenze-quick-menu-advanced-card { opacity: 0; transform: translateY(30px); animation: slideUp 0.6s ease forwards; }';
                $css .= '@keyframes slideUp { to { opacity: 1; transform: translateY(0); } }';
                break;
                
            case 'slideLeft':
                $css .= '.esistenze-quick-menu-advanced-card { opacity: 0; transform: translateX(-30px); animation: slideLeft 0.6s ease forwards; }';
                $css .= '@keyframes slideLeft { to { opacity: 1; transform: translateX(0); } }';
                break;
                
            case 'scale':
                $css .= '.esistenze-quick-menu-advanced-card { opacity: 0; transform: scale(0.8); animation: scaleIn 0.6s ease forwards; }';
                $css .= '@keyframes scaleIn { to { opacity: 1; transform: scale(1); } }';
                break;
                
            case 'bounce':
                $css .= '.esistenze-quick-menu-advanced-card { opacity: 0; transform: translateY(-20px); animation: bounceIn 0.8s ease forwards; }';
                $css .= '@keyframes bounceIn { 0% { opacity: 0; transform: translateY(-20px); } 60% { opacity: 1; transform: translateY(5px); } 100% { transform: translateY(0); } }';
                break;
        }
        
        return $css;
    }
    
    /**
     * Generate schema markup for cards
     */
    private function generate_card_schema($cards, $group_id) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Quick Menu Cards - Grup ' . $group_id,
            'numberOfItems' => count($cards),
            'itemListElement' => array()
        );
        
        $position = 1;
        foreach ($cards as $card) {
            if (empty($card['title'])) continue;
            
            $item = array(
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $card['title']
            );
            
            if (!empty($card['desc'])) {
                $item['description'] = $card['desc'];
            }
            
            if (!empty($card['url'])) {
                $item['url'] = $card['url'];
            }
            
            if (!empty($card['img'])) {
                $item['image'] = $card['img'];
            }
            
            $schema['itemListElement'][] = $item;
        }
        
        return '<script type="application/ld+json">' . 
               wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . 
               '</script>';
    }
    
    /**
     * Get shortcode documentation
     * @return array
     */
    public static function get_shortcode_documentation(): array {
        return array(
            'quick_menu_cards' => array(
                'description' => 'Kartları ızgara düzeninde gösterir',
                'attributes' => array(
                    'id' => 'Grup ID (zorunlu)',
                    'limit' => 'Gösterilecek kart sayısı sınırı',
                    'columns' => 'Sütun sayısı (1-6)',
                    'order' => 'Sıralama: asc, desc, random, title',
                    'show_button' => 'Buton gösterilsin mi: yes/no',
                    'button_text' => 'Özel buton metni',
                    'class' => 'Ek CSS sınıfı',
                    'responsive' => 'Responsive tasarım: yes/no'
                ),
                'examples' => array(
                    '[quick_menu_cards id="1"]',
                    '[quick_menu_cards id="1" limit="4" columns="2"]',
                    '[quick_menu_cards id="1" order="random" button_text="Devamını Oku"]'
                )
            ),
            'quick_menu_banner' => array(
                'description' => 'Kartları banner düzeninde gösterir',
                'attributes' => array(
                    'id' => 'Grup ID (zorunlu)',
                    'limit' => 'Gösterilecek kart sayısı sınırı',
                    'columns' => 'Sütun sayısı (1-3)',
                    'order' => 'Sıralama: asc, desc, random',
                    'show_button' => 'Buton gösterilsin mi: yes/no',
                    'button_text' => 'Özel buton metni',
                    'class' => 'Ek CSS sınıfı',
                    'height' => 'Banner yüksekliği: auto, small, medium, large'
                ),
                'examples' => array(
                    '[quick_menu_banner id="1"]',
                    '[quick_menu_banner id="1" columns="1" height="large"]',
                    '[quick_menu_banner id="1" limit="2" button_text="Ürünleri Gör"]'
                )
            )
        );
    }
}
?>