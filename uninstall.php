<?php
/**
 * Uninstall script for Esistenze WordPress Kit
 * 
 * This file is executed when the plugin is deleted from WordPress admin.
 * It cleans up all plugin data from the database.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Clean up plugin options
 */
function esistenze_wp_kit_cleanup_options() {
    $options_to_delete = array(
        // Main plugin options
        'esistenze_wp_kit_version',
        'esistenze_wp_kit_settings',
        
        // Smart Product Buttons
        'esistenze_smart_custom_buttons',
        'esistenze_smart_buttons_settings',
        'esistenze_smart_buttons_analytics',
        'esistenze_smart_buttons_clicks',
        'esistenze_smart_buttons_views',
        
        // Quick Menu Cards
        'esistenze_qmc_groups',
        'esistenze_qmc_settings',
        'esistenze_qmc_analytics',
        'esistenze_qmc_cache',
        
        // Category Styler
        'esistenze_category_styler_settings',
        'esistenze_custom_category_css',
        'esistenze_category_styler_analytics',
        
        // Custom Topbar
        'esistenze_topbar_settings',
        'esistenze_topbar_analytics',
        'esistenze_topbar_impressions',
        'esistenze_topbar_clicks',
        
        // Price Modifier
        'esistenze_price_modifier_enabled',
        'esistenze_price_note',
        'esistenze_price_note_color',
        'esistenze_price_bg_color',
        'esistenze_price_border_color',
    );
    
    foreach ($options_to_delete as $option) {
        delete_option($option);
        delete_site_option($option); // For multisite
    }
}

/**
 * Clean up transients
 */
function esistenze_wp_kit_cleanup_transients() {
    global $wpdb;
    
    // Delete all transients that start with 'esistenze_'
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_esistenze_%' 
         OR option_name LIKE '_transient_timeout_esistenze_%'"
    );
    
    // For multisite
    if (is_multisite()) {
        $wpdb->query(
            "DELETE FROM {$wpdb->sitemeta} 
             WHERE meta_key LIKE '_site_transient_esistenze_%' 
             OR meta_key LIKE '_site_transient_timeout_esistenze_%'"
        );
    }
}

/**
 * Clean up user meta
 */
function esistenze_wp_kit_cleanup_user_meta() {
    global $wpdb;
    
    $user_meta_keys = array(
        'esistenze_qmc_dismissed_notices',
        'esistenze_smart_buttons_preferences',
        'esistenze_topbar_dismissed_notices',
    );
    
    foreach ($user_meta_keys as $meta_key) {
        $wpdb->delete(
            $wpdb->usermeta,
            array('meta_key' => $meta_key),
            array('%s')
        );
    }
}

/**
 * Clean up custom tables (if any were created)
 */
function esistenze_wp_kit_cleanup_tables() {
    global $wpdb;
    
    // List of custom tables (if any)
    $tables_to_drop = array(
        $wpdb->prefix . 'esistenze_analytics',
        $wpdb->prefix . 'esistenze_button_clicks',
        $wpdb->prefix . 'esistenze_qmc_analytics',
    );
    
    foreach ($tables_to_drop as $table) {
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }
}

/**
 * Clean up scheduled cron jobs
 */
function esistenze_wp_kit_cleanup_cron() {
    $cron_hooks = array(
        'esistenze_daily_cleanup',
        'esistenze_weekly_analytics',
        'esistenze_cache_cleanup',
    );
    
    foreach ($cron_hooks as $hook) {
        wp_clear_scheduled_hook($hook);
    }
}

/**
 * Clean up capabilities (if any custom ones were added)
 */
function esistenze_wp_kit_cleanup_capabilities() {
    global $wp_roles;
    
    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }
    
    $custom_capabilities = array(
        'manage_esistenze_buttons',
        'manage_esistenze_categories',
        'manage_esistenze_topbar',
    );
    
    foreach ($wp_roles->roles as $role_name => $role_info) {
        $role = get_role($role_name);
        if ($role) {
            foreach ($custom_capabilities as $cap) {
                $role->remove_cap($cap);
            }
        }
    }
}

/**
 * Main cleanup function
 */
function esistenze_wp_kit_uninstall() {
    // Only run uninstall if user has proper permissions
    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    // Check if this is a multisite installation
    if (is_multisite()) {
        // Get all sites
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // Run cleanup for each site
            esistenze_wp_kit_cleanup_options();
            esistenze_wp_kit_cleanup_transients();
            esistenze_wp_kit_cleanup_user_meta();
            esistenze_wp_kit_cleanup_tables();
            esistenze_wp_kit_cleanup_cron();
            esistenze_wp_kit_cleanup_capabilities();
            
            restore_current_blog();
        }
        
        // Clean up network-wide options
        delete_site_option('esistenze_wp_kit_network_settings');
        
    } else {
        // Single site cleanup
        esistenze_wp_kit_cleanup_options();
        esistenze_wp_kit_cleanup_transients();
        esistenze_wp_kit_cleanup_user_meta();
        esistenze_wp_kit_cleanup_tables();
        esistenze_wp_kit_cleanup_cron();
        esistenze_wp_kit_cleanup_capabilities();
    }
    
    // Clear any cached data
    wp_cache_flush();
}

// Run the uninstall
esistenze_wp_kit_uninstall(); 