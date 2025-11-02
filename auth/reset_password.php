<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';

// --- Inisialisasi Variabel ---
// Variabel untuk menampung pesan notifikasi kepada pengguna.
$message = '';
// Variabel untuk menentukan jenis alert Bootstrap (misalnya, 'success' atau 'danger').
$message_type = '';
// Variabel untuk menampung link reset password (untuk tujuan demonstrasi).
$reset_link = '';

// Memeriksa apakah permintaan ke halaman ini menggunakan metode POST, yang berarti form telah disubmit.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Mengambil email dari form, jika tidak ada, nilainya string kosong.
    $email = $_POST['email'] ?? '';
    // Memvalidasi format email.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Jika format email tidak valid, atur pesan error.
        $message = "Format email tidak valid.";
        $message_type = "danger";
    } else {
        // Jika format email valid, lanjutkan ke proses database.
        try {
            // --- Langkah 1: Cek Apakah Email Terdaftar ---
            // Mempersiapkan query untuk mencari pengguna berdasarkan email.
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            // Mengambil data pengguna.
            $user = $stmt->fetch();

            // Memeriksa apakah pengguna ditemukan.
            if ($user) {
                // --- Langkah 2: Buat Token Reset Jika Pengguna Ditemukan ---
                // Membuat token acak yang aman secara kriptografis (64 karakter heksadesimal).
                $token = bin2hex(random_bytes(32));
                // Melakukan hashing pada token sebelum disimpan ke database. Ini adalah langkah keamanan penting.
                // Hanya hash yang disimpan, bukan token aslinya.
                $token_hash = hash("sha256", $token);
                // Menentukan waktu kedaluwarsa token (misalnya, 1 jam dari sekarang).
                $expiry = date("Y-m-d H:i:s", time() + 3600);

                // --- Langkah 3: Simpan Hash Token ke Database ---
                // Mempersiapkan query UPDATE untuk menyimpan hash token dan waktu kedaluwarsa ke data pengguna.
                $updateStmt = db()->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");
                $updateStmt->execute([$token_hash, $expiry, $user['id']]);

                // --- Langkah 4: Buat Link Reset ---
                // Membuat link reset lengkap yang akan dikirim ke email pengguna.
                // Link ini mengandung token asli (bukan hash-nya).
                // Dalam aplikasi nyata, baris ini akan menjadi bagian dari fungsi pengiriman email.
                $reset_link = "http://localhost/jejakpetualang/auth/new_password.php?token=" . $token;
            }

            // --- Langkah Keamanan: Mencegah User Enumeration ---
            // Pesan sukses ini ditampilkan terlepas dari apakah email ditemukan atau tidak.
            // Tujuannya agar orang luar tidak bisa menebak-nebak email mana yang terdaftar di sistem.
            $message = "Jika email Anda terdaftar, link untuk mereset password akan ditampilkan di bawah ini.";
            $message_type = "success";

        // Tangkap error jika terjadi masalah pada koneksi atau query database.
        } catch (PDOException $e) {
            $message = "Terjadi kesalahan pada server.";
            $message_type = "danger";
        }
    }
}
// Menetapkan judul halaman.
$page_title = "Reset Password";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Jejak Petualang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="main-wrapper d-flex flex-column">
    <?php include __DIR__ . '/../partials/navbar.php'; // Menyertakan navigasi atas. ?>

    <main class="main-content py-5 flex-grow-1">
        <div class="auth-form-container text-center">
            <h2 class="mb-4">Reset Password</h2>
            <p class="text-white-50 mb-4">Masukkan alamat email Anda, dan kami akan mengirimkan link untuk mereset password Anda.</p>
            
            <?php 
            // Menampilkan pesan notifikasi (baik sukses maupun error) jika ada.
            if ($message): 
            ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>

            <?php 
            // Bagian ini HANYA UNTUK DEMO.
            // Menampilkan link reset secara langsung di halaman agar tidak perlu setup email server.
            if ($reset_link): 
            ?>
                <div class="alert alert-info">
                    <p class="mb-2"><strong>(Untuk Demo) Klik link di bawah ini:</strong></p>
                    <a href="<?= $reset_link ?>" class="text-dark fw-bold"><?= $reset_link ?></a>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="contoh@email.com" required>
                </div>
                <button type="submit" class="btn btn-warning w-100 btn-lg">Kirim Link Reset</button>
                <p class="mt-3"><a href="login.php">Kembali ke Login</a></p>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; // Menyertakan footer. ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
