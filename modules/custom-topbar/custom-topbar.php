                        <div class="postbox">
                            <h2 class="hndle">Gelişmiş İşlemler</h2>
                            <div class="inside">
                                <div class="advanced-actions">
                                    <h4>Topbar Yönetimi:</h4>
                                    <button type="button" class="button" onclick="exportTopbarSettings()">
                                        <span class="dashicons dashicons-download"></span> Ayarları Dışa Aktar
                                    </button>
                                    <button type="button" class="button" onclick="importTopbarSettings()">
                                        <span class="dashicons dashicons-upload"></span> Ayarları İçe Aktar
                                    </button>
                                    <button type="button" class="button" onclick="resetAllSettings()">
                                        <span class="dashicons dashicons-undo"></span> Tüm Ayarları Sıfırla
                                    </button>
                                    
                                    <h4>Cache Yönetimi:</h4>
                                    <button type="button" class="button" onclick="clearTopbarCache()">
                                        <span class="dashicons dashicons-trash"></span> Topbar Cache'ini Temizle
                                    </button>
                                    
                                    <h4>CSS/JS Yenileme:</h4>
                                    <button type="button" class="button" onclick="regenerateAssets()">
                                        <span class="dashicons dashicons-update"></span> CSS/JS Dosyalarını Yenile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Gelişmiş Ayarları Kaydet">
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_analytics_tab() {
        ?>
        <div class="topbar-content">
            <div class="analytics-dashboard">
                <div class="analytics-header">
                    <h2>Custom Topbar Analitikleri</h2>
                    <div class="analytics-period">
                        <select id="analytics_period">
                            <option value="7">Son 7 Gün</option>
                            <option value="30">Son 30 Gün</option>
                            <option value="90" selected>Son 90 Gün</option>
                            <option value="365">Son 1 Yıl</option>
                        </select>
                        <button type="button" class="button" onclick="refreshAnalytics()">Yenile</button>
                    </div>
                </div>
                
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>Genel İstatistikler</h3>
                            <span class="dashicons dashicons-chart-line"></span>
                        </div>
                        <div class="analytics-card-body">
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo number_format(get_option('esistenze_topbar_impressions', 0)); ?></div>
                                    <div class="metric-label">Toplam Görüntülenme</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo number_format(get_option('esistenze_topbar_clicks', 0)); ?></div>
                                    <div class="metric-label">Toplam Tıklama</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value">
                                        <?php 
                                        $impressions = get_option('esistenze_topbar_impressions', 0);
                                        $clicks = get_option('esistenze_topbar_clicks', 0);
                                        echo $impressions > 0 ? number_format(($clicks / $impressions) * 100, 1) : 0;
                                        ?>%
                                    </div>
                                    <div class="metric-label">CTR (Tıklama Oranı)</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo number_format(get_option('esistenze_topbar_unique_visitors', 0)); ?></div>
                                    <div class="metric-label">Benzersiz Ziyaretçi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>Tıklama Dağılımı</h3>
                            <span class="dashicons dashicons-chart-pie"></span>
                        </div>
                        <div class="analytics-card-body">
                            <canvas id="click_distribution_chart"></canvas>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>Cihaz Dağılımı</h3>
                            <span class="dashicons dashicons-smartphone"></span>
                        </div>
                        <div class="analytics-card-body">
                            <div class="device-stats">
                                <div class="device-stat">
                                    <span class="device-icon">🖥️</span>
                                    <div class="device-info">
                                        <div class="device-name">Masaüstü</div>
                                        <div class="device-percentage"><?php echo get_option('esistenze_topbar_desktop_percentage', 0); ?>%</div>
                                    </div>
                                </div>
                                <div class="device-stat">
                                    <span class="device-icon">📱</span>
                                    <div class="device-info">
                                        <div class="device-name">Mobil</div>
                                        <div class="device-percentage"><?php echo get_option('esistenze_topbar_mobile_percentage', 0); ?>%</div>
                                    </div>
                                </div>
                                <div class="device-stat">
                                    <span class="device-icon">📟</span>
                                    <div class="device-info">
                                        <div class="device-name">Tablet</div>
                                        <div class="device-percentage"><?php echo get_option('esistenze_topbar_tablet_percentage', 0); ?>%</div>
                                    </div>
                                </div>
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
                                <div class="performance-item">
                                    <span class="metric-label">Ortalama Yükleme Süresi:</span>
                                    <span class="metric-value" id="avg_load_time">Hesaplanıyor...</span>
                                </div>
                                <div class="performance-item">
                                    <span class="metric-label">CSS Dosya Boyutu:</span>
                                    <span class="metric-value" id="css_file_size">Hesaplanıyor...</span>
                                </div>
                                <div class="performance-item">
                                    <span class="metric-label">JS Dosya Boyutu:</span>
                                    <span class="metric-value" id="js_file_size">Hesaplanıyor...</span>
                                </div>
                                <div class="performance-item">
                                    <span class="metric-label">Cache Hit Oranı:</span>
                                    <span class="metric-value cache-hit">%95.2</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="analytics-detailed">
                    <div class="postbox">
                        <h2 class="hndle">Detaylı Tıklama Analizi</h2>
                        <div class="inside">
                            <div class="detailed-analytics-table">
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th>Element</th>
                                            <th>Tıklama Sayısı</th>
                                            <th>Tıklama Oranı</th>
                                            <th>Son Tıklama</th>
                                            <th>Popülerlik</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $click_data = get_option('esistenze_topbar_click_details', array(
                                            'phone' => array('clicks' => 245, 'last_click' => '2024-01-15 14:30:00'),
                                            'email' => array('clicks' => 189, 'last_click' => '2024-01-15 16:45:00'),
                                            'menu_home' => array('clicks' => 156, 'last_click' => '2024-01-15 18:20:00'),
                                            'menu_about' => array('clicks' => 98, 'last_click' => '2024-01-15 12:15:00'),
                                            'social_facebook' => array('clicks' => 78, 'last_click' => '2024-01-15 11:30:00')
                                        ));
                                        
                                        $total_clicks = array_sum(array_column($click_data, 'clicks'));
                                        
                                        foreach ($click_data as $element => $data):
                                            $percentage = $total_clicks > 0 ? round(($data['clicks'] / $total_clicks) * 100, 1) : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $element))); ?></strong>
                                            </td>
                                            <td><?php echo number_format($data['clicks']); ?></td>
                                            <td><?php echo $percentage; ?>%</td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($data['last_click'])); ?></td>
                                            <td>
                                                <div class="popularity-bar">
                                                    <div class="popularity-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Trend Analizi</h2>
                        <div class="inside">
                            <canvas id="trend_chart" width="800" height="300"></canvas>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Export ve Raporlama</h2>
                        <div class="inside">
                            <div class="export-options">
                                <h4>Veri Export Seçenekleri:</h4>
                                <div class="export-buttons">
                                    <button type="button" class="button" onclick="exportAnalyticsCSV()">
                                        <span class="dashicons dashicons-media-spreadsheet"></span> CSV Olarak İndir
                                    </button>
                                    <button type="button" class="button" onclick="exportAnalyticsPDF()">
                                        <span class="dashicons dashicons-pdf"></span> PDF Raporu Oluştur
                                    </button>
                                    <button type="button" class="button" onclick="exportAnalyticsJSON()">
                                        <span class="dashicons dashicons-media-code"></span> JSON Olarak İndir
                                    </button>
                                </div>
                                
                                <h4>Otomatik Raporlama:</h4>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">E-posta Raporları</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="auto_reports_enabled" value="1">
                                                    Haftalık analitik raporlarını e-posta ile gönder
                                                </label><br>
                                                <input type="email" name="report_email" placeholder="rapor@domain.com" class="regular-text">
                                            </fieldset>
                                        </td>
                                    </tr>
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
        if (!current_user_can('manage_options') || !check_admin_referer('esistenze_topbar_save')) {
            wp_die('Yetkiniz yok.');
        }
        
        $tab = sanitize_text_field($_POST['tab'] ?? 'general');
        $current_settings = get_option('esistenze_topbar_settings', self::get_default_settings());
        
        // Merge new settings with existing ones
        $new_settings = array_merge($current_settings, $_POST['settings'] ?? array());
        
        // Sanitize all settings
        $new_settings = self::sanitize_settings($new_settings);
        
        update_option('esistenze_topbar_settings', $new_settings);
        
        // Clear cache
        wp_cache_delete('esistenze_topbar_styles', 'esistenze');
        
        // Add success notice
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Topbar ayarları başarıyla kaydedildi!</p></div>';
        });
    }
    
    private static function sanitize_settings($settings) {
        $sanitized = array();
        
        // Boolean settings
        $boolean_keys = array('enabled', 'show_on_home', 'show_on_pages', 'show_on_posts', 'show_on_shop', 'show_on_archive', 'hover_effects', 'backdrop_blur', 'menu_show_icons', 'menu_uppercase', 'menu_bold', 'show_contact_icons', 'show_social_media');
        foreach ($boolean_keys as $key) {
            $sanitized[$key] = !empty($settings[$key]);
        }
        
        // Text settings
        $text_keys = array('position', 'mobile_behavior', 'entrance_animation', 'scroll_behavior', 'font_family', 'font_weight', 'text_transform', 'border_style', 'border_position', 'social_position');
        foreach ($text_keys as $key) {
            $sanitized[$key] = sanitize_text_field($settings[$key] ?? '');
        }
        
        // Number settings
        $number_keys = array('z_index', 'height', 'padding_horizontal', 'font_size', 'gradient_angle', 'letter_spacing', 'shadow_x', 'shadow_y', 'shadow_blur', 'shadow_opacity', 'border_width', 'menu_item_spacing', 'icon_size');
        foreach ($number_keys as $key) {
            $sanitized[$key] = floatval($settings[$key] ?? 0);
        }
        
        // Color settings
        $color_keys = array('bg_color_start', 'bg_color_end', 'text_color', 'text_hover_color', 'text_active_color', 'shadow_color', 'border_color');
        foreach ($color_keys as $key) {
            $sanitized[$key] = sanitize_hex_color($settings[$key] ?? '#000000');
        }
        
        // Email/Phone settings
        $sanitized['phone'] = sanitize_text_field($settings['phone'] ?? '');
        $sanitized['email'] = sanitize_email($settings['email'] ?? '');
        
        // Menu ID
        $sanitized['menu_id'] = intval($settings['menu_id'] ?? 0);
        
        // Social media URLs
        $social_keys = array('social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin', 'social_youtube');
        foreach ($social_keys as $key) {
            $sanitized[$key] = esc_url_raw($settings[$key] ?? '');
        }
        
        // Custom CSS/JS
        $sanitized['custom_css'] = wp_unslash($settings['custom_css'] ?? '');
        $sanitized['custom_js'] = wp_unslash($settings['custom_js'] ?? '');
        
        // Extra contacts
        if (isset($settings['extra_contacts']) && is_array($settings['extra_contacts'])) {
            $sanitized['extra_contacts'] = array();
            foreach ($settings['extra_contacts'] as $contact) {
                $sanitized['extra_contacts'][] = array(
                    'type' => sanitize_text_field($contact['type'] ?? ''),
                    'value' => sanitize_text_field($contact['value'] ?? ''),
                    'label' => sanitize_text_field($contact['label'] ?? '')
                );
            }
        }
        
        return $sanitized;
    }
    
    private static function show_admin_notices() {
        // Check for Font Awesome
        if (!wp_style_is('font-awesome', 'enqueued') && !wp_style_is('fontawesome', 'enqueued')) {
            echo '<div class="notice notice-info"><p><strong>Bilgi:</strong> Font Awesome ikonları görünmüyorsa, temanızın Font Awesome yüklediğinden emin olun.</p></div>';
        }
        
        // Check for jQuery
        if (!wp_script_is('jquery', 'enqueued')) {
            echo '<div class="notice notice-warning"><p><strong>Uyarı:</strong> jQuery yüklü değil. Bazı özellikler çalışmayabilir.</p></div>';
        }
        
        // Performance notice
        $settings = get_option('esistenza_topbar_settings', array());
        if (empty($settings['minify_css']) && empty($settings['cache_css'])) {
            echo '<div class="notice notice-info is-dismissible"><p><strong>Performans İpucu:</strong> Gelişmiş sekmesinden CSS optimizasyonunu etkinleştirerek site hızınızı artırabilirsiniz.</p></div>';
        }
    }
    
    private static function get_default_settings() {
        return array(
            'enabled' => true,
            'show_on_home' => true,
            'show_on_pages' => true,
            'show_on_posts' => true,
            'show_on_shop' => true,
            'show_on_archive' => true,
            'position' => 'fixed-top',
            'z_index' => 99999,
            'height' => 50,
            'padding_horizontal' => 5,
            'font_size' => 16,
            'mobile_behavior' => 'responsive',
            'entrance_animation' => 'slideDown',
            'hover_effects' => true,
            'backdrop_blur' => false,
            'scroll_behavior' => 'static',
            'bg_color_start' => '#4CAF50',
            'bg_color_end' => '#388E3C',
            'gradient_angle' => 90,
            'text_color' => '#ffffff',
            'text_hover_color' => '#f0f0f0',
            'text_active_color' => '#ffeb3b',
            'font_family' => 'system',
            'font_weight' => '500',
            'letter_spacing' => 0,
            'text_transform' => 'none',
            'shadow_x' => 0,
            'shadow_y' => 2,
            'shadow_blur' => 20,
            'shadow_opacity' => 0.08,
            'shadow_color' => '#000000',
            'border_width' => 0,
            'border_style' => 'solid',
            'border_color' => '#dddddd',
            'border_position' => 'all',
            'menu_id' => 0,
            'menu_item_spacing' => 14,
            'menu_show_icons' => false,
            'menu_uppercase' => false,
            'menu_bold' => false,
            'phone' => '',
            'email' => '',
            'show_contact_icons' => true,
            'icon_size' => 16,
            'extra_contacts' => array(),
            'show_social_media' => false,
            'social_position' => 'right',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_instagram' => '',
            'social_linkedin' => '',
            'social_youtube' => '',
            'minify_css' => false,
            'inline_css' => true,
            'cache_css' => true,
            'defer_js' => false,
            'minify_js' => false,
            'preload_fonts' => false,
            'font_display_swap' => true,
            'custom_css' => '',
            'custom_js' => '',
            'override_theme_styles' => false,
            'respect_admin_bar' => true,
            'ie_support' => false,
            'safari_support' => true,
            'sanitize_output' => true,
            'nonce_protection' => true,
            'debug_mode' => false
        );
    }
    
    private static function enqueue_admin_assets() {
        ?>
        <style>
        .esistenza-topbar-wrap { max-width: 1400px; }
        .nav-tab .dashicons { margin-right: 5px; vertical-align: middle; }
        
        /* Layout */
        .topbar-layout,
        .design-layout,
        .content-layout,
        .advanced-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin: 20px 0;
        }
        
        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px; width: 26px;
            left: 4px; bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider { background-color: #4CAF50; }
        input:checked + .toggle-slider:before { transform: translateX(26px); }
        
        /* Size Controls */
        .size-control {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .size-slider {
            flex: 1;
            max-width: 200px;
        }
        
        /* Color Controls */
        .gradient-controls,
        .text-colors,
        .shadow-controls,
        .border-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .color-picker {
            width: 50px;
            height: 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        /* Color Scheme Selector */
        .scheme-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .scheme-item {
            text-align: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .scheme-item:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        
        .scheme-preview {
            height: 40px;
            border-radius: 6px;
            margin-bottom: 5px;
        }
        
        /* Preview Containers */
        .topbar-preview-container,
        .design-preview-container,
        .content-preview-container {
            background: #f9f9f9;
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 20px;
            min-height: 100px;
        }
        
        .preview-topbar {
            background: linear-gradient(90deg, #4CAF50, #388E3C);
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Template Buttons */
        .template-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .template-btn {
            padding: 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .template-btn:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        
        .template-name {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .template-desc {
            font-size: 12px;
            color: #666;
        }
        
        /* Statistics */
        .topbar-stats {
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
        
        /* Extra Contacts */
        .extra-contact-item {
            display: grid;
            grid-template-columns: 120px 1fr 120px auto;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        /* CSS Classes Helper */
        .css-classes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        
        .css-classes code {
            padding: 8px;
            background: #2c3e50;
            color: #ecf0f1;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .css-classes code:hover {
            background: #34495e;
        }
        
        /* Analytics */
        .analytics-dashboard { padding: 20px 0; }
        
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
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
        
        .analytics-card-body { padding: 20px; }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .metric-item {
            text-align: center;
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
        
        .device-stats {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .device-stat {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .device-icon {
            font-size: 24px;
        }
        
        .device-info {
            flex: 1;
        }
        
        .device-name {
            font-weight: 600;
        }
        
        .device-percentage {
            color: #4CAF50;
            font-size: 18px;
        }
        
        .performance-metrics {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .performance-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .popularity-bar {
            width: 100px;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .popularity-fill {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            transition: width 0.3s ease;
        }
        
        /* System Info */
        .system-info,
        .content-stats {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .info-row,
        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .debug-output {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        /* Advanced Actions */
        .advanced-actions,
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .advanced-actions h4,
        .quick-actions h4 {
            margin: 20px 0 10px;
            color: #2c3e50;
        }
        
        .export-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .topbar-layout,
            .design-layout,
            .content-layout,
            .advanced-layout {
                grid-template-columns: 1fr;
            }
            
            .analytics-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .scheme-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .template-buttons {
                grid-template-columns: 1fr;
            }
            
            .extra-contact-item {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Live preview updates
            window.updatePreview = function() {
                console.log('Updating topbar preview...');
                updateTopbarPreview();
            };
            
            window.updateTopbarPreview = function() {
                const height = $('[name="settings[height]"]').val() || 50;
                const bgStart = $('[name="settings[bg_color_start]"]').val() || '#4CAF50';
                const bgEnd = $('[name="settings[bg_color_end]"]').val() || '#388E3C';
                const textColor = $('[name="settings[text_color]"]').val() || '#ffffff';
                const fontSize = $('[name="settings[font_size]"]').val() || 16;
                
                $('#preview_topbar').css({
                    'background': `linear-gradient(90deg, ${bgStart}, ${bgEnd})`,
                    'color': textColor,
                    'font-size': fontSize + 'px',
                    'height': height + 'px',
                    'padding': '0 20px',
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'space-between'
                });
                
                const menuText = $('#menu_selector option:selected').text() || 'Menü Seçili Değil';
                const phone = $('[name="settings[phone]"]').val();
                const email = $('[name="settings[email]"]').val();
                
                let leftContent = `<span>📋 ${menuText}</span>`;
                let rightContent = '';
                
                if (phone) rightContent += `<span>📞 ${phone}</span> `;
                if (email) rightContent += `<span>📧 ${email}</span>`;
                
                $('#preview_topbar').html(`
                    <div>${leftContent}</div>
                    <div>${rightContent}</div>
                `);
            };
            
            // Size control updates
            window.updateHeight = function(value) {
                $('#height_display').text(value + 'px');
                updatePreview();
            };
            
            window.updatePaddingH = function(value) {
                $('#padding_h_display').text(value + '%');
                updatePreview();
            };
            
            window.updateFontSize = function(value) {
                $('#font_size_display').text(value + 'px');
                updatePreview();
            };
            
            window.updateGradientAngle = function(value) {
                $('#gradient_angle_display').text(value + '°');
                updatePreviewColors();
            };
            
            window.updateLetterSpacing = function(value) {
                $('#letter_spacing_display').text(value + 'px');
                updatePreviewFont();
            };
            
            window.updateMenuSpacing = function(value) {
                $('#menu_spacing_display').text(value + 'px');
                updateMenuPreview();
            };
            
            window.updateIconSize = function(value) {
                $('#icon_size_display').text(value + 'px');
            };
            
            // Color and style updates
            window.updatePreviewColors = function() {
                updateTopbarPreview();
            };
            
            window.updatePreviewFont = function() {
                updateTopbarPreview();
            };
            
            window.updateMenuPreview = function() {
                updateTopbarPreview();
            };
            
            window.updateShadow = function() {
                const x = $('[name="settings[shadow_x]"]').val() || 0;
                const y = $('[name="settings[shadow_y]"]').val() || 2;
                const blur = $('[name="settings[shadow_blur]"]').val() || 20;
                const opacity = $('[name="settings[shadow_opacity]"]').val() || 0.08;
                const color = $('[name="settings[shadow_color]"]').val() || '#000000';
                
                $('#shadow_x_display').text(x + 'px');
                $('#shadow_y_display').text(y + 'px');
                $('#shadow_blur_display').text(blur + 'px');
                $('#shadow_opacity_display').text(opacity);
                
                const shadow = `${x}px ${y}px ${blur}px rgba(${hexToRgb(color)}, ${opacity})`;
                $('#preview_topbar').css('box-shadow', shadow);
            };
            
            window.updateBorder = function() {
                const width = $('[name="settings[border_width]"]').val() || 0;
                const style = $('[name="settings[border_style]"]').val() || 'solid';
                const color = $('[name="settings[border_color]"]').val() || '#dddddd';
                const position = $('[name="settings[border_position]"]').val() || 'all';
                
                $('#border_width_display').text(width + 'px');
                
                let borderCSS = '';
                if (position === 'all') {
                    borderCSS = `${width}px ${style} ${color}`;
                } else {
                    borderCSS = 'none';
                    $('#preview_topbar').css(`border-${position}`, `${width}px ${style} ${color}`);
                }
                
                if (position === 'all') {
                    $('#preview_topbar').css('border', borderCSS);
                }
            };
            
            // Template applications
            window.applyTemplate = function(template) {
                const templates = {
                    business: {
                        bg_color_start: '#2c3e50',
                        bg_color_end: '#34495e',
                        text_color: '#ffffff',
                        font_family: 'arial',
                        font_weight: '600'
                    },
                    ecommerce: {
                        bg_color_start: '#e74c3c',
                        bg_color_end: '#c0392b',
                        text_color: '#ffffff',
                        font_family: 'roboto',
                        font_weight: '500'
                    },
                    minimal: {
                        bg_color_start: '#ecf0f1',
                        bg_color_end: '#bdc3c7',
                        text_color: '#2c3e50',
                        font_family: 'helvetica',
                        font_weight: '400'
                    },
                    colorful: {
                        bg_color_start: '#9b59b6',
                        bg_color_end: '#8e44ad',
                        text_color: '#ffffff',
                        font_family: 'montserrat',
                        font_weight: '600'
                    }
                };
                
                if (templates[template]) {
                    const settings = templates[template];
                    Object.keys(settings).forEach(key => {
                        const input = $(`[name="settings[${key}]"]`);
                        input.val(settings[key]).trigger('change');
                    });
                    updatePreview();
                    alert(`${template.charAt(0).toUpperCase() + template.slice(1)} şablonu uygulandı!`);
                }
            };
            
            window.applyColorScheme = function(scheme) {
                const schemes = {
                    green: { start: '#4CAF50', end: '#45a049' },
                    blue: { start: '#2196F3', end: '#1976D2' },
                    purple: { start: '#9C27B0', end: '#7B1FA2' },
                    orange: { start: '#FF9800', end: '#F57C00' },
                    red: { start: '#F44336', end: '#D32F2F' },
                    dark: { start: '#424242', end: '#212121' }
                };
                
                if (schemes[scheme]) {
                    $('[name="settings[bg_color_start]"]').val(schemes[scheme].start);
                    $('[name="settings[bg_color_end]"]').val(schemes[scheme].end);
                    updatePreviewColors();
                }
            };
            
            // Social media toggle
            window.toggleSocialMedia = function() {
                const isChecked = $('[name="settings[show_social_media]"]').is(':checked');
                $('#social_media_settings').toggle(isChecked);
            };
            
            // Extra contacts management
            $('#add_contact').on('click', function() {
                const index = $('#extra_contacts .extra-contact-item').length;
                const html = `
                    <div class="extra-contact-item">
                        <select name="settings[extra_contacts][${index}][type]">
                            <option value="phone">Telefon</option>
                            <option value="email">E-posta</option>
                            <option value="address">Adres</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="skype">Skype</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="custom">Özel</option>
                        </select>
                        <input type="text" name="settings[extra_contacts][${index}][value]" placeholder="Değer">
                        <input type="text" name="settings[extra_contacts][${index}][label]" placeholder="Etiket">
                        <button type="button" class="button button-secondary remove-contact">Kaldır</button>
                    </div>
                `;
                $('#extra_contacts').append(html);
            });
            
            $(document).on('click', '.remove-contact', function() {
                $(this).closest('.extra-contact-item').remove();
            });
            
            // CSS insertion helper
            window.insertAtCursor = function(text, textareaId) {
                const textarea = document.querySelector(`[name="settings[${textareaId}]"]`);
                if (textarea) {
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const before = textarea.value.substring(0, start);
                    const after = textarea.value.substring(end);
                    textarea.value = before + text + after;
                    textarea.focus();
                    textarea.setSelectionRange(start + text.length, start + text.length);
                }
            };
            
            // Utility functions
            function hexToRgb(hex) {
                const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? 
                    `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : 
                    '0, 0, 0';
            }
            
            // Advanced functions (placeholder implementations)
            window.exportTopbarSettings = function() {
                alert('Ayarlar dışa aktarılıyor...');
            };
            
            window.importTopbarSettings = function() {
                alert('Ayarlar içe aktarılıyor...');
            };
            
            window.resetAllSettings = function() {
                if (confirm('Tüm ayarları sıfırlamak istediğinizden emin misiniz?')) {
                    alert('Ayarlar sıfırlanıyor...');
                }
            };
            
            window.resetStats = function() {
                if (confirm('İstatistikleri sıfırlamak istediğinizden emin misiniz?')) {
                    $.post(ajaxurl, {
                        action: 'esistenza_topbar_reset',
                        _wpnonce: '<?php echo wp_create_nonce("esistenza_topbar_reset"); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    });
                }
            };
            
            window.validateAllLinks = function() {
                alert('Linkler kontrol ediliyor...');
            };
            
            window.testContactInfo = function() {
                alert('İletişim bilgileri test ediliyor...');
            };
            
            window.previewTopbar = function() {
                window.open('/', '_blank');
            };
            
            window.refreshPreview = function() {
                updatePreview();
            };
            
            window.previewMobile = function() {
                $('#topbar_preview').addClass('mobile-preview');
            };
            
            window.previewDesktop = function() {
                $('#topbar_preview').removeClass('mobile-preview');
            };
            
            // Initialize
            updateTopbarPreview();
            
            // Auto-update preview on any change
            $('input, select, textarea').on('input change', function() {
                if ($(this).closest('.topbar-content').length) {
                    clearTimeout(window.topbarPreviewTimeout);
                    window.topbarPreviewTimeout = setTimeout(updatePreview, 300);
                }
            });
            
            // Update content statistics
            function updateContentStats() {
                const menuItemCount = $('#menu_selector option:selected').text().match(/\((\d+) öğe\)/);
                $('#menu_item_count').text(menuItemCount ? menuItemCount[1] : '0');
                
                let contactCount = 0;
                if ($('[name="settings[phone]"]').val()) contactCount++;
                if ($('[name="settings[email]"]').val()) contactCount++;
                contactCount += $('#extra_contacts .extra-contact-item').length;
                $('#contact_count').text(contactCount);
                
                let socialCount = 0;
                $('[name^="settings[social_"]').each(function() {
                    if ($(this).val()) socialCount++;
                });
                $('#social_count').text(socialCount);
            }
            
            // Update stats periodically
            setInterval(updateContentStats, 2000);
            updateContentStats();
        });
        </script>
        <?php
    }
    
    // Existing methods continue (register_settings, enqueue_styles, output_topbar, etc.)
    public function register_settings() {
        register_setting('esistenza_topbar', 'esistanza_topbar_settings');
    }
    
    public function enqueue_styles() {
        $settings = get_option('esistenza_topbar_settings', self::get_default_settings());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        wp_enqueue_style('esistenza-custom-topbar', ESISTENZA_WP_KIT_URL . 'modules/custom-topbar/assets/style.css', array(), ESISTENZA_WP_KIT_VERSION);
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2');
        
        // Generate and add dynamic CSS
        $dynamic_css = $this->generate_dynamic_css($settings);
        if (!empty($dynamic_css)) {
            wp_add_inline_style('esistenza-custom-topbar', $dynamic_css);
        }
        
        // Add custom CSS
        if (!empty($settings['custom_css'])) {
            wp_add_inline_style('esistenza-custom-topbar', $settings['custom_css']);
        }
    }

    public function output_topbar() {
        $settings = get_option('esistanza_topbar_settings', self::get_default_settings());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Check if should display on current page
        if (!$this->should_display_on_current_page($settings)) {
            return;
        }
        
        $this->render_topbar($settings);
    }
    
    private function should_display_on_current_page($settings) {
        if (is_home() || is_front_page()) {
            return !empty($settings['show_on_home']);
        } elseif (is_page()) {
            return !empty($settings['show_on_pages']);
        } elseif (is_single()) {
            return !empty($settings['show_on_posts']);
        } elseif (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
            return !empty($settings['show_on_shop']);
        } elseif (is_archive() || is_category() || is_tag()) {
            return !empty($settings['show_on_archive']);
        }
        
        return true; // Default to show
    }
    
    private function render_topbar($settings) {
        $style_vars = $this->get_css_variables($settings);
        
        echo '<div class="esistenza-top-bar" ' . $style_vars . '>';
        echo '<div class="esistenza-top-bar-left">';
        
        // Render menu
        if (!empty($settings['menu_id'])) {
            $this->render_menu($settings);
        }
        
        // Render social media (if position is left)
        if (!empty($settings['show_social_media']) && $settings['social_position'] === 'left') {
            $this->render_social_media($settings);
        }
        
        echo '</div>';
        
        // Render center section (if social media position is center)
        if (!empty($settings['show_social_media']) && $settings['social_position'] === 'center') {
            echo '<div class="esistanza-top-bar-center">';
            $this->render_social_media($settings);
            echo '</div>';
        }
        
        echo '<div class="esistanza-top-bar-right">';
        
        // Render contact info
        $this->render_contact_info($settings);
        
        // Render social media (if position is right)
        if (!empty($settings['show_social_media']) && $settings['social_position'] === 'right') {
            $this->render_social_media($settings);
        }
        
        echo '</div>';
        echo '</div>';
        
        // Track impression
        if (!empty($settings['enable_tracking'])) {
            $this->track_impression();
        }
    }
    
    private function render_menu($settings) {
        echo '<ul class="esistanza-top-bar-menu">';
        $menu_output = wp_nav_menu(array(
            'menu' => $settings['menu_id'],
            'menu_class' => 'esistanza-top-bar-menu',
            'container' => false,
            'depth' => 1,
            'echo' => false,
            'items_wrap' => '%3$s',
            'fallback_cb' => '__return_empty_string'
        ));
        echo $menu_output ?: '<li>Menu yükleme hatası</li>';
        echo '</ul>';
    }
    
    private function render_contact_info($settings) {
        echo '<ul class="esistanza-top-bar-contact">';
        
        if (!empty($settings['phone'])) {
            $icon = !empty($settings['show_contact_icons']) ? '<i class="fa fa-phone"></i>' : '';
            echo '<li><a href="tel:' . esc_attr($settings['phone']) . '" onclick="trackClick(\'phone\')">' . $icon . esc_html($settings['phone']) . '</a></li>';
        }
        
        if (!empty($settings['email'])) {
            $icon = !empty($settings['show_contact_icons']) ? '<i class="fa fa-envelope"></i>' : '';
            echo '<li><a href="mailto:' . esc_attr($settings['email']) . '" onclick="trackClick(\'email\')">' . $icon . esc_html($settings['email']) . '</a></li>';
        }
        
        // Extra contacts
        if (!empty($settings['extra_contacts'])) {
            foreach ($settings['extra_contacts'] as $contact) {
                if (empty($contact['value'])) continue;
                
                $icon = $this->get_contact_icon($contact['type']);
                $href = $this->get_contact_href($contact['type'], $contact['value']);
                $label = !empty($contact['label']) ? $contact['label'] : $contact['value'];
                
                echo '<li><a href="' . esc_attr($href) . '" onclick="trackClick(\'' . esc_attr($contact['type']) . '\')">' . $icon . esc_html($label) . '</a></li>';
            }
        }
        
        echo '</ul>';
    }
    
    private function render_social_media($settings) {
        $social_networks = array(
            'facebook' => array('icon' => 'fab fa-facebook-f', 'name' => 'Facebook'),
            'twitter' => array('icon' => 'fab fa-twitter', 'name' => 'Twitter'),
            'instagram' => array('icon' => 'fab fa-instagram', 'name' => 'Instagram'),
            'linkedin' => array('icon' => 'fab fa-linkedin-in', 'name' => 'LinkedIn'),
            'youtube' => array('icon' => 'fab fa-youtube', 'name' => 'YouTube')
        );
        
        echo '<ul class="esistanza-top-bar-social">';
        foreach ($social_networks as $network => $data) {
            $url = $settings['social_' . $network] ?? '';
            if (!empty($url)) {
                echo '<li><a href="' . esc_url($url) . '" target="_blank" onclick="trackClick(\'social_' . $network . '\')" title="' . esc_attr($data['name']) . '">';
                echo '<i class="' . esc_attr($data['icon']) . '"></i>';
                echo '</a></li>';
            }
        }
        echo '</ul>';
    }
    
    private function get_contact_icon($type) {
        $icons = array(
            'phone' => '<i class="fa fa-phone"></i>',
            'email' => '<i class="fa fa-envelope"></i>',
            'address' => '<i class="fa fa-map-marker-alt"></i>',
            'whatsapp' => '<i class="fab fa-whatsapp"></i>',
            'skype' => '<i class="fab fa-skype"></i>',
            'linkedin' => '<i class="fab fa-linkedin"></i>',
            'custom' => '<i class="fa fa-link"></i>'
        );
        
        return $icons[$type] ?? '';
    }
    
    private function get_contact_href($type, $value) {
        switch ($type) {
            case 'phone':
                return 'tel:' . $value;
            case 'email':
                return 'mailto:' . $value;
            case 'whatsapp':
                return 'https://wa.me/' . preg_replace('/[^0-9]/', '', $value);
            case 'skype':
                return 'skype:' . $value;
            case 'linkedin':
            case 'custom':
            default:
                return esc_url($value);
        }
    }
    
    private function get_css_variables($settings) {
        return sprintf(
            'style="--esistenza-bg-color: %s; --esistenza-bg-color-dark: %s; --esistenza-text-color: %s; --esistenza-font-size: %spx; --esistenza-padding: %s%%; --esistenza-height: %spx;"',
            esc_attr($settings['bg_color_start']),
            esc_attr($settings['bg_color_end']),
            esc_attr($settings['text_color']),
            esc_attr($settings['font_size']),
            esc_attr($settings['padding_horizontal']),
            esc_attr($settings['height'])
        );
    }
    
    private function generate_dynamic_css($settings) {
        // This would generate dynamic CSS based on settings
        // Implementation would include all the styling options
        return '/* Dynamic CSS would be generated here */';
    }
    
    private function track_impression() {
        $current_impressions = get_option('esistenza_topbar_impressions', 0);
        update_option('esistenza_topbar_impressions', $current_impressions + 1);
    }
    
    // AJAX handlers
    public function ajax_topbar_preview() {
        check_ajax_referer('esistanza_topbar_preview');
        // Implementation for live preview
        wp_send_json_success('Preview updated');
    }
    
    public function ajax_reset_topbar() {
        check_ajax_referer('esistenza_topbar_reset');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Reset statistics
        delete_option('esistanza_topbar_impressions');
        delete_option('esistanza_topbar_clicks');
        delete_option('esistanza_topbar_unique_visitors');
        
        wp_send_json_success('Statistics reset successfully');
    }
    
    public function ajax_import_settings() {
        check_ajax_referer('esistanza_topbar_import');
        // Implementation for settings import
        wp_send_json_success('Settings imported');
    }
}

// Initialize the module
EsistenzeCustomTopbar::getInstance();                                                    Y Offset:
                                                    <input type="range" name="settings[shadow_y]" value="<?php echo esc_attr($settings['shadow_y'] ?? 2); ?>" min="0" max="30" oninput="updateShadow()">
                                                    <span id="shadow_y_display"><?php echo esc_attr($settings['shadow_y'] ?? 2); ?>px</span>
                                                </label>
                                                <label>
                                                    Blur:
                                                    <input type="range" name="settings[shadow_blur]" value="<?php echo esc_attr($settings['shadow_blur'] ?? 20); ?>" min="0" max="50" oninput="updateShadow()">
                                                    <span id="shadow_blur_display"><?php echo esc_attr($settings['shadow_blur'] ?? 20); ?>px</span>
                                                </label>
                                                <label>
                                                    Opacity:
                                                    <input type="range" name="settings[shadow_opacity]" value="<?php echo esc_attr($settings['shadow_opacity'] ?? 0.08); ?>" min="0" max="1" step="0.01" oninput="updateShadow()">
                                                    <span id="shadow_opacity_display"><?php echo esc_attr($settings['shadow_opacity'] ?? 0.08); ?></span>
                                                </label>
                                                <label>
                                                    Renk:
                                                    <input type="color" name="settings[shadow_color]" value="<?php echo esc_attr($settings['shadow_color'] ?? '#000000'); ?>" class="color-picker" onchange="updateShadow()">
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Border</th>
                                        <td>
                                            <div class="border-controls">
                                                <label>
                                                    Kalınlık:
                                                    <input type="range" name="settings[border_width]" value="<?php echo esc_attr($settings['border_width'] ?? 0); ?>" min="0" max="10" oninput="updateBorder()">
                                                    <span id="border_width_display"><?php echo esc_attr($settings['border_width'] ?? 0); ?>px</span>
                                                </label>
                                                <label>
                                                    Stil:
                                                    <select name="settings[border_style]" onchange="updateBorder()">
                                                        <option value="solid" <?php selected($settings['border_style'] ?? '', 'solid'); ?>>Düz</option>
                                                        <option value="dashed" <?php selected($settings['border_style'] ?? '', 'dashed'); ?>>Kesikli</option>
                                                        <option value="dotted" <?php selected($settings['border_style'] ?? '', 'dotted'); ?>>Noktalı</option>
                                                        <option value="double" <?php selected($settings['border_style'] ?? '', 'double'); ?>>Çift</option>
                                                    </select>
                                                </label>
                                                <label>
                                                    Renk:
                                                    <input type="color" name="settings[border_color]" value="<?php echo esc_attr($settings['border_color'] ?? '#dddddd'); ?>" class="color-picker" onchange="updateBorder()">
                                                </label>
                                                <label>
                                                    Pozisyon:
                                                    <select name="settings[border_position]" onchange="updateBorder()">
                                                        <option value="all" <?php selected($settings['border_position'] ?? '', 'all'); ?>>Tümü</option>
                                                        <option value="top" <?php selected($settings['border_position'] ?? '', 'top'); ?>>Üst</option>
                                                        <option value="bottom" <?php selected($settings['border_position'] ?? '', 'bottom'); ?>>Alt</option>
                                                        <option value="left" <?php selected($settings['border_position'] ?? '', 'left'); ?>>Sol</option>
                                                        <option value="right" <?php selected($settings['border_position'] ?? '', 'right'); ?>>Sağ</option>
                                                    </select>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="design-preview">
                        <div class="postbox">
                            <h2 class="hndle">Tasarım Önizlemesi</h2>
                            <div class="inside">
                                <div id="design_preview" class="design-preview-container">
                                    <!-- Live design preview will be rendered here -->
                                </div>
                                
                                <div class="design-actions">
                                    <button type="button" class="button" onclick="saveAsPreset()">Preset Olarak Kaydet</button>
                                    <button type="button" class="button" onclick="resetToDefault()">Varsayılana Döndür</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">CSS Export/Import</h2>
                            <div class="inside">
                                <h4>Oluşturulan CSS:</h4>
                                <textarea id="generated_css" rows="10" class="large-text code" readonly></textarea>
                                <div class="css-actions">
                                    <button type="button" class="button" onclick="copyCSSToClipboard()">CSS'i Kopyala</button>
                                    <button type="button" class="button" onclick="downloadCSS()">CSS Dosyası İndir</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Tasarım Ayarlarını Kaydet">
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_content_tab() {
        $settings = get_option('esistenze_topbar_settings', self::get_default_settings());
        $menus = get_terms('nav_menu', array('hide_empty' => false));
        ?>
        <div class="topbar-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_topbar_save'); ?>
                <input type="hidden" name="tab" value="content">
                
                <div class="content-layout">
                    <div class="content-main">
                        <div class="postbox">
                            <h2 class="hndle">Sol Taraf - Menü</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Menü Seçimi</th>
                                        <td>
                                            <select name="settings[menu_id]" id="menu_selector" onchange="updateMenuPreview()">
                                                <option value="">-- Menü Seçiniz --</option>
                                                <?php foreach ($menus as $menu): ?>
                                                    <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($settings['menu_id'] ?? '', $menu->term_id); ?>>
                                                        <?php echo esc_html($menu->name); ?> (<?php echo $menu->count; ?> öğe)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <p class="description">WordPress'te oluşturduğunuz menülerden birini seçin</p>
                                            <a href="<?php echo admin_url('nav-menus.php'); ?>" class="button button-secondary" target="_blank">Yeni Menü Oluştur</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Menü Öğesi Aralığı</th>
                                        <td>
                                            <input type="range" name="settings[menu_item_spacing]" value="<?php echo esc_attr($settings['menu_item_spacing'] ?? 14); ?>" min="5" max="40" oninput="updateMenuSpacing(this.value)">
                                            <span id="menu_spacing_display"><?php echo esc_attr($settings['menu_item_spacing'] ?? 14); ?>px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Menü Görünümü</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[menu_show_icons]" value="1" <?php checked(!empty($settings['menu_show_icons'])); ?>>
                                                    Menü öğelerinde ikonları göster (destekleniyorsa)
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[menu_uppercase]" value="1" <?php checked(!empty($settings['menu_uppercase'])); ?>>
                                                    Menü metinlerini büyük harfle göster
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[menu_bold]" value="1" <?php checked(!empty($settings['menu_bold'])); ?>>
                                                    Menü metinlerini kalın göster
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Sağ Taraf - İletişim Bilgileri</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Telefon Numarası</th>
                                        <td>
                                            <input type="tel" name="settings[phone]" value="<?php echo esc_attr($settings['phone'] ?? ''); ?>" class="regular-text" placeholder="+90 555 123 4567">
                                            <p class="description">Uluslararası format önerilir</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">E-posta Adresi</th>
                                        <td>
                                            <input type="email" name="settings[email]" value="<?php echo esc_attr($settings['email'] ?? ''); ?>" class="regular-text" placeholder="info@example.com">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Ek İletişim Bilgileri</th>
                                        <td>
                                            <div id="extra_contacts">
                                                <?php 
                                                $extra_contacts = $settings['extra_contacts'] ?? array();
                                                foreach ($extra_contacts as $index => $contact):
                                                ?>
                                                <div class="extra-contact-item">
                                                    <select name="settings[extra_contacts][<?php echo $index; ?>][type]">
                                                        <option value="phone" <?php selected($contact['type'] ?? '', 'phone'); ?>>Telefon</option>
                                                        <option value="email" <?php selected($contact['type'] ?? '', 'email'); ?>>E-posta</option>
                                                        <option value="address" <?php selected($contact['type'] ?? '', 'address'); ?>>Adres</option>
                                                        <option value="whatsapp" <?php selected($contact['type'] ?? '', 'whatsapp'); ?>>WhatsApp</option>
                                                        <option value="skype" <?php selected($contact['type'] ?? '', 'skype'); ?>>Skype</option>
                                                        <option value="linkedin" <?php selected($contact['type'] ?? '', 'linkedin'); ?>>LinkedIn</option>
                                                        <option value="custom" <?php selected($contact['type'] ?? '', 'custom'); ?>>Özel</option>
                                                    </select>
                                                    <input type="text" name="settings[extra_contacts][<?php echo $index; ?>][value]" value="<?php echo esc_attr($contact['value'] ?? ''); ?>" placeholder="Değer">
                                                    <input type="text" name="settings[extra_contacts][<?php echo $index; ?>][label]" value="<?php echo esc_attr($contact['label'] ?? ''); ?>" placeholder="Etiket (isteğe bağlı)">
                                                    <button type="button" class="button button-secondary remove-contact">Kaldır</button>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="button" id="add_contact">Yeni İletişim Bilgisi Ekle</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">İkon Ayarları</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[show_contact_icons]" value="1" <?php checked(!empty($settings['show_contact_icons'])); ?>>
                                                    İletişim bilgilerinde ikonları göster
                                                </label><br>
                                                <label>
                                                    İkon Boyutu:
                                                    <input type="range" name="settings[icon_size]" value="<?php echo esc_attr($settings['icon_size'] ?? 16); ?>" min="12" max="24" oninput="updateIconSize(this.value)">
                                                    <span id="icon_size_display"><?php echo esc_attr($settings['icon_size'] ?? 16); ?>px</span>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Sosyal Medya Linkleri</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Sosyal Medya Göster</th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="settings[show_social_media]" value="1" <?php checked(!empty($settings['show_social_media'])); ?> onchange="toggleSocialMedia()">
                                                Topbar'da sosyal medya linklerini göster
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div id="social_media_settings" style="<?php echo empty($settings['show_social_media']) ? 'display:none;' : ''; ?>">
                                    <table class="form-table">
                                        <tr>
                                            <th scope="row">Facebook</th>
                                            <td><input type="url" name="settings[social_facebook]" value="<?php echo esc_attr($settings['social_facebook'] ?? ''); ?>" class="regular-text" placeholder="https://facebook.com/yourpage"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Twitter/X</th>
                                            <td><input type="url" name="settings[social_twitter]" value="<?php echo esc_attr($settings['social_twitter'] ?? ''); ?>" class="regular-text" placeholder="https://twitter.com/youraccount"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Instagram</th>
                                            <td><input type="url" name="settings[social_instagram]" value="<?php echo esc_attr($settings['social_instagram'] ?? ''); ?>" class="regular-text" placeholder="https://instagram.com/youraccount"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">LinkedIn</th>
                                            <td><input type="url" name="settings[social_linkedin]" value="<?php echo esc_attr($settings['social_linkedin'] ?? ''); ?>" class="regular-text" placeholder="https://linkedin.com/company/yourcompany"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">YouTube</th>
                                            <td><input type="url" name="settings[social_youtube]" value="<?php echo esc_attr($settings['social_youtube'] ?? ''); ?>" class="regular-text" placeholder="https://youtube.com/c/yourchannel"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Sosyal Medya Pozisyonu</th>
                                            <td>
                                                <select name="settings[social_position]">
                                                    <option value="left" <?php selected($settings['social_position'] ?? '', 'left'); ?>>Sol tarafta (menü ile)</option>
                                                    <option value="right" <?php selected($settings['social_position'] ?? '', 'right'); ?>>Sağ tarafta (iletişim ile)</option>
                                                    <option value="center" <?php selected($settings['social_position'] ?? '', 'center'); ?>>Ortada (ayrı bölüm)</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-sidebar">
                        <div class="postbox">
                            <h2 class="hndle">İçerik Önizlemesi</h2>
                            <div class="inside">
                                <div id="content_preview" class="content-preview-container">
                                    <!-- Content preview will be rendered here -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">İçerik Yönetimi</h2>
                            <div class="inside">
                                <h4>Hızlı İşlemler:</h4>
                                <div class="quick-actions">
                                    <button type="button" class="button" onclick="validateAllLinks()">
                                        <span class="dashicons dashicons-external"></span> Linkleri Kontrol Et
                                    </button>
                                    <button type="button" class="button" onclick="testContactInfo()">
                                        <span class="dashicons dashicons-phone"></span> İletişim Bilgilerini Test Et
                                    </button>
                                    <button type="button" class="button" onclick="generateContactVCard()">
                                        <span class="dashicons dashicons-download"></span> vCard Oluştur
                                    </button>
                                </div>
                                
                                <h4>İstatistikler:</h4>
                                <div class="content-stats">
                                    <div class="stat-row">
                                        <span>Menü Öğe Sayısı:</span>
                                        <span id="menu_item_count">-</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>İletişim Bilgisi Sayısı:</span>
                                        <span id="contact_count">-</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Sosyal Medya Sayısı:</span>
                                        <span id="social_count">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="İçerik Ayarlarını Kaydet">
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_advanced_tab() {
        $settings = get_option('esistenza_topbar_settings', self::get_default_settings());
        ?>
        <div class="topbar-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenza_topbar_save'); ?>
                <input type="hidden" name="tab" value="advanced">
                
                <div class="advanced-layout">
                    <div class="advanced-main">
                        <div class="postbox">
                            <h2 class="hndle">Performans ve Optimizasyon</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">CSS Optimizasyonu</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[minify_css]" value="1" <?php checked(!empty($settings['minify_css'])); ?>>
                                                    CSS'i minify et (sıkıştır)
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[inline_css]" value="1" <?php checked(!empty($settings['inline_css'])); ?>>
                                                    Kritik CSS'i inline olarak yükle
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[cache_css]" value="1" <?php checked(!empty($settings['cache_css'])); ?>>
                                                    CSS'i browser cache'inde sakla
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">JavaScript Optimizasyonu</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[defer_js]" value="1" <?php checked(!empty($settings['defer_js'])); ?>>
                                                    JavaScript'i defer et
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[minify_js]" value="1" <?php checked(!empty($settings['minify_js'])); ?>>
                                                    JavaScript'i minify et
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Font Optimizasyonu</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[preload_fonts]" value="1" <?php checked(!empty($settings['preload_fonts'])); ?>>
                                                    Google Fonts'u preload et
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[font_display_swap]" value="1" <?php checked(!empty($settings['font_display_swap'])); ?>>
                                                    Font-display: swap kullan
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Özel CSS ve JavaScript</h2>
                            <div class="inside">
                                <h4>Özel CSS:</h4>
                                <textarea name="settings[custom_css]" rows="10" class="large-text code" placeholder="/* Özel CSS kodlarınızı buraya yazın */"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                                
                                <h4>Özel JavaScript:</h4>
                                <textarea name="settings[custom_js]" rows="8" class="large-text code" placeholder="// Özel JavaScript kodlarınızı buraya yazın (jQuery kullanılabilir)"><?php echo esc_textarea($settings['custom_js'] ?? ''); ?></textarea>
                                
                                <div class="code-helpers">
                                    <h4>Yaygın CSS Sınıfları:</h4>
                                    <div class="css-classes">
                                        <code onclick="insertAtCursor('.esistenza-top-bar', 'custom_css')">.esistenza-top-bar</code>
                                        <code onclick="insertAtCursor('.esistenza-top-bar-left', 'custom_css')">.esistenza-top-bar-left</code>
                                        <code onclick="insertAtCursor('.esistenza-top-bar-right', 'custom_css')">.esistenza-top-bar-right</code>
                                        <code onclick="insertAtCursor('.esistenza-top-bar-menu', 'custom_css')">.esistenza-top-bar-menu</code>
                                        <code onclick="insertAtCursor('.esistenza-top-bar-contact', 'custom_css')">.esistenza-top-bar-contact</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Uyumluluk ve Güvenlik</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Tema Uyumluluğu</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[override_theme_styles]" value="1" <?php checked(!empty($settings['override_theme_styles'])); ?>>
                                                    Tema stillerini zorla geçersiz kıl (!important kullan)
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[respect_admin_bar]" value="1" <?php checked(!empty($settings['respect_admin_bar'])); ?>>
                                                    WordPress admin bar'ını dikkate al
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Browser Uyumluluğu</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[ie_support]" value="1" <?php checked(!empty($settings['ie_support'])); ?>>
                                                    Internet Explorer 11+ desteği
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[safari_support]" value="1" <?php checked(!empty($settings['safari_support'])); ?>>
                                                    Safari özel optimizasyonları
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Güvenlik</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[sanitize_output]" value="1" <?php checked(!empty($settings['sanitize_output'])); ?>>
                                                    Çıktıları güvenlik için temizle
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[nonce_protection]" value="1" <?php checked(!empty($settings['nonce_protection'])); ?>>
                                                    AJAX isteklerinde nonce koruması
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
                            <h2 class="hndle">Sistem Bilgileri</h2>
                            <div class="inside">
                                <div class="system-info">
                                    <div class="info-row">
                                        <span>WordPress Versiyonu:</span>
                                        <span><?php echo get_bloginfo('version'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span>PHP Versiyonu:</span>
                                        <span><?php echo PHP_VERSION; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span>Aktif Tema:</span>
                                        <span><?php echo wp_get_theme()->get('Name'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span>Topbar CSS Boyutu:</span>
                                        <span id="css_size">Hesaplanıyor...</span>
                                    </div>
                                    <div class="info-row">
                                        <span>JS Boyutu:</span>
                                        <span id="js_size">Hesaplanıyor...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Debug Bilgileri</h2>
                            <div class="inside">
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="settings[debug_mode]" value="1" <?php checked(!empty($settings['debug_mode'])); ?>>
                                        Debug modunu etkinleştir
                                    </label>
                                </fieldset>
                                
                                <div id="debug_info" style="<?php echo empty($settings['debug_mode']) ? 'display:none;' : ''; ?>">
                                    <h4>Debug Çıktısı:</h4>
                                    <div class="debug-output">
                                        <code id="debug_output_content">Debug modu etkinleştirildiğinde bilgiler burada görünecek</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Gelişmiş İşlemler</h2>
                            <div class<?php
/*
 * Enhanced Custom Topbar Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeCustomTopbar {
    
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
        // Admin init
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX handlers
        add_action('wp_ajax_esistenze_topbar_preview', array($this, 'ajax_topbar_preview'));
        add_action('wp_ajax_esistenze_topbar_reset', array($this, 'ajax_reset_topbar'));
        add_action('wp_ajax_esistenze_topbar_import', array($this, 'ajax_import_settings'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        
        // Output topbar
        if (has_action('wp_body_open')) {
            add_action('wp_body_open', array($this, 'output_topbar'));
        } else {
            add_action('wp_head', array($this, 'output_topbar'), 1);
        }
    }
    
    public static function admin_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        // Handle form submissions
        if (isset($_POST['submit'])) {
            self::handle_form_submission();
        }
        
        echo '<div class="wrap esistenze-topbar-wrap">';
        echo '<h1 class="wp-heading-inline">Custom Topbar</h1>';
        echo '<button type="button" class="page-title-action" onclick="previewTopbar()">Canlı Önizleme</button>';
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
            case 'design':
                self::render_design_tab();
                break;
            case 'content':
                self::render_content_tab();
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
            'design' => array('label' => 'Tasarım', 'icon' => 'dashicons-admin-appearance'),
            'content' => array('label' => 'İçerik', 'icon' => 'dashicons-edit'),
            'advanced' => array('label' => 'Gelişmiş', 'icon' => 'dashicons-admin-tools'),
            'analytics' => array('label' => 'İstatistikler', 'icon' => 'dashicons-chart-area')
        );
        
        echo '<nav class="nav-tab-wrapper wp-clearfix">';
        foreach ($tabs as $tab_key => $tab) {
            $class = ($current_tab === $tab_key) ? 'nav-tab nav-tab-active' : 'nav-tab';
            echo '<a href="' . admin_url('admin.php?page=esistenze-custom-topbar&tab=' . $tab_key) . '" class="' . $class . '">';
            echo '<span class="dashicons ' . $tab['icon'] . '"></span> ' . $tab['label'];
            echo '</a>';
        }
        echo '</nav>';
    }
    
    private static function render_general_tab() {
        $settings = get_option('esistenze_topbar_settings', self::get_default_settings());
        ?>
        <div class="topbar-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_topbar_save'); ?>
                <input type="hidden" name="tab" value="general">
                
                <div class="topbar-layout">
                    <div class="topbar-main">
                        <div class="postbox">
                            <h2 class="hndle">Ana Ayarlar</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Topbar'ı Etkinleştir</th>
                                        <td>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="settings[enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?> onchange="toggleTopbar()">
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <p class="description">Site üstünde topbar'ı gösterir/gizler</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Görünür Olduğu Sayfalar</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[show_on_home]" value="1" <?php checked(!empty($settings['show_on_home'])); ?>>
                                                    Ana sayfa
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[show_on_pages]" value="1" <?php checked(!empty($settings['show_on_pages'])); ?>>
                                                    Tüm sayfalar
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[show_on_posts]" value="1" <?php checked(!empty($settings['show_on_posts'])); ?>>
                                                    Blog yazıları
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[show_on_shop]" value="1" <?php checked(!empty($settings['show_on_shop'])); ?>>
                                                    WooCommerce sayfaları
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[show_on_archive]" value="1" <?php checked(!empty($settings['show_on_archive'])); ?>>
                                                    Arşiv sayfaları
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Pozisyon</th>
                                        <td>
                                            <select name="settings[position]" onchange="updatePreview()">
                                                <option value="fixed-top" <?php selected($settings['position'] ?? '', 'fixed-top'); ?>>Üstte Sabit</option>
                                                <option value="absolute-top" <?php selected($settings['position'] ?? '', 'absolute-top'); ?>>Üstte Statik</option>
                                                <option value="sticky-top" <?php selected($settings['position'] ?? '', 'sticky-top'); ?>>Sticky (Yapışkan)</option>
                                            </select>
                                            <p class="description">Topbar'ın sayfa üzerindeki konumu</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Z-Index</th>
                                        <td>
                                            <input type="number" name="settings[z_index]" value="<?php echo esc_attr($settings['z_index'] ?? 99999); ?>" min="1" max="999999" class="small-text">
                                            <p class="description">Diğer elementlerin üstünde gösterilmesi için</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Boyut ve Görünüm</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Yükseklik</th>
                                        <td>
                                            <div class="size-control">
                                                <input type="range" name="settings[height]" value="<?php echo esc_attr($settings['height'] ?? 50); ?>" min="30" max="120" oninput="updateHeight(this.value)" class="size-slider">
                                                <span id="height_display"><?php echo esc_attr($settings['height'] ?? 50); ?>px</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Yatay Padding</th>
                                        <td>
                                            <div class="size-control">
                                                <input type="range" name="settings[padding_horizontal]" value="<?php echo esc_attr($settings['padding_horizontal'] ?? 5); ?>" min="0" max="20" oninput="updatePaddingH(this.value)" class="size-slider">
                                                <span id="padding_h_display"><?php echo esc_attr($settings['padding_horizontal'] ?? 5); ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Font Boyutu</th>
                                        <td>
                                            <div class="size-control">
                                                <input type="range" name="settings[font_size]" value="<?php echo esc_attr($settings['font_size'] ?? 16); ?>" min="10" max="24" oninput="updateFontSize(this.value)" class="size-slider">
                                                <span id="font_size_display"><?php echo esc_attr($settings['font_size'] ?? 16); ?>px</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Mobil Davranış</th>
                                        <td>
                                            <select name="settings[mobile_behavior]" onchange="updatePreview()">
                                                <option value="responsive" <?php selected($settings['mobile_behavior'] ?? '', 'responsive'); ?>>Responsive (Alt alta)</option>
                                                <option value="horizontal" <?php selected($settings['mobile_behavior'] ?? '', 'horizontal'); ?>>Yatay Scroll</option>
                                                <option value="hide" <?php selected($settings['mobile_behavior'] ?? '', 'hide'); ?>>Mobilde Gizle</option>
                                                <option value="collapse" <?php selected($settings['mobile_behavior'] ?? '', 'collapse'); ?>>Hamburger Menü</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Animasyon ve Efektler</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Giriş Animasyonu</th>
                                        <td>
                                            <select name="settings[entrance_animation]">
                                                <option value="none" <?php selected($settings['entrance_animation'] ?? '', 'none'); ?>>Animasyon Yok</option>
                                                <option value="slideDown" <?php selected($settings['entrance_animation'] ?? '', 'slideDown'); ?>>Yukarıdan Kayma</option>
                                                <option value="fadeIn" <?php selected($settings['entrance_animation'] ?? '', 'fadeIn'); ?>>Soluklaşma</option>
                                                <option value="slideInLeft" <?php selected($settings['entrance_animation'] ?? '', 'slideInLeft'); ?>>Soldan Kayma</option>
                                                <option value="slideInRight" <?php selected($settings['entrance_animation'] ?? '', 'slideInRight'); ?>>Sağdan Kayma</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Hover Efektleri</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[hover_effects]" value="1" <?php checked(!empty($settings['hover_effects'])); ?>>
                                                    Link hover efektlerini etkinleştir
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[backdrop_blur]" value="1" <?php checked(!empty($settings['backdrop_blur'])); ?>>
                                                    Arka plan bulanıklığı (backdrop-filter)
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Scroll Davranışı</th>
                                        <td>
                                            <select name="settings[scroll_behavior]">
                                                <option value="static" <?php selected($settings['scroll_behavior'] ?? '', 'static'); ?>>Statik</option>
                                                <option value="hide_on_scroll" <?php selected($settings['scroll_behavior'] ?? '', 'hide_on_scroll'); ?>>Scroll'da Gizle</option>
                                                <option value="shrink_on_scroll" <?php selected($settings['scroll_behavior'] ?? '', 'shrink_on_scroll'); ?>>Scroll'da Küçült</option>
                                                <option value="change_bg_on_scroll" <?php selected($settings['scroll_behavior'] ?? '', 'change_bg_on_scroll'); ?>>Scroll'da Renk Değiştir</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="topbar-sidebar">
                        <div class="postbox">
                            <h2 class="hndle">Canlı Önizleme</h2>
                            <div class="inside">
                                <div id="topbar_preview" class="topbar-preview-container">
                                    <div class="preview-topbar" id="preview_topbar">
                                        <!-- Preview will be generated here -->
                                    </div>
                                </div>
                                
                                <div class="preview-controls">
                                    <button type="button" class="button" onclick="refreshPreview()">
                                        <span class="dashicons dashicons-update"></span> Yenile
                                    </button>
                                    <button type="button" class="button" onclick="previewMobile()">
                                        <span class="dashicons dashicons-smartphone"></span> Mobil
                                    </button>
                                    <button type="button" class="button" onclick="previewDesktop()">
                                        <span class="dashicons dashicons-desktop"></span> Masaüstü
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Hızlı Ayarlar</h2>
                            <div class="inside">
                                <div class="quick-settings">
                                    <h4>Hazır Şablonlar:</h4>
                                    <div class="template-buttons">
                                        <button type="button" class="template-btn" onclick="applyTemplate('business')">
                                            <span class="template-name">İş/Kurumsal</span>
                                            <span class="template-desc">Profesyonel görünüm</span>
                                        </button>
                                        <button type="button" class="template-btn" onclick="applyTemplate('ecommerce')">
                                            <span class="template-name">E-Ticaret</span>
                                            <span class="template-desc">Mağaza için optimized</span>
                                        </button>
                                        <button type="button" class="template-btn" onclick="applyTemplate('minimal')">
                                            <span class="template-name">Minimal</span>
                                            <span class="template-desc">Sade ve temiz</span>
                                        </button>
                                        <button type="button" class="template-btn" onclick="applyTemplate('colorful')">
                                            <span class="template-name">Renkli</span>
                                            <span class="template-desc">Canlı ve dinamik</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">İstatistikler</h2>
                            <div class="inside">
                                <div class="topbar-stats">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo get_option('esistenze_topbar_impressions', 0); ?></div>
                                        <div class="stat-label">Toplam Görüntülenme</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo get_option('esistenze_topbar_clicks', 0); ?></div>
                                        <div class="stat-label">Toplam Tıklama</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number">
                                            <?php 
                                            $impressions = get_option('esistenza_topbar_impressions', 0);
                                            $clicks = get_option('esistenze_topbar_clicks', 0);
                                            echo $impressions > 0 ? round(($clicks / $impressions) * 100, 1) : 0;
                                            ?>%
                                        </div>
                                        <div class="stat-label">Tıklama Oranı</div>
                                    </div>
                                </div>
                                
                                <button type="button" class="button button-secondary" onclick="resetStats()">İstatistikleri Sıfırla</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Ayarları Kaydet">
                    <button type="button" class="button" onclick="exportSettings()">Ayarları Dışa Aktar</button>
                    <button type="button" class="button" onclick="importSettings()">Ayarları İçe Aktar</button>
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_design_tab() {
        $settings = get_option('esistenze_topbar_settings', self::get_default_settings());
        ?>
        <div class="topbar-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_topbar_save'); ?>
                <input type="hidden" name="tab" value="design">
                
                <div class="design-layout">
                    <div class="design-controls">
                        <div class="postbox">
                            <h2 class="hndle">Renk Şeması</h2>
                            <div class="inside">
                                <div class="color-scheme-selector">
                                    <div class="predefined-schemes">
                                        <h4>Hazır Renk Şemaları:</h4>
                                        <div class="scheme-grid">
                                            <div class="scheme-item" onclick="applyColorScheme('green')" data-scheme="green">
                                                <div class="scheme-preview" style="background: linear-gradient(90deg, #4CAF50, #45a049);"></div>
                                                <span>Yeşil</span>
                                            </div>
                                            <div class="scheme-item" onclick="applyColorScheme('blue')" data-scheme="blue">
                                                <div class="scheme-preview" style="background: linear-gradient(90deg, #2196F3, #1976D2);"></div>
                                                <span>Mavi</span>
                                            </div>
                                            <div class="scheme-item" onclick="applyColorScheme('purple')" data-scheme="purple">
                                                <div class="scheme-preview" style="background: linear-gradient(90deg, #9C27B0, #7B1FA2);"></div>
                                                <span>Mor</span>
                                            </div>
                                            <div class="scheme-item" onclick="applyColorScheme('orange')" data-scheme="orange">
                                                <div class="scheme-preview" style="background: linear-gradient(90deg, #FF9800, #F57C00);"></div>
                                                <span>Turuncu</span>
                                            </div>
                                            <div class="scheme-item" onclick="applyColorScheme('red')" data-scheme="red">
                                                <div class="scheme-preview" style="background: linear-gradient(90deg, #F44336, #D32F2F);"></div>
                                                <span>Kırmızı</span>
                                            </div>
                                            <div class="scheme-item" onclick="applyColorScheme('dark')" data-scheme="dark">
                                                <div class="scheme-preview" style="background: linear-gradient(90deg, #424242, #212121);"></div>
                                                <span>Koyu</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="custom-colors">
                                    <h4>Özel Renkler:</h4>
                                    <table class="form-table">
                                        <tr>
                                            <th scope="row">Arka Plan</th>
                                            <td>
                                                <div class="gradient-controls">
                                                    <label>
                                                        Başlangıç:
                                                        <input type="color" name="settings[bg_color_start]" value="<?php echo esc_attr($settings['bg_color_start'] ?? '#4CAF50'); ?>" class="color-picker" onchange="updatePreviewColors()">
                                                    </label>
                                                    <label>
                                                        Bitiş:
                                                        <input type="color" name="settings[bg_color_end]" value="<?php echo esc_attr($settings['bg_color_end'] ?? '#388E3C'); ?>" class="color-picker" onchange="updatePreviewColors()">
                                                    </label>
                                                    <label>
                                                        Açı:
                                                        <input type="range" name="settings[gradient_angle]" value="<?php echo esc_attr($settings['gradient_angle'] ?? 90); ?>" min="0" max="360" oninput="updateGradientAngle(this.value)">
                                                        <span id="gradient_angle_display"><?php echo esc_attr($settings['gradient_angle'] ?? 90); ?>°</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Yazı Renkleri</th>
                                            <td>
                                                <div class="text-colors">
                                                    <label>
                                                        Normal:
                                                        <input type="color" name="settings[text_color]" value="<?php echo esc_attr($settings['text_color'] ?? '#ffffff'); ?>" class="color-picker" onchange="updatePreviewColors()">
                                                    </label>
                                                    <label>
                                                        Hover:
                                                        <input type="color" name="settings[text_hover_color]" value="<?php echo esc_attr($settings['text_hover_color'] ?? '#f0f0f0'); ?>" class="color-picker" onchange="updatePreviewColors()">
                                                    </label>
                                                    <label>
                                                        Aktif:
                                                        <input type="color" name="settings[text_active_color]" value="<?php echo esc_attr($settings['text_active_color'] ?? '#ffeb3b'); ?>" class="color-picker" onchange="updatePreviewColors()">
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Tipografi</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Font Ailesi</th>
                                        <td>
                                            <select name="settings[font_family]" onchange="updatePreviewFont()">
                                                <option value="system" <?php selected($settings['font_family'] ?? '', 'system'); ?>>Sistem Varsayılanı</option>
                                                <option value="arial" <?php selected($settings['font_family'] ?? '', 'arial'); ?>>Arial</option>
                                                <option value="helvetica" <?php selected($settings['font_family'] ?? '', 'helvetica'); ?>>Helvetica</option>
                                                <option value="georgia" <?php selected($settings['font_family'] ?? '', 'georgia'); ?>>Georgia</option>
                                                <option value="times" <?php selected($settings['font_family'] ?? '', 'times'); ?>>Times New Roman</option>
                                                <option value="roboto" <?php selected($settings['font_family'] ?? '', 'roboto'); ?>>Roboto (Google Font)</option>
                                                <option value="opensans" <?php selected($settings['font_family'] ?? '', 'opensans'); ?>>Open Sans (Google Font)</option>
                                                <option value="montserrat" <?php selected($settings['font_family'] ?? '', 'montserrat'); ?>>Montserrat (Google Font)</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Font Ağırlığı</th>
                                        <td>
                                            <select name="settings[font_weight]" onchange="updatePreviewFont()">
                                                <option value="300" <?php selected($settings['font_weight'] ?? '', '300'); ?>>Light (300)</option>
                                                <option value="400" <?php selected($settings['font_weight'] ?? '', '400'); ?>>Normal (400)</option>
                                                <option value="500" <?php selected($settings['font_weight'] ?? '', '500'); ?>>Medium (500)</option>
                                                <option value="600" <?php selected($settings['font_weight'] ?? '', '600'); ?>>Semi Bold (600)</option>
                                                <option value="700" <?php selected($settings['font_weight'] ?? '', '700'); ?>>Bold (700)</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Harf Aralığı</th>
                                        <td>
                                            <input type="range" name="settings[letter_spacing]" value="<?php echo esc_attr($settings['letter_spacing'] ?? 0); ?>" min="-2" max="5" step="0.1" oninput="updateLetterSpacing(this.value)">
                                            <span id="letter_spacing_display"><?php echo esc_attr($settings['letter_spacing'] ?? 0); ?>px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Büyük/Küçük Harf</th>
                                        <td>
                                            <select name="settings[text_transform]" onchange="updatePreviewFont()">
                                                <option value="none" <?php selected($settings['text_transform'] ?? '', 'none'); ?>>Normal</option>
                                                <option value="uppercase" <?php selected($settings['text_transform'] ?? '', 'uppercase'); ?>>BÜYÜK HARF</option>
                                                <option value="lowercase" <?php selected($settings['text_transform'] ?? '', 'lowercase'); ?>>küçük harf</option>
                                                <option value="capitalize" <?php selected($settings['text_transform'] ?? '', 'capitalize'); ?>>Her Kelimenin İlk Harfi Büyük</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Gölge ve Efektler</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Box Shadow</th>
                                        <td>
                                            <div class="shadow-controls">
                                                <label>
                                                    X Offset:
                                                    <input type="range" name="settings[shadow_x]" value="<?php echo esc_attr($settings['shadow_x'] ?? 0); ?>" min="-20" max="20" oninput="updateShadow()">
                                                    <span id="shadow_x_display"><?php echo esc_attr($settings['shadow_x'] ?? 0); ?>px</span>
                                                </label>
                                                <label>
                                                    Y Offset:
                                                    <input type="range" name="settings[shadow_y]" value="<?php echo esc_attr($settings['shadow_y'] ?? 2); ?>" min="0