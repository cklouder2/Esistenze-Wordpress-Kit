<?php
/**
 * Test cases for Smart Product Buttons module
 */

class TestSmartButtons extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        
        // Activate WooCommerce if available
        if (class_exists('WooCommerce')) {
            $GLOBALS['woocommerce'] = new WooCommerce();
        }
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * Test Smart Buttons class exists
     */
    public function test_smart_buttons_class_exists() {
        $this->assertTrue(class_exists('EsistenzeSmartButtons'));
    }

    /**
     * Test Smart Buttons singleton
     */
    public function test_smart_buttons_singleton() {
        $instance1 = EsistenzeSmartButtons::getInstance();
        $instance2 = EsistenzeSmartButtons::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test shortcode registration
     */
    public function test_shortcode_registered() {
        $this->assertTrue(shortcode_exists('esistenze_button'));
    }

    /**
     * Test button shortcode output
     */
    public function test_button_shortcode_output() {
        $output = do_shortcode('[esistenze_button id="1"]');
        $this->assertNotEmpty($output);
    }

    /**
     * Test admin capability check
     */
    public function test_admin_capability() {
        // Test with admin user
        $admin_user = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin_user);
        
        $buttons = new EsistenzeSmartButtons();
        $this->assertTrue(current_user_can(esistenze_qmc_capability()));
    }

    /**
     * Test button save functionality
     */
    public function test_button_save() {
        $admin_user = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin_user);
        
        $test_button = array(
            'title' => 'Test Button',
            'type' => 'phone',
            'value' => '+1234567890',
            'enabled' => true
        );
        
        $buttons = get_option('esistenze_smart_custom_buttons', array());
        $buttons[] = $test_button;
        update_option('esistenze_smart_custom_buttons', $buttons);
        
        $saved_buttons = get_option('esistenze_smart_custom_buttons', array());
        $this->assertNotEmpty($saved_buttons);
        $this->assertEquals('Test Button', $saved_buttons[0]['title']);
    }
} 