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
        
        if (!current_user_can(esistenze_qmc_capability()) || empty($_POST['order'])) {
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
        if (!current_user_can(esistenze_qmc_capability()) || !check_admin_referer('esistenze_smart_button_save')) {
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
        if (!current_user_can(esistenze_qmc_capability()) || !isset($_GET['id']) || !check_admin_referer('esistenze_smart_button_delete_' . $_GET['id'])) {
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
        if (!current_user_can(esistenze_qmc_capability()) || !isset($_GET['id']) || !check_admin_referer('esistenze_smart_button_duplicate')) {
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
        if (!current_user_can(esistenze_qmc_capability()) || !check_admin_referer('esistenze_smart_buttons_bulk')) {
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
        if (!current_user_can(esistenze_qmc_capability()) || !check_admin_referer('esistenze_smart_buttons_import')) {
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
        if (!current_user_can(esistenze_qmc_capability()) || !check_admin_referer('esistenze_smart_buttons_export')) {
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
                    default:
                        valueField.attr('placeholder', '');
                        valueDesc.text('Türe göre değişir');
                        messageField.hide();
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
                const icons = [
                    'fa-phone', 'fa-envelope', 'fa-whatsapp', 'fa-comment', 
                    'fa-shopping-cart', 'fa-heart', 'fa-star', 'fa-home', 
                    'fa-user', 'fa-cog', 'fa-bell', 'fa-calendar', 
                    'fa-search', 'fa-paper-plane', 'fa-map-marker', 
                    'fa-check', 'fa-info-circle', 'fa-question-circle'
                ];
                
                const iconHtml = icons.map(icon => 
                    `<button type="button" onclick="selectIcon('${icon}')" style="margin: 5px; padding: 10px; border: 1px solid #ddd; background: white; cursor: pointer;"><i class="fa ${icon}"></i></button>`
                ).join('');
                
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
                
                $('#bulk-selected-ids').val(selected.join(','));
                $('#bulk-action-form').submit();
            };
            
            // Button preview on hover
            $(document).on('click', '.preview-btn', function() {
                const buttonId = $(this).data('id');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'esistenze_smart_button_preview',
                        _wpnonce: '<?php echo wp_create_nonce("esistenze_smart_button_preview"); ?>',
                        button_id: buttonId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Buton önizleme: ' + response.data);
                        }
                    }
                });
            });
            
            // Make buttons sortable
            if ($.fn.sortable && $('#buttons-container').length) {
                $('#buttons-container').sortable({
                    items: '.button-card',
                    handle: '.button-card-header',
                    placeholder: 'button-card-placeholder',
                    forcePlaceholderSize: true,
                    update: function(event, ui) {
                        const order = $('.button-card').map(function() {
                            return $(this).data('id');
                        }).get();
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'esistenze_smart_button_reorder',
                                _wpnonce: '<?php echo wp_create_nonce("esistenze_smart_button_reorder"); ?>',
                                order: order
                            }
                        });
                    }
                });
            }
            
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
}

// Initialize the module
EsistenzeSmartButtons::getInstance();