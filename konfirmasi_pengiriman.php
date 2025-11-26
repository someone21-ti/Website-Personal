<?php
require 'function.php';
require 'cek.php';

if ($_SESSION['role'] != 'kepala_gudang') {
    echo "Akses ditolak!";
    exit;
}

$id = $_GET['id'];
$username = $_SESSION['username'];

// Ubah status pengiriman menjadi 'dikonfirmasi'
$update = mysqli_query($conn, "UPDATE pengiriman 
                               SET status = 'dikonfirmasi', dikonfirmasi_oleh = '$username' 
                               WHERE id_pengiriman = '$id'");

if ($update) {
    header("Location: pengiriman.php");
    exit();
} else {
    echo "Gagal mengonfirmasi pengiriman!";
}
