<?php
/**
 * Quick Menu Cards Admin Sınıfı
 * Yeniden yazılmış, basit ve çalışır versiyon
 * 
 * @package Esistenze WordPress Kit
 * @subpackage Quick Menu Cards
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeQuickMenuCardsAdmin {
    
    private $module_path;
    private $module_url;
    private $version = '2.0.0';
    
    public function __construct($module_path, $module_url) {
        $this->module_path = $module_path;
        $this->module_url = $module_url;
        
        $this->init();
    }
    
    /**
     * Admin panelini başlat
     */
    private function init() {
        // Admin hook'ları ekle
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
        
        // Debug log
        if (function_exists('qmc_log_error')) {
            qmc_log_error('QMC Admin sınıfı başlatıldı');
        }
    }
    
    /**
     * Admin menüsünü ekle
     */
    public function add_admin_menu() {
        // Kullanılacak yetki
        $capability = function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'edit_posts';
        
        // Ana sayfa - Quick Menu Cards
        add_submenu_page(
            'esistenze-wp-kit',
            'Quick Menu Cards',
            'Quick Menu Cards',
            $capability,
            'quick-menu-cards',
            array($this, 'admin_page_cards')
        );
        
        // Ayarlar sayfası
        add_submenu_page(
            'esistenze-wp-kit',
            'QMC Ayarlar',
            'QMC Ayarlar',
            $capability,
            'quick-menu-cards-settings',
            array($this, 'admin_page_settings')
        );
        
        // İstatistikler sayfası
        add_submenu_page(
            'esistenze-wp-kit',
            'QMC İstatistikler',
            'QMC İstatistikler',
            $capability,
            'quick-menu-cards-analytics',
            array($this, 'admin_page_analytics')
        );
        
        // Araçlar sayfası
        add_submenu_page(
            'esistenze-wp-kit',
            'QMC Araçlar',
            'QMC Araçlar',
            $capability,
            'quick-menu-cards-tools',
            array($this, 'admin_page_tools')
        );
    }
    
    /**
     * Admin scriptlerini yükle
     */
    public function enqueue_admin_scripts($hook) {
        // Sadece QMC sayfalarında yükle
        if (strpos($hook, 'quick-menu-cards') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'qmc-admin-css',
            $this->module_url . 'assets/admin.css',
            array(),
            $this->version
        );
        
        // JavaScript
        wp_enqueue_script(
            'qmc-admin-js',
            $this->module_url . 'assets/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            $this->version,
            true
        );
        
        // WordPress medya kütüphanesi
        wp_enqueue_media();
        
        // Localize script
        wp_localize_script('qmc-admin-js', 'qmc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('qmc_nonce'),
            'strings' => array(
                'confirm_delete' => 'Bu kartı silmek istediğinizden emin misiniz?',
                'confirm_delete_group' => 'Bu grubu ve tüm kartlarını silmek istediğinizden emin misiniz?',
                'loading' => 'Yükleniyor...',
                'error' => 'Bir hata oluştu!',
                'success' => 'İşlem başarılı!'
            )
        ));
    }
    
    /**
     * Form gönderimlerini işle
     */
    public function handle_form_submissions() {
        if (!isset($_POST['qmc_action']) || !wp_verify_nonce($_POST['qmc_nonce'], 'qmc_admin_action')) {
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_die('Yetkiniz yok!');
        }
        
        $action = sanitize_text_field($_POST['qmc_action']);
        
        switch ($action) {
            case 'save_group':
                $this->save_group();
                break;
            case 'delete_group':
                $this->delete_group();
                break;
            case 'save_settings':
                $this->save_settings();
                break;
            case 'reset_settings':
                $this->reset_settings();
                break;
        }
    }
    
    /**
     * Ana kart yönetim sayfası
     */
    public function admin_page_cards() {
        $cards = EsistenzeQuickMenuCards::get_cards();
        $current_group = isset($_GET['group']) ? sanitize_text_field($_GET['group']) : '';
        
        ?>
        <div class="wrap">
            <h1>Quick Menu Cards</h1>
            
            <?php $this->show_notices(); ?>
            
            <div class="qmc-admin-container">
                <div class="qmc-sidebar">
                    <h3>Gruplar</h3>
                    <div class="qmc-groups">
                        <?php if (empty($cards)): ?>
                            <p>Henüz grup yok.</p>
                        <?php else: ?>
                            <?php foreach ($cards as $group_id => $group_data): ?>
                                <div class="qmc-group-item <?php echo $current_group === $group_id ? 'active' : ''; ?>">
                                    <a href="<?php echo admin_url('admin.php?page=quick-menu-cards&group=' . urlencode($group_id)); ?>">
                                        <?php echo esc_html($group_data['name'] ?? $group_id); ?>
                                        <span class="count">(<?php echo count($group_data['cards'] ?? array()); ?>)</span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="button button-primary" id="qmc-new-group">
                        Yeni Grup Ekle
                    </button>
                </div>
                
                <div class="qmc-main-content">
                    <?php if ($current_group): ?>
                        <?php $this->render_group_editor($current_group, $cards[$current_group] ?? null); ?>
                    <?php else: ?>
                        <?php $this->render_dashboard($cards); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Yeni Grup Modal -->
        <div id="qmc-new-group-modal" class="qmc-modal" style="display: none;">
            <div class="qmc-modal-content">
                <span class="qmc-modal-close">&times;</span>
                <h2>Yeni Grup Oluştur</h2>
                <form method="post">
                    <?php wp_nonce_field('qmc_admin_action', 'qmc_nonce'); ?>
                    <input type="hidden" name="qmc_action" value="save_group">
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="group_id">Grup ID</label></th>
                            <td><input type="text" id="group_id" name="group_id" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="group_name">Grup Adı</label></th>
                            <td><input type="text" id="group_name" name="group_name" class="regular-text" required></td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Grup Oluştur">
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Dashboard görünümü
     */
    private function render_dashboard($cards) {
        ?>
        <div class="qmc-dashboard">
            <h2>Quick Menu Cards Dashboard</h2>
            
            <div class="qmc-stats">
                <div class="qmc-stat-box">
                    <h3><?php echo count($cards); ?></h3>
                    <p>Toplam Grup</p>
                </div>
                
                <div class="qmc-stat-box">
                    <h3><?php 
                        $total_cards = 0;
                        foreach ($cards as $group) {
                            $total_cards += count($group['cards'] ?? array());
                        }
                        echo $total_cards;
                    ?></h3>
                    <p>Toplam Kart</p>
                </div>
            </div>
            
            <div class="qmc-quick-actions">
                <h3>Hızlı İşlemler</h3>
                <p>
                    <button type="button" class="button button-primary" id="qmc-new-group">
                        Yeni Grup Oluştur
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=quick-menu-cards-settings'); ?>" class="button">
                        Ayarlara Git
                    </a>
                </p>
            </div>
            
            <?php if (!empty($cards)): ?>
                <div class="qmc-recent-groups">
                    <h3>Son Gruplar</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Grup Adı</th>
                                <th>Kart Sayısı</th>
                                <th>Shortcode</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cards as $group_id => $group_data): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($group_data['name'] ?? $group_id); ?></strong>
                                    </td>
                                    <td><?php echo count($group_data['cards'] ?? array()); ?></td>
                                    <td>
                                        <code>[quick_menu_cards group="<?php echo esc_attr($group_id); ?>"]</code>
                                        <button type="button" class="button-link qmc-copy-shortcode" data-shortcode='[quick_menu_cards group="<?php echo esc_attr($group_id); ?>"]'>
                                            Kopyala
                                        </button>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=quick-menu-cards&group=' . urlencode($group_id)); ?>" class="button button-small">
                                            Düzenle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Grup editörü
     */
    private function render_group_editor($group_id, $group_data) {
        if (!$group_data) {
            echo '<div class="notice notice-error"><p>Grup bulunamadı!</p></div>';
            return;
        }
        
        $cards = $group_data['cards'] ?? array();
        ?>
        <div class="qmc-group-editor">
            <div class="qmc-group-header">
                <h2><?php echo esc_html($group_data['name'] ?? $group_id); ?></h2>
                <div class="qmc-group-actions">
                    <button type="button" class="button button-primary" id="qmc-add-card">
                        Yeni Kart Ekle
                    </button>
                    <button type="button" class="button button-secondary qmc-copy-shortcode" data-shortcode='[quick_menu_cards group="<?php echo esc_attr($group_id); ?>"]'>
                        Shortcode Kopyala
                    </button>
                </div>
            </div>
            
            <div class="qmc-cards-container">
                <?php if (empty($cards)): ?>
                    <div class="qmc-no-cards">
                        <p>Bu grupta henüz kart yok.</p>
                        <button type="button" class="button button-primary" id="qmc-add-first-card">
                            İlk Kartı Ekle
                        </button>
                    </div>
                <?php else: ?>
                    <div id="qmc-cards-list" class="qmc-cards-list">
                        <?php foreach ($cards as $index => $card): ?>
                            <?php $this->render_card_item($group_id, $index, $card); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Kart Editör Modal -->
        <div id="qmc-card-modal" class="qmc-modal" style="display: none;">
            <div class="qmc-modal-content qmc-modal-large">
                <span class="qmc-modal-close">&times;</span>
                <h2 id="qmc-modal-title">Kart Düzenle</h2>
                <form id="qmc-card-form">
                    <input type="hidden" id="card-group-id" value="<?php echo esc_attr($group_id); ?>">
                    <input type="hidden" id="card-index" value="">
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="card-title">Başlık</label></th>
                            <td><input type="text" id="card-title" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="card-description">Açıklama</label></th>
                            <td><textarea id="card-description" rows="3" class="large-text"></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="card-url">URL</label></th>
                            <td><input type="url" id="card-url" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="card-image">Resim</label></th>
                            <td>
                                <input type="hidden" id="card-image-id">
                                <input type="text" id="card-image-url" class="regular-text" readonly>
                                <button type="button" id="card-select-image" class="button">Resim Seç</button>
                                <button type="button" id="card-remove-image" class="button">Kaldır</button>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="card-button-text">Buton Metni</label></th>
                            <td><input type="text" id="card-button-text" class="regular-text"></td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="button" id="qmc-save-card" class="button button-primary">Kaydet</button>
                        <button type="button" class="button qmc-modal-close">İptal</button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Kart öğesi render et
     */
    private function render_card_item($group_id, $index, $card) {
        ?>
        <div class="qmc-card-item" data-index="<?php echo $index; ?>">
            <div class="qmc-card-preview">
                <?php if (!empty($card['image'])): ?>
                    <img src="<?php echo esc_url($card['image']); ?>" alt="<?php echo esc_attr($card['title'] ?? ''); ?>">
                <?php else: ?>
                    <div class="qmc-card-no-image">Resim Yok</div>
                <?php endif; ?>
            </div>
            
            <div class="qmc-card-info">
                <h4><?php echo esc_html($card['title'] ?? 'Başlıksız'); ?></h4>
                <p><?php echo esc_html(wp_trim_words($card['description'] ?? '', 10)); ?></p>
            </div>
            
            <div class="qmc-card-actions">
                <button type="button" class="button button-small qmc-edit-card" data-index="<?php echo $index; ?>">
                    Düzenle
                </button>
                <button type="button" class="button button-small qmc-delete-card" data-index="<?php echo $index; ?>">
                    Sil
                </button>
                <span class="qmc-drag-handle">⋮⋮</span>
            </div>
        </div>
        <?php
    }
    
    /**
     * Ayarlar sayfası
     */
    public function admin_page_settings() {
        $settings = EsistenzeQuickMenuCards::get_settings();
        
        ?>
        <div class="wrap">
            <h1>Quick Menu Cards Ayarları</h1>
            
            <?php $this->show_notices(); ?>
            
            <form method="post">
                <?php wp_nonce_field('qmc_admin_action', 'qmc_nonce'); ?>
                <input type="hidden" name="qmc_action" value="save_settings">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Varsayılan Buton Metni</th>
                        <td>
                            <input type="text" name="default_button_text" value="<?php echo esc_attr($settings['default_button_text']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Banner Buton Metni</th>
                        <td>
                            <input type="text" name="banner_button_text" value="<?php echo esc_attr($settings['banner_button_text']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Grid Sütun Sayısı</th>
                        <td>
                            <select name="grid_columns">
                                <option value="1" <?php selected($settings['grid_columns'], 1); ?>>1</option>
                                <option value="2" <?php selected($settings['grid_columns'], 2); ?>>2</option>
                                <option value="3" <?php selected($settings['grid_columns'], 3); ?>>3</option>
                                <option value="4" <?php selected($settings['grid_columns'], 4); ?>>4</option>
                                <option value="5" <?php selected($settings['grid_columns'], 5); ?>>5</option>
                                <option value="6" <?php selected($settings['grid_columns'], 6); ?>>6</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Animasyonları Etkinleştir</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_animations" value="1" <?php checked($settings['enable_animations']); ?>>
                                Hover ve geçiş animasyonlarını etkinleştir
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Açıklamaları Göster</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_descriptions" value="1" <?php checked($settings['show_descriptions']); ?>>
                                Kart açıklamalarını göster
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Resimleri Göster</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_images" value="1" <?php checked($settings['show_images']); ?>>
                                Kart resimlerini göster
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Özel CSS</th>
                        <td>
                            <textarea name="custom_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                            <p class="description">Kartlar için özel CSS kodları</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Ayarları Kaydet'); ?>
                
                <p>
                    <button type="submit" name="qmc_action" value="reset_settings" class="button button-secondary" onclick="return confirm('Tüm ayarları sıfırlamak istediğinizden emin misiniz?')">
                        Ayarları Sıfırla
                    </button>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * İstatistikler sayfası
     */
    public function admin_page_analytics() {
        ?>
        <div class="wrap">
            <h1>Quick Menu Cards İstatistikleri</h1>
            
            <div class="qmc-analytics">
                <p>İstatistik özellikleri yakında eklenecek...</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Araçlar sayfası
     */
    public function admin_page_tools() {
        ?>
        <div class="wrap">
            <h1>Quick Menu Cards Araçları</h1>
            
            <div class="qmc-tools">
                <p>Araç özellikleri yakında eklenecek...</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Bildirimleri göster
     */
    private function show_notices() {
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            $messages = array(
                'group_saved' => 'Grup başarıyla kaydedildi!',
                'group_deleted' => 'Grup başarıyla silindi!',
                'settings_saved' => 'Ayarlar başarıyla kaydedildi!',
                'settings_reset' => 'Ayarlar sıfırlandı!',
                'error' => 'Bir hata oluştu!'
            );
            
            if (isset($messages[$message])) {
                $class = $message === 'error' ? 'notice-error' : 'notice-success';
                echo '<div class="notice ' . $class . ' is-dismissible"><p>' . esc_html($messages[$message]) . '</p></div>';
            }
        }
    }
    
    /**
     * Grup kaydet
     */
    private function save_group() {
        $group_id = sanitize_text_field($_POST['group_id']);
        $group_name = sanitize_text_field($_POST['group_name']);
        
        if (empty($group_id) || empty($group_name)) {
            wp_redirect(admin_url('admin.php?page=quick-menu-cards&message=error'));
            exit;
        }
        
        $cards = EsistenzeQuickMenuCards::get_cards();
        $cards[$group_id] = array(
            'name' => $group_name,
            'cards' => array()
        );
        
        EsistenzeQuickMenuCards::save_cards($cards);
        
        wp_redirect(admin_url('admin.php?page=quick-menu-cards&group=' . urlencode($group_id) . '&message=group_saved'));
        exit;
    }
    
    /**
     * Grup sil
     */
    private function delete_group() {
        $group_id = sanitize_text_field($_POST['group_id']);
        
        if (empty($group_id)) {
            wp_redirect(admin_url('admin.php?page=quick-menu-cards&message=error'));
            exit;
        }
        
        $cards = EsistenzeQuickMenuCards::get_cards();
        unset($cards[$group_id]);
        
        EsistenzeQuickMenuCards::save_cards($cards);
        
        wp_redirect(admin_url('admin.php?page=quick-menu-cards&message=group_deleted'));
        exit;
    }
    
    /**
     * Ayarları kaydet
     */
    private function save_settings() {
        $settings = array(
            'default_button_text' => sanitize_text_field($_POST['default_button_text']),
            'banner_button_text' => sanitize_text_field($_POST['banner_button_text']),
            'grid_columns' => intval($_POST['grid_columns']),
            'enable_animations' => isset($_POST['enable_animations']),
            'show_descriptions' => isset($_POST['show_descriptions']),
            'show_images' => isset($_POST['show_images']),
            'custom_css' => sanitize_textarea_field($_POST['custom_css'])
        );
        
        EsistenzeQuickMenuCards::save_settings($settings);
        
        wp_redirect(admin_url('admin.php?page=quick-menu-cards-settings&message=settings_saved'));
        exit;
    }
    
    /**
     * Ayarları sıfırla
     */
    private function reset_settings() {
        $default_settings = EsistenzeQuickMenuCards::get_default_settings();
        EsistenzeQuickMenuCards::save_settings($default_settings);
        
        wp_redirect(admin_url('admin.php?page=quick-menu-cards-settings&message=settings_reset'));
        exit;
    }
    
    /**
     * Admin menü metodu (eski uyumluluk için)
     */
    public function admin_menu() {
        // Bu metod artık add_admin_menu() tarafından yapılıyor
        $this->add_admin_menu();
    }
    
    /**
     * Admin sayfa metodu (eski uyumluluk için)
     */
    public function admin_page() {
        // Bu metod artık admin_page_cards() tarafından yapılıyor
        $this->admin_page_cards();
    }
    
    /**
     * Ayarlar sayfası metodu (eski uyumluluk için)
     */
    public function settings_page() {
        // Bu metod artık admin_page_settings() tarafından yapılıyor
        $this->admin_page_settings();
    }
}
?>