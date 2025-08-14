<?php
// File: jejak-petualang/pages/proses_ulasan.php
// Catatan: File ini adalah "otak" yang memproses data dari form ulasan.

// [PERBAIKAN UTAMA] Menggunakan "penjaga gerbang" yang benar untuk halaman proses form standar.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

// Keamanan: Pastikan request datang dari form POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika tidak, alihkan ke halaman utama.
    header('Location: index.php');
    exit();
}

// [CATATAN] Pengecekan login manual sudah dihapus karena sudah ditangani oleh user-auth.php.

// Ambil semua data dari form dan session.
$user_id = $_SESSION['user_id'];
$produk_id = (int)($_POST['produk_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$komentar = trim($_POST['komentar'] ?? '');

// Validasi dasar: Pastikan semua data yang dibutuhkan ada dan valid.
if ($produk_id === 0 || $rating < 1 || $rating > 5 || empty($komentar)) {
    // Jika data tidak valid, simpan pesan error di session.
    $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Rating dan komentar wajib diisi.'];
    // Arahkan kembali ke halaman produk.
    header('Location: product_detail.php?id=' . $produk_id);
    exit();
}

try {
    // Cek apakah pengguna ini sudah pernah memberikan ulasan untuk produk yang sama.
    $stmt_cek = db()->prepare("SELECT id FROM ulasan WHERE user_id = ? AND produk_id = ?");
    $stmt_cek->execute([$user_id, $produk_id]);
    $ulasan_ada = $stmt_cek->fetch();

    if ($ulasan_ada) {
        // Jika sudah ada ulasan, perbarui (UPDATE) ulasan yang lama.
        $stmt_update = db()->prepare(
            "UPDATE ulasan SET rating = ?, komentar = ?, created_at = NOW() WHERE id = ?"
        );
        $stmt_update->execute([$rating, $komentar, $ulasan_ada['id']]);
        // Siapkan pesan sukses.
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Ulasan Anda berhasil diperbarui.'];
    } else {
        // Jika belum ada, buat (INSERT) ulasan baru.
        $stmt_insert = db()->prepare(
            "INSERT INTO ulasan (user_id, produk_id, rating, komentar) VALUES (?, ?, ?, ?)"
        );
        $stmt_insert->execute([$user_id, $produk_id, $rating, $komentar]);
        // Siapkan pesan sukses.
        $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Terima kasih atas ulasan Anda!'];
    }

} catch (PDOException $e) {
    // Jika terjadi error pada database, siapkan pesan error.
    $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Terjadi kesalahan saat menyimpan ulasan.'];
}

// Setelah semua proses selesai, arahkan pengguna kembali ke halaman detail produk.
// Pesan sukses atau error akan ditampilkan di sana.
header('Location: product_detail.php?id=' . $produk_id);
exit();
?>