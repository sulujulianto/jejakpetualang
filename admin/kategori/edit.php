<?php
// 1. Memanggil file konfigurasi
require_once __DIR__ . '/../../config/koneksi.php';

// 2. Memanggil file header
$page_title = 'Edit Kategori';
include __DIR__ . '/../partials/header.php'; // (Termasuk auth.php)

$errors = [];
$id = $_GET['id'] ?? null;

if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID kategori tidak valid.'];
    header('Location: ' . BASE_URL . '/admin/kategori/index.php');
    exit();
}

// 3. Logika untuk memproses form (method POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;
    $nama = $_POST['nama'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';

    if (empty($nama)) {
        $errors[] = 'Nama kategori wajib diisi.';
    }
    if (empty($id)) {
        $errors[] = 'ID kategori tidak ditemukan.';
    }

    if (empty($errors)) {
        try {
            // (Sudah AMAN dari SQL Injection)
            $stmt = db()->prepare("UPDATE kategori SET nama = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$nama, $deskripsi, $id]);

            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Kategori berhasil diperbarui.'];
            header('Location: ' . BASE_URL . '/admin/kategori/index.php');
            exit();

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $errors[] = "Nama kategori '$nama' sudah ada.";
            } else {
                $errors[] = "Terjadi masalah dengan database.";
            }
        }
    }

    // Jika ada error, data $kategori diisi dari $POST
    $kategori = ['id' => $id, 'nama' => $nama, 'deskripsi' => $deskripsi];

} else {
    // 4. Logika untuk mengambil data (method GET)
    try {
        // (Sudah AMAN dari SQL Injection)
        $stmt = db()->prepare("SELECT * FROM kategori WHERE id = ?");
        $stmt->execute([$id]);
        $kategori = $stmt->fetch();

        if (!$kategori) {
            $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Kategori tidak ditemukan.'];
            header('Location: ' . BASE_URL . '/admin/kategori/index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Gagal mengambil data kategori.'];
        header('Location: ' . BASE_URL . '/admin/kategori/index.php');
        exit();
    }
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Edit Kategori</h1>
            
            <a href="<?= BASE_URL ?>/admin/kategori/index.php" class="btn btn-secondary mb-3">&larr; Kembali ke Daftar Kategori</a>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($kategori)): ?>
            <form action="<?= BASE_URL ?>/admin/kategori/edit.php?id=<?= (int)$kategori['id'] ?>" method="POST">
                <input type="hidden" name="id" value="<?= (int)$kategori['id'] ?>">
                
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Kategori</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($kategori['nama']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($kategori['deskripsi']) ?></textarea>
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