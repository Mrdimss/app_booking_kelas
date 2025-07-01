<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];
$query = mysqli_query($koneksi, "
    SELECT b.*, u.username, r.nama_ruang 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    WHERE b.id = $id AND b.user_id = " . $_SESSION['user_id']
);
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan atau Anda tidak punya akses.";
    exit;
}
if ($data['status'] != 'approved') {
    echo "Booking belum disetujui. Tidak bisa dicetak.";
    exit;
}

function hariIndo($tanggal) {
    $hari = date('N', strtotime($tanggal));
    $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    return $namaHari[$hari];
}

function tanggalIndo($tanggal) {
    $bulan = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April',
        'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
        'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];
    $tgl = date('d', strtotime($tanggal));
    $bln = date('F', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));
    return "$tgl " . $bulan[$bln] . " $thn";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Booking Ruang</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f9f9f9;
            margin: 40px auto;
            max-width: 700px;
            color: #333;
        }

        .bukti {
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 30px 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 22px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        td {
            padding: 10px 5px;
            vertical-align: top;
        }

        td.label {
            width: 35%;
            font-weight: bold;
            color: #555;
        }

        .status {
            color: green;
            font-weight: bold;
        }

        .cetak {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 25px;
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }

        .btn:hover {
            background-color: #2c80b4;
        }

        @media print {
            .btn {
                display: none;
            }

            body {
                margin: 0;
                background: none;
                box-shadow: none;
            }

            .bukti {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="bukti">
        <h2>Bukti Booking Ruang Kelas</h2>
        <table>
            <tr>
                <td class="label">Nama Pengguna</td>
                <td>: <?= htmlspecialchars($data['username']) ?></td>
            </tr>
            <tr>
                <td class="label">Ruang</td>
                <td>: <?= htmlspecialchars($data['nama_ruang']) ?></td>
            </tr>
            <tr>
                <td class="label">Hari / Tanggal</td>
                <td>: <?= hariIndo($data['tanggal']) . ', ' . tanggalIndo($data['tanggal']) ?></td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td>: <?= date('H:i', strtotime($data['jam_mulai'])) ?> - <?= date('H:i', strtotime($data['jam_selesai'])) ?></td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td>: <span class="status">✅ Disetujui</span></td>
            </tr>
        </table>

        <div class="cetak">
            <button onclick="window.print()" class="btn">🖨️ Cetak Bukti</button>
        </div>
    </div>
</body>
</html>