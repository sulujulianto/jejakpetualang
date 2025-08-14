<?php
// CATATAN: File ini sekarang HANYA memeriksa login.
// Ia berasumsi sesi sudah dimulai oleh file pemanggil (seperti layout/app.php).

// Memastikan sesi aktif sebelum memeriksa.
if (session_status() === PHP_SESSION_NONE) {
    session_name('USER_SESSION');
    session_start();
}

// --- Pengecekan Keamanan ---
if (!isset($_SESSION['user_id'])) {
    // Jika tidak login, siapkan pesan dan alihkan.
    $_SESSION['pesan'] = ['jenis' => 'info', 'isi' => 'Anda harus login untuk mengakses halaman tersebut.'];
    header('Location: /jejakpetualang/auth/login.php');
    exit();
}
?>