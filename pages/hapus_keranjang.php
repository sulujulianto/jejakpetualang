<?php
// CATATAN: File ini sekarang menggunakan "penjaga gerbang" yang benar dan logikanya telah disederhanakan.

// Menggunakan "penjaga gerbang" untuk halaman proses form standar, bukan AJAX.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

// [CATATAN] Pengecekan login manual sudah dihapus karena sudah ditangani oleh user-auth.php.

$user_id = $_SESSION['user_id'];
// Nama parameter diubah menjadi 'item_id' agar konsisten dengan file keranjang_action.php.
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

if ($item_id > 0) {
    try {
        // Query DELETE disederhanakan dan menggunakan nama tabel yang benar: `keranjang_pengguna`.
        // Cukup hapus berdasarkan ID unik item keranjang dan pastikan item itu milik pengguna yang sedang login.
        $stmt = db()->prepare("DELETE FROM keranjang_pengguna WHERE id = ? AND user_id = ?");
        $stmt->execute([$item_id, $user_id]);
        
        // Atur pesan sukses.
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Produk berhasil dihapus dari keranjang.'];

    } catch (PDOException $e) {
        // Jika terjadi error database, simpan pesan error ke dalam session.
        $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Gagal menghapus item dari keranjang.'];
    }
}

// Setelah semua proses selesai, alihkan pengguna kembali ke halaman keranjang.
header('Location: keranjang.php');
// Hentikan eksekusi skrip.
exit();
?>