<?php
session_start();
require_once '../config.php';
require_once 'navbar.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Proses form tambah kriteria
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kriteria = mysqli_real_escape_string($koneksi, $_POST['nama_kriteria']);
    $bobot = floatval($_POST['bobot']);
    $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama_kriteria)) {
        $errors[] = "Nama kriteria tidak boleh kosong";
    }
    
    if ($bobot <= 0 || $bobot > 100) {
        $errors[] = "Bobot harus bernilai positif dan tidak lebih dari 100";
    }
    
    if ($tipe != 'benefit' && $tipe != 'cost') {
        $errors[] = "Tipe kriteria harus berupa benefit atau cost";
    }
    
    // Cek apakah ada errors
    if (empty($errors)) {
        // Simpan ke database
        $query = "INSERT INTO kriteria (nama_kriteria, bobot, tipe) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sds", $nama_kriteria, $bobot, $tipe);
        
        if (mysqli_stmt_execute($stmt)) {
            // Ambil ID kriteria yang baru saja dibuat
            $id_kriteria_baru = mysqli_insert_id($koneksi);
            
            // Tambahkan kriteria baru ke semua sekolah yang sudah ada
            // dengan nilai default 0
            $query_sekolah = "SELECT id_sekolah FROM sekolah";
            $result_sekolah = mysqli_query($koneksi, $query_sekolah);
            
            while ($sekolah = mysqli_fetch_assoc($result_sekolah)) {
                $id_sekolah = $sekolah['id_sekolah'];
                $query_penilaian = "INSERT INTO penilaian (id_sekolah, id_kriteria, nilai) VALUES (?, ?, 0)";
                $stmt_penilaian = mysqli_prepare($koneksi, $query_penilaian);
                mysqli_stmt_bind_param($stmt_penilaian, "ii", $id_sekolah, $id_kriteria_baru);
                mysqli_stmt_execute($stmt_penilaian);
            }
            
            $_SESSION['pesan'] = "Kriteria berhasil ditambahkan dan diaplikasikan ke semua sekolah!";
            header("Location: daftar_kriteria.php");
            exit();
        } else {
            $errors[] = "Gagal menyimpan kriteria: " . mysqli_error($koneksi);
        }
    }
}

// Ambil total bobot kriteria yang sudah ada
$total_bobot_query = mysqli_query($koneksi, "SELECT SUM(bobot) as total FROM kriteria");
$total_bobot_result = mysqli_fetch_assoc($total_bobot_query);
$total_bobot = $total_bobot_result['total'] ?: 0;
$bobot_tersisa = 100 - $total_bobot;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kriteria | SPK Pemilihan SMA Swasta</title>
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
        

        .form-control:focus, .form-select:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .progress {
            height: 10px;
        }
        
        .info-tooltip {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-plus-circle me-2"></i>Tambah Kriteria</h1>
            <a href="daftar_kriteria.php" class="btn btn-sm btn-secondary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kriteria
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
                        <h6 class="m-0 font-weight-bold"><i class="bi bi-list-check me-2"></i>Form Tambah Kriteria</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="kriteria-form">
                            <div class="mb-3">
                                <label for="nama_kriteria" class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_kriteria" name="nama_kriteria" required 
                                    value="<?php echo isset($_POST['nama_kriteria']) ? htmlspecialchars($_POST['nama_kriteria']) : ''; ?>" 
                                    placeholder="Contoh: Akreditasi, Biaya SPP, Jumlah Guru">
                            </div>
                            
                            <div class="mb-3">
                                <label for="bobot" class="form-label">Bobot Kriteria <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="bobot" name="bobot" min="1" max="<?php echo $bobot_tersisa; ?>" step="0.01" required 
                                        value="<?php echo isset($_POST['bobot']) ? htmlspecialchars($_POST['bobot']) : ''; ?>" 
                                        placeholder="Rentang: 1-100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i> Bobot tersisa: <?php echo number_format($bobot_tersisa, 2); ?>% dari total 100%
                                </div>
                                
                                <div class="mt-2">
                                    <label class="form-label">Total Bobot Kriteria:</label>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $total_bobot; ?>%;" 
                                            aria-valuenow="<?php echo $total_bobot; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo number_format($total_bobot, 2); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tipe" class="form-label">Tipe Kriteria <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipe" name="tipe" required>
                                    <option value="" disabled <?php echo !isset($_POST['tipe']) ? 'selected' : ''; ?>>-- Pilih Tipe Kriteria --</option>
                                    <option value="benefit" <?php echo (isset($_POST['tipe']) && $_POST['tipe'] == 'benefit') ? 'selected' : ''; ?>>
                                        Benefit (Semakin tinggi nilai semakin baik)
                                    </option>
                                    <option value="cost" <?php echo (isset($_POST['tipe']) && $_POST['tipe'] == 'cost') ? 'selected' : ''; ?>>
                                        Cost (Semakin rendah nilai semakin baik)
                                    </option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="reset" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Simpan Kriteria
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Informasi Kriteria</h6>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Penjelasan Kriteria</h5>
                        <p>Kriteria adalah faktor-faktor yang digunakan dalam proses pengambilan keputusan untuk menentukan peringkat alternatif (sekolah) terbaik.</p>
                        
                        <h6 class="mt-4"><i class="bi bi-award me-2"></i>Tipe Kriteria:</h6>
                        <ul>
                            <li><strong>Benefit</strong> - Kriteria yang nilainya semakin tinggi semakin baik.</li>
                            <li><strong>Cost</strong> - Kriteria yang nilainya semakin rendah semakin baik.</li>
                        </ul>
                        
                        <h6 class="mt-3"><i class="bi bi-percent me-2"></i>Bobot Kriteria:</h6>
                        <p>Total keseluruhan bobot kriteria harus berjumlah 100%.</p>
                        
                        <h6 class="mt-3"><i class="bi bi-list-ol me-2"></i>Contoh Kriteria:</h6>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Kriteria</th>
                                    <th>Tipe</th>
                                    <th>Bobot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Akreditasi</td>
                                    <td>Benefit</td>
                                    <td>30%</td>
                                </tr>
                                <tr>
                                    <td>Biaya SPP</td>
                                    <td>Cost</td>
                                    <td>25%</td>
                                </tr>
                                <tr>
                                    <td>Jumlah Guru</td>
                                    <td>Benefit</td>
                                    <td>15%</td>
                                </tr>
                            </tbody>
                        </table>
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
        // Validate form on submit
        document.getElementById('kriteria-form').addEventListener('submit', function(event) {
            const bobot = parseFloat(document.getElementById('bobot').value);
            const maxBobot = parseFloat(<?php echo $bobot_tersisa; ?>);
            
            if (bobot > maxBobot) {
                event.preventDefault();
                alert('Bobot tidak boleh melebihi bobot tersisa (' + maxBobot + '%)');
            }
        });
        
        // Update progress bar when bobot changes
        document.getElementById('bobot').addEventListener('input', function() {
            const currentBobot = parseFloat(this.value) || 0;
            const existingBobot = parseFloat(<?php echo $total_bobot; ?>);
            const totalBobot = currentBobot + existingBobot;
            
            if (totalBobot > 100) {
                document.getElementById('bobot').classList.add('is-invalid');
            } else {
                document.getElementById('bobot').classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>