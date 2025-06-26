<?php
/**
 * Smart Product Buttons - Admin Menu Handler
 * Bu kısımları smart-product-buttons.php dosyasına ekleyin
 */

// EsistenzeSmartButtons sınıfına bu metotları ekleyin:

/**
 * Add admin menu
 */
public function add_admin_menu() {
    add_submenu_page(
        'esistenze-wp-kit',
        'Smart Product Buttons',
        'Smart Buttons',
        esistenze_qmc_capability(),
        'esistenze-smart-buttons',
        array($this, 'admin_page')
    );
}

/**
 * Admin page handler
 */
public function admin_page() {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'buttons';
    $current_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    
    // Handle messages
    $message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
    
    ?>
    <div class="wrap esistenze-smart-buttons-wrap">
        <h1 class="wp-heading-inline">Smart Product Buttons</h1>
        
        <?php if ($current_tab === 'buttons'): ?>
            <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=add'); ?>" class="page-title-action">Yeni Buton Ekle</a>
        <?php endif; ?>
        
        <hr class="wp-header-end">
        
        <?php $this->show_admin_messages($message); ?>
        
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper wp-clearfix">
            <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=buttons'); ?>" 
               class="nav-tab <?php echo $current_tab === 'buttons' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-admin-links"></span> Butonlar
            </a>
            <a href="<?php echo admin_url('admin.php?page=esistenza-smart-buttons&tab=add'); ?>" 
               class="nav-tab <?php echo $current_tab === 'add' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-plus-alt"></span> Yeni Buton
            </a>
            <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=settings'); ?>" 
               class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-admin-settings"></span> Ayarlar
            </a>
            <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=analytics'); ?>" 
               class="nav-tab <?php echo $current_tab === 'analytics' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-chart-bar"></span> İstatistikler
            </a>
            <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=import-export'); ?>" 
               class="nav-tab <?php echo $current_tab === 'import-export' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-upload"></span> İçe/Dışa Aktar
            </a>
        </nav>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <?php
            switch ($current_tab) {
                case 'buttons':
                default:
                    $this->render_buttons_list();
                    break;
                case 'add':
                case 'edit':
                    $this->render_button_form();
                    break;
                case 'settings':
                    $this->render_settings_page();
                    break;
                case 'analytics':
                    $this->render_analytics_page();
                    break;
                case 'import-export':
                    $this->render_import_export_page();
                    break;
            }
            ?>
        </div>
    </div>
    <?php
    
    // Enqueue admin assets
    $this->enqueue_admin_assets();
}

/**
 * Render buttons list
 */
private function render_buttons_list() {
    $buttons = get_option('esistenze_smart_custom_buttons', []);
    
    ?>
    <div class="buttons-list-container">
        <?php if (empty($buttons)): ?>
            <div class="no-buttons-message">
                <div class="dashicons dashicons-admin-links"></div>
                <h3>Henüz buton oluşturulmamış</h3>
                <p>İlk butonunuzu oluşturmak için "Yeni Buton Ekle" düğmesine tıklayın.</p>
                <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=add'); ?>" class="button button-primary button-large">İlk Butonu Oluştur</a>
            </div>
        <?php else: ?>
            
            <!-- Search and Filter -->
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select id="button-filter-type">
                        <option value="">Tüm Türler</option>
                        <option value="phone">Telefon</option>
                        <option value="mail">E-posta</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="form_trigger">Form</option>
                    </select>
                    <input type="search" id="button-search" placeholder="Buton ara..." value="">
                    <input type="submit" class="button" value="Filtrele">
                </div>
                
                <div class="alignleft actions">
                    <select id="bulk-action-selector-top">
                        <option value="">Toplu İşlemler</option>
                        <option value="delete">Sil</option>
                        <option value="duplicate">Kopyala</option>
                        <option value="enable">Etkinleştir</option>
                        <option value="disable">Devre Dışı Bırak</option>
                    </select>
                    <input type="button" class="button action" value="Uygula" onclick="applyBulkAction()">
                </div>
            </div>
            
            <!-- Buttons Statistics -->
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
                    <div class="stat-number"><?php echo $this->get_total_clicks(); ?></div>
                    <div class="stat-label">Toplam Tıklama</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $this->get_total_views(); ?></div>
                    <div class="stat-label">Toplam Görüntülenme</div>
                </div>
            </div>
            
            <!-- Buttons Grid -->
            <div class="esistenze-buttons-grid" id="buttons-container">
                <?php foreach ($buttons as $index => $button): ?>
                    <?php $this->render_button_card($button, $index); ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Bulk Actions Form -->
            <form id="bulk-action-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: none;">
                <input type="hidden" name="action" value="esistenze_smart_buttons_bulk">
                <input type="hidden" name="bulk_action" id="bulk-action-value">
                <input type="hidden" name="button_ids" id="bulk-selected-ids">
                <?php wp_nonce_field('esistenze_smart_buttons_bulk'); ?>
            </form>
            
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render single button card
 */
private function render_button_card($button, $index) {
    $type_label = self::get_type_label($button['type'] ?? '');
    $type_icon = self::get_type_icon($button['type'] ?? '');
    $clicks = get_option('esistenze_button_clicks_' . $index, 0);
    $views = get_option('esistenze_button_views_' . $index, 0);
    $enabled = !empty($button['enabled']);
    
    ?>
    <div class="button-card" data-type="<?php echo esc_attr($button['type'] ?? ''); ?>" data-id="<?php echo $index; ?>">
        <div class="button-card-header">
            <div class="button-type-badge <?php echo esc_attr($button['type'] ?? ''); ?>">
                <?php echo $type_icon . ' ' . esc_html($type_label); ?>
            </div>
            <div class="button-actions">
                <input type="checkbox" class="button-checkbox" value="<?php echo $index; ?>">
                <button type="button" class="preview-btn" data-id="<?php echo $index; ?>" title="Önizle">
                    <span class="dashicons dashicons-visibility"></span>
                </button>
                <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=edit&id=' . $index); ?>" class="edit-btn" title="Düzenle">
                    <span class="dashicons dashicons-edit"></span>
                </a>
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=esistenze_smart_button_duplicate&id=' . $index), 'esistenze_smart_button_duplicate'); ?>" class="duplicate-btn" title="Kopyala">
                    <span class="dashicons dashicons-admin-page"></span>
                </a>
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=esistenze_smart_button_delete&id=' . $index), 'esistenze_smart_button_delete_' . $index); ?>" class="delete-btn" title="Sil" onclick="return confirm('Bu butonu silmek istediğinizden emin misiniz?')">
                    <span class="dashicons dashicons-trash"></span>
                </a>
            </div>
        </div>
        
        <div class="button-card-body">
            <h3 class="button-title"><?php echo esc_html($button['title'] ?? 'Başlıksız'); ?></h3>
            <div class="button-value"><?php echo esc_html($button['value'] ?? ''); ?></div>
            
            <div class="button-preview">
                <?php echo self::render_button_preview($button); ?>
            </div>
            
            <div class="button-stats">
                <div class="stat-item">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php echo number_format($views); ?> görüntülenme
                </div>
                <div class="stat-item">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php echo number_format($clicks); ?> tıklama
                </div>
            </div>
        </div>
        
        <div class="button-card-footer">
            <div class="button-status <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                <?php echo $enabled ? 'Aktif' : 'Pasif'; ?>
            </div>
            <div class="button-shortcode">
                <code>[esistenze_button id="<?php echo $index; ?>"]</code>
                <button type="button" onclick="esistenzeCopyToClipboard('[esistenze_button id=&quot;<?php echo $index; ?>&quot;]')" title="Kopyala">
                    <span class="dashicons dashicons-clipboard"></span>
                </button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render button form (add/edit)
 */
private function render_button_form() {
    $button_id = isset($_GET['id']) ? intval($_GET['id']) : -1;
    $is_edit = $button_id >= 0;
    
    $button = array();
    if ($is_edit) {
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        $button = isset($buttons[$button_id]) ? $buttons[$button_id] : array();
        
        if (empty($button)) {
            echo '<div class="notice notice-error"><p>Buton bulunamadı!</p></div>';
            return;
        }
    }
    
    // Default values
    $button = wp_parse_args($button, array(
        'title' => '',
        'type' => 'phone',
        'value' => '',
        'message' => '',
        'button_color_start' => '#4CAF50',
        'button_color_end' => '#45a049',
        'text_color' => '#ffffff',
        'icon' => 'fa-phone',
        'font_size' => 16,
        'tracking_name' => '',
        'enabled' => true
    ));
    
    ?>
    <div class="form-layout">
        <!-- Form Fields -->
        <div class="form-fields">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="esistenze_smart_button_save">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="button_id" value="<?php echo $button_id; ?>">
                <?php endif; ?>
                <?php wp_nonce_field('esistenze_smart_button_save'); ?>
                
                <!-- Basic Information -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle">Temel Bilgiler</h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="button_title">Buton Başlığı *</label>
                                </th>
                                <td>
                                    <input type="text" id="button_title" name="title" value="<?php echo esc_attr($button['title']); ?>" class="regular-text" required onchange="updatePreview()">
                                    <p class="description">Buton üzerinde görünecek metin</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="button_type">Buton Türü *</label>
                                </th>
                                <td>
                                    <select id="button_type" name="type" required onchange="updateFormFields(this.value)">
                                        <option value="phone" <?php selected($button['type'], 'phone'); ?>>📞 Telefon</option>
                                        <option value="mail" <?php selected($button['type'], 'mail'); ?>>📧 E-posta</option>
                                        <option value="whatsapp" <?php selected($button['type'], 'whatsapp'); ?>>💬 WhatsApp</option>
                                        <option value="form_trigger" <?php selected($button['type'], 'form_trigger'); ?>>📝 Form Popup</option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="button_value">Değer *</label>
                                </th>
                                <td>
                                    <input type="text" id="button_value" name="value" value="<?php echo esc_attr($button['value']); ?>" class="regular-text" required onchange="updatePreview()">
                                    <p class="description" id="value_description">Türe göre değişir</p>
                                </td>
                            </tr>
                            
                            <tr id="message_field" style="<?php echo $button['type'] === 'whatsapp' ? '' : 'display:none;'; ?>">
                                <th scope="row">
                                    <label for="button_message">WhatsApp Mesajı</label>
                                </th>
                                <td>
                                    <textarea id="button_message" name="message" rows="3" class="regular-text"><?php echo esc_textarea($button['message']); ?></textarea>
                                    <p class="description">WhatsApp'ta otomatik olarak yazılacak mesaj</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="button_enabled">Durum</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="button_enabled" name="enabled" value="1" <?php checked($button['enabled']); ?>>
                                        Butonu etkinleştir
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Appearance -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle">Görünüm Ayarları</h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row">Buton Renkleri</th>
                                <td>
                                    <div class="color-picker-group">
                                        <label>
                                            Başlangıç Rengi
                                            <input type="color" name="button_color_start" value="<?php echo esc_attr($button['button_color_start']); ?>" class="color-picker" onchange="updatePreview()">
                                        </label>
                                        <label>
                                            Bitiş Rengi
                                            <input type="color" name="button_color_end" value="<?php echo esc_attr($button['button_color_end']); ?>" class="color-picker" onchange="updatePreview()">
                                        </label>
                                        <label>
                                            Metin Rengi
                                            <input type="color" name="text_color" value="<?php echo esc_attr($button['text_color']); ?>" class="color-picker" onchange="updatePreview()">
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="font_size">Yazı Boyutu</label>
                                </th>
                                <td>
                                    <input type="range" id="font_size" name="font_size" min="12" max="24" value="<?php echo esc_attr($button['font_size']); ?>" oninput="updateFontSize(this.value)" onchange="updatePreview()">
                                    <span id="font_size_display"><?php echo esc_html($button['font_size']); ?>px</span>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="button_icon">İkon</label>
                                </th>
                                <td>
                                    <div class="icon-picker">
                                        <input type="text" id="button_icon" name="icon" value="<?php echo esc_attr($button['icon']); ?>" class="regular-text" onchange="updatePreview()">
                                        <button type="button" class="button" onclick="openIconPicker()">İkon Seç</button>
                                    </div>
                                    <p class="description">Font Awesome sınıf adı (örn: fa-phone)</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Analytics -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle">İstatistik Ayarları</h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="tracking_name">İzleme Adı</label>
                                </th>
                                <td>
                                    <input type="text" id="tracking_name" name="tracking_name" value="<?php echo esc_attr($button['tracking_name']); ?>" class="regular-text">
                                    <p class="description">Google Analytics için özel izleme adı (opsiyonel)</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php submit_button($is_edit ? 'Butonu Güncelle' : 'Buton Oluştur', 'primary', 'submit', false); ?>
                <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons'); ?>" class="button">İptal</a>
                
            </form>
        </div>
        
        <!-- Preview -->
        <div class="form-preview">
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Canlı Önizleme</h2>
                </div>
                <div class="inside">
                    <div class="button-preview-container">
                        <div id="button_preview">
                            <?php echo self::render_button_preview($button); ?>
                        </div>
                    </div>
                    
                    <div class="preview-devices">
                        <button type="button" class="device-btn active" data-device="desktop">Masaüstü</button>
                        <button type="button" class="device-btn" data-device="tablet">Tablet</button>
                        <button type="button" class="device-btn" data-device="mobile">Mobil</button>
                    </div>
                </div>
            </div>
            
            <?php if ($is_edit): ?>
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Shortcode</h2>
                </div>
                <div class="inside">
                    <p><strong>Bu butonu sayfalarınızda kullanmak için:</strong></p>
                    <code id="shortcode_display">[esistenze_button id="<?php echo $button_id; ?>"]</code>
                    <button type="button" class="button button-small" onclick="copyShortcode()">Kopyala</button>
                </div>
            </div>
            
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">İstatistikler</h2>
                </div>
                <div class="inside">
                    <?php
                    $clicks = get_option('esistenze_button_clicks_' . $button_id, 0);
                    $views = get_option('esistenze_button_views_' . $button_id, 0);
                    $ctr = $views > 0 ? round(($clicks / $views) * 100, 2) : 0;
                    ?>
                    <p><strong>Görüntülenme:</strong> <?php echo number_format($views); ?></p>
                    <p><strong>Tıklama:</strong> <?php echo number_format($clicks); ?></p>
                    <p><strong>CTR:</strong> %<?php echo $ctr; ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Show admin messages
 */
private function show_admin_messages($message) {
    if (empty($message)) return;
    
    $messages = array(
        'created' => array('success', 'Buton başarıyla oluşturuldu!'),
        'updated' => array('success', 'Buton başarıyla güncellendi!'),
        'deleted' => array('success', 'Buton başarıyla silindi!'),
        'duplicated' => array('success', 'Buton başarıyla kopyalandı!'),
        'imported' => array('success', 'Butonlar başarıyla içe aktarıldı!'),
        'error' => array('error', 'İşlem sırasında hata oluştu!')
    );
    
    if (isset($messages[$message])) {
        list($type, $text) = $messages[$message];
        echo '<div class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($text) . '</p></div>';
    }
}

/**
 * Get total clicks
 */
private function get_total_clicks() {
    $buttons = get_option('esistenze_smart_custom_buttons', []);
    $total = 0;
    
    foreach (array_keys($buttons) as $index) {
        $total += get_option('esistenze_button_clicks_' . $index, 0);
    }
    
    return $total;
}

/**
 * Get total views
 */
private function get_total_views() {
    $buttons = get_option('esistenze_smart_custom_buttons', []);
    $total = 0;
    
    foreach (array_keys($buttons) as $index) {
        $total += get_option('esistenze_button_views_' . $index, 0);
    }
    
    return $total;
}

/**
 * Enqueue admin assets
 */
private function enqueue_admin_assets() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], '5.15.4');
    
    // Admin CSS ve JS'yi inline olarak ekle
    self::enqueue_admin_assets();
}