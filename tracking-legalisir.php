<?php
include 'config/koneksi.php';

if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Legalisir - Attendya</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/theme-premium-green-gold.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #ffffff !important;
            background-image: 
                radial-gradient(circle at 0% 40%, rgba(0, 103, 79, 0.08) 0%, rgba(255, 255, 255, 0) 50%),
                radial-gradient(circle at 100% 20%, rgba(0, 103, 79, 0.08) 8%, rgba(255, 255, 255, 0) 60%),
                linear-gradient(135deg, rgba(0, 103, 79, 0.02) 0%, rgba(226, 190, 122, 0.02) 100%) !important;
            background-attachment: fixed !important;
            background-size: cover !important;
        }

        .search-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .search-box {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px 30px;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .search-input {
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            padding: 12px 20px;
            width: calc(100% - 100px);
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #00674F;
            box-shadow: 0 0 0 0.25rem rgba(0, 103, 79, 0.15);
        }

        .search-button {
            background: linear-gradient(135deg, #00674F 0%, #56ab81 100%);
            border: none;
            border-radius: 25px;
            color: white;
            padding: 12px 25px;
            font-size: 16px;
            cursor: pointer;
            margin-left: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 103, 79, 0.2);
        }

        .search-button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .modal-content {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: none;
            padding: 20px 25px 10px;
        }

        .modal-title {
            font-weight: bold;
            color: #333;
        }

        .modal-body {
            padding: 10px 25px 25px;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
            font-size: 0.85rem;
        }

        .status-progres {
            background: linear-gradient(310deg, #11cdef, #1171ef);
            color: white;
        }

        .status-selesai {
            background: linear-gradient(310deg, #2dce89, #2dcecc);
            color: white;
        }

        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
            color: #00674F;
            font-size: 1.5rem;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <a href="index.php" class="back-to-home"><i class="bi bi-arrow-left-circle-fill"></i></a>

    <div class="search-container">
        <div class="search-box">
            <img src="assets/img/logoo.png" alt="Logo" style="height: 70px; margin-bottom: 20px;">
            <h2 class="mb-4">Tracking Status Legalisir</h2>
            <form id="nisn-tracking-form" class="d-flex justify-content-center">
                <input type="text" id="nisn-input" name="nisn" class="search-input" placeholder="Masukkan NISN Anda..." required oninput="this.value = this.value.replace(/\D/g, '')" maxlength="10">
                <button type="submit" class="search-button">Cari</button>
            </form>
        </div>
    </div>

    <!-- Modal Hasil Tracking -->
    <div class="modal fade" id="trackingResultModal" tabindex="-1" aria-labelledby="trackingResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trackingResultModalLabel">Detail Legalisir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="trackingResultBody">
                    <!-- Konten hasil tracking akan dimuat di sini -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#nisn-tracking-form').on('submit', function(e) {
                e.preventDefault();
                const nisn = $('#nisn-input').val();
                const button = $('.search-button');

                if (nisn.length !== 10) {
                    Swal.fire('Peringatan', 'NISN harus 10 digit angka.', 'warning');
                    return;
                }

                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');
                $.ajax({
                    url: 'get_legalisir_status.php',
                    method: 'GET',
                    data: { nisn: nisn },
                    dataType: 'json',
                    success: function(response) {
                        const modalBody = $('#trackingResultBody');
                        modalBody.empty();

                        if (response.success && response.data) {
                            const data = response.data;
                            let statusBadgeClass = '';
                            let statusText = '';

                            if (data.status === 'progres') {
                                statusBadgeClass = 'status-progres';
                                statusText = 'Sedang Diproses';
                            } else if (data.status === 'selesai') {
                                statusBadgeClass = 'status-selesai';
                                statusText = 'Selesai';
                            } else {
                                statusBadgeClass = 'bg-secondary'; // Default jika status lain
                                statusText = data.status;
                            }

                            modalBody.append(`
                                <p><strong>Nama:</strong> ${data.nama}</p>
                                <p><strong>NISN:</strong> ${data.nisn}</p>
                                <p><strong>Tanggal Kunjungan:</strong> ${data.tanggal_kunjungan}</p>
                                <p><strong>Status Legalisir:</strong> <span class="status-badge ${statusBadgeClass}">${statusText}</span></p>
                            `);
                        } else {
                            modalBody.append('<p>Data legalisir tidak ditemukan atau belum selesai.</p>');
                        }
                        $('#trackingResultModal').modal('show');
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan saat mengambil data.', 'error');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Cari');
                    }
                });
            });
        });
    </script>
</body>

</html>
