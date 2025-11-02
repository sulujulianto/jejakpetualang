<?php
// Menyertakan file konfigurasi dan otentikasi
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth.php';

// Ambil ID user yang akan dihapus dari URL
$id_to_delete = $_GET['id'] ?? null;
// Ambil ID user yang sedang login dari session
$logged_in_user_id = $_SESSION['user_id'] ?? null;

// Validasi awal: Pastikan ada ID di URL
if (!$id_to_delete) {
    $_SESSION['pesan_error'] = "Permintaan tidak valid. ID pengguna tidak disediakan.";
    header("Location: index.php");
    exit();
}

// Pengecekan keamanan: Mencegah admin menghapus akunnya sendiri
if ($id_to_delete == $logged_in_user_id) {
    $_SESSION['pesan_error'] = "Aksi dilarang! Anda tidak dapat menghapus akun Anda sendiri.";
    header("Location: index.php");
    exit();
}

try {
    // Ini adalah query DELETE yang sesungguhnya
    $stmt = db()->prepare("DELETE FROM users WHERE id = ?");
    
    // Eksekusi query
    $stmt->execute([$id_to_delete]);
    
    // Cek apakah ada baris yang benar-benar terhapus
    if ($stmt->rowCount() > 0) {
        $_SESSION['pesan_sukses'] = "Pengguna berhasil dihapus secara permanen.";
    } else {
        $_SESSION['pesan_error'] = "Pengguna tidak ditemukan atau sudah dihapus.";
    }

} catch (PDOException $e) {
    // Blok ini sebagai pengaman jika masih ada error lain dari database
    $_SESSION['pesan_error'] = "Gagal menghapus pengguna. Error database: " . $e->getMessage();
}

// Alihkan kembali ke halaman daftar pengguna
header("Location: index.php");
exit();
?>
