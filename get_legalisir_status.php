<?php
include 'config/koneksi.php';
header('Content-Type: application/json');

$nisn = preg_replace('/\D+/', '', $_GET['nisn'] ?? '');

if (!preg_match('/^\d{10}$/', $nisn)) {
    echo json_encode(['success' => false, 'message' => 'NISN harus 10 digit angka.']);
    exit;
}

$stmt = mysqli_prepare($conn, "
    SELECT nama, nisn, status, tanggal_kunjungan
    FROM tamu
    WHERE nisn = ?
    AND kategori_tamu = 'siswa'
    AND sub_kategori = 'legalisir'
    AND (status = 'progres' OR status = 'selesai')
    ORDER BY tanggal_kunjungan DESC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 's', $nisn);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

if ($query && mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data legalisir tidak ditemukan atau belum selesai.']);
}
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>
