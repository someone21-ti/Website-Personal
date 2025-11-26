<?php

session_start();
//koneksi database
$conn = mysqli_connect("localhost", "root", "", "pkl");


// Barang Masuk
if (isset($_POST['tambahbarang'])) {
    // Ambil data dari form
    $barangnya = $_POST['barangnya'];
    $tgl_muat = $_POST['tgl_muat'];
    $tgl_produksi = $_POST['tgl_produksi'];
    $jumlah = (int)$_POST['jumlah'];  // Pastikan jumlah bertipe integer
    $bg = $_POST['bg'];

    // Ambil data barang dari tabel stok
    $ambildata = mysqli_query($conn, "SELECT nm_barang, jenis, jumlah FROM stok WHERE id_stok = '$barangnya'");

    if ($ambildata && mysqli_num_rows($ambildata) > 0) {
        $data = mysqli_fetch_assoc($ambildata);
        $nm_barang = $data['nm_barang'];
        $jenis = $data['jenis'];
        $stoksekarang = (int)$data['jumlah'];

        // Tambahkan jumlah stok
        $tambahstoksekarang = $stoksekarang + $jumlah;

        // Insert ke barang_masuk
        $addtomasuk = mysqli_query($conn, "INSERT INTO barang_masuk (id_stok, nm_barang, jenis, tgl_muat, tgl_produksi, jumlah, bg) 
                                           VALUES ('$barangnya', '$nm_barang', '$jenis', '$tgl_muat', '$tgl_produksi', '$jumlah', '$bg')");

        // Update stok
        $updatestockmasuk = mysqli_query($conn, "UPDATE stok SET jumlah = '$tambahstoksekarang' WHERE id_stok = '$barangnya'");

        if ($addtomasuk && $updatestockmasuk) {
            header('Location: masuk.php');
            exit();
        } else {
            echo 'Gagal menambahkan data: ' . mysqli_error($conn);
        }
    } else {
        echo 'Data tidak ditemukan untuk ID Stok: ' . $barangnya;
    }
}



//tambah barang baru 
if (isset($_POST['barangbaru'])) {
    $nm_barang = $_POST['nm_barang'];
    $jenis = $_POST['jenis'];
    $jumlah = $_POST['jumlah'];

    $addtostok = mysqli_query($conn, "insert into stok (nm_barang,jenis,jumlah) values('$nm_barang','$jenis','$jumlah')");

    if ($addtostok) {
        header('location:index.php');
    } else {
        echo 'gagal';
        header('location:index.php');
    }
}

// Barang Keluar
if (isset($_POST['keluarbarang'])) {
    // Ambil data dari form
    $barangnya = $_POST['barangnya'];  // id_stok dari form
    $tujuan = $_POST['tujuan'];
    $jumlah = (int)$_POST['jumlah'];  // Pastikan jumlah bertipe integer
    $bg = $_POST['bg'];

    // Ambil data barang dari tabel stok
    $ambildata = mysqli_query($conn, "SELECT nm_barang, jumlah FROM stok WHERE id_stok = '$barangnya'");

    if ($ambildata && mysqli_num_rows($ambildata) > 0) {
        $data = mysqli_fetch_assoc($ambildata);
        $nm_barang = $data['nm_barang'];
        $stoksekarang = (int)$data['jumlah'];

        // Cek apakah stok mencukupi
        if ($stoksekarang >= $jumlah) {
            $kurangistoksekarang = $stoksekarang - $jumlah;

            // Insert ke tabel barang_keluar dengan id_stok
            $addtokeluar = mysqli_query($conn, "INSERT INTO barang_keluar (id_stok, tujuan, nm_barang, jumlah, bg, tgl_kirim) 
                                                VALUES ('$barangnya', '$tujuan', '$nm_barang', '$jumlah', '$bg', CURRENT_TIMESTAMP)");

            // Update stok
            $updatestockkeluar = mysqli_query($conn, "UPDATE stok SET jumlah = '$kurangistoksekarang' WHERE id_stok = '$barangnya'");

            if ($addtokeluar && $updatestockkeluar) {
                header('Location: keluar.php');
                exit();
            } else {
                echo 'Gagal mengurangi stok: ' . mysqli_error($conn);
            }
        } else {
            echo 'Stok tidak mencukupi untuk barang: ' . $nm_barang;
        }
    } else {
        echo 'Data tidak ditemukan untuk ID Stok: ' . $barangnya;
    }
}

// Fungsi-fungsi yang sudah ada...

// Fungsi untuk update barang masuk
if (isset($_POST['updatebarang'])) {
    $id_masuk = $_POST['id_masuk'];
    $id_stok = $_POST['id_stok'];
    $old_jumlah = $_POST['old_jumlah'];
    $tgl_muat = $_POST['tgl_muat'];
    $tgl_produksi = $_POST['tgl_produksi'];
    $jumlah = $_POST['jumlah'];
    $bg = $_POST['bg'];

    // Hitung selisih jumlah untuk update stok
    $selisih = $jumlah - $old_jumlah;

    // Update data barang masuk
    $update_masuk = mysqli_query($conn, "UPDATE barang_masuk SET 
        tgl_muat='$tgl_muat',
        tgl_produksi='$tgl_produksi',
        jumlah='$jumlah',
        bg='$bg'
        WHERE id_masuk='$id_masuk'");

    // Update stok
    $update_stok = mysqli_query($conn, "UPDATE stok SET 
        jumlah = jumlah + $selisih
        WHERE id_stok='$id_stok'");

    if ($update_masuk && $update_stok) {
        echo '<script>alert("Data berhasil diupdate");</script>';
        echo '<script>window.location.href="masuk.php";</script>';
    } else {
        echo '<script>alert("Gagal mengupdate data");</script>';
    }
}

// Fungsi untuk delete barang masuk
if (isset($_GET['delete'])) {
    $id_masuk = $_GET['delete'];
    $id_stok = $_GET['id_stok'];
    $jumlah = $_GET['jumlah'];

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    try {
        // 1. Hapus data barang masuk
        $delete_masuk = mysqli_query($conn, "DELETE FROM barang_masuk WHERE id_masuk='$id_masuk'");

        if (!$delete_masuk) {
            throw new Exception("Gagal menghapus data barang masuk");
        }

        // 2. Kurangi stok
        $update_stok = mysqli_query($conn, "UPDATE stok SET jumlah = jumlah - $jumlah WHERE id_stok='$id_stok'");

        if (!$update_stok) {
            throw new Exception("Gagal mengupdate stok");
        }

        // Commit transaksi jika semua query berhasil
        mysqli_commit($conn);

        // Set session untuk alert sukses
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Data berhasil dihapus'
        ];
    } catch (Exception $e) {
        // Rollback transaksi jika ada error
        mysqli_rollback($conn);

        // Set session untuk alert error
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Gagal menghapus data: ' . $e->getMessage()
        ];
    }

    // Redirect ke halaman masuk.php
    header("Location: masuk.php");
    exit();
}
