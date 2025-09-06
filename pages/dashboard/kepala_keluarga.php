<?php
include_once '../../includes/sidebar.php';

// Query untuk menghitung statistik
$queryTotal = "SELECT COUNT(*) as total FROM data_kk";
$queryLaki = "SELECT COUNT(*) as total FROM data_kk WHERE jenis_kelamin = 'Laki-laki'";
$queryPerempuan = "SELECT COUNT(*) as total FROM data_kk WHERE jenis_kelamin = 'Perempuan'";

$resultTotal = $conn->query($queryTotal);
$resultLaki = $conn->query($queryLaki);
$resultPerempuan = $conn->query($queryPerempuan);

$totalKK = $resultTotal->fetch_assoc()['total'];
$totalLaki = $resultLaki->fetch_assoc()['total'];
$totalPerempuan = $resultPerempuan->fetch_assoc()['total'];

// Query untuk provinsi (untuk dropdown filter)
$queryProvinsi = "SELECT DISTINCT kode_wilayah, nama_wilayah FROM wilayah WHERE LENGTH(kode_wilayah) = 2 ORDER BY nama_wilayah";
$resultProvinsi = $conn->query($queryProvinsi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kepala Keluarga - Data Analytics Kependudukan</title>
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar sudah di-include dari sidebar.php -->
        
        <div class="main-content">
            <div class="content-header">
                <h2>Data Kepala Keluarga</h2>
                <p class="content-description">Distribusi penduduk berdasarkan status kepala keluarga</p>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-header">
                    <i class="fas fa-filter"></i>
                    <span>Filter Data Kepala Keluarga</span>
                </div>
                <div class="filter-content">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="tipeWilayah">TIPE WILAYAH</label>
                            <select id="tipeWilayah" class="filter-select">
                                <option value="">Provinsi</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="pilihProvinsi">PILIH PROVINSI</label>
                            <select id="pilihProvinsi" class="filter-select">
                                <option value="">Semua Provinsi</option>
                                <?php while($row = $resultProvinsi->fetch_assoc()): ?>
                                    <option value="<?= $row['kode_wilayah'] ?>"><?= $row['nama_wilayah'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="urutanBerdasarkan">URUTAN BERDASARKAN</label>
                            <select id="urutanBerdasarkan" class="filter-select">
                                <option value="">Tidak Terdapat</option>
                                <option value="nama_asc">Nama A-Z</option>
                                <option value="nama_desc">Nama Z-A</option>
                                <option value="umur_asc">Umur Termuda</option>
                                <option value="umur_desc">Umur Tertua</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button id="refreshData" class="refresh-btn">
                                <i class="fas fa-sync-alt"></i>
                                REFRESH DATA
                            </button>
                            <div class="data-info">
                                Menampilkan <span id="dataCount"><?= $totalKK ?></span> wilayah
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card stat-card-blue">
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($totalKK) ?></div>
                        <div class="stat-label">TOTAL KEPALA KELUARGA</div>
                    </div>
                </div>
                <div class="stat-card stat-card-green">
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($totalLaki) ?></div>
                        <div class="stat-label">KEPALA KELUARGA LAKI-LAKI</div>
                    </div>
                </div>
                <div class="stat-card stat-card-orange">
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($totalPerempuan) ?></div>
                        <div class="stat-label">KEPALA KELUARGA PEREMPUAN</div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-container">
                    <h3>Distribusi Kepala Keluarga per Wilayah</h3>
                    <canvas id="kkChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Proporsi Jenis Kelamin Kepala Keluarga</h3>
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Data Detail Kepala Keluarga</h3>
                    <div class="table-actions">
                        <button class="btn-export">
                            <i class="fas fa-download"></i>
                            Export Data
                        </button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table id="kkTable" class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kepala Keluarga</th>
                                <th>NIK</th>
                                <th>Jenis Kelamin</th>
                                <th>Umur</th>
                                <th>Pekerjaan</th>
                                <th>Alamat</th>
                                <th>Jumlah Anggota</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="kkTableBody">
                            <!-- Data akan dimuat via JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="table-pagination">
                    <div class="pagination-info">
                        Menampilkan <span id="showingStart">1</span> - <span id="showingEnd">10</span> dari <span id="totalData"><?= $totalKK ?></span> data
                    </div>
                    <div class="pagination-controls">
                        <button id="prevPage" class="pagination-btn" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="currentPage">1</span>
                        <button id="nextPage" class="pagination-btn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/dashboard/kepala_keluarga.js"></script>
</body>
</html>
