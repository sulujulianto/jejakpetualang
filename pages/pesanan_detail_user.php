<?php
// CATATAN: Ini adalah "controller" dan "view" untuk halaman Detail Pesanan (sisi User).

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../auth/user-auth.php'; // Pastikan user login

// 2. Memanggil helper CSRF (BARU)
// Diperlukan untuk form ulasan.
require_once __DIR__ . '/../helpers/csrf.php';

$pesanan_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$pesanan_id || !filter_var($pesanan_id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID Pesanan tidak valid.'];
    header('Location: ' . BASE_URL . '/pages/akun.php');
    exit();
}

try {
    // 3. Ambil data pesanan (Pastikan pesanan ini milik user yang login)
    // (Sudah AMAN dari SQL Injection)
    $stmt_pesanan = db()->prepare("SELECT * FROM pesanan WHERE id = ? AND user_id = ?");
    $stmt_pesanan->execute([$pesanan_id, $user_id]);
    $pesanan = $stmt_pesanan->fetch();

    if (!$pesanan) {
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Pesanan tidak ditemukan atau bukan milik Anda.'];
        header('Location: ' . BASE_URL . '/pages/akun.php');
        exit();
    }
    
    // 4. Ambil item detail pesanan
    // (Sudah AMAN dari SQL Injection)
    $stmt_detail = db()->prepare(
        "SELECT dp.*, p.gambar 
         FROM detail_pesanan dp
         LEFT JOIN produk p ON dp.produk_id = p.id
         WHERE dp.pesanan_id = ? AND dp.user_id = ?"
    );
    $stmt_detail->execute([$pesanan_id, $user_id]);
    $detail_items = $stmt_detail->fetchAll();

} catch (PDOException $e) {
    // error_log($e->getMessage());
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Gagal mengambil data pesanan.'];
    header('Location: ' . BASE_URL . '/pages/akun.php');
    exit();
}

// 5. Menyiapkan variabel untuk layout
$title = 'Detail Pesanan #' . $pesanan['id'];
// (Kita tidak memanggil $page, karena file ini akan dipanggil oleh layout/app.php)
// (Ini adalah asumsi berdasarkan file lain, jika ini salah, struktur di bawah tetap aman)

// --- Mulai "pura-pura" jadi file content ---
// Ini untuk file yang tidak menggunakan controller terpisah
ob_start(); 
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="fw-bold">Detail Pesanan</h1>
            <p class="text-muted">ID Pesanan: #<?= $pesanan['id'] ?></p>
            <a href="<?= BASE_URL ?>/pages/akun.php" class="btn btn-sm btn-outline-secondary">&larr; Kembali ke Akun Saya</a>
        </div>
    </div>

    <?php if (isset($_SESSION['pesan'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['pesan']['jenis']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['pesan']['isi']); unset($_SESSION['pesan']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header">
                    Ringkasan Pesanan
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong><br>
                        <span class="fw-bold"><?= htmlspecialchars($pesanan['status_pesanan']) ?></span>
                    </p>
                    <p><strong>Tanggal Pesan:</strong><br>
                        <?= date('d M Y, H:i', strtotime($pesanan['tgl_pesanan'])) ?>
                    </p>
                    <p><strong>Metode Pembayaran:</strong><br>
                        <?= htmlspecialchars($pesanan['metode_pembayaran']) ?>
                    </p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    Alamat Pengiriman
                </div>
                <div class="card-body">
                    <address><?= nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])) ?></address>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Rincian Pembayaran
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>Rp <?= number_format($pesanan['subtotal']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Diskon (Voucher):</span>
                        <span>- Rp <?= number_format($pesanan['diskon']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Biaya Kirim:</span>
                        <span>Rp <?= number_format($pesanan['biaya_kirim']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
                        <span>Total Bayar:</span>
                        <span>Rp <?= number_format($pesanan['total']) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Item yang Dipesan</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($detail_items as $item): ?>
                        <div class="d-flex mb-3 border-bottom pb-3">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['gambar'] ?? 'public/images/placeholder.jpg') ?>" 
                                 alt="<?= htmlspecialchars($item['nama_produk']) ?>" 
                                 class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['nama_produk']) ?></h6>
                                <small class="text-muted">
                                    <?= $item['jumlah'] ?> x Rp <?= number_format($item['harga_saat_beli']) ?>
                                </small>
                                <strong class="d-block">Subtotal: Rp <?= number_format($item['harga_saat_beli'] * $item['jumlah']) ?></strong>
                            </div>
                        </div>

                        <?php if ($pesanan['status_pesanan'] == 'Selesai' && !$item['sudah_diulas']): ?>
                            <div class="mt-2 mb-3 p-3 bg-light rounded">
                                <h6 class="mb-3">Beri ulasan untuk produk ini:</h6>
                                <form action="<?= BASE_URL ?>/pages/proses_ulasan.php" method="POST">
                                    
                                    <?= csrf_field() ?>
                                    
                                    <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
                                    <input type="hidden" name="produk_id" value="<?= $item['produk_id'] ?>">
                                    <input type="hidden" name="detail_pesanan_id" value="<?= $item['id'] ?>">
                                    
                                    <div class="mb-2">
                                        <label class="form-label">Rating:</label>
                                        <div class="rating-stars">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                <input type="radio" name="rating" id="rating-<?= $item['id'] ?>-<?= $i ?>" value="<?= $i ?>" required>
                                                <label for="rating-<?= $item['id'] ?>-<?= $i ?>"><i class="bi bi-star-fill"></i></label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="komentar-<?= $item['id'] ?>" class="form-label">Komentar (Opsional):</label>
                                        <textarea name="komentar" id="komentar-<?= $item['id'] ?>" class="form-control" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">Kirim Ulasan</button>
                                </form>
                            </div>
                        <?php elseif ($item['sudah_diulas']): ?>
                             <div class="alert alert-success py-2">
                                <i class="bi bi-check-circle-fill"></i> Anda sudah memberi ulasan untuk produk ini.
                             </div>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// --- Akhir "pura-pura" jadi file content ---
$page = ob_get_clean();
require_ab_clean();
require_once __DIR__ . '/../layout/app.php';
?>