<?php
// CATATAN: Ini adalah "script" murni untuk memproses form Ulasan Produk.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../auth/user-auth.php'; // Pastikan user login

// 2. Memanggil helper CSRF (BARU)
require_once __DIR__ . '/../helpers/csrf.php';

// 3. Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: '. BASE_URL . '/pages/akun.php');
    exit();
}

// 4. --- PERBAIKAN CSRF (BARU) ---
// Validasi token CSRF. Jika tidak valid, skrip akan berhenti.
require_valid_csrf_token();

// 5. Ambil dan validasi data
$user_id = $_SESSION['user_id'];
$pesanan_id = $_POST['pesanan_id'] ?? null; // (BARU) Ambil pesanan_id
$produk_id = $_POST['produk_id'] ?? null;
$detail_pesanan_id = $_POST['detail_pesanan_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$komentar = $_POST['komentar'] ?? '';

// Validasi
if (empty($pesanan_id) || empty($produk_id) || empty($detail_pesanan_id) || empty($rating)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Data tidak lengkap untuk memberi ulasan.'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/akun.php'));
    exit();
}
$rating = (int)$rating;
if ($rating < 1 || $rating > 5) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Rating harus antara 1 dan 5.'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/akun.php'));
    exit();
}

try {
    db()->beginTransaction();

    // 6. Masukkan ulasan baru (Sudah AMAN dari SQLi)
    $stmt_insert = db()->prepare(
        "INSERT INTO ulasan (user_id, produk_id, detail_pesanan_id, rating, komentar) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt_insert->execute([$user_id, $produk_id, $detail_pesanan_id, $rating, $komentar]);

    // 7. Tandai item sudah diulas (Sudah AMAN dari SQLi)
    $stmt_update = db()->prepare(
        "UPDATE detail_pesanan SET sudah_diulas = 1 WHERE id = ? AND user_id = ?"
    );
    $stmt_update->execute([$detail_pesanan_id, $user_id]);

    db()->commit();
    $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Terima kasih atas ulasan Anda!'];

} catch (PDOException $e) {
    db()->rollBack(); 
    if ($e->getCode() == '23000') {
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Anda sudah pernah memberi ulasan untuk produk ini.'];
    } else {
        // error_log($e->getMessage());
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Terjadi masalah saat menyimpan ulasan.'];
    }
}

// 8. Arahkan pengguna kembali ke halaman detail pesanan
header('Location: ' . BASE_URL . '/pages/pesanan_detail_user.php?id=' . $pesanan_id);
exit();
?>