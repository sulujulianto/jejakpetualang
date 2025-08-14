<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses halaman ini.
require_once __DIR__ . '/auth.php';

// Inisialisasi variabel error sebagai string kosong.
$error = '';
// --- Langkah 1: Pengambilan dan Validasi ID Produk dari URL ---
// Mengambil ID dari URL (contoh: produk_edit.php?id=5).
$id = $_GET['id'] ?? null;
// Validasi ID: jika ID tidak ada, atau bukan integer yang valid, alihkan ke dashboard.
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    header("Location: /jejakpetualang/admin/dashboard.php");
    exit();
}

// --- Langkah 2: Pengambilan Data Awal dari Database ---
// Menggunakan try-catch untuk menangani error koneksi atau query.
try {
    // Mempersiapkan query untuk mengambil semua data produk berdasarkan ID.
    $stmt = db()->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->execute([$id]);
    // Mengambil data produk sebagai array asosiatif.
    $produk = $stmt->fetch(PDO::FETCH_ASSOC);
    // Jika tidak ada produk yang ditemukan dengan ID tersebut, alihkan ke dashboard.
    if (!$produk) {
        header("Location: /jejakpetualang/admin/dashboard.php");
        exit();
    }
    // Mengambil semua data kategori untuk ditampilkan di dropdown form.
    $kategori = db()->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
} catch (PDOException $e) {
    // Jika terjadi error, hentikan skrip dan tampilkan pesan.
    die("Error mengambil data: " . $e->getMessage());
}

// --- Langkah 3: Memproses Data Saat Form Disubmit ---
// Memeriksa apakah permintaan (request) menggunakan metode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Langkah 3a: Validasi dan Sanitasi Input dari Form ---
    $nama = trim($_POST['nama']);
    // Mengambil dan memvalidasi kategori_id sebagai integer.
    $kategori_id = filter_input(INPUT_POST, 'kategori_id', FILTER_VALIDATE_INT);
    $deskripsi = trim($_POST['deskripsi']);
    // Mengambil dan memvalidasi harga dan stok sebagai integer.
    $harga = filter_var($_POST['harga'], FILTER_VALIDATE_INT);
    $stok = filter_var($_POST['stok'], FILTER_VALIDATE_INT);
    $ukuran = trim($_POST['ukuran']);
    // Mengambil nama file gambar lama dari input tersembunyi.
    $gambar_lama = $_POST['gambar_lama'];
    
    // Memperbarui variabel $produk dengan data baru dari form.
    // Ini berguna agar jika terjadi error, form akan menampilkan data yang baru diinput, bukan data lama.
    $produk['nama'] = $nama;
    $produk['kategori_id'] = $kategori_id;
    $produk['deskripsi'] = $deskripsi;
    $produk['harga'] = $harga;
    $produk['stok'] = $stok;
    $produk['ukuran'] = $ukuran;
    
    // Inisialisasi nama file baru dengan nama file lama.
    $nama_file_baru = $gambar_lama;
    // Variabel flag untuk menandakan apakah proses boleh lanjut atau tidak.
    $uploadOk = 1;

    // Pengecekan dasar: pastikan field-field penting tidak kosong.
    if (empty($nama) || !$kategori_id || $harga === false || $stok === false) {
        $error = "Semua field wajib diisi.";
        $uploadOk = 0; // Jika tidak lengkap, batalkan proses.
    }

    // --- Langkah 3b: Proses Upload Gambar Baru (Jika Ada) ---
    // Cek jika proses masih OK dan ada file gambar yang di-upload tanpa error.
    if ($uploadOk == 1 && isset($_FILES["gambar"]) && $_FILES["gambar"]["error"] == 0) {
        // Tentukan direktori tujuan untuk menyimpan file.
        $target_dir = __DIR__ . "/../uploads/produk/";
        // Buat direktori jika belum ada. '@' menekan pesan error jika direktori sudah ada.
        if (!is_dir($target_dir)) @mkdir($target_dir, 0755, true);
        // Dapatkan ekstensi file (misal: "jpg", "png").
        $imageFileType = strtolower(pathinfo(basename($_FILES["gambar"]["name"]), PATHINFO_EXTENSION));
        // Buat nama file baru yang unik untuk menghindari penimpaan file.
        $nama_file_baru = uniqid('produk_') . '.' . $imageFileType;
        // Tentukan path lengkap untuk menyimpan file baru.
        $target_path = $target_dir . $nama_file_baru;
        // Pindahkan file dari lokasi sementara ke direktori tujuan.
        if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_path)) {
            $error = "Error saat mengupload file baru."; $uploadOk = 0; // Jika gagal, batalkan proses.
        }
    }

    // --- Langkah 3c: Update Data ke Database ---
    // Cek jika proses masih OK dan tidak ada pesan error.
    if ($uploadOk == 1 && empty($error)) {
        try {
            // Tentukan status ketersediaan berdasarkan jumlah stok.
            $ketersediaan_stok = ($stok > 0) ? 'tersedia' : 'habis';
            // Persiapkan statement SQL UPDATE.
            $stmt = db()->prepare(
                "UPDATE produk SET nama=?, kategori_id=?, deskripsi=?, harga=?, stok=?, ukuran=?, gambar=?, ketersediaan_stok=? WHERE id=?"
            );
            // Eksekusi query dengan semua data baru.
            $stmt->execute([$nama, $kategori_id, $deskripsi, $harga, $stok, $ukuran, $nama_file_baru, $ketersediaan_stok, $id]);
            
            // Jika ada file baru yang di-upload dan berbeda dari yang lama, hapus file lama untuk menghemat ruang.
            if ($nama_file_baru != $gambar_lama && !empty($gambar_lama) && file_exists(__DIR__ . "/../uploads/produk/" . $gambar_lama)) {
                @unlink(__DIR__ . "/../uploads/produk/" . $gambar_lama); // Hapus file lama. '@' menekan error jika file tidak ada.
            }
            // Atur pesan sukses dan alihkan ke halaman daftar produk.
            $_SESSION['pesan_sukses'] = "Produk berhasil diperbarui.";
            header("Location: /jejakpetualang/admin/produk/index.php");
            exit();
        } catch (PDOException $e) {
            // Jika terjadi error database, simpan pesan error untuk ditampilkan.
            $error = "Gagal memperbarui produk: " . $e->getMessage();
        }
    }
}
// Tetapkan judul halaman.
$page_title = 'Edit Produk';
// Sertakan file header.
include __DIR__ . '/partials/header.php';
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Edit Produk: <?= htmlspecialchars($produk['nama']); ?></h1>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>
            <form action="produk_edit.php?id=<?= $id; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($produk['gambar']); ?>">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($produk['nama']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id" required>
                        <?php foreach ($kategori as $kat): ?>
                            <option value="<?= $kat['id']; ?>" <?= ($kat['id'] == $produk['kategori_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi Produk</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="10"><?= htmlspecialchars($produk['deskripsi']); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <input type="number" class="form-control" id="harga" name="harga" value="<?= htmlspecialchars($produk['harga']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($produk['stok']); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="ukuran" class="form-label">Ukuran Tersedia (opsional)</label>
                    <input type="text" class="form-control" id="ukuran" name="ukuran" value="<?= htmlspecialchars($produk['ukuran']); ?>">
                    <div class="form-text text-white-50">Pisahkan setiap ukuran dengan koma. Contoh: S,M,L,XL</div>
                </div>
                <div class="mb-3">
                    <label for="gambar" class="form-label">Ganti Foto Produk (Opsional)</label>
                    <input class="form-control" type="file" id="gambar" name="gambar">
                    <div class="form-text text-white-50">Biarkan kosong jika tidak ingin mengganti.</div>
                    <?php if ($produk['gambar']): ?>
                        <img src="/jejakpetualang/uploads/produk/<?= htmlspecialchars($produk['gambar']); ?>" alt="Foto saat ini" class="img-thumbnail mt-2" width="150">
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
    license_key: 'gpl' // Menggunakan lisensi GPL.
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>