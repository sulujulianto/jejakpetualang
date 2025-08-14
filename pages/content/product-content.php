<?php
// CATATAN: File ini sekarang menggunakan class CSS baru: .search-not-found dan memiliki logika filter/sort yang lengkap.

// --- Langkah 1: Membangun Query SQL secara Dinamis ---

// Query dasar untuk mengambil semua produk.
$sql_base = "SELECT * FROM produk";
// Array untuk menampung kondisi WHERE (misal: filter berdasarkan kategori atau pencarian).
$conditions = [];
// Array untuk menampung parameter yang akan diikat (bind) ke query untuk keamanan (mencegah SQL Injection).
$params = [];

// Cek apakah ada parameter 'kategori' di URL.
if (!empty($_GET['kategori'])) {
    // Jika ada, tambahkan kondisi "kategori_id = ?" ke dalam array $conditions.
    $conditions[] = "kategori_id = ?";
    // Tambahkan nilai dari 'kategori' ke dalam array parameter.
    $params[] = $_GET['kategori'];
}

// Cek apakah ada parameter 'q' (query pencarian) di URL.
if (!empty($_GET['q'])) {
    // Jika ada, tambahkan kondisi pencarian "nama LIKE ?". LIKE digunakan untuk pencarian teks yang cocok sebagian.
    $conditions[] = "nama LIKE ?";
    // Tambahkan nilai dari 'q' yang sudah diapit dengan tanda '%' ke parameter. '%' berarti cocok dengan karakter apa pun.
    $params[] = '%' . $_GET['q'] . '%';
}

// Menentukan urutan pengurutan (sorting) produk. Defaultnya adalah produk terbaru (id DESC).
$order_by = "ORDER BY id DESC";
// Cek apakah ada parameter 'sort' di URL.
if (!empty($_GET['sort'])) {
    // Gunakan switch-case untuk menentukan klausa ORDER BY berdasarkan nilai dari 'sort'.
    switch ($_GET['sort']) {
        case 'harga_asc': $order_by = "ORDER BY harga ASC"; break; // Termurah
        case 'harga_desc': $order_by = "ORDER BY harga DESC"; break; // Termahal
        case 'nama_asc': $order_by = "ORDER BY nama ASC"; break; // Nama A-Z
    }
}

// Menggabungkan semua bagian menjadi satu query SQL akhir.
$sql_final = $sql_base;
// Jika ada kondisi (filter/pencarian) yang aktif, tambahkan klausa WHERE.
if (!empty($conditions)) {
    // `implode(' AND ', $conditions)` akan menggabungkan semua kondisi dengan kata 'AND'.
    $sql_final .= " WHERE " . implode(' AND ', $conditions);
}
// Tambahkan klausa ORDER BY yang sudah ditentukan.
$sql_final .= " " . $order_by;

// --- Langkah 2: Eksekusi Query dan Mengambil Data ---
try {
    // Mempersiapkan query SQL akhir.
    $stmt = db()->prepare($sql_final);
    // Menjalankan query dengan parameter yang sudah disiapkan.
    $stmt->execute($params);
    // Mengambil semua hasil produk sebagai array asosiatif.
    $produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Jika terjadi error, hentikan skrip dan tampilkan pesan.
    die("Error dalam mengambil data produk: " . $e->getMessage());
}

// Mengambil daftar semua kategori untuk ditampilkan di dropdown filter.
$kategori_list = db()->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-5 fw-bold">Jelajahi Produk Kami</h1>
            <p class="lead">Temukan semua yang Anda butuhkan untuk petualangan berikutnya.</p>
        </div>
    </div>

    <div class="filter-bar mb-5">
        <form action="/jejak-petualang/pages/product.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5">
                <label for="kategori" class="visually-hidden">Kategori</label>
                <select name="kategori" id="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
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
        // Memeriksa apakah hasil query produk kosong.
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
                <a href="/jejak-petualang/pages/product.php" class="btn btn-secondary mt-2">Lihat Semua Produk</a>
            </div>
        <?php else: // Jika ada produk yang ditemukan ?>
            <?php 
            // Looping untuk menampilkan setiap produk dalam bentuk kartu.
            foreach ($produk_list as $produk): 
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 d-flex align-items-stretch">
                    <div class="product-card h-100">
                        <a href="/jejak-petualang/pages/product_detail.php?id=<?= $produk['id'] ?>" class="product-card-link">
                            <div class="product-card-img-container">
                                <img src="/jejak-petualang/uploads/produk/<?= htmlspecialchars($produk['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produk['nama']) ?>">
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