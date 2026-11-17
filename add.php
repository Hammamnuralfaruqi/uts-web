<?php
// Proteksi autentikasi: wajib login
require_once("auth_check.php");

// Hubungkan ke file koneksi database
include_once("config.php");

$message = "";
$status = "";

// Cek parameter kategori awal dari URL (jika ada)
$kategori_default = (isset($_GET['kategori']) && strtolower(trim($_GET['kategori'])) === 'non medik') ? 'Non Medik' : 'Medik';

// Cek apakah form telah disubmit
if (isset($_POST['Submit'])) {
    $nama_alat = mysqli_real_escape_string($mysqli, trim($_POST['nama_alat']));
    $kategori  = mysqli_real_escape_string($mysqli, trim($_POST['kategori']));
    $tahun     = mysqli_real_escape_string($mysqli, trim($_POST['tahun']));
    $merek     = mysqli_real_escape_string($mysqli, trim($_POST['merek']));
    $lokasi    = mysqli_real_escape_string($mysqli, trim($_POST['lokasi']));

    // Simpan nilai pilihan untuk mempertahankan seleksi jika error
    $kategori_default = $kategori;

    // Validasi kelengkapan form
    if (!empty($nama_alat) && !empty($kategori) && !empty($tahun) && !empty($merek) && !empty($lokasi)) {
        // Simpan data alat/aset ke database
        $result = mysqli_query($mysqli, "INSERT INTO alat(nama_alat, kategori, tahun, merek, lokasi) VALUES('$nama_alat', '$kategori', '$tahun', '$merek', '$lokasi')");

        if ($result) {
            $status = "success";
            $message = "Data aset <strong>" . htmlspecialchars($nama_alat) . "</strong> (" . htmlspecialchars($kategori) . ") berhasil ditambahkan!";
        } else {
            $status = "error";
            $message = "Terjadi kesalahan saat menyimpan data: " . mysqli_error($mysqli);
        }
    } else {
        $status = "error";
        $message = "Harap isi semua kolom formulir.";
    }
}

// Konfigurasi judul & menu aktif
$page_title = "Tambah Aset Baru";
$page_subtitle = "Formulir pendaftaran inventaris aset rumah sakit (Medik / Non Medik)";
$active_menu = "tambah_alat";

// Load header template
include_once("header.php");
?>

<!-- Alert Feedback -->
<?php if ($status === "success"): ?>
  <div class="alert-custom alert-custom-success mb-4">
    <i class="bi bi-check-circle-fill alert-custom-icon"></i>
    <div class="alert-custom-content">
      <?= $message ?>
      <a href="index.php?kategori=<?= urlencode($kategori_default) ?>" class="ms-2 fw-semibold text-decoration-underline">Lihat Aset <?= htmlspecialchars($kategori_default) ?> &rarr;</a>
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

<!-- Form Card Container -->
<div class="row">
  <div class="col-12 col-lg-8 col-xl-7">
    <div class="card border-light shadow-sm p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
          <h5 class="card-title mb-1">Formulir Tambah Aset</h5>
          <span class="text-muted small">Pilih kategori aset (Medik atau Non Medik) dan isi spesifikasi alat</span>
        </div>
        <a href="index.php" class="btn-custom btn-custom-outline-secondary btn-custom-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
      </div>

      <form action="add.php" method="post" name="form1">
        <!-- Pilihan Kategori Aset -->
        <div class="mb-3">
          <label for="kategori" class="form-label-custom">Kategori Aset <span class="text-danger">*</span></label>
          <select class="form-select-custom" id="kategori" name="kategori" required>
            <option value="Medik" <?= ($kategori_default === 'Medik') ? 'selected' : '' ?>>
              Aset Medik (Peralatan Medis, Elektromedis, Pasien)
            </option>
            <option value="Non Medik" <?= ($kategori_default === 'Non Medik') ? 'selected' : '' ?>>
              Aset Non Medik (Fasilitas Gedung, IT, Sarana & Prasarana)
            </option>
          </select>
        </div>

        <!-- Input Nama Alat / Aset -->
        <div class="mb-3">
          <label for="nama_alat" class="form-label-custom">Nama Alat / Aset <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="nama_alat" name="nama_alat" placeholder="Contoh: USG 4D, Pasien Monitor, AC Sentral, Genset" required>
        </div>

        <!-- Input Tahun -->
        <div class="mb-3">
          <label for="tahun" class="form-label-custom">Tahun Pengadaan <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="tahun" name="tahun" placeholder="Contoh: 2024" required>
        </div>

        <!-- Input Merek -->
        <div class="mb-3">
          <label for="merek" class="form-label-custom">Merek / Pabrikan <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="merek" name="merek" placeholder="Contoh: GE Healthcare, Daikin, Philips, Dell" required>
        </div>

        <!-- Input Lokasi -->
        <div class="mb-4">
          <label for="lokasi" class="form-label-custom">Lokasi Penempatan <span class="text-danger">*</span></label>
          <input type="text" class="form-control-custom" id="lokasi" name="lokasi" placeholder="Contoh: Ruang ICU, IGD, Poli Radiologi, Ruang Server" required>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2">
          <button type="submit" name="Submit" class="btn-custom btn-custom-primary">
            <i class="bi bi-plus-circle me-1"></i> Simpan Data Aset
          </button>
          <a href="index.php" class="btn-custom btn-custom-light">
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Information Card Side -->
  <div class="col-12 col-lg-4 col-xl-5">
    <div class="card border-light shadow-sm p-4 mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Panduan Kategori</h6>
      <div class="mb-3 pb-3 border-bottom">
        <strong class="d-block text-success mb-1"><i class="bi bi-heart-pulse me-1"></i> Aset Medik:</strong>
        <p class="text-muted small mb-0">Alat-alat elektromedis, instrumen klinis, laboratorium, atau diagnostik untuk pelayanan medis.</p>
      </div>
      <div>
        <strong class="d-block text-info mb-1"><i class="bi bi-building-gear me-1"></i> Aset Non Medik:</strong>
        <p class="text-muted small mb-0">Fasilitas umum penunjang operasional, sarana gedung, komputer/IT, furnitur, dan genset listrik.</p>
      </div>
    </div>
  </div>
</div>

<?php
// Load footer template
include_once("footer.php");
?>
