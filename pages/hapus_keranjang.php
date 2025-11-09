<?php
// CATATAN: Ini adalah "script" untuk memproses aksi Hapus Item Keranjang.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../auth/user-auth.php'; 

// 2. Ambil dan validasi ID dari URL
$user_id = $_SESSION['user_id'];
$keranjang_id = $_GET['id'] ?? null;

if (!$keranjang_id || !filter_var($keranjang_id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID item tidak valid.'];
    header('Location: ' . BASE_URL . '/pages/keranjang.php');
    exit();
}

try {
    // --- PERBAIKAN SQL INJECTION (DELETE) ---
    // 3. Gunakan prepared statement untuk menghapus data dengan aman.
    // Tambahkan "AND user_id = ?" untuk memastikan user hanya bisa
    // menghapus item miliknya sendiri. Ini sangat PENTING!
    $stmt = db()->prepare("DELETE FROM keranjang WHERE id = ? AND user_id = ?");
    $stmt->execute([$keranjang_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Item berhasil dihapus dari keranjang.'];
    } else {
        // Ini terjadi jika user mencoba hapus ID yang bukan miliknya
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Gagal menghapus item atau item tidak ditemukan.'];
    }

} catch (PDOException $e) {
    // error_log($e->getMessage());
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Terjadi masalah dengan database.'];
}

// 4. Arahkan pengguna kembali ke halaman keranjang
header('Location: ' . BASE_URL . '/pages/keranjang.php');
exit();
?>