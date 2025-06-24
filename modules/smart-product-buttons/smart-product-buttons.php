<?php
/*
 * Enhanced Smart Product Buttons Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) exit;

class EsistenzeSmartButtons {
    
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
        // Admin hooks
        add_action('admin_post_esistenze_smart_button_save', array($this, 'save_button'));
        add_action('admin_post_esistenze_smart_button_delete', array($this, 'delete_button'));
        add_action('admin_post_esistenze_smart_button_duplicate', array($this, 'duplicate_button'));
        add_action('admin_post_esistenze_smart_buttons_bulk', array($this, 'bulk_actions'));
        add_action('admin_post_esistenze_smart_buttons_import', array($this, 'import_settings'));
        add_action('wp_ajax_esistenze_smart_button_preview', array($this, 'ajax_preview'));
        add_action('wp_ajax_esistenze_smart_button_reorder', array($this, 'ajax_reorder'));
        
        // Frontend hooks
        add_action('woocommerce_product_meta_end', array($this, 'render_buttons_on_product_page'), 10, 0);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('wp_footer', array($this, 'render_modal_container'));
    }
    
    public static function admin_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'buttons';
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        
        echo '<div class="wrap esistenze-smart-buttons-wrap">';
        echo '<h1 class="wp-heading-inline">Smart Product Buttons</h1>';
        echo '<a href="' . admin_url('admin.php?page=esistenze-smart-buttons&tab=add-new') . '" class="page-title-action">Yeni Buton Ekle</a>';
        echo '<hr class="wp-header-end">';
        
        // Tabs
        self::render_tabs($current_tab);
        
        // Tab content
        switch ($current_tab) {
            case 'buttons':
                self::render_buttons_list($buttons);
                break;
            case 'add-new':
            case 'edit':
                $button_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                $button_data = ($button_id !== null && isset($buttons[$button_id])) ? $buttons[$button_id] : [];
                self::render_button_form($button_data, $button_id);
                break;
            case 'settings':
                self::render_settings_tab();
                break;
            case 'import-export':
                self::render_import_export_tab();
                break;
        }
        
        echo '</div>';
        
        // Add JavaScript and CSS
        self::enqueue_admin_assets();
    }
    
    private static function render_tabs($current_tab) {
        $tabs = array(
            'buttons' => array('label' => 'Butonlar', 'icon' => 'dashicons-button'),
            'add-new' => array('label' => 'Yeni Ekle', 'icon' => 'dashicons-plus-alt'),
            'settings' => array('label' => 'Ayarlar', 'icon' => 'dashicons-admin-settings'),
            'import-export' => array('label' => 'İçe/Dışa Aktar', 'icon' => 'dashicons-migrate')
        );
        
        echo '<nav class="nav-tab-wrapper wp-clearfix">';
        foreach ($tabs as $tab_key => $tab) {
            $class = ($current_tab === $tab_key) ? 'nav-tab nav-tab-active' : 'nav-tab';
            echo '<a href="' . admin_url('admin.php?page=esistenze-smart-buttons&tab=' . $tab_key) . '" class="' . $class . '">';
            echo '<span class="dashicons ' . $tab['icon'] . '"></span> ' . $tab['label'];
            echo '</a>';
        }
        echo '</nav>';
    }
    
    private static function render_buttons_list($buttons) {
        ?>
        <div class="esistenze-buttons-manager">
            <!-- Search and Filter Bar -->
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select id="bulk-action-selector-top">
                        <option value="">Toplu İşlemler</option>
                        <option value="delete">Sil</option>
                        <option value="duplicate">Çoğalt</option>
                        <option value="enable">Etkinleştir</option>
                        <option value="disable">Devre Dışı Bırak</option>
                    </select>
                    <button class="button" onclick="applyBulkAction()">Uygula</button>
                </div>
                <div class="alignright actions">
                    <input type="search" id="button-search" placeholder="Buton ara..." value="">
                    <select id="button-filter-type">
                        <option value="">Tüm Türler</option>
                        <option value="phone">Telefon</option>
                        <option value="mail">E-posta</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="form_trigger">Form</option>
                    </select>
                </div>
            </div>
            
            <!-- Buttons Table -->
            <div class="esistenze-buttons-grid" id="buttons-container">
                <?php if (empty($buttons)): ?>
                <div class="no-buttons-message">
                    <div class="dashicons dashicons-button"></div>
                    <h3>Henüz hiç buton oluşturmadınız</h3>
                    <p>İlk butonunuzu oluşturmak için "Yeni Buton Ekle" butonuna tıklayın.</p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=add-new'); ?>" class="button button-primary">Yeni Buton Ekle</a>
                </div>
                <?php else: ?>
                    <?php foreach ($buttons as $index => $button): ?>
                    <div class="button-card" data-id="<?php echo $index; ?>" data-type="<?php echo esc_attr($button['type'] ?? ''); ?>">
                        <div class="button-card-header">
                            <input type="checkbox" class="button-checkbox" value="<?php echo $index; ?>">
                            <span class="button-type-badge <?php echo esc_attr($button['type'] ?? ''); ?>">
                                <?php echo self::get_type_icon($button['type'] ?? ''); ?>
                                <?php echo self::get_type_label($button['type'] ?? ''); ?>
                            </span>
                            <div class="button-actions">
                                <button class="preview-btn" data-id="<?php echo $index; ?>" title="Önizleme">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=edit&id=' . $index); ?>" class="edit-btn" title="Düzenle">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <button class="duplicate-btn" data-id="<?php echo $index; ?>" title="Çoğalt">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                                <button class="delete-btn" data-id="<?php echo $index; ?>" title="Sil">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="button-card-body">
                            <h4 class="button-title"><?php echo esc_html($button['title'] ?? 'Başlıksız'); ?></h4>
                            <p class="button-value"><?php echo esc_html($button['value'] ?? ''); ?></p>
                            
                            <div class="button-preview">
                                <?php echo self::render_button_preview($button); ?>
                            </div>
                            
                            <div class="button-stats">
                                <span class="stat-item">
                                    <span class="dashicons dashicons-visibility"></span>
                                    Görüntülenme: <strong><?php echo get_option('esistenze_button_views_' . $index, 0); ?></strong>
                                </span>
                                <span class="stat-item">
                                    <span class="dashicons dashicons-external"></span>
                                    Tıklama: <strong><?php echo get_option('esistenze_button_clicks_' . $index, 0); ?></strong>
                                </span>
                            </div>
                        </div>
                        
                        <div class="button-card-footer">
                            <span class="button-status <?php echo !empty($button['enabled']) ? 'enabled' : 'disabled'; ?>">
                                <?php echo !empty($button['enabled']) ? 'Aktif' : 'Pasif'; ?>
                            </span>
                            <span class="button-created">
                                <?php echo isset($button['created']) ? date('d.m.Y', strtotime($button['created'])) : 'Bilinmiyor'; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Statistics Summary -->
            <?php if (!empty($buttons)): ?>
            <div class="buttons-statistics">
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($buttons); ?></div>
                    <div class="stat-label">Toplam Buton</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo count(array_filter($buttons, function($b) { return !empty($b['enabled']); })); ?></div>
                    <div class="stat-label">Aktif Buton</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo array_sum(array_map(function($i) { return get_option('esistenze_button_views_' . $i, 0); }, array_keys($buttons))); ?></div>
                    <div class="stat-label">Toplam Görüntülenme</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo array_sum(array_map(function($i) { return get_option('esistenze_button_clicks_' . $i, 0); }, array_keys($buttons))); ?></div>
                    <div class="stat-label">Toplam Tıklama</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    private static function render_button_form($button, $button_id = null) {
        $is_edit = ($button_id !== null);
        ?>
        <div class="esistenze-button-form-container">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="esistenze-button-form">
                <input type="hidden" name="action" value="esistenze_smart_button_save">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="button_id" value="<?php echo esc_attr($button_id); ?>">
                <?php endif; ?>
                <?php wp_nonce_field('esistenze_smart_button_save'); ?>
                
                <div class="form-layout">
                    <!-- Sol Panel - Form Alanları -->
                    <div class="form-fields">
                        <div class="postbox">
                            <h2 class="hndle">Temel Bilgiler</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="button_title">Buton Başlığı *</label></th>
                                        <td>
                                            <input type="text" id="button_title" name="title" value="<?php echo esc_attr($button['title'] ?? ''); ?>" class="regular-text" required>
                                            <p class="description">Buton üzerinde görünecek metin</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="button_type">Buton Türü *</label></th>
                                        <td>
                                            <select id="button_type" name="type" class="regular-text" required onchange="updateFormFields(this.value)">
                                                <option value="">Seçiniz</option>
                                                <option value="phone" <?php selected($button['type'] ?? '', 'phone'); ?>>📞 Telefon</option>
                                                <option value="mail" <?php selected($button['type'] ?? '', 'mail'); ?>>📧 E-posta</option>
                                                <option value="whatsapp" <?php selected($button['type'] ?? '', 'whatsapp'); ?>>💬 WhatsApp</option>
                                                <option value="form_trigger" <?php selected($button['type'] ?? '', 'form_trigger'); ?>>📝 Form</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr id="value_field">
                                        <th scope="row"><label for="button_value">Değer *</label></th>
                                        <td>
                                            <input type="text" id="button_value" name="value" value="<?php echo esc_attr($button['value'] ?? ''); ?>" class="regular-text" required>
                                            <p class="description" id="value_description">Türe göre değişir</p>
                                        </td>
                                    </tr>
                                    <tr id="message_field" style="display: none;">
                                        <th scope="row"><label for="button_message">Mesaj</label></th>
                                        <td>
                                            <textarea id="button_message" name="message" class="large-text" rows="3"><?php echo esc_textarea($button['message'] ?? ''); ?></textarea>
                                            <p class="description">WhatsApp için ön tanımlı mesaj</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Görünüm Ayarları</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Renkler</th>
                                        <td>
                                            <div class="color-picker-group">
                                                <label>
                                                    Başlangıç Rengi
                                                    <input type="color" name="button_color_start" value="<?php echo esc_attr($button['button_color_start'] ?? '#4CAF50'); ?>" class="color-picker">
                                                </label>
                                                <label>
                                                    Bitiş Rengi
                                                    <input type="color" name="button_color_end" value="<?php echo esc_attr($button['button_color_end'] ?? '#45a049'); ?>" class="color-picker">
                                                </label>
                                                <label>
                                                    Yazı Rengi
                                                    <input type="color" name="text_color" value="<?php echo esc_attr($button['text_color'] ?? '#ffffff'); ?>" class="color-picker">
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="button_icon">İkon</label></th>
                                        <td>
                                            <div class="icon-picker">
                                                <input type="text" id="button_icon" name="icon" value="<?php echo esc_attr($button['icon'] ?? ''); ?>" class="regular-text" placeholder="fa-phone">
                                                <button type="button" class="button" onclick="openIconPicker()">İkon Seç</button>
                                            </div>
                                            <p class="description">Font Awesome ikon sınıfı (örn: fa-phone)</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="font_size">Font Boyutu</label></th>
                                        <td>
                                            <input type="range" id="font_size" name="font_size" value="<?php echo esc_attr($button['font_size'] ?? '16'); ?>" min="10" max="30" oninput="updateFontSize(this.value)">
                                            <span id="font_size_display"><?php echo esc_attr($button['font_size'] ?? '16'); ?>px</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Gelişmiş Ayarlar</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="tracking_name">Takip Adı</label></th>
                                        <td>
                                            <input type="text" id="tracking_name" name="tracking_name" value="<?php echo esc_attr($button['tracking_name'] ?? ''); ?>" class="regular-text">
                                            <p class="description">Analytics için özel takip adı</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Durum</th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="enabled" value="1" <?php checked(!empty($button['enabled'])); ?>>
                                                Butonu etkinleştir
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sağ Panel - Önizleme -->
                    <div class="form-preview">
                        <div class="postbox">
                            <h2 class="hndle">Canlı Önizleme</h2>
                            <div class="inside">
                                <div id="button_preview" class="button-preview-container">
                                    <?php echo self::render_button_preview($button); ?>
                                </div>
                                
                                <div class="preview-devices">
                                    <button type="button" class="device-btn active" data-device="desktop">
                                        <span class="dashicons dashicons-desktop"></span> Masaüstü
                                    </button>
                                    <button type="button" class="device-btn" data-device="tablet">
                                        <span class="dashicons dashicons-tablet"></span> Tablet
                                    </button>
                                    <button type="button" class="device-btn" data-device="mobile">
                                        <span class="dashicons dashicons-smartphone"></span> Mobil
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <h2 class="hndle">Kısa Kod</h2>
                            <div class="inside">
                                <p>Bu butonu sayfalarınızda göstermek için:</p>
                                <code id="shortcode_display">[esistenze_button id="<?php echo $button_id ?? 'NEW'; ?>"]</code>
                                <button type="button" class="button" onclick="copyShortcode()">Kopyala</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php echo $is_edit ? 'Güncelle' : 'Kaydet'; ?>">
                        <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons'); ?>" class="button">İptal</a>
                        <?php if ($is_edit): ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=esistenze_smart_button_duplicate&id=' . $button_id), 'esistenze_smart_button_duplicate'); ?>" class="button">Çoğalt</a>
                        <?php endif; ?>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
    
    private static function render_settings_tab() {
        $settings = get_option('esistenze_smart_buttons_settings', array());
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('esistenze_smart_buttons_settings'); ?>
            
            <div class="postbox">
                <h2 class="hndle">Genel Ayarlar</h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Butonları Göster</th>
                            <td>
                                <fieldset>
                                    <label><input type="checkbox" name="esistenze_smart_buttons_settings[show_on_products]" value="1" <?php checked(!empty($settings['show_on_products'])); ?>> Ürün sayfalarında</label><br>
                                    <label><input type="checkbox" name="esistenze_smart_buttons_settings[show_on_shop]" value="1" <?php checked(!empty($settings['show_on_shop'])); ?>> Mağaza sayfasında</label><br>
                                    <label><input type="checkbox" name="esistenze_smart_buttons_settings[show_on_category]" value="1" <?php checked(!empty($settings['show_on_category'])); ?>> Kategori sayfalarında</label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Buton Sıralaması</th>
                            <td>
                                <select name="esistenze_smart_buttons_settings[button_order]">
                                    <option value="manual" <?php selected($settings['button_order'] ?? '', 'manual'); ?>>Manuel Sıralama</option>
                                    <option value="alphabetical" <?php selected($settings['button_order'] ?? '', 'alphabetical'); ?>>Alfabetik</option>
                                    <option value="type" <?php selected($settings['button_order'] ?? '', 'type'); ?>>Türe Göre</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Animasyon</th>
                            <td>
                                <label><input type="checkbox" name="esistenze_smart_buttons_settings[enable_animations]" value="1" <?php checked(!empty($settings['enable_animations'])); ?>> Hover animasyonlarını etkinleştir</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">İstatistik Takibi</th>
                            <td>
                                <label><input type="checkbox" name="esistenze_smart_buttons_settings[enable_tracking]" value="1" <?php checked(!empty($settings['enable_tracking'])); ?>> Tıklama ve görüntülenme istatistiklerini takip et</label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    private static function render_import_export_tab() {
        ?>
        <div class="import-export-container">
            <div class="postbox">
                <h2 class="hndle">Dışa Aktar</h2>
                <div class="inside">
                    <p>Tüm buton ayarlarınızı JSON formatında dışa aktarın.</p>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="esistenze_smart_buttons_export">
                        <?php wp_nonce_field('esistenze_smart_buttons_export'); ?>
                        <p class="submit">
                            <input type="submit" class="button-secondary" value="Ayarları Dışa Aktar">
                        </p>
                    </form>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle">İçe Aktar</h2>
                <div class="inside">
                    <p>Daha önce dışa aktardığınız JSON dosyasını yükleyin.</p>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="esistenze_smart_buttons_import">
                        <?php wp_nonce_field('esistenze_smart_buttons_import'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="import_file">JSON Dosyası</label></th>
                                <td>
                                    <input type="file" id="import_file" name="import_file" accept=".json" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">İçe Aktarma Modu</th>
                                <td>
                                    <label><input type="radio" name="import_mode" value="replace" checked> Mevcut ayarları değiştir</label><br>
                                    <label><input type="radio" name="import_mode" value="merge"> Mevcut ayarlarla birleştir</label>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button-primary" value="Ayarları İçe Aktar">
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    
    private static function render_button_preview($button) {
        if (empty($button)) {
            return '<div class="preview-placeholder">Önizleme için form alanlarını doldurun</div>';
        }
        
        $title = $button['title'] ?? 'Örnek Buton';
        $color1 = $button['button_color_start'] ?? '#4CAF50';
        $color2 = $button['button_color_end'] ?? '#45a049';
        $text_color = $button['text_color'] ?? '#ffffff';
        $font_size = $button['font_size'] ?? 16;
        $icon = $button['icon'] ?? '';
        
        $style = sprintf(
            'background: linear-gradient(45deg, %s, %s); color: %s; font-size: %spx; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; border: none; cursor: pointer;',
            esc_attr($color1),
            esc_attr($color2),
            esc_attr($text_color),
            esc_attr($font_size)
        );
        
        $icon_html = $icon ? '<i class="fa ' . esc_attr($icon) . '" style="margin-right: 8px;"></i>' : '';
        
        return '<button style="' . $style . '">' . $icon_html . esc_html($title) . '</button>';
    }
    
    private static function get_type_icon($type) {
        $icons = array(
            'phone' => '📞',
            'mail' => '📧',
            'whatsapp' => '💬',
            'form_trigger' => '📝'
        );
        return $icons[$type] ?? '🔘';
    }
    
    private static function get_type_label($type) {
        $labels = array(
            'phone' => 'Telefon',
            'mail' => 'E-posta',
            'whatsapp' => 'WhatsApp',
            'form_trigger' => 'Form'
        );
        return $labels[$type] ?? 'Bilinmiyor';
    }
    
    private static function enqueue_admin_assets() {
        ?>
        <style>
        .esistenze-smart-buttons-wrap { max-width: 1200px; }
        .nav-tab { padding: 8px 16px; }
        .nav-tab .dashicons { margin-right: 5px; vertical-align: middle; }
        
        /* Buttons Grid */
        .esistenze-buttons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .button-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .button-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .button-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #eee;
            background: #f9f9f9;
        }
        
        .button-type-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .button-type-badge.phone { background: #e3f2fd; color: #1976d2; }
        .button-type-badge.mail { background: #f3e5f5; color: #7b1fa2; }
        .button-type-badge.whatsapp { background: #e8f5e8; color: #388e3c; }
        .button-type-badge.form_trigger { background: #fff3e0; color: #f57c00; }
        
        .button-actions {
            display: flex;
            gap: 5px;
        }
        
        .button-actions button {
            padding: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 3px;
            transition: background 0.2s ease;
        }
        
        .button-actions button:hover { background: rgba(0,0,0,0.1); }
        .preview-btn:hover { color: #2196f3; }
        .edit-btn:hover { color: #4caf50; }
        .duplicate-btn:hover { color: #ff9800; }
        .delete-btn:hover { color: #f44336; }
        
        .button-card-body {
            padding: 15px;
        }
        
        .button-title {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .button-value {
            margin: 0 0 15px;
            color: #666;
            font-size: 14px;
        }
        
        .button-preview {
            margin: 15px 0;
            text-align: center;
        }
        
        .button-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .stat-item {
            font-size: 12px;
            color: #666;
        }
        
        .stat-item .dashicons {
            font-size: 14px;
            margin-right: 3px;
        }
        
        .button-card-footer {
            padding: 10px 15px;
            background: #f9f9f9;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }
        
        .button-status.enabled {
            color: #4caf50;
            font-weight: 600;
        }
        
        .button-status.disabled {
            color: #f44336;
            font-weight: 600;
        }
        
        /* Form Layout */
        .form-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin: 20px 0;
        }
        
        .form-fields .postbox {
            margin-bottom: 20px;
        }
        
        .form-preview .postbox {
            position: sticky;
            top: 32px;
            margin-bottom: 20px;
        }
        
        .color-picker-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .color-picker-group label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .color-picker {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .icon-picker {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .button-preview-container {
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            text-align: center;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .preview-devices {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }
        
        .device-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .device-btn.active {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
        }
        
        /* Statistics */
        .buttons-statistics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .stat-box {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #4caf50;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        /* No buttons message */
        .no-buttons-message {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-buttons-message .dashicons {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .no-buttons-message h3 {
            margin: 20px 0 10px;
            color: #333;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .form-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .esistenze-buttons-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .buttons-statistics {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 600px) {
            .esistenze-buttons-grid {
                grid-template-columns: 1fr;
            }
            
            .buttons-statistics {
                grid-template-columns: 1fr;
            }
            
            .color-picker-group {
                justify-content: center;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Form field updates based on button type
            window.updateFormFields = function(type) {
                const valueField = $('#button_value');
                const valueDesc = $('#value_description');
                const messageField = $('#message_field');
                
                switch(type) {
                    case 'phone':
                        valueField.attr('placeholder', '+90 555 123 4567');
                        valueDesc.text('Telefon numarası (uluslararası format)');
                        messageField.hide();
                        break;
                    case 'mail':
                        valueField.attr('placeholder', 'info@example.com');
                        valueDesc.text('E-posta adresi');
                        messageField.hide();
                        break;
                    case 'whatsapp':
                        valueField.attr('placeholder', '+90 555 123 4567');
                        valueDesc.text('WhatsApp numarası (uluslararası format)');
                        messageField.show();
                        break;
                    case 'form_trigger':
                        valueField.attr('placeholder', '123');
                        valueDesc.text('Contact Form 7 ID numarası');
                        messageField.hide();
                        break;
                }
                updatePreview();
            };
            
            // Live preview update
            window.updatePreview = function() {
                const title = $('#button_title').val() || 'Örnek Buton';
                const color1 = $('input[name="button_color_start"]').val();
                const color2 = $('input[name="button_color_end"]').val();
                const textColor = $('input[name="text_color"]').val();
                const fontSize = $('#font_size').val();
                const icon = $('#button_icon').val();
                
                const iconHtml = icon ? `<i class="fa ${icon}" style="margin-right: 8px;"></i>` : '';
                const style = `background: linear-gradient(45deg, ${color1}, ${color2}); color: ${textColor}; font-size: ${fontSize}px; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; border: none; cursor: pointer;`;
                
                $('#button_preview').html(`<button style="${style}">${iconHtml}${title}</button>`);
            };
            
            // Font size slider update
            window.updateFontSize = function(value) {
                $('#font_size_display').text(value + 'px');
                updatePreview();
            };
            
            // Copy shortcode
            window.copyShortcode = function() {
                const shortcode = $('#shortcode_display').text();
                navigator.clipboard.writeText(shortcode).then(function() {
                    alert('Kısa kod kopyalandı!');
                });
            };
            
            // Icon picker (basic implementation)
            window.openIconPicker = function() {
                const icons = ['fa-phone', 'fa-envelope', 'fa-whatsapp', 'fa-comment', 'fa-shopping-cart', 'fa-heart', 'fa-star', 'fa-home', 'fa-user', 'fa-cog'];
                const iconHtml = icons.map(icon => `<button type="button" onclick="selectIcon('${icon}')" style="margin: 5px; padding: 10px; border: 1px solid #ddd; background: white; cursor: pointer;"><i class="fa ${icon}"></i></button>`).join('');
                
                const modal = $(`
                    <div id="icon-picker-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                        <div style="background: white; padding: 20px; border-radius: 8px; max-width: 400px;">
                            <h3>İkon Seç</h3>
                            <div style="max-height: 300px; overflow-y: auto;">
                                ${iconHtml}
                            </div>
                            <button type="button" onclick="closeIconPicker()" style="margin-top: 15px; padding: 8px 16px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">Kapat</button>
                        </div>
                    </div>
                `);
                
                $('body').append(modal);
            };
            
            window.selectIcon = function(icon) {
                $('#button_icon').val(icon);
                updatePreview();
                closeIconPicker();
            };
            
            window.closeIconPicker = function() {
                $('#icon-picker-modal').remove();
            };
            
            // Device preview toggle
            $('.device-btn').on('click', function() {
                $('.device-btn').removeClass('active');
                $(this).addClass('active');
                
                const device = $(this).data('device');
                const preview = $('#button_preview');
                
                switch(device) {
                    case 'desktop':
                        preview.css('max-width', '100%');
                        break;
                    case 'tablet':
                        preview.css('max-width', '768px');
                        break;
                    case 'mobile':
                        preview.css('max-width', '375px');
                        break;
                }
            });
            
            // Search and filter
            $('#button-search').on('input', function() {
                const search = $(this).val().toLowerCase();
                $('.button-card').each(function() {
                    const title = $(this).find('.button-title').text().toLowerCase();
                    const value = $(this).find('.button-value').text().toLowerCase();
                    
                    if (title.includes(search) || value.includes(search)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            
            $('#button-filter-type').on('change', function() {
                const filterType = $(this).val();
                $('.button-card').each(function() {
                    if (!filterType || $(this).data('type') === filterType) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            
            // Bulk actions
            window.applyBulkAction = function() {
                const action = $('#bulk-action-selector-top').val();
                const selected = $('.button-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
                
                if (!action || selected.length === 0) {
                    alert('Lütfen bir işlem seçin ve en az bir buton işaretleyin.');
                    return;
                }
                
                if (action === 'delete' && !confirm('Seçili butonları silmek istediğinizden emin misiniz?')) {
                    return;
                }
                
                // AJAX call for bulk actions
                $.post(ajaxurl, {
                    action: 'esistenze_smart_buttons_bulk',
                    bulk_action: action,
                    button_ids: selected,
                    _wpnonce: '<?php echo wp_create_nonce("esistenze_smart_buttons_bulk"); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + response.data);
                    }
                });
            };
            
            // Live form updates
            $('input, select, textarea').on('input change', function() {
                updatePreview();
            });
            
            // Initialize
            if ($('#button_type').val()) {
                updateFormFields($('#button_type').val());
            }
            updatePreview();
        });
        </script>
        <?php
    }
    
    // AJAX handlers and other methods continue...
    public function ajax_preview() {
        check_ajax_referer('esistenze_smart_button_preview');
        
        $button_data = array(
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'type' => sanitize_text_field($_POST['type'] ?? ''),
            'button_color_start' => sanitize_hex_color($_POST['button_color_start'] ?? '#4CAF50'),
            'button_color_end' => sanitize_hex_color($_POST['button_color_end'] ?? '#45a049'),
            'text_color' => sanitize_hex_color($_POST['text_color'] ?? '#ffffff'),
            'font_size' => intval($_POST['font_size'] ?? 16),
            'icon' => sanitize_text_field($_POST['icon'] ?? '')
        );
        
        wp_send_json_success(self::render_button_preview($button_data));
    }
    
    public function bulk_actions() {
        if (!current_user_can('manage_options') || !check_admin_referer('esistenze_smart_buttons_bulk')) {
            wp_die('Yetkiniz yok.');
        }
        
        $action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $button_ids = array_map('intval', $_POST['button_ids'] ?? []);
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        
        switch ($action) {
            case 'delete':
                foreach ($button_ids as $id) {
                    unset($buttons[$id]);
                }
                $buttons = array_values($buttons);
                break;
                
            case 'enable':
                foreach ($button_ids as $id) {
                    if (isset($buttons[$id])) {
                        $buttons[$id]['enabled'] = true;
                    }
                }
                break;
                
            case 'disable':
                foreach ($button_ids as $id) {
                    if (isset($buttons[$id])) {
                        $buttons[$id]['enabled'] = false;
                    }
                }
                break;
                
            case 'duplicate':
                foreach ($button_ids as $id) {
                    if (isset($buttons[$id])) {
                        $duplicate = $buttons[$id];
                        $duplicate['title'] = $duplicate['title'] . ' (Kopya)';
                        $buttons[] = $duplicate;
                    }
                }
                break;
        }
        
        update_option('esistenze_smart_custom_buttons', $buttons);
        wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons'));
        exit;
    }
    
    // Save button method (enhanced)
    public function save_button() {
        if (!current_user_can('manage_options') || !check_admin_referer('esistenze_smart_button_save')) {
            wp_die('Yetkiniz yok.');
        }

        $buttons = get_option('esistenze_smart_custom_buttons', []);
        $button_data = array(
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'type' => sanitize_text_field($_POST['type'] ?? ''),
            'value' => sanitize_text_field($_POST['value'] ?? ''),
            'message' => sanitize_textarea_field($_POST['message'] ?? ''),
            'button_color_start' => sanitize_hex_color($_POST['button_color_start'] ?? '#4CAF50'),
            'button_color_end' => sanitize_hex_color($_POST['button_color_end'] ?? '#45a049'),
            'text_color' => sanitize_hex_color($_POST['text_color'] ?? '#ffffff'),
            'icon' => sanitize_text_field($_POST['icon'] ?? ''),
            'font_size' => intval($_POST['font_size'] ?? 16),
            'tracking_name' => sanitize_text_field($_POST['tracking_name'] ?? ''),
            'enabled' => !empty($_POST['enabled']),
            'created' => isset($_POST['button_id']) ? ($buttons[$_POST['button_id']]['created'] ?? current_time('mysql')) : current_time('mysql'),
            'modified' => current_time('mysql')
        );

        if (isset($_POST['button_id'])) {
            $buttons[$_POST['button_id']] = $button_data;
            $message = 'updated';
        } else {
            $buttons[] = $button_data;
            $message = 'created';
        }

        update_option('esistenze_smart_custom_buttons', $buttons);
        wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons&message=' . $message));
        exit;
    }

    public function delete_button() {
        if (!current_user_can('manage_options') || !isset($_GET['id']) || !check_admin_referer('esistenze_smart_button_delete_' . $_GET['id'])) {
            wp_die('Yetkiniz yok.');
        }
        
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        $button_id = intval($_GET['id']);
        
        if (isset($buttons[$button_id])) {
            unset($buttons[$button_id]);
            $buttons = array_values($buttons);
            update_option('esistenze_smart_custom_buttons', $buttons);
        }
        
        wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons&message=deleted'));
        exit;
    }
    
    public function duplicate_button() {
        if (!current_user_can('manage_options') || !isset($_GET['id']) || !check_admin_referer('esistenze_smart_button_duplicate')) {
            wp_die('Yetkiniz yok.');
        }
        
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        $button_id = intval($_GET['id']);
        
        if (isset($buttons[$button_id])) {
            $duplicate = $buttons[$button_id];
            $duplicate['title'] = $duplicate['title'] . ' (Kopya)';
            $duplicate['created'] = current_time('mysql');
            $duplicate['modified'] = current_time('mysql');
            $buttons[] = $duplicate;
            update_option('esistenze_smart_custom_buttons', $buttons);
        }
        
        wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons&message=duplicated'));
        exit;
    }
    
    // Existing methods continue (render_buttons_on_product_page, enqueue_public_assets, etc.)
    public function enqueue_public_assets() {
        wp_enqueue_style('esistenze-smart-buttons-css', ESISTENZE_WP_KIT_URL . 'modules/smart-product-buttons/assets/style.css', array(), ESISTENZE_WP_KIT_VERSION);
        wp_enqueue_script('esistenze-smart-buttons-js', ESISTENZE_WP_KIT_URL . 'modules/smart-product-buttons/assets/script.js', array('jquery'), ESISTENZE_WP_KIT_VERSION, true);
        
        // Add tracking if enabled
        $settings = get_option('esistenze_smart_buttons_settings', array());
        if (!empty($settings['enable_tracking'])) {
            wp_localize_script('esistenze-smart-buttons-js', 'esistenzeButtons', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('esistenze_button_tracking')
            ));
        }
    }

    public function render_buttons_on_product_page() {
        static $rendered = false;
        if ($rendered) return;

        $settings = get_option('esistenze_smart_buttons_settings', array());
        if (empty($settings['show_on_products'])) return;

        $buttons = get_option('esistenze_smart_custom_buttons', []);
        if (empty($buttons)) return;

        // Filter enabled buttons
        $enabled_buttons = array_filter($buttons, function($button) {
            return !empty($button['enabled']);
        });

        if (empty($enabled_buttons)) return;

        ob_start();
        echo '<div class="esistenze-smart-buttons-frontend">';
        
        foreach ($enabled_buttons as $index => $button) {
            echo $this->render_single_button($button, $index);
        }
        
        echo '</div>';

        $output = ob_get_clean();
        if (!empty($output)) {
            echo $output;
            $rendered = true;
        }
    }
    
    private function render_single_button($button, $index) {
        $type = $button['type'] ?? '';
        $title = $button['title'] ?? '';
        $value = $button['value'] ?? '';
        $message = $button['message'] ?? '';
        $color1 = $button['button_color_start'] ?? '#4CAF50';
        $color2 = $button['button_color_end'] ?? '#45a049';
        $text_color = $button['text_color'] ?? '#fff';
        $font_size = intval($button['font_size'] ?? 16);
        $icon = $button['icon'] ?? '';
        $track = $button['tracking_name'] ?? ($type . '_button_' . $index);
        
        $data = 'data-track="' . esc_attr($track) . '" data-id="' . esc_attr($value) . '" data-button-id="' . esc_attr($index) . '"';
        $icon_html = $icon ? '<i class="fa ' . esc_attr($icon) . '"></i>' : '';
        $style = 'style="background: linear-gradient(45deg, ' . esc_attr($color1) . ', ' . esc_attr($color2) . ') !important; color: ' . esc_attr($text_color) . ' !important; font-size: ' . min($font_size, 20) . 'px !important; padding: 12px 24px !important; border-radius: 8px !important; box-shadow: 0 4px 12px rgba(' . $this->hexToRgb($color1) . ', 0.3) !important;"';

        switch ($type) {
            case 'phone':
                return '<a href="tel:' . esc_attr($value) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . esc_html($title) . '</a>';
                
            case 'mail':
                return '<a href="mailto:' . esc_attr($value) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . esc_html($title) . '</a>';
                
            case 'whatsapp':
                $url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $value);
                if ($message) $url .= '?text=' . urlencode($message);
                return '<a target="_blank" href="' . esc_url($url) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . esc_html($title) . '</a>';
                
            case 'form_trigger':
                return '<button type="button" class="esistenze-smart-btn esistenze-form-popup-trigger" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . esc_html($title) . '</button>';
        }
        
        return '';
    }

    public function render_modal_container() {
        echo '<div id="esistenze-form-modal" class="esistenze-smart-modal"><div class="esistenze-smart-modal-content"><span class="esistenze-smart-close">×</span><div class="esistenze-smart-form-container"></div></div></div>';
    }
    
    private function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "$r, $g, $b";
    }
}

// Initialize the module
EsistenzeSmartButtons::getInstance();