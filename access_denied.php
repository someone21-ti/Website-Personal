<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="alert alert-danger text-center">
            <h4>Akses Ditolak</h4>
            <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            <a href="pengiriman.php" class="btn btn-warning">Kembali ke Halaman Jadwal Pengiriman</a>
        </div>
    </div>
</body>

</html>