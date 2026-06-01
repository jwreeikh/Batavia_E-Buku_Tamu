<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
/** @var mysqli $conn */

requireLogin('login.php');
$role = $_SESSION['role'] ?? '';

$kategori = 'siswa';
$query = mysqli_query($conn, "
    SELECT tamu.*, tl.nama_tujuan
    FROM tamu
    LEFT JOIN tujuan_layanan tl ON tamu.tujuan_id = tl.id
    WHERE tamu.kategori_tamu = '" . mysqli_real_escape_string($conn, $kategori) . "'
    ORDER BY tamu.id DESC
");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card p-4 shadow-lg border-radius-xl">
    <h5 class="mb-4">Log Kunjungan: Alumni / Siswa</h5>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success text-white" role="alert"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-white" role="alert"><?= e($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table id="tabelSiswa" class="table admin-table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="col-no text-center">No</th>
                    <th>Nama</th>
                    <th>NISN</th>
                    <th>Universitas</th>
                    <th>No HP</th>
                    <th>Sub Kategori</th>
                    <th class="col-date">Tanggal</th>
                    <th class="col-status text-center">Status</th>
                    <th class="col-action text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; $modals = ''; while ($t = mysqli_fetch_assoc($query)) : ?>
                    <?php $modalId = 'detailSiswa' . (int) $t['id']; ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><span class="fw-bold text-dark truncate-text" title="<?= e($t['nama']); ?>"><?= e($t['nama']); ?></span></td>
                        <td class="text-nowrap"><?= e($t['nisn'] ?: '-'); ?></td>
                        <td><span class="truncate-text" title="<?= e($t['universitas'] ?: '-'); ?>"><?= e($t['universitas'] ?: '-'); ?></span></td>
                        <td class="text-nowrap"><?= e($t['no_hp']); ?></td>
                        <td><?= e(ucfirst($t['sub_kategori'] ?: '-')); ?></td>
                        <td class="text-nowrap"><?= e(date('d/m/Y', strtotime($t['tanggal_kunjungan']))); ?></td>
                        <td class="text-center"><?= renderStatusBadge($t['status']); ?></td>
                        <td class="text-center">
                            <div class="action-group">
                                <?php if ($role === 'resepsionis'): ?>
                                    <?php if (($t['sub_kategori'] ?? '') === 'legalisir'): ?>
                                        <?php if ($t['status'] === 'progres'): ?>
                                            <a href="approve.php?id=<?= (int) $t['id']; ?>&aksi=selesai&asal=siswa&kategori=siswa" class="guest-action finish" title="Tandai Selesai"><i class="fas fa-flag-checkered"></i></a>
                                        <?php elseif ($t['status'] === 'selesai'): ?>
                                            <a href="approve.php?id=<?= (int) $t['id']; ?>&aksi=batal_selesai&asal=siswa&kategori=siswa" class="guest-action undo" title="Kembalikan Progres"><i class="fas fa-undo"></i></a>
                                        <?php endif; ?>
                                    <?php elseif ($t['status'] === 'pending'): ?>
                                        <a href="approve.php?id=<?= (int) $t['id']; ?>&aksi=terima&asal=siswa&kategori=siswa" class="guest-action approve" title="Setujui"><i class="fas fa-check"></i></a>
                                        <a href="approve.php?id=<?= (int) $t['id']; ?>&aksi=tolak&asal=siswa&kategori=siswa" class="guest-action reject" title="Tolak"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <button type="button" class="guest-action detail" data-bs-toggle="modal" data-bs-target="#<?= e($modalId); ?>" title="Detail"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    </tr>

                    <?php ob_start(); ?>
                    <div class="modal fade" id="<?= e($modalId); ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">Detail Alumni / Siswa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body"><?= guestDetailRows($t); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php $modals .= ob_get_clean(); ?>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $modals; ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    $('#tabelSiswa').DataTable({
        pageLength: 10,
        info: false,
        autoWidth: false,
        searchDelay: 120,
        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            search: 'Cari:',
            searchPlaceholder: 'Nama, universitas, NISN, status...',
            zeroRecords: 'Data tidak ditemukan',
            paginate: { next: '>', previous: '<' }
        },
        dom: "<'d-flex justify-content-between mb-3 dt-toolbar'<'length_wrap'l><'filter_wrap'f>>rt<'row mt-3'<'col-sm-12'p>>"
    });
});
</script>
<?php include '../layouts/footer.php'; ?>
