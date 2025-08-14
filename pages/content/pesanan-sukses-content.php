<?php
// CATATAN: File ini berisi tampilan untuk halaman konfirmasi pesanan yang berhasil.
// Logika untuk menampilkan diskon sekarang telah diperbaiki.

// 1. Mengambil ID transaksi dari parameter URL (contoh: konfirmasi.php?id=123).
// (int) digunakan untuk memastikan nilainya adalah integer. Jika tidak ada ID, nilainya 0.
$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Jika ID tidak valid (bernilai 0), hentikan eksekusi skrip untuk mencegah error.
if ($transaksi_id === 0) {
    die("Error: ID transaksi tidak valid.");
}

// Menggunakan blok try-catch untuk menangani semua operasi database.
try {
    // 2. Mengambil data transaksi utama.
    // Query ini menggabungkan tabel 'transaksi' (t) dengan 'users' (u) untuk mendapatkan detail pesanan dan nama pelanggan sekaligus.
    $stmt_transaksi = db()->prepare(
        "SELECT t.*, u.nama AS nama_pelanggan, u.email 
         FROM transaksi t 
         JOIN users u ON t.user_id = u.id 
         WHERE t.id = ?"
    );
    $stmt_transaksi->execute([$transaksi_id]);
    // Mengambil satu baris hasil sebagai array asosiatif.
    $transaksi = $stmt_transaksi->fetch();

    // Jika tidak ada transaksi yang ditemukan, hentikan skrip.
    if (!$transaksi) {
        die("Pesanan tidak ditemukan.");
    }

    // 3. Mengambil semua item yang terkait dengan transaksi ini.
    // Query ini menggabungkan tabel 'transaksi_item' (ti) dengan 'produk' (p) untuk mendapatkan detail setiap produk yang dibeli.
    $stmt_items = db()->prepare(
        "SELECT ti.*, p.nama AS nama_produk 
         FROM transaksi_item ti 
         JOIN produk p ON ti.produk_id = p.id 
         WHERE ti.transaksi_id = ?"
    );
    $stmt_items->execute([$transaksi_id]);
    // Mengambil semua item sebagai array.
    $items = $stmt_items->fetchAll();

// Tangkap error jika terjadi masalah pada salah satu query di atas.
} catch (PDOException $e) {
    die("Error mengambil data pesanan: ". $e->getMessage());
}
?>

<style>
    /* Aturan CSS ini hanya berlaku saat halaman dicetak (print). */
    @media print {
        /* Sembunyikan semua elemen di halaman. */
        body * { visibility: hidden; }
        /* Kemudian, tampilkan hanya kotak invoice dan semua elemen di dalamnya. */
        .invoice-box, .invoice-box * { visibility: visible; }
        /* Posisikan kotak invoice agar memenuhi seluruh halaman cetak. */
        .invoice-box { position: absolute; left: 0; top: 0; width: 100%; }
        /* Sembunyikan elemen yang tidak perlu dicetak (seperti tombol). */
        .no-print { display: none; }
    }
    /* Gaya untuk kotak invoice di tampilan web. */
    .invoice-box {
        background-color: #fff;
        color: #333;
        padding: 2rem;
        border-radius: 10px;
        border: 1px solid #ddd;
    }
</style>

<div class="container">
    <div class="text-center py-4 no-print">
        <h1 class="text-success"><i class="fas fa-check-circle"></i></h1>
        <h2 class="text-white mt-3">Pesanan Berhasil Dibuat!</h2>
        <p class="text-white-50">Terima kasih telah berbelanja. Simpan atau cetak bukti pesanan Anda di bawah ini.</p>
    </div>

    <div class="invoice-box mt-4">
        <div class="invoice-header text-center mb-4">
            <h3>Bukti Pesanan</h3>
            <strong>Jejak Petualang</strong>
        </div>
        <div class="row mt-4">
            <div class="col-6">
                <strong>Dipesan oleh:</strong><br>
                <?= htmlspecialchars($transaksi['nama_pelanggan']) ?><br>
                <?= htmlspecialchars($transaksi['email']) ?>
            </div>
            <div class="col-6 text-end">
                <strong>Kode Pesanan:</strong> <?= htmlspecialchars($transaksi['kode_transaksi']) ?><br>
                <strong>Tanggal:</strong> <?= date('d F Y, H:i', strtotime($transaksi['tanggal_transaksi'])) ?>
            </div>
        </div>
        <hr>
        <p><strong>Alamat Pengiriman:</strong><br><?= nl2br(htmlspecialchars($transaksi['alamat_pengiriman'])) ?></p>
        <hr>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // [PERBAIKAN] Inisialisasi variabel subtotal untuk dihitung ulang dari item-item.
                    // Ini memastikan subtotal yang ditampilkan adalah jumlah asli sebelum diskon.
                    $subtotal = 0; 
                    // Looping untuk setiap item dalam pesanan.
                    foreach($items as $item): 
                        // Hitung subtotal untuk item ini.
                        $item_subtotal = $item['harga'] * $item['jumlah'];
                        // Tambahkan subtotal item ini ke total subtotal keseluruhan.
                        $subtotal += $item_subtotal;
                    ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($item['nama_produk']) ?>
                            <?php if($item['ukuran'] != 'N/A'): ?>
                                <small class="d-block text-muted">Ukuran: <?= htmlspecialchars($item['ukuran']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $item['jumlah'] ?></td>
                        <td class="text-end">Rp <?= number_format($item_subtotal, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">Subtotal</th>
                        <th class="text-end">Rp <?= number_format($subtotal, 0, ',', '.') ?></th>
                    </tr>
                    <?php 
                    // [PERBAIKAN] Menghitung nilai diskon yang diterapkan dengan cara mengurangi subtotal dengan total akhir.
                    $diskon_diterapkan = $subtotal - $transaksi['total'];
                    // Tampilkan baris diskon hanya jika nilainya lebih dari 0.
                    // Toleransi 0.01 digunakan untuk menghindari masalah presisi angka desimal (float).
                    if($diskon_diterapkan > 0.01):
                    ?>
                    <tr>
                        <th colspan="2" class="text-end text-success">Diskon</th>
                        <th class="text-end text-success">- Rp <?= number_format($diskon_diterapkan, 0, ',', '.') ?></th>
                    </tr>
                    <?php endif; ?>
                    <tr class="fw-bold border-top">
                        <th colspan="2" class="text-end">Total Akhir</th>
                        <th class="text-end">Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <hr>
        <p class="text-center">Status pesanan saat ini: <strong><?= htmlspecialchars($transaksi['status']) ?></strong>. Terima kasih!</p>
    </div>

    <div class="text-center mt-4 mb-5 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Cetak Bukti</button>
        <a href="/jejakpetualang/pages/index.php" class="btn btn-secondary">Kembali ke Home</a>
    </div>
</div>