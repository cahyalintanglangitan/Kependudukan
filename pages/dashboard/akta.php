<?php
// pages/dashboard/akta.php
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
    <!-- Include Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Data Akta Kependudukan</h1>
            <p>Distribusi kepemilikan akta cerai, akta lahir, dan akta mati</p>
        </div>

        <!-- Filter Controls -->
        <div class="filter-controls">
            <div class="filter-title">
                <i class="fas fa-filter"></i>
                Filter Data Akta
            </div>
            
            <div class="filter-group">
                <label for="aktaTypeFilter">Jenis Akta</label>
                <select id="aktaTypeFilter">
                    <option value="all">Semua Akta</option>
                    <option value="akta_cerai">Akta Cerai</option>
                    <option value="akta_lahir">Akta Lahir</option>
                    <option value="akta_mati">Akta Mati</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="regionTypeFilter">Tipe Wilayah</label>
                <select id="regionTypeFilter">
                    <option value="all">Semua Wilayah</option>
                    <option value="province">Provinsi</option>
                    <option value="kabupaten">Kabupaten</option>
                    <option value="kota">Kota</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="provinceFilter">Pilih Provinsi</label>
                <select id="provinceFilter">
                    <option value="all">Semua Provinsi</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="sortFilter">Urutkan Berdasarkan</label>
                <select id="sortFilter">
                    <option value="memiliki_desc">Kepemilikan Tertinggi</option>
                    <option value="wajib_desc">Wajib Akta Tertinggi</option>
                    <option value="name_asc">Nama A-Z</option>
                    <option value="name_desc">Nama Z-A</option>
                </select>
            </div>

            <div class="filter-group">
                <label>&nbsp;</label>
                <button id="refreshBtn" class="btn-refresh">
                    <i class="fas fa-sync-alt"></i>
                    Refresh Data
                </button>
            </div>

            <div class="filter-stats" id="filterStats">
                Menampilkan <span id="dataCount">0</span> record
            </div>
        </div>

        <!-- Overall Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card cerai">
                <h3>Total Akta Cerai</h3>
                <div class="value" id="statCerai">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card lahir">
                <h3>Total Akta Lahir</h3>
                <div class="value" id="statLahir">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card mati">
                <h3>Total Akta Mati</h3>
                <div class="value" id="statMati">
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

        <!-- Akta Tabs -->
        <div class="akta-tabs">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab="akta_cerai">
                    <i class="fas fa-file-contract"></i>
                    Akta Cerai
                </button>
                <button class="tab-button" data-tab="akta_lahir">
                    <i class="fas fa-baby"></i>
                    Akta Lahir
                </button>
                <button class="tab-button" data-tab="akta_mati">
                    <i class="fas fa-cross"></i>
                    Akta Mati
                </button>
            </div>

            <!-- Akta Cerai Tab Content -->
            <div id="akta_cerai-content" class="tab-content active">
                <!-- Stats for this tab -->
                <div class="stats-grid" id="akta_cerai-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <!-- Charts -->
                <div class="akta-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Kepemilikan Akta Cerai</h3>
                        <div class="chart-container">
                            <canvas id="akta_cerai-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Kepemilikan</h3>
                        <div class="chart-container">
                            <canvas id="akta_cerai-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table-container">
                    <div class="export-controls">
                        <button class="btn-export" onclick="window.aktaDashboard?.exportData()">
                            <i class="fas fa-download"></i>
                            Export CSV
                        </button>
                    </div>
                    <div id="akta_cerai-table">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Akta Lahir Tab Content -->
            <div id="akta_lahir-content" class="tab-content">
                <!-- Stats for this tab -->
                <div class="stats-grid" id="akta_lahir-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <!-- Charts -->
                <div class="akta-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Kepemilikan Akta Lahir</h3>
                        <div class="chart-container">
                            <canvas id="akta_lahir-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Kepemilikan</h3>
                        <div class="chart-container">
                            <canvas id="akta_lahir-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table-container">
                    <div class="export-controls">
                        <button class="btn-export" onclick="window.aktaDashboard?.exportData()">
                            <i class="fas fa-download"></i>
                            Export CSV
                        </button>
                    </div>
                    <div id="akta_lahir-table">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Akta Mati Tab Content -->
            <div id="akta_mati-content" class="tab-content">
                <!-- Stats for this tab -->
                <div class="stats-grid" id="akta_mati-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>

                <!-- Charts -->
                <div class="akta-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Akta Mati per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="akta_mati-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Berdasarkan Gender</h3>
                        <div class="chart-container">
                            <canvas id="akta_mati-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table-container">
                    <div class="export-controls">
                        <button class="btn-export" onclick="window.aktaDashboard?.exportData()">
                            <i class="fas fa-download"></i>
                            Export CSV
                        </button>
                    </div>
                    <div id="akta_mati-table">
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
    <script src="../../assets/js/dashboard/akta.js"></script>
</body>
</html>
