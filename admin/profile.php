<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<div class="row justify-content-center">

    <div class="col-md-6">

        <div class="card p-4 text-center">

            <!-- AVATAR -->
            <div class="mb-3">
                <div class="avatar avatar-xl mx-auto bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                    <h2><?= strtoupper(substr($username, 0, 1)); ?></h2>
                </div>
            </div>

            <h5 class="mb-1"><?= $username; ?></h5>

            <p class="text-muted mb-3"><?= ucfirst($role); ?></p>

            <hr>

            <div class="text-start">

                <p><strong>Username:</strong> <?= $username; ?></p>
                <p><strong>Role:</strong> <?= $role; ?></p>

            </div> 

        </div>

    </div>

</div>

<?php include '../layouts/footer.php'; ?>
