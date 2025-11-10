<?php
// CATATAN: Ini adalah file "view" untuk Halaman Akun Saya.
// (Variabel $user dan $pesanan_list sudah disiapkan oleh 'controller' akun.php)

// 1. Memanggil helper CSRF
// Kita panggil di sini agar fungsi csrf_field() tersedia untuk form.
require_once __DIR__ . '/../../helpers/csrf.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="fw-bold">Akun Saya</h1>
            <p class="lead">Selamat datang, <?= htmlspecialchars($user['nama']) ?>!</p>
        </div>
    </div>

    <?php if (isset($_SESSION['pesan'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['pesan']['jenis']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['pesan']['isi']); unset($_SESSION['pesan']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Update Profil</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/pages/update_profil.php" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="telepon" class="form-label">Telepon</label>
                            <input type="tel" class="form-control" id="telepon" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Profil</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Ubah Password</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/pages/update_password.php" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="password_lama" class="form-label">Password Lama</label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                        </div>
                        <div class="mb-3">
                            <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Ubah Password</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Pesanan Anda</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pesanan_list)): ?>
                        <p class="text-center text-muted">Anda belum memiliki riwayat pesanan.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pesanan_list as $pesanan): ?>
                                        <tr>
                                            <td>#<?= $pesanan['id'] ?></td>
                                            <td><?= date('d M Y', strtotime($pesanan['tgl_pesanan'])) ?></td>
                                            <td>Rp <?= number_format($pesanan['total']) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= htmlspecialchars($pesanan['status_pesanan']) ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/pages/pesanan_detail_user.php?id=<?= $pesanan['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>