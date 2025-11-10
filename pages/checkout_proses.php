<?php
// CATATAN: File ini sekarang menggunakan "penjaga gerbang" yang benar dan logika "kunci harga".

// [PERBAIKAN 1] Menggunakan "penjaga gerbang" untuk halaman proses form standar.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

// --- Keamanan dan Validasi Awal ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /jejakpetualang/pages/checkout.php");
    exit();
}

// [CATATAN] Pemeriksaan login manual sudah dihapus karena ditangani oleh user-auth.php.

$user_id = $_SESSION['user_id'];
$db = db();

try {
    // Memulai mode transaksi untuk memastikan semua query berhasil atau gagal bersamaan.
    $db->beginTransaction();

    // [PERBAIKAN 2 & 3] Ambil item dari keranjang dengan NAMA TABEL BENAR dan HARGA TERKUNCI.
    $sql_items = "
        SELECT 
            kp.produk_id, 
            kp.kuantitas, 
            kp.ukuran,
            kp.harga_saat_ditambahkan as harga, -- Mengambil harga yang disimpan
            p.stok,
            p.nama as nama_produk
        FROM keranjang_pengguna kp -- Nama tabel yang benar
        JOIN produk p ON kp.produk_id = p.id
        WHERE kp.user_id = ?
    ";
    $stmt_items = $db->prepare($sql_items);
    $stmt_items->execute([$user_id]);
    $items_in_cart = $stmt_items->fetchAll();

    if (empty($items_in_cart)) {
        throw new Exception("Keranjang belanja Anda kosong.");
    }

    // --- Hitung ulang total harga di server & validasi stok ---
    $total_harga = 0;
    foreach ($items_in_cart as $item) {
        if ($item['kuantitas'] > $item['stok']) {
            throw new Exception("Stok untuk produk '" . htmlspecialchars($item['nama_produk']) . "' tidak mencukupi.");
        }
        // [PERBAIKAN 3] Hitung total harga berdasarkan harga yang terkunci dari keranjang.
        $total_harga += $item['harga'] * $item['kuantitas'];
    }
    
    // --- Ambil data dari form & hitung total akhir dengan diskon ---
    $alamat_pengiriman = trim($_POST['alamat_pengiriman']);
    $metode_pembayaran = trim($_POST['metode_pembayaran']);
    $kode_transaksi = 'INV-' . strtoupper(uniqid());

    $diskon = $_SESSION['promo']['diskon'] ?? 0;
    $total_akhir = $total_harga - $diskon;
    if ($total_akhir < 0) $total_akhir = 0;

    // --- Simpan data transaksi utama ke tabel 'transaksi' ---
    $stmt_transaksi = db()->prepare(
        "INSERT INTO transaksi (user_id, kode_transaksi, total, alamat_pengiriman, metode_pembayaran, status, tanggal_transaksi) 
         VALUES (?, ?, ?, ?, ?, 'Diproses', NOW())"
    );
    $stmt_transaksi->execute([$user_id, $kode_transaksi, $total_akhir, $alamat_pengiriman, $metode_pembayaran]);
    $transaksi_id = $db->lastInsertId();

    // --- Simpan setiap item ke 'transaksi_item' dan kurangi stok produk ---
    $stmt_item = db()->prepare("INSERT INTO transaksi_item (transaksi_id, produk_id, ukuran, jumlah, harga) VALUES (?, ?, ?, ?, ?)");
    $stmt_stok = db()->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

    foreach ($items_in_cart as $item) {
        // [PERBAIKAN 3] Simpan detail item ke 'transaksi_item' dengan harga yang terkunci.
        $stmt_item->execute([$transaksi_id, $item['produk_id'], $item['ukuran'], $item['kuantitas'], $item['harga']]);
        $stmt_stok->execute([$item['kuantitas'], $item['produk_id']]);
    }
    
    // --- Jika voucher digunakan, kurangi kuotanya ---
    if (isset($_SESSION['promo']['kode'])) {
        $stmt_voucher = db()->prepare("UPDATE vouchers SET kuota = kuota - 1 WHERE kode_voucher = ?");
        $stmt_voucher->execute([$_SESSION['promo']['kode']]);
    }

    // [PERBAIKAN 2] HAPUS KERANJARI DARI TABEL `keranjang_pengguna`
    $stmt_clear_cart = db()->prepare("DELETE FROM keranjang_pengguna WHERE user_id = ?");
    $stmt_clear_cart->execute([$user_id]);

    // --- Commit Transaksi & Bersihkan Session ---
    db()->commit();

    unset($_SESSION['promo']);
    
    // Alihkan ke halaman konfirmasi pesanan berhasil.
    header("Location: pesanan_sukses.php?id=" . $transaksi_id);
    exit();

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['pesan_error'] = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
    header("Location: checkout.php");
    exit();
}
?>