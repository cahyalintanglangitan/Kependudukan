<?php
// config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "kependudukan";
    private $username = "root";
    private $password = "";
    private $port = "3306";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Create PDO connection with proper options
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed: " . $exception->getMessage());
        }
        
        return $this->conn;
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $connection = $this->getConnection();
            
            if ($connection) {
                // Test with a simple query
                $stmt = $connection->query("SELECT 1");
                $result = $stmt->fetch();
                
                if ($result) {
                    return [
                        'success' => true,
                        'message' => 'Database connection successful',
                        'server_info' => $connection->getAttribute(PDO::ATTR_SERVER_VERSION)
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Database connection test failed'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database connection error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get database info for debugging
     */
    public function getDatabaseInfo() {
        try {
            $connection = $this->getConnection();
            
            if ($connection) {
                $info = [
                    'host' => $this->host,
                    'database' => $this->db_name,
                    'port' => $this->port,
                    'server_version' => $connection->getAttribute(PDO::ATTR_SERVER_VERSION),
                    'connection_status' => $connection->getAttribute(PDO::ATTR_CONNECTION_STATUS),
                    'charset' => $connection->query("SELECT @@character_set_connection")->fetchColumn()
                ];
                
                return $info;
            }
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Close connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>