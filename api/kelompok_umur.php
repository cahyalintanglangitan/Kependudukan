<?php
// api/kelompok_umur.php
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
    $age_group = isset($_GET['age_group']) ? $_GET['age_group'] : 'all';
    $province = isset($_GET['province']) ? $_GET['province'] : 'all';
    $region_type = isset($_GET['region_type']) ? $_GET['region_type'] : 'all';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'total_desc';
    $gender = isset($_GET['gender']) ? $_GET['gender'] : 'all';

    $response_data = [];
    $all_stats = [];

    // Function to build region filter
    function buildRegionFilter($region_type) {
        switch ($region_type) {
            case 'province':
                return " AND w.kode_wilayah REGEXP '^[0-9]{1,2}\\.[0-9]{2}$' AND w.kode_wilayah NOT LIKE '%.%.%'";
            case 'kabupaten':
                return " AND w.kode_wilayah REGEXP '^[0-9]{1,2}\\.[0-9]{2}\\.[0-9]{2}$' AND w.kode_wilayah NOT LIKE '%.7%'";
            case 'kota':
                return " AND w.kode_wilayah REGEXP '^[0-9]{1,2}\\.[0-9]{2}\\.7[0-9]$'";
            default:
                return "";
        }
    }

    // Function to build province filter
    function buildProvinceFilter($province) {
        if ($province !== 'all') {
            return " AND w.kode_wilayah LIKE '" . $province . "%'";
        }
        return "";
    }

    // Function to build gender filter
    function buildGenderFilter($gender) {
        if ($gender !== 'all') {
            return " AND p.jenis_kelamin = '" . $gender . "'";
        }
        return "";
    }

    // Function to build age group conditions
    function getAgeCondition($age_group) {
        switch ($age_group) {
            case 'balita':
                return " AND p.umur >= 0 AND p.umur <= 4";
            case 'anak':
                return " AND p.umur >= 5 AND p.umur <= 17";
            case 'dewasa':
                return " AND p.umur >= 18 AND p.umur <= 59";
            case 'lansia':
                return " AND p.umur >= 60";
            default:
                return "";
        }
    }

    function buildSortClause($sort_by) {
        switch ($sort_by) {
            case 'kode_asc':
                return " ORDER BY CAST(REPLACE(w.kode_wilayah, '.', '') AS DECIMAL(10,0)) ASC";
            case 'kode_desc':
                return " ORDER BY CAST(REPLACE(w.kode_wilayah, '.', '') AS DECIMAL(10,0)) DESC";
            case 'wilayah_asc':
            case 'name_asc':
                return " ORDER BY w.nama_wilayah ASC";
            case 'wilayah_desc':
            case 'name_desc':
                return " ORDER BY w.nama_wilayah DESC";
            case 'balita_desc':
                return " ORDER BY balita DESC";
            case 'balita_asc':
                return " ORDER BY balita ASC";
            case 'anak_desc':
                return " ORDER BY anak DESC";
            case 'anak_asc':
                return " ORDER BY anak ASC";
            case 'dewasa_desc':
                return " ORDER BY dewasa DESC";
            case 'dewasa_asc':
                return " ORDER BY dewasa ASC";
            case 'lansia_desc':
                return " ORDER BY lansia DESC";
            case 'lansia_asc':
                return " ORDER BY lansia ASC";
            case 'laki_laki_desc':
                return " ORDER BY laki_laki DESC";
            case 'laki_laki_asc':
                return " ORDER BY laki_laki ASC";
            case 'perempuan_desc':
                return " ORDER BY perempuan DESC";
            case 'perempuan_asc':
                return " ORDER BY perempuan ASC";
            case 'total_asc':
                return " ORDER BY total ASC";
            case 'total_desc':
            default:
                return " ORDER BY total DESC";
        }
    }

    // Process each age group type
    $age_groups = ($age_group === 'all') ? ['overview', 'balita', 'anak', 'dewasa', 'lansia'] : [$age_group];

    foreach ($age_groups as $group) {
        if ($group === 'overview') {
            // Overview query - all age groups combined
            $sql = "SELECT 
                        w.kode_wilayah as kode,
                        w.nama_wilayah as wilayah,
                        COALESCE(SUM(CASE WHEN p.umur >= 0 AND p.umur <= 4 AND p.jenis_kelamin = 'L' THEN 1 ELSE 0 END), 0) as balita_laki,
                        COALESCE(SUM(CASE WHEN p.umur >= 0 AND p.umur <= 4 AND p.jenis_kelamin = 'P' THEN 1 ELSE 0 END), 0) as balita_perempuan,
                        COALESCE(SUM(CASE WHEN p.umur >= 0 AND p.umur <= 4 THEN 1 ELSE 0 END), 0) as balita,
                        COALESCE(SUM(CASE WHEN p.umur >= 5 AND p.umur <= 17 AND p.jenis_kelamin = 'L' THEN 1 ELSE 0 END), 0) as anak_laki,
                        COALESCE(SUM(CASE WHEN p.umur >= 5 AND p.umur <= 17 AND p.jenis_kelamin = 'P' THEN 1 ELSE 0 END), 0) as anak_perempuan,
                        COALESCE(SUM(CASE WHEN p.umur >= 5 AND p.umur <= 17 THEN 1 ELSE 0 END), 0) as anak,
                        COALESCE(SUM(CASE WHEN p.umur >= 18 AND p.umur <= 59 AND p.jenis_kelamin = 'L' THEN 1 ELSE 0 END), 0) as dewasa_laki,
                        COALESCE(SUM(CASE WHEN p.umur >= 18 AND p.umur <= 59 AND p.jenis_kelamin = 'P' THEN 1 ELSE 0 END), 0) as dewasa_perempuan,
                        COALESCE(SUM(CASE WHEN p.umur >= 18 AND p.umur <= 59 THEN 1 ELSE 0 END), 0) as dewasa,
                        COALESCE(SUM(CASE WHEN p.umur >= 60 AND p.jenis_kelamin = 'L' THEN 1 ELSE 0 END), 0) as lansia_laki,
                        COALESCE(SUM(CASE WHEN p.umur >= 60 AND p.jenis_kelamin = 'P' THEN 1 ELSE 0 END), 0) as lansia_perempuan,
                        COALESCE(SUM(CASE WHEN p.umur >= 60 THEN 1 ELSE 0 END), 0) as lansia,
                        COALESCE(COUNT(p.id), 0) as total
                    FROM wilayah w 
                    LEFT JOIN penduduk p ON w.id = p.wilayah_id 
                    WHERE w.kode_wilayah IS NOT NULL AND w.kode_wilayah != ''";
        } else {
            // Individual age group query
            $sql = "SELECT 
                        w.kode_wilayah as kode,
                        w.nama_wilayah as wilayah,
                        COALESCE(SUM(CASE WHEN p.jenis_kelamin = 'L' THEN 1 ELSE 0 END), 0) as laki_laki,
                        COALESCE(SUM(CASE WHEN p.jenis_kelamin = 'P' THEN 1 ELSE 0 END), 0) as perempuan,
                        COALESCE(COUNT(p.id), 0) as total
                    FROM wilayah w 
                    LEFT JOIN penduduk p ON w.id = p.wilayah_id 
                    WHERE w.kode_wilayah IS NOT NULL AND w.kode_wilayah != ''";
            
            // Add age condition
            $sql .= getAgeCondition($group);
        }

        // Add filters
        $sql .= buildRegionFilter($region_type);
        $sql .= buildProvinceFilter($province);
        
        if ($group !== 'overview') {
            $sql .= buildGenderFilter($gender);
        }

        $sql .= " GROUP BY w.id, w.kode_wilayah, w.nama_wilayah";
        $sql .= buildSortClause($sort_by);

        // Execute query
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process data
        $processed_data = [];
        foreach ($results as $row) {
            if ($group === 'overview') {
                $processed_data[] = [
                    'kode' => $row['kode'],
                    'wilayah' => trim($row['wilayah']),
                    'balita_laki' => (int)$row['balita_laki'],
                    'balita_perempuan' => (int)$row['balita_perempuan'],
                    'balita' => (int)$row['balita'],
                    'anak_laki' => (int)$row['anak_laki'],
                    'anak_perempuan' => (int)$row['anak_perempuan'],
                    'anak' => (int)$row['anak'],
                    'dewasa_laki' => (int)$row['dewasa_laki'],
                    'dewasa_perempuan' => (int)$row['dewasa_perempuan'],
                    'dewasa' => (int)$row['dewasa'],
                    'lansia_laki' => (int)$row['lansia_laki'],
                    'lansia_perempuan' => (int)$row['lansia_perempuan'],
                    'lansia' => (int)$row['lansia'],
                    'total' => (int)$row['total']
                ];
            } else {
                $processed_data[] = [
                    'kode' => $row['kode'],
                    'wilayah' => trim($row['wilayah']),
                    'laki_laki' => (int)$row['laki_laki'],
                    'perempuan' => (int)$row['perempuan'],
                    'total' => (int)$row['total']
                ];
            }
        }

        $response_data[$group] = $processed_data;

        // Calculate statistics for each group
        if ($group === 'overview') {
            $stats_sql = "SELECT 
                            SUM(CASE WHEN p.umur >= 0 AND p.umur <= 4 THEN 1 ELSE 0 END) as total_balita,
                            SUM(CASE WHEN p.umur >= 5 AND p.umur <= 17 THEN 1 ELSE 0 END) as total_anak,
                            SUM(CASE WHEN p.umur >= 18 AND p.umur <= 59 THEN 1 ELSE 0 END) as total_dewasa,
                            SUM(CASE WHEN p.umur >= 60 THEN 1 ELSE 0 END) as total_lansia,
                            COUNT(p.id) as grand_total,
                            COUNT(DISTINCT w.id) as total_regions
                          FROM wilayah w 
                          LEFT JOIN penduduk p ON w.id = p.wilayah_id 
                          WHERE w.kode_wilayah IS NOT NULL AND w.kode_wilayah != ''";
        } else {
            $stats_sql = "SELECT 
                            SUM(CASE WHEN p.jenis_kelamin = 'L' THEN 1 ELSE 0 END) as total_laki_laki,
                            SUM(CASE WHEN p.jenis_kelamin = 'P' THEN 1 ELSE 0 END) as total_perempuan,
                            COUNT(p.id) as total_count,
                            COUNT(DISTINCT w.id) as total_regions
                          FROM wilayah w 
                          LEFT JOIN penduduk p ON w.id = p.wilayah_id 
                          WHERE w.kode_wilayah IS NOT NULL AND w.kode_wilayah != ''";
            
            $stats_sql .= getAgeCondition($group);
        }

        $stats_sql .= buildRegionFilter($region_type);
        $stats_sql .= buildProvinceFilter($province);
        
        if ($group !== 'overview') {
            $stats_sql .= buildGenderFilter($gender);
        }

        $stats_stmt = $db->prepare($stats_sql);
        $stats_stmt->execute();
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

        $all_stats[$group] = $stats;
    }

    // Get provinces list
    $province_sql = "SELECT DISTINCT
                        SUBSTRING_INDEX(w.kode_wilayah, '.', 1) as kode,
                        w.nama_wilayah as wilayah
                     FROM wilayah w
                     WHERE w.kode_wilayah REGEXP '^[0-9]{1,2}\\.[0-9]{2}$' 
                     AND w.kode_wilayah NOT LIKE '%.%.%'
                     ORDER BY w.nama_wilayah ASC";
    
    $province_stmt = $db->prepare($province_sql);
    $province_stmt->execute();
    $provinces = $province_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get age group definitions for reference
    $age_definitions = [
        'balita' => ['min' => 0, 'max' => 4, 'label' => 'Balita (0-4 tahun)'],
        'anak' => ['min' => 5, 'max' => 17, 'label' => 'Anak (5-17 tahun)'],
        'dewasa' => ['min' => 18, 'max' => 59, 'label' => 'Dewasa (18-59 tahun)'],
        'lansia' => ['min' => 60, 'max' => null, 'label' => 'Lansia (60+ tahun)']
    ];

    // Build comprehensive response
    $response = [
        'success' => true,
        'data' => $response_data,
        'stats' => $all_stats,
        'provinces' => $provinces,
        'age_definitions' => $age_definitions,
        'meta' => [
            'filters' => [
                'age_group' => $age_group,
                'province' => $province,
                'region_type' => $region_type,
                'sort_by' => $sort_by,
                'gender' => $gender
            ],
            'timestamp' => date('Y-m-d H:i:s'),
            'total_records' => array_sum(array_map('count', $response_data))
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
            'code' => 'KELOMPOK_UMUR_API_ERROR',
            'details' => $e->getMessage()
        ],
        'meta' => [
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>
