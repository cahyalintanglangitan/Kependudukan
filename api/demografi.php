<?php
// api/demografi.php
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
                LAKI_LAKI as laki_laki,
                PEREMPUAN as perempuan,
                JUMLAH as total
            FROM demografi 
            WHERE KODE IS NOT NULL AND KODE != '' AND WILAYAH != 'INDONESIA'";

    $params = [];

    // Enhanced region type filtering - 4 levels untuk demografi
    if ($region_type !== 'all') {
        switch ($region_type) {
            case 'province':
                // Province codes are exactly 2 digits without dots (11, 12, 13, etc.)
                $sql .= " AND KODE REGEXP '^[0-9]{2}$'";
                break;
            case 'kabupaten':
                // Kabupaten codes have pattern XX.XX
                $sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$'";
                break;
            case 'kecamatan':
                // Kecamatan codes have pattern XX.XX.XX
                $sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}$'";
                break;
            case 'desa':
                // Desa codes have pattern XX.XX.XX.XXXX
                $sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}\\.[0-9]{4}$'";
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
            $sql .= " ORDER BY JUMLAH ASC";
            break;
        case 'total_desc':
            $sql .= " ORDER BY JUMLAH DESC";
            break;
        case 'laki_laki_desc':
            $sql .= " ORDER BY LAKI_LAKI DESC";
            break;
        case 'perempuan_desc':
            $sql .= " ORDER BY PEREMPUAN DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY WILAYAH ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY WILAYAH DESC";
            break;
        default:
            $sql .= " ORDER BY JUMLAH DESC";
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
            'laki_laki' => (int)$row['laki_laki'] ?: 0,
            'perempuan' => (int)$row['perempuan'] ?: 0,
            'total' => (int)$row['total'] ?: 0
        ];
    }

    // Calculate aggregate statistics
    $stats_sql = "SELECT 
                    SUM(LAKI_LAKI) as total_laki_laki,
                    SUM(PEREMPUAN) as total_perempuan,
                    SUM(JUMLAH) as grand_total,
                    COUNT(*) as total_regions
                  FROM demografi 
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
                $stats_sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$'";
                break;
            case 'kecamatan':
                $stats_sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}$'";
                break;
            case 'desa':
                $stats_sql .= " AND KODE REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}\\.[0-9]{4}$'";
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
        'total_laki_laki' => (int)$stats['total_laki_laki'] ?: 0,
        'total_perempuan' => (int)$stats['total_perempuan'] ?: 0,
        'grand_total' => (int)$stats['grand_total'] ?: 0,
        'total_regions' => (int)$stats['total_regions'] ?: 0
    ];

    // Get province breakdown untuk dropdown/filter
    $province_sql = "SELECT 
                        KODE, WILAYAH, JUMLAH
                     FROM demografi 
                     WHERE KODE REGEXP '^[0-9]{2}$'
                     ORDER BY JUMLAH DESC";
    
    $province_stmt = $db->prepare($province_sql);
    $province_stmt->execute();
    $provinces = $province_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process province data
    $processedProvinces = [];
    foreach ($provinces as $prov) {
        $processedProvinces[] = [
            'kode' => $prov['KODE'],
            'wilayah' => trim($prov['WILAYAH']),
            'total' => (int)$prov['JUMLAH'] ?: 0
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