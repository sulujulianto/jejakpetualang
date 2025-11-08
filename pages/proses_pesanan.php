<?php
// CATATAN: File ini menangani finalisasi checkout dengan validasi stok dan voucher yang ketat.

// Menggunakan "penjaga gerbang" untuk halaman proses form standar.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers/csrf.php';

// --- Keamanan dan Validasi Awal ---

// Keamanan: Memastikan permintaan datang dari form checkout.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /jejakpetualang/pages/checkout.php');
    exit();
}

require_valid_csrf_token();

$user_id = $_SESSION['user_id'];
$db = db();

try {
    // Memulai transaksi database agar seluruh proses konsisten.
    $db->beginTransaction();

    // Ambil item keranjang lengkap dengan stok produk saat ini dan kunci baris terkait.
    $sql_items = "
        SELECT 
            kp.produk_id, 
            kp.kuantitas, 
            kp.ukuran, 
            kp.harga_saat_ditambahkan AS harga,
            p.stok,
            p.nama
        FROM keranjang_pengguna kp
        JOIN produk p ON kp.produk_id = p.id
        WHERE kp.user_id = ?
        FOR UPDATE
    ";
    $stmt_items = $db->prepare($sql_items);
    $stmt_items->execute([$user_id]);
    $items_in_cart = $stmt_items->fetchAll();

    if (empty($items_in_cart)) {
        throw new Exception('Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.');
    }
    
    // Menghitung total harga berdasarkan harga yang terkunci serta memvalidasi stok.
    $total_harga = 0;
    foreach ($items_in_cart as $item) {
        $stok_tersedia = max(0, (int)$item['stok']);
        if ($stok_tersedia < (int)$item['kuantitas']) {
            throw new Exception("Stok untuk {$item['nama']} tidak mencukupi. Silakan perbarui keranjang Anda.");
        }
        $total_harga += $item['harga'] * $item['kuantitas'];
    }

    if ($total_harga <= 0) {
        throw new Exception('Total belanja tidak valid. Mohon ulangi proses checkout.');
    }
    
    // Validasi ulang kode promo (jika ada) agar tidak menggunakan nilai yang sudah kedaluwarsa.
    $diskon = 0;
    $promo_kode = null;
    if (!empty($_SESSION['promo']['kode'])) {
        $stmt_voucher = $db->prepare("
            SELECT * FROM vouchers 
            WHERE kode_voucher = ? 
              AND status = 'aktif' 
              AND kuota > 0 
              AND NOW() BETWEEN tanggal_mulai AND tanggal_berakhir
            FOR UPDATE
        ");
        $stmt_voucher->execute([$_SESSION['promo']['kode']]);
        $voucher = $stmt_voucher->fetch();

        if (!$voucher) {
            unset($_SESSION['promo']);
            throw new Exception('Kode promo yang Anda gunakan sudah tidak berlaku. Silakan pilih kode lain.');
        }

        if ($total_harga < $voucher['minimal_pembelian']) {
            unset($_SESSION['promo']);
            throw new Exception('Total belanja tidak memenuhi syarat minimal pembelian untuk kode promo tersebut.');
        }

        if ($voucher['jenis_diskon'] === 'persen') {
            $persentase = max(0, min(100, (float)$voucher['nilai_diskon']));
            $diskon = ($persentase / 100) * $total_harga;
        } else {
            $diskon = (float)$voucher['nilai_diskon'];
        }
        $diskon = min($diskon, $total_harga);
        $promo_kode = $voucher['kode_voucher'];
    }
    
    $harga_akhir_transaksi = $total_harga - $diskon;
    if ($harga_akhir_transaksi < 0) {
        $harga_akhir_transaksi = 0;
    }

    $alamat_pengiriman = trim($_POST['alamat_pengiriman'] ?? '');
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? '');

    if ($alamat_pengiriman === '' || $metode_pembayaran === '') {
        throw new Exception('Alamat pengiriman dan metode pembayaran wajib diisi.');
    }

    $kode_transaksi = 'INV-' . strtoupper(uniqid());

    // Memasukkan data transaksi baru dengan total harga yang sudah terverifikasi.
    $stmt_transaksi = $db->prepare(
        "INSERT INTO transaksi (kode_transaksi, user_id, total, alamat_pengiriman, status, metode_pembayaran, tanggal_transaksi) 
         VALUES (?, ?, ?, ?, 'Diproses', ?, NOW())"
    );
    $stmt_transaksi->execute([$kode_transaksi, $user_id, $harga_akhir_transaksi, $alamat_pengiriman, $metode_pembayaran]);
    
    $transaksi_id = $db->lastInsertId();

    // Simpan detail setiap item sekaligus kurangi stok produk secara atomik.
    $stmt_item = $db->prepare(
        "INSERT INTO transaksi_item (transaksi_id, produk_id, ukuran, jumlah, harga) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt_reduce_stock = $db->prepare("UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?");
    
    foreach ($items_in_cart as $item) {
        $stmt_item->execute([$transaksi_id, $item['produk_id'], $item['ukuran'], $item['kuantitas'], $item['harga']]);
        $stmt_reduce_stock->execute([$item['kuantitas'], $item['produk_id'], $item['kuantitas']]);
        if ($stmt_reduce_stock->rowCount() === 0) {
            throw new Exception("Stok untuk {$item['nama']} berubah. Silakan perbarui keranjang Anda.");
        }
    }

    // Kurangi kuota voucher apabila digunakan.
    if ($promo_kode !== null) {
        $stmt_reduce_voucher = $db->prepare("UPDATE vouchers SET kuota = kuota - 1 WHERE kode_voucher = ? AND kuota > 0");
        $stmt_reduce_voucher->execute([$promo_kode]);
        if ($stmt_reduce_voucher->rowCount() === 0) {
            throw new Exception('Kuota voucher sudah habis. Silakan gunakan kode promo lainnya.');
        }
    }
    
    // Mengosongkan keranjang pengguna setelah pesanan berhasil dibuat.
    $stmt_clear_cart = $db->prepare("DELETE FROM keranjang_pengguna WHERE user_id = ?");
    $stmt_clear_cart->execute([$user_id]);

    // Menghapus session promo.
    unset($_SESSION['promo']);
    
    // Menyimpan semua perubahan ke database.
    $db->commit();
    
    // Arahkan ke halaman pesanan sukses.
    header('Location: /jejakpetualang/pages/pesanan_sukses.php?id=' . $transaksi_id);
    exit();

} catch (Exception $e) {
    // Jika terjadi error di manapun dalam blok try, batalkan semua perubahan.
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['pesan_error'] = 'Gagal memproses pesanan: ' . $e->getMessage();
    header('Location: /jejakpetualang/pages/checkout.php');
    exit();
}
?>
