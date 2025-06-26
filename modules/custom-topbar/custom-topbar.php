<?php
/*
 * Custom Topbar Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

class EsistenzeCustomTopbar {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_head', array($this, 'add_topbar'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        register_setting('esistenze_custom_topbar', 'esistenze_custom_topbar_settings');
    }
    
    public function enqueue_styles() {
        $settings = get_option('esistenze_custom_topbar_settings', array());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        wp_enqueue_style(
            'esistenze-custom-topbar',
            plugin_dir_url(__FILE__) . 'assets/style.css',
            array(),
            ESISTENZE_WP_KIT_VERSION
        );
        
        // Dynamic CSS
        $dynamic_css = $this->generate_dynamic_css($settings);
        if (!empty($dynamic_css)) {
            wp_add_inline_style('esistenze-custom-topbar', $dynamic_css);
        }
    }
    
    private function generate_dynamic_css($settings) {
        $css = '';
        
        // Default values
        $defaults = array(
            'bg_color' => '#2c3e50',
            'text_color' => '#ffffff',
            'font_size' => 14,
            'height' => 40
        );
        
        $settings = array_merge($defaults, $settings);
        
        $css .= '.esistenze-custom-topbar {';
        $css .= 'background-color: ' . sanitize_hex_color($settings['bg_color']) . ';';
        $css .= 'color: ' . sanitize_hex_color($settings['text_color']) . ';';
        $css .= 'font-size: ' . intval($settings['font_size']) . 'px;';
        $css .= 'height: ' . intval($settings['height']) . 'px;';
        $css .= 'line-height: ' . intval($settings['height']) . 'px;';
        $css .= '}';
        
        return $css;
    }
    
    public function add_topbar() {
        $settings = get_option('esistenze_custom_topbar_settings', array());
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        $phone = isset($settings['phone']) ? $settings['phone'] : '';
        $email = isset($settings['email']) ? $settings['email'] : '';
        $address = isset($settings['address']) ? $settings['address'] : '';
        $social_links = isset($settings['social_links']) ? $settings['social_links'] : '';
        
        if (empty($phone) && empty($email) && empty($address) && empty($social_links)) {
            return;
        }
        ?>
        <div class="esistenze-custom-topbar">
            <div class="container">
                <div class="topbar-content">
                    <?php if (!empty($phone)): ?>
                        <span class="topbar-phone">📞 <?php echo esc_html($phone); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($email)): ?>
                        <span class="topbar-email">✉️ <?php echo esc_html($email); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($address)): ?>
                        <span class="topbar-address">📍 <?php echo esc_html($address); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($social_links)): ?>
                        <span class="topbar-social"><?php echo wp_kses_post($social_links); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    public static function admin_page() {
        if (!current_user_can(esistenze_qmc_capability())) {
            wp_die(__('Bu sayfaya erişmenize izin verilmiyor.', 'esistenze-wp-kit'));
        }
        
        $settings = get_option('esistenze_custom_topbar_settings', array());
        
        // Default values
        $defaults = array(
            'enabled' => false,
            'phone' => '',
            'email' => '',
            'address' => '',
            'social_links' => '',
            'bg_color' => '#2c3e50',
            'text_color' => '#ffffff',
            'font_size' => 14,
            'height' => 40
        );
        
        $settings = array_merge($defaults, $settings);
        ?>
        <div class="wrap">
            <h1><?php _e('Custom Topbar Ayarları', 'esistenze-wp-kit'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('esistenze_custom_topbar'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php _e('Eklentiyi Etkinleştir', 'esistenze-wp-kit'); ?></th>
                        <td>
                            <input type="checkbox" name="esistenze_custom_topbar_settings[enabled]" value="1" <?php checked($settings['enabled']); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="phone"><?php _e('Telefon', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="text" id="phone" name="esistenze_custom_topbar_settings[phone]" value="<?php echo esc_attr($settings['phone']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email"><?php _e('E-posta', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="email" id="email" name="esistenze_custom_topbar_settings[email]" value="<?php echo esc_attr($settings['email']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="address"><?php _e('Adres', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="text" id="address" name="esistenze_custom_topbar_settings[address]" value="<?php echo esc_attr($settings['address']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="social_links"><?php _e('Sosyal Medya Linkleri', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <textarea id="social_links" name="esistenze_custom_topbar_settings[social_links]" rows="3" class="large-text"><?php echo esc_textarea($settings['social_links']); ?></textarea>
                            <p class="description"><?php _e('HTML formatında sosyal medya linklerini girin.', 'esistenze-wp-kit'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bg_color"><?php _e('Arka Plan Rengi', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="color" id="bg_color" name="esistenze_custom_topbar_settings[bg_color]" value="<?php echo esc_attr($settings['bg_color']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="text_color"><?php _e('Metin Rengi', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="color" id="text_color" name="esistenze_custom_topbar_settings[text_color]" value="<?php echo esc_attr($settings['text_color']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="font_size"><?php _e('Yazı Boyutu', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="number" class="small-text" id="font_size" name="esistenze_custom_topbar_settings[font_size]" value="<?php echo esc_attr($settings['font_size']); ?>" /> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="height"><?php _e('Yükseklik', 'esistenze-wp-kit'); ?></label></th>
                        <td>
                            <input type="number" class="small-text" id="height" name="esistenze_custom_topbar_settings[height]" value="<?php echo esc_attr($settings['height']); ?>" /> px
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <h2><?php _e('Nasıl Kullanılır', 'esistenze-wp-kit'); ?></h2>
            <p><?php _e('Bu modül sitenizin üst kısmına özelleştirilebilir bir bilgi çubuğu ekler.', 'esistenze-wp-kit'); ?></p>
            <p><?php _e('Telefon, e-posta, adres ve sosyal medya linklerini ekleyebilirsiniz.', 'esistenze-wp-kit'); ?></p>
            
            <h3><?php _e('Sosyal Medya Linkleri Örneği:', 'esistenze-wp-kit'); ?></h3>
            <pre><code>&lt;a href="https://facebook.com/yourpage"&gt;Facebook&lt;/a&gt; | &lt;a href="https://twitter.com/yourpage"&gt;Twitter&lt;/a&gt;</code></pre>
        </div>
        <?php
    }
}

// Initialize the class
EsistenzeCustomTopbar::getInstance();
?>