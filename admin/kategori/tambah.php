<?php
// CATATAN: Ini adalah "controller" dan "view" untuk halaman Tambah Kategori.

// 1. Memanggil file konfigurasi
require_once __DIR__ . '/../../config/koneksi.php';

// 2. Memanggil file header
$page_title = 'Tambah Kategori';
include __DIR__ . '/../partials/header.php'; // (Termasuk auth.php)

$errors = [];
$nama = '';
$deskripsi = '';

// 3. Logika untuk memproses form (method POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';

    if (empty($nama)) {
        $errors[] = 'Nama kategori wajib diisi.';
    }

    if (empty($errors)) {
        try {
            // (Sudah AMAN dari SQL Injection)
            $stmt = db()->prepare("INSERT INTO kategori (nama, deskripsi) VALUES (?, ?)");
            $stmt->execute([$nama, $deskripsi]);

            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Kategori baru berhasil ditambahkan.'];
            header('Location: ' . BASE_URL . '/admin/kategori/index.php');
            exit();

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $errors[] = "Nama kategori '$nama' sudah ada. Silakan gunakan nama lain.";
            } else {
                $errors[] = "Terjadi masalah dengan database.";
            }
        }
    }
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Tambah Kategori Baru</h1>
            
            <a href="<?= BASE_URL ?>/admin/kategori/index.php" class="btn btn-secondary mb-3">&larr; Kembali ke Daftar Kategori</a>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/admin/kategori/tambah.php" method="POST">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Kategori</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>

        </div>
    </div>
</main>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>