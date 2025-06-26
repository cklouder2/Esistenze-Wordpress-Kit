<?php
/**
 * Admin Menu Trait
 * Common admin menu functionality for all modules
 */

if (!defined('ABSPATH')) {
    exit;
}

trait EsistenzeAdminMenu {
    
    /**
     * Get admin capability
     * @return string
     */
    protected function getAdminCapability(): string {
        return function_exists('esistenze_qmc_capability') ? esistenze_qmc_capability() : 'manage_options';
    }
    
    /**
     * Check if current user can access admin pages
     * @return bool
     */
    protected function canAccessAdmin(): bool {
        return current_user_can($this->getAdminCapability()) || current_user_can('manage_options');
    }
    
    /**
     * Display access denied message
     * @return never
     */
    protected function denyAccess(): never {
        wp_die(__('Bu sayfaya erişim yetkiniz bulunmuyor.', 'esistenze-wp-kit'));
    }
    
    /**
     * Show admin notice
     * @param string $message
     * @param string $type
     */
    protected function showAdminNotice(string $message, string $type = 'success'): void {
        echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible">';
        echo '<p>' . esc_html($message) . '</p>';
        echo '</div>';
    }
    
    /**
     * Render admin page header
     * @param string $title
     * @param array $tabs
     * @param string $current_tab
     */
    protected function renderAdminHeader(string $title, array $tabs = [], string $current_tab = ''): void {
        echo '<div class="wrap esistenze-admin-wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html($title) . '</h1>';
        echo '<hr class="wp-header-end">';
        
        if (!empty($tabs)) {
            echo '<nav class="nav-tab-wrapper wp-clearfix">';
            foreach ($tabs as $tab_key => $tab_data) {
                $class = ($current_tab === $tab_key) ? 'nav-tab nav-tab-active' : 'nav-tab';
                $icon = isset($tab_data['icon']) ? '<span class="dashicons ' . esc_attr($tab_data['icon']) . '"></span> ' : '';
                echo '<a href="' . esc_url($tab_data['url']) . '" class="' . esc_attr($class) . '">';
                echo $icon . esc_html($tab_data['label']);
                echo '</a>';
            }
            echo '</nav>';
        }
    }
    
    /**
     * Render admin page footer
     */
    protected function renderAdminFooter(): void {
        echo '</div>';
    }
} 