<?php
require_once '../config/app.php';
include '../config/koneksi.php';
/** @var mysqli $conn */

requireLogin('login.php');

if (
    $_SESSION['role'] !== 'resepsionis' &&
    $_SESSION['role'] !== 'admin'
) {
    header("Location: dashboard.php");
    exit;
}

$file = '../config/kepsek.php';

$data = require $file;

$statusAda = $data['status'];
$namaKepalaSekolah = $data['nama'];
$fotoUrl = $data['foto'];

$isAdmin = $_SESSION['role'] === 'admin';
$isResepsionis = $_SESSION['role'] === 'resepsionis';

/* ================= UPDATE STATUS ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ADMIN EDIT PROFIL
    if (
        isset($_POST['update_profile']) &&
        $_SESSION['role'] === 'admin'
    ) {

        $data['nama'] = $_POST['nama'];
        if (!empty($_FILES['foto']['name'])) {

    $namaFile = time() . '_' . $_FILES['foto']['name'];

    move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        '../assets/img/' . $namaFile
    );

    $data['foto'] = 'assets/img/' . $namaFile;
}

    }

    // RESEPSIONIS TOGGLE STATUS
    if (
        isset($_POST['toggle_status']) &&
        $_SESSION['role'] === 'resepsionis'
    ) {

        $data['status'] = $_POST['status'] == '1';

    }

    $content = "<?php\n\nreturn " . var_export($data, true) . ";";

    file_put_contents($file, $content);

    header("Location: tracking-kepsek.php");
    exit;
}

$statusAda = $data['status'];
$namaKepalaSekolah = $data['nama'];
$fotoUrl = $data['foto'];

/* ================= LAYOUT ================= */

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<style>
    .tracking-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .toggle-bg {
        width: 64px;
        height: 36px;
        border-radius: 999px;
        transition: 0.3s;
        position: relative;
    }

    .toggle-circle {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: white;
        position: absolute;
        top: 4px;
        left: 4px;
        transition: 0.3s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .toggle-active {
        background: #10b981;
    }

    .toggle-inactive {
        background: #cbd5e1;
    }

    .toggle-circle.active {
        transform: translateX(28px);
    }
</style>

<div class="container-fluid py-4">

    <div class="card tracking-card mx-auto p-4 text-center"
         style="max-width: 500px;">

        <h3 class="fw-bold mb-4">
            Tracking Kepala Sekolah
        </h3>

        <img
    src="../<?= htmlspecialchars($fotoUrl) ?>"
    alt="Kepala Sekolah"
    class="mx-auto shadow"
    style="
        width:140px;
        height:140px;
        object-fit:cover;
        border-radius:20px;
    "
>

        <h4 class="mt-4 fw-bold">
            <?= htmlspecialchars($namaKepalaSekolah) ?>
        </h4>

        <p class="text-muted mb-4">
            Kepala Sekolah
        </p>

        <?php if ($isResepsionis): ?>

<form method="POST">

    <input type="hidden" name="toggle_status" value="1">

    <div class="d-flex align-items-center justify-content-center gap-3">

        <span class="fw-semibold text-secondary">
            Tidak Ada
        </span>

        <button
            type="submit"
            name="status"
            value="<?= $statusAda ? '0' : '1' ?>"
            style="border:none; background:none;"
        >

            <div class="toggle-bg <?= $statusAda ? 'toggle-active' : 'toggle-inactive' ?>">

                <div class="toggle-circle <?= $statusAda ? 'active' : '' ?>"></div>

            </div>

        </button>

        <span class="fw-semibold <?= $statusAda ? 'text-success' : 'text-secondary' ?>">
            Ada
        </span>

    </div>

</form>

<?php endif; ?>

<?php if ($isAdmin): ?>

<form method="POST" enctype="multipart/form-data" class="mb-4">

    <input type="hidden" name="update_profile" value="1">

    <div class="mb-3 text-start">
        <label class="form-label fw-semibold">
            Nama Kepala Sekolah
        </label>

        <input
            type="text"
            name="nama"
            class="form-control"
            value="<?= htmlspecialchars($namaKepalaSekolah) ?>"
            required
        >
    </div>

    <div class="mb-3 text-start">
        <label class="form-label fw-semibold">
            Foto Kepala Sekolah
        </label>

        <input
            type="file"
            name="foto"
            class="form-control"
            accept="image/*"
        >
    </div>

    <button
        type="submit"
        class="btn btn-primary w-100 rounded-4"
    >
        Simpan Profil
    </button>

</form>

<?php endif; ?>

        <div class="mt-4 p-3 rounded-4 fw-semibold
            <?= $statusAda
                ? 'bg-success-subtle text-success'
                : 'bg-danger-subtle text-danger'
            ?>">

            Status:
            <?= $statusAda
                ? 'Ada di Sekolah'
                : 'Sedang dinas di luar'
            ?>

        </div>

    </div>

</div>

<?php include '../layouts/footer.php'; ?>