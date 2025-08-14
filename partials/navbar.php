<?php
$is_logged_in = isset($_SESSION['user_id']);
$jumlah_keranjang = 0;

if ($is_logged_in) {
    try {
        $stmt = db()->prepare("SELECT COUNT(id) FROM keranjang_pengguna WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $jumlah_keranjang = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $jumlah_keranjang = 0;
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
  <div class="container">
    <a class="navbar-brand fs-4" href="/jejakpetualang/pages/index.php"><b>Jejak Petualang</b></a>
    <div class="mx-auto navbar-search">
      <form class="d-flex" action="/jejakpetualang/pages/product.php" method="get">
        <input class="form-control me-2" type="search" name="q" placeholder="Cari tenda, tas, dll...">
        <button class="btn btn-light" type="submit">Cari</button>
      </form>
    </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav" style="flex-grow: 0;">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="/jejakpetualang/pages/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/jejakpetualang/pages/product.php">Produk</a></li>
        <li class="nav-item"><a class="nav-link" href="/jejakpetualang/pages/promo.php">Promo</a></li>
        <li class="nav-item"><a class="nav-link" href="/jejakpetualang/pages/info.php">Info</a></li>
        
        <?php if ($is_logged_in): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              Hi, <?= htmlspecialchars($_SESSION['user_nama']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/jejakpetualang/pages/akun.php">Akun Saya</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="/jejakpetualang/auth/logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/jejakpetualang/auth/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="/jejakpetualang/auth/register.php">Register</a></li>
        <?php endif; ?>
        
        <li class="nav-item">
          <a class="nav-link fs-5 position-relative" href="/jejakpetualang/pages/keranjang.php">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($jumlah_keranjang > 0): ?>
            <span id="cart-item-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= $jumlah_keranjang ?>
              <span class="visually-hidden">item di keranjang</span>
            </span>
            <?php endif; ?>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>