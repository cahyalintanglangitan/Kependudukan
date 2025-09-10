<?php
// api/kelompok_umur.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

class KelompokUmurAPI {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        try {
            switch ($method) {
                case 'GET':
                    $this->getData();
                    break;
                default:
                    $this->sendResponse(['error' => 'Method not allowed'], 405);
                    break;
            }
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
    
    private function getData() {
        try {
            // Get query parameters
            $wilayah = isset($_GET['wilayah']) ? $_GET['wilayah'] : null;
            $kelompok = isset($_GET['kelompok']) ? $_GET['kelompok'] : 'all';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'kode_asc';
            
            // Parse sort parameter
            $sortParts = explode('_', $sort);
            $sortField = $sortParts[0];
            $sortDirection = isset($sortParts[1]) ? strtoupper($sortParts[1]) : 'ASC';
            
            // Validate sort direction
            if (!in_array($sortDirection, ['ASC', 'DESC'])) {
                $sortDirection = 'ASC';
            }
            
            // Build base query for kelompok_umur table
            $baseQuery = "
                SELECT 
                    ku.kode_wilayah,
                    ku.wilayah,
                    ku.umur_00_04,
                    ku.umur_05_09,
                    ku.umur_10_14,
                    ku.umur_15_19,
                    ku.umur_20_24,
                    ku.umur_25_29,
                    ku.umur_30_34,
                    ku.umur_35_39,
                    ku.umur_40_44,
                    ku.umur_45_49,
                    ku.umur_50_54,
                    ku.umur_55_59,
                    ku.umur_60_64,
                    ku.umur_65_69,
                    ku.umur_70_74,
                    ku.umur_75_plus,
                    ku.total,
                    -- Calculate age group totals
                    ku.umur_00_04 as balita,
                    (ku.umur_05_09 + ku.umur_10_14) as anak,
                    (ku.umur_15_19 + ku.umur_20_24 + ku.umur_25_29 + ku.umur_30_34 + ku.umur_35_39 + ku.umur_40_44 + ku.umur_45_49 + ku.umur_50_54 + ku.umur_55_59) as dewasa,
                    (ku.umur_60_64 + ku.umur_65_69 + ku.umur_70_74 + ku.umur_75_plus) as lansia
                FROM kelompok_umur ku
                WHERE 1=1
            ";
            
            $params = [];
            $whereConditions = [];
            
            // Add search condition
            if (!empty($search)) {
                $whereConditions[] = "(ku.wilayah LIKE ? OR ku.kode_wilayah LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // Add wilayah filter if specified
            if ($wilayah) {
                $whereConditions[] = "ku.kode_wilayah = ?";
                $params[] = $wilayah;
            }
            
            // Add where conditions to query
            if (!empty($whereConditions)) {
                $baseQuery .= " AND " . implode(" AND ", $whereConditions);
            }
            
            // Add sorting
            $orderByClause = $this->buildOrderByClause($sortField, $sortDirection);
            $baseQuery .= " " . $orderByClause;
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM (" . $baseQuery . ") as counted";
            $countStmt = $this->conn->prepare($countQuery);
            if (!empty($params)) {
                $countStmt->execute($params);
            } else {
                $countStmt->execute();
            }
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Add pagination
            $offset = ($page - 1) * $limit;
            $baseQuery .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            // Execute main query
            $stmt = $this->conn->prepare($baseQuery);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get overall statistics
            $statsQuery = "
                SELECT 
                    SUM(umur_00_04) as total_balita,
                    SUM(umur_05_09 + umur_10_14) as total_anak,
                    SUM(umur_15_19 + umur_20_24 + umur_25_29 + umur_30_34 + umur_35_39 + umur_40_44 + umur_45_49 + umur_50_54 + umur_55_59) as total_dewasa,
                    SUM(umur_60_64 + umur_65_69 + umur_70_74 + umur_75_plus) as total_lansia,
                    SUM(total) as total_keseluruhan,
                    COUNT(*) as total_wilayah
                FROM kelompok_umur
                WHERE kode_wilayah != '11.00'
            ";
            
            $statsStmt = $this->conn->prepare($statsQuery);
            $statsStmt->execute();
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            // Get detailed age group breakdown
            $detailQuery = "
                SELECT 
                    SUM(umur_00_04) as umur_0_4,
                    SUM(umur_05_09) as umur_5_9,
                    SUM(umur_10_14) as umur_10_14,
                    SUM(umur_15_19) as umur_15_19,
                    SUM(umur_20_24) as umur_20_24,
                    SUM(umur_25_29) as umur_25_29,
                    SUM(umur_30_34) as umur_30_34,
                    SUM(umur_35_39) as umur_35_39,
                    SUM(umur_40_44) as umur_40_44,
                    SUM(umur_45_49) as umur_45_49,
                    SUM(umur_50_54) as umur_50_54,
                    SUM(umur_55_59) as umur_55_59,
                    SUM(umur_60_64) as umur_60_64,
                    SUM(umur_65_69) as umur_65_69,
                    SUM(umur_70_74) as umur_70_74,
                    SUM(umur_75_plus) as umur_75_plus
                FROM kelompok_umur
                WHERE kode_wilayah != '11.00'
            ";
            
            $detailStmt = $this->conn->prepare($detailQuery);
            $detailStmt->execute();
            $detailStats = $detailStmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate percentages
            $totalPopulation = $stats['total_keseluruhan'];
            
            // Format response data
            $formattedData = array_map(function($row) use ($totalPopulation) {
                return [
                    'kode_wilayah' => $row['kode_wilayah'],
                    'wilayah' => $row['wilayah'],
                    'umur_00_04' => (int)$row['umur_00_04'],
                    'umur_05_09' => (int)$row['umur_05_09'],
                    'umur_10_14' => (int)$row['umur_10_14'],
                    'umur_15_19' => (int)$row['umur_15_19'],
                    'umur_20_24' => (int)$row['umur_20_24'],
                    'umur_25_29' => (int)$row['umur_25_29'],
                    'umur_30_34' => (int)$row['umur_30_34'],
                    'umur_35_39' => (int)$row['umur_35_39'],
                    'umur_40_44' => (int)$row['umur_40_44'],
                    'umur_45_49' => (int)$row['umur_45_49'],
                    'umur_50_54' => (int)$row['umur_50_54'],
                    'umur_55_59' => (int)$row['umur_55_59'],
                    'umur_60_64' => (int)$row['umur_60_64'],
                    'umur_65_69' => (int)$row['umur_65_69'],
                    'umur_70_74' => (int)$row['umur_70_74'],
                    'umur_75_plus' => (int)$row['umur_75_plus'],
                    'balita' => (int)$row['balita'],
                    'anak' => (int)$row['anak'],
                    'dewasa' => (int)$row['dewasa'],
                    'lansia' => (int)$row['lansia'],
                    'total' => (int)$row['total'],
                    'persentase_balita' => $row['total'] > 0 ? round(($row['balita'] / $row['total']) * 100, 2) : 0,
                    'persentase_anak' => $row['total'] > 0 ? round(($row['anak'] / $row['total']) * 100, 2) : 0,
                    'persentase_dewasa' => $row['total'] > 0 ? round(($row['dewasa'] / $row['total']) * 100, 2) : 0,
                    'persentase_lansia' => $row['total'] > 0 ? round(($row['lansia'] / $row['total']) * 100, 2) : 0
                ];
            }, $data);
            
            // Prepare response
            $response = [
                'success' => true,
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_records' => (int)$totalRecords,
                    'total_pages' => ceil($totalRecords / $limit)
                ],
                'statistics' => [
                    'total_balita' => (int)$stats['total_balita'],
                    'total_anak' => (int)$stats['total_anak'],
                    'total_dewasa' => (int)$stats['total_dewasa'],
                    'total_lansia' => (int)$stats['total_lansia'],
                    'total_keseluruhan' => (int)$stats['total_keseluruhan'],
                    'total_wilayah' => (int)$stats['total_wilayah'],
                    'persentase_balita' => $totalPopulation > 0 ? round(($stats['total_balita'] / $totalPopulation) * 100, 2) : 0,
                    'persentase_anak' => $totalPopulation > 0 ? round(($stats['total_anak'] / $totalPopulation) * 100, 2) : 0,
                    'persentase_dewasa' => $totalPopulation > 0 ? round(($stats['total_dewasa'] / $totalPopulation) * 100, 2) : 0,
                    'persentase_lansia' => $totalPopulation > 0 ? round(($stats['total_lansia'] / $totalPopulation) * 100, 2) : 0
                ],
                'detail_breakdown' => [
                    'umur_0_4' => [
                        'jumlah' => (int)$detailStats['umur_0_4'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_0_4'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_5_9' => [
                        'jumlah' => (int)$detailStats['umur_5_9'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_5_9'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_10_14' => [
                        'jumlah' => (int)$detailStats['umur_10_14'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_10_14'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_15_19' => [
                        'jumlah' => (int)$detailStats['umur_15_19'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_15_19'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_20_24' => [
                        'jumlah' => (int)$detailStats['umur_20_24'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_20_24'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_25_29' => [
                        'jumlah' => (int)$detailStats['umur_25_29'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_25_29'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_30_34' => [
                        'jumlah' => (int)$detailStats['umur_30_34'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_30_34'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_35_39' => [
                        'jumlah' => (int)$detailStats['umur_35_39'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_35_39'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_40_44' => [
                        'jumlah' => (int)$detailStats['umur_40_44'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_40_44'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_45_49' => [
                        'jumlah' => (int)$detailStats['umur_45_49'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_45_49'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_50_54' => [
                        'jumlah' => (int)$detailStats['umur_50_54'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_50_54'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_55_59' => [
                        'jumlah' => (int)$detailStats['umur_55_59'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_55_59'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_60_64' => [
                        'jumlah' => (int)$detailStats['umur_60_64'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_60_64'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_65_69' => [
                        'jumlah' => (int)$detailStats['umur_65_69'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_65_69'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_70_74' => [
                        'jumlah' => (int)$detailStats['umur_70_74'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_70_74'] / $totalPopulation) * 100, 2) : 0
                    ],
                    'umur_75_plus' => [
                        'jumlah' => (int)$detailStats['umur_75_plus'],
                        'persentase' => $totalPopulation > 0 ? round(($detailStats['umur_75_plus'] / $totalPopulation) * 100, 2) : 0
                    ]
                ],
                'filters' => [
                    'search' => $search,
                    'sort' => $sort,
                    'wilayah' => $wilayah,
                    'kelompok' => $kelompok
                ]
            ];
            
            $this->sendResponse($response);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    private function buildOrderByClause($sortField, $sortDirection) {
        $allowedSortFields = [
            'kode' => 'ku.kode_wilayah',
            'wilayah' => 'ku.wilayah',
            'total' => 'ku.total',
            'balita' => 'balita',
            'anak' => 'anak',
            'dewasa' => 'dewasa',
            'lansia' => 'lansia',
            'persentase' => 'ku.total'
        ];
        
        if (array_key_exists($sortField, $allowedSortFields)) {
            return "ORDER BY " . $allowedSortFields[$sortField] . " " . $sortDirection;
        }
        
        // Default sort
        return "ORDER BY ku.kode_wilayah ASC";
    }
    
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Initialize database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Database connection failed');
    }
    
    $api = new KelompokUmurAPI($db);
    $api->handleRequest();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
