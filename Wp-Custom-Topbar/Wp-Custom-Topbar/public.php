<?php
if (!defined('ABSPATH')) {
    exit;
}
function xai_top_bar_enqueue_styles() {
    wp_enqueue_style('xai-top-bar-style', plugins_url('css/style.css', __FILE__), array(), '2.0');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2');
}
add_action('wp_enqueue_scripts', 'xai_top_bar_enqueue_styles');

function xai_top_bar_output() {
    $selected_menu = get_option('xai_top_bar_menu', '');
    $phone = get_option('xai_top_bar_phone', '+90 212 612 35 11');
    $email = get_option('xai_top_bar_email', 'mail@durucanta.com');
    $font_size = get_option('xai_top_bar_font_size', '16');
    $text_color = get_option('xai_top_bar_text_color', '#ffffff');
    $padding = get_option('xai_top_bar_padding', '5');
    $height = get_option('xai_top_bar_height', '50');
    $bg_color = get_option('xai_top_bar_bg_color', '#4CAF50');
    $bg_color_dark = adjust_color_brightness($bg_color, -60);

    if (empty($selected_menu) && empty($phone) && empty($email)) {
        return;
    }

    $style_vars = sprintf(
        'style="--xai-bg-color: %s; --xai-bg-color-dark: %s; --xai-text-color: %s; --xai-font-size: %spx; --xai-padding: %s%%; --xai-height: %spx;"',
        esc_attr($bg_color),
        esc_attr($bg_color_dark),
        esc_attr($text_color),
        esc_attr($font_size),
        esc_attr($padding),
        esc_attr($height)
    );

    echo '<div class="xai-top-bar" ' . $style_vars . '>';
    echo '<div class="xai-top-bar-left">';
    if (!empty($selected_menu)) {
        echo '<ul class="xai-top-bar-menu">';
        $menu_output = wp_nav_menu(array(
            'menu' => $selected_menu,
            'menu_class' => 'xai-top-bar-menu',
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

    echo '<div class="xai-top-bar-right">';
    echo '<ul class="xai-top-bar-contact">';
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

if (has_action('wp_body_open')) {
    add_action('wp_body_open', 'xai_top_bar_output');
} else {
    add_action('wp_head', 'xai_top_bar_output', 1);
}

function adjust_color_brightness($hex, $steps) {
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
?>
