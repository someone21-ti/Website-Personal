<?php
require 'function.php';
require 'cek.php';

// Ambil ID pengiriman dari parameter URL
$id_pengiriman = $_GET['id'] ?? 0;

// Query data pengiriman
$query = $conn->query("SELECT 
    p.*, 
    GROUP_CONCAT(CONCAT(s.nm_barang, '|', pd.jumlah) SEPARATOR ';;') as barang_data
    FROM pengiriman p
    JOIN pengiriman_detail pd ON p.id_pengiriman = pd.id_pengiriman
    JOIN stok s ON pd.id_stok = s.id_stok
    WHERE p.id_pengiriman = '$id_pengiriman'");

$data = $query->fetch_assoc();

// Jika data tidak ditemukan
if (!$data) {
    die("Data pengiriman tidak ditemukan");
}

// Format nomor surat jalan
$no_surat_jalan = "SJ-" . date('Ymd') . "-" . str_pad($id_pengiriman, 4, '0', STR_PAD_LEFT);

// Parsing data barang
$barang_items = [];
if (!empty($data['barang_data'])) {
    $items = explode(';;', $data['barang_data']);
    foreach ($items as $item) {
        list($nm_barang, $jumlah) = explode('|', $item);
        $barang_items[] = [
            'nama' => $nm_barang,
            'jumlah' => $jumlah,
            'berat' => $jumlah * 50 // Asumsi 50kg per sak
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Surat Jalan - <?= $no_surat_jalan ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .header p {
            margin: 3px 0;
            font-size: 12px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .info-box {
            width: 48%;
        }

        .info-label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .signature {
            text-align: center;
            width: 40%;
        }

        .signature-space {
            height: 60px;
            border-bottom: 1px solid #000;
            margin: 10px 0;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }

            .container {
                border: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Perusahaan -->
        <div class="header">
            <h1>SATWA INDOTAMA PERKASA</h1>
            <p>Jl. Tanjung Harapan No. 88 Rt. 021 Rw. 005</p>
            <p>Telp. 0711 - 817043, 817243 Fax : 0711 - 819460</p>
            <p>PALEMBANG 30114 | e-mail : satwagroup@yahoo.com</p>
        </div>

        <!-- Info Pengiriman -->
        <div class="info-section">
            <div class="info-box">
                <p><span class="info-label">Kepada:</span> <?= htmlspecialchars($data['tujuan']) ?></p>
                <p><span class="info-label">Alamat:</span> <?= htmlspecialchars($data['alamat'] ?? '.......................') ?></p>
            </div>
            <div class="info-box">
                <p><span class="info-label">Tanggal:</span> <?= date('d/m/Y', strtotime($data['tanggal_pengiriman'])) ?></p>
                <p><span class="info-label">No. Surat Jalan:</span> <?= $no_surat_jalan ?></p>
                <p>
                    <span class="info-label">No. Kendaraan:</span>
                    <?= htmlspecialchars($data['plat_supir'] ?? '-') ?>
                    - <?= htmlspecialchars($data['nama_supir'] ?? '-') ?>
                </p>
            </div>
        </div>

        <!-- Tabel Barang -->
        <table>
            <thead>
                <tr>
                    <th width="10%">Kode</th>
                    <th>Jenis Ransum</th>
                    <th width="15%">Jumlah Sak</th>
                    <th width="15%">Berat (Kg)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($barang_items as $item): ?>
                    <tr>
                        <td><?= substr($item['nama'], 0, 3) ?></td> <!-- Contoh kode dari nama -->
                        <td><?= htmlspecialchars($item['nama']) ?></td>
                        <td><?= $item['jumlah'] ?></td>
                        <td><?= $item['berat'] ?> kg</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Catatan (opsional) -->
        <div style="margin-bottom: 20px;">
            <p><strong>Catatan:</strong></p>
            <p>Barang sudah diperiksa dan dalam kondisi baik.</p>
        </div>

        <!-- Tanda Tangan -->
        <div class="footer">
            <div class="signature">
                <p>Penerima,</p>
                <div class="signature-space"></div>
                <p>(_______________________)</p>
                <p>Nama: ___________________</p>
            </div>
            <div class="signature">
                <p>Hormat Kami,</p>
                <div class="signature-space"></div>
                <p>(_______________________)</p>
                <p>Bag. Penjualan</p>
            </div>
        </div>

        <!-- Tombol Cetak (hanya tampil di browser) -->
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Cetak Surat Jalan</button>
            <button onclick="window.close()" style="padding: 10px 20px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Tutup</button>
        </div>
    </div>

    <script>
        // Auto print saat halaman selesai dimuat (opsional)
        window.onload = function() {
            // window.print(); // Uncomment jika ingin auto print
        };
    </script>
</body>

</html>