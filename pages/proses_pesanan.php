<?php
// CATATAN: File ini sekarang menggunakan "penjaga gerbang" yang benar.

// [PERBAIKAN UTAMA] Menggunakan "penjaga gerbang" untuk halaman proses form standar.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

// --- Keamanan dan Validasi Awal ---

// Keamanan: Memastikan permintaan datang dari form checkout.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /jejakpetualang/pages/checkout.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = db();

try {
    // Memulai mode transaksi.
    $db->beginTransaction();

    // Mengambil semua item dari keranjang pengguna, termasuk harga yang terkunci.
    $sql_items = "
        SELECT kp.produk_id, kp.kuantitas, kp.ukuran, kp.harga_saat_ditambahkan as harga
        FROM keranjang_pengguna kp
        WHERE kp.user_id = ?
    ";
    $stmt_items = $db->prepare($sql_items);
    $stmt_items->execute([$user_id]);
    $items_in_cart = $stmt_items->fetchAll();

    if (empty($items_in_cart)) {
        header('Location: /jejakpetualang/pages/keranjang.php');
        exit();
    }
    
    // Menghitung total harga berdasarkan harga yang terkunci.
    $total_harga = 0;
    foreach($items_in_cart as $item) {
        $total_harga += $item['harga'] * $item['kuantitas'];
    }
    
    // Mengambil data dari form checkout.
    $diskon = $_SESSION['promo']['diskon'] ?? 0;
    $harga_akhir_transaksi = $total_harga - $diskon;
    if ($harga_akhir_transaksi < 0) $harga_akhir_transaksi = 0;

    $alamat_pengiriman = $_POST['alamat_pengiriman'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $kode_transaksi = 'INV-' . strtoupper(uniqid());

    // Memasukkan data transaksi baru dengan total harga yang benar.
    $stmt_transaksi = $db->prepare(
        "INSERT INTO transaksi (kode_transaksi, user_id, total, alamat_pengiriman, status, metode_pembayaran, tanggal_transaksi) 
         VALUES (?, ?, ?, ?, 'Diproses', ?, NOW())"
    );
    $stmt_transaksi->execute([$kode_transaksi, $user_id, $harga_akhir_transaksi, $alamat_pengiriman, $metode_pembayaran]);
    
    $transaksi_id = $db->lastInsertId();

    // Mempersiapkan statement INSERT untuk menyimpan setiap item.
    $stmt_item = $db->prepare(
        "INSERT INTO transaksi_item (transaksi_id, produk_id, ukuran, jumlah, harga) 
         VALUES (?, ?, ?, ?, ?)"
    );
    
    // Looping dan menyimpan setiap item dengan harga yang terkunci.
    foreach ($items_in_cart as $item) {
        $stmt_item->execute([$transaksi_id, $item['produk_id'], $item['ukuran'], $item['kuantitas'], $item['harga']]);
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
    // Hentikan skrip dan tampilkan pesan error.
    die("Gagal memproses pesanan: " . $e->getMessage());
}
?>