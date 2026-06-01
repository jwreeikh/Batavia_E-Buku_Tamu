<?php
include 'config/koneksi.php';
$kepsek = require 'config/kepsek.php';

if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

$queryReview = mysqli_query($conn, "
    SELECT review.*, tamu.nama 
    FROM review 
    LEFT JOIN tamu ON review.tamu_id = tamu.id 
    WHERE tampil='ya' 
    ORDER BY review.id DESC 
    LIMIT 6
");

// Fetch tujuan_layanan for dropdowns
$queryTujuanLayanan = mysqli_query($conn, "SELECT id, nama_tujuan FROM tujuan_layanan ORDER BY CASE WHEN LOWER(nama_tujuan) = 'lainnya' THEN 1 ELSE 0 END, nama_tujuan ASC");
$tujuanLayananList = [];
while ($tl = mysqli_fetch_assoc($queryTujuanLayanan)) {
    $tujuanLayananList[] = $tl;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendya - E-Buku Tamu Digital</title>
    <meta name="description" content="Attendya adalah aplikasi e-buku tamu digital untuk mencatat kunjungan secara cepat dan rapi.">
    <meta name="keywords" content="buku tamu digital, attendya, e-buku tamu">

    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="assets/css/index-local.css" rel="stylesheet">

    <style>
        /* ==========================================================================
           RE-DESIGN MATCHING MOCKUP IMAGE (SOFT GOLD & PREMIUM MARBLE GREEN)
           ========================================================================== */

        :root {
            --text-dark-green: #113322;
            /* Warna teks utama (Hijau Gelap) */
            --btn-green-start: #0f6348;
            /* Tombol Isi Buku Tamu (Kiri) */
            --btn-green-end: #1b8a66;
            /* Tombol Isi Buku Tamu (Kanan) */
            --gold-light-bg: #f4f1e6;
            /* Background Navbar Ivory/Gold Soft */
            --gold-gradient-start: #e5b869;
            /* Tombol Login Gradasi Emas */
            --gold-gradient-end: #b88732;
            /* Tombol Login Gradasi Emas Tua */
        }

        body {
            color: var(--text-dark-green) !important;
            background-color: #ffffff;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: var(--text-dark-green) !important;
            font-weight: 700;
        }

        /* HEADER & NAVBAR CUSTOM (Kapsul Melengkung Sesuai Gambar) */
        .header {
            background-color: transparent !important;
            box-shadow: none !important;
            padding: 20px 0;
        }

        .header-container {
            background: rgba(244, 241, 230, 0.85) !important;
            /* Efek ivory transparan */
            backdrop-filter: blur(15px);
            border-radius: 50px !important;
            /* Membuat bentuk kapsul melengkung */
            padding: 10px 35px !important;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 30px rgba(17, 51, 34, 0.06) !important;
        }

        /* Nav Menu Links */
        .navmenu a {
            color: var(--text-dark-green) !important;
            font-weight: 500;
            font-size: 15px;
        }

        .navmenu a:hover,
        .navmenu .active {
            color: #00674F !important;
        }

        /* TOMBOL LOGIN (Gradasi Emas Melengkung Bulat) */
        .btn-getstarted {
            background: linear-gradient(135deg, var(--gold-gradient-start), var(--gold-gradient-end)) !important;
            color: #ffffff !important;
            border: none !important;
            padding: 10px 30px !important;
            border-radius: 30px !important;
            /* Bulat kapsul */
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(184, 135, 50, 0.3) !important;
            transition: all 0.3s ease !important;
        }

        .btn-getstarted:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(184, 135, 50, 0.4) !important;
            opacity: 0.95;
        }

        /* HERO CONTENT CUSTOM */
        .company-badge {
            background-color: #f0f7f4 !important;
            color: #00674F !important;
            border: 1px solid rgba(15, 99, 72, 0.15);
            font-weight: 500;
        }

        .company-badge i {
            color: var(--gold-gradient-start) !important;
        }

        /* TOMBOL ISI BUKU TAMU HERO (Hijau Gradasi Melengkung Bulat) */
        .hero-buttons .btn-getstarted {
            background: linear-gradient(90deg, var(--btn-green-start), var(--btn-green-end)) !important;
            border-radius: 30px !important;
            /* Bulat Kapsul Sesuai Gambar */
            padding: 14px 35px !important;
            font-size: 16px;
            box-shadow: 0 6px 20px rgba(15, 99, 72, 0.25) !important;
        }

        .hero-buttons .btn-getstarted:hover {
            background: linear-gradient(90deg, var(--btn-green-end), var(--btn-green-start)) !important;
            transform: translateY(-2px);
        }

        /* JAM LAYANAN SECTION CARD HARMONIZATION */
        .service-card.purple {
            background: linear-gradient(135deg, #0f6348, #23a37a) !important;
            color: #ffffff !important;
            border-radius: 20px;
            border-color: #00674F;
        }

        .service-card.purple .icon-box {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #f4f1e6 !important;
        }

        .service-card.purple .badge {
            background-color: #e5a369 !important;
            color: var(--text-dark-green) !important;
            font-weight: 600;
        }

        .service-card.pink {
            background: linear-gradient(135deg, #e5b869, #b88732) !important;
            color: #ffffff !important;
            border-radius: 20px;
        }

        .service-card.pink .icon-box {
            background-color: rgba(255, 255, 255, 0.2) !important;
        }

        .service-card.blue {
            background: #ffffff !important;
            color: var(--text-dark-green) !important;
            border: 1px solid rgba(15, 99, 72, 0.2) !important;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }

        .service-card.blue .icon-box {
            background-color: #f0f7f4 !important;
            color: #0f6348 !important;
        }

        .service-card.blue a {
            color: #0f6348 !important;
        }

        /* MODAL & REVIEWS */
        .section-title h2::after {
            background: var(--gold-gradient-start) !important;
        }

        .testimonial-item {
            border-left: 4px solid #0f6348 !important;
            background-color: #ffffff !important;
            border-radius: 0 15px 15px 0;
        }

        .testimonial-item .stars i {
            color: var(--gold-gradient-start) !important;
        }

        .soft-modal {
            border-top: 5px solid #0f6348 !important;
            border-radius: 24px !important;
        }

        .soft-input {
            border-radius: 12px !important;
        }

        .soft-input:focus {
            border-color: #0f6348 !important;
            box-shadow: 0 0 0 0.25rem rgba(15, 99, 72, 0.15) !important;
        }

        .soft-btn {
            background: linear-gradient(90deg, var(--btn-green-start), var(--btn-green-end)) !important;
            border-radius: 12px !important;
            border: none !important;
        }

        .soft-btn:hover {
            opacity: 0.9;
        }

        /* SELECT2 FULL WIDTH ADJUSTMENT */
        .select2-container {
            width: 100% !important;
        }

        .select2-selection--single {
            width: 100% !important;
            height: 54px !important;
            border-radius: 12px !important;
            padding: 10px 15px !important;
            border: 1px solid #e9ecef !important;
        }

        .select2-selection__rendered {
            line-height: 32px !important;
            color: #6c757d !important;
        }

        .select2-selection__arrow {
            height: 54px !important;
            right: 15px !important;
        }
        .footer-custom{
    margin-top:40px;
    padding:15px 25px;
    background:#fff;
    border-top:1px solid #e5e7eb;
    color:#6b7280;
    font-size:14px;
}

@media(max-width:768px){
    .footer-custom .container{
        flex-direction:column;
        gap:5px;
        text-align:center;
    }
}
    </style>
</head>

<body class="index-page">
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="header-container container-xl position-relative d-flex align-items-center justify-content-between">
            <div class="text-center py-1">
                <img src="assets/img/logoo.png" style="max-width: 55px; height:auto; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.05));">
            </div>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero" class="active">Home</a></li>
                    <li><a href="#layanan">Jam Layanan</a></li>
                    <li class="dropdown"><a href="#"><span>Tracking Tamu</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="tracking-legalisir.php">Tracking Legalisir</a></li>
                            <li><a href="kunjungan-terbaru.php">Kunjungan Terbaru</a></li>
                        </ul>
                    </li>
                    <li><a href="#testimonials">Review</a></li>

                    <li class="d-xl-none mt-3">
                        <a href="admin/login.php" class="btn btn-primary w-100 text-center">Login</a>
                    </li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a class="btn-getstarted d-none d-xl-inline-flex" href="admin/login.php">Login</a>
        </div>
    </header>

    <main class="main">

        <section id="hero" class="hero section">
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-content" data-aos="fade-up" data-aos-delay="200">
                            <br>
                            <div class="company-badge mb-4">
                                <i class="bi bi-compass-fill me-2"></i> Membantu pekerjaan Anda lebih mudah
                            </div>
                            <style>
                                #hero.hero.section {
                                    background: linear-gradient(135deg,
                                            rgba(229, 184, 105, 0.12),
                                            rgba(184, 135, 50, 0.10),
                                            rgba(0, 103, 79, 0.12));
                                }
                            </style>
                            <h1 class="mb-4">
                                Aplikasi Berbasis Website <br>
                                <span style="color: #00674F;">E-Buku Tamu Digital</span>
                            </h1>

                            <p class="mb-4 mb-md-5" style="color: #00674F;">
                                Attendya adalah solusi berbasis web untuk mencatat kunjungan secara cepat, rapi, dan profesional.
                            </p>

                            <div class="hero-buttons d-flex">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalTamu" class="btn-getstarted">
                                    Isi Buku Tamu
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="hero-image" data-aos="zoom-out" data-aos-delay="300">
                            <img src="assets/img/Gambar3.png" alt="Hero Image" class="img-fluid">
                            <div class="customers-badge">
                                <div class="customer-avatars">
                                    <img src="assets/img/avatar-1.webp" alt="Customer 1" class="avatar">
                                    <img src="assets/img/avatar-2.webp" alt="Customer 2" class="avatar">
                                    <img src="assets/img/avatar-3.webp" alt="Customer 3" class="avatar">
                                    <img src="assets/img/avatar-4.webp" alt="Customer 4" class="avatar">
                                    <img src="assets/img/avatar-5.webp" alt="Customer 5" class="avatar">
                                    <span class="avatar more" style="background: #e5ab69;">12+</span>
                                </div>
                                <p class="mb-0 mt-2">Digitalisasi membantu meningkatkan efisiensi pekerjaan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <class="info-box" style="background: linear-gradient(135deg, rgba(66, 147, 108, 0.3) 0%, rgba(66, 147, 108, 0.08) 100%); border: 1px solid rgba(66, 147, 108, 0.35); color: #1e3527;">

            <section id="layanan" class="service-section">
                <div class="container section-title">
                    <h2 style="color:#00674F;">Jam Layanan Kami</h2>
                </div>

                <div class="service-container">

                    <div class="service-card blue" style="border:2px solid #00674F;">
                        <div class="icon-box">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="content">
                            <h3>Senin - Jumat</h3>
                            <p>08.00 - 16.00 WIB</p>
                            <span class="badge">Istirahat 12.00 - 13.00 WIB</span>
                        </div>
                        <div class="bg-circle" style="background: #e5b869;"></div>
                    </div>

                    <div class="service-card blue" style="border:2px solid #00674F;">
                        <div class="icon-box">
                            <i class="bi bi-calendar2"></i>
                        </div>
                        <div class="content">
                            <h3>Sabtu & Minggu</h3>
                            <p class="libur">Libur</p>
                        </div>
                        <div class="bg-circle" style="background:#e5b869" ; opacity:0.15;></div>
                    </div>

                    <div class="service-card blue" style="border:2px solid #00674F;">
                        <div class="icon-box">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div class="content">
                            <h3>Kontak Kami</h3>
                            <p>(021) 1234 5678</p>
                            <a href="#">support@ebookutamu.id</a>
                        </div>
                        <div class="bg-circle" style="background:#e5b869"></div>
                    </div>
                    <!-- TRACKING KEPSEK -->
                    <div class="service-card blue" style="border:2px solid #00674F;">

                        <div class="icon-box">
                            <img
                                src="<?= htmlspecialchars($kepsek['foto']) ?>"
                                alt="Kepala Sekolah"
                                style="width:70px; height:70px; border-radius:20px; object-fit:cover;">
                        </div>

                        <div class="content">
                            <h3><?= htmlspecialchars($kepsek['nama']) ?></h3>

                            <p style="color:#00674F; font-weight:600;">
                                Kepala Sekolah
                            </p>

                            <span class="badge <?= $kepsek['status'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $kepsek['status']
                                    ? 'Ada di Sekolah'
                                    : 'Sedang Dinas Di Luar' ?>
                            </span>
                        </div>

                        <div class="bg-circle" style="background:#e5b869;"></div>
                    </div>

                </div>

                <div class="info-box" style="background: linear-gradient(135deg, rgba(66, 147, 108, 0.3) 0%, rgba(66, 147, 108, 0.08) 100%); border: 1px solid rgba(66, 147, 108, 0.35); color: #1e3527;">
                    <center>
                        <span style="font-weight: 500;">ⓘ Layanan kami mengikuti waktu operasional yang tertera di atas. Terima kasih.</span>
                    </center>
                </div>
            </section>
            <section id="testimonials" class="testimonials section light-background">
                <div class="container section-title" data-aos="fade-up">
                    <h2>Review</h2>
                    <p>Berikut ini adalah review dari pengunjung sekolah kami terkait sistem yang kami buat</p>
                </div>

                <div class="container">
                    <div class="row" id="publicReviewList">
                        <?php while ($r = mysqli_fetch_assoc($queryReview)): ?>
                            <div class="col-lg-6">
                                <div class="testimonial-item">
                                    <h5 class="review-guest-name" title="<?= htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h5>
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi <?= $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p><?= $r['tags'] ?: '-' ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </section>
    </main>

    <div class="modal fade" id="modalTamu" tabindex="-1" aria-labelledby="modalTamuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content soft-modal">
                <form id="formTamu">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="modalTamuLabel">Isi Buku Tamu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="alertPlaceholder"></div>

                        <div class="mb-3 field-kategori">
                            <label class="form-label small ms-2">Kategori Tamu</label>
                            <select name="kategori_tamu" id="kategori_tamu" class="form-control soft-input" required>
                                <option value="umum">Umum</option>
                                <option value="instansi">Instansi / Kedinasan</option>
                                <option value="siswa">Alumni Siswa</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <input type="text" name="nama" id="nama" class="form-control soft-input" placeholder="Masukkan nama" oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\s\.\,\-']/g, '')" maxlength="60" required>
                            <small id="error-nama" class="text-danger fw-bold"></small>
                        </div>

                        <div class="mb-3 field-instansi" style="display: none;">
                            <input type="text" name="instansi" id="instansi" class="form-control soft-input" placeholder="Masukkan instansi" maxlength="100" required>
                            <small id="error-instansi" class="text-danger fw-bold"></small>
                        </div>

                        <div class="field-siswa" style="display: none;">
                            <div class="mb-3">
                                <input type="text" name="nisn" id="nisn" class="form-control soft-input" placeholder="Masukkan NISN" oninput="this.value = this.value.replace(/\D/g, '')" maxlength="10">
                            </div>
                            <div class="mb-3">
                                <input type="text" name="universitas" id="universitas" class="form-control soft-input" placeholder="Masukkan Universitas (Opsional)" maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small ms-2">Sub Kategori</label>
                                <select name="sub_kategori" id="sub_kategori" class="form-control soft-input">
                                    <option value="">-- Pilih Sub Kategori --</option>
                                    <option value="legalisir">Legalisir</option>
                                    <option value="biasa">Biasa</option>
                                </select>
                            </div>

                            <div class="field-siswa-biasa" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label small ms-2">Tujuan Layanan</label>
                                    <select name="tujuan_id_siswa" id="tujuan_id_siswa" class="form-control soft-input">
                                        <option value="">Pilih tujuan layanan...</option>
                                        <?php foreach ($tujuanLayananList as $tl): ?>
                                            <option value="<?= $tl['id']; ?>"><?= $tl['nama_tujuan']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small id="error-tujuan-id-siswa" class="text-danger fw-bold"></small>
                                </div>
                                <div class="mb-3">
                                    <textarea name="tujuan_siswa" id="tujuan_siswa" class="form-control soft-input" placeholder="Masukkan keperluan"></textarea>
                                    <small id="error-tujuan-siswa" class="text-danger fw-bold"></small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="text" name="no_hp" id="no_hp" class="form-control soft-input" placeholder="Masukkan nomor HP" oninput="this.value = this.value.replace(/\D/g, '')" maxlength="13" required>
                            <small id="error-nohp" class="text-danger fw-bold"></small>
                        </div>

                        <div class="field-umum-instansi" style="display: block;">
                            <div class="mb-3">
                                <label class="form-label small ms-2">Tujuan Layanan</label>
                                <select name="tujuan_id" id="tujuan_id" class="form-control soft-input" required>
                                    <option value="">Pilih tujuan layanan...</option>
                                    <?php foreach ($tujuanLayananList as $tl): ?>
                                        <option value="<?= $tl['id']; ?>"><?= $tl['nama_tujuan']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small id="error-tujuan-id" class="text-danger fw-bold"></small>
                            </div>

                            <div class="mb-3">
                                <textarea name="tujuan" id="tujuan" class="form-control soft-input" placeholder="Masukkan keperluan" required></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small ms-2">Tanggal Kunjungan</label>
                            <input type="date" name="tanggal_kunjungan" class="form-control soft-input" required min="<?= date('Y-m-d'); ?>" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary w-100 soft-btn">Kirim Kunjungan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer class="footer-custom">
        <div class="container d-flex justify-content-between align-items-center flex-wrap">
            <div>
                © <?= date('Y'); ?> Copy Right base ui <strong><span>iLanding</span></strong>.
            </div>
            <div>
                Versi 1.0
            </div>
        </div>
    </footer>

    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center" style="background-color: #0f6348 !important;"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/index-local.js"></script>
    <script>
        const modalTamu = document.getElementById('modalTamu');
        modalTamu.addEventListener('hidden.bs.modal', function() {
            document.getElementById('formTamu').reset();
        });
    </script>
</body>

</html>