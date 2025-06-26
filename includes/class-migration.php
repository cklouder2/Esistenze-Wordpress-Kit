<?php
/**
 * Migration handler for Esistenze WordPress Kit
 * Handles database migrations and version updates
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeWPKitMigration {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_init', array($this, 'check_version_and_migrate'));
    }
    
    /**
     * Check plugin version and run migrations if needed
     */
    public function check_version_and_migrate() {
        $current_version = get_option('esistenze_wp_kit_version', '1.0.0');
        $plugin_version = ESISTENZE_WP_KIT_VERSION;
        
        if (version_compare($current_version, $plugin_version, '<')) {
            $this->run_migrations($current_version, $plugin_version);
            update_option('esistenze_wp_kit_version', $plugin_version);
        }
    }
    
    /**
     * Run necessary migrations based on version
     */
    private function run_migrations($from_version, $to_version) {
        // Migration from 1.x to 2.0.0
        if (version_compare($from_version, '2.0.0', '<') && version_compare($to_version, '2.0.0', '>=')) {
            $this->migrate_to_v2();
        }
    }
    
    /**
     * Migration to version 2.0.0
     */
    private function migrate_to_v2() {
        // Migrate Smart Buttons settings
        $this->migrate_smart_buttons_v2();
        
        // Migrate Category Styler settings
        $this->migrate_category_styler_v2();
        
        // Migrate Custom Topbar settings
        $this->migrate_custom_topbar_v2();
        
        // Migrate Quick Menu Cards settings
        $this->migrate_quick_menu_cards_v2();
        
        // Migrate Price Modifier settings
        $this->migrate_price_modifier_v2();
        
        // Clean up old options
        $this->cleanup_old_options_v2();
        
        // Add migration notice
        $this->add_migration_notice();
    }
    
    /**
     * Migrate Smart Buttons to v2.0.0
     */
    private function migrate_smart_buttons_v2() {
        $old_buttons = get_option('smart_product_buttons', array());
        if (!empty($old_buttons)) {
            $new_buttons = array();
            foreach ($old_buttons as $button) {
                $new_buttons[] = array(
                    'id' => uniqid(),
                    'title' => $button['title'] ?? 'Untitled Button',
                    'type' => $button['type'] ?? 'phone',
                    'value' => $button['value'] ?? '',
                    'enabled' => $button['enabled'] ?? true,
                    'icon' => $button['icon'] ?? '',
                    'color' => $button['color'] ?? '#007cba',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                );
            }
            update_option('esistenze_smart_custom_buttons', $new_buttons);
        }
    }
    
    /**
     * Migrate Category Styler to v2.0.0
     */
    private function migrate_category_styler_v2() {
        $old_settings = get_option('category_styler_settings', array());
        if (!empty($old_settings)) {
            $new_settings = array(
                'enabled' => $old_settings['enabled'] ?? true,
                'grid_columns' => $old_settings['columns'] ?? 'auto',
                'image_size' => $old_settings['image_size'] ?? 'medium',
                'show_product_count' => $old_settings['show_count'] ?? true,
                'lazy_load_images' => true,
                'enable_caching' => true,
                'cache_duration' => 43200,
                'enable_analytics' => true
            );
            update_option('esistenze_category_styler_settings', $new_settings);
        }
    }
    
    /**
     * Migrate Custom Topbar to v2.0.0
     */
    private function migrate_custom_topbar_v2() {
        $old_settings = get_option('custom_topbar_settings', array());
        if (!empty($old_settings)) {
            $new_settings = array(
                'enabled' => $old_settings['enabled'] ?? false,
                'position' => $old_settings['position'] ?? 'fixed-top',
                'height' => $old_settings['height'] ?? 50,
                'z_index' => 99999,
                'show_on_home' => true,
                'show_on_pages' => true,
                'show_on_posts' => true,
                'show_on_shop' => true,
                'show_on_archive' => true,
                'background_color' => $old_settings['bg_color'] ?? '#ffffff',
                'text_color' => $old_settings['text_color'] ?? '#333333',
                'enable_analytics' => true
            );
            update_option('esistenze_topbar_settings', $new_settings);
        }
    }
    
    /**
     * Migrate Quick Menu Cards to v2.0.0
     */
    private function migrate_quick_menu_cards_v2() {
        $old_groups = get_option('qmc_groups', array());
        if (!empty($old_groups)) {
            $new_groups = array();
            foreach ($old_groups as $group) {
                $new_groups[] = array(
                    'id' => $group['id'] ?? uniqid(),
                    'name' => $group['name'] ?? 'Untitled Group',
                    'enabled' => $group['enabled'] ?? true,
                    'cards' => $group['cards'] ?? array(),
                    'settings' => array(
                        'layout' => 'grid',
                        'columns' => 3,
                        'enable_lazy_loading' => true,
                        'enable_analytics' => true
                    ),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                );
            }
            update_option('esistenze_qmc_groups', $new_groups);
        }
        
        // Migrate general settings
        $old_qmc_settings = get_option('qmc_settings', array());
        $new_qmc_settings = array(
            'default_button_text' => $old_qmc_settings['button_text'] ?? 'Detayları Gör',
            'banner_button_text' => 'Ürünleri İncele',
            'cache_duration' => 3600,
            'enable_lazy_loading' => true,
            'enable_analytics' => true,
            'custom_css' => $old_qmc_settings['custom_css'] ?? ''
        );
        update_option('esistenze_qmc_settings', $new_qmc_settings);
    }
    
    /**
     * Migrate Price Modifier to v2.0.0
     */
    private function migrate_price_modifier_v2() {
        // Price Modifier settings are already in the correct format
        // Just ensure they have the new option names
        $enabled = get_option('price_modifier_enabled');
        if ($enabled !== false) {
            update_option('esistenze_price_modifier_enabled', $enabled);
            delete_option('price_modifier_enabled');
        }
        
        $note = get_option('price_note');
        if ($note !== false) {
            update_option('esistenze_price_note', $note);
            delete_option('price_note');
        }
    }
    
    /**
     * Clean up old options from previous versions
     */
    private function cleanup_old_options_v2() {
        $old_options = array(
            'smart_product_buttons',
            'category_styler_settings',
            'custom_topbar_settings',
            'qmc_groups',
            'qmc_settings',
            'price_modifier_enabled',
            'price_note'
        );
        
        foreach ($old_options as $option) {
            delete_option($option);
        }
    }
    
    /**
     * Add migration success notice
     */
    private function add_migration_notice() {
        add_option('esistenze_wp_kit_migration_notice', array(
            'type' => 'success',
            'message' => 'Esistenze WordPress Kit has been successfully upgraded to version 2.0.0! All your settings have been migrated.',
            'dismissible' => true,
            'show_until' => time() + (7 * DAY_IN_SECONDS) // Show for 7 days
        ));
    }
    
    /**
     * Display migration notices
     */
    public static function display_migration_notices() {
        $notice = get_option('esistenze_wp_kit_migration_notice');
        if ($notice && $notice['show_until'] > time()) {
            $class = 'notice notice-' . $notice['type'];
            if ($notice['dismissible']) {
                $class .= ' is-dismissible';
            }
            
            echo '<div class="' . esc_attr($class) . '">';
            echo '<p><strong>Esistenze WordPress Kit:</strong> ' . esc_html($notice['message']) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Handle notice dismissal
     */
    public static function dismiss_migration_notice() {
        if (isset($_GET['esistenze_dismiss_notice']) && wp_verify_nonce($_GET['_wpnonce'], 'esistenze_dismiss_notice')) {
            delete_option('esistenze_wp_kit_migration_notice');
            wp_redirect(remove_query_arg(array('esistenze_dismiss_notice', '_wpnonce')));
            exit;
        }
    }
}

// Initialize migration handler
add_action('plugins_loaded', function() {
    EsistenzeWPKitMigration::getInstance();
});

// Add admin notices hook
add_action('admin_notices', array('EsistenzeWPKitMigration', 'display_migration_notices'));
add_action('admin_init', array('EsistenzeWPKitMigration', 'dismiss_migration_notice')); 