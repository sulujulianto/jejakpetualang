<?php
// CATATAN: File ini berisi konten untuk halaman utama (index.php).

// --- Pengambilan Data dari Database ---
// Menggunakan blok try-catch untuk menangani potensi error saat query.
try {
    // Mengambil 8 produk terbaru yang stoknya tersedia.
    // ORDER BY id DESC: Mengurutkan dari ID terbesar (paling baru).
    // LIMIT 8: Membatasi hasil hanya 8 produk.
    $produk_terbaru = db()->query("SELECT * FROM produk WHERE ketersediaan_stok = 'tersedia' ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    
    // Mengambil semua kategori produk untuk ditampilkan sebagai tombol filter.
    $kategori_list = db()->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll(PDO::FETCH_ASSOC);
// Jika terjadi error, inisialisasi variabel sebagai array kosong agar halaman tidak rusak.
} catch (PDOException $e) {
    $produk_terbaru = [];
    $kategori_list = [];
}
// --- Logika untuk Carousel ---
// `array_chunk` digunakan untuk memecah array `$produk_terbaru` menjadi beberapa bagian (chunks).
// Dalam kasus ini, setiap bagian berisi 4 produk. Ini akan menjadi satu slide di carousel.
$produk_slides = !empty($produk_terbaru) ? array_chunk($produk_terbaru, 4) : [];
?>

<section class="container text-center hero-section">
    <h1 class="display-4 fw-bold">Selamat Datang di Jejak Petualang</h1>
    <p class="lead">Temukan perlengkapan jejak-petualang terbaik untuk petualangan Anda.</p>
    <a href="/jejak-petualang/pages/product.php" class="btn btn-primary btn-lg mt-3">Lihat Semua Produk</a>
</section>

<section class="container text-center py-4">
    <h2 class="section-title">Kategori Produk</h2>
    <div class="d-flex justify-content-center flex-wrap gap-2">
        <?php 
        // Melakukan perulangan (looping) untuk setiap kategori yang didapat dari database.
        foreach ($kategori_list as $kategori): 
        ?>
            <a href="/jejak-petualang/pages/product.php?kategori=<?= $kategori['id'] ?>" class="category-btn"><?= htmlspecialchars($kategori['nama_kategori']) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<section class="container text-center py-4">
    <h2 class="section-title">Promo Spesial</h2>
    <div>
        <a href="/jejak-petualang/pages/promo.php" class="promo-btn">Voucher Diskon Rp 50.000</a>
        <a href="/jejak-petualang/pages/promo.php" class="promo-btn">Voucher Diskon Rp 100.000</a>
        <a href="/jejak-petualang/pages/promo.php" class="promo-btn">Voucher Diskon Rp 300.000</a>
    </div>
</section>
    
<section class="py-5">
    <div class="container position-relative">
        <h2 class="section-title text-center">Produk Terbaru</h2>
        <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php 
                // Memeriksa apakah ada produk untuk ditampilkan di carousel.
                if (empty($produk_slides)): 
                ?>
                    <div class="carousel-item active text-center"><p class="text-white-50">Belum ada produk terbaru.</p></div>
                <?php else: ?>
                    <?php 
                    // Perulangan pertama: untuk setiap slide (setiap grup berisi 4 produk).
                    foreach ($produk_slides as $index => $slide_produk): 
                    ?>
                        <div class="carousel-item <?= ($index == 0) ? 'active' : '' ?>">
                            <div class="row g-4 justify-content-center">
                                <?php 
                                // Perulangan kedua: untuk setiap produk di dalam slide saat ini.
                                foreach ($slide_produk as $produk): 
                                ?>
                                    <div class="col-lg-3 col-md-6 d-flex align-items-stretch">
                                        <div class="product-card h-100">
                                            <a href="/jejak-petualang/pages/product_detail.php?id=<?= $produk['id'] ?>" class="product-card-link">
                                                <div class="product-card-img-container">
                                                    <img src="/jejak-petualang/uploads/produk/<?= htmlspecialchars($produk['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produk['nama']) ?>">
                                                </div>
                                                <div class="card-body text-center d-flex flex-column">
                                                    <h5 class="card-title flex-grow-1"><?= htmlspecialchars($produk['nama']) ?></h5>
                                                    <p class="card-text card-price fw-bold fs-5 mt-3">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; // Akhir perulangan produk ?>
                            </div>
                        </div>
                    <?php endforeach; // Akhir perulangan slide ?>
                <?php endif; // Akhir kondisi if-else ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                <span class="carousel-arrow-container"><i class="fas fa-chevron-left"></i></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                <span class="carousel-arrow-container"><i class="fas fa-chevron-right"></i></span>
            </button>
        </div>
    </div>
</section>