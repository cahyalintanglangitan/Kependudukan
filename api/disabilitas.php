<?php
// api/disabilitas.php
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
    $province = isset($_GET['province']) ? $_GET['province'] : 'all';
    $region_type = isset($_GET['region_type']) ? $_GET['region_type'] : 'all';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'total_desc';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    // Base query with proper field mapping
    $sql = "SELECT 
                KODE as kode,
                WILAYAH as wilayah,
                `DISABILITAS FISIK` as fisik,
                `DISABILITAS NETRA/ BUTA` as netra,
                `DISABILITAS RUNGU/ WICARA` as rungu,
                `DISABILITAS MENTAL/ JIWA` as mental,
                `DISABILITAS FISIK DAN MENTAL` as fisik_mental,
                `DISABILITAS LAINNYA` as lainnya,
                TOTAL as total
            FROM disabilitas 
            WHERE KODE IS NOT NULL AND KODE != '' AND WILAYAH != 'INDONESIA'";

    $params = [];

    // Enhanced region type filtering - PERBAIKAN UTAMA
    if ($region_type !== 'all') {
        switch ($region_type) {
            case 'province':
                // Province codes are exactly 2 digits without dots (11, 12, 13, etc.)
                $sql .= " AND KODE REGEXP '^[0-9]{2}$'";
                break;
            case 'kabupaten':
                // Kabupaten codes have pattern XX.XX but NOT XX.7X (yang .7X adalah kota)
                $sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$' AND KODE NOT LIKE '%.7%'";
                break;
            case 'kota':
                // Kota codes have pattern XX.7X
                $sql .= " AND KODE REGEXP '^[0-9]{2}\\.7[0-9]$'";
                break;
        }
    }

    // Province filtering
    if ($province !== 'all') {
        $sql .= " AND KODE LIKE ?";
        $params[] = $province . '%';
    }

    // Enhanced sorting options
    switch ($sort_by) {
        case 'total_asc':
            $sql .= " ORDER BY TOTAL ASC";
            break;
        case 'total_desc':
            $sql .= " ORDER BY TOTAL DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY WILAYAH ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY WILAYAH DESC";
            break;
        case 'fisik_desc':
            $sql .= " ORDER BY `DISABILITAS FISIK` DESC";
            break;
        case 'mental_desc':
            $sql .= " ORDER BY `DISABILITAS MENTAL/ JIWA` DESC";
            break;
        default:
            $sql .= " ORDER BY TOTAL DESC";
    }

    $sql .= " LIMIT " . $limit;

    // Execute query
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process and clean data
    $processedData = [];
    foreach ($results as $row) {
        $processedData[] = [
            'kode' => $row['kode'],
            'wilayah' => trim($row['wilayah']),
            'fisik' => (int)$row['fisik'] ?: 0,
            'netra' => (int)$row['netra'] ?: 0, 
            'rungu' => (int)$row['rungu'] ?: 0,
            'mental' => (int)$row['mental'] ?: 0,
            'fisik_mental' => (int)$row['fisik_mental'] ?: 0,
            'lainnya' => (int)$row['lainnya'] ?: 0,
            'total' => (int)$row['total'] ?: 0
        ];
    }

    // Calculate aggregate statistics
    $stats_sql = "SELECT 
                    SUM(`DISABILITAS FISIK`) as total_fisik,
                    SUM(`DISABILITAS NETRA/ BUTA`) as total_netra,
                    SUM(`DISABILITAS RUNGU/ WICARA`) as total_rungu,
                    SUM(`DISABILITAS MENTAL/ JIWA`) as total_mental,
                    SUM(`DISABILITAS FISIK DAN MENTAL`) as total_fisik_mental,
                    SUM(`DISABILITAS LAINNYA`) as total_lainnya,
                    SUM(TOTAL) as grand_total,
                    COUNT(*) as total_regions
                  FROM disabilitas 
                  WHERE KODE IS NOT NULL AND KODE != '' AND WILAYAH != 'INDONESIA'";
    
    $stats_params = [];

    // CRITICAL FIX: Prevent double counting when showing all regions
if ($region_type === 'all' && $province === 'all') {
    // For "all regions" + "all provinces", use only provinces for stats
    $stats_sql .= " AND KODE REGEXP '^[0-9]{2}$'";
}
    
    // Apply same filters to stats
    if ($region_type !== 'all') {
        switch ($region_type) {
            case 'province':
                $stats_sql .= " AND KODE REGEXP '^[0-9]{2}$'";
                break;
            case 'kabupaten':
                $stats_sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$' AND KODE NOT LIKE '%.7%'";
                break;
            case 'kota':
                $stats_sql .= " AND KODE REGEXP '^[0-9]{2}\\.7[0-9]$'";
                break;
        }
    }
    
    if ($province !== 'all') {
        $stats_sql .= " AND KODE LIKE ?";
        $stats_params[] = $province . '%';
    }

    $stats_stmt = $db->prepare($stats_sql);
    $stats_stmt->execute($stats_params);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    // Clean stats data
    $processedStats = [
        'total_fisik' => (int)$stats['total_fisik'] ?: 0,
        'total_netra' => (int)$stats['total_netra'] ?: 0,
        'total_rungu' => (int)$stats['total_rungu'] ?: 0,
        'total_mental' => (int)$stats['total_mental'] ?: 0,
        'total_fisik_mental' => (int)$stats['total_fisik_mental'] ?: 0,
        'total_lainnya' => (int)$stats['total_lainnya'] ?: 0,
        'grand_total' => (int)$stats['grand_total'] ?: 0,
        'total_regions' => (int)$stats['total_regions'] ?: 0
    ];

    // Get province breakdown untuk dropdown/filter
    $province_sql = "SELECT 
                        KODE, WILAYAH, TOTAL
                     FROM disabilitas 
                     WHERE KODE REGEXP '^[0-9]{2}$'
                     ORDER BY TOTAL DESC";
    
    $province_stmt = $db->prepare($province_sql);
    $province_stmt->execute();
    $provinces = $province_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process province data
    $processedProvinces = [];
    foreach ($provinces as $prov) {
        $processedProvinces[] = [
            'kode' => $prov['KODE'],
            'wilayah' => trim($prov['WILAYAH']),
            'total' => (int)$prov['TOTAL'] ?: 0
        ];
    }

    // Build comprehensive response
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