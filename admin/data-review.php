<?php
require_once '../config/app.php';
include '../config/koneksi.php';
/** @var mysqli $conn */

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';

// Ambil semua data review
$query = mysqli_query($conn, "
    SELECT review.*, tamu.nama 
    FROM review
    LEFT JOIN tamu ON review.tamu_id = tamu.id
    ORDER BY review.id DESC
");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    .dataTables_length select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: none !important;
        padding-right: 0.5rem !important;
        text-align: center;
    }
</style>

<div class="card p-4 shadow-lg border-radius-xl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Data Review Tamu</h5>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success text-white border-0"><?= $_SESSION['success'];
                                                                unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-white border-0">
            <?= $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table id="tabelReview" class="table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Rating</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tags</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                while ($r = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td>
                            <p class="text-xs font-weight-bold mb-0 ps-3"><?= $no++; ?></p>
                        </td>
                        <td>
                            <p class="text-sm font-weight-bold mb-0"><?= htmlspecialchars($r['nama'] ?? '-'); ?></p>
                        </td>
                        <td>
                            <?php
                            $rating = (int)$r['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo '<i class="fas fa-star ' . ($i <= $rating ? 'text-warning' : 'text-secondary opacity-3') . ' text-xs"></i>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($r['tags'])) {
                                $tags = explode(',', $r['tags']);
                                foreach (array_slice($tags, 0, 2) as $tag) {
                                    echo '<span class="badge bg-light text-dark border text-xxs me-1">' . htmlspecialchars(trim($tag)) . '</span>';
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?= renderStatusBadge($r['tampil']); ?>
                        </td>
                        <td class="text-center">
                            <div class="action-group">
                                <?php if ($role == 'admin'): ?>
                                    <a href="approve-review.php?id=<?= $r['id']; ?>&aksi=<?= $r['tampil'] == 'ya' ? 'tidak' : 'ya' ?>"
                                        class="guest-action <?= $r['tampil'] == 'ya' ? 'undo' : 'approve' ?>"
                                        onclick="return confirm('Ubah status tampil review?')"
                                        title="<?= $r['tampil'] == 'ya' ? 'Sembunyikan' : 'Tampilkan' ?>">
                                        <i class="fas <?= $r['tampil'] == 'ya' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                    </a>
                                    <a href="hapus-review.php?id=<?= $r['id']; ?>"
                                        class="guest-action reject"
                                        onclick="return confirm('Hapus permanen?')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-secondary text-xs">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelReview').DataTable({
            "info": false,
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data",
                "search": "Cari:",
                "paginate": {
                    "next": ">",
                    "previous": "<"
                }
            },
            "pageLength": 10
        });
    });
</script>


<?php include '../layouts/footer.php'; ?>