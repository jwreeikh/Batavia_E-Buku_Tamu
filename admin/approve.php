<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
require_once '../config/log.php';
/** @var mysqli $conn */

requireRole(['resepsionis'], 'dashboard.php');

$id = getIntParam($_GET, 'id');
$aksi = $_GET['aksi'] ?? '';
$asal = $_GET['asal'] ?? 'tamu';
$kat_asal = validGuestCategory($_GET['kategori'] ?? $asal);

$allowedActions = [
    'terima' => 'diterima',
    'tolak' => 'ditolak',
    'progres' => 'progres',
    'selesai' => 'selesai',
    'batal_selesai' => 'progres',
];

if (!$id || !isset($allowedActions[$aksi])) {
    $_SESSION['error'] = 'Aksi tidak valid atau ID tamu tidak ditemukan.';
    header('Location: ' . redirectToGuestPage($asal, $kat_asal));
    exit;
}

$res_guest = mysqli_query($conn, "SELECT id, nama, kategori_tamu, sub_kategori, status, file_pdf FROM tamu WHERE id = " . (int)$id . " LIMIT 1");
$guest = mysqli_fetch_assoc($res_guest);

if (!$guest) {
    $_SESSION['error'] = 'Data tamu tidak ditemukan.';
    header('Location: ' . redirectToGuestPage($asal, $kat_asal));
    exit;
}

$kategori = validGuestCategory($guest['kategori_tamu']);
$targetStatus = $allowedActions[$aksi];
$currentStatus = $guest['status'];
$canUpdate = false;
$denyMessage = 'Status tidak berubah karena aksi tersebut tidak sesuai dengan status tamu saat ini.';

if (in_array($aksi, ['terima', 'tolak'], true)) {
    $canUpdate = ($currentStatus === 'pending');
    if ($kategori === 'instansi' && empty($guest['file_pdf'])) {
        $canUpdate = false;
        $denyMessage = 'Upload PDF wajib dilakukan sebelum data instansi dapat diterima atau ditolak.';
    }
} elseif ($aksi === 'progres') {
    $canUpdate = ($kategori === 'siswa' && ($guest['sub_kategori'] ?? '') === 'legalisir' && $currentStatus !== 'selesai');
} elseif ($aksi === 'selesai') {
    $canUpdate = ($kategori === 'siswa' && ($guest['sub_kategori'] ?? '') === 'legalisir' && $currentStatus === 'progres');
} elseif ($aksi === 'batal_selesai') {
    $canUpdate = ($kategori === 'siswa' && ($guest['sub_kategori'] ?? '') === 'legalisir' && $currentStatus === 'selesai');
}

if (!$canUpdate) {
    $_SESSION['error'] = $denyMessage;
    header('Location: ' . redirectToGuestPage($asal, $kat_asal));
    exit;
}

$ok = mysqli_query($conn, "UPDATE tamu SET status = '" . mysqli_real_escape_string($conn, $targetStatus) . "' WHERE id = " . (int)$id . " LIMIT 1");

if ($ok) {
    $meta = statusMeta($targetStatus);
    logAktivitas($conn, $_SESSION['user_id'], "Mengubah status tamu ID $id ({$guest['nama']}) menjadi {$meta['label']}");
    $_SESSION['success'] = 'Status tamu berhasil diperbarui menjadi ' . $meta['label'] . '.';
} else {
    $_SESSION['error'] = 'Gagal memperbarui status tamu.';
}

header('Location: ' . redirectToGuestPage($asal, $kat_asal));
exit;
?>
