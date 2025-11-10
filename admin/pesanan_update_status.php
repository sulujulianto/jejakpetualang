<?php
// CATATAN: File ini sekarang HANYA berfungsi untuk memproses pembaruan status dari form di halaman detail pesanan.

// --- PERBAIKAN UTAMA ---
// Hapus logika sesi manual dan panggil "penjaga gerbang" admin yang terpusat.
// File auth.php ini akan menangani semua urusan memulai sesi ADMIN_SESSION dan memvalidasi login admin.
require_once __DIR__ . '/auth.php';

// Setelah baris di atas, kita bisa yakin 100% bahwa pengguna adalah admin yang sah.

// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';

// Keamanan: Memastikan permintaan (request) ke file ini datang dari form yang menggunakan metode POST.
// Ini mencegah pengguna mengakses file ini secara langsung melalui URL.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, alihkan ke halaman daftar pesanan.
    header('Location: /jejakpetualang/admin/pesanan_index.php');
    exit(); // Hentikan eksekusi.
}

// Mengambil ID transaksi dari data POST. (int) mengubahnya menjadi integer, dan ?? 0 memberikan nilai default jika tidak ada.
$transaksi_id = (int)($_POST['transaksi_id'] ?? 0);
// Mengambil status baru dari data POST. ?? '' memberikan nilai default jika tidak ada.
$status_baru = $_POST['status'] ?? '';

// Membuat daftar (array) status yang dianggap valid.
// Ini sangat penting untuk keamanan agar hanya nilai yang ada di daftar ini yang bisa dimasukkan ke database.
$status_valid = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

// Melakukan validasi:
// 1. Pastikan ID transaksi lebih besar dari 0.
// 2. Pastikan status baru yang dikirim ada di dalam daftar $status_valid.
if ($transaksi_id > 0 && in_array($status_baru, $status_valid)) {
    // Jika data valid, lanjutkan proses update ke database.
    try {
        // Mempersiapkan statement SQL UPDATE untuk mengubah kolom 'status' di tabel 'transaksi' berdasarkan 'id'.
        $stmt = db()->prepare("UPDATE transaksi SET status = ? WHERE id = ?");
        // Menjalankan query dengan mengikat nilai status baru dan ID transaksi secara aman.
        $stmt->execute([$status_baru, $transaksi_id]);
        // Jika berhasil, atur pesan sukses di session untuk ditampilkan di halaman detail.
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => "Status pesanan #{$transaksi_id} berhasil diubah menjadi '{$status_baru}'."];
    // Tangkap error jika terjadi masalah pada database.
    } catch (PDOException $e) {
        // Jika gagal, atur pesan error di session.
        $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Gagal mengubah status: ' . $e->getMessage()];
    }
} else {
    // Jika data yang dikirim tidak valid (ID = 0 atau status tidak ada di daftar), atur pesan error.
    $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Data tidak valid atau status yang dipilih tidak diizinkan.'];
}

// Setelah semua proses selesai (baik berhasil maupun gagal), arahkan admin kembali ke halaman detail pesanan yang sama.
// ID transaksi disertakan kembali di URL agar halaman yang benar dimuat.
header('Location: /jejakpetualang/admin/pesanan_detail.php?id=' . $transaksi_id);
// Hentikan eksekusi skrip.
exit();
?>