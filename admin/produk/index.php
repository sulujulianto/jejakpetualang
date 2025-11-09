<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses.
require_once __DIR__ . '/../auth.php';

// Menetapkan judul halaman untuk ditampilkan di tag <title> HTML.
$page_title = 'Manajemen Produk';
// Menyertakan file header (bagian atas HTML, navbar, dll).
include __DIR__ . '/../partials/header.php';

// --- Logika untuk filter, pencarian, dan persiapan query ---

$kategori_filter = $_GET['kategori'] ?? null;
$search_query = $_GET['q'] ?? null; 

$sql_conditions = [];
$params = [];

if ($kategori_filter && $kategori_filter != '') {
    $sql_conditions[] = 'p.kategori_id = ?';
    $params[] = $kategori_filter;
}

if ($search_query && $search_query != '') {
    $sql_conditions[] = '(p.nama LIKE ? OR p.deskripsi LIKE ?)';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
}

$sql_where = '';
if (!empty($sql_conditions)) {
    $sql_where = 'WHERE ' . implode(' AND ', $sql_conditions);
}

// Ambil data kategori untuk dropdown filter
try {
    $kategori_list = db()->query("SELECT id, nama FROM kategori ORDER BY nama")->fetchAll();
} catch (PDOException $e) {
    $kategori_list = [];
}


// Blok try-catch untuk menangani potensi error dari database.
try {
    // (Sudah AMAN dari SQL Injection)
    $sql = "SELECT p.*, k.nama as nama_kategori
            FROM produk p 
            LEFT JOIN kategori k ON p.kategori_id = k.id 
            $sql_where
            ORDER BY p.id DESC";
    
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $produk_list = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error: ". $e->getMessage());
}
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Manajemen Produk</h1>
                <a href="<?= BASE_URL ?>/admin/produk_tambah.php" class="btn btn-primary">Tambah Produk Baru</a>
            </div>
            
            <div class="card card-body mb-4">
                <form action="<?= BASE_URL ?>/admin/produk/index.php" method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label for="q" class="form-label">Cari Produk</label>
                        <input type="search" class="form-control" id="q" name="q" value="<?= htmlspecialchars($search_query ?? '') ?>" placeholder="Cari nama atau deskripsi...">
                    </div>
                    <div class="col-md-5">
                        <label for="kategori" class="form-label">Filter Kategori</label>
                        <select id="kategori" name="kategori" class="form-select">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategori_list as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>" <?= ($kategori_filter == $kategori['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kategori['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>


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
                            <th class="text-end" style="min-width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produk_list)): ?>
                            <tr><td colspan="7" class="text-center">
                                <?php if ($search_query || $kategori_filter): ?>
                                    Produk tidak ditemukan dengan kriteria tersebut.
                                <?php else: ?>
                                    Belum ada produk.
                                <?php endif; ?>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($produk_list as $index => $produk): ?>
                                <tr>
                                    <th scope="row"><?= $index + 1 ?></th>
                                    <td>
                                        <?php if (!empty($produk['gambar'])): ?>
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($produk['gambar']) ?>" alt="<?= htmlspecialchars($produk['nama']) ?>" width="60" class="rounded">
                                        <?php else: ?>
                                            <span class="text-muted">No-img</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($produk['nama']) ?></td>
                                    <td><?= htmlspecialchars($produk['nama_kategori'] ?? 'Tanpa Kategori') ?></td>
                                    <td>Rp <?= number_format($produk['harga']) ?></td>
                                    <td><?= $produk['stok'] ?></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/admin/produk_edit.php?id=<?= $produk['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="<?= BASE_URL ?>/admin/produk_hapus.php?id=<?= $produk['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
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
</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>