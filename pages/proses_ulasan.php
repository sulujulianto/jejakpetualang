<?php
// CATATAN: Ini adalah "script" murni untuk memproses form Ulasan Produk.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../auth/user-auth.php'; // Pastikan user login

// 2. Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: '. BASE_URL . '/pages/akun.php');
    exit();
}

// 3. Ambil dan validasi data
$user_id = $_SESSION['user_id'];
$produk_id = $_POST['produk_id'] ?? null;
$detail_pesanan_id = $_POST['detail_pesanan_id'] ?? null; // Kita perlu ini
$rating = $_POST['rating'] ?? null;
$komentar = $_POST['komentar'] ?? '';

// Validasi
if (empty($produk_id) || empty($detail_pesanan_id) || empty($rating)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Data tidak lengkap untuk memberi ulasan.'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/akun.php'));
    exit();
}

// Validasi rating (1-5)
$rating = (int)$rating;
if ($rating < 1 || $rating > 5) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Rating harus antara 1 dan 5.'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/akun.php'));
    exit();
}

// Kita gunakan database transaction untuk memastikan 2 query berhasil
try {
    db()->beginTransaction();

    // --- PERBAIKAN SQL INJECTION (INSERT) ---
    // 1. Masukkan ulasan baru
    $stmt_insert = db()->prepare(
        "INSERT INTO ulasan (user_id, produk_id, detail_pesanan_id, rating, komentar) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt_insert->execute([$user_id, $produk_id, $detail_pesanan_id, $rating, $komentar]);

    // --- PERBAIKAN SQL INJECTION (UPDATE) ---
    // 2. Tandai di detail_pesanan bahwa item ini sudah diulas
    $stmt_update = db()->prepare(
        "UPDATE detail_pesanan SET sudah_diulas = 1 WHERE id = ? AND user_id = ?"
    );
    // Kita tambahkan user_id untuk keamanan ekstra
    $stmt_update->execute([$detail_pesanan_id, $user_id]);

    // Jika semua berhasil
    db()->commit();
    
    $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Terima kasih atas ulasan Anda!'];

} catch (PDOException $e) {
    db()->rollBack(); // Batalkan semua jika ada error
    // error_log($e->getMessage());
    if ($e->getCode() == '23000') {
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Anda sudah pernah memberi ulasan untuk produk ini.'];
    } else {
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Terjadi masalah saat menyimpan ulasan.'];
    }
}

// 4. Arahkan pengguna kembali ke halaman detail pesanan
header('Location: ' . BASE_URL . '/pages/pesanan_detail_user.php?id=' . $_POST['pesanan_id']);
exit();
?>