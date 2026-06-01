<?php
require_once '../config/app.php';
include '../config/koneksi.php';
include '../config/log.php';
/** @var mysqli $conn */

requireRole(['admin'], 'data-review.php');

$id = getIntParam($_GET, 'id');
if (!$id) {
    $_SESSION['error'] = 'ID review tidak valid.';
    header('Location: data-review.php');
    exit;
}

$ok = mysqli_query($conn, "DELETE FROM review WHERE id = " . (int)$id . " LIMIT 1");

if ($ok) {
    $_SESSION['success'] = 'Review berhasil dihapus.';
    logAktivitas($conn, $_SESSION['user_id'], "Menghapus review ID $id");
} else {
    $_SESSION['error'] = 'Gagal menghapus review.';
}

header('Location: data-review.php');
exit;
?>
