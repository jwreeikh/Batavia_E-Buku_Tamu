<?php
$host = "localhost"; // SESUAI HOSTING BARU KAMU
$user = "root";
$pass = "";
$db   = "ebuku_tamu_new"; // SESUAI DATABASE BARU KAMU

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// timezone (opsional tapi bagus)
date_default_timezone_set("Asia/Jakarta");