<?php
// Memanggil "penjaga gerbang" untuk memastikan pengguna sudah login.
// require_once __DIR__ . '/../auth/user-auth.php';

// Menyertakan file konfigurasi untuk koneksi ke database dan memulai sesi.
require_once __DIR__ . '/../config/koneksi.php';
// Menetapkan judul halaman yang akan ditampilkan di tag <title> HTML.
$page_title = 'Info & Kontak';
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
    // Menyertakan file navigasi atas (navbar).
    include __DIR__ . '/../partials/navbar.php'; 
    ?>

    <main class="main-content py-5 flex-grow-1">
        <div class="container">
            
            <div class="info-box">
                <div class="text-center">
                    <h1 class="section-title">Tentang Jejak Petualang</h1>
                    <p class="lead">
                        <strong>Jejak Petualang</strong> adalah toko online yang menyediakan berbagai perlengkapan camping dan aktivitas luar ruangan terbaik untuk para petualang sejati di seluruh Indonesia.
                    </p>
                    <p>
                        Kami menyediakan berbagai produk outdoor berkualitas mulai dari tenda, jaket, sleeping bag, hingga alat survival. Kami berkomitmen untuk memberikan perlengkapan yang aman, nyaman, dan tahan lama untuk setiap petualangan Anda.
                    </p>
                </div>

                <hr class="info-divider">

                <div class="text-center">
                    <h2 class="section-title">Kontak Kami</h2>
                    <p><i class="fas fa-envelope me-2"></i>Email: <a href="mailto:cs@jejakpetualang.com">cs@jejakpetualang.com</a></p>
                    <p><i class="fab fa-whatsapp me-2"></i>WhatsApp: <a href="https://wa.me/6281234567890" target="_blank">+62 851-1721-8640</a></p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>Alamat: Jl. Petualang Raya No. 88, Jakarta, Indonesia</p>
                </div>

                <hr class="info-divider">

                <div class="text-center">
                    <h2 class="section-title">Jam Operasional</h2>
                    <p class="mb-1">Senin - Jumat: 08.00 - 18.00 WIB</p>
                    <p>Sabtu - Minggu: 09.00 - 14.00 WIB</p>
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