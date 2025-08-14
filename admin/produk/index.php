<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses.
require_once __DIR__ . '/../auth.php';

// Menetapkan judul halaman untuk ditampilkan di tag <title> HTML.
$page_title = 'Manajemen Produk';
// Menyertakan file header (bagian atas HTML, navbar, dll).
include __DIR__ . '/../partials/header.php';

// --- Logika untuk filter dan persiapan query ---

// Mengambil nilai 'kategori' dari URL (misal: index.php?kategori=3).
// Jika tidak ada, nilainya akan menjadi null. Ini digunakan untuk memfilter produk.
$kategori_filter = $_GET['kategori'] ?? null;

// Variabel untuk menampung klausa WHERE pada query SQL. Defaultnya kosong.
$sql_where = '';
// Array untuk menampung parameter yang akan diikat (bind) ke query. Ini untuk keamanan (mencegah SQL Injection).
$params = [];

// Jika ada filter kategori yang aktif (variabel $kategori_filter tidak null).
if ($kategori_filter) {
    // Siapkan klausa WHERE untuk memfilter berdasarkan ID kategori.
    $sql_where = 'WHERE p.kategori_id = ?';
    // Tambahkan nilai ID kategori ke dalam array parameter.
    $params[] = $kategori_filter;
}

// Blok try-catch untuk menangani potensi error dari database.
try {
    // --- Pengambilan Data Produk dari Database ---

    // Menyiapkan query SQL utama untuk mengambil data produk.
    // SELECT p.*, k.nama_kategori: Ambil semua kolom dari tabel produk (alias 'p') dan kolom nama_kategori dari tabel kategori (alias 'k').
    // FROM produk p: Tabel utamanya adalah produk.
    // LEFT JOIN kategori k ON p.kategori_id = k.id: Gabungkan dengan tabel kategori. LEFT JOIN memastikan produk tetap tampil meskipun kategori_id-nya null.
    // $sql_where: Sisipkan klausa WHERE yang sudah disiapkan sebelumnya (bisa kosong atau berisi filter).
    // ORDER BY p.id DESC: Urutkan produk berdasarkan ID secara menurun (produk terbaru tampil duluan).
    $sql = "SELECT p.*, k.nama_kategori 
            FROM produk p 
            LEFT JOIN kategori k ON p.kategori_id = k.id 
            $sql_where
            ORDER BY p.id DESC";
    
    // Mempersiapkan statement SQL untuk dieksekusi.
    $stmt = db()->prepare($sql);
    // Menjalankan query dengan parameter yang ada di array $params. Jika tidak ada filter, array ini kosong.
    $stmt->execute($params);
    // Mengambil semua hasil query dan menyimpannya ke dalam variabel $produk_list.
    $produk_list = $stmt->fetchAll();

// Jika terjadi error saat eksekusi query, blok catch akan dijalankan.
} catch (PDOException $e) {
    // Hentikan skrip dan tampilkan pesan error.
    die("Error: " . $e->getMessage());
}
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Manajemen Produk</h1>
                <a href="/jejakpetualang/admin/produk_tambah.php" class="btn btn-primary">Tambah Produk Baru</a>
            </div>

            <?php 
            // Menampilkan pesan sukses jika ada (misalnya setelah berhasil menambah/edit/hapus produk).
            if (isset($_SESSION['pesan_sukses'])): 
            ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']); unset($_SESSION['pesan_sukses']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Jika tidak ada produk yang ditemukan, tampilkan pesan.
                        if (empty($produk_list)): 
                        ?>
                            <tr><td colspan="7" class="text-center">Belum ada produk.</td></tr>
                        <?php else: // Jika ada produk, lakukan perulangan untuk menampilkannya ?>
                            <?php foreach ($produk_list as $index => $produk): ?>
                                <tr>
                                    <th scope="row"><?= $index + 1 ?></th>
                                    <td>
                                        <img src="/jejakpetualang/uploads/produk/<?= htmlspecialchars($produk['gambar']) ?>" alt="Gambar Produk" width="60" class="rounded">
                                    </td>
                                    <td><?= htmlspecialchars($produk['nama']) ?></td>
                                    <td><?= htmlspecialchars($produk['nama_kategori']) ?></td>
                                    <td>Rp <?= number_format($produk['harga']) ?></td>
                                    <td><?= $produk['stok'] ?></td>
                                    <td class="text-end">
                                        <a href="/jejakpetualang/admin/produk_edit.php?id=<?= $produk['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="/jejakpetualang/admin/produk_hapus.php?id=<?= $produk['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>