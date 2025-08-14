<?php
// CATATAN: File ini adalah "penjaga gerbang" (authentication guard) untuk semua halaman admin.

// PENTING: Atur nama sesi KHUSUS untuk admin SEBELUM session_start()
session_name('ADMIN_SESSION');

// Memulai atau melanjutkan sesi ADMIN_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Pengecekan Keamanan (Security Check) ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Anda harus login sebagai admin untuk mengakses halaman ini.'];
    header('Location: /jejak-petualang/auth/login.php');
    exit();
}
?>