<?php
require 'function.php';
if (isset($_POST['idbarang'])) {
    $idbarang = $_POST['idbarang'];
    $query = mysqli_query($conn, "SELECT jumlah FROM stok WHERE idbarang='$idbarang'");
    $data = mysqli_fetch_array($query);
    echo $data['jumlah'];
}
