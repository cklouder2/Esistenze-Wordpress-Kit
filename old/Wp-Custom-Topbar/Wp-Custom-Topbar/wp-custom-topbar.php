<?php
/*
Plugin Name: WP Custom Topbar
Description: Yönetilebilir bir üst çubuk eklentisi. Sol tarafta yatay menü, sağ tarafta telefon ve e-posta bilgileri.
Version: 1.2
Author: Cem Karabulut - Esistenze
*/

// Eklenti güvenliği için doğrudan erişimi engelle
if (!defined('ABSPATH')) {
    exit;
}

// Admin ve Public dosyalarını dahil et
require_once plugin_dir_path(__FILE__) . 'admin.php';
require_once plugin_dir_path(__FILE__) . 'public.php';
?>