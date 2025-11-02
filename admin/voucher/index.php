<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses halaman ini.
require_once __DIR__ . '/../auth.php';

// Menetapkan judul halaman yang akan ditampilkan di tab browser.
$page_title = 'Manajemen Voucher';
// Menyertakan file header, yang berisi bagian awal dari struktur HTML dan navigasi.
include __DIR__ . '/../partials/header.php';

// Menggunakan blok try-catch untuk menangani potensi error saat mengambil data dari database.
try {
    // Menjalankan query SQL untuk mengambil semua data (*) dari tabel 'vouchers'.
    // "ORDER BY tanggal_berakhir DESC" mengurutkan hasil berdasarkan tanggal berakhir, dari yang paling baru ke yang paling lama.
    // fetchAll() mengambil semua baris hasil query dan menyimpannya sebagai array ke dalam variabel $vouchers.
    $vouchers = db()->query("SELECT * FROM vouchers ORDER BY tanggal_berakhir DESC")->fetchAll();
// Jika terjadi error selama query, blok catch akan menangkapnya.
} catch (PDOException $e) {
    // Menghentikan eksekusi skrip dan menampilkan pesan error yang jelas.
    die("Error: " . $e->getMessage());
}
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Manajemen Voucher</h1>
                <a href="tambah.php" class="btn btn-primary">Tambah Voucher</a>
            </div>

            <?php 
            // Memeriksa apakah ada pesan sukses di session (misalnya, setelah berhasil menambah/mengedit voucher).
            if (isset($_SESSION['pesan_sukses'])): 
            ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']); unset($_SESSION['pesan_sukses']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php 
            // Memeriksa apakah ada pesan error di session.
            if (isset($_SESSION['pesan_error'])): 
            ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['pesan_error']); unset($_SESSION['pesan_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Kode</th>
                            <th>Diskon</th>
                            <th>Min. Belanja</th>
                            <th>Kuota</th>
                            <th>Berlaku Hingga</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Memeriksa apakah array $vouchers kosong.
                        if(empty($vouchers)): 
                        ?>
                            <tr><td colspan="7" class="text-center">Belum ada voucher yang dibuat.</td></tr>
                        <?php else: // Jika ada data voucher, lakukan perulangan untuk menampilkannya. ?>
                            <?php foreach ($vouchers as $voucher): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($voucher['kode_voucher']) ?></strong></td>
                                    <td>
                                        <?php 
                                        // Memeriksa jenis diskon voucher.
                                        if ($voucher['jenis_diskon'] == 'persen'): 
                                        ?>
                                            <?= (int)$voucher['nilai_diskon'] ?>%
                                        <?php else: ?>
                                            Rp <?= number_format($voucher['nilai_diskon']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>Rp <?= number_format($voucher['minimal_pembelian']) ?></td>
                                    <td><?= $voucher['kuota'] ?></td>
                                    <td><?= date('d M Y', strtotime($voucher['tanggal_berakhir'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $voucher['status'] == 'aktif' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($voucher['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="edit.php?id=<?= $voucher['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="hapus.php?id=<?= $voucher['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin ingin menghapus voucher ini?');">Hapus</a>
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
