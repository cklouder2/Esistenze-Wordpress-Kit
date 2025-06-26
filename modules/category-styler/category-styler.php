<?php
/*
 * Category Styler Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Category Styler Module
 * Part of Esistenze WordPress Kit
 */
class EsistenzeCategoryStyler extends EsistenzeBaseModule {
    
    /**
     * Get module name
     * @return string
     */
    protected function getModuleName(): string {
        return 'category-styler';
    }
    
    /**
     * Get settings option name
     * @return string
     */
    protected function getSettingsOptionName(): string {
        return 'esistenze_category_styler_settings';
    }
    
    /**
     * Get default settings
     * @return array
     */
    protected function getDefaultSettings(): array {
        return [
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
            'image_size' => 'medium',
            'enable_caching' => true,
            'cache_duration' => 43200,
            'debug_mode' => false,
            'minify_css' => false,
            'inline_critical_css' => false
        ];
    }
    
    /**
     * Initialize module
     * @return void
     */
    public function init(): void {
        // Register shortcode
        add_shortcode('esistenze_display_categories', [$this, 'displayStyledCategories']);
        
        // Frontend hooks
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueueStyles'], 999);
        
        // Admin hooks
        $this->addAction('admin_init', [$this, 'registerSettings']);
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // AJAX hooks
        $this->addAction('wp_ajax_esistenze_category_preview', [$this, 'ajaxCategoryPreview']);
        $this->addAction('wp_ajax_esistenza_reset_category_stats', [$this, 'ajaxResetStats']);
    }
    
    /**
     * Register admin menus
     * @return void
     */
    public function registerAdminMenus(): void {
        $this->registerAdminSubmenu(
            'Category Styler',
            'Category Styler',
            'esistenze-category-styler',
            [$this, 'adminPage']
        );
    }
    
    /**
     * Register plugin settings
     * @return void
     */
    public function registerSettings(): void {
        register_setting('esistenza_category_styler', $this->settingsOptionName);
        register_setting('esistenza_category_styler', 'esistenze_custom_category_css');
    }
    
    /**
     * Display styled categories via shortcode
     * @param array $atts
     * @return string
     */
    public function displayStyledCategories(array $atts): string {
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
        
        // Get settings with caching
        $settings = $this->getSettings($this->settingsOptionName, $this->getDefaultSettings());
        
        // Check cache first
        $cache_key = $this->generateCacheKey($args);
        
        if (!empty($settings['enable_caching'])) {
            $categories = $this->remember($cache_key, function() use ($args) {
                return get_terms($args);
            }, $settings['cache_duration'] ?? 43200);
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
    
    /**
     * Enqueue frontend styles and dynamic CSS
     * @return void
     */
    public function enqueueStyles(): void {
        $settings = $this->getSettings($this->settingsOptionName, $this->getDefaultSettings());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Generate dynamic CSS with caching
        $css_cache_key = 'dynamic_css_' . md5(serialize($settings));
        $dynamic_css = $this->remember($css_cache_key, function() use ($settings) {
            return $this->generateDynamicCss($settings);
        }, 3600); // Cache for 1 hour
        
        // Enqueue main stylesheet
        wp_enqueue_style(
            'esistenza-category-styler',
            $this->getAssetUrl('style.css'),
            [],
            ESISTENZE_WP_KIT_VERSION
        );
        
        // Add dynamic CSS
        if (!empty($dynamic_css)) {
            wp_add_inline_style('esistenza-category-styler', $dynamic_css);
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
    
    private function generateDynamicCss($settings) {
        try {
            $css = '';
            
            // Güvenli değerler için fallback'ler ekle
            $grid_columns = isset($settings['grid_columns']) ? $settings['grid_columns'] : 'auto';
            $card_min_width = isset($settings['card_min_width']) ? intval($settings['card_min_width']) : 250;
            $grid_gap = isset($settings['grid_gap']) ? intval($settings['grid_gap']) : 20;
            
            // Grid settings
            if ($grid_columns === 'auto') {
                $css .= '.esistenza-category-styler-grid { grid-template-columns: repeat(auto-fit, minmax(' . $card_min_width . 'px, 1fr)); }';
            } else {
                $css .= '.esistanza-category-styler-grid { grid-template-columns: repeat(' . intval($grid_columns) . ', 1fr); }';
            }
            
            $css .= '.esistanza-category-styler-grid { gap: ' . $grid_gap . 'px; }';
            
            // Temel card styling (güvenli fallback değerlerle)
            $card_bg_color = isset($settings['card_bg_color']) ? esc_attr($settings['card_bg_color']) : '#ffffff';
            $card_bg_gradient = isset($settings['card_bg_gradient']) ? esc_attr($settings['card_bg_gradient']) : '#f8f8f8';
            $card_border_width = isset($settings['card_border_width']) ? intval($settings['card_border_width']) : 1;
            $card_border_color = isset($settings['card_border_color']) ? esc_attr($settings['card_border_color']) : '#e0e0e0';
            $card_border_radius = isset($settings['card_border_radius']) ? intval($settings['card_border_radius']) : 15;
            
            $css .= '.esistenza-category-styler-item {';
            $css .= 'background: linear-gradient(to bottom, ' . $card_bg_color . ', ' . $card_bg_gradient . ');';
            $css .= 'border: ' . $card_border_width . 'px solid ' . $card_border_color . ';';
            $css .= 'border-radius: ' . $card_border_radius . 'px;';
            $css .= 'padding: 15px;';
            $css .= 'text-align: center;';
            $css .= '}';
            
            // Typography (güvenli fallback değerlerle)
            $title_font_size = isset($settings['title_font_size']) ? intval($settings['title_font_size']) : 20;
            $title_color = isset($settings['title_color']) ? esc_attr($settings['title_color']) : '#2c3e50';
            
            $css .= '.esistenza-category-styler-title {';
            $css .= 'font-size: ' . $title_font_size . 'px;';
            $css .= 'color: ' . $title_color . ';';
            $css .= 'margin: 10px 0 5px;';
            $css .= '}';
            
            // Description styling
            $desc_font_size = isset($settings['desc_font_size']) ? intval($settings['desc_font_size']) : 14;
            $desc_color = isset($settings['desc_color']) ? esc_attr($settings['desc_color']) : '#7f8c8d';
            
            $css .= '.esistenza-category-styler-description {';
            $css .= 'font-size: ' . $desc_font_size . 'px;';
            $css .= 'color: ' . $desc_color . ';';
            $css .= 'margin: 0;';
            $css .= '}';
            
            // Basit hover effect
            if (!empty($settings['enable_animations'])) {
                $css .= '.esistenza-category-styler-item { transition: all 0.3s ease; }';
                $css .= '.esistenza-category-styler-item:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }';
            }
            
            return $css;
            
        } catch (Exception $e) {
            // Hata durumunda boş CSS döndür
            return '';
        }
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
    
    /**
     * AJAX: Category preview
     * @return void
     */
    public function ajaxCategoryPreview(): void {
        check_ajax_referer('esistanza_category_preview');
        
        $settings = $_POST['settings'] ?? array();
        
        // Generate preview HTML with settings
        $preview_html = $this->generate_preview_html($settings);
        
        wp_send_json_success($preview_html);
    }
    
    /**
     * AJAX: Reset category stats
     * @return void
     */
    public function ajaxResetStats(): void {
        check_ajax_referer('esistenza_reset_stats');
        if (!current_user_can(esistenze_qmc_capability())) {
            wp_send_json_error(__('Insufficient permissions', 'esistenze-wp-kit'));
        }
        delete_option('esistanza_category_styler_usage');
        wp_cache_flush();
        wp_send_json_success(__('Statistics reset successfully', 'esistenze-wp-kit'));
    }
    
    private function generate_preview_html($settings) {
        // Generate a sample category preview based on settings
        $sample_categories = array(
            array('name' => __('Örnek Kategori 1', 'esistenze-wp-kit'), 'description' => __('Bu bir örnek açıklamadır.', 'esistenze-wp-kit')),
            array('name' => __('Örnek Kategori 2', 'esistenze-wp-kit'), 'description' => __('İkinci örnek açıklama.', 'esistenze-wp-kit')),
            array('name' => __('Örnek Kategori 3', 'esistenze-wp-kit'), 'description' => __('Üçüncü örnek açıklama.', 'esistenze-wp-kit'))
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
    
    /**
     * Render admin page
     * @return void
     */
    public function adminPage(): void {
        if (!$this->canAccessAdmin()) {
            $this->denyAccess();
        }
        
        $settings = $this->getSettings($this->settingsOptionName, $this->getDefaultSettings());
        $custom_css = get_option('esistenze_custom_category_css', '');

        $this->renderAdminHeader(__('Category Styler Ayarları', 'esistenze-wp-kit'));
        
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('esistenza_category_styler'); ?>
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
                            <input type="number" class="small-text" id="grid_columns" name="esistenze_category_styler_settings[grid_columns]" value="<?php echo esc_attr($settings['grid_columns']); ?>" />
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
                        <th scope="row"><label for="custom_css"><?php _e('Özel CSS', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <textarea id="custom_css" name="esistenze_custom_category_css" rows="5" class="large-text"><?php echo esc_textarea($custom_css); ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        <?php
        $this->renderAdminFooter();
    }
}

// Initialize the module  
if (class_exists('EsistenzeBaseModule')) {
    EsistenzeCategoryStyler::getInstance();
}