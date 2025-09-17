<?php
// pages/dashboard/akta.php - VERSI FINAL
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Akta - Dashboard Kependudukan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard/akta.css">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Data Akta Kependudukan</h1>
            <p>Distribusi kepemilikan akta cerai, akta lahir, dan akta mati</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card cerai">
                <h3>Total Akta Cerai</h3>
                <div class="value" id="statCerai"><div class="loading-spinner"></div></div>
            </div>
            <div class="stat-card lahir">
                <h3>Total Akta Lahir</h3>
                <div class="value" id="statLahir"><div class="loading-spinner"></div></div>
            </div>
            <div class="stat-card mati">
                <h3>Total Akta Mati</h3>
                <div class="value" id="statMati"><div class="loading-spinner"></div></div>
            </div>
            <div class="stat-card total">
                <h3>Total Keseluruhan</h3>
                <div class="value" id="statTotal"><div class="loading-spinner"></div></div>
            </div>
        </div>

        <div class="akta-tabs">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab="akta_cerai"><i class="fas fa-file-contract"></i> Akta Cerai</button>
                <button class="tab-button" data-tab="akta_lahir"><i class="fas fa-baby"></i> Akta Lahir</button>
                <button class="tab-button" data-tab="akta_mati"><i class="fas fa-cross"></i> Akta Mati</button>
            </div>

            <?php 
            $tabs = [
                'akta_cerai' => [
                    'title' => 'Akta Cerai',
                    'sort_options' => [
                        'wilayah_asc' => 'Wilayah A-Z', 'wilayah_desc' => 'Wilayah Z-A',
                        'wajib_desc' => 'Wajib Tertinggi', 'wajib_asc' => 'Wajib Terendah',
                        'memiliki_desc' => 'Memiliki Tertinggi', 'memiliki_asc' => 'Memiliki Terendah',
                        'belum_memiliki_desc' => 'Belum Tertinggi', 'belum_memiliki_asc' => 'Belum Terendah',
                        'persentase_desc' => 'Persentase Tertinggi', 'persentase_asc' => 'Persentase Terendah'
                    ]
                ],
                'akta_lahir' => [
                    'title' => 'Akta Lahir',
                    'sort_options' => [
                        'wilayah_asc' => 'Wilayah A-Z', 'wilayah_desc' => 'Wilayah Z-A',
                        'wajib_desc' => 'Wajib Tertinggi', 'wajib_asc' => 'Wajib Terendah',
                        'memiliki_desc' => 'Memiliki Tertinggi', 'memiliki_asc' => 'Memiliki Terendah',
                        'belum_memiliki_desc' => 'Belum Tertinggi', 'belum_memiliki_asc' => 'Belum Terendah',
                        'persentase_desc' => 'Persentase Tertinggi', 'persentase_asc' => 'Persentase Terendah'
                    ]
                ],
                'akta_mati' => [
                    'title' => 'Akta Mati',
                    'sort_options' => [
                        'wilayah_asc' => 'Wilayah A-Z', 'wilayah_desc' => 'Wilayah Z-A',
                        'laki_laki_desc' => 'Laki-laki Tertinggi', 'laki_laki_asc' => 'Laki-laki Terendah',
                        'perempuan_desc' => 'Perempuan Tertinggi', 'perempuan_asc' => 'Perempuan Terendah',
                        'total_desc' => 'Total Tertinggi', 'total_asc' => 'Total Terendah'
                    ]
                ]
            ];
            $is_first_tab = true;
            foreach ($tabs as $tab_id => $tab_details):
            ?>
            <div id="<?php echo $tab_id; ?>-content" class="tab-content <?php echo $is_first_tab ? 'active' : ''; ?>">
                <div class="stats-grid" id="<?php echo $tab_id; ?>-stats"></div>
                <div class="akta-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi <?php echo $tab_details['title']; ?> per Wilayah</h3>
                        <div class="chart-container"><canvas id="<?php echo $tab_id; ?>-bar-chart"></canvas></div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Kepemilikan <?php echo $tab_details['title']; ?></h3>
                        <div class="chart-container"><canvas id="<?php echo $tab_id; ?>-pie-chart"></canvas></div>
                    </div>
                </div>
                <div class="data-table-container">
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="<?php echo $tab_id; ?>-search">Cari:</label>
                            <input type="text" id="<?php echo $tab_id; ?>-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="<?php echo $tab_id; ?>-region-type">Tipe Wilayah:</label>
                            <select id="<?php echo $tab_id; ?>-region-type" class="sort-select">
                                <option value="all">Semua Wilayah</option>
                                <option value="provinsi">Provinsi</option>
                                <option value="kabupaten">Kota/Kabupaten</option>
                            </select>
                        </div>
                        <div class="sort-container">
                            <label for="<?php echo $tab_id; ?>-sort">Urutkan:</label>
                            <select id="<?php echo $tab_id; ?>-sort" class="sort-select">
                                <?php foreach($tab_details['sort_options'] as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="<?php echo $tab_id; ?>-refresh"><i class="fas fa-sync-alt"></i> Refresh</button>
                            <button class="btn-export" onclick="window.aktaDashboard?.exportData()"><i class="fas fa-download"></i> Export</button>
                        </div>
                    </div>
                    <div id="<?php echo $tab_id; ?>-table">
                        </div>
                </div>
            </div>
            <?php 
            $is_first_tab = false;
            endforeach; 
            ?>
        </div>
    </div>

    <script>window.API_BASE_URL = '../../api/';</script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/dashboard/common.js"></script>
    <script src="../../assets/js/dashboard/akta.js"></script>
</body>
</html>