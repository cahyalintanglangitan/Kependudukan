<?php
include '../config/database.php';

// Filter parameters
$jenis_kk = isset($_GET['jenis_kk']) ? $_GET['jenis_kk'] : '';
$tipe_wilayah = isset($_GET['tipe_wilayah']) ? $_GET['tipe_wilayah'] : '';
$pilih_provinsi = isset($_GET['pilih_provinsi']) ? $_GET['pilih_provinsi'] : '';
$urutan = isset($_GET['urutan']) ? $_GET['urutan'] : 'terbanyak';

// Build WHERE clause
$where_conditions = [];
$where_clause = "";

if (!empty($jenis_kk)) {
    if ($jenis_kk === 'laki') {
        $where_conditions[] = "kk.jenis_kelamin = 'L'";
    } elseif ($jenis_kk === 'perempuan') {
        $where_conditions[] = "kk.jenis_kelamin = 'P'";
    }
}

if (!empty($pilih_provinsi)) {
    $where_conditions[] = "kw.KODE_WILAYAH LIKE '" . mysqli_real_escape_string($conn, $pilih_provinsi) . "%'";
}

if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Order clause
$order_clause = $urutan === 'tersedikit' ? 'ASC' : 'DESC';

// Query untuk mendapatkan data kepala keluarga per wilayah
$query = "SELECT 
    kw.NAMA_WILAYAH as wilayah,
    COUNT(kk.id) as total_kk,
    SUM(CASE WHEN kk.jenis_kelamin = 'L' THEN 1 ELSE 0 END) as laki_laki,
    SUM(CASE WHEN kk.jenis_kelamin = 'P' THEN 1 ELSE 0 END) as perempuan,
    ROUND((SUM(CASE WHEN kk.jenis_kelamin = 'L' THEN 1 ELSE 0 END) / COUNT(kk.id) * 100), 0) as persentase_laki
FROM kepala_keluarga kk 
JOIN kode_wilayah kw ON kk.kode_wilayah = kw.KODE_WILAYAH 
$where_clause
GROUP BY kw.KODE_WILAYAH, kw.NAMA_WILAYAH
ORDER BY COUNT(kk.id) $order_clause";

$result = mysqli_query($conn, $query);

// Count total records
$count_query = "SELECT COUNT(DISTINCT kw.KODE_WILAYAH) as total_records
FROM kepala_keluarga kk 
JOIN kode_wilayah kw ON kk.kode_wilayah = kw.KODE_WILAYAH 
$where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total_records'];

// Summary totals
$total_query = "SELECT 
    COUNT(*) as total_kk,
    SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as total_laki,
    SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as total_perempuan
FROM kepala_keluarga kk
JOIN kode_wilayah kw ON kk.kode_wilayah = kw.KODE_WILAYAH
$where_clause";

$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kepala Keluarga - Analytics Kependudukan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard/style.css" rel="stylesheet">
    <link href="../assets/css/kepala_keluarga.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <div>
                        <h1 class="h2"><i class="fas fa-home me-2"></i>Data Kepala Keluarga</h1>
                        <p class="text-muted">Distribusi kepala keluarga berdasarkan jenis kelamin dan wilayah</p>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filter Data Kepala Keluarga</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="jenis_kk" class="form-label">Jenis Kepala Keluarga</label>
                                    <select class="form-select" id="jenis_kk" name="jenis_kk">
                                        <option value="">Semua Kepala Keluarga</option>
                                        <option value="laki" <?php echo $jenis_kk === 'laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="perempuan" <?php echo $jenis_kk === 'perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="tipe_wilayah" class="form-label">Tipe Wilayah</label>
                                    <select class="form-select" id="tipe_wilayah" name="tipe_wilayah">
                                        <option value="">Semua Wilayah</option>
                                        <option value="kabupaten" <?php echo $tipe_wilayah === 'kabupaten' ? 'selected' : ''; ?>>Kabupaten</option>
                                        <option value="kota" <?php echo $tipe_wilayah === 'kota' ? 'selected' : ''; ?>>Kota</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="pilih_provinsi" class="form-label">Pilih Provinsi</label>
                                    <select class="form-select" id="pilih_provinsi" name="pilih_provinsi">
                                        <option value="">Semua Provinsi</option>
                                        <?php
                                        $provinsi_query = "SELECT DISTINCT LEFT(KODE_WILAYAH, 2) as kode_prov, 
                                                          SUBSTRING_INDEX(NAMA_WILAYAH, ' ', 2) as nama_prov 
                                                          FROM kode_wilayah 
                                                          WHERE LENGTH(KODE_WILAYAH) = 4 
                                                          ORDER BY nama_prov";
                                        $provinsi_result = mysqli_query($conn, $provinsi_query);
                                        while($prov = mysqli_fetch_assoc($provinsi_result)) {
                                            $selected = $pilih_provinsi === $prov['kode_prov'] ? 'selected' : '';
                                            echo "<option value='{$prov['kode_prov']}' $selected>{$prov['nama_prov']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="urutan" class="form-label">Urutkan Berdasarkan</label>
                                    <select class="form-select" id="urutan" name="urutan">
                                        <option value="terbanyak" <?php echo $urutan === 'terbanyak' ? 'selected' : ''; ?>>Jumlah Terbanyak</option>
                                        <option value="tersedikit" <?php echo $urutan === 'tersedikit' ? 'selected' : ''; ?>>Jumlah Tersedikit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-sync me-1"></i> Refresh Data
                                    </button>
                                    <a href="?" class="btn btn-secondary">
                                        <i class="fas fa-undo me-1"></i> Reset Filter
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="text-end mb-3">
                    <small class="text-muted">Menampilkan <?php echo $total_records; ?> record</small>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="summary-card gradient-blue">
                            <div class="card-body text-center text-white">
                                <div class="summary-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <h6 class="card-subtitle mb-2">TOTAL KK</h6>
                                <h2 class="mb-0"><?php echo number_format($total_data['total_kk'] ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card gradient-green">
                            <div class="card-body text-center text-white">
                                <div class="summary-icon">
                                    <i class="fas fa-male"></i>
                                </div>
                                <h6 class="card-subtitle mb-2">KK LAKI-LAKI</h6>
                                <h2 class="mb-0"><?php echo number_format($total_data['total_laki'] ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card gradient-red">
                            <div class="card-body text-center text-white">
                                <div class="summary-icon">
                                    <i class="fas fa-female"></i>
                                </div>
                                <h6 class="card-subtitle mb-2">KK PEREMPUAN</h6>
                                <h2 class="mb-0"><?php echo number_format($total_data['total_perempuan'] ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card gradient-purple">
                            <div class="card-body text-center text-white">
                                <div class="summary-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <h6 class="card-subtitle mb-2">% LAKI-LAKI</h6>
                                <h2 class="mb-0"><?php echo $total_data['total_kk'] > 0 ? round(($total_data['total_laki'] / $total_data['total_kk']) * 100, 1) : 0; ?>%</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Data Section -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Data Kepala Keluarga per Wilayah</h5>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-success btn-sm" onclick="exportCSV()">
                                    <i class="fas fa-download me-1"></i> Export CSV
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-map-marker-alt me-1"></i>Wilayah</th>
                                        <th class="text-center"><i class="fas fa-home me-1"></i>Total KK</th>
                                        <th class="text-center"><i class="fas fa-male me-1"></i>Laki-laki</th>
                                        <th class="text-center"><i class="fas fa-female me-1"></i>Perempuan</th>
                                        <th class="text-center"><i class="fas fa-chart-pie me-1"></i>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($result) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($row['wilayah']); ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6 px-3 py-2">
                                                    <?php echo number_format($row['total_kk']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success fs-6 px-3 py-2">
                                                    <?php echo number_format($row['laki_laki']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger fs-6 px-3 py-2">
                                                    <?php echo number_format($row['perempuan']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="badge <?php echo $row['persentase_laki'] > 50 ? 'bg-info' : 'bg-warning'; ?> fs-6 px-3 py-2 me-2">
                                                        <?php echo $row['persentase_laki']; ?>%
                                                    </span>
                                                    <div class="progress" style="width: 80px; height: 10px;">
                                                        <div class="progress-bar <?php echo $row['persentase_laki'] > 50 ? 'bg-info' : 'bg-warning'; ?>" 
                                                             style="width: <?php echo $row['persentase_laki']; ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-search fa-3x mb-3"></i>
                                                    <p class="fs-5">Tidak ada data yang ditemukan</p>
                                                    <p>Coba ubah filter pencarian Anda</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportCSV() {
            const params = new URLSearchParams(window.location.search);
            window.open(`export_kepala_keluarga.php?${params.toString()}`, '_blank');
        }

        // Auto-refresh setiap 5 menit
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
