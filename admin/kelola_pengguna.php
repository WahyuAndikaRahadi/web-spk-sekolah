<?php
session_start();
require_once '../config.php';
require_once 'navbar.php'; 

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Proses hapus pengguna
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Cek apakah ID yang akan dihapus bukan ID admin yang sedang login
    if ($id != $_SESSION['user_id']) {
        if (mysqli_query($koneksi, "DELETE FROM users WHERE id = $id")) {
            $_SESSION['pesan'] = "Pengguna berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus pengguna: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "Anda tidak dapat menghapus akun Anda sendiri!";
    }
    
    header("Location: kelola_pengguna.php");
    exit();
}

// Proses tambah/edit pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    
    // Jika ID ada, berarti edit pengguna
    if (isset($_POST['id']) && $_POST['id'] > 0) {
        $id = intval($_POST['id']);
        
        // Cek apakah username sudah ada (selain pengguna yang sedang diedit)
        $cek_username = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' AND id != $id");
        if (mysqli_num_rows($cek_username) > 0) {
            $_SESSION['error'] = "Username sudah digunakan!";
        } else {
            // Jika password diisi, update password juga
            if (!empty($_POST['password'])) {
                $password = md5($_POST['password']);
                $query = "UPDATE users SET nama_lengkap = '$nama_lengkap', username = '$username', password = '$password', role = '$role' WHERE id = $id";
            } else {
                $query = "UPDATE users SET nama_lengkap = '$nama_lengkap', username = '$username', role = '$role' WHERE id = $id";
            }
            
            if (mysqli_query($koneksi, $query)) {
                $_SESSION['pesan'] = "Pengguna berhasil diupdate!";
                header("Location: kelola_pengguna.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal mengupdate pengguna: " . mysqli_error($koneksi);
            }
        }
    } else { // Tambah pengguna baru
        // Cek apakah username sudah ada
        $cek_username = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek_username) > 0) {
            $_SESSION['error'] = "Username sudah digunakan!";
        } else {
            // Periksa apakah password diisi
            if (empty($_POST['password'])) {
                $_SESSION['error'] = "Password harus diisi untuk pengguna baru!";
            } else {
                $password = md5($_POST['password']);
                $query = "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama_lengkap', '$username', '$password', '$role')";
                
                if (mysqli_query($koneksi, $query)) {
                    $_SESSION['pesan'] = "Pengguna baru berhasil ditambahkan!";
                    header("Location: kelola_pengguna.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Gagal menambahkan pengguna: " . mysqli_error($koneksi);
                }
            }
        }
    }
}

// Ambil data untuk edit (jika ada)
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $id");
    $edit_data = mysqli_fetch_assoc($result);
}

// Ambil daftar pengguna
$pengguna = mysqli_query($koneksi, "SELECT * FROM users ORDER BY role ASC, nama_lengkap ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna | SPK Pemilihan SMA Swasta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-gray-800"><i class="bi bi-people me-2"></i>Kelola Pengguna</h1>
        
        <?php if(isset($_SESSION['pesan'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <?php if(isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">
                            <?php echo $edit_data ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_data) { ?>
                                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                            <?php } ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" 
                                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama_lengkap']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['username']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password <?php echo $edit_data ? '(Kosongkan jika tidak diubah)' : ''; ?></label>
                                <input type="password" name="password" class="form-control" 
                                       <?php echo $edit_data ? '' : 'required'; ?>>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-control" required>
                                    <option value="admin" <?php echo ($edit_data && $edit_data['role'] == 'admin') ? 'selected' : ''; ?>>
                                        Admin
                                    </option>
                                    <option value="user" <?php echo ($edit_data && $edit_data['role'] == 'user') ? 'selected' : ''; ?>>
                                        User
                                    </option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $edit_data ? 'Update Pengguna' : 'Tambah Pengguna'; ?>
                                </button>
                                
                                <?php if ($edit_data) { ?>
                                    <a href="kelola_pengguna.php" class="btn btn-secondary">Batal Edit</a>
                                <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title m-0">Daftar Pengguna</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Lengkap</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($pengguna)) { 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['role'] == 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                                <?php echo ucfirst($row['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="kelola_pengguna.php?edit=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <?php if ($row['id'] != $_SESSION['user_id']) { ?>
                                            <a href="kelola_pengguna.php?hapus=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    
                                    <?php if (mysqli_num_rows($pengguna) == 0) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data pengguna</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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