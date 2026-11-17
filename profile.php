<?php
// Proteksi autentikasi: wajib login
require_once("auth_check.php");

// Hubungkan ke file koneksi database
include_once("config.php");

$message = "";
$status = "";

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Ambil data profil pengguna saat ini
$stmt = mysqli_prepare($mysqli, "SELECT id, nama, username, email, foto, status_profile, created_at FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: logout.php");
    exit();
}

$current_nama    = $user['nama'];
$current_status  = !empty($user['status_profile']) ? $user['status_profile'] : 'Pebisnis';
$current_foto    = !empty($user['foto']) ? $user['foto'] : 'assets/images/avatar.png';
$current_email   = $user['email'];
$current_user    = $user['username'];
$current_created = $user['created_at'];

// Daftar pilihan status profil yang disediakan
$status_options = [
    "Pebisnis"                  => "Pebisnis",
    "Sibuk"                     => "Sibuk",
    "Pedagang"                  => "Pedagang",
    "Supplier"                  => "Supplier",
    "Jasa Kalibrasi"            => "Jasa Kalibrasi",
    "Jual Alat Medis"           => "Jual Alat Medis",
    "Service Alat Medis"        => "Service Alat Medis",
    "Jual Sparepart Alat Medis" => "Jual Sparepart Alat Medis"
];

// Proses simpan perubahan profil
if (isset($_POST['update_profile'])) {
    $nama_baru   = trim($_POST['nama']);
    $status_pilih = trim($_POST['status_profile']);

    // Jika memilih 'Lainnya', gunakan status kustom
    if ($status_pilih === 'Lainnya' && !empty(trim($_POST['custom_status']))) {
        $status_baru = trim($_POST['custom_status']);
    } else {
        $status_baru = $status_pilih;
    }

    $foto_baru = $current_foto;

    if (empty($nama_baru) || empty($status_baru)) {
        $status = "error";
        $message = "Nama lengkap dan status profil tidak boleh kosong.";
    } else {
        // Cek apakah ada file foto yang diunggah
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['foto']['tmp_name'];
            $fileName      = $_FILES['foto']['name'];
            $fileSize      = $_FILES['foto']['size'];
            $fileType      = $_FILES['foto']['type'];
            $fileNameCmps  = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $maxFileSize       = 3 * 1024 * 1024; // 3MB

            if (!in_array($fileExtension, $allowedExtensions)) {
                $status = "error";
                $message = "Format gambar tidak didukung! Harap unggah file JPG, JPEG, PNG, WEBP, atau GIF.";
            } elseif ($fileSize > $maxFileSize) {
                $status = "error";
                $message = "Ukuran gambar terlalu besar! Maksimal ukuran file adalah 3MB.";
            } else {
                // Pastikan folder uploads/profiles tersedia
                $uploadDir = 'uploads/profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Buat nama file unik
                $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
                $destPath    = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // Hapus foto lama jika tersimpan di folder uploads
                    if (!empty($current_foto) && strpos($current_foto, 'uploads/profiles/') !== false && file_exists($current_foto)) {
                        @unlink($current_foto);
                    }
                    $foto_baru = $destPath;
                } else {
                    $status = "error";
                    $message = "Gagal memindahkan file yang diunggah ke server.";
                }
            }
        }

        // Jika tidak ada error file, lanjutkan update database
        if ($status !== "error") {
            $stmt_up = mysqli_prepare($mysqli, "UPDATE users SET nama = ?, status_profile = ?, foto = ? WHERE id = ?");
            if ($stmt_up) {
                mysqli_stmt_bind_param($stmt_up, "sssi", $nama_baru, $status_baru, $foto_baru, $userId);
                if (mysqli_stmt_execute($stmt_up)) {
                    $status = "success";
                    $message = "Profil Anda berhasil diperbarui!";

                    // Update data lokal & session
                    $current_nama   = $nama_baru;
                    $current_status = $status_baru;
                    $current_foto   = $foto_baru;

                    $_SESSION['nama']           = $nama_baru;
                    $_SESSION['status_profile'] = $status_baru;
                    $_SESSION['foto']           = $foto_baru;
                } else {
                    $status = "error";
                    $message = "Terjadi kesalahan saat memperbarui database: " . mysqli_error($mysqli);
                }
                mysqli_stmt_close($stmt_up);
            }
        }
    }
}

// Konfigurasi halaman
$page_title = "Profil Pengguna";
$page_subtitle = "Kelola nama, foto profil, dan status profesi Anda";
$active_menu = "profile";

// Load header template
include_once("header.php");
?>

<!-- Alert Feedback -->
<?php if ($status === "success"): ?>
  <div class="alert-custom alert-custom-success mb-4">
    <i class="bi bi-check-circle-fill alert-custom-icon"></i>
    <div class="alert-custom-content">
      <?= $message ?>
    </div>
    <button class="alert-custom-close" type="button" aria-label="Close" onclick="this.parentElement.remove();">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
<?php elseif ($status === "error"): ?>
  <div class="alert-custom alert-custom-danger mb-4">
    <i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i>
    <div class="alert-custom-content">
      <?= $message ?>
    </div>
    <button class="alert-custom-close" type="button" aria-label="Close" onclick="this.parentElement.remove();">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <!-- Sisi Kiri: Kartu Tampilan Profil Saat Ini -->
  <div class="col-12 col-lg-4 col-xl-4">
    <div class="card border-light shadow-sm p-4 text-center h-100">
      
      <!-- Avatar Foto Saat Ini -->
      <div class="position-relative d-inline-block mx-auto mb-3">
        <img src="<?= htmlspecialchars($current_foto) ?>" id="previewAvatar" alt="<?= htmlspecialchars($current_nama) ?>" 
             class="rounded-circle shadow-sm border border-3 border-light-subtle object-fit-cover" 
             style="width: 130px; height: 130px;"
             onerror="this.src='assets/images/avatar.png'">
        <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-2 border-white rounded-circle" title="Status Aktif">
          <span class="visually-hidden">Online</span>
        </span>
      </div>

      <h5 class="fw-bold mb-1"><?= htmlspecialchars($current_nama) ?></h5>
      <p class="text-muted small mb-2">@<?= htmlspecialchars($current_user) ?></p>

      <!-- Badge Status Profil -->
      <div class="mb-4">
        <span class="badge-table success px-3 py-2 fs-6">
          <i class="bi bi-person-badge me-1"></i> <?= htmlspecialchars($current_status) ?>
        </span>
      </div>

      <hr class="my-3">

      <!-- Informasi Akun -->
      <div class="text-start small">
        <div class="d-flex justify-content-between py-2 border-bottom">
          <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email</span>
          <span class="fw-semibold text-main text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($current_email) ?>"><?= htmlspecialchars($current_email) ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
          <span class="text-muted"><i class="bi bi-shield-check me-2"></i>Role</span>
          <span class="fw-semibold text-main">Administrator</span>
        </div>
        <div class="d-flex justify-content-between py-2">
          <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>Terdaftar</span>
          <span class="fw-semibold text-main"><?= date('d M Y', strtotime($current_created)) ?></span>
        </div>
      </div>

    </div>
  </div>

  <!-- Sisi Kanan: Form Edit Profil & Upload Foto -->
  <div class="col-12 col-lg-8 col-xl-8">
    <div class="card border-light shadow-sm p-4">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
          <h5 class="card-title mb-1">Pengaturan Profil</h5>
          <span class="text-muted small">Ubah foto profil, nama lengkap, dan status profesi Anda</span>
        </div>
        <a href="index.php" class="btn-custom btn-custom-outline-secondary btn-custom-sm">
          <i class="bi bi-arrow-left me-1"></i> Ke Dashboard
        </a>
      </div>

      <form action="profile.php" method="POST" enctype="multipart/form-data" id="formProfile">

        <!-- Input Nama Lengkap -->
        <div class="mb-4">
          <label for="nama" class="form-label-custom">Nama Lengkap <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control-custom border-start-0" id="nama" name="nama" 
                   value="<?= htmlspecialchars($current_nama) ?>" placeholder="Masukkan nama lengkap Anda" required>
          </div>
        </div>

        <!-- Pilihan Status Profile -->
        <div class="mb-4">
          <label for="status_profile" class="form-label-custom">Status Profil / Profesi <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-workspace"></i></span>
            <select class="form-select-custom border-start-0" id="status_profile" name="status_profile" required onchange="toggleCustomStatus(this.value)">
              <?php 
              $is_custom = true;
              foreach ($status_options as $val => $label): 
                $selected = ($current_status === $val);
                if ($selected) $is_custom = false;
              ?>
                <option value="<?= htmlspecialchars($val) ?>" <?= $selected ? 'selected' : '' ?>>
                  <?= htmlspecialchars($label) ?>
                </option>
              <?php endforeach; ?>
              <option value="Lainnya" <?= $is_custom ? 'selected' : '' ?>>
                Lainnya (Ketik Sendiri...)
              </option>
            </select>
          </div>
          <small class="text-muted">Pilih status yang mendeskripsikan aktivitas Anda di sistem SIMRS.</small>
        </div>

        <!-- Input Custom Status (Muncul jika pilih 'Lainnya') -->
        <div class="mb-4 <?= $is_custom ? '' : 'd-none' ?>" id="customStatusWrapper">
          <label for="custom_status" class="form-label-custom">Ketik Status Profil Kustom <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="custom_status" name="custom_status" 
                 value="<?= $is_custom ? htmlspecialchars($current_status) : '' ?>" placeholder="Contoh: Konsultan Elektromedis, Teknisi Lab, dll">
        </div>

        <!-- Upload Foto Profil -->
        <div class="mb-4">
          <label for="foto" class="form-label-custom">Unggah Foto Profil Baru</label>
          <input type="file" class="form-control-custom" id="foto" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" onchange="previewImage(this)">
          <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Format: JPG, JPEG, PNG, WEBP, GIF. Ukuran Maks: 3MB.</small>
            <small class="text-primary fw-semibold" id="fileChosenText">Belum ada file baru dipilih</small>
          </div>
        </div>

        <!-- Tombol Aksi Simpan -->
        <div class="d-flex gap-2 pt-2 border-top">
          <button type="submit" name="update_profile" class="btn-custom btn-custom-primary">
            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan Profil
          </button>
          <a href="index.php" class="btn-custom btn-custom-light">
            Batal
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
// Live Preview Foto Profil saat pengguna memilih file gambar baru
function previewImage(input) {
  const fileChosenText = document.getElementById('fileChosenText');
  const previewAvatar = document.getElementById('previewAvatar');

  if (input.files && input.files[0]) {
    const file = input.files[0];
    fileChosenText.textContent = 'Dipilih: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';

    const reader = new FileReader();
    reader.onload = function(e) {
      if (previewAvatar) {
        previewAvatar.src = e.target.result;
      }
    };
    reader.readAsDataURL(file);
  } else {
    fileChosenText.textContent = 'Belum ada file baru dipilih';
  }
}

// Buka/Tutup input teks kustom jika memilih opsi 'Lainnya'
function toggleCustomStatus(val) {
  const customWrapper = document.getElementById('customStatusWrapper');
  const customInput = document.getElementById('custom_status');
  if (val === 'Lainnya') {
    customWrapper.classList.remove('d-none');
    customInput.focus();
  } else {
    customWrapper.classList.add('d-none');
  }
}
</script>

<?php
// Load footer template
include_once("footer.php");
?>
