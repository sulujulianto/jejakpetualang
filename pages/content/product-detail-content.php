<?php
// File: jejak-petualang/pages/content/product-detail-content.php
// Catatan: Kode ini sudah diperbaiki agar form mengirim data ke file yang benar dengan nama input yang benar.

$produk = null;
$produk_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($produk_id > 0) {
    try {
        // Mengambil data produk utama.
        $stmt_produk = db()->prepare("SELECT * FROM produk WHERE id = ?");
        $stmt_produk->execute([$produk_id]);
        $produk = $stmt_produk->fetch(PDO::FETCH_ASSOC);

        // Mengambil semua ulasan untuk produk ini.
        $ulasan_stmt = db()->prepare(
            "SELECT ulasan.*, users.nama AS nama_pengguna
             FROM ulasan
             JOIN users ON ulasan.user_id = users.id
             WHERE ulasan.produk_id = ?
             ORDER BY ulasan.created_at DESC"
        );
        $ulasan_stmt->execute([$produk_id]);
        $ulasan_list = $ulasan_stmt->fetchAll();

    } catch (PDOException $e) {
        die("<h1>Terjadi Error Database:</h1><pre>" . $e->getMessage() . "</pre>");
    }
}
?>

<div class="container py-5">
    
    <?php if ($produk): ?>
        <div class="product-detail-card">
            <a href="/jejak-petualang/pages/product.php" class="back-to-products-btn" title="Kembali"><i class="fas fa-arrow-left"></i></a>
            
            <?php if(isset($_SESSION['pesan'])): ?>
                <div class="alert alert-<?= $_SESSION['pesan']['jenis'] == 'error' ? 'danger' : 'success' ?> mb-4">
                    <?= htmlspecialchars($_SESSION['pesan']['isi']) ?>
                </div>
                <?php unset($_SESSION['pesan']); ?>
            <?php endif; ?>

            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="product-image-container">
                        <img src="/jejak-petualang/uploads/produk/<?= htmlspecialchars($produk['gambar']) ?>" class="img-fluid" alt="<?= htmlspecialchars($produk['nama']) ?>">
                    </div>
                </div>

                <div class="col-lg-6">
                    <h1 class="fw-bold mb-3"><?= htmlspecialchars($produk['nama']) ?></h1>
                    
                    <p class="fs-3 fw-bold text-primary mb-3">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
                    
                    <div class="d-flex justify-content-start gap-4 mb-3">
                        <p class="product-info-text">
                            <strong>Kategori:</strong> 
                            <?php
                                $kategori_stmt = db()->prepare("SELECT nama_kategori FROM kategori WHERE id = ?");
                                $kategori_stmt->execute([$produk['kategori_id']]);
                                echo htmlspecialchars($kategori_stmt->fetchColumn() ?? 'N/A');
                            ?>
                        </p>
                        <p class="product-info-text">
                            <strong>Stok:</strong> <?= $produk['stok'] > 0 ? htmlspecialchars($produk['stok']) : 'Habis' ?>
                        </p>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.2);">
                    <label class="form-label fw-bold">Deskripsi:</label>
                    
                    <div class="product-description" id="description-box">
                        <?= html_entity_decode($produk['deskripsi']); ?>
                    </div>
                    <a href="#" class="read-more-btn" id="read-more-trigger">Baca Selengkapnya</a>
                    
                    <form id="add-to-cart-form" action="/jejak-petualang/pages/tambah_keranjang.php" method="POST" class="mt-4">
                        <input type="hidden" name="id_produk" value="<?= $produk['id'] ?>">
                        
                        <?php if (!empty($produk['ukuran'])): ?>
                            <div class="size-selector">
                                <label class="form-label">Pilih Ukuran:</label>
                                <div class="size-options">
                                    <?php 
                                    $ukuran_array = explode(',', $produk['ukuran']);
                                    foreach ($ukuran_array as $index => $ukuran): 
                                    ?>
                                        <div class="size-option">
                                            <input type="radio" name="ukuran" value="<?= trim($ukuran) ?>" id="ukuran_<?= $index ?>" <?= ($produk['stok'] <= 0) ? 'disabled' : '' ?> required>
                                            <label for="ukuran_<?= $index ?>"><?= trim($ukuran) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                            <div class="mt-4">
                                <label class="form-label fw-bold">Jumlah:</label>
                                <div class="input-group quantity-wrapper" style="max-width: 150px;">
                                    <button class="btn btn-outline-secondary" type="button">-</button>
                                    <input 
                                        type="number" 
                                        name="jumlah" 
                                        class="form-control text-center quantity-input" 
                                        value="1" 
                                        min="1" 
                                        step="1" 
                                        max="<?= $produk['stok'] ?>" 
                                        required
                                    >
                                    <button class="btn btn-outline-secondary" type="button">+</button>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button type="submit" class="btn btn-primary btn-lg" <?= ($produk['stok'] <= 0) ? 'disabled' : '' ?>>
                                    <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                                </button>
                            <?php else: ?>
                                <a href="/jejak-petualang/auth/login.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                                </a>
                            <?php endif; ?>
                            </div>
                        </form>
                </div>
            </div>

            <hr class="my-5" style="border-color: rgba(255,255,255,0.2);">

            <div class="product-review-section">
                <h3 class="mb-4">Ulasan Produk</h3>
                </div>
            </div>
    <?php else: ?>
        <div class="text-center py-5">
            <h2 class="display-4">Produk Tidak Ditemukan</h2>
            <p class="lead text-muted">Maaf, produk dengan ID (<?= htmlspecialchars($produk_id) ?>) tidak ada dalam database.</p>
            <a href="/jejak-petualang/pages/product.php" class="btn btn-secondary mt-3">Kembali ke Daftar Produk</a>
        </div>
    <?php endif; ?>
</div>