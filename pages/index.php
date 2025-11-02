<?php
// CATATAN: File ini berfungsi sebagai "controller" atau file logika utama untuk Halaman Utama (Homepage).
// Tugasnya adalah mempersiapkan semua yang dibutuhkan sebelum menampilkan halaman.

// 1. Memanggil file konfigurasi untuk koneksi ke database dan memulai sesi.
require_once __DIR__ . '/../config/koneksi.php';

// 2. Menyiapkan semua variabel yang akan digunakan oleh file layout utama (`app.php`).
// Menetapkan judul halaman yang akan muncul di tab browser.
$title = 'Selamat Datang di Jejak Petualang';
// Memberi tahu file layout (`app.php`) untuk memuat konten HTML dari file ini.
// Ini adalah inti dari sistem templating: memisahkan logika dari tampilan.
$page = __DIR__ . '/content/home-content.php'; 

// 3. Menyiapkan JavaScript khusus yang hanya akan dimuat di halaman ini.
// `ob_start()` memulai output buffering. Semua output (seperti tag <script>) setelah ini akan ditangkap, bukan langsung ditampilkan.
ob_start();
?>
<script>
// Menjalankan JavaScript setelah seluruh halaman HTML selesai dimuat.
document.addEventListener('DOMContentLoaded', function() {
    // Cari elemen carousel berdasarkan ID-nya.
    const productCarousel = document.getElementById('productCarousel');
    // Jika elemen tersebut ditemukan, inisialisasi sebagai Bootstrap Carousel.
    if (productCarousel) {
        // `interval: 4000` berarti slide akan berpindah setiap 4 detik.
        // `pause: 'hover'` berarti carousel akan berhenti berputar saat mouse berada di atasnya.
        new bootstrap.Carousel(productCarousel, { interval: 4000, pause: 'hover' });
    }
});
</script>
<?php
// `ob_get_clean()` mengambil semua yang ada di buffer (tag <script> di atas) ke dalam variabel `$extra_js`,
// lalu membersihkan dan menghentikan buffer.
$extra_js = ob_get_clean();

// 4. TERAKHIR, panggil file layout utama untuk merakit dan menampilkan halaman lengkap.
// File `app.php` akan menggunakan variabel `$title`, `$page`, dan `$extra_js` yang sudah diatur di atas.
require_once __DIR__ . '/../layout/app.php';
?>
