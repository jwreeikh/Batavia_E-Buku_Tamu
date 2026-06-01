<?php
ob_start(); 
require_once '../config/app.php';
require_once '../assets/vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
include '../config/koneksi.php';
include '../config/log.php';
/** @var mysqli $conn */

if (!isset($_SESSION['login'])) { die("Akses ditolak."); }

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');
$kat  = $_GET['kategori'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

$where = "tanggal_kunjungan BETWEEN '$from' AND '$to'";
if (!empty($bulan) && !empty($tahun)) {
    $where = "MONTH(tanggal_kunjungan) = '$bulan' AND YEAR(tanggal_kunjungan) = '$tahun'";
}
if (!empty($kat)) {
    $where .= " AND kategori_tamu = '$kat'";
}

$data = mysqli_query($conn, "
    SELECT tamu.*, tl.nama_tujuan 
    FROM tamu
    LEFT JOIN tujuan_layanan tl ON tamu.tujuan_id = tl.id
    WHERE $where
    ORDER BY tamu.id DESC
");

logAktivitas($conn, $_SESSION['user_id'], "Export laporan PDF");

/* ===== TEMPLATE HTML PDF ===== */
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { 
            margin: 100px 40px 60px 40px; 
        }
        body { 
            font-family: sans-serif; 
            font-size: 11px; 
            color: #333; 
        }
        
        /* HEADER */
        header { 
            position: fixed; 
            top: -75px; 
            left: 0px; 
            right: 0px; 
            height: 60px; 
            text-align: center;
            border-bottom: 2px solid #444;
            padding-bottom: 5px;
        }

        /* FOOTER */
        footer { 
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px; 
            height: 30px; 
            text-align: center;
            font-size: 9px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .pagenum:before { 
            content: counter(page); 
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed;
            word-wrap: break-word;
        }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; text-transform: uppercase; }
        th, td { 
            border: 1px solid #666; 
            padding: 7px 4px; 
            vertical-align: middle; 
        }

        /* PENGATURAN WIDTH KOLOM - Nomor dibuat sangat ramping */
        .col-no { width: 25px; } 
        .col-nama { width: 110px; }
        .col-instansi { width: 120px; }
        .col-hp { width: 90px; }
        .col-guru { width: 110px; }
        .col-tgl { width: 75px; }
        .col-status { width: 65px; }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h2 style="margin:0; font-size: 18px;">LAPORAN KUNJUNGAN TAMU</h2>
        <p style="margin:5px 0 0 0; font-size: 12px;">Periode: ' . date('d-m-Y', strtotime($from)) . ' s/d ' . date('d-m-Y', strtotime($to)) . '</p>
    </header>

    <footer>
        Dicetak pada: ' . date('d-m-Y H:i:s') . ' | Halaman <span class="pagenum"></span>
    </footer>

    <main>
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nama">Nama</th>
                    <th class="col-instansi">' . ($kat == 'siswa' ? 'NISN / Univ' : 'Instansi') . '</th>
                    <th class="col-hp">No HP</th>
                    <th class="col-guru">Menemui</th>
                    <th class="col-tgl">Tanggal</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>';

$no = 1;
while ($t = mysqli_fetch_assoc($data)) {
    $instansi_val = ($t['kategori_tamu'] == 'siswa') ? $t['nisn'] . ($t['universitas'] ? " / " . $t['universitas'] : "") : ($t['instansi'] ?: '-');
    $status_text = ucfirst($t['status']);
    if($t['status'] == 'diterima') $status_text = 'Diterima';

    $html .= '
                <tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td class="font-bold">' . htmlspecialchars($t['nama']) . '</td>
                    <td>' . htmlspecialchars($instansi_val) . '</td>
                    <td>' . htmlspecialchars($t['no_hp']) . '</td>
                    <td>' . htmlspecialchars(displayPurpose($t)) . '</td>
                    <td class="text-center">' . date('d-m-Y', strtotime($t['tanggal_kunjungan'])) . '</td>
                    <td class="text-center">' . $status_text . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </main>
</body>
</html>';

// Bersihkan buffer
if (ob_get_length()) ob_end_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nama file format dd-mm-yyyy
$f_from = date('d-m-Y', strtotime($from));
$f_to = date('d-m-Y', strtotime($to));
$nama_file = "Laporan_Tamu_" . $f_from . "_s_d_" . $f_to . ".pdf";

$dompdf->stream($nama_file, ["Attachment" => 1]);
exit;
