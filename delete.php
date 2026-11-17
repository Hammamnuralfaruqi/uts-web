<?php
// Proteksi autentikasi: wajib login
require_once("auth_check.php");

// Hubungkan ke file koneksi database
include_once("config.php");

// Ambil ID dari URL dan sanitasi
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Eksekusi query hapus data alat
    mysqli_query($mysqli, "DELETE FROM alat WHERE id=$id");
}

// Redirect ke halaman utama dengan parameter pesan 'deleted'
header("Location: index.php?msg=deleted");
exit();
?>
