<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
/** @var mysqli $conn */

requireLogin('login.php');
$role = $_SESSION['role'] ?? '';

$kategori = 'instansi';
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
    <h5 class="mb-4">Log Kunjungan: Tamu Instansi</h5>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success text-white" role="alert"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-white" role="alert"><?= e($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table id="tabelInstansi" class="table admin-table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="col-no text-center">No</th>
                    <th>Nama</th>
                    <th>Instansi</th>
                    <th>No HP</th>
                    <th>Menemui</th>
                    <th class="col-date">Tanggal</th>
                    <th class="col-status text-center">Status</th>
                    <th class="col-action text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; $modals = ''; while ($t = mysqli_fetch_assoc($query)) : ?>
                    <?php $modalId = 'detailInstansi' . (int) $t['id']; ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><span class="fw-bold text-dark truncate-text" title="<?= e($t['nama']); ?>"><?= e($t['nama']); ?></span></td>
                        <td><span class="truncate-text" title="<?= e($t['instansi'] ?: '-'); ?>"><?= e($t['instansi'] ?: '-'); ?></span></td>
                        <td class="text-nowrap"><?= e($t['no_hp']); ?></td>
                        <td><span class="truncate-text" title="<?= e(displayPurpose($t)); ?>"><?= e(displayPurpose($t)); ?></span></td>
                        <td class="text-nowrap"><?= e(date('d/m/Y', strtotime($t['tanggal_kunjungan']))); ?></td>
                        <td class="text-center"><?= renderStatusBadge($t['status']); ?></td>
                        <td class="text-center">
                            <div class="action-group">
                                <?php if ($role === 'resepsionis' && $t['status'] === 'pending'): ?>
                                    <?php if (!empty($t['file_pdf'])): ?>
                                        <a href="approve.php?id=<?= (int) $t['id']; ?>&aksi=terima&asal=instansi" class="guest-action approve" title="Setujui"><i class="fas fa-check"></i></a>
                                        <a href="approve.php?id=<?= (int) $t['id']; ?>&aksi=tolak&asal=instansi" class="guest-action reject" title="Tolak"><i class="fas fa-times"></i></a>
                                    <?php else: ?>
                                        <span class="guest-action approve disabled" title="Upload PDF terlebih dahulu"><i class="fas fa-check"></i></span>
                                        <span class="guest-action reject disabled" title="Upload PDF terlebih dahulu"><i class="fas fa-times"></i></span>
                                    <?php endif; ?>
                                <?php endif; ?> 
                                <?php if ($role === 'resepsionis'): ?>
                                    <a href="upload-pdf.php?id=<?= (int) $t['id']; ?>"
                                       class="guest-action pdf <?= !empty($t['file_pdf']) ? 'uploaded' : ''; ?>"
                                       title="<?= !empty($t['file_pdf']) ? 'Lihat/Ganti PDF' : 'Upload PDF'; ?>">
                                       <i class="fas fa-file-pdf"></i>
                                    </a>
                                <?php elseif ($role === 'tu' && !empty($t['file_pdf'])): ?>
                                    <a href="upload-pdf.php?id=<?= (int) $t['id']; ?>&view=1"
                                       target="_blank"
                                       class="guest-action pdf uploaded"
                                       title="Lihat PDF">
                                       <i class="fas fa-file-pdf"></i>
                                    </a>
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
                                    <h5 class="modal-title">Detail Tamu Instansi</h5>
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
    $('#tabelInstansi').DataTable({
        pageLength: 10,
        info: false,
        autoWidth: false,
        searchDelay: 120,
        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            search: 'Cari:',
            searchPlaceholder: 'Nama, instansi, no HP, status...',
            zeroRecords: 'Data tidak ditemukan',
            paginate: { next: '>', previous: '<' }
        },
        dom: "<'d-flex justify-content-between mb-3 dt-toolbar'<'length_wrap'l><'filter_wrap'f>>rt<'row mt-3'<'col-sm-12'p>>"
    });
});
</script>
<?php include '../layouts/footer.php'; ?>
