<?php
// pages/dashboard/demografi.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Demografi - Analytics Kependudukan</title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/dashboard/demografi.css" rel="stylesheet">
</head>
<body class="demografi-page">
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Data Demografi</h1>
                <p>Distribusi populasi berdasarkan jenis kelamin per wilayah</p>
            </div>

            <!-- Filter Controls -->
            <div class="filter-controls">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    Filter Data Demografi
                </div>
                
                <div class="filter-group">
                    <label for="regionTypeFilter">Tipe Wilayah</label>
                    <select id="regionTypeFilter">
                        <option value="all">Semua Wilayah</option>
                        <option value="province">Provinsi</option>
                        <option value="kabupaten">Kabupaten/Kota</option>
                        <option value="kecamatan">Kecamatan</option>
                        <option value="desa">Desa/Kelurahan</option>
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
                        <option value="total_desc">Total Populasi (Tertinggi)</option>
                        <option value="total_asc">Total Populasi (Terendah)</option>
                        <option value="laki_laki_desc">Laki-laki (Tertinggi)</option>
                        <option value="perempuan_desc">Perempuan (Tertinggi)</option>
                        <option value="name_asc">Nama Wilayah (A-Z)</option>
                        <option value="name_desc">Nama Wilayah (Z-A)</option>
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Laki-laki
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="statLakiLaki">
                                        <div class="loading-spinner"></div>
                                    </div>
                                </div>
                                <!-- Icon removed -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Perempuan
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="statPerempuan">
                                        <div class="loading-spinner"></div>
                                    </div>
                                </div>
                                <!-- Icon removed -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Populasi
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="statTotal">
                                        <div class="loading-spinner"></div>
                                    </div>
                                </div>
                                <!-- Icon removed -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <!-- Bar Chart -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Distribusi Populasi per Wilayah</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                                     aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Aksi Chart:</div>
                                    <a class="dropdown-item" href="#" id="downloadBarChart">Download PNG</a>
                                    <a class="dropdown-item" href="#" id="printBarChart">Print Chart</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="barChart" width="400" height="200"></canvas>
                            </div>
                            <div class="mt-3">
                                <div class="row text-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="legend-color" style="background-color: #3498db;"></div>
                                            <span class="ms-2">Laki-laki</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="legend-color" style="background-color: #e91e63;"></div>
                                            <span class="ms-2">Perempuan</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Proporsi Jenis Kelamin</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink2" 
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                                     aria-labelledby="dropdownMenuLink2">
                                    <div class="dropdown-header">Aksi Chart:</div>
                                    <a class="dropdown-item" href="#" id="downloadPieChart">Download PNG</a>
                                    <a class="dropdown-item" href="#" id="printPieChart">Print Chart</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="pieChart" width="400" height="300"></canvas>
                            </div>
                            <div class="mt-4 text-center small">
                                <span class="mr-2">
                                    <i class="fas fa-circle text-primary"></i> Laki-laki
                                </span>
                                <span class="mr-2">
                                    <i class="fas fa-circle" style="color: #e91e63;"></i> Perempuan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="row">
                <div class="col-12">
                    <div class="data-table-container">
                        <!-- Table Controls Header - Style seperti Akta -->
                        <div class="table-controls-header">
                            <div class="search-container">
                                <label for="searchWilayah">Cari:</label>
                                <input type="text" id="searchWilayah" placeholder="Cari wilayah..." class="search-input">
                            </div>
                            <div class="sort-container">
                                <label for="sortFilter">Urutkan:</label>
                                <select id="sortFilterTable" class="sort-select">
                                    <option value="kode_asc">Kode Wilayah A-Z</option>
                                    <option value="kode_desc">Kode Wilayah Z-A</option>
                                    <option value="wilayah_asc">Wilayah A-Z</option>
                                    <option value="wilayah_desc">Wilayah Z-A</option>
                                    <option value="laki_laki_desc">Laki-laki Tertinggi</option>
                                    <option value="laki_laki_asc">Laki-laki Terendah</option>
                                    <option value="perempuan_desc">Perempuan Tertinggi</option>
                                    <option value="perempuan_asc">Perempuan Terendah</option>
                                    <option value="total_desc" selected>Total Tertinggi</option>
                                    <option value="total_asc">Total Terendah</option>
                                </select>
                            </div>
                            <div class="export-controls">
                                <button class="btn-refresh" id="refreshTableBtn">
                                    <i class="fas fa-sync-alt"></i>
                                    Refresh
                                </button>
                                <button class="btn-export" id="exportBtn">
                                    <i class="fas fa-download"></i>
                                    Export CSV
                                </button>
                            </div>
                        </div>
                        
                        <!-- Table Content -->
                        <div id="demografiTable">
                            <!-- Table will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <!-- Scripts -->
    <script>window.API_BASE_URL = '../../api/';</script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/sidebar.js"></script>
    <script src="../../assets/js/dashboard/common.js"></script>
    <script src="../../assets/js/dashboard/demografi.js"></script>
</body>
</html>