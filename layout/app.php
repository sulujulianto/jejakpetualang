<?php
// CATATAN: File ini sekarang adalah SATU-SATUNYA file yang memulai sesi untuk frontend.

// Memulai sesi dengan nama yang benar HANYA JIKA belum ada sesi yang aktif.
// Ini aman untuk semua halaman (publik maupun yang terproteksi).
if (session_status() === PHP_SESSION_NONE) {
    session_name('USER_SESSION');
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Jejak Petualang' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/jejakpetualang/public/css/style.css">
</head>
<body>

<div class="main-wrapper d-flex flex-column min-vh-100">

    <?php 
    require_once __DIR__ . '/../config/koneksi.php';
    include_once __DIR__ . '/../partials/navbar.php'; 
    ?>

    <main class="flex-grow-1">
        <?php 
            if (isset($page) && file_exists($page)) {
                include $page;
            } else {
                echo "<div class='container text-center py-5'><p class='text-danger'>Error: Konten halaman tidak dapat dimuat.</p></div>";
            }
        ?>
    </main>

    <?php 
    include_once __DIR__ . '/../partials/footer.php'; 
    ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/jejakpetualang/public/js/main.js"></script>
<?php
if (!empty($extra_js)) {
    echo $extra_js;
}
?>
</body>
</html>