<?php
// Memanggil "penjaga gerbang" untuk memastikan pengguna sudah login.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers/csrf.php';

$title = 'Checkout - Jejak Petualang';
$page = __DIR__ . '/content/checkout-content.php'; 
$extra_js = '';

require_once __DIR__ . '/../layout/app.php';
?>