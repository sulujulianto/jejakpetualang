<?php
// Fungsi utilitas untuk proteksi CSRF di seluruh aplikasi.
if (!function_exists('ensure_session_started')) {
    // Memastikan bahwa session PHP aktif tanpa mengubah nama session saat ini.
    function ensure_session_started(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('csrf_token')) {
    // Mendapatkan atau membuat token CSRF untuk session yang aktif.
    function csrf_token(): string
    {
        ensure_session_started();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    // Menampilkan input field tersembunyi untuk token CSRF.
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('require_valid_csrf_token')) {
    // Menghentikan request ketika token CSRF yang dikirim hilang atau tidak valid.
    function require_valid_csrf_token(): void
    {
        ensure_session_started();

        $token = $_POST['csrf_token'] ?? '';
        $expected = $_SESSION['csrf_token'] ?? '';

        if (!hash_equals($expected, $token)) {
            http_response_code(419);
            die('Token CSRF tidak valid. Silakan muat ulang halaman dan coba lagi.');
        }
    }
}
