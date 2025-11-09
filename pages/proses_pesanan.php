<?php
// CATATAN: Ini adalah "script" murni untuk memproses Checkout.
// Ini adalah salah satu file paling kritis di aplikasi.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../auth/user-auth.php'; // Pastikan user login

// 2. Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: '. BASE_URL . '/pages/checkout.php');
    exit();
}

// 3. Ambil data dari form (Validasi di sisi server)
$user_id = $_SESSION['user_id'];
$alamat_pengiriman = $_POST['alamat_pengiriman'] ?? null;
$metode_pembayaran = $_POST['metode_pembayaran'] ?? null;
$kode_voucher = $_POST['kode_voucher'] ?? null;

// Validasi
if (empty($alamat_pengiriman) || empty($metode_pembayaran)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Alamat dan metode pembayaran wajib diisi.'];
    header('Location: ' . BASE_URL . '/pages/checkout.php');
    exit();
}

// --- Mulai Transaksi Database ---
// Ini SANGAT PENTING. Jika 1 query gagal (misal stok habis),
// semua query lain akan dibatalkan (rollback).
try {
    db()->beginTransaction();

    // --- PERBAIKAN SQL INJECTION (SELECT KERANJANG) ---
    // 4. Ambil semua item keranjang user
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

    // 5. Hitung total dan cek stok
    $subtotal = 0;
    foreach ($items as $item) {
        // Cek stok
        if ($item['stok'] < $item['jumlah']) {
            throw new Exception("Stok produk '" . htmlspecialchars($item['nama']) . "' tidak mencukupi.");
        }
        $subtotal += $item['harga'] * $item['jumlah'];
    }

    // 6. Validasi Voucher (jika ada)
    $diskon = 0;
    $voucher_id = null;
    if (!empty($kode_voucher)) {
        // --- PERBAIKAN SQL INJECTION (SELECT VOUCHER) ---
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
            } else { // Asumsi 'tetap'
                $diskon = $voucher['nilai'];
            }
            // (Opsional) Kurangi kuota voucher
            db()->prepare("UPDATE vouchers SET kuota = kuota - 1 WHERE id = ?")->execute([$voucher_id]);
        } else {
            $_SESSION['pesan_voucher'] = 'Kode voucher tidak valid atau sudah kedaluwarsa.';
        }
    }

    $total_akhir = $subtotal - $diskon;
    // Asumsi biaya kirim (bisa ditambahkan nanti)
    $biaya_kirim = 0; 
    $total_bayar = $total_akhir + $biaya_kirim;

    // --- PERBAIKAN SQL INJECTION (INSERT PESANAN) ---
    // 7. Buat 1 record pesanan baru
    $sql_insert_pesanan = "
        INSERT INTO pesanan (user_id, total, status_pesanan, alamat_pengiriman, metode_pembayaran, voucher_id, diskon, subtotal, biaya_kirim, tgl_pesanan)
        VALUES (?, ?, 'Menunggu Pembayaran', ?, ?, ?, ?, ?, ?, NOW())
    ";
    $stmt_pesanan = db()->prepare($sql_insert_pesanan);
    $stmt_pesanan->execute([
        $user_id, $total_bayar, $alamat_pengiriman, $metode_pembayaran, $voucher_id, $diskon, $subtotal, $biaya_kirim
    ]);
    
    // Ambil ID pesanan yang baru saja dibuat
    $pesanan_id = db()->lastInsertId();

    // 8. Siapkan query untuk memasukkan detail pesanan & update stok (di dalam loop)
    // --- PERBAIKAN SQL INJECTION (INSERT DETAIL & UPDATE STOK) ---
    $stmt_insert_detail = db()->prepare(
        "INSERT INTO detail_pesanan (pesanan_id, produk_id, user_id, jumlah, harga_saat_beli, nama_produk) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt_update_stok = db()->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

    // 9. Loop item keranjang lagi, masukkan ke detail_pesanan & kurangi stok
    foreach ($items as $item) {
        // Masukkan ke detail_pesanan
        $stmt_insert_detail->execute([
            $pesanan_id, $item['produk_id'], $user_id, $item['jumlah'], $item['harga'], $item['nama']
        ]);
        // Kurangi stok produk
        $stmt_update_stok->execute([$item['jumlah'], $item['produk_id']]);
    }

    // --- PERBAIKAN SQL INJECTION (DELETE KERANJANG) ---
    // 10. Kosongkan keranjang user
    $stmt_clear_cart = db()->prepare("DELETE FROM keranjang WHERE user_id = ?");
    $stmt_clear_cart->execute([$user_id]);

    // 11. Jika semua query di atas berhasil, commit transaksi
    db()->commit();

    // 12. Arahkan ke halaman sukses
    $_SESSION['pesanan_sukses_id'] = $pesanan_id; // Untuk ditampilkan di halaman sukses
    header('Location: ' . BASE_URL . '/pages/pesanan_sukses.php');
    exit();

} catch (Exception $e) {
    // --- PENTING: BATALKAN SEMUA JIKA ADA ERROR ---
    db()->rollBack();
    
    // error_log($e->getMessage()); // Catat error
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Checkout Gagal: ' . $e->getMessage()];
    header('Location: ' . BASE_URL . '/pages/checkout.php');
    exit();
}
?>