<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

// Get action parameter
$action = $_GET['action'] ?? 'data';

try {
    switch($action) {
        case 'data':
            getKepalaKeluargaData();
            break;
        case 'stats':
            getKepalaKeluargaStats();
            break;
        case 'chart':
            getKepalaKeluargaChart();
            break;
        case 'gender':
            getKepalaKeluargaByGender();
            break;
        case 'export':
            exportKepalaKeluargaData();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

// Function to get kepala keluarga data with pagination
function getKepalaKeluargaData() {
    global $pdo;
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    // Count total records
    $countQuery = "SELECT COUNT(*) as total FROM kepala_keluarga";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get data with pagination
    $query = "SELECT 
                kk.id,
                kk.nama_kepala_keluarga,
                kk.nik,
                kk.jenis_kelamin,
                kk.umur,
                kk.alamat,
                kk.rt_rw,
                kk.kelurahan,
                kk.kecamatan,
                kk.jumlah_anggota_keluarga,
                kk.status_kepala_keluarga,
                kk.pekerjaan,
                kk.pendidikan_terakhir,
                DATE_FORMAT(kk.tanggal_daftar, '%d/%m/%Y') as tanggal_daftar
              FROM kepala_keluarga kk 
              ORDER BY kk.nama_kepala_keluarga ASC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_records' => (int)$total,
            'per_page' => $limit
        ]
    ]);
}

// Function to get kepala keluarga statistics
function getKepalaKeluargaStats() {
    global $pdo;
    
    // Total kepala keluarga
    $totalQuery = "SELECT COUNT(*) as total FROM kepala_keluarga";
    $totalStmt = $pdo->prepare($totalQuery);
    $totalStmt->execute();
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Status aktif
    $aktifQuery = "SELECT COUNT(*) as aktif FROM kepala_keluarga WHERE status_kepala_keluarga = 'Aktif'";
    $aktifStmt = $pdo->prepare($aktifQuery);
    $aktifStmt->execute();
    $aktif = $aktifStmt->fetch(PDO::FETCH_ASSOC)['aktif'];
    
    // Status tidak aktif
    $tidakAktifQuery = "SELECT COUNT(*) as tidak_aktif FROM kepala_keluarga WHERE status_kepala_keluarga = 'Tidak Aktif'";
    $tidakAktifStmt = $pdo->prepare($tidakAktifQuery);
    $tidakAktifStmt->execute();
    $tidakAktif = $tidakAktifStmt->fetch(PDO::FETCH_ASSOC)['tidak_aktif'];
    
    // Rata-rata jumlah anggota keluarga
    $avgQuery = "SELECT AVG(jumlah_anggota_keluarga) as rata_rata FROM kepala_keluarga";
    $avgStmt = $pdo->prepare($avgQuery);
    $avgStmt->execute();
    $rataRata = $avgStmt->fetch(PDO::FETCH_ASSOC)['rata_rata'];
    
    // Kelompok umur
    $umurQuery = "SELECT 
                    CASE 
                        WHEN umur < 30 THEN 'Dibawah 30'
                        WHEN umur BETWEEN 30 AND 50 THEN '30-50'
                        ELSE 'Diatas 50'
                    END as kelompok_umur,
                    COUNT(*) as jumlah
                  FROM kepala_keluarga 
                  GROUP BY kelompok_umur";
    $umurStmt = $pdo->prepare($umurQuery);
    $umurStmt->execute();
    $kelompokUmur = $umurStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_kepala_keluarga' => (int)$total,
            'status_aktif' => (int)$aktif,
            'status_tidak_aktif' => (int)$tidakAktif,
            'rata_rata_anggota' => round($rataRata, 2),
            'kelompok_umur' => $kelompokUmur
        ]
    ]);
}

// Function to get chart data
function getKepalaKeluargaChart() {
    global $pdo;
    
    // Data untuk pie chart status
    $statusQuery = "SELECT 
                      status_kepala_keluarga as label,
                      COUNT(*) as value
                    FROM kepala_keluarga 
                    GROUP BY status_kepala_keluarga";
    $statusStmt = $pdo->prepare($statusQuery);
    $statusStmt->execute();
    $statusData = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Data untuk bar chart pendidikan
    $pendidikanQuery = "SELECT 
                          pendidikan_terakhir as label,
                          COUNT(*) as value
                        FROM kepala_keluarga 
                        GROUP BY pendidikan_terakhir
                        ORDER BY value DESC
                        LIMIT 10";
    $pendidikanStmt = $pdo->prepare($pendidikanQuery);
    $pendidikanStmt->execute();
    $pendidikanData = $pendidikanStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Data untuk line chart per bulan (registrasi baru)
    $bulanQuery = "SELECT 
                     DATE_FORMAT(tanggal_daftar, '%Y-%m') as bulan,
                     COUNT(*) as jumlah
                   FROM kepala_keluarga 
                   WHERE tanggal_daftar >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                   GROUP BY DATE_FORMAT(tanggal_daftar, '%Y-%m')
                   ORDER BY bulan ASC";
    $bulanStmt = $pdo->prepare($bulanQuery);
    $bulanStmt->execute();
    $trendData = $bulanStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'charts' => [
            'status_pie' => $statusData,
            'pendidikan_bar' => $pendidikanData,
            'trend_line' => $trendData
        ]
    ]);
}

// Function to get data by gender
function getKepalaKeluargaByGender() {
    global $pdo;
    
    $genderQuery = "SELECT 
                      jenis_kelamin,
                      COUNT(*) as jumlah,
                      AVG(umur) as rata_rata_umur,
                      AVG(jumlah_anggota_keluarga) as rata_rata_anggota
                    FROM kepala_keluarga 
                    GROUP BY jenis_kelamin";
    
    $stmt = $pdo->prepare($genderQuery);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

// Function to export data
function exportKepalaKeluargaData() {
    global $pdo;
    
    $format = $_GET['format'] ?? 'json';
    
    $query = "SELECT 
                kk.nama_kepala_keluarga,
                kk.nik,
                kk.jenis_kelamin,
                kk.umur,
                kk.alamat,
                kk.rt_rw,
                kk.kelurahan,
                kk.kecamatan,
                kk.jumlah_anggota_keluarga,
                kk.status_kepala_keluarga,
                kk.pekerjaan,
                kk.pendidikan_terakhir,
                DATE_FORMAT(kk.tanggal_daftar, '%d/%m/%Y') as tanggal_daftar
              FROM kepala_keluarga kk 
              ORDER BY kk.nama_kepala_keluarga ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="kepala_keluarga_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, [
            'Nama Kepala Keluarga', 'NIK', 'Jenis Kelamin', 'Umur', 'Alamat', 
            'RT/RW', 'Kelurahan', 'Kecamatan', 'Jumlah Anggota', 'Status', 
            'Pekerjaan', 'Pendidikan', 'Tanggal Daftar'
        ]);
        
        // Data CSV
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    } else {
        // Default JSON format
        echo json_encode([
            'success' => true,
            'data' => $data,
            'total' => count($data),
            'exported_at' => date('Y-m-d H:i:s')
        ]);
    }
}
?>
