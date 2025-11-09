<?php
// CATATAN: Ini adalah "script" murni untuk memproses aksi Tambah ke Keranjang.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
// Memanggil user-auth untuk memastikan user sudah login
require_once __DIR__ . '/../auth/user-auth.php'; 

// 2. Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // Jika bukan POST, kembalikan ke halaman produk
    header('Location: '. BASE_URL . '/pages/product.php');
    exit();
}

// 3. Ambil dan validasi data
$user_id = $_SESSION['user_id'];
$produk_id = $_POST['produk_id'] ?? null;
$jumlah = $_POST['jumlah'] ?? 1; // Default jumlah adalah 1

// Validasi dasar
if (!$produk_id || !filter_var($produk_id, FILTER_VALIDATE_INT) || $produk_id <= 0) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID produk tidak valid.'];
    header('Location: ' . BASE_URL . '/pages/product.php');
    exit();
}
if (!filter_var($jumlah, FILTER_VALIDATE_INT) || $jumlah <= 0) {
    $jumlah = 1; // Paksa jumlah jadi 1 jika tidak valid
}

try {
    // --- PERBAIKAN SQL INJECTION (SELECT) ---
    // 4. Cek apakah produk sudah ada di keranjang user
    $stmt_cek = db()->prepare("SELECT * FROM keranjang WHERE user_id = ? AND produk_id = ?");
    $stmt_cek->execute([$user_id, $produk_id]);
    $item_keranjang = $stmt_cek->fetch();

    if ($item_keranjang) {
        // --- PERBAIKAN SQL INJECTION (UPDATE) ---
        // 5. Jika SUDAH ADA, update jumlahnya
        $jumlah_baru = $item_keranjang['jumlah'] + $jumlah;
        $stmt_update = db()->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ?");
        $stmt_update->execute([$jumlah_baru, $item_keranjang['id']]);
        
    } else {
        // --- PERBAIKAN SQL INJECTION (INSERT) ---
        // 6. Jika BELUM ADA, masukkan sebagai item baru
        $stmt_insert = db()->prepare("INSERT INTO keranjang (user_id, produk_id, jumlah) VALUES (?, ?, ?)");
        $stmt_insert->execute([$user_id, $produk_id, $jumlah]);
    }

    // Beri pesan sukses
    $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Produk berhasil ditambahkan ke keranjang.'];

} catch (PDOException $e) {
    // error_log($e->getMessage());
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Terjadi masalah saat menambahkan ke keranjang.'];
}

// 7. Arahkan pengguna kembali ke halaman sebelumnya (atau halaman produk)
// (Kita gunakan HTTP_REFERER agar lebih fleksibel)
$previous_url = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/product.php';
header('Location: ' . $previous_url);
exit();
?>