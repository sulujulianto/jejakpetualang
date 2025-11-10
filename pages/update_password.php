<?php
// [PERBAIKAN UTAMA] Menggunakan "penjaga gerbang" yang benar untuk halaman proses form standar.
// File ini akan memulai sesi USER_SESSION dan memastikan pengguna sudah login.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

// --- Keamanan dan Validasi Awal ---

// Memastikan permintaan datang dari form yang menggunakan metode POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, alihkan ke halaman akun.
    header("Location: akun.php");
    exit();
}

// [CATATAN] Pengecekan login manual di bawah ini sudah tidak diperlukan
// karena sudah ditangani oleh 'user-auth.php' di baris paling atas.
// if (!isset($_SESSION['user_id'])) { ... }

// --- Pengambilan Data dari Form ---
$user_id = $_SESSION['user_id'];
$password_lama = $_POST['password_lama'];
$password_baru = $_POST['password_baru'];
$konfirmasi_password = $_POST['konfirmasi_password'];

// --- Validasi Input ---

// Cek apakah ada field yang kosong.
if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
    $_SESSION['pesan_error'] = "Semua field password wajib diisi.";
    // Alihkan kembali ke halaman akun, langsung ke tab password (`#v-pills-password`).
    header("Location: akun.php#v-pills-password");
    exit();
}

// Cek panjang minimal password baru.
if (strlen($password_baru) < 6) {
    $_SESSION['pesan_error'] = "Password baru minimal harus 6 karakter.";
    header("Location: akun.php#v-pills-password");
    exit();
}

// Cek apakah password baru dan konfirmasinya cocok.
if ($password_baru !== $konfirmasi_password) {
    $_SESSION['pesan_error'] = "Konfirmasi password baru tidak cocok.";
    header("Location: akun.php#v-pills-password");
    exit();
}

// --- Proses Update ke Database ---
try {
    // Ambil hash password pengguna saat ini dari database untuk verifikasi.
    $stmt = db()->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Verifikasi password lama:
    if ($user && password_verify($password_lama, $user['password'])) {
        // Jika password lama benar, lanjutkan proses.
        
        // Hash password baru sebelum disimpan. Ini adalah langkah keamanan yang SANGAT PENTING.
        $new_password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        
        // Persiapkan dan jalankan statement UPDATE untuk mengubah password di database.
        $updateStmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$new_password_hash, $user_id]);

        // Atur pesan sukses dan alihkan kembali ke tab password.
        $_SESSION['pesan_sukses'] = "Password berhasil diperbarui.";
        header("Location: akun.php#v-pills-password");
        exit();
    } else {
        // Jika password lama yang dimasukkan salah.
        $_SESSION['pesan_error'] = "Password saat ini yang Anda masukkan salah.";
        header("Location: akun.php#v-pills-password");
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['pesan_error'] = "Terjadi kesalahan database: " . $e->getMessage();
    header("Location: akun.php#v-pills-password");
    exit();
}