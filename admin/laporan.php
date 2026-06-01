<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
require_once '../config/log.php';
/** @var mysqli $conn */

requireLogin('login.php');

$from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : date('Y-m-01');
$to = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : date('Y-m-d');
$kat = in_array(($_GET['kategori'] ?? ''), ['umum', 'instansi', 'siswa'], true) ? $_GET['kategori'] : '';

$where = [];
$where[] = "tamu.tanggal_kunjungan BETWEEN '" .
           mysqli_real_escape_string($conn, $from) .
           "' AND '" .
           mysqli_real_escape_string($conn, $to) .
           "'";
if ($kat !== '') {
    $where[] = "tamu.kategori_tamu = '" . mysqli_real_escape_string($conn, $kat) . "'";
}

$whereSql = implode(' AND ', $where);

// --- QUERY DATA TAMU ---

$sql = "
    SELECT tamu.*, tl.nama_tujuan
    FROM tamu
    LEFT JOIN tujuan_layanan tl ON tamu.tujuan_id = tl.id
    WHERE $whereSql
    ORDER BY tamu.id DESC
";
$query = mysqli_query($conn, $sql);

// --- QUERY STATISTIK ---

$statsSql = "
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN tamu.status='diterima' THEN 1 ELSE 0 END) as diterima,
        SUM(CASE WHEN tamu.status='ditolak' THEN 1 ELSE 0 END) as ditolak,
        SUM(CASE WHEN tamu.status='pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN tamu.status='progres' THEN 1 ELSE 0 END) as progres,
        SUM(CASE WHEN tamu.status='selesai' THEN 1 ELSE 0 END) as selesai
    FROM tamu
    WHERE $whereSql
";
$statsRes = mysqli_query($conn, $statsSql);
$s = mysqli_fetch_assoc($statsRes);

logAktivitas($conn, $_SESSION['user_id'], "Melihat laporan periode $from s/d $to");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4">
    <div class="card p-3 mb-4 shadow-sm border-radius-xl">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold">Kategori Tamu</label>
                <select name="kategori" class="form-control form-control-sm">
                    <option value="">Semua Kategori</option>
                    <option value="umum" <?= $kat === 'umum' ? 'selected' : '' ?>>Umum</option>
                    <option value="instansi" <?= $kat === 'instansi' ? 'selected' : '' ?>>Instansi</option>
                    <option value="siswa" <?= $kat === 'siswa' ? 'selected' : '' ?>>Siswa/Alumni</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Dari Tanggal</label>
                <input type="date" name="from" value="<?= e($from); ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Sampai Tanggal</label>
                <input type="date" name="to" value="<?= e($to); ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn bg-gradient-primary w-100 btn-sm mb-0">Cari</button>
            </div>
            <div class="col-md-4">
                <a href="export-pdf.php?<?= e(http_build_query($_GET)); ?>" class="btn bg-gradient-danger w-100 btn-sm mb-0">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
            </div>
        </form>
    </div>

    <div class="row mb-4">
        <?php
        $cards = [
            ['Total Tamu', $s['total'] ?? 0, 'text-dark'],
            ['Pending', $s['pending'] ?? 0, 'text-warning'],
            ['Diterima', $s['diterima'] ?? 0, 'text-success'],
            ['Ditolak', $s['ditolak'] ?? 0, 'text-danger'],
            ['Progres', $s['progres'] ?? 0, 'text-info'],
            ['Selesai', $s['selesai'] ?? 0, 'text-success'],
        ];
        ?>
        <?php foreach ($cards as $card): ?>
            <div class="col-6 col-lg-2 mb-2">
                <div class="card text-center p-3 shadow-sm border-radius-lg h-100">
                    <h6 class="text-sm mb-0 text-capitalize font-weight-bold <?= e($card[2]); ?>"><?= e($card[0]); ?></h6>
                    <h3 class="<?= e($card[2]); ?> font-weight-bolder mb-0"><?= (int) $card[1]; ?></h3>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card p-3 shadow-sm border-radius-xl">
        <div class="table-responsive">
            <table id="tabelLaporan" class="table admin-table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="col-no text-center">No</th>
                        <th>Nama</th>
                        <th><?= $kat === 'siswa' ? 'NISN / Univ' : 'Instansi' ?></th>
                        <th>No HP</th>
                        <th>Menemui</th>
                        <th>Keperluan</th>
                        <th class="col-date">Tanggal</th>
                        <th class="col-status text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($t = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><span class="fw-bold text-dark truncate-text" title="<?= e($t['nama']); ?>"><?= e($t['nama']); ?></span></td>
                            <td>
                                <span class="truncate-text" title="<?php
                                    echo e($t['kategori_tamu'] === 'siswa'
                                        ? trim(($t['nisn'] ?: '-') . ($t['universitas'] ? ' / ' . $t['universitas'] : ''))
                                        : ($t['instansi'] ?: '-'));
                                ?>">
                                    <?php
                                    echo e($t['kategori_tamu'] === 'siswa'
                                        ? trim(($t['nisn'] ?: '-') . ($t['universitas'] ? ' / ' . $t['universitas'] : ''))
                                        : ($t['instansi'] ?: '-'));
                                    ?>
                                </span>
                            </td>
                            <td class="text-nowrap"><?= e($t['no_hp']); ?></td>
                            <td><span class="truncate-text" title="<?= e(displayPurpose($t)); ?>"><?= e(displayPurpose($t)); ?></span></td>
                            <td><span class="truncate-text" title="<?= e($t['tujuan'] ?: '-'); ?>"><?= e($t['tujuan'] ?: '-'); ?></span></td>
                            <td class="text-nowrap"><?= e(date('d/m/Y', strtotime($t['tanggal_kunjungan']))); ?></td>
                            <td class="text-center"><?= renderStatusBadge($t['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    $('#tabelLaporan').DataTable({
        info: false,
        pageLength: 10,
        autoWidth: false,
        searchDelay: 120,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            search: 'Cari:',
            searchPlaceholder: 'Nama, instansi, NISN, no HP, status...',
            zeroRecords: 'Data tidak ditemukan',
            paginate: { next: '>', previous: '<' }
        },
        dom: "<'d-flex justify-content-between mb-3 dt-toolbar'<'length_wrap'l><'filter_wrap'f>>rt<'row mt-3'<'col-sm-12'p>>"
    });
});
</script>

<?php include '../layouts/footer.php'; ?>
