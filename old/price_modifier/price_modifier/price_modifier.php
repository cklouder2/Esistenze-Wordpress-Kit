<?php
/*
Plugin Name: Price Modifier
Description: Adds a custom price note to WooCommerce products with modern styling.
Version: 1.0
Author: Cem Karabulut - Esistenze
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add admin menu
add_action('admin_menu', 'price_modifier_admin_menu');
function price_modifier_admin_menu() {
    add_menu_page('Price Modifier Settings', 'Price Modifier', 'manage_options', 'price-modifier', 'price_modifier_settings_page');
}

// Admin settings page
function price_modifier_settings_page() {
    ?>
    <div class="wrap">
        <h1>Price Modifier Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('price_modifier_options');
            do_settings_sections('price-modifier');
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="price_note">Fiyat Notu</label></th>
                    <td><input type="text" name="price_note" id="price_note" value="<?php echo esc_attr(get_option('price_note', '1000 adet için liste fiyatıdır. Özel fiyat için bizimle iletişime geçin.')); ?>" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'price_modifier_admin_init');
function price_modifier_admin_init() {
    register_setting('price_modifier_options', 'price_note');
}

// Enqueue styles with higher priority
add_action('wp_enqueue_scripts', 'price_modifier_enqueue_styles', 999);
function price_modifier_enqueue_styles() {
    $custom_css = '
        .price-modifier-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
            background: linear-gradient(135deg, #e6f3e6 0%, #d0e8d0 100%);
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid #4CAF50;
        }
        .price-modifier-price {
            font-size: 1.8em;
            color: #2c3e50;
            font-weight: 700;
            font-family: "Montserrat", sans-serif;
        }
        .price-modifier-note {
            font-size: 14px;
            color: #e74c3c;
            background-color: #ffffff;
            padding: 10px 15px;
            border-radius: 6px;
            font-family: "Roboto", sans-serif;
            font-weight: 500;
            border-left: 6px solid #e74c3c;
            box-shadow: 0 2px 10px rgba(231, 76, 60, 0.1);
            transition: all 0.3s ease;
        }
        .price-modifier-note:hover {
            background-color: #ffebee;
            transform: translateY(-2px);
        }
        .woocommerce-product-details__short-description {
            margin-top: 20px;
            font-size: 1.1em;
            color: #7f8c8d;
            line-height: 1.6;
        }
        .button {
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            background-color: #4CAF50;
            color: #fff;
            border: none;
        }
        .button:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }
    ';
    wp_add_inline_style('woocommerce-inline', $custom_css);
}

// Remove default price and add custom price with note
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
add_action('woocommerce_single_product_summary', 'custom_price_with_note', 10);
function custom_price_with_note() {
    global $product;
    if ($product && $product->get_price()) {
        $price_note = get_option('price_note', '1000 adet için liste fiyatıdır. Özel fiyat için bizimle iletişime geçin.');
        $price_html = wc_price($product->get_price());
        echo '<div class="price-modifier-wrapper">';
        echo '<span class="price-modifier-price">' . $price_html . '</span>';
        echo '<span class="price-modifier-note">' . esc_html($price_note) . '</span>';
        echo '</div>';
    }
}