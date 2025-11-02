<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';

// --- Langkah 1: Validasi Token dari URL ---
// Mengambil token dari parameter URL (contoh: new_password.php?token=...).
$token = $_GET['token'] ?? '';
// Inisialisasi variabel error dan user.
$error = '';
$user = null;

// Jika tidak ada token di URL, hentikan proses dan tampilkan pesan.
if (empty($token)) {
    die("Token tidak ditemukan.");
}

// Token yang dikirim via email adalah token mentah. Di database, kita menyimpan versi hash-nya untuk keamanan.
// Jadi, kita harus hash token dari URL ini untuk bisa membandingkannya dengan yang ada di database.
$token_hash = hash("sha256", $token);

// --- Langkah 2: Verifikasi Token di Database ---
try {
    // Mempersiapkan query untuk mencari pengguna berdasarkan hash token DAN memastikan token belum kedaluwarsa.
    // `NOW()` adalah fungsi SQL untuk mendapatkan waktu saat ini.
    $stmt = db()->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token_hash]);
    // Mengambil data pengguna jika ditemukan.
    $user = $stmt->fetch();

    // Jika tidak ada pengguna yang cocok ($user bernilai false), berarti token tidak valid atau sudah lewat batas waktu.
    if (!$user) {
        die("Link reset password tidak valid atau sudah kedaluwarsa.");
    }
// Tangkap error jika terjadi masalah pada database.
} catch (PDOException $e) {
    die("Error database.");
}

// --- Langkah 3: Proses Form Saat Password Baru Disubmit ---
// Memeriksa apakah permintaan menggunakan metode POST.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Mengambil data password baru dan konfirmasinya dari form.
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validasi password baru:
    // Cek panjang minimal password.
    if (strlen($password) < 6) {
        $error = "Password minimal harus 6 karakter.";
    // Cek apakah password dan konfirmasinya sama.
    } elseif ($password !== $password_confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // --- Langkah 4: Update Password di Database ---
        // Jika validasi lolos, hash password baru sebelum disimpan ke database.
        // `PASSWORD_DEFAULT` adalah algoritma hashing yang direkomendasikan dan paling aman saat ini.
        $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Persiapkan statement UPDATE untuk mengubah password dan MENGOSONGKAN token reset.
        // Token dikosongkan (NULL) agar tidak bisa digunakan lagi.
        $updateStmt = db()->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $updateStmt->execute([$new_password_hash, $user['id']]);

        // Atur pesan sukses di session untuk ditampilkan di halaman login.
        $_SESSION['pesan_sukses'] = "Password Anda telah berhasil direset. Silakan login.";
        // Alihkan pengguna ke halaman login.
        header("Location: login.php");
        exit(); // Hentikan skrip.
    }
}
// Menetapkan judul halaman.
$page_title = "Buat Password Baru";
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
            <h2 class="mb-4">Buat Password Baru</h2>
            
            <?php 
            // Jika ada pesan error dari validasi form, tampilkan di sini.
            if ($error): 
            ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3 text-start">
                    <label for="password" class="form-label">Password Baru</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3 text-start">
                    <label for="password_confirm" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg">Reset Password</button>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; // Menyertakan footer. ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
