<?php
/*
 * Quick Menu Cards - Groups List View
 * Admin sayfası grup listesi görünümü
 */

if (!defined('ABSPATH')) {
    exit;
}

// $kartlar değişkeni admin sınıfından geliyor
?>

<div class="quick-menu-groups-page">
    <div class="groups-header">
        <h2>Kart Grupları Yönetimi</h2>
        <div class="header-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=esistenze-quick-menu&tab=edit&edit_group=' . count($kartlar))); ?>" 
               class="button button-primary">
                <span class="dashicons dashicons-plus-alt"></span> <?php echo esc_html('Yeni Grup Oluştur'); ?>
            </a>
            <button type="button" class="button" onclick="exportAllGroups()">
                <span class="dashicons dashicons-download"></span> Dışa Aktar
            </button>
            <button type="button" class="button" onclick="importGroups()">
                <span class="dashicons dashicons-upload"></span> İçe Aktar
            </button>
        </div>
    </div>
    
    <!-- Özet İstatistikler -->
    <div class="groups-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo esc_html(count($kartlar)); ?></div>
            <div class="stat-label">Toplam Grup</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo esc_html($this->get_total_cards_count()); ?></div>
            <div class="stat-label">Toplam Kart</div>
        </div>
        <div class="stat-card">
            <?php $analytics = get_option('esistenze_quick_menu_analytics', array()); ?>
            <div class="stat-number"><?php echo esc_html(number_format($analytics['total_views'] ?? 0)); ?></div>
            <div class="stat-label">Toplam Görüntülenme</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo esc_html(number_format($analytics['total_clicks'] ?? 0)); ?></div>
            <div class="stat-label">Toplam Tıklama</div>
        </div>
    </div>
    
    <?php if (empty($kartlar)): ?>
        <!-- Boş Durum -->
        <div class="no-groups">
            <div class="no-groups-icon">🎯</div>
            <h3>Henüz kart grubu oluşturulmamış</h3>
            <p>İlk kart grubunuzu oluşturarak başlayın. Her grup farklı konuları içerebilir ve farklı sayfalarda kullanılabilir.</p>
            <div class="no-groups-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=esistenze-quick-menu&tab=edit&edit_group=0')); ?>" 
                   class="button button-primary button-hero">
                    <span class="dashicons dashicons-plus-alt"></span> <?php echo esc_html('İlk Grubu Oluştur'); ?>
                </a>
                <button type="button" class="button button-secondary" onclick="importGroups()">
                    <span class="dashicons dashicons-upload"></span> Hazır Grup İçe Aktar
                </button>
            </div>
            
            <!-- Hızlı Başlangıç İpuçları -->
            <div class="quick-start-tips">
                <h4>💡 Hızlı Başlangıç İpuçları:</h4>
                <ul>
                    <li><strong>Kategori Bazlı:</strong> Her grup için farklı konular seçin (Hizmetler, Ürünler, vb.)</li>
                    <li><strong>Shortcode Kullanımı:</strong> <code>[quick_menu_cards id="0"]</code> ile sayfalarınıza ekleyin</li>
                    <li><strong>Banner Görünümü:</strong> <code>[quick_menu_banner id="0"]</code> ile geniş görünüm</li>
                    <li><strong>Responsive:</strong> Tüm cihazlarda otomatik olarak uyumlu</li>
                </ul>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Grup Listesi -->
        <div class="groups-grid">
            <?php foreach ($kartlar as $group_id => $group_data): ?>
                <?php 
                $stats = $this->get_group_stats($group_id);
                $card_count = is_array($group_data) ? count($group_data) : 0;
                ?>
                
                <div class="group-card" data-group-id="<?php echo esc_attr($group_id); ?>">
                    <!-- Grup Başlığı -->
                    <div class="group-header">
                        <h3>
                            <span class="group-id">#<?php echo esc_html($group_id); ?></span>
                            Grup <?php echo esc_html($group_id + 1); ?>
                            <?php if ($stats['views'] > 100): ?>
                                <span class="popular-badge">🔥 Popüler</span>
                            <?php endif; ?>
                        </h3>
                        <div class="group-status">
                            <?php if ($card_count > 0): ?>
                                <span class="status-active">✅ Aktif</span>
                            <?php else: ?>
                                <span class="status-empty">⚠️ Boş</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Grup İstatistikleri -->
                    <div class="group-stats-row">
                        <div class="stat-item">
                            <span class="dashicons dashicons-grid-view"></span>
                            <div class="stat-value"><?php echo esc_html($card_count); ?></div>
                            <span class="stat-label">kart</span>
                        </div>
                        <div class="stat-item">
                            <span class="dashicons dashicons-visibility"></span>
                            <div class="stat-value"><?php echo esc_html(number_format($stats['views'])); ?></div>
                            <span class="stat-label">görüntülenme</span>
                        </div>
                        <div class="stat-item">
                            <span class="dashicons dashicons-external"></span>
                            <div class="stat-value"><?php echo esc_html(number_format($stats['clicks'])); ?></div>
                            <span class="stat-label">tıklama</span>
                        </div>
                        <div class="stat-item">
                            <span class="dashicons dashicons-chart-line"></span>
                            <div class="stat-value"><?php echo esc_html($stats['ctr']); ?>%</div>
                            <span class="stat-label">CTR</span>
                        </div>
                    </div>
                    
                    <!-- Kart Önizlemeleri -->
                    <div class="group-cards-preview">
                        <?php if (!empty($group_data) && is_array($group_data)): ?>
                            <?php 
                            $preview_count = min(4, count($group_data));
                            for ($i = 0; $i < $preview_count; $i++): 
                                $card = $group_data[$i];
                            ?>
                                <div class="mini-card" title="<?php echo esc_attr($card['title'] ?? 'Başlıksız'); ?>">
                                    <?php if (!empty($card['img'])): ?>
                                        <img src="<?php echo esc_url($card['img']); ?>" alt="">
                                    <?php else: ?>
                                        <div class="mini-card-icon">&#128196;</div>
                                    <?php endif; ?>
                                    <span class="mini-card-title"><?php echo esc_html(wp_trim_words($card['title'] ?? 'Başlıksız', 3)); ?></span>
                                </div>
                            <?php endfor; ?>
                            
                            <?php if (count($group_data) > 4): ?>
                                <div class="mini-card more-cards">
                                    <div class="more-count">+<?php echo esc_html(count($group_data) - 4); ?></div>
                                    <span>daha fazla</span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="empty-group-preview">
                                <span class="dashicons dashicons-format-image"></span>
                                <p>Bu grupta henüz kart yok</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Grup İşlemleri -->
                    <div class="group-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=esistenze-quick-menu&tab=edit&edit_group=' . $group_id)); ?>" 
                           class="button button-small action-primary" title="<?php echo esc_attr('Grubu Düzenle'); ?>">
                            <span class="dashicons dashicons-edit"></span> <?php echo esc_html('Düzenle'); ?>
                        </a>
                        <button type="button" class="button button-small action-secondary duplicate-group" 
                                data-group-id="<?php echo esc_attr($group_id); ?>" title="<?php echo esc_attr('Grubu Kopyala'); ?>">
                            <span class="dashicons dashicons-admin-page"></span> <?php echo esc_html('Kopyala'); ?>
                        </button>
                        <button type="button" class="button button-small action-danger delete-group" 
                                data-group-id="<?php echo esc_attr($group_id); ?>" title="<?php echo esc_attr('Grubu Sil'); ?>">
                            <span class="dashicons dashicons-trash"></span> <?php echo esc_html('Sil'); ?>
                        </button>
                    </div>
                    
                    <!-- Shortcode Bilgileri -->
                    <div class="group-shortcodes">
                        <h4>Shortcode'lar:</h4>
                        <div class="shortcode-grid">
                            <div class="shortcode-item">
                                <label>Izgara Görünüm:</label>
                                <div class="shortcode-wrapper">
                                    <code class="shortcode-text">[quick_menu_cards id="<?php echo esc_attr($group_id); ?>"]</code>
                                    <button type="button" class="copy-shortcode" 
                                            data-shortcode="[quick_menu_cards id=&quot;<?php echo esc_attr($group_id); ?>&quot;]" 
                                            title="<?php echo esc_attr('Shortcode\'u Kopyala'); ?>">
                                        <span class="dashicons dashicons-clipboard"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="shortcode-item">
                                <label>Banner Görünüm:</label>
                                <div class="shortcode-wrapper">
                                    <code class="shortcode-text">[quick_menu_banner id="<?php echo esc_attr($group_id); ?>"]</code>
                                    <button type="button" class="copy-shortcode" 
                                            data-shortcode="[quick_menu_banner id=&quot;<?php echo esc_attr($group_id); ?>&quot;]" 
                                            title="<?php echo esc_attr('Shortcode\'u Kopyala'); ?>">
                                        <span class="dashicons dashicons-clipboard"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Geriye Uyumluluk -->
                        <details class="legacy-shortcodes">
                            <summary>Eski Shortcode'lar (geriye uyumluluk)</summary>
                            <div class="shortcode-item">
                                <code>[hizli_menu id="<?php echo esc_attr($group_id); ?>"]</code>
                                <button type="button" class="copy-shortcode" 
                                        data-shortcode="[hizli_menu id=&quot;<?php echo esc_attr($group_id); ?>&quot;]">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                            <div class="shortcode-item">
                                <code>[hizli_menu_banner id="<?php echo esc_attr($group_id); ?>"]</code>
                                <button type="button" class="copy-shortcode" 
                                        data-shortcode="[hizli_menu_banner id=&quot;<?php echo esc_attr($group_id); ?>&quot;]">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                        </details>
                    </div>
                    
                    <!-- Hızlı Önizleme -->
                    <div class="quick-preview">
                        <button type="button" class="button button-small" onclick="previewGroup(<?php echo esc_attr($group_id); ?>)">
                            <span class="dashicons dashicons-visibility"></span> Önizleme
                        </button>
                        <a href="<?php echo home_url('?quick_menu_preview=' . $group_id); ?>" 
                           target="_blank" class="button button-small">
                            <span class="dashicons dashicons-external"></span> Sitede Gör
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Toplu İşlemler -->
        <div class="bulk-actions">
            <h3>Toplu İşlemler</h3>
            <div class="bulk-controls">
                <button type="button" class="button" onclick="selectAllGroups()">Tümünü Seç</button>
                <button type="button" class="button" onclick="exportSelectedGroups()">Seçilenleri Dışa Aktar</button>
                <button type="button" class="button button-link-delete" onclick="deleteSelectedGroups()">Seçilenleri Sil</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Önizleme Modal -->
<div id="preview-modal" class="preview-modal" style="display: none;">
    <div class="preview-modal-content">
        <div class="preview-modal-header">
            <h3>Grup Önizlemesi</h3>
            <button type="button" class="modal-close" onclick="closePreviewModal()">&times;</button>
        </div>
        <div class="preview-modal-body">
            <div class="preview-tabs">
                <button class="preview-tab active" data-type="grid">Izgara Görünüm</button>
                <button class="preview-tab" data-type="banner">Banner Görünüm</button>
            </div>
            <div id="preview-content" class="preview-content">
                <!-- Önizleme içeriği buraya yüklenecek -->
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="import-modal" class="import-modal" style="display: none;">
    <div class="import-modal-content">
        <div class="import-modal-header">
            <h3>Grup İçe Aktar</h3>
            <button type="button" class="modal-close" onclick="closeImportModal()">&times;</button>
        </div>
        <div class="import-modal-body">
            <div class="import-methods">
                <div class="import-method">
                    <h4>JSON Dosyası Yükle</h4>
                    <input type="file" id="import-file" accept=".json" onchange="handleFileImport(event)">
                    <p class="description">Daha önce dışa aktarılmış JSON dosyasını seçin.</p>
                </div>
                <div class="import-method">
                    <h4>JSON Metni Yapıştır</h4>
                    <textarea id="import-text" rows="10" placeholder="JSON verilerini buraya yapıştırın..."></textarea>
                    <button type="button" class="button button-primary" onclick="importFromText()">İçe Aktar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Groups page specific styles */
.quick-menu-groups-page {
    margin-top: 20px;
}

.groups-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.groups-header h2 {
    margin: 0;
    font-size: 24px;
    color: #1d2327;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.header-actions .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.groups-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #0073aa;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #646970;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.no-groups {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-top: 20px;
}

.no-groups-icon {
    font-size: 72px;
    margin-bottom: 20px;
    opacity: 0.7;
}

.no-groups h3 {
    font-size: 24px;
    margin-bottom: 15px;
    color: #1d2327;
}

.no-groups p {
    font-size: 16px;
    color: #646970;
    max-width: 600px;
    margin: 0 auto 30px;
    line-height: 1.6;
}

.no-groups-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 40px;
}

.quick-start-tips {
    background: #f0f6fc;
    border: 1px solid #c3d4e6;
    border-radius: 6px;
    padding: 20px;
    margin-top: 40px;
    text-align: left;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.quick-start-tips h4 {
    margin-top: 0;
    color: #0073aa;
}

.quick-start-tips ul {
    margin: 0;
    padding-left: 20px;
}

.quick-start-tips li {
    margin-bottom: 8px;
    line-height: 1.5;
}

.groups-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 25px;
    margin: 20px 0;
}

.group-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
}

.group-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.group-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.group-header h3 {
    margin: 0;
    font-size: 18px;
    color: #1d2327;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.group-id {
    background: #0073aa;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.popular-badge {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.group-status .status-active {
    color: #00a32a;
    font-weight: 600;
}

.group-status .status-empty {
    color: #d63638;
    font-weight: 600;
}

.group-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f6f7f7;
    border-radius: 6px;
}

.stat-item {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.stat-item .dashicons {
    font-size: 16px;
    color: #646970;
}

.stat-value {
    font-weight: 700;
    font-size: 16px;
    color: #1d2327;
}

.stat-label {
    font-size: 11px;
    color: #646970;
    text-transform: uppercase;
}

.group-cards-preview {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 20px;
    min-height: 80px;
}

.mini-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 6px;
    border: 1px solid #eee;
    transition: background 0.2s;
}

.mini-card:hover {
    background: #f0f0f0;
}

.mini-card img,
.mini-card-icon {
    width: 24px;
    height: 24px;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}

.mini-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ddd;
    font-size: 12px;
}

.mini-card-title {
    font-size: 12px;
    color: #1d2327;
    font-weight: 500;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.more-cards {
    justify-content: center;
    background: #e0e0e0;
    color: #646970;
    font-weight: 600;
}

.more-count {
    font-size: 18px;
    font-weight: 700;
}

.empty-group-preview {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    color: #646970;
    background: #f9f9f9;
    border-radius: 6px;
    border: 2px dashed #ddd;
}

.empty-group-preview .dashicons {
    font-size: 32px;
    margin-bottom: 10px;
    opacity: 0.5;
}

.group-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
}

.action-primary {
    background: #0073aa;
    color: white;
    border-color: #0073aa;
}

.action-secondary {
    background: #f0f0f1;
    color: #2c3338;
}

.action-danger {
    background: #d63638;
    color: white;
    border-color: #d63638;
}

.group-shortcodes {
    background: #f8f9fa;
    border: 1px solid #e2e4e7;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.group-shortcodes h4 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #1d2327;
}

.shortcode-grid {
    display: grid;
    gap: 10px;
}

.shortcode-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.shortcode-item label {
    font-size: 12px;
    color: #646970;
    font-weight: 600;
}

.shortcode-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.shortcode-text {
    flex: 1;
    background: white;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 12px;
    color: #0073aa;
    font-family: 'Monaco', 'Menlo', monospace;
}

.copy-shortcode {
    padding: 6px;
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.copy-shortcode:hover {
    background: #005a87;
}

.legacy-shortcodes {
    margin-top: 10px;
}

.legacy-shortcodes summary {
    font-size: 12px;
    color: #646970;
    cursor: pointer;
    padding: 5px 0;
}

.quick-preview {
    display: flex;
    gap: 8px;
}

.bulk-actions {
    margin-top: 40px;
    padding: 20px;
    background: #f6f7f7;
    border-radius: 8px;
}

.bulk-actions h3 {
    margin-top: 0;
    margin-bottom: 15px;
}

.bulk-controls {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Modal Styles */
.preview-modal,
.import-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.preview-modal-content,
.import-modal-content {
    background: white;
    margin: 3% auto;
    border-radius: 8px;
    width: 90%;
    max-width: 1000px;
    max-height: 90vh;
    overflow-y: auto;
}

.preview-modal-header,
.import-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #ddd;
}

.preview-modal-body,
.import-modal-body {
    padding: 20px;
}

.preview-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.preview-tab {
    padding: 10px 20px;
    border: none;
    background: none;
    cursor: pointer;
    border-bottom: 2px solid transparent;
}

.preview-tab.active {
    border-bottom-color: #0073aa;
    color: #0073aa;
}

.preview-content {
    min-height: 300px;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
}

.import-methods {
    display: grid;
    gap: 30px;
}

.import-method h4 {
    margin-bottom: 10px;
}

.import-method input[type="file"] {
    margin-bottom: 10px;
}

.import-method textarea {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    font-family: monospace;
}

.description {
    font-size: 13px;
    color: #646970;
    margin: 5px 0 0;
}

/* Responsive */
@media (max-width: 768px) {
    .groups-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .header-actions {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .groups-grid {
        grid-template-columns: 1fr;
    }
    
    .group-stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .group-actions {
        flex-wrap: wrap;
    }
    
    .bulk-controls {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Grup silme
    $('.delete-group').on('click', function() {
        var groupId = $(this).data('group-id');
        var $card = $(this).closest('.group-card');
        
        if (confirm(esistenzeAdmin.strings.delete_confirm)) {
            $.ajax({
                url: esistenzeAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'esistenze_delete_card_group',
                    nonce: esistenzeAdmin.nonce,
                    group_id: groupId
                },
                success: function(response) {
                    if (response.success) {
                        $card.fadeOut(300, function() {
                            $(this).remove();
                            // Sayfa boş kaldıysa reload et
                            if ($('.group-card').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert('Silme işlemi başarısız: ' + response.data);
                    }
                },
                error: function() {
                    alert('Bağlantı hatası oluştu.');
                }
            });
        }
    });
    
    // Grup kopyalama
    $('.duplicate-group').on('click', function() {
        var groupId = $(this).data('group-id');
        
        $.ajax({
            url: esistenzeAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'esistenze_duplicate_card_group',
                nonce: esistenzeAdmin.nonce,
                group_id: groupId
            },
            success: function(response) {
                if (response.success) {
                    // Başarı mesajı göster ve sayfayı yenile
                    alert('Grup başarıyla kopyalandı!');
                    location.reload();
                } else {
                    alert('Kopyalama işlemi başarısız: ' + response.data);
                }
            },
            error: function() {
                alert('Bağlantı hatası oluştu.');
            }
        });
    });
    
    // Shortcode kopyalama
    $('.copy-shortcode').on('click', function() {
        var shortcode = $(this).data('shortcode');
        var $button = $(this);
        
        // Clipboard API kullan
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(shortcode).then(function() {
                showCopySuccess($button);
            }).catch(function() {
                // Fallback
                copyToClipboardFallback(shortcode, $button);
            });
        } else {
            copyToClipboardFallback(shortcode, $button);
        }
    });
    
    function showCopySuccess($button) {
        var originalContent = $button.html();
        $button.html('<span class="dashicons dashicons-yes"></span>');
        $button.css('background', '#00a32a');
        
        setTimeout(function() {
            $button.html(originalContent);
            $button.css('background', '');
        }, 1500);
    }
    
    function copyToClipboardFallback(text, $button) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess($button);
        } catch (err) {
            alert('Shortcode: ' + text);
        }
        
        document.body.removeChild(textArea);
    }
    
    // Önizleme modal
    window.previewGroup = function(groupId) {
        $('#preview-modal').show();
        loadPreview(groupId, 'grid');
        
        // Tab değişimi
        $('.preview-tab').removeClass('active').on('click', function() {
            $('.preview-tab').removeClass('active');
            $(this).addClass('active');
            var type = $(this).data('type');
            loadPreview(groupId, type);
        });
        $('.preview-tab[data-type="grid"]').addClass('active');
    };
    
    window.closePreviewModal = function() {
        $('#preview-modal').hide();
    };
    
    function loadPreview(groupId, type) {
        $('#preview-content').html('<div class="loading">Yükleniyor...</div>');
        
        $.ajax({
            url: esistenzeAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'esistenze_preview_group',
                nonce: esistenzeAdmin.nonce,
                group_id: groupId,
                preview_type: type
            },
            success: function(response) {
                if (response.success) {
                    $('#preview-content').html(response.data.html);
                } else {
                    $('#preview-content').html('<p>Önizleme yüklenemedi.</p>');
                }
            },
            error: function() {
                $('#preview-content').html('<p>Bağlantı hatası.</p>');
            }
        });
    }
    
    // Export işlemleri
    window.exportAllGroups = function() {
        $.ajax({
            url: esistenzeAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'esistenze_export_groups',
                nonce: esistenzeAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    downloadJSON(response.data.data, response.data.filename);
                } else {
                    alert('Export işlemi başarısız: ' + response.data);
                }
            },
            error: function() {
                alert('Bağlantı hatası oluştu.');
            }
        });
    };
    
    function downloadJSON(data, filename) {
        var blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    
    // Import işlemleri
    window.importGroups = function() {
        $('#import-modal').show();
    };
    
    window.closeImportModal = function() {
        $('#import-modal').hide();
    };
    
    window.handleFileImport = function(event) {
        var file = event.target.files[0];
        if (file && file.type === 'application/json') {
            var reader = new FileReader();
            reader.onload = function(e) {
                var data = e.target.result;
                importData(data);
            };
            reader.readAsText(file);
        } else {
            alert('Lütfen geçerli bir JSON dosyası seçin.');
        }
    };
    
    window.importFromText = function() {
        var data = $('#import-text').val().trim();
        if (data) {
            importData(data);
        } else {
            alert('Lütfen JSON verilerini girin.');
        }
    };
    
    function importData(jsonData) {
        $.ajax({
            url: esistenzeAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'esistenze_import_groups',
                nonce: esistenzeAdmin.nonce,
                import_data: jsonData
            },
            success: function(response) {
                if (response.success) {
                    alert('İçe aktarma başarılı! ' + response.data.group_count + ' grup eklendi.');
                    location.reload();
                } else {
                    alert('İçe aktarma başarısız: ' + response.data);
                }
            },
            error: function() {
                alert('Bağlantı hatası oluştu.');
            }
        });
    }
    
    // Toplu işlemler
    window.selectAllGroups = function() {
        var checkboxes = $('.group-card input[type="checkbox"]');
        if (checkboxes.length === 0) {
            // Checkbox'lar yoksa ekle
            $('.group-card').each(function() {
                var groupId = $(this).data('group-id');
                $(this).prepend('<input type="checkbox" class="group-select" value="' + groupId + '">');
            });
            $(this).text('Seçimi İptal Et');
        } else {
            checkboxes.prop('checked', true);
        }
    };
    
    window.exportSelectedGroups = function() {
        var selected = $('.group-select:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selected.length === 0) {
            alert('Lütfen dışa aktarmak istediğiniz grupları seçin.');
            return;
        }
        
        // Seçili grupları export et
        alert('Seçili ' + selected.length + ' grup dışa aktarılacak. (Bu özellik henüz geliştirilecek)');
    };
    
    window.deleteSelectedGroups = function() {
        var selected = $('.group-select:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selected.length === 0) {
            alert('Lütfen silmek istediğiniz grupları seçin.');
            return;
        }
        
        if (confirm('Seçili ' + selected.length + ' grubu silmek istediğinizden emin misiniz?')) {
            // Seçili grupları sil
            alert('Seçili gruplar silinecek. (Bu özellik henüz geliştirilecek)');
        }
    };
    
    // Modal kapatma (dış tıklama)
    $(document).on('click', '.preview-modal, .import-modal', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // ESC tuşu ile modal kapatma
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27) { // ESC
            $('.preview-modal, .import-modal').hide();
        }
    });
});
</script>