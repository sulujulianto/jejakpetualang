<?php
// CATATAN: File ini untuk menampilkan DETAIL SATU pesanan dengan form update status yang benar.

// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';

// --- Pengambilan dan Validasi ID Transaksi ---
// Mengambil 'id' dari URL. (int) digunakan untuk mengubah nilainya menjadi integer sebagai langkah keamanan.
// Jika tidak ada 'id', nilainya akan menjadi 0.
$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Jika ID transaksi adalah 0 (artinya tidak valid atau tidak ada), alihkan pengguna kembali ke daftar pesanan.
if ($transaksi_id === 0) {
    header('Location: /jejak-petualang/admin/pesanan_index.php');
    exit(); // Hentikan eksekusi skrip.
}

// Menggunakan blok try-catch untuk menangani semua operasi database.
try {
    // --- Langkah 1: Mengambil Data Utama Transaksi dan Data Pengguna ---
    // Query ini menggabungkan (JOIN) tabel 'transaksi' dan 'users' untuk mendapatkan detail pesanan sekaligus info pelanggan dalam satu kali jalan.
    $sql_transaksi = "
        SELECT t.*, u.nama as nama_pelanggan, u.email, u.nomor_telepon 
        FROM transaksi t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.id = ?
    ";
    // Mempersiapkan dan menjalankan query dengan ID transaksi yang aman.
    $stmt_transaksi = db()->prepare($sql_transaksi);
    $stmt_transaksi->execute([$transaksi_id]);
    // Mengambil hasilnya sebagai satu baris array asosiatif.
    $transaksi = $stmt_transaksi->fetch();

    // Jika $transaksi bernilai false, artinya pesanan dengan ID tersebut tidak ditemukan.
    if (!$transaksi) {
        // Atur pesan error di session untuk ditampilkan di halaman daftar pesanan.
        $_SESSION['pesan'] = ['jenis' => 'error', 'isi' => 'Pesanan tidak ditemukan.'];
        header('Location: /jejak-petualang/admin/pesanan_index.php');
        exit(); // Hentikan skrip.
    }

    // --- Langkah 2: Mengambil Item-item yang Dipesan dalam Transaksi Ini ---
    // Query ini menggabungkan tabel 'transaksi_item' dan 'produk' untuk mendapatkan detail setiap produk yang dipesan.
    $sql_item = "
        SELECT ti.*, p.nama as nama_produk, p.gambar 
        FROM transaksi_item ti 
        JOIN produk p ON ti.produk_id = p.id 
        WHERE ti.transaksi_id = ?
    ";
    // Mempersiapkan dan menjalankan query.
    $stmt_item = db()->prepare($sql_item);
    $stmt_item->execute([$transaksi_id]);
    // Mengambil semua item yang cocok sebagai array.
    $item_transaksi = $stmt_item->fetchAll();

// Jika ada error pada salah satu query di atas, blok catch akan dijalankan.
} catch (PDOException $e) {
    die("Error mengambil data detail pesanan: " . $e->getMessage());
}

// Menetapkan judul halaman secara dinamis dengan kode transaksi.
$page_title = 'Detail Pesanan #' . $transaksi['kode_transaksi'];
// Memanggil file header admin yang sudah berisi otentikasi dan layout atas.
include_once __DIR__ . '/partials/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Pesanan <span class="text-primary">#<?= htmlspecialchars($transaksi['kode_transaksi']) ?></span></h1>
    
    <a href="/jejak-petualang/admin/pesanan_index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
    </a>
    
    <?php 
    // Menampilkan pesan notifikasi (sukses atau error) jika ada di session.
    if(isset($_SESSION['pesan'])): 
    ?>
        <div class="alert alert-<?= $_SESSION['pesan']['jenis'] == 'error' ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($_SESSION['pesan']['isi']) ?>
        </div>
        <?php unset($_SESSION['pesan']); // Hapus pesan setelah ditampilkan. ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-dark text-white"><i class="fas fa-user me-1"></i>Informasi Pelanggan & Pengiriman</div>
                <div class="card-body">
                    <h5>Pelanggan</h5>
                    <p>
                        <strong>Nama:</strong> <?= htmlspecialchars($transaksi['nama_pelanggan']) ?><br>
                        <strong>Email:</strong> <?= htmlspecialchars($transaksi['email']) ?><br>
                        <strong>No. Telepon:</strong> <?= htmlspecialchars($transaksi['nomor_telepon']) ?>
                    </p>
                    <hr>
                    <h5>Alamat Pengiriman</h5>
                    <p><?= nl2br(htmlspecialchars($transaksi['alamat_pengiriman'])) ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-dark text-white"><i class="fas fa-info-circle me-1"></i>Status & Total</div>
                <div class="card-body">
                    <p><strong>Tanggal Pesan:</strong> <?= date('d M Y, H:i', strtotime($transaksi['tanggal_transaksi'])) ?></p>
                    
                    <form action="/jejak-petualang/admin/pesanan_update_status.php" method="POST">
                        <input type="hidden" name="transaksi_id" value="<?= $transaksi['id'] ?>">
                        <div class="mb-3">
                            <label for="status_pesanan" class="form-label"><strong>Ubah Status Pesanan:</strong></label>
                            <select name="status" id="status_pesanan" class="form-select">
                                <?php 
                                // Daftar pilihan status. Sebaiknya daftar ini sama dengan tipe ENUM di database.
                                $status_options = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
                                // Looping untuk membuat setiap pilihan <option>.
                                foreach ($status_options as $status) {
                                    // Jika status dari database cocok dengan status di dalam loop, tambahkan atribut 'selected'.
                                    $selected = ($transaksi['status'] == $status) ? 'selected' : '';
                                    echo "<option value=\"$status\" $selected>$status</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white"><i class="fas fa-list-ul me-1"></i>Item yang Dipesan</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th colspan="2">Produk</th>
                        <th>Ukuran</th>
                        <th class="text-end">Harga</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($item_transaksi as $item): ?>
                    <tr>
                        <td style="width: 70px;"><img src="/jejak-petualang/uploads/produk/<?= htmlspecialchars($item['gambar']) ?>" width="50" class="rounded"></td>
                        <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                        <td><?= htmlspecialchars($item['ukuran']) ?></td>
                        <td class="text-end">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['jumlah']) ?></td>
                        <td class="text-end">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                 <tfoot>
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Total Belanja</td>
                        <td class="text-end">Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
// Penutup tag dari layout utama.
?>
</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>