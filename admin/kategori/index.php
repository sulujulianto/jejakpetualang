<?php
// Menyertakan file konfigurasi database. 'require_once' memastikan file ini hanya disertakan satu kali.
// __DIR__ mengambil direktori dari file saat ini, membuatnya lebih andal saat menemukan file 'koneksi.php'.
require_once __DIR__ . '/../../config/koneksi.php';

// Menyertakan file otentikasi untuk memastikan pengguna yang mengakses halaman ini sudah login atau memiliki izin.
require_once __DIR__ . '/../auth.php';

// Menetapkan judul halaman yang akan ditampilkan di tag <title> HTML.
$page_title = 'Manajemen Kategori';
// Menyertakan file header, yang biasanya berisi bagian awal HTML, <head>, dan navigasi.
include __DIR__ . '/../partials/header.php';

// Menggunakan blok try-catch untuk menangani potensi error saat berinteraksi dengan database.
try {
    // Menyiapkan query SQL yang kompleks untuk mengambil data.
    // SELECT k.id, k.nama_kategori: Memilih ID dan nama dari tabel kategori (diberi alias 'k').
    // COUNT(p.id) AS jumlah_produk: Menghitung jumlah produk (diberi alias 'p') untuk setiap kategori dan menamainya 'jumlah_produk'.
    // FROM kategori k: Tabel utama adalah 'kategori'.
    // LEFT JOIN produk p ON k.id = p.kategori_id: Menggabungkan tabel kategori dengan tabel produk. LEFT JOIN digunakan agar semua kategori tetap ditampilkan, bahkan yang belum memiliki produk (jumlah_produk akan menjadi 0).
    // GROUP BY k.id, k.nama_kategori: Mengelompokkan hasil berdasarkan ID dan nama kategori, agar fungsi COUNT() bekerja dengan benar untuk setiap kategori.
    // ORDER BY k.nama_kategori ASC: Mengurutkan hasil berdasarkan nama kategori secara menaik (A-Z).
    $sql = "SELECT k.id, k.nama_kategori, COUNT(p.id) AS jumlah_produk
            FROM kategori k
            LEFT JOIN produk p ON k.id = p.kategori_id
            GROUP BY k.id, k.nama_kategori
            ORDER BY k.nama_kategori ASC";
    
    // Menjalankan query SQL dan mengambil semua hasilnya sebagai array asosiatif.
    // db() adalah fungsi dari file koneksi.php yang mengembalikan objek koneksi database (PDO).
    // ->query($sql) mengeksekusi query.
    // ->fetchAll() mengambil semua baris hasil dan menyimpannya ke dalam variabel $kategori.
    $kategori = db()->query($sql)->fetchAll();

// Jika terjadi error selama eksekusi query (misalnya, tabel tidak ada), blok catch akan menangkapnya.
} catch (PDOException $e) {
    // Menghentikan eksekusi skrip dan menampilkan pesan error yang jelas.
    die("Error: " . $e->getMessage());
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Manajemen Kategori</h1>
                <a href="tambah.php" class="btn btn-primary">Tambah Kategori Baru</a>
            </div>

            <?php 
            // Memeriksa apakah ada 'pesan_sukses' di dalam session.
            // Session ini biasanya diatur di halaman lain (misal: setelah berhasil menambah/mengedit/menghapus data).
            if (isset($_SESSION['pesan_sukses'])): 
            ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    // Menampilkan pesan. htmlspecialchars() digunakan untuk mencegah serangan XSS.
                    echo htmlspecialchars($_SESSION['pesan_sukses']); 
                    // Menghapus pesan dari session setelah ditampilkan agar tidak muncul lagi jika halaman di-refresh.
                    unset($_SESSION['pesan_sukses']); 
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; // Akhir dari blok if ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nama Kategori</th>
                            <th>Jumlah Produk</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Memeriksa apakah variabel $kategori kosong (artinya tidak ada data dari database).
                        if (empty($kategori)): 
                        ?>
                            <tr><td colspan="4" class="text-center">Belum ada data kategori.</td></tr>
                        <?php else: // Jika ada data ?>
                            <?php 
                            // Melakukan perulangan (looping) untuk setiap item di dalam array $kategori.
                            // $index akan berisi indeks (0, 1, 2, ...) dan $item akan berisi data per baris (array asosiatif).
                            foreach ($kategori as $index => $item): 
                            ?>
                                <tr>
                                    <th scope="row"><?= $index + 1; ?></th>
                                    <td><?= htmlspecialchars($item['nama_kategori']); ?></td>
                                    <td><?= $item['jumlah_produk']; ?></td>
                                    <td class="text-end">
                                        <a href="/jejakpetualang/admin/produk/index.php?kategori=<?= $item['id']; ?>" class="btn btn-sm btn-info">Lihat Produk</a>
                                        <a href="edit.php?id=<?= $item['id']; ?>" class="btn btn-sm btn-warning">Edit Kategori</a>
                                        <a href="hapus.php?id=<?= $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">Hapus Kategori</a>
                                    </td>
                                </tr>
                            <?php endforeach; // Akhir dari perulangan foreach ?>
                        <?php endif; // Akhir dari blok if-else ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
