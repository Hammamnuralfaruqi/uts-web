<?php
// Konfigurasi Database MySQL
$databaseHost     = 'localhost';
$databaseName     = 'simrs';
$databseUsername = 'root';
$databasePassword = '';

// Buat koneksi database
$mysqli = mysqli_connect($databaseHost, $databseUsername, $databasePassword, $databaseName);

// Otomatis buat tabel users & alat jika belum ada
if ($mysqli) {
    // 1. Tabel users
    $createUsersTable = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        foto VARCHAR(255) DEFAULT 'assets/images/avatar.png',
        status_profile VARCHAR(100) DEFAULT 'Pebisnis',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($mysqli, $createUsersTable);

    // Pastikan kolom 'foto' dan 'status_profile' tersedia di tabel users
    $checkFoto = mysqli_query($mysqli, "SHOW COLUMNS FROM users LIKE 'foto'");
    if ($checkFoto && mysqli_num_rows($checkFoto) === 0) {
        mysqli_query($mysqli, "ALTER TABLE users ADD COLUMN foto VARCHAR(255) DEFAULT 'assets/images/avatar.png'");
    }
    $checkStatus = mysqli_query($mysqli, "SHOW COLUMNS FROM users LIKE 'status_profile'");
    if ($checkStatus && mysqli_num_rows($checkStatus) === 0) {
        mysqli_query($mysqli, "ALTER TABLE users ADD COLUMN status_profile VARCHAR(100) DEFAULT 'Pebisnis'");
    }

    // 2. Tabel alat (jika belum ada)
    $createAlatTable = "CREATE TABLE IF NOT EXISTS alat (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_alat VARCHAR(150) NOT NULL,
        tahun VARCHAR(20) NOT NULL,
        merek VARCHAR(100) NOT NULL,
        lokasi VARCHAR(100) NOT NULL,
        kategori VARCHAR(50) NOT NULL DEFAULT 'Medik'
    )";
    mysqli_query($mysqli, $createAlatTable);

    // 3. Pastikan kolom 'kategori' tersedia di tabel alat
    $checkCol = mysqli_query($mysqli, "SHOW COLUMNS FROM alat LIKE 'kategori'");
    if ($checkCol && mysqli_num_rows($checkCol) === 0) {
        mysqli_query($mysqli, "ALTER TABLE alat ADD COLUMN kategori VARCHAR(50) NOT NULL DEFAULT 'Medik'");
    }

    // 4. Buat akun default admin jika tabel users masih kosong
    $checkUser = mysqli_query($mysqli, "SELECT id FROM users LIMIT 1");
    if ($checkUser && mysqli_num_rows($checkUser) === 0) {
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = "INSERT INTO users (nama, username, email, password, foto, status_profile) VALUES ('Administrator', 'admin', 'admin@simrs.local', '$defaultPassword', 'assets/images/avatar.png', 'Pebisnis')";
        mysqli_query($mysqli, $insertAdmin);
    }
}
?>