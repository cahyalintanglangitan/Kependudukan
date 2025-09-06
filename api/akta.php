<?php
// api/akta.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Get filter parameters
    $akta_type = isset($_GET['akta_type']) ? $_GET['akta_type'] : 'all';
    $province = isset($_GET['province']) ? $_GET['province'] : 'all';
    $region_type = isset($_GET['region_type']) ? $_GET['region_type'] : 'all';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'total_desc';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    $response_data = [];
    $all_stats = [];

    // Function to build region filter
    function buildRegionFilter($region_type) {
        switch ($region_type) {
            case 'province':
                return " AND `KODE WILAYAH` REGEXP '^[0-9]{1,2}\\.[0-9]{2}$' AND `KODE WILAYAH` NOT LIKE '%.%.%'";
            case 'kabupaten':
                return " AND `KODE WILAYAH` REGEXP '^[0-9]{1,2}\\.[0-9]{2}\\.[0-9]{2}$' AND `KODE WILAYAH` NOT LIKE '%.7%'";
            case 'kota':
                return " AND `KODE WILAYAH` REGEXP '^[0-9]{1,2}\\.[0-9]{2}\\.7[0-9]$'";
            default:
                return "";
        }
    }

    // Function to build province filter
    function buildProvinceFilter($province) {
        if ($province !== 'all') {
            return " AND `KODE WILAYAH` LIKE '" . $province . "%'";
        }
        return "";
    }

    // Function to build sort clause
    function buildSortClause($sort_by, $table_type) {
        switch ($sort_by) {
            case 'name_asc':
                return " ORDER BY `NAMA WILAYAH` ASC";
            case 'name_desc':
                return " ORDER BY `NAMA WILAYAH` DESC";
            case 'wajib_desc':
                if ($table_type === 'akta_mati') {
                    return " ORDER BY JUMLAH DESC";
                } else {
                    return " ORDER BY `WAJIB AKTA CERAI` DESC";
                }
            case 'memiliki_desc':
                if ($table_type === 'akta_mati') {
                    return " ORDER BY JUMLAH DESC";
                } else {
                    return " ORDER BY `MEMILIKI AKTA CERAI` DESC";
                }
            default:
                if ($table_type === 'akta_mati') {
                    return " ORDER BY JUMLAH DESC";
                } else {
                    return " ORDER BY `MEMILIKI AKTA CERAI` DESC";
                }
        }
    }

    // Process each akta type
    $akta_types = ($akta_type === 'all') ? ['akta_cerai', 'akta_lahir', 'akta_mati'] : [$akta_type];

    foreach ($akta_types as $type) {
        // Build base query based on table structure
        if ($type === 'akta_mati') {
            $sql = "SELECT 
                        `KODE WILAYAH` as kode,
                        `NAMA WILAYAH` as wilayah,
                        `LAKI-LAKI` as laki_laki,
                        `PEREMPUAN` as perempuan,
                        JUMLAH as total
                    FROM {$type} 
                    WHERE `KODE WILAYAH` IS NOT NULL AND `KODE WILAYAH` != '' AND `NAMA WILAYAH` != 'INDONESIA'";
        } else {
            $sql = "SELECT 
                        `KODE WILAYAH` as kode,
                        `NAMA WILAYAH` as wilayah,
                        `WAJIB AKTA CERAI` as wajib,
                        `BELUM MEMILIKI AKTA CERAI` as belum_memiliki,
                        `MEMILIKI AKTA CERAI` as memiliki,
                        `Persentase Kepemilikan` as persentase
                    FROM {$type} 
                    WHERE `KODE WILAYAH` IS NOT NULL AND `KODE WILAYAH` != '' AND `NAMA WILAYAH` != 'INDONESIA'";
        }

        // Add filters
        $sql .= buildRegionFilter($region_type);
        $sql .= buildProvinceFilter($province);
        $sql .= buildSortClause($sort_by, $type);
        $sql .= " LIMIT " . $limit;

        // Execute query
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process data
        $processed_data = [];
        foreach ($results as $row) {
            if ($type === 'akta_mati') {
                $processed_data[] = [
                    'kode' => $row['kode'],
                    'wilayah' => trim($row['wilayah']),
                    'laki_laki' => (int)str_replace([',', '.'], '', $row['laki_laki']) ?: 0,
                    'perempuan' => (int)str_replace([',', '.'], '', $row['perempuan']) ?: 0,
                    'total' => (int)$row['total'] ?: 0
                ];
            } else {
                $processed_data[] = [
                    'kode' => $row['kode'],
                    'wilayah' => trim($row['wilayah']),
                    'wajib' => (int)str_replace([',', '.'], '', $row['wajib']) ?: 0,
                    'belum_memiliki' => (int)str_replace([',', '.'], '', $row['belum_memiliki']) ?: 0,
                    'memiliki' => (int)str_replace([',', '.'], '', $row['memiliki']) ?: 0,
                    'persentase' => floatval(str_replace('%', '', $row['persentase'])) ?: 0
                ];
            }
        }

        $response_data[$type] = $processed_data;

        // Calculate statistics
        if ($type === 'akta_mati') {
            $stats_sql = "SELECT 
                            SUM(CAST(REPLACE(REPLACE(`LAKI-LAKI`, ',', ''), '.', '') AS UNSIGNED)) as total_laki_laki,
                            SUM(CAST(REPLACE(REPLACE(PEREMPUAN, ',', ''), '.', '') AS UNSIGNED)) as total_perempuan,
                            SUM(JUMLAH) as grand_total,
                            COUNT(*) as total_regions
                          FROM {$type} 
                          WHERE `KODE WILAYAH` IS NOT NULL AND `KODE WILAYAH` != '' AND `NAMA WILAYAH` != 'INDONESIA'";
        } else {
            $stats_sql = "SELECT 
                            SUM(CAST(REPLACE(REPLACE(`WAJIB AKTA CERAI`, ',', ''), '.', '') AS UNSIGNED)) as total_wajib,
                            SUM(CAST(REPLACE(REPLACE(`BELUM MEMILIKI AKTA CERAI`, ',', ''), '.', '') AS UNSIGNED)) as total_belum_memiliki,
                            SUM(CAST(REPLACE(REPLACE(`MEMILIKI AKTA CERAI`, ',', ''), '.', '') AS UNSIGNED)) as total_memiliki,
                            COUNT(*) as total_regions
                          FROM {$type} 
                          WHERE `KODE WILAYAH` IS NOT NULL AND `KODE WILAYAH` != '' AND `NAMA WILAYAH` != 'INDONESIA'";
        }

        $stats_sql .= buildRegionFilter($region_type);
        $stats_sql .= buildProvinceFilter($province);

        $stats_stmt = $db->prepare($stats_sql);
        $stats_stmt->execute();
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

        $all_stats[$type] = $stats;
    }

    // Get province breakdown for dropdown
    $province_sql = "SELECT DISTINCT
                        SUBSTRING_INDEX(`KODE WILAYAH`, '.', 1) as kode,
                        `NAMA WILAYAH` as wilayah
                     FROM akta_cerai 
                     WHERE `KODE WILAYAH` REGEXP '^[0-9]{1,2}\\.[0-9]{2}$' 
                     AND `KODE WILAYAH` NOT LIKE '%.%.%'
                     ORDER BY `NAMA WILAYAH` ASC";
    
    $province_stmt = $db->prepare($province_sql);
    $province_stmt->execute();
    $provinces = $province_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build comprehensive response
    $response = [
        'success' => true,
        'data' => $response_data,
        'stats' => $all_stats,
        'provinces' => $provinces,
        'meta' => [
            'filters' => [
                'akta_type' => $akta_type,
                'province' => $province,
                'region_type' => $region_type,
                'sort_by' => $sort_by,
                'limit' => $limit
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];

    // Send response
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => 'Internal server error occurred',
            'code' => 'API_ERROR',
            'details' => $e->getMessage()
        ],
        'meta' => [
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>
