<?php
// CATATAN: File ini berisi fungsi-fungsi untuk perlindungan CSRF.

// --- PERBAIKAN CACAT LOGIKA ---
// Kita hapus semua logika session_start() dari file ini.
// File ini sekarang berasumsi bahwa sesi (baik USER_SESSION atau ADMIN_SESSION)
// SUDAH DIMULAI oleh file pemanggil (seperti layout/app.php atau admin/partials/header.php).
// Ini membuatnya aman untuk digunakan di kedua sisi (publik dan admin).

/**
 * Membuat token CSRF jika belum ada di sesi.
 *
 * @return string Token CSRF
 */
function generate_csrf_token()
{
    // Pastikan sesi sudah dimulai sebelum mengakses $_SESSION
    if (session_status() === PHP_SESSION_NONE) {
        // Ini adalah fallback darurat, seharusnya tidak terjadi
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Memvalidasi token CSRF dari request POST.
 * Jika tidak valid, skrip akan dihentikan.
 */
function require_valid_csrf_token()
{
    // Pastikan sesi sudah dimulai
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Ambil token dari request
    $token = $_POST['csrf_token'] ?? null;

    if (!isset($_SESSION['csrf_token']) || !$token || !hash_equals($_SESSION['csrf_token'], $token)) {
        // Token tidak valid, tidak ada, atau tidak cocok.
        // Hentikan eksekusi untuk mencegah serangan.
        die('Aksi tidak diizinkan. (Validasi CSRF Gagal)');
    }

    // Token valid, hapus untuk pemakaian sekali pakai (mencegah replay attack)
    unset($_SESSION['csrf_token']);
}

/**
 * Menghasilkan input field HTML yang tersembunyi berisi token CSRF.
 *
 * @return void (Langsung di-echo)
 */
function csrf_field()
{
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}