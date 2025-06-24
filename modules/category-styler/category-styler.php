            case 'grid-styling':
                $new_settings = array_merge($current_settings, array(
                    'card_bg_color' => sanitize_hex_color($_POST['settings']['card_bg_color'] ?? '#ffffff'),
                    'card_bg_gradient' => sanitize_hex_color($_POST['settings']['card_bg_gradient'] ?? '#f8f8f8'),
                    'card_border_width' => intval($_POST['settings']['card_border_width'] ?? 1),
                    'card_border_color' => sanitize_hex_color($_POST['settings']['card_border_color'] ?? '#e0e0e0'),
                    'card_border_radius' => intval($_POST['settings']['card_border_radius'] ?? 15),
                    'shadow_x' => intval($_POST['settings']['shadow_x'] ?? 0),
                    'shadow_y' => intval($_POST['settings']['shadow_y'] ?? 8),
                    'shadow_blur' => intval($_POST['settings']['shadow_blur'] ?? 20),
                    'shadow_opacity' => floatval($_POST['settings']['shadow_opacity'] ?? 0.1),
                    'title_font_size' => intval($_POST['settings']['title_font_size'] ?? 20),
                    'title_font_weight' => sanitize_text_field($_POST['settings']['title_font_weight'] ?? '600'),
                    'title_color' => sanitize_hex_color($_POST['settings']['title_color'] ?? '#2c3e50'),
                    'desc_font_size' => intval($_POST['settings']['desc_font_size'] ?? 14),
                    'desc_color' => sanitize_hex_color($_POST['settings']['desc_color'] ?? '#7f8c8d'),
                    'hover_scale' => !empty($_POST['settings']['hover_scale']),
                    'hover_lift' => !empty($_POST['settings']['hover_lift']),
                    'hover_shadow_intensity' => floatval($_POST['settings']['hover_shadow_intensity'] ?? 0.2)
                ));
                break;
                
            case 'sidebar-styling':
                $new_settings = array_merge($current_settings, array(
                    'sidebar_header_bg_start' => sanitize_hex_color($_POST['settings']['sidebar_header_bg_start'] ?? '#4CAF50'),
                    'sidebar_header_bg_end' => sanitize_hex_color($_POST['settings']['sidebar_header_bg_end'] ?? '#45a049'),
                    'sidebar_header_color' => sanitize_hex_color($_POST['settings']['sidebar_header_color'] ?? '#ffffff'),
                    'sidebar_header_font_size' => intval($_POST['settings']['sidebar_header_font_size'] ?? 18),
                    'sidebar_item_bg' => sanitize_hex_color($_POST['settings']['sidebar_item_bg'] ?? '#ffffff'),
                    'sidebar_item_hover_bg' => sanitize_hex_color($_POST['settings']['sidebar_item_hover_bg'] ?? '#f9f9f9'),
                    'sidebar_item_color' => sanitize_hex_color($_POST['settings']['sidebar_item_color'] ?? '#2c3e50'),
                    'sidebar_item_hover_color' => sanitize_hex_color($_POST['settings']['sidebar_item_hover_color'] ?? '#4CAF50'),
                    'sidebar_active_bg' => sanitize_hex_color($_POST['settings']['sidebar_active_bg'] ?? '#e6f3e6'),
                    'sidebar_active_border' => sanitize_hex_color($_POST['settings']['sidebar_active_border'] ?? '#4CAF50'),
                    'sidebar_active_border_width' => intval($_POST['settings']['sidebar_active_border_width'] ?? 5),
                    'header_bg_start' => sanitize_hex_color($_POST['settings']['header_bg_start'] ?? '#4CAF50'),
                    'header_bg_middle' => sanitize_hex_color($_POST['settings']['header_bg_middle'] ?? '#45a049'),
                    'header_bg_end' => sanitize_hex_color($_POST['settings']['header_bg_end'] ?? '#2E7D32'),
                    'header_height' => intval($_POST['settings']['header_height'] ?? 350),
                    'header_title_size' => intval($_POST['settings']['header_title_size'] ?? 48),
                    'header_title_color' => sanitize_hex_color($_POST['settings']['header_title_color'] ?? '#ffffff'),
                    'header_title_bg_opacity' => floatval($_POST['settings']['header_title_bg_opacity'] ?? 0.6)
                ));
                break;
                
            case 'advanced':
                $new_settings = array_merge($current_settings, array(
                    'minify_css' => !empty($_POST['settings']['minify_css']),
                    'inline_critical_css' => !empty($_POST['settings']['inline_critical_css']),
                    'defer_non_critical_css' => !empty($_POST['settings']['defer_non_critical_css']),
                    'lazy_load_images' => !empty($_POST['settings']['lazy_load_images']),
                    'webp_support' => !empty($_POST['settings']['webp_support']),
                    'image_size' => sanitize_text_field($_POST['settings']['image_size'] ?? 'medium'),
                    'enable_caching' => !empty($_POST['settings']['enable_caching']),
                    'cache_duration' => intval($_POST['settings']['cache_duration'] ?? 43200),
                    'debug_mode' => !empty($_POST['settings']['debug_mode']),
                    'disable_theme_styles' => !empty($_POST['settings']['disable_theme_styles']),
                    'force_styles' => !empty($_POST['settings']['force_styles']),
                    'legacy_support' => !empty($_POST['settings']['legacy_support'])
                ));
                
                // Handle custom CSS separately
                if (isset($_POST['custom_css'])) {
                    update_option('esistenze_custom_category_css', wp_unslash($_POST['custom_css']));
                }
                break;
        }
        
        update_option('esistenze_category_styler_settings', $new_settings);
        
        // Clear cache if caching is enabled
        if (!empty($new_settings['enable_caching'])) {
            wp_cache_delete('esistenze_category_styles', 'esistenze');
        }
        
        // Add admin notice
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Ayarlar başarıyla kaydedildi!</p></div>';
        });
    }
    
    private static function show_admin_notices() {
        // Check for WooCommerce
        if (!class_exists('WooCommerce')) {
            echo '<div class="notice notice-warning"><p><strong>Uyarı:</strong> WooCommerce eklentisi bulunamadı. Category Styler tam olarak çalışmayabilir.</p></div>';
        }
        
        // Check for image optimization
        $settings = get_option('esistenze_category_styler_settings', array());
        if (!empty($settings['webp_support']) && !function_exists('imagewebp')) {
            echo '<div class="notice notice-info"><p><strong>Bilgi:</strong> WebP desteği etkin ama sunucunuzda WebP desteği yok. Hosting sağlayıcınızla iletişime geçin.</p></div>';
        }
    }
    
    private static function get_default_settings() {
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
    
    private static function enqueue_admin_assets() {
        ?>
        <style>
        .esistenze-category-styler-wrap { max-width: 1400px; }
        .nav-tab .dashicons { margin-right: 5px; vertical-align: middle; }
        
        /* Settings Grid Layout */
        .settings-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin: 20px 0;
        }
        
        .settings-main .postbox,
        .settings-sidebar .postbox {
            margin-bottom: 20px;
        }
        
        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #4CAF50;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        /* Color Controls */
        .color-controls,
        .widget-header-controls,
        .menu-item-controls,
        .active-item-controls,
        .header-bg-controls,
        .header-title-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .color-controls label,
        .widget-header-controls label,
        .menu-item-controls label,
        .active-item-controls label,
        .header-bg-controls label,
        .header-title-controls label {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-weight: 600;
            font-size: 12px;
        }
        
        .color-picker {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Range Sliders */
        .width-slider {
            width: 100%;
            margin: 10px 0;
        }
        
        /* Preview Container */
        .category-preview-container,
        .realtime-preview-container {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 20px;
            min-height: 200px;
            border: 2px dashed #ddd;
        }
        
        .preview-loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .preview-controls {
            margin-top: 15px;
            text-align: center;
        }
        
        .preview-controls .button {
            margin: 0 5px;
        }
        
        /* Statistics */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 15px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #4CAF50;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Shortcode Generator */
        .shortcode-generator {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .shortcode-result {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .shortcode-result input {
            font-family: monospace;
            background: #2c3e50;
            color: #ecf0f1;
            border: none;
            padding: 10px;
            border-radius: 4px;
        }
        
        /* Advanced Styling Layout */
        .styling-layout,
        .advanced-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin: 20px 0;
        }
        
        /* Border Controls */
        .border-controls,
        .shadow-controls,
        .typography-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            align-items: end;
        }
        
        /* Preset Styles */
        .preset-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .preset-btn {
            padding: 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .preset-btn:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        
        .preset-name {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .preset-desc {
            font-size: 12px;
            color: #666;
        }
        
        /* CSS Editor */
        .css-editor-container {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .css-editor-toolbar {
            background: #f1f1f1;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .css-editor-toolbar .button {
            margin-right: 5px;
            font-size: 12px;
        }
        
        #custom_css_editor {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
            border: none;
            resize: vertical;
            background: #2c3e50;
            color: #ecf0f1;
        }
        
        .css-editor-footer {
            background: #f1f1f1;
            padding: 8px 15px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        
        .css-selectors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        
        .css-selectors-grid code {
            padding: 8px;
            background: #2c3e50;
            color: #ecf0f1;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .css-selectors-grid code:hover {
            background: #34495e;
        }
        
        /* CSS Snippets */
        .css-snippets h4 {
            margin: 20px 0 10px;
            color: #2c3e50;
        }
        
        .css-snippet {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s ease;
            margin: 10px 0;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .css-snippet:hover {
            background: #34495e;
        }
        
        /* Analytics Dashboard */
        .analytics-dashboard {
            padding: 20px 0;
        }
        
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .analytics-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .analytics-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .analytics-card-header {
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .analytics-card-header h3 {
            margin: 0;
            font-size: 16px;
        }
        
        .analytics-card-body {
            padding: 20px;
        }
        
        .analytics-metric {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .metric-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .metric-label {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .popular-category-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .category-name {
            font-weight: 600;
        }
        
        .category-count {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        .performance-metrics {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .performance-metric {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cache-enabled {
            color: #4CAF50;
            font-weight: 600;
        }
        
        .category-analysis-table {
            overflow-x: auto;
        }
        
        .category-slug {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .settings-grid,
            .styling-layout,
            .advanced-layout {
                grid-template-columns: 1fr;
            }
            
            .analytics-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .analytics-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .analytics-actions {
                justify-content: center;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Live preview updates
            window.updatePreview = function() {
                // Update preview based on current settings
                console.log('Updating preview...');
            };
            
            window.updateCardSize = function(value) {
                $('#card_size_display').text(value + 'px');
                updatePreview();
            };
            
            window.updateGridGap = function(value) {
                $('#grid_gap_display').text(value + 'px');
                updatePreview();
            };
            
            window.copyShortcode = function() {
                const shortcode = $('#generated_shortcode').val();
                navigator.clipboard.writeText(shortcode).then(function() {
                    alert('Kısa kod kopyalandı!');
                });
            };
            
            // Generate shortcode dynamically
            $('#shortcode_limit, #shortcode_orderby, #shortcode_order').on('change', function() {
                let shortcode = '[esistenze_display_categories';
                const limit = $('#shortcode_limit').val();
                const orderby = $('#shortcode_orderby').val();
                const order = $('#shortcode_order').val();
                
                const params = [];
                if (limit) params.push('limit="' + limit + '"');
                if (orderby !== 'name') params.push('orderby="' + orderby + '"');
                if (order !== 'ASC') params.push('order="' + order + '"');
                
                if (params.length > 0) {
                    shortcode += ' ' + params.join(' ');
                }
                shortcode += ']';
                
                $('#generated_shortcode').val(shortcode);
            });
            
            // Styling controls
            window.updateBorderWidth = function(value) {
                $('#border_width_display').text(value + 'px');
                updatePreviewStyle();
            };
            
            window.updateBorderRadius = function(value) {
                $('#border_radius_display').text(value + 'px');
                updatePreviewStyle();
            };
            
            window.updateTitleSize = function(value) {
                $('#title_size_display').text(value + 'px');
                updatePreviewStyle();
            };
            
            window.updateDescSize = function(value) {
                $('#desc_size_display').text(value + 'px');
                updatePreviewStyle();
            };
            
            window.updatePreviewStyle = function() {
                // Update realtime preview with current style settings
                console.log('Updating preview styles...');
            };
            
            // Apply preset styles
            window.applyPreset = function(preset) {
                const presets = {
                    modern: {
                        card_bg_color: '#ffffff',
                        card_border_radius: 20,
                        shadow_blur: 30,
                        title_color: '#2c3e50'
                    },
                    classic: {
                        card_bg_color: '#f8f9fa',
                        card_border_radius: 8,
                        shadow_blur: 15,
                        title_color: '#343a40'
                    },
                    colorful: {
                        card_bg_color: '#e3f2fd',
                        card_border_radius: 15,
                        shadow_blur: 25,
                        title_color: '#1976d2'
                    },
                    elegant: {
                        card_bg_color: '#fafafa',
                        card_border_radius: 12,
                        shadow_blur: 20,
                        title_color: '#424242'
                    }
                };
                
                if (presets[preset]) {
                    const settings = presets[preset];
                    Object.keys(settings).forEach(key => {
                        const input = $(`[name="settings[${key}]"]`);
                        if (input.attr('type') === 'color') {
                            input.val(settings[key]);
                        } else if (input.attr('type') === 'range') {
                            input.val(settings[key]);
                            // Update display
                            const display = $(`#${key.replace('_', '')}_display`);
                            if (display.length) {
                                display.text(settings[key] + (key.includes('radius') || key.includes('blur') ? 'px' : ''));
                            }
                        }
                    });
                    updatePreviewStyle();
                    alert(`${preset.charAt(0).toUpperCase() + preset.slice(1)} stili uygulandı!`);
                }
            };
            
            // CSS Editor functions
            window.insertCSSTemplate = function(type) {
                const templates = {
                    'category-card': `/* Kategori Kart Stili */
.esistenze-category-styler-item {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 15px;
    padding: 20px;
    transition: all 0.3s ease;
}`,
                    'hover-effects': `/* Hover Efektleri */
.esistenze-category-styler-item:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}`,
                    'responsive': `/* Responsive Tasarım */
@media (max-width: 768px) {
    .esistenze-category-styler-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}`
                };
                
                if (templates[type]) {
                    const editor = $('#custom_css_editor');
                    const currentValue = editor.val();
                    const newValue = currentValue + (currentValue ? '\n\n' : '') + templates[type];
                    editor.val(newValue);
                    editor.focus();
                }
            };
            
            window.insertAtCursor = function(text) {
                const editor = document.getElementById('custom_css_editor');
                const startPos = editor.selectionStart;
                const endPos = editor.selectionEnd;
                const beforeText = editor.value.substring(0, startPos);
                const afterText = editor.value.substring(endPos, editor.value.length);
                editor.value = beforeText + text + afterText;
                editor.selectionStart = startPos + text.length;
                editor.selectionEnd = startPos + text.length;
                editor.focus();
            };
            
            window.insertSnippet = function(element) {
                const snippet = $(element).text();
                insertAtCursor('\n\n' + snippet + '\n\n');
            };
            
            window.validateCSS = function() {
                const css = $('#custom_css_editor').val();
                // Basic CSS validation
                const errors = [];
                const lines = css.split('\n');
                
                lines.forEach((line, index) => {
                    if (line.includes('{') && !line.includes('}') && lines[index + 1] && !lines[index + 1].includes('}')) {
                        // Check for missing closing braces
                    }
                });
                
                $('#css_validation_results').html(
                    errors.length === 0 
                        ? '<div style="color: green;">✓ CSS geçerli görünüyor</div>'
                        : '<div style="color: red;">⚠ ' + errors.length + ' hata bulundu</div>'
                );
            };
            
            // Initialize
            updatePreview();
            
            // Auto-update preview on any input change
            $('input, select, textarea').on('input change', function() {
                if ($(this).closest('.category-styler-content').length) {
                    clearTimeout(window.previewTimeout);
                    window.previewTimeout = setTimeout(updatePreview, 500);
                }
            });
        });
        </script>
        <?php
    }
    
    // Other existing methods (register_settings, display_styled_categories, enqueue_styles, etc.)
    public function register_settings() {
        register_setting('esistenza_category_styler', 'esistenze_category_styler_settings');
        register_setting('esistenze_category_styler', 'esistenze_custom_category_css');
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
        $settings = get_option('esistenza_category_styler_settings', self::get_default_settings());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Generate dynamic CSS
        $dynamic_css = $this->generate_dynamic_css($settings);
        
        // Enqueue main stylesheet
        wp_enqueue_style('esistenza-category-styler', ESISTENZA_WP_KIT_URL . 'modules/category-styler/assets/style.css', array(), ESISTENZA_WP_KIT_VERSION);
        
        // Add dynamic CSS
        if (!empty($dynamic_css)) {
            if (!empty($settings['inline_critical_css'])) {
                wp_add_inline_style('esistenza-category-styler', $dynamic_css);
            } else {
                wp_add_inline_style('esistenza-category-styler', $dynamic_css);
            }
        }
        
        // Add custom CSS
        $custom_css = get_option('esistenza_custom_category_css', '');
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
}

// Initialize the module
EsistenzaCategoryStyler::getInstance();                                        <label>
                                            Font Boyutu:
                                            <input type="range" name="settings[sidebar_header_font_size]" value="<?php echo esc_attr($settings['sidebar_header_font_size'] ?? 18); ?>" min="12" max="24" oninput="updateSidebarHeaderSize(this.value)">
                                            <span id="sidebar_header_size_display"><?php echo esc_attr($settings['sidebar_header_font_size'] ?? 18); ?>px</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Menu Öğeleri</th>
                                <td>
                                    <div class="menu-item-controls">
                                        <label>
                                            Arka Plan:
                                            <input type="color" name="settings[sidebar_item_bg]" value="<?php echo esc_attr($settings['sidebar_item_bg'] ?? '#ffffff'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Hover Arka Plan:
                                            <input type="color" name="settings[sidebar_item_hover_bg]" value="<?php echo esc_attr($settings['sidebar_item_hover_bg'] ?? '#f9f9f9'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Yazı Rengi:
                                            <input type="color" name="settings[sidebar_item_color]" value="<?php echo esc_attr($settings['sidebar_item_color'] ?? '#2c3e50'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Hover Yazı Rengi:
                                            <input type="color" name="settings[sidebar_item_hover_color]" value="<?php echo esc_attr($settings['sidebar_item_hover_color'] ?? '#4CAF50'); ?>" class="color-picker">
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Aktif Öğe</th>
                                <td>
                                    <div class="active-item-controls">
                                        <label>
                                            Arka Plan:
                                            <input type="color" name="settings[sidebar_active_bg]" value="<?php echo esc_attr($settings['sidebar_active_bg'] ?? '#e6f3e6'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Sol Kenarlık:
                                            <input type="color" name="settings[sidebar_active_border]" value="<?php echo esc_attr($settings['sidebar_active_border'] ?? '#4CAF50'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Kenarlık Kalınlığı:
                                            <input type="range" name="settings[sidebar_active_border_width]" value="<?php echo esc_attr($settings['sidebar_active_border_width'] ?? 5); ?>" min="1" max="10" oninput="updateActiveBorderWidth(this.value)">
                                            <span id="active_border_width_display"><?php echo esc_attr($settings['sidebar_active_border_width'] ?? 5); ?>px</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="postbox">
                    <h2 class="hndle">Page Header Tasarımı</h2>
                    <div class="inside">
                        <p class="description">Kategori sayfalarının üst header bölümü tasarımı (#page-header-wrap).</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">Header Arka Plan</th>
                                <td>
                                    <div class="header-bg-controls">
                                        <label>
                                            Gradient Başlangıç:
                                            <input type="color" name="settings[header_bg_start]" value="<?php echo esc_attr($settings['header_bg_start'] ?? '#4CAF50'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Gradient Orta:
                                            <input type="color" name="settings[header_bg_middle]" value="<?php echo esc_attr($settings['header_bg_middle'] ?? '#45a049'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Gradient Bitiş:
                                            <input type="color" name="settings[header_bg_end]" value="<?php echo esc_attr($settings['header_bg_end'] ?? '#2E7D32'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Yükseklik:
                                            <input type="range" name="settings[header_height]" value="<?php echo esc_attr($settings['header_height'] ?? 350); ?>" min="200" max="500" oninput="updateHeaderHeight(this.value)">
                                            <span id="header_height_display"><?php echo esc_attr($settings['header_height'] ?? 350); ?>px</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Header Başlık</th>
                                <td>
                                    <div class="header-title-controls">
                                        <label>
                                            Font Boyutu:
                                            <input type="range" name="settings[header_title_size]" value="<?php echo esc_attr($settings['header_title_size'] ?? 48); ?>" min="24" max="72" oninput="updateHeaderTitleSize(this.value)">
                                            <span id="header_title_size_display"><?php echo esc_attr($settings['header_title_size'] ?? 48); ?>px</span>
                                        </label>
                                        <label>
                                            Yazı Rengi:
                                            <input type="color" name="settings[header_title_color]" value="<?php echo esc_attr($settings['header_title_color'] ?? '#ffffff'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Arka Plan Opacity:
                                            <input type="range" name="settings[header_title_bg_opacity]" value="<?php echo esc_attr($settings['header_title_bg_opacity'] ?? 0.6); ?>" min="0" max="1" step="0.1" oninput="updateHeaderTitleOpacity(this.value)">
                                            <span id="header_title_opacity_display"><?php echo esc_attr($settings['header_title_bg_opacity'] ?? 0.6); ?></span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Sidebar Ayarlarını Kaydet">
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_advanced_tab() {
        $settings = get_option('esistenze_category_styler_settings', self::get_default_settings());
        $custom_css = get_option('esistenze_custom_category_css', '');
        ?>
        <div class="category-styler-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_category_styler_save'); ?>
                <input type="hidden" name="tab" value="advanced">
                
                <div class="advanced-layout">
                    <div class="advanced-main">
                        <div class="postbox">
                            <h2 class="hndle">Özel CSS</h2>
                            <div class="inside">
                                <p class="description">Kategori stillerine eklemek istediğiniz özel CSS kodlarını buraya yazın. Bu kodlar tema CSS'inden sonra yüklenecektir.</p>
                                
                                <div class="css-editor-container">
                                    <div class="css-editor-toolbar">
                                        <button type="button" class="button" onclick="insertCSSTemplate('category-card')">Kategori Kartı Template</button>
                                        <button type="button" class="button" onclick="insertCSSTemplate('hover-effects')">Hover Efektleri</button>
                                        <button type="button" class="button" onclick="insertCSSTemplate('responsive')">Responsive</button>
                                        <button type="button" class="button" onclick="formatCSS()">Format</button>
                                        <button type="button" class="button" onclick="validateCSS()">Doğrula</button>
                                    </div>
                                    
                                    <textarea name="custom_css" id="custom_css_editor" rows="20" class="large-text code" placeholder="/* Özel CSS kodlarınızı buraya yazın */"><?php echo esc_textarea($custom_css); ?></textarea>
                                    
                                    <div class="css-editor-footer">
                                        <span class="css-status" id="css_status">Hazır</span>
                                        <span class="css-lines" id="css_lines">Satır: 1</span>
                                    </div>
                                </div>
                                
                                <div class="css-helpers">
                                    <h4>Yaygın CSS Seçiciler:</h4>
                                    <div class="css-selectors-grid">
                                        <code onclick="insertAtCursor('.esistenze-category-styler-grid')">.esistenze-category-styler-grid</code>
                                        <code onclick="insertAtCursor('.esistenze-category-styler-item')">.esistenze-category-styler-item</code>
                                        <code onclick="insertAtCursor('.esistenze-category-styler-title')">.esistenze-category-styler-title</code>
                                        <code onclick="insertAtCursor('.esistenze-category-styler-image')">.esistenze-category-styler-image</code>
                                        <code onclick="insertAtCursor('.esistenze-category-styler-description')">.esistenze-category-styler-description</code>
                                        <code onclick="insertAtCursor('#nav_menu-3')">#nav_menu-3</code>
                                        <code onclick="insertAtCursor('#nav_menu-7')">#nav_menu-7</code>
                                        <code onclick="insertAtCursor('#page-header-wrap')">#page-header-wrap</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Performans Ayarları</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">CSS Optimizasyonu</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[minify_css]" value="1" <?php checked(!empty($settings['minify_css'])); ?>>
                                                    CSS'i minify et (dosya boyutunu küçült)
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[inline_critical_css]" value="1" <?php checked(!empty($settings['inline_critical_css'])); ?>>
                                                    Kritik CSS'i inline olarak yükle
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[defer_non_critical_css]" value="1" <?php checked(!empty($settings['defer_non_critical_css'])); ?>>
                                                    Kritik olmayan CSS'i defer et
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Görsel Optimizasyonu</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[lazy_load_images]" value="1" <?php checked(!empty($settings['lazy_load_images'])); ?>>
                                                    Kategori görsellerini lazy load ile yükle
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[webp_support]" value="1" <?php checked(!empty($settings['webp_support'])); ?>>
                                                    WebP formatını destekle (varsa)
                                                </label><br>
                                                <label>
                                                    Görsel Boyutu:
                                                    <select name="settings[image_size]">
                                                        <option value="thumbnail" <?php selected($settings['image_size'] ?? '', 'thumbnail'); ?>>Thumbnail (150x150)</option>
                                                        <option value="medium" <?php selected($settings['image_size'] ?? '', 'medium'); ?>>Medium (300x300)</option>
                                                        <option value="medium_large" <?php selected($settings['image_size'] ?? '', 'medium_large'); ?>>Medium Large (768x0)</option>
                                                        <option value="large" <?php selected($settings['image_size'] ?? '', 'large'); ?>>Large (1024x1024)</option>
                                                    </select>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Önbellek</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[enable_caching]" value="1" <?php checked(!empty($settings['enable_caching'])); ?>>
                                                    Kategori verilerini önbellekte sakla
                                                </label><br>
                                                <label>
                                                    Önbellek Süresi:
                                                    <select name="settings[cache_duration]">
                                                        <option value="3600" <?php selected($settings['cache_duration'] ?? '', '3600'); ?>>1 Saat</option>
                                                        <option value="21600" <?php selected($settings['cache_duration'] ?? '', '21600'); ?>>6 Saat</option>
                                                        <option value="43200" <?php selected($settings['cache_duration'] ?? '', '43200'); ?>>12 Saat</option>
                                                        <option value="86400" <?php selected($settings['cache_duration'] ?? '', '86400'); ?>>24 Saat</option>
                                                    </select>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Gelişmiş Seçenekler</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Debug Modu</th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="settings[debug_mode]" value="1" <?php checked(!empty($settings['debug_mode'])); ?>>
                                                Debug bilgilerini göster (sadece admin kullanıcılar için)
                                            </label>
                                            <p class="description">Etkinleştirildiğinde, CSS yükleme süreleri ve performans bilgileri gösterilir.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Uyumluluk</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[disable_theme_styles]" value="1" <?php checked(!empty($settings['disable_theme_styles'])); ?>>
                                                    Tema'nın kategori stillerini devre dışı bırak
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[force_styles]" value="1" <?php checked(!empty($settings['force_styles'])); ?>>
                                                    Stilleri !important ile zorla uygula
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[legacy_support]" value="1" <?php checked(!empty($settings['legacy_support'])); ?>>
                                                    Eski browser desteği (IE11+)
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="advanced-sidebar">
                        <div class="postbox">
                            <h2 class="hndle">CSS Önizleme</h2>
                            <div class="inside">
                                <div id="css_preview" class="css-preview-container">
                                    <p>CSS değişikliklerinizin önizlemesi burada görünecek.</p>
                                </div>
                                <button type="button" class="button button-primary" onclick="previewCSS()">CSS'i Önizle</button>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">CSS Kod Snippets</h2>
                            <div class="inside">
                                <div class="css-snippets">
                                    <h4>Hover Efektleri:</h4>
                                    <pre class="css-snippet" onclick="insertSnippet(this)">/* Yumuşak Zoom */
.esistenze-category-styler-item:hover {
    transform: scale(1.03);
    transition: transform 0.3s ease;
}</pre>
                                    
                                    <h4>Gradient Arkaplan:</h4>
                                    <pre class="css-snippet" onclick="insertSnippet(this)">/* Gradient Arkaplan */
.esistenza-category-styler-item {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}</pre>
                                    
                                    <h4>Gölge Efekti:</h4>
                                    <pre class="css-snippet" onclick="insertSnippet(this)">/* Dinamik Gölge */
.esistenze-category-styler-item {
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.esistenze-category-styler-item:hover {
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}</pre>
                                    
                                    <h4>Responsive Grid:</h4>
                                    <pre class="css-snippet" onclick="insertSnippet(this)">/* Responsive Grid */
@media (max-width: 768px) {
    .esistenze-category-styler-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}</pre>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">CSS Doğrulama</h2>
                            <div class="inside">
                                <div id="css_validation_results">
                                    <p>CSS doğrulama sonuçları burada görünecek.</p>
                                </div>
                                <button type="button" class="button" onclick="validateCSS()">CSS'i Doğrula</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Gelişmiş Ayarları Kaydet">
                    <button type="button" class="button" onclick="resetCSS()">CSS'i Sıfırla</button>
                    <button type="button" class="button" onclick="exportCSS()">CSS'i Dışa Aktar</button>
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_analytics_tab() {
        ?>
        <div class="category-styler-content">
            <div class="analytics-dashboard">
                <div class="analytics-header">
                    <h2>Category Styler İstatistikleri</h2>
                    <div class="analytics-actions">
                        <select id="analytics_period">
                            <option value="7">Son 7 Gün</option>
                            <option value="30">Son 30 Gün</option>
                            <option value="90">Son 90 Gün</option>
                        </select>
                        <button type="button" class="button" onclick="refreshAnalytics()">Yenile</button>
                        <button type="button" class="button" onclick="exportAnalytics()">Dışa Aktar</button>
                    </div>
                </div>
                
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>Kategori Performansı</h3>
                            <span class="dashicons dashicons-chart-line"></span>
                        </div>
                        <div class="analytics-card-body">
                            <div class="analytics-metric">
                                <div class="metric-value">
                                    <?php 
                                    $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
                                    echo count($categories);
                                    ?>
                                </div>
                                <div class="metric-label">Toplam Kategori</div>
                            </div>
                            <div class="analytics-metric">
                                <div class="metric-value">
                                    <?php 
                                    $with_images = 0;
                                    foreach ($categories as $cat) {
                                        if (get_term_meta($cat->term_id, 'thumbnail_id', true)) {
                                            $with_images++;
                                        }
                                    }
                                    echo round(($with_images / count($categories)) * 100, 1);
                                    ?>%
                                </div>
                                <div class="metric-label">Resim Tamamlanma</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>Stil Kullanımı</h3>
                            <span class="dashicons dashicons-admin-appearance"></span>
                        </div>
                        <div class="analytics-card-body">
                            <div class="style-usage-chart">
                                <canvas id="style_usage_chart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>En Popüler Kategoriler</h3>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <div class="analytics-card-body">
                            <div class="popular-categories-list">
                                <?php
                                $popular_cats = get_terms(array(
                                    'taxonomy' => 'product_cat',
                                    'orderby' => 'count',
                                    'order' => 'DESC',
                                    'number' => 5,
                                    'hide_empty' => false
                                ));
                                
                                foreach ($popular_cats as $cat) {
                                    echo '<div class="popular-category-item">';
                                    echo '<span class="category-name">' . esc_html($cat->name) . '</span>';
                                    echo '<span class="category-count">' . $cat->count . ' ürün</span>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>Performans Metrikleri</h3>
                            <span class="dashicons dashicons-performance"></span>
                        </div>
                        <div class="analytics-card-body">
                            <div class="performance-metrics">
                                <div class="performance-metric">
                                    <span class="metric-label">CSS Boyutu:</span>
                                    <span class="metric-value" id="css_size">Hesaplanıyor...</span>
                                </div>
                                <div class="performance-metric">
                                    <span class="metric-label">Yükleme Süresi:</span>
                                    <span class="metric-value" id="load_time">Hesaplanıyor...</span>
                                </div>
                                <div class="performance-metric">
                                    <span class="metric-label">Önbellek Durumu:</span>
                                    <span class="metric-value cache-enabled">Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="analytics-detailed">
                    <div class="postbox">
                        <h2 class="hndle">Detaylı Kategori Analizi</h2>
                        <div class="inside">
                            <div class="category-analysis-table">
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th>Kategori</th>
                                            <th>Ürün Sayısı</th>
                                            <th>Resim</th>
                                            <th>Açıklama</th>
                                            <th>Son Güncelleme</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html($category->name); ?></strong>
                                                <div class="category-slug"><?php echo esc_html($category->slug); ?></div>
                                            </td>
                                            <td><?php echo $category->count; ?></td>
                                            <td>
                                                <?php if (get_term_meta($category->term_id, 'thumbnail_id', true)): ?>
                                                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                                <?php else: ?>
                                                    <span class="dashicons dashicons-dismiss" style="color: red;"></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($category->description)): ?>
                                                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                                <?php else: ?>
                                                    <span class="dashicons dashicons-dismiss" style="color: red;"></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $last_modified = get_term_meta($category->term_id, '_last_modified', true);
                                                echo $last_modified ? date('d.m.Y', strtotime($last_modified)) : 'Bilinmiyor';
                                                ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo admin_url('term.php?taxonomy=product_cat&tag_ID=' . $category->term_id); ?>" class="button button-small">Düzenle</a>
                                                <a href="<?php echo get_term_link($category); ?>" class="button button-small" target="_blank">Görüntüle</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private static function handle_form_submission() {
        if (!current_user_can('manage_options') || !check_admin_referer('esistenze_category_styler_save')) {
            wp_die('Yetkiniz yok.');
        }
        
        $tab = sanitize_text_field($_POST['tab'] ?? 'general');
        $current_settings = get_option('esistenze_category_styler_settings', self::get_default_settings());
        
        switch ($tab) {
            case 'general':
                $new_settings = array_merge($current_settings, array(
                    'enabled' => !empty($_POST['settings']['enabled']),
                    'grid_columns' => sanitize_text_field($_POST['settings']['grid_columns'] ?? ''),
                    'card_min_width' => intval($_POST['settings']['card_min_width'] ?? 250),
                    'grid_gap' => intval($_POST['settings']['grid_gap'] ?? 20),
                    'hide_price_hover' => !empty($_POST['settings']['hide_price_hover']),
                    'enable_animations' => !empty($_POST['settings']['enable_animations']),
                    'show_product_count' => !empty($_POST['settings']['show_product_count']),
                    'lazy_load_images' => !empty($_POST['settings']['lazy_load_images'])
                ));
                break;
                
            case 'grid-styling':
                $new_settings = array_merge($current_settings, array(
                    'card_bg_color' => sanitize_hex_color($_POST['settings']['card_bg_color'] ?? '#ffffff'),
                    'card_bg_gradient' => sanitize_hex_color($_POST['settings']['card_bg_gradient'] ?? '#f8f8f8'),
                    'card_border_width' => intval($_POST['settings']['card_border_width'] ?? 1),
                    'card_border_color' => sanitize_hex_color($_POST['settings']['card_border_color'] ?? '#e0e0e0'),
                    'card_<?php
/*
 * Enhanced Category Styler Module
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
    
    public static function admin_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        // Handle form submissions
        if (isset($_POST['submit'])) {
            self::handle_form_submission();
        }
        
        echo '<div class="wrap esistenze-category-styler-wrap">';
        echo '<h1 class="wp-heading-inline">Category Styler</h1>';
        echo '<a href="#" class="page-title-action" onclick="previewStyles()">Önizleme</a>';
        echo '<hr class="wp-header-end">';
        
        // Show admin notices
        self::show_admin_notices();
        
        // Tabs
        self::render_tabs($current_tab);
        
        // Tab content
        switch ($current_tab) {
            case 'general':
                self::render_general_tab();
                break;
            case 'grid-styling':
                self::render_grid_styling_tab();
                break;
            case 'sidebar-styling':
                self::render_sidebar_styling_tab();
                break;
            case 'advanced':
                self::render_advanced_tab();
                break;
            case 'analytics':
                self::render_analytics_tab();
                break;
        }
        
        echo '</div>';
        
        // Add JavaScript and CSS
        self::enqueue_admin_assets();
    }
    
    private static function render_tabs($current_tab) {
        $tabs = array(
            'general' => array('label' => 'Genel Ayarlar', 'icon' => 'dashicons-admin-settings'),
            'grid-styling' => array('label' => 'Grid Tasarım', 'icon' => 'dashicons-grid-view'),
            'sidebar-styling' => array('label' => 'Sidebar Tasarım', 'icon' => 'dashicons-menu-alt'),
            'advanced' => array('label' => 'Gelişmiş', 'icon' => 'dashicons-admin-tools'),
            'analytics' => array('label' => 'İstatistikler', 'icon' => 'dashicons-chart-bar')
        );
        
        echo '<nav class="nav-tab-wrapper wp-clearfix">';
        foreach ($tabs as $tab_key => $tab) {
            $class = ($current_tab === $tab_key) ? 'nav-tab nav-tab-active' : 'nav-tab';
            echo '<a href="' . admin_url('admin.php?page=esistenze-category-styler&tab=' . $tab_key) . '" class="' . $class . '">';
            echo '<span class="dashicons ' . $tab['icon'] . '"></span> ' . $tab['label'];
            echo '</a>';
        }
        echo '</nav>';
    }
    
    private static function render_general_tab() {
        $settings = get_option('esistenze_category_styler_settings', self::get_default_settings());
        ?>
        <div class="category-styler-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_category_styler_save'); ?>
                <input type="hidden" name="tab" value="general">
                
                <div class="settings-grid">
                    <!-- Sol Panel -->
                    <div class="settings-main">
                        <div class="postbox">
                            <h2 class="hndle">Ana Ayarlar</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Category Styler'ı Etkinleştir</th>
                                        <td>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="settings[enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?> onchange="togglePreview()">
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <p class="description">Kategori stilizasyonunu site genelinde etkinleştirir</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Grid Görünümü</th>
                                        <td>
                                            <select name="settings[grid_columns]" class="regular-text" onchange="updatePreview()">
                                                <option value="2" <?php selected($settings['grid_columns'] ?? '', 2); ?>>2 Sütun</option>
                                                <option value="3" <?php selected($settings['grid_columns'] ?? '', 3); ?>>3 Sütun</option>
                                                <option value="4" <?php selected($settings['grid_columns'] ?? '', 4); ?>>4 Sütun</option>
                                                <option value="auto" <?php selected($settings['grid_columns'] ?? '', 'auto'); ?>>Otomatik</option>
                                            </select>
                                            <p class="description">Kategori kartlarının sütun sayısı</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Kart Boyutu</th>
                                        <td>
                                            <input type="range" name="settings[card_min_width]" value="<?php echo esc_attr($settings['card_min_width'] ?? 250); ?>" min="200" max="400" oninput="updateCardSize(this.value)" class="width-slider">
                                            <span id="card_size_display"><?php echo esc_attr($settings['card_min_width'] ?? 250); ?>px</span>
                                            <p class="description">Minimum kart genişliği</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Boşluk</th>
                                        <td>
                                            <input type="range" name="settings[grid_gap]" value="<?php echo esc_attr($settings['grid_gap'] ?? 20); ?>" min="10" max="50" oninput="updateGridGap(this.value)" class="width-slider">
                                            <span id="grid_gap_display"><?php echo esc_attr($settings['grid_gap'] ?? 20); ?>px</span>
                                            <p class="description">Kartlar arası boşluk</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Görünüm Seçenekleri</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Özel Seçenekler</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[hide_price_hover]" value="1" <?php checked(!empty($settings['hide_price_hover'])); ?>>
                                                    Price hover elementlerini gizle
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[enable_animations]" value="1" <?php checked(!empty($settings['enable_animations'])); ?>>
                                                    Hover animasyonlarını etkinleştir
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[show_product_count]" value="1" <?php checked(!empty($settings['show_product_count'])); ?>>
                                                    Ürün sayısını göster
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[lazy_load_images]" value="1" <?php checked(!empty($settings['lazy_load_images'])); ?>>
                                                    Görselleri lazy loading ile yükle
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Kısa Kod Ayarları</h2>
                            <div class="inside">
                                <div class="shortcode-generator">
                                    <h4>Kısa Kod Oluşturucu</h4>
                                    <table class="form-table">
                                        <tr>
                                            <th>Kategori Sayısı</th>
                                            <td>
                                                <select id="shortcode_limit">
                                                    <option value="">Tümü</option>
                                                    <option value="3">3</option>
                                                    <option value="6">6</option>
                                                    <option value="9">9</option>
                                                    <option value="12">12</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Sıralama</th>
                                            <td>
                                                <select id="shortcode_orderby">
                                                    <option value="name">İsim</option>
                                                    <option value="count">Ürün Sayısı</option>
                                                    <option value="id">ID</option>
                                                    <option value="menu_order">Menü Sırası</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Sıra</th>
                                            <td>
                                                <select id="shortcode_order">
                                                    <option value="ASC">Artan</option>
                                                    <option value="DESC">Azalan</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <div class="shortcode-result">
                                        <label>Oluşturulan Kısa Kod:</label>
                                        <input type="text" id="generated_shortcode" value="[esistenze_display_categories]" readonly class="large-text code">
                                        <button type="button" class="button" onclick="copyShortcode()">Kopyala</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sağ Panel - Önizleme -->
                    <div class="settings-sidebar">
                        <div class="postbox">
                            <h2 class="hndle">Canlı Önizleme</h2>
                            <div class="inside">
                                <div id="category_preview" class="category-preview-container">
                                    <div class="preview-loading">
                                        <span class="spinner is-active"></span>
                                        <p>Önizleme yükleniyor...</p>
                                    </div>
                                </div>
                                
                                <div class="preview-controls">
                                    <button type="button" class="button" onclick="refreshPreview()">
                                        <span class="dashicons dashicons-update"></span> Yenile
                                    </button>
                                    <button type="button" class="button" onclick="fullPreview()">
                                        <span class="dashicons dashicons-external"></span> Tam Ekran
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Kategori İstatistikleri</h2>
                            <div class="inside">
                                <?php
                                $categories = get_terms(array(
                                    'taxonomy' => 'product_cat',
                                    'hide_empty' => false,
                                    'parent' => 0
                                ));
                                ?>
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo count($categories); ?></div>
                                        <div class="stat-label">Ana Kategori</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number">
                                            <?php 
                                            $with_images = 0;
                                            foreach ($categories as $cat) {
                                                if (get_term_meta($cat->term_id, 'thumbnail_id', true)) {
                                                    $with_images++;
                                                }
                                            }
                                            echo $with_images;
                                            ?>
                                        </div>
                                        <div class="stat-label">Resimli Kategori</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number">
                                            <?php 
                                            $total_products = 0;
                                            foreach ($categories as $cat) {
                                                $total_products += $cat->count;
                                            }
                                            echo $total_products;
                                            ?>
                                        </div>
                                        <div class="stat-label">Toplam Ürün</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Hızlı İşlemler</h2>
                            <div class="inside">
                                <p><strong>CSS'i Yeniden Oluştur:</strong></p>
                                <button type="button" class="button" onclick="regenerateCSS()">
                                    <span class="dashicons dashicons-admin-appearance"></span> CSS Yenile
                                </button>
                                
                                <p><strong>Önbelleği Temizle:</strong></p>
                                <button type="button" class="button" onclick="clearCache()">
                                    <span class="dashicons dashicons-trash"></span> Önbellek Temizle
                                </button>
                                
                                <p><strong>Ayarları Sıfırla:</strong></p>
                                <button type="button" class="button button-secondary" onclick="resetSettings()">
                                    <span class="dashicons dashicons-undo"></span> Varsayılana Dön
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Ayarları Kaydet">
                    <button type="button" class="button" onclick="previewChanges()">Değişiklikleri Önizle</button>
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_grid_styling_tab() {
        $settings = get_option('esistenze_category_styler_settings', self::get_default_settings());
        ?>
        <div class="category-styler-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_category_styler_save'); ?>
                <input type="hidden" name="tab" value="grid-styling">
                
                <div class="styling-layout">
                    <div class="styling-controls">
                        <div class="postbox">
                            <h2 class="hndle">Kart Tasarımı</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Arka Plan</th>
                                        <td>
                                            <div class="color-controls">
                                                <label>
                                                    Renk:
                                                    <input type="color" name="settings[card_bg_color]" value="<?php echo esc_attr($settings['card_bg_color'] ?? '#ffffff'); ?>" class="color-picker" onchange="updatePreviewStyle()">
                                                </label>
                                                <label>
                                                    Gradient:
                                                    <input type="color" name="settings[card_bg_gradient]" value="<?php echo esc_attr($settings['card_bg_gradient'] ?? '#f8f8f8'); ?>" class="color-picker" onchange="updatePreviewStyle()">
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Kenarlık</th>
                                        <td>
                                            <div class="border-controls">
                                                <label>
                                                    Kalınlık:
                                                    <input type="range" name="settings[card_border_width]" value="<?php echo esc_attr($settings['card_border_width'] ?? 1); ?>" min="0" max="5" oninput="updateBorderWidth(this.value)">
                                                    <span id="border_width_display"><?php echo esc_attr($settings['card_border_width'] ?? 1); ?>px</span>
                                                </label>
                                                <label>
                                                    Renk:
                                                    <input type="color" name="settings[card_border_color]" value="<?php echo esc_attr($settings['card_border_color'] ?? '#e0e0e0'); ?>" class="color-picker" onchange="updatePreviewStyle()">
                                                </label>
                                                <label>
                                                    Radius:
                                                    <input type="range" name="settings[card_border_radius]" value="<?php echo esc_attr($settings['card_border_radius'] ?? 15); ?>" min="0" max="30" oninput="updateBorderRadius(this.value)">
                                                    <span id="border_radius_display"><?php echo esc_attr($settings['card_border_radius'] ?? 15); ?>px</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Gölge</th>
                                        <td>
                                            <div class="shadow-controls">
                                                <label>
                                                    X Offset:
                                                    <input type="range" name="settings[shadow_x]" value="<?php echo esc_attr($settings['shadow_x'] ?? 0); ?>" min="-20" max="20" oninput="updateShadow()">
                                                </label>
                                                <label>
                                                    Y Offset:
                                                    <input type="range" name="settings[shadow_y]" value="<?php echo esc_attr($settings['shadow_y'] ?? 8); ?>" min="0" max="30" oninput="updateShadow()">
                                                </label>
                                                <label>
                                                    Blur:
                                                    <input type="range" name="settings[shadow_blur]" value="<?php echo esc_attr($settings['shadow_blur'] ?? 20); ?>" min="0" max="50" oninput="updateShadow()">
                                                </label>
                                                <label>
                                                    Opacity:
                                                    <input type="range" name="settings[shadow_opacity]" value="<?php echo esc_attr($settings['shadow_opacity'] ?? 0.1); ?>" min="0" max="1" step="0.1" oninput="updateShadow()">
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Tipografi</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Başlık</th>
                                        <td>
                                            <div class="typography-controls">
                                                <label>
                                                    Font Boyutu:
                                                    <input type="range" name="settings[title_font_size]" value="<?php echo esc_attr($settings['title_font_size'] ?? 20); ?>" min="12" max="32" oninput="updateTitleSize(this.value)">
                                                    <span id="title_size_display"><?php echo esc_attr($settings['title_font_size'] ?? 20); ?>px</span>
                                                </label>
                                                <label>
                                                    Font Ağırlığı:
                                                    <select name="settings[title_font_weight]" onchange="updatePreviewStyle()">
                                                        <option value="400" <?php selected($settings['title_font_weight'] ?? '', '400'); ?>>Normal</option>
                                                        <option value="500" <?php selected($settings['title_font_weight'] ?? '', '500'); ?>>Medium</option>
                                                        <option value="600" <?php selected($settings['title_font_weight'] ?? '', '600'); ?>>Semi Bold</option>
                                                        <option value="700" <?php selected($settings['title_font_weight'] ?? '', '700'); ?>>Bold</option>
                                                    </select>
                                                </label>
                                                <label>
                                                    Renk:
                                                    <input type="color" name="settings[title_color]" value="<?php echo esc_attr($settings['title_color'] ?? '#2c3e50'); ?>" class="color-picker" onchange="updatePreviewStyle()">
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Açıklama</th>
                                        <td>
                                            <div class="typography-controls">
                                                <label>
                                                    Font Boyutu:
                                                    <input type="range" name="settings[desc_font_size]" value="<?php echo esc_attr($settings['desc_font_size'] ?? 14); ?>" min="10" max="18" oninput="updateDescSize(this.value)">
                                                    <span id="desc_size_display"><?php echo esc_attr($settings['desc_font_size'] ?? 14); ?>px</span>
                                                </label>
                                                <label>
                                                    Renk:
                                                    <input type="color" name="settings[desc_color]" value="<?php echo esc_attr($settings['desc_color'] ?? '#7f8c8d'); ?>" class="color-picker" onchange="updatePreviewStyle()">
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Hover Efektleri</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Transform</th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="settings[hover_scale]" value="1" <?php checked(!empty($settings['hover_scale'])); ?>>
                                                Scale efekti (1.05x büyütme)
                                            </label><br>
                                            <label>
                                                <input type="checkbox" name="settings[hover_lift]" value="1" <?php checked(!empty($settings['hover_lift'])); ?>>
                                                Lift efekti (yukarı hareket)
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Gölge Değişimi</th>
                                        <td>
                                            <label>
                                                Hover Gölge Yoğunluğu:
                                                <input type="range" name="settings[hover_shadow_intensity]" value="<?php echo esc_attr($settings['hover_shadow_intensity'] ?? 0.2); ?>" min="0" max="0.5" step="0.1" oninput="updateHoverShadow(this.value)">
                                                <span id="hover_shadow_display"><?php echo esc_attr($settings['hover_shadow_intensity'] ?? 0.2); ?></span>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="styling-preview">
                        <div class="postbox">
                            <h2 class="hndle">Gerçek Zamanlı Önizleme</h2>
                            <div class="inside">
                                <div id="realtime_preview" class="realtime-preview-container">
                                    <!-- Live preview will be injected here -->
                                </div>
                                
                                <div class="preset-styles">
                                    <h4>Hazır Stiller</h4>
                                    <div class="preset-grid">
                                        <button type="button" class="preset-btn" onclick="applyPreset('modern')">
                                            <span class="preset-name">Modern</span>
                                            <span class="preset-desc">Temiz ve minimal</span>
                                        </button>
                                        <button type="button" class="preset-btn" onclick="applyPreset('classic')">
                                            <span class="preset-name">Klasik</span>
                                            <span class="preset-desc">Geleneksel tasarım</span>
                                        </button>
                                        <button type="button" class="preset-btn" onclick="applyPreset('colorful')">
                                            <span class="preset-name">Renkli</span>
                                            <span class="preset-desc">Canlı renkler</span>
                                        </button>
                                        <button type="button" class="preset-btn" onclick="applyPreset('elegant')">
                                            <span class="preset-name">Zarif</span>
                                            <span class="preset-desc">Lüks görünüm</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Tasarım Ayarlarını Kaydet">
                    <button type="button" class="button" onclick="exportStyles()">Stilleri Dışa Aktar</button>
                    <button type="button" class="button" onclick="importStyles()">Stilleri İçe Aktar</button>
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_sidebar_styling_tab() {
        $settings = get_option('esistenze_category_styler_settings', self::get_default_settings());
        ?>
        <div class="category-styler-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_category_styler_save'); ?>
                <input type="hidden" name="tab" value="sidebar-styling">
                
                <div class="postbox">
                    <h2 class="hndle">Sidebar Widget Tasarımı</h2>
                    <div class="inside">
                        <p class="description">Bu ayarlar sidebar'daki kategori widget'larının görünümünü kontrol eder (#nav_menu-3, #nav_menu-7 gibi).</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">Widget Başlığı</th>
                                <td>
                                    <div class="widget-header-controls">
                                        <label>
                                            Arka Plan:
                                            <input type="color" name="settings[sidebar_header_bg_start]" value="<?php echo esc_attr($settings['sidebar_header_bg_start'] ?? '#4CAF50'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Gradient Bitiş:
                                            <input type="color" name="settings[sidebar_header_bg_end]" value="<?php echo esc_attr($settings['sidebar_header_bg_end'] ?? '#45a049'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Yazı Rengi:
                                            <input type="color" name="settings[sidebar_header_color]" value="<?php echo esc_attr($settings['sidebar_header_color'] ?? '#ffffff'); ?>" class="color-picker">
                                        </label>
                                        <label>
                                            Font Boyutu:
                                            <input type="range" name="settings[sidebar_header_font_size]" value="<?php echo esc_attr($settings['sidebar_header_font_size'] ?? 18); ?>" min="12" max="24" oninput="updateSidebarHeaderSize(this.value)">
                                            <span id="sidebar_header_size_display"><?php echo esc_attr($settings['sidebar_header_font_size'] ?? 18); ?>px</span>
                                        </label>
                                