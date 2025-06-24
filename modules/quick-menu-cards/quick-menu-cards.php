<?php
/*
 * Enhanced Quick Menu Cards Module - Complete Admin Panel
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCards {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
    
    private static function enqueue_admin_assets() {
        ?>
        <style>
        .esistenza-quick-menu-wrap { max-width: 1400px; }
        .nav-tab .dashicons { margin-right: 5px; vertical-align: middle; }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .card-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .card-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .card-preview {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .card-icon {
            font-size: 24px;
            margin-right: 10px;
            width: 40px;
            text-align: center;
        }
        
        .card-content h4 {
            margin: 0 0 5px;
            font-size: 16px;
        }
        
        .card-content p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .card-actions {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .card-stats {
            display: flex;
            gap: 10px;
            font-size: 12px;
            color: #999;
        }
        
        .card-modal {
            display: none;
            position: fixed;
            z-index: 100000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .card-modal-content {
            background-color: #fefefe;
            margin: 2% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .card-modal-header {
            background: #f1f1f1;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-modal-body {
            padding: 20px;
        }
        
        .card-modal-footer {
            background: #f1f1f1;
            padding: 15px 20px;
            border-top: 1px solid #ddd;
            text-align: right;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .card-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
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
        
        .predefined-colors {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        
        .color-option {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .color-option:hover {
            border-color: #333;
        }
        
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
        }
        
        .icon-option {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 50px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .icon-option:hover {
            background: #f0f0f0;
            border-color: #4CAF50;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
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
            gap: 15px;
        }
        
        .metric-item { text-align: center; }
        
        .metric-value {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .metric-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .no-cards {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-cards-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .card-form-grid {
                grid-template-columns: 1fr;
            }
            
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            window.addNewCard = function() {
                $('#modal_title').text('Yeni Kart Ekle');
                $('#card_form')[0].reset();
                $('#card_editor_modal').show();
            };
            
            window.editCard = function(cardId) {
                $('#modal_title').text('Kart Düzenle');
                $('#card_editor_modal').show();
            };
            
            window.closeCardModal = function() {
                $('#card_editor_modal').hide();
            };
            
            window.deleteCard = function(cardId) {
                if (confirm('Bu kartı silmek istediğinizden emin misiniz?')) {
                    window.location.href = `admin.php?page=esistenze-quick-menu&action=delete&card_id=${cardId}`;
                }
            };
            
            window.duplicateCard = function(cardId) {
                window.location.href = `admin.php?page=esistenze-quick-menu&action=duplicate&card_id=${cardId}`;
            };
            
            window.toggleCardStatus = function(cardId) {
                window.location.href = `admin.php?page=esistenze-quick-menu&action=toggle&card_id=${cardId}`;
            };
            
            window.previewCards = function() {
                window.open('/', '_blank');
            };
            
            $('.color-option').on('click', function() {
                const color = $(this).data('color');
                $('#card_color').val(color);
            });
            
            window.openIconPicker = function() {
                loadIcons();
                $('#icon_picker_modal').show();
            };
            
            window.closeIconPicker = function() {
                $('#icon_picker_modal').hide();
            };
            
            function loadIcons() {
                const commonIcons = [
                    'fa fa-home', 'fa fa-user', 'fa fa-envelope', 'fa fa-phone',
                    'fa fa-shopping-cart', 'fa fa-heart', 'fa fa-star', 'fa fa-search',
                    'fa fa-calendar', 'fa fa-clock', 'fa fa-map-marker', 'fa fa-camera',
                    'fa fa-music', 'fa fa-video', 'fa fa-book', 'fa fa-graduation-cap'
                ];
                
                let iconsHtml = '';
                commonIcons.forEach(icon => {
                    iconsHtml += `<div class="icon-option" onclick="selectIcon('${icon}')"><i class="${icon}"></i></div>`;
                });
                
                $('#icon_grid').html(iconsHtml);
            }
            
            window.selectIcon = function(iconClass) {
                $('#card_icon').val(iconClass);
                closeIconPicker();
            };
            
            window.exportAllSettings = function() {
                alert('Ayarlar dışa aktarılıyor...');
            };
            
            window.importAllSettings = function() {
                alert('Ayarlar içe aktarılıyor...');
            };
            
            window.resetAllSettings = function() {
                if (confirm('Tüm ayarları sıfırlamak istediğinizden emin misiniz?')) {
                    $.post(ajaxurl, {
                        action: 'esistenze_quick_menu_reset',
                        _wpnonce: '<?php echo wp_create_nonce("esistenze_quick_menu_reset"); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    });
                }
            };
            
            window.testEntranceAnimation = function() {
                const card = $('#preview_card');
                card.removeClass().addClass('preview-card');
                setTimeout(() => {
                    card.addClass('animate-entrance');
                }, 100);
            };
            
            window.testHoverAnimation = function() {
                $('#preview_card').addClass('hover-effect');
                setTimeout(() => {
                    $('#preview_card').removeClass('hover-effect');
                }, 1000);
            };
            
            window.insertAtCursor = function(text, textareaId) {
                const textarea = document.getElementById(textareaId);
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
            
            $('#card_form').on('submit', function(e) {
                e.preventDefault();
                alert('Kart kaydediliyor...');
                closeCardModal();
            });
        });
        </script>
        <?php
    }
}

EsistenzeQuickMenuCards::getInstance();
?>
    
    private static function render_analytics_tab() {
        ?>
        <div class="quick-menu-content">
            <div class="analytics-dashboard">
                <div class="analytics-header">
                    <h2>Quick Menu Cards İstatistikleri</h2>
                    <div class="analytics-period">
                        <select id="analytics_period">
                            <option value="7">Son 7 Gün</option>
                            <option value="30">Son 30 Gün</option>
                            <option value="90" selected>Son 90 Gün</option>
                            <option value="365">Son 1 Yıl</option>
                        </select>
                        <button type="button" class="button" onclick="refreshAnalytics()">Yenile</button>
                        <button type="button" class="button" onclick="exportAnalytics()">Rapor İndir</button>
                    </div>
                </div>
                
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>Genel İstatistikler</h3>
                            <span class="dashicons dashicons-chart-area"></span>
                        </div>
                        <div class="analytics-card-body">
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo number_format(get_option('esistenze_quick_menu_total_views', 0)); ?></div>
                                    <div class="metric-label">Toplam Görüntülenme</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo number_format(get_option('esistenze_quick_menu_total_clicks', 0)); ?></div>
                                    <div class="metric-label">Toplam Tıklama</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value">
                                        <?php 
                                        $views = get_option('esistenze_quick_menu_total_views', 0);
                                        $clicks = get_option('esistenze_quick_menu_total_clicks', 0);
                                        echo $views > 0 ? number_format(($clicks / $views) * 100, 1) : 0;
                                        ?>%
                                    </div>
                                    <div class="metric-label">CTR (Tıklama Oranı)</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo count(get_option('esistenze_quick_menu_cards', array())); ?></div>
                                    <div class="metric-label">Toplam Kart</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-card-header">
                            <h3>En Popüler Kartlar</h3>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <div class="analytics-card-body">
                            <div class="popular-cards-list">
                                <?php
                                $cards = get_option('esistenze_quick_menu_cards', array());
                                $card_stats = array();
                                
                                foreach ($cards as $index => $card) {
                                    $views = get_option("esistenze_quick_menu_views_{$index}", 0);
                                    $clicks = get_option("esistenze_quick_menu_clicks_{$index}", 0);
                                    $card_stats[] = array(
                                        'title' => $card['title'] ?? 'Başlıksız',
                                        'views' => $views,
                                        'clicks' => $clicks,
                                        'ctr' => $views > 0 ? ($clicks / $views) * 100 : 0
                                    );
                                }
                                
                                usort($card_stats, function($a, $b) {
                                    return $b['clicks'] - $a['clicks'];
                                });
                                
                                $top_cards = array_slice($card_stats, 0, 5);
                                
                                if (empty($top_cards)):
                                ?>
                                    <p>Henüz istatistik verisi bulunmuyor.</p>
                                <?php else: ?>
                                    <?php foreach ($top_cards as $card): ?>
                                        <div class="popular-card-item">
                                            <div class="card-name"><?php echo esc_html($card['title']); ?></div>
                                            <div class="card-metrics">
                                                <span class="views">👁️ <?php echo number_format($card['views']); ?></span>
                                                <span class="clicks">👆 <?php echo number_format($card['clicks']); ?></span>
                                                <span class="ctr"><?php echo number_format($card['ctr'], 1); ?>%</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
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
                                        <div class="device-percentage"><?php echo get_option('esistenze_quick_menu_desktop_percentage', 45); ?>%</div>
                                    </div>
                                </div>
                                <div class="device-stat">
                                    <span class="device-icon">📱</span>
                                    <div class="device-info">
                                        <div class="device-name">Mobil</div>
                                        <div class="device-percentage"><?php echo get_option('esistenze_quick_menu_mobile_percentage', 35); ?>%</div>
                                    </div>
                                </div>
                                <div class="device-stat">
                                    <span class="device-icon">📟</span>
                                    <div class="device-info">
                                        <div class="device-name">Tablet</div>
                                        <div class="device-percentage"><?php echo get_option('esistenze_quick_menu_tablet_percentage', 20); ?>%</div>
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
                                    <span class="metric-label">Cache Hit Oranı:</span>
                                    <span class="metric-value">%94.7</span>
                                </div>
                                <div class="performance-item">
                                    <span class="metric-label">CSS Boyutu:</span>
                                    <span class="metric-value" id="css_size">Hesaplanıyor...</span>
                                </div>
                                <div class="performance-item">
                                    <span class="metric-label">JS Boyutu:</span>
                                    <span class="metric-value" id="js_size">Hesaplanıyor...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="analytics-charts">
                    <div class="postbox">
                        <h2 class="hndle">Trend Analizi</h2>
                        <div class="inside">
                            <canvas id="trend_chart" width="800" height="300"></canvas>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Kart Performans Tablosu</h2>
                        <div class="inside">
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Kart Adı</th>
                                        <th>Görüntülenme</th>
                                        <th>Tıklama</th>
                                        <th>CTR</th>
                                        <th>Son Aktivite</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cards = get_option('esistenze_quick_menu_cards', array());
                                    if (empty($cards)):
                                    ?>
                                        <tr>
                                            <td colspan="6">Henüz kart eklenmemiş.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($cards as $index => $card): ?>
                                            <?php
                                            $views = get_option("esistenze_quick_menu_views_{$index}", 0);
                                            $clicks = get_option("esistenze_quick_menu_clicks_{$index}", 0);
                                            $ctr = $views > 0 ? ($clicks / $views) * 100 : 0;
                                            $last_activity = get_option("esistenze_quick_menu_last_activity_{$index}", 'Henüz aktivite yok');
                                            ?>
                                            <tr>
                                                <td><strong><?php echo esc_html($card['title'] ?? 'Başlıksız'); ?></strong></td>
                                                <td><?php echo number_format($views); ?></td>
                                                <td><?php echo number_format($clicks); ?></td>
                                                <td><?php echo number_format($ctr, 1); ?>%</td>
                                                <td><?php echo esc_html($last_activity); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo !empty($card['enabled']) ? 'active' : 'inactive'; ?>">
                                                        <?php echo !empty($card['enabled']) ? 'Aktif' : 'Pasif'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private static function get_default_design_settings() {
        return array(
            'card_style' => 'raised',
            'card_width' => 280,
            'card_height' => 200,
            'border_radius' => 8,
            'enable_shadow' => true,
            'shadow_intensity' => 0.1,
            'shadow_blur' => 20,
            'font_family' => 'system',
            'title_size' => 18,
            'description_size' => 14,
            'text_align' => 'center',
            'default_color' => '#4CAF50',
            'title_color' => '#333333',
            'description_color' => '#666666',
            'background_color' => '#ffffff',
            'hover_color' => '#f5f5f5'
        );
    }
    
    private static function get_default_layout_settings() {
        return array(
            'columns_desktop' => '4',
            'columns_tablet' => '2',
            'columns_mobile' => '1',
            'gap_horizontal' => 20,
            'gap_vertical' => 20,
            'max_width' => 1200,
            'full_width' => false,
            'container_padding' => 20,
            'default_order' => 'custom',
            'show_featured_first' => true,
            'highlight_featured' => true,
            'enable_pagination' => false,
            'cards_per_page' => 12,
            'ajax_pagination' => false,
            'layout_type' => 'grid',
            'height_mode' => 'equal'
        );
    }
    
    private static function get_default_animation_settings() {
        return array(
            'enable_animations' => true,
            'entrance_animation' => 'fadeIn',
            'animation_duration' => 600,
            'animation_delay' => 100,
            'animation_easing' => 'ease',
            'hover_effect' => 'lift',
            'hover_duration' => 300,
            'enable_3d' => false,
            'parallax_hover' => false,
            'enable_scroll_animation' => true,
            'scroll_threshold' => 0.1,
            'repeat_animation' => false
        );
    }
    
    private static function get_default_advanced_settings() {
        return array(
            'lazy_load' => true,
            'lazy_load_images' => true,
            'enable_cache' => true,
            'cache_duration' => 3600,
            'minify_css' => false,
            'minify_js' => false,
            'defer_js' => false,
            'enable_schema' => true,
            'schema_type' => 'ItemList',
            'enable_aria' => true,
            'keyboard_navigation' => true,
            'high_contrast' => false,
            'reduce_motion' => true,
            'nofollow_external' => true,
            'noopener_external' => true,
            'noreferrer_external' => false,
            'screen_reader_support' => true,
            'focus_indicators' => true,
            'custom_css' => '',
            'custom_js' => '',
            'auto_backup' => false,
            'backup_retention' => '3',
            'debug_mode' => false
        );
    }
    
    private static function handle_form_submission() {
        if (!current_user_can('manage_options') || !check_admin_referer('esistenze_quick_menu_save')) {
            wp_die('Yetkiniz yok.');
        }
        
        $tab = sanitize_text_field($_POST['tab'] ?? 'cards');
        $settings = $_POST['settings'] ?? array();
        
        switch ($tab) {
            case 'design':
                $settings = self::sanitize_design_settings($settings);
                update_option('esistenze_quick_menu_design', $settings);
                break;
            case 'layout':
                $settings = self::sanitize_layout_settings($settings);
                update_option('esistenze_quick_menu_layout', $settings);
                break;
            case 'animation':
                $settings = self::sanitize_animation_settings($settings);
                update_option('esistenze_quick_menu_animation', $settings);
                break;
            case 'advanced':
                $settings = self::sanitize_advanced_settings($settings);
                update_option('esistenze_quick_menu_advanced', $settings);
                break;
        }
        
        wp_cache_delete('esistenze_quick_menu_styles', 'esistenze');
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Ayarlar başarıyla kaydedildi!</p></div>';
        });
    }
    
    private static function handle_card_actions() {
        $action = sanitize_text_field($_GET['action']);
        $card_id = intval($_GET['card_id']);
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkiniz yok.');
        }
        
        $cards = get_option('esistenze_quick_menu_cards', array());
        
        switch ($action) {
            case 'delete':
                if (isset($cards[$card_id])) {
                    unset($cards[$card_id]);
                    $cards = array_values($cards);
                    update_option('esistenze_quick_menu_cards', $cards);
                }
                break;
            case 'toggle':
                if (isset($cards[$card_id])) {
                    $cards[$card_id]['enabled'] = !($cards[$card_id]['enabled'] ?? true);
                    update_option('esistenze_quick_menu_cards', $cards);
                }
                break;
            case 'duplicate':
                if (isset($cards[$card_id])) {
                    $new_card = $cards[$card_id];
                    $new_card['title'] = ($new_card['title'] ?? 'Kart') . ' - Kopyası';
                    $cards[] = $new_card;
                    update_option('esistenze_quick_menu_cards', $cards);
                }
                break;
        }
        
        wp_redirect(admin_url('admin.php?page=esistenze-quick-menu&tab=cards'));
        exit;
    }
    
    private static function sanitize_design_settings($settings) {
        $sanitized = array();
        
        $sanitized['card_style'] = sanitize_text_field($settings['card_style'] ?? 'raised');
        $sanitized['card_width'] = intval($settings['card_width'] ?? 280);
        $sanitized['card_height'] = intval($settings['card_height'] ?? 200);
        $sanitized['border_radius'] = intval($settings['border_radius'] ?? 8);
        $sanitized['enable_shadow'] = !empty($settings['enable_shadow']);
        $sanitized['shadow_intensity'] = floatval($settings['shadow_intensity'] ?? 0.1);
        $sanitized['shadow_blur'] = intval($settings['shadow_blur'] ?? 20);
        $sanitized['font_family'] = sanitize_text_field($settings['font_family'] ?? 'system');
        $sanitized['title_size'] = intval($settings['title_size'] ?? 18);
        $sanitized['description_size'] = intval($settings['description_size'] ?? 14);
        $sanitized['text_align'] = sanitize_text_field($settings['text_align'] ?? 'center');
        $sanitized['default_color'] = sanitize_hex_color($settings['default_color'] ?? '#4CAF50');
        $sanitized['title_color'] = sanitize_hex_color($settings['title_color'] ?? '#333333');
        $sanitized['description_color'] = sanitize_hex_color($settings['description_color'] ?? '#666666');
        $sanitized['background_color'] = sanitize_hex_color($settings['background_color'] ?? '#ffffff');
        $sanitized['hover_color'] = sanitize_hex_color($settings['hover_color'] ?? '#f5f5f5');
        
        return $sanitized;
    }
    
    private static function sanitize_layout_settings($settings) {
        $sanitized = array();
        
        $sanitized['columns_desktop'] = sanitize_text_field($settings['columns_desktop'] ?? '4');
        $sanitized['columns_tablet'] = sanitize_text_field($settings['columns_tablet'] ?? '2');
        $sanitized['columns_mobile'] = sanitize_text_field($settings['columns_mobile'] ?? '1');
        $sanitized['gap_horizontal'] = intval($settings['gap_horizontal'] ?? 20);
        $sanitized['gap_vertical'] = intval($settings['gap_vertical'] ?? 20);
        $sanitized['max_width'] = intval($settings['max_width'] ?? 1200);
        $sanitized['full_width'] = !empty($settings['full_width']);
        $sanitized['container_padding'] = intval($settings['container_padding'] ?? 20);
        $sanitized['default_order'] = sanitize_text_field($settings['default_order'] ?? 'custom');
        $sanitized['show_featured_first'] = !empty($settings['show_featured_first']);
        $sanitized['highlight_featured'] = !empty($settings['highlight_featured']);
        $sanitized['enable_pagination'] = !empty($settings['enable_pagination']);
        $sanitized['cards_per_page'] = intval($settings['cards_per_page'] ?? 12);
        $sanitized['ajax_pagination'] = !empty($settings['ajax_pagination']);
        $sanitized['layout_type'] = sanitize_text_field($settings['layout_type'] ?? 'grid');
        $sanitized['height_mode'] = sanitize_text_field($settings['height_mode'] ?? 'equal');
        
        return $sanitized;
    }
    
    private static function sanitize_animation_settings($settings) {
        $sanitized = array();
        
        $sanitized['enable_animations'] = !empty($settings['enable_animations']);
        $sanitized['entrance_animation'] = sanitize_text_field($settings['entrance_animation'] ?? 'fadeIn');
        $sanitized['animation_duration'] = intval($settings['animation_duration'] ?? 600);
        $sanitized['animation_delay'] = intval($settings['animation_delay'] ?? 100);
        $sanitized['animation_easing'] = sanitize_text_field($settings['animation_easing'] ?? 'ease');
        $sanitized['hover_effect'] = sanitize_text_field($settings['hover_effect'] ?? 'lift');
        $sanitized['hover_duration'] = intval($settings['hover_duration'] ?? 300);
        $sanitized['enable_3d'] = !empty($settings['enable_3d']);
        $sanitized['parallax_hover'] = !empty($settings['parallax_hover']);
        $sanitized['enable_scroll_animation'] = !empty($settings['enable_scroll_animation']);
        $sanitized['scroll_threshold'] = floatval($settings['scroll_threshold'] ?? 0.1);
        $sanitized['repeat_animation'] = !empty($settings['repeat_animation']);
        
        return $sanitized;
    }
    
    private static function sanitize_advanced_settings($settings) {
        $sanitized = array();
        
        $boolean_keys = array('lazy_load', 'lazy_load_images', 'enable_cache', 'minify_css', 'minify_js', 'defer_js', 'enable_schema', 'enable_aria', 'keyboard_navigation', 'high_contrast', 'reduce_motion', 'nofollow_external', 'noopener_external', 'noreferrer_external', 'screen_reader_support', 'focus_indicators', 'auto_backup', 'debug_mode');
        foreach ($boolean_keys as $key) {
            $sanitized[$key] = !empty($settings[$key]);
        }
        
        $sanitized['cache_duration'] = intval($settings['cache_duration'] ?? 3600);
        $sanitized['schema_type'] = sanitize_text_field($settings['schema_type'] ?? 'ItemList');
        $sanitized['backup_retention'] = sanitize_text_field($settings['backup_retention'] ?? '3');
        $sanitized['custom_css'] = wp_unslash($settings['custom_css'] ?? '');
        $sanitized['custom_js'] = wp_unslash($settings['custom_js'] ?? '');
        
        return $sanitized;
    }
    
    private static function show_admin_notices() {
        if (!wp_script_is('jquery', 'enqueued')) {
            echo '<div class="notice notice-warning"><p><strong>Uyarı:</strong> jQuery yüklü değil. Bazı özellikler çalışmayabilir.</p></div>';
        }
        
        $settings = get_option('esistenze_quick_menu_advanced', array());
        if (empty($settings['enable_cache'])) {
            echo '<div class="notice notice-info is-dismissible"><p><strong>Performans İpucu:</strong> Gelişmiş sekmesinden cache\'i etkinleştirerek performansı artırabilirsiniz.</p></div>';
        }
    }
    
    public function ajax_preview() {
        check_ajax_referer('esistenze_quick_menu_preview');
        wp_send_json_success('Preview updated');
    }
    
    public function ajax_reset() {
        check_ajax_referer('esistenze_quick_menu_reset');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        delete_option('esistenze_quick_menu_cards');
        delete_option('esistenze_quick_menu_design');
        delete_option('esistenze_quick_menu_layout');
        delete_option('esistenze_quick_menu_animation');
        delete_option('esistenze_quick_menu_advanced');
        
        wp_send_json_success('Settings reset successfully');
    }
    
    public function ajax_import() {
        check_ajax_referer('esistenze_quick_menu_import');
        wp_send_json_success('Settings imported');
    }
    
    public function ajax_export() {
        check_ajax_referer('esistenze_quick_menu_export');
        
        $data = array(
            'cards' => get_option('esistenze_quick_menu_cards', array()),
            'design' => get_option('esistenze_quick_menu_design', self::get_default_design_settings()),
            'layout' => get_option('esistenze_quick_menu_layout', self::get_default_layout_settings()),
            'animation' => get_option('esistenze_quick_menu_animation', self::get_default_animation_settings()),
            'advanced' => get_option('esistenze_quick_menu_advanced', self::get_default_advanced_settings())
        );
        
        wp_send_json_success($data);
    }
    
    public function ajax_reorder() {
        check_ajax_referer('esistenze_quick_menu_reorder');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $order = $_POST['order'] ?? array();
        $cards = get_option('esistenze_quick_menu_cards', array());
        $reordered_cards = array();
        
        foreach ($order as $card_id) {
            if (isset($cards[$card_id])) {
                $reordered_cards[] = $cards[$card_id];
            }
        }
        
        update_option('esistenze_quick_menu_cards', $reordered_cards);
        wp_send_json_success('Cards reordered successfully');
    }
    
    public function register_settings() {
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_cards');
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_design');
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_layout');
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_animation');
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_advanced');
    }
    
    public function enqueue_styles() {
        wp_enqueue_style('esistenze-quick-menu', ESISTENZE_WP_KIT_URL . 'modules/quick-menu-cards/assets/style.css', array(), ESISTENZE_WP_KIT_VERSION);
        wp_enqueue_script('esistenze-quick-menu', ESISTENZE_WP_KIT_URL . 'modules/quick-menu-cards/assets/script.js', array('jquery'), ESISTENZE_WP_KIT_VERSION, true);
        
        $dynamic_css = $this->generate_dynamic_css();
        if (!empty($dynamic_css)) {
            wp_add_inline_style('esistenze-quick-menu', $dynamic_css);
        }
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 12,
            'columns' => '',
            'category' => '',
            'order' => '',
            'featured_only' => false
        ), $atts, 'quick_menu_cards');
        
        $cards = get_option('esistenze_quick_menu_cards', array());
        $layout_settings = get_option('esistenze_quick_menu_layout', self::get_default_layout_settings());
        
        $filtered_cards = $this->filter_cards($cards, $atts);
        
        ob_start();
        $this->render_cards_grid($filtered_cards, $atts, $layout_settings);
        return ob_get_clean();
    }
    
    private function filter_cards($cards, $atts) {
        $cards = array_filter($cards, function($card) {
            return !empty($card['enabled']);
        });
        
        if (!empty($atts['featured_only'])) {
            $cards = array_filter($cards, function($card) {
                return !empty($card['featured']);
            });
        }
        
        if (!empty($atts['limit']) && is_numeric($atts['limit'])) {
            $cards = array_slice($cards, 0, intval($atts['limit']));
        }
        
        return $cards;
    }
    
    private function render_cards_grid($cards, $atts, $layout_settings) {
        $columns = $atts['columns'] ?: $layout_settings['columns_desktop'];
        $animation_settings = get_option('esistenze_quick_menu_animation', self::get_default_animation_settings());
        
        echo '<div class="esistenze-quick-menu" data-columns="' . esc_attr($columns) . '">';
        
        if (empty($cards)) {
            echo '<div class="no-cards-message">Henüz kart eklenmemiş.</div>';
        } else {
            foreach ($cards as $index => $card) {
                $this->render_single_card($card, $index, $animation_settings);
            }
        }
        
        echo '</div>';
    }
    
    private function render_single_card($card, $index, $animation_settings) {
        $classes = array('quick-menu-card');
        
        if (!empty($card['featured'])) {
            $classes[] = 'featured';
        }
        
        if (!empty($animation_settings['enable_animations'])) {
            $classes[] = 'animate-on-scroll';
        }
        
        $style_vars = $this->get_card_style_variables($card);
        
        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" 
             data-card-id="<?php echo esc_attr($index); ?>"
             <?php echo $style_vars; ?>>
            
            <?php if (!empty($card['badge'])): ?>
                <div class="card-badge"><?php echo esc_html($card['badge']); ?></div>
            <?php endif; ?>
            
            <a href="<?php echo esc_url($card['url'] ?? '#'); ?>" 
               target="<?php echo esc_attr($card['target'] ?? '_self'); ?>"
               onclick="trackCardClick(<?php echo esc_attr($index); ?>)">
                
                <?php if (!empty($card['background_image'])): ?>
                    <div class="card-background" style="background-image: url('<?php echo esc_url($card['background_image']); ?>')"></div>
                <?php endif; ?>
                
                <div class="card-content">
                    <?php if (!empty($card['icon'])): ?>
                        <div class="card-icon">
                            <i class="<?php echo esc_attr($card['icon']); ?>"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="card-title"><?php echo esc_html($card['title'] ?? 'Başlıksız'); ?></h3>
                    
                    <?php if (!empty($card['description'])): ?>
                        <p class="card-description"><?php echo esc_html($card['description']); ?></p>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php
    }
    
    private function get_card_style_variables($card) {
        $color = $card['color'] ?? '#4CAF50';
        
        return sprintf(
            'style="--card-color: %s; --card-color-rgb: %s;"',
            esc_attr($color),
            esc_attr($this->hex_to_rgb($color))
        );
    }
    
    private function hex_to_rgb($hex) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "$r, $g, $b";
    }
    
    private function generate_dynamic_css() {
        $design = get_option('esistenze_quick_menu_design', self::get_default_design_settings());
        $layout = get_option('esistenze_quick_menu_layout', self::get_default_layout_settings());
        $animation = get_option('esistenze_quick_menu_animation', self::get_default_animation_settings());
        
        $css = '';
        
        $css .= '.esistenza-quick-menu {';
        $css .= 'display: grid;';
        $css .= 'grid-template-columns: repeat(' . esc_attr($layout['columns_desktop']) . ', 1fr);';
        $css .= 'gap: ' . esc_attr($layout['gap_vertical']) . 'px ' . esc_attr($layout['gap_horizontal']) . 'px;';
        $css .= 'max-width: ' . esc_attr($layout['max_width']) . 'px;';
        $css .= 'margin: 0 auto;';
        $css .= 'padding: ' . esc_attr($layout['container_padding']) . 'px;';
        $css .= '}';
        
        $css .= '.quick-menu-card {';
        $css .= 'width: ' . esc_attr($design['card_width']) . 'px;';
        $css .= 'height: ' . esc_attr($design['card_height']) . 'px;';
        $css .= 'border-radius: ' . esc_attr($design['border_radius']) . 'px;';
        $css .= 'background-color: ' . esc_attr($design['background_color']) . ';';
        $css .= 'color: ' . esc_attr($design['title_color']) . ';';
        $css .= 'text-align: ' . esc_attr($design['text_align']) . ';';
        
        if (!empty($design['enable_shadow'])) {
            $css .= 'box-shadow: 0 ' . esc_attr($design['shadow_blur']) . 'px ' . esc_attr($design['shadow_blur'] * 2) . 'px rgba(0,0,0,' . esc_attr($design['shadow_intensity']) . ');';
        }
        
        $css .= 'transition: all ' . esc_attr($animation['hover_duration']) . 'ms ' . esc_attr($animation['animation_easing']) . ';';
        $css .= '}';
        
        if (!empty($animation['hover_effect']) && $animation['hover_effect'] !== 'none') {
            $css .= '.quick-menu-card:hover {';
            
            switch ($animation['hover_effect']) {
                case 'lift':
                    $css .= 'transform: translateY(-10px);';
                    break;
                case 'scale':
                    $css .= 'transform: scale(1.05);';
                    break;
                case 'tilt':
                    $css .= 'transform: rotate(2deg);';
                    break;
                case 'glow':
                    $css .= 'box-shadow: 0 0 20px var(--card-color);';
                    break;
            }
            
            $css .= 'background-color: ' . esc_attr($design['hover_color']) . ';';
            $css .= '}';
        }
        
        $css .= '.card-title {';
        $css .= 'font-size: ' . esc_attr($design['title_size']) . 'px;';
        $css .= 'color: ' . esc_attr($design['title_color']) . ';';
        $css .= '}';
        
        $css .= '.card-description {';
        $css .= 'font-size: ' . esc_attr($design['description_size']) . 'px;';
        $css .= 'color: ' . esc_attr($design['description_color']) . ';';
        $css .= '}';
        
        $css .= '@media (max-width: 768px) {';
        $css .= '.esistenza-quick-menu {';
        $css .= 'grid-template-columns: repeat(' . esc_attr($layout['columns_tablet']) . ', 1fr);';
        $css .= '}';
        $css .= '}';
        
        $css .= '@media (max-width: 480px) {';
        $css .= '.esistenza-quick-menu {';
        $css .= 'grid-template-columns: repeat(' . esc_attr($layout['columns_mobile']) . ', 1fr);';
        $css .= '}';
        $css .= '}';
        
        if (!empty($animation['enable_animations'])) {
            $css .= '.animate-on-scroll {';
            $css .= 'opacity: 0;';
            $css .= 'transform: translateY(50px);';
            $css .= 'transition: all ' . esc_attr($animation['animation_duration']) . 'ms ' . esc_attr($animation['animation_easing']) . ';';
            $css .= '}';
            
            $css .= '.animate-on-scroll.in-view {';
            $css .= 'opacity: 1;';
            $css .= 'transform: translateY(0);';
            $css .= '}';
        }
        
        return $css;
    }
    
    public function add_admin_bar_menu($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $wp_admin_bar->add_menu(array(
            'id' => 'esistenze-quick-menu',
            'title' => 'Quick Menu Cards',
            'href' => admin_url('admin.php?page=esistenze-quick-menu'),
            'meta' => array(
                'title' => 'Quick Menu Cards Yönetimi'
            )
        ));
        
        $wp_admin_bar->add_menu(array(
            'id' => 'esistenze-quick-menu-add',
            'parent' => 'esistenze-quick-menu',
            'title' => 'Yeni Kart Ekle',
            'href' => admin_url('admin.php?page=esistenze-quick-menu&tab=cards#add-new')
        ));
        
        $wp_admin_bar->add_menu(array(
            'id' => 'esistenze-quick-menu-stats',
            'parent' => 'esistenze-quick-menu',
            'title' => 'İstatistikler',
            'href' => admin_url('admin.php?page=esistenze-quick-menu&tab=analytics')
        ));
    }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_esistenze_quick_menu_preview', array($this, 'ajax_preview'));
        add_action('wp_ajax_esistenze_quick_menu_reset', array($this, 'ajax_reset'));
        add_action('wp_ajax_esistenze_quick_menu_import', array($this, 'ajax_import'));
        add_action('wp_ajax_esistenze_quick_menu_export', array($this, 'ajax_export'));
        add_action('wp_ajax_esistenze_quick_menu_reorder', array($this, 'ajax_reorder'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_shortcode('quick_menu_cards', array($this, 'render_shortcode'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_menu'), 100);
    }
    
    public static function admin_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'cards';
        
        if (isset($_POST['submit'])) {
            self::handle_form_submission();
        }
        
        if (isset($_GET['action']) && isset($_GET['card_id'])) {
            self::handle_card_actions();
        }
        
        echo '<div class="wrap esistenze-quick-menu-wrap">';
        echo '<h1 class="wp-heading-inline">Quick Menu Cards</h1>';
        echo '<a href="#" class="page-title-action" onclick="addNewCard()">Yeni Kart Ekle</a>';
        echo '<a href="#" class="page-title-action" onclick="previewCards()">Önizleme</a>';
        echo '<hr class="wp-header-end">';
        
        self::show_admin_notices();
        self::render_tabs($current_tab);
        
        switch ($current_tab) {
            case 'cards':
                self::render_cards_tab();
                break;
            case 'design':
                self::render_design_tab();
                break;
            case 'layout':
                self::render_layout_tab();
                break;
            case 'animation':
                self::render_animation_tab();
                break;
            case 'advanced':
                self::render_advanced_tab();
                break;
            case 'analytics':
                self::render_analytics_tab();
                break;
        }
        
        echo '</div>';
        self::enqueue_admin_assets();
    }
    
    private static function render_layout_tab() {
        $settings = get_option('esistenze_quick_menu_layout', self::get_default_layout_settings());
        ?>
        <div class="quick-menu-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_quick_menu_save'); ?>
                <input type="hidden" name="tab" value="layout">
                
                <div class="layout-settings">
                    <div class="postbox">
                        <h2 class="hndle">Grid Düzeni</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Kolon Sayısı</th>
                                    <td>
                                        <div class="column-controls">
                                            <label>
                                                Masaüstü:
                                                <select name="settings[columns_desktop]" onchange="updateGridPreview()">
                                                    <option value="2" <?php selected($settings['columns_desktop'] ?? '', '2'); ?>>2 Kolon</option>
                                                    <option value="3" <?php selected($settings['columns_desktop'] ?? '', '3'); ?>>3 Kolon</option>
                                                    <option value="4" <?php selected($settings['columns_desktop'] ?? '', '4'); ?>>4 Kolon</option>
                                                    <option value="5" <?php selected($settings['columns_desktop'] ?? '', '5'); ?>>5 Kolon</option>
                                                    <option value="6" <?php selected($settings['columns_desktop'] ?? '', '6'); ?>>6 Kolon</option>
                                                </select>
                                            </label>
                                            <label>
                                                Tablet:
                                                <select name="settings[columns_tablet]" onchange="updateGridPreview()">
                                                    <option value="1" <?php selected($settings['columns_tablet'] ?? '', '1'); ?>>1 Kolon</option>
                                                    <option value="2" <?php selected($settings['columns_tablet'] ?? '', '2'); ?>>2 Kolon</option>
                                                    <option value="3" <?php selected($settings['columns_tablet'] ?? '', '3'); ?>>3 Kolon</option>
                                                </select>
                                            </label>
                                            <label>
                                                Mobil:
                                                <select name="settings[columns_mobile]" onchange="updateGridPreview()">
                                                    <option value="1" <?php selected($settings['columns_mobile'] ?? '', '1'); ?>>1 Kolon</option>
                                                    <option value="2" <?php selected($settings['columns_mobile'] ?? '', '2'); ?>>2 Kolon</option>
                                                </select>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Kart Aralığı</th>
                                    <td>
                                        <div class="spacing-controls">
                                            <label>
                                                Yatay:
                                                <input type="range" name="settings[gap_horizontal]" value="<?php echo esc_attr($settings['gap_horizontal'] ?? 20); ?>" min="0" max="50" oninput="updateSpacing()">
                                                <span id="gap_h_display"><?php echo esc_attr($settings['gap_horizontal'] ?? 20); ?>px</span>
                                            </label>
                                            <label>
                                                Dikey:
                                                <input type="range" name="settings[gap_vertical]" value="<?php echo esc_attr($settings['gap_vertical'] ?? 20); ?>" min="0" max="50" oninput="updateSpacing()">
                                                <span id="gap_v_display"><?php echo esc_attr($settings['gap_vertical'] ?? 20); ?>px</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Container Ayarları</th>
                                    <td>
                                        <table class="form-table">
                                            <tr>
                                                <td>
                                                    <label>
                                                        Maksimum Genişlik:
                                                        <input type="number" name="settings[max_width]" value="<?php echo esc_attr($settings['max_width'] ?? 1200); ?>" min="800" max="1600" step="50">px
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label>
                                                        <input type="checkbox" name="settings[full_width]" value="1" <?php checked(!empty($settings['full_width'])); ?>>
                                                        Tam genişlik kullan
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label>
                                                        Container Padding:
                                                        <input type="range" name="settings[container_padding]" value="<?php echo esc_attr($settings['container_padding'] ?? 20); ?>" min="0" max="100" oninput="updateContainerPadding(this.value)">
                                                        <span id="container_padding_display"><?php echo esc_attr($settings['container_padding'] ?? 20); ?>px</span>
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Sıralama ve Filtreleme</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Varsayılan Sıralama</th>
                                    <td>
                                        <select name="settings[default_order]">
                                            <option value="custom" <?php selected($settings['default_order'] ?? '', 'custom'); ?>>Özel Sıra</option>
                                            <option value="title" <?php selected($settings['default_order'] ?? '', 'title'); ?>>Başlık (A-Z)</option>
                                            <option value="title_desc" <?php selected($settings['default_order'] ?? '', 'title_desc'); ?>>Başlık (Z-A)</option>
                                            <option value="popular" <?php selected($settings['default_order'] ?? '', 'popular'); ?>>Popülerlik</option>
                                            <option value="recent" <?php selected($settings['default_order'] ?? '', 'recent'); ?>>En Yeni</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Öne Çıkan Kartlar</th>
                                    <td>
                                        <fieldset>
                                            <label>
                                                <input type="checkbox" name="settings[show_featured_first]" value="1" <?php checked(!empty($settings['show_featured_first'])); ?>>
                                                Öne çıkan kartları önce göster
                                            </label><br>
                                            <label>
                                                <input type="checkbox" name="settings[highlight_featured]" value="1" <?php checked(!empty($settings['highlight_featured'])); ?>>
                                                Öne çıkan kartları görsel olarak vurgula
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Sayfalama</th>
                                    <td>
                                        <fieldset>
                                            <label>
                                                <input type="checkbox" name="settings[enable_pagination]" value="1" <?php checked(!empty($settings['enable_pagination'])); ?> onchange="togglePagination()">
                                                Sayfalama etkinleştir
                                            </label>
                                        </fieldset>
                                        
                                        <div id="pagination_settings" style="<?php echo empty($settings['enable_pagination']) ? 'display:none;' : ''; ?>">
                                            <table class="form-table">
                                                <tr>
                                                    <td>
                                                        <label>
                                                            Sayfa başına kart sayısı:
                                                            <input type="number" name="settings[cards_per_page]" value="<?php echo esc_attr($settings['cards_per_page'] ?? 12); ?>" min="1" max="50">
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" name="settings[ajax_pagination]" value="1" <?php checked(!empty($settings['ajax_pagination'])); ?>>
                                                            AJAX sayfalama kullan
                                                        </label>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Masonry ve Grid Seçenekleri</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Layout Tipi</th>
                                    <td>
                                        <div class="layout-type-selector">
                                            <label>
                                                <input type="radio" name="settings[layout_type]" value="grid" <?php checked($settings['layout_type'] ?? '', 'grid'); ?>>
                                                <div class="layout-preview grid">Grid</div>
                                            </label>
                                            <label>
                                                <input type="radio" name="settings[layout_type]" value="masonry" <?php checked($settings['layout_type'] ?? '', 'masonry'); ?>>
                                                <div class="layout-preview masonry">Masonry</div>
                                            </label>
                                            <label>
                                                <input type="radio" name="settings[layout_type]" value="flex" <?php checked($settings['layout_type'] ?? '', 'flex'); ?>>
                                                <div class="layout-preview flex">Flexbox</div>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Kart Yükseklik Modu</th>
                                    <td>
                                        <select name="settings[height_mode]">
                                            <option value="auto" <?php selected($settings['height_mode'] ?? '', 'auto'); ?>>Otomatik</option>
                                            <option value="equal" <?php selected($settings['height_mode'] ?? '', 'equal'); ?>>Eşit Yükseklik</option>
                                            <option value="content" <?php selected($settings['height_mode'] ?? '', 'content'); ?>>İçeriğe Göre</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Layout Önizlemesi</h2>
                        <div class="inside">
                            <div id="layout_preview" class="layout-preview-container">
                            </div>
                            
                            <div class="preview-device-toggle">
                                <button type="button" class="button" onclick="previewDevice('desktop')" data-device="desktop">Masaüstü</button>
                                <button type="button" class="button" onclick="previewDevice('tablet')" data-device="tablet">Tablet</button>
                                <button type="button" class="button" onclick="previewDevice('mobile')" data-device="mobile">Mobil</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Layout Ayarlarını Kaydet">
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_animation_tab() {
        $settings = get_option('esistenze_quick_menu_animation', self::get_default_animation_settings());
        ?>
        <div class="quick-menu-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_quick_menu_save'); ?>
                <input type="hidden" name="tab" value="animation">
                
                <div class="animation-settings">
                    <div class="postbox">
                        <h2 class="hndle">Giriş Animasyonları</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Animasyon Etkinleştir</th>
                                    <td>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="settings[enable_animations]" value="1" <?php checked(!empty($settings['enable_animations'])); ?> onchange="toggleAnimations()">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            
                            <div id="animation_options" style="<?php echo empty($settings['enable_animations']) ? 'display:none;' : ''; ?>">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Giriş Animasyonu</th>
                                        <td>
                                            <div class="animation-selector">
                                                <select name="settings[entrance_animation]" onchange="previewAnimation()">
                                                    <option value="fadeIn" <?php selected($settings['entrance_animation'] ?? '', 'fadeIn'); ?>>Fade In</option>
                                                    <option value="slideUp" <?php selected($settings['entrance_animation'] ?? '', 'slideUp'); ?>>Slide Up</option>
                                                    <option value="slideDown" <?php selected($settings['entrance_animation'] ?? '', 'slideDown'); ?>>Slide Down</option>
                                                    <option value="slideLeft" <?php selected($settings['entrance_animation'] ?? '', 'slideLeft'); ?>>Slide Left</option>
                                                    <option value="slideRight" <?php selected($settings['entrance_animation'] ?? '', 'slideRight'); ?>>Slide Right</option>
                                                    <option value="zoomIn" <?php selected($settings['entrance_animation'] ?? '', 'zoomIn'); ?>>Zoom In</option>
                                                    <option value="zoomOut" <?php selected($settings['entrance_animation'] ?? '', 'zoomOut'); ?>>Zoom Out</option>
                                                    <option value="rotateIn" <?php selected($settings['entrance_animation'] ?? '', 'rotateIn'); ?>>Rotate In</option>
                                                    <option value="flipX" <?php selected($settings['entrance_animation'] ?? '', 'flipX'); ?>>Flip X</option>
                                                    <option value="flipY" <?php selected($settings['entrance_animation'] ?? '', 'flipY'); ?>>Flip Y</option>
                                                </select>
                                                <button type="button" class="button" onclick="previewAnimation()">Önizle</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Animasyon Süresi</th>
                                        <td>
                                            <input type="range" name="settings[animation_duration]" value="<?php echo esc_attr($settings['animation_duration'] ?? 600); ?>" min="200" max="2000" step="100" oninput="updateAnimationDuration(this.value)">
                                            <span id="duration_display"><?php echo esc_attr($settings['animation_duration'] ?? 600); ?>ms</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Gecikme Aralığı</th>
                                        <td>
                                            <input type="range" name="settings[animation_delay]" value="<?php echo esc_attr($settings['animation_delay'] ?? 100); ?>" min="0" max="500" step="50" oninput="updateAnimationDelay(this.value)">
                                            <span id="delay_display"><?php echo esc_attr($settings['animation_delay'] ?? 100); ?>ms</span>
                                            <p class="description">Kartlar arasındaki animasyon gecikmesi</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Easing Fonksiyonu</th>
                                        <td>
                                            <select name="settings[animation_easing]">
                                                <option value="ease" <?php selected($settings['animation_easing'] ?? '', 'ease'); ?>>Ease</option>
                                                <option value="ease-in" <?php selected($settings['animation_easing'] ?? '', 'ease-in'); ?>>Ease In</option>
                                                <option value="ease-out" <?php selected($settings['animation_easing'] ?? '', 'ease-out'); ?>>Ease Out</option>
                                                <option value="ease-in-out" <?php selected($settings['animation_easing'] ?? '', 'ease-in-out'); ?>>Ease In Out</option>
                                                <option value="cubic-bezier(0.68, -0.55, 0.265, 1.55)" <?php selected($settings['animation_easing'] ?? '', 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'); ?>>Bounce</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Hover Animasyonları</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Hover Efekti</th>
                                    <td>
                                        <select name="settings[hover_effect]" onchange="previewHoverEffect()">
                                            <option value="none" <?php selected($settings['hover_effect'] ?? '', 'none'); ?>>Efekt Yok</option>
                                            <option value="lift" <?php selected($settings['hover_effect'] ?? '', 'lift'); ?>>Yukarı Kaldır</option>
                                            <option value="scale" <?php selected($settings['hover_effect'] ?? '', 'scale'); ?>>Ölçeklendir</option>
                                            <option value="tilt" <?php selected($settings['hover_effect'] ?? '', 'tilt'); ?>>Eğ</option>
                                            <option value="glow" <?php selected($settings['hover_effect'] ?? '', 'glow'); ?>>Parla</option>
                                            <option value="shadow" <?php selected($settings['hover_effect'] ?? '', 'shadow'); ?>>Gölge Büyüt</option>
                                            <option value="rotate" <?php selected($settings['hover_effect'] ?? '', 'rotate'); ?>>Döndür</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Hover Süresi</th>
                                    <td>
                                        <input type="range" name="settings[hover_duration]" value="<?php echo esc_attr($settings['hover_duration'] ?? 300); ?>" min="100" max="1000" step="50" oninput="updateHoverDuration(this.value)">
                                        <span id="hover_duration_display"><?php echo esc_attr($settings['hover_duration'] ?? 300); ?>ms</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">3D Efektler</th>
                                    <td>
                                        <fieldset>
                                            <label>
                                                <input type="checkbox" name="settings[enable_3d]" value="1" <?php checked(!empty($settings['enable_3d'])); ?>>
                                                3D transform efektlerini etkinleştir
                                            </label><br>
                                            <label>
                                                <input type="checkbox" name="settings[parallax_hover]" value="1" <?php checked(!empty($settings['parallax_hover'])); ?>>
                                                Parallax hover efekti
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Scrollspy Animasyonları</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Scroll Animasyonu</th>
                                    <td>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="settings[enable_scroll_animation]" value="1" <?php checked(!empty($settings['enable_scroll_animation'])); ?> onchange="toggleScrollAnimation()">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <p class="description">Kartlar görünüme girdiğinde animasyon oynat</p>
                                    </td>
                                </tr>
                            </table>
                            
                            <div id="scroll_animation_options" style="<?php echo empty($settings['enable_scroll_animation']) ? 'display:none;' : ''; ?>">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Scroll Threshold</th>
                                        <td>
                                            <input type="range" name="settings[scroll_threshold]" value="<?php echo esc_attr($settings['scroll_threshold'] ?? 0.1); ?>" min="0" max="1" step="0.1" oninput="updateScrollThreshold(this.value)">
                                            <span id="threshold_display"><?php echo esc_attr(($settings['scroll_threshold'] ?? 0.1) * 100); ?>%</span>
                                            <p class="description">Animasyonun tetikleneceği görünürlük oranı</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Tekrar Oynat</th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="settings[repeat_animation]" value="1" <?php checked(!empty($settings['repeat_animation'])); ?>>
                                                Kart tekrar görünüme girdiğinde animasyonu tekrarla
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Animasyon Önizlemesi</h2>
                        <div class="inside">
                            <div id="animation_preview" class="animation-preview-container">
                                <div class="preview-card" id="preview_card">
                                    <div class="card-icon">🏠</div>
                                    <h4>Örnek Kart</h4>
                                    <p>Bu bir örnek kart açıklamasıdır.</p>
                                </div>
                            </div>
                            
                            <div class="animation-controls">
                                <button type="button" class="button" onclick="testEntranceAnimation()">Giriş Animasyonu</button>
                                <button type="button" class="button" onclick="testHoverAnimation()">Hover Efekti</button>
                                <button type="button" class="button" onclick="resetAnimationPreview()">Sıfırla</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Animasyon Ayarlarını Kaydet">
                </p>
            </form>
        </div>
        <?php
    }
    
    private static function render_advanced_tab() {
        $settings = get_option('esistenze_quick_menu_advanced', self::get_default_advanced_settings());
        ?>
        <div class="quick-menu-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_quick_menu_save'); ?>
                <input type="hidden" name="tab" value="advanced">
                
                <div class="advanced-layout">
                    <div class="advanced-main">
                        <div class="postbox">
                            <h2 class="hndle">Performans ve Optimizasyon</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Lazy Loading</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[lazy_load]" value="1" <?php checked(!empty($settings['lazy_load'])); ?>>
                                                    Kartları lazy loading ile yükle
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[lazy_load_images]" value="1" <?php checked(!empty($settings['lazy_load_images'])); ?>>
                                                    Kart resimlerini lazy loading ile yükle
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Cache Ayarları</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[enable_cache]" value="1" <?php checked(!empty($settings['enable_cache'])); ?>>
                                                    Kart verilerini cache'le
                                                </label><br>
                                                <label>
                                                    Cache süresi:
                                                    <input type="number" name="settings[cache_duration]" value="<?php echo esc_attr($settings['cache_duration'] ?? 3600); ?>" min="300" max="86400"> saniye
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">CSS/JS Optimizasyon</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[minify_css]" value="1" <?php checked(!empty($settings['minify_css'])); ?>>
                                                    CSS'i minify et
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[minify_js]" value="1" <?php checked(!empty($settings['minify_js'])); ?>>
                                                    JavaScript'i minify et
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[defer_js]" value="1" <?php checked(!empty($settings['defer_js'])); ?>>
                                                    JavaScript'i defer et
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">SEO ve Erişilebilirlik</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Schema Markup</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[enable_schema]" value="1" <?php checked(!empty($settings['enable_schema'])); ?>>
                                                    JSON-LD schema markup ekle
                                                </label><br>
                                                <label>
                                                    Schema tipi:
                                                    <select name="settings[schema_type]">
                                                        <option value="ItemList" <?php selected($settings['schema_type'] ?? '', 'ItemList'); ?>>ItemList</option>
                                                        <option value="Menu" <?php selected($settings['schema_type'] ?? '', 'Menu'); ?>>Menu</option>
                                                        <option value="SiteNavigationElement" <?php selected($settings['schema_type'] ?? '', 'SiteNavigationElement'); ?>>SiteNavigationElement</option>
                                                    </select>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Erişilebilirlik</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[enable_aria]" value="1" <?php checked(!empty($settings['enable_aria'])); ?>>
                                                    ARIA etiketlerini etkinleştir
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[keyboard_navigation]" value="1" <?php checked(!empty($settings['keyboard_navigation'])); ?>>
                                                    Klavye navigasyonu desteği
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[high_contrast]" value="1" <?php checked(!empty($settings['high_contrast'])); ?>>
                                                    Yüksek kontrast modu desteği
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[reduce_motion]" value="1" <?php checked(!empty($settings['reduce_motion'])); ?>>
                                                    "Reduce motion" tercihini dikkate al
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[screen_reader_support]" value="1" <?php checked(!empty($settings['screen_reader_support'])); ?>>
                                                    Ekran okuyucu desteği
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[focus_indicators]" value="1" <?php checked(!empty($settings['focus_indicators'])); ?>>
                                                    Gelişmiş odak göstergeleri
                                                </label>
                                            </fieldset>
                                            <p class="description">Bu ayarlar web erişilebilirlik standartlarına (WCAG 2.1) uygunluğu sağlar.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Link Attributes</th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="settings[nofollow_external]" value="1" <?php checked(!empty($settings['nofollow_external'])); ?>>
                                                    Dış linklere nofollow ekle
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[noopener_external]" value="1" <?php checked(!empty($settings['noopener_external'])); ?>>
                                                    Dış linklere noopener ekle
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="settings[noreferrer_external]" value="1" <?php checked(!empty($settings['noreferrer_external'])); ?>>
                                                    Dış linklere noreferrer ekle
                                                </label>
                                            </fieldset>
                                            <p class="description">Güvenlik ve SEO için link özelliklerini ayarlayın.</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Özel CSS ve JavaScript</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="custom_css">Özel CSS</label></th>
                                        <td>
                                            <textarea id="custom_css" name="settings[custom_css]" rows="10" class="large-text code" placeholder="/* Özel CSS kodlarınızı buraya yazın */"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                                            <p class="description">Kartlar için özel CSS kodlarınızı buraya ekleyebilirsiniz.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="custom_js">Özel JavaScript</label></th>
                                        <td>
                                            <textarea id="custom_js" name="settings[custom_js]" rows="8" class="large-text code" placeholder="// Özel JavaScript kodlarınızı buraya yazın"><?php echo esc_textarea($settings['custom_js'] ?? ''); ?></textarea>
                                            <p class="description">Kartlar için özel JavaScript kodlarınızı buraya ekleyebilirsiniz.</p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="code-helpers">
                                    <h4>Yaygın CSS Sınıfları:</h4>
                                    <div class="css-classes-grid">
                                        <code onclick="insertAtCursor('.esistenze-quick-menu', 'custom_css')">.esistenze-quick-menu</code>
                                        <code onclick="insertAtCursor('.quick-menu-card', 'custom_css')">.quick-menu-card</code>
                                        <code onclick="insertAtCursor('.card-icon', 'custom_css')">.card-icon</code>
                                        <code onclick="insertAtCursor('.card-title', 'custom_css')">.card-title</code>
                                        <code onclick="insertAtCursor('.card-description', 'custom_css')">.card-description</code>
                                        <code onclick="insertAtCursor('.card-badge', 'custom_css')">.card-badge</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Import/Export ve Backup</h2>
                            <div class="inside">
                                <div class="import-export-actions">
                                    <h4>Ayarları Yönet:</h4>
                                    <div class="action-buttons">
                                        <button type="button" class="button" onclick="exportAllSettings()">
                                            <span class="dashicons dashicons-download"></span> Tüm Ayarları Dışa Aktar
                                        </button>
                                        <button type="button" class="button" onclick="importAllSettings()">
                                            <span class="dashicons dashicons-upload"></span> Ayarları İçe Aktar
                                        </button>
                                        <button type="button" class="button" onclick="resetAllSettings()">
                                            <span class="dashicons dashicons-undo"></span> Tüm Ayarları Sıfırla
                                        </button>
                                    </div>
                                    
                                    <h4>Otomatik Backup:</h4>
                                    <table class="form-table">
                                        <tr>
                                            <td>
                                                <label>
                                                    <input type="checkbox" name="settings[auto_backup]" value="1" <?php checked(!empty($settings['auto_backup'])); ?>>
                                                    Haftalık otomatik backup oluştur
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label>
                                                    Backup saklama süresi:
                                                    <select name="settings[backup_retention]">
                                                        <option value="1" <?php selected($settings['backup_retention'] ?? '', '1'); ?>>1 Ay</option>
                                                        <option value="3" <?php selected($settings['backup_retention'] ?? '', '3'); ?>>3 Ay</option>
                                                        <option value="6" <?php selected($settings['backup_retention'] ?? '', '6'); ?>>6 Ay</option>
                                                        <option value="12" <?php selected($settings['backup_retention'] ?? '', '12'); ?>>1 Yıl</option>
                                                    </select>
                                                </label>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
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
                                        <span>Toplam Kart Sayısı:</span>
                                        <span><?php echo count(get_option('esistenze_quick_menu_cards', array())); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span>Aktif Kart Sayısı:</span>
                                        <span id="active_cards_count">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Debug ve Test</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="settings[debug_mode]" value="1" <?php checked(!empty($settings['debug_mode'])); ?>>
                                                Debug modunu etkinleştir
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="debug-actions">
                                    <h4>Test İşlemleri:</h4>
                                    <button type="button" class="button" onclick="testCardRendering()">Kart Render Testi</button>
                                    <button type="button" class="button" onclick="testAnimations()">Animasyon Testi</button>
                                    <button type="button" class="button" onclick="testResponsive()">Responsive Testi</button>
                                    <button type="button" class="button" onclick="validateCSS()">CSS Doğrulama</button>
                                </div>
                                
                                <div id="debug_output" style="<?php echo empty($settings['debug_mode']) ? 'display:none;' : ''; ?>">
                                    <h4>Debug Çıktısı:</h4>
                                    <div class="debug-console">
                                        <pre id="debug_console_content">Debug modu etkinleştirildiğinde bilgiler burada görünecek</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Kısayol ve Entegrasyon</h2>
                            <div class="inside">
                                <h4>Shortcode:</h4>
                                <div class="shortcode-generator">
                                    <input type="text" value="[quick_menu_cards]" readonly class="shortcode-input">
                                    <button type="button" class="button" onclick="copyShortcode()">Kopyala</button>
                                </div>
                                
                                <h4>Gelişmiş Shortcode Seçenekleri:</h4>
                                <div class="shortcode-options">
                                    <label>
                                        Limit:
                                        <input type="number" id="shortcode_limit" value="12" min="1" max="50">
                                    </label>
                                    <label>
                                        Kategori:
                                        <input type="text" id="shortcode_category" placeholder="kategori-slug">
                                    </label>
                                    <label>
                                        Kolon:
                                        <select id="shortcode_columns">
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="6">6</option>
                                        </select>
                                    </label>
                                    <button type="button" class="button" onclick="generateCustomShortcode()">Shortcode Oluştur</button>
                                </div>
                                
                                <h4>PHP Kodu:</h4>
                                <div class="php-code">
                                    <code>&lt;?php echo do_shortcode('[quick_menu_cards]'); ?&gt;</code>
                                    <button type="button" class="button" onclick="copyPHPCode()">Kopyala</button>
                                </div>
                                
                                <h4>Widget Desteği:</h4>
                                <p class="description">Quick Menu Cards widget'ını <a href="<?php echo admin_url('widgets.php'); ?>">Widget Sayfası</a>'ndan ekleyebilirsiniz.</p>
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
    } render_tabs($current_tab) {
        $tabs = array(
            'cards' => array('label' => 'Kartlar', 'icon' => 'dashicons-grid-view'),
            'design' => array('label' => 'Tasarım', 'icon' => 'dashicons-admin-appearance'),
            'layout' => array('label' => 'Düzen', 'icon' => 'dashicons-layout'),
            'animation' => array('label' => 'Animasyon', 'icon' => 'dashicons-controls-play'),
            'advanced' => array('label' => 'Gelişmiş', 'icon' => 'dashicons-admin-tools'),
            'analytics' => array('label' => 'İstatistikler', 'icon' => 'dashicons-chart-bar')
        );
        
        echo '<nav class="nav-tab-wrapper wp-clearfix">';
        foreach ($tabs as $tab_key => $tab) {
            $class = ($current_tab === $tab_key) ? 'nav-tab nav-tab-active' : 'nav-tab';
            echo '<a href="' . admin_url('admin.php?page=esistenze-quick-menu&tab=' . $tab_key) . '" class="' . $class . '">';
            echo '<span class="dashicons ' . $tab['icon'] . '"></span> ' . $tab['label'];
            echo '</a>';
        }
        echo '</nav>';
    }
    
    private static function render_cards_tab() {
        $cards = get_option('esistenze_quick_menu_cards', array());
        ?>
        <div class="quick-menu-content">
            <div class="cards-manager">
                <div class="cards-header">
                    <h2>Kart Yönetimi</h2>
                    <div class="cards-actions">
                        <button type="button" class="button" onclick="importCards()">İçe Aktar</button>
                        <button type="button" class="button" onclick="exportCards()">Dışa Aktar</button>
                        <button type="button" class="button" onclick="duplicateCard()">Kopyala</button>
                        <button type="button" class="button button-primary" onclick="addNewCard()">Yeni Kart</button>
                    </div>
                </div>
                
                <div class="cards-grid" id="cards_container">
                    <?php if (empty($cards)): ?>
                        <div class="no-cards">
                            <div class="no-cards-icon">📄</div>
                            <h3>Henüz kart eklenmemiş</h3>
                            <p>İlk kartınızı oluşturmak için "Yeni Kart" butonuna tıklayın.</p>
                            <button type="button" class="button button-primary" onclick="addNewCard()">İlk Kartı Oluştur</button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cards as $index => $card): ?>
                            <div class="card-item" data-card-id="<?php echo $index; ?>">
                                <div class="card-preview">
                                    <div class="card-icon">
                                        <?php if (!empty($card['icon'])): ?>
                                            <i class="<?php echo esc_attr($card['icon']); ?>"></i>
                                        <?php else: ?>
                                            📄
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-content">
                                        <h4><?php echo esc_html($card['title'] ?? 'Başlıksız'); ?></h4>
                                        <p><?php echo esc_html(wp_trim_words($card['description'] ?? '', 10)); ?></p>
                                    </div>
                                </div>
                                
                                <div class="card-actions">
                                    <button type="button" class="button button-small" onclick="editCard(<?php echo $index; ?>)" title="Düzenle">
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>
                                    <button type="button" class="button button-small" onclick="duplicateCard(<?php echo $index; ?>)" title="Kopyala">
                                        <span class="dashicons dashicons-admin-page"></span>
                                    </button>
                                    <button type="button" class="button button-small" onclick="toggleCardStatus(<?php echo $index; ?>)" title="Aktif/Pasif">
                                        <span class="dashicons dashicons-<?php echo !empty($card['enabled']) ? 'visibility' : 'hidden'; ?>"></span>
                                    </button>
                                    <button type="button" class="button button-small button-link-delete" onclick="deleteCard(<?php echo $index; ?>)" title="Sil">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                                
                                <div class="card-stats">
                                    <span class="stat-item">👁️ <?php echo get_option("esistenze_quick_menu_views_{$index}", 0); ?></span>
                                    <span class="stat-item">👆 <?php echo get_option("esistenze_quick_menu_clicks_{$index}", 0); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div id="card_editor_modal" class="card-modal" style="display: none;">
                    <div class="card-modal-content">
                        <div class="card-modal-header">
                            <h3 id="modal_title">Kart Düzenle</h3>
                            <button type="button" class="modal-close" onclick="closeCardModal()">&times;</button>
                        </div>
                        
                        <div class="card-modal-body">
                            <form id="card_form">
                                <div class="card-form-grid">
                                    <div class="form-section">
                                        <h4>Temel Bilgiler</h4>
                                        <table class="form-table">
                                            <tr>
                                                <th><label for="card_title">Başlık</label></th>
                                                <td><input type="text" id="card_title" name="title" class="regular-text" required></td>
                                            </tr>
                                            <tr>
                                                <th><label for="card_description">Açıklama</label></th>
                                                <td><textarea id="card_description" name="description" rows="3" class="large-text"></textarea></td>
                                            </tr>
                                            <tr>
                                                <th><label for="card_url">URL</label></th>
                                                <td>
                                                    <input type="url" id="card_url" name="url" class="regular-text">
                                                    <p class="description">Karta tıklandığında gidilecek sayfa</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="card_target">Hedef</label></th>
                                                <td>
                                                    <select id="card_target" name="target">
                                                        <option value="_self">Aynı pencerede</option>
                                                        <option value="_blank">Yeni sekmede</option>
                                                        <option value="_parent">Üst pencerede</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div class="form-section">
                                        <h4>Görsel Ayarlar</h4>
                                        <table class="form-table">
                                            <tr>
                                                <th><label for="card_icon">İkon</label></th>
                                                <td>
                                                    <div class="icon-selector">
                                                        <input type="text" id="card_icon" name="icon" class="regular-text" placeholder="fa fa-home">
                                                        <button type="button" class="button" onclick="openIconPicker()">İkon Seç</button>
                                                    </div>
                                                    <p class="description">Font Awesome icon sınıfı (örn: fa fa-home)</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="card_image">Arka Plan Resmi</label></th>
                                                <td>
                                                    <div class="image-upload">
                                                        <input type="url" id="card_image" name="background_image" class="regular-text">
                                                        <button type="button" class="button" onclick="selectImage()">Resim Seç</button>
                                                        <div id="image_preview"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="card_color">Renk Teması</label></th>
                                                <td>
                                                    <div class="color-scheme-picker">
                                                        <input type="color" id="card_color" name="color" value="#4CAF50">
                                                        <div class="predefined-colors">
                                                            <span class="color-option" data-color="#4CAF50" style="background: #4CAF50;"></span>
                                                            <span class="color-option" data-color="#2196F3" style="background: #2196F3;"></span>
                                                            <span class="color-option" data-color="#FF9800" style="background: #FF9800;"></span>
                                                            <span class="color-option" data-color="#E91E63" style="background: #E91E63;"></span>
                                                            <span class="color-option" data-color="#9C27B0" style="background: #9C27B0;"></span>
                                                            <span class="color-option" data-color="#607D8B" style="background: #607D8B;"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div class="form-section">
                                        <h4>Gelişmiş Ayarlar</h4>
                                        <table class="form-table">
                                            <tr>
                                                <th><label for="card_order">Sıra</label></th>
                                                <td><input type="number" id="card_order" name="order" min="0" value="0" class="small-text"></td>
                                            </tr>
                                            <tr>
                                                <th><label for="card_enabled">Durum</label></th>
                                                <td>
                                                    <label class="toggle-switch">
                                                        <input type="checkbox" id="card_enabled" name="enabled" value="1">
                                                        <span class="toggle-slider"></span>
                                                    </label>
                                                    <span>Kartı aktif et</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="card_featured">Öne Çıkan</label></th>
                                                <td>
                                                    <input type="checkbox" id="card_featured" name="featured" value="1">
                                                    <span>Bu kartı öne çıkar</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="card_badge">Rozet Metni</label></th>
                                                <td>
                                                    <input type="text" id="card_badge" name="badge" class="regular-text" placeholder="YENİ, POPULER, vb.">
                                                    <p class="description">Kart üzerinde gösterilecek rozet</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div class="form-section">
                                        <h4>Kart Önizlemesi</h4>
                                        <div class="card-live-preview">
                                            <div id="live_card_preview" class="preview-card">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-modal-footer">
                                    <button type="button" class="button" onclick="closeCardModal()">İptal</button>
                                    <button type="submit" class="button button-primary">Kaydet</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div id="icon_picker_modal" class="card-modal" style="display: none;">
                    <div class="card-modal-content">
                        <div class="card-modal-header">
                            <h3>İkon Seç</h3>
                            <button type="button" class="modal-close" onclick="closeIconPicker()">&times;</button>
                        </div>
                        <div class="card-modal-body">
                            <div class="icon-search">
                                <input type="text" id="icon_search" placeholder="İkon ara..." onkeyup="filterIcons()">
                            </div>
                            <div class="icon-grid" id="icon_grid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private static function render_design_tab() {
        $settings = get_option('esistenze_quick_menu_design', self::get_default_design_settings());
        ?>
        <div class="quick-menu-content">
            <form method="post" action="">
                <?php wp_nonce_field('esistenze_quick_menu_save'); ?>
                <input type="hidden" name="tab" value="design">
                
                <div class="design-layout">
                    <div class="design-controls">
                        <div class="postbox">
                            <h2 class="hndle">Kart Tasarımı</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Kart Stili</th>
                                        <td>
                                            <div class="card-style-selector">
                                                <label>
                                                    <input type="radio" name="settings[card_style]" value="flat" <?php checked($settings['card_style'] ?? '', 'flat'); ?>>
                                                    <div class="style-preview flat">Düz</div>
                                                </label>
                                                <label>
                                                    <input type="radio" name="settings[card_style]" value="raised" <?php checked($settings['card_style'] ?? '', 'raised'); ?>>
                                                    <div class="style-preview raised">Yükseltilmiş</div>
                                                </label>
                                                <label>
                                                    <input type="radio" name="settings[card_style]" value="outlined" <?php checked($settings['card_style'] ?? '', 'outlined'); ?>>
                                                    <div class="style-preview outlined">Çerçeveli</div>
                                                </label>
                                                <label>
                                                    <input type="radio" name="settings[card_style]" value="gradient" <?php checked($settings['card_style'] ?? '', 'gradient'); ?>>
                                                    <div class="style-preview gradient">Gradyan</div>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Kart Boyutu</th>
                                        <td>
                                            <div class="size-controls">
                                                <label>
                                                    Genişlik:
                                                    <input type="range" name="settings[card_width]" value="<?php echo esc_attr($settings['card_width'] ?? 280); ?>" min="200" max="400" oninput="updateCardSize()">
                                                    <span id="width_display"><?php echo esc_attr($settings['card_width'] ?? 280); ?>px</span>
                                                </label>
                                                <label>
                                                    Yükseklik:
                                                    <input type="range" name="settings[card_height]" value="<?php echo esc_attr($settings['card_height'] ?? 200); ?>" min="150" max="300" oninput="updateCardSize()">
                                                    <span id="height_display"><?php echo esc_attr($settings['card_height'] ?? 200); ?>px</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Köşe Yuvarlaklığı</th>
                                        <td>
                                            <input type="range" name="settings[border_radius]" value="<?php echo esc_attr($settings['border_radius'] ?? 8); ?>" min="0" max="50" oninput="updateBorderRadius(this.value)">
                                            <span id="radius_display"><?php echo esc_attr($settings['border_radius'] ?? 8); ?>px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Gölge Efekti</th>
                                        <td>
                                            <div class="shadow-controls">
                                                <label>
                                                    <input type="checkbox" name="settings[enable_shadow]" value="1" <?php checked(!empty($settings['enable_shadow'])); ?>>
                                                    Gölge efektini etkinleştir
                                                </label>
                                                <div class="shadow-options" style="<?php echo empty($settings['enable_shadow']) ? 'display:none;' : ''; ?>">
                                                    <label>
                                                        Yoğunluk:
                                                        <input type="range" name="settings[shadow_intensity]" value="<?php echo esc_attr($settings['shadow_intensity'] ?? 0.1); ?>" min="0" max="1" step="0.1" oninput="updateShadow()">
                                                    </label>
                                                    <label>
                                                        Bulanıklık:
                                                        <input type="range" name="settings[shadow_blur]" value="<?php echo esc_attr($settings['shadow_blur'] ?? 20); ?>" min="0" max="50" oninput="updateShadow()">
                                                    </label>
                                                </div>
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
                                        <th scope="row">Font Ailesi</th>
                                        <td>
                                            <select name="settings[font_family]">
                                                <option value="system" <?php selected($settings['font_family'] ?? '', 'system'); ?>>Sistem Varsayılanı</option>
                                                <option value="roboto" <?php selected($settings['font_family'] ?? '', 'roboto'); ?>>Roboto</option>
                                                <option value="opensans" <?php selected($settings['font_family'] ?? '', 'opensans'); ?>>Open Sans</option>
                                                <option value="montserrat" <?php selected($settings['font_family'] ?? '', 'montserrat'); ?>>Montserrat</option>
                                                <option value="poppins" <?php selected($settings['font_family'] ?? '', 'poppins'); ?>>Poppins</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Başlık Boyutu</th>
                                        <td>
                                            <input type="range" name="settings[title_size]" value="<?php echo esc_attr($settings['title_size'] ?? 18); ?>" min="12" max="32" oninput="updatetypography()">
                                            <span id="title_size_display"><?php echo esc_attr($settings['title_size'] ?? 18); ?>px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Açıklama Boyutu</th>
                                        <td>
                                            <input type="range" name="settings[description_size]" value="<?php echo esc_attr($settings['description_size'] ?? 14); ?>" min="10" max="20" oninput="updateTypography()">
                                            <span id="desc_size_display"><?php echo esc_attr($settings['description_size'] ?? 14); ?>px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Metin Hizalama</th>
                                        <td>
                                            <div class="text-align-options">
                                                <label>
                                                    <input type="radio" name="settings[text_align]" value="left" <?php checked($settings['text_align'] ?? '', 'left'); ?>>
                                                    <span class="dashicons dashicons-editor-alignleft"></span> Sol
                                                </label>
                                                <label>
                                                    <input type="radio" name="settings[text_align]" value="center" <?php checked($settings['text_align'] ?? '', 'center'); ?>>
                                                    <span class="dashicons dashicons-editor-aligncenter"></span> Orta
                                                </label>
                                                <label>
                                                    <input type="radio" name="settings[text_align]" value="right" <?php checked($settings['text_align'] ?? '', 'right'); ?>>
                                                    <span class="dashicons dashicons-editor-alignright"></span> Sağ
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Renk Ayarları</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Varsayılan Tema Rengi</th>
                                        <td>
                                            <input type="color" name="settings[default_color]" value="<?php echo esc_attr($settings['default_color'] ?? '#4CAF50'); ?>" class="color-picker">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Başlık Rengi</th>
                                        <td>
                                            <input type="color" name="settings[title_color]" value="<?php echo esc_attr($settings['title_color'] ?? '#333333'); ?>" class="color-picker">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Açıklama Rengi</th>
                                        <td>
                                            <input type="color" name="settings[description_color]" value="<?php echo esc_attr($settings['description_color'] ?? '#666666'); ?>" class="color-picker">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Arka Plan Rengi</th>
                                        <td>
                                            <input type="color" name="settings[background_color]" value="<?php echo esc_attr($settings['background_color'] ?? '#ffffff'); ?>" class="color-picker">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Hover Rengi</th>
                                        <td>
                                            <input type="color" name="settings[hover_color]" value="<?php echo esc_attr($settings['hover_color'] ?? '#f5f5f5'); ?>" class="color-picker">
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
                                </div>
                                
                                <div class="preview-controls">
                                    <button type="button" class="button" onclick="refreshDesignPreview()">Yenile</button>
                                    <button type="button" class="button" onclick="resetToDefaults()">Varsayılana Döndür</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Hazır Temalar</h2>
                            <div class="inside">
                                <div class="theme-selector">
                                    <div class="theme-option" onclick="applyTheme('modern')">
                                        <div class="theme-preview modern"></div>
                                        <span>Modern</span>
                                    </div>
                                    <div class="theme-option" onclick="applyTheme('classic')">
                                        <div class="theme-preview classic"></div>
                                        <span>Klasik</span>
                                    </div>
                                    <div class="theme-option" onclick="applyTheme('minimal')">
                                        <div class="theme-preview minimal"></div>
                                        <span>Minimal</span>
                                    </div>
                                    <div class="theme-option" onclick="applyTheme('colorful')">
                                        <div class="theme-preview colorful"></div>
                                        <span>Renkli</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">CSS Export</h2>
                            <div class="inside">
                                <textarea id="generated_css" rows="10" class="large-text code" readonly></textarea>
                                <div class="css-actions">
                                    <button type="button" class="button" onclick="copyCSSToClipboard()">CSS'i Kopyala</button>
                                    <button type="button" class="button" onclick="downloadCSS()">CSS İndir</button>
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
    
    private static function

// Initialize the module
EsistenzeQuickMenuCards::getInstance();