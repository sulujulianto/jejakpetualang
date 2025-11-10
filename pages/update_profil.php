<?php
// CATATAN: Ini adalah "script" murni untuk memproses update profil.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../auth/user-auth.php'; // Pastikan user login

// 2. Memanggil helper CSRF
require_once __DIR__ . '/../helpers/csrf.php';

// 3. Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: '. BASE_URL . '/pages/akun.php');
    exit();
}

// 4. --- PERBAIKAN CSRF ---
// Validasi token CSRF. Jika tidak valid, skrip akan berhenti.
require_valid_csrf_token();

// 5. Ambil data dari form
$user_id = $_SESSION['user_id'];
$nama = $_POST['nama'] ?? '';
$email = $_POST['email'] ?? '';
$telepon = $_POST['telepon'] ?? '';
$alamat = $_POST['alamat'] ?? '';

// Validasi dasar
if (empty($nama) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Nama dan Email (valid) wajib diisi.'];
    header('Location: ' . BASE_URL . '/pages/akun.php');
    exit();
}

try {
    // (Sudah AMAN dari SQL Injection)
    $stmt = db()->prepare(
        "UPDATE users SET nama = ?, email = ?, telepon = ?, alamat = ? 
         WHERE id = ?"
    );
    $stmt->execute([$nama, $email, $telepon, $alamat, $user_id]);

    // Update juga nama di sesi
    $_SESSION['user_nama'] = $nama;

    $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Profil berhasil diperbarui.'];

} catch (PDOException $e) {
    if ($e->getCode() == '23000') { // Error duplikat email
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Email tersebut sudah digunakan oleh akun lain.'];
    } else {
        // error_log($e->getMessage());
        $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Terjadi masalah database saat update profil.'];
    }
}

// 6. Kembalikan ke halaman akun
header('Location: ' . BASE_URL . '/pages/akun.php');
exit();
?>