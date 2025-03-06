<?php
session_start();
require_once '../config.php';
require_once 'navbar.php'; 

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil kriteria
$kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria");
$kriteria_data = [];
while($k = mysqli_fetch_assoc($kriteria)) {
    $kriteria_data[] = $k;
}

// Ambil hasil ranking
$ranking = mysqli_query($koneksi, "
    SELECT ha.total_skor, ha.peringkat, s.* 
    FROM hasil_akhir ha
    JOIN sekolah s ON ha.id_sekolah = s.id_sekolah
    ORDER BY ha.total_skor DESC
");

// Hitung statistik
$total_sekolah = mysqli_num_rows($ranking);
mysqli_data_seek($ranking, 0); // Reset pointer
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Sekolah Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --text-primary: #5a5c69;
            --text-secondary: #858796;
        }
        
        body {
            background-color: #f8f9fc;
            color: var(--text-primary);
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            color: #4e73df;
            font-weight: 700;
            padding: 1rem 1.25rem;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }
        
        .table-responsive {
            border-radius: 10px;
        }
        
        .table th {
            font-weight: 600;
            background-color: #f8f9fc;
            border-top: none;
        }
        
       
        
        .badge-criteria {
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .btn-info {
            background-color: #36b9cc;
            border-color: #36b9cc;
            color: white;
        }
        
        .btn-info:hover {
            background-color: #2a96a5;
            border-color: #258391;
            color: white;
        }
        
        .top-school {
            background-color: #f6c23e;
            color: white;
        }
        
        .stat-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 1.5rem;
            border-left: 4px solid;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .stat-card-primary {
            border-left-color: #4e73df;
        }
        
        .stat-card-success {
            border-left-color: #1cc88a;
        }
        
        .stat-card .stat-card-icon {
            color: #dddfeb;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .stat-card-title {
            color: var(--text-secondary);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .stat-card .stat-card-value {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .criterion-card {
            transition: transform 0.2s;
        }
        
        .criterion-card:hover {
            transform: translateY(-5px);
        }
        
        .criterion-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .rank-badge {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            background-color: #4e73df;
            color: white;
            font-weight: 700;
        }
        
        .rank-1 {
            background-color: #f6c23e; /* Gold */
        }
        
        .rank-2 {
            background-color: #858796; /* Silver */
        }
        
        .rank-3 {
            background-color: #e74a3b; /* Bronze */
        }
        
        .modal-header {
            background-color: #4e73df;
            color: white;
            border-bottom: none;
        }
        
        .modal-footer {
            border-top: none;
        }
        
        .progress {
            height: 0.5rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-gray-500">
            <i class="fas fa-trophy me-2"></i> Ranking Sekolah
        </h1>
        
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-card-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="stat-card-title">Total Sekolah Dinilai</div>
                    <div class="stat-card-value"><?php echo $total_sekolah; ?> Sekolah</div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="stat-card stat-card-success">
                    <div class="stat-card-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-card-title">Metode Penilaian</div>
                    <div class="stat-card-value">Simple Additive Weighting (SAW)</div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 mb-4">
                <div class="stat-card" style="border-left-color: #36b9cc;">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-card-title">Tanggal Penilaian</div>
                    <div class="stat-card-value"><?php echo date("d F Y"); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Content Row -->
        <div class="row">
            <!-- Criteria Card -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-list-check me-2"></i>
                        <span>Kriteria Penilaian</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php 
                            $icons = [
                                'rata_un' => 'fas fa-graduation-cap',
                                'total_guru' => 'fas fa-chalkboard-teacher',
                                'biaya_spp' => 'fas fa-money-bill-wave',
                                'akreditasi' => 'fas fa-award',
                                'default' => 'fas fa-check-circle'
                            ];
                            
                            foreach($kriteria_data as $k) { 
                                $icon_key = strtolower(str_replace(' ', '_', $k['nama_kriteria']));
                                $icon = isset($icons[$icon_key]) ? $icons[$icon_key] : $icons['default'];
                                $badge_color = $k['tipe'] == 'benefit' ? 'primary' : 'danger';
                            ?>
                                <div class="list-group-item criterion-card d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="<?php echo $icon; ?> criterion-icon text-<?php echo $badge_color; ?>"></i>
                                        <span class="fw-semibold"><?php echo $k['nama_kriteria']; ?></span>
                                    </div>
                                    <span class="badge bg-<?php echo $badge_color; ?> badge-criteria">
                                        <i class="fas <?php echo $k['tipe'] == 'benefit' ? 'fa-arrow-up' : 'fa-arrow-down'; ?> me-1"></i>
                                        <?php 
                                        echo $k['tipe'] == 'benefit' ? 'Semakin Tinggi Semakin Baik' : 'Semakin Rendah Semakin Baik'; 
                                        ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <!-- Info Card -->
                <div class="card mt-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <span>Informasi Sistem</span>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-bullseye text-primary me-2"></i> <strong>Tujuan:</strong> Membantu calon siswa dan orang tua dalam memilih sekolah terbaik.</p>
                        <p><i class="fas fa-chart-line text-success me-2"></i> <strong>Metode SAW:</strong> Memperhitungkan berbagai kriteria untuk mendapatkan hasil yang komprehensif.</p>
                        <p><i class="fas fa-exclamation-triangle text-warning me-2"></i> <strong>Catatan:</strong> Hasil perankingan ini hanya sebagai referensi, keputusan akhir tetap di tangan Anda.</p>
                    </div>
                </div>
            </div>
            
            <!-- Ranking Table -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fas fa-ranking-star me-2"></i>
                            <span>Daftar Peringkat Sekolah</span>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" id="printBtn">
                                <i class="fas fa-print me-1"></i> Cetak
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="rankingTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">Peringkat</th>
                                        <th>Nama Sekolah</th>
                                        <th class="text-center">Akreditasi</th>
                                        <th class="text-center">Skor</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $ranking_reset = mysqli_query($koneksi, "
                                        SELECT ha.total_skor, ha.peringkat, s.* 
                                        FROM hasil_akhir ha
                                        JOIN sekolah s ON ha.id_sekolah = s.id_sekolah
                                        ORDER BY ha.total_skor DESC
                                    ");
                                    
                                    while($row = mysqli_fetch_assoc($ranking_reset)) { 
                                        $rank_class = '';
                                        if($row['peringkat'] == 1) $rank_class = 'rank-1';
                                        else if($row['peringkat'] == 2) $rank_class = 'rank-2';
                                        else if($row['peringkat'] == 3) $rank_class = 'rank-3';
                                    ?>
                                        <tr <?php echo ($row['peringkat'] <= 3) ? 'class="fw-bold"' : ''; ?>>
                                            <td class="text-center">
                                                <span class="rank-badge <?php echo $rank_class; ?>">
                                                    <?php echo $row['peringkat']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($row['nama_sekolah']); ?>
                                                <?php if($row['peringkat'] == 1): ?>
                                                <span class="badge bg-warning text-dark ms-1"><i class="fas fa-crown"></i> Top</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                $akreditasi = $row['akreditasi'];
                                                $badge_color = '';
                                                
                                                if($akreditasi == 'A') $badge_color = 'success';
                                                else if($akreditasi == 'B') $badge_color = 'primary';
                                                else if($akreditasi == 'C') $badge_color = 'warning';
                                                else $badge_color = 'secondary';
                                                ?>
                                                <span class="badge rounded-pill bg-<?php echo $badge_color; ?>">
                                                    <?php echo $akreditasi; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold"><?php echo number_format($row['total_skor'], 4); ?></div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-primary" role="progressbar" 
                                                         style="width: <?php echo ($row['total_skor'] * 100); ?>%" 
                                                         aria-valuenow="<?php echo $row['total_skor']; ?>" 
                                                         aria-valuemin="0" aria-valuemax="1">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal<?php echo $row['id_sekolah']; ?>">
                                                    <i class="fas fa-eye me-1"></i> Detail
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal Detail Sekolah -->
                                        <div class="modal fade" id="detailModal<?php echo $row['id_sekolah']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-school me-2"></i>
                                                            <?php echo htmlspecialchars($row['nama_sekolah']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="border-bottom pb-2 mb-3">Informasi Umum</h6>
                                                                <table class="table table-borderless">
                                                                    <tr>
                                                                        <th><i class="fas fa-map-marker-alt text-danger me-2"></i> Alamat</th>
                                                                        <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th><i class="fas fa-award text-primary me-2"></i> Akreditasi</th>
                                                                        <td>
                                                                            <span class="badge bg-<?php echo $badge_color; ?> px-3 py-2">
                                                                                <?php echo $row['akreditasi']; ?>
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th><i class="fas fa-trophy text-warning me-2"></i> Peringkat</th>
                                                                        <td>#<?php echo $row['peringkat']; ?> dari <?php echo $total_sekolah; ?> sekolah</td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="border-bottom pb-2 mb-3">Kriteria Penilaian</h6>
                                                                <table class="table table-borderless">
                                                                    <tr>
                                                                        <th><i class="fas fa-chalkboard-teacher text-info me-2"></i> Total Guru</th>
                                                                        <td><?php echo $row['total_guru']; ?> orang</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th><i class="fas fa-graduation-cap text-success me-2"></i> Rata-rata UN</th>
                                                                        <td><?php echo number_format($row['rata_un'], 2); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th><i class="fas fa-money-bill-wave text-danger me-2"></i> Biaya SPP</th>
                                                                        <td>Rp <?php echo number_format($row['biaya_spp'], 0, ',', '.'); ?>/bulan</td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-12">
                                                                <h6 class="border-bottom pb-2 mb-3">Perbandingan Skor</h6>
                                                                <div class="progress mb-3" style="height: 20px;">
                                                                    <div class="progress-bar bg-primary" role="progressbar" 
                                                                        style="width: <?php echo ($row['total_skor'] * 100); ?>%" 
                                                                        aria-valuenow="<?php echo $row['total_skor']; ?>" 
                                                                        aria-valuemin="0" aria-valuemax="1">
                                                                        <?php echo number_format($row['total_skor'], 4); ?>
                                                                    </div>
                                                                </div>
                                                                <p class="text-muted mt-2"><small><i class="fas fa-info-circle me-1"></i> Skor dihitung menggunakan metode Simple Additive Weighting (SAW) berdasarkan kriteria yang telah ditentukan.</small></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">

        <?php require_once 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk mencetak tabel ranking
        document.getElementById('printBtn').addEventListener('click', function() {
            window.print();
        });
        
        // Highlight row when hovering over details button
        document.querySelectorAll('.btn-info').forEach(function(btn) {
            btn.addEventListener('mouseover', function() {
                this.closest('tr').classList.add('table-active');
            });
            
            btn.addEventListener('mouseout', function() {
                this.closest('tr').classList.remove('table-active');
            });
        });
    </script>
</body>
</html>