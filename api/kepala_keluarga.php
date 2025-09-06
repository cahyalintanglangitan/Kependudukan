<?php
header('Content-Type: application/json');
include_once '../config/database.php';

// Ambil parameter dari request
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$provinsi = isset($_GET['provinsi']) ? $_GET['provinsi'] : '';
$urutan = isset($_GET['urutan']) ? $_GET['urutan'] : '';

$offset = ($page - 1) * $limit;

try {
    // Base query
    $whereClause = "WHERE 1=1";
    $orderClause = "ORDER BY kk.nama ASC";
    $params = [];
    $types = "";
    
    // Filter provinsi
    if (!empty($provinsi)) {
        $whereClause .= " AND LEFT(kk.kode_wilayah, 2) = ?";
        $params[] = $provinsi;
        $types .= "s";
    }
    
    // Order by
    switch ($urutan) {
        case 'nama_asc':
            $orderClause = "ORDER BY kk.nama ASC";
            break;
        case 'nama_desc':
            $orderClause = "ORDER BY kk.nama DESC";
            break;
        case 'umur_asc':
            $orderClause = "ORDER BY kk.umur ASC";
            break;
        case 'umur_desc':
            $orderClause = "ORDER BY kk.umur DESC";
            break;
    }
    
    // Query untuk count total
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM data_kk kk 
        LEFT JOIN wilayah w ON kk.kode_wilayah = w.kode_wilayah 
        $whereClause
    ";
    
    if (!empty($params)) {
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $totalResult = $countStmt->get_result();
    } else {
        $totalResult = $conn->query($countQuery);
    }
    
    $totalRow = $totalResult->fetch_assoc();
    $total = $totalRow['total'];
    
    // Query untuk data dengan pagination
    $dataQuery = "
        SELECT 
            kk.*,
            w.nama_wilayah,
            (SELECT COUNT(*) FROM anggota_keluarga ak WHERE ak.no_kk = kk.no_kk) as jumlah_anggota,
            CASE 
                WHEN kk.status_perkawinan = 'Kawin' THEN 'Aktif'
                ELSE 'Tidak Aktif'
            END as status
        FROM data_kk kk 
        LEFT JOIN wilayah w ON kk.kode_wilayah = w.kode_wilayah 
        $whereClause 
        $orderClause 
        LIMIT ? OFFSET ?
    ";
    
    // Add pagination parameters
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    if (!empty($params)) {
        $dataStmt = $conn->prepare($dataQuery);
        $dataStmt->bind_param($types, ...$params);
        $dataStmt->execute();
        $dataResult = $dataStmt->get_result();
    } else {
        $dataResult = $conn->query($dataQuery);
    }
    
    $data = [];
    while ($row = $dataResult->fetch_assoc()) {
        // Format alamat
        $alamat = $row['nama_wilayah'] ? $row['nama_wilayah'] : 'Tidak Diketahui';
        if (!empty($row['alamat'])) {
            $alamat = $row['alamat'] . ', ' . $alamat;
        }
        
        $data[] = [
            'id' => $row['id'],
            'nama' => $row['nama'],
            'nik' => $row['nik'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'umur' => $row['umur'],
            'pekerjaan' => $row['pekerjaan'] ?: 'Tidak Diketahui',
            'alamat' => $alamat,
            'jumlah_anggota' => $row['jumlah_anggota'],
            'status' => $row['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
