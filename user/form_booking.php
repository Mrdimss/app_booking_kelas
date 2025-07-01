<?php
session_start();
include '../config/database.php';

// Pastikan user sudah login dan berperan sebagai 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

// Ambil data ruang dari database
$result = mysqli_query($koneksi, "SELECT * FROM rooms");

// Proses form booking
if (isset($_POST['submit'])) {
    $user_id     = $_SESSION['user_id'];
    $room_id     = $_POST['room_id'];
    $tanggal     = $_POST['tanggal'];
    $jam_mulai   = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    // Cek bentrok jadwal
    $cek = mysqli_query($koneksi, "
        SELECT * FROM bookings 
        WHERE room_id = '$room_id'
          AND tanggal = '$tanggal'
          AND (
              (jam_mulai < '$jam_selesai' AND jam_selesai > '$jam_mulai')
          )
          AND status = 'approved'
    ");

    if (mysqli_num_rows($cek) > 0) {
        $error = "Gagal booking: Ruang sudah dibooking pada waktu tersebut!";
    } else {
        $query = "INSERT INTO bookings (user_id, room_id, tanggal, jam_mulai, jam_selesai, status)
                  VALUES ('$user_id', '$room_id', '$tanggal', '$jam_mulai', '$jam_selesai', 'pending')";
        if (mysqli_query($koneksi, $query)) {
            $success = "Booking berhasil! Menunggu persetujuan admin.";
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Booking Ruang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Booking Ruang Kelas</h5>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php elseif (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="room_id" class="form-label">Ruang</label>
                    <select name="room_id" id="room_id" class="form-select" required>
                        <option value="">-- Pilih Ruang --</option>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama_ruang']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="jam_selesai" class="form-label">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Kirim Booking</button>
                <a href="../dashboard.php" class="btn btn-secondary ms-2">Kembali ke Dashboard</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>