<?php
// api/kepala_keluarga.php

// Headers untuk API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Aktifkan di development untuk melihat error
ini_set('log_errors', 1);

try {
    // Include config dan database class
    // Pastikan path ini benar sesuai struktur proyek Anda
    require_once __DIR__ . '/../config/database.php';
    
    // Buat koneksi database
    $database = new Database();
    $db = $database->getConnection();
    
    // Default action adalah 'all' yang akan digunakan oleh frontend
    $action = isset($_GET['action']) ? trim($_GET['action']) : 'all';
    
    // Route ke fungsi yang sesuai
    switch($action) {
        case 'stats':
            getKepalaKeluargaStats($db);
            break;
        case 'distribution': // Untuk chart per provinsi
            getDistributionByProvince($db);
            break;
        case 'all': // Mengembalikan semua data untuk diolah di frontend
        default:
            getAllKepalaKeluarga($db);
            break;
    }
    
} catch(Exception $e) {
    // Tangani error dan kirim response JSON yang informatif
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => basename(__FILE__),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}

/**
 * Mengambil statistik total Kepala Keluarga dari seluruh kabupaten/kota.
 * Ini mencegah penghitungan ganda dari baris total provinsi.
 */
function getKepalaKeluargaStats($db) {
    // Query untuk menjumlahkan data dari semua KABUPATEN dan KOTA
    // `KODE WILAYAH` LIKE '%.__'` memastikan kita hanya mengambil data level kab/kota (misal 11.01, 11.71)
    $query = "
        SELECT 
            SUM(CAST(REPLACE(`LAKI-LAKI`, '.', '') AS UNSIGNED)) as total_laki_laki,
            SUM(CAST(REPLACE(`PEREMPUAN`, '.', '') AS UNSIGNED)) as total_perempuan,
            SUM(CAST(REPLACE(`JUMLAH`, '.', '') AS UNSIGNED)) as total_jumlah
        FROM kepala_keluarga
        WHERE `NAMA WILAYAH` LIKE 'KAB.%' OR `NAMA WILAYAH` LIKE 'KOTA%';
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'lakiLaki' => (int)$result['total_laki_laki'],
            'perempuan' => (int)$result['total_perempuan'],
            'total' => (int)$result['total_jumlah']
        ]
    ], JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
}

/**
 * Mengambil data agregat per provinsi untuk chart distribusi.
 */
function getDistributionByProvince($db) {
    // Query untuk mengambil data provinsi saja (kode wilayah diakhiri dengan .00)
    $query = "
        SELECT 
            `NAMA WILAYAH` as name,
            CAST(REPLACE(`LAKI-LAKI`, '.', '') AS UNSIGNED) as laki_laki,
            CAST(REPLACE(`PEREMPUAN`, '.', '') AS UNSIGNED) as perempuan,
            CAST(REPLACE(`JUMLAH`, '.', '') AS UNSIGNED) as total
        FROM kepala_keluarga
        WHERE `KODE WILAYAH` LIKE '%.00'
        ORDER BY total DESC;
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $result
    ], JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
}


/**
 * Mengambil semua data dari tabel kepala_keluarga.
 * Frontend JS akan melakukan filter dan sorting.
 */
function getAllKepalaKeluarga($db) {
    $query = "
        SELECT 
            `NAMA WILAYAH` as name,
            `KODE WILAYAH` as kode,
            (CASE 
                WHEN `NAMA WILAYAH` LIKE 'KAB.%' THEN 'kabupaten'
                WHEN `NAMA WILAYAH` LIKE 'KOTA%' THEN 'kota'
                ELSE 'provinsi'
            END) as type,
            CAST(REPLACE(`LAKI-LAKI`, '.', '') AS UNSIGNED) as laki_laki,
            CAST(REPLACE(`PEREMPUAN`, '.', '') AS UNSIGNED) as perempuan,
            CAST(REPLACE(`JUMLAH`, '.', '') AS UNSIGNED) as total
        FROM kepala_keluarga;
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $result
    ], JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
}
?>