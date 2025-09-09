<?php
// api/kepala_keluarga.php

// Headers untuk API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error di output
ini_set('log_errors', 1);

try {
    // Include database config
    $config_path = __DIR__ . '/../config/database.php';
    if (!file_exists($config_path)) {
        throw new Exception("Database configuration file not found at: $config_path");
    }
    
    include_once $config_path;
    
    // Test database connection first
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Could not establish database connection");
    }
    
    // Check if required tables exist
    if (!$database->tableExists('kepala_keluarga')) {
        throw new Exception("Table 'kepala_keluarga' does not exist. Please run the database setup script first.");
    }
    
    // Get action parameter
    $action = isset($_GET['action']) ? trim($_GET['action']) : 'list';
    
    // Route to appropriate function
    switch($action) {
        case 'stats':
            getKepalaKeluargaStats($db);
            break;
        case 'distribution':
            getDistributionByProvince($db);
            break;
        case 'by_province':
            getDataByProvince($db);
            break;
        case 'by_gender':
            getDataByGender($db);
            break;
        case 'test':
            testAPI($db);
            break;
        case 'list':
        default:
            getKepalaKeluargaList($db);
            break;
    }
    
} catch(Exception $e) {
    // Log error
    error_log("API Error: " . $e->getMessage() . " in " . __FILE__ . " on line " . __LINE__);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage(),
        'debug_info' => [
            'file' => basename(__FILE__),
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => isset($action) ? $action : 'unknown',
            'method' => $_SERVER['REQUEST_METHOD']
        ]
    ], JSON_PRETTY_PRINT);
}

/**
 * Get statistics for Kepala Keluarga
 */
function getKepalaKeluargaStats($db) {
    try {
        $stats = [];
        
        // Total Kepala Keluarga
        $query = "SELECT COUNT(*) as total FROM kepala_keluarga WHERE status_aktif = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['total_kepala_keluarga'] = (int) ($result['total'] ?? 0);
        
        // Kepala Keluarga Laki-laki
        $query = "SELECT COUNT(*) as total FROM kepala_keluarga WHERE jenis_kelamin = 'L' AND status_aktif = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['kepala_keluarga_laki_laki'] = (int) ($result['total'] ?? 0);
        
        // Kepala Keluarga Perempuan
        $query = "SELECT COUNT(*) as total FROM kepala_keluarga WHERE jenis_kelamin = 'P' AND status_aktif = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['kepala_keluarga_perempuan'] = (int) ($result['total'] ?? 0);
        
        // Additional stats
        $query = "SELECT 
                    AVG(umur) as rata_rata_umur,
                    MIN(umur) as umur_minimum,
                    MAX(umur) as umur_maximum
                  FROM kepala_keluarga 
                  WHERE status_aktif = 1 AND umur IS NOT NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $stats['rata_rata_umur'] = round((float) ($result['rata_rata_umur'] ?? 0), 1);
        $stats['umur_minimum'] = (int) ($result['umur_minimum'] ?? 0);
        $stats['umur_maximum'] = (int) ($result['umur_maximum'] ?? 0);
        $stats['updated_at'] = date('Y-m-d H:i:s');
        
        echo json_encode([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully'
        ], JSON_PRETTY_PRINT);
        
    } catch(Exception $e) {
        throw new Exception("Error getting statistics: " . $e->getMessage());
    }
}

/**
 * Get distribution by province
 */
function getDistributionByProvince($db) {
    try {
        $query = "SELECT 
                    COALESCE(p.nama_provinsi, 'Tidak Diketahui') as nama_provinsi,
                    COUNT(kk.id) as jumlah_kk,
                    COUNT(CASE WHEN kk.jenis_kelamin = 'L' THEN 1 END) as laki_laki,
                    COUNT(CASE WHEN kk.jenis_kelamin = 'P' THEN 1 END) as perempuan,
                    ROUND(AVG(kk.umur), 1) as rata_rata_umur
                  FROM kepala_keluarga kk
                  LEFT JOIN provinsi p ON kk.id_provinsi = p.id
                  WHERE kk.status_aktif = 1
                  GROUP BY p.id, p.nama_provinsi
                  ORDER BY jumlah_kk DESC
                  LIMIT 15";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'total_records' => count($result),
            'message' => 'Distribution data retrieved successfully'
        ], JSON_PRETTY_PRINT);
        
    } catch(Exception $e) {
        throw new Exception("Error getting distribution data: " . $e->getMessage());
    }
}

/**
 * Get data by specific province
 */
function getDataByProvince($db) {
    try {
        $provinsi_id = isset($_GET['provinsi_id']) ? (int)$_GET['provinsi_id'] : null;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
        
        $whereClause = "WHERE kk.status_aktif = 1";
        $params = [];
        
        if ($provinsi_id) {
            $whereClause .= " AND kk.id_provinsi = :provinsi_id";
            $params[':provinsi_id'] = $provinsi_id;
        }
        
        $query = "SELECT 
                    kk.id,
                    kk.no_kk,
                    kk.nama_lengkap,
                    kk.nik,
                    kk.jenis_kelamin,
                    kk.umur,
                    kk.alamat,
                    COALESCE(p.nama_provinsi, 'Tidak Diketahui') as nama_provinsi,
                    COALESCE(k.nama_kabupaten, 'Tidak Diketahui') as nama_kabupaten,
                    COALESCE(kec.nama_kecamatan, 'Tidak Diketahui') as nama_kecamatan,
                    kk.created_at
                  FROM kepala_keluarga kk
                  LEFT JOIN provinsi p ON kk.id_provinsi = p.id
                  LEFT JOIN kabupaten k ON kk.id_kabupaten = k.id
                  LEFT JOIN kecamatan kec ON kk.id_kecamatan = kec.id
                  $whereClause
                  ORDER BY kk.created_at DESC
                  LIMIT $limit";
        
        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'total_records' => count($result),
            'filters' => ['provinsi_id' => $provinsi_id],
            'message' => 'Province data retrieved successfully'
        ], JSON_PRETTY_PRINT);
        
    } catch(Exception $e) {
        throw new Exception("Error getting province data: " . $e->getMessage());
    }
}

/**
 * Get data by gender
 */
function getDataByGender($db) {
    try {
        $query = "SELECT 
                    CASE 
                        WHEN jenis_kelamin = 'L' THEN 'Laki-laki'
                        WHEN jenis_kelamin = 'P' THEN 'Perempuan'
                        ELSE 'Tidak Diketahui'
                    END as jenis_kelamin,
                    jenis_kelamin as kode_kelamin,
                    COUNT(*) as jumlah,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM kepala_keluarga WHERE status_aktif = 1)), 2) as persentase,
                    ROUND(AVG(umur), 1) as rata_rata_umur
                  FROM kepala_keluarga 
                  WHERE status_aktif = 1
                  GROUP BY jenis_kelamin
                  ORDER BY jumlah DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'message' => 'Gender data retrieved successfully'
        ], JSON_PRETTY_PRINT);
        
    } catch(Exception $e) {
        throw new Exception("Error getting gender data: " . $e->getMessage());
    }
}

/**
 * Get list of Kepala Keluarga with pagination
 */
function getKepalaKeluargaList($db) {
    try {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM kepala_keluarga WHERE status_aktif = 1";
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute();
        $totalRecords = (int) $countStmt->fetch()['total'];
        
        // Get paginated data
        $query = "SELECT 
                    kk.id,
                    kk.no_kk,
                    kk.nama_lengkap,
                    kk.nik,
                    kk.jenis_kelamin,
                    kk.umur,
                    kk.alamat,
                    kk.rt,
                    kk.rw,
                    COALESCE(p.nama_provinsi, 'Tidak Diketahui') as nama_provinsi,
                    COALESCE(k.nama_kabupaten, 'Tidak Diketahui') as nama_kabupaten,
                    COALESCE(kec.nama_kecamatan, 'Tidak Diketahui') as nama_kecamatan,
                    kk.created_at
                  FROM kepala_keluarga kk
                  LEFT JOIN provinsi p ON kk.id_provinsi = p.id
                  LEFT JOIN kabupaten k ON kk.id_kabupaten = k.id
                  LEFT JOIN kecamatan kec ON kk.id_kecamatan = kec.id
                  WHERE kk.status_aktif = 1
                  ORDER BY kk.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalRecords / $limit),
                'total_records' => $totalRecords,
                'per_page' => $limit,
                'has_next' => $page < ceil($totalRecords / $limit),
                'has_prev' => $page > 1
            ],
            'message' => 'Data retrieved successfully'
        ], JSON_PRETTY_PRINT);
        
    } catch(Exception $e) {
        throw new Exception("Error getting kepala keluarga list: " . $e->getMessage());
    }
}

/**
 * Test API functionality
 */
function testAPI($db) {
    try {
        $tests = [];
        
        // Test 1: Database connection
        $tests['database_connection'] = [
            'status' => 'success',
            'message' => 'Database connected successfully'
        ];
        
        // Test 2: Table exists
        $database = Database::getInstance();
        if ($database->tableExists('kepala_keluarga')) {
            $tests['table_kepala_keluarga'] = [
                'status' => 'success',
                'message' => 'Table kepala_keluarga exists'
            ];
        } else {
            $tests['table_kepala_keluarga'] = [
                'status' => 'error',
                'message' => 'Table kepala_keluarga not found'
            ];
        }
        
        // Test 3: Sample query
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM kepala_keluarga LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch();
            $tests['sample_query'] = [
                'status' => 'success',
                'message' => "Query successful, found {$result['total']} records"
            ];
        } catch(Exception $e) {
            $tests['sample_query'] = [
                'status' => 'error',
                'message' => 'Query failed: ' . $e->getMessage()
            ];
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'API test completed',
            'tests' => $tests,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        
    } catch(Exception $e) {
        throw new Exception("Error running API test: " . $e->getMessage());
    }
}
?>
