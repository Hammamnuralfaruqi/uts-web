<?php
// Proteksi autentikasi: wajib login
require_once("auth_check.php");

// Hubungkan ke database
include_once("config.php");

// Hitung total aset per kategori
$total_semua = 0;
$total_medik = 0;
$total_non_medik = 0;

if ($mysqli) {
    // Total semua
    $q_all = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM alat");
    if ($q_all && $row = mysqli_fetch_assoc($q_all)) {
        $total_semua = $row['total'];
    }

    // Total medik
    $q_medik = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM alat WHERE kategori = 'Medik'");
    if ($q_medik && $row = mysqli_fetch_assoc($q_medik)) {
        $total_medik = $row['total'];
    }

    // Total non medik
    $q_non_medik = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM alat WHERE kategori = 'Non Medik'");
    if ($q_non_medik && $row = mysqli_fetch_assoc($q_non_medik)) {
        $total_non_medik = $row['total'];
    }
}

// Konfigurasi halaman
$page_title = "Kategori Aset";
$page_subtitle = "Daftar kategori dan pengelompokan inventaris aset rumah sakit";
$active_menu = "kategori";

// Load header template
include_once("header.php");
?>

<!-- START: Overview Metric Cards -->
<div class="row g-4 mb-4">
  <!-- Card Total Semua Aset Kalibrator -->
  <div class="col-12 col-md-4">
    <div class="card border-light shadow-sm p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted small fw-semibold text-uppercase">Total Seluruh Aset Kalibrator</span>
        <div class="p-2 rounded bg-light text-primary">
          <i class="bi bi-boxes fs-5"></i>
        </div>
      </div>
      <h2 class="fw-bold mb-1"><?= $total_semua ?> <small class="text-muted fs-6 fw-normal">Unit</small></h2>
      <span class="text-muted small">Semua unit terdaftar di SIMRS</span>
    </div>
  </div>

  <!-- Card Total Medik -->
  <div class="col-12 col-md-4">
    <div class="card border-light shadow-sm p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted small fw-semibold text-uppercase">Aset Medik</span>
        <div class="p-2 rounded bg-light text-success">
          <i class="bi bi-heart-pulse-fill fs-5"></i>
        </div>
      </div>
      <h2 class="fw-bold mb-1 text-success"><?= $total_medik ?> <small class="text-muted fs-6 fw-normal">Unit</small></h2>
      <span class="text-muted small">Peralatan medis & elektromedis</span>
    </div>
  </div>

  <!-- Card Total Non Medik -->
  <div class="col-12 col-md-4">
    <div class="card border-light shadow-sm p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted small fw-semibold text-uppercase">Aset Non Medik</span>
        <div class="p-2 rounded bg-light text-info">
          <i class="bi bi-building-gear fs-5"></i>
        </div>
      </div>
      <h2 class="fw-bold mb-1 text-info"><?= $total_non_medik ?> <small class="text-muted fs-6 fw-normal">Unit</small></h2>
      <span class="text-muted small">Sarana, fasilitas & prasarana</span>
    </div>
  </div>
</div>
<!-- END: Overview Metric Cards -->

<!-- START: Detail Kategori Cards -->
<div class="row g-4 mb-4">
  <!-- Kategori 1: Aset Medik -->
  <div class="col-12 col-lg-6">
    <div class="card border-light shadow-sm p-4 h-100 d-flex flex-column">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="p-3 rounded-3 bg-success-subtle text-success">
          <i class="bi bi-heart-pulse fs-2"></i>
        </div>
        <div>
          <span class="badge-table success mb-1">Kategori Medik</span>
          <h4 class="fw-bold mb-0">Aset Medik</h4>
        </div>
      </div>

      <p class="text-muted mb-4 flex-grow-1">
        Meliputi seluruh instrumen kesehatan, elektromedis, alat diagnostik, terapi, dan perlengkapan tindakan medis pasien rumah sakit.
        Contoh: <em>USG, ECG, Pasien Monitor, Defibrillator, Syringe Pump, X-Ray</em>.
      </p>

      <div class="p-3 rounded bg-light mb-3 d-flex justify-content-between align-items-center">
        <span class="text-muted small">Jumlah Alat Terdaftar:</span>
        <span class="fw-bold fs-5 text-success"><?= $total_medik ?> Unit</span>
      </div>

      <div class="d-flex gap-2">
        <a href="index.php?kategori=Medik" class="btn-custom btn-custom-primary flex-grow-1 text-center">
          <i class="bi bi-eye me-1"></i> Buka Aset Medik
        </a>
        <a href="add.php?kategori=Medik" class="btn-custom btn-custom-outline-primary" title="Tambah Aset Medik">
          <i class="bi bi-plus-lg"></i> Tambah
        </a>
      </div>
    </div>
  </div>

  <!-- Kategori 2: Aset Non Medik -->
  <div class="col-12 col-lg-6">
    <div class="card border-light shadow-sm p-4 h-100 d-flex flex-column">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="p-3 rounded-3 bg-info-subtle text-info">
          <i class="bi bi-building-gear fs-2"></i>
        </div>
        <div>
          <span class="badge-table pending mb-1">Kategori Non Medik</span>
          <h4 class="fw-bold mb-0">Aset Non Medik</h4>
        </div>
      </div>

      <p class="text-muted mb-4 flex-grow-1">
        Meliputi sarana dan prasarana penunjang operasional, fasilitas gedung, kelistrikan, perlengkapan ruangan, serta teknologi informasi rumah sakit.
        Contoh: <em>AC Sentral, Genset, Bed Pasien, Komputer SIMRS, Kursi Roda, Meja Operasi</em>.
      </p>

      <div class="p-3 rounded bg-light mb-3 d-flex justify-content-between align-items-center">
        <span class="text-muted small">Jumlah Fasilitas Terdaftar:</span>
        <span class="fw-bold fs-5 text-info"><?= $total_non_medik ?> Unit</span>
      </div>

      <div class="d-flex gap-2">
        <a href="index.php?kategori=Non Medik" class="btn-custom btn-custom-primary flex-grow-1 text-center">
          <i class="bi bi-eye me-1"></i> Buka Aset Non Medik
        </a>
        <a href="add.php?kategori=Non Medik" class="btn-custom btn-custom-outline-primary" title="Tambah Aset Non Medik">
          <i class="bi bi-plus-lg"></i> Tambah
        </a>
      </div>
    </div>
  </div>
</div>
<!-- END: Detail Kategori Cards -->

<!-- START: Tabel Ringkasan Kategori -->
<div class="table-card-custom mb-4">
  <div class="table-header-control d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h5 class="fw-bold mb-0">Tabel Rangkuman Kategori</h5>
      <small class="text-muted">Ringkasan pengelompokan aset di SIMRS</small>
    </div>
    <a href="index.php" class="btn-custom btn-custom-outline-secondary btn-custom-sm">
      <i class="bi bi-list-ul me-1"></i> Lihat Seluruh Aset Kalibrator
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-custom">
      <thead>
        <tr>
          <th style="width: 70px;">No</th>
          <th>Nama Kategori</th>
          <th>Tipe / Deskripsi</th>
          <th class="text-center" style="width: 150px;">Total Unit</th>
          <th class="text-center" style="width: 180px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-heart-pulse-fill text-success fs-5"></i>
              <strong>Aset Medik</strong>
            </div>
          </td>
          <td>Peralatan kesehatan, elektromedis, dan instrumen klinis</td>
          <td class="text-center"><span class="badge-table success"><?= $total_medik ?> Unit</span></td>
          <td class="text-center">
            <a href="index.php?kategori=Medik" class="btn-custom btn-custom-primary btn-custom-sm">
              <i class="bi bi-arrow-right-circle me-1"></i> Lihat Data
            </a>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-box-seam-fill text-info fs-5"></i>
              <strong>Aset Non Medik</strong>
            </div>
          </td>
          <td>Sarana, prasarana, fasilitas gedung, dan perlengkapan umum</td>
          <td class="text-center"><span class="badge-table pending"><?= $total_non_medik ?> Unit</span></td>
          <td class="text-center">
            <a href="index.php?kategori=Non Medik" class="btn-custom btn-custom-primary btn-custom-sm">
              <i class="bi bi-arrow-right-circle me-1"></i> Lihat Data
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<!-- END: Tabel Ringkasan Kategori -->

<?php
// Load footer template
include_once("footer.php");
?>
