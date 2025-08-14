<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
// Ini penting untuk memastikan bahwa skrip memiliki akses ke fungsi-fungsi database.
require_once __DIR__ . '/../../config/koneksi.php';

// Menyertakan file otentikasi. 
// Ini memastikan bahwa hanya pengguna yang sudah login atau memiliki hak akses yang bisa menjalankan skrip ini.
require_once __DIR__ . '/../auth.php';

// Mengambil nilai 'id' dari URL (query string, contoh: hapus.php?id=10).
// Operator '??' (null coalescing) digunakan sebagai jalan pintas. Jika $_GET['id'] ada dan tidak null, nilainya akan diambil. Jika tidak, $id akan diisi dengan null.
$id = $_GET['id'] ?? null;

// Melakukan validasi awal terhadap ID yang diterima.
// Kondisi ini akan bernilai true jika:
// 1. !$id : ID tidak ada di URL (bernilai null).
// 2. !filter_var($id, FILTER_VALIDATE_INT) : ID ada, tetapi bukan merupakan bilangan bulat (integer) yang valid.
// Validasi ini penting untuk keamanan dan untuk memastikan query database hanya akan dijalankan dengan ID yang benar.
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    // Jika ID tidak valid, buat pesan error yang akan ditampilkan di halaman sebelumnya.
    $_SESSION['pesan_error'] = "ID kategori tidak valid.";
    // Alihkan (redirect) pengguna kembali ke halaman utama (index.php).
    header("Location: index.php");
    // Hentikan eksekusi skrip agar tidak ada kode lain yang dijalankan setelah redirect.
    exit();
}

// Blok try-catch digunakan untuk menangani kemungkinan error (exception) yang terjadi saat berinteraksi dengan database.
try {
    // Langkah Tambahan: Sebelum menghapus, periksa apakah kategori ini masih terhubung dengan data lain (misalnya, di tabel produk).
    // Ini adalah praktik yang baik untuk menjaga integritas data (foreign key constraint).

    // Mempersiapkan statement SQL untuk menghitung (COUNT) berapa banyak produk yang menggunakan kategori_id ini.
    $stmtCheck = db()->prepare("SELECT COUNT(*) FROM produk WHERE kategori_id = ?");
    // Menjalankan query dengan ID kategori yang akan dihapus.
    $stmtCheck->execute([$id]);
    
    // fetchColumn() mengambil nilai dari satu kolom dari hasil query (dalam hal ini, hasil dari COUNT(*)).
    // Jika hasilnya lebih dari 0, berarti ada produk yang masih menggunakan kategori ini.
    if ($stmtCheck->fetchColumn() > 0) {
        // Jika kategori masih digunakan, buat pesan error.
        $_SESSION['pesan_error'] = "Tidak bisa menghapus kategori karena masih digunakan oleh produk.";
        // Alihkan pengguna kembali ke halaman utama.
        header("Location: index.php");
        // Hentikan eksekusi skrip.
        exit();
    }

    // Jika kategori tidak digunakan oleh produk mana pun, lanjutkan proses penghapusan.
    // Mempersiapkan statement SQL DELETE untuk menghapus data dari tabel 'kategori' berdasarkan 'id'.
    // Penggunaan prepared statement (tanda '?') mencegah serangan SQL Injection.
    $stmt = db()->prepare("DELETE FROM kategori WHERE id = ?");
    // Menjalankan statement DELETE dengan ID yang sudah divalidasi.
    $stmt->execute([$id]);

    // rowCount() mengembalikan jumlah baris yang terpengaruh oleh statement DELETE.
    // Jika nilainya lebih besar dari 0, berarti ada baris yang berhasil dihapus.
    if ($stmt->rowCount() > 0) {
        // Jika penghapusan berhasil, buat pesan sukses.
        $_SESSION['pesan_sukses'] = "Kategori berhasil dihapus.";
    } else {
        // Jika tidak ada baris yang terpengaruh (rowCount() = 0), kemungkinan ID-nya tidak ada di database.
        $_SESSION['pesan_error'] = "Kategori tidak ditemukan atau sudah dihapus.";
    }
// Blok catch akan dieksekusi jika terjadi kesalahan selama proses di dalam blok try (misalnya, masalah koneksi database).
} catch (PDOException $e) {
    // Jika terjadi error database, simpan pesan error yang lebih teknis ke dalam session.
    // $e->getMessage() berisi detail pesan error dari PDO.
    $_SESSION['pesan_error'] = "Gagal menghapus kategori: " . $e->getMessage();
}

// Setelah semua proses selesai (baik berhasil maupun gagal), alihkan pengguna kembali ke halaman index.php.
// Pesan sukses atau error yang disimpan di session akan ditampilkan di halaman tersebut.
header("Location: index.php");
// Hentikan eksekusi skrip.
exit();
?>