<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Utama</div>
                
                <a class="nav-link" href="/jejak-petualang/admin/dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>

                <div class="sb-sidenav-menu-heading">Manajemen Toko</div>
                
                <a class="nav-link" href="/jejak-petualang/admin/kategori/index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                    Kategori
                </a>
                
                <a class="nav-link" href="/jejak-petualang/admin/produk/index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                    Produk
                </a>
                
                <a class="nav-link" href="/jejak-petualang/admin/pesanan_index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                    Pesanan
                </a>
                
                <a class="nav-link" href="/jejak-petualang/admin/voucher/index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-ticket-alt"></i></div>
                    Voucher
                </a>
                
                <div class="sb-sidenav-menu-heading">Manajemen Situs</div>
                
                <a class="nav-link" href="/jejak-petualang/admin/users/index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Pengguna
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Login sebagai:</div>
            <?= htmlspecialchars($_SESSION['user_role']); ?>
        </div>
    </nav>
</div>