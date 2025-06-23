<?php
/*
Plugin Name: Smart Product Buttons
Description: WooCommerce ürün sayfaları için animasyonlu, özelleştirilebilir, izlenebilir özel butonlar.
Version: 6.1
Author: Cem Karabulut
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'admin/admin.php';
require_once plugin_dir_path(__FILE__) . 'public.php';

new Smart_Product_Buttons_Admin();
new Smart_Product_Buttons_Public();