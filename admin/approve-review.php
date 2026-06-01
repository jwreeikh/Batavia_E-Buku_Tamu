<?php
require_once '../config/app.php';
include '../config/koneksi.php';
include '../config/log.php';
/** @var mysqli $conn */

requireRole(['admin'], 'data-review.php');

$id = getIntParam($_GET, 'id');
$aksi = in_array(($_GET['aksi'] ?? ''), ['ya', 'tidak'], true) ? $_GET['aksi'] : '';

if (!$id || !$aksi) {
    $_SESSION['error'] = 'ID review atau aksi tidak valid.';
    header('Location: data-review.php');
    exit;
}

if ($aksi === 'ya') {
    $cek = mysqli_query($conn, "SELECT COUNT(*) as total FROM review WHERE tampil = 'ya'");
    $jumlahTampil = (int) (mysqli_fetch_assoc($cek)['total'] ?? 0);

    if ($jumlahTampil >= 6) {
        $_SESSION['error'] = 'Maksimal hanya 6 review yang dapat ditampilkan di beranda.';
        header('Location: data-review.php');
        exit;
    }
}

$ok = mysqli_query($conn, "UPDATE review SET tampil = '" . mysqli_real_escape_string($conn, $aksi) . "' WHERE id = " . (int)$id . " LIMIT 1");

if ($ok) {
    $_SESSION['success'] = $aksi === 'ya' ? 'Review berhasil ditampilkan di beranda.' : 'Review berhasil disembunyikan.';
    logAktivitas($conn, $_SESSION['user_id'] ?? 0, ($aksi === 'ya' ? 'Menampilkan' : 'Menyembunyikan') . " review ID $id");
} else {
    $_SESSION['error'] = 'Gagal mengubah status review.';
}

header('Location: data-review.php');
exit;
?>
