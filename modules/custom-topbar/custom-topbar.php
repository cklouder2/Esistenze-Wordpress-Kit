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
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Admin init
        add_action('admin_init', array($this, 'register_settings'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        
        // Output topbar
        if (has_action('wp_body_open')) {
            add_action('wp_body_open', array($this, 'output_topbar'));
        } else {
            add_action('wp_head', array($this, 'output_topbar'), 1);
        }
    }
    
    public static function admin_page() {
        if (isset($_POST['esistenze_topbar_save'])) {
            update_option('esistenze_topbar_enabled', isset($_POST['topbar_enabled']));
            update_option('esistenze_topbar_menu', sanitize_text_field($_POST['menu']));
            update_option('esistenze_topbar_phone', sanitize_text_field($_POST['phone']));
            update_option('esistenze_topbar_email', sanitize_email($_POST['email']));
            update_option('esistenze_topbar_bg_color', sanitize_hex_color($_POST['bg_color']));
            update_option('esistenze_topbar_font_size', sanitize_text_field($_POST['font_size']));
            update_option('esistenze_topbar_text_color', sanitize_hex_color($_POST['text_color']));
            update_option('esistenze_topbar_padding', sanitize_text_field($_POST['padding']));
            update_option('esistenze_topbar_height', sanitize_text_field($_POST['height']));
            echo '<div class="notice notice-success"><p>Ayarlar kaydedildi!</p></div>';
        }

        $enabled = get_option('esistenze_topbar_enabled', true);
        $selected_menu = get_option('esistenze_topbar_menu', '');
        $phone = get_option('esistenze_topbar_phone', '');
        $email = get_option('esistenze_topbar_email', '');
        $bg_color = get_option('esistenze_topbar_bg_color', '#4CAF50');
        $font_size = get_option('esistenze_topbar_font_size', '16');
        $text_color = get_option('esistenze_topbar_text_color', '#fff');
        $padding = get_option('esistenze_topbar_padding', '5');
        $height = get_option('esistenze_topbar_height', '50');

        // Get available menus
        $menus = get_terms('nav_menu', array('hide_empty' => false));
        ?>
        <div class="wrap">
            <h1>Custom Topbar Ayarları</h1>
            <p>Site üstüne özelleştirilebilir menü ve iletişim bilgileri çubuğu ekler.</p>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Topbar'ı Etkinleştir</th>
                        <td>
                            <label>
                                <input type="checkbox" name="topbar_enabled" <?php checked($enabled); ?> />
                                Üst çubuğu etkinleştir
                            </label>
                        </td>
                    </tr>
                </table>
                
                <h2>Menü Seçimi</h2>
                <p>WordPress'te daha önce oluşturduğunuz menülerden birini seçin.</p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="menu">Menü</label></th>
                        <td>
                            <select name="menu" id="menu" class="regular-text">
                                <option value="">-- Menü Seçiniz --</option>
                                <?php
                                foreach ($menus as $menu) {
                                    printf('<option value="%s" %s>%s</option>',
                                        esc_attr($menu->term_id),
                                        selected($selected_menu, $menu->term_id, false),
                                        esc_html($menu->name)
                                    );
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>İletişim Bilgileri</h2>
                <p>Telefon numarasını ve e-posta adresini girin.</p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="phone">Telefon Numarası</label></th>
                        <td>
                            <input type="text" name="phone" id="phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" placeholder="+90 123 456 7890">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email">E-posta Adresi</label></th>
                        <td>
                            <input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>" class="regular-text" placeholder="ornek@ornek.com">
                        </td>
                    </tr>
                </table>

                <h2>Stil Ayarları</h2>
                <p>Top bar'ın görünümünü özelleştirin.</p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="bg_color">Arka Plan Rengi</label></th>
                        <td>
                            <input type="color" name="bg_color" id="bg_color" value="<?php echo esc_attr($bg_color); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="text_color">Metin Rengi</label></th>
                        <td>
                            <input type="color" name="text_color" id="text_color" value="<?php echo esc_attr($text_color); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="font_size">Font Boyutu (px)</label></th>
                        <td>
                            <input type="number" name="font_size" id="font_size" value="<?php echo esc_attr($font_size); ?>" min="10" max="30" step="1" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="padding">İç Boşluk (%)</label></th>
                        <td>
                            <input type="number" name="padding" id="padding" value="<?php echo esc_attr($padding); ?>" min="0" max="20" step="1" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="height">Yükseklik (px)</label></th>
                        <td>
                            <input type="number" name="height" id="height" value="<?php echo esc_attr($height); ?>" min="30" max="100" step="1" class="small-text">
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="esistenze_topbar_save" class="button-primary" value="Ayarları Kaydet">
                </p>
            </form>
        </div>
        <?php
    }
    
    public function register_settings() {
        register_setting('esistenze_topbar', 'esistenze_topbar_enabled');
        register_setting('esistenze_topbar', 'esistenze_topbar_menu');
        register_setting('esistenze_topbar', 'esistenze_topbar_phone');
        register_setting('esistenze_topbar', 'esistenze_topbar_email');
        register_setting('esistenze_topbar', 'esistenze_topbar_bg_color');
        register_setting('esistenze_topbar', 'esistenze_topbar_font_size');
        register_setting('esistenze_topbar', 'esistenze_topbar_text_color');
        register_setting('esistenze_topbar', 'esistenze_topbar_padding');
        register_setting('esistenze_topbar', 'esistenze_topbar_height');
    }
    
    public function enqueue_styles() {
        if (!get_option('esistenze_topbar_enabled', true)) {
            return;
        }
        
        wp_enqueue_style('esistenze-custom-topbar', ESISTENZE_WP_KIT_URL . 'modules/custom-topbar/assets/style.css', array(), ESISTENZE_WP_KIT_VERSION);
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2');
    }

    public function output_topbar() {
        if (!get_option('esistenze_topbar_enabled', true)) {
            return;
        }
        
        $selected_menu = get_option('esistenze_topbar_menu', '');
        $phone = get_option('esistenze_topbar_phone', '');
        $email = get_option('esistenze_topbar_email', '');
        $font_size = get_option('esistenze_topbar_font_size', '16');
        $text_color = get_option('esistenze_topbar_text_color', '#ffffff');
        $padding = get_option('esistenze_topbar_padding', '5');
        $height = get_option('esistenze_topbar_height', '50');
        $bg_color = get_option('esistenze_topbar_bg_color', '#4CAF50');
        $bg_color_dark = $this->adjust_color_brightness($bg_color, -60);

        if (empty($selected_menu) && empty($phone) && empty($email)) {
            return;
        }

        $style_vars = sprintf(
            'style="--esistenze-bg-color: %s; --esistenze-bg-color-dark: %s; --esistenze-text-color: %s; --esistenze-font-size: %spx; --esistenze-padding: %s%%; --esistenze-height: %spx;"',
            esc_attr($bg_color),
            esc_attr($bg_color_dark),
            esc_attr($text_color),
            esc_attr($font_size),
            esc_attr($padding),
            esc_attr($height)
        );

        echo '<div class="esistenze-top-bar" ' . $style_vars . '>';
        echo '<div class="esistenze-top-bar-left">';
        if (!empty($selected_menu)) {
            echo '<ul class="esistenze-top-bar-menu">';
            $menu_output = wp_nav_menu(array(
                'menu' => $selected_menu,
                'menu_class' => 'esistenze-top-bar-menu',
                'container' => false,
                'depth' => 1,
                'echo' => false,
                'items_wrap' => '%3$s',
                'fallback_cb' => '__return_empty_string'
            ));
            echo $menu_output ?: '<li>Menu failed to load</li>';
            echo '</ul>';
        }
        echo '</div>';

        echo '<div class="esistenze-top-bar-right">';
        echo '<ul class="esistenze-top-bar-contact">';
        if (!empty($phone)) {
            echo '<li><a href="tel:' . esc_attr($phone) . '"><i class="fa fa-phone"></i>' . esc_html($phone) . '</a></li>';
        }
        if (!empty($email)) {
            echo '<li><a href="mailto:' . esc_attr($email) . '"><i class="fa fa-envelope"></i>' . esc_html($email) . '</a></li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';
    }

    private function adjust_color_brightness($hex, $steps) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) .
                     str_pad(dechex($g), 2, '0', STR_PAD_LEFT) .
                     str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}

// Initialize the module
EsistenzeCustomTopbar::getInstance();