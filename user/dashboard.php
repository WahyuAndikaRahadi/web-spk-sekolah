<?php
session_start();
require_once '../config.php';
require_once 'navbar.php'; 

// Cek apakah user sudah login dan role user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

// Hitung statistik untuk dashboard
$total_sekolah = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM sekolah"))['total'];
$total_kriteria = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kriteria"))['total'];

// Ambil data hasil ranking teratas
$top_schools = mysqli_query($koneksi, "
    SELECT s.nama_sekolah, ha.total_skor, ha.peringkat 
    FROM hasil_akhir ha
    JOIN sekolah s ON ha.id_sekolah = s.id_sekolah
    ORDER BY ha.peringkat ASC
    LIMIT 3
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User | SPK Pemilihan SMA Swasta</title>
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
        
        .rank-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .rank-1 {
            background-color: gold;
            color: #333;
        }
        
        .rank-2 {
            background-color: silver;
            color: #333;
        }
        
        .rank-3 {
            background-color: #cd7f32; /* bronze */
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-gray-800"><i class="bi bi-speedometer2 me-2"></i>Dashboard Pengguna</h1>
        
        <?php if(isset($_SESSION['pesan'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <!-- Statistik Dashboard -->
        <div class="row">
            <div class="col-xl-4 col-md-6 mb-4">
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
            
            <div class="col-xl-4 col-md-6 mb-4">
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
            
            <div class="col-xl-4 col-md-6 mb-4">
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
        </div>
        
        <!-- Main Content -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-trophy me-2"></i>Sekolah Terbaik</h6>
                        <a href="lihat_ranking.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-list-ol me-1"></i> Lihat Semua Ranking
                        </a>
                    </div>
                    <div class="card-body">
                        <?php 
                        if (mysqli_num_rows($top_schools) > 0) {
                            $i = 1;
                            while ($school = mysqli_fetch_assoc($top_schools)) { 
                        ?>
                            <div class="d-flex align-items-center mb-3 p-3 border-bottom">
                                <div class="rank-badge rank-<?php echo $i; ?>"><?php echo $i; ?></div>
                                <div>
                                    <h5 class="mb-0"><?php echo $school['nama_sekolah']; ?></h5>
                                    <div class="small text-muted">
                                        Skor: <?php echo number_format($school['total_skor'], 4); ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                                $i++;
                            }
                        } else { 
                        ?>
                            <div class="alert alert-info">
                                Belum ada perhitungan ranking yang dilakukan. Silahkan tunggu admin melakukan perhitungan.
                            </div>
                        <?php } ?>
                        
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-info-circle me-2"></i>Tentang Metode SAW</h6>
                    </div>
                    <div class="card-body">
                        <p>Metode Simple Additive Weighting (SAW) adalah metode penjumlahan terbobot dari rating kinerja pada setiap alternatif dari semua atribut.</p>
                        <p>Metode ini digunakan untuk memberikan rekomendasi sekolah terbaik berdasarkan kriteria-kriteria yang telah ditentukan.</p>
                        <a href="lihat_ranking.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-bar-chart me-1"></i> Lihat Hasil Ranking
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Guide -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-book me-2"></i>Panduan Pengguna</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5><i class="bi bi-1-circle me-2 text-primary"></i>Lihat Ranking</h5>
                                    <p>Lihat hasil perankingan sekolah SMA swasta berdasarkan kriteria yang telah ditentukan.</p>
                                </div>
                                <div class="mb-4">
                                    <h5><i class="bi bi-2-circle me-2 text-primary"></i>Lihat Detail Sekolah</h5>
                                    <p>Klik pada nama sekolah untuk melihat detail lengkap informasi sekolah.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5><i class="bi bi-3-circle me-2 text-primary"></i>Bandingkan Sekolah</h5>
                                    <p>Gunakan fitur perbandingan untuk melihat perbedaan antar sekolah secara side-by-side.</p>
                                </div>
                                <div class="mb-4">
                                    <h5><i class="bi bi-4-circle me-2 text-primary"></i>Berikan Feedback</h5>
                                    <p>Berikan saran dan masukan untuk pengembangan sistem lewat halaman kontak.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>