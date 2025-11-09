<?php
// File: jejakpetualang/index.php

// CATATAN: File ini berfungsi sebagai "pintu gerbang" utama situs Anda.
// Tujuannya adalah untuk mengalihkan (redirect) pengunjung dari direktori root
// ke halaman utama yang sebenarnya, yang berada di dalam folder 'pages'.

// 1. Memanggil konfigurasi
// Kita perlu ini agar konstanta BASE_URL bisa digunakan.
require_once __DIR__ . '/config/koneksi.php';

// 2. Mengirimkan header HTTP 'Location'.
// Sekarang menggunakan BASE_URL yang dinamis dan portabel.
header('Location: ' . BASE_URL . '/pages/index.php');

// 3. Menghentikan eksekusi skrip PHP.
exit();
?>