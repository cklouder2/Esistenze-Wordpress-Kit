<?php
/*
 * Quick Menu Cards - Edit Group View
 * Admin sayfası grup düzenleme görünümü
 */

if (!defined('ABSPATH')) {
    exit;
}

// $group_id ve $group_data değişkenleri admin sınıfından geliyor
?>

<div class="quick-menu-edit-page">
    <!-- Sayfa Başlığı -->
    <div class="edit-header">
        <div class="header-left">
            <h2>
                <?php if ($group_id === 0 && empty($group_data)): ?>
                    <span class="dashicons dashicons-plus-alt"></span> Yeni Grup Oluştur
                <?php else: ?>
                    <span class="dashicons dashicons-edit"></span> Grup #<?php echo $group_id; ?> Düzenle
                <?php endif; ?>
            </h2>
            <div class="group-meta">
                <?php if (!empty($group_data)): ?>
                    <span class="meta-item">
                        <span class="dashicons dashicons-grid-view"></span>
                        <?php echo count($group_data); ?> kart
                    </span>
                    <?php $stats = $this->get_group_stats($group_id); ?>
                    <span class="meta-item">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php echo number_format($stats['views']); ?> görüntülenme
                    </span>
                    <span class="meta-item">
                        <span class="dashicons dashicons-external"></span>
                        <?php echo number_format($stats['clicks']); ?> tıklama
                    </span>
                <?php else: ?>
                    <span class="meta-item new-group">🆕 Yeni grup</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="header-actions">
            <button id="save-group" class="button button-primary button-large">
                <span class="dashicons dashicons-saved"></span> Kaydet
            </button>
            <button id="save-and-continue" class="button button-secondary">
                <span class="dashicons dashicons-plus-alt"></span> Kaydet ve Devam Et
            </button>
            <a href="<?php echo admin_url('admin.php?page=esistenze-quick-menu'); ?>" class="button">
                <span class="dashicons dashicons-arrow-left-alt"></span> Geri Dön
            </a>
        </div>
    </div>
    
    <!-- Ana İçerik -->
    <div class="edit-content">
        <!-- Sol Panel: Kart Düzenleyici -->
        <div class="cards-container">
            <div class="cards-header">
                <h3>
                    <span class="dashicons dashicons-screenoptions"></span>
                    Kartlar
                </h3>
                <div class="cards-actions">
                    <button id="add-card" class="button button-primary">
                        <span class="dashicons dashicons-plus-alt"></span> Yeni Kart Ekle
                    </button>
                    <button id="import-cards" class="button">
                        <span class="dashicons dashicons-upload"></span> Kartları İçe Aktar
                    </button>
                    <button id="sort-cards" class="button">
                        <span class="dashicons dashicons-sort"></span> Otomatik Sırala
                    </button>
                </div>
            </div>
            
            <!-- Kartlar Listesi -->
            <div id="cards-list" class="cards-list sortable">
                <?php if (!empty($group_data)): ?>
                    <?php foreach ($group_data as $index => $kart): ?>
                        <?php include $this->module_path . 'views/partials/card-editor.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-cards-message">
                        <div class="no-cards-icon">🎯</div>
                        <h4>Henüz kart eklenmemiş</h4>
                        <p>İlk kartınızı eklemek için "Yeni Kart Ekle" butonuna tıklayın.</p>
                        <button class="button button-primary" onclick="addNewCard()">
                            <span class="dashicons dashicons-plus-alt"></span> İlk Kartı Ekle
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Toplu İşlemler -->
            <div class="bulk-card-actions">
                <h4>Toplu İşlemler</h4>
                <div class="bulk-buttons">
                    <button type="button" class="button" onclick="selectAllCards()">Tümünü Seç</button>
                    <button type="button" class="button" onclick="duplicateSelected()">Seçilenleri Kopyala</button>
                    <button type="button" class="button button-link-delete" onclick="deleteSelected()">Seçilenleri Sil</button>
                    <button type="button" class="button" onclick="exportSelected()">Seçilenleri Dışa Aktar</button>
                </div>
            </div>
        </div>
        
        <!-- Sağ Panel: Önizleme ve Ayarlar -->
        <div class="preview-container">
            <!-- Canlı Önizleme -->
            <div class="preview-section">
                <div class="preview-header">
                    <h3>
                        <span class="dashicons dashicons-visibility"></span>
                        Canlı Önizleme
                    </h3>
                    <div class="preview-controls">
                        <div class="preview-tabs">
                            <button class="preview-tab active" data-preview="grid" title="Izgara Görünüm">
                                <span class="dashicons dashicons-grid-view"></span>
                            </button>
                            <button class="preview-tab" data-preview="banner" title="Banner Görünüm">
                                <span class="dashicons dashicons-format-gallery"></span>
                            </button>
                        </div>
                        <button class="preview-refresh" onclick="refreshPreview()" title="Önizlemeyi Yenile">
                            <span class="dashicons dashicons-update"></span>
                        </button>
                    </div>
                </div>
                <div id="live-preview" class="live-preview">
                    <div class="preview-loading">
                        <span class="dashicons dashicons-update-alt"></span>
                        <p>Önizleme yükleniyor...</p>
                    </div>
                </div>
            </div>
            
            <!-- Shortcode Bilgileri -->
            <div class="shortcode-section">
                <h3>
                    <span class="dashicons dashicons-editor-code"></span>
                    Shortcode'lar
                </h3>
                <div class="shortcode-list">
                    <div class="shortcode-item">
                        <label>Izgara Görünüm:</label>
                        <div class="shortcode-wrapper">
                            <code class="shortcode-text">[quick_menu_cards id="<?php echo $group_id; ?>"]</code>
                            <button class="copy-shortcode" data-shortcode="[quick_menu_cards id=&quot;<?php echo $group_id; ?>&quot;]">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </div>
                    </div>
                    <div class="shortcode-item">
                        <label>Banner Görünüm:</label>
                        <div class="shortcode-wrapper">
                            <code class="shortcode-text">[quick_menu_banner id="<?php echo $group_id; ?>"]</code>
                            <button class="copy-shortcode" data-shortcode="[quick_menu_banner id=&quot;<?php echo $group_id; ?>&quot;]">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Gelişmiş Shortcode Seçenekleri -->
                    <details class="advanced-shortcodes">
                        <summary>Gelişmiş Seçenekler</summary>
                        <div class="shortcode-generator">
                            <div class="generator-field">
                                <label>Kart Limiti:</label>
                                <input type="number" id="shortcode-limit" value="" min="1" max="50" placeholder="Tümü">
                            </div>
                            <div class="generator-field">
                                <label>Kolon Sayısı:</label>
                                <select id="shortcode-columns">
                                    <option value="">Varsayılan</option>
                                    <option value="1">1 Kolon</option>
                                    <option value="2">2 Kolon</option>
                                    <option value="3">3 Kolon</option>
                                    <option value="4">4 Kolon</option>
                                    <option value="5">5 Kolon</option>
                                    <option value="6">6 Kolon</option>
                                </select>
                            </div>
                            <button class="button" onclick="generateCustomShortcode()">Özel Shortcode Oluştur</button>
                            <div id="custom-shortcode-result"></div>
                        </div>
                    </details>
                </div>
            </div>
            
            <!-- Grup Ayarları -->
            <div class="group-settings-section">
                <h3>
                    <span class="dashicons dashicons-admin-settings"></span>
                    Grup Ayarları
                </h3>
                <div class="settings-list">
                    <div class="setting-item">
                        <label>
                            <input type="checkbox" id="group-enabled" <?php echo !empty($group_data) ? 'checked' : ''; ?>>
                            Grup Aktif
                        </label>
                        <p class="setting-description">Bu grup frontend'de görüntülensin mi?</p>
                    </div>
                    <div class="setting-item">
                        <label>
                            <input type="checkbox" id="group-featured">
                            Öne Çıkan Grup
                        </label>
                        <p class="setting-description">Bu grup öne çıkan olarak işaretlensin mi?</p>
                    </div>
                    <div class="setting-item">
                        <label for="group-order">Grup Sırası:</label>
                        <input type="number" id="group-order" value="<?php echo $group_id; ?>" min="0">
                        <p class="setting-description">Düşük sayılar önce görüntülenir.</p>
                    </div>
                </div>
            </div>
            
            <!-- Test Araçları -->
            <div class="test-tools-section">
                <h3>
                    <span class="dashicons dashicons-performance"></span>
                    Test Araçları
                </h3>
                <div class="test-buttons">
                    <button class="button" onclick="testResponsive()">
                        <span class="dashicons dashicons-smartphone"></span> Responsive Test
                    </button>
                    <button class="button" onclick="testAccessibility()">
                        <span class="dashicons dashicons-universal-access"></span> Erişilebilirlik Test
                    </button>
                    <button class="button" onclick="testPerformance()">
                        <span class="dashicons dashicons-chart-line"></span> Performans Test
                    </button>
                    <a href="<?php echo home_url('?quick_menu_preview=' . $group_id); ?>" target="_blank" class="button">
                        <span class="dashicons dashicons-external"></span> Sitede Önizle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript dosyası dahil et -->
<script src="<?php echo $this->module_url; ?>assets/admin-edit.js?v=<?php echo $this->get_version(); ?>"></script>

<!-- Sayfa özel script'i -->
<script>
// Global değişkenler
var groupId = <?php echo $group_id; ?>;
var esistenzeAdmin = window.esistenzeAdmin || {};

// Sayfa yüklendiğinde başlat
jQuery(document).ready(function($) {
    if (typeof window.EsistenzeEditPage !== 'undefined') {
        window.EsistenzeEditPage.init({
            groupId: groupId,
            moduleUrl: '<?php echo $this->module_url; ?>',
            ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('esistenze_quick_menu_nonce'); ?>'
        });
    }
});
</script>