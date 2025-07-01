<?php
session_start();
include 'config/database.php';

// Jika sudah login, arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Validasi
    if (empty($nama)) {
        $error = "Nama lengkap wajib diisi!";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $check = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$hash', 'user')");

            if ($insert) {
                // Redirect ke login dengan parameter sukses
                header("Location: login.php?register=success");
                exit;
            } else {
                $error = "Registrasi gagal. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
        body { font-family: Arial; margin: 40px; }
        form { width: 300px; margin: auto; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="container">
    <h2>Registrasi User Baru</h2>

    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nama" placeholder="Nama Lengkap" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm" placeholder="Ulangi Password" required>
        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Kembali ke Login</a></p>
</div>
</body>
</html>