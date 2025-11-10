<?php
// CATATAN: Ini adalah "script" murni untuk memproses update status pesanan.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth.php'; // Pastikan hanya admin

// 2. Memanggil helper CSRF (BARU)
require_once __DIR__ . '/../../helpers/csrf.php';

// 3. Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: '. BASE_URL . '/admin/pesanan_index.php');
    exit();
}

// 4. --- PERBAIKAN CSRF (BARU) ---
// Validasi token CSRF. Jika tidak valid, skrip akan berhenti.
require_valid_csrf_token();

// 5. Ambil data dari form
$pesanan_id = $_POST['pesanan_id'] ?? null;
$status = $_POST['status'] ?? null;

// Daftar status yang diizinkan
$status_options = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

// 6. Validasi
if (empty($pesanan_id) || empty($status) || !in_array($status, $status_options)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Data tidak valid untuk update status.'];
    header('Location: ' . BASE_URL . '/admin/pesanan_index.php');
    exit();
}

try {
    // 7. Update status pesanan (Sudah AMAN dari SQLi)
    $stmt = db()->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id = ?");
    $stmt->execute([$status, $pesanan_id]);

    $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Status pesanan berhasil diperbarui.'];

} catch (PDOException $e) {
    // error_log($e->getMessage());
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Gagal memperbarui status pesanan.'];
}

// 8. Kembalikan ke halaman detail pesanan
header('Location: ' . BASE_URL . '/admin/pesanan_detail.php?id=' . $pesanan_id);
exit();
?>