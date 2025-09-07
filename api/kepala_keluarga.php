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
    $gender = isset($_GET['gender']) ? $_GET['gender'] : 'all';
    $age_range = isset($_GET['age_range']) ? $_GET['age_range'] : 'all';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Base query untuk data kepala keluarga per wilayah
    $sql = "SELECT 
                w.KODE as kode,
                w.WILAYAH as wilayah,
                COALESCE(SUM(CASE WHEN kk.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END), 0) as laki_laki,
                COALESCE(SUM(CASE WHEN kk.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END), 0) as perempuan,
                COALESCE(COUNT(kk.id), 0) as total_kk,
                COALESCE(AVG(kk.umur), 0) as rata_rata_umur,
                COALESCE(SUM(CASE WHEN kk.umur BETWEEN 20 AND 30 THEN 1 ELSE 0 END), 0) as usia_20_30,
                COALESCE(SUM(CASE WHEN kk.umur BETWEEN 31 AND 40 THEN 1 ELSE 0 END), 0) as usia_31_40,
                COALESCE(SUM(CASE WHEN kk.umur BETWEEN 41 AND 50 THEN 1 ELSE 0 END), 0) as usia_41_50,
                COALESCE(SUM(CASE WHEN kk.umur > 50 THEN 1 ELSE 0 END), 0) as usia_50_plus,
                COALESCE(SUM(CASE WHEN kk.status_perkawinan = 'Kawin' THEN 1 ELSE 0 END), 0) as status_kawin,
                COALESCE(SUM(CASE WHEN kk.status_perkawinan IN ('Belum Kawin', 'Cerai Hidup', 'Cerai Mati') THEN 1 ELSE 0 END), 0) as status_tidak_kawin
            FROM wilayah w 
            LEFT JOIN data_kk kk ON (
                CASE 
                    WHEN LENGTH(w.KODE) = 2 THEN LEFT(kk.kode_wilayah, 2) = w.KODE
                    WHEN LENGTH(w.KODE) = 5 THEN LEFT(kk.kode_wilayah, 5) = w.KODE
                    ELSE kk.kode_wilayah = w.KODE
                END
            )
            WHERE w.KODE IS NOT NULL AND w.KODE != '' AND w.WILAYAH != 'INDONESIA'";

    $params = [];

    // Enhanced region type filtering
    if ($region_type !== 'all') {
        switch ($region_type) {
            case 'province':
                // Province codes are exactly 2 digits
                $sql .= " AND w.KODE REGEXP '^[0-9]{2}$'";
                break;
            case 'kabupaten':
                // Kabupaten codes have pattern XX.XX but NOT XX.7X
                $sql .= " AND w.KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$' AND w.KODE NOT LIKE '%.7%'";
                break;
            case 'kota':
                // Kota codes have pattern XX.7X
                $sql .= " AND w.KODE REGEXP '^[0-9]{2}\\.7[0-9]$'";
                break;
        }
    }

    // Province filtering
    if ($province !== 'all') {
        $sql .= " AND w.KODE LIKE ?";
        $params[] = $province . '%';
    }

    // Gender filtering (applied to the KK data)
    if ($gender !== 'all') {
        $sql .= " AND (kk.jenis_kelamin = ? OR kk.jenis_kelamin IS NULL)";
        $params[] = $gender;
    }

    // Age range filtering
    if ($age_range !== 'all') {
        switch ($age_range) {
            case '20-30':
                $sql .= " AND (kk.umur BETWEEN 20 AND 30 OR kk.umur IS NULL)";
                break;
            case '31-40':
                $sql .= " AND (kk.umur BETWEEN 31 AND 40 OR kk.umur IS NULL)";
                break;
            case '41-50':
                $sql .= " AND (kk.umur BETWEEN 41 AND 50 OR kk.umur IS NULL)";
                break;
            case '50+':
                $sql .= " AND (kk.umur > 50 OR kk.umur IS NULL)";
                break;
        }
    }

    // Group by wilayah
    $sql .= " GROUP BY w.KODE, w.WILAYAH";

    // Enhanced sorting options
    switch ($sort_by) {
        case 'total_asc':
            $sql .= " ORDER BY total_kk ASC";
            break;
        case 'total_desc':
            $sql .= " ORDER BY total_kk DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY w.WILAYAH ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY w.WILAYAH DESC";
            break;
        case 'laki_desc':
            $sql .= " ORDER BY laki_laki DESC";
            break;
        case 'perempuan_desc':
            $sql .= " ORDER BY perempuan DESC";
            break;
        case 'age_desc':
            $sql .= " ORDER BY rata_rata_umur DESC";
            break;
        default:
            $sql .= " ORDER BY total_kk DESC";
    }

    // Add pagination
    $sql .= " LIMIT " . $limit . " OFFSET " . $offset;

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
            'laki_laki' => (int)$row['laki_laki'],
            'perempuan' => (int)$row['perempuan'],
            'total_kk' => (int)$row['total_kk'],
            'rata_rata_umur' => round((float)$row['rata_rata_umur'], 1),
            'breakdown_usia' => [
                'usia_20_30' => (int)$row['usia_20_30'],
                'usia_31_40' => (int)$row['usia_31_40'],
                'usia_41_50' => (int)$row['usia_41_50'],
                'usia_50_plus' => (int)$row['usia_50_plus']
            ],
            'status_perkawinan' => [
                'kawin' => (int)$row['status_kawin'],
                'tidak_kawin' => (int)$row['status_tidak_kawin']
            ]
        ];
    }

    // Calculate aggregate statistics dengan filter yang sama
    $stats_sql = "SELECT 
                    COALESCE(SUM(CASE WHEN kk.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END), 0) as total_laki,
                    COALESCE(SUM(CASE WHEN kk.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END), 0) as total_perempuan,
                    COALESCE(COUNT(kk.id), 0) as grand_total_kk,
                    COALESCE(AVG(kk.umur), 0) as rata_rata_umur_keseluruhan,
                    COALESCE(SUM(CASE WHEN kk.umur BETWEEN 20 AND 30 THEN 1 ELSE 0 END), 0) as total_usia_20_30,
                    COALESCE(SUM(CASE WHEN kk.umur BETWEEN 31 AND 40 THEN 1 ELSE 0 END), 0) as total_usia_31_40,
                    COALESCE(SUM(CASE WHEN kk.umur BETWEEN 41 AND 50 THEN 1 ELSE 0 END), 0) as total_usia_41_50,
                    COALESCE(SUM(CASE WHEN kk.umur > 50 THEN 1 ELSE 0 END), 0) as total_usia_50_plus,
                    COALESCE(SUM(CASE WHEN kk.status_perkawinan = 'Kawin' THEN 1 ELSE 0 END), 0) as total_kawin,
                    COALESCE(SUM(CASE WHEN kk.status_perkawinan IN ('Belum Kawin', 'Cerai Hidup', 'Cerai Mati') THEN 1 ELSE 0 END), 0) as total_tidak_kawin,
                    COUNT(DISTINCT w.KODE) as total_regions
                  FROM wilayah w 
                  LEFT JOIN data_kk kk ON (
                    CASE 
                        WHEN LENGTH(w.KODE) = 2 THEN LEFT(kk.kode_wilayah, 2) = w.KODE
                        WHEN LENGTH(w.KODE) = 5 THEN LEFT(kk.kode_wilayah, 5) = w.KODE
                        ELSE kk.kode_wilayah = w.KODE
                    END
                  )
                  WHERE w.KODE IS NOT NULL AND w.KODE != '' AND w.WILAYAH != 'INDONESIA'";
    
    $stats_params = [];

    // Apply same filters to stats
    if ($region_type !== 'all') {
        switch ($region_type) {
            case 'province':
                $stats_sql .= " AND w.KODE REGEXP '^[0-9]{2}$'";
                break;
            case 'kabupaten':
                $stats_sql .= " AND w.KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$' AND w.KODE NOT LIKE '%.7%'";
                break;
            case 'kota':
                $stats_sql .= " AND w.KODE REGEXP '^[0-9]{2}\\.7[0-9]$'";
                break;
        }
    }
    
    if ($province !== 'all') {
        $stats_sql .= " AND w.KODE LIKE ?";
        $stats_params[] = $province . '%';
    }

    if ($gender !== 'all') {
        $stats_sql .= " AND (kk.jenis_kelamin = ? OR kk.jenis_kelamin IS NULL)";
        $stats_params[] = $gender;
    }

    if ($age_range !== 'all') {
        switch ($age_range) {
            case '20-30':
                $stats_sql .= " AND (kk.umur BETWEEN 20 AND 30 OR kk.umur IS NULL)";
                break;
            case '31-40':
                $stats_sql .= " AND (kk.umur BETWEEN 31 AND 40 OR kk.umur IS NULL)";
                break;
            case '41-50':
                $stats_sql .= " AND (kk.umur BETWEEN 41 AND 50 OR kk.umur IS NULL)";
                break;
            case '50+':
                $stats_sql .= " AND (kk.umur > 50 OR kk.umur IS NULL)";
                break;
        }
    }

    $stats_stmt = $db->prepare($stats_sql);
    $stats_stmt->execute($stats_params);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    // Clean stats data
    $processedStats = [
        'total_laki' => (int)$stats['total_laki'],
        'total_perempuan' => (int)$stats['total_perempuan'],
        'grand_total_kk' => (int)$stats['grand_total_kk'],
        'rata_rata_umur_keseluruhan' => round((float)$stats['rata_rata_umur_keseluruhan'], 1),
        'breakdown_usia_total' => [
            'total_usia_20_30' => (int)$stats['total_usia_20_30'],
            'total_usia_31_40' => (int)$stats['total_usia_31_40'],
            'total_usia_41_50' => (int)$stats['total_usia_41_50'],
            'total_usia_50_plus' => (int)$stats['total_usia_50_plus']
        ],
        'status_perkawinan_total' => [
            'total_kawin' => (int)$stats['total_kawin'],
            'total_tidak_kawin' => (int)$stats['total_tidak_kawin']
        ],
        'total_regions' => (int)$stats['total_regions']
    ];

    // Get province breakdown untuk dropdown/filter
    $province_sql = "SELECT 
                        w.KODE, 
                        w.WILAYAH, 
                        COUNT(kk.id) as total_kk
                     FROM wilayah w 
                     LEFT JOIN data_kk kk ON LEFT(kk.kode_wilayah, 2) = w.KODE
                     WHERE w.KODE REGEXP '^[0-9]{2}$'
                     GROUP BY w.KODE, w.WILAYAH
                     ORDER BY total_kk DESC";
    
    $province_stmt = $db->prepare($province_sql);
    $province_stmt->execute();
    $provinces = $province_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process province data
    $processedProvinces = [];
    foreach ($provinces as $prov) {
        $processedProvinces[] = [
            'kode' => $prov['KODE'],
            'wilayah' => trim($prov['WILAYAH']),
            'total_kk' => (int)$prov['total_kk']
        ];
    }

    // Get detailed KK data for table (jika diminta)
    $detailed_data = [];
    if (isset($_GET['include_details']) && $_GET['include_details'] === 'true') {
        $detail_sql = "SELECT 
                          kk.*,
                          w.WILAYAH as nama_wilayah,
                          (SELECT COUNT(*) FROM anggota_keluarga ak WHERE ak.no_kk = kk.no_kk) as jumlah_anggota
                       FROM data_kk kk 
                       LEFT JOIN wilayah w ON LEFT(kk.kode_wilayah, 2) = LEFT(w.KODE, 2)
                       WHERE 1=1";

        $detail_params = [];

        if ($province !== 'all') {
            $detail_sql .= " AND LEFT(kk.kode_wilayah, 2) = ?";
            $detail_params[] = $province;
        }

        if ($gender !== 'all') {
            $detail_sql .= " AND kk.jenis_kelamin = ?";
            $detail_params[] = $gender;
        }

        if ($age_range !== 'all') {
            switch ($age_range) {
                case '20-30':
                    $detail_sql .= " AND kk.umur BETWEEN 20 AND 30";
                    break;
                case '31-40':
                    $detail_sql .= " AND kk.umur BETWEEN 31 AND 40";
                    break;
                case '41-50':
                    $detail_sql .= " AND kk.umur BETWEEN 41 AND 50";
                    break;
                case '50+':
                    $detail_sql .= " AND kk.umur > 50";
                    break;
            }
        }

        $detail_sql .= " ORDER BY kk.created_at DESC LIMIT 20";

        $detail_stmt = $db->prepare($detail_sql);
        $detail_stmt->execute($detail_params);
        $details = $detail_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $detail) {
            $detailed_data[] = [
                'id' => $detail['id'],
                'no_kk' => $detail['no_kk'],
                'nik' => $detail['nik'],
                'nama' => $detail['nama'],
                'jenis_kelamin' => $detail['jenis_kelamin'],
                'umur' => (int)$detail['umur'],
                'tempat_lahir' => $detail['tempat_lahir'],
                'tanggal_lahir' => $detail['tanggal_lahir'],
                'agama' => $detail['agama'],
                'pendidikan' => $detail['pendidikan'],
                'pekerjaan' => $detail['pekerjaan'],
                'status_perkawinan' => $detail['status_perkawinan'],
                'alamat' => $detail['alamat'],
                'wilayah' => $detail['nama_wilayah'],
                'jumlah_anggota' => (int)$detail['jumlah_anggota'],
                'created_at' => $detail['created_at']
            ];
        }
    }

    // Get total count for pagination
    $count_sql = "SELECT COUNT(DISTINCT w.KODE) as total_count
                  FROM wilayah w 
                  LEFT JOIN data_kk kk ON (
                    CASE 
                        WHEN LENGTH(w.KODE) = 2 THEN LEFT(kk.kode_wilayah, 2) = w.KODE
                        WHEN LENGTH(w.KODE) = 5 THEN LEFT(kk.kode_wilayah, 5) = w.KODE
                        ELSE kk.kode_wilayah = w.KODE
                    END
                  )
                  WHERE w.KODE IS NOT NULL AND w.KODE != '' AND w.WILAYAH != 'INDONESIA'";

    // Apply same filters for count
    $count_params = [];
    if ($region_type !== 'all') {
        switch ($region_type) {
            case 'province':
                $count_sql .= " AND w.KODE REGEXP '^[0-9]{2}$'";
                break;
            case 'kabupaten':
                $count_sql .= " AND w.KODE REGEXP '^[0-9]{2}\\.[0-9]{2}$' AND w.KODE NOT LIKE '%.7%'";
                break;
            case 'kota':
                $count_sql .= " AND w.KODE REGEXP '^[0-9]{2}\\.7[0-9]$'";
                break;
        }
    }
    
    if ($province !== 'all') {
        $count_sql .= " AND w.KODE LIKE ?";
        $count_params[] = $province . '%';
    }

    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_count'];

    // Build comprehensive response
    $response = [
        'success' => true,
        'data' => $processedData,
        'stats' => $processedStats,
        'provinces' => $processedProvinces,
        'detailed_data' => $detailed_data,
        'meta' => [
            'count' => count($processedData),
            'total_count' => (int)$total_count,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total_count / $limit),
            'filters' => [
                'province' => $province,
                'region_type' => $region_type,
                'sort_by' => $sort_by,
                'gender' => $gender,
                'age_range' => $age_range,
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
