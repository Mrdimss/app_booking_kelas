<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'user';
$nama = $_SESSION['nama'] ?? '';

// Fungsi hari dan tanggal bahasa Indonesia
function hariIndo($tanggal) {
    $hariInggris = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    $hariIndonesia = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    return str_replace($hariInggris, $hariIndonesia, date('l', strtotime($tanggal)));
}

function tanggalIndo($tanggal) {
    $bulanInggris = [
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];
    $bulanIndonesia = [
        'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];
    return str_replace($bulanInggris, $bulanIndonesia, date('d F Y', strtotime($tanggal)));
}

// Data ketersediaan ruang
date_default_timezone_set('Asia/Jakarta');
$nowDate = date('Y-m-d');
$nowTime = date('H:i');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistem Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Sistem Booking</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($nama) ?> (<?= htmlspecialchars($role) ?>)
                </span>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container mt-5">
        <h3 class="mb-4 text-center">Selamat Datang di Sistem Booking Ruang</h3>

        <div class="row justify-content-center">
            <?php if ($role === 'admin'): ?>
                <!-- Menu Admin -->
                <div class="col-md-4 mb-3">
                    <div class="card border-primary shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><i class="bi bi-people-fill me-2"></i>Kelola User</h5>
                            <p class="card-text">Tambah, edit, atau hapus pengguna.</p>
                            <a href="admin/kelola_user.php" class="btn btn-primary">Kelola User</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-success shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><i class="bi bi-journal-text me-2"></i>Kelola Booking</h5>
                            <p class="card-text">Lihat dan kelola data pemesanan.</p>
                            <a href="admin/kelola_booking.php" class="btn btn-success">Kelola Booking</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-warning shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><i class="bi bi-building me-2"></i>Kelola Ruang</h5>
                            <p class="card-text">Atur dan tambah ruang kelas.</p>
                            <a href="admin/kelola_ruang.php" class="btn btn-warning">Kelola Ruang</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Menu User -->
                <div class="col-md-5 mb-3">
                    <div class="card border-primary shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><i class="bi bi-pencil-square me-2"></i>Form Booking</h5>
                            <p class="card-text">Pesan ruang kelas sesuai kebutuhan Anda.</p>
                            <a href="user/form_booking.php" class="btn btn-primary">Booking Sekarang</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 mb-3">
                    <div class="card border-success shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><i class="bi bi-calendar-check me-2"></i>Riwayat Booking</h5>
                            <p class="card-text">Lihat status dan histori pemesanan Anda.</p>
                            <a href="user/riwayat_booking.php" class="btn btn-success">Lihat Riwayat</a>
                        </div>
                    </div>
                </div>
        </div>

        <!-- Ketersediaan Ruangan -->
        <div class="mt-5">
            <h4 class="text-center mb-3"><i class="bi bi-building-check me-2"></i>Ketersediaan Ruang Hari Ini</h4>
            <p class="text-center">
                Tanggal: <strong><?= hariIndo($nowDate) . ', ' . tanggalIndo($nowDate) ?></strong> —
                Waktu sekarang: <strong id="jam-sekarang"></strong> WIB
            </p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Nama Ruang</th>
                            <th>Kapasitas</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-center" id="data-ruang">
                        <tr><td colspan="4">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk update status ruang
        function loadKetersediaan() {
            fetch('cek_ketersediaan_ajax.php')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('data-ruang');
                    tbody.innerHTML = '';

                    data.forEach(ruang => {
                        const badge = ruang.status === 'Dipakai' ? 'danger' : 'success';
                        const icon = ruang.status === 'Dipakai' ? 'bi-x-circle-fill' : 'bi-check-circle-fill';

                        tbody.innerHTML += `
                            <tr>
                                <td>${ruang.nama}</td>
                                <td>${ruang.kapasitas} orang</td>
                                <td>${ruang.lokasi}</td>
                                <td>
                                    <span class="badge bg-${badge}">
                                        <i class="bi ${icon} me-1"></i>${ruang.status}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(() => {
                    document.getElementById('data-ruang').innerHTML = '<tr><td colspan="4">Gagal memuat data</td></tr>';
                });
        }

        // Fungsi untuk update jam sekarang
        function updateJamSekarang() {
            const now = new Date();
            const jam = now.getHours().toString().padStart(2, '0');
            const menit = now.getMinutes().toString().padStart(2, '0');
            document.getElementById('jam-sekarang').textContent = `${jam}:${menit}`;
        }

        // Jalankan saat awal
        loadKetersediaan();
        updateJamSekarang();

        // Update otomatis
        setInterval(loadKetersediaan, 5000);      // update status ruang tiap 5 detik
        setInterval(updateJamSekarang, 1000);    // update jam tiap 1 detik
    </script>
</body>
</html>