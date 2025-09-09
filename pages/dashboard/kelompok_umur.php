<?php
// pages/dashboard/kelompok_umur.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelompok Umur - Dashboard Kependudukan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard/kelompok_umur.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Data Kelompok Umur</h1>
            <p>Distribusi penduduk berdasarkan kelompok umur di berbagai wilayah</p>
        </div>

        <!-- Overall Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card balita">
                <h3>Total Balita (0-4)</h3>
                <div class="value" id="statBalita">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card anak">
                <h3>Total Anak (5-14)</h3>
                <div class="value" id="statAnak">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card dewasa">
                <h3>Total Dewasa (15-59)</h3>
                <div class="value" id="statDewasa">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card lansia">
                <h3>Total Lansia (60+)</h3>
                <div class="value" id="statLansia">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card total">
                <h3>Total Keseluruhan</h3>
                <div class="value" id="statTotal">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>

        <!-- Kelompok Umur Tabs -->
        <div class="kelompok-umur-tabs">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab="kelompok_detail">
                    <i class="fas fa-users"></i>
                    Detail Kelompok Umur
                </button>
                <button class="tab-button" data-tab="distribusi_wilayah">
                    <i class="fas fa-map-marker-alt"></i>
                    Distribusi Wilayah
                </button>
            </div>

            <!-- Detail Kelompok Umur Tab Content -->
            <div id="kelompok_detail-content" class="tab-content active">
                <!-- Stats for this tab -->
                <div class="stats-grid" id="kelompok_detail-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <!-- Charts Container -->
                <div class="kelompok-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Kelompok Umur per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="kelompok_detail-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Kelompok Umur Keseluruhan</h3>
                        <div class="chart-container">
                            <canvas id="kelompok_detail-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Age Group Distribution Chart -->
                <div class="age-distribution-container">
                    <div class="age-chart-full">
                        <h3>Piramida Penduduk Berdasarkan Kelompok Umur</h3>
                        <div class="chart-container">
                            <canvas id="kelompok_detail-pyramid-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table-container">
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="kelompok_detail-search">Cari:</label>
                            <input type="text" id="kelompok_detail-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="kelompok_detail-sort">Urutkan:</label>
                            <select id="kelompok_detail-sort" class="sort-select">
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="00_04_desc">0-4 Tahun Tertinggi</option>
                                <option value="00_04_asc">0-4 Tahun Terendah</option>
                                <option value="05_09_desc">5-9 Tahun Tertinggi</option>
                                <option value="05_09_asc">5-9 Tahun Terendah</option>
                                <option value="10_14_desc">10-14 Tahun Tertinggi</option>
                                <option value="10_14_asc">10-14 Tahun Terendah</option>
                                <option value="15_19_desc">15-19 Tahun Tertinggi</option>
                                <option value="15_19_asc">15-19 Tahun Terendah</option>
                                <option value="total_desc">Total Tertinggi</option>
                                <option value="total_asc">Total Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-export" onclick="window.kelompokUmurDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
                    </div>
                    <div id="kelompok_detail-table">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Distribusi Wilayah Tab Content -->
            <div id="distribusi_wilayah-content" class="tab-content">
                <!-- Stats for this tab -->
                <div class="stats-grid" id="distribusi_wilayah-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <!-- Regional Distribution Charts -->
                <div class="kelompok-chart-container">
                    <div class="comparison-chart">
                        <h3>Perbandingan Kelompok Umur Antar Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="distribusi_wilayah-comparison-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Distribusi Berdasarkan Kategori Umur</h3>
                        <div class="chart-container">
                            <canvas id="distribusi_wilayah-category-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Heatmap Style Distribution -->
                <div class="heatmap-container">
                    <div class="heatmap-chart">
                        <h3>Peta Sebaran Kelompok Umur</h3>
                        <div class="chart-container">
                            <canvas id="distribusi_wilayah-heatmap-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table-container">
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="distribusi_wilayah-search">Cari:</label>
                            <input type="text" id="distribusi_wilayah-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="distribusi_wilayah-sort">Urutkan:</label>
                            <select id="distribusi_wilayah-sort" class="sort-select">
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="balita_desc">Balita (0-4) Tertinggi</option>
                                <option value="balita_asc">Balita (0-4) Terendah</option>
                                <option value="anak_desc">Anak (5-14) Tertinggi</option>
                                <option value="anak_asc">Anak (5-14) Terendah</option>
                                <option value="remaja_desc">Remaja (15-24) Tertinggi</option>
                                <option value="remaja_asc">Remaja (15-24) Terendah</option>
                                <option value="dewasa_desc">Dewasa (25-59) Tertinggi</option>
                                <option value="dewasa_asc">Dewasa (25-59) Terendah</option>
                                <option value="lansia_desc">Lansia (60+) Tertinggi</option>
                                <option value="lansia_asc">Lansia (60+) Terendah</option>
                                <option value="total_desc">Total Tertinggi</option>
                                <option value="total_asc">Total Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-export" onclick="window.kelompokUmurDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
                    </div>
                    <div id="distribusi_wilayah-table">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>window.API_BASE_URL = '../../api/';</script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/dashboard/common.js"></script>
    <script src="../../assets/js/dashboard/kelompok_umur.js"></script>
</body>
</html>
