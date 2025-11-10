<?php
// CATATAN: File ini adalah "controller" yang menyiapkan data dan logika untuk halaman login.

// --- TAHAP 1: PENGECEKAN JIKA SUDAH LOGIN ---
// Logika ini dijalankan sebelum HTML apapun ditampilkan untuk redirect jika perlu.

// Cek dulu sesi USER
session_name('USER_SESSION');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (isset($_SESSION['user_id'])) {
    header('Location: /jejakpetualang/pages/index.php');
    exit();
}
session_write_close(); // Tutup sesi dengan aman agar tidak mengganggu pengecekan berikutnya.

// Sekarang cek sesi ADMIN
session_name('ADMIN_SESSION');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (isset($_SESSION['user_id'])) {
    header('Location: /jejakpetualang/admin/dashboard.php');
    exit();
}
session_write_close(); // Tutup sesi ADMIN.


// --- TAHAP 2: PROSES LOGIN (METHOD POST) ---
// Memanggil file konfigurasi untuk koneksi database.
require_once __DIR__ . '/../config/koneksi.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        try {
            $stmt = db()->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Jika login berhasil, mulai sesi yang TEPAT berdasarkan peran.
                if ($user['role'] == 'admin') {
                    session_name('ADMIN_SESSION');
                    if (session_status() === PHP_SESSION_NONE) { session_start(); }
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nama'] = $user['nama'];
                    $_SESSION['user_role'] = $user['role'];
                    header('Location: /jejakpetualang/admin/dashboard.php');
                    exit();
                } else {
                    session_name('USER_SESSION');
                    if (session_status() === PHP_SESSION_NONE) { session_start(); }
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nama'] = $user['nama'];
                    $_SESSION['user_role'] = $user['role'];
                    header('Location: /jejakpetualang/pages/index.php');
                    exit();
                }
            } else {
                $error = 'Email atau password yang Anda masukkan salah.';
            }
        } catch (PDOException $e) {
            $error = "Terjadi error pada database.";
        }
    }
}

// --- TAHAP 3: PERSIAPAN UNTUK TAMPILAN (STRUKTUR ASLI ANDA) ---
// Menyiapkan variabel untuk layout utama.
$title = 'Login - Jejak Petualang';
$page = __DIR__ . '/content/login-content.php'; // Kita tetap pakai content file Anda
$extra_js = '';

// PENTING: Membuat "sinyal" untuk memberitahu layout/app.php agar tidak memulai sesi.
$is_login_page = true;

// TERAKHIR: Memanggil file layout utama Anda untuk merender halaman.
// Tampilan akan kembali normal karena kita menggunakan struktur asli Anda.
require_once __DIR__ . '/../layout/app.php';
?>