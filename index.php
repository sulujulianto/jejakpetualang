<?php
// File: jejakpetualang/index.php

// CATATAN: File ini berfungsi sebagai "pintu gerbang" utama situs Anda.
// Tujuannya adalah untuk mengalihkan (redirect) pengunjung dari direktori root
// ke halaman utama yang sebenarnya, yang berada di dalam folder 'pages'.

// Mengirimkan header HTTP 'Location'. Ini adalah perintah untuk browser
// agar segera berpindah ke URL yang ditentukan.
header('Location: pages/index.php');

// Menghentikan eksekusi skrip PHP. Ini adalah praktik yang baik setelah melakukan redirect
// untuk memastikan tidak ada kode lain yang tidak sengaja dijalankan.
exit();
?>