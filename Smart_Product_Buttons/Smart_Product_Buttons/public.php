<?php
if (!defined('ABSPATH')) exit;

class Smart_Product_Buttons_Public {
    private static $rendered = false;
    private static $modal_content = '';

    public function __construct() {
        add_action('woocommerce_product_meta_end', array($this, 'render_buttons_on_product_page'), 10, 0);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('wp_footer', array($this, 'render_modal_container'));
    }

    public function enqueue_public_assets() {
        wp_enqueue_style('smart-product-buttons-public-css', plugin_dir_url(__FILE__) . 'public.css', array(), '1.0');
        wp_enqueue_script('smart-product-buttons-public-js', plugin_dir_url(__FILE__) . 'public.js', array('jquery'), '1.0', true);
        if (is_user_logged_in()) {
            wp_add_inline_script('smart-product-buttons-public-js', 'console.log("JS file enqueued at ' . current_time('mysql') . '");');
        }
    }

    public function render_buttons_on_product_page() {
        if (self::$rendered) return;

        $buttons = get_option('smart_custom_buttons', []);
        if (empty($buttons)) return;

        ob_start();
        $rendered_form_trigger = false;

        echo '<div class="smart-buttons-frontend">';
        foreach ($buttons as $index => $button) {
            $type = $button['type'] ?? '';
            if ($type === 'form_trigger' && $rendered_form_trigger) {
                continue;
            }

            $title = $button['title'] ?? '';
            $value = $button['value'] ?? '';
            $message = $button['message'] ?? '';
            $color1 = $button['button_color_start'] ?? '#ff0000';
            $color2 = $button['button_color_end'] ?? '#940000';
            $text_color = $button['text_color'] ?? '#fff';
            $font_size = intval($button['font_size'] ?? 16);
            $icon = $button['icon'] ?? '';
            $track = $button['tracking_name'] ?? ($type === 'form_trigger' ? 'form_teklif_al' : ($type . '_button_' . sanitize_title($title)));
            $data = 'data-track="' . esc_attr($track) . '" data-id="' . esc_attr($value) . '"';
            $icon_html = $icon ? '<i class="fa ' . esc_attr($icon) . '"></i>' : '';
            $style = 'style="background: linear-gradient(45deg, ' . esc_attr($color1) . ', ' . esc_attr($color2) . ') !important; color: ' . esc_attr($text_color) . ' !important; font-size: ' . min($font_size, 16) . 'px !important; padding: 10px 20px 10px 20px !important; box-shadow: 0 6px 15px rgba(' . $this->hexToRgb($color1) . ', 0.4) !important;"';

            switch ($type) {
                case 'phone':
                    echo '<a href="tel:' . esc_attr($value) . '" class="smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'mail':
                    echo '<a href="mailto:' . esc_attr($value) . '" class="smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'whatsapp':
                    $url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $value);
                    if ($message) $url .= '?text=' . urlencode($message);
                    echo '<a target="_blank" href="' . esc_url($url) . '" class="smart-btn" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . $title . '</a>';
                    break;
                case 'form_trigger':
                    $shortcode = '[contact-form-7 id="' . esc_attr($value) . '" title="Fiyat Teklifi"]';
                    $rendered_shortcode = do_shortcode($shortcode);
                    if (!empty($rendered_shortcode)) {
                        self::$modal_content = $rendered_shortcode;
                        echo '<button type="button" class="smart-btn form-popup-trigger" ' . $style . ' ' . $data . '>' . $icon_html . ' ' . $title . '</button>';
                        $rendered_form_trigger = true;
                    } else {
                        error_log('Shortcode render failed for ID: ' . $value . ' at ' . current_time('mysql'));
                    }
                    break;
            }
        }
        echo '</div>';

        $output = ob_get_clean();
        if (!empty($output)) {
            echo $output;
            self::$rendered = true;
        }
    }

    public function render_modal_container() {
        echo '<div id="smart-form-modal" class="smart-modal"><div class="smart-modal-content"><span class="smart-close">×</span><div class="smart-form-container"></div></div></div>';
        if (!empty(self::$modal_content)) {
            echo '<div id="smart-form-modal-content" style="display:none;">' . self::$modal_content . '</div>';
        } else {
            error_log('Modal content is empty at ' . current_time('mysql'));
        }
    }

    private function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "$r, $g, $b";
    }
}

new Smart_Product_Buttons_Public();