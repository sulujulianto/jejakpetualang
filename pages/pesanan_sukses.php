<?php
// Memanggil "penjaga gerbang" untuk memastikan pengguna sudah login.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

// Validasi tambahan untuk memastikan ID transaksi ada
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$title = 'Pesanan Berhasil - Jejak Petualang';
$page = __DIR__ . '/content/pesanan-sukses-content.php'; 
$extra_js = ''; 

require_once __DIR__ . '/../layout/app.php';
?>