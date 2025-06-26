<?php
/**
 * Base Module Class
 * Abstract base class for all Esistenze modules
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include traits
require_once ESISTENZE_WP_KIT_PATH . 'includes/traits/trait-singleton.php';
require_once ESISTENZE_WP_KIT_PATH . 'includes/traits/trait-admin-menu.php';
require_once ESISTENZE_WP_KIT_PATH . 'includes/traits/trait-settings.php';
require_once ESISTENZE_WP_KIT_PATH . 'includes/traits/trait-cache.php';

abstract class EsistenzeBaseModule {
    
    use EsistenzeSingleton;
    use EsistenzeAdminMenu;
    use EsistenzeSettings;
    use EsistenzeCache;
    
    /**
     * Module name
     * @var string
     */
    protected string $moduleName = '';
    
    /**
     * Module version
     * @var string
     */
    protected string $moduleVersion = '1.0.0';
    
    /**
     * Settings option name
     * @var string
     */
    protected string $settingsOptionName = '';
    
    /**
     * Constructor
     */
    protected function __construct() {
        $this->moduleName = $this->getModuleName();
        $this->settingsOptionName = $this->getSettingsOptionName();
        
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'loadTextdomain']);
        add_action('admin_menu', [$this, 'registerAdminMenus'], 20); // Priority 20 to ensure main menu exists
        
        // Clear cache on settings update
        $this->clearCacheOnSettingsUpdate($this->settingsOptionName);
    }
    
    /**
     * Initialize module
     * @return void
     */
    abstract public function init(): void;
    
    /**
     * Register admin menus (to be implemented by child classes if needed)
     * @return void
     */
    public function registerAdminMenus(): void {
        // Override in child classes to register admin menus
    }
    
    /**
     * Get module name
     * @return string
     */
    abstract protected function getModuleName(): string;
    
    /**
     * Get settings option name
     * @return string
     */
    abstract protected function getSettingsOptionName(): string;
    
    /**
     * Load textdomain
     * @return void
     */
    public function loadTextdomain(): void {
        load_plugin_textdomain('esistenze-wp-kit', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Register admin submenu
     * @param string $page_title
     * @param string $menu_title
     * @param string $menu_slug
     * @param callable $callback
     * @param string $icon_url
     * @param int $position
     */
    protected function registerAdminSubmenu(string $page_title, string $menu_title, string $menu_slug, callable $callback, string $icon_url = '', int $position = null): void {
        // Ensure main menu exists before adding submenu
        global $menu;
        $main_menu_exists = false;
        if (is_array($menu)) {
            foreach ($menu as $menu_item) {
                if (isset($menu_item[2]) && $menu_item[2] === 'esistenze-wp-kit') {
                    $main_menu_exists = true;
                    break;
                }
            }
        }
        
        // Continue with submenu registration even if main menu check fails
        
        add_submenu_page(
            'esistenze-wp-kit',
            $page_title,
            $menu_title,
            $this->getAdminCapability(),
            $menu_slug,
            $callback
        );
    }
    
    /**
     * Enqueue admin assets
     * @param string $hook
     */
    public function enqueueAdminAssets(string $hook): void {
        if (strpos($hook, 'esistenze') === false) {
            return;
        }
        
        wp_enqueue_style(
            $this->moduleName . '-admin',
            ESISTENZE_WP_KIT_URL . 'assets/admin.css',
            [],
            ESISTENZE_WP_KIT_VERSION
        );
        
        wp_enqueue_script(
            $this->moduleName . '-admin',
            ESISTENZE_WP_KIT_URL . 'assets/admin.js',
            ['jquery'],
            ESISTENZE_WP_KIT_VERSION,
            true
        );
        
        wp_localize_script($this->moduleName . '-admin', 'esistenzeAjax', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce($this->moduleName . '_nonce'),
            'module' => $this->moduleName
        ]);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueueFrontendAssets(): void {
        wp_enqueue_style(
            $this->moduleName . '-frontend',
            ESISTENZE_WP_KIT_URL . 'modules/' . $this->moduleName . '/assets/style.css',
            [],
            ESISTENZE_WP_KIT_VERSION
        );
        
        wp_enqueue_script(
            $this->moduleName . '-frontend',
            ESISTENZE_WP_KIT_URL . 'modules/' . $this->moduleName . '/assets/script.js',
            ['jquery'],
            ESISTENZE_WP_KIT_VERSION,
            true
        );
    }
    
    /**
     * Add module-specific actions
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $accepted_args
     */
    protected function addAction(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void {
        add_action($hook, $callback, $priority, $accepted_args);
    }
    
    /**
     * Add module-specific filters
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $accepted_args
     */
    protected function addFilter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void {
        add_filter($hook, $callback, $priority, $accepted_args);
    }
    
    /**
     * Log module messages
     * @param string $message
     * @param string $level
     */
    protected function log(string $message, string $level = 'info'): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[%s] %s: %s', strtoupper($level), $this->moduleName, $message));
        }
    }
    
    /**
     * Get module asset URL
     * @param string $asset
     * @return string
     */
    protected function getAssetUrl(string $asset): string {
        return ESISTENZE_WP_KIT_URL . 'modules/' . $this->moduleName . '/assets/' . $asset;
    }
    
    /**
     * Get module path
     * @param string $file
     * @return string
     */
    protected function getModulePath(string $file = ''): string {
        return ESISTENZE_WP_KIT_PATH . 'modules/' . $this->moduleName . '/' . $file;
    }
} 