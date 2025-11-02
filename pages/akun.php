<?php
// Memanggil "penjaga gerbang" untuk memastikan pengguna sudah login.
require_once __DIR__ . '/../auth/user-auth.php';

// Kode di bawah ini hanya akan berjalan jika pengguna sudah login.
require_once __DIR__ . '/../config/koneksi.php';

$user_id = $_SESSION['user_id'];
$page_title = 'Akun Saya';

try {
    $user_stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch();
    $transaksi_stmt = db()->prepare("SELECT * FROM transaksi WHERE user_id = ? ORDER BY tanggal_transaksi DESC");
    $transaksi_stmt->execute([$user_id]);
    $transaksi_list = $transaksi_stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$title = $page_title;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Jejak Petualang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="main-wrapper d-flex flex-column">
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <main class="main-content py-5 flex-grow-1">
        <div class="container">
            <h1 class="text-white text-center mb-5 section-title">Akun Saya</h1>

            <?php if(isset($_SESSION['pesan_sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['pesan_sukses']; unset($_SESSION['pesan_sukses']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if(isset($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['pesan_error']; unset($_SESSION['pesan_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="list-group account-sidebar" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="list-group-item list-group-item-action active" id="v-pills-pesanan-tab" data-bs-toggle="pill" data-bs-target="#v-pills-pesanan" type="button" role="tab">Riwayat Pesanan</button>
                        <button class="list-group-item list-group-item-action" id="v-pills-profil-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profil" type="button" role="tab">Profil Saya</button>
                        <button class="list-group-item list-group-item-action" id="v-pills-password-tab" data-bs-toggle="pill" data-bs-target="#v-pills-password" type="button" role="tab">Ubah Password</button>
                        <a href="/jejakpetualang/auth/logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade show active account-content" id="v-pills-pesanan" role="tabpanel">
                            <h3>Riwayat Pesanan Anda</h3>
                            <div class="table-responsive">
                                <table class="table account-table">
                                    <thead>
                                        <tr>
                                            <th>Kode Pesanan</th>
                                            <th>Tanggal</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($transaksi_list)): ?>
                                            <tr><td colspan="5" class="text-center">Anda belum memiliki riwayat pesanan.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($transaksi_list as $transaksi): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($transaksi['kode_transaksi']) ?></strong></td>
                                                    <td><?= date('d M Y', strtotime($transaksi['tanggal_transaksi'])) ?></td>
                                                    <td>Rp <?= number_format($transaksi['total']) ?></td>
                                                    <td>
                                                        <?php
                                                            $status_class = 'bg-secondary';
                                                            if ($transaksi['status'] == 'Menunggu Pembayaran') $status_class = 'bg-warning text-dark';
                                                            elseif ($transaksi['status'] == 'Diproses') $status_class = 'bg-info text-dark';
                                                            elseif ($transaksi['status'] == 'Dikirim') $status_class = 'bg-primary';
                                                            elseif ($transaksi['status'] == 'Selesai') $status_class = 'bg-success';
                                                            elseif ($transaksi['status'] == 'Dibatalkan') $status_class = 'bg-danger';
                                                        ?>
                                                        <span class="badge <?= $status_class ?>"><?= htmlspecialchars($transaksi['status']) ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="/jejakpetualang/pages/pesanan_detail_user.php?id=<?= $transaksi['id'] ?>" class="btn btn-sm btn-light">Detail</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade account-content" id="v-pills-profil" role="tabpanel">
                            <h3>Profil Saya</h3>
                            <p>Lengkapi data diri Anda untuk mempercepat proses checkout.</p>
                            <hr>
                            <form action="update_profil.php" method="POST">
                                <?= csrf_field(); ?>
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Alamat Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($user['nomor_telepon'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="4"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </form>
                        </div>

                        <div class="tab-pane fade account-content" id="v-pills-password" role="tabpanel">
                            <h3>Ubah Password</h3>
                            <p>Untuk keamanan, ganti password Anda secara berkala.</p>
                            <hr>
                            <form action="update_password.php" method="POST">
                                <?= csrf_field(); ?>
                                <div class="mb-3">
                                    <label for="password_lama" class="form-label">Password Saat Ini</label>
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
                                <button type="submit" class="btn btn-primary">Ubah Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
