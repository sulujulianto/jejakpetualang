<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses skrip ini.
require_once __DIR__ . '/auth.php';

// Mengambil ID produk dari parameter URL (contoh: produk_hapus.php?id=10).
$id = $_GET['id'] ?? null;

// Validasi ID: Memastikan ID ada dan merupakan bilangan bulat yang valid.
// Ini adalah langkah keamanan penting untuk mencegah error dan potensi serangan.
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    // Jika ID tidak valid, atur pesan error di session.
    $_SESSION['pesan_error'] = "ID produk tidak valid.";
    // Alihkan kembali ke halaman daftar produk.
    header("Location: /jejakpetualang/admin/produk/index.php");
    // Hentikan eksekusi skrip.
    exit();
}

// Menggunakan blok try-catch untuk menangani potensi kesalahan selama operasi database.
try {
    // --- Langkah 1: Ambil nama file gambar sebelum menghapus record dari database ---
    // Kita perlu nama filenya untuk bisa menghapus file fisik dari server nanti.
    $stmt = db()->prepare("SELECT gambar FROM produk WHERE id = ?");
    $stmt->execute([$id]);
    // Mengambil data produk (hanya kolom gambar).
    $produk = $stmt->fetch();

    // Memeriksa apakah produk dengan ID tersebut ada di database.
    if ($produk) {
        // --- Langkah 2: Hapus record produk dari database ---
        // Mempersiapkan statement DELETE untuk menghapus produk berdasarkan ID.
        $deleteStmt = db()->prepare("DELETE FROM produk WHERE id = ?");
        $deleteStmt->execute([$id]);

        // --- Langkah 3: Jika record berhasil dihapus, hapus file gambarnya dari server ---
        // rowCount() > 0 menandakan bahwa proses DELETE berhasil mempengaruhi satu baris (atau lebih).
        if ($deleteStmt->rowCount() > 0) {
            // Tentukan path lengkap menuju file gambar yang akan dihapus.
            $gambar_path = __DIR__ . "/../uploads/produk/" . $produk['gambar'];
            
            // Lakukan pengecekan keamanan:
            // 1. Pastikan nama file gambar tidak kosong.
            // 2. Pastikan file tersebut benar-benar ada di server sebelum mencoba menghapusnya.
            if (!empty($produk['gambar']) && file_exists($gambar_path)) {
                // Hapus file dari server. Simbol @ digunakan untuk menekan pesan error jika penghapusan gagal karena masalah izin (permission).
                @unlink($gambar_path); 
            }
            // Atur pesan sukses di session.
            $_SESSION['pesan_sukses'] = "Produk berhasil dihapus.";
        } else {
            // Ini terjadi jika query DELETE berjalan tapi tidak ada baris yang terhapus (kemungkinan ID sudah tidak ada).
            $_SESSION['pesan_error'] = "Gagal menghapus produk dari database (ID tidak ditemukan).";
        }
    } else {
        // Jika produk tidak ditemukan pada Langkah 1.
        $_SESSION['pesan_error'] = "Produk dengan ID tersebut tidak ditemukan.";
    }

// Blok catch akan menangkap error PDOException dari database.
} catch (PDOException $e) {
    // --- Penanganan Error Spesifik untuk Foreign Key ---
    // Pengecekan kode error '23000' adalah cara umum untuk mendeteksi pelanggaran integritas (integrity constraint violation).
    // Ini biasanya terjadi jika Anda mencoba menghapus produk yang sudah terhubung dengan data di tabel lain (misalnya, tabel transaksi_item).
    if ($e->getCode() == '23000') {
         $_SESSION['pesan_error'] = "Gagal menghapus: Produk ini sudah pernah dipesan dalam transaksi dan tidak dapat dihapus.";
    } else {
        // Untuk error database lainnya, tampilkan pesan error yang lebih umum.
        $_SESSION['pesan_error'] = "Terjadi kesalahan pada database: " . $e->getMessage();
    }
}

// Setelah semua proses selesai, arahkan kembali pengguna ke halaman manajemen produk.
// Pesan sukses atau error yang disimpan di session akan ditampilkan di halaman tersebut.
header("Location: /jejakpetualang/admin/produk/index.php");
exit();
