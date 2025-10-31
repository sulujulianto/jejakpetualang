<?php
// [PERBAIKAN UTAMA] Menggunakan "penjaga gerbang" yang benar untuk halaman proses form standar.
// File ini akan memulai sesi USER_SESSION dan memastikan pengguna sudah login.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers/csrf.php';

// --- Keamanan dan Validasi Awal ---

// Memastikan permintaan datang dari form yang menggunakan metode POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, alihkan kembali ke halaman akun.
    header("Location: akun.php");
    exit();
}

require_valid_csrf_token();

// [CATATAN] Pengecekan login manual di bawah ini sudah tidak diperlukan
// karena sudah ditangani oleh 'user-auth.php' di baris paling atas.
// if (!isset($_SESSION['user_id'])) { ... }

// --- Pengambilan dan Pembersihan Data dari Form ---
$user_id = $_SESSION['user_id'];
$nama = trim($_POST['nama']);
$nomor_telepon = trim($_POST['nomor_telepon']);
$alamat = trim($_POST['alamat']);

// --- Validasi Sederhana di Sisi Server ---
if (empty($nama)) {
    // Jika nama kosong, atur pesan error di session.
    $_SESSION['pesan_error'] = "Nama lengkap tidak boleh kosong.";
    header("Location: akun.php");
    exit();
}

// --- Proses Update ke Database ---
try {
    // Mempersiapkan statement SQL UPDATE untuk mengubah data di tabel 'users'.
    $stmt = db()->prepare("UPDATE users SET nama = ?, nomor_telepon = ?, alamat = ? WHERE id = ?");
    $stmt->execute([$nama, $nomor_telepon, $alamat, $user_id]);

    // Perbarui juga nama yang tersimpan di session agar langsung berubah di navbar.
    $_SESSION['user_nama'] = $nama;

    // Atur pesan sukses di session untuk ditampilkan di halaman akun.
    $_SESSION['pesan_sukses'] = "Profil berhasil diperbarui.";

} catch (PDOException $e) {
    // Jika terjadi error database, simpan pesan error ke dalam session.
    $_SESSION['pesan_error'] = "Gagal memperbarui profil: " . $e->getMessage();
}

// Setelah semua proses selesai, alihkan pengguna kembali ke halaman akun.
header("Location: akun.php");
exit();
?>