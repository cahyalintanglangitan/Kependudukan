<?php
// pages/dashboard/disabilitas.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Disabilitas - Dashboard Kependudukan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard/disabilitas.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Data Disabilitas</h1>
            <p>Distribusi penduduk berdasarkan jenis disabilitas</p>
        </div>

        <!-- Filter Controls -->
        <div class="filter-controls">
    <div class="filter-title">
        <i class="fas fa-filter"></i>
        Filter Data Disabilitas
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
            <option value="fisik_desc">Fisik Tertinggi</option>
            <option value="mental_desc">Mental Tertinggi</option>
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
            <div class="stat-card fisik">
                <h3>Disabilitas Fisik</h3>
                <div class="value" id="statFisik">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card netra">
                <h3>Disabilitas Netra</h3>
                <div class="value" id="statNetra">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card rungu">
                <h3>Disabilitas Rungu/Wicara</h3>
                <div class="value" id="statRungu">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="stat-card total">
                <h3>Total Disabilitas</h3>
                <div class="value" id="statTotal">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>

        <!-- Charts Container -->
        <div class="charts-container">
            <!-- Bar Chart -->
            <div class="chart-card">
                <h3>Distribusi Disabilitas per Wilayah</h3>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-color fisik"></span>Disabilitas Fisik</span>
                    <span class="legend-item"><span class="legend-color netra"></span>Disabilitas Netra</span>
                    <span class="legend-item"><span class="legend-color rungu"></span>Disabilitas Rungu/Wicara</span>
                    <span class="legend-item"><span class="legend-color mental"></span>Disabilitas Mental</span>
                    <span class="legend-item"><span class="legend-color fisik-mental"></span>Disabilitas Fisik & Mental</span>
                    <span class="legend-item"><span class="legend-color lainnya"></span>Disabilitas Lainnya</span>
                </div>
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="chart-card">
                <h3>Proporsi Jenis Disabilitas</h3>
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
<script src="../../assets/js/dashboard/disabilitas.js"></script>
</body>
</html>