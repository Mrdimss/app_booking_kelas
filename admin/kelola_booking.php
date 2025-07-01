<?php
session_start();
include '../config/database.php';

// Hanya untuk admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Edit booking
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $room_id = $_POST['room_id'];
    $status = $_POST['status'];

    mysqli_query($koneksi, "
        UPDATE bookings 
        SET tanggal = '$tanggal', jam_mulai = '$jam_mulai', jam_selesai = '$jam_selesai', 
            room_id = '$room_id', status = '$status'
        WHERE id = '$id'
    ");

    $_SESSION['notif'] = "✅ Booking berhasil diperbarui!";
    header("Location: kelola_booking.php");
    exit;
}

// Hapus booking
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM bookings WHERE id = '$id'");
    $_SESSION['notif'] = "🗑️ Booking berhasil dihapus!";
    header("Location: kelola_booking.php");
    exit;
}

// Ambil data booking dengan filter status
$allowed_status = ['pending', 'approved', 'rejected'];
$where = "";
$filter_status = $_GET['filter_status'] ?? '';

if (in_array($filter_status, $allowed_status)) {
    $status = mysqli_real_escape_string($koneksi, $filter_status);
    $where = "WHERE b.status = '$status'";
}

$query = mysqli_query($koneksi, "
    SELECT b.*, u.username, r.nama_ruang 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    $where
    ORDER BY b.tanggal DESC, b.jam_mulai DESC
");

// Data ruang
$rooms = mysqli_query($koneksi, "SELECT * FROM rooms");
$room_options = [];
while ($room = mysqli_fetch_assoc($rooms)) {
    $room_options[$room['id']] = $room['nama_ruang'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Booking - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light py-4">
    <div class="container">
        <h2 class="mb-4">Kelola Booking</h2>

        <!-- Notifikasi -->
        <?php if (isset($_SESSION['notif'])): ?>
            <div class="alert alert-success"><?= $_SESSION['notif'] ?></div>
            <?php unset($_SESSION['notif']); ?>
        <?php endif; ?>

        <!-- Form Filter -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <select name="filter_status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>⏳ Menunggu</option>
                    <option value="approved" <?= $filter_status === 'approved' ? 'selected' : '' ?>>✅ Disetujui</option>
                    <option value="rejected" <?= $filter_status === 'rejected' ? 'selected' : '' ?>>❌ Ditolak</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>

        <!-- Tabel Booking -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Ruang</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($query) == 0): ?>
                    <tr><td colspan="6">Tidak ada data booking</td></tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan perubahan ini?')">
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>
                                    <select name="room_id" class="form-select form-select-sm">
                                        <?php foreach ($room_options as $id => $nama): ?>
                                            <option value="<?= $id ?>" <?= $id == $row['room_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($nama) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="date" name="tanggal" value="<?= $row['tanggal'] ?>" class="form-control form-control-sm" required></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <input type="time" name="jam_mulai" value="<?= $row['jam_mulai'] ?>" class="form-control form-control-sm" required>
                                        <span>-</span>
                                        <input type="time" name="jam_selesai" value="<?= $row['jam_selesai'] ?>" class="form-control form-control-sm" required>
                                    </div>
                                </td>
                                <td>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>⏳ Menunggu</option>
                                        <option value="approved" <?= $row['status'] === 'approved' ? 'selected' : '' ?>>✅ Disetujui</option>
                                        <option value="rejected" <?= $row['status'] === 'rejected' ? 'selected' : '' ?>>❌ Ditolak</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="edit" class="btn btn-sm btn-success" title="Simpan">
                                        <i class="bi bi-save"></i>
                                    </button>
                                    <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus booking ini?')" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="../dashboard.php" class="btn btn-secondary mt-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</body>
</html>