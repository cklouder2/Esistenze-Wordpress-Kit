<?php
/*
 * Quick Menu Cards - Admin Analytics View
 * İstatistikler ve analytics sayfası
 */

if (!defined('ABSPATH')) {
    exit;
}

// Analytics verilerini işle
$total_groups = count($kartlar);
$total_cards = array_sum(array_map('count', $kartlar));
$total_views = $analytics['total_views'] ?? 0;
$total_clicks = $analytics['total_clicks'] ?? 0;
$overall_ctr = $total_views > 0 ? round(($total_clicks / $total_views) * 100, 2) : 0;

// Tarih filtreleme
$date_ranges = array(
    '7_days' => 'Son 7 Gün',
    '30_days' => 'Son 30 Gün',
    '90_days' => 'Son 90 Gün',
    'all_time' => 'Tüm Zamanlar'
);

$current_range = isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : '30_days';

// En popüler grupları bul
$popular_groups = array();
if (!empty($analytics['group_clicks'])) {
    arsort($analytics['group_clicks']);
    $popular_groups = array_slice($analytics['group_clicks'], 0, 5, true);
}

// Son aktiviteler
$recent_activities = array();
if (!empty($analytics['click_details'])) {
    $recent_activities = array_slice($analytics['click_details'], -10);
    $recent_activities = array_reverse($recent_activities);
}

// Grafik verileri hazırla (son 30 gün)
$chart_data = array();
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $chart_data[] = array(
        'date' => $date,
        'views' => rand(0, 50), // Gerçek veri burada olacak
        'clicks' => rand(0, 25)
    );
}
?>

<div class="wrap esistenze-analytics-wrap">
    <h1>Quick Menu Cards - İstatistikler</h1>
    
    <!-- Filtre Bölümü -->
    <div class="analytics-filters">
        <form method="get" class="filter-form">
            <input type="hidden" name="page" value="esistenze-quick-menu-analytics">
            
            <label for="date_filter">Tarih Aralığı:</label>
            <select name="date_filter" id="date_filter" onchange="this.form.submit()">
                <?php foreach ($date_ranges as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($current_range, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="button" class="button" id="refresh-data">
                <span class="dashicons dashicons-update"></span>
                Yenile
            </button>
            
            <button type="button" class="button" id="export-analytics">
                <span class="dashicons dashicons-download"></span>
                Dışa Aktar
            </button>
        </form>
    </div>

    <!-- Genel İstatistikler -->
    <div class="analytics-overview">
        <h2>Genel Bakış</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-grid-view"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($total_groups); ?></div>
                    <div class="stat-label">Toplam Grup</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-id-alt"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($total_cards); ?></div>
                    <div class="stat-label">Toplam Kart</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-visibility"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($total_views); ?></div>
                    <div class="stat-label">Toplam Görüntülenme</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-admin-links"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($total_clicks); ?></div>
                    <div class="stat-label">Toplam Tıklama</div>
                </div>
            </div>
            
            <div class="stat-card highlight">
                <div class="stat-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number">%<?php echo $overall_ctr; ?></div>
                    <div class="stat-label">Genel CTR</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Bölümü -->
    <div class="analytics-chart">
        <h2>Zaman İçinde Performans</h2>
        
        <div class="chart-container">
            <canvas id="performanceChart" width="800" height="300"></canvas>
        </div>
        
        <div class="chart-legend">
            <div class="legend-item">
                <span class="legend-color" style="background: #2196F3;"></span>
                <span class="legend-label">Görüntülenme</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background: #FF9800;"></span>
                <span class="legend-label">Tıklama</span>
            </div>
        </div>
    </div>

    <!-- İki Sütunlu İçerik -->
    <div class="analytics-content">
        <!-- Sol Sütun: Grup Performansı -->
        <div class="analytics-left">
            <h2>Grup Performansı</h2>
            
            <?php if (empty($kartlar)): ?>
                <div class="no-data">
                    <p>Henüz analiz edilecek grup bulunmuyor.</p>
                    <a href="<?php echo admin_url('admin.php?page=esistenze-quick-menu&tab=edit&action=new'); ?>" class="button button-primary">
                        İlk Grubunu Oluştur
                    </a>
                </div>
            <?php else: ?>
                <div class="group-performance-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Grup ID</th>
                                <th>Kart Sayısı</th>
                                <th>Görüntülenme</th>
                                <th>Tıklama</th>
                                <th>CTR</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kartlar as $group_id => $group_data): 
                                $group_views = $analytics['group_views'][$group_id] ?? 0;
                                $group_clicks = $analytics['group_clicks'][$group_id] ?? 0;
                                $group_ctr = $group_views > 0 ? round(($group_clicks / $group_views) * 100, 2) : 0;
                                $card_count = is_array($group_data) ? count($group_data) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $group_id; ?></strong>
                                        <?php if (in_array($group_id, array_keys($popular_groups))): ?>
                                            <span class="popular-badge" title="Popüler Grup">🔥</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $card_count; ?></td>
                                    <td>
                                        <span class="number"><?php echo number_format($group_views); ?></span>
                                        <?php if ($group_views > 0): ?>
                                            <div class="mini-chart" data-value="<?php echo $group_views; ?>"></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="number"><?php echo number_format($group_clicks); ?></span>
                                        <?php if ($group_clicks > 0): ?>
                                            <div class="mini-chart" data-value="<?php echo $group_clicks; ?>"></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="ctr-badge <?php echo $group_ctr >= 5 ? 'good' : ($group_ctr >= 2 ? 'average' : 'low'); ?>">
                                            %<?php echo $group_ctr; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=esistenze-quick-menu&tab=edit&action=edit&edit_group=' . $group_id); ?>" 
                                           class="button button-small">
                                            Düzenle
                                        </a>
                                        <button type="button" 
                                                class="button button-small copy-shortcode" 
                                                data-shortcode="[quick_menu_cards id=&quot;<?php echo $group_id; ?>&quot;]">
                                            Shortcode
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sağ Sütun: En Popüler Gruplar ve Son Aktiviteler -->
        <div class="analytics-right">
            <!-- En Popüler Gruplar -->
            <div class="popular-groups">
                <h3>En Popüler Gruplar</h3>
                
                <?php if (empty($popular_groups)): ?>
                    <p class="no-data-small">Henüz yeterli veri yok.</p>
                <?php else: ?>
                    <div class="popular-list">
                        <?php $rank = 1; foreach ($popular_groups as $group_id => $clicks): ?>
                            <div class="popular-item">
                                <div class="rank">#<?php echo $rank++; ?></div>
                                <div class="group-info">
                                    <strong>Grup #<?php echo $group_id; ?></strong>
                                    <small><?php echo number_format($clicks); ?> tıklama</small>
                                </div>
                                <div class="percentage">
                                    <?php 
                                    $percentage = $total_clicks > 0 ? round(($clicks / $total_clicks) * 100, 1) : 0;
                                    echo '%' . $percentage;
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Son Aktiviteler -->
            <div class="recent-activities">
                <h3>Son Aktiviteler</h3>
                
                <?php if (empty($recent_activities)): ?>
                    <p class="no-data-small">Henüz aktivite bulunmuyor.</p>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <span class="dashicons dashicons-admin-links"></span>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        <strong>Grup #<?php echo $activity['group_id']; ?></strong> - 
                                        Kart #<?php echo $activity['card_index']; ?> tıklandı
                                    </div>
                                    <div class="activity-time">
                                        <?php echo human_time_diff(strtotime($activity['timestamp']), current_time('timestamp')) . ' önce'; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="view-all">
                        <button type="button" class="button button-small" id="load-more-activities">
                            Daha Fazla Göster
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Hızlı İstatistikler -->
            <div class="quick-stats">
                <h3>Hızlı İstatistikler</h3>
                
                <div class="quick-stats-grid">
                    <div class="quick-stat">
                        <div class="stat-value"><?php echo date('j F Y', strtotime($analytics['last_view'] ?? 'now')); ?></div>
                        <div class="stat-label">Son Görüntülenme</div>
                    </div>
                    
                    <div class="quick-stat">
                        <div class="stat-value"><?php echo date('j F Y', strtotime($analytics['last_click'] ?? 'now')); ?></div>
                        <div class="stat-label">Son Tıklama</div>
                    </div>
                    
                    <div class="quick-stat">
                        <div class="stat-value">
                            <?php 
                            $avg_cards = $total_groups > 0 ? round($total_cards / $total_groups, 1) : 0;
                            echo $avg_cards;
                            ?>
                        </div>
                        <div class="stat-label">Ortalama Kart/Grup</div>
                    </div>
                    
                    <div class="quick-stat">
                        <div class="stat-value">
                            <?php 
                            $avg_ctr = $total_groups > 0 && $total_views > 0 ? round(($total_clicks / $total_views) * 100, 1) : 0;
                            echo '%' . $avg_ctr;
                            ?>
                        </div>
                        <div class="stat-label">Ortalama CTR</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Ayarları -->
    <div class="analytics-settings">
        <h2>Analytics Ayarları</h2>
        
        <div class="settings-grid">
            <div class="setting-item">
                <label class="setting-label">
                    <input type="checkbox" <?php checked(!empty($settings['enable_analytics'])); ?> disabled>
                    Analytics Aktif
                </label>
                <p class="setting-description">
                    Analytics ayarlarını <a href="<?php echo admin_url('admin.php?page=esistenze-quick-menu-settings#analytics'); ?>">buradan</a> değiştirebilirsiniz.
                </p>
            </div>
            
            <div class="setting-item">
                <button type="button" class="button" id="clear-analytics">
                    <span class="dashicons dashicons-trash"></span>
                    Tüm Verileri Temizle
                </button>
                <p class="setting-description">
                    <strong>Dikkat:</strong> Bu işlem geri alınamaz.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js kütüphanesi -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
jQuery(document).ready(function($) {
    // Grafik oluştur
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const chartData = <?php echo json_encode($chart_data); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('tr-TR', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Görüntülenme',
                data: chartData.map(item => item.views),
                borderColor: '#2196F3',
                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Tıklama',
                data: chartData.map(item => item.clicks),
                borderColor: '#FF9800',
                backgroundColor: 'rgba(255, 152, 0, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f0f0f0'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Shortcode kopyalama
    $('.copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(function() {
            alert('Shortcode kopyalandı!');
        });
    });
    
    // Verileri temizleme
    $('#clear-analytics').on('click', function() {
        if (confirm('Tüm analytics verilerini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
            // AJAX request for clearing data
            $.post(ajaxurl, {
                action: 'esistenze_clear_analytics',
                nonce: '<?php echo wp_create_nonce('esistenze_quick_menu_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Hata: ' + response.data);
                }
            });
        }
    });
    
    // Veri yenileme
    $('#refresh-data').on('click', function() {
        location.reload();
    });
    
    // Analytics dışa aktarma
    $('#export-analytics').on('click', function() {
        window.location.href = '<?php echo admin_url('admin-ajax.php?action=esistenze_export_analytics&nonce=' . wp_create_nonce('esistenze_quick_menu_nonce')); ?>';
    });
    
    // Daha fazla aktivite yükleme
    $('#load-more-activities').on('click', function() {
        // Bu özellik daha sonra eklenebilir
        alert('Bu özellik yakında eklenecek.');
    });
});
</script>

<style>
.esistenze-analytics-wrap {
    max-width: 1400px;
}

.analytics-filters {
    background: #fff;
    padding: 15px 20px;
    margin: 20px 0;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 15px;
}

.filter-form label {
    font-weight: 600;
}

.analytics-overview {
    margin: 20px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: box-shadow 0.2s;
}

.stat-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-card.highlight {
    border-color: #2196F3;
    background: linear-gradient(135deg, #fff 0%, #f3f8ff 100%);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #f6f7f7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #646970;
    font-size: 20px;
}

.stat-card.highlight .stat-icon {
    background: #2196F3;
    color: #fff;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1d2327;
    line-height: 1;
}

.stat-label {
    font-size: 13px;
    color: #646970;
    margin-top: 4px;
}

.analytics-chart {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.chart-container {
    height: 300px;
    margin: 20px 0;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 15px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 2px;
}

.analytics-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.analytics-left,
.analytics-right > div {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
}

.analytics-right {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.no-data {
    text-align: center;
    padding: 40px 20px;
    color: #646970;
}

.group-performance-table {
    overflow-x: auto;
}

.popular-badge {
    font-size: 12px;
    margin-left: 5px;
}

.mini-chart {
    height: 3px;
    background: #ddd;
    border-radius: 2px;
    margin-top: 3px;
    overflow: hidden;
}

.ctr-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
}

.ctr-badge.good { background: #4CAF50; }
.ctr-badge.average { background: #FF9800; }
.ctr-badge.low { background: #f44336; }

.popular-list,
.activity-list {
    space-y: 10px;
}

.popular-item,
.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.popular-item:last-child,
.activity-item:last-child {
    border-bottom: none;
}

.rank {
    font-weight: 700;
    color: #FF9800;
    min-width: 30px;
}

.group-info {
    flex: 1;
}

.group-info strong {
    display: block;
}

.group-info small {
    color: #646970;
}

.percentage {
    font-weight: 600;
    color: #2196F3;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f6f7f7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #646970;
}

.activity-content {
    flex: 1;
}

.activity-text {
    font-size: 14px;
}

.activity-time {
    font-size: 12px;
    color: #646970;
    margin-top: 2px;
}

.quick-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.quick-stat {
    text-align: center;
    padding: 15px 10px;
    background: #f9f9f9;
    border-radius: 6px;
}

.quick-stat .stat-value {
    font-size: 18px;
    font-weight: 600;
    color: #1d2327;
}

.quick-stat .stat-label {
    font-size: 12px;
    color: #646970;
    margin-top: 4px;
}

.analytics-settings {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 15px;
}

.setting-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.setting-label {
    font-weight: 600;
}

.setting-description {
    font-size: 13px;
    color: #646970;
    margin: 0;
}

.no-data-small {
    color: #646970;
    font-style: italic;
    text-align: center;
    padding: 20px;
}

.view-all {
    text-align: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

@media (max-width: 1200px) {
    .analytics-content {
        grid-template-columns: 1fr;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .filter-form {
        flex-wrap: wrap;
    }
    
    .quick-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style><?php
/* End of admin-analytics.php */
?>