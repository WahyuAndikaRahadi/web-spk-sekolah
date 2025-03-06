<?php
session_start();
require_once '../config.php';
require_once 'navbar.php'; 

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil hasil ranking
$ranking = mysqli_query($koneksi, "
    SELECT ha.total_skor, ha.peringkat, s.* 
    FROM hasil_akhir ha
    JOIN sekolah s ON ha.id_sekolah = s.id_sekolah
    ORDER BY ha.total_skor DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Ranking Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Hasil Ranking Sekolah</h2>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Nama Sekolah</th>
                            <th>Akreditasi</th>
                            <th>Total Skor</th>
                            <th>Total Guru</th>
                            <th>Rata-rata UN</th>
                            <th>Biaya SPP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($ranking)) { ?>
                            <tr>
                                <td><?php echo $row['peringkat']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_sekolah']); ?></td>
                                <td><?php echo $row['akreditasi']; ?></td>
                                <td><?php echo number_format($row['total_skor'], 4); ?></td>
                                <td><?php echo $row['total_guru']; ?></td>
                                <td><?php echo number_format($row['rata_un'], 2); ?></td>
                                <td>Rp <?php echo number_format($row['biaya_spp'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
    <?php
 require_once 'footer.php';
 ?>
</body>
</html>