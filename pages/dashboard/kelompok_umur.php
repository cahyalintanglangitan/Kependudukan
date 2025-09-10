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
            <p>Distribusi penduduk berdasarkan kelompok umur dan wilayah</p>
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
                <button class="tab-button active" data-tab="overview">
                    <i class="fas fa-chart-pie"></i>
                    Overview
                </button>
                <button class="tab-button" data-tab="balita">
                    <i class="fas fa-baby"></i>
                    Balita (0-4)
                </button>
                <button class="tab-button" data-tab="anak">
                    <i class="fas fa-child"></i>
                    Anak (5-14)
                </button>
                <button class="tab-button" data-tab="dewasa">
                    <i class="fas fa-user"></i>
                    Dewasa (15-59)
                </button>
                <button class="tab-button" data-tab="lansia">
                    <i class="fas fa-walking-cane"></i>
                    Lansia (60+)
                </button>
            </div>

            <!-- Overview Tab Content -->
            <div id="overview-content" class="tab-content active">
                <!-- Detail Kelompok Umur Stats -->
                <div class="detail-stats-grid">
                    <div class="detail-stat-card">
                        <h4>0-4 Tahun</h4>
                        <div class="stat-value">25,837</div>
                        <div class="stat-percentage">5.4%</div>
                    </div>
                    <div class="detail-stat-card">
                        <h4>5-9 Tahun</h4>
                        <div class="stat-value">34,349</div>
                        <div class="stat-percentage">7.2%</div>
                    </div>
                    <div class="detail-stat-card">
                        <h4>10-14 Tahun</h4>
                        <div class="stat-value">35,853</div>
                        <div class="stat-percentage">7.5%</div>
                    </div>
                    <div class="detail-stat-card">
                        <h4>15-19 Tahun</h4>
                        <div class="stat-value">32,412</div>
                        <div class="stat-percentage">6.8%</div>
                    </div>
                </div>

                <!-- Overview Charts -->
                <div class="overview-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Kelompok Umur</h3>
                        <div class="chart-container">
                            <canvas id="overview-pie-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Perbandingan Kelompok Umur per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="overview-bar-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Overview Table -->
                <div class="data-table-container">
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="overview-search">Cari:</label>
                            <input type="text" id="overview-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="overview-sort">Urutkan:</label>
                            <select id="overview-sort" class="sort-select">
                                <option value="kode_asc">Kode Wilayah A-Z</option>
                                <option value="kode_desc">Kode Wilayah Z-A</option>
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="total_desc">Total Tertinggi</option>
                                <option value="total_asc">Total Terendah</option>
                                <option value="balita_desc">Balita Tertinggi</option>
                                <option value="anak_desc">Anak Tertinggi</option>
                                <option value="dewasa_desc">Dewasa Tertinggi</option>
                                <option value="lansia_desc">Lansia Tertinggi</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="overview-refresh">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn-export" onclick="window.kelompokUmurDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
                    </div>
                    <div id="overview-table">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Balita Tab Content -->
            <div id="balita-content" class="tab-content">
                <div class="stats-grid" id="balita-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <div class="kelompok-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Balita (0-4) per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="balita-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Balita per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="balita-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="data-table-container">
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="balita-search">Cari:</label>
                            <input type="text" id="balita-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="balita-sort">Urutkan:</label>
                            <select id="balita-sort" class="sort-select">
                                <option value="kode_asc">Kode Wilayah A-Z</option>
                                <option value="kode_desc">Kode Wilayah Z-A</option>
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="balita_desc">Jumlah Balita Tertinggi</option>
                                <option value="balita_asc">Jumlah Balita Terendah</option>
                                <option value="persentase_desc">Persentase Tertinggi</option>
                                <option value="persentase_asc">Persentase Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="balita-refresh">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn-export" onclick="window.kelompokUmurDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
                    </div>
                    <div id="balita-table">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Anak Tab Content -->
            <div id="anak-content" class="tab-content">
                <div class="stats-grid" id="anak-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <div class="kelompok-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Anak (5-14) per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="anak-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Anak per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="anak-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="data-table-container">
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="anak-search">Cari:</label>
                            <input type="text" id="anak-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="anak-sort">Urutkan:</label>
                            <select id="anak-sort" class="sort-select">
                                <option value="kode_asc">Kode Wilayah A-Z</option>
                                <option value="kode_desc">Kode Wilayah Z-A</option>
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="anak_desc">Jumlah Anak Tertinggi</option>
                                <option value="anak_asc">Jumlah Anak Terendah</option>
                                <option value="persentase_desc">Persentase Tertinggi</option>
                                <option value="persentase_asc">Persentase Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="anak-refresh">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn-export" onclick="window.kelompokUmurDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
                    </div>
                    <div id="anak-table">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Dewasa Tab Content -->
            <div id="dewasa-content" class="tab-content">
                <div class="stats-grid" id="dewasa-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <div class="kelompok-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Dewasa (15-59) per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="dewasa-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Dewasa per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="dewasa-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="data-table-container">
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="dewasa-search">Cari:</label>
                            <input type="text" id="dewasa-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="dewasa-sort">Urutkan:</label>
                            <select id="dewasa-sort" class="sort-select">
                                <option value="kode_asc">Kode Wilayah A-Z</option>
                                <option value="kode_desc">Kode Wilayah Z-A</option>
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="dewasa_desc">Jumlah Dewasa Tertinggi</option>
                                <option value="dewasa_asc">Jumlah Dewasa Terendah</option>
                                <option value="persentase_desc">Persentase Tertinggi</option>
                                <option value="persentase_asc">Persentase Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="dewasa-refresh">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn-export" onclick="window.kelompokUmurDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
                    </div>
                    <div id="dewasa-table">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Lansia Tab Content -->
            <div id="lansia-content" class="tab-content">
                <div class="stats-grid" id="lansia-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <div class="kelompok-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Lansia (60+) per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="lansia-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Lansia per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="lansia-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="data-table-container">
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="lansia-search">Cari:</label>
                            <input type="text" id="lansia-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="lansia-sort">Urutkan:</label>
                            <select id="lansia-sort" class="sort-select">
                                <option value="kode_asc">Kode Wilayah A-Z</option>
                                <option value="kode_desc">Kode Wilayah Z-A</option>
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="lansia_desc">Jumlah Lansia Tertinggi</option>
                                <option value="lansia_asc">Jumlah Lansia Terendah</option>
                                <option value="persentase_desc">Persentase Tertinggi</option>
                                <option value="persentase_asc">Persentase Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="lansia-refresh">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn-export" onclick="window.kelompokUmurDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
                    </div>
                    <div id="lansia-table">
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
