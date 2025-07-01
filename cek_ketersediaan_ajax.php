<?php
include 'config/database.php';
date_default_timezone_set('Asia/Jakarta');

$nowDate = date('Y-m-d');
$nowTime = date('H:i');

$result = mysqli_query($koneksi, "SELECT * FROM rooms");
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $room_id = $row['id'];

    // Cek status
    $q = mysqli_query($koneksi, "
        SELECT * FROM bookings 
        WHERE room_id = '$room_id'
        AND tanggal = '$nowDate'
        AND status = 'approved'
        AND '$nowTime' BETWEEN jam_mulai AND jam_selesai
    ");
    $status = (mysqli_num_rows($q) > 0) ? 'Dipakai' : 'Tersedia';

    $data[] = [
        'nama' => $row['nama_ruang'],
        'status' => $status,
        'kapasitas' => $row['kapasitas'],
        'lokasi' => $row['lokasi']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>