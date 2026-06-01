<?php
$current = basename($_SERVER['PHP_SELF']);
$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
?>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar sidenav navbar navbar-vertical navbar-expand-xs fixed-start" id="sidenav-main">
    <div class="text-center py-3">
        <img src="../assets/img/logoo.png" style="max-width:140px;">
    </div>

    <hr>

    <ul class="nav submenu">

        <li class="nav-item">
            <a class="nav-link <?= ($current == 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= (in_array($current, ['data-instansi.php', 'data-umum.php', 'data-siswa.php'])) ? 'active' : '' ?>"
                data-bs-toggle="collapse" href="#collapseKunjungan" role="button" aria-expanded="false">
                <i class="bi bi-journal-text me-2"></i>Data Kunjungan
            </a>
            <div class="collapse <?= (in_array($current, ['data-instansi.php', 'data-umum.php', 'data-siswa.php'])) ? 'show' : '' ?>" id="collapseKunjungan">
                <ul class="nav ms-4">

                    <li class="nav-item">
                        <a class="nav-link <?= ($current == 'data-instansi.php') ? 'active' : '' ?>" href="data-instansi.php">
                            <i class="bi bi-building me-2"></i>Tamu Instansi
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current == 'data-umum.php') ? 'active' : '' ?>" href="data-umum.php">
                            <i class="bi bi-people me-2"></i>Tamu Umum
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current == 'data-siswa.php') ? 'active' : '' ?>" href="data-siswa.php">
                            <i class="bi bi-mortarboard me-2"></i>Alumni / Siswa
                        </a>
                    </li>

                </ul>
            </div>
        </li>

        <?php if ($role == 'resepsionis' or $role == 'admin') : ?>

<li class="nav-item">
    <a class="nav-link <?= ($current == 'tracking-kepsek.php') ? 'active' : '' ?>"
       href="tracking-kepsek.php">

        <i class="bi bi-person-badge me-2"></i>
        Tracking Kepala Sekolah

    </a>
</li>

<?php endif; ?>

        <li class="nav-item">
            <a class="nav-link <?= ($current == 'data-review.php') ? 'active' : '' ?>" href="data-review.php"><i class="bi bi-star me-2"></i>Data Review</a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= ($current == 'laporan.php') ? 'active' : '' ?>" href="laporan.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan</a>
        </li>

        <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($current == 'log-activity.php') ? 'active' : '' ?>" href="log-activity.php">
                    <i class="bi bi-clock-history me-2"></i>Log Aktivitas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current == 'management-user.php') ? 'active' : '' ?>" href="management-user.php">
                    <i class="bi bi-person-gear me-2"></i>Management User
                </a>
            </li>
        <?php endif; ?>
    </ul>

</aside>

<!-- MAIN -->
<main class="main-content position-relative pt-4">

    <nav class="navbar navbar-main px-3 d-flex justify-content-between align-items-center">

        <!-- KIRI -->
        <div class="d-flex align-items-center gap-3">

            <!-- HAMBURGER -->
            <button id="toggleSidebar" class="btn custom-soft-btn" type="button" aria-label="Buka menu" aria-expanded="false">
                <i class="bi bi-list fs-4"></i>
            </button>

            <!-- JUDUL -->
            <h6 class="mb-0 text-dark fw-bold">
    <?php
        $titles = [
            'dashboard.php' => 'Dashboard',
            'data-instansi.php' => 'Tamu Instansi',
            'data-umum.php' => 'Tamu Umum',
            'data-siswa.php' => 'Alumni / Siswa',
            'tracking-kepsek.php' => 'Tracking Kepala Sekolah',
            'data-review.php' => 'Data Review',
            'laporan.php' => 'Laporan',
            'management-user.php' => 'Management User',
            'log-activity.php' => 'Log Aktivitas',
            'profile.php' => 'Profil'
        ];

        echo $titles[$current] ?? ucfirst(str_replace(['-', '.php'], [' ', ''], $current));
    ?>
</h6>

        </div>

        <div class="dropdown">
            <button class="btn custom-soft-btn d-flex align-items-center gap-2"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">

                <i class="bi bi-person-circle"></i>
                <?= $username ?>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li>
                    <a class="dropdown-item" href="profile.php">
                        <i class="bi bi-person me-2"></i> Profil
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>



    </nav>

    <div class="container-fluid py-4">