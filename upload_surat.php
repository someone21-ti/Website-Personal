<?php
require 'function.php';
require 'cek.php';

if ($_SESSION['role'] != 'staff_gudang') {
    echo "Akses ditolak!";
    exit;
}

$id = $_GET['id'];

if (isset($_POST['upload'])) {
    $nama_file = $_FILES['surat']['name'];
    $tmp_file = $_FILES['surat']['tmp_name'];
    $folder = 'file/' . $nama_file;

    // Validasi ekstensi MIME
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    $file_type = mime_content_type($tmp_file);

    if (in_array($file_type, $allowed_types)) {
        if (move_uploaded_file($tmp_file, $folder)) {
            // Update database
            $update = mysqli_query($conn, "UPDATE pengiriman 
                                           SET surat_jalan = '$nama_file', status = 'dalam_proses' 
                                           WHERE id_pengiriman = '$id'");

            if ($update) {
                header("Location: pengiriman.php");
                exit();
            } else {
                echo "Gagal menyimpan ke database!";
            }
        } else {
            echo "Upload file gagal!";
        }
    } else {
        echo "<script>alert('Format file tidak didukung. Hanya PDF, JPG, atau PNG.'); window.location='pengiriman.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Upload Surat Jalan</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">
        <h3>Upload Surat Jalan</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="surat">Pilih File Surat Jalan (PDF/JPG/PNG):</label>
                <input type="file" name="surat" class="form-control" required>
            </div>
            <button type="submit" name="upload" class="btn btn-primary">Upload</button>
            <a href="pengiriman.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>

</html>