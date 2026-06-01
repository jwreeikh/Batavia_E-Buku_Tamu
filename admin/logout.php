<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
require_once '../config/log.php';
/** @var mysqli $conn */

/* ambil user sebelum session dihapus */
$user_id = $_SESSION['user_id'] ?? null;

/* log dulu */
if ($user_id) {
    logAktivitas($conn, $user_id, "Logout dari sistem");
}

/* baru hapus session */
session_unset();
session_destroy();

header("Location: ../index.php");
exit;