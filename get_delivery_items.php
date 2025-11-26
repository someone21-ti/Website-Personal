<?php
require 'function.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([]);
    exit;
}

$id_pengiriman = $conn->real_escape_string($_GET['id']);
$query = "SELECT pd.id_stok, pd.jumlah, s.nm_barang, s.jenis, s.jumlah as stok_tersedia 
          FROM pengiriman_detail pd
          JOIN stok s ON pd.id_stok = s.id_stok
          WHERE pd.id_pengiriman = '$id_pengiriman'";

$result = $conn->query($query);
$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
