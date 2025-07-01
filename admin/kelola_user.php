<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses Tambah User
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan!');</script>";
    } else {
        mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')");
        echo "<script>window.location='kelola_user.php';</script>";
    }
}

// Proses Hapus User
if (isset($_POST['hapus'])) {
    $hapus_id = intval($_POST['hapus_id']);
    if ($_SESSION['user_id'] == $hapus_id) {
        echo "<script>alert('Tidak bisa menghapus akun Anda sendiri.');</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM users WHERE id = $hapus_id");
        echo "<script>window.location='kelola_user.php';</script>";
    }
}

// Proses Ganti Password
if (isset($_POST['update_password'])) {
    $id            = intval($_POST['edit_id']);
    $new_password  = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE users SET password='$new_password' WHERE id=$id");
    echo "<script>alert('Password berhasil diupdate'); window.location='kelola_user.php';</script>";
}

// Ambil data user
$result = mysqli_query($koneksi, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4">Kelola User</h3>

    <!-- Form Tambah User -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Tambah User</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="tambah" class="btn btn-primary">Tambah User</button>
            </form>
        </div>
    </div>

    <!-- Tabel User -->
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
        <tr>
            <th style="width:5%;">No</th>
            <th style="width:25%;">Nama</th>
            <th style="width:25%;">Username</th>
            <th style="width:15%;">Role</th>
            <th style="width:30%;">Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): $no = 1; $modals = ""; ?>
            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($user['nama']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $user['id'] ?>">Edit Password</button>

                        <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                            <input type="hidden" name="hapus_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="hapus" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>

                <?php
                // Simpan modal edit password
                $modals .= '
                <div class="modal fade" id="editModal'.$user['id'].'" tabindex="-1" aria-labelledby="editModalLabel'.$user['id'].'" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel'.$user['id'].'">Ganti Password - '.htmlspecialchars($user['username']).'</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="edit_id" value="'.$user['id'].'">
                                <div class="mb-3">
                                    <label>Password Baru</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="4">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_password" class="btn btn-success">Simpan</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>';
                ?>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center">Belum ada data user.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal ditampilkan di sini -->
    <?= $modals ?>

    <a href="../dashboard.php" class="btn btn-secondary mt-3">
        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>