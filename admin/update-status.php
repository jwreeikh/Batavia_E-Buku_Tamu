<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
/** @var mysqli $conn */

$id = getIntParam($_GET, 'id');
$status = $_GET['status'] ?? '';
$asal = $_GET['asal'] ?? 'siswa';

$aksi = $status === 'selesai' ? 'selesai' : ($status === 'progres' ? 'batal_selesai' : '');

header('Location: approve.php?id=' . $id . '&aksi=' . urlencode($aksi) . '&asal=' . urlencode($asal) . '&kategori=siswa');
exit;
?>
