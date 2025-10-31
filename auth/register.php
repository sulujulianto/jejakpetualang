<?php
// Pastikan sesi pengguna aktif agar flash message dan CSRF dapat bekerja.
session_name('USER_SESSION');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers/csrf.php';

// --- Inisialisasi Variabel ---
// Inisialisasi array untuk menampung semua pesan error validasi.
$errors = [];
// Inisialisasi variabel untuk nama dan email, agar bisa ditampilkan kembali di form jika ada error (sticky form).
$nama = '';
$email = '';

// Memeriksa apakah permintaan ke halaman ini menggunakan metode POST, yang berarti form telah disubmit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();

    // Mengambil data dari form dan membersihkannya dari spasi di awal/akhir menggunakan trim().
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // --- Validasi Input dari Pengguna ---
    // Setiap kali validasi gagal, tambahkan pesan error ke dalam array $errors.
    if (empty($nama)) $errors[] = "Nama lengkap harus diisi.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if (empty($password)) $errors[] = "Password harus diisi.";
    if ($password !== $konfirmasi_password) $errors[] = "Konfirmasi password tidak cocok.";
    if (strlen($password) < 6) $errors[] = "Password minimal harus 6 karakter.";
    
    // --- Proses ke Database Jika Tidak Ada Error Validasi ---
    // Memeriksa apakah array $errors kosong. Jika ya, berarti semua validasi di atas berhasil.
    if (empty($errors)) {
        // Menggunakan blok try-catch untuk menangani potensi error dari database.
        try {
            // Langkah 1: Cek apakah email sudah terdaftar untuk mencegah duplikasi akun.
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            // Jika fetch() mengembalikan data, berarti email sudah ada.
            if ($stmt->fetch()) {
                $errors[] = "Email sudah terdaftar. Silakan gunakan email lain.";
            } else {
                // Jika email belum terdaftar, lanjutkan proses pendaftaran.
                // Langkah 2: Hash password sebelum disimpan ke database. Ini adalah langkah keamanan yang SANGAT PENTING.
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Langkah 3: Masukkan data pengguna baru ke dalam tabel 'users'.
                $stmt = db()->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$nama, $email, $hashedPassword]);

                // Langkah 4: Atur pesan sukses di session dan alihkan (redirect) ke halaman login.
                $_SESSION['pesan_sukses'] = "Pendaftaran berhasil! Silakan login.";
                header("Location: login.php");
                exit(); // Hentikan eksekusi skrip setelah redirect.
            }
        // Tangkap error jika terjadi masalah pada koneksi atau query database.
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan pada database: " . $e->getMessage();
        }
    }
}
// Menetapkan judul halaman untuk tag <title> di HTML.
$page_title = 'Register';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Jejak Petualang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="main-wrapper d-flex flex-column">
    <?php include __DIR__ . '/../partials/navbar.php'; // Menyertakan navigasi atas. ?>

    <main class="main-content py-5 flex-grow-1">
        <div class="auth-form-container text-center">
            <h2 class="mb-4">Buat Akun Baru</h2>

            <?php 
            // Memeriksa apakah ada pesan error di array $errors.
            if (!empty($errors)): 
            ?>
                <div class="alert alert-danger text-start">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="post">
                <?= csrf_field(); ?>
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">Daftar</button>
            </form>
            <p class="mt-4 text-center">
                Sudah punya akun? <a href="/jejakpetualang/auth/login.php">Login di sini</a>
            </p>
        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; // Menyertakan footer. ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>