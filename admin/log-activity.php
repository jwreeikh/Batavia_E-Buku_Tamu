<?php
require_once '../config/app.php';
include '../config/koneksi.php';
include '../config/log.php';
/** @var mysqli $conn */

/* Hanya super admin */
if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

/* Include layout */
include '../layouts/header.php';
include '../layouts/sidebar.php';

/* ================= QUERY SEMUA DATA ================= */
// Kita ambil semua data agar DataTables bisa melakukan pencarian (termasuk pencarian tanggal) di client-side
$query = mysqli_query($conn, "
    SELECT log_aktivitas.*, users.username 
    FROM log_aktivitas
    JOIN users ON users.id = log_aktivitas.user_id
    ORDER BY log_aktivitas.created_at DESC
");
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid py-1">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary shadow-lg border-0">
                <div class="card-body p-4">
                    <h5 class="text-white mb-1">
                        <i class="fas fa-chart-line me-2"></i> Log Aktivitas
                    </h5>
                    <p class="text-white opacity-8 mb-0">
                        Riwayat aktivitas pengguna dalam sistem. Gunakan kolom pencarian untuk memfilter data.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-radius-xl">
        <div class="card-header pb-0 p-3">
            <h6 class="mb-0">Daftar Aktivitas</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive p-4">
                <table id="tabelLog" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 5%;">No</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu & Tanggal</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User Pengguna</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aktivitas Sistem</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)) : ?>
                            <tr>
                                <td class="text-center text-xs"><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-xs font-weight-bold"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                        <span class="text-xxs text-muted"><?= date('H:i:s', strtotime($row['created_at'])) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-gradient-dark me-2">
                                            <span class="text-white text-xs"><?= strtoupper(substr($row['username'], 0, 1)); ?></span>
                                        </div>
                                        <span class="text-xs font-weight-bold"><?= htmlspecialchars($row['username']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs mb-0 cell-truncate" style="max-width: 350px;" data-bs-toggle="tooltip" title="<?= htmlspecialchars($row['aktivitas']); ?>">
                                        <?= htmlspecialchars($row['aktivitas']); ?>
                                    </p>
                                </td>
                                <td class="text-center">
                                    <?= renderStatusBadge('tercatat'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#tabelLog').DataTable({
        "info": false, // Menghilangkan "Showing 1 to X..."
        "language": {
            "lengthMenu": "Tampilkan _MENU_ data",
            "search": "Cari (User/Tanggal/Aktivitas):",
            "zeroRecords": "Data aktivitas tidak ditemukan",
            "paginate": {
                "next": ">",
                "previous": "<"
            }
        },
        "lengthMenu": [ [10, 25, 50, 100], [10, 25, 50, 100] ],
        "pageLength": 10,
        "order": [[ 0, "asc" ]] 
    });
});
</script>

<style>
    /* Styling agar seragam dengan Data Review */
    .dataTables_length select {
        appearance: none !important;
        -webkit-appearance: none !important;
        background-image: none !important;
        padding: 5px 10px !important;
        border-radius: 8px !important;
        text-align: center;
        border: 1px solid #d2d6da !important;
    }
    .dataTables_filter input {
        border: 1px solid #d2d6da !important;
        border-radius: 8px !important;
        padding: 4px 10px !important;
        margin-left: 10px;
    }
    .cell-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
    #tabelLog thead th {
        padding-left: 1.5rem !important;
    }
</style>

<?php include '../layouts/footer.php'; ?>