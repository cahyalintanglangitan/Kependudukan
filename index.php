<?php
// Mulai session
session_start();

// Include konfigurasi database
require_once 'config/database.php';

// Mendapatkan parameter page dari URL
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Menentukan file yang akan di-include berdasarkan parameter page
$current_file = basename($_SERVER['PHP_SELF'], '.php');
if (isset($_GET['page'])) {
    $current_file = $_GET['page'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Analytics Kependudukan</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="container-fluid p-4">
                <?php
                // Routing untuk halaman yang berbeda
                switch($page) {
                    case 'dashboard':
                        include 'pages/dashboard.php';
                        break;
                        
                    case 'demografi':
                        include 'pages/dashboard/demografi.php';
                        break;
                        
                    case 'akta':
                        include 'pages/dashboard/akta.php';
                        break;
                        
                    case 'disabilitas':
                        include 'pages/dashboard/disabilitas.php';
                        break;
                        
                    case 'kelompok-umur':
                        include 'pages/dashboard/kelompok_umur.php';
                        break;
                        
                    case 'kepala-keluarga':
                        include 'pages/dashboard/kepala_keluarga.php';
                        break;
                        
                    default:
                        include 'pages/dashboard.php';
                        break;
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
