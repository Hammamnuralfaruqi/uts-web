<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, langsung arahkan ke index.php
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

include_once("config.php");

$error = "";
$nama     = "";
$username = "";
$email    = "";

// Proses registrasi akun baru
if (isset($_POST['register'])) {
    $nama             = trim($_POST['nama']);
    $username         = strtolower(trim($_POST['username']));
    $email            = strtolower(trim($_POST['email']));
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($nama) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Mohon lengkapi seluruh formulir pendaftaran.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format alamat email tidak valid.";
    } elseif (strlen($username) < 3) {
        $error = "Username minimal harus 3 karakter.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal harus 6 karakter.";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok dengan password yang dimasukkan.";
    } else {
        // Cek apakah username atau email sudah pernah terdaftar
        $stmt_check = mysqli_prepare($mysqli, "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
            mysqli_stmt_execute($stmt_check);
            $check_res = mysqli_stmt_get_result($stmt_check);

            if ($check_res && mysqli_num_rows($check_res) > 0) {
                $error = "Username atau email tersebut sudah terdaftar. Silakan gunakan yang lain.";
            } else {
                // Hash password secara aman
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert user baru ke database
                $stmt_insert = mysqli_prepare($mysqli, "INSERT INTO users (nama, username, email, password) VALUES (?, ?, ?, ?)");
                if ($stmt_insert) {
                    mysqli_stmt_bind_param($stmt_insert, "ssss", $nama, $username, $email, $password_hash);
                    if (mysqli_stmt_execute($stmt_insert)) {
                        // Redirect ke halaman login dengan notifikasi berhasil
                        header("Location: login.php?msg=registered");
                        exit();
                    } else {
                        $error = "Terjadi kegagalan saat mendaftar: " . mysqli_error($mysqli);
                    }
                    mysqli_stmt_close($stmt_insert);
                }
            }
            mysqli_stmt_close($stmt_check);
        } else {
            $error = "Kesalahan query database: " . mysqli_error($mysqli);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - SIMRS Alat Elektromedis</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">
    
    <!-- Local Third-Party Libraries -->
    <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/libs/bootstrap-icons/bootstrap-icons.css">
    
    <!-- Main Design System & Custom Stylesheet -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

    <div class="login-wrapper">
        <!-- Glowing background shapes -->
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>
        
        <!-- Main centered register card -->
        <div class="login-card" style="max-width: 480px;">
            
            <!-- Brand Identity -->
            <a href="login.php" class="login-brand text-decoration-none">
                <i class="bi bi-hospital text-lime"></i>
                <span>SIMRS Medis</span>
            </a>
            
            <p class="login-subtitle">Lengkapi formulir di bawah ini untuk membuat akun baru</p>

            <!-- Alert Error Validasi -->
            <?php if (!empty($error)): ?>
                <div class="alert-custom alert-custom-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i>
                    <div class="alert-custom-content">
                        <?= htmlspecialchars($error) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Register Form -->
            <form action="register.php" method="POST" id="registerForm">
                
                <!-- Nama Lengkap -->
                <div class="login-form-group">
                    <label for="nama" class="login-form-label">Nama Lengkap</label>
                    <div class="login-input-group">
                        <i class="bi bi-person-vcard input-icon"></i>
                        <input type="text" name="nama" id="nama" class="login-input" placeholder="Contoh: Budi Santoso" value="<?= htmlspecialchars($nama) ?>" required autofocus>
                    </div>
                </div>

                <!-- Username -->
                <div class="login-form-group">
                    <label for="username" class="login-form-label">Username</label>
                    <div class="login-input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="username" id="username" class="login-input" placeholder="Contoh: budi123" value="<?= htmlspecialchars($username) ?>" required>
                    </div>
                </div>
                
                <!-- Email -->
                <div class="login-form-group">
                    <label for="email" class="login-form-label">Alamat Email</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email" id="email" class="login-input" placeholder="budi@example.com" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                </div>
                
                <!-- Password Input Group -->
                <div class="login-form-group">
                    <label for="password" class="login-form-label">Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="login-input login-input-password" placeholder="Minimal 6 karakter" required>
                        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Lihat password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div class="login-form-group">
                    <label for="confirm_password" class="login-form-label">Konfirmasi Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-check input-icon"></i>
                        <input type="password" name="confirm_password" id="confirm_password" class="login-input login-input-password" placeholder="Ulangi password di atas" required>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" name="register" class="btn-login" id="btn-submit">
                    <span>Daftar Akun Sekarang</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
                
            </form>
            
            <!-- Footer Link -->
            <p class="login-footer-text">
                Sudah memiliki akun? <a href="login.php" id="link-login">Masuk Sekarang</a>
            </p>
            
        </div>
    </div>

    <!-- Script Dependencies -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
