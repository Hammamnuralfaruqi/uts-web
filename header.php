<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi default judul dan menu jika belum diset
if (!isset($page_title)) {
    $page_title = "Data Alat & Aset Kalibrator";
}
if (!isset($page_subtitle)) {
    $page_subtitle = "Sistem Informasi Manajemen Rumah Sakit (SIMRS)";
}
if (!isset($active_menu)) {
    $active_menu = "semua_aset";
}

// Ambil ID pengguna dari sesi
$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Default data pengguna dari session
$userNama   = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
$userEmail  = isset($_SESSION['email']) ? $_SESSION['email'] : 'admin@simrs.local';
$userFoto   = isset($_SESSION['foto']) && !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'assets/images/avatar.png';
$userStatus = isset($_SESSION['status_profile']) && !empty($_SESSION['status_profile']) ? $_SESSION['status_profile'] : 'Pebisnis';

// Sinkronkan data pengguna secara langsung dari database jika koneksi tersedia
if (isset($mysqli) && $userId > 0) {
    $q_user = mysqli_query($mysqli, "SELECT nama, email, foto, status_profile FROM users WHERE id = $userId LIMIT 1");
    if ($q_user && $row_u = mysqli_fetch_assoc($q_user)) {
        $userNama   = $row_u['nama'];
        $userEmail  = $row_u['email'];
        if (!empty($row_u['foto'])) {
            $userFoto = $row_u['foto'];
        }
        if (!empty($row_u['status_profile'])) {
            $userStatus = $row_u['status_profile'];
        }
        // Update session agar selalu terbaru
        $_SESSION['nama']           = $userNama;
        $_SESSION['email']          = $userEmail;
        $_SESSION['foto']           = $userFoto;
        $_SESSION['status_profile'] = $userStatus;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> - SIMRS</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="assets/images/favicon.ico">

  <!-- Local Third-Party Libraries -->
  <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/libs/bootstrap-icons/bootstrap-icons.css">

  <!-- Main Design System & Custom Stylesheet -->
  <link rel="stylesheet" href="assets/css/main.css">

  <style>
    /* Styling tambahan untuk submenu & status profil */
    .sidebar-submenu-link {
      display: flex;
      align-items: center;
      text-decoration: none;
    }
    .sidebar-submenu-link.active {
      color: var(--brand-lime, #b4f105) !important;
      font-weight: 600;
    }
    .sidebar-submenu-link.active i {
      color: var(--brand-lime, #b4f105) !important;
    }
    .profile-status-badge {
      display: inline-block;
      font-size: 0.68rem;
      font-weight: 600;
      padding: 0.15rem 0.5rem;
      border-radius: 50rem;
      background-color: rgba(180, 241, 5, 0.18);
      color: #b4f105;
      border: 1px solid rgba(180, 241, 5, 0.35);
      line-height: 1.2;
    }
    .sidebar-profile {
      transition: background-color 0.2s ease, border-color 0.2s ease;
    }
    .sidebar-profile:hover {
      background-color: rgba(255, 255, 255, 0.08);
      border-color: rgba(180, 241, 5, 0.4);
    }
  </style>
</head>

<body>

  <!-- ==========================================
         START: Sidebar Component
       ========================================== -->
  <div class="sidebar-wrapper" id="sidebar">
    <!-- Brand Logo / Identity -->
    <a href="index.php" class="sidebar-brand">
      <i class="bi bi-hospital text-lime"></i>
      <span>SIMRS Medis</span>
    </a>

    <!-- Navigation Menu -->
    <div class="flex-grow-1 overflow-y-auto">
      <!-- Group: Menu Utama -->
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Menu Utama</div>
        <ul class="sidebar-menu-list">
          
          <!-- 1. Semua Aset Kalibrator / Dashboard -->
          <li class="sidebar-menu-item">
            <a href="index.php" class="sidebar-menu-link <?= (in_array($active_menu, ['semua_aset', 'data_alat'])) ? 'active' : '' ?>" title="Semua Data Aset Kalibrator">
              <i class="bi bi-grid-fill"></i>
              <span>Semua Data Aset Kalibrator</span>
            </a>
          </li>

          <!-- 2. Lihat Semua Kategori -->
          <li class="sidebar-menu-item">
            <a href="kategori.php" class="sidebar-menu-link <?= ($active_menu === 'kategori') ? 'active' : '' ?>" title="Lihat Semua Kategori">
              <i class="bi bi-tags-fill"></i>
              <span>Lihat Semua Kategori</span>
            </a>
          </li>

          <!-- 3. Aset (Dropdown Submenu: Medik & Non Medik) -->
          <li class="sidebar-menu-item">
            <a href="#submenu-aset" class="sidebar-menu-link <?= in_array($active_menu, ['aset_medik', 'aset_non_medik']) ? 'active' : '' ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?= in_array($active_menu, ['aset_medik', 'aset_non_medik']) ? 'true' : 'false' ?>" aria-controls="submenu-aset">
              <i class="bi bi-boxes"></i>
              <span>Aset</span>
              <i class="bi bi-chevron-down dropdown-caret"></i>
            </a>
            <div class="collapse <?= in_array($active_menu, ['aset_medik', 'aset_non_medik']) ? 'show' : '' ?>" id="submenu-aset">
              <ul class="sidebar-submenu">
                <li>
                  <a href="index.php?kategori=Medik" class="sidebar-submenu-link <?= ($active_menu === 'aset_medik') ? 'active' : '' ?>">
                    <i class="bi bi-heart-pulse me-2"></i> Medik
                  </a>
                </li>
                <li>
                  <a href="index.php?kategori=Non Medik" class="sidebar-submenu-link <?= ($active_menu === 'aset_non_medik') ? 'active' : '' ?>">
                    <i class="bi bi-building-gear me-2"></i> Non Medik
                  </a>
                </li>
              </ul>
            </div>
          </li>

          <!-- 4. Tambah Alat / Aset -->
          <li class="sidebar-menu-item">
            <a href="add.php" class="sidebar-menu-link <?= ($active_menu === 'tambah_alat') ? 'active' : '' ?>" title="Tambah Alat Baru">
              <i class="bi bi-plus-circle"></i>
              <span>Tambah Aset</span>
            </a>
          </li>

        </ul>
      </div>

      <!-- Group: Akun & Profil Pengguna -->
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Akun Pengguna</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="profile.php" class="sidebar-menu-link <?= ($active_menu === 'profile') ? 'active' : '' ?>" title="Kelola Profil Saya">
              <i class="bi bi-person-gear"></i>
              <span>Profil Saya</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="logout.php" class="sidebar-menu-link text-danger" title="Keluar dari Sistem" onclick="return confirm('Apakah Anda yakin ingin logout?')">
              <i class="bi bi-box-arrow-right"></i>
              <span>Logout</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Group: Referensi Tema Spark -->
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Referensi Tema</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="tables-basic.html" target="_blank" class="sidebar-menu-link" title="Preview Tabel Tema">
              <i class="bi bi-table"></i>
              <span>Preview Tabel</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="ui-forms.html" target="_blank" class="sidebar-menu-link" title="Preview Form Tema">
              <i class="bi bi-input-cursor-text"></i>
              <span>Preview Form</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Sidebar Profile Card (Bisa diklik untuk buka profil) -->
    <a href="profile.php" class="sidebar-profile text-decoration-none" title="Klik untuk mengedit profil Anda">
      <img src="<?= htmlspecialchars($userFoto) ?>" alt="<?= htmlspecialchars($userNama) ?>" class="sidebar-profile-img object-fit-cover"
        onerror="this.src='assets/images/avatar.png'">
      <div class="sidebar-profile-info">
        <div class="sidebar-profile-name text-truncate" style="max-width: 140px;"><?= htmlspecialchars($userNama) ?></div>
        <div class="mt-1">
          <span class="profile-status-badge text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars($userStatus) ?>">
            <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i><?= htmlspecialchars($userStatus) ?>
          </span>
        </div>
      </div>
    </a>
  </div>
  <!-- ==========================================
         END: Sidebar Component
       ========================================== -->

  <!-- ==========================================
         START: Main Content Area
       ========================================== -->
  <div class="main-wrapper">

    <!-- START: Top Navbar Component -->
    <header class="navbar-custom">
      <div class="navbar-left">
        <!-- Desktop sidebar toggle -->
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
          id="desktop-sidebar-toggle" aria-label="Minimize Sidebar">
          <i class="bi bi-chevron-bar-left"></i>
        </button>
        <!-- Mobile sidebar toggle -->
        <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Toggle Navigation">
          <i class="bi bi-list"></i>
        </button>

        <span class="fw-semibold text-main d-none d-sm-inline">
          <i class="bi bi-heart-pulse me-1 text-lime"></i> Belajar Pemrograman Web - Sistem Aset Rumah Sakit
        </span>
      </div>

      <!-- Right actions -->
      <div class="navbar-actions">
        <!-- Fullscreen Toggle -->
        <button class="navbar-action-btn me-1" aria-label="Toggle Fullscreen" id="btn-fullscreen" title="Layar Penuh">
          <i class="bi bi-arrows-fullscreen"></i>
        </button>

        <!-- Profile Dropdown -->
        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false" id="profile-dropdown">
            <img src="<?= htmlspecialchars($userFoto) ?>" alt="Profile Image" class="navbar-profile-img object-fit-cover" onerror="this.src='assets/images/avatar.png'">
            <div class="d-none d-md-flex flex-column text-start ms-2 me-1">
              <span class="navbar-profile-name"><?= htmlspecialchars($userNama) ?></span>
              <small class="text-success fw-semibold" style="font-size: 0.72rem; line-height: 1;">
                <i class="bi bi-circle-fill" style="font-size: 0.45rem;"></i> <?= htmlspecialchars($userStatus) ?>
              </small>
            </div>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
            <li class="dropdown-header">
              <div class="fw-bold text-main"><?= htmlspecialchars($userNama) ?></div>
              <small class="badge bg-light text-dark border mt-1"><?= htmlspecialchars($userStatus) ?></small>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-gear"></i> Kelola Profil</a></li>
            <li><a class="dropdown-item" href="index.php"><i class="bi bi-grid-fill"></i> Semua Aset Kalibrator</a></li>
            <li><a class="dropdown-item" href="kategori.php"><i class="bi bi-tags-fill"></i> Kategori Aset</a></li>
            <li><a class="dropdown-item" href="add.php"><i class="bi bi-plus-circle"></i> Tambah Aset</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </header>
    <!-- END: Top Navbar Component -->

    <!-- START: Page Header Banner -->
    <div class="page-header">
      <div>
        <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
        <p class="page-subtitle"><?= htmlspecialchars($page_subtitle) ?></p>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted-green">Home</a></li>
          <li class="breadcrumb-item active text-main" aria-current="page"><?= htmlspecialchars($page_title) ?></li>
        </ol>
      </nav>
    </div>
    <!-- END: Page Header Banner -->
