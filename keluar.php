<?php
require 'function.php';
require 'cek.php';

$role = $_SESSION['role'];

// Daftar role yang diizinkan mengakses halaman ini
$allowed_roles = ['admin1', 'logistik1', 'gudang1'];

// Jika role pengguna tidak ada dalam daftar yang diizinkan, redirect atau tampilkan pesan error
if (!in_array($role, $allowed_roles)) {
    header("Location: access_denied.php"); // Redirect ke halaman akses ditolak
    exit();
}

// Validasi stok sebelum proses pengiriman
if (isset($_POST['addnewbarangkeluar'])) {
    $barangkeluar = $_POST['barangkeluar'];
    $qty = $_POST['qty'];
    $penerima = $_POST['penerima'];
    $alamat = $_POST['alamat'];

    // Cek stok tersedia
    $cekstok = mysqli_query($conn, "SELECT * FROM stok WHERE idbarang='$barangkeluar'");
    $ambildatanya = mysqli_fetch_array($cekstok);
    $stoksekarang = $ambildatanya['jumlah'];

    if ($stoksekarang >= $qty) {
        // Jika stok mencukupi
        $insert = mysqli_query($conn, "INSERT INTO keluar (idbarang, jumlah, penerima, alamat) VALUES('$barangkeluar','$qty','$penerima','$alamat')");

        if ($insert) {
            // Update stok
            $updatestok = mysqli_query($conn, "UPDATE stok SET jumlah=jumlah-$qty WHERE idbarang='$barangkeluar'");
            echo '<script>alert("Pengiriman berhasil");</script>';
            echo '<script>window.location="keluar.php";</script>';
        } else {
            echo '<script>alert("Gagal melakukan pengiriman");</script>';
        }
    } else {
        // Jika stok tidak mencukupi
        echo '<script>alert("Stok tidak mencukupi! Stok tersedia: ' . $stoksekarang . '");</script>';
        echo '<script>window.location="keluar.php";</script>';
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // First, get the data to restore stock
    $getdata = mysqli_query($conn, "SELECT * FROM barang_keluar WHERE id_keluar='$id'");
    $data = mysqli_fetch_array($getdata);
    $idbarang = $data['id_stok'];
    $jumlah = $data['jumlah'];

    // Restore stock
    $restorestock = mysqli_query($conn, "UPDATE stok SET jumlah=jumlah+$jumlah WHERE id_stok='$idbarang'");

    // Delete the record
    $delete = mysqli_query($conn, "DELETE FROM barang_keluar WHERE id_keluar='$id'");

    if ($delete) {
        echo '<script>alert("Data berhasil dihapus");</script>';
        echo '<script>window.location="keluar.php";</script>';
    } else {
        echo '<script>alert("Gagal menghapus data");</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Barang Keluar - CV.Satwa Indotama Perkasa</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">CV.Satwa Indotama Perkasa</a>
        <button class="btn btn-link btn-sm order-2 order-lg 5" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2" />
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>

        <!-- User Info (Letakkan di luar form) -->
        <div class="text-white small mr-3 d-none d-md-block" style="margin-top: 10px;">
            <i class="fas fa-user-circle"></i>
            <strong><?= $_SESSION['username']; ?></strong> (<?= ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?>)
        </div>

        <ul class="navbar-nav ml-auto ml-md-0">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#">Settings</a>
                    <a class="dropdown-item" href="#">Activity Log</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php">Logout</a>
                </div>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link" href="masuk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Barang Masuk
                        </a>
                        <a class="nav-link" href="keluar.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Barang Keluar
                        </a>
                        <a class="nav-link" href="pengiriman.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Jadwal Pengiriman
                        </a>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <h1 class="mt-4">Barang Keluar</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                <i class="fas fa-plus"></i> Tambah Barang Keluar
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal Kirim</th>
                                            <th>Tujuan Kandang</th>
                                            <th>Nama Barang</th>
                                            <th>Jumlah Pakan</th>
                                            <th>BG</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Ambil data dari tabel barang_keluar
                                        $ambildatakeluar = mysqli_query($conn, "SELECT * FROM barang_keluar");
                                        $i = 1; // Inisialisasi nomor

                                        // Loop untuk menampilkan data
                                        while ($data = mysqli_fetch_array($ambildatakeluar)) {
                                            $id_keluar = $data['id_keluar'];
                                            $tgl_kirim = date('d-m-Y', strtotime($data['tgl_kirim'])); // Format tanggal kirim
                                            $tujuan = $data['tujuan'];
                                            $nm_barang = $data['nm_barang'];
                                            $jumlah = $data['jumlah'];
                                            $bg = $data['bg'];
                                        ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= $tgl_kirim; ?></td>
                                                <td><?= $tujuan; ?></td>
                                                <td><?= $nm_barang; ?></td>
                                                <td><?= $jumlah; ?> sak</td>
                                                <td><?= $bg; ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $id_keluar; ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <a href="keluar.php?delete=<?= $id_keluar; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal for each row -->
                                            <div class="modal fade" id="editModal<?= $id_keluar; ?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Barang Keluar</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>

                                                        <!-- Modal body -->
                                                        <form method="post" action="update_keluar.php">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id_keluar" value="<?= $id_keluar; ?>">

                                                                <label for="tujuan" class="form-label">Nama Tujuan</label>
                                                                <input type="text" name="tujuan" value="<?= $tujuan; ?>" class="form-control mb-2" required>

                                                                <label for="nm_barang" class="form-label">Nama Barang</label>
                                                                <input type="text" name="nm_barang" value="<?= $nm_barang; ?>" class="form-control mb-2" readonly>

                                                                <label for="jumlah" class="form-label">Jumlah Pakan</label>
                                                                <input type="number" name="jumlah" value="<?= $jumlah; ?>" class="form-control mb-2" required>

                                                                <label for="bg" class="form-label">Plat Nomor Mobil</label>
                                                                <input type="text" name="bg" value="<?= $bg; ?>" class="form-control mb-3">

                                                                <button type="submit" class="btn btn-primary mb-2" name="updatekeluar">Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        };
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/datatables-demo.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
<script>
    function validateStock() {
        var barang = document.getElementById("barangkeluar").value;
        var qty = document.getElementById("qty").value;

        // Lakukan AJAX request untuk cek stok
        if (barang && qty) {
            $.ajax({
                url: 'cek_stok.php',
                method: 'POST',
                data: {
                    idbarang: barang
                },
                success: function(response) {
                    var stok = parseInt(response);
                    if (parseInt(qty) > stok) {
                        alert('Stok tidak mencukupi! Stok tersedia: ' + stok);
                        return false;
                    }
                    return true;
                }
            });
        }
        return true;
    }
</script>
<script>
    // Fungsi untuk update info stok
    function updateStockInfo() {
        const select = document.getElementById('barangkeluar');
        const selectedOption = select.options[select.selectedIndex];
        const stokTersedia = selectedOption.getAttribute('data-stok');
        document.getElementById('stockInfo').innerHTML = `Stok tersedia: ${stokTersedia} sak`;
    }

    // Validasi sebelum submit
    function validateStock() {
        const jumlah = parseInt(document.getElementById('qty').value);
        const select = document.getElementById('barangkeluar');
        const stokTersedia = parseInt(select.options[select.selectedIndex].getAttribute('data-stok'));

        if (jumlah <= 0) {
            alert('Jumlah harus lebih dari 0');
            return false;
        }

        if (jumlah > stokTersedia) {
            alert(`Stok tidak mencukupi! Stok tersedia: ${stokTersedia} sak`);
            return false;
        }
        return true;
    }

    // Jalankan saat modal terbuka
    $('#myModal').on('shown.bs.modal', function() {
        updateStockInfo();
    });
</script>
<!-- The Modal -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Barang Keluar</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" onsubmit="return validateStock()">
                <div class="modal-body">
                    <label for="tujuan" class="form-label">Nama Tujuan</label>
                    <input type="text" name="tujuan" placeholder="Masukkan Nama Tujuan" class="form-control mb-2" required>

                    <select name="barangnya" id="barangkeluar" class="form-control mb-2" required
                        onchange="updateStockInfo()">
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "select * from stok");
                        while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                            $idstoknya = $fetcharray['id_stok'];
                            $nm_barangnya = $fetcharray['nm_barang'];
                            $jenisnya = $fetcharray['jenis'];
                            $jumlah = $fetcharray['jumlah'];
                        ?>
                            <option value="<?= $idstoknya; ?>" data-stok="<?= $jumlah ?>">
                                <?= $nm_barangnya ?> | <?= $jenisnya ?> (Stok: <?= $jumlah ?>)
                            </option>
                        <?php
                        }
                        ?>
                    </select>

                    <div id="stockInfo" class="text-muted small mb-2"></div>

                    <label for="jumlah" class="form-label">Jumlah Pakan</label>
                    <input type="number" name="jumlah" id="qty" placeholder="Masukkan Jumlah Pakan"
                        class="form-control mb-2" required>

                    <label for="bg" class="form-label">Plat Nomor Mobil</label>
                    <input type="text" name="bg" placeholder="Masukkan Plat Nomor" class="form-control mb-3">

                    <button type="submit" class="btn btn-primary mb-2" name="keluarbarang">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

</html>