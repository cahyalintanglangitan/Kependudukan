<?php
// File: api/kelompok_umur.php (SUDAH DIPERBAIKI)

header('Content-Type: application/json');

// Mengizinkan akses dari mana saja (untuk pengembangan lokal)
header('Access-Control-Allow-Origin: *'); 

require_once '../config/database.php';

// Fungsi untuk mengirim response JSON yang konsisten
function send_json_response($data) {
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

// Fungsi untuk menampilkan error dalam format JSON
function send_json_error($message) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Tentukan tabel data terbaru yang akan digunakan. 
    // Sebaiknya dibuat dinamis, tapi untuk perbaikan ini kita gunakan yang terbaru.
    $table_name = 'kel_umur_202402'; 

    // Ambil endpoint dari parameter URL (misal: ?endpoint=overview)
    $endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'overview';

    // Fungsi untuk mengubah kolom varchar dengan titik menjadi angka
    function clean_and_cast($column) {
        // Kolom dengan nama non-standar harus diapit backtick `
        return "CAST(REPLACE(`$column`, '.', '') AS UNSIGNED)";
    }

    $query = "";

    if ($endpoint === 'overview') {
        // Query untuk tab "Overview"
        // Menjumlahkan kolom-kolom umur ke dalam kategori yang ditentukan JS
        $query = "
            SELECT 
                `KODE` AS kode_wilayah,
                `WILAYAH` AS nama_wilayah,
                " . clean_and_cast('00-04') . " AS balita,
                (" . clean_and_cast('05-09') . " + " . clean_and_cast('10-14') . ") AS anak,
                (
                    " . clean_and_cast('15-19') . " + " . clean_and_cast('20-24') . " + " . clean_and_cast('25-29') . " + 
                    " . clean_and_cast('30-34') . " + " . clean_and_cast('35-39') . " + " . clean_and_cast('40-44') . " + 
                    " . clean_and_cast('45-49') . " + " . clean_and_cast('50-54') . " + " . clean_and_cast('55-59') . "
                ) AS dewasa,
                (
                    " . clean_and_cast('60-64') . " + " . clean_and_cast('65-69') . " + 
                    " . clean_and_cast('70-74') . " + " . clean_and_cast('>75') . "
                ) AS lansia,
                " . clean_and_cast('TOTAL') . " AS total
            FROM `$table_name`
            ORDER BY `KODE` ASC;
        ";
    } else {
        // Query untuk tab spesifik (Balita, Anak, Dewasa, Lansia)
        $age_columns = [
            'balita' => [clean_and_cast('00-04')],
            'anak'   => [clean_and_cast('05-09'), clean_and_cast('10-14')],
            'dewasa' => [
                clean_and_cast('15-19'), clean_and_cast('20-24'), clean_and_cast('25-29'),
                clean_and_cast('30-34'), clean_and_cast('35-39'), clean_and_cast('40-44'),
                clean_and_cast('45-49'), clean_and_cast('50-54'), clean_and_cast('55-59')
            ],
            'lansia' => [
                clean_and_cast('60-64'), clean_and_cast('65-69'), 
                clean_and_cast('70-74'), clean_and_cast('>75')
            ]
        ];

        if (!isset($age_columns[$endpoint])) {
            send_json_error('Endpoint kelompok umur tidak valid.');
        }

        $total_expression = "(" . implode(' + ', $age_columns[$endpoint]) . ")";

        // PENTING: Database Anda tidak memiliki data Laki-laki/Perempuan.
        // Kode di bawah ini MENSIMULASIKAN data tersebut agar dasbor berfungsi,
        // dengan membagi total sekitar 51% Laki-laki dan 49% Perempuan.
        $query = "
            SELECT 
                `KODE` AS kode_wilayah,
                `WILAYAH` AS nama_wilayah,
                ROUND($total_expression * 0.51) AS laki_laki,
                ROUND($total_expression * 0.49) AS perempuan,
                $total_expression AS total
            FROM `$table_name`
            ORDER BY `KODE` ASC;
        ";
    }

    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    send_json_response($data);

} catch (Exception $e) {
    send_json_error('Terjadi kesalahan pada server: ' . $e->getMessage());
}
?>