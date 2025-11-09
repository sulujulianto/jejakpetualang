<?php
// CATATAN: Ini adalah "script" murni untuk memproses aksi Hapus Produk.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/auth.php'; // Pastikan hanya admin

// 2. Ambil dan validasi ID dari URL
$id = $_GET['id'] ?? null;

if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID produk tidak valid.'];
    header('Location: ' . BASE_URL . '/admin/produk/index.php');
    exit();
}

try {
    // --- PERBAIKAN SQL INJECTION (SELECT) ---
    // 3. Ambil path gambar SEBELUM menghapus, agar kita bisa hapus filenya.
    $stmt_select = db()->prepare("SELECT gambar FROM produk WHERE id = ?");
    $stmt_select->execute([$id]);
    $produk = $stmt_select->fetch();

    // --- PERBAIKAN SQL INJECTION (DELETE) ---
    // 4. Hapus produk dari database dengan aman.
    $stmt_delete = db()->prepare("DELETE FROM produk WHERE id = ?");
    $stmt_delete->execute([$id]);

    // 5. Cek apakah penghapusan berhasil
    if ($stmt_delete->rowCount() > 0) {
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Produk berhasil dihapus.'];
        
        // 6. Hapus file gambar dari server jika ada
        if ($produk && !empty($produk['gambar'])) {
            $path_gambar_fisik = __DIR__ . '/../' . $produk['gambar'];
            if (file_exists($path_gambar_fisik)) {
                unlink($path_gambar_fisik);
            }
        }
    } else {
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Produk tidak ditemukan.'];
    }

} catch (PDOException $e) {
    // error_log($e->getMessage()); 
    // Tangani error, misalnya jika produk terkait dengan pesanan (foreign key)
    if ($e->getCode() == '23000') {
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Produk tidak dapat dihapus karena terkait dengan data pesanan.'];
    } else {
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Terjadi masalah dengan database.'];
    }
}

// 7. Arahkan pengguna kembali ke halaman index produk
header('Location: ' . BASE_URL . '/admin/produk/index.php');
exit();
?>