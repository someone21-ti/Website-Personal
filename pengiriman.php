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
// Data supir dan kendaraan
$supirList = [
    ['plat' => 'BG8732NQ', 'nama' => 'Ujang', 'username' => 'supir1', 'max_tonase' => 10],
    ['plat' => 'BG8733NQ', 'nama' => 'Marno', 'username' => 'supir2', 'max_tonase' => 10],
    ['plat' => 'BG8941NX', 'nama' => 'Sumardi', 'username' => 'supir3', 'max_tonase' => 13],
    ['plat' => 'BG8978IH', 'nama' => 'Suroto', 'username' => 'supir4', 'max_tonase' => 10],
    ['plat' => 'BG8027AH', 'nama' => 'Parjo', 'username' => 'supir5', 'max_tonase' => 13]
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
    // Query dasar untuk mengambil data pengiriman
    $baseQuery = "SELECT p.* FROM pengiriman p";

    if ($role == 'supir') {
        $platSupir = $usernameToPlat[$username] ?? '';
        $query = "$baseQuery WHERE p.username_supir = '$username' OR p.status = 'menunggu_konfirmasi'";
    } else {
        $query = $baseQuery;
    }

    $query .= " ORDER BY p.id_pengiriman DESC";
    $mainQuery = mysqli_query($conn, $query);

    // Untuk setiap pengiriman, ambil detail barangnya
    $results = [];
    while ($row = mysqli_fetch_assoc($mainQuery)) {
        $id_pengiriman = $row['id_pengiriman'];

        // Query untuk mengambil detail barang
        $detailQuery = "SELECT pd.*, s.nm_barang, s.jenis 
                       FROM pengiriman_detail pd
                       JOIN stok s ON pd.id_stok = s.id_stok
                       WHERE pd.id_pengiriman = '$id_pengiriman'";
        $detailResult = mysqli_query($conn, $detailQuery);

        $barangDetails = [];
        $totalJumlah = 0;
        while ($detail = mysqli_fetch_assoc($detailResult)) {
            $barangDetails[] = $detail;
            $totalJumlah += $detail['jumlah'];
        }

        $row['daftar_barang'] = implode(', ', array_map(
            function ($item) {
                return $item['nm_barang'] . ' (' . $item['jumlah'] . ' sak)';
            },
            $barangDetails
        ));
        $row['total_jumlah'] = $totalJumlah;
        $row['barang_details'] = $barangDetails;

        $results[] = $row;
    }

    return $results;
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

    // Terima pengiriman (admin) - Versi multi-barang
    if (isset($_POST['terima_pengiriman']) && isset($_POST['id_pengiriman']) && isset($_POST['plat_supir'])) {
        $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);
        $plat_supir = $conn->real_escape_string($_POST['plat_supir']);
        $username_supir = $platToUsername[$plat_supir] ?? '';

        // Dapatkan data supir termasuk max_tonase
        $supirData = $supirList[array_search($plat_supir, array_column($supirList, 'plat'))] ?? null;
        $nama_supir = $supirData['nama'] ?? '';
        $max_tonase = $supirData['max_tonase'] ?? 0;

        // Mulai transaksi
        $conn->begin_transaction();

        try {
            // 1. Ambil semua barang dalam pengiriman dan hitung total tonase
            $detailQuery = $conn->query("SELECT pd.*, s.nm_barang, s.jenis, s.jumlah as stok_tersedia
                                   FROM pengiriman_detail pd
                                   JOIN stok s ON pd.id_stok = s.id_stok
                                   WHERE pd.id_pengiriman = '$id_pengiriman'");

            if ($detailQuery->num_rows === 0) {
                throw new Exception("Data pengiriman tidak ditemukan");
            }

            $total_tonase = 0;
            $barang_details = [];
            while ($barang = $detailQuery->fetch_assoc()) {
                // Asumsi 1 sak = 50 kg (0.05 ton)
                $tonase_barang = $barang['jumlah'] * 0.05;
                $total_tonase += $tonase_barang;
                $barang_details[] = $barang;

                // Validasi stok
                if ($barang['jumlah'] > $barang['stok_tersedia']) {
                    throw new Exception("Stok tidak mencukupi untuk {$barang['nm_barang']}! Stok tersedia: {$barang['stok_tersedia']} sak");
                }
            }

            // 2. Validasi tonase maksimal kendaraan
            if ($total_tonase > $max_tonase) {
                throw new Exception("Tonase melebihi kapasitas kendaraan! Total: {$total_tonase} ton, Maksimal: {$max_tonase} ton");
            }

            // 3. Kurangi stok untuk semua barang
            $conn->query("UPDATE stok s
                     JOIN pengiriman_detail pd ON s.id_stok = pd.id_stok
                     SET s.jumlah = s.jumlah - pd.jumlah
                     WHERE pd.id_pengiriman = '$id_pengiriman'");

            // 4. Update status pengiriman
            $conn->query("UPDATE pengiriman SET 
                     status = 'pembuatan_surat_jalan', 
                     plat_supir = '$plat_supir', 
                     username_supir = '$username_supir',
                     nama_supir = '$nama_supir',
                     total_tonase = $total_tonase 
                     WHERE id_pengiriman = '$id_pengiriman'");

            // 5. Catat ke barang keluar untuk semua barang
            $conn->query("INSERT INTO barang_keluar 
                     (id_stok, nm_barang, jumlah, tujuan, bg)
                     SELECT pd.id_stok, s.nm_barang, pd.jumlah, p.tujuan, '$plat_supir'
                     FROM pengiriman_detail pd
                     JOIN stok s ON pd.id_stok = s.id_stok
                     JOIN pengiriman p ON pd.id_pengiriman = p.id_pengiriman
                     WHERE pd.id_pengiriman = '$id_pengiriman'");

            // Commit transaksi
            $conn->commit();
            $_SESSION['success'] = "Pengiriman #$id_pengiriman diterima. Supir: $nama_supir (Tonase: {$total_tonase} ton)";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }

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
        $catatan = $conn->real_escape_string($_POST['catatan'] ?? 'Tanpa alasan');

        // Ubah status menjadi 'perlu_revisi' bukan 'ditolak'
        $stmt = $conn->prepare("UPDATE pengiriman SET 
                      status = 'perlu_revisi', 
                      catatan = ? 
                      WHERE id_pengiriman = ?");
        $stmt->bind_param("ss", $catatan, $id_pengiriman);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Pengiriman ditolak dan memerlukan revisi dari logistik';
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
    // Di bagian POST handler untuk tambah pengiriman
    if (isset($_POST['tambahpengiriman'])) {
        $tujuan = $conn->real_escape_string($_POST['tujuan']);
        $barangList = $_POST['barang'] ?? [];
        $jumlahList = $_POST['jumlah'] ?? [];

        // Validasi input
        if (empty($tujuan) || empty($barangList) || count($barangList) != count($jumlahList)) {
            $_SESSION['error'] = 'Data tidak valid';
            header("Location: pengiriman.php");
            exit;
        }

        // Mulai transaksi
        $conn->begin_transaction();

        try {
            // 1. Buat record pengiriman utama TANPA info barang
            $stmt = $conn->prepare("INSERT INTO pengiriman (tujuan, status) VALUES (?, 'menunggu_konfirmasi')");
            $stmt->bind_param("s", $tujuan);
            $stmt->execute();
            $id_pengiriman = $conn->insert_id;

            // 2. Simpan SEMUA barang ke pengiriman_detail
            for ($i = 0; $i < count($barangList); $i++) {
                $id_stok = $conn->real_escape_string($barangList[$i]);
                $jumlah = (int)$jumlahList[$i];

                // Validasi stok
                $cekStok = $conn->query("SELECT jumlah, nm_barang FROM stok WHERE id_stok = '$id_stok'");
                $stokData = $cekStok->fetch_assoc();

                if (!$stokData) {
                    throw new Exception("Barang tidak ditemukan");
                }

                if ($jumlah <= 0) {
                    throw new Exception("Jumlah tidak valid untuk {$stokData['nm_barang']}");
                }

                if ($jumlah > $stokData['jumlah']) {
                    throw new Exception("Stok tidak mencukupi untuk {$stokData['nm_barang']}! Stok tersedia: {$stokData['jumlah']} sak");
                }

                // Simpan detail pengiriman
                $stmtDetail = $conn->prepare("INSERT INTO pengiriman_detail 
                                         (id_pengiriman, id_stok, jumlah) 
                                         VALUES (?, ?, ?)");
                $stmtDetail->bind_param("isi", $id_pengiriman, $id_stok, $jumlah);
                $stmtDetail->execute();
            }

            // Commit transaksi
            $conn->commit();
            $_SESSION['success'] = 'Pengiriman berhasil ditambahkan dengan ' . count($barangList) . ' jenis barang';
        } catch (Exception $e) {
            // Rollback jika ada error
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
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

        // Validasi file
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($_FILES['foto_dokumen']['type'], $allowed)) {
            $_SESSION['error'] = 'Format file tidak didukung (hanya JPG, PNG, PDF)';
            header("Location: pengiriman.php");
            exit;
        }

        // Buat folder jika belum ada
        $folder = "dokumen_pengiriman/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        // Generate nama file unik
        $ext = pathinfo($_FILES['foto_dokumen']['name'], PATHINFO_EXTENSION);
        $filename = "signed_" . time() . "." . $ext;
        $path = $folder . $filename;

        if (move_uploaded_file($_FILES['foto_dokumen']['tmp_name'], $path)) {
            // Update status ke 'berhasil_dikirim' (menunggu konfirmasi logistik)
            $stmt = $conn->prepare("UPDATE pengiriman SET 
                              dokumen_ttd = ?, 
                              status = 'barang_dibongkar',
                              waktu_selesai = NOW() 
                              WHERE id_pengiriman = ?");
            $stmt->bind_param("ss", $filename, $id_pengiriman);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Dokumen berhasil diupload. Menunggu konfirmasi logistik.';
            } else {
                unlink($path);
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

// Revisi pengiriman (logistik)
if (isset($_POST['revisi_pengiriman'])) {
    if ($role !== 'logistik1') {
        $_SESSION['error'] = 'Hanya logistik yang dapat merevisi pengiriman.';
        header("Location: pengiriman.php");
        exit;
    }

    $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman_revisi']);
    $tujuan = $conn->real_escape_string($_POST['tujuan_revisi']);
    $barangList = $_POST['barang_revisi'] ?? [];
    $jumlahList = $_POST['jumlah_revisi'] ?? [];

    // Validasi input
    if (empty($tujuan) || empty($barangList) || count($barangList) != count($jumlahList)) {
        $_SESSION['error'] = 'Data tidak valid';
        header("Location: pengiriman.php");
        exit;
    }

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // 1. Hapus detail pengiriman lama
        $conn->query("DELETE FROM pengiriman_detail WHERE id_pengiriman = '$id_pengiriman'");

        // 2. Update data pengiriman utama
        $stmt = $conn->prepare("UPDATE pengiriman SET 
                             tujuan = ?,
                             status = 'menunggu_konfirmasi',
                             catatan = NULL
                             WHERE id_pengiriman = ?");
        $stmt->bind_param("ss", $tujuan, $id_pengiriman);
        $stmt->execute();

        // 3. Simpan barang baru ke pengiriman_detail
        for ($i = 0; $i < count($barangList); $i++) {
            $id_stok = $conn->real_escape_string($barangList[$i]);
            $jumlah = (int)$jumlahList[$i];

            // Validasi stok
            $cekStok = $conn->query("SELECT jumlah, nm_barang FROM stok WHERE id_stok = '$id_stok'");
            $stokData = $cekStok->fetch_assoc();

            if (!$stokData) {
                throw new Exception("Barang tidak ditemukan");
            }

            if ($jumlah <= 0) {
                throw new Exception("Jumlah tidak valid untuk {$stokData['nm_barang']}");
            }

            if ($jumlah > $stokData['jumlah']) {
                throw new Exception("Stok tidak mencukupi untuk {$stokData['nm_barang']}! Stok tersedia: {$stokData['jumlah']} sak");
            }

            // Simpan detail pengiriman
            $stmtDetail = $conn->prepare("INSERT INTO pengiriman_detail 
                                     (id_pengiriman, id_stok, jumlah) 
                                     VALUES (?, ?, ?)");
            $stmtDetail->bind_param("isi", $id_pengiriman, $id_stok, $jumlah);
            $stmtDetail->execute();
        }

        // Commit transaksi
        $conn->commit();
        $_SESSION['success'] = 'Pengiriman berhasil direvisi dan menunggu konfirmasi ulang';
    } catch (Exception $e) {
        // Rollback jika ada error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: pengiriman.php");
    exit;
}

// Di bagian POST handler, tambahkan:
if (isset($_POST['konfirmasi_pengiriman'])) {
    if ($role !== 'logistik1') {
        $_SESSION['error'] = 'Hanya logistik yang dapat mengkonfirmasi pengiriman.';
        header("Location: pengiriman.php");
        exit;
    }

    $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);
    $konfirmasi = $conn->real_escape_string($_POST['konfirmasi']);

    if ($konfirmasi === 'ya') {
        // Update status ke 'barang_dibongkar'
        $status = 'barang_dibongkar';
        $message = 'Pengiriman dikonfirmasi sebagai barang telah dibongkar';
    } else {
        // Kembalikan ke status sebelumnya
        $status = 'berhasil_dikirim';
        $message = 'Pengiriman belum dikonfirmasi';
    }

    $stmt = $conn->prepare("UPDATE pengiriman SET status = ? WHERE id_pengiriman = ?");
    $stmt->bind_param("ss", $status, $id_pengiriman);

    if ($stmt->execute()) {
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = 'Gagal mengupdate status pengiriman';
    }

    header("Location: pengiriman.php");
    exit;
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
                                            <th>Tujuan Kandang</th>
                                            <th>Nama Barang</th>
                                            <th>Jumlah Pakan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                            <th>Surat Jalan</th>
                                            <th>Konfirmasi</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        $statusColors = [
                                            'menunggu_konfirmasi' => 'warning',
                                            'ditolak' => 'danger',
                                            'ditolak_supir' => 'danger',
                                            'perlu_revisi' => 'info',
                                            'pembuatan_surat_jalan' => 'info',
                                            'dalam_proses_muat' => 'secondary',
                                            'berhasil_dikirim' => 'primary', // Status ketika logistik konfirmasi
                                            'barang_dibongkar' => 'success', // Status baru setelah supir upload dokumen
                                            'selesai' => 'success'
                                        ];

                                        // Pastikan $deliveryQuery sudah berisi array hasil dari fungsi getDeliveryData()
                                        foreach ($deliveryQuery as $row) :
                                            $status = $row['status'];
                                            $badgeColor = $statusColors[$status] ?? 'secondary';
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($row['id_pengiriman']); ?></td>
                                                <td><?= htmlspecialchars($row['tujuan']); ?></td>
                                                <td>
                                                    <?php if (isset($row['barang_details'])): ?>
                                                        <!-- Tampilan multi-barang -->
                                                        <ul style="list-style-type: none; padding-left: 0; margin-bottom: 0;">
                                                            <?php foreach ($row['barang_details'] as $barang): ?>
                                                                <li>
                                                                    <?= htmlspecialchars($barang['nm_barang'] . ' - ' . $barang['jenis'] . ' - ' . $barang['jumlah'] . ' sak ') ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <!-- Fallback untuk tampilan single barang (kompatibilitas) -->
                                                        <?= htmlspecialchars($row['nm_barang'] . ' | ' . $row['jenis']) ?>
                                                        <span class="badge bg-light text-dark"><?= $row['jumlah'] ?> sak</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= (int)($row['total_jumlah'] ?? $row['jumlah']) ?> sak
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $badgeColor ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($status == 'perlu_revisi' && $role == 'logistik1') : ?>
                                                        <!-- Tombol Revisi -->
                                                        <button class="btn btn-warning btn-sm" onclick="openRevisiModal(
            '<?= $row['id_pengiriman'] ?>', 
            '<?= htmlspecialchars($row['tujuan'], ENT_QUOTES) ?>',
            '<?= htmlspecialchars($row['catatan'] ?? '', ENT_QUOTES) ?>'
        )">
                                                            <i class="fas fa-edit"></i> Revisi
                                                        </button>

                                                    <?php elseif ($status == 'pembuatan_surat_jalan' && $role == 'admin1') : ?>
                                                        <a href="cetak_surat_jalan.php?id=<?= $row['id_pengiriman'] ?>" class="btn btn-success btn-sm" target="_blank">
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
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-primary text-white">
                                                                        <h5 class="modal-title">Detail Pengiriman #<?= htmlspecialchars($row['id_pengiriman']) ?></h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="row mb-3">
                                                                            <div class="col-md-6">
                                                                                <div class="mb-3">
                                                                                    <strong>Tujuan:</strong> <?= htmlspecialchars($row['tujuan']) ?>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <strong>Plat Kendaraan:</strong> <?= htmlspecialchars($row['plat_supir'] . ' - ' . $row['nama_supir'] ?? '-') ?>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="mb-3">
                                                                                    <strong>Status:</strong>
                                                                                    <span class="badge bg-<?= $badgeColor ?>">
                                                                                        <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <strong>Total Jumlah:</strong> <?= (int)($row['total_jumlah'] ?? $row['jumlah']) ?> sak
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="card mb-3">
                                                                            <div class="card-header bg-light">
                                                                                <strong>Daftar Barang</strong>
                                                                            </div>
                                                                            <div class="card-body">
                                                                                <div class="table-responsive">
                                                                                    <table class="table table-bordered table-sm">
                                                                                        <thead>
                                                                                            <tr class="bg-light">
                                                                                                <th>Nama Barang</th>
                                                                                                <th>Jenis</th>
                                                                                                <th>Jumlah (sak)</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?php
                                                                                            // Query untuk mengambil detail barang
                                                                                            $detailQuery = mysqli_query(
                                                                                                $conn,
                                                                                                "SELECT pd.jumlah, s.nm_barang, s.jenis  FROM pengiriman_detail pd JOIN stok s ON pd.id_stok = s.id_stok WHERE pd.id_pengiriman = '" . $row['id_pengiriman'] . "'"
                                                                                            );

                                                                                            // Fallback jika tidak ada data di pengiriman_detail (kompatibilitas)
                                                                                            if (mysqli_num_rows($detailQuery) == 0) {
                                                                                                echo '<tr>
                                                                                                        <td>' . htmlspecialchars($row['nm_barang'] ?? '-') . '</td>
                                                                                                        <td>' . htmlspecialchars($row['jenis'] ?? '-') . '</td>
                                                                                                        <td>' . (int)($row['jumlah'] ?? 0) . '</td>
                                                                                                    </tr>';
                                                                                            } else {
                                                                                                while ($detail = mysqli_fetch_assoc($detailQuery)) {
                                                                                                    echo '<tr>
                                                                                                            <td>' . htmlspecialchars($detail['nm_barang']) . '</td>
                                                                                                            <td>' . htmlspecialchars($detail['jenis']) . '</td>
                                                                                                            <td>' . (int)$detail['jumlah'] . '</td>
                                                                                                        </tr>';
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <?php if ($role == 'gudang1' && $status == 'menunggu_konfirmasi') : ?>
                                                                            <form method="post">
                                                                                <input type="hidden" name="id_pengiriman" value="<?= $row['id_pengiriman'] ?>">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label"><strong>Pilih Kendaraan</strong></label>
                                                                                    <select name="plat_supir" class="form-select" required id="selectSupir" onchange="updateTonaseInfo(this)">
                                                                                        <option value="">-- Pilih Supir --</option>
                                                                                        <?php foreach ($supirList as $supir) : ?>
                                                                                            <option value="<?= $supir['plat'] ?>" data-max-tonase="<?= $supir['max_tonase'] ?>">
                                                                                                <?= $supir['plat'] ?> - <?= $supir['nama'] ?> (Maks: <?= $supir['max_tonase'] ?> ton)
                                                                                            </option>
                                                                                        <?php endforeach; ?>
                                                                                    </select>
                                                                                    <small class="text-muted" id="tonaseInfo">
                                                                                        Total tonase pengiriman ini: <span id="totalTonase"><?= ($row['total_jumlah'] ?? $row['jumlah']) * 0.05 ?></span> ton
                                                                                    </small>
                                                                                    <small class="text-danger d-none" id="tonaseWarning">
                                                                                        ⚠️ Tonase melebihi kapasitas kendaraan!
                                                                                    </small>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <label class="form-label"><strong>Alasan Penolakan (jika ditolak)</strong></label>
                                                                                    <textarea name="catatan" class="form-control" rows="2" placeholder="Masukkan alasan penolakan..."></textarea>
                                                                                </div>
                                                                                <div class="d-flex justify-content-end gap-2">
                                                                                    <button type="submit" name="tolak_pengiriman" class="btn btn-outline-danger">
                                                                                        <i class="fas fa-times"></i> Tolak
                                                                                    </button>
                                                                                    <button type="submit" name="terima_pengiriman" class="btn btn-success" id="submitBtn">
                                                                                        <i class="fas fa-check"></i> Terima
                                                                                    </button>
                                                                                </div>
                                                                            </form>
                                                                            <script>
                                                                                function updateTonaseInfo(selectElement) {
                                                                                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                                                                                    const maxTonase = parseFloat(selectedOption.getAttribute('data-max-tonase') || 0);
                                                                                    const totalTonase = parseFloat(document.getElementById('totalTonase').textContent);

                                                                                    const tonaseWarning = document.getElementById('tonaseWarning');
                                                                                    const submitBtn = document.getElementById('submitBtn');

                                                                                    if (maxTonase > 0 && totalTonase > maxTonase) {
                                                                                        tonaseWarning.classList.remove('d-none');
                                                                                        submitBtn.disabled = true;
                                                                                    } else {
                                                                                        tonaseWarning.classList.add('d-none');
                                                                                        submitBtn.disabled = false;
                                                                                    }
                                                                                }
                                                                            </script>
                                                                        <?php endif; ?>
                                                                        <?php if ($role == 'supir' && $status == 'pembuatan_surat_jalan') : ?>
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
                                                <td>
                                                    <?php if ($status == 'barang_dibongkar' && $role == 'logistik1') : ?>
                                                        <form method="POST" style="display: inline-block;">
                                                            <input type="hidden" name="id_pengiriman" value="<?= $row['id_pengiriman'] ?>">
                                                            <button type="submit" name="konfirmasi_pengiriman" value="ya" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check"></i> Ya
                                                            </button>
                                                        </form>
                                                    <?php elseif ($status == 'berhasil_dikirim') : ?>
                                                        <span class="badge bg-success">Terkonfirmasi</span>
                                                    <?php else : ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($status == 'ditolak' && !empty($row['catatan'])) : ?>
                                                        <span class="text-danger" data-bs-toggle="tooltip" title="<?= htmlspecialchars($row['catatan']) ?>">
                                                            <i class="fas fa-info-circle"></i> Lihat
                                                        </span>
                                                    <?php elseif ($status == 'ditolak_supir' && !empty($row['catatan_supir'])) : ?>
                                                        <span class="text-danger" data-bs-toggle="tooltip" title="<?= htmlspecialchars($row['catatan_supir']) ?>">
                                                            <i class="fas fa-info-circle"></i> Lihat
                                                        </span>
                                                    <?php else : ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Jadwal Pengiriman</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="formTambahPengiriman">
                <div class="modal-body">
                    <label for="tujuan" class="form-label">Tujuan Pengiriman</label>
                    <input type="text" name="tujuan" class="form-control mb-3" placeholder="Masukkan Tujuan" required>

                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Daftar Barang</span>
                            <button type="button" class="btn btn-sm btn-primary" id="tambahBarang">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </button>
                        </div>
                        <div class="card-body" id="daftarBarangContainer">
                            <!-- Barang akan ditambahkan dinamis di sini -->
                            <div class="barang-item mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Pilih Barang</label>
                                        <select name="barang[]" class="form-select select-barang" required onchange="updateStockInfo(this)">
                                            <option value="">-- Pilih Barang --</option>
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
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Jumlah (sak)</label>
                                        <input type="number" name="jumlah[]" class="form-control input-jumlah" required min="1">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm hapus-barang" style="display:none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="stock-info text-muted small mt-1">Stok tersedia: 0 sak</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="tambahpengiriman" class="btn btn-primary">Simpan Pengiriman</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Revisi Pengiriman -->
<div class="modal fade" id="modalRevisiPengiriman">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Revisi Pengiriman</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="formRevisiPengiriman">
                <input type="hidden" name="id_pengiriman_revisi" id="id_pengiriman_revisi">
                <div class="modal-body">
                    <label for="tujuan_revisi" class="form-label">Tujuan Pengiriman</label>
                    <input type="text" name="tujuan_revisi" id="tujuan_revisi" class="form-control mb-3" required>

                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Daftar Barang</span>
                            <button type="button" class="btn btn-sm btn-primary" id="tambahBarangRevisi">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </button>
                        </div>
                        <div class="card-body" id="daftarBarangRevisiContainer">
                            <!-- Barang akan ditambahkan dinamis di sini -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Catatan Penolakan dari Kepala Gudang</label>
                        <textarea class="form-control" id="catatan_penolakan" readonly></textarea>
                    </div>

                    <button type="submit" name="revisi_pengiriman" class="btn btn-primary">Simpan Revisi</button>
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
<script>
    // Fungsi untuk menambah barang baru
    document.getElementById('tambahBarang').addEventListener('click', function() {
        const container = document.getElementById('daftarBarangContainer');
        const newItem = document.querySelector('.barang-item').cloneNode(true);

        // Reset nilai input
        newItem.querySelector('.select-barang').selectedIndex = 0;
        newItem.querySelector('.input-jumlah').value = '';
        newItem.querySelector('.stock-info').textContent = 'Stok tersedia: 0 sak';

        // Tampilkan tombol hapus
        newItem.querySelector('.hapus-barang').style.display = 'block';

        // Tambahkan event listener untuk tombol hapus
        newItem.querySelector('.hapus-barang').addEventListener('click', function() {
            if (document.querySelectorAll('.barang-item').length > 1) {
                this.closest('.barang-item').remove();
            }
        });

        container.appendChild(newItem);
    });

    // Fungsi untuk update info stok
    function updateStockInfo(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const stokTersedia = selectedOption.getAttribute('data-stok');
        const stockInfo = selectElement.closest('.barang-item').querySelector('.stock-info');
        stockInfo.textContent = `Stok tersedia: ${stokTersedia} sak`;
    }

    // Validasi sebelum submit
    document.getElementById('formTambahPengiriman').addEventListener('submit', function(e) {
        const barangItems = document.querySelectorAll('.barang-item');
        let isValid = true;

        barangItems.forEach(item => {
            const select = item.querySelector('.select-barang');
            const jumlahInput = item.querySelector('.input-jumlah');
            const stokTersedia = parseInt(select.options[select.selectedIndex]?.getAttribute('data-stok') || 0);
            const jumlah = parseInt(jumlahInput.value) || 0;

            if (jumlah <= 0) {
                alert('Jumlah harus lebih dari 0 untuk semua barang');
                isValid = false;
                return;
            }

            if (jumlah > stokTersedia) {
                const namaBarang = select.options[select.selectedIndex].text.split('|')[0].trim();
                alert(`Stok tidak mencukupi untuk ${namaBarang}! Stok tersedia: ${stokTersedia} sak`);
                isValid = false;
                return;
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Jalankan saat modal terbuka
    $('#modalTambahPengiriman').on('shown.bs.modal', function() {
        // Reset form kecuali item pertama
        const items = document.querySelectorAll('.barang-item');
        items.forEach((item, index) => {
            if (index > 0) {
                item.remove();
            } else {
                item.querySelector('.select-barang').selectedIndex = 0;
                item.querySelector('.input-jumlah').value = '';
                item.querySelector('.stock-info').textContent = 'Stok tersedia: 0 sak';
                item.querySelector('.hapus-barang').style.display = 'none';
            }
        });
    });
</script>
<script>
    // Aktifkan tooltip
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
<script>
    // Fungsi untuk membuka modal revisi
    function openRevisiModal(id, tujuan, catatan) {
        // Set nilai form
        document.getElementById('id_pengiriman_revisi').value = id;
        document.getElementById('tujuan_revisi').value = tujuan;
        document.getElementById('catatan_penolakan').value = catatan;

        // Kosongkan container barang
        const container = document.getElementById('daftarBarangRevisiContainer');
        container.innerHTML = '';

        // Ambil data barang pengiriman via AJAX
        fetch(`get_delivery_items.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    addBarangItemRevisi(item.id_stok, item.jumlah, item.nm_barang, item.jenis, item.stok_tersedia);
                });

                // Tampilkan modal
                $('#modalRevisiPengiriman').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat data barang');
            });
    }

    // Fungsi untuk menambah barang di form revisi
    function addBarangItemRevisi(id_stok, jumlah, nm_barang, jenis, stok_tersedia) {
        const container = document.getElementById('daftarBarangRevisiContainer');
        const newItem = document.createElement('div');
        newItem.className = 'barang-item-revisi mb-3';
        newItem.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Pilih Barang</label>
                <select name="barang_revisi[]" class="form-select select-barang-revisi" required onchange="updateStockInfoRevisi(this)">
                    <option value="">-- Pilih Barang --</option>
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
            </div>
            <div class="col-md-4">
                <label class="form-label">Jumlah (sak)</label>
                <input type="number" name="jumlah_revisi[]" class="form-control input-jumlah-revisi" required min="1" value="${jumlah}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm hapus-barang-revisi">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="stock-info-revisi text-muted small mt-1">Stok tersedia: ${stok_tersedia} sak</div>
    `;

        // Set selected barang
        const select = newItem.querySelector('.select-barang-revisi');
        select.value = id_stok;

        // Update info stok
        updateStockInfoRevisi(select);

        // Tambahkan event listener untuk tombol hapus
        newItem.querySelector('.hapus-barang-revisi').addEventListener('click', function() {
            if (document.querySelectorAll('.barang-item-revisi').length > 1) {
                this.closest('.barang-item-revisi').remove();
            }
        });

        container.appendChild(newItem);
    }

    // Fungsi untuk update info stok di form revisi
    function updateStockInfoRevisi(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const stokTersedia = selectedOption.getAttribute('data-stok');
        const stockInfo = selectElement.closest('.barang-item-revisi').querySelector('.stock-info-revisi');
        stockInfo.textContent = `Stok tersedia: ${stokTersedia} sak`;
    }

    // Event listener untuk tombol tambah barang di form revisi
    document.getElementById('tambahBarangRevisi').addEventListener('click', function() {
        addBarangItemRevisi('', 0, '', '', 0);
    });
</script>

</html>