<?php
session_start();
require_once '../config.php';
require_once 'navbar.php'; 

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil ID kriteria dari parameter
$id_kriteria = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data kriteria
$kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria WHERE id_kriteria = $id_kriteria");
$data_kriteria = mysqli_fetch_assoc($kriteria);

if (!$data_kriteria) {
    $_SESSION['pesan'] = "Kriteria tidak ditemukan!";
    header("Location: daftar_kriteria.php");
    exit();
}

// Proses update kriteria
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kriteria = mysqli_real_escape_string($koneksi, $_POST['nama_kriteria']);
    $bobot = floatval($_POST['bobot']);
    $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe']);

    // Query update kriteria
    $query = "UPDATE kriteria SET 
              nama_kriteria = '$nama_kriteria', 
              bobot = $bobot, 
              tipe = '$tipe' 
              WHERE id_kriteria = $id_kriteria";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan'] = "Kriteria berhasil diupdate!";
        header("Location: daftar_kriteria.php");
        exit();
    } else {
        $error = "Gagal mengupdate kriteria: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kriteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Edit Kriteria</h2>
            </div>
            <div class="card-body">
                <?php if(isset($error)) { ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php } ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Kriteria</label>
                        <input type="text" name="nama_kriteria" class="form-control" 
                               value="<?php echo htmlspecialchars($data_kriteria['nama_kriteria']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bobot</label>
                        <input type="number" step="0.01" name="bobot" class="form-control" 
                               value="<?php echo $data_kriteria['bobot']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe</label>
                        <select name="tipe" class="form-control" required>
                            <option value="benefit" <?php echo $data_kriteria['tipe'] == 'benefit' ? 'selected' : ''; ?>>Benefit</option>
                            <option value="cost" <?php echo $data_kriteria['tipe'] == 'cost' ? 'selected' : ''; ?>>Cost</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Kriteria</button>
                    <a href="daftar_kriteria.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
    <?php
    require_once 'footer.php';
    ?>
</body>
</html>