<?php
/**
 * Fungsi Logging Aktivitas Admin
 */
function logAktivitas($conn, $user_id, $aktivitas) {
    
    // Skip jika user_id kosong (untuk keamanan)
    if (empty($user_id) || !is_numeric($user_id)) {
        return false;
    }

    $user_id = (int)$user_id;
    $aktivitas = mysqli_real_escape_string($conn, $aktivitas);
    
    $query = "INSERT INTO log_aktivitas (user_id, aktivitas) VALUES ($user_id, '$aktivitas')";
    $result = mysqli_query($conn, $query);
    
    return $result;
}