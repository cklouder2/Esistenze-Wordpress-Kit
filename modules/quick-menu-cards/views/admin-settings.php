<?php
/*
 * Quick Menu Cards - Admin Settings View
 * Ayarlar sayfası
 */

if (!defined('ABSPATH')) {
    exit;
}

// Varsayılan ayarları al
$defaults = EsistenzeQuickMenuCards::get_default_settings();
?>

<div class="wrap esistenze-settings-wrap">
    <h1>Quick Menu Cards - Ayarlar</h1>
    
    <form method="post" action="" class="esistenze-settings-form">
        <?php wp_nonce_field('esistenze_quick_menu_settings_save'); ?>
        
        <div class="settings-tabs">
            <nav class="settings-nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active" data-tab="general">
                    <span class="dashicons dashicons-admin-generic"></span>
                    Genel Ayarlar
                </a>
                <a href="#appearance" class="nav-tab" data-tab="appearance">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    Görünüm
                </a>
                <a href="#performance" class="nav-tab" data-tab="performance">
                    <span class="dashicons dashicons-performance"></span>
                    Performans
                </a>
                <a href="#analytics" class="nav-tab" data-tab="analytics">
                    <span class="dashicons dashicons-chart-bar"></span>
                    Analytics
                </a>
                <a href="#advanced" class="nav-tab" data-tab="advanced">
                    <span class="dashicons dashicons-admin-tools"></span>
                    Gelişmiş
                </a>
            </nav>
        </div>

        <div class="settings-content">
            <!-- Genel Ayarlar Tab -->
            <div id="general" class="settings-tab-content active">
                <h2>Genel Ayarlar</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="default_button_text">Varsayılan Buton Metni</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="default_button_text" 
                                   name="settings[default_button_text]" 
                                   value="<?php echo esc_attr($settings['default_button_text'] ?? $defaults['default_button_text']); ?>" 
                                   class="regular-text" 
                                   placeholder="<?php echo esc_attr($defaults['default_button_text']); ?>">
                            <p class="description">Kart görünümünde kullanılacak varsayılan buton metni.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="banner_button_text">Banner Buton Metni</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="banner_button_text" 
                                   name="settings[banner_button_text]" 
                                   value="<?php echo esc_attr($settings['banner_button_text'] ?? $defaults['banner_button_text']); ?>" 
                                   class="regular-text" 
                                   placeholder="<?php echo esc_attr($defaults['banner_button_text']); ?>">
                            <p class="description">Banner görünümünde kullanılacak varsayılan buton metni.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Görünüm Tab -->
            <div id="appearance" class="settings-tab-content">
                <h2>Görünüm Ayarları</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="mobile_columns">Mobil Sütun Sayısı</label>
                        </th>
                        <td>
                            <select id="mobile_columns" name="settings[mobile_columns]">
                                <option value="1" <?php selected($settings['mobile_columns'] ?? $defaults['mobile_columns'], 1); ?>>1 Sütun</option>
                                <option value="2" <?php selected($settings['mobile_columns'] ?? $defaults['mobile_columns'], 2); ?>>2 Sütun</option>
                                <option value="3" <?php selected($settings['mobile_columns'] ?? $defaults['mobile_columns'], 3); ?>>3 Sütun</option>
                            </select>
                            <p class="description">Mobil cihazlarda kaç sütun gösterileceğini belirler.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="enable_dark_mode">Dark Mode Desteği</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           id="enable_dark_mode" 
                                           name="settings[enable_dark_mode]" 
                                           value="1" 
                                           <?php checked(!empty($settings['enable_dark_mode'])); ?>>
                                    Otomatik dark mode desteğini etkinleştir
                                </label>
                                <p class="description">Kullanıcının sistem tercihine göre otomatik dark mode.</p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="custom_css">Özel CSS</label>
                        </th>
                        <td>
                            <textarea id="custom_css" 
                                      name="settings[custom_css]" 
                                      rows="10" 
                                      class="large-text code"
                                      placeholder="/* Özel CSS kodlarınızı buraya yazın */"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                            <p class="description">
                                Kartlarınızın görünümünü özelleştirmek için CSS kodu ekleyebilirsiniz.<br>
                                <strong>CSS Sınıfları:</strong> .esistenze-quick-menu-wrapper, .esistenze-quick-menu-kart, .esistenze-quick-menu-banner
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Performans Tab -->
            <div id="performance" class="settings-tab-content">
                <h2>Performans Ayarları</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_lazy_loading">Lazy Loading</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           id="enable_lazy_loading" 
                                           name="settings[enable_lazy_loading]" 
                                           value="1" 
                                           <?php checked(!empty($settings['enable_lazy_loading'])); ?>>
                                    Görseller için lazy loading'i etkinleştir
                                </label>
                                <p class="description">Sayfa yükleme hızını artırır. Önerilir.</p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cache_duration">Cache Süresi</label>
                        </th>
                        <td>
                            <select id="cache_duration" name="settings[cache_duration]">
                                <option value="0" <?php selected($settings['cache_duration'] ?? $defaults['cache_duration'], 0); ?>>Devre Dışı</option>
                                <option value="1800" <?php selected($settings['cache_duration'] ?? $defaults['cache_duration'], 1800); ?>>30 Dakika</option>
                                <option value="3600" <?php selected($settings['cache_duration'] ?? $defaults['cache_duration'], 3600); ?>>1 Saat</option>
                                <option value="7200" <?php selected($settings['cache_duration'] ?? $defaults['cache_duration'], 7200); ?>>2 Saat</option>
                                <option value="21600" <?php selected($settings['cache_duration'] ?? $defaults['cache_duration'], 21600); ?>>6 Saat</option>
                                <option value="86400" <?php selected($settings['cache_duration'] ?? $defaults['cache_duration'], 86400); ?>>24 Saat</option>
                            </select>
                            <p class="description">Kartların ne kadar süreyle cache'de tutulacağını belirler.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="enable_gpu_acceleration">GPU Acceleration</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           id="enable_gpu_acceleration" 
                                           name="settings[enable_gpu_acceleration]" 
                                           value="1" 
                                           <?php checked(!empty($settings['enable_gpu_acceleration'])); ?>>
                                    CSS animasyonları için GPU acceleration kullan
                                </label>
                                <p class="description">Daha smooth animasyonlar için GPU'yu kullanır.</p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Analytics Tab -->
            <div id="analytics" class="settings-tab-content">
                <h2>Analytics Ayarları</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_analytics">Analytics</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           id="enable_analytics" 
                                           name="settings[enable_analytics]" 
                                           value="1" 
                                           <?php checked(!empty($settings['enable_analytics'])); ?>>
                                    Tıklama ve görüntülenme istatistiklerini topla
                                </label>
                                <p class="description">Hangi kartların daha popüler olduğunu görmek için analytics'i etkinleştirin.</p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="track_user_data">Kullanıcı Verisi</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           id="track_user_data" 
                                           name="settings[track_user_data]" 
                                           value="1" 
                                           <?php checked(!empty($settings['track_user_data'])); ?>>
                                    IP adresi ve tarayıcı bilgilerini kaydet
                                </label>
                                <p class="description">
                                    <strong>Dikkat:</strong> GDPR/KVKK uyumluluğu için kullanıcılardan izin almanız gerekebilir.
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="analytics_retention">Veri Saklama Süresi</label>
                        </th>
                        <td>
                            <select id="analytics_retention" name="settings[analytics_retention]">
                                <option value="30" <?php selected($settings['analytics_retention'] ?? 90, 30); ?>>30 Gün</option>
                                <option value="90" <?php selected($settings['analytics_retention'] ?? 90, 90); ?>>90 Gün</option>
                                <option value="180" <?php selected($settings['analytics_retention'] ?? 90, 180); ?>>6 Ay</option>
                                <option value="365" <?php selected($settings['analytics_retention'] ?? 90, 365); ?>>1 Yıl</option>
                            </select>
                            <p class="description">Analytics verilerinin ne kadar süre saklanacağını belirler.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Gelişmiş Tab -->
            <div id="advanced" class="settings-tab-content">
                <h2>Gelişmiş Ayarlar</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_schema_markup">Schema Markup</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           id="enable_schema_markup" 
                                           name="settings[enable_schema_markup]" 
                                           value="1" 
                                           <?php checked(!empty($settings['enable_schema_markup'])); ?>>
                                    SEO için schema markup ekle
                                </label>
                                <p class="description">Arama motorları için yapılandırılmış veri ekler.</p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="max_cards_per_group">Grup Başına Max Kart</label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="max_cards_per_group" 
                                   name="settings[max_cards_per_group]" 
                                   value="<?php echo esc_attr($settings['max_cards_per_group'] ?? 20); ?>" 
                                   min="1" 
                                   max="100" 
                                   class="small-text">
                            <p class="description">Bir grupta maksimum kaç kart olabileceğini belirler.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="auto_save">Otomatik Kaydet</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           id="auto_save" 
                                           name="settings[auto_save]" 
                                           value="1" 
                                           <?php checked(!empty($settings['auto_save'])); ?>>
                                    Düzenleme sırasında otomatik kaydet
                                </label>
                                <p class="description">Her 30 saniyede bir otomatik olarak değişiklikleri kaydeder.</p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="debug_mode">Debug Modu</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           id="debug_mode" 
                                           name="settings[debug_mode]" 
                                           value="1" 
                                           <?php checked(!empty($settings['debug_mode'])); ?>>
                                    Debug bilgilerini göster
                                </label>
                                <p class="description">Geliştirici araçları ve hata ayıklama bilgileri.</p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Kaydet Butonu -->
        <div class="settings-footer">
            <?php submit_button('Ayarları Kaydet', 'primary', 'submit', false, array('id' => 'submit-settings')); ?>
            
            <button type="button" class="button button-secondary" id="reset-defaults">
                Varsayılanlara Sıfırla
            </button>
            
            <div class="settings-info">
                <p>
                    <strong>Not:</strong> Ayarları değiştirdikten sonra cache'i temizlemeniz önerilir.
                    <a href="<?php echo admin_url('admin.php?page=esistenze-quick-menu-tools'); ?>">Araçlar sayfasından</a> cache'i temizleyebilirsiniz.
                </p>
            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.settings-nav-tab-wrapper .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        
        // Remove active class from all tabs and content
        $('.nav-tab').removeClass('nav-tab-active');
        $('.settings-tab-content').removeClass('active');
        
        // Add active class to clicked tab and corresponding content
        $(this).addClass('nav-tab-active');
        $('#' + targetTab).addClass('active');
        
        // Update URL hash
        window.location.hash = targetTab;
    });
    
    // Check for hash in URL on page load
    if (window.location.hash) {
        var hash = window.location.hash.substring(1);
        $('[data-tab="' + hash + '"]').trigger('click');
    }
    
    // Reset to defaults
    $('#reset-defaults').on('click', function() {
        if (confirm('Tüm ayarları varsayılan değerlerine sıfırlamak istediğinizden emin misiniz?')) {
            // Reset form fields to default values
            $('#default_button_text').val('<?php echo esc_js($defaults['default_button_text']); ?>');
            $('#banner_button_text').val('<?php echo esc_js($defaults['banner_button_text']); ?>');
            $('#mobile_columns').val('<?php echo esc_js($defaults['mobile_columns'] ?? 1); ?>');
            $('#cache_duration').val('<?php echo esc_js($defaults['cache_duration'] ?? 3600); ?>');
            $('#analytics_retention').val('90');
            $('#max_cards_per_group').val('20');
            $('#custom_css').val('');
            
            // Reset checkboxes
            $('input[type="checkbox"]').prop('checked', function() {
                var name = $(this).attr('name');
                if (name === 'settings[enable_analytics]' || 
                    name === 'settings[enable_lazy_loading]' || 
                    name === 'settings[enable_schema_markup]') {
                    return true;
                }
                return false;
            });
        }
    });
    
    // Unsaved changes warning
    var formChanged = false;
    $('.esistenze-settings-form input, .esistenze-settings-form select, .esistenze-settings-form textarea').on('change', function() {
        formChanged = true;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'Kaydedilmemiş değişiklikleriniz var. Sayfadan çıkmak istediğinizden emin misiniz?';
        }
    });
    
    $('.esistenze-settings-form').on('submit', function() {
        formChanged = false;
    });
});
</script>

<style>
.esistenze-settings-wrap {
    max-width: 1200px;
}

.settings-tabs {
    margin: 20px 0;
}

.settings-nav-tab-wrapper {
    border-bottom: 1px solid #ccd0d4;
    background: #fff;
    padding: 0;
}

.settings-nav-tab-wrapper .nav-tab {
    font-size: 14px;
    padding: 12px 16px;
    margin: 0;
    border: none;
    border-bottom: 3px solid transparent;
    background: transparent;
    color: #646970;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.settings-nav-tab-wrapper .nav-tab:hover {
    background: #f6f7f7;
    color: #135e96;
}

.settings-nav-tab-wrapper .nav-tab-active {
    color: #135e96;
    border-bottom-color: #135e96;
    background: #f6f7f7;
}

.settings-content {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-top: none;
    padding: 20px;
}

.settings-tab-content {
    display: none;
}

.settings-tab-content.active {
    display: block;
}

.settings-tab-content h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.settings-footer {
    background: #f6f7f7;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-top: none;
    display: flex;
    align-items: center;
    gap: 10px;
}

.settings-info {
    margin-left: auto;
    font-size: 13px;
    color: #646970;
}

.form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
    vertical-align: top;
}

.form-table td {
    padding: 15px 10px;
}

.form-table .description {
    margin-top: 5px;
    font-size: 13px;
    color: #646970;
}

#custom_css {
    font-family: Consolas, Monaco, monospace;
    font-size: 13px;
}
</style><?php
/* End of admin-settings.php */
?>