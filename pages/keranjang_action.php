<?php
// CATATAN: File ini sekarang menggunakan "penjaga gerbang" yang benar dan logikanya sudah diperbaiki.

// Menggunakan "penjaga gerbang" untuk halaman proses form standar, bukan AJAX.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers/csrf.php';

// [CATATAN] Logika session_start() dan if(!isset) manual sudah dihapus karena sudah ditangani oleh user-auth.php.

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if (in_array($action, ['update', 'apply_promo'], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();
}
$db = db();

try {
    // [CATATAN] Aksi 'add' telah dihapus karena sudah ditangani oleh file 'tambah_keranjang.php'.

    // === AKSI: UPDATE KERANJANG ===
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $items_to_update = $_POST['items'] ?? [];
        if (!empty($items_to_update)) {
            $db->beginTransaction();
            $stmt_update_item = $db->prepare("UPDATE keranjang_pengguna SET kuantitas = ?, ukuran = ? WHERE id = ? AND user_id = ?");
            $stmt_fetch_item = $db->prepare("
                SELECT kp.produk_id, p.nama, p.stok 
                FROM keranjang_pengguna kp 
                JOIN produk p ON kp.produk_id = p.id 
                WHERE kp.id = ? AND kp.user_id = ?
            ");

            $errors = [];
            foreach ($items_to_update as $item_id => $data) {
                $item_id = (int)$item_id;
                $kuantitas_baru = max(0, (int)($data['kuantitas'] ?? 0));
                $ukuran_baru = trim((string)($data['ukuran'] ?? ''));
                $ukuran_baru = $ukuran_baru !== '' ? substr($ukuran_baru, 0, 50) : 'N/A';

                if ($kuantitas_baru === 0) {
                    continue;
                }

                $stmt_fetch_item->execute([$item_id, $user_id]);
                $itemInfo = $stmt_fetch_item->fetch();
                if (!$itemInfo) {
                    continue;
                }

                $stok_tersedia = (int)$itemInfo['stok'];
                if ($stok_tersedia <= 0 || $kuantitas_baru > $stok_tersedia) {
                    $errors[] = "Stok untuk \"" . $itemInfo['nama'] . "\" hanya {$stok_tersedia} unit.";
                    continue;
                }

                $stmt_update_item->execute([$kuantitas_baru, $ukuran_baru, $item_id, $user_id]);
            }

            if (!empty($errors)) {
                $db->rollBack();
                $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => implode(' ', $errors)];
            } else {
                $db->commit();
                unset($_SESSION['promo']);
                $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Keranjang berhasil diperbarui.'];
            }
        }
        header('Location: /jejakpetualang/pages/keranjang.php');
        exit();
    }

    // === AKSI: HAPUS BARANG DARI KERANJANG ===
    if ($action === 'remove' && isset($_GET['item_id'])) {
        $item_id = (int)$_GET['item_id'];
        $stmt_delete = $db->prepare("DELETE FROM keranjang_pengguna WHERE id = ? AND user_id = ?");
        $stmt_delete->execute([$item_id, $user_id]);
        unset($_SESSION['promo']);
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Produk berhasil dihapus dari keranjang.'];
        header('Location: /jejakpetualang/pages/keranjang.php');
        exit();
    }

    // === AKSI: GUNAKAN KODE PROMO ===
    if ($action === 'apply_promo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $kode_promo_input = trim($_POST['kode_promo'] ?? '');
        unset($_SESSION['promo']); 

        // Hitung ulang total harga berdasarkan HARGA YANG TERKUNCI di keranjang.
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
        } elseif ($total_harga <= 0) {
            $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Keranjang Anda masih kosong.'];
        } elseif ($total_harga < $voucher['minimal_pembelian']) {
            $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Total belanja belum memenuhi minimal pembelian untuk promo ini.'];
        } else {
            if ($voucher['jenis_diskon'] === 'persen') {
                $persentase = max(0, min(100, (float)$voucher['nilai_diskon']));
                $nilai_diskon_final = ($persentase / 100) * $total_harga;
            } else {
                $nilai_diskon_final = (float)$voucher['nilai_diskon'];
            }

            $nilai_diskon_final = min($nilai_diskon_final, $total_harga);

            $_SESSION['promo'] = [
                'kode' => $voucher['kode_voucher'],
                'diskon' => $nilai_diskon_final,
                'jenis' => $voucher['jenis_diskon']
            ];
            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Kode promo berhasil digunakan!'];
        }
        header('Location: /jejakpetualang/pages/keranjang.php');
        exit();
    }

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Terjadi kesalahan pada server.'];
    header('Location: /jejakpetualang/pages/keranjang.php');
    exit();
}

// Pengaman: jika file ini diakses tanpa parameter 'action' yang valid, alihkan ke halaman keranjang.
header('Location: /jejakpetualang/pages/keranjang.php');
exit();
?>
