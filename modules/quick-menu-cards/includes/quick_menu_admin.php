<?php
/*
 * Quick Menu Cards - Admin Class
 * Handles all admin panel functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsAdmin {
    
    private $module_path;
    private $module_url;
    
    public function __construct($module_path, $module_url) {
        $this->module_path = $module_path;
        $this->module_url = $module_url;
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }
    
    public function admin_menu() {
        add_menu_page(
            'Quick Menu Cards',
            'Quick Menu Cards',
            'manage_options',
            'esistenze-quick-menu',
            array($this, 'admin_page'),
            'dashicons-grid-view',
            30
        );
        
        add_submenu_page(
            'esistenze-quick-menu',
            'Kart Grupları',
            'Kart Grupları',
            'manage_options',
            'esistenze-quick-menu',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'esistenze-quick-menu',
            'Ayarlar',
            'Ayarlar',
            'manage_options',
            'esistenze-quick-menu-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'esistenze-quick-menu',
            'İstatistikler',
            'İstatistikler',
            'manage_options',
            'esistenze-quick-menu-analytics',
            array($this, 'analytics_page')
        );
    }
    
    public function register_settings() {
        register_setting('esistenze_quick_menu_cards', 'esistenze_quick_menu_kartlari');
        register_setting('esistenze_quick_menu_settings', 'esistenze_quick_menu_settings');
        register_setting('esistenze_quick_menu_analytics', 'esistenze_quick_menu_analytics');
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'esistenze-quick-menu') === false) {
            return;
        }
        
        // WordPress media uploader
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Admin styles
        wp_enqueue_style(
            'esistenze-quick-menu-admin',
            $this->module_url . 'assets/admin.css',
            array('wp-color-picker'),
            $this->get_version()
        );
        
        // Admin scripts
        wp_enqueue_script(
            'esistenze-quick-menu-admin',
            $this->module_url . 'assets/admin.js',
            array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
            $this->get_version(),
            true
        );
        
        // Localize script
        wp_localize_script('esistenze-quick-menu-admin', 'esistenzeAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('esistenze_quick_menu_nonce'),
            'strings' => array(
                'delete_confirm' => 'Bu grubu silmek istediğinizden emin misiniz?',
                'delete_card_confirm' => 'Bu kartı silmek istediğinizden emin misiniz?',
                'save_success' => 'Grup başarıyla kaydedildi!',
                'save_error' => 'Kayıt sırasında hata oluştu.',
                'copy_success' => 'Shortcode kopyalandı!',
                'required_field' => 'Bu alan zorunludur.',
            )
        ));
    }
    
    public function admin_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'groups';
        
        echo '<div class="wrap esistenze-quick-menu-wrap">';
        echo '<h1 class="wp-heading-inline">Quick Menu Cards</h1>';
        
        // Tab navigation
        $this->render_admin_tabs($current_tab);
        
        switch ($current_tab) {
            case 'groups':
            default:
                $this->render_groups_page();
                break;
            case 'edit':
                $this->render_edit_page();
                break;
        }
        
        echo '</div>';
    }
    
    private function render_admin_tabs($current_tab) {
        $tabs = array(
            'groups' => 'Kart Grupları',
            'edit' => isset($_GET['edit_group']) ? 'Grup Düzenle' : null
        );
        
        echo '<nav class="nav-tab-wrapper wp-clearfix">';
        foreach ($tabs as $tab_key => $tab_label) {
            if ($tab_label === null) continue;
            
            $class = ($current_tab === $tab_key) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $url = admin_url('admin.php?page=esistenze-quick-menu&tab=' . $tab_key);
            if ($tab_key === 'edit' && isset($_GET['edit_group'])) {
                $url .= '&edit_group=' . intval($_GET['edit_group']);
            }
            echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">' . esc_html($tab_label) . '</a>';
        }
        echo '</nav>';
    }
    
    private function render_groups_page() {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        if (!is_array($kartlar)) {
            $kartlar = array();
            update_option('esistenze_quick_menu_kartlari', $kartlar);
        }
        
        include $this->module_path . 'views/admin-groups.php';
    }
    
    private function render_edit_page() {
        if (!isset($_GET['edit_group'])) {
            wp_redirect(admin_url('admin.php?page=esistenze-quick-menu'));
            exit;
        }
        
        $group_id = intval($_GET['edit_group']);
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        if (!isset($kartlar[$group_id]) || !is_array($kartlar[$group_id])) {
            $kartlar[$group_id] = array();
        }
        $group_data = $kartlar[$group_id];
        
        include $this->module_path . 'views/admin-edit.php';
    }
    
    public function settings_page() {
        $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
        
        if (isset($_POST['submit'])) {
            $this->save_settings($_POST);
        }
        
        include $this->module_path . 'views/admin-settings.php';
    }
    
    public function analytics_page() {
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        include $this->module_path . 'views/admin-analytics.php';
    }
    
    private function save_settings($post_data) {
        if (!wp_verify_nonce($post_data['_wpnonce'], 'esistenze_quick_menu_settings_save')) {
            wp_die('Güvenlik doğrulaması başarısız.');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkiniz yok.');
        }
        
        $settings = $post_data['settings'] ?? array();
        $sanitized_settings = array(
            'default_button_text' => sanitize_text_field($settings['default_button_text'] ?? 'Detayları Gör'),
            'banner_button_text' => sanitize_text_field($settings['banner_button_text'] ?? 'Ürünleri İncele'),
            'enable_analytics' => !empty($settings['enable_analytics']),
            'enable_lazy_loading' => !empty($settings['enable_lazy_loading']),
            'enable_schema_markup' => !empty($settings['enable_schema_markup']),
            'mobile_columns' => intval($settings['mobile_columns'] ?? 1),
            'custom_css' => wp_unslash($settings['custom_css'] ?? ''),
        );
        
        update_option('esistenze_quick_menu_settings', $sanitized_settings);
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Ayarlar başarıyla kaydedildi!</p></div>';
        });
    }
    
    public function show_admin_notices() {
        // WordPress bağımlılık kontrolü
        if (!wp_script_is('jquery', 'enqueued')) {
            echo '<div class="notice notice-warning"><p><strong>Uyarı:</strong> jQuery yüklü değil. Bazı özellikler çalışmayabilir.</p></div>';
        }
        
        // Esistenze Kit bağımlılık kontrolü
        if (!defined('ESISTENZE_WP_KIT_URL')) {
            echo '<div class="notice notice-info"><p><strong>Bilgi:</strong> Esistenze WordPress Kit bulunamadı. Standalone mod aktif.</p></div>';
        }
        
        // Performance ipucu
        $settings = get_option('esistenze_quick_menu_settings', array());
        if (empty($settings['enable_lazy_loading'])) {
            echo '<div class="notice notice-info is-dismissible"><p><strong>Performans İpucu:</strong> Ayarlar sayfasından lazy loading\'i etkinleştirerek sayfa yükleme hızını artırabilirsiniz.</p></div>';
        }
    }
    
    // Helper methods
    public function get_total_cards_count() {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $total = 0;
        foreach ($kartlar as $group) {
            if (is_array($group)) {
                $total += count($group);
            }
        }
        return $total;
    }
    
    public function get_group_stats($group_id) {
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        
        return array(
            'views' => $analytics['group_views'][$group_id] ?? 0,
            'clicks' => $analytics['group_clicks'][$group_id] ?? 0,
            'ctr' => $this->calculate_ctr($analytics['group_views'][$group_id] ?? 0, $analytics['group_clicks'][$group_id] ?? 0)
        );
    }
    
    private function calculate_ctr($views, $clicks) {
        return $views > 0 ? round(($clicks / $views) * 100, 2) : 0;
    }
    
    public function render_analytics_table() {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        
        if (empty($kartlar)) {
            echo '<tr><td colspan="5">Henüz veri bulunmuyor.</td></tr>';
            return;
        }
        
        foreach ($kartlar as $group_id => $group_data) {
            $stats = $this->get_group_stats($group_id);
            
            echo '<tr>';
            echo '<td><strong>#' . $group_id . '</strong></td>';
            echo '<td>' . (is_array($group_data) ? count($group_data) : 0) . '</td>';
            echo '<td>' . number_format($stats['views']) . '</td>';
            echo '<td>' . number_format($stats['clicks']) . '</td>';
            echo '<td>' . $stats['ctr'] . '%</td>';
            echo '</tr>';
        }
    }
    
    public function export_groups() {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $settings = get_option('esistenze_quick_menu_settings', array());
        $analytics = get_option('esistenze_quick_menu_analytics', array());
        
        $export_data = array(
            'version' => $this->get_version(),
            'export_date' => current_time('mysql'),
            'groups' => $kartlar,
            'settings' => $settings,
            'analytics' => $analytics
        );
        
        return $export_data;
    }
    
    public function import_groups($import_data) {
        if (!is_array($import_data) || empty($import_data['groups'])) {
            return false;
        }
        
        // Güvenlik kontrolü
        foreach ($import_data['groups'] as $group) {
            if (!is_array($group)) {
                return false;
            }
            
            foreach ($group as $card) {
                if (!is_array($card)) {
                    return false;
                }
            }
        }
        
        // Verileri kaydet
        update_option('esistenze_quick_menu_kartlari', $import_data['groups']);
        
        if (!empty($import_data['settings'])) {
            update_option('esistenze_quick_menu_settings', $import_data['settings']);
        }
        
        return true;
    }
    
    public function get_shortcode_preview($group_id, $type = 'grid') {
        if ($type === 'banner') {
            return '[quick_menu_banner id="' . intval($group_id) . '"]';
        }
        return '[quick_menu_cards id="' . intval($group_id) . '"]';
    }
    
    public function validate_card_data($card_data) {
        $errors = array();
        
        if (empty($card_data['title'])) {
            $errors[] = 'Kart başlığı zorunludur.';
        }
        
        if (!empty($card_data['url']) && !filter_var($card_data['url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Geçerli bir URL giriniz.';
        }
        
        if (!empty($card_data['img']) && !filter_var($card_data['img'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Geçerli bir görsel URL\'i giriniz.';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    public function sanitize_card_data($card_data) {
        return array(
            'title' => sanitize_text_field($card_data['title'] ?? ''),
            'desc' => sanitize_textarea_field($card_data['desc'] ?? ''),
            'img' => esc_url_raw($card_data['img'] ?? ''),
            'url' => esc_url_raw($card_data['url'] ?? ''),
            'order' => intval($card_data['order'] ?? 0),
            'enabled' => !empty($card_data['enabled']),
            'created_at' => $card_data['created_at'] ?? current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
    }
    
    private function get_version() {
        return defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0';
    }
    
    // Debug functions
    public function debug_info() {
        if (!current_user_can('manage_options') || !defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        echo '<div class="notice notice-info">';
        echo '<h4>Debug Bilgileri:</h4>';
        echo '<ul>';
        echo '<li>WordPress Version: ' . get_bloginfo('version') . '</li>';
        echo '<li>PHP Version: ' . PHP_VERSION . '</li>';
        echo '<li>Module Path: ' . $this->module_path . '</li>';
        echo '<li>Module URL: ' . $this->module_url . '</li>';
        echo '<li>Total Groups: ' . count(get_option('esistenze_quick_menu_kartlari', array())) . '</li>';
        echo '<li>Total Cards: ' . $this->get_total_cards_count() . '</li>';
        echo '</ul>';
        echo '</div>';
    }
}

?>