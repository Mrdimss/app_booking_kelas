<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Tambah ruang
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_ruang']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $kapasitas = (int) $_POST['kapasitas'];

    mysqli_query($koneksi, "INSERT INTO rooms (nama_ruang, lokasi, kapasitas) 
        VALUES ('$nama', '$lokasi', '$kapasitas')");
    header("Location: kelola_ruang.php");
    exit;
}

// Hapus ruang
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM rooms WHERE id = '$id'");
    header("Location: kelola_ruang.php");
    exit;
}

// Edit ruang
if (isset($_POST['edit'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_ruang']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $kapasitas = (int) $_POST['kapasitas'];

    mysqli_query($koneksi, "UPDATE rooms 
        SET nama_ruang = '$nama', lokasi = '$lokasi', kapasitas = '$kapasitas' 
        WHERE id = '$id'");
    header("Location: kelola_ruang.php");
    exit;
}

// Ambil data ruang
$rooms = [];
$result = mysqli_query($koneksi, "SELECT * FROM rooms");
while ($row = mysqli_fetch_assoc($result)) {
    $rooms[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Ruang Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Kelola Ruang Kelas</h2>

    <!-- Form Tambah -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="nama_ruang" class="form-control" placeholder="Nama Ruang" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="lokasi" class="form-control" placeholder="Lokasi Ruang" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="kapasitas" class="form-control" placeholder="Kapasitas" required min="1">
        </div>
        <div class="col-auto">
            <button type="submit" name="tambah" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Tambah
            </button>
        </div>
    </form>

    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>Nama Ruang</th>
                <th>Lokasi</th>
                <th>Kapasitas</th>
                <th style="width: 200px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $row): ?>
            <tr>
                <form method="POST" class="d-flex">
                    <td>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="text" name="nama_ruang" value="<?= htmlspecialchars($row['nama_ruang']) ?>" class="form-control" required>
                    </td>
                    <td>
                        <input type="text" name="lokasi" value="<?= htmlspecialchars($row['lokasi']) ?>" class="form-control" required>
                    </td>
                    <td>
                        <input type="number" name="kapasitas" value="<?= htmlspecialchars($row['kapasitas']) ?>" class="form-control" required min="1">
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="submit" name="edit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus ruang ini?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </div>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="../dashboard.php" class="btn btn-secondary mt-3">
        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>
</body>
</html>