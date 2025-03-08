<?php 
session_start(); 
require_once '../config.php'; 
require_once 'navbar.php';   

// Cek apakah user sudah login dan role admin 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {     
    header("Location: ../index.php");     
    exit(); 
}  

// Proses hapus sekolah 
if (isset($_GET['hapus'])) {     
    $id_sekolah = intval($_GET['hapus']);          
    
    // Hapus penilaian terkait     
    mysqli_query($koneksi, "DELETE FROM penilaian WHERE id_sekolah = $id_sekolah");          
    
    // Hapus dari hasil akhir     
    mysqli_query($koneksi, "DELETE FROM hasil_akhir WHERE id_sekolah = $id_sekolah");          
    
    // Hapus sekolah     
    mysqli_query($koneksi, "DELETE FROM sekolah WHERE id_sekolah = $id_sekolah");          
    
    $_SESSION['pesan'] = "Sekolah berhasil dihapus!";     
    header("Location: daftar_sekolah.php");     
    exit(); 
}  

// Ambil daftar kriteria untuk ditampilkan di kolom tabel
$kriteria_query = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
$kriteria_list = [];
while ($k = mysqli_fetch_assoc($kriteria_query)) {
    $kriteria_list[] = $k;
}

// Ambil daftar sekolah
$sekolah_query = mysqli_query($koneksi, "SELECT * FROM sekolah");

// Ambil semua data penilaian untuk efisiensi query
$penilaian_query = mysqli_query($koneksi, "SELECT id_sekolah, id_kriteria, nilai FROM penilaian");
$penilaian_data = [];

// Susun data penilaian per sekolah
while ($p = mysqli_fetch_assoc($penilaian_query)) {
    $penilaian_data[$p['id_sekolah']][$p['id_kriteria']] = $p['nilai'];
}
?>  

<!DOCTYPE html> 
<html lang="id"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Sekolah</title>     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #f8f9fa;
        }
        .nav-tabs {
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .nav-tabs .nav-link {
            margin-bottom: -1px;
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .tab-content {
            padding: 15px 0;
        }
        .kriteria-card {
            height: 100%;
            transition: all 0.3s;
        }
        .kriteria-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .nilai-kriteria {
            font-size: 1.75rem;
            font-weight: 500;
        }
        .card-title-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        @media (max-width: 576px) {
            .card-title-action {
                flex-direction: column;
                align-items: flex-start;
            }
            .action-buttons {
                margin-top: 10px;
            }
        }
    </style>
</head> 
<body>     
    <div class="container mt-5 mb-5">         
        <div class="card">             
            <div class="card-header d-flex justify-content-between align-items-center">                 
                <h2>Daftar Sekolah</h2>                 
                <a href="tambah_sekolah.php" class="btn btn-primary">Tambah Sekolah</a>             
            </div>             
            <div class="card-body">                 
                <?php if(isset($_SESSION['pesan'])) { ?>                     
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>                 
                <?php } ?>
                
                <!-- Tab View -->
                <?php
                $no = 1;
                while($row = mysqli_fetch_assoc($sekolah_query)) {
                ?>
                <div class="card">
                    <div class="card-header py-3">
                        <div class="card-title-action">
                            <h5 class="mb-0"><?php echo $no . '. ' . htmlspecialchars($row['nama_sekolah']); ?></h5>
                            <div class="action-buttons">
                                <a href="edit_sekolah.php?id=<?php echo $row['id_sekolah']; ?>" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>                                     
                                <a href="daftar_sekolah.php?hapus=<?php echo $row['id_sekolah']; ?>"                                         
                                   class="btn btn-danger"                                         
                                   onclick="return confirm('Yakin ingin menghapus sekolah ini?');">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab-<?php echo $row['id_sekolah']; ?>" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="home-tab-<?php echo $row['id_sekolah']; ?>" data-bs-toggle="tab" 
                                        data-bs-target="#home-<?php echo $row['id_sekolah']; ?>" type="button" role="tab" 
                                        aria-controls="home" aria-selected="true">Data Umum</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab-<?php echo $row['id_sekolah']; ?>" data-bs-toggle="tab" 
                                        data-bs-target="#profile-<?php echo $row['id_sekolah']; ?>" type="button" role="tab" 
                                        aria-controls="profile" aria-selected="false">Kriteria Penilaian</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent-<?php echo $row['id_sekolah']; ?>">
                            <!-- Tab Data Umum -->
                            <div class="tab-pane fade show active" id="home-<?php echo $row['id_sekolah']; ?>" role="tabpanel" 
                                 aria-labelledby="home-tab-<?php echo $row['id_sekolah']; ?>">
                                <div class="row mt-3">
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-subtitle mb-2 text-muted">Akreditasi</h6>
                                                <h4 class="card-title"><?php echo $row['akreditasi']; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-subtitle mb-2 text-muted">Total Guru</h6>
                                                <h4 class="card-title"><?php echo $row['total_guru']; ?> orang</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-subtitle mb-2 text-muted">Rata-rata UN</h6>
                                                <h4 class="card-title"><?php echo number_format($row['rata_un'], 2); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-subtitle mb-2 text-muted">Biaya SPP</h6>
                                                <h4 class="card-title">Rp <?php echo number_format($row['biaya_spp'], 0, ',', '.'); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab Kriteria Penilaian -->
                            <div class="tab-pane fade" id="profile-<?php echo $row['id_sekolah']; ?>" role="tabpanel" 
                                 aria-labelledby="profile-tab-<?php echo $row['id_sekolah']; ?>">
                                <div class="row mt-3">
                                    <?php foreach ($kriteria_list as $k): ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                        <div class="card kriteria-card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-subtitle mb-3 text-muted"><?php echo htmlspecialchars($k['nama_kriteria']); ?></h6>
                                                <div class="nilai-kriteria">
                                                    <?php 
                                                    if (isset($penilaian_data[$row['id_sekolah']][$k['id_kriteria']])) {
                                                        $nilai = $penilaian_data[$row['id_sekolah']][$k['id_kriteria']];
                                                        echo number_format($nilai, 2);
                                                        
                                                        // Warna berdasarkan nilai (misal: hijau untuk nilai tinggi)
                                                        $warna = 'text-primary';
                                                        if ($nilai >= 80) {
                                                            $warna = 'text-success';
                                                        } elseif ($nilai >= 50) {
                                                            $warna = 'text-info';
                                                        } elseif ($nilai < 50) {
                                                            $warna = 'text-danger';
                                                        }
                                                        echo '</div>';
                                                        echo '<div class="mt-2 '.$warna.'">';
                                                        if ($nilai >= 80) {
                                                            echo 'Sangat Baik';
                                                        } elseif ($nilai >= 60) {
                                                            echo 'Baik';
                                                        } elseif ($nilai >= 40) {
                                                            echo 'Cukup';
                                                        } else {
                                                            echo 'Kurang';
                                                        }
                                                    } else {
                                                        echo '0.00';
                                                        echo '</div>';
                                                        echo '<div class="mt-2 text-secondary">Belum dinilai</div>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $no++; } ?>
            </div>         
        </div>     
    </div>     
    
    <?php require_once 'footer.php'; ?> 
</body> 
</html>