<?php
// Proteksi autentikasi: wajib login
require_once("auth_check.php");

// Hubungkan ke file koneksi database
include_once("config.php");

// Cek filter kategori dari URL (Medik / Non Medik)
$kategoriFilter = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

if (!empty($kategoriFilter)) {
    // Query dengan filter kategori
    $stmt = mysqli_prepare($mysqli, "SELECT * FROM alat WHERE kategori = ? ORDER BY id DESC");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $kategoriFilter);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($mysqli, "SELECT * FROM alat WHERE kategori = '$kategoriFilter' ORDER BY id DESC");
    }

    $page_title = "Data Aset " . htmlspecialchars($kategoriFilter);
    $page_subtitle = "Daftar inventaris aset rumah sakit untuk kategori " . htmlspecialchars($kategoriFilter);
    $active_menu = (strtolower($kategoriFilter) === 'medik') ? 'aset_medik' : 'aset_non_medik';
} else {
    // Query ambil seluruh data alat & aset
    $result = mysqli_query($mysqli, "SELECT * FROM alat ORDER BY id DESC");
    $page_title = "Semua Data Aset Kalibrator";
    $page_subtitle = "Daftar inventaris dan informasi seluruh aset kalibrator rumah sakit (Medik & Non Medik)";
    $active_menu = "semua_aset";
}

// Load template header
include_once("header.php");
?>

<!-- Alert Notifikasi Flash Message -->
<?php if (isset($_GET['msg'])): ?>
  <?php if ($_GET['msg'] === 'added'): ?>
    <div class="alert-custom alert-custom-success mb-4">
      <i class="bi bi-check-circle-fill alert-custom-icon"></i>
      <div class="alert-custom-content">
        <strong>Berhasil!</strong> Data aset baru telah berhasil disimpan ke database.
      </div>
      <button class="alert-custom-close" type="button" aria-label="Close" onclick="this.parentElement.remove();">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  <?php elseif ($_GET['msg'] === 'updated'): ?>
    <div class="alert-custom alert-custom-info mb-4">
      <i class="bi bi-info-circle-fill alert-custom-icon"></i>
      <div class="alert-custom-content">
        <strong>Berhasil!</strong> Data aset telah berhasil diperbarui.
      </div>
      <button class="alert-custom-close" type="button" aria-label="Close" onclick="this.parentElement.remove();">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  <?php elseif ($_GET['msg'] === 'deleted'): ?>
    <div class="alert-custom alert-custom-danger mb-4">
      <i class="bi bi-trash-fill alert-custom-icon"></i>
      <div class="alert-custom-content">
        <strong>Dihapus!</strong> Data aset telah berhasil dihapus.
      </div>
      <button class="alert-custom-close" type="button" aria-label="Close" onclick="this.parentElement.remove();">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  <?php endif; ?>
<?php endif; ?>

<!-- Informasi Filter Aktif (jika ada) -->
<?php if (!empty($kategoriFilter)): ?>
  <div class="d-flex align-items-center justify-content-between p-3 mb-4 rounded bg-light border">
    <div class="d-flex align-items-center gap-2">
      <span class="text-muted small">Filter Kategori Aktif:</span>
      <span class="badge-table <?= (strtolower($kategoriFilter) === 'medik') ? 'success' : 'pending' ?>">
        <?= (strtolower($kategoriFilter) === 'medik') ? '<i class="bi bi-heart-pulse me-1"></i>' : '<i class="bi bi-building-gear me-1"></i>' ?>
        Aset <?= htmlspecialchars($kategoriFilter) ?>
      </span>
    </div>
    <a href="index.php" class="btn-custom btn-custom-outline-secondary btn-custom-sm">
      <i class="bi bi-x-circle me-1"></i> Reset Filter (Lihat Semua)
    </a>
  </div>
<?php endif; ?>

<!-- START: Basic Table Card Container -->
<div class="table-card-custom mb-4">
  <!-- Header Controls -->
  <div class="table-header-control d-flex justify-content-between align-items-center flex-wrap gap-3">
    <!-- Search bar -->
    <div class="table-search-box">
      <i class="bi bi-search table-search-icon"></i>
      <input type="text" id="filterInput" class="table-search-input" placeholder="Cari nama aset, kategori, merek, lokasi...">
    </div>

    <!-- Tombol Tambah Data & Filter Cepat -->
    <div class="d-flex gap-2">
      <div class="dropdown">
        <button class="btn-custom btn-custom-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-funnel me-1"></i> Filter Kategori
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item <?= empty($kategoriFilter) ? 'active' : '' ?>" href="index.php">Semua Kategori</a></li>
          <li><a class="dropdown-item <?= ($kategoriFilter === 'Medik') ? 'active' : '' ?>" href="index.php?kategori=Medik"><i class="bi bi-heart-pulse text-success me-1"></i> Aset Medik</a></li>
          <li><a class="dropdown-item <?= ($kategoriFilter === 'Non Medik') ? 'active' : '' ?>" href="index.php?kategori=Non Medik"><i class="bi bi-building-gear text-info me-1"></i> Aset Non Medik</a></li>
        </ul>
      </div>

      <a href="add.php<?= !empty($kategoriFilter) ? '?kategori=' . urlencode($kategoriFilter) : '' ?>" class="btn-custom btn-custom-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Aset
      </a>
    </div>
  </div>

  <!-- Responsive Table Wrapper -->
  <div class="table-responsive">
    <table class="table-custom" id="dataTable">
      <thead>
        <tr>
          <th style="width: 60px;">No</th>
          <th>Nama Aset</th>
          <th style="width: 140px;">Kategori</th>
          <th style="width: 110px;">Tahun</th>
          <th>Merek</th>
          <th>Lokasi</th>
          <th class="text-center" style="width: 130px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 1;
        $total_rows = ($result) ? mysqli_num_rows($result) : 0;
        if ($total_rows > 0) {
            while ($user_data = mysqli_fetch_array($result)) {
                $kat = isset($user_data['kategori']) && !empty($user_data['kategori']) ? $user_data['kategori'] : 'Medik';
                $isMedik = (strtolower($kat) === 'medik');
        ?>
            <tr>
              <td><span class="fw-semibold text-muted"><?= $i ?></span></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="p-2 rounded bg-light <?= $isMedik ? 'text-success' : 'text-info' ?>">
                    <i class="bi <?= $isMedik ? 'bi-heart-pulse' : 'bi-building-gear' ?> fs-5"></i>
                  </div>
                  <div>
                    <span class="fw-bold text-main d-block"><?= htmlspecialchars($user_data['nama_alat']) ?></span>
                    <small class="text-muted">ID: #<?= $user_data['id'] ?></small>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge-table <?= $isMedik ? 'success' : 'pending' ?>">
                  <?= htmlspecialchars($kat) ?>
                </span>
              </td>
              <td>
                <span class="fw-semibold text-muted small"><?= htmlspecialchars($user_data['tahun']) ?></span>
              </td>
              <td>
                <span class="fw-semibold"><?= htmlspecialchars($user_data['merek']) ?></span>
              </td>
              <td>
                <span class="text-main"><i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($user_data['lokasi']) ?></span>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <a href="edit.php?id=<?= $user_data['id'] ?>" class="table-btn-action" title="Edit Aset">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="delete.php?id=<?= $user_data['id'] ?>" class="table-btn-action delete" title="Hapus Aset" onclick="return confirm('Apakah Anda yakin ingin menghapus data aset: <?= htmlspecialchars(addslashes($user_data['nama_alat'])) ?>?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
        <?php
                $i++;
            }
        } else {
        ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                Belum ada data aset <?= !empty($kategoriFilter) ? 'kategori ' . htmlspecialchars($kategoriFilter) : '' ?>. 
                Silakan <a href="add.php<?= !empty($kategoriFilter) ? '?kategori=' . urlencode($kategoriFilter) : '' ?>" class="text-decoration-underline fw-semibold">tambah data baru</a>.
              </td>
            </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Filter pencarian tabel secara realtime di sisi klien
document.addEventListener('DOMContentLoaded', function() {
  const filterInput = document.getElementById('filterInput');
  const table = document.getElementById('dataTable');
  if (filterInput && table) {
    filterInput.addEventListener('keyup', function() {
      const keyword = this.value.toLowerCase();
      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(function(row) {
        const text = row.innerText.toLowerCase();
        if (text.includes(keyword)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }
});
</script>

<?php
// Load template footer
include_once("footer.php");
?>
