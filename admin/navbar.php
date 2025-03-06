<?php

// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">SPK Sekolah</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="tambah_sekolah.php">Tambah Sekolah</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="daftar_sekolah.php">Daftar Sekolah</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="tambah_kriteria.php">Tambah Kriteria</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="daftar_kriteria.php">Daftar Kriteria</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="hasil_ranking.php">Hasil Ranking</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="kelola_pengguna.php">Kelola Pengguna</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="export_data.php">Export Data</a>
        </li>
    </ul>

    
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary" type="button" disabled>
            <i class="bi bi-person-fill"></i> <?php echo $_SESSION['username']; ?>
        </button>

        <a href="../logout.php" class="btn btn-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

    </div>
</nav>

<!-- Include Bootstrap and Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>