<?php
// CATATAN: File ini adalah bagian ATAS dari semua halaman admin.

// --- PERBAIKAN UTAMA ---
// Logika sesi dan keamanan TIDAK seharusnya ada di sini.
// Kita panggil "penjaga gerbang" yaitu auth.php yang akan menangani semuanya.
require_once __DIR__ . '/../auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Area' ?> - Jejak Petualang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="stylesheet" href="/jejakpetualang/public/css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/jejakpetualang/admin/dashboard.php">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/jejakpetualang/admin/dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="/jejakpetualang/admin/kategori/index.php">Kategori</a></li>
                <li class="nav-item"><a class="nav-link" href="/jejakpetualang/admin/produk/index.php">Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="/jejakpetualang/admin/pesanan_index.php">Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="/jejakpetualang/admin/voucher/index.php">Voucher</a></li>
                <li class="nav-item"><a class="nav-link" href="/jejakpetualang/admin/users/index.php">Pengguna</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> 
                        <?= htmlspecialchars($_SESSION['user_nama']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/jejakpetualang/auth/logout.php?from=admin">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">