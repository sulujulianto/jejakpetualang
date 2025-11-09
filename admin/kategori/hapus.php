<?php
// CATATAN: Ini adalah "script" murni untuk memproses aksi Hapus.
// Tidak ada HTML yang ditampilkan di file ini.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../../config/koneksi.php';
// auth.php dipanggil untuk memastikan hanya admin yang bisa mengakses script ini
require_once __DIR__ . '/../auth.php'; 

// 2. Ambil dan validasi ID dari URL
$id = $_GET['id'] ?? null;

if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID kategori tidak valid.'];
    header('Location: ' . BASE_URL . '/admin/kategori/index.php');
    exit();
}

try {
    // --- PERBAIKAN SQL INJECTION (DELETE) ---
    // Gunakan prepared statement untuk menghapus data dengan aman.
    $stmt = db()->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->execute([$id]);

    // setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION) di koneksi.php
    // akan melempar exception jika query gagal (misalnya foreign key constraint).
    // Jika tidak ada exception, berarti query berhasil.

    // Cek apakah ada baris yang terpengaruh (dihapus)
    if ($stmt->rowCount() > 0) {
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Kategori berhasil dihapus.'];
    } else {
        // Ini terjadi jika ID-nya valid tapi tidak ada di database
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Kategori tidak ditemukan.'];
    }

} catch (PDOException $e) {
    // Tangani error database
    // error_log($e->getMessage()); 
    if ($e->getCode() == '23000') {
        // Error Foreign Key constraint (misalnya, kategori masih dipakai oleh produk)
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Kategori tidak dapat dihapus karena masih digunakan oleh produk lain.'];
    } else {
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Terjadi masalah dengan database.'];
    }
}

// 4. Arahkan pengguna kembali ke halaman index kategori
header('Location: ' . BASE_URL . '/admin/kategori/index.php');
exit();
?>