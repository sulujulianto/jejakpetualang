<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses halaman ini.
require_once __DIR__ . '/../auth.php';

// Menetapkan judul halaman yang akan ditampilkan di tab browser.
$page_title = 'Manajemen Pengguna';
// Menyertakan file header, yang berisi bagian awal dari struktur HTML dan navigasi.
include __DIR__ . '/../partials/header.php';

// Menggunakan blok try-catch untuk menangani potensi error saat mengambil data dari database.
try {
    // Menjalankan query SQL untuk mengambil data spesifik (id, nama, email, role) dari tabel 'users'.
    // "ORDER BY id ASC" mengurutkan hasil berdasarkan ID dari yang terkecil ke terbesar.
    // fetchAll() mengambil semua baris hasil query dan menyimpannya sebagai array ke dalam variabel $users.
    $users = db()->query("SELECT id, nama, email, role FROM users ORDER BY id ASC")->fetchAll();
// Jika terjadi error selama query, blok catch akan menangkapnya.
} catch (PDOException $e) {
    // Menghentikan eksekusi skrip dan menampilkan pesan error yang jelas.
    die("Error: " . $e->getMessage());
}
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Manajemen Pengguna</h1>
            <p>Berikut adalah daftar semua pengguna yang terdaftar di website Anda.</p>
            
            <?php 
            // Memeriksa apakah ada pesan sukses di session (misalnya, setelah menghapus atau mengedit pengguna).
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
                            <th>ID</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Memeriksa apakah array $users kosong.
                        if (empty($users)): 
                        ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada pengguna yang terdaftar.</td>
                            </tr>
                        <?php else: // Jika ada data pengguna, lakukan perulangan. ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><a href="detail.php?id=<?= $user['id'] ?>"><?= htmlspecialchars($user['nama']) ?></a></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php 
                                        // Memeriksa apakah role pengguna adalah 'admin'.
                                        if($user['role'] == 'admin'): 
                                        ?>
                                            <span class="badge bg-success"><?= ucfirst($user['role']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($user['role']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="detail.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info">Preview</a>
                                        <?php 
                                        // Kondisi untuk menyembunyikan tombol edit dan hapus untuk admin.
                                        // Ini adalah tindakan pengamanan sederhana agar admin tidak bisa menghapus atau mengubah role dirinya sendiri dari daftar ini.
                                        if ($user['role'] !== 'admin'): 
                                        ?>
                                            <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit Role</a>
                                            <a href="hapus.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin ingin menghapus pengguna ini?');">Hapus</a>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>