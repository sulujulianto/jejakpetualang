<?php
// CATATAN: File ini berfungsi sebagai "controller" atau file logika utama untuk Halaman Daftar Produk.
// Tugas utamanya adalah mempersiapkan semua variabel yang dibutuhkan sebelum merakit dan menampilkan halaman lengkap.

// Panggilan ke 'user-auth.php' telah dihapus karena ini adalah halaman publik.
// require_once __DIR__ . '/../auth/user-auth.php';

// 1. Memanggil file konfigurasi.
// Ini adalah langkah pertama yang penting agar semua komponen lain dapat berfungsi.
require_once __DIR__ . '/../config/koneksi.php';

// 2. Menyiapkan semua variabel yang akan digunakan oleh file layout utama (`app.php`).
// Variabel-variabel ini akan "dilemparkan" ke dalam file layout.

// Menetapkan judul halaman yang akan muncul di tag <title> pada HTML.
$title = 'Daftar Produk - Jejak Petualang';

// Memberi tahu file layout (`app.php`) bagian konten mana yang harus dimuat.
// Ini adalah inti dari sistem templating sederhana: memisahkan logika dari tampilan.
// File `product-content.php` akan berisi semua HTML khusus untuk menampilkan daftar produk, filter, dll.
$page = __DIR__ . '/content/product-content.php'; 

// Variabel untuk menyisipkan JavaScript tambahan jika diperlukan.
// Untuk halaman ini, tidak ada JavaScript khusus, jadi nilainya dikosongkan.
$extra_js = ''; 

// 3. TERAKHIR, panggil file layout utama untuk merakit dan menampilkan halaman lengkap.
// File `app.php` akan bertindak sebagai "kerangka" yang akan mengambil dan menggunakan
// variabel `$title`, `$page`, dan `$extra_js` yang sudah kita siapkan di atas untuk membangun halaman HTML yang utuh.
require_once __DIR__ . '/../layout/app.php';
?>