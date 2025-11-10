<?php
// CATATAN: Ini adalah "script" murni untuk memproses Checkout.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../auth/user-auth.php'; // Pastikan user login

// 2. Memanggil helper CSRF
require_once __DIR__ . '/../helpers/csrf.php';

// 3. Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: '. BASE_URL . '/pages/checkout.php');
    exit();
}

// 4. --- PERBAIKAN CSRF ---
// Validasi token CSRF di paling atas.
// Jika token tidak valid, skrip akan berhenti.
require_valid_csrf_token();


// 5. Ambil data dari form (Validasi di sisi server)
$user_id = $_SESSION['user_id'];
$alamat_pengiriman = $_POST['alamat_pengiriman'] ?? null;
$metode_pembayaran = $_POST['metode_pembayaran'] ?? null;
// Ambil kode voucher dari Sesi (yang di-set oleh checkout.php), bukan dari POST
$kode_voucher = $_SESSION['kode_voucher_valid'] ?? null; 

// Validasi
if (empty($alamat_pengiriman) || empty($metode_pembayaran)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Alamat dan metode pembayaran wajib diisi.'];
    header('Location: ' . BASE_URL . '/pages/checkout.php');
    exit();
}

// --- Mulai Transaksi Database ---
try {
    db()->beginTransaction();

    // 6. Ambil semua item keranjang user (Sudah AMAN dari SQLi)
    $sql_cart = "SELECT k.produk_id, k.jumlah, p.nama, p.harga, p.stok
                 FROM keranjang k
                 JOIN produk p ON k.produk_id = p.id
                 WHERE k.user_id = ?";
    $stmt_cart = db()->prepare($sql_cart);
    $stmt_cart->execute([$user_id]);
    $items = $stmt_cart->fetchAll();

    if (empty($items)) {
        throw new Exception("Keranjang Anda kosong.");
    }

    // 7. Hitung total dan cek stok
    $subtotal = 0;
    foreach ($items as $item) {
        if ($item['stok'] < $item['jumlah']) {
            throw new Exception("Stok produk '" . htmlspecialchars($item['nama']) . "' tidak mencukupi.");
        }
        $subtotal += $item['harga'] * $item['jumlah'];
    }

    // 8. Validasi Voucher (jika ada) (Sudah AMAN dari SQLi)
    $diskon = 0;
    $voucher_id = null;
    if (!empty($kode_voucher)) {
        $stmt_voucher = db()->prepare(
            "SELECT * FROM vouchers 
             WHERE kode = ? AND status_aktif = 1 AND kuota > 0 AND tgl_mulai <= CURDATE() AND tgl_selesai >= CURDATE()"
        );
        $stmt_voucher->execute([$kode_voucher]);
        $voucher = $stmt_voucher->fetch();

        if ($voucher) {
            $voucher_id = $voucher['id'];
            if ($voucher['jenis'] == 'persen') {
                $diskon = $subtotal * ($voucher['nilai'] / 100);
            } else { 
                $diskon = $voucher['nilai'];
            }
            db()->prepare("UPDATE vouchers SET kuota = kuota - 1 WHERE id = ?")->execute([$voucher_id]);
        }
    }

    $total_akhir = $subtotal - $diskon;
    $biaya_kirim = 0; 
    $total_bayar = $total_akhir + $biaya_kirim;

    // 9. Buat 1 record pesanan baru (Sudah AMAN dari SQLi)
    $sql_insert_pesanan = "
        INSERT INTO pesanan (user_id, total, status_pesanan, alamat_pengiriman, metode_pembayaran, voucher_id, diskon, subtotal, biaya_kirim, tgl_pesanan)
        VALUES (?, ?, 'Menunggu Pembayaran', ?, ?, ?, ?, ?, ?, NOW())
    ";
    $stmt_pesanan = db()->prepare($sql_insert_pesanan);
    $stmt_pesanan->execute([
        $user_id, $total_bayar, $alamat_pengiriman, $metode_pembayaran, $voucher_id, $diskon, $subtotal, $biaya_kirim
    ]);
    
    $pesanan_id = db()->lastInsertId();

    // 10. Siapkan query (Sudah AMAN dari SQLi)
    $stmt_insert_detail = db()->prepare(
        "INSERT INTO detail_pesanan (pesanan_id, produk_id, user_id, jumlah, harga_saat_beli, nama_produk) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt_update_stok = db()->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

    // 11. Loop item keranjang lagi
    foreach ($items as $item) {
        $stmt_insert_detail->execute([
            $pesanan_id, $item['produk_id'], $user_id, $item['jumlah'], $item['harga'], $item['nama']
        ]);
        $stmt_update_stok->execute([$item['jumlah'], $item['produk_id']]);
    }

    // 12. Kosongkan keranjang user (Sudah AMAN dari SQLi)
    $stmt_clear_cart = db()->prepare("DELETE FROM keranjang WHERE user_id = ?");
    $stmt_clear_cart->execute([$user_id]);

    // 13. Hapus data voucher dari sesi
    unset($_SESSION['kode_voucher_valid']);
    unset($_SESSION['pesan_voucher']);

    // 14. Commit transaksi
    db()->commit();

    // 15. Arahkan ke halaman sukses
    $_SESSION['pesanan_sukses_id'] = $pesanan_id; 
    header('Location: ' . BASE_URL . '/pages/pesanan_sukses.php');
    exit();

} catch (Exception $e) {
    db()->rollBack();
    
    // Hapus token CSRF baru agar form bisa disubmit ulang
    unset($_SESSION['csrf_token']);
    unset($_SESSION['kode_voucher_valid']); // Hapus juga voucher
    
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Checkout Gagal: ' . $e->getMessage()];
    header('Location: ' . BASE_URL . '/pages/checkout.php');
    exit();
}
?>