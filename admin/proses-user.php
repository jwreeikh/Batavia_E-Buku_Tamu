<?php
require_once '../config/app.php';
include '../config/koneksi.php';
include '../config/log.php';
/** @var mysqli $conn */

requireRole(['admin'], 'dashboard.php');

function validUserRole($role)
{
    return in_array($role, ['admin', 'resepsionis', 'tu'], true) ? $role : '';
}

if (isset($_POST['tambah'])) {
    $username = trim($_POST['username'] ?? '');
    $passwordRaw = $_POST['password'] ?? '';
    $role = validUserRole($_POST['role'] ?? '');

    if ($username === '' || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username) || $passwordRaw === '' || !$role) {
        $_SESSION['error'] = 'Data user tidak valid. Username 3-50 karakter dan role harus sesuai.';
        header('Location: management-user.php');
        exit;
    }

    $password = md5($passwordRaw);
    $query_tambah = "INSERT INTO users (username, password, role) VALUES ('" . mysqli_real_escape_string($conn, $username) . "', '$password', '" . mysqli_real_escape_string($conn, $role) . "')";
    $insert = mysqli_query($conn, $query_tambah);

    if ($insert) {
        logAktivitas($conn, $_SESSION['user_id'], "Menambah user baru: $username ($role)");
        $_SESSION['success'] = 'User berhasil ditambahkan.';
    } else {
        $_SESSION['error'] = 'Gagal menambah user.';
    }
    header('Location: management-user.php');
    exit;
}

if (isset($_POST['edit'])) {
    $id = getIntParam($_POST, 'id');
    $username = trim($_POST['username'] ?? '');
    $role = validUserRole($_POST['role'] ?? '');
    $passwordRaw = $_POST['password'] ?? '';

    if (!$id || $username === '' || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username) || !$role) {
        $_SESSION['error'] = 'Data user tidak valid.';
        header('Location: management-user.php');
        exit;
    }

    if ($passwordRaw !== '') {
        $password = md5($passwordRaw);
        $query_edit = "UPDATE users SET username = '" . mysqli_real_escape_string($conn, $username) . "', role = '" . mysqli_real_escape_string($conn, $role) . "', password = '$password' WHERE id = " . (int)$id . " LIMIT 1";
    } else {
        $query_edit = "UPDATE users SET username = '" . mysqli_real_escape_string($conn, $username) . "', role = '" . mysqli_real_escape_string($conn, $role) . "' WHERE id = " . (int)$id . " LIMIT 1";
    }

    $update = mysqli_query($conn, $query_edit);

    if ($update) {
        logAktivitas($conn, $_SESSION['user_id'], "Mengubah data user: $username");
        $_SESSION['success'] = 'User berhasil diperbarui.';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui user.';
    }
    header('Location: management-user.php');
    exit;
}

if (isset($_GET['hapus'])) {

    $id = getIntParam($_GET, 'hapus');

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, username, role
         FROM users
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $cek_user = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    if (!$cek_user) {

        $_SESSION['error'] = 'User tidak ditemukan.';
    } elseif ($id == ($_SESSION['user_id'] ?? 0)) {

        $_SESSION['error'] = 'Anda tidak dapat menghapus akun yang sedang digunakan untuk login!';
    } else {

        $username = strtolower(trim($cek_user['username']));
        $role = strtolower(trim($cek_user['role']));

        // Proteksi akun inti sistem
        if (
            ($username === 'admin' && $role === 'admin') ||
            ($username === 'resepsionis' && $role === 'resepsionis') ||
            ($username === 'tata_usaha' && $role === 'tu')
        ) {

            $_SESSION['error'] = 'Akun utama sistem tidak dapat dihapus!';
        } else {

            // Putus relasi log aktivitas terlebih dahulu
            mysqli_query(
                $conn,
                "UPDATE log_aktivitas
                 SET user_id = NULL
                 WHERE user_id = " . (int)$id
            );

            $stmt = mysqli_prepare(
                $conn,
                "DELETE FROM users
                 WHERE id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, 'i', $id);

            $hapus = mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

            if ($hapus) {

                logAktivitas(
                    $conn,
                    $_SESSION['user_id'],
                    "Menghapus user: " . $cek_user['username']
                );

                $_SESSION['success'] = 'User berhasil dihapus.';
            } else {

                $_SESSION['error'] =
                    'Gagal menghapus user: ' . mysqli_error($conn);
            }
        }
    }

    header('Location: management-user.php');
    exit;
}

header('Location: management-user.php');
exit;
