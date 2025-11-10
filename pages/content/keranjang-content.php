<?php
// CATATAN: File ini sekarang menggunakan harga yang "dikunci" dari `harga_saat_ditambahkan`.

// --- Inisialisasi Variabel ---
$produk_di_keranjang = [];
$total_harga = 0;
$user_id = $_SESSION['user_id'];

// --- Pengambilan Data Keranjang dari Database ---
try {
    // [PERBAIKAN UTAMA] Query diubah untuk mengambil `harga_saat_ditambahkan` dari tabel keranjang.
    // Nama kolom `p.harga` diabaikan, dan kita menggunakan `kp.harga_saat_ditambahkan` dengan alias `harga`.
    $sql = "
        SELECT 
            kp.id as item_keranjang_id,
            p.id as produk_id,
            p.nama,
            kp.harga_saat_ditambahkan as harga,  -- Menggunakan harga yang disimpan di keranjang
            p.gambar,
            p.ukuran as semua_ukuran_produk,
            kp.kuantitas,
            kp.ukuran as ukuran_terpilih
        FROM keranjang_pengguna kp
        JOIN produk p ON kp.produk_id = p.id
        WHERE kp.user_id = ?
        ORDER BY kp.ditambahkan_pada DESC
    ";
    $stmt = db()->prepare($sql);
    $stmt->execute([$user_id]);
    $produk_di_keranjang = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Kalkulasi Harga ---
    // Looping melalui setiap item untuk menghitung total harga berdasarkan harga yang sudah terkunci.
    foreach($produk_di_keranjang as $item) {
        $total_harga += $item['harga'] * $item['kuantitas'];
    }
    
    $diskon = $_SESSION['promo']['diskon'] ?? 0;
    $harga_akhir = $total_harga - $diskon;
    if ($harga_akhir < 0) $harga_akhir = 0;

} catch (PDOException $e) {
    die("Gagal memuat data keranjang Anda: " . $e->getMessage());
}
?>

<div class="container py-5">
    <div class="cart-container">
        <h1 class="text-center mb-4">Keranjang Belanja</h1>
        <?php if(isset($_SESSION['pesan'])): ?>
            <div class="alert alert-<?= $_SESSION['pesan']['jenis'] == 'error' ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($_SESSION['pesan']['isi']) ?>
                <?php unset($_SESSION['pesan']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($produk_di_keranjang)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <p class="lead">Keranjang Anda masih kosong.</p>
                <a href="/jejakpetualang/pages/product.php" class="btn btn-primary">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <form action="/jejakpetualang/pages/keranjang_action.php?action=update" method="POST">
                <div class="table-responsive">
                    <table class="table cart-table">
                        <thead>
                            <tr>
                                <th scope="col" colspan="2">Produk</th>
                                <th scope="col">Ukuran</th>
                                <th scope="col" class="text-center">Kuantitas</th>
                                <th scope="col" class="text-end">Subtotal</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produk_di_keranjang as $item): ?>
                                <tr>
                                    <td style="width: 100px;"><a href="/jejakpetualang/pages/product_detail.php?id=<?= $item['produk_id'] ?>"><img src="/jejakpetualang/uploads/produk/<?= htmlspecialchars($item['gambar']) ?>" width="80" class="rounded"></a></td>
                                    <td>
                                        <a href="/jejakpetualang/pages/product_detail.php?id=<?= $item['produk_id'] ?>" class="text-white text-decoration-none"><?= htmlspecialchars($item['nama']) ?></a><br>
                                        <small class="text-muted">Rp <?= number_format($item['harga'], 0, ',', '.') ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['semua_ukuran_produk'])): ?>
                                            <select name="items[<?= $item['item_keranjang_id'] ?>][ukuran]" class="form-select form-select-sm" style="width: 80px;">
                                                <?php foreach(explode(',', $item['semua_ukuran_produk']) as $u): $u = trim($u); ?>
                                                    <option value="<?= $u ?>" <?= ($u == $item['ukuran_terpilih']) ? 'selected' : '' ?>><?= $u ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <span>N/A</span>
                                            <input type="hidden" name="items[<?= $item['item_keranjang_id'] ?>][ukuran]" value="N/A">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="input-group quantity-wrapper" style="width: 120px; margin: 0 auto;">
                                            <input type="number" name="items[<?= $item['item_keranjang_id'] ?>][kuantitas]" class="form-control form-control-sm text-center" value="<?= $item['kuantitas'] ?>" min="1">
                                        </div>
                                    </td>
                                    <td class="text-end">Rp <?= number_format($item['harga'] * $item['kuantitas'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <a href="/jejakpetualang/pages/keranjang_action.php?action=remove&item_id=<?= $item['item_keranjang_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menghapus item ini?')">&times;</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-info"><i class="fas fa-sync-alt"></i> Perbarui Keranjang</button>
                </div>
            </form>

            <hr class="my-4">

            <div class="row justify-content-end">
                <div class="col-md-7">
                    <form action="/jejakpetualang/pages/keranjang_action.php?action=apply_promo" method="POST">
                        <label for="kode_promo" class="form-label">Punya Kode Promo?</label>
                        <div class="input-group">
                            <input type="text" name="kode_promo" id="kode_promo" class="form-control" placeholder="Masukkan kode..." value="<?= htmlspecialchars($_SESSION['promo']['kode'] ?? '') ?>">
                            <button class="btn btn-outline-secondary" type="submit">Gunakan</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-5">
                    <dl class="row text-end">
                        <dt class="col-7">Subtotal</dt>
                        <dd class="col-5">Rp <?= number_format($total_harga, 0, ',', '.') ?></dd>
                        <?php if ($diskon > 0): ?>
                            <dt class="col-7 text-success">Diskon (<?= htmlspecialchars($_SESSION['promo']['kode']) ?>)</dt>
                            <dd class="col-5 text-success">- Rp <?= number_format($diskon, 0, ',', '.') ?></dd>
                        <?php endif; ?>
                        <hr class="my-2">
                        <dt class="col-7 fs-5">Total Akhir</dt>
                        <dd class="col-5 fs-5">Rp <?= number_format($harga_akhir, 0, ',', '.') ?></dd>
                    </dl>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="/jejakpetualang/pages/product.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Lanjut Belanja</a>
                <a href="/jejakpetualang/pages/checkout.php" class="btn btn-primary btn-lg">Lanjut ke Checkout <i class="fas fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>
    </div>
</div>