<?php
session_start();
require_once '../config.php';
require_once 'navbar.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Proses tambah sekolah
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_sekolah = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $akreditasi = mysqli_real_escape_string($koneksi, $_POST['akreditasi']);
    $total_guru = intval($_POST['total_guru']);
    $rata_un = floatval($_POST['rata_un']);
    $biaya_spp = floatval($_POST['biaya_spp']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama_sekolah)) {
        $errors[] = "Nama sekolah tidak boleh kosong";
    }
    
    if (empty($alamat)) {
        $errors[] = "Alamat tidak boleh kosong";
    }
    
    if ($total_guru <= 0) {
        $errors[] = "Total guru harus bernilai positif";
    }
    
    if ($rata_un < 0 || $rata_un > 10) {
        $errors[] = "Nilai rata-rata UN harus antara 0 dan 10";
    }
    
    if ($biaya_spp < 0) {
        $errors[] = "Biaya SPP tidak boleh negatif";
    }
    
    // Cek apakah ada errors
    if (empty($errors)) {
        // Query tambah sekolah
        $query = "INSERT INTO sekolah (nama_sekolah, alamat, akreditasi, total_guru, rata_un, biaya_spp) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssidi", $nama_sekolah, $alamat, $akreditasi, $total_guru, $rata_un, $biaya_spp);
        
        if (mysqli_stmt_execute($stmt)) {
            $id_sekolah = mysqli_insert_id($koneksi);
            
            // Tambah penilaian untuk setiap kriteria
            $kriteria = mysqli_query($koneksi, "SELECT id_kriteria FROM kriteria");
            while ($k = mysqli_fetch_assoc($kriteria)) {
                $id_kriteria = $k['id_kriteria'];
                $nilai = floatval($_POST['kriteria_' . $id_kriteria]);
                
                $query_nilai = "INSERT INTO penilaian (id_sekolah, id_kriteria, nilai) VALUES (?, ?, ?)";
                $stmt_nilai = mysqli_prepare($koneksi, $query_nilai);
                mysqli_stmt_bind_param($stmt_nilai, "iid", $id_sekolah, $id_kriteria, $nilai);
                mysqli_stmt_execute($stmt_nilai);
            }
            
            $_SESSION['pesan'] = "Sekolah berhasil ditambahkan!";
            header("Location: daftar_sekolah.php");
            exit();
        } else {
            $errors[] = "Gagal menambahkan sekolah: " . mysqli_error($koneksi);
        }
    }
}

// Ambil daftar kriteria
$kriteria_query = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Sekolah | SPK Pemilihan SMA Swasta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .card-header h6 {
            font-weight: 700;
            color: #4e73df;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        
        .btn-secondary {
            background-color: #858796;
            border-color: #858796;
        }
        
        .btn-secondary:hover {
            background-color: #717384;
            border-color: #717384;
        }
        
        .footer {
            padding: 1.5rem;
            color: #858796;
            background-color: white;
            border-top: 1px solid #e3e6f0;
            margin-top: 3rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-plus-circle me-2"></i>Tambah Sekolah</h1>
            <a href="daftar_sekolah.php" class="btn btn-sm btn-secondary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Sekolah
            </a>
        </div>
        
        <?php if(isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Error!</strong>
                <ul class="mb-0">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold"><i class="bi bi-building me-2"></i>Form Tambah Sekolah</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="sekolah-form">
                            <h5 class="border-bottom pb-2 mb-3">Informasi Dasar Sekolah</h5>
                            
                            <div class="mb-3">
                                <label for="nama_sekolah" class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_sekolah" name="nama_sekolah" required 
                                    value="<?php echo isset($_POST['nama_sekolah']) ? htmlspecialchars($_POST['nama_sekolah']) : ''; ?>" 
                                    placeholder="Masukkan nama sekolah">
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required 
                                    placeholder="Masukkan alamat lengkap sekolah"><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="akreditasi" class="form-label">Akreditasi <span class="text-danger">*</span></label>
                                    <select class="form-select" id="akreditasi" name="akreditasi" required>
                                        <option value="" disabled <?php echo !isset($_POST['akreditasi']) ? 'selected' : ''; ?>>-- Pilih Akreditasi --</option>
                                        <option value="A" <?php echo (isset($_POST['akreditasi']) && $_POST['akreditasi'] == 'A') ? 'selected' : ''; ?>>A</option>
                                        <option value="B" <?php echo (isset($_POST['akreditasi']) && $_POST['akreditasi'] == 'B') ? 'selected' : ''; ?>>B</option>
                                        <option value="C" <?php echo (isset($_POST['akreditasi']) && $_POST['akreditasi'] == 'C') ? 'selected' : ''; ?>>C</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="total_guru" class="form-label">Total Guru <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="total_guru" name="total_guru" min="1" required 
                                        value="<?php echo isset($_POST['total_guru']) ? htmlspecialchars($_POST['total_guru']) : ''; ?>" 
                                        placeholder="Jumlah guru">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="rata_un" class="form-label">Rata-rata UN <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="rata_un" name="rata_un" min="0" max="10" step="0.01" required 
                                        value="<?php echo isset($_POST['rata_un']) ? htmlspecialchars($_POST['rata_un']) : ''; ?>" 
                                        placeholder="Nilai 0-10">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="biaya_spp" class="form-label">Biaya SPP (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="biaya_spp" name="biaya_spp" min="0" required 
                                        value="<?php echo isset($_POST['biaya_spp']) ? htmlspecialchars($_POST['biaya_spp']) : ''; ?>" 
                                        placeholder="Contoh: 500000">
                                </div>
                            </div>
                            
                            <h5 class="border-bottom pb-2 mb-3 mt-4">Penilaian Kriteria Sekolah</h5>
                            
                            <?php if(mysqli_num_rows($kriteria_query) > 0): ?>
                                <div class="row">
                                    <?php while($k = mysqli_fetch_assoc($kriteria_query)): ?>
                                        <div class="col-md-6 mb-3">
                                            <label for="kriteria_<?php echo $k['id_kriteria']; ?>" class="form-label">
                                                <?php echo htmlspecialchars($k['nama_kriteria']); ?> 
                                                <span class="badge <?php echo $k['tipe'] == 'benefit' ? 'bg-success' : 'bg-danger'; ?> ms-1">
                                                    <?php echo $k['tipe'] == 'benefit' ? 'Benefit' : 'Cost'; ?>
                                                </span>
                                            </label>
                                            <input type="number" class="form-control" id="kriteria_<?php echo $k['id_kriteria']; ?>" 
                                                name="kriteria_<?php echo $k['id_kriteria']; ?>" step="0.01" min="0" required
                                                value="<?php echo isset($_POST['kriteria_'.$k['id_kriteria']]) ? htmlspecialchars($_POST['kriteria_'.$k['id_kriteria']]) : ''; ?>"
                                                placeholder="Nilai untuk <?php echo htmlspecialchars($k['nama_kriteria']); ?>">
                                            <div class="form-text">
                                                <small>
                                                    <i class="bi bi-info-circle me-1"></i> 
                                                    Bobot: <?php echo number_format($k['bobot'], 2); ?>%
                                                </small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Tidak ada kriteria yang tersedia. Harap tambahkan kriteria terlebih dahulu.
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="reset" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary" <?php echo mysqli_num_rows($kriteria_query) == 0 ? 'disabled' : ''; ?>>
                                    <i class="bi bi-save me-1"></i> Simpan Sekolah
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Informasi Sekolah</h6>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Panduan Pengisian Form</h5>
                        <p>Lengkapi informasi sekolah dengan detail yang akurat untuk hasil penilaian yang optimal.</p>
                        
                        <h6 class="mt-4"><i class="bi bi-building me-2"></i>Data Utama Sekolah:</h6>
                        <ul>
                            <li><strong>Nama Sekolah</strong> - Nama lengkap sekolah SMA Swasta</li>
                            <li><strong>Alamat</strong> - Lokasi lengkap sekolah</li>
                            <li><strong>Akreditasi</strong> - Status akreditasi resmi (A/B/C)</li>
                            <li><strong>Total Guru</strong> - Jumlah keseluruhan tenaga pengajar</li>
                            <li><strong>Rata-rata UN</strong> - Nilai rata-rata ujian nasional (skala 0-10)</li>
                            <li><strong>Biaya SPP</strong> - Biaya SPP bulanan dalam Rupiah</li>
                        </ul>
                        
                        <h6 class="mt-3"><i class="bi bi-list-check me-2"></i>Penilaian Kriteria:</h6>
                        <p>Berikan nilai untuk setiap kriteria yang telah ditentukan sebelumnya.</p>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-lightbulb-fill me-2"></i>
                            <strong>Tip:</strong> Perhatikan tipe kriteria
                            <ul class="mb-0 mt-1">
                                <li><strong>Benefit</strong> - Nilai yang semakin tinggi semakin baik</li>
                                <li><strong>Cost</strong> - Nilai yang semakin rendah semakin baik</li>
                            </ul>
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
    <script>
        // Format currency for biaya SPP
        document.getElementById('biaya_spp').addEventListener('blur', function() {
            const value = this.value;
            if (!isNaN(value) && value !== '') {
                this.value = parseFloat(value).toFixed(0);
            }
        });
        
        // Format decimal for rata-rata UN
        document.getElementById('rata_un').addEventListener('blur', function() {
            const value = this.value;
            if (!isNaN(value) && value !== '') {
                this.value = parseFloat(value).toFixed(2);
            }
        });
    </script>
</body>
</html>