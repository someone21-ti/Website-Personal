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

// Data supir dan kendaraan
$supirList = [
    ['plat' => 'BG8732NQ', 'nama' => 'Supir A', 'username' => 'supir1'],
    ['plat' => 'BG8733NQ', 'nama' => 'Supir B', 'username' => 'supir2'],
    ['plat' => 'BG8941NX', 'nama' => 'Supir C', 'username' => 'supir3'],
    ['plat' => 'BG8978IH', 'nama' => 'Supir D', 'username' => 'supir4'],
    ['plat' => 'BG8027AH', 'nama' => 'Supir E', 'username' => 'supir5']
];

// Mapping data supir
$usernameToPlat = [];
$platToUsername = [];
foreach ($supirList as $supir) {
    $usernameToPlat[$supir['username']] = $supir['plat'];
    $platToUsername[$supir['plat']] = $supir['username'];
}

// FUNGSI UTAMA: Ambil data pengiriman berdasarkan role
function getDeliveryData($conn, $role, $username = null, $usernameToPlat = [])
{
    $baseQuery = "SELECT p.*, s.nm_barang, s.jenis 
                 FROM pengiriman p 
                 JOIN stok s ON p.id_stok = s.id_stok";

    if ($role == 'supir') {
        $platSupir = $usernameToPlat[$username] ?? '';
        $query = "$baseQuery WHERE p.username_supir = '$username' OR p.status = 'menunggu_konfirmasi'";
    } else {
        // Untuk admin dan logistik tampilkan SEMUA data
        $query = $baseQuery;
    }

    $query .= " ORDER BY p.id_pengiriman DESC";
    return mysqli_query($conn, $query);
}

// PROSES FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Batasi aksi hanya untuk admin
    if (isset($_POST['terima_pengiriman']) || isset($_POST['admin_tolak_pengiriman'])) {
        if ($role !== 'gudang1') {
            $_SESSION['error'] = 'Hanya admin yang dapat memproses pengiriman.';
            header("Location: pengiriman.php");
            exit;
        }
    }

    // Terima pengiriman (admin)
    if (isset($_POST['terima_pengiriman']) && isset($_POST['id_pengiriman']) && isset($_POST['plat_supir'])) {
        $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);
        $plat_supir = $conn->real_escape_string($_POST['plat_supir']);
        $username_supir = $platToUsername[$plat_supir] ?? '';

        // 1. Ambil data pengiriman
        $query = $conn->query("SELECT p.*, s.nm_barang as nama_barang, s.id_stok, s.jumlah as stok_tersedia
                              FROM pengiriman p
                              JOIN stok s ON p.id_stok = s.id_stok
                              WHERE p.id_pengiriman = '$id_pengiriman'");
        $data = $query->fetch_assoc();

        if (!$data) {
            $_SESSION['error'] = "Data pengiriman tidak ditemukan";
            header("Location: pengiriman.php");
            exit;
        }

        // Validasi stok
        if ($data['jumlah'] > $data['stok_tersedia']) {
            $_SESSION['error'] = "Stok tidak mencukupi! Stok tersedia: {$data['stok_tersedia']} sak";
            header("Location: pengiriman.php");
            exit;
        }

        // 2. Kurangi stok
        $updateStok = $conn->query("UPDATE stok SET jumlah = jumlah - {$data['jumlah']} WHERE id_stok = '{$data['id_stok']}'");
        if (!$updateStok) {
            $_SESSION['error'] = "Gagal mengurangi stok: " . $conn->error;
            header("Location: pengiriman.php");
            exit;
        }

        // 3. Update status pengiriman
        $nama_supir = $supirList[array_search($plat_supir, array_column($supirList, 'plat'))]['nama'] ?? '';
        $conn->query("UPDATE pengiriman SET 
                     status = 'pembuatan_surat_jalan', 
                     plat_supir = '$plat_supir', 
                     username_supir = '$username_supir',
                     nama_supir = '$nama_supir' 
                     WHERE id_pengiriman = '$id_pengiriman'");

        // 4. Catat ke barang keluar
        $conn->query("INSERT INTO barang_keluar 
                     (id_stok, nm_barang, jumlah, tujuan, bg) 
                     VALUES (
                         '{$data['id_stok']}',
                         '{$data['nama_barang']}',
                         {$data['jumlah']},
                         '{$data['tujuan']}',
                         '$plat_supir'
                     )");

        $_SESSION['success'] = "Pengiriman diterima. Supir: $nama_supir";
        header("Location: pengiriman.php");
        exit;
    }

    // Tolak pengiriman (admin)
    if (isset($_POST['tolak_pengiriman']) && isset($_POST['id_pengiriman'])) {
        if ($role !== 'gudang1') {
            $_SESSION['error'] = 'Hanya admin yang dapat menolak pengiriman.';
            header("Location: pengiriman.php");
            exit;
        }

        $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);
        $stmt = $conn->prepare("UPDATE pengiriman SET status = 'ditolak' WHERE id_pengiriman = ?");
        $stmt->bind_param("s", $id_pengiriman);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Pengiriman ditolak oleh admin';
        } else {
            $_SESSION['error'] = 'Gagal menolak pengiriman';
        }
        header("Location: pengiriman.php");
        exit;
    }

    // Proses muat (supir)
    if (isset($_POST['proses_muat']) && isset($_POST['id_pengiriman'])) {
        $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);

        // Update status ke 'dalam_proses_muat'
        $stmt = $conn->prepare("UPDATE pengiriman SET status = 'dalam_proses_muat' WHERE id_pengiriman = ?");
        $stmt->bind_param("s", $id_pengiriman);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Status pengiriman berubah menjadi "Dalam Proses Muat & Kirim"';
        } else {
            $_SESSION['error'] = 'Gagal mengupdate status pengiriman';
        }

        header("Location: pengiriman.php");
        exit;
    }

    // Proses ketika supir menolak pengiriman
    // Di bagian POST handler (sekitar line 150-200)
    if (isset($_POST['supir_tolak_pengiriman']) && isset($_POST['id_pengiriman'])) {
        if ($role !== 'supir') {
            $_SESSION['error'] = 'Hanya supir yang terkait yang dapat menolak.';
            header("Location: pengiriman.php");
            exit;
        }

        $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);
        $catatan = $conn->real_escape_string($_POST['catatan_supir'] ?? 'Tanpa alasan');

        // 1. Ambil data pengiriman untuk mengetahui jumlah dan barang yang dikembalikan
        $query = $conn->query("SELECT p.*, s.id_stok 
                       FROM pengiriman p
                       JOIN stok s ON p.id_stok = s.id_stok 
                       WHERE p.id_pengiriman = '$id_pengiriman'");
        $data = $query->fetch_assoc();

        if (!$data) {
            $_SESSION['error'] = "Data pengiriman tidak ditemukan";
            header("Location: pengiriman.php");
            exit;
        }

        // 2. Kembalikan stok
        $kembalikanStok = $conn->query("UPDATE stok SET jumlah = jumlah + {$data['jumlah']} 
                                   WHERE id_stok = '{$data['id_stok']}'");

        if (!$kembalikanStok) {
            $_SESSION['error'] = "Gagal mengembalikan stok: " . $conn->error;
            header("Location: pengiriman.php");
            exit;
        }

        // 3. Update status pengiriman
        $update = $conn->query("UPDATE pengiriman SET status = 'ditolak_supir',catatan_supir = '$catatan',waktu_ditolak = NOW() WHERE id_pengiriman = '$id_pengiriman'");

        if ($update) {
            $_SESSION['success'] = 'Pengiriman ditolak dan stok telah dikembalikan';
        } else {
            $_SESSION['error'] = 'Gagal menolak pengiriman: ' . $conn->error;
        }
        header("Location: pengiriman.php");
        exit;
    }


    // Tambah pengiriman (logistik)
    // Di bagian POST handler (sekitar line 100)
    if (isset($_POST['tambahpengiriman'])) {
        $tujuan = $conn->real_escape_string($_POST['tujuan']);
        $idstok = $conn->real_escape_string($_POST['barangnya']);
        $jumlah = (int)$_POST['jumlah'];

        // Validasi stok
        $cekStok = $conn->query("SELECT jumlah FROM stok WHERE id_stok = '$idstok'");
        $stokTersedia = $cekStok->fetch_assoc()['jumlah'];

        if ($jumlah <= 0) {
            $_SESSION['error'] = 'Jumlah tidak valid';
        } elseif ($jumlah > $stokTersedia) {
            $_SESSION['error'] = "Stok tidak mencukupi! Stok tersedia: $stokTersedia sak";
        } else {
            $stmt = $conn->prepare("INSERT INTO pengiriman 
                              (id_stok, tujuan, nm_barang, jenis, jumlah, status) 
                              VALUES (?, ?, ?, ?, ?, 'menunggu_konfirmasi')");
            $stmt->bind_param("ssssi", $idstok, $tujuan, $idstok, $idstok, $jumlah);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Pengiriman berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan pengiriman';
            }
        }
        header("Location: pengiriman.php?force_reload=1");
        exit;
    }

    // Upload surat jalan (supir)
    if (isset($_POST['upload_surat']) && isset($_FILES['foto_surat'])) {
        $id = $conn->real_escape_string($_POST['id_pengiriman']);
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];

        if (!in_array($_FILES['foto_surat']['type'], $allowed)) {
            $_SESSION['error'] = 'Format file tidak didukung (hanya JPG, PNG, PDF)';
            header("Location: pengiriman.php");
            exit;
        }

        $folder = "surat_jalan/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ext = pathinfo($_FILES['foto_surat']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $path = $folder . $filename;

        if (move_uploaded_file($_FILES['foto_surat']['tmp_name'], $path)) {
            $stmt = $conn->prepare("UPDATE pengiriman SET surat_jalan = ?, status = 'pengiriman_selesai' WHERE id_pengiriman = ?");
            $stmt->bind_param("ss", $filename, $id);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Surat jalan berhasil diupload';
            } else {
                unlink($path);
                $_SESSION['error'] = 'Gagal menyimpan data surat jalan';
            }
        } else {
            $_SESSION['error'] = 'Gagal upload file';
        }

        header("Location: pengiriman.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // [Previous form processing code remains the same...]

    // New: Process document upload from driver
    if (isset($_POST['upload_dokumen']) && isset($_FILES['foto_dokumen'])) {
        $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);

        // Validate file
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($_FILES['foto_dokumen']['type'], $allowed)) {
            $_SESSION['error'] = 'Format file tidak didukung (hanya JPG, PNG, PDF)';
            header("Location: pengiriman.php");
            exit;
        }

        // Create folder if not exists
        $folder = "dokumen_pengiriman/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        // Generate unique filename
        $ext = pathinfo($_FILES['foto_dokumen']['name'], PATHINFO_EXTENSION);
        $filename = "signed_" . time() . "." . $ext;
        $path = $folder . $filename;

        if (move_uploaded_file($_FILES['foto_dokumen']['tmp_name'], $path)) {
            // Update status and store document path
            $stmt = $conn->prepare("UPDATE pengiriman SET 
                                  dokumen_ttd = ?, 
                                  status = 'berhasil_dikirim',
                                  waktu_selesai = NOW() 
                                  WHERE id_pengiriman = ?");
            $stmt->bind_param("ss", $filename, $id_pengiriman);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Dokumen berhasil diupload. Status pengiriman diupdate.';
            } else {
                unlink($path); // Delete file if database update fails
                $_SESSION['error'] = 'Gagal menyimpan data dokumen';
            }
        } else {
            $_SESSION['error'] = 'Gagal upload file';
        }

        header("Location: pengiriman.php");
        exit;
    }
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

// AMBIL DATA UNTUK DITAMPILKAN
$deliveryQuery = getDeliveryData($conn, $role, $username, $usernameToPlat);
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
                                            <th>ID</th>
                                            <th>Tujuan</th>
                                            <th>Barang</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                            <th>Surat Jalan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        $statusColors = [
                                            'menunggu_konfirmasi' => 'warning',
                                            'diterima' => 'primary',
                                            'ditolak' => 'danger',
                                            'pembuatan_surat_jalan' => 'info',
                                            'dalam_proses_muat' => 'secondary',
                                            'selesai' => 'success'
                                        ];

                                        while ($row = mysqli_fetch_assoc($deliveryQuery)) :
                                            $status = $row['status'];
                                            $badgeColor = $statusColors[$status] ?? 'secondary';
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($row['id_pengiriman']); ?></td>
                                                <td><?= htmlspecialchars($row['tujuan']); ?></td>
                                                <td><?= htmlspecialchars($row['nm_barang'] . ' | ' . $row['jenis']); ?></td>
                                                <td><?= (int)$row['jumlah']; ?> sak</td>
                                                <td>
                                                    <span class="badge bg-<?= $badgeColor ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($status == 'pembuatan_surat_jalan' && $role == 'admin1') : ?>
                                                        <a href="cetak_surat_jalan.php?id=<?= $row['id_pengiriman'] ?>" class="btn btn-success btn-sm">
                                                            <i class="fas fa-print"></i> Cetak
                                                        </a>
                                                    <?php elseif ($status == 'ditolak') : ?>
                                                        <span class="text-danger">Ditolak</span>
                                                    <?php else : ?>
                                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal<?= $row['id_pengiriman'] ?>">
                                                            <i class="fas fa-eye"></i> Detail
                                                        </button>

                                                        <!-- Modal Detail -->
                                                        <div class="modal fade" id="modal<?= $row['id_pengiriman'] ?>" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-primary text-white">
                                                                        <h5 class="modal-title">Detail Pengiriman</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <strong>ID:</strong> <?= htmlspecialchars($row['id_pengiriman']); ?>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <strong>Tujuan:</strong> <?= htmlspecialchars($row['tujuan']); ?>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <strong>Barang:</strong> <?= htmlspecialchars($row['nm_barang'] . ' | ' . $row['jenis']); ?>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <strong>Jumlah:</strong> <?= (int)$row['jumlah']; ?> sak
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <strong>Plat Kendaraan:</strong> <?= htmlspecialchars($row['plat_supir']); ?>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <strong>Status:</strong>
                                                                            <span class="badge bg-<?= $badgeColor ?>">
                                                                                <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                                                            </span>
                                                                        </div>

                                                                        <?php if ($role == 'gudang1' && $status == 'menunggu_konfirmasi') : ?>
                                                                            <form method="post">
                                                                                <input type="hidden" name="id_pengiriman" value="<?= $row['id_pengiriman'] ?>">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label"><strong>Pilih Kendaraan</strong></label>
                                                                                    <select name="plat_supir" class="form-select" required>
                                                                                        <option value="">-- Pilih Supir --</option>
                                                                                        <?php foreach ($supirList as $supir) : ?>
                                                                                            <option value="<?= $supir['plat'] ?>">
                                                                                                <?= $supir['plat'] ?> - <?= $supir['nama'] ?>
                                                                                            </option>
                                                                                        <?php endforeach; ?>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="d-flex justify-content-end gap-2">
                                                                                    <button type="submit" name="tolak_pengiriman" class="btn btn-outline-danger">
                                                                                        <i class="fas fa-times"></i> Tolak
                                                                                    </button>
                                                                                    <button type="submit" name="terima_pengiriman" class="btn btn-success">
                                                                                        <i class="fas fa-check"></i> Terima
                                                                                    </button>
                                                                                </div>
                                                                            </form>
                                                                        <?php elseif ($role == 'supir' && $status == 'pembuatan_surat_jalan') : ?>
                                                                            <form method="post">
                                                                                <input type="hidden" name="id_pengiriman" value="<?= $row['id_pengiriman'] ?>">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label"><strong>Alasan Penolakan</strong></label>
                                                                                    <textarea name="catatan_supir" class="form-control" rows="2"></textarea>
                                                                                </div>
                                                                                <div class="d-grid gap-2">
                                                                                    <button type="submit" name="proses_muat" class="btn btn-success">
                                                                                        <i class="fas fa-check-circle"></i> Terima
                                                                                    </button>
                                                                                    <button type="submit" name="supir_tolak_pengiriman" class="btn btn-danger">
                                                                                        <i class="fas fa-times"></i> Tolak Pengiriman
                                                                                    </button>
                                                                                </div>
                                                                            </form>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($status == 'dalam_proses_muat' && $role == 'supir') : ?>
                                                        <form method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="id_pengiriman" value="<?= $row['id_pengiriman'] ?>">
                                                            <input type="file" name="foto_dokumen" class="form-control form-control-sm mb-1" required accept=".jpg,.jpeg,.png,.pdf">
                                                            <button type="submit" name="upload_dokumen" class="btn btn-sm btn-success w-100">
                                                                <i class="fas fa-upload"></i> Upload TTD
                                                            </button>
                                                        </form>
                                                    <?php elseif (!empty($row['dokumen_ttd'])) : ?>
                                                        <a href="dokumen_pengiriman/<?= htmlspecialchars($row['dokumen_ttd']); ?>" target="_blank" class="btn btn-sm btn-outline-success w-100">
                                                            <i class="fas fa-file-signature"></i> Lihat
                                                        </a>
                                                    <?php else : ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
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
            <div class="modal-header">
                <h4 class="modal-title">Tambah Jadwal Pengiriman</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" onsubmit="return validateStockBeforeSubmit()">
                <div class="modal-body">
                    <label for="tujuan" class="form-label">Tujuan Pengiriman</label>
                    <input type="text" name="tujuan" class="form-control mb-2" placeholder="Masukkan Tujuan" required>

                    <label for="stok" class="form-label">Pilih Barang</label>
                    <select name="barangnya" id="selectBarang" class="form-control mb-2" required
                        onchange="updateStockInfo()">
                        <?php
                        $stok = mysqli_query($conn, "SELECT * FROM stok");
                        while ($data = mysqli_fetch_array($stok)) {
                            $id = $data['id_stok'];
                            $nama = $data['nm_barang'];
                            $jenis = $data['jenis'];
                            $jumlah = $data['jumlah'];
                            echo "<option value='$id' data-stok='$jumlah'>$nama | $jenis (Stok: $jumlah)</option>";
                        }
                        ?>
                    </select>

                    <div id="stockInfo" class="text-muted small mb-2">Stok tersedia: <?= $data['jumlah'] ?? 0 ?> sak</div>

                    <label for="jumlah" class="form-label">Jumlah (dalam sak)</label>
                    <input type="number" name="jumlah" id="inputJumlah" class="form-control mb-2"
                        placeholder="Masukkan jumlah" required min="1">

                    <button type="submit" name="tambahpengiriman" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk update info stok
    function updateStockInfo() {
        const select = document.getElementById('selectBarang');
        const selectedOption = select.options[select.selectedIndex];
        const stokTersedia = selectedOption.getAttribute('data-stok');
        document.getElementById('stockInfo').innerHTML = `Stok tersedia: ${stokTersedia} sak`;
    }

    // Validasi sebelum submit
    function validateStockBeforeSubmit() {
        const jumlah = parseInt(document.getElementById('inputJumlah').value);
        const select = document.getElementById('selectBarang');
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
    $('#modalTambahPengiriman').on('shown.bs.modal', function() {
        updateStockInfo();
    });
</script>

</html>