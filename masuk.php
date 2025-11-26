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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Barang Masuk - CV.Satwa Indotama Perkasa</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <h1 class="mt-4">Barang Masuk</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            <!-- Button to Open the Modal -->
                            <?php if ($role == 'gudang1' || $role == 'admin1') : ?>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                    Tambah Barang
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>ID Stok</th>
                                            <th>Nama Barang</th>
                                            <th>Jenis Pakan</th>
                                            <th>Tanggal Muat</th>
                                            <th>Tanggal Produksi</th>
                                            <th>Jumlah Pakan</th>
                                            <th>BG</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $ambildatamasuk = mysqli_query($conn, "SELECT * FROM barang_masuk");
                                        $i = 1;
                                        while ($data = mysqli_fetch_array($ambildatamasuk)) {
                                            $id_masuk = $data['id_masuk'];
                                            $id_stok = $data['id_stok'];
                                            $nm_barang = $data['nm_barang'];
                                            $jenis = $data['jenis'];
                                            $tgl_muat = date('d-m-Y', strtotime($data['tgl_muat']));
                                            $tgl_produksi = date('d-m-Y', strtotime($data['tgl_produksi']));
                                            $jumlah = $data['jumlah'];
                                            $bg = $data['bg'];
                                        ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= $id_stok; ?></td>
                                                <td><?= $nm_barang; ?></td>
                                                <td><?= $jenis; ?></td>
                                                <td><?= $tgl_muat; ?></td>
                                                <td><?= $tgl_produksi; ?></td>
                                                <td><?= $jumlah; ?> sak</td>
                                                <td><?= $bg; ?></td>
                                                <td>
                                                    <?php if ($role == 'gudang1') : ?>
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $id_masuk; ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $id_masuk; ?>, <?= $id_stok; ?>, <?= $jumlah; ?>)">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit untuk setiap barang -->
                                            <div class="modal fade" id="editModal<?= $id_masuk; ?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Barang Masuk</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <form method="post">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id_masuk" value="<?= $id_masuk; ?>">
                                                                <input type="hidden" name="id_stok" value="<?= $id_stok; ?>">
                                                                <input type="hidden" name="old_jumlah" value="<?= $jumlah; ?>">

                                                                <label for="nm_barang">Nama Barang</label>
                                                                <input type="text" name="nm_barang" value="<?= $nm_barang; ?>" class="form-control mb-2" readonly>

                                                                <label for="jenis">Jenis Pakan</label>
                                                                <input type="text" name="jenis" value="<?= $jenis; ?>" class="form-control mb-2" readonly>

                                                                <label for="tgl_muat">Tanggal Muat</label>
                                                                <input type="date" name="tgl_muat" value="<?= date('Y-m-d', strtotime($data['tgl_muat'])); ?>" class="form-control mb-2" required>

                                                                <label for="tgl_produksi">Tanggal Produksi</label>
                                                                <input type="date" name="tgl_produksi" value="<?= date('Y-m-d', strtotime($data['tgl_produksi'])); ?>" class="form-control mb-2" required>

                                                                <label for="jumlah">Jumlah Pakan</label>
                                                                <input type="number" name="jumlah" value="<?= $jumlah; ?>" class="form-control mb-2" required>

                                                                <label for="bg">Plat Nomor Mobil</label>
                                                                <input type="text" name="bg" value="<?= $bg; ?>" class="form-control mb-3">

                                                                <button type="submit" class="btn btn-primary" name="updatebarang">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }; ?>
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
    <script>
        function confirmDelete(id_masuk, id_stok, jumlah) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'masuk.php?delete=' + id_masuk + '&id_stok=' + id_stok + '&jumlah=' + jumlah;
                }
            });
        }
    </script>
</body>
<!-- The Modal -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Barang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post">
                <div class="modal-body">
                    <label for="barangnya">Nama Barang</label>
                    <select name="barangnya" class="form-control" id="barangnya">
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM stok");
                        while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                            $idstoknya = $fetcharray['id_stok'];
                            $nm_barangnya = $fetcharray['nm_barang'];
                            $jenisnya = $fetcharray['jenis'];
                        ?>
                            <option value="<?= $idstoknya; ?>"><?= $nm_barangnya ?> | <?= $jenisnya ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <label for="tgl_muat" class="form-label">Tanggal Muat</label>
                    <input type="date" name="tgl_muat" class="form-control mb-2" required>
                    <label for="tgl_produksi" class="form-label">Tanggal Produksi</label>
                    <input type="date" name="tgl_produksi" class="form-control mb-2" required>
                    <label for="jumlah" class="form-label">Jumlah Pakan</label>
                    <input type="text" name="jumlah" placeholder="Masukkan Jumlah Pakan" class="form-control mb-2" required>
                    <label for="bg" class="form-label">Plat Nomor Mobil</label>
                    <input type="text" name="bg" placeholder="Masukkan Plat Nomor" class="form-control mb-3">
                    <button type="submit" class="btn btn-primary mb-2" name="tambahbarang">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

</html>