<?php
include 'config/koneksi.php';

if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

$query = mysqli_query($conn, "
    SELECT tamu.*, tl.nama_tujuan
    FROM tamu
    LEFT JOIN tujuan_layanan tl ON tamu.tujuan_id = tl.id
    WHERE (tamu.kategori_tamu = 'umum' 
           OR tamu.kategori_tamu = 'instansi' 
           OR (tamu.kategori_tamu = 'siswa' AND tamu.sub_kategori = 'biasa'))
    AND (tamu.status = 'pending' OR tamu.status = 'diterima')
    ORDER BY tamu.created_at DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kunjungan Terbaru - Attendya</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #fdf2f8 50%, #fff5f5 100%);
            padding-top: 80px;
            /* Adjust for fixed header if any */
        }

        .container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .back-to-home {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            color: #2D6A4F;
            font-size: 1.5rem;
            text-decoration: none;
        }

        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-image: linear-gradient(135deg, #2D6A4F 0%, #52B788 100%) !important;
            border: none;
            color: #fff !important;
        }

        .dataTables_filter input {
            border: 1px solid #d2d6da !important;
            border-radius: 8px !important;
            padding: 6px 12px !important;
            outline: none;
        }

        .dataTables_length select {
            appearance: none !important;
            -webkit-appearance: none !important;
            padding: 5px 10px !important;
            border-radius: 8px !important;
            border: 1px solid #d2d6da !important;
        }

        .truncate-text {
            max-width: 120px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            display: inline-block;
        }

        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.8em;
            border-radius: 0.5rem;
        }

        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #tabelKunjunganTerbaru {
            min-width: 900px;
        }
    </style>
</head>

<body>
    <a href="index.php" class="back-to-home"><i class="bi bi-arrow-left-circle-fill"></i></a>

    <div class="container">
        <h2 class="mb-4 text-center">10 Kunjungan Terbaru</h2>
        <div class="table-responsive">
            <table id="tabelKunjunganTerbaru" class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Instansi / NISN</th>
                        <th>Keperluan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    while ($t = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= e($t['nama']); ?></td>
                            <td><?= ucfirst(e($t['kategori_tamu'])); ?></td>
                            <td>
                                <?php
                                if ($t['kategori_tamu'] == 'siswa') {
                                    echo e($t['nisn']) . (empty($t['universitas']) ? '' : ' / ' . e($t['universitas']));
                                } else {
                                    echo e($t['instansi'] ?: '-');
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($t['kategori_tamu'] == 'siswa' && $t['sub_kategori'] == 'biasa') {
                                    echo e($t['tujuan'] ?: $t['nama_tujuan'] ?: '-');
                                } else {
                                    echo e($t['tujuan'] ?: $t['nama_tujuan'] ?: '-');
                                }
                                ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($t['tanggal_kunjungan'])); ?></td>
                            <td>
                                <?php if ($t['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($t['status'] == 'diterima'): ?>
                                    <span class="badge bg-success">Diterima</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabelKunjunganTerbaru').DataTable({
                "info": false,
                "paging": false, // Tidak perlu pagination karena hanya 10 data
                "searching": false, // Tidak perlu search
                "ordering": false, // Tidak perlu sorting
                "language": {
                    "zeroRecords": "Tidak ada data kunjungan terbaru."
                }
            });
        });
    </script>
</body>

</html>