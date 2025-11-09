<?php
require_once __DIR__ . '/../config/koneksi.php';
$page_title = 'Tambah Produk';
include __DIR__ . '/partials/header.php'; // (Termasuk auth.php)

try {
    $kategori_options = db()->query("SELECT id, nama FROM kategori ORDER BY nama")->fetchAll();
} catch (PDOException $e) {
    $kategori_options = [];
    $errors[] = "Gagal memuat data kategori.";
}

$errors = [];
$nama = '';
$kategori_id = '';
$harga = '';
$stok = '';
$deskripsi = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $harga = $_POST['harga'] ?? '';
    $stok = $_POST['stok'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $gambar = $_FILES['gambar'] ?? null;
    $gambar_path_untuk_db = null; 

    if (empty($nama)) $errors[] = 'Nama produk wajib diisi.';
    if (empty($kategori_id)) $errors[] = 'Kategori wajib dipilih.';
    if (empty($harga) || !is_numeric($harga) || $harga < 0) $errors[] = 'Harga tidak valid.';
    if (empty($stok) || !is_numeric($stok) || $stok < 0) $errors[] = 'Stok tidak valid.';
    
    // --- Logika Upload Gambar (Tidak berubah) ---
    if ($gambar && $gambar['error'] == UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../public/uploads/produk/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $extension = pathinfo($gambar['name'], PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array(strtolower($extension), $allowed_ext)) {
            if ($gambar['size'] <= 5 * 1024 * 1024) { // Maks 5MB
                $filename = uniqid('produk_', true) . '.' . $extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($gambar['tmp_name'], $upload_path)) {
                    $gambar_path_untuk_db = 'public/uploads/produk/' . $filename;
                } else {
                    $errors[] = "Gagal memindahkan file yang di-upload.";
                }
            } else {
                $errors[] = "Ukuran gambar tidak boleh lebih dari 5MB.";
            }
        } else {
            $errors[] = "Format gambar tidak valid. Hanya (jpg, jpeg, png, webp).";
        }
    } else if ($gambar && $gambar['error'] != UPLOAD_ERR_NO_FILE) {
        $errors[] = "Terjadi error saat meng-upload gambar.";
    }

    if (empty($errors)) {
        try {
            // (Sudah AMAN dari SQL Injection)
            $sql = "INSERT INTO produk (nama, kategori_id, harga, stok, deskripsi, gambar) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = db()->prepare($sql);
            $stmt->execute([$nama, $kategori_id, $harga, $stok, $deskripsi, $gambar_path_untuk_db]);

            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Produk baru berhasil ditambahkan.'];
            header('Location: ' . BASE_URL . '/admin/produk/index.php');
            exit();

        } catch (PDOException $e) {
            $errors[] = "Terjadi masalah dengan database. Silakan coba lagi.";
        }
    }
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Tambah Produk Baru</h1>
            
            <a href="<?= BASE_URL ?>/admin/produk/index.php" class="btn btn-secondary mb-3">&larr; Kembali ke Daftar Produk</a>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/admin/produk_tambah.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori_options as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>" <?= ($kategori_id == $kategori['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kategori['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="harga" class="form-label">Harga (Rp)</label>
                        <input type="number" class="form-control" id="harga" name="harga" value="<?= htmlspecialchars($harga) ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($stok) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?= htmlspecialchars($deskripsi) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="gambar" class="form-label">Gambar Produk</label>
                    <input class="form-control" type="file" id="gambar" name="gambar">
                    <div class="form-text">Maks 5MB. Format: jpg, png, webp.</div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Produk</button>
            </form>

        </div>
    </div>
</main>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>