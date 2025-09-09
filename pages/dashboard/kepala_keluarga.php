<?php
// pages/dashboard/kepala_keluarga.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kepala Keluarga - Dashboard Kependudukan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard/kepala_keluarga.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Data Kepala Keluarga</h1>
            <p>Distribusi kepala keluarga berdasarkan jenis kelamin</p>
        </div>

        <!-- Filter Controls -->
        <div class="filter-controls">
    <div class="filter-title">
        <i class="fas fa-filter"></i>
        Filter Data Kepala Keluarga
    </div>
    
    <div class="filter-group">
        <label for="regionTypeFilter">Tipe Wilayah</label>
        <select id="regionTypeFilter">
            <option value="province">Provinsi</option>
            <option value="kabupaten">Kabupaten</option>
            <option value="kota">Kota</option>
            <option value="all">Semua Wilayah</option>
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
            <option value="total_desc">Total Tertinggi</option>
            <option value="total_asc">Total Terendah</option>
            <option value="name_asc">Nama A-Z</option>
            <option value="name_desc">Nama Z-A</option>
            <option value="laki_desc">Laki-laki Tertinggi</option>
            <option value="perempuan_desc">Perempuan Tertinggi</option>
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
        Menampilkan <span id="dataCount">0</span> wilayah
    </div>
</div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card laki-laki">
                <h3>Kepala Keluarga Laki-laki</h3>
                <div class="value" id="statLakiLaki">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card perempuan">
                <h3>Kepala Keluarga Perempuan</h3>
                <div class="value" id="statPerempuan">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card total">
                <h3>Total Kepala Keluarga</h3>
                <div class="value" id="statTotal">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>

        <!-- Charts Container -->
        <div class="charts-container">
            <!-- Bar Chart -->
            <div class="chart-card">
                <h3>Distribusi Kepala Keluarga per Wilayah</h3>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-color laki-laki"></span>Kepala Keluarga Laki-laki</span>
                    <span class="legend-item"><span class="legend-color perempuan"></span>Kepala Keluarga Perempuan</span>
                </div>
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="chart-card">
                <h3>Proporsi Kepala Keluarga Berdasarkan Jenis Kelamin</h3>
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

<!-- Scripts -->
<script>window.API_BASE_URL = '../../api/';</script>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/api.js"></script>
<script src="../../assets/js/dashboard/common.js"></script>
<script src="../../assets/js/dashboard/kepala_keluarga.js"></script>
</body>
</html>
