<?php 
session_start(); 
require_once '../config.php'; 
require_once 'navbar.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Proses hapus kriteria
if (isset($_GET['hapus'])) {
    $id_kriteria = intval($_GET['hapus']);
    
    // Hapus penilaian terkait
    mysqli_query($koneksi, "DELETE FROM penilaian WHERE id_kriteria = $id_kriteria");
    
    
    // Hapus kriteria
    mysqli_query($koneksi, "DELETE FROM kriteria WHERE id_kriteria = $id_kriteria");
    
    $_SESSION['pesan'] = "Kriteria berhasil dihapus!";
    header("Location: daftar_kriteria.php");
    exit();
}

// Ambil daftar kriteria
$kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kriteria</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>Daftar Kriteria</h2>
                <a href="tambah_kriteria.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Kriteria
                </a>
            </div>
            <div class="card-body">
                <?php if(isset($_SESSION['pesan'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Kriteria</th>
                                <th>Bobot</th>
                                <th>Tipe</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($kriteria) > 0) {
                                while($row = mysqli_fetch_assoc($kriteria)) {
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_kriteria']); ?></td>
                                    <td><?php echo $row['bobot']; ?></td>
                                    <td>
                                        <?php if($row['tipe'] == 'benefit') { ?>
                                            <span class="badge bg-success">Benefit</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">Cost</span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit_kriteria.php?id=<?php echo $row['id_kriteria']; ?>" class="btn btn-warning btn-sm me-1">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="daftar_kriteria.php?hapus=<?php echo $row['id_kriteria']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus kriteria ini?');">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Tidak ada data kriteria</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
 require_once 'footer.php';
 ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>