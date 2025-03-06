<?php
session_start();
require_once '../config.php';
require_once 'navbar.php'; 

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil ID sekolah dari parameter
$id_sekolah = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data sekolah
$sekolah = mysqli_query($koneksi, "SELECT * FROM sekolah WHERE id_sekolah = $id_sekolah");
$data_sekolah = mysqli_fetch_assoc($sekolah);

if (!$data_sekolah) {
    $_SESSION['pesan'] = "Sekolah tidak ditemukan!";
    header("Location: daftar_sekolah.php");
    exit();
}

// Proses update sekolah
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_sekolah = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $akreditasi = mysqli_real_escape_string($koneksi, $_POST['akreditasi']);
    $total_guru = intval($_POST['total_guru']);
    $rata_un = floatval($_POST['rata_un']);
    $biaya_spp = floatval($_POST['biaya_spp']);

    // Query update sekolah
    $query = "UPDATE sekolah SET 
              nama_sekolah = '$nama_sekolah', 
              alamat = '$alamat', 
              akreditasi = '$akreditasi', 
              total_guru = $total_guru, 
              rata_un = $rata_un, 
              biaya_spp = $biaya_spp 
              WHERE id_sekolah = $id_sekolah";
    
    if (mysqli_query($koneksi, $query)) {
        // Update penilaian untuk setiap kriteria
        $kriteria = mysqli_query($koneksi, "SELECT id_kriteria FROM kriteria");
        while ($k = mysqli_fetch_assoc($kriteria)) {
            $nilai = floatval($_POST['kriteria_' . $k['id_kriteria']]);
            mysqli_query($koneksi, "UPDATE penilaian 
                                    SET nilai = $nilai 
                                    WHERE id_sekolah = $id_sekolah 
                                    AND id_kriteria = {$k['id_kriteria']}");
        }
        
        $_SESSION['pesan'] = "Sekolah berhasil diupdate!";
        header("Location: daftar_sekolah.php");
        exit();
    } else {
        $error = "Gagal mengupdate sekolah: " . mysqli_error($koneksi);
    }
}

// Ambil daftar kriteria
$kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria");

// Ambil data penilaian
$penilaian = mysqli_query($koneksi, "SELECT * FROM penilaian WHERE id_sekolah = $id_sekolah");
$data_penilaian = [];
while ($p = mysqli_fetch_assoc($penilaian)) {
    $data_penilaian[$p['id_kriteria']] = $p['nilai'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Edit Sekolah</h2>
            </div>
            <div class="card-body">
                <?php if(isset($error)) { ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php } ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Sekolah</label>
                        <input type="text" name="nama_sekolah" class="form-control" 
                               value="<?php echo htmlspecialchars($data_sekolah['nama_sekolah']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" required><?php echo htmlspecialchars($data_sekolah['alamat']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Akreditasi</label>
                        <select name="akreditasi" class="form-control" required>
                            <option value="A" <?php echo $data_sekolah['akreditasi'] == 'A' ? 'selected' : ''; ?>>A</option>
                            <option value="B" <?php echo $data_sekolah['akreditasi'] == 'B' ? 'selected' : ''; ?>>B</option>
                            <option value="C" <?php echo $data_sekolah['akreditasi'] == 'C' ? 'selected' : ''; ?>>C</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Guru</label>
                        <input type="number" name="total_guru" class="form-control" 
                               value="<?php echo $data_sekolah['total_guru']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rata-rata UN</label>
                        <input type="number" step="0.01" name="rata_un" class="form-control" 
                               value="<?php echo $data_sekolah['rata_un']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Biaya SPP</label>
                        <input type="number" name="biaya_spp" class="form-control" 
                               value="<?php echo $data_sekolah['biaya_spp']; ?>" required>
                    </div>

                    <h4>Penilaian Kriteria</h4>
                    <?php 
                    // Reset pointer
                    mysqli_data_seek($kriteria, 0);
                    while($k = mysqli_fetch_assoc($kriteria)) { 
                    ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo $k['nama_kriteria']; ?></label>
                            <input type="number" step="0.01" 
                                   name="kriteria_<?php echo $k['id_kriteria']; ?>" 
                                   class="form-control" 
                                   value="<?php echo $data_penilaian[$k['id_kriteria']] ?? ''; ?>" 
                                   required>
                        </div>
                    <?php } ?>

                    <button type="submit" class="btn btn-primary">Update Sekolah</button>
                    <a href="daftar_sekolah.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
    <?php
 require_once 'footer.php';
 ?>
</body>
</html>