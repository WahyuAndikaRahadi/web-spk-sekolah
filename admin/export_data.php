<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk export data ke CSV
function exportToCSV($koneksi, $query, $filename) {
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        // Set header untuk download file
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Buat file pointer untuk output
        $output = fopen('php://output', 'w');
        
        // Ambil nama kolom dari result
        $fields = mysqli_fetch_fields($result);
        $headers = [];
        foreach ($fields as $field) {
            $headers[] = $field->name;
        }
        
        // Output header
        fputcsv($output, $headers);
        
        // Output data
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
    } else {
        return false;
    }
}

// Process export requests first, before any HTML output
if (isset($_POST['export_sekolah']) || 
    isset($_POST['export_kriteria']) || 
    isset($_POST['export_penilaian']) || 
    isset($_POST['export_ranking']) || 
    isset($_POST['export_semua'])) {
    
    // Export data sekolah
    if (isset($_POST['export_sekolah'])) {
        $query = "SELECT s.*, ha.total_skor, ha.peringkat 
                FROM sekolah s 
                LEFT JOIN hasil_akhir ha ON s.id_sekolah = ha.id_sekolah 
                ORDER BY ha.peringkat ASC, s.nama_sekolah ASC";
        exportToCSV($koneksi, $query, 'data_sekolah_' . date('Y-m-d') . '.csv');
    }
    
    // Export data kriteria
    if (isset($_POST['export_kriteria'])) {
        $query = "SELECT * FROM kriteria ORDER BY id_kriteria ASC";
        exportToCSV($koneksi, $query, 'data_kriteria_' . date('Y-m-d') . '.csv');
    }
    
    // Export data penilaian
    if (isset($_POST['export_penilaian'])) {
        $query = "SELECT s.nama_sekolah, k.nama_kriteria, p.nilai 
                FROM penilaian p
                JOIN sekolah s ON p.id_sekolah = s.id_sekolah
                JOIN kriteria k ON p.id_kriteria = k.id_kriteria
                ORDER BY s.nama_sekolah ASC, k.nama_kriteria ASC";
        exportToCSV($koneksi, $query, 'data_penilaian_' . date('Y-m-d') . '.csv');
    }
    
    // Export data ranking
    if (isset($_POST['export_ranking'])) {
        $query = "SELECT s.nama_sekolah, s.alamat, s.akreditasi, s.total_guru, s.rata_un, s.biaya_spp, 
                        ha.total_skor, ha.peringkat 
                FROM hasil_akhir ha
                JOIN sekolah s ON ha.id_sekolah = s.id_sekolah
                ORDER BY ha.peringkat ASC, s.nama_sekolah ASC";
        exportToCSV($koneksi, $query, 'data_ranking_' . date('Y-m-d') . '.csv');
    }
    
    // Export data lengkap (gabungan semua)
    if (isset($_POST['export_semua'])) {
        // Membuat query yang menggabungkan semua data
        $query = "SELECT s.id_sekolah, s.nama_sekolah, s.alamat, s.akreditasi, s.total_guru, s.rata_un, s.biaya_spp,
                        GROUP_CONCAT(CONCAT(k.nama_kriteria, ': ', p.nilai) SEPARATOR '  ') as penilaian,
                        ha.total_skor, ha.peringkat
                FROM sekolah s
                LEFT JOIN penilaian p ON s.id_sekolah = p.id_sekolah
                LEFT JOIN kriteria k ON p.id_kriteria = k.id_kriteria
                LEFT JOIN hasil_akhir ha ON s.id_sekolah = ha.id_sekolah
                GROUP BY s.id_sekolah
                ORDER BY ha.peringkat ASC, s.nama_sekolah ASC";
        exportToCSV($koneksi, $query, 'data_lengkap_spk_' . date('Y-m-d') . '.csv');
    }
}

require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data | SPK Pemilihan SMA Swasta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-gray-800"><i class="bi bi-file-earmark-excel me-2"></i>Export Data</h1>
        
        <?php if(isset($_SESSION['pesan'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <?php if(isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Export Data ke CSV</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Anda dapat mengekspor data dari sistem SPK ini ke dalam format CSV untuk dianalisis lebih lanjut atau diimpor ke aplikasi lain seperti Excel atau Google Sheets.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Data Sekolah</h5>
                                        <p class="card-text">Export informasi dasar tentang sekolah termasuk skor dan peringkat.</p>
                                        <form method="post">
                                            <button type="submit" name="export_sekolah" class="btn btn-primary btn-sm">
                                                <i class="bi bi-download me-1"></i> Download CSV
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Data Kriteria</h5>
                                        <p class="card-text">Export semua kriteria beserta bobot dan tipe kriteria.</p>
                                        <form method="post">
                                            <button type="submit" name="export_kriteria" class="btn btn-primary btn-sm">
                                                <i class="bi bi-download me-1"></i> Download CSV
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Data Penilaian</h5>
                                        <p class="card-text">Export data penilaian setiap sekolah pada semua kriteria.</p>
                                        <form method="post">
                                            <button type="submit" name="export_penilaian" class="btn btn-primary btn-sm">
                                                <i class="bi bi-download me-1"></i> Download CSV
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Data Ranking</h5>
                                        <p class="card-text">Export hasil perhitungan ranking sekolah dengan metode SAW.</p>
                                        <form method="post">
                                            <button type="submit" name="export_ranking" class="btn btn-primary btn-sm">
                                                <i class="bi bi-download me-1"></i> Download CSV
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <div class="card">
                                <div class="card-body bg-light">
                                    <h5 class="card-title">Export Data Lengkap</h5>
                                    <p class="card-text">Download semua data dalam satu file CSV (gabungan dari semua data)</p>
                                    <form method="post">
                                        <button type="submit" name="export_semua" class="btn btn-success">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Export Semua Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php require_once 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>