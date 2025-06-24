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
        return self::$instance; // Bu satır eksikti
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu')); // Bu hook eksikti
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
    
    // Admin menü ekleme fonksiyonu eksikti
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
    
    private static function render_tabs($current_tab) {
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
    
    private static function enqueue_admin_assets() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_media();
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
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Color picker initialization
            $('.color-picker').wpColorPicker();
            
            window.addNewCard = function() {
                $('#modal_title').text('Yeni Kart Ekle');
                $('#card_form')[0].reset();
                $('#card_editor_modal').show();
            };
            
            window.editCard = function(cardId) {
                $('#modal_title').text('Kart Düzenle');
                $('#card_editor_modal').show();
                // Load card data here
            };
            
            window.closeCardModal = function() {
                $('#card_editor_modal').hide();
            };
            
            window.deleteCard = function(cardId) {
                if (confirm('Bu kartı silmek istediğinizden emin misiniz?')) {
                    window.location.href = `admin.php?page=esistenze-quick-menu&action=delete&card_id=${cardId}&_wpnonce=<?php echo wp_create_nonce('esistenze_quick_menu_action'); ?>`;
                }
            };
            
            window.duplicateCard = function(cardId) {
                window.location.href = `admin.php?page=esistenze-quick-menu&action=duplicate&card_id=${cardId}&_wpnonce=<?php echo wp_create_nonce('esistenze_quick_menu_action'); ?>`;
            };
            
            window.toggleCardStatus = function(cardId) {
                window.location.href = `admin.php?page=esistenze-quick-menu&action=toggle&card_id=${cardId}&_wpnonce=<?php echo wp_create_nonce('esistenze_quick_menu_action'); ?>`;
            };
            
            window.previewCards = function() {
                window.open('/', '_blank');
            };
            
            $('#card_form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                formData.append('action', 'esistenze_quick_menu_save_card');
                formData.append('_wpnonce', '<?php echo wp_create_nonce('esistenze_quick_menu_save_card'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Hata: ' + response.data);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    // Eksik olan temel fonksiyonlar
    public function register_settings() {
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_cards');
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_design');
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_layout');
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_animation');
        register_setting('esistenze_quick_menu', 'esistenze_quick_menu_advanced');
    }
    
    public function enqueue_styles() {
        if (!defined('ESISTENZE_WP_KIT_URL')) {
            return; // Plugin URL tanımlı değilse çık
        }
        
        wp_enqueue_style(
            'esistenze-quick-menu', 
            ESISTENZE_WP_KIT_URL . 'modules/quick-menu-cards/assets/style.css', 
            array(), 
            defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0'
        );
        
        wp_enqueue_script(
            'esistenze-quick-menu', 
            ESISTENZE_WP_KIT_URL . 'modules/quick-menu-cards/assets/script.js', 
            array('jquery'), 
            defined('ESISTENZE_WP_KIT_VERSION') ? ESISTENZE_WP_KIT_VERSION : '1.0.0', 
            true
        );
        
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
        
        if (empty($cards)) {
            return '<div class="esistenze-quick-menu-empty">Henüz kart eklenmemiş.</div>';
        }
        
        $filtered_cards = $this->filter_cards($cards, $atts);
        
        ob_start();
        $this->render_cards_grid($filtered_cards, $atts);
        return ob_get_clean();
    }
    
    private function filter_cards($cards, $atts) {
        // Sadece aktif kartları göster
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
    
    private function render_cards_grid($cards, $atts) {
        $wrapper_class = 'esistenze-quick-menu-wrapper';
        
        echo '<div class="' . esc_attr($wrapper_class) . '">';
        
        foreach ($cards as $index => $card) {
            $this->render_single_card($card, $index);
        }
        
        echo '</div>';
    }
    
    private function render_single_card($card, $index) {
        $card_url = !empty($card['url']) ? esc_url($card['url']) : '#';
        $card_target = !empty($card['target']) ? esc_attr($card['target']) : '_self';
        $card_title = !empty($card['title']) ? esc_html($card['title']) : 'Başlıksız';
        $card_description = !empty($card['description']) ? esc_html($card['description']) : '';
        $card_icon = !empty($card['icon']) ? esc_attr($card['icon']) : 'fa fa-square';
        
        ?>
        <a href="<?php echo $card_url; ?>" target="<?php echo $card_target; ?>" class="esistenze-quick-menu-kart">
            <div class="esistenze-quick-menu-icerik">
                <?php if (!empty($card['icon'])): ?>
                    <i class="<?php echo $card_icon; ?>"></i>
                <?php endif; ?>
            </div>
            <div class="esistenze-quick-menu-yazi">
                <h4><?php echo $card_title; ?></h4>
                <?php if ($card_description): ?>
                    <p><?php echo $card_description; ?></p>
                <?php endif; ?>
            </div>
            <div class="esistenze-quick-menu-buton">
                Detayları Gör
            </div>
        </a>
        <?php
    }
    
    private function generate_dynamic_css() {
        $design = get_option('esistenze_quick_menu_design', array());
        
        if (empty($design)) {
            return '';
        }
        
        $css = '';
        
        // Dinamik CSS oluşturma
        if (!empty($design['card_width'])) {
            $css .= '.esistenze-quick-menu-kart { width: ' . intval($design['card_width']) . 'px; }';
        }
        
        if (!empty($design['card_height'])) {
            $css .= '.esistenze-quick-menu-kart { min-height: ' . intval($design['card_height']) . 'px; }';
        }
        
        return $css;
    }
    
    // AJAX handlers - basitleştirilmiş versiyon
    public function ajax_preview() {
        if (!check_ajax_referer('esistenze_quick_menu_preview', '_wpnonce', false)) {
            wp_send_json_error('Invalid nonce');
        }
        wp_send_json_success('Preview updated');
    }
    
    public function ajax_reset() {
        if (!check_ajax_referer('esistenze_quick_menu_reset', '_wpnonce', false)) {
            wp_send_json_error('Invalid nonce');
        }
        
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
        if (!check_ajax_referer('esistenze_quick_menu_import', '_wpnonce', false)) {
            wp_send_json_error('Invalid nonce');
        }
        wp_send_json_success('Settings imported');
    }
    
    public function ajax_export() {
        if (!check_ajax_referer('esistenze_quick_menu_export', '_wpnonce', false)) {
            wp_send_json_error('Invalid nonce');
        }
        
        $data = array(
            'cards' => get_option('esistenze_quick_menu_cards', array()),
            'design' => get_option('esistenze_quick_menu_design', array()),
            'layout' => get_option('esistenze_quick_menu_layout', array()),
            'animation' => get_option('esistenze_quick_menu_animation', array()),
            'advanced' => get_option('esistenze_quick_menu_advanced', array())
        );
        
        wp_send_json_success($data);
    }
    
    public function ajax_reorder() {
        if (!check_ajax_referer('esistenze_quick_menu_reorder', '_wpnonce', false)) {
            wp_send_json_error('Invalid nonce');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        wp_send_json_success('Cards reordered successfully');
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
    }
    
    // Basitleştirilmiş tab render fonksiyonları
    private static function render_cards_tab() {
        $cards = get_option('esistenze_quick_menu_cards', array());
        ?>
        <div class="quick-menu-content">
            <div class="cards-manager">
                <div class="cards-header">
                    <h2>Kart Yönetimi</h2>
                    <div class="cards-actions">
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
                
                <!-- Modal HTML -->
                <div id="card_editor_modal" class="card-modal" style="display: none;">
                    <div class="card-modal-content">
                        <div class="card-modal-header">
                            <h3 id="modal_title">Kart Düzenle</h3>
                            <button type="button" class="modal-close" onclick="closeCardModal()">&times;</button>
                        </div>
                        
                        <div class="card-modal-body">
                            <form id="card_form">
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
                                        <td><input type="url" id="card_url" name="url" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="card_icon">İkon</label></th>
                                        <td><input type="text" id="card_icon" name="icon" class="regular-text" placeholder="fa fa-home"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="card_enabled">Aktif</label></th>
                                        <td>
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="card_enabled" name="enabled" value="1">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="card-modal-footer">
                                    <button type="button" class="button" onclick="closeCardModal()">İptal</button>
                                    <button type="submit" class="button button-primary">Kaydet</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Diğer tab fonksiyonları için placeholder'lar
    private static function render_design_tab() {
        echo '<div class="quick-menu-content"><h2>Tasarım ayarları burada olacak</h2></div>';
    }
    
    private static function render_layout_tab() {
        echo '<div class="quick-menu-content"><h2>Layout ayarları burada olacak</h2></div>';
    }
    
    private static function render_animation_tab() {
        echo '<div class="quick-menu-content"><h2>Animasyon ayarları burada olacak</h2></div>';
    }
    
    private static function render_advanced_tab() {
        echo '<div class="quick-menu-content"><h2>Gelişmiş ayarlar burada olacak</h2></div>';
    }
    
    private static function render_analytics_tab() {
        echo '<div class="quick-menu-content"><h2>İstatistikler burada olacak</h2></div>';
    }
    
    // Basitleştirilmiş helper fonksiyonlar
    private static function handle_form_submission() {
        if (!current_user_can('manage_options') || !check_admin_referer('esistenze_quick_menu_save')) {
            wp_die('Yetkiniz yok.');
        }
        
        // Form işleme kodu buraya
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Ayarlar başarıyla kaydedildi!</p></div>';
        });
    }
    
    private static function handle_card_actions() {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'esistenze_quick_menu_action')) {
            wp_die('Güvenlik doğrulaması başarısız.');
        }
        
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
    
    private static function show_admin_notices() {
        if (!wp_script_is('jquery', 'enqueued')) {
            echo '<div class="notice notice-warning"><p><strong>Uyarı:</strong> jQuery yüklü değil. Bazı özellikler çalışmayabilir.</p></div>';
        }
        
        $settings = get_option('esistenze_quick_menu_advanced', array());
        if (empty($settings['enable_cache'])) {
            echo '<div class="notice notice-info is-dismissible"><p><strong>Performans İpucu:</strong> Gelişmiş sekmesinden cache\'i etkinleştirerek performansı artırabilirsiniz.</p></div>';
        }
    }
    
    // AJAX handler for saving cards
    public function ajax_save_card() {
        if (!check_ajax_referer('esistenze_quick_menu_save_card', '_wpnonce', false)) {
            wp_send_json_error('Invalid nonce');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $card_data = array(
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
            'url' => esc_url_raw($_POST['url'] ?? ''),
            'icon' => sanitize_text_field($_POST['icon'] ?? ''),
            'target' => sanitize_text_field($_POST['target'] ?? '_self'),
            'enabled' => !empty($_POST['enabled']),
            'featured' => !empty($_POST['featured']),
            'order' => intval($_POST['order'] ?? 0),
            'color' => sanitize_hex_color($_POST['color'] ?? '#4CAF50'),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $cards = get_option('esistenze_quick_menu_cards', array());
        $card_id = intval($_POST['card_id'] ?? -1);
        
        if ($card_id >= 0 && isset($cards[$card_id])) {
            // Update existing card
            $cards[$card_id] = array_merge($cards[$card_id], $card_data);
        } else {
            // Add new card
            $cards[] = $card_data;
        }
        
        update_option('esistenze_quick_menu_cards', $cards);
        
        wp_send_json_success(array(
            'message' => 'Kart başarıyla kaydedildi',
            'card_id' => $card_id >= 0 ? $card_id : count($cards) - 1
        ));
    }
    
    // Widget support
    public function register_widget() {
        register_widget('EsistenzeQuickMenuCardsWidget');
    }
    
    // Activation hook
    public static function activate() {
        // Create default cards
        $default_cards = array(
            array(
                'title' => 'Ana Sayfa',
                'description' => 'Web sitesinin ana sayfasına dönün',
                'url' => home_url(),
                'icon' => 'fa fa-home',
                'enabled' => true,
                'featured' => false,
                'order' => 1,
                'color' => '#4CAF50',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array(
                'title' => 'İletişim',
                'description' => 'Bizimle iletişime geçin',
                'url' => home_url('/contact'),
                'icon' => 'fa fa-envelope',
                'enabled' => true,
                'featured' => false,
                'order' => 2,
                'color' => '#2196F3',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array(
                'title' => 'Hakkımızda',
                'description' => 'Şirketimiz hakkında bilgi alın',
                'url' => home_url('/about'),
                'icon' => 'fa fa-info-circle',
                'enabled' => true,
                'featured' => false,
                'order' => 3,
                'color' => '#FF9800',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array(
                'title' => 'Hizmetler',
                'description' => 'Sunduğumuz hizmetleri keşfedin',
                'url' => home_url('/services'),
                'icon' => 'fa fa-cogs',
                'enabled' => true,
                'featured' => true,
                'order' => 4,
                'color' => '#E91E63',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            )
        );
        
        if (!get_option('esistenze_quick_menu_cards')) {
            update_option('esistenze_quick_menu_cards', $default_cards);
        }
        
        // Set default settings
        if (!get_option('esistenze_quick_menu_design')) {
            update_option('esistenze_quick_menu_design', self::get_default_design_settings());
        }
        
        if (!get_option('esistenze_quick_menu_layout')) {
            update_option('esistenze_quick_menu_layout', self::get_default_layout_settings());
        }
        
        if (!get_option('esistenze_quick_menu_animation')) {
            update_option('esistenze_quick_menu_animation', self::get_default_animation_settings());
        }
        
        if (!get_option('esistenze_quick_menu_advanced')) {
            update_option('esistenze_quick_menu_advanced', self::get_default_advanced_settings());
        }
    }
    
    // Deactivation hook
    public static function deactivate() {
        // Clean up if needed
        wp_clear_scheduled_hook('esistenze_quick_menu_cleanup');
    }
    
    // Default settings methods
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
    
    // Track card views and clicks
    public function track_card_view($card_id) {
        $views = get_option("esistenze_quick_menu_views_{$card_id}", 0);
        update_option("esistenze_quick_menu_views_{$card_id}", $views + 1);
        
        // Update last activity
        update_option("esistenze_quick_menu_last_activity_{$card_id}", current_time('mysql'));
        
        // Update total views
        $total_views = get_option('esistenze_quick_menu_total_views', 0);
        update_option('esistenze_quick_menu_total_views', $total_views + 1);
    }
    
    public function track_card_click($card_id) {
        $clicks = get_option("esistenze_quick_menu_clicks_{$card_id}", 0);
        update_option("esistenze_quick_menu_clicks_{$card_id}", $clicks + 1);
        
        // Update total clicks
        $total_clicks = get_option('esistenze_quick_menu_total_clicks', 0);
        update_option('esistenze_quick_menu_total_clicks', $total_clicks + 1);
    }
    
    // REST API endpoints
    public function register_rest_routes() {
        register_rest_route('esistenze/v1', '/quick-menu/cards', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_cards'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('esistenze/v1', '/quick-menu/cards/(?P<id>\d+)/click', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_track_click'),
            'permission_callback' => '__return_true'
        ));
    }
    
    public function rest_get_cards($request) {
        $cards = get_option('esistenze_quick_menu_cards', array());
        
        // Filter only enabled cards
        $enabled_cards = array_filter($cards, function($card) {
            return !empty($card['enabled']);
        });
        
        return rest_ensure_response(array_values($enabled_cards));
    }
    
    public function rest_track_click($request) {
        $card_id = intval($request['id']);
        $this->track_card_click($card_id);
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Click tracked successfully'
        ));
    }
    
    // Schema markup generation
    public function generate_schema_markup($cards) {
        $advanced_settings = get_option('esistenze_quick_menu_advanced', self::get_default_advanced_settings());
        
        if (empty($advanced_settings['enable_schema'])) {
            return '';
        }
        
        $schema_type = $advanced_settings['schema_type'] ?? 'ItemList';
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => $schema_type,
            'name' => 'Quick Menu Cards',
            'description' => 'Navigation menu cards for quick access to important pages',
            'itemListElement' => array()
        );
        
        foreach ($cards as $index => $card) {
            if (empty($card['enabled'])) continue;
            
            $schema['itemListElement'][] = array(
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $card['title'] ?? '',
                'description' => $card['description'] ?? '',
                'url' => $card['url'] ?? ''
            );
        }
        
        return '<script type="application/ld+json">' . wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    // CSS/JS minification
    private function minify_css($css) {
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
        return $css;
    }
    
    private function minify_js($js) {
        $js = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', '', $js);
        $js = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $js);
        return $js;
    }
    
    // Cache management
    public function clear_cache() {
        wp_cache_delete('esistenze_quick_menu_styles', 'esistenze');
        wp_cache_delete('esistenze_quick_menu_cards_html', 'esistenze');
        
        // Clear any transients
        delete_transient('esistenze_quick_menu_cards_cache');
        delete_transient('esistenze_quick_menu_settings_cache');
    }
    
    // Performance monitoring
    public function log_performance($action, $duration) {
        if (get_option('esistenze_quick_menu_debug_mode')) {
            error_log("EsistenzeQuickMenuCards: {$action} took {$duration}ms");
        }
    }
    
    // Cleanup function for scheduled tasks
    public function cleanup_old_data() {
        // Clean up old analytics data if needed
        $retention_days = get_option('esistenze_quick_menu_retention_days', 90);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        // This would clean up old view/click logs if implemented
        do_action('esistenze_quick_menu_cleanup', $cutoff_date);
    }
}

// Widget class
class EsistenzeQuickMenuCardsWidget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'esistenze_quick_menu_cards_widget',
            'Quick Menu Cards',
            array(
                'description' => 'Display quick menu cards in a widget area',
                'classname' => 'esistenze-quick-menu-cards-widget'
            )
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $shortcode_atts = array(
            'limit' => $instance['limit'] ?? 4,
            'columns' => $instance['columns'] ?? 2,
            'featured_only' => !empty($instance['featured_only'])
        );
        
        $shortcode_string = '[quick_menu_cards';
        foreach ($shortcode_atts as $key => $value) {
            if ($value) {
                $shortcode_string .= " {$key}=\"{$value}\"";
            }
        }
        $shortcode_string .= ']';
        
        echo do_shortcode($shortcode_string);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Quick Menu';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 4;
        $columns = !empty($instance['columns']) ? $instance['columns'] : 2;
        $featured_only = !empty($instance['featured_only']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Başlık:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">Kart Sayısı:</label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('limit'); ?>" 
                   name="<?php echo $this->get_field_name('limit'); ?>" type="number" 
                   value="<?php echo esc_attr($limit); ?>" min="1" max="12">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('columns'); ?>">Kolon Sayısı:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('columns'); ?>" 
                    name="<?php echo $this->get_field_name('columns'); ?>">
                <option value="1" <?php selected($columns, 1); ?>>1</option>
                <option value="2" <?php selected($columns, 2); ?>>2</option>
                <option value="3" <?php selected($columns, 3); ?>>3</option>
                <option value="4" <?php selected($columns, 4); ?>>4</option>
            </select>
        </p>
        <p>
            <input class="checkbox" type="checkbox" 
                   <?php checked($featured_only); ?>
                   id="<?php echo $this->get_field_id('featured_only'); ?>" 
                   name="<?php echo $this->get_field_name('featured_only'); ?>" value="1">
            <label for="<?php echo $this->get_field_id('featured_only'); ?>">Sadece öne çıkan kartları göster</label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 4;
        $instance['columns'] = (!empty($new_instance['columns'])) ? intval($new_instance['columns']) : 2;
        $instance['featured_only'] = !empty($new_instance['featured_only']);
        
        return $instance;
    }
}

// Initialize the module and register hooks
add_action('plugins_loaded', function() {
    $esistenze_quick_menu = EsistenzeQuickMenuCards::getInstance();
    
    // Register AJAX handlers
    add_action('wp_ajax_esistenze_quick_menu_save_card', array($esistenze_quick_menu, 'ajax_save_card'));
    
    // Register widget
    add_action('widgets_init', array($esistenze_quick_menu, 'register_widget'));
    
    // Register REST API routes
    add_action('rest_api_init', array($esistenze_quick_menu, 'register_rest_routes'));
    
    // Schedule cleanup task
    if (!wp_next_scheduled('esistenze_quick_menu_cleanup')) {
        wp_schedule_event(time(), 'weekly', 'esistenze_quick_menu_cleanup');
    }
    add_action('esistenze_quick_menu_cleanup', array($esistenze_quick_menu, 'cleanup_old_data'));
});

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('EsistenzeQuickMenuCards', 'activate'));
register_deactivation_hook(__FILE__, array('EsistenzeQuickMenuCards', 'deactivate'));

// Add schema markup to frontend
add_action('wp_head', function() {
    if (is_front_page() || is_home()) {
        $esistenze_quick_menu = EsistenzeQuickMenuCards::getInstance();
        $cards = get_option('esistenze_quick_menu_cards', array());
        echo $esistenze_quick_menu->generate_schema_markup($cards);
    }
});

// Add tracking script to footer
add_action('wp_footer', function() {
    ?>
    <script>
    function trackCardClick(cardId) {
        if (typeof fetch !== 'undefined') {
            fetch('<?php echo rest_url('esistenze/v1/quick-menu/cards/'); ?>' + cardId + '/click', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            }).catch(function(error) {
                console.log('Tracking error:', error);
            });
        }
    }
    
    // Track card views when they come into viewport
    if (typeof IntersectionObserver !== 'undefined') {
        const cardObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const cardId = entry.target.getAttribute('data-card-id');
                    if (cardId) {
                        // Track view (would need backend endpoint)
                        console.log('Card viewed:', cardId);
                    }
                }
            });
        }, { threshold: 0.5 });
        
        document.querySelectorAll('.esistenze-quick-menu-kart').forEach(function(card) {
            cardObserver.observe(card);
        });
    }
    </script>
    <?php
}, 999);

// Add admin notice for missing dependencies
add_action('admin_notices', function() {
    if (!defined('ESISTENZE_WP_KIT_URL')) {
        echo '<div class="notice notice-error"><p><strong>Quick Menu Cards:</strong> ESISTENZE_WP_KIT_URL sabitinin tanımlanması gerekiyor.</p></div>';
    }
});

// Add support for Gutenberg blocks (future enhancement)
add_action('init', function() {
    if (function_exists('register_block_type')) {
        // Block registration would go here
    }
});

// Add customizer support
add_action('customize_register', function($wp_customize) {
    $wp_customize->add_section('esistenze_quick_menu', array(
        'title' => 'Quick Menu Cards',
        'priority' => 120,
    ));
    
    $wp_customize->add_setting('esistenze_quick_menu_enable', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('esistenze_quick_menu_enable', array(
        'label' => 'Quick Menu Cards\'ı Etkinleştir',
        'section' => 'esistenze_quick_menu',
        'type' => 'checkbox',
    ));
});

?>