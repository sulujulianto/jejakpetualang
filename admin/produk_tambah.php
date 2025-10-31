<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses halaman ini.
require_once __DIR__ . '/auth.php';

// --- Inisialisasi Variabel ---
// Inisialisasi variabel error sebagai string kosong.
$error = '';
// Inisialisasi semua variabel input form sebagai string kosong.
// Ini berguna untuk "sticky form", yaitu menjaga nilai input tetap ada jika terjadi error validasi.
$nama = ''; $kategori_id = ''; $deskripsi = ''; $harga = ''; $stok = ''; $ukuran = '';

// --- Pengambilan Data Awal ---
// Mengambil semua data kategori dari database untuk ditampilkan di dropdown form.
try {
    $kategori = db()->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
} catch (PDOException $e) {
    // Jika gagal mengambil data, hentikan skrip dan tampilkan pesan error.
    die("Error mengambil data kategori: " . $e->getMessage());
}

// --- Memproses Data Saat Form Disubmit ---
// Memeriksa apakah permintaan (request) menggunakan metode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Langkah 1: Sanitasi dan Ambil Data dari Form ---
    // Mengambil dan membersihkan data input. `trim` menghapus spasi di awal dan akhir.
    $nama = trim($_POST['nama']);
    // Mengambil dan memvalidasi `kategori_id` sebagai integer.
    $kategori_id = filter_input(INPUT_POST, 'kategori_id', FILTER_VALIDATE_INT);
    $deskripsi = trim($_POST['deskripsi']);
    // Mengambil dan memvalidasi `harga` dan `stok` sebagai integer.
    $harga = filter_var($_POST['harga'], FILTER_VALIDATE_INT);
    $stok = filter_var($_POST['stok'], FILTER_VALIDATE_INT);
    $ukuran = trim($_POST['ukuran']);

    // --- Langkah 2: Validasi Data ---
    // Cek apakah field-field penting kosong atau tidak valid.
    if (empty($nama) || !$kategori_id || $harga === false || $stok === false) {
        $error = "Semua field wajib diisi.";
    // Cek apakah gambar tidak diupload atau terjadi error saat upload.
    } elseif (!isset($_FILES["gambar"]) || $_FILES["gambar"]["error"] !== 0) {
        $error = "Gambar produk wajib diupload.";
    } else {
        // --- Langkah 3: Proses Upload Gambar (Jika Validasi Data Lolos) ---
        // Tentukan direktori tujuan.
        $target_dir = __DIR__ . "/../uploads/produk/";
        // Buat direktori jika belum ada. '@' menekan pesan error jika direktori sudah ada.
        if (!is_dir($target_dir)) @mkdir($target_dir, 0755, true);
        // Dapatkan ekstensi file (misal: "jpg", "png").
        $imageFileType = strtolower(pathinfo(basename($_FILES["gambar"]["name"]), PATHINFO_EXTENSION));
        // Buat nama file baru yang unik.
        $nama_file = uniqid('produk_') . '.' . $imageFileType;
        // Tentukan path lengkap file tujuan.
        $target_path = $target_dir . $nama_file;
        // Variabel flag untuk status upload.
        $uploadOk = 1;

        // Validasi file gambar:
        // Cek apakah file benar-benar gambar.
        if (getimagesize($_FILES["gambar"]["tmp_name"]) === false) {
            $error = "File yang diupload bukan gambar."; $uploadOk = 0;
        // Cek apakah ekstensi file diizinkan.
        } elseif (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            $error = "Hanya format JPG, JPEG, PNG & GIF yang diizinkan."; $uploadOk = 0;
        // Coba pindahkan file ke direktori tujuan.
        } elseif (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_path)) {
            $error = "Error saat mengupload file."; $uploadOk = 0;
        }

        // --- Langkah 4: Simpan ke Database (Jika Upload Gambar Berhasil) ---
        // Cek jika status upload OK dan tidak ada pesan error.
        if ($uploadOk == 1 && empty($error)) {
            try {
                // Persiapkan statement SQL INSERT.
                $stmt = db()->prepare(
                    "INSERT INTO produk (nama, kategori_id, deskripsi, harga, stok, ukuran, gambar, ketersediaan_stok) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                // Tentukan status ketersediaan stok berdasarkan jumlah stok.
                $ketersediaan_stok = ($stok > 0) ? 'tersedia' : 'habis';
                // Eksekusi query dengan semua data yang sudah divalidasi.
                $stmt->execute([$nama, $kategori_id, $deskripsi, $harga, $stok, $ukuran, $nama_file, $ketersediaan_stok]);
                // Atur pesan sukses dan alihkan ke halaman daftar produk.
                $_SESSION['pesan_sukses'] = "Produk baru berhasil ditambahkan.";
                header("Location: /jejakpetualang/admin/produk/index.php");
                exit();
            } catch (PDOException $e) {
                // Jika penyimpanan ke database gagal, hapus file yang sudah terlanjur di-upload.
                if (file_exists($target_path)) unlink($target_path);
                // Simpan pesan error untuk ditampilkan.
                $error = "Gagal menyimpan produk: " . $e->getMessage();
            }
        }
    }
}

// Tetapkan judul halaman.
$page_title = 'Tambah Produk';
// Sertakan file header.
include __DIR__ . '/partials/header.php';
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Tambah Produk Baru</h1>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>
            <form action="produk_tambah.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategori as $kat): ?>
                            <option value="<?= $kat['id']; ?>" <?= ($kategori_id == $kat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi Produk</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="10"><?= htmlspecialchars($deskripsi) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <input type="number" class="form-control" id="harga" name="harga" value="<?= htmlspecialchars($harga) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($stok) ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="ukuran" class="form-label">Ukuran Tersedia (opsional)</label>
                    <input type="text" class="form-control" id="ukuran" name="ukuran" value="<?= htmlspecialchars($ukuran) ?>">
                    <div class="form-text text-white-50">Pisahkan setiap ukuran dengan koma. Contoh: S,M,L,XL</div>
                </div>
                <div class="mb-3">
                    <label for="gambar" class="form-label">Foto Produk</label>
                    <input class="form-control" type="file" id="gambar" name="gambar" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Produk</button>
                <a href="/jejakpetualang/admin/produk/index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</main>
<script src="/jejakpetualang/public/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  // Inisialisasi TinyMCE pada textarea dengan id 'deskripsi'.
  tinymce.init({
    selector: 'textarea#deskripsi',
    plugins: 'lists link image code help wordcount',
    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image | code | help',
    license_key: 'gpl'
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>