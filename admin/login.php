<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
require_once '../config/log.php';
/** @var mysqli $conn */

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = md5($_POST['password']);

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE username = ? AND password = ? LIMIT 1"
    );

    $stmt->bind_param("ss", $username, $password);

    $stmt->execute();

    $result = $stmt->get_result();

    $data = $result->fetch_assoc();

    if ($data) {

        $_SESSION['login'] = true;
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['user_id'] = $data['id'];

        logAktivitas(
            $conn,
            $_SESSION['user_id'],
            "Login ke sistem"
        );

        $stmt->close();

        header("Location: dashboard.php");
        exit;

    } else {

        $error = "Username atau password salah!";

    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Attendya</title>

    <link href="../assets/css/soft-ui-dashboard.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

   <style>
    :root{
        --premium-green-dark: #0f3d2e;
        --premium-green: #1d6b52;
        --premium-green-soft: #2f8a6a;

        --gold-light: #f3d38b;
        --gold-main: #d4a64f;
        --gold-dark: #b8862f;

        --cream-bg: #f8f6f1;
    }

    body {
        margin: 0;
        font-family: 'Open Sans', sans-serif;
        background: var(--cream-bg);
    }

    .login-container {
        display: flex;
        min-height: 100vh;
    }

    /* LEFT LOGIN AREA */
    .login-left {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;

        background:
            radial-gradient(circle at top left, rgba(212,166,79,0.10), transparent 30%),
            radial-gradient(circle at bottom right, rgba(29,107,82,0.10), transparent 35%),
            linear-gradient(135deg, #faf8f3, #f4efe4);

        position: relative;
    }

    .login-card {
        width: 100%;
        max-width: 380px;
    }

    /* RIGHT IMAGE AREA */
    .login-right {
        flex: 1;

        background:
            linear-gradient(rgba(15,61,46,0.35), rgba(15,61,46,0.35)),
            url('../assets/img/curved-images/curved2.jpeg') no-repeat center center;

        background-size: cover;
    }

    /* BACK BUTTON */
    .btn-back {
        position: absolute;
        top: 20px;
        left: 20px;
        font-size: 24px;

        color: var(--premium-green) !important;

        text-decoration: none;
        z-index: 100;

        background: rgba(255, 255, 255, 0.85);

        width: 45px;
        height: 45px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    /* TITLE */
    .text-primary {
        color: var(--premium-green) !important;
    }

    .text-muted {
        color: #6b7280 !important;
    }

    /* INPUT */
    .form-control {
        border-radius: 14px !important;
        border: 1px solid rgba(29,107,82,0.15) !important;
        background: rgba(255,255,255,0.9) !important;

        transition: 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--gold-main) !important;

        box-shadow:
            0 0 0 0.20rem rgba(212,166,79,0.18) !important;
    }

    /* BUTTON LOGIN */
   /* BUTTON LOGIN */
.btn-gradient {
    background: linear-gradient(
        135deg,
        #145c43,
        #1d6b52,
        #2f8a6a
    ) !important;

    border: none !important;
    color: white !important;

    border-radius: 16px;

    font-weight: 700;
    letter-spacing: 0.5px;

    box-shadow: 0 10px 24px rgba(29,107,82,0.28);

    transition: 0.3s ease;
}

.btn-gradient:hover {
    transform: translateY(-2px);

    background: linear-gradient(
        135deg,
        #1d6b52,
        #2f8a6a,
        #3ea27e
    ) !important;

    box-shadow: 0 14px 30px rgba(29,107,82,0.35);
}

    /* SWITCH */
    .form-check-input:checked {
        background-color: var(--premium-green) !important;
        border-color: var(--premium-green) !important;
    }

    /* MOBILE */
    @media (max-width: 991px) {

        .login-container {
            display: block;
        }

        .login-right {
            display: none;
        }

        .login-left {
            padding: 80px 20px 40px;

            background:
                linear-gradient(135deg, #faf8f3, #f4efe4);

            min-height: 100vh;
        }

        .login-card {
            background: rgba(255,255,255,0.75);

            backdrop-filter: blur(15px);

            box-shadow: 0 15px 35px rgba(15,61,46,0.08);

            border-radius: 24px;

            padding: 30px 25px;
        }

        .btn-back {
            top: 15px;
            left: 15px;
            background: white;
        }
    }
</style>
</head>

<body>
    <!-- Tombol Back -->
    <a href="../index.php" class="btn-back">
        ←
    </a>

    <div class="login-container">

        <!-- LEFT - FORM -->
        <div class="login-left">
            <div class="login-card">

                <div class="text-center mb-4">
                    <img src="../assets/img/logoo.png" alt="Logo" style="height: 65px;">
                    <h3 class="mt-3 fw-bold text-primary">Selamat Datang Kembali</h3>
                    <p class="text-muted">Masuk ke sistem Attendya</p>
                </div>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label">Ingat saya</label>
                    </div>
                    <button type="submit" name="login" class="btn btn-gradient w-100 py-3">
                        MASUK
                    </button>
                </form>

            </div>
        </div>

        <!-- RIGHT - GAMBAR (Desktop Only) -->
        <div class="login-right"></div>

    </div>

</body>

</html>