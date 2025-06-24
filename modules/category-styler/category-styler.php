<?php
/*
 * Category Styler Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeCategoryStyler {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Use init hook for all initialization to avoid "load textdomain too early" error
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Register shortcode
        add_shortcode('esistenze_display_categories', array($this, 'display_styled_categories'));
        
        // Enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 999);
        
        // Admin init
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_esistenze_category_preview', array($this, 'ajax_category_preview'));
        add_action('wp_ajax_esistenze_reset_category_stats', array($this, 'ajax_reset_stats'));
    }
    
    public function register_settings() {
        register_setting('esistenza_category_styler', 'esistenze_category_styler_settings');
        register_setting('esistenza_category_styler', 'esistenze_custom_category_css');
    }
    
    public function display_styled_categories($atts) {
        $atts = shortcode_atts(array(
            'limit' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => 0,
            'hide_empty' => false
        ), $atts);
        
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => $atts['hide_empty'],
            'parent' => intval($atts['parent']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );
        
        if (!empty($atts['limit'])) {
            $args['number'] = intval($atts['limit']);
        }
        
        // Check cache first
        $settings = get_option('esistenze_category_styler_settings', array());
        $cache_key = 'esistenze_categories_' . md5(serialize($args));
        
        if (!empty($settings['enable_caching'])) {
            $categories = wp_cache_get($cache_key, 'esistenza');
            if ($categories === false) {
                $categories = get_terms($args);
                wp_cache_set($cache_key, $categories, 'esistenze', $settings['cache_duration'] ?? 43200);
            }
        } else {
            $categories = get_terms($args);
        }

        if (empty($categories) || is_wp_error($categories)) {
            return '<p>Hiç kategori bulunamadı.</p>';
        }

        ob_start();
        ?>
        <div class="esistenze-category-styler-grid" data-columns="<?php echo esc_attr($settings['grid_columns'] ?? 'auto'); ?>">
            <?php foreach ($categories as $category) : ?>
                <div class="esistenze-category-styler-item" data-category-id="<?php echo $category->term_id; ?>">
                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                        <?php
                        $image_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                        $image_size = $settings['image_size'] ?? 'medium';
                        $lazy_load = !empty($settings['lazy_load_images']);
                        
                        if ($image_id) {
                            $image_url = wp_get_attachment_image_url($image_id, $image_size);
                            if ($lazy_load) {
                                echo '<div class="esistenze-category-styler-image" data-bg="' . esc_url($image_url) . '" style="background-image: url(data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"><rect width="1" height="1" fill="#f0f0f0"/></svg>') . ');"></div>';
                            } else {
                                echo '<div class="esistenze-category-styler-image" style="background-image: url(\'' . esc_url($image_url) . '\');"></div>';
                            }
                        } else {
                            echo '<div class="esistenze-category-styler-image esistenza-no-image"></div>';
                        }
                        ?>
                        <h3 class="esistenza-category-styler-title"><?php echo esc_html($category->name); ?></h3>
                        <?php if (!empty($category->description)): ?>
                            <p class="esistence-category-styler-description"><?php echo esc_html($category->description); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($settings['show_product_count']) && $category->count > 0): ?>
                            <span class="esistenza-category-product-count"><?php echo sprintf(_n('%d ürün', '%d ürün', $category->count), $category->count); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($lazy_load): ?>
        <script>
        // Lazy loading implementation
        document.addEventListener('DOMContentLoaded', function() {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const bgUrl = img.dataset.bg;
                        if (bgUrl) {
                            img.style.backgroundImage = `url('${bgUrl}')`;
                            img.removeAttribute('data-bg');
                        }
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('[data-bg]').forEach(img => {
                imageObserver.observe(img);
            });
        });
        </script>
        <?php endif; ?>
        <?php
        
        // Track usage if analytics enabled
        if (!empty($settings['enable_analytics'])) {
            $this->track_shortcode_usage('display_categories', $atts);
        }
        
        return ob_get_clean();
    }
    
    public function enqueue_styles() {
        $settings = get_option('esistenze_category_styler_settings', $this->get_default_settings());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Generate dynamic CSS
        $dynamic_css = $this->generate_dynamic_css($settings);
        
        // Enqueue main stylesheet
        wp_enqueue_style('esistenza-category-styler', ESISTENZE_WP_KIT_URL . 'modules/category-styler/assets/style.css', array(), ESISTENZE_WP_KIT_VERSION);
        
        // Add dynamic CSS
        if (!empty($dynamic_css)) {
            if (!empty($settings['inline_critical_css'])) {
                wp_add_inline_style('esistenza-category-styler', $dynamic_css);
            } else {
                wp_add_inline_style('esistenza-category-styler', $dynamic_css);
            }
        }
        
        // Add custom CSS
        $custom_css = get_option('esistenze_custom_category_css', '');
        if (!empty($custom_css)) {
            wp_add_inline_style('esistenza-category-styler', $custom_css);
        }
        
        // Hide price hover if enabled
        if (!empty($settings['hide_price_hover'])) {
            $hide_css = '.woocommerce-products-header ~ .products .price-hover-wrap { display: none !important; }';
            wp_add_inline_style('esistenza-category-styler', $hide_css);
        }
        
        // Debug info
        if (!empty($settings['debug_mode']) && current_user_can('manage_options')) {
            wp_add_inline_script('esistenza-category-styler-debug', 'console.log("Esistenza Category Styler: CSS loaded at ' . current_time('mysql') . '");');
        }
    }
    
    private function generate_dynamic_css($settings) {
        $css = '';
        
        // Grid settings
        if ($settings['grid_columns'] === 'auto') {
            $css .= '.esistenza-category-styler-grid { grid-template-columns: repeat(auto-fit, minmax(' . intval($settings['card_min_width']) . 'px, 1fr)); }';
        } else {
            $css .= '.esistenze-category-styler-grid { grid-template-columns: repeat(' . intval($settings['grid_columns']) . ', 1fr); }';
        }
        
        $css .= '.esistanza-category-styler-grid { gap: ' . intval($settings['grid_gap']) . 'px; }';
        
        // Card styling
        $css .= '.esistenza-category-styler-item {';
        $css .= 'background: linear-gradient(to bottom, ' . $settings['card_bg_color'] . ', ' . $settings['card_bg_gradient'] . ');';
        $css .= 'border: ' . intval($settings['card_border_width']) . 'px solid ' . $settings['card_border_color'] . ';';
        $css .= 'border-radius: ' . intval($settings['card_border_radius']) . 'px;';
        $css .= 'box-shadow: ' . intval($settings['shadow_x']) . 'px ' . intval($settings['shadow_y']) . 'px ' . intval($settings['shadow_blur']) . 'px rgba(0,0,0,' . floatval($settings['shadow_opacity']) . ');';
        $css .= '}';
        
        // Typography
        $css .= '.esistanza-category-styler-title {';
        $css .= 'font-size: ' . intval($settings['title_font_size']) . 'px;';
        $css .= 'font-weight: ' . $settings['title_font_weight'] . ';';
        $css .= 'color: ' . $settings['title_color'] . ';';
        $css .= '}';
        
        $css .= '.esistanza-category-styler-description {';
        $css .= 'font-size: ' . intval($settings['desc_font_size']) . 'px;';
        $css .= 'color: ' . $settings['desc_color'] . ';';
        $css .= '}';
        
        // Hover effects
        if (!empty($settings['enable_animations'])) {
            $css .= '.esistenza-category-styler-item { transition: all 0.3s ease; }';
            
            $hover_effects = array();
            if (!empty($settings['hover_scale'])) {
                $hover_effects[] = 'scale(1.05)';
            }
            if (!empty($settings['hover_lift'])) {
                $hover_effects[] = 'translateY(-5px)';
            }
            
            if (!empty($hover_effects)) {
                $css .= '.esistanza-category-styler-item:hover { transform: ' . implode(' ', $hover_effects) . '; }';
            }
            
            $css .= '.esistanza-category-styler-item:hover {';
            $css .= 'box-shadow: ' . intval($settings['shadow_x']) . 'px ' . (intval($settings['shadow_y']) + 5) . 'px ' . (intval($settings['shadow_blur']) + 10) . 'px rgba(0,0,0,' . floatval($settings['hover_shadow_intensity']) . ');';
            $css .= '}';
        }
        
        // Sidebar styling
        $css .= '#nav_menu-3 .widget_nav_menu h4, #nav_menu-7 .widget_nav_menu h4 {';
        $css .= 'background: linear-gradient(90deg, ' . $settings['sidebar_header_bg_start'] . ', ' . $settings['sidebar_header_bg_end'] . ');';
        $css .= 'color: ' . $settings['sidebar_header_color'] . ';';
        $css .= 'font-size: ' . intval($settings['sidebar_header_font_size']) . 'px;';
        $css .= '}';
        
        $css .= '#nav_menu-3 .menu li, #nav_menu-7 .menu li {';
        $css .= 'background: ' . $settings['sidebar_item_bg'] . ';';
        $css .= '}';
        
        $css .= '#nav_menu-3 .menu li:hover, #nav_menu-7 .menu li:hover {';
        $css .= 'background: ' . $settings['sidebar_item_hover_bg'] . ';';
        $css .= '}';
        
        $css .= '#nav_menu-3 .menu li a, #nav_menu-7 .menu li a {';
        $css .= 'color: ' . $settings['sidebar_item_color'] . ';';
        $css .= '}';
        
        $css .= '#nav_menu-3 .menu li a:hover, #nav_menu-7 .menu li a:hover {';
        $css .= 'color: ' . $settings['sidebar_item_hover_color'] . ';';
        $css .= '}';
        
        $css .= '#nav_menu-3 .menu li.current-menu-item a, #nav_menu-7 .menu li.current-menu-item a {';
        $css .= 'background: ' . $settings['sidebar_active_bg'] . ';';
        $css .= 'border-left: ' . intval($settings['sidebar_active_border_width']) . 'px solid ' . $settings['sidebar_active_border'] . ';';
        $css .= '}';
        
        // Page header styling
        $css .= '#page-header-wrap {';
        $css .= 'background: linear-gradient(135deg, ' . $settings['header_bg_start'] . ' 0%, ' . $settings['header_bg_middle'] . ' 70%, ' . $settings['header_bg_end'] . ' 100%) !important;';
        $css .= 'height: ' . intval($settings['header_height']) . 'px;';
        $css .= '}';
        
        $css .= '#page-header-wrap .inner-wrap h1 {';
        $css .= 'font-size: ' . intval($settings['header_title_size']) . 'px;';
        $css .= 'color: ' . $settings['header_title_color'] . ';';
        $css .= 'background: rgba(0, 0, 0, ' . floatval($settings['header_title_bg_opacity']) . ');';
        $css .= '}';
        
        // Minify CSS if enabled
        if (!empty($settings['minify_css'])) {
            $css = $this->minify_css($css);
        }
        
        return $css;
    }
    
    private function minify_css($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove unnecessary whitespace
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
        // Remove space around specific characters
        $css = str_replace(array(' {', '{ ', ' }', '} ', '; ', ' ;', ': ', ' :', ', ', ' ,'), array('{', '{', '}', '}', ';', ';', ':', ':', ',', ','), $css);
        return trim($css);
    }
    
    private function track_shortcode_usage($shortcode, $atts) {
        $usage_data = get_option('esistanza_category_styler_usage', array());
        $today = date('Y-m-d');
        
        if (!isset($usage_data[$today])) {
            $usage_data[$today] = array();
        }
        
        if (!isset($usage_data[$today][$shortcode])) {
            $usage_data[$today][$shortcode] = 0;
        }
        
        $usage_data[$today][$shortcode]++;
        
        // Keep only last 90 days
        $cutoff_date = date('Y-m-d', strtotime('-90 days'));
        foreach ($usage_data as $date => $data) {
            if ($date < $cutoff_date) {
                unset($usage_data[$date]);
            }
        }
        
        update_option('esistenza_category_styler_usage', $usage_data);
    }
    
    // AJAX handlers
    public function ajax_category_preview() {
        check_ajax_referer('esistanza_category_preview');
        
        $settings = $_POST['settings'] ?? array();
        
        // Generate preview HTML with settings
        $preview_html = $this->generate_preview_html($settings);
        
        wp_send_json_success($preview_html);
    }
    
    public function ajax_reset_stats() {
        check_ajax_referer('esistenza_reset_stats');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        delete_option('esistanza_category_styler_usage');
        wp_cache_flush();
        
        wp_send_json_success('Statistics reset successfully');
    }
    
    private function generate_preview_html($settings) {
        // Generate a sample category preview based on settings
        $sample_categories = array(
            array('name' => 'Örnek Kategori 1', 'description' => 'Bu bir örnek açıklamadır.'),
            array('name' => 'Örnek Kategori 2', 'description' => 'İkinci örnek açıklama.'),
            array('name' => 'Örnek Kategori 3', 'description' => 'Üçüncü örnek açıklama.')
        );
        
        ob_start();
        ?>
        <div class="esistanza-category-styler-grid" style="grid-template-columns: repeat(3, 1fr); gap: <?php echo intval($settings['grid_gap'] ?? 20); ?>px;">
            <?php foreach ($sample_categories as $cat): ?>
            <div class="esistenza-category-styler-item" style="
                background: linear-gradient(to bottom, <?php echo esc_attr($settings['card_bg_color'] ?? '#ffffff'); ?>, <?php echo esc_attr($settings['card_bg_gradient'] ?? '#f8f8f8'); ?>);
                border: <?php echo intval($settings['card_border_width'] ?? 1); ?>px solid <?php echo esc_attr($settings['card_border_color'] ?? '#e0e0e0'); ?>;
                border-radius: <?php echo intval($settings['card_border_radius'] ?? 15); ?>px;
                box-shadow: <?php echo intval($settings['shadow_x'] ?? 0); ?>px <?php echo intval($settings['shadow_y'] ?? 8); ?>px <?php echo intval($settings['shadow_blur'] ?? 20); ?>px rgba(0,0,0,<?php echo floatval($settings['shadow_opacity'] ?? 0.1); ?>);
                padding: 15px;
                text-align: center;
            ">
                <div class="esistanza-category-styler-image" style="height: 100px; background: #f0f0f0; border-radius: 8px; margin-bottom: 10px;"></div>
                <h3 class="esistanza-category-styler-title" style="
                    font-size: <?php echo intval($settings['title_font_size'] ?? 20); ?>px;
                    font-weight: <?php echo esc_attr($settings['title_font_weight'] ?? '600'); ?>;
                    color: <?php echo esc_attr($settings['title_color'] ?? '#2c3e50'); ?>;
                    margin: 10px 0 5px;
                "><?php echo esc_html($cat['name']); ?></h3>
                <p class="esistanza-category-styler-description" style="
                    font-size: <?php echo intval($settings['desc_font_size'] ?? 14); ?>px;
                    color: <?php echo esc_attr($settings['desc_color'] ?? '#7f8c8d'); ?>;
                    margin: 0;
                "><?php echo esc_html($cat['description']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function admin_page() {
        // Implementation will go here
    }
    
    private function get_default_settings() {
        return array(
            'enabled' => true,
            'grid_columns' => 'auto',
            'card_min_width' => 250,
            'grid_gap' => 20,
            'hide_price_hover' => true,
            'enable_animations' => true,
            'show_product_count' => false,
            'lazy_load_images' => false,
            'card_bg_color' => '#ffffff',
            'card_bg_gradient' => '#f8f8f8',
            'card_border_width' => 1,
            'card_border_color' => '#e0e0e0',
            'card_border_radius' => 15,
            'shadow_x' => 0,
            'shadow_y' => 8,
            'shadow_blur' => 20,
            'shadow_opacity' => 0.1,
            'title_font_size' => 20,
            'title_font_weight' => '600',
            'title_color' => '#2c3e50',
            'desc_font_size' => 14,
            'desc_color' => '#7f8c8d',
            'hover_scale' => true,
            'hover_lift' => true,
            'hover_shadow_intensity' => 0.2,
            'sidebar_header_bg_start' => '#4CAF50',
            'sidebar_header_bg_end' => '#45a049',
            'sidebar_header_color' => '#ffffff',
            'sidebar_header_font_size' => 18,
            'sidebar_item_bg' => '#ffffff',
            'sidebar_item_hover_bg' => '#f9f9f9',
            'sidebar_item_color' => '#2c3e50',
            'sidebar_item_hover_color' => '#4CAF50',
            'sidebar_active_bg' => '#e6f3e6',
            'sidebar_active_border' => '#4CAF50',
            'sidebar_active_border_width' => 5,
            'header_bg_start' => '#4CAF50',
            'header_bg_middle' => '#45a049',
            'header_bg_end' => '#2E7D32',
            'header_height' => 350,
            'header_title_size' => 48,
            'header_title_color' => '#ffffff',
            'header_title_bg_opacity' => 0.6,
            'minify_css' => false,
            'inline_critical_css' => false,
            'defer_non_critical_css' => false,
            'webp_support' => false,
            'image_size' => 'medium',
            'enable_caching' => true,
            'cache_duration' => 43200,
            'debug_mode' => false,
            'disable_theme_styles' => false,
            'force_styles' => false,
            'legacy_support' => false
        );
    }
}

// Initialize the module
EsistenzeCategoryStyler::getInstance();