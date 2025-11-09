<?php
// CATATAN: Ini adalah file "view" untuk Halaman Detail Produk.
// PERBAIKAN XSS: Semua output dari variabel $produk dan $ulasan
//                wajib dibungkus dengan htmlspecialchars().
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="product-gallery">
                <div class="product-gallery-main">
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($produk['gambar']) ?>" 
                         alt="<?= htmlspecialchars($produk['nama']) ?>" 
                         class="img-fluid w-100 rounded shadow">
                </div>
                </div>
        </div>

        <div class="col-lg-6">
            <h1 class="display-6 fw-bold mb-3"><?= htmlspecialchars($produk['nama']) ?></h1>
            
            <p class="fs-3 fw-bold text-primary mb-3">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
            
            <p class="text-muted">
                <?php if ($produk['stok'] > 0): ?>
                    <span class="badge bg-success">Stok Tersedia: <?= $produk['stok'] ?></span>
                <?php else: ?>
                    <span class="badge bg-danger">Stok Habis</span>
                <?php endif; ?>
            </p>

            <div class="product-description mb-4">
                <h5 class="fw-bold">Deskripsi Produk</h5>
                <p class_text-muted"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
            </div>

            <form action="<?= BASE_URL ?>/pages/tambah_keranjang.php" method="POST">
                <input type="hidden" name="produk_id" value="<?= $produk['id'] ?>">
                
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-auto">
                        <label for="jumlah" class="col-form-label">Jumlah:</label>
                    </div>
                    <div class="col-auto" style="width: 100px;">
                        <input type="number" id="jumlah" name="jumlah" class="form-control text-center" value="1" min="1" max="<?= $produk['stok'] ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100" <?= ($produk['stok'] <= 0) ? 'disabled' : '' ?>>
                    <i class="bi bi-cart-plus-fill me-2"></i>
                    <?= ($produk['stok'] > 0) ? 'Tambah ke Keranjang' : 'Stok Habis' ?>
                </button>
            </form>
        </div>
    </div>

    <hr class="my-5">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Ulasan Produk (<?= count($ulasan) ?>)</h3>
            
            <?php if (empty($ulasan)): ?>
                <p class="text-center text-muted">Belum ada ulasan untuk produk ini.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($ulasan as $item): ?>
                        <div class="list-group-item mb-3 p-3 border rounded">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1 fw-bold"><?= htmlspecialchars($item['nama']) ?></h5>
                                <small class="text-muted"><?= date('d M Y', strtotime($item['created_at'])) ?></small>
                            </div>
                            <div class_rating mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= ($i <= $item['rating']) ? 'bi-star-fill text-warning' : 'bi-star text-muted' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($item['komentar'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_id']) && !$user_sudah_ulas): ?>
                <div class="alert alert-info mt-4">
                    Anda hanya bisa memberi ulasan untuk produk yang sudah Anda beli.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>