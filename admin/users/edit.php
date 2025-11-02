<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses halaman ini.
require_once __DIR__ . '/../auth.php';

// Mengambil ID pengguna dari URL (contoh: edit.php?id=12).
// Jika tidak ada ID, variabel $id akan menjadi null.
$id = $_GET['id'] ?? null;
// Jika tidak ada ID yang diberikan di URL, langsung alihkan pengguna kembali ke halaman utama.
if (!$id) {
    header("Location: index.php");
    exit(); // Hentikan eksekusi skrip.
}

// --- Blok Logika untuk Memproses Update Data ---
// Cek apakah permintaan ke halaman ini menggunakan metode POST, yang berarti form telah disubmit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil nilai 'role' yang baru dari form yang disubmit.
    $role = $_POST['role'];
    // Gunakan try-catch untuk menangani potensi error saat update ke database.
    try {
        // Persiapkan statement SQL UPDATE untuk mengubah kolom 'role' pada tabel 'users' berdasarkan 'id'.
        $stmt = db()->prepare("UPDATE users SET role = ? WHERE id = ?");
        // Jalankan query dengan mengikat nilai $role ke tanda tanya pertama dan $id ke tanda tanya kedua.
        $stmt->execute([$role, $id]);
        // Jika berhasil, buat pesan sukses di session untuk ditampilkan di halaman daftar pengguna.
        $_SESSION['pesan_sukses'] = "Role pengguna berhasil diperbarui.";
        // Alihkan (redirect) kembali ke halaman daftar pengguna.
        header("Location: index.php");
        // Hentikan eksekusi skrip setelah redirect.
        exit();
    // Tangkap error jika terjadi masalah pada database.
    } catch (PDOException $e) {
        // Simpan pesan error ke dalam variabel untuk ditampilkan di halaman ini.
        $error = "Gagal memperbarui role: " . $e->getMessage();
    }
}

// --- Blok Logika untuk Mengambil Data Awal Pengguna ---
// Bagian ini akan selalu berjalan untuk menampilkan data pengguna di dalam form.
try {
    // Persiapkan statement SQL untuk mengambil semua data pengguna berdasarkan ID.
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    // Jalankan query dengan ID yang didapat dari URL.
    $stmt->execute([$id]);
    // Ambil data pengguna sebagai array asosiatif.
    $user = $stmt->fetch();
    // Jika tidak ada pengguna yang ditemukan dengan ID tersebut, $user akan bernilai false.
    if (!$user) {
        // Alihkan kembali ke halaman utama jika pengguna tidak ditemukan.
        header("Location: index.php");
        exit();
    }
// Tangkap error jika terjadi masalah saat mengambil data dari database.
} catch (PDOException $e) {
    // Hentikan skrip dan tampilkan pesan error.
    die("Error: " . $e->getMessage());
}

// Tetapkan judul halaman.
$page_title = 'Edit Pengguna';
// Sertakan file header HTML.
include __DIR__ . '/../partials/header.php';
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Edit Role Pengguna</h1>
            <?php 
            // Jika ada pesan error (misalnya dari proses update yang gagal), tampilkan di sini.
            if (isset($error)): 
            ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" action="edit.php?id=<?= $id ?>">
                <?= csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label">Nama Pengguna</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" disabled readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled readonly>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select">
                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <div class="form-text text-white-50">Mengubah role menjadi 'Admin' akan memberikan akses penuh ke panel admin.</div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
