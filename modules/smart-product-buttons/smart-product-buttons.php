<?php
/*
 * Enhanced Smart Product Buttons Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) exit;

class EsistenzeSmartButtons {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     * @return self
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Init hooks and actions
     * @return void
     */
    public function init(): void {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_esistenze_smart_button_save', array($this, 'save_button'));
        add_action('admin_post_esistenze_smart_button_delete', array($this, 'delete_button'));
        add_action('admin_post_esistenze_smart_button_duplicate', array($this, 'duplicate_button'));
        add_action('admin_post_esistenze_smart_buttons_bulk', array($this, 'bulk_actions'));
        add_action('admin_post_esistenze_smart_buttons_import', array($this, 'import_settings'));
        add_action('admin_post_esistenze_smart_buttons_export', array($this, 'export_settings'));
        add_action('wp_ajax_esistenze_smart_button_preview', array($this, 'ajax_preview'));
        add_action('wp_ajax_esistenze_smart_button_reorder', array($this, 'ajax_reorder'));
        add_action('wp_ajax_nopriv_esistenze_button_track_click', array($this, 'track_button_click'));
        add_action('wp_ajax_esistenze_button_track_click', array($this, 'track_button_click'));
        
        // Frontend hooks
        add_action('woocommerce_after_single_product_summary', array($this, 'render_buttons_on_product_page'), 15);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('wp_footer', array($this, 'render_modal_container'));
        
        // Shortcode
        add_shortcode('esistenze_button', array($this, 'button_shortcode'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        add_submenu_page(
            'esistenze-wp-kit',
            'Smart Product Buttons',
            'Smart Buttons',
            $capability,
            'esistenze-smart-buttons',
            array($this, 'admin_page')
        );
    }

    /**
     * Admin page handler
     */
    public function admin_page() {
        // Check user capability
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        if (!current_user_can($capability)) {
            wp_die(__('Bu sayfaya erişim yetkiniz bulunmuyor.', 'esistenze-wp-kit'));
        }
        
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
                <a href="<?php echo admin_url('admin.php?page=esistenze-smart-buttons&tab=add'); ?>" 
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
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    private function render_settings_page() {
        $settings = get_option('esistenze_smart_buttons_settings', array());
        
        if (isset($_POST['submit'])) {
            check_admin_referer('esistenze_smart_buttons_settings');
            
            $settings = array(
                'show_on_products' => !empty($_POST['show_on_products']),
                'enable_tracking' => !empty($_POST['enable_tracking']),
                'button_order' => sanitize_text_field($_POST['button_order'] ?? 'default')
            );
            
            update_option('esistenza_smart_buttons_settings', $settings);
            echo '<div class="notice notice-success"><p>Ayarlar kaydedildi!</p></div>';
        }
        
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('esistenze_smart_buttons_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Ürün Sayfalarında Göster</th>
                    <td>
                        <label>
                            <input type="checkbox" name="show_on_products" value="1" <?php checked(!empty($settings['show_on_products'])); ?>>
                            WooCommerce ürün sayfalarında butonları göster
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">İstatistik Takibi</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_tracking" value="1" <?php checked(!empty($settings['enable_tracking'])); ?>>
                            Buton tıklama ve görüntülenme istatistiklerini topla
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Buton Sıralaması</th>
                    <td>
                        <select name="button_order">
                            <option value="default" <?php selected($settings['button_order'] ?? 'default', 'default'); ?>>Varsayılan</option>
                            <option value="alphabetical" <?php selected($settings['button_order'] ?? 'default', 'alphabetical'); ?>>Alfabetik</option>
                            <option value="type" <?php selected($settings['button_order'] ?? 'default', 'type'); ?>>Türe Göre</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Render analytics page
     */
    private function render_analytics_page() {
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        
        echo '<h2>Buton İstatistikleri</h2>';
        
        if (empty($buttons)) {
            echo '<p>Henüz buton bulunmuyor.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Buton</th><th>Tür</th><th>Görüntülenme</th><th>Tıklama</th><th>CTR</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($buttons as $index => $button) {
            $views = get_option('esistenze_button_views_' . $index, 0);
            $clicks = get_option('esistenze_button_clicks_' . $index, 0);
            $ctr = $views > 0 ? round(($clicks / $views) * 100, 2) : 0;
            
            echo '<tr>';
            echo '<td>' . esc_html($button['title'] ?? 'Başlıksız') . '</td>';
            echo '<td>' . esc_html(self::get_type_label($button['type'] ?? '')) . '</td>';
            echo '<td>' . number_format($views) . '</td>';
            echo '<td>' . number_format($clicks) . '</td>';
            echo '<td>%' . $ctr . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }

    /**
     * Render import/export page
     */
    private function render_import_export_page() {
        ?>
        <div class="import-export-container">
            <div class="postbox">
                <h2>Dışa Aktar</h2>
                <div class="inside">
                    <p>Tüm butonlarınızı JSON formatında dışa aktarın.</p>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="esistenze_smart_buttons_export">
                        <?php wp_nonce_field('esistenze_smart_buttons_export'); ?>
                        <?php submit_button('Dışa Aktar', 'secondary'); ?>
                    </form>
                </div>
            </div>
            
            <div class="postbox">
                <h2>İçe Aktar</h2>
                <div class="inside">
                    <p>JSON formatındaki buton dosyasını içe aktarın.</p>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="esistenze_smart_buttons_import">
                        <?php wp_nonce_field('esistenze_smart_buttons_import'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">Dosya</th>
                                <td><input type="file" name="import_file" accept=".json" required></td>
                            </tr>
                            <tr>
                                <th scope="row">İçe Aktarma Modu</th>
                                <td>
                                    <label><input type="radio" name="import_mode" value="replace" checked> Mevcut butonları değiştir</label><br>
                                    <label><input type="radio" name="import_mode" value="merge"> Mevcut butonlarla birleştir</label>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button('İçe Aktar', 'primary'); ?>
                    </form>
                </div>
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
            $total += get_option('esistenza_button_views_' . $index, 0);
        }
        
        return $total;
    }

    /**
     * Enqueue admin assets
     */
    private function enqueue_admin_assets() {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], '5.15.4');
        
        // Inline CSS and JS
        $this->output_admin_styles();
        $this->output_admin_scripts();
    }
    
    private function output_admin_styles() {
        ?>
        <style>
        .esistenze-smart-buttons-wrap { max-width: 1200px; }
        .nav-tab { padding: 8px 16px; }
        .nav-tab .dashicons { margin-right: 5px; vertical-align: middle; }
        
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
        }
        
        .button-card-header {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #eee;
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
        
        .button-card-body { padding: 15px; }
        .button-title { margin: 0 0 8px; font-size: 16px; font-weight: 600; }
        .button-value { margin: 0 0 15px; color: #666; font-size: 14px; }
        
        .buttons-statistics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-box {
            text-align: center;
            padding: 20px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .stat-number { font-size: 24px; font-weight: 700; color: #4caf50; }
        .stat-label { font-size: 12px; color: #666; margin-top: 5px; }
        
        .no-buttons-message {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .form-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin: 20px 0;
        }
        
        .button-preview-container {
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            text-align: center;
            min-height: 100px;
        }
        
        @media (max-width: 1024px) {
            .form-layout { grid-template-columns: 1fr; }
            .buttons-statistics { grid-template-columns: repeat(2, 1fr); }
        }
        </style>
        <?php
    }
    
    private function output_admin_scripts() {
        ?>
        <script>
        function updateFormFields(type) {
            const valueField = document.getElementById('button_value');
            const valueDesc = document.getElementById('value_description');
            const messageField = document.getElementById('message_field');
            
            switch(type) {
                case 'phone':
                    valueField.placeholder = '+90 555 123 4567';
                    valueDesc.textContent = 'Telefon numarası (uluslararası format)';
                    messageField.style.display = 'none';
                    break;
                case 'mail':
                    valueField.placeholder = 'info@example.com';
                    valueDesc.textContent = 'E-posta adresi';
                    messageField.style.display = 'none';
                    break;
                case 'whatsapp':
                    valueField.placeholder = '+90 555 123 4567';
                    valueDesc.textContent = 'WhatsApp numarası';
                    messageField.style.display = 'table-row';
                    break;
                case 'form_trigger':
                    valueField.placeholder = '123';
                    valueDesc.textContent = 'Contact Form 7 ID numarası';
                    messageField.style.display = 'none';
                    break;
            }
            updatePreview();
        }
        
        function updatePreview() {
            const title = document.getElementById('button_title').value || 'Örnek Buton';
            const color1 = document.querySelector('input[name="button_color_start"]')?.value || '#4CAF50';
            const color2 = document.querySelector('input[name="button_color_end"]')?.value || '#45a049';
            const textColor = document.querySelector('input[name="text_color"]')?.value || '#ffffff';
            const fontSize = document.getElementById('font_size')?.value || '16';
            
            const style = `background: linear-gradient(45deg, ${color1}, ${color2}); color: ${textColor}; font-size: ${fontSize}px; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; border: none; cursor: pointer;`;
            
            const preview = document.getElementById('button_preview');
            if (preview) {
                preview.innerHTML = `<button style="${style}">${title}</button>`;
            }
        }
        
        function applyBulkAction() {
            const action = document.getElementById('bulk-action-selector-top').value;
            const selected = Array.from(document.querySelectorAll('.button-checkbox:checked')).map(cb => cb.value);
            
            if (!action || selected.length === 0) {
                alert('Lütfen bir işlem seçin ve en az bir buton işaretleyin.');
                return;
            }
            
            if (action === 'delete' && !confirm('Seçili butonları silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            document.getElementById('bulk-action-value').value = action;
            document.getElementById('bulk-selected-ids').value = selected.join(',');
            document.getElementById('bulk-action-form').submit();
        }
        
        // Initialize form if in edit mode
        document.addEventListener('DOMContentLoaded', function() {
            const typeField = document.getElementById('button_type');
            if (typeField) {
                updateFormFields(typeField.value);
                updatePreview();
            }
        });
        </script>
        <?php
    }
    
    /**
     * Register plugin settings
     * @return void
     */
    public function register_settings(): void {
        register_setting('esistenze_smart_buttons_settings', 'esistenze_smart_buttons_settings');
    }
    
    /**
     * AJAX: Preview button
     * @return void
     */
    public function ajax_preview(): void {
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
    
    /**
     * AJAX: Reorder buttons
     * @return void
     */
    public function ajax_reorder(): void {
        check_ajax_referer('esistenze_smart_button_reorder');
        
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        if (!current_user_can($capability) || empty($_POST['order'])) {
            wp_send_json_error('Yetkiniz yok veya geçersiz veri.');
        }
        
        $order = array_map('intval', $_POST['order']);
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        
        // Reorder buttons
        $reordered_buttons = [];
        foreach ($order as $index) {
            if (isset($buttons[$index])) {
                $reordered_buttons[] = $buttons[$index];
            }
        }
        
        update_option('esistenze_smart_custom_buttons', $reordered_buttons);
        wp_send_json_success('Butonlar yeniden sıralandı.');
    }
    
    /**
     * AJAX: Track button click
     * @return void
     */
    public function track_button_click(): void {
        check_ajax_referer('esistenze_button_tracking', 'nonce');
        
        $button_id = isset($_POST['button_id']) ? intval($_POST['button_id']) : 0;
        $count = get_option('esistenze_button_clicks_' . $button_id, 0);
        update_option('esistenze_button_clicks_' . $button_id, $count + 1);
        
        wp_send_json_success();
    }
    
    /**
     * Save button (admin)
     * @return void
     */
    public function save_button(): void {
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        if (!current_user_can($capability) || !check_admin_referer('esistenze_smart_button_save')) {
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

    /**
     * Delete button (admin)
     * @return void
     */
    public function delete_button(): void {
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        if (!current_user_can($capability) || !isset($_GET['id']) || !check_admin_referer('esistenze_smart_button_delete_' . $_GET['id'])) {
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
    
    /**
     * Duplicate button (admin)
     * @return void
     */
    public function duplicate_button(): void {
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        if (!current_user_can($capability) || !isset($_GET['id']) || !check_admin_referer('esistenze_smart_button_duplicate')) {
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
    
    /**
     * Bulk actions (admin)
     * @return void
     */
    public function bulk_actions(): void {
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        if (!current_user_can($capability) || !check_admin_referer('esistenze_smart_buttons_bulk')) {
            wp_die('Yetkiniz yok.');
        }
        
        $action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $button_ids = isset($_POST['button_ids']) ? explode(',', $_POST['button_ids']) : [];
        $button_ids = array_map('intval', $button_ids);
        
        if (empty($action) || empty($button_ids)) {
            wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons&message=error'));
            exit;
        }
        
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        
        switch ($action) {
            case 'delete':
                foreach ($button_ids as $id) {
                    if (isset($buttons[$id])) {
                        unset($buttons[$id]);
                    }
                }
                $buttons = array_values($buttons);
                break;
                
            case 'duplicate':
                foreach ($button_ids as $id) {
                    if (isset($buttons[$id])) {
                        $duplicate = $buttons[$id];
                        $duplicate['title'] = $duplicate['title'] . ' (Kopya)';
                        $duplicate['created'] = current_time('mysql');
                        $duplicate['modified'] = current_time('mysql');
                        $buttons[] = $duplicate;
                    }
                }
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
        }
        
        update_option('esistenze_smart_custom_buttons', $buttons);
        wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons&message=' . $action));
        exit;
    }
    
    /**
     * Import settings (admin)
     * @return void
     */
    public function import_settings(): void {
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        if (!current_user_can($capability) || !check_admin_referer('esistenze_smart_buttons_import')) {
            wp_die('Yetkiniz yok.');
        }
        
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons&tab=import-export&message=error'));
            exit;
        }
        
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        $import_data = json_decode($file_content, true);
        
        if (!$import_data || !isset($import_data['buttons'])) {
            wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons&tab=import-export&message=error'));
            exit;
        }
        
        $import_mode = sanitize_text_field($_POST['import_mode'] ?? 'replace');
        
        if ($import_mode === 'replace') {
            update_option('esistenze_smart_custom_buttons', $import_data['buttons']);
        } else {
            $current_buttons = get_option('esistenze_smart_custom_buttons', []);
            $merged_buttons = array_merge($current_buttons, $import_data['buttons']);
            update_option('esistenze_smart_custom_buttons', $merged_buttons);
        }
        
        if (isset($import_data['settings'])) {
            update_option('esistenze_smart_buttons_settings', $import_data['settings']);
        }
        
        wp_redirect(admin_url('admin.php?page=esistenze-smart-buttons&message=imported'));
        exit;
    }
    
    /**
     * Export settings (admin)
     * @return void
     */
    public function export_settings(): void {
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
        if (!current_user_can($capability) || !check_admin_referer('esistenze_smart_buttons_export')) {
            wp_die('Yetkiniz yok.');
        }
        
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        $settings = get_option('esistenze_smart_buttons_settings', []);
        
        $export_data = [
            'buttons' => $buttons,
            'settings' => $settings,
            'version' => ESISTENZE_WP_KIT_VERSION,
            'exported_at' => current_time('mysql')
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="esistenze-smart-buttons-export-' . date('Y-m-d') . '.json"');
        header('Pragma: no-cache');
        
        echo json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Render buttons on product page
     * @return void
     */
    public function render_buttons_on_product_page(): void {
        $settings = get_option('esistenze_smart_buttons_settings', []);
        
        if (empty($settings['show_on_products'])) {
            return;
        }
        
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        
        if (empty($buttons)) {
            return;
        }
        
        // Filter enabled buttons
        $enabled_buttons = array_filter($buttons, function($button) {
            return !empty($button['enabled']);
        });
        
        if (empty($enabled_buttons)) {
            return;
        }
        
        // Sort buttons if needed
        if (!empty($settings['button_order'])) {
            $this->sort_buttons($enabled_buttons, $settings['button_order']);
        }
        
        // Output buttons
        echo '<div class="esistenze-smart-buttons-frontend">';
        
        foreach ($enabled_buttons as $index => $button) {
            echo $this->render_single_button($button, $index);
            
            // Track view if enabled
            if (!empty($settings['enable_tracking'])) {
                $this->track_button_view($index);
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Button shortcode
     * @param array $atts
     * @return string
     */
    public function button_shortcode(array $atts): string {
        $atts = shortcode_atts(['id' => ''], $atts);
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $buttons = get_option('esistenze_smart_custom_buttons', []);
        $button_id = intval($atts['id']);
        
        if (!isset($buttons[$button_id]) || empty($buttons[$button_id]['enabled'])) {
            return '';
        }
        
        // Track view if enabled
        $settings = get_option('esistenze_smart_buttons_settings', []);
        if (!empty($settings['enable_tracking'])) {
            $this->track_button_view($button_id);
        }
        
        return $this->render_single_button($buttons[$button_id], $button_id);
    }
    
    /**
     * Enqueue public assets
     * @return void
     */
    public function enqueue_public_assets(): void {
        $settings = get_option('esistenze_smart_buttons_settings', []);
        
        if (empty($settings)) {
            return;
        }
        
        wp_enqueue_style('esistenze-smart-buttons-css', ESISTENZE_WP_KIT_URL . 'modules/smart-product-buttons/assets/style.css', [], ESISTENZE_WP_KIT_VERSION);
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], '5.15.4');
        
        wp_enqueue_script('esistenze-smart-buttons-js', ESISTENZE_WP_KIT_URL . 'modules/smart-product-buttons/assets/script.js', ['jquery'], ESISTENZE_WP_KIT_VERSION, true);
        
        // Add tracking if enabled
        if (!empty($settings['enable_tracking'])) {
            wp_localize_script('esistenze-smart-buttons-js', 'esistenzeButtons', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('esistenze_button_tracking')
            ]);
        }
    }
    
    /**
     * Render modal container
     * @return void
     */
    public function render_modal_container(): void {
        // This adds a container for form popups
        if (!is_product()) {
            return;
        }
        
        echo '<div id="esistenze-form-modal" class="esistenze-smart-modal">';
        echo '<div class="esistenze-smart-modal-content">';
        echo '<span class="esistenze-smart-close">&times;</span>';
        echo '<div class="esistenze-smart-form-container"></div>';
        echo '</div>';
        echo '</div>';
        
        // Add hidden container for Contact Form 7 content
        if (shortcode_exists('contact-form-7')) {
            $buttons = get_option('esistenze_smart_custom_buttons', []);
            $enabled_buttons = array_filter($buttons, function($button) {
                return !empty($button['enabled']) && $button['type'] === 'form_trigger';
            });
            
            if (!empty($enabled_buttons)) {
                echo '<div id="esistenze-form-modal-content" style="display:none;">';
                
                foreach ($enabled_buttons as $button) {
                    if (!empty($button['value']) && is_numeric($button['value'])) {
                        echo do_shortcode('[contact-form-7 id="' . intval($button['value']) . '"]');
                    }
                }
                
                echo '</div>';
            }
        }
    }
    
    // Helper Methods
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
        
        $data_attrs = 'data-track="' . esc_attr($track) . '" data-id="' . esc_attr($value) . '" data-button-id="' . esc_attr($index) . '"';
        $icon_html = $icon ? '<i class="fa ' . esc_attr($icon) . '"></i> ' : '';
        $style = 'style="background: linear-gradient(45deg, ' . esc_attr($color1) . ', ' . esc_attr($color2) . ') !important; color: ' . esc_attr($text_color) . ' !important; font-size: ' . min($font_size, 20) . 'px !important;"';

        switch ($type) {
            case 'phone':
                return '<a href="tel:' . esc_attr($value) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data_attrs . '>' . $icon_html . esc_html($title) . '</a>';
                
            case 'mail':
                return '<a href="mailto:' . esc_attr($value) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data_attrs . '>' . $icon_html . esc_html($title) . '</a>';
                
            case 'whatsapp':
                $url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $value);
                if ($message) $url .= '?text=' . urlencode($message);
                return '<a target="_blank" href="' . esc_url($url) . '" class="esistenze-smart-btn" ' . $style . ' ' . $data_attrs . '>' . $icon_html . esc_html($title) . '</a>';
                
            case 'form_trigger':
                return '<button type="button" class="esistenze-smart-btn esistenze-form-popup-trigger" ' . $style . ' ' . $data_attrs . '>' . $icon_html . esc_html($title) . '</button>';
        }
        
        return '';
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
        return isset($icons[$type]) ? $icons[$type] : '🔘';
    }
    
    private static function get_type_label($type) {
        $labels = array(
            'phone' => 'Telefon',
            'mail' => 'E-posta',
            'whatsapp' => 'WhatsApp',
            'form_trigger' => 'Form'
        );
        return isset($labels[$type]) ? $labels[$type] : 'Bilinmiyor';
    }
    
    private function track_button_view($button_id) {
        $count = get_option('esistenze_button_views_' . $button_id, 0);
        update_option('esistenze_button_views_' . $button_id, $count + 1);
    }
    
    private function sort_buttons(&$buttons, $order_type) {
        switch ($order_type) {
            case 'alphabetical':
                usort($buttons, function($a, $b) {
                    return strcmp($a['title'] ?? '', $b['title'] ?? '');
                });
                break;
                
            case 'type':
                usort($buttons, function($a, $b) {
                    return strcmp($a['type'] ?? '', $b['type'] ?? '');
                });
                break;
        }
    }
}

// Initialize the module
EsistenzeSmartButtons::getInstance();