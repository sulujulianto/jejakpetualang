<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya pengguna terautentikasi (admin) yang dapat mengakses halaman ini.
require_once __DIR__ . '/../auth.php';

// Mengambil nilai 'id' dari parameter URL (contoh: detail.php?id=5).
// Jika 'id' tidak ada di URL, variabel $id akan diisi dengan null berkat operator null coalescing (??).
$id = $_GET['id'] ?? null;
// Memeriksa apakah $id kosong (null). Jika ya, berarti tidak ada ID yang diberikan.
if (!$id) {
    // Alihkan (redirect) pengguna kembali ke halaman daftar pengguna.
    header("Location: index.php");
    // Hentikan eksekusi skrip untuk memastikan tidak ada kode lain yang berjalan setelah redirect.
    exit();
}

// Menggunakan blok try-catch untuk menangani potensi error saat berinteraksi dengan database.
try {
    // Mempersiapkan statement SQL untuk mengambil semua data (*) dari tabel 'users' berdasarkan 'id'.
    // Menggunakan prepared statement dengan tanda tanya (?) adalah praktik keamanan untuk mencegah SQL Injection.
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    // Menjalankan statement yang telah dipersiapkan dengan mengikat nilai dari $id ke tanda tanya (?).
    $stmt->execute([$id]);
    // Mengambil satu baris hasil query sebagai array asosiatif. Hasilnya disimpan di variabel $user.
    $user = $stmt->fetch();

    // Memeriksa apakah data pengguna ditemukan. Jika $user bernilai false, artinya tidak ada pengguna dengan ID tersebut.
    if (!$user) {
        // Jika pengguna tidak ditemukan, alihkan kembali ke halaman daftar pengguna.
        header("Location: index.php");
        // Hentikan eksekusi skrip.
        exit();
    }
// Blok catch akan menangkap error PDOException jika terjadi masalah pada koneksi atau query.
} catch (PDOException $e) {
    // Jika terjadi error, hentikan skrip dan tampilkan pesan error yang jelas.
    die("Error: " . $e->getMessage());
}

// Menetapkan judul halaman untuk tag <title> di HTML.
$page_title = 'Detail Pengguna';
// Menyertakan file header yang berisi bagian atas dari template HTML.
include __DIR__ . '/../partials/header.php';
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Detail Pengguna: <?= htmlspecialchars($user['nama']) ?></h1>
            
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 20%;">ID Pengguna</th>
                        <td><?= $user['id'] ?></td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td><?= htmlspecialchars($user['nama']) ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td><?= ucfirst($user['role']) ?></td>
                    </tr>
                     <tr>
                        <th>Nomor Telepon</th>
                        <td><?= htmlspecialchars($user['nomor_telepon'] ?? 'Belum diisi') ?></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td><?= nl2br(htmlspecialchars($user['alamat'] ?? 'Belum diisi')) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Daftar</th>
                        <td><?= date('d F Y, H:i', strtotime($user['created_at'])) ?></td>
                    </tr>
                </tbody>
            </table>
            <a href="index.php" class="btn btn-secondary mt-3">Kembali ke Daftar Pengguna</a>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
