<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = MD5($_POST['password']);

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SPK Pemilihan SMA Swasta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
        }
        
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: transparent;
            border-bottom: none;
            padding: 2rem 0 1rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .login-icon {
            font-size: 3rem;
            color: var(--primary-color);
        }
        
        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #5a5c69;
            margin-bottom: 1.5rem;
        }
        
        .form-floating > label {
            color: #6e707e;
        }
        
        .card-footer {
            background: transparent;
            border-top: none;
            padding: 1rem 0 2rem;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 15px;
            cursor: pointer;
            z-index: 10;
        }
        
        .password-container {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image">
                                <div class="p-5 h-100 d-flex flex-column justify-content-center align-items-center text-center" style="background-color: #f8f9fc;">
                                    <div class="login-icon mb-4">
                                        <i class="bi bi-building-check"></i>
                                    </div>
                                    <h1 class="brand-name">SPK Pemilihan SMA Swasta</h1>
                                    <p class="mb-4">Sistem Pendukung Keputusan untuk membantu Anda memilih SMA Swasta terbaik sesuai dengan kriteria yang diinginkan.</p>
                                    <div class="mt-3">
                                        <i class="bi bi-award text-primary me-2"></i> Objektif
                                        <i class="bi bi-graph-up-arrow text-primary ms-3 me-2"></i> Terukur
                                        <i class="bi bi-lightning-charge text-primary ms-3 me-2"></i> Cepat
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Selamat Datang!</h1>
                                    </div>
                                    
                                    <?php if(isset($error)) { ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php } ?>
                                    
                                    <form class="user" method="POST">
                                        <div class="form-floating mb-3">
                                            <input type="text" name="username" class="form-control" id="floatingUsername" placeholder="Username" required>
                                            <label for="floatingUsername"><i class="bi bi-person me-2"></i>Username</label>
                                        </div>
                                        
                                        <div class="form-floating mb-3 password-container">
                                            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                                            <label for="floatingPassword"><i class="bi bi-lock me-2"></i>Password</label>
                                            <span class="password-toggle" onclick="togglePassword()">
                                                <i class="bi bi-eye" id="toggleIcon"></i>
                                            </span>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                                            <label class="form-check-label" for="rememberMe">
                                                Ingat saya
                                            </label>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary btn-user btn-block w-100 py-3">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                        </button>
                                    </form>
                                    
                                    <hr>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center text-white mt-3">
                    <small>
                        Copyright &copy; SPK Pemilihan SMA Swasta <?php echo date('Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('floatingPassword');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>