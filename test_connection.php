<?php
// test_connection.php - File untuk test koneksi database
require_once 'config/database.php';
require_once 'config/config.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $test_result = $database->testConnection();
    
    if ($test_result['success']) {
        $db_info = $database->getDatabaseInfo();
        
        // Test query pada tabel disabilitas
        $conn = $database->getConnection();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM disabilitas WHERE KODE IS NOT NULL AND KODE != ''");
        $count = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Database connection and test successful',
            'database_info' => $db_info,
            'test_data' => [
                'disabilitas_records' => $count['total'],
                'test_query_time' => microtime(true)
            ],
            'config' => [
                'environment' => ENVIRONMENT,
                'app_name' => APP_NAME,
                'base_url' => BASE_URL
            ]
        ], JSON_PRETTY_PRINT);
        
    } else {
        throw new Exception($test_result['message']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'config_check' => [
            'host' => DB_HOST,
            'database' => DB_NAME,
            'port' => DB_PORT,
            'environment' => ENVIRONMENT
        ]
    ], JSON_PRETTY_PRINT);
}
?>