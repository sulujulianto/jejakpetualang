<?php
// auth/logout.php

// Cek apakah ada parameter 'from' di URL untuk menentukan logout dari mana
$from = $_GET['from'] ?? 'user';

if ($from === 'admin') {
    // Jika logout dari admin, hancurkan ADMIN_SESSION
    session_name('ADMIN_SESSION');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} else {
    // Jika tidak, hancurkan USER_SESSION (default)
    session_name('USER_SESSION');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Hapus semua variabel sesi
$_SESSION = [];

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login utama
header('Location: /jejakpetualang/auth/login.php');
exit();
?>
