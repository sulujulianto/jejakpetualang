<?php
// 1. Memanggil "penjaga gerbang" untuk memastikan pengguna sudah login.
require_once __DIR__ . '/../auth/user-auth.php';

// 2. Memanggil file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';

// --- Keamanan dan Validasi Awal ---

// Mengambil ID pengguna dari session.
$user_id = $_SESSION['user_id'];
// Mengambil ID transaksi dari parameter URL (contoh: ?id=123).
$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi: Jika tidak ada ID transaksi di URL, alihkan kembali ke halaman akun.
if ($transaksi_id <= 0) {
    header("Location: akun.php");
    exit();
}

// --- Pengambilan Data dari Database ---
try {
    // Menyiapkan query untuk mengambil detail transaksi.
    // Keamanan: Kondisi `t.user_id = ?` ditambahkan untuk memastikan pengguna hanya bisa melihat riwayat transaksinya sendiri.
    $stmt_transaksi = db()->prepare(
        "SELECT t.*, u.nama AS nama_pelanggan, u.email 
         FROM transaksi t 
         JOIN users u ON t.user_id = u.id 
         WHERE t.id = ? AND t.user_id = ?"
    );
    $stmt_transaksi->execute([$transaksi_id, $user_id]);
    $transaksi = $stmt_transaksi->fetch();

    // Jika tidak ada transaksi yang ditemukan (atau transaksi bukan milik user ini), alihkan.
    if (!$transaksi) {
        $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Pesanan tidak ditemukan.'];
        header("Location: akun.php");
        exit();
    }

    // Mengambil semua item yang terkait dengan transaksi ini.
    $stmt_items = db()->prepare(
        "SELECT ti.*, p.nama AS nama_produk, p.gambar 
         FROM transaksi_item ti
         JOIN produk p ON ti.produk_id = p.id
         WHERE ti.transaksi_id = ?"
    );
    $stmt_items->execute([$transaksi_id]);
    $items = $stmt_items->fetchAll();

} catch (PDOException $e) {
    // Jika terjadi error, hentikan skrip dan tampilkan pesan.
    die("Terjadi kesalahan saat mengambil data pesanan: " . $e->getMessage());
}

// Menetapkan judul halaman yang akan ditampilkan di tag <title> HTML.
$page_title = 'Detail Pesanan ' . htmlspecialchars($transaksi['kode_transaksi']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Jejak Petualang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/jejak-petualang/public/css/style.css">
</head>
<body>
<div class="main-wrapper d-flex flex-column">
    <?php include __DIR__ . '/../partials/navbar.php'; // Menyertakan navigasi atas. ?>

    <main class="main-content py-5 flex-grow-1">
        <div class="container">
            <div class="card bg-dark text-white">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Detail Pesanan</h2>
                    <a href="/jejak-petualang/pages/akun.php#v-pills-pesanan" class="btn btn-sm btn-outline-light">&larr; Kembali ke Riwayat</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informasi Pesanan:</h5>
                            <p>
                                <strong>Kode Pesanan:</strong> <?= htmlspecialchars($transaksi['kode_transaksi']) ?><br>
                                <strong>Tanggal:</strong> <?= date('d F Y, H:i', strtotime($transaksi['tanggal_transaksi'])) ?> WIB<br>
                                <strong>Status:</strong> <span class="badge bg-success"><?= htmlspecialchars($transaksi['status']) ?></span><br>
                                <strong>Metode Pembayaran:</strong> <?= htmlspecialchars($transaksi['metode_pembayaran']) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Alamat Pengiriman:</h5>
                            <p>
                                <strong>Penerima:</strong> <?= htmlspecialchars($transaksi['nama_pelanggan']) ?><br>
                                <address class="mb-0">
                                    <?= nl2br(htmlspecialchars($transaksi['alamat_pengiriman'])) ?>
                                </address>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <h5>Item yang Dipesan:</h5>
                    <div class="table-responsive">
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th colspan="2">Produk</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                foreach ($items as $item): 
                                    $item_subtotal = $item['harga'] * $item['jumlah'];
                                    $subtotal += $item_subtotal;
                                ?>
                                    <tr>
                                        <td style="width: 70px;">
                                            <img src="/jejak-petualang/uploads/produk/<?= htmlspecialchars($item['gambar']) ?>" width="50" class="rounded">
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($item['nama_produk']) ?><br>
                                            <small class="text-muted">Ukuran: <?= htmlspecialchars($item['ukuran']) ?></small>
                                        </td>
                                        <td class="text-center"><?= $item['jumlah'] ?></td>
                                        <td class="text-end">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                        <td class="text-end">Rp <?= number_format($item_subtotal, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end border-0">Subtotal</td>
                                    <td class="text-end border-0">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                </tr>
                                <?php
                                $diskon_diterapkan = $subtotal - $transaksi['total'];
                                if ($diskon_diterapkan > 0.01):
                                ?>
                                    <tr class="text-success">
                                        <td colspan="4" class="text-end border-0">Diskon</td>
                                        <td class="text-end border-0">- Rp <?= number_format($diskon_diterapkan, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="fw-bold fs-5 border-top">
                                    <td colspan="4" class="text-end">Total Pembayaran</td>
                                    <td class="text-end">Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; // Menyertakan footer. ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>