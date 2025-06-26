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
    private $page_hooks = array();
    
    /**
     * Constructor
     * @param string $module_path
     * @param string $module_url
     */
    public function __construct(string $module_path, string $module_url) {
        $this->module_path = $module_path;
        $this->module_url = $module_url;
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Menü ekleme - priority 25 ile geç çalıştır
        add_action('admin_menu', array($this, 'admin_menu'), 25);
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
        
        // Form işleme
        add_action('admin_post_esistenze_save_group', array($this, 'handle_save_group'));
        add_action('admin_post_esistenze_delete_group', array($this, 'handle_delete_group'));
        
        // AJAX hooks
        add_action('wp_ajax_esistenze_live_preview', array($this, 'ajax_live_preview'));
        add_action('wp_ajax_esistenze_get_media', array($this, 'ajax_get_media'));
        
        // Dashboard widget
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
    }
    
    /**
     * Register admin menu - submenu olarak
     */
    public function admin_menu(): void {
        // Ana eklenti menüsünün var olup olmadığını kontrol et
        global $submenu;
        
        // Ana menü yoksa kendi menümüzü oluştur
        if (!menu_page_url('esistenze-wp-kit', false)) {
            add_menu_page(
                'Esistenze Kit',
                'Esistenze Kit',
                'read',
                'esistenze-wp-kit',
                '__return_null',
                'dashicons-admin-tools',
                30
            );
        }
        
        // Quick Menu Cards ana sayfası
        $this->page_hooks[] = add_submenu_page(
            'esistenze-wp-kit',
            'Quick Menu Cards',
            'Quick Menu Cards',
            'read',
            'esistenze-quick-menu',
            array($this, 'admin_page')
        );
        
        // Ayarlar sayfası
        $this->page_hooks[] = add_submenu_page(
            'esistenze-wp-kit',
            'QMC Ayarlar',
            'QMC Ayarlar',
            'read',
            'esistenze-quick-menu-settings',
            array($this, 'settings_page')
        );
        
        // İstatistikler sayfası
        $this->page_hooks[] = add_submenu_page(
            'esistenze-wp-kit',
            'QMC İstatistikler',
            'QMC İstatistikler',
            'read',
            'esistenze-quick-menu-analytics',
            array($this, 'analytics_page')
        );
        
        // Araçlar sayfası
        $this->page_hooks[] = add_submenu_page(
            'esistenze-wp-kit',
            'QMC Araçlar',
            'QMC Araçlar',
            'read',
            'esistenze-quick-menu-tools',
            array($this, 'tools_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings(): void {
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
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts(string $hook): void {
        // Sadece kendi sayfalarımızda script yükle
        if (!in_array($hook, $this->page_hooks)) {
            return;
        }
        
        // WordPress medya kütüphanesi
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Admin CSS
        wp_enqueue_style(
            'esistenze-quick-menu-admin',
            $this->module_url . 'assets/admin.css',
            array('wp-color-picker', 'dashicons'),
            '2.0.0'
        );
        
        // Admin JS
        wp_enqueue_script(
            'esistenze-quick-menu-admin',
            $this->module_url . 'assets/admin.js',
            array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
            '2.0.0',
            true
        );
        
        // Localize script
        wp_localize_script('esistenze-quick-menu-admin', 'esistenzeAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('esistenze_quick_menu_nonce'),
            'post_url' => admin_url('admin-post.php'),
            'strings' => array(
                'delete_confirm' => 'Bu grubu silmek istediğinizden emin misiniz?',
                'save_success' => 'Başarıyla kaydedildi!',
                'save_error' => 'Kayıt sırasında hata oluştu.',
                'loading' => 'Yükleniyor...'
            )
        ));
    }
    
    /**
     * Ana admin sayfası
     */
    public function admin_page(): void {
        // Yetki kontrolü - basit ve güvenli
        if (!current_user_can('read')) {
            wp_die(__('Bu sayfaya erişim yetkiniz yok.'));
        }
        
        // Mevcut tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'groups';
        
        echo '<div class="wrap">';
        echo '<h1>Quick Menu Cards</h1>';
        
        // Debug bilgisi - geçici
        $current_user = wp_get_current_user();
        echo '<div class="notice notice-info">';
        echo '<p><strong>Debug:</strong> Kullanıcı: ' . esc_html($current_user->user_login) . ' | ';
        echo 'Roller: ' . esc_html(implode(', ', $current_user->roles)) . ' | ';
        echo 'read yetkisi: ' . (current_user_can('read') ? 'VAR' : 'YOK') . '</p>';
        echo '</div>';
        
        // Tab menüsü
        $this->render_admin_tabs($current_tab);
        
        // Tab içeriği
        switch ($current_tab) {
            case 'edit':
                $this->render_edit_page();
                break;
            case 'groups':
            default:
                $this->render_groups_page();
                break;
        }
        
        echo '</div>';
    }
    
    /**
     * Tab menüsü
     */
    private function render_admin_tabs($current_tab) {
        $tabs = array(
            'groups' => 'Gruplar',
            'edit' => 'Düzenle'
        );
        
        echo '<nav class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current_tab) ? 'nav-tab-active' : '';
            echo '<a href="?page=esistenze-quick-menu&tab=' . $tab . '" class="nav-tab ' . $class . '">' . $name . '</a>';
        }
        echo '</nav>';
    }
    
    /**
     * Gruplar sayfası
     */
    private function render_groups_page() {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions">';
        echo '<a href="?page=esistenze-quick-menu&tab=edit" class="button button-primary">Yeni Grup Ekle</a>';
        echo '</div>';
        echo '</div>';
        
        if (empty($kartlar)) {
            echo '<div class="notice notice-info"><p>Henüz hiç grup oluşturulmamış. <a href="?page=esistenze-quick-menu&tab=edit">İlk grubunuzu oluşturun</a>.</p></div>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Grup Adı</th><th>Kart Sayısı</th><th>Shortcode</th><th>İşlemler</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($kartlar as $group_id => $group) {
            $card_count = is_array($group['cards']) ? count($group['cards']) : 0;
            echo '<tr>';
            echo '<td><strong>' . esc_html($group['name']) . '</strong></td>';
            echo '<td>' . $card_count . ' kart</td>';
            echo '<td><code>[esistenze_quick_menu id="' . $group_id . '"]</code></td>';
            echo '<td>';
            echo '<a href="?page=esistenze-quick-menu&tab=edit&group_id=' . $group_id . '" class="button button-small">Düzenle</a> ';
            echo '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=esistenze_delete_group&group_id=' . $group_id), 'delete_group_' . $group_id) . '" class="button button-small button-link-delete" onclick="return confirm(\'Silmek istediğinizden emin misiniz?\')">Sil</a>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Düzenleme sayfası
     */
    private function render_edit_page() {
        $group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $group = $group_id && isset($kartlar[$group_id]) ? $kartlar[$group_id] : array(
            'name' => '',
            'cards' => array()
        );
        
        echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
        wp_nonce_field('save_group_' . $group_id, 'esistenze_nonce');
        echo '<input type="hidden" name="action" value="esistenze_save_group">';
        echo '<input type="hidden" name="group_id" value="' . $group_id . '">';
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="group_name">Grup Adı</label></th>';
        echo '<td><input type="text" id="group_name" name="group_name" value="' . esc_attr($group['name']) . '" class="regular-text" required></td>';
        echo '</tr>';
        echo '</table>';
        
        echo '<h3>Kartlar</h3>';
        echo '<div id="cards-container">';
        
        if (!empty($group['cards'])) {
            foreach ($group['cards'] as $index => $card) {
                $this->render_card_editor($index, $card);
            }
        } else {
            $this->render_card_editor(0, array());
        }
        
        echo '</div>';
        
        echo '<p><button type="button" id="add-card" class="button">Yeni Kart Ekle</button></p>';
        
        echo '<p class="submit">';
        echo '<input type="submit" name="submit" class="button button-primary" value="Kaydet">';
        echo ' <a href="?page=esistenze-quick-menu" class="button">İptal</a>';
        echo '</p>';
        
        echo '</form>';
        
        // JavaScript template
        echo '<script type="text/template" id="card-template">';
        $this->render_card_editor('{{INDEX}}', array());
        echo '</script>';
    }
    
    /**
     * Kart editörü
     */
    private function render_card_editor($index, $card = array()) {
        $card = wp_parse_args($card, array(
            'title' => '',
            'description' => '',
            'image' => '',
            'link' => '',
            'button_text' => 'Detayları Gör',
            'type' => 'card'
        ));
        
        echo '<div class="card-editor" data-index="' . $index . '">';
        echo '<h4>Kart #' . ($index + 1) . ' <button type="button" class="button-link remove-card">Sil</button></h4>';
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label>Başlık</label></th>';
        echo '<td><input type="text" name="cards[' . $index . '][title]" value="' . esc_attr($card['title']) . '" class="regular-text"></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label>Açıklama</label></th>';
        echo '<td><textarea name="cards[' . $index . '][description]" rows="3" class="large-text">' . esc_textarea($card['description']) . '</textarea></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label>Görsel</label></th>';
        echo '<td>';
        echo '<input type="url" name="cards[' . $index . '][image]" value="' . esc_url($card['image']) . '" class="regular-text image-url">';
        echo ' <button type="button" class="button select-image">Görsel Seç</button>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label>Link</label></th>';
        echo '<td><input type="url" name="cards[' . $index . '][link]" value="' . esc_url($card['link']) . '" class="regular-text"></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label>Buton Metni</label></th>';
        echo '<td><input type="text" name="cards[' . $index . '][button_text]" value="' . esc_attr($card['button_text']) . '" class="regular-text"></td>';
        echo '</tr>';
        echo '</table>';
        
        echo '</div>';
    }
    
    /**
     * Ayarlar sayfası
     */
    public function settings_page(): void {
        if (!current_user_can('read')) {
            wp_die(__('Bu sayfaya erişim yetkiniz yok.'));
        }
        
        echo '<div class="wrap">';
        echo '<h1>QMC Ayarlar</h1>';
        echo '<p>Ayarlar sayfası yakında eklenecek.</p>';
        echo '</div>';
    }
    
    /**
     * İstatistikler sayfası
     */
    public function analytics_page(): void {
        if (!current_user_can('read')) {
            wp_die(__('Bu sayfaya erişim yetkiniz yok.'));
        }
        
        echo '<div class="wrap">';
        echo '<h1>QMC İstatistikler</h1>';
        echo '<p>İstatistikler sayfası yakında eklenecek.</p>';
        echo '</div>';
    }
    
    /**
     * Araçlar sayfası
     */
    public function tools_page(): void {
        if (!current_user_can('read')) {
            wp_die(__('Bu sayfaya erişim yetkiniz yok.'));
        }
        
        echo '<div class="wrap">';
        echo '<h1>QMC Araçlar</h1>';
        echo '<p>Araçlar sayfası yakında eklenecek.</p>';
        echo '</div>';
    }
    
    /**
     * Grup kaydetme işlemi
     */
    public function handle_save_group(): void {
        $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
        
        // Nonce kontrolü
        if (!wp_verify_nonce($_POST['esistenze_nonce'], 'save_group_' . $group_id)) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        // Yetki kontrolü
        if (!current_user_can('read')) {
            wp_die('Bu işlemi yapma yetkiniz yok.');
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        // Yeni grup ID'si oluştur
        if ($group_id == 0) {
            $group_id = time();
        }
        
        // Grup verilerini hazırla
        $group_data = array(
            'name' => sanitize_text_field($_POST['group_name']),
            'cards' => array(),
            'created' => $group_id == time() ? current_time('mysql') : (isset($kartlar[$group_id]['created']) ? $kartlar[$group_id]['created'] : current_time('mysql')),
            'updated' => current_time('mysql')
        );
        
        // Kartları işle
        if (isset($_POST['cards']) && is_array($_POST['cards'])) {
            foreach ($_POST['cards'] as $card_data) {
                if (!empty($card_data['title'])) {
                    $group_data['cards'][] = array(
                        'title' => sanitize_text_field($card_data['title']),
                        'description' => sanitize_textarea_field($card_data['description']),
                        'image' => esc_url_raw($card_data['image']),
                        'link' => esc_url_raw($card_data['link']),
                        'button_text' => sanitize_text_field($card_data['button_text']),
                        'type' => 'card'
                    );
                }
            }
        }
        
        // Kaydet
        $kartlar[$group_id] = $group_data;
        update_option('esistenze_quick_menu_kartlari', $kartlar);
        
        // Yönlendir
        wp_redirect(admin_url('admin.php?page=esistenze-quick-menu&saved=1'));
        exit;
    }
    
    /**
     * Grup silme işlemi
     */
    public function handle_delete_group(): void {
        $group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
        
        // Nonce kontrolü
        if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_group_' . $group_id)) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        // Yetki kontrolü
        if (!current_user_can('read')) {
            wp_die('Bu işlemi yapma yetkiniz yok.');
        }
        
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        
        if (isset($kartlar[$group_id])) {
            unset($kartlar[$group_id]);
            update_option('esistenze_quick_menu_kartlari', $kartlar);
        }
        
        // Yönlendir
        wp_redirect(admin_url('admin.php?page=esistenze-quick-menu&deleted=1'));
        exit;
    }
    
    /**
     * AJAX live preview
     */
    public function ajax_live_preview(): void {
        check_ajax_referer('esistenze_quick_menu_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_die('Yetki yok.');
        }
        
        wp_send_json_success(array('preview' => 'Önizleme yakında eklenecek.'));
    }
    
    /**
     * AJAX medya seçici
     */
    public function ajax_get_media(): void {
        check_ajax_referer('esistenze_quick_menu_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_die('Yetki yok.');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $image_url = wp_get_attachment_image_url($attachment_id, 'medium');
        
        wp_send_json_success(array('url' => $image_url));
    }
    
    /**
     * Dashboard widget
     */
    public function add_dashboard_widget(): void {
        wp_add_dashboard_widget(
            'esistenze_quick_menu_stats',
            'Quick Menu Cards İstatistikleri',
            array($this, 'dashboard_widget_content')
        );
    }
    
    /**
     * Dashboard widget içeriği
     */
    public function dashboard_widget_content(): void {
        $kartlar = get_option('esistenze_quick_menu_kartlari', array());
        $total_groups = count($kartlar);
        $total_cards = 0;
        
        foreach ($kartlar as $group) {
            if (isset($group['cards']) && is_array($group['cards'])) {
                $total_cards += count($group['cards']);
            }
        }
        
        echo '<div class="activity-block">';
        echo '<h4>Özet</h4>';
        echo '<ul>';
        echo '<li><strong>' . $total_groups . '</strong> grup</li>';
        echo '<li><strong>' . $total_cards . '</strong> kart</li>';
        echo '</ul>';
        echo '<p><a href="' . admin_url('admin.php?page=esistenze-quick-menu') . '" class="button button-primary">Yönet</a></p>';
        echo '</div>';
    }
    
    /**
     * Admin notices
     */
    public function show_admin_notices(): void {
        if (isset($_GET['saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Grup başarıyla kaydedildi!</p></div>';
        }
        
        if (isset($_GET['deleted'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Grup başarıyla silindi!</p></div>';
        }
    }
    
    /**
     * Sanitize functions
     */
    public function sanitize_cards_data(array $input): array {
        return $input; // Basit sanitizasyon - gerekirse genişletilebilir
    }
    
    public function sanitize_settings_data(array $input): array {
        return $input;
    }
    
    public function sanitize_analytics_data(array $input): array {
        return $input;
    }
}

?>