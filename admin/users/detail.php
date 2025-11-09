<?php
// 1. Memanggil file konfigurasi
require_once __DIR__ . '/../../config/koneksi.php';

// 2. Memanggil file header (termasuk auth.php)
$page_title = 'Detail Pengguna';
include __DIR__ . '/../partials/header.php';

$user_id = $_GET['id'] ?? null;

if (!$user_id || !filter_var($user_id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID Pengguna tidak valid.'];
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit();
}

try {
    // 3. Ambil data pengguna
    // (Sudah AMAN dari SQL Injection)
    $stmt_user = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch();

    if (!$user) {
        $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Pengguna tidak ditemukan.'];
        header('Location: ' . BASE_URL . '/admin/users/index.php');
        exit();
    }
    
    // 4. Ambil riwayat pesanan pengguna
    // (Sudah AMAN dari SQL Injection)
    $stmt_pesanan = db()->prepare(
        "SELECT * FROM pesanan WHERE user_id = ? ORDER BY tgl_pesanan DESC LIMIT 10"
    );
    $stmt_pesanan->execute([$user_id]);
    $pesanan_list = $stmt_pesanan->fetchAll();

} catch (PDOException $e) {
    // error_log($e->getMessage());
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Gagal mengambil data pengguna.'];
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit();
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            
            <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-secondary mb-3">&larr; Kembali ke Daftar Pengguna</a>

            <h1 class="mb-4">Detail Pengguna</h1>
            
            <div class="card mb-4">
                <div class="card-header">
                    Informasi Pengguna
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nama:</strong><br>
                                <?= htmlspecialchars($user['nama']) ?>
                            </p>
                            <p><strong>Email:</strong><br>
                                <?= htmlspecialchars($user['email']) ?>
                            </p>
                            <p><strong>Role:</strong><br>
                                <span class="badge <?= $user['role'] == 'admin' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Telepon:</strong><br>
                                <?= htmlspecialchars($user['telepon'] ?? 'N/A') ?>
                            </p>
                            <p><strong>Alamat:</strong><br>
                                <?= nl2br(htmlspecialchars($user['alamat'] ?? 'N/A')) ?>
                            </p>
                            <p><strong>Bergabung:</strong><br>
                                <?= date('d M Y, H:i', strtotime($user['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $user['id'] ?>" class="btn btn-warning">Edit Pengguna</a>
                </div>
            </div>

            <h3 class="mb-3">Riwayat Pesanan (10 Terbaru)</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Tgl. Pesanan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pesanan_list)): ?>
                            <tr><td colspan="5" class="text-center">Pengguna ini belum memiliki pesanan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pesanan_list as $pesanan): ?>
                                <tr>
                                    <td><?= $pesanan['id'] ?></td>
                                    <td><?= date('d M Y', strtotime($pesanan['tgl_pesanan'])) ?></td>
                                    <td>Rp <?= number_format($pesanan['total']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($pesanan['status_pesanan']) ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/pesanan_detail.php?id=<?= $pesanan['id'] ?>" class="btn btn-info btn-sm">Lihat</a>
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