<?php
session_start();
require_once '../config.php';
require_once 'navbar.php'; 

// Cek apakah user sudah  dan role admin
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

// Ambil daftar sekolah
$sekolah = mysqli_query($koneksi, "SELECT * FROM sekolah");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>Daftar Sekolah</h2>
                <a href="tambah_sekolah.php" class="btn btn-primary">Tambah Sekolah</a>
            </div>
            <div class="card-body">
                <?php if(isset($_SESSION['pesan'])) { ?>
                    <div class="alert alert-success"><?php echo $_SESSION['pesan']; unset($_SESSION['pesan']); ?></div>
                <?php } ?>
                
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Sekolah</th>
                            <th>Akreditasi</th>
                            <th>Total Guru</th>
                            <th>Rata-rata UN</th>
                            <th>Biaya SPP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($sekolah)) { 
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_sekolah']); ?></td>
                                <td><?php echo $row['akreditasi']; ?></td>
                                <td><?php echo $row['total_guru']; ?></td>
                                <td><?php echo number_format($row['rata_un'], 2); ?></td>
                                <td>Rp <?php echo number_format($row['biaya_spp'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="edit_sekolah.php?id=<?php echo $row['id_sekolah']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="daftar_sekolah.php?hapus=<?php echo $row['id_sekolah']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Yakin ingin menghapus sekolah ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
 require_once 'footer.php';
 ?>
</body>
</html>