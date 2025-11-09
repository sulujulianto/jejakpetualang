<?php
// 1. Memanggil file konfigurasi
require_once __DIR__ . '/../../config/koneksi.php';

// 2. Memanggil file header
$page_title = 'Edit Pengguna';
include __DIR__ . '/../partials/header.php'; // (Termasuk auth.php)

$errors = [];
$user_id = $_GET['id'] ?? null;

if (!$user_id || !filter_var($user_id, FILTER_VALIDATE_INT)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'ID Pengguna tidak valid.'];
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit();
}

// 3. Logika untuk memproses form (method POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? 0;
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $role = $_POST['role'] ?? 'user'; // Default ke 'user'

    // Validasi
    if (empty($nama)) $errors[] = 'Nama wajib diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (!in_array($role, ['user', 'admin'])) $role = 'user'; // Paksa role
    
    if (empty($errors)) {
        try {
            // (Sudah AMAN dari SQL Injection)
            $stmt = db()->prepare(
                "UPDATE users SET nama = ?, email = ?, telepon = ?, alamat = ?, role = ? 
                 WHERE id = ?"
            );
            $stmt->execute([$nama, $email, $telepon, $alamat, $role, $user_id]);

            $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Data pengguna berhasil diperbarui.'];
            header('Location: ' . BASE_URL . '/admin/users/index.php');
            exit();

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $errors[] = "Email '$email' sudah digunakan oleh pengguna lain.";
            } else {
                // error_log($e->getMessage());
                $errors[] = "Terjadi masalah dengan database. Silakan coba lagi.";
            }
        }
    }
    
    // Jika ada error, data $user diisi dari $POST
    $user = [
        'id' => $user_id, 'nama' => $nama, 'email' => $email, 
        'telepon' => $telepon, 'alamat' => $alamat, 'role' => $role
    ];

} else {
    // 4. Logika untuk mengambil data (method GET)
    try {
        // (Sudah AMAN dari SQL Injection)
        $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['pesan'] = ['jenis' => 'warning', 'isi' => 'Pengguna tidak ditemukan.'];
            header('Location: ' . BASE_URL . '/admin/users/index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Gagal mengambil data pengguna.'];
        header('Location: ' . BASE_URL . '/admin/users/index.php');
        exit();
    }
}
?>

<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Edit Pengguna</h1>
            
            <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-secondary mb-3">&larr; Kembali ke Daftar Pengguna</a>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($user)): ?>
            <form action="<?= BASE_URL ?>/admin/users/edit.php?id=<?= (int)$user['id'] ?>" method="POST">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</Slabel>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="tel" class="form-control" id="telepon" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select">
                            <option value="user" <?= ($user['role'] == 'user') ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="4"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
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