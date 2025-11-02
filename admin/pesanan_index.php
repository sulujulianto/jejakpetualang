<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses halaman ini.
require_once __DIR__ . '/auth.php';

// Menetapkan judul halaman yang akan ditampilkan di tab browser.
$page_title = 'Manajemen Pesanan';
// Menyertakan file header, yang berisi bagian awal dari struktur HTML, navigasi, dan pengecekan otentikasi.
include __DIR__ . '/partials/header.php';

// Menggunakan blok try-catch untuk menangani potensi error saat mengambil data dari database.
try {
    // Kueri SQL untuk mengambil data dari dua tabel sekaligus.
    // SELECT t.*, u.nama AS nama_pelanggan: Memilih semua kolom dari tabel 'transaksi' (diberi alias 't') dan kolom 'nama' dari tabel 'users' (diberi alias 'u'), kemudian mengubah nama kolom 'nama' menjadi 'nama_pelanggan'.
    // FROM transaksi t: Menentukan tabel utama adalah 'transaksi'.
    // JOIN users u ON t.user_id = u.id: Menggabungkan setiap baris dari tabel 'transaksi' dengan baris yang cocok di tabel 'users' berdasarkan 'user_id'. Ini dilakukan untuk mendapatkan nama pelanggan untuk setiap pesanan.
    // ORDER BY t.tanggal_transaksi DESC: Mengurutkan hasil berdasarkan tanggal transaksi, dari yang paling baru ke yang paling lama.
    $sql = "SELECT t.*, u.nama AS nama_pelanggan 
            FROM transaksi t 
            JOIN users u ON t.user_id = u.id 
            ORDER BY t.tanggal_transaksi DESC";
            
    // Menjalankan query dan mengambil semua hasilnya sebagai array asosiatif.
    $transaksi_list = db()->query($sql)->fetchAll();

// Jika terjadi error selama query (misalnya, tabel tidak ada atau koneksi gagal), blok catch akan menangkapnya.
} catch (PDOException $e) {
    // Menghentikan eksekusi skrip dan menampilkan pesan error yang jelas.
    die("Error mengambil data transaksi: " . $e->getMessage());
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Manajemen Pesanan</h1>

            <?php 
            // Memeriksa apakah ada pesan sukses di session (misalnya, setelah berhasil memperbarui status pesanan).
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
                            <th scope="col">Kode Transaksi</th>
                            <th scope="col">Pelanggan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Memeriksa apakah array $transaksi_list kosong.
                        if (empty($transaksi_list)): 
                        ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada pesanan yang masuk.</td>
                            </tr>
                        <?php else: // Jika ada data, lakukan perulangan untuk menampilkannya. ?>
                            <?php foreach ($transaksi_list as $transaksi): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($transaksi['kode_transaksi']); ?></strong></td>
                                    <td><?= htmlspecialchars($transaksi['nama_pelanggan']); ?></td>
                                    <td><?= date('d M Y, H:i', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                                    <td>Rp <?= number_format($transaksi['total'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php
                                            // Menentukan kelas CSS (warna badge) berdasarkan teks status pesanan.
                                            // Ini membuat tampilan lebih informatif secara visual.
                                            $status_class = 'bg-secondary'; // Warna default
                                            if ($transaksi['status'] == 'Menunggu Pembayaran') $status_class = 'bg-warning text-dark';
                                            if ($transaksi['status'] == 'Diproses') $status_class = 'bg-info text-dark';
                                            if ($transaksi['status'] == 'Dikirim') $status_class = 'bg-primary';
                                            if ($transaksi['status'] == 'Selesai') $status_class = 'bg-success';
                                            if ($transaksi['status'] == 'Dibatalkan') $status_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $status_class; ?>"><?= htmlspecialchars($transaksi['status']); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="pesanan_detail.php?id=<?= $transaksi['id']; ?>" class="btn btn-sm btn-light">Lihat Detail</a>
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
