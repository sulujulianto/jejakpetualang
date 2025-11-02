<?php
// CATATAN: File ini sekarang menggunakan "penjaga gerbang" yang benar dan menyimpan perubahan ke database.

// Menggunakan "penjaga gerbang" untuk halaman proses form standar, bukan AJAX.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

// Memeriksa apakah permintaan datang dari form yang menggunakan metode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Mengambil ID pengguna dari session.
    $user_id = $_SESSION['user_id'];
    // Mengambil array 'items' dari form.
    $items_to_update = $_POST['items'] ?? [];

    if (!empty($items_to_update)) {
        try {
            $db = db();
            // Memulai transaksi untuk memastikan semua item diupdate sekaligus.
            $db->beginTransaction();

            // Menyiapkan query UPDATE untuk menyimpan perubahan ke database.
            $stmt_update_item = $db->prepare(
                "UPDATE keranjang_pengguna SET kuantitas = ?, ukuran = ? WHERE id = ? AND user_id = ?"
            );

            // Looping untuk setiap item yang dikirim dari form.
            foreach ($items_to_update as $item_id => $data) {
                $kuantitas_baru = (int)$data['kuantitas'];
                $ukuran_baru = htmlspecialchars($data['ukuran']);

                // Validasi: Lakukan pembaruan hanya jika kuantitas lebih besar dari 0.
                if ($kuantitas_baru > 0) {
                    // Menjalankan query UPDATE untuk setiap item.
                    $stmt_update_item->execute([$kuantitas_baru, $ukuran_baru, (int)$item_id, $user_id]);
                }
            }

            // Jika semua update berhasil, simpan perubahan secara permanen.
            $db->commit();
            
            // Atur pesan sukses.
            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Keranjang berhasil diperbarui.'];

        } catch (PDOException $e) {
            // Jika terjadi error, batalkan semua perubahan.
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Gagal memperbarui keranjang.'];
        }
    }

    // Karena kuantitas berubah, hapus promo agar dihitung ulang.
    unset($_SESSION['promo']);
}

// Setelah selesai memproses pembaruan, alihkan pengguna kembali ke halaman keranjang.
header('Location: keranjang.php');
exit();
?>