<?php
// CATATAN: File ini berfungsi sebagai "controller" untuk Halaman Detail Produk.

// 1. Memanggil file konfigurasi.
require_once __DIR__ . '/../config/koneksi.php';

// --- LOGIKA PENGAMBILAN DATA ---

// Ambil ID produk dari URL.
// Kita paksa jadi integer (int) sebagai lapisan keamanan tambahan.
$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Jika ID tidak valid atau 0, lempar pengguna kembali ke halaman produk.
if ($id_produk <= 0) {
    header('Location: ' . BASE_URL . '/pages/product.php');
    exit();
}

try {
    // --- PERBAIKAN SQL INJECTION (SELECT PRODUK) ---
    // 1. Ambil data produk secara AMAN menggunakan prepared statement.
    $stmt_produk = db()->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt_produk->execute([$id_produk]);
    $produk = $stmt_produk->fetch();

    // Jika produk dengan ID tersebut tidak ditemukan, lempar kembali.
    if (!$produk) {
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Produk tidak ditemukan.'];
        header('Location: ' . BASE_URL . '/pages/product.php');
        exit();
    }

    // --- PERBAIKAN SQL INJECTION (SELECT ULASAN) ---
    // 2. Ambil data ulasan secara AMAN menggunakan prepared statement.
    // Query ini menggabungkan tabel 'ulasan' dan 'users'
    $stmt_ulasan = db()->prepare("
        SELECT ulasan.*, users.nama 
        FROM ulasan 
        JOIN users ON ulasan.user_id = users.id 
        WHERE ulasan.produk_id = ? 
        ORDER BY ulasan.created_at DESC
    ");
    $stmt_ulasan->execute([$id_produk]);
    $ulasan = $stmt_ulasan->fetchAll();

    // Cek apakah user saat ini sudah pernah membeli & memberi ulasan
    $user_sudah_ulas = false;
    if (isset($_SESSION['user_id'])) {
        $stmt_cek_ulasan = db()->prepare("SELECT COUNT(id) FROM ulasan WHERE produk_id = ? AND user_id = ?");
        $stmt_cek_ulasan->execute([$id_produk, $_SESSION['user_id']]);
        if ($stmt_cek_ulasan->fetchColumn() > 0) {
            $user_sudah_ulas = true;
        }
    }


} catch (PDOException $e) {
    // Tangani jika ada error database
    // error_log($e->getMessage()); // Catat error
    die("Terjadi error saat mengambil data produk. Silakan coba lagi.");
}

// --- PERSIAPAN VARIABEL UNTUK LAYOUT ---

// Menetapkan judul halaman. Kita gunakan htmlspecialchars untuk keamanan (XSS).
$title = htmlspecialchars($produk['nama']) . ' - Jejak Petualang';

// Memberi tahu file layout (`app.php`) bagian konten mana yang harus dimuat.
$page = __DIR__ . '/content/product-detail-content.php'; 

// Variabel untuk menyisipkan JavaScript tambahan jika diperlukan.
// (Misalnya untuk image gallery atau slider)
$extra_js = ''; 

// 3. TERAKHIR, panggil file layout utama untuk merakit halaman.
require_once __DIR__ . '/../layout/app.php';
?>