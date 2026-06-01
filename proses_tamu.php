<?php
// Pindahkan ke paling atas untuk mencegah kebocoran output
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'config/app.php';
include 'config/koneksi.php';

// Validasi koneksi agar tidak menyebabkan Fatal Error pada fungsi string di bawah
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyambung ke database: ' . mysqli_connect_error()]);
    exit;
}

$kategori_tamu = validGuestCategory($_POST['kategori_tamu'] ?? 'umum');
$nama          = trim($_POST['nama'] ?? '');
$no_hp         = preg_replace('/\D+/', '', $_POST['no_hp'] ?? '');
$tanggal       = $_POST['tanggal_kunjungan'] ?? date('Y-m-d');

// Input tambahan sesuai kategori
$instansi      = trim($_POST['instansi'] ?? '');
$nisn          = preg_replace('/\D+/', '', $_POST['nisn'] ?? '');
$universitas   = trim($_POST['universitas'] ?? '');
$sub_kategori  = in_array(($_POST['sub_kategori'] ?? ''), ['legalisir', 'biasa'], true) ? $_POST['sub_kategori'] : '';

// Field tujuan layanan dan keperluan
$tujuan_id     = filter_var($_POST['tujuan_id'] ?? '', FILTER_VALIDATE_INT) ?: null; // Untuk umum/instansi
$tujuan        = trim($_POST['tujuan'] ?? ''); // Keperluan text untuk umum/instansi
$tujuan_id_siswa = filter_var($_POST['tujuan_id_siswa'] ?? '', FILTER_VALIDATE_INT) ?: null; // Untuk siswa biasa
$tujuan_siswa  = trim($_POST['tujuan_siswa'] ?? ''); // Keperluan text untuk siswa biasa

// Logika status dan keperluan otomatis
$status        = 'pending';
$keperluan     = '';

// ==================== VALIDASI SERVER-SIDE ====================

$errors = [];

// Validasi Nama
if (empty($nama)) {
    $errors[] = ['field' => 'nama', 'message' => "Nama tidak boleh kosong"];
} elseif (strlen($nama) > 60) {
    $errors[] = ['field' => 'nama', 'message' => "Nama maksimal 60 karakter"];
} elseif (!preg_match("/^[\p{L}\s.,'\-]+$/u", $nama)) {
    $errors[] = ['field' => 'nama', 'message' => "Nama hanya boleh huruf, spasi, titik, koma, petik, dan strip"];
}

// Validasi No HP
if (empty($no_hp)) {
    $errors[] = ['field' => 'nohp', 'message' => "Nomor HP tidak boleh kosong"];
} elseif (!preg_match('/^\d{10,13}$/', $no_hp)) {
    $errors[] = ['field' => 'nohp', 'message' => "Nomor HP harus berupa angka 10 - 13 digit"];
}

// Validasi per kategori
if ($kategori_tamu === 'siswa') {
    if (empty($nisn)) {
        $errors[] = ['field' => 'nisn', 'message' => "NISN wajib diisi"];
    }
    // NISN harus 10 digit angka
    if (!empty($nisn) && (!preg_match('/^\d{10}$/', $nisn))) {
        $errors[] = ['field' => 'nisn', 'message' => "NISN harus tepat 10 digit angka"];
    }
    if (empty($sub_kategori)) {
        $errors[] = ['field' => 'sub_kategori', 'message' => "Pilih sub kategori (Legalisir atau Biasa)"];
    }
    
    if ($sub_kategori === 'legalisir') {
        $status = 'progres';
        $keperluan = 'legalisir';
        // Untuk legalisir, tujuan_id dan tujuan (keperluan text) tidak diperlukan
        $tujuan_id = null;
        $tujuan = ''; // Gunakan string kosong, jangan NULL untuk menghindari warning pada fungsi string
        $tujuan_id_siswa = null;
        $tujuan_siswa = '';
    } elseif ($sub_kategori === 'biasa') {
        $status = 'pending';
        $keperluan = 'biasa';
        // Untuk biasa, tujuan_id_siswa dan tujuan_siswa diperlukan
        if (!$tujuan_id_siswa) {
            $errors[] = ['field' => 'tujuan_id_siswa', 'message' => "Tujuan Layanan tidak boleh kosong"];
        }
        if (empty($tujuan_siswa)) {
            $errors[] = ['field' => 'tujuan_siswa', 'message' => "Keperluan tidak boleh kosong"];
        }
        // Gunakan nilai dari field siswa untuk tujuan_id dan tujuan
        $tujuan_id = $tujuan_id_siswa;
        $tujuan = $tujuan_siswa;
    }
} else {
    // Validasi untuk Umum / Instansi
    if ($kategori_tamu === 'instansi' && empty($instansi)) {
        $errors[] = ['field' => 'instansi', 'message' => "Instansi tidak boleh kosong"];
    } elseif ($kategori_tamu === 'instansi' && !preg_match("/^[\p{L}\p{N}\s.,'&()\-\/]+$/u", $instansi)) {
        $errors[] = ['field' => 'instansi', 'message' => "Instansi mengandung karakter yang tidak valid"];
    }

    if (!$tujuan_id) {
        $errors[] = ['field' => 'tujuan_id', 'message' => "Tujuan Layanan tidak boleh kosong"];
    }
    if (empty($tujuan)) {
        $errors[] = ['field' => 'tujuan', 'message' => "Keperluan tidak boleh kosong"];
    }
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $errors[] = ['field' => 'tanggal_kunjungan', 'message' => "Tanggal kunjungan tidak valid"];
}

if ($universitas !== '' && !preg_match("/^[\p{L}\p{N}\s.,'&()\-\/]+$/u", $universitas)) {
    $errors[] = ['field' => 'universitas', 'message' => "Universitas mengandung karakter yang tidak valid"];
}

$shouldValidateTujuan = $kategori_tamu !== 'siswa' || $sub_kategori === 'biasa';
$shouldValidateTujuanSiswa = $kategori_tamu === 'siswa' && $sub_kategori === 'biasa';

if ($shouldValidateTujuan && $tujuan !== null && $tujuan !== '' && !preg_match("/^[\p{L}\p{N}\s.,'&()\-\/]+$/u", $tujuan)) {
    $errors[] = ['field' => 'tujuan', 'message' => "Keperluan mengandung karakter yang tidak valid"];
}

if ($shouldValidateTujuanSiswa && $tujuan_siswa !== '' && !preg_match("/^[\p{L}\p{N}\s.,'&()\-\/]+$/u", $tujuan_siswa)) {
    $errors[] = ['field' => 'tujuan_siswa', 'message' => "Keperluan mengandung karakter yang tidak valid"];
}

// Jika ada error, kembalikan response
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Validasi gagal',
        'errors' => $errors
    ]);
    exit;
}


// ==================== SIMPAN DATA ====================

$query = "INSERT INTO tamu (
    kategori_tamu,
    sub_kategori,
    nama,
    nisn,
    instansi,
    universitas,
    no_hp,
    tujuan_id,
    tujuan,
    status,
    keperluan,
    tanggal_kunjungan
) VALUES (
    '" . mysqli_real_escape_string($conn, $kategori_tamu) . "',
    '" . mysqli_real_escape_string($conn, $sub_kategori) . "',
    '" . mysqli_real_escape_string($conn, $nama) . "',
    '" . mysqli_real_escape_string($conn, $nisn) . "',
    '" . mysqli_real_escape_string($conn, $instansi) . "',
    '" . mysqli_real_escape_string($conn, $universitas) . "',
    '" . mysqli_real_escape_string($conn, $no_hp) . "',
    " . ($tujuan_id ? (int)$tujuan_id : "NULL") . ",
    '" . mysqli_real_escape_string($conn, $tujuan) . "',
    '" . mysqli_real_escape_string($conn, $status) . "',
    '" . mysqli_real_escape_string($conn, $keperluan) . "',
    '" . mysqli_real_escape_string($conn, $tanggal) . "'
)";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        'success' => true,
        'message' => 'Data kunjungan berhasil disimpan',
        'tamu_id' => mysqli_insert_id($conn)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan data: ' . mysqli_error($conn)
    ]);
}
