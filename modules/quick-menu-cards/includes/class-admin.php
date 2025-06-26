<?php
/*
 * Quick Menu Cards - Admin Class
 * Handles all admin panel functionality with modern architecture
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsAdmin {
    
    private $module_path;
    private $module_url;
    private $capability;
    private $page_hooks;
    
    public function __construct($module_path, $module_url) {
        $this->module_path = $module_path;
        $this->module_url = $module_url;
        $this->capability = esistenze_qmc_capability();
        $this->page_hooks = array();
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
        add_action('admin_post_esistenze_save_group', array($this, 'handle_save_group'));
        add_action('admin_post_esistenze_delete_group', array($this, 'handle_delete_group'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        
        // AJAX hooks for live preview
        add_action('wp_ajax_esistenze_live_preview', array($this, 'ajax_live_preview'));
        add_action('wp_ajax_esistenze_get_media', array($this, 'ajax_get_media'));
        
        // Bulk actions
        add_filter('bulk_actions-toplevel_page_esistenze-quick-menu', array($this, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-toplevel_page_esistenze-quick-menu', array($this, 'handle_bulk_actions'), 10, 3);
    }
    
    public function admin_menu() {
        // Ana menü
        $this->page_hooks['main'] = add_menu_page(
            'Quick Menu Cards',
            'Quick Menu Cards',
            $this->capability,
            'esistenze-quick-menu',
            array($this, 'admin_page'),
            'dashicons-grid-view',
            30
        );
        
        // Alt menüler
        $this->page_hooks['groups'] = add_submenu_page(
            'esistenze-quick-menu',
            'Kart Grupları',
            'Kart Grupları',
            $this->capability,
            'esistenza-quick-menu',
            array($this, 'admin_page')
        );
        
        $this->page_hooks['settings'] = add_submenu_page(
            'esistenze-quick-menu',
            'Ayarlar',
            'Ayarlar',
            $this->capability,
            'esistenze-quick-menu-settings',
            array($this, 'settings_page')
        );
        
        $this->page_hooks['analytics'] = add_submenu_page(
            'esistenze-quick-menu',
            'İstatistikler',
            'İstatistikler',
            $this->capability,
            'esistenze-quick-menu-analytics',
            array($this, 'analytics_page')
        );
        
        $this->page_hooks['tools'] = add_submenu_page(
            'esistenze-quick-menu',
            'Araçlar',
            'Araçlar',
            $this->capability,
            'esistenze-quick-menu-tools',
            array($this, 'tools_page')
        );
        
        // Kontextüel yardım ekle
        foreach ($this->page_hooks as $hook) {
            add_action('load-' . $hook, array($this, 'add_contextual_help'));
        }
    }
    
    public function register_settings() {
        register_setting('esistenze_quick_menu_cards', 'esistenze_quick_menu_kartlari', array(
            'sanitize_callback' => array($this, 'sanitize_cards_data'),
            'show_in_rest' => false,
            'default' => array()
        ));
        
        register_setting('esistenze_quick_menu_settings', 'esistenze_quick_menu_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings_data'),
            'show_in_rest' => false,
            'default' => EsistenzeQuickMenuCards::get_default_settings()
        ));
        
        register_setting('esistenze_quick_menu_analytics', 'esistenze_quick_menu_analytics', array(
            'sanitize_callback' => array($this, 'sanitize_analytics_data'),
            'show_in_rest' => false,
            'default' => array()
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        // Sadece kendi sayfalarımızda script yükle
        if (!in_array($hook, $this->page_hooks)) {
            return;
        }
        
        // WordPress media uploader
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('wp-util');
        
        // Admin styles
        wp_enqueue_style(
            'esistenze-quick-menu-admin',
            $this->module_url . 'assets/admin.css',
            array('wp-color-picker', 'dashicons'),
            $this->get_version()
        );
        
        // Admin scripts
        wp_enqueue_script(
            'esistenze-quick-menu-admin',
            $this->module_url . 'assets/admin.js',
            array('jquery', 'wp-color-picker', 'jquery-ui-sortable', 'wp-util'),
            $this->get_version(),
            true
        );
        
        // Localize script
        wp_localize_script('esistenze-quick-menu-admin', 'esistenzeAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('esistenze_quick_menu_nonce'),
            'post_url' => admin_url('admin-post.php'),
            'media_title' => 'Görsel Seç',
            'media_button' => 'Seçilen Görseli Kullan',
            'strings' => array(
                'delete_confirm' => 'Bu grubu silmek istediğinizden emin misiniz?',
                'delete_card_confirm' => 'Bu kartı silmek istediğinizden emin misiniz?',
                'save_success' => 'Grup başarıyla kaydedildi!',
                'save_error' => 'Kayıt sırasında hata oluştu.',
                'copy_success' => 'Shortcode kopyalandı!',
                'required_field' => 'Bu alan zorunludur.',
                'max_cards' => 'En fazla 20 kart ekleyebilirsiniz.',
                'loading' => 'Yükleniyor...',
                'preview' => 'Önizleme',
                'invalid_url' => 'Geçersiz URL formatı.',
                'unsaved_changes' => 'Kaydedilmemiş değişiklikleriniz var. Sayfadan çıkmak istediğinizden emin misiniz?'
            ),
            'settings' => array(
                'max_cards_per_group' => 20,
                'auto_save' => true,
                'preview_enabled' => true,
                'debug_mode' => defined('WP_DEBUG') && WP_DEBUG
            )
        ));
    }
    
    public function admin_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'groups';
        $current_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        
        echo '<div class="wrap esistenze-quick-menu-wrap">';
        echo '<h1 class="wp-heading-inline">Quick Menu Cards</h1>';
        
        // Yeni grup ekle butonu
        if ($current_tab === 'groups') {
            echo '<a href="' . esc_url(admin_url('admin.php?page=esistenze-quick-menu&tab=edit&action=new')) . '" class="page-title-action">Yeni Grup Ekle</a>';
        }
        
        echo '<hr class="wp-header-end">';
        
        // Tab navigation
        $this->render_admin_tabs($current_tab);
        
        // İçerik alanı
        echo '<div class="tab-content">';
        
        switch ($current_tab) {
            case 'groups':
            default:
                $this->render_groups_page();
                break;
            case 'edit':
                $this->render_edit_page();
                break;
        }
        
        echo '</div></div>';
    }
    
    private function render_admin_tabs($current_tab) {
        $tabs = array(
            'groups' => array(
                'title' => 'Kart Grupları',
                'icon' => 'dashicons-grid-view'
            )
        );
        
        // Edit tab (sadece edit modundayken göster)
        if (isset($_GET['action']) && ($_GET['action'] === 'edit' || $_GET['action'] === 'new')) {
            $edit_title = $_GET['action'] === 'new' ? 'Yeni Grup' : 'Grup Düzenle';
            $tabs['edit'] = array(
                'title' => $edit_title,
                'icon' => 'dashicons-edit'
            );
        }
        
        echo '<nav class="nav-tab-wrapper wp-clearfix">';
        foreach ($tabs as $tab_key => $tab_data) {
            $class = ($current_tab === $tab_key) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $url = admin_url('admin.php?page=esistenza-quick-menu&tab=' . $tab_key);
            
            if ($tab_key === 'edit' && isset($_GET['action'])) {
                $url .= '&action=' . sanitize_text_field($_GET['action']);
                if (isset($_GET['edit_group'])) {
                    $url .= '&edit_group=' . intval($_GET['edit_group']);
                }
            }
            
            echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">';
            echo '<span class="dashicons ' . esc_attr($tab_data['icon']) . '"></span> ';
            echo esc_html($tab_data['title']);
            echo '</a>';
        }
        echo '</nav>';
    }
    
    private function render_groups_page() {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        if (!is_array($kartlar)) {
            $kartlar = array();
            update_option('esistenze_quick_menu_kartlari', $kartlar);
        }
        
        // Sayfa değişkenleri
        $per_page = 10;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $total_groups = count($kartlar);
        $offset = ($current_page - 1) * $per_page;
        $groups_slice = array_slice($kartlar, $offset, $per_page, true);
        
        // Arama fonksiyonu
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        if (!empty($search)) {
            $groups_slice = array_filter($kartlar, function($group, $id) use ($search) {
                if (!is_array($group)) return false;
                foreach ($group as $card) {
                    if (stripos($card['title'] ?? '', $search) !== false ||
                        stripos($card['desc'] ?? '', $search) !== false) {
                        return true;
                    }
                }
                return false;
            }, ARRAY_FILTER_USE_BOTH);
        }
        
        include $this->module_path . 'views/admin-groups.php';
    }
    
    private function render_edit_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'edit';
        $group_id = isset($_GET['edit_group']) ? intval($_GET['edit_group']) : -1;
        
        if ($action === 'new') {
            $group_id = -1; // Yeni grup
            $group_data = array();
        } else {
            if ($group_id < 0) {
                wp_redirect(admin_url('admin.php?page=esistenza-quick-menu'));
                exit;
            }
            
            $kartlar = get_option('esistenze_quick_menu_kartlari', array());
            if (!isset($kartlar[$group_id]) || !is_array($kartlar[$group_id])) {
                wp_die('Grup bulunamadı.');
            }
            $group_data = $kartlar[$group_id];
        }
        
        include $this->module_path . 'views/admin-edit.php';
    }
    
    public function settings_page() {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        // Form işleme
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'esistenze_quick_menu_settings_save')) {
            $this->save_settings($_POST);
            $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        }
        
        include $this->module_path . 'views/admin-settings.php';
    }
    
    public function analytics_page() {
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $settings = get_option('esistenze_quick_menu_settings', array());
        
        // Analytics verilerini işle
        $processed_analytics = $this->process_analytics_data($analytics, $kartlar);
        
        // Tarih filtreleme
        $date_filter = isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : '30_days';
        
        include $this->module_path . 'views/admin-analytics.php';
    }
    
    public function tools_page() {
        $tools_data = array(
            'export_available' => true,
            'import_available' => true,
            'cache_stats' => $this->get_cache_stats(),
            'system_info' => $this->get_system_info()
        );
        
        include $this->module_path . 'views/admin-tools.php';
    }
    
    // Form handling methods
    public function handle_save_group() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'esistenze_save_group') || !current_user_can($this->capability)) {
            wp_die('Yetkisiz erişim.');
        }
        
        $group_id = intval($_POST['group_id'] ?? -1);
        $cards_data = $_POST['cards'] ?? array();
        
        // Veri doğrulama
        $sanitized_cards = array();
        foreach ($cards_data as $card_data) {
            $sanitized_card = $this->sanitize_card_data($card_data);
            $validation = $this->validate_card_data($sanitized_card);
            
            if ($validation !== true) {
                wp_redirect(add_query_arg(array(
                    'tab' => 'edit',
                    'action' => $group_id === -1 ? 'new' : 'edit',
                    'edit_group' => $group_id !== -1 ? $group_id : null,
                    'error' => urlencode(implode(', ', $validation))
                ), admin_url('admin.php?page=esistenza-quick-menu')));
                exit;
            }
            
            $sanitized_cards[] = $sanitized_card;
        }
        
        // Veritabanına kaydet
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        if ($group_id === -1) {
            // Yeni grup
            $kartlar[] = $sanitized_cards;
            $new_group_id = count($kartlar) - 1;
        } else {
            // Mevcut grubu güncelle
            $kartlar[$group_id] = $sanitized_cards;
            $new_group_id = $group_id;
        }
        
        $result = update_option('esistenze_quick_menu_kartlari', $kartlar);
        
        if ($result) {
            $this->clear_cache();
            wp_redirect(add_query_arg(array(
                'tab' => 'groups',
                'success' => 'saved',
                'group_id' => $new_group_id
            ), admin_url('admin.php?page=esistenza-quick-menu')));
        } else {
            wp_redirect(add_query_arg(array(
                'tab' => 'edit',
                'action' => $group_id === -1 ? 'new' : 'edit',
                'edit_group' => $group_id !== -1 ? $group_id : null,
                'error' => 'save_failed'
            ), admin_url('admin.php?page=esistenza-quick-menu')));
        }
        exit;
    }
    
    public function handle_delete_group() {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'esistenze_delete_group') || !current_user_can($this->capability)) {
            wp_die('Yetkisiz erişim.');
        }
        
        $group_id = intval($_GET['group_id'] ?? -1);
        
        if ($group_id < 0) {
            wp_die('Geçersiz grup ID.');
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        if (isset($kartlar[$group_id])) {
            unset($kartlar[$group_id]);
            $kartlar = array_values($kartlar); // Re-index
            
            update_option('esistenze_quick_menu_kartlari', $kartlar);
            $this->cleanup_group_analytics($group_id);
            $this->clear_cache();
            
            wp_redirect(add_query_arg(array(
                'success' => 'deleted'
            ), admin_url('admin.php?page=esistenza-quick-menu')));
        } else {
            wp_redirect(add_query_arg(array(
                'error' => 'group_not_found'
            ), admin_url('admin.php?page=esistenza-quick-menu')));
        }
        exit;
    }
    
    private function save_settings($post_data) {
        if (!current_user_can($this->capability)) {
            wp_die('Yetkiniz yok.');
        }
        
        $settings = $post_data['settings'] ?? array();
        $sanitized_settings = $this->sanitize_settings_data($settings);
        
        $result = update_option('esistenze_quick_menu_settings', $sanitized_settings);
        
        if ($result) {
            $this->clear_cache();
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Ayarlar başarıyla kaydedildi!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Ayarlar kaydedilirken hata oluştu!</p></div>';
            });
        }
    }
    
    // AJAX methods
    public function ajax_live_preview() {
        if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce')) {
            wp_send_json_error('Nonce doğrulaması başarısız.');
        }
        
        $card_data = $_POST['card_data'] ?? array();
        $preview_type = sanitize_text_field($_POST['preview_type'] ?? 'grid');
        
        $sanitized_card = $this->sanitize_card_data($card_data);
        
        $html = '';
        if ($preview_type === 'banner') {
            $html = $this->generate_banner_preview($sanitized_card);
        } else {
            $html = $this->generate_card_preview($sanitized_card);
        }
        
        wp_send_json_success(array('html' => $html));
    }
    
    public function ajax_get_media() {
        if (!wp_verify_nonce($_POST['nonce'], 'esistenze_quick_menu_nonce')) {
            wp_send_json_error('Nonce doğrulaması başarısız.');
        }
        
        $attachment_id = intval($_POST['attachment_id'] ?? 0);
        
        if ($attachment_id <= 0) {
            wp_send_json_error('Geçersiz ek dosya ID.');
        }
        
        $image_url = wp_get_attachment_image_url($attachment_id, 'medium');
        $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        
        if (!$image_url) {
            wp_send_json_error('Görsel bulunamadı.');
        }
        
        wp_send_json_success(array(
            'url' => $image_url,
            'alt' => $image_alt,
            'id' => $attachment_id
        ));
    }
    
    // Bulk actions
    public function add_bulk_actions($bulk_actions) {
        $bulk_actions['delete_groups'] = 'Seçili Grupları Sil';
        $bulk_actions['duplicate_groups'] = 'Seçili Grupları Kopyala';
        return $bulk_actions;
    }
    
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        if ($doaction === 'delete_groups') {
            $deleted = 0;
            $kartlar = get_option('esistenze_quick_menu_kartlari', array());
            
            foreach ($post_ids as $group_id) {
                if (isset($kartlar[$group_id])) {
                    unset($kartlar[$group_id]);
                    $deleted++;
                }
            }
            
            if ($deleted > 0) {
                $kartlar = array_values($kartlar);
                update_option('esistenza_quick_menu_kartlari', $kartlar);
                $this->clear_cache();
            }
            
            $redirect_to = add_query_arg('bulk_deleted', $deleted, $redirect_to);
        }
        
        return $redirect_to;
    }
    
    // Dashboard widget
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'esistenze_quick_menu_stats',
            'Quick Menu Cards İstatistikleri',
            array($this, 'dashboard_widget_content')
        );
    }
    
    public function dashboard_widget_content() {
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        $total_groups = count($kartlar);
        $total_cards = array_sum(array_map('count', $kartlar));
        $total_views = $analytics['total_views'] ?? 0;
        $total_clicks = $analytics['total_clicks'] ?? 0;
        $ctr = $total_views > 0 ? round(($total_clicks / $total_views) * 100, 2) : 0;
        
        echo '<div class="activity-block">';
        echo '<h4>Genel İstatistikler</h4>';
        echo '<ul>';
        echo '<li><strong>' . $total_groups . '</strong> grup</li>';
        echo '<li><strong>' . $total_cards . '</strong> kart</li>';
        echo '<li><strong>' . number_format($total_views) . '</strong> görüntülenme</li>';
        echo '<li><strong>' . number_format($total_clicks) . '</strong> tıklama</li>';
        echo '<li><strong>%' . $ctr . '</strong> CTR</li>';
        echo '</ul>';
        echo '<p><a href="' . admin_url('admin.php?page=esistenza-quick-menu-analytics') . '">Detayları Görüntüle</a></p>';
        echo '</div>';
    }
    
    // Contextual help
    public function add_contextual_help() {
        $screen = get_current_screen();
        
        $screen->add_help_tab(array(
            'id' => 'esistenza_overview',
            'title' => 'Genel Bakış',
            'content' => '<p>Quick Menu Cards ile sitenizde interaktif kart menüleri oluşturabilirsiniz.</p>'
        ));
        
        $screen->add_help_tab(array(
            'id' => 'esistenza_shortcodes',
            'title' => 'Shortcode\'lar',
            'content' => '<p><strong>[quick_menu_cards id="0"]</strong> - Kart görünümü<br>' .
                        '<strong>[quick_menu_banner id="0"]</strong> - Banner görünümü</p>'
        ));
        
        $screen->set_help_sidebar('<p><strong>Destek için:</strong><br><a href="#">Dokümantasyon</a></p>');
    }
    
    // Utility methods
    public function show_admin_notices() {
        // Başarı mesajları
        if (isset($_GET['success'])) {
            $success_type = sanitize_text_field($_GET['success']);
            $messages = array(
                'saved' => 'Grup başarıyla kaydedildi!',
                'deleted' => 'Grup başarıyla silindi!',
                'duplicated' => 'Grup başarıyla kopyalandı!'
            );
            
            if (isset($messages[$success_type])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$success_type]) . '</p></div>';
            }
        }
        
        // Hata mesajları
        if (isset($_GET['error'])) {
            $error_type = sanitize_text_field($_GET['error']);
            $messages = array(
                'save_failed' => 'Kaydetme işlemi başarısız oldu!',
                'group_not_found' => 'Grup bulunamadı!',
                'invalid_data' => 'Geçersiz veri!'
            );
            
            if (isset($messages[$error_type])) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($messages[$error_type]) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(urldecode($error_type)) . '</p></div>';
            }
        }
        
        // Sistem kontrolleri
        $this->show_system_notices();
    }
    
    private function show_system_notices() {
        // WordPress version kontrolü
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            echo '<div class="notice notice-warning"><p><strong>Uyarı:</strong> WordPress 5.0 ve üzeri sürüm önerilir.</p></div>';
        }
        
        // PHP version kontrolü
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            echo '<div class="notice notice-warning"><p><strong>Uyarı:</strong> PHP 7.4 ve üzeri sürüm önerilir.</p></div>';
        }
        
        // Performans ipuçları
        $settings = get_option('esistenza_quick_menu_settings', array());
        if (empty($settings['enable_lazy_loading'])) {
            echo '<div class="notice notice-info is-dismissible"><p><strong>İpucu:</strong> Performans için lazy loading\'i etkinleştirin.</p></div>';
        }
        
        // Cache uyarısı
        if (!empty($settings['cache_duration']) && !$this->is_caching_plugin_active()) {
            echo '<div class="notice notice-info is-dismissible"><p><strong>İpucu:</strong> Daha iyi performans için bir cache eklentisi kullanın.</p></div>';
        }
    }
    
    // Helper methods
    public function sanitize_cards_data($input) {
        if (!is_array($input)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($input as $group_id => $group) {
            if (!is_array($group)) continue;
            
            $sanitized_group = array();
            foreach ($group as $card) {
                $sanitized_group[] = $this->sanitize_card_data($card);
            }
            $sanitized[$group_id] = $sanitized_group;
        }
        
        return $sanitized;
    }
    
    public function sanitize_settings_data($input) {
        $defaults = EsistenzeQuickMenuCards::get_default_settings();
        $sanitized = array();
        
        foreach ($defaults as $key => $default_value) {
            if (isset($input[$key])) {
                switch ($key) {
                    case 'default_button_text':
                    case 'banner_button_text':
                        $sanitized[$key] = sanitize_text_field($input[$key]);
                        break;
                    case 'custom_css':
                        $sanitized[$key] = wp_strip_all_tags($input[$key]);
                        break;
                    case 'enable_analytics':
                    case 'enable_lazy_loading':
                    case 'enable_schema_markup':
                    case 'enable_gpu_acceleration':
                    case 'enable_dark_mode':
                        $sanitized[$key] = !empty($input[$key]);
                        break;
                    case 'mobile_columns':
                    case 'cache_duration':
                        $sanitized[$key] = max(0, intval($input[$key]));
                        break;
                    default:
                        $sanitized[$key] = $default_value;
                        break;
                }
            } else {
                $sanitized[$key] = $default_value;
            }
        }
        
        return $sanitized;
    }
    
    public function sanitize_analytics_data($input) {
        if (!is_array($input)) {
            return array();
        }
        
        $sanitized = array();
        $allowed_keys = array('total_views', 'total_clicks', 'group_views', 'group_clicks', 'card_clicks', 'last_view', 'last_click', 'click_details');
        
        foreach ($allowed_keys as $key) {
            if (isset($input[$key])) {
                if (in_array($key, array('total_views', 'total_clicks'))) {
                    $sanitized[$key] = max(0, intval($input[$key]));
                } elseif (in_array($key, array('group_views', 'group_clicks', 'card_clicks'))) {
                    $sanitized[$key] = is_array($input[$key]) ? $input[$key] : array();
                } elseif (in_array($key, array('last_view', 'last_click'))) {
                    $sanitized[$key] = sanitize_text_field($input[$key]);
                } elseif ($key === 'click_details') {
                    $sanitized[$key] = is_array($input[$key]) ? array_slice($input[$key], -1000) : array();
                }
            }
        }
        
        return $sanitized;
    }
    
    public function sanitize_card_data($card_data) {
        return array(
            'title' => sanitize_text_field($card_data['title'] ?? ''),
            'desc' => sanitize_textarea_field($card_data['desc'] ?? ''),
            'img' => esc_url_raw($card_data['img'] ?? ''),
            'url' => esc_url_raw($card_data['url'] ?? ''),
            'order' => max(0, intval($card_data['order'] ?? 0)),
            'enabled' => !empty($card_data['enabled']),
            'created_at' => $card_data['created_at'] ?? current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
    }
    
    public function validate_card_data($card_data) {
        $errors = array();
        
        if (empty($card_data['title'])) {
            $errors[] = 'Kart başlığı zorunludur.';
        }
        
        if (strlen($card_data['title']) > 100) {
            $errors[] = 'Kart başlığı 100 karakterden uzun olamaz.';
        }
        
        if (strlen($card_data['desc']) > 500) {
            $errors[] = 'Kart açıklaması 500 karakterden uzun olamaz.';
        }
        
        if (!empty($card_data['url']) && !filter_var($card_data['url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Geçerli bir URL giriniz.';
        }
        
        if (!empty($card_data['img']) && !filter_var($card_data['img'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Geçerli bir görsel URL\'i giriniz.';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    // Analytics processing
    private function process_analytics_data($analytics, $kartlar) {
        $processed = array(
            'total_groups' => count($kartlar),
            'total_cards' => array_sum(array_map('count', $kartlar)),
            'total_views' => $analytics['total_views'] ?? 0,
            'total_clicks' => $analytics['total_clicks'] ?? 0,
            'ctr' => 0,
            'top_groups' => array(),
            'recent_activity' => array()
        );
        
        // CTR hesapla
        if ($processed['total_views'] > 0) {
            $processed['ctr'] = round(($processed['total_clicks'] / $processed['total_views']) * 100, 2);
        }
        
        // En popüler grupları bul
        if (!empty($analytics['group_clicks'])) {
            arsort($analytics['group_clicks']);
            $processed['top_groups'] = array_slice($analytics['group_clicks'], 0, 5, true);
        }
        
        // Son aktiviteleri işle
        if (!empty($analytics['click_details'])) {
            $processed['recent_activity'] = array_slice($analytics['click_details'], -10);
        }
        
        return $processed;
    }
    
    // Cache and system info
    private function get_cache_stats() {
        global $wpdb;
        
        $transients = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_qmc_%'");
        
        return array(
            'transient_count' => intval($transients),
            'object_cache_enabled' => wp_using_ext_object_cache(),
            'caching_plugin' => $this->get_active_cache_plugin()
        );
    }
    
    private function get_system_info() {
        global $wp_version;
        
        return array(
            'wp_version' => $wp_version,
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->get_mysql_version(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'active_theme' => wp_get_theme()->get('Name'),
            'active_plugins' => count(get_option('active_plugins', array()))
        );
    }
    
    private function get_mysql_version() {
        global $wpdb;
        return $wpdb->get_var("SELECT VERSION()");
    }
    
    private function get_active_cache_plugin() {
        $cache_plugins = array(
            'wp-rocket/wp-rocket.php' => 'WP Rocket',
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            'wp-super-cache/wp-cache.php' => 'WP Super Cache',
            'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
            'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache'
        );
        
        foreach ($cache_plugins as $plugin_file => $plugin_name) {
            if (is_plugin_active($plugin_file)) {
                return $plugin_name;
            }
        }
        
        return 'Yok';
    }
    
    private function is_caching_plugin_active() {
        return $this->get_active_cache_plugin() !== 'Yok';
    }
    
    // Preview generation
    private function generate_card_preview($card) {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        $html = '<div class="esistenze-quick-menu-wrapper preview-wrapper" style="max-width: 300px;">';
        $html .= '<div class="esistenze-quick-menu-kart">';
        $html .= '<div class="esistenze-quick-menu-icerik">';
        
        if (!empty($card['img'])) {
            $html .= '<img src="' . esc_url($card['img']) . '" alt="' . esc_attr($card['title']) . '" style="max-height: 150px;">';
        }
        
        $html .= '<div class="esistenze-quick-menu-yazi">';
        $html .= '<h4>' . esc_html($card['title'] ?: 'Başlık') . '</h4>';
        $html .= '<p>' . esc_html($card['desc'] ?: 'Açıklama') . '</p>';
        $html .= '</div></div>';
        $html .= '<div class="esistenze-quick-menu-buton">' . esc_html($settings['default_button_text']) . '</div>';
        $html .= '</div></div>';
        
        return $html;
    }
    
    private function generate_banner_preview($card) {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        if (empty($card['img'])) {
            return '<p style="text-align: center; color: #666;">Banner görünüm için görsel gereklidir.</p>';
        }
        
        $html = '<div class="esistenze-quick-menu-banner-wrapper preview-wrapper" style="max-width: 500px;">';
        $html .= '<div class="esistenza-quick-menu-banner">';
        $html .= '<div class="banner-img" style="width: 80px; height: 80px;"><img src="' . esc_url($card['img']) . '" alt="' . esc_attr($card['title']) . '" style="max-width: 100%; max-height: 100%; object-fit: contain;"></div>';
        $html .= '<div class="banner-text">';
        $html .= '<h4>' . esc_html($card['title'] ?: 'Başlık') . '</h4>';
        $html .= '<p>' . esc_html($card['desc'] ?: 'Açıklama') . '</p>';
        $html .= '</div>';
        $html .= '<div class="banner-button"><span>' . esc_html($settings['banner_button_text']) . '</span></div>';
        $html .= '</div></div>';
        
        return $html;
    }
    
    // Group management helpers
    public function get_total_cards_count() {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        return array_sum(array_map('count', $kartlar));
    }
    
    public function get_group_stats($group_id) {
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        
        $views = $analytics['group_views'][$group_id] ?? 0;
        $clicks = $analytics['group_clicks'][$group_id] ?? 0;
        $ctr = $views > 0 ? round(($clicks / $views) * 100, 2) : 0;
        
        return array(
            'views' => $views,
            'clicks' => $clicks,
            'ctr' => $ctr
        );
    }
    
    public function get_shortcode_preview($group_id, $type = 'grid') {
        $params = array('id' => $group_id);
        
        if ($type === 'banner') {
            return '[quick_menu_banner id="' . intval($group_id) . '"]';
        }
        
        return '[quick_menu_cards id="' . intval($group_id) . '"]';
    }
    
    private function cleanup_group_analytics($group_id) {
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        
        // Grup ile ilgili analytics verilerini temizle
        unset(
            $analytics['group_views'][$group_id],
            $analytics['group_clicks'][$group_id],
            $analytics['card_clicks'][$group_id]
        );
        
        // Click details'den o grup ile ilgili kayıtları kaldır
        if (!empty($analytics['click_details'])) {
            $analytics['click_details'] = array_filter($analytics['click_details'], function($detail) use ($group_id) {
                return ($detail['group_id'] ?? -1) != $group_id;
            });
        }
        
        update_option('esistenze_quick_menu_analytics', $analytics);
    }
    
    private function clear_cache() {
        // Transient cache temizle
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_qmc_%' OR option_name LIKE '_transient_timeout_qmc_%'");
        
        // Object cache temizle
        wp_cache_delete('esistenze_quick_menu_cards', 'esistenze');
        wp_cache_delete('esistenza_quick_menu_settings', 'esistenze');
        
        // External cache plugins
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        
        if (function_exists('wp_rocket_clean_domain')) {
            wp_rocket_clean_domain();
        }
        
        do_action('esistenza_quick_menu_cache_cleared');
    }
    
    private function get_version() {
        return defined('ESISTENZA_WP_KIT_VERSION') ? ESISTENZA_WP_KIT_VERSION : '1.0.0';
    }
    
    // Debug helper
    public function debug_info() {
        if (!current_user_can($this->capability) || !defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        echo '<div class="notice notice-info">';
        echo '<h4>Debug Bilgileri:</h4>';
        echo '<ul>';
        echo '<li>WordPress Version: ' . get_bloginfo('version') . '</li>';
        echo '<li>PHP Version: ' . PHP_VERSION . '</li>';
        echo '<li>Module Path: ' . $this->module_path . '</li>';
        echo '<li>Module URL: ' . $this->module_url . '</li>';
        echo '<li>Total Groups: ' . count(get_option('esistenza_quick_menu_kartlari', array())) . '</li>';
        echo '<li>Total Cards: ' . $this->get_total_cards_count() . '</li>';
        echo '<li>Memory Usage: ' . size_format(memory_get_usage(true)) . '</li>';
        echo '<li>Peak Memory: ' . size_format(memory_get_peak_usage(true)) . '</li>';
        echo '</ul>';
        echo '</div>';
    }
}

?>