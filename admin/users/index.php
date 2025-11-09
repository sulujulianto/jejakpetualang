<?php
// 1. Memanggil file konfigurasi
require_once __DIR__ . '/../../config/koneksi.php';

// 2. Memanggil file header (termasuk auth.php)
$page_title = 'Manajemen Pengguna';
include __DIR__ . '/../partials/header.php';

// 3. Logika untuk mengambil data
try {
    // Ambil semua user, diurutkan berdasarkan role (admin dulu) lalu nama
    $stmt = db()->query("SELECT * FROM users ORDER BY role ASC, nama ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    // error_log($e->getMessage());
    echo "<div class='container py-5'><div class='alert alert-danger'>Gagal mengambil data pengguna.</div></div>";
    $users = []; // Kosongkan list
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Manajemen Pengguna</h1>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tgl. Bergabung</th>
                            <th class="text-end" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data pengguna.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <th scope="row"><?= $index + 1 ?></th>
                                    
                                    <td><?= htmlspecialchars($user['nama']) ?></td>
                                    
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    
                                    <td>
                                        <span class="badge <?= $user['role'] == 'admin' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    
                                    <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                    
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/admin/users/detail.php?id=<?= (int)$user['id'] ?>" class="btn btn-info btn-sm">Detail</a>
                                        <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= (int)$user['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        
                                        <?php if ($_SESSION['user_id'] != $user['id']): ?>
                                            <a href="<?= BASE_URL ?>/admin/users/hapus.php?id=<?= (int)$user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pengguna ini? Semua pesanan terkait akan ikut terhapus.')">Hapus</a>
                                        <?php endif; ?>
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