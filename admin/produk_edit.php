<?php
require_once __DIR__ . '/../config/koneksi.php';
$page_title = 'Edit Produk';
include __DIR__ . '/partials/header.php'; // (Termasuk auth.php)

$errors = [];
$id = $_GET['id'] ?? null;

if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID produk tidak valid.'];
    header('Location: ' . BASE_URL . '/admin/produk/index.php');
    exit();
}

try {
    $kategori_options = db()->query("SELECT id, nama FROM kategori ORDER BY nama")->fetchAll();
} catch (PDOException $e) {
    $kategori_options = [];
    $errors[] = "Gagal memuat data kategori.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;
    $nama = $_POST['nama'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $harga = $_POST['harga'] ?? '';
    $stok = $_POST['stok'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $gambar = $_FILES['gambar'] ?? null;
    $gambar_path_untuk_db = null;
    $gambar_lama = $_POST['gambar_lama'] ?? ''; 

    if (empty($nama)) $errors[] = 'Nama produk wajib diisi.';
    if (empty($kategori_id)) $errors[] = 'Kategori wajib dipilih.';
    if (empty($harga) || !is_numeric($harga) || $harga < 0) $errors[] = 'Harga tidak valid.';
    if (empty($stok) || !is_numeric($stok) || $stok < 0) $errors[] = 'Stok tidak valid.';
    if (empty($id)) $errors[] = 'ID produk tidak ditemukan.';

    // --- Logika Upload Gambar (Tidak berubah) ---
    if ($gambar && $gambar['error'] == UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../public/uploads/produk/';
        $extension = pathinfo($gambar['name'], PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array(strtolower($extension), $allowed_ext)) {
            if ($gambar['size'] <= 5 * 1024 * 1024) { 
                $filename = uniqid('produk_', true) . '.' . $extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($gambar['tmp_name'], $upload_path)) {
                    $gambar_path_untuk_db = 'public/uploads/produk/' . $filename;
                    if ($gambar_lama && file_exists(__DIR__ . '/../' . $gambar_lama)) {
                        unlink(__DIR__ . '/../' . $gambar_lama);
                    }
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
            $sql = "UPDATE produk SET nama = ?, kategori_id = ?, harga = ?, stok = ?, deskripsi = ?";
            $params = [$nama, $kategori_id, $harga, $stok, $deskripsi];

            if ($gambar_path_untuk_db) {
                $sql .= ", gambar = ?";
                $params[] = $gambar_path_untuk_db;
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = db()->prepare($sql);
            $stmt->execute($params);

            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Produk berhasil diperbarui.'];
            header('Location: ' . BASE_URL . '/admin/produk/index.php');
            exit();

        } catch (PDOException $e) {
            $errors[] = "Terjadi masalah dengan database. Silakan coba lagi.";
        }
    }
    
    $produk = [
        'id' => $id, 'nama' => $nama, 'kategori_id' => $kategori_id,
        'harga' => $harga, 'stok' => $stok, 'deskripsi' => $deskripsi,
        'gambar' => $gambar_lama
    ];

} else {
    try {
        // (Sudah AMAN dari SQL Injection)
        $stmt = db()->prepare("SELECT * FROM produk WHERE id = ?");
        $stmt->execute([$id]);
        $produk = $stmt->fetch();

        if (!$produk) {
            $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Produk tidak ditemukan.'];
            header('Location: ' . BASE_URL . '/admin/produk/index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Gagal mengambil data produk.'];
        header('Location: ' . BASE_URL . '/admin/produk/index.php');
        exit();
    }
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Edit Produk</h1>
            
            <a href="<?= BASE_URL ?>/admin/produk/index.php" class="btn btn-secondary mb-3">&larr; Kembali ke Daftar Produk</a>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($produk)): ?>
            <form action="<?= BASE_URL ?>/admin/produk_edit.php?id=<?= (int)$produk['id'] ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= (int)$produk['id'] ?>">
                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($produk['gambar'] ?? '') ?>">
                
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($produk['nama']) ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori_options as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>" <?= ($produk['kategori_id'] == $kategori['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kategori['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="harga" class="form-label">Harga (Rp)</label>
                        <input type="number" class="form-control" id="harga" name="harga" value="<?= htmlspecialchars($produk['harga']) ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($produk['stok']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="gambar" class="form-label">Ganti Gambar Produk</label>
                    <input class="form-control" type="file" id="gambar" name="gambar">
                    <div class="form-text">Kosongkan jika tidak ingin mengganti gambar.</div>
                    <?php if (!empty($produk['gambar'])): ?>
                        <div class="mt-2">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($produk['gambar']) ?>" alt="Gambar saat ini" style="width: 150px; height: auto; border: 1px solid #ddd;">
                            <br><small>Gambar saat ini</small>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
            <?php endif; ?>

        </div>
    </div>
</main>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>