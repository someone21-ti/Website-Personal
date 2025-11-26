<?php
if (isset($_SESSION['log'])) {
    // user sudah login
} else {
    header('location:login.php');
    exit;
}
