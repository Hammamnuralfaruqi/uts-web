<?php
// Proteksi autentikasi: wajib login
require_once("auth_check.php");

// Hubungkan ke file koneksi database
include_once("config.php");

$message = "";
$status = "";

// Cek apakah form update disubmit
if (isset($_POST['update'])) {
    $id        = intval($_POST['id']);
    $nama_alat = mysqli_real_escape_string($mysqli, trim($_POST['nama_alat']));
    $kategori  = mysqli_real_escape_string($mysqli, trim($_POST['kategori']));
    $tahun     = mysqli_real_escape_string($mysqli, trim($_POST['tahun']));
    $merek     = mysqli_real_escape_string($mysqli, trim($_POST['merek']));
    $lokasi    = mysqli_real_escape_string($mysqli, trim($_POST['lokasi']));

    // Update data alat ke database
    $result = mysqli_query($mysqli, "UPDATE alat SET nama_alat='$nama_alat', kategori='$kategori', tahun='$tahun', merek='$merek', lokasi='$lokasi' WHERE id=$id");

    if ($result) {
        // Redirect ke index dengan status notifikasi updated
        header("Location: index.php?msg=updated&kategori=" . urlencode($kategori));
        exit();
    } else {
        $status = "error";
        $message = "Terjadi kesalahan saat memperbarui data: " . mysqli_error($mysqli);
    }
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data alat dari database sesuai ID
$result = mysqli_query($mysqli, "SELECT * FROM alat WHERE id=$id");

$nama_alat = '';
$kategori  = 'Medik';
$tahun     = '';
$merek     = '';
$lokasi    = '';

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_array($result);
    $nama_alat = $user_data['nama_alat'];
    $kategori  = isset($user_data['kategori']) && !empty($user_data['kategori']) ? $user_data['kategori'] : 'Medik';
    $tahun     = $user_data['tahun'];
    $merek     = $user_data['merek'];
    $lokasi    = $user_data['lokasi'];
} else {
    // Jika data tidak ditemukan, kembali ke halaman utama
    header("Location: index.php");
    exit();
}

// Konfigurasi halaman
$page_title = "Edit Data Aset";
$page_subtitle = "Perbarui rincian inventaris aset rumah sakit";
$active_menu = (strtolower($kategori) === 'non medik') ? 'aset_non_medik' : 'aset_medik';

// Load header template
include_once("header.php");
?>

<!-- Alert Feedback -->
<?php if ($status === "error"): ?>
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

<!-- Form Edit Card Container -->
<div class="row">
  <div class="col-12 col-lg-8 col-xl-7">
    <div class="card border-light shadow-sm p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
          <h5 class="card-title mb-1">Edit Aset: <?= htmlspecialchars($nama_alat) ?></h5>
          <span class="text-muted small">ID Aset: #<?= $id ?> &bull; Kategori: <strong><?= htmlspecialchars($kategori) ?></strong></span>
        </div>
        <a href="index.php?kategori=<?= urlencode($kategori) ?>" class="btn-custom btn-custom-outline-secondary btn-custom-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
      </div>

      <form action="edit.php" method="post" name="update_user">
        <input type="hidden" name="id" value="<?= $id ?>">

        <!-- Pilihan Kategori -->
        <div class="mb-3">
          <label for="kategori" class="form-label-custom">Kategori Aset <span class="text-danger">*</span></label>
          <select class="form-select-custom" id="kategori" name="kategori" required>
            <option value="Medik" <?= ($kategori === 'Medik') ? 'selected' : '' ?>>
              Aset Medik (Peralatan Medis, Elektromedis, Tindakan)
            </option>
            <option value="Non Medik" <?= ($kategori === 'Non Medik') ? 'selected' : '' ?>>
              Aset Non Medik (Fasilitas Gedung, IT, Sarana & Prasarana)
            </option>
          </select>
        </div>

        <!-- Input Nama Alat / Aset -->
        <div class="mb-3">
          <label for="nama_alat" class="form-label-custom">Nama Alat / Aset <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="nama_alat" name="nama_alat" value="<?= htmlspecialchars($nama_alat) ?>" required>
        </div>

        <!-- Input Tahun -->
        <div class="mb-3">
          <label for="tahun" class="form-label-custom">Tahun Pengadaan <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="tahun" name="tahun" value="<?= htmlspecialchars($tahun) ?>" required>
        </div>

        <!-- Input Merek -->
        <div class="mb-3">
          <label for="merek" class="form-label-custom">Merek / Pabrikan <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="merek" name="merek" value="<?= htmlspecialchars($merek) ?>" required>
        </div>

        <!-- Input Lokasi -->
        <div class="mb-4">
          <label for="lokasi" class="form-label-custom">Lokasi Penempatan <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="lokasi" name="lokasi" value="<?= htmlspecialchars($lokasi) ?>" required>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2">
          <button type="submit" name="update" class="btn-custom btn-custom-primary">
            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
          </button>
          <a href="index.php?kategori=<?= urlencode($kategori) ?>" class="btn-custom btn-custom-light">
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Information Card Side -->
  <div class="col-12 col-lg-4 col-xl-5">
    <div class="card border-light shadow-sm p-4 mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-shield-check text-primary me-2"></i>Informasi Edit</h6>
      <p class="text-muted small mb-3">
        Pastikan informasi yang Anda perbarui telah diverifikasi dengan unit fisik dan lokasi penempatan alat di rumah sakit.
      </p>
      <div class="p-3 rounded bg-light">
        <div class="small text-muted mb-1">Status Kategori:</div>
        <div class="fw-semibold text-main"><?= ($kategori === 'Medik') ? '<i class="bi bi-heart-pulse text-success me-1"></i> Aset Medik' : '<i class="bi bi-building-gear text-info me-1"></i> Aset Non Medik' ?></div>
      </div>
    </div>
  </div>
</div>

<?php
// Load footer template
include_once("footer.php");
?>
