<?php
// Aktifkan error reporting hanya di environment development
if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);

    session_start();
}
if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin($redirect = 'login.php')
    {
        if (empty($_SESSION['login'])) {
            header('Location: ' . $redirect);
            exit;
        }
    }
}

if (!function_exists('requireRole')) {
    function requireRole(array $roles, $redirect = 'dashboard.php')
    {
        requireLogin();
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, $roles, true)) {
            $_SESSION['error'] = 'Anda tidak memiliki akses untuk menjalankan aksi tersebut.';
            header('Location: ' . $redirect);
            exit;
        }
    }
}

if (!function_exists('getIntParam')) {
    function getIntParam($source, $key)
    {
        $value = $source[$key] ?? null;
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
    }
}

if (!function_exists('validGuestCategory')) {
    function validGuestCategory($category)
    {
        return in_array($category, ['instansi', 'umum', 'siswa'], true) ? $category : 'umum';
    }
}

if (!function_exists('redirectToGuestPage')) {
    function redirectToGuestPage($asal, $kategori = '')
    {
        $allowed = ['instansi', 'umum', 'siswa', 'tamu'];
        $asal = in_array($asal, $allowed, true) ? $asal : 'tamu';
        $kategori = validGuestCategory($kategori ?: $asal);

        if ($asal === 'tamu') {
            return 'data-tamu.php?kategori=' . urlencode($kategori);
        }

        return 'data-' . $asal . '.php';
    }
}

if (!function_exists('statusMeta')) {
    function statusMeta($status)
    {
        $status = strtolower((string) $status);
        $map = [
            'pending' => ['label' => 'Pending', 'class' => 'bg-gradient-warning'],
            'diterima' => ['label' => 'Diterima', 'class' => 'bg-gradient-success'],
            'ditolak' => ['label' => 'Ditolak', 'class' => 'bg-gradient-danger'],
            'progres' => ['label' => 'Progres', 'class' => 'bg-gradient-info'],
            'selesai' => ['label' => 'Selesai', 'class' => 'bg-gradient-success'],
            // Status untuk Review
            'ya'      => ['label' => 'Tampil', 'class' => 'bg-gradient-success'],
            'tidak'   => ['label' => 'Pending', 'class' => 'bg-gradient-warning'],
            'tercatat' => ['label' => 'Tercatat', 'class' => 'bg-gradient-success'],
        ];

        return $map[$status] ?? ['label' => '-', 'class' => 'bg-gradient-secondary'];
    }
}

if (!function_exists('renderStatusBadge')) {
    function renderStatusBadge($status)
    {
        $meta = statusMeta($status);
        return '<span class="badge status-badge ' . e($meta['class']) . ' text-nowrap">' . e($meta['label']) . '</span>';
    }
}

if (!function_exists('displayPurpose')) {
    function displayPurpose(array $guest)
    {
        if (($guest['kategori_tamu'] ?? '') === 'siswa' && ($guest['sub_kategori'] ?? '') === 'legalisir') {
            return 'Legalisir';
        }

        return $guest['nama_tujuan'] ?? $guest['tujuan'] ?? $guest['keperluan'] ?? 'Lainnya';
    }
}

if (!function_exists('guestDetailRows')) {
    function guestDetailRows(array $guest)
    {
        $rows = [
            'Nama' => $guest['nama'] ?? '-',
            'Kategori' => ucfirst($guest['kategori_tamu'] ?? '-'),
            'Sub Kategori' => $guest['sub_kategori'] ?? '-',
            'NISN' => $guest['nisn'] ?? '-',
            'Instansi' => $guest['instansi'] ?? '-',
            'Universitas' => $guest['universitas'] ?? '-',
            'No HP' => $guest['no_hp'] ?? '-',
            'Menemui' => displayPurpose($guest),
            'Keperluan' => $guest['tujuan'] ?? $guest['keperluan'] ?? '-',
            'Tanggal Kunjungan' => $guest['tanggal_kunjungan'] ?? '-',
            'Status' => statusMeta($guest['status'] ?? '')['label'],
            'PDF' => !empty($guest['file_pdf']) ? $guest['file_pdf'] : '-',
        ];

        $html = '';
        foreach ($rows as $label => $value) {
            if ($value === null || $value === '') {
                $value = '-';
            }
            $html .= '<div class="detail-row"><span>' . e($label) . '</span><strong>' . e($value) . '</strong></div>';
        }

        return $html;
    }
}
