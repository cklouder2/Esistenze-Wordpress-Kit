<?php
/*
 * Quick Menu Cards - Card Editor Partial
 * Tek kart düzenleme komponenti
 */

if (!defined('ABSPATH')) {
    exit;
}

// Bu dosya admin-edit.php'den include edilir
// $index ve $kart değişkenleri mevcut olmalı

$title = isset($kart['title']) ? esc_attr($kart['title']) : '';
$desc = isset($kart['desc']) ? esc_textarea($kart['desc']) : '';
$img = isset($kart['img']) ? esc_url($kart['img']) : '';
$url = isset($kart['url']) ? esc_url($kart['url']) : '';
$order = isset($kart['order']) ? intval($kart['order']) : $index;
$enabled = !isset($kart['enabled']) || $kart['enabled']; // Varsayılan true
$featured = !empty($kart['featured']);
$new_tab = !empty($kart['new_tab']);
$created_at = isset($kart['created_at']) ? $kart['created_at'] : current_time('mysql');
$updated_at = isset($kart['updated_at']) ? $kart['updated_at'] : current_time('mysql');

// Unique ID for this card
$card_uid = 'card_' . $index . '_' . uniqid();
?>

<div class="card-editor" data-index="<?php echo $index; ?>" data-card-id="<?php echo $card_uid; ?>">
    <!-- Kart Başlığı -->
    <div class="card-editor-header">
        <div class="card-handle">
            <span class="card-drag-handle dashicons dashicons-sort" title="Sürükleyerek sıralayın"></span>
            <span class="card-number">#<?php echo $index + 1; ?></span>
            <span class="card-title-preview"><?php echo $title ?: 'Yeni Kart'; ?></span>
            <?php if ($featured): ?>
                <span class="featured-badge" title="Öne çıkan kart">⭐</span>
            <?php endif; ?>
            <?php if (!$enabled): ?>
                <span class="disabled-badge" title="Pasif kart">🚫</span>
            <?php endif; ?>
        </div>
        
        <div class="card-controls">
            <label class="card-visibility-toggle" title="Kartı aktif/pasif yap">
                <input type="checkbox" class="card-enabled" <?php checked($enabled); ?>>
                <span class="visibility-icon dashicons <?php echo $enabled ? 'dashicons-visibility' : 'dashicons-hidden'; ?>"></span>
            </label>
            
            <button class="card-action preview-card" type="button" title="Kartı önizle">
                <span class="dashicons dashicons-visibility"></span>
            </button>
            
            <button class="card-action duplicate-card" type="button" title="Kartı kopyala">
                <span class="dashicons dashicons-admin-page"></span>
            </button>
            
            <button class="card-action toggle-card" type="button" title="Kartı aç/kapat">
                <span class="dashicons dashicons-arrow-up-alt2"></span>
            </button>
            
            <button class="card-action remove-card" type="button" title="Kartı sil">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
    </div>
    
    <!-- Kart İçeriği -->
    <div class="card-editor-content">
        <div class="card-form-grid">
            <!-- Sol Kolon: Temel Bilgiler -->
            <div class="form-column form-column-main">
                <!-- Başlık -->
                <div class="field-group field-group-title">
                    <label class="field-label required">
                        <span class="dashicons dashicons-text-page"></span>
                        Kart Başlığı
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="text" 
                           class="card-title widefat" 
                           name="cards[<?php echo $index; ?>][title]"
                           value="<?php echo $title; ?>" 
                           placeholder="Örn: Hizmetlerimiz" 
                           maxlength="100" 
                           required
                           autocomplete="off">
                    <div class="field-meta">
                        <div class="field-help">Kart üzerinde görünecek ana başlık</div>
                        <div class="character-count">
                            <span class="current"><?php echo mb_strlen($title); ?></span>/100
                        </div>
                    </div>
                </div>
                
                <!-- Açıklama -->
                <div class="field-group field-group-description">
                    <label class="field-label">
                        <span class="dashicons dashicons-text"></span>
                        Açıklama
                    </label>
                    <textarea class="card-desc widefat" 
                              name="cards[<?php echo $index; ?>][desc]"
                              placeholder="Kart açıklaması..." 
                              rows="4" 
                              maxlength="500"><?php echo $desc; ?></textarea>
                    <div class="field-meta">
                        <div class="field-help">Kart başlığının altında görünecek açıklama metni</div>
                        <div class="character-count">
                            <span class="current"><?php echo mb_strlen($desc); ?></span>/500
                        </div>
                    </div>
                </div>
                
                <!-- URL -->
                <div class="field-group field-group-url">
                    <label class="field-label">
                        <span class="dashicons dashicons-admin-links"></span>
                        Bağlantı URL
                    </label>
                    <div class="url-input-wrapper">
                        <input type="url" 
                               class="card-url widefat" 
                               name="cards[<?php echo $index; ?>][url]"
                               value="<?php echo $url; ?>" 
                               placeholder="https://example.com"
                               autocomplete="url">
                        <button type="button" class="url-test-button" title="URL'i test et">
                            <span class="dashicons dashicons-external"></span>
                        </button>
                    </div>
                    
                    <!-- URL Önerileri -->
                    <div class="url-suggestions">
                        <div class="url-suggestion-group">
                            <span class="suggestion-label">Hızlı Seçim:</span>
                            <button type="button" class="url-suggestion" data-url="<?php echo home_url(); ?>">
                                🏠 Ana Sayfa
                            </button>
                            <button type="button" class="url-suggestion" data-url="<?php echo home_url('/hakkimizda'); ?>">
                                ℹ️ Hakkımızda
                            </button>
                            <button type="button" class="url-suggestion" data-url="<?php echo home_url('/iletisim'); ?>">
                                📞 İletişim
                            </button>
                            <button type="button" class="url-suggestion" data-url="<?php echo home_url('/hizmetler'); ?>">
                                🔧 Hizmetler
                            </button>
                            <button type="button" class="url-suggestion" data-url="<?php echo home_url('/blog'); ?>">
                                📝 Blog
                            </button>
                        </div>
                    </div>
                    
                    <div class="field-meta">
                        <div class="field-help">Karta tıklandığında gidilecek sayfa (boş bırakılabilir)</div>
                        <!-- URL Doğrulama -->
                        <div class="url-validation" style="display: none;">
                            <span class="validation-icon"></span>
                            <span class="validation-message"></span>
                        </div>
                    </div>
                </div>
                
                <!-- URL Seçenekleri -->
                <div class="field-group field-group-url-options">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" 
                                   class="card-new-tab" 
                                   name="cards[<?php echo $index; ?>][new_tab]"
                                   value="1"
                                   <?php checked($new_tab); ?>>
                            <span class="checkbox-text">
                                <span class="dashicons dashicons-external"></span>
                                Yeni sekmede aç
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Sağ Kolon: Görsel ve Ayarlar -->
            <div class="form-column form-column-media">
                <!-- Görsel Upload -->
                <div class="field-group field-group-image">
                    <label class="field-label">
                        <span class="dashicons dashicons-format-image"></span>
                        Kart Görseli
                    </label>
                    
                    <div class="image-upload-container">
                        <input type="hidden" 
                               class="card-img" 
                               name="cards[<?php echo $index; ?>][img]"
                               value="<?php echo $img; ?>">
                        
                        <div class="image-upload-area" data-card-index="<?php echo $index; ?>">
                            <?php if ($img): ?>
                                <div class="image-preview">
                                    <img src="<?php echo $img; ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                                    <div class="image-overlay">
                                        <button type="button" class="image-action image-edit" title="Görseli değiştir">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button type="button" class="image-action image-fullscreen" title="Tam boyut görüntüle">
                                            <span class="dashicons dashicons-fullscreen-alt"></span>
                                        </button>
                                        <button type="button" class="image-action image-remove" title="Görseli sil">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                    
                                    <!-- Görsel Bilgileri -->
                                    <div class="image-info">
                                        <div class="image-size-info">Boyut kontrol ediliyor...</div>
                                        <div class="image-format-info">
                                            Format: <?php echo strtoupper(pathinfo($img, PATHINFO_EXTENSION) ?: 'Bilinmiyor'); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="upload-placeholder">
                                    <div class="upload-icon">
                                        <span class="dashicons dashicons-plus-alt"></span>
                                    </div>
                                    <div class="upload-text">
                                        <h4>Görsel Ekle</h4>
                                        <p>Sürükle-bırak veya tıkla</p>
                                        <small>JPG, PNG, WebP - Max: 2MB</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Drag & Drop Zone -->
                            <div class="drop-zone <?php echo $img ? 'has-image' : ''; ?>">
                                <div class="drop-zone-content">
                                    <p>Görseli buraya sürükleyip bırakın</p>
                                    <span class="drop-separator">veya</span>
                                    <button type="button" class="upload-image-button button button-secondary">
                                        <span class="dashicons dashicons-upload"></span> 
                                        Medya Kütüphanesinden Seç
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Görsel URL'i Elle Gir -->
                        <details class="manual-url-input">
                            <summary>Görsel URL'i elle gir</summary>
                            <div class="manual-url-content">
                                <input type="url" 
                                       class="manual-image-url widefat" 
                                       placeholder="https://example.com/image.jpg"
                                       value="<?php echo $img; ?>">
                                <button type="button" class="button button-small load-manual-image">Yükle</button>
                            </div>
                        </details>
                    </div>
                    
                    <div class="field-meta">
                        <div class="field-help">
                            📏 <strong>Önerilen boyut:</strong> 300x200px (3:2 oran)<br>
                            📁 <strong>Desteklenen formatlar:</strong> JPG, PNG, WebP<br>
                            📦 <strong>Maksimum boyut:</strong> 2MB
                        </div>
                    </div>
                </div>
                
                <!-- Kart Ayarları -->
                <div class="field-group field-group-settings">
                    <label class="field-label">
                        <span class="dashicons dashicons-admin-settings"></span>
                        Kart Ayarları
                    </label>
                    
                    <div class="settings-grid">
                        <!-- Sıralama -->
                        <div class="setting-item">
                            <label class="setting-label">Sıra:</label>
                            <input type="number" 
                                   class="card-order small-text" 
                                   name="cards[<?php echo $index; ?>][order]"
                                   value="<?php echo $order; ?>" 
                                   min="0" 
                                   step="1"
                                   title="Küçük sayılar önce görüntülenir">
                        </div>
                        
                        <!-- Öne Çıkan -->
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       class="card-featured" 
                                       name="cards[<?php echo $index; ?>][featured]"
                                       value="1"
                                       <?php checked($featured); ?>>
                                <span class="checkbox-text">
                                    <span class="dashicons dashicons-star-filled"></span>
                                    Öne Çıkan
                                </span>
                            </label>
                        </div>
                        
                        <!-- Aktif Durum -->
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       class="card-enabled-main" 
                                       name="cards[<?php echo $index; ?>][enabled]"
                                       value="1"
                                       <?php checked($enabled); ?>>
                                <span class="checkbox-text">
                                    <span class="dashicons dashicons-visibility"></span>
                                    Kart Aktif
                                </span>
                            </label>
                        </div>
                        
                        <!-- Analytics -->
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       class="card-analytics" 
                                       name="cards[<?php echo $index; ?>][analytics]"
                                       value="1"
                                       checked>
                                <span class="checkbox-text">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                    Analytics
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Meta Bilgiler -->
                <div class="field-group field-group-meta">
                    <div class="meta-info">
                        <div class="meta-item">
                            <span class="meta-label">Oluşturulma:</span>
                            <span class="meta-value"><?php echo date('d.m.Y H:i', strtotime($created_at)); ?></span>
                        </div>
                        <?php if ($created_at !== $updated_at): ?>
                        <div class="meta-item">
                            <span class="meta-label">Güncelleme:</span>
                            <span class="meta-value"><?php echo date('d.m.Y H:i', strtotime($updated_at)); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <span class="meta-label">Kart ID:</span>
                            <span class="meta-value"><?php echo $card_uid; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alt Bölüm: Önizleme ve Hızlı İşlemler -->
        <div class="card-footer">
            <!-- Kart Önizlemesi -->
            <div class="card-mini-preview">
                <h4>
                    <span class="dashicons dashicons-visibility"></span>
                    Kart Önizlemesi:
                </h4>
                <div class="mini-preview-container">
                    <div class="mini-preview-card">
                        <div class="mini-preview-image">
                            <?php if ($img): ?>
                                <img src="<?php echo $img; ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="mini-preview-placeholder">
                                    <span class="dashicons dashicons-format-image"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mini-preview-content">
                            <h5 class="mini-preview-title"><?php echo $title ?: 'Kart Başlığı'; ?></h5>
                            <p class="mini-preview-desc"><?php echo wp_trim_words($desc ?: 'Kart açıklaması...', 10); ?></p>
                        </div>
                        <div class="mini-preview-button">
                            <?php 
                            $settings = get_option('esistenze_quick_menu_settings', EsistenzeQuickMenuCards::get_default_settings());
                            echo esc_html($settings['default_button_text'] ?? 'Detayları Gör'); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hızlı İşlemler -->
            <div class="card-quick-actions">
                <h4>
                    <span class="dashicons dashicons-admin-tools"></span>
                    Hızlı İşlemler:
                </h4>
                <div class="quick-action-buttons">
                    <button type="button" class="button button-small preview-card-modal" title="Detaylı önizleme">
                        <span class="dashicons dashicons-visibility"></span>
                        Önizle
                    </button>
                    
                    <button type="button" class="button button-small duplicate-card-action" title="Bu kartı kopyala">
                        <span class="dashicons dashicons-admin-page"></span>
                        Kopyala
                    </button>
                    
                    <button type="button" class="button button-small reset-card" title="Kartı sıfırla">
                        <span class="dashicons dashicons-undo"></span>
                        Sıfırla
                    </button>
                    
                    <button type="button" class="button button-small button-link-delete remove-card-confirm" title="Kartı sil">
                        <span class="dashicons dashicons-trash"></span>
                        Sil
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden Fields -->
    <input type="hidden" name="cards[<?php echo $index; ?>][created_at]" value="<?php echo esc_attr($created_at); ?>">
    <input type="hidden" name="cards[<?php echo $index; ?>][updated_at]" value="<?php echo esc_attr(current_time('mysql')); ?>">
    <input type="hidden" class="card-index" value="<?php echo $index; ?>">
</div>

<!-- JavaScript için template -->
<script type="text/template" id="card-preview-template-<?php echo $index; ?>">
    <div class="card-preview-modal">
        <div class="preview-content">
            <div class="preview-types">
                <button class="preview-type active" data-type="grid">Izgara Görünüm</button>
                <button class="preview-type" data-type="banner">Banner Görünüm</button>
            </div>
            <div class="preview-output"></div>
        </div>
    </div>
</script>