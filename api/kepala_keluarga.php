<?php
// api/kepala_keluarga.php
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
    // Init database
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Get filter params
    $province = isset($_GET['province']) ? $_GET['province'] : 'all';
    $region_type = isset($_GET['region_type']) ? $_GET['region_type'] : 'all';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'total_desc';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    // Base query
    $sql = "SELECT 
                KODE as kode,
                WILAYAH as wilayah,
                `LAKI-LAKI` as laki,
                `PEREMPUAN` as perempuan,
                `JUMLAH` as total
            FROM kepala_keluarga
            WHERE KODE IS NOT NULL AND KODE != '' AND WILAYAH != 'INDONESIA'";

    $params = [];

    // Region type filter
    if ($region_type !== 'all') {
        switch ($region_type) {
            case 'province':
                $sql .= " AND KODE REGEXP '^[0-9]{2}$'";
                break;
            case 'kabupaten':
                $sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$' AND KODE NOT LIKE '%.7%'";
                break;
            case 'kota':
                $sql .= " AND KODE REGEXP '^[0-9]{2}\\.7[0-9]$'";
                break;
        }
    }

    // Province filter
    if ($province !== 'all') {
        $sql .= " AND KODE LIKE ?";
        $params[] = $province . '%';
    }

    // Sorting
    switch ($sort_by) {
        case 'total_asc': $sql .= " ORDER BY JUMLAH ASC"; break;
        case 'total_desc': $sql .= " ORDER BY JUMLAH DESC"; break;
        case 'name_asc': $sql .= " ORDER BY WILAYAH ASC"; break;
        case 'name_desc': $sql .= " ORDER BY WILAYAH DESC"; break;
        case 'laki_desc': $sql .= " ORDER BY `LAKI-LAKI` DESC"; break;
        case 'perempuan_desc': $sql .= " ORDER BY `PEREMPUAN` DESC"; break;
        default: $sql .= " ORDER BY JUMLAH DESC";
    }

    $sql .= " LIMIT " . $limit;

    // Execute
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process data
    $processedData = [];
    foreach ($results as $row) {
        $processedData[] = [
            'kode' => $row['kode'],
            'wilayah' => trim($row['wilayah']),
            'laki' => (int)$row['laki'] ?: 0,
            'perempuan' => (int)$row['perempuan'] ?: 0,
            'total' => (int)$row['total'] ?: 0
        ];
    }

    // Stats
    $stats_sql = "SELECT 
                    SUM(`LAKI-LAKI`) as total_laki,
                    SUM(`PEREMPUAN`) as total_perempuan,
                    SUM(`JUMLAH`) as grand_total,
                    COUNT(*) as total_regions
                  FROM kepala_keluarga
                  WHERE KODE IS NOT NULL AND KODE != '' AND WILAYAH != 'INDONESIA'";
    $stats_params = [];

    // Prevent double counting (use province only when all selected)
    if ($region_type === 'all' && $province === 'all') {
        $stats_sql .= " AND KODE REGEXP '^[0-9]{2}$'";
    }

    // Apply filters
    if ($region_type !== 'all') {
        switch ($region_type) {
            case 'province': $stats_sql .= " AND KODE REGEXP '^[0-9]{2}$'"; break;
            case 'kabupaten': $stats_sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$' AND KODE NOT LIKE '%.7%'"; break;
            case 'kota': $stats_sql .= " AND KODE REGEXP '^[0-9]{2}\\.7[0-9]$'"; break;
        }
    }
    if ($province !== 'all') {
        $stats_sql .= " AND KODE LIKE ?";
        $stats_params[] = $province . '%';
    }

    $stats_stmt = $db->prepare($stats_sql);
    $stats_stmt->execute($stats_params);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    $processedStats = [
        'total_laki' => (int)$stats['total_laki'] ?: 0,
        'total_perempuan' => (int)$stats['total_perempuan'] ?: 0,
        'grand_total' => (int)$stats['grand_total'] ?: 0,
        'total_regions' => (int)$stats['total_regions'] ?: 0
    ];

    // Province list for dropdown
    $province_sql = "SELECT KODE, WILAYAH, JUMLAH 
                     FROM kepala_keluarga
                     WHERE KODE REGEXP '^[0-9]{2}$'
                     ORDER BY JUMLAH DESC";
    $province_stmt = $db->prepare($province_sql);
    $province_stmt->execute();
    $provinces = $province_stmt->fetchAll(PDO::FETCH_ASSOC);

    $processedProvinces = [];
    foreach ($provinces as $prov) {
        $processedProvinces[] = [
            'kode' => $prov['KODE'],
            'wilayah' => trim($prov['WILAYAH']),
            'total' => (int)$prov['JUMLAH'] ?: 0
        ];
    }

    // Response
    $response = [
        'success' => true,
        'data' => $processedData,
        'stats' => $processedStats,
        'provinces' => $processedProvinces,
        'meta' => [
            'count' => count($processedData),
            'filters' => [
                'province' => $province,
                'region_type' => $region_type,
                'sort_by' => $sort_by,
                'limit' => $limit
            ],
            'timestamp' => date('Y-m-d H:i:s'),
            'total_available_regions' => $processedStats['total_regions']
        ]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => 'Internal server error occurred',
            'code' => 'API_ERROR',
            'details' => $e->getMessage()
        ],
        'meta' => ['timestamp' => date('Y-m-d H:i:s')]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
