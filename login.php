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
$login_input = "";

// Proses autentikasi login
if (isset($_POST['login'])) {
    $login_input = trim($_POST['username_or_email']);
    $password    = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        $error = "Harap isi username/email dan password.";
    } else {
        // Cari pengguna berdasarkan username atau email menggunakan prepared statement
        $stmt = mysqli_prepare($mysqli, "SELECT id, nama, username, email, password, foto, status_profile FROM users WHERE username = ? OR email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            if ($res && $user = mysqli_fetch_assoc($res)) {
                // Verifikasi password hash
                if (password_verify($password, $user['password'])) {
                    // Simpan data sesi pengguna
                    $_SESSION['user_id']        = $user['id'];
                    $_SESSION['nama']           = $user['nama'];
                    $_SESSION['username']       = $user['username'];
                    $_SESSION['email']          = $user['email'];
                    $_SESSION['foto']           = !empty($user['foto']) ? $user['foto'] : 'assets/images/avatar.png';
                    $_SESSION['status_profile'] = !empty($user['status_profile']) ? $user['status_profile'] : 'Pebisnis';
                    $_SESSION['is_logged_in']   = true;

                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Password yang Anda masukkan salah.";
                }
            } else {
                $error = "Akun dengan username atau email tersebut tidak ditemukan.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Gagal memproses login: " . mysqli_error($mysqli);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMRS Alat Elektromedis</title>
    
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
        
        <!-- Main centered login card -->
        <div class="login-card">
            
            <!-- Brand Identity -->
            <a href="login.php" class="login-brand text-decoration-none">
                <i class="bi bi-hospital text-lime"></i>
                <span>SIMRS Medis</span>
            </a>
            
            <p class="login-subtitle">Masuk ke akun Anda untuk mengelola data alat elektromedis</p>

            <!-- Alert Notifikasi Sesi & Pesan -->
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'need_login'): ?>
                    <div class="alert-custom alert-custom-warning mb-3">
                        <i class="bi bi-shield-exclamation alert-custom-icon"></i>
                        <div class="alert-custom-content">
                            Silakan masuk terlebih dahulu untuk mengakses sistem.
                        </div>
                    </div>
                <?php elseif ($_GET['msg'] === 'registered'): ?>
                    <div class="alert-custom alert-custom-success mb-3">
                        <i class="bi bi-check-circle-fill alert-custom-icon"></i>
                        <div class="alert-custom-content">
                            Pendaftaran berhasil! Silakan masuk menggunakan akun baru Anda.
                        </div>
                    </div>
                <?php elseif ($_GET['msg'] === 'logout'): ?>
                    <div class="alert-custom alert-custom-info mb-3">
                        <i class="bi bi-info-circle-fill alert-custom-icon"></i>
                        <div class="alert-custom-content">
                            Anda telah berhasil keluar (logout) dari sistem.
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Alert Error Validasi -->
            <?php if (!empty($error)): ?>
                <div class="alert-custom alert-custom-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i>
                    <div class="alert-custom-content">
                        <?= htmlspecialchars($error) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form action="login.php" method="POST" id="loginForm">
                
                <!-- Username / Email -->
                <div class="login-form-group">
                    <label for="username_or_email" class="login-form-label">Username atau Email</label>
                    <div class="login-input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="username_or_email" id="username_or_email" class="login-input" placeholder="admin atau admin@simrs.local" value="<?= htmlspecialchars($login_input) ?>" required autofocus>
                    </div>
                </div>
                
                <!-- Password Input Group -->
                <div class="login-form-group">
                    <label for="password" class="login-form-label">Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="login-input login-input-password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Lihat password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" name="login" class="btn-login" id="btn-submit">
                    <span>Masuk ke Dashboard</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
                
            </form>
            
            <!-- Footer Link -->
            <p class="login-footer-text">
                Belum punya akun? <a href="register.php" id="link-register">Daftar Sekarang</a>
            </p>

            <!-- Akun Bawaan Info -->
            <div class="mt-4 pt-3 border-top text-center">
                <small class="text-muted d-block mb-1">Akun Demo Bawaan:</small>
                <div class="p-2 rounded bg-light border small text-muted">
                    Username: <strong class="text-main">admin</strong> &bull; Password: <strong class="text-main">admin123</strong>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Script Dependencies -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
