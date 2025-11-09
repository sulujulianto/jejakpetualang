<?php
// 1. Memanggil file konfigurasi
require_once __DIR__ . '/../../config/koneksi.php';

// 2. Memanggil file header (termasuk auth.php)
$page_title = 'Manajemen Pesanan';
include __DIR__ . '/../partials/header.php';

// 3. Logika untuk filter
$status_filter = $_GET['status'] ?? '';
$sql_where = '';
$params = [];

if (!empty($status_filter)) {
    $sql_where = "WHERE p.status_pesanan = ?";
    $params[] = $status_filter;
}

// 4. Logika untuk mengambil data
try {
    // (Sudah AMAN dari SQL Injection)
    $sql = "SELECT p.*, u.nama as nama_user 
            FROM pesanan p
            LEFT JOIN users u ON p.user_id = u.id
            $sql_where
            ORDER BY p.tgl_pesanan DESC";
    
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $pesanan_list = $stmt->fetchAll();

} catch (PDOException $e) {
    // error_log($e->getMessage());
    echo "<div class='container py-5'><div class='alert alert-danger'>Gagal mengambil data pesanan.</div></div>";
    $pesanan_list = []; // Kosongkan list
}

// Daftar status untuk dropdown filter
$status_options = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Manajemen Pesanan</h1>

            <div class="card card-body mb-4">
                <form action="<?= BASE_URL ?>/admin/pesanan_index.php" method="GET" class="row g-3">
                    <div class="col-md-10">
                        <label for="status" class="form-label">Filter Status Pesanan</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <?php foreach ($status_options as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>" <?= ($status_filter == $status) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($status) ?>
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
                            <th>ID</th>
                            <th>Tgl. Pesanan</th>
                            <th>Nama Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pesanan_list)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data pesanan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pesanan_list as $pesanan): ?>
                                <tr>
                                    <td><?= $pesanan['id'] ?></td>
                                    <td><?= date('d M Y, H:i', strtotime($pesanan['tgl_pesanan'])) ?></td>
                                    
                                    <td><?= htmlspecialchars($pesanan['nama_user'] ?? 'User Dihapus') ?></td>
                                    
                                    <td>Rp <?= number_format($pesanan['total']) ?></td>
                                    
                                    <td>
                                        <?= htmlspecialchars($pesanan['status_pesanan']) ?>
                                    </td>
                                    
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/admin/pesanan_detail.php?id=<?= (int)$pesanan['id'] ?>" class="btn btn-info btn-sm">Detail</a>
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