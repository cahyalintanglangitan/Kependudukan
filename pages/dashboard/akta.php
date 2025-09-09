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

                <!-- Removed fake charts, keeping only real database-driven charts -->
                <div class="akta-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Kepemilikan Akta Cerai per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="akta_cerai-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Kepemilikan Akta Cerai</h3>
                        <div class="chart-container">
                            <canvas id="akta_cerai-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table-container">
                    <!-- Added table controls with sorting and search -->
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="akta_cerai-search">Cari:</label>
                            <input type="text" id="akta_cerai-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="akta_cerai-sort">Urutkan:</label>
                            <select id="akta_cerai-sort" class="sort-select">
                                <option value="kode_asc">Kode Wilayah A-Z</option>
                                <option value="kode_desc">Kode Wilayah Z-A</option>
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="wajib_desc">Wajib Tertinggi</option>
                                <option value="wajib_asc">Wajib Terendah</option>
                                <option value="memiliki_desc">Memiliki Tertinggi</option>
                                <option value="memiliki_asc">Memiliki Terendah</option>
                                <option value="belum_memiliki_desc">Belum Memiliki Tertinggi</option>
                                <option value="belum_memiliki_asc">Belum Memiliki Terendah</option>
                                <option value="persentase_desc">Persentase Tertinggi</option>
                                <option value="persentase_asc">Persentase Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="akta_cerai-refresh">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn-export" onclick="window.aktaDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
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

                <!-- Removed fake charts, keeping only real database-driven charts -->
                <div class="akta-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Kepemilikan Akta Lahir per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="akta_lahir-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Kepemilikan Akta Lahir</h3>
                        <div class="chart-container">
                            <canvas id="akta_lahir-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table-container">
                    <!-- Added table controls with sorting and search -->
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="akta_lahir-search">Cari:</label>
                            <input type="text" id="akta_lahir-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="akta_lahir-sort">Urutkan:</label>
                            <select id="akta_lahir-sort" class="sort-select">
                                <option value="kode_asc">Kode Wilayah A-Z</option>
                                <option value="kode_desc">Kode Wilayah Z-A</option>
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="wajib_desc">Wajib Tertinggi</option>
                                <option value="wajib_asc">Wajib Terendah</option>
                                <option value="memiliki_desc">Memiliki Tertinggi</option>
                                <option value="memiliki_asc">Memiliki Terendah</option>
                                <option value="belum_memiliki_desc">Belum Memiliki Tertinggi</option>
                                <option value="belum_memiliki_asc">Belum Memiliki Terendah</option>
                                <option value="persentase_desc">Persentase Tertinggi</option>
                                <option value="persentase_asc">Persentase Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="akta_lahir-refresh">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn-export" onclick="window.aktaDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
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

                <!-- Removed fake charts, keeping only real database-driven charts -->
                <div class="akta-chart-container">
                    <div class="comparison-chart">
                        <h3>Distribusi Akta Mati per Wilayah</h3>
                        <div class="chart-container">
                            <canvas id="akta_mati-bar-chart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-chart">
                        <h3>Proporsi Berdasarkan Jenis Kelamin</h3>
                        <div class="chart-container">
                            <canvas id="akta_mati-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table-container">
                    <!-- Added table controls with sorting and search -->
                    <div class="table-controls-header">
                        <div class="search-container">
                            <label for="akta_mati-search">Cari:</label>
                            <input type="text" id="akta_mati-search" placeholder="Cari wilayah..." class="search-input">
                        </div>
                        <div class="sort-container">
                            <label for="akta_mati-sort">Urutkan:</label>
                            <select id="akta_mati-sort" class="sort-select">
                                <option value="kode_asc">Kode Wilayah A-Z</option>
                                <option value="kode_desc">Kode Wilayah Z-A</option>
                                <option value="wilayah_asc">Wilayah A-Z</option>
                                <option value="wilayah_desc">Wilayah Z-A</option>
                                <option value="laki_laki_desc">Laki-laki Tertinggi</option>
                                <option value="laki_laki_asc">Laki-laki Terendah</option>
                                <option value="perempuan_desc">Perempuan Tertinggi</option>
                                <option value="perempuan_asc">Perempuan Terendah</option>
                                <option value="total_desc">Total Tertinggi</option>
                                <option value="total_asc">Total Terendah</option>
                            </select>
                        </div>
                        <div class="export-controls">
                            <button class="btn-refresh" id="akta_mati-refresh">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn-export" onclick="window.aktaDashboard?.exportData()">
                                <i class="fas fa-download"></i>
                                Export CSV
                            </button>
                        </div>
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
