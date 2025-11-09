<?php
// CATATAN: Ini adalah Kerangka (Layout) Utama untuk Halaman Publik.

// 1. Memulai Sesi untuk USER_SESSION
// Ini adalah tempat terbaik untuk memulai sesi karena hampir semua halaman publik
// akan memanggil file ini.
// Kita cek apakah sesi sudah dimulai oleh file lain (seperti login.php)
// dengan memeriksa variabel $is_login_page.
if (!isset($is_login_page) || !$is_login_page) {
    session_name('USER_SESSION');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?= isset($title) ? htmlspecialchars($title) : 'Jejak Petualang' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <?php
    if (isset($page) && file_exists($page)) {
        include $page;
    } else {
        echo "<div class='container py-5'><div class='alert alert-danger'>Error: Konten halaman tidak ditemukan.</div></div>";
    }
    ?>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?= isset($extra_js) ? $extra_js : '' ?>
</body>
</html>