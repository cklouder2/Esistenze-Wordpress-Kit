<?php

if (!defined('ABSPATH')) exit;

class Smart_Product_Buttons_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_post_smart_button_save', array($this, 'save_button'));
        add_action('admin_post_smart_button_delete', array($this, 'delete_button'));
    }

    public function admin_menu() {
        add_menu_page('Smart Buttons', 'Smart Buttons', 'manage_options', 'smart-product-buttons', array($this, 'settings_page'));
    }

    public function settings_page() {
        $buttons = get_option('smart_custom_buttons', []);
        echo '<div class="wrap">';
        echo '<h1>Smart Product Buttons</h1>';
        echo '<a href="' . admin_url('admin.php?page=smart-product-buttons&new=1') . '" class="button-primary">Yeni Buton Ekle</a>';
        if (isset($_GET['edit']) && isset($buttons[$_GET['edit']])) {
            $this->render_button_form($buttons[$_GET['edit']], $_GET['edit']);
        } elseif (isset($_GET['new'])) {
            $this->render_button_form([], null);
        } else {
            echo '<table class="widefat smart-button-table"><thead><tr><th>Başlık</th><th>Tür</th><th>Grup</th><th>İşlem</th></tr></thead><tbody class="sortable-button-list">';
            foreach ($buttons as $index => $button) {
                echo '<tr>';
                echo '<td>' . esc_html($button['title'] ?? '-') . '</td>';
                echo '<td>' . esc_html($button['type'] ?? '-') . '</td>';
                echo '<td>' . esc_html($button['group'] ?? '-') . '</td>';
                echo '<td>
                        <a class="button" href="' . admin_url('admin.php?page=smart-product-buttons&edit=' . $index) . '">Düzenle</a>
                        <a class="button delete-button" href="' . wp_nonce_url(admin_url('admin-post.php?action=smart_button_delete&index=' . $index), 'smart_button_delete_' . $index) . '" onclick="return confirm(\'Silmek istediğinize emin misiniz?\')">Sil</a>
                    </td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        echo '</div>';
    }

    private function render_button_form($button, $index = null) {
        $action = admin_url('admin-post.php');
        ?>
        <form method="post" action="<?php echo esc_url($action); ?>">
            <input type="hidden" name="action" value="smart_button_save">
            <?php if ($index !== null): ?><input type="hidden" name="button_index" value="<?php echo esc_attr($index); ?>"><?php endif; ?>
            <?php wp_nonce_field('smart_button_save'); ?>
            <table class="form-table smart-button-table">
                <tr><th scope="row">Grup</th><td><input name="group" type="text" value="<?php echo esc_attr($button['group'] ?? ''); ?>" /></td></tr>
                <tr><th scope="row">Tür</th><td>
                    <select name="type">
                        <option value="phone" <?php selected($button['type'] ?? '', 'phone'); ?>>Telefon</option>
                        <option value="mail" <?php selected($button['type'] ?? '', 'mail'); ?>>Mail</option>
                        <option value="whatsapp" <?php selected($button['type'] ?? '', 'whatsapp'); ?>>WhatsApp</option>
                        <option value="add_to_cart" <?php selected($button['type'] ?? '', 'add_to_cart'); ?>>Sepete Ekle</option>
                        <option value="form_trigger" <?php selected($button['type'] ?? '', 'form_trigger'); ?>>Form</option>
                    </select>
                </td></tr>
                <tr><th scope="row">Başlık</th><td><input name="title" type="text" value="<?php echo esc_attr($button['title'] ?? ''); ?>" required /></td></tr>
                <tr><th scope="row">Contact Form 7 ID</th><td><input name="value" type="text" value="<?php echo esc_attr($button['value'] ?? ''); ?>" placeholder="Örnek: 61def4f" required /></td></tr>
                <tr><th scope="row">Mesaj</th><td><input name="message" type="text" value="<?php echo esc_attr($button['message'] ?? ''); ?>" /></td></tr>
                <tr><th scope="row">Renk 1</th><td><input name="button_color_start" type="color" value="<?php echo esc_attr($button['button_color_start'] ?? '#000000'); ?>" required /></td></tr>
                <tr><th scope="row">Renk 2</th><td><input name="button_color_end" type="color" value="<?php echo esc_attr($button['button_color_end'] ?? '#000000'); ?>" required /></td></tr>
                <tr><th scope="row">Yazı Rengi</th><td><input name="text_color" type="color" value="<?php echo esc_attr($button['text_color'] ?? '#ffffff'); ?>" required /></td></tr>
                <tr><th scope="row">İkon</th><td><input name="icon" type="text" value="<?php echo esc_attr($button['icon'] ?? ''); ?>" placeholder="fa-phone" /></td></tr>
                <tr><th scope="row">Font</th><td>
                    <select name="font_size">
                        <?php for ($fs = 11; $fs <= 40; $fs++): ?>
                            <option value="<?php echo $fs; ?>" <?php selected($button['font_size'] ?? '', $fs); ?>><?php echo $fs; ?>px</option>
                        <?php endfor; ?>
                    </select>
                </td></tr>
                <tr><th scope="row">Takip Adı</th><td><input name="tracking_name" type="text" value="<?php echo esc_attr($button['tracking_name'] ?? ''); ?>" /></td></tr>
            </table>
            <p class="submit"><input type="submit" class="button-primary" value="Kaydet"></p>
        </form>
        <?php
    }

    public function save_button() {
        if (!current_user_can('manage_options') || !check_admin_referer('smart_button_save')) {
            wp_die('Yetkiniz yok.');
        }

        $buttons = get_option('smart_custom_buttons', []);
        $button_data = array(
            'group' => sanitize_text_field($_POST['group'] ?? ''),
            'type' => sanitize_text_field($_POST['type'] ?? ''),
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'value' => sanitize_text_field($_POST['value'] ?? ''),
            'message' => sanitize_text_field($_POST['message'] ?? ''),
            'button_color_start' => sanitize_hex_color($_POST['button_color_start'] ?? '#000000'),
            'button_color_end' => sanitize_hex_color($_POST['button_color_end'] ?? '#000000'),
            'text_color' => sanitize_hex_color($_POST['text_color'] ?? '#ffffff'),
            'icon' => sanitize_text_field($_POST['icon'] ?? ''),
            'font_size' => intval($_POST['font_size'] ?? 14),
            'tracking_name' => sanitize_text_field($_POST['tracking_name'] ?? ''),
        );

        $existing_form_triggers = array_filter($buttons, function($btn) {
            return $btn['type'] === 'form_trigger';
        });
        if ($button_data['type'] === 'form_trigger' && count($existing_form_triggers) > 0 && !isset($_POST['button_index'])) {
            $button_data['type'] = 'phone';
        }

        if (isset($_POST['button_index'])) {
            $buttons[$_POST['button_index']] = $button_data;
        } else {
            $buttons[] = $button_data;
        }

        update_option('smart_custom_buttons', $buttons);
        wp_redirect(admin_url('admin.php?page=smart-product-buttons'));
        exit;
    }

    public function delete_button() {
        if (!current_user_can('manage_options') || !isset($_GET['index']) || !check_admin_referer('smart_button_delete_' . $_GET['index'])) {
            wp_die('Yetkiniz yok.');
        }
        $buttons = get_option('smart_custom_buttons', []);
        unset($buttons[$_GET['index']]);
        update_option('smart_custom_buttons', array_values($buttons));
        wp_redirect(admin_url('admin.php?page=smart-product-buttons'));
        exit;
    }
}