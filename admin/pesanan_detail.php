<?php
// 1. Memanggil file konfigurasi
require_once __DIR__ . '/../../config/koneksi.php';

// 2. Memanggil file header (termasuk auth.php)
$page_title = 'Detail Pesanan';
include __DIR__ . '/../partials/header.php';

$pesanan_id = $_GET['id'] ?? null;

if (!$pesanan_id || !filter_var($pesanan_id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID Pesanan tidak valid.'];
    header('Location: ' . BASE_URL . '/admin/pesanan_index.php');
    exit();
}

try {
    // 3. Ambil data pesanan utama
    // (Sudah AMAN dari SQL Injection)
    $sql_pesanan = "SELECT p.*, u.nama as nama_user, u.email as email_user, u.telepon as telepon_user
                    FROM pesanan p
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.id = ?";
    $stmt_pesanan = db()->prepare($sql_pesanan);
    $stmt_pesanan->execute([$pesanan_id]);
    $pesanan = $stmt_pesanan->fetch();

    if (!$pesanan) {
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Pesanan tidak ditemukan.'];
        header('Location: ' . BASE_URL . '/admin/pesanan_index.php');
        exit();
    }
    
    // 4. Ambil item detail pesanan
    // (Sudah AMAN dari SQL Injection)
    $stmt_detail = db()->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id = ?");
    $stmt_detail->execute([$pesanan_id]);
    $detail_items = $stmt_detail->fetchAll();

} catch (PDOException $e) {
    // error_log($e->getMessage());
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Gagal mengambil data pesanan.'];
    header('Location: ' . BASE_URL . '/admin/pesanan_index.php');
    exit();
}

// Daftar status untuk dropdown update
$status_options = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            
            <a href="<?= BASE_URL ?>/admin/pesanan_index.php" class="btn btn-secondary mb-3">&larr; Kembali ke Daftar Pesanan</a>

            <h1 class="mb-4">Detail Pesanan #<?= $pesanan['id'] ?></h1>
            
            <div class="row">
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header">
                            Informasi Pelanggan
                        </div>
                        <div class="card-body">
                            <p><strong>Nama:</strong><br>
                                <?= htmlspecialchars($pesanan['nama_user'] ?? 'User Dihapus') ?>
                            </p>
                            <p><strong>Email:</strong><br>
                                <?= htmlspecialchars($pesanan['email_user'] ?? 'N/A') ?>
                            </p>
                            <p><strong>Telepon:</strong><br>
                                <?= htmlspecialchars($pesanan['telepon_user'] ?? 'N/A') ?>
                            </p>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            Alamat Pengiriman
                        </div>
                        <div class="card-body">
                            <address>
                                <?= nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])) ?>
                            </address>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header">
                            Ringkasan Pesanan
                        </div>
                        <div class="card-body">
                            <p><strong>Status:</strong><br>
                                <span class="fw-bold"><?= htmlspecialchars($pesanan['status_pesanan']) ?></span>
                            </p>
                            <p><strong>Metode Pembayaran:</strong><br>
                                <?= htmlspecialchars($pesanan['metode_pembayaran']) ?>
                            </p>
                            <hr>
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

                    <div class="card mb-4">
                        <div class="card-header">
                            Update Status Pesanan
                        </div>
                        <div class="card-body">
                            <form action="<?= BASE_URL ?>/admin/pesanan_update_status.php" method="POST">
                                <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Ubah Status</label>
                                    <select id="status" name="status" class="form-select">
                                        <?php foreach ($status_options as $status): ?>
                                            <option value="<?= htmlspecialchars($status) ?>" <?= ($pesanan['status_pesanan'] == $status) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($status) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="mb-3">Item yang Dipesan</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Harga Saat Beli</th>
                            <th>Jumlah</th>
                            <th>Subtotal Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detail_items as $item): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($item['nama_produk']) ?>
                                    <br>
                                    <small class="text-muted">ID Produk: <?= (int)$item['produk_id'] ?></small>
                                </td>
                                <td>Rp <?= number_format($item['harga_saat_beli']) ?></td>
                                <td><?= (int)$item['jumlah'] ?></td>
                                <td>Rp <?= number_format($item['harga_saat_beli'] * $item['jumlah']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</main>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>