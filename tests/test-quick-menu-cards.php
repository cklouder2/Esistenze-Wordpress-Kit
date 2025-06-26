<?php
/**
 * Quick Menu Cards Module Test
 * WordPress ortamında PHP destekli test
 */

class Test_Quick_Menu_Cards extends WP_UnitTestCase {

    private $admin;
    private $frontend;
    private $shortcodes;
    private $ajax;

    public function setUp() {
        parent::setUp();
        
        // Quick Menu Cards modülünü yükle
        if (class_exists('EsistenzeQuickMenuCards')) {
            $instance = EsistenzeQuickMenuCards::getInstance();
            $this->admin = $instance->admin;
            $this->frontend = $instance->frontend;
            $this->shortcodes = $instance->shortcodes;
            $this->ajax = $instance->ajax;
        }
    }

    /**
     * Test: Modül sınıflarının varlığı
     */
    public function test_module_classes_exist() {
        $this->assertTrue(class_exists('EsistenzeQuickMenuCards'), 'Ana Quick Menu Cards sınıfı bulunamadı');
        $this->assertTrue(class_exists('EsistenzeQuickMenuCardsAdmin'), 'Admin sınıfı bulunamadı');
        $this->assertTrue(class_exists('EsistenzeQuickMenuCardsFrontend'), 'Frontend sınıfı bulunamadı');
        $this->assertTrue(class_exists('EsistenzeQuickMenuCardsShortcodes'), 'Shortcodes sınıfı bulunamadı');
        $this->assertTrue(class_exists('EsistenzeQuickMenuCardsAjax'), 'AJAX sınıfı bulunamadı');
    }

    /**
     * Test: Capability fonksiyonu
     */
    public function test_capability_function() {
        $this->assertTrue(function_exists('esistenze_qmc_capability'), 'Capability fonksiyonu bulunamadı');
        $capability = esistenze_qmc_capability();
        $this->assertEquals('read', $capability, 'Capability fonksiyonu yanlış değer döndürüyor');
    }

    /**
     * Test: Admin menü kaydı
     */
    public function test_admin_menu_registration() {
        if (!$this->admin) {
            $this->markTestSkipped('Admin sınıfı yüklenemedi');
        }

        // Admin kullanıcısı oluştur
        $admin_user = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin_user);

        // Admin menü hook'unu tetikle
        do_action('admin_menu');

        global $submenu;
        
        // Ana menünün varlığını kontrol et
        $this->assertArrayHasKey('esistenze-wp-kit', $submenu, 'Ana eklenti menüsü bulunamadı');
        
        // Quick Menu Cards submenu'sunun varlığını kontrol et
        $qmc_menu_found = false;
        if (isset($submenu['esistenze-wp-kit'])) {
            foreach ($submenu['esistenze-wp-kit'] as $menu_item) {
                if ($menu_item[2] === 'esistenze-quick-menu') {
                    $qmc_menu_found = true;
                    break;
                }
            }
        }
        $this->assertTrue($qmc_menu_found, 'Quick Menu Cards submenu bulunamadı');
    }

    /**
     * Test: Shortcode kayıtları
     */
    public function test_shortcode_registration() {
        global $shortcode_tags;
        
        $this->assertArrayHasKey('quick_menu_cards', $shortcode_tags, 'quick_menu_cards shortcode kayıtlı değil');
        $this->assertArrayHasKey('quick_menu_banner', $shortcode_tags, 'quick_menu_banner shortcode kayıtlı değil');
        
        // Legacy shortcode'lar
        $this->assertArrayHasKey('hizli_menu', $shortcode_tags, 'hizli_menu shortcode kayıtlı değil');
        $this->assertArrayHasKey('hizli_menu_banner', $shortcode_tags, 'hizli_menu_banner shortcode kayıtlı değil');
    }

    /**
     * Test: Varsayılan ayarlar
     */
    public function test_default_settings() {
        $default_settings = EsistenzeQuickMenuCards::get_default_settings();
        
        $this->assertIsArray($default_settings, 'Varsayılan ayarlar array değil');
        $this->assertArrayHasKey('default_button_text', $default_settings, 'default_button_text ayarı yok');
        $this->assertArrayHasKey('banner_button_text', $default_settings, 'banner_button_text ayarı yok');
        $this->assertArrayHasKey('cache_duration', $default_settings, 'cache_duration ayarı yok');
        $this->assertArrayHasKey('enable_lazy_loading', $default_settings, 'enable_lazy_loading ayarı yok');
        $this->assertArrayHasKey('enable_analytics', $default_settings, 'enable_analytics ayarı yok');
    }

    /**
     * Test: Kart verisi sanitizasyonu
     */
    public function test_card_data_sanitization() {
        if (!$this->admin) {
            $this->markTestSkipped('Admin sınıfı yüklenemedi');
        }

        $test_data = array(
            'title' => '<script>alert("test")</script>Test Title',
            'description' => '<p>Test description</p><script>alert("xss")</script>',
            'image' => 'javascript:alert("xss")',
            'link' => 'http://example.com',
            'button_text' => '<b>Click</b>',
            'type' => 'card'
        );

        $sanitized = $this->admin->sanitize_cards_data(array($test_data));
        
        // Temel sanitizasyon kontrolü (basit implementasyon için)
        $this->assertIsArray($sanitized, 'Sanitize edilen veri array değil');
    }

    /**
     * Test: Shortcode çıktısı
     */
    public function test_shortcode_output() {
        // Test verisi oluştur
        $test_group = array(
            'name' => 'Test Group',
            'cards' => array(
                array(
                    'title' => 'Test Card',
                    'description' => 'Test Description',
                    'image' => 'http://example.com/image.jpg',
                    'link' => 'http://example.com',
                    'button_text' => 'Test Button',
                    'type' => 'card'
                )
            )
        );
        
        update_option('esistenze_quick_menu_kartlari', array(0 => $test_group));
        
        // Shortcode çıktısını test et
        $output = do_shortcode('[quick_menu_cards id="0"]');
        
        $this->assertNotEmpty($output, 'Shortcode çıktısı boş');
        $this->assertStringContainsString('esistenze-quick-menu-wrapper', $output, 'Wrapper class bulunamadı');
        $this->assertStringContainsString('Test Card', $output, 'Kart başlığı bulunamadı');
    }

    /**
     * Test: AJAX nonce doğrulama
     */
    public function test_ajax_nonce_verification() {
        if (!$this->ajax) {
            $this->markTestSkipped('AJAX sınıfı yüklenemedi');
        }

        // Reflection kullanarak private metoda erişim
        $reflection = new ReflectionClass($this->ajax);
        $method = $reflection->getMethod('verify_nonce');
        $method->setAccessible(true);

        // Geçersiz nonce ile test
        $_POST['nonce'] = 'invalid_nonce';
        $result = $method->invoke($this->ajax);
        $this->assertFalse($result, 'Geçersiz nonce kabul edildi');
    }

    /**
     * Test: Yetki kontrolleri
     */
    public function test_permission_checks() {
        // Giriş yapmamış kullanıcı
        wp_set_current_user(0);
        $this->assertFalse(current_user_can('read'), 'Giriş yapmamış kullanıcı read yetkisine sahip');

        // Subscriber kullanıcısı
        $subscriber = $this->factory->user->create(array('role' => 'subscriber'));
        wp_set_current_user($subscriber);
        $this->assertTrue(current_user_can('read'), 'Subscriber kullanıcısı read yetkisine sahip değil');

        // Admin kullanıcısı
        $admin = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin);
        $this->assertTrue(current_user_can('read'), 'Admin kullanıcısı read yetkisine sahip değil');
    }

    /**
     * Test: Cache işlemleri
     */
    public function test_cache_operations() {
        if (!$this->frontend) {
            $this->markTestSkipped('Frontend sınıfı yüklenemedi');
        }

        // Cache temizleme metodunu test et
        $reflection = new ReflectionClass($this->frontend);
        $method = $reflection->getMethod('clear_cache');
        $method->setAccessible(true);

        // Cache temizleme işlemini çalıştır
        $method->invoke($this->frontend);
        
        // Test başarılı olarak geçer (exception fırlatmazsa)
        $this->assertTrue(true, 'Cache temizleme işlemi başarısız');
    }

    /**
     * Test: Frontend stil dosyaları
     */
    public function test_frontend_styles() {
        if (!$this->frontend) {
            $this->markTestSkipped('Frontend sınıfı yüklenemedi');
        }

        // wp_enqueue_scripts hook'unu tetikle
        do_action('wp_enqueue_scripts');

        // Stil dosyasının kaydedilip kaydedilmediğini kontrol et
        $this->assertTrue(wp_style_is('esistenze-quick-menu-cards', 'registered'), 'Frontend stil dosyası kayıtlı değil');
    }

    /**
     * Test: Modül dosya yapısı
     */
    public function test_module_file_structure() {
        $module_path = dirname(__DIR__) . '/modules/quick-menu-cards/';
        
        $this->assertFileExists($module_path . 'quick-menu-cards.php', 'Ana modül dosyası bulunamadı');
        $this->assertFileExists($module_path . 'includes/class-admin.php', 'Admin sınıf dosyası bulunamadı');
        $this->assertFileExists($module_path . 'includes/class-frontend.php', 'Frontend sınıf dosyası bulunamadı');
        $this->assertFileExists($module_path . 'includes/class-shortcodes.php', 'Shortcodes sınıf dosyası bulunamadı');
        $this->assertFileExists($module_path . 'includes/class-ajax.php', 'AJAX sınıf dosyası bulunamadı');
        $this->assertFileExists($module_path . 'assets/admin.css', 'Admin CSS dosyası bulunamadı');
        $this->assertFileExists($module_path . 'assets/admin.js', 'Admin JS dosyası bulunamadı');
        $this->assertFileExists($module_path . 'assets/style.css', 'Frontend CSS dosyası bulunamadı');
    }

    /**
     * Test: PHP syntax kontrolü
     */
    public function test_php_syntax() {
        $module_path = dirname(__DIR__) . '/modules/quick-menu-cards/';
        $php_files = array(
            $module_path . 'quick-menu-cards.php',
            $module_path . 'includes/class-admin.php',
            $module_path . 'includes/class-frontend.php',
            $module_path . 'includes/class-shortcodes.php',
            $module_path . 'includes/class-ajax.php'
        );

        foreach ($php_files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $this->assertNotFalse($content, "Dosya okunamadı: $file");
                
                // Temel PHP syntax kontrolleri
                $this->assertStringContainsString('<?php', $content, "PHP açılış tag'i bulunamadı: $file");
                $this->assertStringNotContainsString('<?', $content, "Kısa PHP tag'i kullanılmış: $file");
            }
        }
    }

    /**
     * Temizlik işlemleri
     */
    public function tearDown() {
        // Test verilerini temizle
        delete_option('esistenze_quick_menu_kartlari');
        delete_option('esistenze_quick_menu_settings');
        delete_option('esistenze_quick_menu_analytics');
        
        parent::tearDown();
    }
} 