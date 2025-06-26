<?php
/**
 * Settings Trait
 * Common settings management functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

trait EsistenzeSettings {
    
    /**
     * Settings cache
     * @var array
     */
    private array $settingsCache = [];
    
    /**
     * Get settings with caching
     * @param string $option_name
     * @param array $defaults
     * @return array
     */
    protected function getSettings(string $option_name, array $defaults = []): array {
        if (!isset($this->settingsCache[$option_name])) {
            $this->settingsCache[$option_name] = get_option($option_name, $defaults);
        }
        return $this->settingsCache[$option_name];
    }
    
    /**
     * Update settings with cache invalidation
     * @param string $option_name
     * @param array $settings
     * @return bool
     */
    protected function updateSettings(string $option_name, array $settings): bool {
        $sanitized = $this->sanitizeSettings($settings);
        $result = update_option($option_name, $sanitized);
        
        if ($result) {
            $this->settingsCache[$option_name] = $sanitized;
        }
        
        return $result;
    }
    
    /**
     * Sanitize settings array
     * @param array $settings
     * @return array
     */
    protected function sanitizeSettings(array $settings): array {
        $sanitized = [];
        
        foreach ($settings as $key => $value) {
            $sanitized[$key] = $this->sanitizeSettingValue($key, $value);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize individual setting value
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function sanitizeSettingValue(string $key, $value) {
        // Color values
        if (str_contains($key, 'color')) {
            return sanitize_hex_color($value);
        }
        
        // Boolean values
        if (is_bool($value) || in_array($value, ['1', '0', 1, 0, true, false], true)) {
            return (bool) $value;
        }
        
        // Numeric values
        if (str_contains($key, 'size') || str_contains($key, 'width') || str_contains($key, 'height')) {
            return absint($value);
        }
        
        // Email values
        if (str_contains($key, 'email')) {
            return sanitize_email($value);
        }
        
        // URL values
        if (str_contains($key, 'url') || str_contains($key, 'link')) {
            return esc_url_raw($value);
        }
        
        // Text values
        if (str_contains($key, 'message') || str_contains($key, 'description')) {
            return sanitize_textarea_field($value);
        }
        
        // Default: text field
        return sanitize_text_field($value);
    }
    
    /**
     * Verify nonce for settings form
     * @param string $action
     * @return bool
     */
    protected function verifyNonce(string $action): bool {
        return (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], $action)) ||
               (isset($_POST['_wp_http_referer']) && check_admin_referer($action));
    }
    
    /**
     * Get default settings (to be overridden by child classes)
     * @return array
     */
    protected function getDefaultSettings(): array {
        return [];
    }
} 