<?php
include 'config/koneksi.php';

header('Content-Type: application/json');

$tamu_id = $_POST['tamu_id'] ?? '';
$rating  = $_POST['rating'] ?? '';
$tags    = $_POST['tags'] ?? '';

if (empty($tamu_id) || empty($rating)) {
    echo json_encode([
        'success' => false,
        'message' => 'Data review belum lengkap'
    ]);
    exit;
}

// Gunakan prepared statement
$stmt = mysqli_prepare($conn, "
    INSERT INTO review (tamu_id, rating, tags, tampil) 
    VALUES (?, ?, ?, 'tidak')
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "iis", $tamu_id, $rating, $tags);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Review berhasil disimpan dan menunggu approval'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);