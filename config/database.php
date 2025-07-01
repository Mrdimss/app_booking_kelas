<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "booking_kelas";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>