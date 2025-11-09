<?php
// 1. Memanggil file konfigurasi
require_once __DIR__ . '/../../config/koneksi.php';

// 2. Memanggil file header (termasuk auth.php)
$page_title = 'Manajemen Kategori';
include __DIR__ . '/../partials/header.php';

// 3. Logika untuk mengambil data
try {
    $stmt = db()->query("SELECT * FROM kategori ORDER BY nama ASC");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {
    // error_log($e->getMessage());
    echo "<div class='container py-5'><div class='alert alert-danger'>Gagal mengambil data kategori.</div></div>";
    $kategori_list = []; // Kosongkan list
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Manajemen Kategori</h1>
                <a href="<?= BASE_URL ?>/admin/kategori/tambah.php" class="btn btn-primary">Tambah Kategori</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-end" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kategori_list)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data kategori.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kategori_list as $index => $kategori): ?>
                                <tr>
                                    <th scope="row"><?= $index + 1 ?></th>
                                    
                                    <td><?= htmlspecialchars($kategori['nama']) ?></td>
                                    
                                    <td><?= htmlspecialchars($kategori['deskripsi']) ?></td>
                                    
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/admin/kategori/edit.php?id=<?= (int)$kategori['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="<?= BASE_URL ?>/admin/kategori/hapus.php?id=<?= (int)$kategori['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini? Produk terkait akan kehilangan kategorinya.')">Hapus</a>
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