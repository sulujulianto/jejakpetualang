<?php
// --- AWAL PERBAIKAN ---
// Inisialisasi session dengan nama yang benar (USER_SESSION)
session_name('USER_SESSION');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// --- AKHIR PERBAIKAN ---

// Memanggil "penjaga gerbang" untuk memastikan pengguna sudah login.
// require_once __DIR__ . '/../auth/user-auth.php';

// Menyertakan file konfigurasi untuk koneksi ke database dan memulai sesi (session).
require_once __DIR__ . '/../config/koneksi.php';
// Menetapkan judul halaman yang akan ditampilkan di tag <title> HTML.
$page_title = 'Promo Spesial';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Jejak Petualang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="main-wrapper d-flex flex-column">
    <?php 
    // Menyertakan file navigasi atas (navbar) ke dalam halaman.
    include __DIR__ . '/../partials/navbar.php'; 
    ?>

    <main class="main-content py-5 flex-grow-1">
        <div class="container">
            
            <div class="text-center text-white mb-5">
                <h1 class="section-title">Promo Hari Kemerdekaan Indonesia</h1>
                <p class="lead">Rayakan semangat kemerdekaan dengan perlengkapan jejakpetualang terbaik!</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="promo-card">
                        <h4>Diskon Merah Putih 17%</h4>
                        <p>Dapatkan diskon 17% untuk seluruh produk selama bulan Agustus.</p>
                        <a href="/jejakpetualang/pages/product.php" class="btn btn-danger mt-3">Lihat Produk</a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="promo-card">
                        <h4>Gratis Ongkir Seluruh Indonesia</h4>
                        <p>Nikmati pengiriman gratis untuk semua pembelian di bulan kemerdekaan.</p>
                        <a href="/jejakpetualang/pages/product.php" class="btn btn-warning mt-3">Belanja Sekarang</a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="promo-card">
                        <h4>Hadiah Spesial Setiap Pembelian</h4>
                        <p>Dapatkan hadiah eksklusif untuk setiap pembelian minimal Rp500.000.</p>
                        <a href="/jejakpetualang/pages/product.php" class="btn btn-success mt-3">Ambil Promo</a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php 
    // Menyertakan file footer (bagian bawah halaman).
    include __DIR__ . '/../partials/footer.php'; 
    ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>