<?php
// CATATAN: Ini adalah halaman dasbor Anda yang sudah diintegrasikan dengan layout yang benar.

// 1. Memanggil file konfigurasi untuk koneksi ke database.
// Ini diperlukan agar kita bisa menjalankan query untuk mengambil data statistik.
require_once __DIR__ . '/../config/koneksi.php';

// 2. Memanggil file header.
// Di dalam 'header.php' sudah ada pemanggilan 'auth.php', sehingga keamanan (pengecekan login admin) sudah terjamin.
// File ini juga akan menampilkan bagian atas dari layout, seperti navbar.
$page_title = 'Dashboard';
include __DIR__ . '/partials/header.php';

// 3. Logika untuk mengambil data statistik dari database.
// Menggunakan blok try-catch untuk menangani potensi error jika query ke database gagal.
try {
    // Menjalankan query untuk menghitung jumlah total baris (produk) di tabel 'produk'.
    // fetchColumn() digunakan untuk mengambil hasil dari satu kolom saja (hasil dari COUNT).
    $jumlah_produk = db()->query("SELECT COUNT(id) FROM produk")->fetchColumn();
    
    // Menghitung jumlah total baris (kategori) di tabel 'kategori'.
    $jumlah_kategori = db()->query("SELECT COUNT(id) FROM kategori")->fetchColumn();
    
    // Menghitung jumlah total baris di tabel 'users' yang memiliki peran (role) sebagai 'user'.
    $jumlah_user = db()->query("SELECT COUNT(id) FROM users WHERE role = 'user'")->fetchColumn();

// Blok catch akan dieksekusi jika terjadi error di dalam blok try.
} catch (PDOException $e) {
    // Jika query gagal (misalnya, tabel tidak ada atau koneksi error), atur nilai statistik ke 0.
    // Ini adalah fallback agar halaman tidak menampilkan error dan tetap bisa dimuat.
    $jumlah_produk = 0;
    $jumlah_kategori = 0;
    $jumlah_user = 0;
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4 text-center">Dashboard Admin</h1>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-white border-primary h-100 admin-stat-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Produk</h5>
                            <p class="card-text fs-1 fw-bold"><?php echo $jumlah_produk; ?></p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="/jejakpetualang/admin/produk/index.php" class="text-white stretched-link">Lihat Produk &rarr;</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-white border-success h-100 admin-stat-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Kategori</h5>
                            <p class="card-text fs-1 fw-bold"><?php echo $jumlah_kategori; ?></p>
                        </div>
                         <div class="card-footer text-center">
                            <a href="/jejakpetualang/admin/kategori/index.php" class="text-white stretched-link">Manajemen Kategori &rarr;</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-white border-info h-100 admin-stat-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Jumlah Pengguna</h5>
                            <p class="card-text fs-1 fw-bold"><?php echo $jumlah_user; ?></p>
                        </div>
                         <div class="card-footer text-center">
                            <a href="/jejakpetualang/admin/users/index.php" class="text-white stretched-link">Lihat Pengguna &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>