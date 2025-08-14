<?php
// CATATAN: File ini sekarang menggunakan "penjaga gerbang" yang benar dan logikanya sudah diperbaiki.

// [PERBAIKAN 1] Menggunakan "penjaga gerbang" untuk halaman proses form standar, bukan AJAX.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

// [CATATAN] Logika session_start() dan if(!isset) manual sudah dihapus karena sudah ditangani oleh user-auth.php.

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$db = db();

try {
    // [CATATAN] Aksi 'add' telah dihapus karena sudah ditangani oleh file 'tambah_keranjang.php'.

    // === AKSI: UPDATE KERANJANG ===
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $items_to_update = $_POST['items'] ?? [];
        if (!empty($items_to_update)) {
            $db->beginTransaction();
            $stmt_update_item = $db->prepare("UPDATE keranjang_pengguna SET kuantitas = ?, ukuran = ? WHERE id = ? AND user_id = ?");
            foreach ($items_to_update as $item_id => $data) {
                $kuantitas_baru = (int)$data['kuantitas'];
                $ukuran_baru = htmlspecialchars($data['ukuran']);
                if ($kuantitas_baru > 0) {
                    $stmt_update_item->execute([$kuantitas_baru, $ukuran_baru, $item_id, $user_id]);
                }
            }
            $db->commit();
            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Keranjang berhasil diperbarui.'];
        }
        header('Location: /jejak-petualang/pages/keranjang.php');
        exit();
    }

    // === AKSI: HAPUS BARANG DARI KERANJANG ===
    if ($action === 'remove' && isset($_GET['item_id'])) {
        $item_id = (int)$_GET['item_id'];
        $stmt_delete = $db->prepare("DELETE FROM keranjang_pengguna WHERE id = ? AND user_id = ?");
        $stmt_delete->execute([$item_id, $user_id]);
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Produk berhasil dihapus dari keranjang.'];
        header('Location: /jejak-petualang/pages/keranjang.php');
        exit();
    }

    // === AKSI: GUNAKAN KODE PROMO ===
    if ($action === 'apply_promo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $kode_promo_input = trim($_POST['kode_promo'] ?? '');
        unset($_SESSION['promo']); 

        // [PERBAIKAN 2] Hitung ulang total harga berdasarkan HARGA YANG TERKUNCI di keranjang.
        $total_harga = 0;
        $stmt_total = $db->prepare("SELECT harga_saat_ditambahkan, kuantitas FROM keranjang_pengguna WHERE user_id = ?");
        $stmt_total->execute([$user_id]);
        foreach ($stmt_total->fetchAll() as $item) {
            $total_harga += $item['harga_saat_ditambahkan'] * $item['kuantitas'];
        }

        $stmt_promo = $db->prepare("SELECT * FROM vouchers WHERE kode_voucher = ? AND status = 'aktif' AND kuota > 0 AND NOW() BETWEEN tanggal_mulai AND tanggal_berakhir");
        $stmt_promo->execute([$kode_promo_input]);
        $voucher = $stmt_promo->fetch();

        if (!$voucher) {
            $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Kode promo tidak ditemukan atau sudah tidak berlaku.'];
        } elseif ($total_harga < $voucher['minimal_pembelian']) {
            $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Total belanja belum memenuhi minimal pembelian untuk promo ini.'];
        } else {
            $nilai_diskon_final = ($voucher['jenis_diskon'] === 'persen') ? ($voucher['nilai_diskon'] / 100) * $total_harga : $voucher['nilai_diskon'];
            $_SESSION['promo'] = ['kode' => $voucher['kode_voucher'], 'diskon' => $nilai_diskon_final, 'jenis' => $voucher['jenis_diskon']];
            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Kode promo berhasil digunakan!'];
        }
        header('Location: /jejak-petualang/pages/keranjang.php');
        exit();
    }

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Terjadi kesalahan pada server.'];
    header('Location: /jejak-petualang/pages/keranjang.php');
    exit();
}

// Pengaman: jika file ini diakses tanpa parameter 'action' yang valid, alihkan ke halaman keranjang.
header('Location: /jejak-petualang/pages/keranjang.php');
exit();
?>