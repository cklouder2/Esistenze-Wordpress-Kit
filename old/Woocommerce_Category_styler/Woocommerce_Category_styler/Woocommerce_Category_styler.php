<?php
/*
Plugin Name: Category Styler
Description: Styles WooCommerce categories, products, and page header with a modern luxury layout.
Version: 1.0
Author: Cem Karabulut - Esistenze
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register shortcode to display styled categories
add_shortcode('display_categories', 'display_styled_categories');
function display_styled_categories() {
    $args = array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => 0,
    );
    $categories = get_terms($args);

    if (empty($categories)) {
        error_log('No categories found for shortcode [display_categories].');
        return '<p>Hiç kategori bulunamadı.</p>';
    }

    ob_start();
    ?>
    <div class="category-styler-grid">
        <?php foreach ($categories as $category) : ?>
            <div class="category-styler-item">
                <a href="<?php echo esc_url(get_term_link($category)); ?>">
                    <div class="category-styler-image" style="background-image: url('<?php echo esc_url(wp_get_attachment_url(get_term_meta($category->term_id, 'thumbnail_id', true))); ?>');"></div>
                    <h3 class="category-styler-title"><?php echo esc_html($category->name); ?></h3>
                    <p class="category-styler-description"><?php echo esc_html($category->description); ?></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Enqueue styles with higher priority
add_action('wp_enqueue_scripts', 'category_styler_enqueue_styles', 999);
function category_styler_enqueue_styles() {
    $custom_css = '
        /* Main Grid Layout for Shortcode */
        .category-styler-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .category-styler-item {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .category-styler-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }
        .category-styler-image {
            height: 150px;
            background-size: cover;
            background-position: center;
        }
        .category-styler-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            padding: 15px;
            margin: 0;
            font-family: "Montserrat", sans-serif;
            text-align: center;
        }
        .category-styler-description {
            font-size: 0.95em;
            color: #7f8c8d;
            padding: 0 15px 15px;
            margin: 0;
            line-height: 1.6;
            font-family: "Roboto", sans-serif;
            text-align: center;
        }
        .category-styler-item a {
            text-decoration: none;
            color: inherit;
        }

        /* Sidebar Category Styling for #nav_menu-7 */
        #nav_menu-7 .widget_nav_menu h4 {
            font-size: 1.4em;
            color: #ffffff;
            padding: 15px 20px;
            margin: 0 0 20px;
            font-family: "Montserrat", sans-serif;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        #nav_menu-7 .widget_nav_menu h4:hover {
            background: linear-gradient(90deg, #45a049, #2E7D32);
            transform: translateY(-2px);
        }
        #nav_menu-7 .menu {
            list-style: none;
            padding: 0;
        }
        #nav_menu-7 .menu li {
            margin-bottom: 12px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        #nav_menu-7 .menu li:hover {
            background: #f9f9f9;
            transform: translateX(5px);
        }
        #nav_menu-7 .menu li a {
            display: block;
            padding: 12px 15px;
            color: #2c3e50;
            font-family: "Montserrat", sans-serif;
            font-size: 1em;
            text-decoration: none;
            text-align: left;
        }
        #nav_menu-7 .menu li a:hover {
            color: #4CAF50;
        }
        #nav_menu-7 .menu li.current-menu-item a {
            font-weight: 600;
            background: #e6f3e6;
            border-left: 5px solid #4CAF50;
        }

        /* Sidebar Category Styling for #nav_menu-3 */
        #nav_menu-3 .widget_nav_menu h4 {
            font-size: 1.4em;
            color: #ffffff;
            padding: 15px 20px;
            margin: 0 0 20px;
            font-family: "Montserrat", sans-serif;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        #nav_menu-3 .widget_nav_menu h4:hover {
            background: linear-gradient(90deg, #45a049, #2E7D32);
            transform: translateY(-2px);
        }
        #nav_menu-3 .menu {
            list-style: none;
            padding: 0;
        }
        #nav_menu-3 .menu li {
            margin-bottom: 12px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        #nav_menu-3 .menu li:hover {
            background: #f9f9f9;
            transform: translateX(5px);
        }
        #nav_menu-3 .menu li a {
            display: block;
            padding: 12px 15px;
            color: #2c3e50;
            font-family: "Montserrat", sans-serif;
            font-size: 1em;
            text-decoration: none;
            text-align: left;
        }
        #nav_menu-3 .menu li a:hover {
            color: #4CAF50;
        }
        #nav_menu-3 .menu li.current-menu-item a {
            font-weight: 600;
            background: #e6f3e6;
            border-left: 5px solid #4CAF50;
        }

        /* Page Header Styling for #page-header-wrap */
        #page-header-wrap {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 70%, #2E7D32 100%) !important;
            height: 350px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        #page-header-bg {
            background: none !important;
        }
        .nectar-parallax-enabled {
            background-size: cover;
            background-position: center;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }
        #page-header-wrap .inner-wrap {
            text-align: center;
            padding: 20px;
        }
        #page-header-wrap .inner-wrap h1 {
            font-size: 3em;
            font-weight: 800;
            color: #ffffff;
            text-transform: uppercase;
            background: rgba(0, 0, 0, 0.6);
            padding: 20px 40px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            font-family: "Montserrat", sans-serif;
            display: inline-block;
            animation: fadeInUp 1s ease-out;
            position: relative;
            z-index: 1;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        #page-header-wrap .inner-wrap h1:hover {
            transform: scale(1.1);
            background: rgba(0, 0, 0, 0.7);
        }
        #page-header-wrap .subheader {
            color: #ffffff;
            font-size: 1.3em;
            font-family: "Roboto", sans-serif;
            margin-top: 15px;
            opacity: 0;
            animation: fadeIn 1.5s ease-out 0.5s forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Hide price-hover-wrap on category pages */
        .woocommerce-products-header ~ .products .price-hover-wrap {
            display: none !important;
        }

        /* Shop Header Styling */
        .nectar-shop-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            background: #ffffff; /* Beyaz arka plan korundu */
            box-shadow: none; /* Gölge kaldırıldı */
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .nectar-shop-header .page-title {
            font-size: 2.2em;
            font-weight: 700;
            color: #86ba3d; /* Tüm başlıklar için yeşil ton */
            font-family: "Montserrat", sans-serif;
            margin: 0;
            text-align: center;
            padding: 10px 0;
        }
        .woocommerce-breadcrumb {
            font-size: 1.1em;
            font-family: "Roboto", sans-serif;
            color: #7f8c8d;
            margin: 10px 0;
            text-align: center;
        }
        .woocommerce-breadcrumb a {
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .woocommerce-breadcrumb a:hover {
            color: #45a049;
        }
        .woocommerce-breadcrumb .fa-angle-right {
            margin: 0 5px;
            color: #7f8c8d;
        }
        .nectar-shop-header-bottom {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px 20px;
        }
        .nectar-shop-header-bottom .left-side,
        .nectar-shop-header-bottom .right-side {
            flex: 1;
        }
        .nectar-shop-filter-trigger {
            background: #ffffff;
            border: 1px solid #4CAF50;
            border-radius: 20px;
            padding: 8px 15px;
            color: #4CAF50;
            font-family: "Montserrat", sans-serif;
            font-size: 1em;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        .nectar-shop-filter-trigger:hover {
            background: #4CAF50;
            color: #ffffff;
            transform: translateY(-2px);
        }
        .nectar-shop-filter-trigger .toggle-icon {
            margin-right: 10px;
        }
        .nectar-shop-filter-trigger .text-wrap .dynamic .show,
        .nectar-shop-filter-trigger .text-wrap .dynamic .hide {
            display: none;
        }
        .nectar-shop-filter-trigger .text-wrap {
            position: relative;
        }
        .nectar-shop-filter-trigger .text-wrap:after {
            content: "Kategorileri Göster";
            font-family: "Montserrat", sans-serif;
        }
        .nectar-shop-filter-trigger[aria-expanded="true"] .text-wrap:after {
            content: "Kategorileri Gizle";
        }
        .woocommerce-result-count {
            font-size: 1em;
            font-family: "Roboto", sans-serif;
            color: #7f8c8d;
            margin: 0;
        }
        .woocommerce-ordering select {
            padding: 8px;
            border: 1px solid #4CAF50;
            border-radius: 20px;
            font-family: "Roboto", sans-serif;
            color: #2c3e50;
            transition: border-color 0.3s ease;
        }
        .woocommerce-ordering select:focus {
            outline: none;
            border-color: #45a049;
        }

        /* Product Styling */
        .products .product {
            border: 2px solid #4CAF50 !important;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
        .products .product:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
        }
        .product-wrap {
            padding: 10px;
        }
        .woocommerce-loop-product__title {
            font-size: 1.1em;
            font-weight: 600;
            color: #2c3e50;
            font-family: "Montserrat", sans-serif;
            text-align: center;
            margin: 10px 0;
        }
        .price-hover-wrap {
            text-align: center;
            padding: 5px;
        }
        .button {
            background: #4CAF50;
            color: #fff;
            border-radius: 20px;
            padding: 8px 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .button:hover {
            background: #45a049;
            transform: scale(1.05);
        }
    ';
    wp_add_inline_style('woocommerce-inline', $custom_css);

    // Hata ayıklama için stilin yüklendiğini logla
    error_log('Category Styler CSS loaded for #page-header-wrap, #nav_menu-7, #nav_menu-3, price-hover-wrap, and shop header.');
}