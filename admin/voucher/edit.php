<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses halaman ini.
require_once __DIR__ . '/../auth.php';

// Mengambil ID voucher dari parameter URL (contoh: edit.php?id=7).
$id = $_GET['id'] ?? null;
// Validasi ID: Memastikan ID ada dan merupakan bilangan bulat yang valid.
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    // Jika ID tidak valid, alihkan pengguna kembali ke halaman daftar voucher.
    header("Location: index.php");
    // Hentikan eksekusi skrip.
    exit();
}

// --- Blok Logika untuk Memproses Update Data ---
// Memeriksa apakah permintaan ke halaman ini menggunakan metode POST, yang berarti form telah disubmit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gunakan try-catch untuk menangani potensi error saat update ke database.
    try {
        // Mempersiapkan statement SQL UPDATE untuk memperbarui semua kolom voucher berdasarkan ID.
        // Tanda tanya (?) adalah placeholder yang akan diisi dengan data dari form.
        $sql = "UPDATE vouchers SET 
                    kode_voucher = ?, 
                    jenis_diskon = ?, 
                    nilai_diskon = ?, 
                    minimal_pembelian = ?, 
                    kuota = ?, 
                    tanggal_mulai = ?, 
                    tanggal_berakhir = ?, 
                    status = ?
                WHERE id = ?";
        // Mempersiapkan statement SQL.
        $stmt = db()->prepare($sql);
        // Menjalankan query dengan mengeksekusi statement dan mengirimkan array nilai dari $_POST.
        // Urutan nilai dalam array harus sama persis dengan urutan tanda tanya (?) di dalam query SQL.
        $stmt->execute([
            $_POST['kode_voucher'],
            $_POST['jenis_diskon'],
            $_POST['nilai_diskon'],
            $_POST['minimal_pembelian'],
            $_POST['kuota'],
            $_POST['tanggal_mulai'],
            $_POST['tanggal_berakhir'],
            $_POST['status'],
            $id // ID voucher untuk klausa WHERE.
        ]);
        // Jika berhasil, atur pesan sukses di session untuk ditampilkan di halaman daftar voucher.
        $_SESSION['pesan_sukses'] = "Voucher berhasil diperbarui.";
        // Alihkan (redirect) kembali ke halaman daftar voucher.
        header("Location: index.php");
        // Hentikan eksekusi skrip setelah redirect.
        exit();
    // Tangkap error jika terjadi masalah pada database (misalnya, tipe data salah).
    } catch (PDOException $e) {
        // Simpan pesan error ke dalam variabel untuk ditampilkan di halaman ini.
        $error = "Gagal memperbarui voucher: " . $e->getMessage();
    }
}

// --- Blok Logika untuk Mengambil Data Awal Voucher ---
// Bagian ini akan selalu berjalan untuk mengisi data awal pada form.
try {
    // Persiapkan statement SQL untuk mengambil semua data voucher berdasarkan ID.
    $stmt = db()->prepare("SELECT * FROM vouchers WHERE id = ?");
    // Jalankan query dengan ID yang didapat dari URL.
    $stmt->execute([$id]);
    // Ambil data voucher sebagai array asosiatif.
    $voucher = $stmt->fetch();
    // Jika tidak ada voucher yang ditemukan dengan ID tersebut, $voucher akan bernilai false.
    if (!$voucher) {
        // Alihkan kembali ke halaman utama jika voucher tidak ditemukan.
        header("Location: index.php");
        exit();
    }
// Tangkap error jika terjadi masalah saat mengambil data dari database.
} catch (PDOException $e) {
    // Hentikan skrip dan tampilkan pesan error yang jelas.
    die("Error: " . $e->getMessage());
}

// Menetapkan judul halaman.
$page_title = 'Edit Voucher';
// Menyertakan file header HTML.
include __DIR__ . '/../partials/header.php';
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Edit Voucher</h1>
            <?php 
            // Jika ada pesan error (misalnya dari proses update yang gagal), tampilkan di sini.
            if (isset($error)): 
            ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="edit.php?id=<?= $id ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_voucher" class="form-label">Kode Voucher</label>
                        <input type="text" class="form-control" name="kode_voucher" id="kode_voucher" value="<?= htmlspecialchars($voucher['kode_voucher']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kuota" class="form-label">Kuota Penggunaan</label>
                        <input type="number" class="form-control" name="kuota" id="kuota" value="<?= htmlspecialchars($voucher['kuota']) ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jenis_diskon" class="form-label">Jenis Diskon</label>
                        <select name="jenis_diskon" id="jenis_diskon" class="form-select">
                            <option value="persen" <?= $voucher['jenis_diskon'] == 'persen' ? 'selected' : '' ?>>Persentase (%)</option>
                            <option value="nominal" <?= $voucher['jenis_diskon'] == 'nominal' ? 'selected' : '' ?>>Nominal (Rp)</option>
                        </select>
                    </div>
                     <div class="col-md-6 mb-3">
                        <label for="nilai_diskon" class="form-label">Nilai Diskon</label>
                        <input type="number" class="form-control" name="nilai_diskon" id="nilai_diskon" step="0.01" value="<?= htmlspecialchars($voucher['nilai_diskon']) ?>" required>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="minimal_pembelian" class="form-label">Minimal Pembelian (Rp)</label>
                    <input type="number" class="form-control" name="minimal_pembelian" id="minimal_pembelian" value="<?= htmlspecialchars($voucher['minimal_pembelian']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai Berlaku</label>
                        <input type="datetime-local" class="form-control" name="tanggal_mulai" id="tanggal_mulai" value="<?= date('Y-m-d\TH:i', strtotime($voucher['tanggal_mulai'])) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir</label>
                        <input type="datetime-local" class="form-control" name="tanggal_berakhir" id="tanggal_berakhir" value="<?= date('Y-m-d\TH:i', strtotime($voucher['tanggal_berakhir'])) ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                     <select name="status" id="status" class="form-select">
                        <option value="aktif" <?= $voucher['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="tidak aktif" <?= $voucher['status'] == 'tidak aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>

                <hr class="my-4">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>