<?php
// Menyertakan file konfigurasi dan otentikasi
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth.php';

// --- BAGIAN 1: LOGIKA UNTUK MEMPROSES FORM SAAT DISUBMIT (METHOD POST) ---
// Ini adalah blok logika yang hilang dari kode asli Anda.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ambil ID dari input tersembunyi dan nama kategori dari form
        $id = $_POST['id'] ?? null;
        $nama_kategori = $_POST['nama_kategori'] ?? '';

        // Validasi: Pastikan ID ada dan nama kategori tidak kosong
        if (!$id || empty(trim($nama_kategori))) {
            $_SESSION['pesan_error'] = "Nama kategori tidak boleh kosong.";
            // Jika error, kembalikan ke halaman edit dengan ID yang sama
            header("Location: edit.php?id=" . $id);
            exit();
        }

        // Siapkan statement UPDATE menggunakan PDO untuk mencegah SQL Injection
        $stmt = db()->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
        
        // Eksekusi query dengan nilai yang diikat
        $stmt->execute([$nama_kategori, $id]);

        // Jika berhasil, siapkan pesan sukses dan alihkan ke halaman utama
        $_SESSION['pesan_sukses'] = "Kategori berhasil diperbarui.";
        header("Location: index.php");
        exit();

    } catch (PDOException $e) {
        // Jika terjadi error saat update database
        $_SESSION['pesan_error'] = "Gagal memperbarui kategori: " . $e->getMessage();
        header("Location: edit.php?id=" . $id);
        exit();
    }
}

// --- BAGIAN 2: LOGIKA UNTUK MENAMPILKAN DATA AWAL (METHOD GET) ---
// Kode ini berfungsi untuk mengambil data dari database dan menampilkannya di form.
try {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        $_SESSION['pesan_error'] = "Permintaan tidak valid, ID kategori tidak ditemukan.";
        header("Location: index.php");
        exit();
    }

    $stmt = db()->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->execute([$id]);
    $kategori = $stmt->fetch();

    if (!$kategori) {
        $_SESSION['pesan_error'] = "Kategori tidak ditemukan.";
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    die("Terjadi error saat mengambil data: " . $e->getMessage());
}

$page_title = 'Edit Kategori';
include __DIR__ . '/../partials/header.php';
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Edit Kategori</h1>

            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>
                    <?php unset($_SESSION['pesan_error']); ?>
                </div>
            <?php endif; ?>

            <form action="edit.php" method="POST">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($kategori['id']); ?>">

                <div class="mb-3">
                    <label for="nama_kategori" class="form-label">Nama Kategori</label>
                    <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= htmlspecialchars($kategori['nama_kategori']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</main>