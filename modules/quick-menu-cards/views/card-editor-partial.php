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
$desc = isset($kart['desc']) ? esc_attr($kart['desc']) : '';
$img = isset($kart['img']) ? esc_url($kart['img']) : '';
$url = isset($kart['url']) ? esc_url($kart['url']) : '';
$color = isset($kart['color']) ? esc_attr($kart['color']) : '#5ea226';
$order = isset($kart['order']) ? intval($kart['order']) : $index;
$featured = !empty($kart['featured']);
$new_tab = !empty($kart['new_tab']);
$enabled = !isset($kart['enabled']) || $kart['enabled']; // Varsayılan true
?>

<div class="card-editor" data-index="<?php echo $index; ?>">
    <!-- Kart Başlığı -->
    <div class="card-editor-header">
        <div class="card-handle">
            <span class="card-drag-handle dashicons dashicons-menu-alt" title="Sürükleyerek sıralayın"></span>
            <span class="card-number">Kart #<?php echo $index + 1; ?></span>
            <?php if ($featured): ?>
                <span class="featured-badge" title="Öne çıkan kart">⭐</span>
            <?php endif; ?>
        </div>
        <div class="card-controls">
            <label class="card-visibility" title="Kartı aktif/pasif yap">
                <input type="checkbox" class="card-enabled" <?php checked($enabled); ?>>
                <span class="visibility-icon dashicons <?php echo $enabled ? 'dashicons-visibility' : 'dashicons-hidden'; ?>"></span>
            </label>
            <button class="duplicate-card" type="button" title="Kartı kopyala">
                <span class="dashicons dashicons-admin-page"></span>
            </button>
            <button class="remove-card" type="button" title="Kartı sil">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
    </div>
    
    <!-- Kart İçeriği -->
    <div class="card-editor-content">
        <div class="card-form-grid">
            <!-- Sol Kolon: Temel Bilgiler -->
            <div class="form-column">
                <div class="field-group">
                    <label class="field-label required">
                        <span class="dashicons dashicons-text"></span>
                        Kart Başlığı
                    </label>
                    <input type="text" 
                           class="card-title" 
                           value="<?php echo $title; ?>" 
                           placeholder="Örn: Hizmetlerimiz" 
                           maxlength="100" 
                           required>
                    <div class="field-help">Kart üzerinde görünecek ana başlık</div>
                    <div class="character-count">
                        <span class="current"><?php echo strlen($title); ?></span>/100
                    </div>
                </div>
                
                <div class="field-group">
                    <label class="field-label">
                        <span class="dashicons dashicons-text-page"></span>
                        Açıklama
                    </label>
                    <textarea class="card-desc" 
                              placeholder="Kart açıklaması..." 
                              rows="3" 
                              maxlength="500"><?php echo $desc; ?></textarea>
                    <div class="field-help">Kart başlığının altında görünecek açıklama metni</div>
                    <div class="character-count">
                        <span class="current"><?php echo strlen($desc); ?></span>/500
                    </div>
                </div>
                
                <div class="field-group">
                    <label class="field-label">
                        <span class="dashicons dashicons-admin-links"></span>
                        Bağlantı URL
                    </label>
                    <input type="url" 
                           class="card-url" 
                           value="<?php echo $url; ?>" 
                           placeholder="https://example.com">
                    
                    <!-- URL Önerileri -->
                    <div class="url-suggestions">
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
                    
                    <div class="field-help">Karta tıklandığında gidilecek sayfa (boş bırakılabilir)</div>
                    
                    <!-- URL Doğrulama -->
                    <div class="url-validation" style="display: none;">
                        <span class="validation-icon"></span>
                        <span class="validation-message"></span>
                    </div>
                </div>
            </div>
            
            <!-- Sağ Kolon: Görsel ve Ayarlar -->
            <div class="form-column">
                <div class="field-group">
                    <label class="field-label">
                        <span class="dashicons dashicons-format-image"></span>
                        Kart Görseli
                    </label>
                    <div class="image-upload-container">
                        <input type="hidden" class="card-img" value="<?php echo $img; ?>">
                        <div class="image-upload-area">
                            <div class="image-preview">
                                <?php if ($img): ?>
                                    <img src="<?php echo $img; ?>" alt="<?php echo esc_attr($title); ?>">
                                    <div class="image-overlay">
                                        <button type="button" class="image-edit" title="Görseli değiştir">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button type="button" class="image-remove" title="Görseli sil">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="upload-placeholder">
                                        <span class="dashicons dashicons-plus-alt"></span>
                                        <p>Görsel Ekle</p>
                                        <small>Sürükle-bırak veya tıkla</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Drag & Drop Zone -->
                            <div class="drop-zone" style="<?php echo $img ? 'display:none;' : ''; ?>">
                                <p>Görseli buraya sürükleyip bırakın</p>
                                <span>veya</span>
                                <button type="button" class="upload-image-button button">
                                    <span class="dashicons dashicons-upload"></span> Görsel Seç
                                </button>
                            </div>
                        </div>
                        
                        <!-- Görsel Bilgileri -->
                        <?php if ($img): ?>
                            <div class="image-info">
                                <div class="image-size">Boyut kontrol ediliyor...</div>
                                <div class="image-format">Format: <?php echo strtoupper(pathinfo($img, PATHINFO_EXTENSION)); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="field-help">
                        📏 Önerilen boyut: 300x200px<br>
                        📁 Format: JPG, PNG, WebP<br>
                        📦 Maksimum boyut: 2MB
                    </div>
                </div>
                
                <div class="field-group">
                    <label class="field-label">
                        <span class="dashicons dashicons-admin-appearance"></span>
                        Kart Teması
                    </label>
                    <div class="color-picker-container">
                        <input type="color" class="card-color" value="<?php echo $color; ?>">
                        
                        <!-- Renk Önizlemesi -->
                        <div class="color-preview" style="background-color: <?php echo $color; ?>;">
                            <span class="color-value"><?php echo $color; ?></span>
                        </div>
                        
                        <!-- Önceden Tanımlı Renkler -->
                        <div class="color-presets">
                            <button type="button" class="color-preset" data-color="#5ea226" style="background:#5ea226" title="Varsayılan Yeşil" data-name="Yeşil"></button>
                            <button type="button" class="color-preset" data-color="#0073aa" style="background:#0073aa" title="WordPress Mavi" data-name="Mavi"></button>
                            <button type="button" class="color-preset" data-color="#d63638" style="background:#d63638" title="Kırmızı" data-name="Kırmızı"></button>
                            <button type="button" class="color-preset" data-color="#ff9800" style="background:#ff9800" title="Turuncu" data-name="Turuncu"></button>
                            <button type="button" class="color-preset" data-color="#9c27b0" style="background:#9c27b0" title="Mor" data-name="Mor"></button>
                            <button type="button" class="color-preset" data-color="#607d8b" style="background:#607d8b" title="Gri" data-name="Gri"></button>
                        </div>
                        
                        <!-- Gradyan Seçenekleri -->
                        <details class="gradient-options">
                            <summary>Gradyan Renkler</summary>
                            <div class="gradient-presets">
                                <button type="button" class="gradient-preset" data-gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" title="Mor-Mavi"></button>
                                <button type="button" class="gradient-preset" data-gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" title="Pembe"></button>
                                <button type="button" class="gradient-preset" data-gradient="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" title="Mavi"></button>
                                <button type="button" class="gradient-preset" data-gradient="linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)" title="Yeşil"></button>
                            </div>
                        </details>
                    </div>
                </div>
                
                <div class="field-group">
                    <label class="field-label">
                        <span class="dashicons dashicons-sort"></span>
                        Sıralama & Ayarlar
                    </label>
                    
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label>Sıra:</label>
                            <input type="number" class="card-order" value="<?php echo $order; ?>" min="0" step="1">
                        </div>
                        
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" class="card-featured" <?php checked($featured); ?>>
                                <span class="dashicons dashicons-star-filled"></span>
                                Öne Çıkan
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" class="card-new-tab" <?php checked($new_tab); ?>>
                                <span class="dashicons dashicons-external"></span>
                                Yeni Sekme
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" class="card-analytics" checked>
                                <span class="dashicons dashicons-chart-bar"></span>
                                Analytics
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kart Önizlemesi -->
        <div class="card-mini-preview">
            <h4>Kart Önizlemesi:</h4>
            <div class="mini-preview-card" style="border-color: <?php echo $color; ?>;">
                <?php if ($img): ?>
                    <div class="mini-preview-image">
                        <img src="<?php echo $img; ?>" alt="">
                    </div>
                <?php endif; ?>
                <div class="mini-preview-content">
                    <h5><?php echo $title ?: 'Kart Başlığı'; ?></h5>
                    <p><?php echo $desc ?: 'Kart açıklaması...'; ?></p>
                </div>
                <div class="mini-preview-button" style="background-color: <?php echo $color; ?>;">
                    Detayları Gör
                </div>
            </div>
        </div>
    </div>
</div>