<?php
// api/kelompok_umur.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'includes/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get action parameter
    $action = isset($_GET['action']) ? $_GET['action'] : 'get_all';
    
    switch ($action) {
        case 'get_all':
            handleGetAll($db);
            break;
            
        case 'get_stats':
            handleGetStats($db);
            break;
            
        case 'get_by_filter':
            handleGetByFilter($db);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action parameter',
                'data' => []
            ]);
    }
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'data' => []
    ]);
}

// Function to get all data
function handleGetAll($db) {
    $query = "SELECT 
                KODE,
                WILAYAH,
                `00-04` as `00_04`,
                `05-09` as `05_09`, 
                `10-14` as `10_14`,
                `15-19` as `15_19`,
                `20-24` as `20_24`,
                `25-29` as `25_29`,
                `30-34` as `30_34`,
                `35-39` as `35_39`,
                `40-44` as `40_44`,
                `45-49` as `45_49`,
                `50-54` as `50_54`,
                `55-59` as `55_59`,
                `60-64` as `60_64`,
                `65-69` as `65_69`,
                `70-74` as `70_74`,
                `>75` as `>75`
              FROM kel_umur_202401 
              ORDER BY WILAYAH";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert string numbers to integers
    foreach ($data as &$row) {
        foreach ($row as $key => &$value) {
            if ($key !== 'KODE' && $key !== 'WILAYAH') {
                $value = intval($value);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'total_records' => count($data),
        'message' => 'Data kelompok umur berhasil diambil'
    ]);
}

// Function to get statistics
function handleGetStats($db) {
    // Query to get statistics by age groups
    $query = "SELECT 
                SUM(`00-04`) as total_balita,
                SUM(`05-09` + `10-14`) as total_anak,
                SUM(`15-19` + `20-24` + `25-29` + `30-34` + `35-39` + `40-44` + `45-49` + `50-54` + `55-59`) as total_dewasa,
                SUM(`60-64` + `65-69` + `70-74` + `>75`) as total_lansia,
                SUM(`00-04` + `05-09` + `10-14` + `15-19` + `20-24` + `25-29` + `30-34` + `35-39` + `40-44` + `45-49` + `50-54` + `55-59` + `60-64` + `65-69` + `70-74` + `>75`) as total_keseluruhan,
                COUNT(*) as total_wilayah
              FROM kel_umur_202401";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $overall_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Query to get detailed age group stats
    $detail_query = "SELECT 
                       SUM(`00-04`) as `00_04`,
                       SUM(`05-09`) as `05_09`,
                       SUM(`10-14`) as `10_14`,
                       SUM(`15-19`) as `15_19`,
                       SUM(`20-24`) as `20_24`,
                       SUM(`25-29`) as `25_29`,
                       SUM(`30-34`) as `30_34`,
                       SUM(`35-39`) as `35_39`,
                       SUM(`40-44`) as `40_44`,
                       SUM(`45-49`) as `45_49`,
                       SUM(`50-54`) as `50_54`,
                       SUM(`55-59`) as `55_59`,
                       SUM(`60-64`) as `60_64`,
                       SUM(`65-69`) as `65_69`,
                       SUM(`70-74`) as `70_74`,
                       SUM(`>75`) as `>75`
                     FROM kel_umur_202401";
                     
    $detail_stmt = $db->prepare($detail_query);
    $detail_stmt->execute();
    
    $detail_stats = $detail_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Query to get top wilayah by categories
    $top_balita_query = "SELECT WILAYAH, `00-04` as total 
                         FROM kel_umur_202401 
                         ORDER BY `00-04` DESC LIMIT 1";
    
    $top_anak_query = "SELECT WILAYAH, (`05-09` + `10-14`) as total 
                       FROM kel_umur_202401 
                       ORDER BY (`05-09` + `10-14`) DESC LIMIT 1";
    
    $top_dewasa_query = "SELECT WILAYAH, 
                         (`15-19` + `20-24` + `25-29` + `30-34` + `35-39` + `40-44` + `45-49` + `50-54` + `55-59`) as total 
                         FROM kel_umur_202401 
                         ORDER BY (`15-19` + `20-24` + `25-29` + `30-34` + `35-39` + `40-44` + `45-49` + `50-54` + `55-59`) DESC LIMIT 1";
    
    $top_lansia_query = "SELECT WILAYAH, (`60-64` + `65-69` + `70-74` + `>75`) as total 
                         FROM kel_umur_202401 
                         ORDER BY (`60-64` + `65-69` + `70-74` + `>75`) DESC LIMIT 1";
    
    // Execute top queries
    $top_balita = $db->query($top_balita_query)->fetch(PDO::FETCH_ASSOC);
    $top_anak = $db->query($top_anak_query)->fetch(PDO::FETCH_ASSOC);
    $top_dewasa = $db->query($top_dewasa_query)->fetch(PDO::FETCH_ASSOC);
    $top_lansia = $db->query($top_lansia_query)->fetch(PDO::FETCH_ASSOC);
    
    // Convert string numbers to integers
    foreach ($overall_stats as $key => &$value) {
        $value = intval($value);
    }
    
    foreach ($detail_stats as $key => &$value) {
        $value = intval($value);
    }
    
    // Format top wilayah data
    $top_wilayah = [
        'balita' => [
            'wilayah' => $top_balita['WILAYAH'],
            'count' => intval($top_balita['total'])
        ],
        'anak' => [
            'wilayah' => $top_anak['WILAYAH'],
            'count' => intval($top_anak['total'])
        ],
        'dewasa' => [
            'wilayah' => $top_dewasa['WILAYAH'],
            'count' => intval($top_dewasa['total'])
        ],
        'lansia' => [
            'wilayah' => $top_lansia['WILAYAH'],
            'count' => intval($top_lansia['total'])
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'overall' => $overall_stats,
            'detail' => $detail_stats,
            'top_wilayah' => $top_wilayah
        ],
        'message' => 'Statistik kelompok umur berhasil diambil'
    ]);
}

// Function to get filtered data
function handleGetByFilter($db) {
    // Get filters from request
    $wilayah = isset($_GET['wilayah']) ? $_GET['wilayah'] : '';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'WILAYAH';
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
    
    // Build base query
    $query = "SELECT 
                KODE,
                WILAYAH,
                `00-04` as `00_04`,
                `05-09` as `05_09`, 
                `10-14` as `10_14`,
                `15-19` as `15_19`,
                `20-24` as `20_24`,
                `25-29` as `25_29`,
                `30-34` as `30_34`,
                `35-39` as `35_39`,
                `40-44` as `40_44`,
                `45-49` as `45_49`,
                `50-54` as `50_54`,
                `55-59` as `55_59`,
                `60-64` as `60_64`,
                `65-69` as `65_69`,
                `70-74` as `70_74`,
                `>75` as `>75`,
                (`00-04` + `05-09` + `10-14` + `15-19` + `20-24` + `25-29` + `30-34` + `35-39` + `40-44` + `45-49` + `50-54` + `55-59` + `60-64` + `65-69` + `70-74` + `>75`) as total_population
              FROM kel_umur_202401";
    
    $conditions = [];
    $params = [];
    
    // Add wilayah filter if specified
    if (!empty($wilayah)) {
        $conditions[] = "WILAYAH LIKE ?";
        $params[] = "%$wilayah%";
    }
    
    // Add WHERE clause if there are conditions
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // Add ORDER BY clause
    $allowed_sort_columns = [
        'WILAYAH', 'KODE', '00_04', '05_09', '10_14', '15_19', '20_24',
        '25_29', '30_34', '35_39', '40_44', '45_49', '50_54', '55_59',
        '60_64', '65_69', '70_74', '>75', 'total_population'
    ];
    
    if (in_array($sort_by, $allowed_sort_columns)) {
        $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
        
        // Handle special case for >75 column
        if ($sort_by === '>75') {
            $query .= " ORDER BY `>75` $sort_order";
        } else if ($sort_by === 'total_population') {
            $query .= " ORDER BY total_population $sort_order";
        } else {
            $query .= " ORDER BY `$sort_by` $sort_order";
        }
    } else {
        $query .= " ORDER BY WILAYAH ASC";
    }
    
    // Add LIMIT clause if specified
    if ($limit > 0) {
        $query .= " LIMIT $limit";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert string numbers to integers and calculate additional fields
    foreach ($data as &$row) {
        // Convert numeric fields
        foreach ($row as $key => &$value) {
            if ($key !== 'KODE' && $key !== 'WILAYAH') {
                $value = intval($value);
            }
        }
        
        // Calculate category totals
        $row['balita'] = $row['00_04'];
        $row['anak'] = $row['05_09'] + $row['10_14'];
        $row['remaja'] = $row['15_19'] + $row['20_24'];
        $row['dewasa'] = $row['25_29'] + $row['30_34'] + $row['35_39'] + 
                        $row['40_44'] + $row['45_49'] + $row['50_54'] + $row['55_59'];
        $row['lansia'] = $row['60_64'] + $row['65_69'] + $row['70_74'] + $row['>75'];
        
        // Calculate percentages
        if ($row['total_population'] > 0) {
            $row['balita_pct'] = round(($row['balita'] / $row['total_population']) * 100, 2);
            $row['anak_pct'] = round(($row['anak'] / $row['total_population']) * 100, 2);
            $row['remaja_pct'] = round(($row['remaja'] / $row['total_population']) * 100, 2);
            $row['dewasa_pct'] = round(($row['dewasa'] / $row['total_population']) * 100, 2);
            $row['lansia_pct'] = round(($row['lansia'] / $row['total_population']) * 100, 2);
        } else {
            $row['balita_pct'] = 0;
            $row['anak_pct'] = 0;
            $row['remaja_pct'] = 0;
            $row['dewasa_pct'] = 0;
            $row['lansia_pct'] = 0;
        }
    }
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) as total FROM kel_umur_202401";
    if (!empty($conditions)) {
        $count_query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'total_records' => intval($total_count),
        'filtered_records' => count($data),
        'filters' => [
            'wilayah' => $wilayah,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'limit' => $limit
        ],
        'message' => 'Data kelompok umur berhasil difilter'
    ]);
}
?>
