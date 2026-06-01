<?php
require_once '../config/app.php';
include '../config/koneksi.php';
/** @var mysqli $conn */


requireLogin('login.php');

/* ================= DATA ================= */

$totalTamu = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM tamu"
))['total'];

$pending = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM tamu WHERE status='pending'"
))['total'];

$today = date('Y-m-d');
$todayTamu = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM tamu 
     WHERE DATE(created_at) = '$today'"
))['total'];

$legalisirSelesai = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM tamu 
     WHERE kategori_tamu='siswa' AND sub_kategori='legalisir' AND status='selesai'"
))['total'];

$tamuInstansi = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='instansi'"
))['total'];

$tamuSiswa = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='siswa'"
))['total'];

$pdfTerunggah = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM tamu WHERE file_pdf IS NOT NULL AND file_pdf <> ''"
))['total'];

/* ================= RATING ================ */
$totalReview = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM review"
))['total'];

$avgRating = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT AVG(rating) as avg FROM review"
))['avg'];

$avgRating = round((float) $avgRating, 1);

$rating5 = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM review WHERE rating=5"
))['total'];

/* ================= DATA STATISTIK LANJUTAN ================= */


// Data mingguan (Senin - Minggu)
$weeklyData = [];
$weeklyLabels = [];

$namaHari = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];

for ($i = 0; $i < 7; $i++) {

    $date = date('Y-m-d', strtotime("monday this week +$i day"));

    $hariInggris = date('l', strtotime($date));

    $weeklyLabels[] = $namaHari[$hariInggris] . ', ' . date('d/m', strtotime($date));

    $result = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) as total 
         FROM tamu 
         WHERE DATE(created_at) = '$date'"
    ));

    $weeklyData[] = $result['total'];
}

// Data bulanan
$bulan = [];
$dataBulanan = [];
$namaBulanSingkat = [
    1 => 'Jan',
    2 => 'Feb',
    3 => 'Mar',
    4 => 'Apr',
    5 => 'Mei',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Agu',
    9 => 'Sep',
    10 => 'Okt',
    11 => 'Nov',
    12 => 'Des'
];

for ($i = 1; $i <= 12; $i++) {
    $result = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) as total FROM tamu 
         WHERE MONTH(created_at) = '$i' 
         AND YEAR(created_at) = YEAR(CURDATE())"
    ));
    $bulan[] = $namaBulanSingkat[$i];
    $dataBulanan[] = $result['total'];
}

// Data tahunan (5 tahun terakhir)
$tahunLabels = [];
$tahunData = [];
$currentYear = date('Y');
for ($i = 4; $i >= 0; $i--) {
    $year = $currentYear - $i;
    $tahunLabels[] = $year;
    $result = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) as total FROM tamu 
         WHERE YEAR(created_at) = '$year'"
    ));
    $tahunData[] = $result['total'];
}

// Statistik tambahan
$rataHarian = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COALESCE(AVG(harian), 0) as rata FROM (
        SELECT COUNT(*) as harian, DATE(created_at) as tanggal 
        FROM tamu 
        GROUP BY DATE(created_at)
    ) as daily"
))['rata'];

$bulanTerbanyak = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT MONTH(created_at) as bulan, COUNT(*) as total 
     FROM tamu 
     WHERE YEAR(created_at) = YEAR(CURDATE())
     GROUP BY MONTH(created_at)
     ORDER BY total DESC 
     LIMIT 1"
));

$kategoriTerbanyak = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT kategori_tamu, COUNT(*) as total
     FROM tamu
     GROUP BY kategori_tamu
     ORDER BY total DESC
     LIMIT 1"
));

$queryHari = mysqli_query($conn, "
    SELECT 
        DAYNAME(created_at) AS hari,
        COUNT(*) AS total
    FROM tamu
    GROUP BY DAYNAME(created_at)
    ORDER BY total DESC
    LIMIT 1
");

$hariTerbanyak = mysqli_fetch_assoc($queryHari);

/* UBAH KE BAHASA INDONESIA */
$translateHari = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];

if ($hariTerbanyak && isset($hariTerbanyak['hari'])) {

    $hariTerbanyak['hari'] =
        $translateHari[$hariTerbanyak['hari']] ?? '-';
} else {

    $hariTerbanyak = [
        'hari' => '-',
        'total' => 0
    ];
}
if (!$bulanTerbanyak) {

    $bulanTerbanyak = [
        'bulan' => '-',
        'total' => 0
    ];
} elseif (isset($namaBulanSingkat[(int) $bulanTerbanyak['bulan']])) {
    $bulanTerbanyak['bulan'] = $namaBulanSingkat[(int) $bulanTerbanyak['bulan']];
}

$labelKategori = [
    'umum' => 'Tamu Umum',
    'instansi' => 'Tamu Instansi',
    'siswa' => 'Alumni/Siswa'
];

if (!$kategoriTerbanyak) {
    $kategoriTerbanyak = [
        'kategori_tamu' => '-',
        'total' => 0
    ];
}
include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<style>
    /* ================= CARD PREMIUM 3D ================= */
    .card {
        border-radius: 20px;
        border: none;
        backdrop-filter: blur(12px);
        box-shadow: 0 10px 25px rgba(94, 114, 228, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.6);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-6px) scale(1.01);
        box-shadow: 0 20px 45px rgba(94, 114, 228, 0.25), 0 0 20px rgba(203, 12, 159, 0.15);
    }

    /* ================= GRADIENT TEXT ================= */
    .text-gradient {
        background: linear-gradient(135deg, #5e72e4, #cb0c9f, #ff4d6d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    /* ================= ICON MODERN 3D ================= */
    .icon-modern {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15), inset 0 2px 4px rgba(255, 255, 255, 0.3);
        transition: 0.3s;
    }

    .icon-modern:hover {
        transform: scale(1.1) rotate(3deg);
    }

    /* ================= WARNA GRADIENT ================= */
    .bg-blue {
        background: linear-gradient(135deg, #5e72e4, #3f51b5);
    }

    .bg-purple {
        background: linear-gradient(135deg, #356d4d 0%, #46a36f 55%, #5bcf97 100% 100%);
    }

    .bg-red {
        background: linear-gradient(135deg, #ff416c, #ff4b2b);
    }

    /* ================= STAR STYLE ================= */
    .star-display {
        color: #facc15;
        font-size: 18px;
        text-shadow: 0 0 6px rgba(250, 204, 21, 0.6), 0 0 12px rgba(250, 204, 21, 0.4);
    }

    /* ================= STAT CARD KHUSUS ================= */
    .stat-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        border-radius: 1rem;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .stat-card .card-body {
        position: relative;
        z-index: 1;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1);
    }

    /* ================= CHART STYLE ================= */
    .chart-container {
        position: relative;
        min-height: 280px;
        width: 100%;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .filter-btn {
        padding: 6px 18px;
        border-radius: 20px;
        border: none;
        background: #f0f0f0;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        cursor: pointer;
    }

    .filter-btn.active {
        background: #00674F;
        color: white;
        box-shadow: 0 4px 8px rgba(94, 114, 228, 0.3);
    }

    .filter-btn:hover {
        transform: translateY(-2px);
    }

    .info-box {
        background: linear-gradient(135deg, #356d4d 0%, #46a36f 55%, #5bcf97 100%);
        border-radius: 1rem;
        padding: 20px;
        color: white;
    }

    .insight-item {
        padding: 12px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .insight-item:last-child {
        border-bottom: none;
    }

    canvas {
        max-height: 280px !important;
        width: 100% !important;
    }

    /* ================= BACKGROUND BODY ================= */
    body {
        background: linear-gradient(135deg, #f5f7ff 0%, #fdf2f8 50%, #fff5f5 100%);
    }

    .info-badge {
        background: rgba(45, 206, 137, 0.1);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-block;
    }

    .dashboard-stats {
        --bs-gutter-x: 0.85rem;
        --bs-gutter-y: 0.85rem;
    }

    .dashboard-stats>[class*="col-"] {
        margin-bottom: 0 !important;
    }

    .dashboard-stats .stat-card .card-body {
        padding: 0.9rem !important;
    }

    .dashboard-stats .stat-card h3 {
        font-size: clamp(1.35rem, 2.1vw, 1.9rem);
    }

    @media (max-width: 768px) {

        .card:hover,
        .stat-card:hover,
        .filter-btn:hover {
            transform: none;
        }

        .alert .d-flex,
        .card-header .d-flex {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 12px;
        }

        .filter-buttons {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .filter-btn {
            flex: 0 0 auto;
        }

        .chart-container {
            min-height: 300px;
        }

        .dashboard-stats {
            --bs-gutter-x: 0.65rem;
            --bs-gutter-y: 0.65rem;
            margin-bottom: 1rem !important;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            font-size: 1.2rem;
        }

        @media (max-width: 767px) {

            .statistik-mobile {
                display: flex;
                flex-wrap: nowrap;
                justify-content: space-between;
                gap: 10px;
            }

            .statistik-mobile .col-md-4 {
                width: 33.3%;
                flex: 0 0 33.3%;
                max-width: 33.3%;
                padding: 0 4px;
            }

            .statistik-mobile h5 {
                font-size: 15px;
            }

            .statistik-mobile p,
            .statistik-mobile small {
                font-size: 11px;
            }
        }
    }
</style>

<!-- WELCOME SECTION -->
<div class="alert alert-primary text-white mb-4" style="border-radius: 1rem; background: linear-gradient(135deg, #356d4d 0%, #46a36f 55%, #5bcf97 100%  100%) !important;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="text-white mb-1">Selamat datang kembali, <strong><?= e($_SESSION['username']); ?></strong></h4>
            <p class="text-white opacity-8 mb-0">Ringkasan aktivitas kunjungan dan layanan tamu hari ini</p>
        </div>
        <div class="text-end">
            <?php
            $hariIndo = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];

            $bulanIndo = [
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            ];

            $hariSekarang = $hariIndo[date('l')];
            $bulanSekarang = $bulanIndo[date('F')];
            ?>

            <small class="text-white opacity-8">
                <?= $hariSekarang . ', ' . date('d') . ' ' . $bulanSekarang . ' ' . date('Y'); ?>
            </small>
        </div>
    </div>
</div>

<!-- CARDS UTAMA (4 CARD) -->
<div class="row dashboard-stats mb-3">
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted text-sm mb-1">Tamu Hari Ini</p>
                        <h3 class="font-weight-bolder mb-0"><?= number_format($todayTamu); ?></h3>
                        <div class="info-badge mt-2">
                            <i class="ni ni-calendar-grid-58"></i> Kunjungan terbaru
                        </div>
                    </div>
                    <div class="stat-icon bg-gradient-success">
                        <i class="ni ni-single-02 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted text-sm mb-1">Pending</p>
                        <h3 class="font-weight-bolder mb-0"><?= number_format($pending); ?></h3>
                        <div class="info-badge mt-2">
                            <i class="ni ni-time-alarm"></i> Perlu ditindaklanjuti
                        </div>
                    </div>
                    <div class="stat-icon bg-gradient-warning">
                        <i class="ni ni-time-alarm text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted text-sm mb-1">Legalisir Selesai</p>
                        <h3 class="font-weight-bolder mb-0"><?= number_format($legalisirSelesai); ?></h3>
                        <div class="info-badge mt-2">
                            <i class="ni ni-check-bold"></i> Dokumen siap
                        </div>
                    </div>
                    <div class="stat-icon bg-gradient-info">
                        <i class="ni ni-check-bold text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted text-sm mb-1">PDF Terunggah</p>
                        <h3 class="font-weight-bolder mb-0"><?= number_format($pdfTerunggah); ?></h3>
                        <div class="info-badge mt-2">
                            <i class="ni ni-single-copy-04"></i> Berkas instansi
                        </div>
                    </div>
                    <div class="stat-icon bg-gradient-primary">
                        <i class="ni ni-single-copy-04 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK DAN ULASAN -->
<!-- GRAFIK DAN ULASAN -->
<div class="row">
    <!-- KOLOM KIRI: GRAFIK -->
    <div class="col-lg-8">
        <div class="card shadow-lg" style="border-radius: 1rem;">
            <div class="card-header bg-white pb-0 pt-3" style="border-radius: 1rem 1rem 0 0;">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-0 font-weight-bolder">Statistik Kunjungan Tamu</h5>
                        <p class="text-muted text-sm mb-3">Pemantauan jumlah kunjungan berdasarkan periode waktu</p>
                    </div>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-period="week">Per Minggu</button>
                        <button class="filter-btn" data-period="month">Per Bulan</button>
                        <button class="filter-btn" data-period="year">Per Tahun</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartKunjungan"></canvas>
                </div>

                <!-- Ringkasan Statistik -->
                <div class="row mt-4 pt-3 border-top statistik-mobile">
                    <div class="col-md-4">
                        <div class="text-center">
                            <p class="text-muted mb-0 text-sm">Rata-rata Kunjungan Harian</p>
                            <h5 class="mb-0 font-weight-bolder"><?= round($rataHarian); ?></h5>
                            <small class="text-muted">tamu per hari</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <p class="text-muted mb-0 text-sm">Bulan Kunjungan Terbanyak</p>
                            <h5 class="mb-0 font-weight-bolder"><?= e($bulanTerbanyak['bulan'] ?? '-'); ?></h5>
                            <small class="text-muted"><?= number_format($bulanTerbanyak['total'] ?? 0); ?> tamu</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <p class="text-muted mb-0 text-sm">Hari Kunjungan Terbanyak</p>
                            <h5 class="mb-0 font-weight-bolder"><?= e($hariTerbanyak['hari'] ?? '-'); ?></h5>
                            <small class="text-muted"><?= number_format($hariTerbanyak['total'] ?? 0); ?> tamu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: ULASAN DAN KATEGORI -->
    <div class="col-lg-4">
        <!-- ULASAN MASUK -->
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-sm mb-1">Ulasan Masuk</p>
                    <h5 class="font-weight-bold mb-0"><?= $totalReview ?></h5>
                </div>
                <div class="icon-modern bg-blue">
                    <i class="bi bi-chat-dots"></i>
                </div>
            </div>
        </div>

        <!-- NILAI KEPUASAN RATA-RATA -->
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-sm mb-1">Rata-rata Kepuasan</p>
                    <h5 class="font-weight-bold mb-0"><?= $avgRating ?></h5>
                    <div class="star-display">
                        ★★★★★
                    </div>
                </div>
                <div class="icon-modern bg-purple">
                    <i class="bi bi-star-fill"></i>
                </div>
            </div>
        </div>

        <!-- PENILAIAN  -->
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-sm mb-1">Penilaian Bintang Lima</p>
                    <h5 class="font-weight-bold mb-0"><?= $rating5 ?></h5>
                </div>
                <div class="icon-modern bg-red">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>

        <!-- RINGKASAN KATEGORI -->
        <div class="info-box mt-2">
            <div class="d-flex align-items-center mb-2">
                <i class="ni ni-chart-bar-32" style="font-size: 2rem;"></i>
                <div class="ms-3">
                    <h6 class="text-white mb-0">Kategori Kunjungan Terbanyak</h6>
                    <h3 class="text-white font-weight-bolder mb-0"><?= e($labelKategori[$kategoriTerbanyak['kategori_tamu']] ?? '-'); ?></h3>
                </div>
            </div>
            <p class="text-white opacity-8 mb-0 small">
                <i class="ni ni-calendar-grid-58"></i> <?= number_format($kategoriTerbanyak['total'] ?? 0); ?> dari <?= number_format($totalTamu); ?> total kunjungan tercatat
            </p>
            <p class="text-white opacity-8 mb-0 small mt-2">
                Instansi: <?= number_format($tamuInstansi); ?> kunjungan, Alumni/Siswa: <?= number_format($tamuSiswa); ?> kunjungan
            </p>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // Data multi-line dari PHP untuk setiap kategori (weekly, monthly, yearly)
    // Data Mingguan per kategori
    const weeklyUmum = [];
    const weeklyInstansi = [];
    const weeklySiswa = [];

    // Data Bulanan per kategori
    const monthlyUmum = [];
    const monthlyInstansi = [];
    const monthlySiswa = [];

    // Data Tahunan per kategori
    const yearlyUmum = [];
    const yearlyInstansi = [];
    const yearlySiswa = [];

    // Mengambil data mingguan (7 hari terakhir)
    <?php
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("monday this week +$i day"));
        
        $resultUmum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='umum' AND DATE(created_at) = '$date'"));
        $resultInstansi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='instansi' AND DATE(created_at) = '$date'"));
        $resultSiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='siswa' AND DATE(created_at) = '$date'"));
    ?>
        weeklyUmum.push(<?= $resultUmum['total'] ?? 0 ?>);
        weeklyInstansi.push(<?= $resultInstansi['total'] ?? 0 ?>);
        weeklySiswa.push(<?= $resultSiswa['total'] ?? 0 ?>);
    <?php } ?>

    // Mengambil data bulanan (12 bulan)
    <?php
    for ($i = 1; $i <= 12; $i++) {
        $resultUmum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='umum' AND MONTH(created_at) = '$i' AND YEAR(created_at) = YEAR(CURDATE())"));
        $resultInstansi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='instansi' AND MONTH(created_at) = '$i' AND YEAR(created_at) = YEAR(CURDATE())"));
        $resultSiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='siswa' AND MONTH(created_at) = '$i' AND YEAR(created_at) = YEAR(CURDATE())"));
    ?>
        monthlyUmum.push(<?= $resultUmum['total'] ?? 0 ?>);
        monthlyInstansi.push(<?= $resultInstansi['total'] ?? 0 ?>);
        monthlySiswa.push(<?= $resultSiswa['total'] ?? 0 ?>);
    <?php } ?>

    // Mengambil data tahunan (5 tahun terakhir)
    <?php
    for ($i = 4; $i >= 0; $i--) {
        $year = $currentYear - $i;
        $resultUmum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='umum' AND YEAR(created_at) = '$year'"));
        $resultInstansi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='instansi' AND YEAR(created_at) = '$year'"));
        $resultSiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu WHERE kategori_tamu='siswa' AND YEAR(created_at) = '$year'"));
    ?>
        yearlyUmum.push(<?= $resultUmum['total'] ?? 0 ?>);
        yearlyInstansi.push(<?= $resultInstansi['total'] ?? 0 ?>);
        yearlySiswa.push(<?= $resultSiswa['total'] ?? 0 ?>);
    <?php } ?>

    // Hitung total per kategori
    const totalUmum = <?= $totalTamu - $tamuInstansi - $tamuSiswa ?>;
    const totalInstansi = <?= $tamuInstansi ?>;
    const totalSiswa = <?= $tamuSiswa ?>;

    const weeklyLabels = <?= json_encode($weeklyLabels); ?>;
    const monthlyLabels = <?= json_encode($bulan); ?>;
    const yearlyLabels = <?= json_encode($tahunLabels); ?>;

    let currentChart = null;

    // Fungsi untuk menampilkan multi-line chart
    function renderMultiLineChart(datasets, labels, periodType = 'week') {
        const ctx = document.getElementById('chartKunjungan').getContext('2d');

        if (currentChart) {
            currentChart.destroy();
        }

        currentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            },
                            usePointStyle: true,
                            boxWidth: 12,
                            padding: 15,
                            generateLabels: function(chart) {
                                const original = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                const totals = {
                                    'Tamu Umum': totalUmum,
                                    'Tamu Instansi': totalInstansi,
                                    'Alumni/Siswa': totalSiswa
                                };
                                original.forEach(label => {
                                    const total = totals[label.text];
                                    if (total !== undefined) {
                                        label.text = `${label.text} (${total} total)`;
                                    }
                                });
                                return original;
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#009f38',
                        borderWidth: 2,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw} tamu`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Tamu',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.08)',
                            drawBorder: false,
                            lineWidth: 1
                        },
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return value + ' tamu';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: labels.length <= 7 ? 'Hari' : (labels.length <= 12 ? 'Bulan' : 'Tahun'),
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6,
                        borderWidth: 2,
                        borderColor: '#fff'
                    },
                    line: {
                        tension: 0.3,
                        borderWidth: 2.5
                    }
                }
            }
        });

        // Tambahkan label nilai di atas titik (data label)
        setTimeout(() => {
            if (currentChart) {
                currentChart.update();
            }
        }, 100);
    }

    // Siapkan dataset untuk setiap periode
    function getDatasetsForPeriod(period) {
        if (period === 'week') {
            return [
                {
                    label: 'Tamu Umum',
                    data: weeklyUmum,
                    borderColor: '#5e72e4',
                    backgroundColor: 'rgba(94, 114, 228, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#5e72e4',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Tamu Instansi',
                    data: weeklyInstansi,
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#6c757d',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Alumni/Siswa',
                    data: weeklySiswa,
                    borderColor: '#009f38',
                    backgroundColor: 'rgba(0, 159, 56, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#009f38',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                }
            ];
        } else if (period === 'month') {
            return [
                {
                    label: 'Tamu Umum',
                    data: monthlyUmum,
                    borderColor: '#5e72e4',
                    backgroundColor: 'rgba(94, 114, 228, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#5e72e4',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Tamu Instansi',
                    data: monthlyInstansi,
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#6c757d',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Alumni/Siswa',
                    data: monthlySiswa,
                    borderColor: '#009f38',
                    backgroundColor: 'rgba(0, 159, 56, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#009f38',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                }
            ];
        } else {
            return [
                {
                    label: 'Tamu Umum',
                    data: yearlyUmum,
                    borderColor: '#5e72e4',
                    backgroundColor: 'rgba(94, 114, 228, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#5e72e4',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Tamu Instansi',
                    data: yearlyInstansi,
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#6c757d',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Alumni/Siswa',
                    data: yearlySiswa,
                    borderColor: '#009f38',
                    backgroundColor: 'rgba(0, 159, 56, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#009f38',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: false
                }
            ];
        }
    }

    // Pengaturan tombol periode grafik
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const period = this.dataset.period;
            let datasets, labels;

            if (period === 'week') {
                datasets = getDatasetsForPeriod('week');
                labels = weeklyLabels;
                renderMultiLineChart(datasets, labels, 'week');
            } else if (period === 'month') {
                datasets = getDatasetsForPeriod('month');
                labels = monthlyLabels;
                renderMultiLineChart(datasets, labels, 'month');
            } else if (period === 'year') {
                datasets = getDatasetsForPeriod('year');
                labels = yearlyLabels;
                renderMultiLineChart(datasets, labels, 'year');
            }
        });
    });

    // Menampilkan grafik awal (mingguan)
    if (document.getElementById('chartKunjungan')) {
        const initialDatasets = getDatasetsForPeriod('week');
        renderMultiLineChart(initialDatasets, weeklyLabels, 'week');
    }
</script>
