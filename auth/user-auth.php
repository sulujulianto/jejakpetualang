<?php
// CATATAN: File ini sekarang HANYA memeriksa login.
// Ia berasumsi sesi sudah dimulai oleh file pemanggil (seperti layout/app.php).

// 1. Memanggil konfigurasi
// Kita perlu ini agar konstanta BASE_URL bisa digunakan untuk redirect.
require_once __DIR__ . '/../config/koneksi.php';

// 2. Memastikan sesi aktif sebelum memeriksa.
if (session_status() === PHP_SESSION_NONE) {
    session_name('USER_SESSION');
    session_start();
}

// 3. --- Pengecekan Keamanan ---
if (!isset($_SESSION['user_id'])) {
    // Jika tidak login, siapkan pesan dan alihkan.
    $_SESSION['pesan'] = ['jenis' => 'info', 'isi' => 'Anda harus login untuk mengakses halaman tersebut.'];
    
    // 4. Menggunakan BASE_URL untuk redirect yang aman dan portabel.
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}