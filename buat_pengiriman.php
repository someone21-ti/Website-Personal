<?php
require 'function.php';
require 'cek.php';

if ($_SESSION['role'] != 'kepala_logistik') {
    echo "Akses ditolak!";
    exit;
}

if (isset($_POST['submit'])) {
    $tujuan = $_POST['tujuan'];
    $tanggal = $_POST['tanggal'];
    $dibuat_oleh = $_SESSION['username'];

    $insert = mysqli_query($conn, "INSERT INTO pengiriman (tujuan, tanggal_pengiriman, dibuat_oleh) 
                                   VALUES ('$tujuan', '$tanggal', '$dibuat_oleh')");

    if ($insert) {
        header("Location: pengiriman.php");
        exit();
    } else {
        echo "Gagal menambahkan pengiriman!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Buat Pengiriman</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">
        <h3>Form Jadwal Pengiriman</h3>
        <form method="POST">
            <div class="form-group">
                <label>Tujuan</label>
                <input type="text" name="tujuan" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Tanggal Pengiriman</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <button class="btn btn-success" name="submit">Jadwalkan</button>
            <a href="pengiriman.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>

</html>