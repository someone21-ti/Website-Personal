<?php
require 'function.php';
require 'cek.php';

if ($_SESSION['role'] != 'supir') {
    echo "Akses ditolak!";
    exit;
}

$id = $_GET['id'];

if (isset($_POST['upload'])) {
    $nama_file = $_FILES['bukti']['name'];
    $tmp_file = $_FILES['bukti']['tmp_name'];
    $folder = 'file/' . $nama_file;

    // Validasi ekstensi MIME
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    $file_type = mime_content_type($tmp_file);

    if (in_array($file_type, $allowed_types)) {
        // Upload file ke folder
        if (move_uploaded_file($tmp_file, $folder)) {
            // Update ke database
            $update = mysqli_query($conn, "UPDATE pengiriman 
                                           SET bukti_pengiriman = '$nama_file', status = 'selesai' 
                                           WHERE id_pengiriman = '$id'");

            if ($update) {
                header("Location: pengiriman.php");
                exit();
            } else {
                echo "Gagal menyimpan ke database!";
            }
        } else {
            echo "Gagal upload file!";
        }
    } else {
        echo "<script>alert('Format file tidak didukung. Hanya JPG, PNG, atau PDF.'); window.location='pengiriman.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Upload Bukti Pengiriman</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">
        <h3>Upload Bukti Pengiriman</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bukti">Pilih File Bukti (PDF/JPG/PNG):</label>
                <input type="file" name="bukti" class="form-control" required>
            </div>
            <button type="submit" name="upload" class="btn btn-primary">Upload</button>
            <a href="pengiriman.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>

</html>