<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses skrip ini.
require_once __DIR__ . '/../auth.php';

// Mengambil ID voucher dari parameter URL (contoh: hapus.php?id=8).
$id = $_GET['id'] ?? null;

// Validasi ID: Memeriksa apakah ID ada dan merupakan bilangan bulat yang valid.
// Ini adalah langkah keamanan untuk mencegah error atau percobaan serangan.
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    // Jika ID tidak valid, atur pesan error di session.
    $_SESSION['pesan_error'] = "ID voucher tidak valid.";
    // Alihkan kembali ke halaman daftar voucher.
    header("Location: index.php");
    // Hentikan eksekusi skrip.
    exit();
}

// Menggunakan blok try-catch untuk menangani potensi kesalahan selama operasi database.
try {
    // Mempersiapkan statement SQL DELETE untuk menghapus baris dari tabel 'vouchers' berdasarkan 'id'.
    // Menggunakan prepared statement (?) adalah praktik keamanan untuk mencegah SQL Injection.
    $stmt = db()->prepare("DELETE FROM vouchers WHERE id = ?");
    // Menjalankan statement dengan mengikat nilai dari variabel $id ke placeholder (?).
    $stmt->execute([$id]);

    // Memeriksa jumlah baris yang terpengaruh oleh query DELETE.
    // Jika rowCount() > 0, berarti ada baris yang berhasil dihapus.
    if ($stmt->rowCount() > 0) {
        // Jika berhasil, atur pesan sukses di session.
        $_SESSION['pesan_sukses'] = "Voucher berhasil dihapus.";
    } else {
        // Jika tidak ada baris yang terpengaruh, berarti voucher dengan ID tersebut tidak ditemukan.
        $_SESSION['pesan_error'] = "Voucher tidak ditemukan atau sudah dihapus.";
    }
// Blok catch akan dieksekusi jika terjadi error di dalam blok try (misalnya, masalah koneksi atau foreign key).
} catch (PDOException $e) {
    // Jika terjadi error database, simpan pesan error yang spesifik ke dalam session.
    $_SESSION['pesan_error'] = "Gagal menghapus voucher: " . $e->getMessage();
}

// Setelah semua proses selesai, baik berhasil maupun gagal, alihkan pengguna kembali ke halaman daftar voucher.
// Pesan yang disimpan di session akan ditampilkan di halaman tersebut.
header("Location: index.php");
// Hentikan eksekusi skrip.
exit();