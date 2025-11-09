<?php
// CATATAN: Ini adalah file Header & Navigasi ATAS untuk seluruh Panel Admin.
// File ini dipanggil oleh hampir semua file di folder /admin/

// 1. Memanggil file otentikasi
// Ini memastikan bahwa HANYA admin yang sudah login
// yang bisa mengakses halaman manapun yang memanggil file ini.
require_once __DIR__ . '/../auth.php';

// 2. Mengambil nama file saat ini
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?> | Admin Jejak Petualang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark admin-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/admin/dashboard.php">Admin Jejak Petualang</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/pages/index.php" target="_blank">Lihat Situs</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> 
                        <?= isset($_SESSION['user_nama']) ? htmlspecialchars($_SESSION['user_nama']) : 'Admin' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="admin-wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-main-content">
        <?php if (isset($_SESSION['pesan'])): ?>
            <div class_alert-container p-3" style="position: sticky; top: 0; z-index: 1050;">
                <div class="alert alert-<?= htmlspecialchars($_SESSION['pesan']['jenis']) ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['pesan']['isi']); unset($_SESSION['pesan']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>