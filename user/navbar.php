<?php
// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">SPK Sekolah</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="lihat_ranking.php">Ranking Sekolah</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-black" type="button" disabled>
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