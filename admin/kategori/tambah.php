<?php
// Menyertakan file konfigurasi database agar skrip ini dapat terhubung ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya pengguna yang berwenang yang dapat mengakses halaman ini.
require_once __DIR__ . '/../auth.php';

// Inisialisasi variabel error sebagai string kosong. Variabel ini akan diisi jika terjadi kesalahan validasi.
$error = '';
// Inisialisasi variabel untuk menyimpan kembali input pengguna jika terjadi error.
// Ini berguna agar pengguna tidak perlu mengetik ulang data yang sudah benar di form.
$nama_kategori_input = ''; 

// Memeriksa apakah permintaan ke halaman ini menggunakan metode POST.
// Ini menandakan bahwa pengguna telah mengirimkan (submit) form tambah kategori.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari input form dengan nama 'nama_kategori'.
    // trim() digunakan untuk menghapus spasi yang tidak perlu di awal dan akhir string.
    $nama_kategori = trim($_POST['nama_kategori']);
    // Simpan input asli pengguna ke dalam variabel, untuk ditampilkan kembali jika terjadi error.
    $nama_kategori_input = $nama_kategori; 

    // Memeriksa apakah nama kategori kosong setelah di-trim.
    if (empty($nama_kategori)) {
        // Jika kosong, atur pesan error.
        $error = "Nama kategori tidak boleh kosong.";
    } else {
        // Jika tidak kosong, lanjutkan ke proses interaksi dengan database.
        // Blok try-catch digunakan untuk menangani potensi error dari database (PDOException).
        try {
            // Langkah 1: Cek duplikasi. Pastikan tidak ada kategori dengan nama yang sama persis.
            // Mempersiapkan statement SQL untuk mencari kategori berdasarkan nama.
            $stmtCheck = db()->prepare("SELECT id FROM kategori WHERE nama_kategori = ?");
            // Menjalankan query dengan nama kategori yang diinput oleh pengguna.
            $stmtCheck->execute([$nama_kategori]);
            
            // fetch() akan mengembalikan data jika kategori ditemukan, dan false jika tidak.
            // Jika fetch() mengembalikan data, berarti nama tersebut sudah ada.
            if ($stmtCheck->fetch()) {
                 // Jika sudah ada, buat pesan error. htmlspecialchars() digunakan untuk keamanan saat menampilkan kembali input pengguna.
                $error = "Kategori dengan nama '" . htmlspecialchars($nama_kategori) . "' sudah ada.";
            } else {
                // Langkah 2: Jika nama belum ada, lakukan proses penyimpanan data baru.
                // Mempersiapkan statement SQL INSERT untuk menambahkan data baru ke tabel 'kategori'.
                $stmt = db()->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
                // Menjalankan query INSERT dengan data nama kategori.
                $stmt->execute([$nama_kategori]);
    
                // Setelah berhasil menyimpan, atur pesan sukses di session.
                // Session digunakan agar pesan bisa ditampilkan di halaman lain setelah redirect.
                $_SESSION['pesan_sukses'] = "Kategori baru berhasil ditambahkan.";
                // Alihkan (redirect) pengguna ke halaman utama manajemen kategori (index.php).
                header("Location: index.php");
                // Hentikan eksekusi skrip setelah redirect untuk memastikan tidak ada kode lain yang berjalan.
                exit();
            }
        // Blok catch akan menangkap error jika ada masalah pada koneksi atau query database.
        } catch (PDOException $e) {
            // Jika terjadi error database, simpan pesan error yang teknis untuk ditampilkan.
            $error = "Gagal menambahkan kategori: " . $e->getMessage();
        }
    }
}

// Menetapkan judul halaman untuk tag <title> di HTML.
$page_title = 'Tambah Kategori';
// Menyertakan file header.php, yang berisi bagian atas dari layout HTML.
include __DIR__ . '/../partials/header.php';
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Tambah Kategori Baru</h1>
            <p>Isi form di bawah untuk menambahkan kategori baru.</p>

            <?php 
            // Memeriksa apakah variabel $error tidak kosong.
            if (!empty($error)): 
            ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; // Akhir dari blok if ?>

            <form action="tambah.php" method="POST">
                <div class="mb-3">
                    <label for="nama_kategori" class="form-label">Nama Kategori</label>
                    <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= htmlspecialchars($nama_kategori_input); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>