<?php
// CATATAN: Ini adalah "controller" untuk halaman registrasi.

// --- TAHAP 1: PENGECEKAN JIKA SUDAH LOGIN ---
// Sama seperti login, kita cek dulu agar yang sudah login tidak bisa register lagi.
session_name('USER_SESSION');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (isset($_SESSION['user_id'])) {
    // Arahkan ke index jika sudah login sebagai user
    header('Location: /jejakpetualang/pages/index.php');
    exit();
}
session_write_close();

session_name('ADMIN_SESSION');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (isset($_SESSION['user_id'])) {
    // Arahkan ke dashboard jika sudah login sebagai admin
    header('Location: /jejakpetualang/admin/dashboard.php');
    exit();
}
session_write_close();

// Buka kembali sesi USER untuk CSRF dan flash message
session_name('USER_SESSION');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- TAHAP 2: PROSES REGISTRASI (METHOD POST) ---
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers/csrf.php';

$errors = [];
$nama = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_valid_csrf_token();

    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Validasi dasar
    if (empty($nama)) {
        $errors[] = 'Nama wajib diisi.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }
    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'Password minimal harus 8 karakter.';
    }
    if ($password !== $konfirmasi_password) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // Jika tidak ada error validasi, lanjut ke database
    if (empty($errors)) {
        try {
            // --- PERBAIKAN SQL INJECTION (SELECT) ---
            // 1. Cek apakah email sudah terdaftar (AMAN)
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.';
            } else {
                // Email aman, lanjut buat user baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // --- PERBAIKAN SQL INJECTION (INSERT) ---
                // 2. Masukkan user baru ke database (AMAN)
                $insertStmt = db()->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'user')");
                $insertStmt->execute([$nama, $email, $hashed_password]);

                // Registrasi berhasil, arahkan ke login dengan pesan sukses
                $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Registrasi berhasil! Silakan login dengan akun baru Anda.'];
                header('Location: ' . BASE_URL . '/auth/login.php');
                exit();
            }
        } catch (PDOException $e) {
            // Tangani error database
            // error_log($e->getMessage()); // Catat error ke log server
            $errors[] = "Terjadi masalah dengan database. Silakan coba lagi nanti.";
        }
    }
}

// --- TAHAP 3: PERSIAPAN UNTUK TAMPILAN ---
$title = 'Daftar - Jejak Petualang';
// Tetap gunakan content file Anda
$page = __DIR__ . '/content/register-content.php'; 
$extra_js = '';

// Memberi sinyal ke layout/app.php agar tidak memulai sesi lagi
$is_login_page = true; 

// Memanggil file layout utama
require_once __DIR__ . '/../layout/app.php';
?>