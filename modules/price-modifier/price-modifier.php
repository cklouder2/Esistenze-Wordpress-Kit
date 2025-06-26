<?php
/*
 * Price Modifier Module
 * Part of Esistenze WordPress Kit
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Price Modifier Module
 * Part of Esistenze WordPress Kit
 */
class EsistenzePriceModifier extends EsistenzeBaseModule {
    
    /**
     * Get module name
     * @return string
     */
    protected function getModuleName(): string {
        return 'price-modifier';
    }
    
    /**
     * Get settings option name
     * @return string
     */
    protected function getSettingsOptionName(): string {
        return 'esistenze_price_modifier_settings';
    }
    
    /**
     * Get default settings
     * @return array
     */
    protected function getDefaultSettings(): array {
        return [
            'enabled' => true,
            'price_note' => '1000 adet için liste fiyatıdır. Özel fiyat için bizimle iletişime geçin.',
            'note_color' => '#e74c3c',
            'bg_color' => '#e6f3e6',
            'border_color' => '#4CAF50'
        ];
    }
    
    /**
     * Initialize module
     * @return void
     */
    public function init(): void {
        // Admin hooks
        $this->addAction('admin_init', [$this, 'registerSettings']);
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Frontend hooks
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueueStyles'], 999);
        
        // Replace default price with custom price
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
        $this->addAction('woocommerce_single_product_summary', [$this, 'customPriceWithNote'], 10);
    }
    
    /**
     * Register admin menus
     * @return void
     */
    public function registerAdminMenus(): void {
        $this->registerAdminSubmenu(
            'Price Modifier',
            'Price Modifier',
            'esistenze-price-modifier',
            [$this, 'adminPage']
        );
    }
    
    /**
     * Render admin page
     * @return void
     */
    public function adminPage(): void {
        if (!$this->canAccessAdmin()) {
            $this->denyAccess();
        }
        
        $settings = $this->getSettings($this->settingsOptionName, $this->getDefaultSettings());
        
        if (isset($_POST['submit']) && $this->verifyNonce('price_modifier_save')) {
            $new_settings = [
                'enabled' => isset($_POST['enable_price_modifier']),
                'price_note' => sanitize_textarea_field($_POST['price_note']),
                'note_color' => sanitize_hex_color($_POST['note_color']),
                'bg_color' => sanitize_hex_color($_POST['bg_color']),
                'border_color' => sanitize_hex_color($_POST['border_color'])
            ];
            
            if ($this->updateSettings($this->settingsOptionName, $new_settings)) {
                $this->showAdminNotice(__('Ayarlar kaydedildi!', 'esistenze-wp-kit'));
                $settings = $new_settings;
            }
        }
        
        $this->renderAdminHeader(__('Price Modifier Ayarları', 'esistenze-wp-kit'));
        ?>
        <p><?php _e('WooCommerce ürün fiyatlarına özel notlar ve stiller ekler.', 'esistenze-wp-kit'); ?></p>
        
        <form method="post" action="">
            <?php wp_nonce_field('price_modifier_save'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Price Modifier\'ı Etkinleştir', 'esistenze-wp-kit'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_price_modifier" <?php checked($settings['enabled']); ?> />
                            <?php _e('Fiyat modifikasyonunu etkinleştir', 'esistenze-wp-kit'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="price_note"><?php _e('Fiyat Notu', 'esistenze-wp-kit'); ?></label></th>
                    <td>
                        <textarea name="price_note" id="price_note" rows="3" class="large-text"><?php echo esc_textarea($settings['price_note']); ?></textarea>
                        <p class="description"><?php _e('Fiyatın yanında gösterilecek özel not.', 'esistenze-wp-kit'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="note_color"><?php _e('Not Rengi', 'esistenze-wp-kit'); ?></label></th>
                    <td>
                        <input type="color" name="note_color" id="note_color" value="<?php echo esc_attr($settings['note_color']); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bg_color"><?php _e('Arka Plan Rengi', 'esistenze-wp-kit'); ?></label></th>
                    <td>
                        <input type="color" name="bg_color" id="bg_color" value="<?php echo esc_attr($settings['bg_color']); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="border_color"><?php _e('Çerçeve Rengi', 'esistenze-wp-kit'); ?></label></th>
                    <td>
                        <input type="color" name="border_color" id="border_color" value="<?php echo esc_attr($settings['border_color']); ?>">
                    </td>
                </tr>
            </table>
            
            <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                <h3><?php _e('Önizleme:', 'esistenze-wp-kit'); ?></h3>
                <div style="display: flex; align-items: center; gap: 20px; background: <?php echo esc_attr($settings['bg_color']); ?>; padding: 15px 20px; border-radius: 10px; border: 2px solid <?php echo esc_attr($settings['border_color']); ?>;">
                    <span style="font-size: 1.8em; color: #2c3e50; font-weight: 700;">₺199.90</span>
                    <span style="font-size: 14px; color: <?php echo esc_attr($settings['note_color']); ?>; background-color: #ffffff; padding: 10px 15px; border-radius: 6px; border-left: 6px solid <?php echo esc_attr($settings['note_color']); ?>;"><?php echo esc_html($settings['price_note']); ?></span>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>
        <?php
        $this->renderAdminFooter();
    }
    
    /**
     * Register plugin settings
     * @return void
     */
    public function registerSettings(): void {
        register_setting('esistenze_price_modifier', $this->settingsOptionName);
    }
    
    /**
     * Enqueue frontend styles and dynamic CSS
     * @return void
     */
    public function enqueueStyles(): void {
        $settings = $this->getSettings($this->settingsOptionName, $this->getDefaultSettings());
        
        if (!$settings['enabled']) {
            return;
        }
        
        $custom_css = sprintf('
            .esistenze-price-modifier-wrapper {
                display: flex;
                align-items: center;
                gap: 20px;
                margin: 20px 0;
                background: %s;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                border: 2px solid %s;
            }
            .esistenze-price-modifier-price {
                font-size: 1.8em;
                color: #2c3e50;
                font-weight: 700;
                font-family: "Montserrat", sans-serif;
            }
            .esistenze-price-modifier-note {
                font-size: 14px;
                color: %s;
                background-color: #ffffff;
                padding: 10px 15px;
                border-radius: 6px;
                font-family: "Roboto", sans-serif;
                font-weight: 500;
                border-left: 6px solid %s;
                box-shadow: 0 2px 10px rgba(%s, 0.1);
                transition: all 0.3s ease;
            }
            .esistenze-price-modifier-note:hover {
                background-color: #ffebee;
                transform: translateY(-2px);
            }
            .woocommerce-product-details__short-description {
                margin-top: 20px;
                font-size: 1.1em;
                color: #7f8c8d;
                line-height: 1.6;
            }
            @media (max-width: 768px) {
                .esistenze-price-modifier-wrapper {
                    flex-direction: column;
                    text-align: center;
                    gap: 15px;
                }
                .esistenze-price-modifier-price {
                    font-size: 1.5em;
                }
                .esistenze-price-modifier-note {
                    font-size: 13px;
                }
            }
        ', 
        esc_attr($settings['bg_color']),
        esc_attr($settings['border_color']),
        esc_attr($settings['note_color']),
        esc_attr($settings['note_color']),
        $this->hexToRgb($settings['note_color'])
        );
        
        wp_add_inline_style('woocommerce-inline', $custom_css);
    }

    /**
     * Output custom price with note
     * @return void
     */
    public function customPriceWithNote(): void {
        try {
            $settings = $this->getSettings($this->settingsOptionName, $this->getDefaultSettings());
            
            if (!$settings['enabled']) {
                if (function_exists('woocommerce_template_single_price')) {
                    woocommerce_template_single_price();
                }
                return;
            }
            
            global $product;
            if ($product && method_exists($product, 'get_price') && $product->get_price()) {
                $price_html = function_exists('wc_price') ? wc_price($product->get_price()) : '$' . $product->get_price();
                
                echo '<div class="esistenze-price-modifier-wrapper">';
                echo '<span class="esistenze-price-modifier-price">' . wp_kses_post($price_html) . '</span>';
                echo '<span class="esistenze-price-modifier-note">' . esc_html($settings['price_note']) . '</span>';
                echo '</div>';
            } else if (function_exists('woocommerce_template_single_price')) {
                woocommerce_template_single_price();
            }
        } catch (Exception $e) {
            // Hata durumunda standart fiyatı göster
            if (function_exists('woocommerce_template_single_price')) {
                woocommerce_template_single_price();
            }
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

// Initialize the module
if (class_exists('EsistenzeBaseModule')) {
    EsistenzePriceModifier::getInstance();
}