<?php
// CATATAN: File ini berfungsi sebagai "penjaga gerbang" (authentication guard) untuk semua halaman di dalam direktori admin.
// Tujuannya adalah untuk memastikan hanya pengguna dengan peran 'admin' yang dapat mengakses halaman-halaman tersebut.

// Memeriksa status session saat ini.
// `session_status() === PHP_SESSION_NONE` berarti belum ada session yang aktif untuk skrip ini.
if (session_status() === PHP_SESSION_NONE) {
    // Jika belum ada session, mulai session baru. Ini wajib dilakukan sebelum mengakses variabel `$_SESSION`.
    session_start();
}

// --- Pengecekan Keamanan (Security Check) ---
// Memeriksa dua kondisi utama:
// 1. `!isset($_SESSION['user_id'])`: Apakah variabel session 'user_id' TIDAK diatur? Ini menandakan pengguna belum login.
// 2. `$_SESSION['user_role'] !== 'admin'`: Jika sudah login, apakah perannya BUKAN 'admin'?
// Jika salah satu atau kedua kondisi ini benar (true), maka akses ditolak.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Jika akses ditolak, siapkan pesan error di dalam session.
    // Pesan ini akan dapat diakses di halaman login setelah redirect.
    $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Akses ditolak. Anda harus login sebagai admin.'];
    
    // Mengalihkan (redirect) pengguna secara paksa ke halaman login.
    header('Location: /jejakpetualang/auth/login.php');
    
    // Menghentikan eksekusi skrip. Ini sangat penting untuk memastikan tidak ada kode lain di halaman admin yang dieksekusi setelah redirect.
    exit();
}
?>
