<?php
// CATATAN: Ini adalah file "view" untuk halaman Checkout.
// File ini dipanggil oleh checkout.php

// 1. Memanggil helper CSRF
// Kita panggil di sini agar fungsi csrf_field() tersedia.
require_once __DIR__ . '/../../helpers/csrf.php';

// (Variabel $items, $subtotal, $alamat_default, $telepon_default 
//  sudah disiapkan oleh 'controller' checkout.php)
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="fw-bold">Checkout</h1>
            <p class="text-muted">Selesaikan pesanan Anda dalam beberapa langkah mudah.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['pesan'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['pesan']['jenis']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['pesan']['isi']); unset($_SESSION['pesan']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['pesan_voucher'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['pesan_voucher']); unset($_SESSION['pesan_voucher']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="alert alert-info text-center">
            Keranjang Anda kosong. Silakan <a href="<?= BASE_URL ?>/pages/product.php">belanja</a> terlebih dahulu.
        </div>
    <?php else: ?>
        <div class="row g-5">
            <div class="col-lg-7">
                <h4 class="mb-3">Alamat Pengiriman</h4>
                
                <form action="<?= BASE_URL ?>/pages/proses_pesanan.php" method="POST" id="checkoutForm">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="alamat_pengiriman" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat_pengiriman" name="alamat_pengiriman" rows="4" required><?= htmlspecialchars($alamat_default ?? '') ?></textarea>
                            <div class="form-text">
                                Pastikan alamat sudah benar, termasuk nama jalan, nomor rumah, RT/RW, dan kode pos (jika ada).
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="telepon" class="form-label">Nomor Telepon (Opsional)</label>
                            <input type="tel" class="form-control" id="telepon" name="telepon" value="<?= htmlspecialchars($telepon_default ?? '') ?>" placeholder="Contoh: 08123456789">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h4 class="mb-3">Metode Pembayaran</h4>
                    <div class="my-3">
                        <div class="form-check">
                            <input id="bank_transfer" name="metode_pembayaran" type="radio" class="form-check-input" value="Bank Transfer" checked required>
                            <label class="form-check-label" for="bank_transfer">Bank Transfer (BCA/Mandiri)</label>
                        </div>
                        <div class="form-check">
                            <input id="ewallet" name="metode_pembayaran" type="radio" class="form-check-input" value="E-Wallet" required>
                            <label class="form-check-label" for="ewallet">E-Wallet (GoPay/OVO)</label>
                        </div>
                    </div>

                    <hr class="my-4">

                    </form>
            </div>

            <div class="col-lg-5">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-primary">Ringkasan Belanja</span>
                    <span class="badge bg-primary rounded-pill"><?= count($items) ?></span>
                </h4>
                <ul class="list-group mb-3">
                    <?php foreach ($items as $item): ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0"><?= htmlspecialchars($item['nama']) ?></h6>
                                <small class="text-muted">Jumlah: <?= $item['jumlah'] ?></small>
                            </div>
                            <span class="text-muted">Rp <?= number_format($item['harga'] * $item['jumlah']) ?></span>
                        </li>
                    <?php endforeach; ?>
                    
                    <li class="list-group-item d-flex justify-content-between bg-light">
                        <span class="fw-bold">Subtotal</span>
                        <strong>Rp <?= number_format($subtotal) ?></strong>
                    </li>
                </ul>

                <form action="<?= BASE_URL ?>/pages/checkout.php" method="GET" class="card p-2 mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="kode_voucher" placeholder="Kode Voucher" value="<?= htmlspecialchars($_GET['kode_voucher'] ?? '') ?>">
                        <button type="submit" class="btn btn-secondary">Gunakan</button>
                    </div>
                </form>

                <button type="submit" class="btn btn-primary btn-lg w-100" form="checkoutForm">
                    Buat Pesanan (Total: Rp <?= number_format($total_akhir) ?>)
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>