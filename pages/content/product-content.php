<?php
// CATATAN: File ini sekarang menggunakan class CSS baru: .search-not-found dan memiliki logika filter/sort yang lengkap.
// PERBAIKAN XSS: Semua output echo/<?= ?> yang berasal dari user atau database
//                kini dibungkus dengan htmlspecialchars().

// --- Langkah 1: Membangun Query SQL secara Dinamis (Sudah AMAN dari SQLi) ---

$sql_base = "SELECT * FROM produk";
$conditions = [];
$params = [];

if (!empty($_GET['kategori'])) {
    $conditions[] = "kategori_id = ?";
    $params[] = $_GET['kategori'];
}

if (!empty($_GET['q'])) {
    $conditions[] = "nama LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

$order_by = "ORDER BY id DESC";
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'harga_asc': $order_by = "ORDER BY harga ASC"; break; 
        case 'harga_desc': $order_by = "ORDER BY harga DESC"; break;
        case 'nama_asc': $order_by = "ORDER BY nama ASC"; break;
    }
}

$sql_final = $sql_base;
if (!empty($conditions)) {
    $sql_final .= " WHERE " . implode(' AND ', $conditions);
}
$sql_final .= " " . $order_by;

// --- Langkah 2: Eksekusi Query dan Mengambil Data ---
try {
    $stmt = db()->prepare($sql_final);
    $stmt->execute($params);
    $produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error dalam mengambil data produk: " . $e->getMessage());
}

// Mengambil daftar kategori (query sederhana, tidak perlu prepare)
$kategori_list = db()->query("SELECT * FROM kategori ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-5 fw-bold">Jelajahi Produk Kami</h1>
            <p class="lead">Temukan semua yang Anda butuhkan untuk petualangan berikutnya.</p>
        </div>
    </div>

    <div class="filter-bar mb-5">
        <form action="<?= BASE_URL ?>/pages/product.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5">
                <label for="kategori" class="visually-hidden">Kategori</label>
                <select name="kategori" id="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="sort" class="visually-hidden">Urutkan</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="">Urutkan (Terbaru)</option>
                    <option value="harga_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'harga_asc') ? 'selected' : '' ?>>Harga: Termurah</option>
                    <option value="harga_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'harga_desc') ? 'selected' : '' ?>>Harga: Termahal</option>
                    <option value="nama_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'nama_asc') ? 'selected' : '' ?>>Nama: A-Z</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <?php 
        if (empty($produk_list)): 
        ?>
            <div class="col-12 text-center">
                <?php if (!empty($_GET['q'])): // Jika tidak ditemukan karena pencarian ?>
                    <p class="search-not-found mt-5">
                        Oops! Produk dengan kata kunci "<strong><?= htmlspecialchars($_GET['q']) ?></strong>" tidak ditemukan.
                    </p>
                <?php else: // Jika tidak ditemukan karena filter lain ?>
                    <p class="search-not-found mt-5">Oops! Tidak ada produk yang cocok dengan kriteria Anda.</p>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/pages/product.php" class="btn btn-secondary mt-2">Lihat Semua Produk</a>
            </div>
        <?php else: // Jika ada produk yang ditemukan ?>
            <?php 
            foreach ($produk_list as $produk): 
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 d-flex align-items-stretch">
                    <div class="product-card h-100">
                        <a href="<?= BASE_URL ?>/pages/product_detail.php?id=<?= $produk['id'] ?>" class="product-card-link">
                            <div class="product-card-img-container">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($produk['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produk['nama']) ?>">
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="card-title flex-grow-1"><?= htmlspecialchars($produk['nama']) ?></h5>
                                <p class="card-text card-price fw-bold fs-5 mt-3">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>