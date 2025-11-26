<?php
require 'function.php';
require 'cek.php';

// Validasi session dan role
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? null;

// Batasi aksi "Terima" dan "Tolak" hanya untuk admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array(true, [
    isset($_POST['terima_pengiriman']),
    isset($_POST['tolak_pengiriman'])
])) {
    if ($role !== 'admin1') {
        $_SESSION['error'] = 'Hanya admin yang dapat memproses pengiriman.';
        header("Location: pengiriman.php");
        exit;
    }
}


/// Data supir dan kendaraan
// Data supir dan kendaraan (tambahkan username untuk masing-masing supir)
$supirList = [
    ['plat' => 'BG8732NQ', 'nama' => 'Supir A', 'username' => 'supir1'],
    ['plat' => 'BG8733NQ', 'nama' => 'Supir B', 'username' => 'supir2'],
    ['plat' => 'BG8941NX', 'nama' => 'Supir C', 'username' => 'supir3'],
    ['plat' => 'BG8978IH', 'nama' => 'Supir D', 'username' => 'supir4'],
    ['plat' => 'BG8027AH', 'nama' => 'Supir E', 'username' => 'supir5']
];

// Mapping supir ke plat_supir
$usernameToPlat = [];
foreach ($supirList as $supir) {
    $usernameToPlat[$supir['nama']] = $supir['plat'];
}

// Query data pengiriman berdasarkan role
// GANTI query awal dengan ini:
if ($role == 'supir') {
    $platSupir = $usernameToPlat[$username] ?? '';
    $sql = "SELECT p.*, s.nm_barang, s.jenis 
            FROM pengiriman p 
            JOIN stok s ON p.id_stok = s.id_stok 
            WHERE p.plat_supir = '$platSupir' OR p.status = 'menunggu_konfirmasi'
            ORDER BY p.id_pengiriman DESC";
} else {
    $sql = "SELECT p.*, s.nm_barang, s.jenis 
            FROM pengiriman p 
            JOIN stok s ON p.id_stok = s.id_stok 
            ORDER BY p.id_pengiriman DESC";
}

// Simpan query ke session untuk consistency
$_SESSION['last_pengiriman_query'] = $sql;
$query = mysqli_query($conn, "SELECT p.*, s.nm_barang, s.jenis 
                            FROM pengiriman p 
                            JOIN stok s ON p.id_stok = s.id_stok 
                            ORDER BY p.id_pengiriman DESC");

// Handle form submissions
if (isset($_POST['terima_pengiriman']) && isset($_POST['id_pengiriman']) && isset($_POST['plat_supir'])) {
    $id_pengiriman = $_POST['id_pengiriman'];
    $plat_supir = $_POST['plat_supir'];

    // 1. Ambil data pengiriman LENGKAP dengan JOIN ke tabel stok
    $query = $conn->query("SELECT p.*, s.nm_barang as nama_barang, s.jenis, s.id_stok 
                          FROM pengiriman p
                          JOIN stok s ON p.id_stok = s.id_stok
                          WHERE p.id_pengiriman = '$id_pengiriman'");
    $dataPengiriman = $query->fetch_assoc();

    if (!$dataPengiriman) {
        $_SESSION['error'] = "Data pengiriman tidak ditemukan";
        header("Location: pengiriman.php");
        exit;
    }

    $nama_barang = $dataPengiriman['nama_barang'];
    $id_stok = $dataPengiriman['id_stok'];
    $jumlah = $dataPengiriman['jumlah'];
    $tanggal = date("Y-m-d");

    // 2. Debugging - Catat data yang akan diproses
    error_log("Mengurangi stok: ID Stok=$id_stok, Barang=$nama_barang, Jumlah=$jumlah");

    // 3. KURANGI STOK (versi paling pasti)
    $updateStok = $conn->query("UPDATE stok SET jumlah = jumlah - $jumlah WHERE id_stok = '$id_stok'");

    if (!$updateStok) {
        $_SESSION['error'] = "Gagal mengurangi stok: " . $conn->error;
        header("Location: pengiriman.php");
        exit;
    }

    // 4. Tandai pengiriman sebagai diproses
    $nama_supir = '';
    foreach ($supirList as $supir) {
        if ($supir['plat'] == $plat_supir) {
            $nama_supir = $supir['nama'];
            break;
        }
    }

    $conn->query("UPDATE pengiriman SET 
                 status='pembuatan_surat_jalan', 
                 plat_supir='$plat_supir', 
                 nama_supir='$nama_supir' 
                 WHERE id_pengiriman='$id_pengiriman'");

    // 5. Catat ke barang keluar
    $conn->query("INSERT INTO barang_keluar 
             (id_stok, nm_barang, jumlah, tujuan, bg) 
             VALUES (
                 '$id_stok',
                 '$nama_barang',
                 $jumlah,
                 '{$dataPengiriman['tujuan']}',
                 '$plat_supir'
             )");


    $_SESSION['success'] = "Pengiriman diterima. Stok $nama_barang berkurang sebanyak $jumlah sak.";
    header("Location: pengiriman.php");
    exit;
}

// Tolak pengiriman (admin)
if (isset($_POST['tolak_pengiriman']) && isset($_POST['id_pengiriman'])) {
    $id_pengiriman = mysqli_real_escape_string($conn, $_POST['id_pengiriman']);

    $stmt = $conn->prepare("UPDATE pengiriman SET status='ditolak' WHERE id_pengiriman=?");
    $stmt->bind_param("s", $id_pengiriman);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Pengiriman ditolak';
    } else {
        $_SESSION['error'] = 'Gagal menolak pengiriman';
    }

    header("Location: pengiriman.php");
    exit;
}

// Tambah pengiriman (logistik)
if (isset($_POST['tambahpengiriman'])) {
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $idstok = mysqli_real_escape_string($conn, $_POST['barangnya']);
    $jumlah = (int)$_POST['jumlah'];

    // Validasi input
    if ($jumlah <= 0) {
        $_SESSION['error'] = 'Jumlah tidak valid';
        header("Location: pengiriman.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO pengiriman 
                      (id_stok, tujuan, nm_barang, jenis, jumlah, status) 
                      VALUES (?, ?, ?, ?, ?, 'menunggu_konfirmasi,ditolak,diterima')");
    $stmt->bind_param("ssssi", $idstok, $tujuan, $idstok, $idstok, $jumlah);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Pengiriman berhasil ditambahkan';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan pengiriman';
    }

    session_write_close(); // <-- Penting!
    header("Location: pengiriman.php?force_reload=1"); // <-- Tambahkan parameter
    exit();
}




// Upload surat jalan (supir)
if (isset($_POST['upload_surat']) && isset($_FILES['foto_surat'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_pengiriman']);

    // Validasi file
    $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($_FILES['foto_surat']['type'], $allowed)) {
        $_SESSION['error'] = 'Format file tidak didukung';
        header("Location: pengiriman.php");
        exit;
    }

    // Buat folder jika belum ada
    $folder = "surat_jalan/";
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // Generate nama file unik
    $ext = pathinfo($_FILES['foto_surat']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $path = $folder . $filename;

    if (move_uploaded_file($_FILES['foto_surat']['tmp_name'], $path)) {
        $stmt = $conn->prepare("UPDATE pengiriman SET surat_jalan=?, status='pengiriman_selesai' WHERE id_pengiriman=?");
        $stmt->bind_param("ss", $filename, $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Surat jalan berhasil diupload';
        } else {
            unlink($path); // Hapus file jika gagal update database
            $_SESSION['error'] = 'Gagal menyimpan data surat jalan';
        }
    } else {
        $_SESSION['error'] = 'Gagal upload file';
    }

    header("Location: pengiriman.php");
    exit;
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
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            console.log("Bootstrap modal should be working.");
        });
    </script>
    <?php if (isset($_GET['force_reload'])) : ?>
        <script>
            // Force reload tanpa cache
            window.location.href = window.location.pathname + "?nocache=" + new Date().getTime();
        </script>
    <?php endif; ?>

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

        <!-- Navbar User Info -->
        <?php
        $usernameDisplay = $_SESSION['username'] ?? 'Tidak diketahui';
        $roleDisplay = ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? 'Tidak diketahui'));
        ?>
        <div class="text-white small mr-3 d-none d-md-block" style="margin-top: 10px;">
            <i class="fas fa-user-circle"></i>
            <strong><?= $usernameDisplay; ?></strong> (<?= $roleDisplay; ?>)
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
                            Masuk
                        </a>
                        <a class="nav-link" href="keluar.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Keluar
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
                    <h1 class="mt-4">Jadwal Pengiriman</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            <!-- Button to Open the Modal -->
                            <?php if ($role == 'logistik1') : ?>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambahPengiriman">
                                    Tambah Pengiriman
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>ID Pengiriman</th>
                                            <th>Tujuan</th>
                                            <th>Jenis Pakan</th>
                                            <th>Jumlah (sak)</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                            <th>Bukti Surat Jalan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        // Gunakan query FRESH untuk memastikan data tidak tertimpa
                                        $query_fix = mysqli_query($conn, "SELECT p.*, s.nm_barang, s.jenis FROM pengiriman p JOIN stok s ON p.id_stok = s.id_stok ORDER BY p.id_pengiriman DESC");
                                        while ($row = mysqli_fetch_assoc($query)) {
                                            $id = $row['id_pengiriman'];
                                            $tujuan = $row['tujuan'];
                                            $status = $row['status'];
                                            $badge_color = [
                                                'menunggu_konfirmasi' => 'warning',
                                                'diterima' => 'primary',
                                                'ditolak' => 'danger',
                                                'pembuatan_surat_jalan' => 'info',
                                                'dalam_proses_muat' => 'secondary',
                                                'selesai' => 'success'
                                            ][$status];
                                            $nm_barang = $row['nm_barang'];
                                            $jenis = $row['jenis'];
                                            $jumlah = $row['jumlah'];
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= $id; ?></td>
                                                <td><?= $tujuan; ?></td>
                                                <td><?= $nm_barang . ' | ' . $jenis; ?></td>
                                                <td><?= $jumlah; ?> sak</td>
                                                <td><span class="badge badge-info"><?= $status; ?></span></td>
                                                <td>
                                                    <?php if ($row['status'] == 'pembuatan_surat_jalan' && in_array($role, ['admin1'])) : ?>
                                                        <a href="cetak_surat_jalan.php?id=<?= $row['id_pengiriman'] ?>" class="btn btn-success btn-sm">Cetak Surat Jalan</a>

                                                    <?php elseif ($row['status'] == 'ditolak') : ?>
                                                        <span class="text-danger">Ditolak</span>
                                                    <?php else : ?>
                                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal<?= $row['id_pengiriman'] ?>">Detail</button>

                                                        <!-- Modal -->
                                                        <div class="modal fade" id="modal<?= $row['id_pengiriman'] ?>" tabindex="-1">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content shadow-sm">
                                                                    <div class="modal-header bg-primary text-white">
                                                                        <h5 class="modal-title">Detail Jadwal Pengiriman</h5>
                                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                    </div>

                                                                    <div class="modal-body">
                                                                        <?php
                                                                        $id_pengiriman = $row['id_pengiriman'];
                                                                        $query = mysqli_query($conn, "SELECT p.*, s.nm_barang, s.jenis FROM pengiriman p JOIN stok s ON p.id_stok = s.id_stok WHERE p.id_pengiriman = '$id_pengiriman'");
                                                                        $data = mysqli_fetch_assoc($query);
                                                                        ?>
                                                                        <?php if ($data): ?>
                                                                            <ul class="list-group list-group-flush mb-3">
                                                                                <li class="list-group-item"><strong>Tujuan:</strong> <?= $data['tujuan'] ?></li>
                                                                                <li class="list-group-item"><strong>Nama Barang:</strong> <?= $data['nm_barang'] ?></li>
                                                                                <li class="list-group-item"><strong>Jenis Barang:</strong> <?= $data['jenis'] ?></li>
                                                                                <li class="list-group-item"><strong>Jumlah:</strong> <?= $data['jumlah'] ?> sak</li>
                                                                            </ul>
                                                                        <?php else: ?>
                                                                            <div class="alert alert-warning">Data pengiriman tidak ditemukan.</div>
                                                                        <?php endif; ?>

                                                                        <!-- Form Aksi -->
                                                                        <form method="post" action="">
                                                                            <input type="hidden" name="id_pengiriman" value="<?= $row['id_pengiriman'] ?>">

                                                                            <div class="mb-3">
                                                                                <label for="plat_supir" class="form-label"><strong>Pilih Nomor Kendaraan</strong></label>
                                                                                <select name="plat_supir" class="form-select" required>
                                                                                    <option value="">-- Pilih --</option>
                                                                                    <option value="BG8732NQ">BG8732NQ</option>
                                                                                    <option value="BG8733NQ">BG8733NQ</option>
                                                                                    <option value="BG8941NX">BG8941NX</option>
                                                                                    <option value="BG8978IH">BG8978IH</option>
                                                                                    <option value="BG8027AH">BG8027AH</option>
                                                                                </select>
                                                                            </div>

                                                                            <div class="d-flex justify-content-end gap-2">
                                                                                <button type="submit" name="tolak_pengiriman" class="btn btn-outline-danger">Tolak</button>
                                                                                <button type="submit" name="terima_pengiriman" class="btn btn-success">Terima</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($role == 'supir' && strtolower($row['status']) == 'pembuatan_surat_jalan') : ?>
                                                        <form method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="id_pengiriman" value="<?= $id; ?>">
                                                            <input type="file" name="foto_surat" class="form-control-file mb-1" required>
                                                            <button type="submit" name="upload_surat" class="btn btn-sm btn-primary mt-1">Upload</button>
                                                        </form>
                                                    <?php elseif ($row['surat_jalan']) : ?>
                                                        <a href="surat_jalan/<?= $row['surat_jalan']; ?>" target="_blank">Lihat</a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
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
    <!-- Bootstrap Bundle JS (wajib: bundle includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/demo/datatables-demo.js"></script>

    <script>
        function openDetailModal(id) {
            document.getElementById('modal_id_pengiriman').value = id;
            var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
            myModal.show();
        }
    </script>

</body>
<!-- Modal Tambah Jadwal Pengiriman -->
<div class="modal fade" id="modalTambahPengiriman">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Jadwal Pengiriman</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal Body -->
            <form method="POST">
                <div class="modal-body">
                    <label for="tujuan" class="form-label">Tujuan Pengiriman</label>
                    <input type="text" name="tujuan" class="form-control mb-2" placeholder="Masukkan Tujuan" required>

                    <label for="stok" class="form-label">Pilih Barang dan Jenis</label>
                    <select name="barangnya" class="form-control mb-2" required>
                        <?php
                        $stok = mysqli_query($conn, "SELECT * FROM stok");
                        while ($data = mysqli_fetch_array($stok)) {
                            $id = $data['id_stok'];
                            $nama = $data['nm_barang'];
                            $jenis = $data['jenis'];
                            echo "<option value='$id'>$nama | $jenis</option>";
                        }
                        ?>
                    </select>
                    <label for="jumlah" class="form-label">Jumlah (dalam sak)</label>
                    <input type="number" name="jumlah" class="form-control mb-2" placeholder="Masukkan jumlah" required>
                    <button type="submit" name="tambahpengiriman" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

</html>