<?php
session_start();
include '../config/database.php';

// Pastikan user login sebagai 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data booking berdasarkan user
$query = mysqli_query($koneksi, "
    SELECT b.*, r.nama_ruang 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = '$user_id'
    ORDER BY b.tanggal DESC, b.jam_mulai DESC
");

// Fungsi hari dan tanggal dalam Bahasa Indonesia
function hari_indo($tanggal) {
    $hari = date('N', strtotime($tanggal));
    $nama_hari = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu'
    ];
    return $nama_hari[$hari];
}

function formatTanggalIndo($tanggal) {
    $bulanIndo = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    ];

    $tgl = date('d', strtotime($tanggal));
    $bln = date('F', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));

    return "$tgl {$bulanIndo[$bln]} $thn";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Riwayat Booking Anda</h5>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ruang</th>
                            <th>Tanggal</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_ruang']) ?></td>
                                    <td><?= hari_indo($row['tanggal']) . ', ' . formatTanggalIndo($row['tanggal']) ?></td>
                                    <td><?= date('H:i', strtotime($row['jam_mulai'])) ?></td>
                                    <td><?= date('H:i', strtotime($row['jam_selesai'])) ?></td>
                                    <td>
                                        <?php
                                            if ($row['status'] == 'pending') echo "⏳ Menunggu";
                                            elseif ($row['status'] == 'approved') echo "✅ Disetujui";
                                            elseif ($row['status'] == 'rejected') echo "❌ Ditolak";
                                            else echo htmlspecialchars($row['status']);
                                        ?>
                                        <?php if ($row['status'] == 'approved'): ?>
                                            <br>
                                            <a href="../cetak.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success mt-1" target="_blank">🖨️ Cetak</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-muted">Belum ada booking.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <a href="../dashboard.php" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>