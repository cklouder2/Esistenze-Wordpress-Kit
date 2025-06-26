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
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_shortcode('display_categories', array($this, 'display_categories_shortcode'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        register_setting('esistenze_category_styler', 'esistenze_category_styler_settings');
        register_setting('esistenze_category_styler', 'esistenze_custom_category_css');
    }
    
    public function enqueue_styles() {
        $settings = get_option('esistenze_category_styler_settings', array());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        wp_enqueue_style(
            'esistenze-category-styler',
            plugin_dir_url(__FILE__) . 'assets/style.css',
            array(),
            ESISTENZE_WP_KIT_VERSION
        );
        
        // Dynamic CSS
        $dynamic_css = $this->generate_dynamic_css($settings);
        if (!empty($dynamic_css)) {
            wp_add_inline_style('esistenze-category-styler', $dynamic_css);
        }
        
        // Custom CSS
        $custom_css = get_option('esistenze_custom_category_css', '');
        if (!empty($custom_css)) {
            wp_add_inline_style('esistenze-category-styler', $custom_css);
        }
    }
    
    private function generate_dynamic_css($settings) {
        $css = '';
        
        // Default values
        $defaults = array(
            'grid_columns' => 'auto',
            'card_min_width' => 250,
            'grid_gap' => 20,
            'card_bg_color' => '#ffffff',
            'card_bg_gradient' => '#f8f8f8',
            'card_border_width' => 1,
            'card_border_color' => '#e0e0e0',
            'card_border_radius' => 15,
            'title_font_size' => 20,
            'title_color' => '#2c3e50',
            'desc_font_size' => 14,
            'desc_color' => '#7f8c8d'
        );
        
        $settings = array_merge($defaults, $settings);
        
        // Grid settings  
        if ($settings['grid_columns'] === 'auto') {
            $css .= '.esistenze-category-styler-grid { grid-template-columns: repeat(auto-fit, minmax(' . intval($settings['card_min_width']) . 'px, 1fr)); }';
        } else {
            $css .= '.esistenze-category-styler-grid { grid-template-columns: repeat(' . intval($settings['grid_columns']) . ', 1fr); }';
        }
        
        $css .= '.esistenze-category-styler-grid { gap: ' . intval($settings['grid_gap']) . 'px; }';
        
        // Card styling
        $css .= '.esistenze-category-styler-item {';
        $css .= 'background: linear-gradient(to bottom, ' . sanitize_hex_color($settings['card_bg_color']) . ', ' . sanitize_hex_color($settings['card_bg_gradient']) . ');';
        $css .= 'border: ' . intval($settings['card_border_width']) . 'px solid ' . sanitize_hex_color($settings['card_border_color']) . ';';
        $css .= 'border-radius: ' . intval($settings['card_border_radius']) . 'px;';
        $css .= '}';
        
        // Typography
        $css .= '.esistenze-category-styler-title {';
        $css .= 'font-size: ' . intval($settings['title_font_size']) . 'px;';
        $css .= 'color: ' . sanitize_hex_color($settings['title_color']) . ';';
        $css .= '}';
        
        $css .= '.esistenze-category-styler-description {';
        $css .= 'font-size: ' . intval($settings['desc_font_size']) . 'px;';
        $css .= 'color: ' . sanitize_hex_color($settings['desc_color']) . ';';
        $css .= '}';
        
        // Animations
        if (!empty($settings['enable_animations'])) {
            $css .= '.esistenze-category-styler-item { transition: all 0.3s ease; }';
            $css .= '.esistenze-category-styler-item:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }';
        }
        
        return $css;
    }
    
    public function display_categories_shortcode($atts) {
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
        
        $categories = get_terms($args);
        $settings = get_option('esistenze_category_styler_settings', array());
        
        if (empty($categories) || is_wp_error($categories)) {
            return '<p>Hiç kategori bulunamadı.</p>';
        }

        ob_start();
        ?>
        <div class="esistenze-category-styler-grid">
            <?php foreach ($categories as $category) : ?>
                <div class="esistenze-category-styler-item">
                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                        <?php
                        $image_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                        if ($image_id) {
                            $image_url = wp_get_attachment_image_url($image_id, 'medium');
                            echo '<div class="esistenze-category-styler-image" style="background-image: url(\'' . esc_url($image_url) . '\');"></div>';
                        } else {
                            echo '<div class="esistenze-category-styler-image esistenze-no-image"></div>';
                        }
                        ?>
                        <h3 class="esistenze-category-styler-title"><?php echo esc_html($category->name); ?></h3>
                        <?php if (!empty($category->description)): ?>
                            <p class="esistenze-category-styler-description"><?php echo esc_html($category->description); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($settings['show_product_count']) && $category->count > 0): ?>
                            <span class="esistenze-category-product-count"><?php echo sprintf(_n('%d ürün', '%d ürün', $category->count), $category->count); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function admin_page() {
        if (!current_user_can(esistenze_qmc_capability())) {
            wp_die(__('Bu sayfaya erişmenize izin verilmiyor.', 'esistenze-wp-kit'));
        }
        
        $settings = get_option('esistenze_category_styler_settings', array());
        $custom_css = get_option('esistenze_custom_category_css', '');
        
        // Default values
        $defaults = array(
            'enabled' => false,
            'grid_columns' => 'auto',
            'card_min_width' => 250,
            'grid_gap' => 20,
            'show_product_count' => false,
            'enable_animations' => true,
            'card_bg_color' => '#ffffff',
            'card_bg_gradient' => '#f8f8f8',
            'card_border_width' => 1,
            'card_border_color' => '#e0e0e0',
            'card_border_radius' => 15,
            'title_font_size' => 20,
            'title_color' => '#2c3e50',
            'desc_font_size' => 14,
            'desc_color' => '#7f8c8d'
        );
        
        $settings = array_merge($defaults, $settings);
        ?>
        <div class="wrap">
            <h1><?php _e('Category Styler Ayarları', 'esistenze-wp-kit'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('esistenze_category_styler'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php _e('Eklentiyi Etkinleştir', 'esistenze-wp-kit'); ?></th>
                        <td>
                            <input type="checkbox" name="esistenze_category_styler_settings[enabled]" value="1" <?php checked($settings['enabled']); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="grid_columns"><?php _e('Grid Sütunları', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <select id="grid_columns" name="esistenze_category_styler_settings[grid_columns]">
                                <option value="auto" <?php selected($settings['grid_columns'], 'auto'); ?>><?php _e('Otomatik', 'esistenze-wp-kit'); ?></option>
                                <option value="2" <?php selected($settings['grid_columns'], '2'); ?>>2</option>
                                <option value="3" <?php selected($settings['grid_columns'], '3'); ?>>3</option>
                                <option value="4" <?php selected($settings['grid_columns'], '4'); ?>>4</option>
                                <option value="5" <?php selected($settings['grid_columns'], '5'); ?>>5</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="card_min_width"><?php _e('Kart Min. Genişliği', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="number" class="small-text" id="card_min_width" name="esistenze_category_styler_settings[card_min_width]" value="<?php echo esc_attr($settings['card_min_width']); ?>" /> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="grid_gap"><?php _e('Grid Boşluğu', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="number" class="small-text" id="grid_gap" name="esistenze_category_styler_settings[grid_gap]" value="<?php echo esc_attr($settings['grid_gap']); ?>" /> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Ürün Sayısını Göster', 'esistenze-wp-kit'); ?></th>
                        <td>
                            <input type="checkbox" name="esistenze_category_styler_settings[show_product_count]" value="1" <?php checked($settings['show_product_count']); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Animasyonları Etkinleştir', 'esistenze-wp-kit'); ?></th>
                        <td>
                            <input type="checkbox" name="esistenze_category_styler_settings[enable_animations]" value="1" <?php checked($settings['enable_animations']); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="card_bg_color"><?php _e('Kart Arka Plan Rengi', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="color" id="card_bg_color" name="esistenze_category_styler_settings[card_bg_color]" value="<?php echo esc_attr($settings['card_bg_color']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="card_bg_gradient"><?php _e('Kart Gradient Rengi', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="color" id="card_bg_gradient" name="esistenze_category_styler_settings[card_bg_gradient]" value="<?php echo esc_attr($settings['card_bg_gradient']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="card_border_color"><?php _e('Kart Çerçeve Rengi', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="color" id="card_border_color" name="esistenze_category_styler_settings[card_border_color]" value="<?php echo esc_attr($settings['card_border_color']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="title_color"><?php _e('Başlık Rengi', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="color" id="title_color" name="esistenze_category_styler_settings[title_color]" value="<?php echo esc_attr($settings['title_color']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="desc_color"><?php _e('Açıklama Rengi', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="color" id="desc_color" name="esistenze_category_styler_settings[desc_color]" value="<?php echo esc_attr($settings['desc_color']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="custom_css"><?php _e('Özel CSS', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <textarea id="custom_css" name="esistenze_custom_category_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
                            <p class="description"><?php _e('Buraya özel CSS kurallarınızı yazabilirsiniz.', 'esistenze-wp-kit'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <h2><?php _e('Kullanım', 'esistenze-wp-kit'); ?></h2>
            <p><?php _e('Kategorileri göstermek için aşağıdaki kısa kodu kullanın:', 'esistenze-wp-kit'); ?></p>
            <code>[display_categories]</code>
            
            <h3><?php _e('Kısa Kod Parametreleri:', 'esistenze-wp-kit'); ?></h3>
            <ul>
                <li><strong>limit:</strong> <?php _e('Gösterilecek kategori sayısı', 'esistenze-wp-kit'); ?></li>
                <li><strong>orderby:</strong> <?php _e('Sıralama kriteri (name, count, id)', 'esistenze-wp-kit'); ?></li>
                <li><strong>order:</strong> <?php _e('Sıralama yönü (ASC, DESC)', 'esistenze-wp-kit'); ?></li>
                <li><strong>parent:</strong> <?php _e('Ana kategori ID\'si', 'esistenze-wp-kit'); ?></li>
                <li><strong>hide_empty:</strong> <?php _e('Boş kategorileri gizle (true/false)', 'esistenze-wp-kit'); ?></li>
            </ul>
            
            <p><?php _e('Örnek:', 'esistenze-wp-kit'); ?> <code>[display_categories limit="6" orderby="count" order="DESC"]</code></p>
        </div>
        <?php
    }
}

// Initialize the class
EsistenzeCategoryStyler::getInstance();