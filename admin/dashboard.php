<?php
session_start();
require_once '../config.php';
require_once 'navbar.php'; 

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fungsi Perhitungan SAW
function hitungSAW($koneksi) {
    // Ambil data kriteria
    $kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria");
    
    // Ambil data sekolah dan penilaian
    $sekolah = mysqli_query($koneksi, "SELECT * FROM sekolah");
    
    // Normalisasi
    while ($s = mysqli_fetch_assoc($sekolah)) {
        $total_skor = 0;
        
        // Hitung untuk setiap kriteria
        $kriteria_reset = mysqli_query($koneksi, "SELECT * FROM kriteria");
        while ($k = mysqli_fetch_assoc($kriteria_reset)) {
            $penilaian = mysqli_query($koneksi, 
                "SELECT nilai FROM penilaian 
                 WHERE id_sekolah = {$s['id_sekolah']} 
                 AND id_kriteria = {$k['id_kriteria']}"
            );
            $p = mysqli_fetch_assoc($penilaian);
            
            // Normalisasi berdasarkan benefit/cost
            if ($k['tipe'] == 'benefit') {
                $max_nilai = mysqli_fetch_assoc(
                    mysqli_query($koneksi, 
                        "SELECT MAX(nilai) as max_nilai FROM penilaian 
                         WHERE id_kriteria = {$k['id_kriteria']}")
                )['max_nilai'];
                $normalisasi = $p['nilai'] / $max_nilai;
            } else {
                $min_nilai = mysqli_fetch_assoc(
                    mysqli_query($koneksi, 
                        "SELECT MIN(nilai) as min_nilai FROM penilaian 
                         WHERE id_kriteria = {$k['id_kriteria']}")
                )['min_nilai'];
                $normalisasi = $min_nilai / $p['nilai'];
            }
            
            $total_skor += $normalisasi * $k['bobot'];
        }
        
        // Simpan hasil
        mysqli_query($koneksi, 
            "REPLACE INTO hasil_akhir (id_sekolah, total_skor) 
             VALUES ({$s['id_sekolah']}, $total_skor)"
        );
    }
    
    // Beri peringkat menggunakan cara yang lebih kompatibel
    mysqli_query($koneksi, "DROP TEMPORARY TABLE IF EXISTS ranked_results");
    mysqli_query($koneksi, "
        CREATE TEMPORARY TABLE ranked_results AS (
            SELECT 
                id_sekolah, 
                total_skor, 
                DENSE_RANK() OVER (ORDER BY total_skor DESC) as peringkat
            FROM hasil_akhir
        )
    ");
    
    mysqli_query($koneksi, "
        UPDATE hasil_akhir ha
        JOIN ranked_results rr ON ha.id_sekolah = rr.id_sekolah
        SET ha.peringkat = rr.peringkat
    ");
}

// Proses perhitungan
if (isset($_POST['hitung'])) {
    hitungSAW($koneksi);
    $_SESSION['pesan'] = "Perhitungan ranking berhasil dilakukan!";
    header("Location: hasil_ranking.php");
    exit();
}

// Hitung statistik untuk dashboard
$total_sekolah = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM sekolah"))['total'];
$total_kriteria = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kriteria"))['total'];
$total_users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | SPK Pemilihan SMA Swasta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
        }
        
        .bg-gradient-primary {
            background-color: var(--primary-color);
            background-image: linear-gradient(180deg, var(--primary-color) 10%, #224abe 100%);
            color: white;
        }
        
        .bg-gradient-success {
            background-color: var(--secondary-color);
            background-image: linear-gradient(180deg, var(--secondary-color) 10%, #13855c 100%);
            color: white;
        }
        
        .bg-gradient-info {
            background-color: var(--info-color);
            background-image: linear-gradient(180deg, var(--info-color) 10%, #2c9faf 100%);
            color: white;
        }
        
        .bg-gradient-warning {
            background-color: var(--warning-color);
            background-image: linear-gradient(180deg, var(--warning-color) 10%, #dda20a 100%);
            color: white;
        }
        
        .icon-circle {
            height: 3rem;
            width: 3rem;
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .dashboard-card {
            transition: transform 0.2s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .feature-card .card-body {
            min-height: 180px;
        }
        
        .footer {
            padding: 1.5rem;
            color: #858796;
            background-color: white;
            border-top: 1px solid #e3e6f0;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-gray-800"><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</h1>
        
        <?php if(isset($_SESSION['pesan'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <!-- Statistik Dashboard -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sekolah</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_sekolah; ?></div>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-gradient-primary">
                                    <i class="bi bi-building text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-success h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Kriteria</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_kriteria; ?></div>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-gradient-success">
                                    <i class="bi bi-list-check text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-info h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Metode</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">SAW</div>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-gradient-info">
                                    <i class="bi bi-calculator text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-warning h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Pengguna</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-gradient-warning">
                                    <i class="bi bi-people text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Menu -->
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card feature-card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-building me-2"></i>Manajemen Sekolah</h6>
                    </div>
                    <div class="card-body">
                        <p>Kelola data sekolah SMA swasta yang akan dinilai dalam sistem pendukung keputusan.</p>
                        <div class="d-flex justify-content-between">
                            <a href="tambah_sekolah.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Sekolah
                            </a>
                            <a href="daftar_sekolah.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-list me-1"></i> Daftar Sekolah
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card feature-card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-list-check me-2"></i>Manajemen Kriteria</h6>
                    </div>
                    <div class="card-body">
                        <p>Kelola kriteria dan bobot yang digunakan dalam perhitungan metode SAW.</p>
                        <div class="d-flex justify-content-between">
                            <a href="tambah_kriteria.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Kriteria
                            </a>
                            <a href="daftar_kriteria.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-list me-1"></i> Daftar Kriteria
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card feature-card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-calculator me-2"></i>Perhitungan SAW</h6>
                    </div>
                    <div class="card-body">
                        <p>Lakukan perhitungan ranking sekolah menggunakan metode Simple Additive Weighting (SAW).</p>
                        <form method="POST">
                            <button type="submit" name="hitung" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-right-circle me-1"></i> Hitung Ranking
                            </button>
                            <a href="hasil_ranking.php" class="btn btn-info btn-sm ms-2">
                                <i class="bi bi-bar-chart me-1"></i> Lihat Hasil Ranking
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Features -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-info-circle me-2"></i>Tentang Metode SAW</h6>
                    </div>
                    <div class="card-body">
                        <p>Metode Simple Additive Weighting (SAW) adalah metode penjumlahan terbobot dari rating kinerja pada setiap alternatif dari semua atribut.</p>
                        <p>Langkah-langkah metode SAW:</p>
                        <ol>
                            <li>Menentukan kriteria dan alternatif</li>
                            <li>Normalisasi matriks keputusan</li>
                            <li>Perhitungan nilai preferensi untuk setiap alternatif</li>
                            <li>Perankingan</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-tools me-2"></i>Alat Admin Lainnya</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="kelola_pengguna.php" class="btn btn-secondary btn-block w-100">
                                    <i class="bi bi-people me-2"></i> Kelola Pengguna
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="export_data.php" class="btn btn-secondary btn-block w-100">
                                    <i class="bi bi-file-excel me-2"></i> Export Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
 require_once 'footer.php';
 ?>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>