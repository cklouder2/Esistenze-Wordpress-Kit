<?php
/**
 * Test cases for main plugin functionality
 */

class TestMainPlugin extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * Test that the plugin is loaded
     */
    public function test_plugin_loaded() {
        $this->assertTrue(class_exists('EsistenzeWPKit'));
    }

    /**
     * Test plugin constants are defined
     */
    public function test_plugin_constants() {
        $this->assertTrue(defined('ESISTENZE_WP_KIT_VERSION'));
        $this->assertTrue(defined('ESISTENZE_WP_KIT_PATH'));
        $this->assertTrue(defined('ESISTENZE_WP_KIT_URL'));
    }

    /**
     * Test capability function exists
     */
    public function test_capability_function() {
        $this->assertTrue(function_exists('esistenze_qmc_capability'));
    }

    /**
     * Test capability function returns correct values
     */
    public function test_capability_function_returns() {
        // Test for admin user
        $admin_user = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin_user);
        $this->assertEquals('manage_options', esistenze_qmc_capability());

        // Test for editor user
        $editor_user = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($editor_user);
        $this->assertEquals('edit_pages', esistenze_qmc_capability());

        // Test for author user
        $author_user = $this->factory->user->create(array('role' => 'author'));
        wp_set_current_user($author_user);
        $this->assertEquals('edit_posts', esistenze_qmc_capability());
    }

    /**
     * Test module loading
     */
    public function test_modules_loaded() {
        $kit = EsistenzeWPKit::getInstance();
        $this->assertInstanceOf('EsistenzeWPKit', $kit);
    }

    /**
     * Test admin menu is registered
     */
    public function test_admin_menu_registered() {
        global $menu, $submenu;
        
        // Set admin user
        $admin_user = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin_user);
        set_current_screen('dashboard');
        
        // Trigger admin_menu action
        do_action('admin_menu');
        
        // Check if our menu exists
        $menu_exists = false;
        foreach ($menu as $menu_item) {
            if (isset($menu_item[2]) && $menu_item[2] === 'esistenze-wp-kit') {
                $menu_exists = true;
                break;
            }
        }
        
        $this->assertTrue($menu_exists, 'Admin menu should be registered');
    }
} 