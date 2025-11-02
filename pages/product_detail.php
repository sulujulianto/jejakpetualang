<?php
// --- Langkah 1: Memanggil file konfigurasi ---
// Ini memastikan koneksi ke database tersedia dan sesi (session) sudah dimulai.
require_once __DIR__ . '/../config/koneksi.php';

// --- Langkah 2: Menyiapkan judul halaman default ---
// Judul ini akan digunakan jika terjadi error atau jika produk tidak ditemukan.
$title = 'Detail Produk - Jejak Petualang';

// Mengambil ID produk dari parameter URL dan mengubahnya menjadi integer untuk keamanan.
$produk_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- Langkah 3: Jika ada ID produk, ambil nama produk untuk membuat judul yang lebih spesifik ---
// Pengecekan ini memastikan query hanya berjalan jika ada ID yang valid.
if ($produk_id > 0) {
    // Menggunakan try-catch untuk menangani potensi error dari database.
    try {
        // Mempersiapkan query untuk mengambil HANYA kolom 'nama' dari produk berdasarkan ID.
        $stmt = db()->prepare("SELECT nama FROM produk WHERE id = ?");
        $stmt->execute([$produk_id]);
        
        // `fetchColumn()` mengambil nilai dari satu kolom dari baris berikutnya.
        // Jika produk ditemukan, `$nama_produk` akan berisi nama produk, jika tidak, hasilnya `false`.
        if ($nama_produk = $stmt->fetchColumn()) {
            // Jika nama produk berhasil didapat, perbarui variabel `$title`.
            // `htmlspecialchars` digunakan untuk keamanan, mencegah serangan XSS.
            $title = htmlspecialchars($nama_produk) . ' - Jejak Petualang';
        }
    } catch (PDOException $e) {
        // Jika terjadi error saat mengambil judul (misalnya, koneksi gagal),
        // kita tidak menghentikan skrip (`die()`), tapi membiarkannya lanjut.
        // Halaman akan tetap ditampilkan dengan judul default.
    }
}

// --- Langkah 4: Menentukan file konten utama yang akan ditampilkan ---
// Memberi tahu file layout (`app.php`) untuk memuat konten HTML dari file ini.
$page = __DIR__ . '/content/product-detail-content.php';

// --- Langkah 5: Menyiapkan variabel untuk JavaScript tambahan ---
// Dikosongkan karena logika JavaScript yang relevan telah dipindahkan ke file global (main.js).
$extra_js = '';

// --- Langkah 6: Memanggil file layout utama untuk merakit dan menampilkan halaman lengkap ---
// File `app.php` akan menggunakan variabel `$title`, `$page`, dan `$extra_js` yang sudah diatur di atas.
require_once __DIR__ . '/../layout/app.php';
?>
