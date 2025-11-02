<?php
// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../../config/koneksi.php';
// Menyertakan file otentikasi untuk memastikan hanya admin yang bisa mengakses halaman ini.
require_once __DIR__ . '/../auth.php';

// Inisialisasi variabel error sebagai string kosong. Variabel ini akan diisi jika ada kesalahan.
$error = '';
// Memeriksa apakah permintaan ke halaman ini menggunakan metode POST, yang berarti form telah disubmit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Validasi Sederhana di Sisi Server ---
    // Daftar semua nama field dari form yang wajib diisi.
    $required_fields = ['kode_voucher', 'jenis_diskon', 'nilai_diskon', 'minimal_pembelian', 'kuota', 'tanggal_mulai', 'tanggal_berakhir', 'status'];
    // Asumsikan semua field terisi (true) pada awalnya.
    $is_complete = true;
    // Lakukan perulangan untuk setiap field yang wajib diisi.
    foreach ($required_fields as $field) {
        // Periksa apakah field tersebut tidak ada di data POST atau nilainya kosong.
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            // Jika ada satu saja field yang kosong, ubah status menjadi false dan hentikan perulangan.
            $is_complete = false;
            break;
        }
    }

    // Jika setelah divalidasi ternyata ada field yang tidak lengkap.
    if (!$is_complete) {
        // Atur pesan error.
        $error = "Semua field wajib diisi.";
    } else {
        // --- Proses Penyimpanan ke Database ---
        // Jika semua field terisi, lanjutkan ke blok try-catch untuk eksekusi database.
        try {
            // Persiapkan statement SQL INSERT untuk menambahkan data baru ke tabel 'vouchers'.
            // Tanda tanya (?) adalah placeholder yang aman untuk data yang akan dimasukkan.
            $sql = "INSERT INTO vouchers (kode_voucher, jenis_diskon, nilai_diskon, minimal_pembelian, kuota, tanggal_mulai, tanggal_berakhir, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            // Mempersiapkan query SQL.
            $stmt = db()->prepare($sql);
            // Menjalankan query dengan mengirimkan array nilai dari $_POST.
            // Urutan nilai dalam array ini harus sesuai dengan urutan kolom dan placeholder di query SQL.
            $stmt->execute([
                $_POST['kode_voucher'],
                $_POST['jenis_diskon'],
                $_POST['nilai_diskon'],
                $_POST['minimal_pembelian'],
                $_POST['kuota'],
                $_POST['tanggal_mulai'],
                $_POST['tanggal_berakhir'],
                $_POST['status']
            ]);
            // Jika berhasil, atur pesan sukses di session untuk ditampilkan di halaman utama.
            $_SESSION['pesan_sukses'] = "Voucher baru berhasil ditambahkan.";
            // Alihkan (redirect) kembali ke halaman daftar voucher.
            header("Location: index.php");
            // Hentikan eksekusi skrip setelah redirect.
            exit();
        // Tangkap error PDOException jika terjadi masalah pada database.
        } catch (PDOException $e) {
            // --- Penanganan Error Spesifik ---
            // Memeriksa kode error dari PDO. Kode 1062 menandakan adanya pelanggaran 'unique constraint' (data duplikat).
            if ($e->errorInfo[1] == 1062) {
                // Jika terjadi duplikasi (kemungkinan pada 'kode_voucher'), berikan pesan error yang lebih spesifik.
                $error = "Gagal menyimpan: Kode Voucher sudah ada. Silakan gunakan kode lain.";
            } else {
                // Jika error lain, tampilkan pesan error umum dari PDO.
                $error = "Gagal menyimpan voucher: " . $e->getMessage();
            }
        }
    }
}

// Menetapkan judul halaman untuk tag <title> di HTML.
$page_title = 'Tambah Voucher Baru';
// Menyertakan file header HTML.
include __DIR__ . '/../partials/header.php';
?>
<main class="main-content py-5">
    <div class="container">
        <div class="admin-content-box">
            <h1 class="mb-4">Tambah Voucher Baru</h1>
            <?php 
            // Jika variabel $error berisi pesan, tampilkan dalam sebuah alert.
            if (!empty($error)): 
            ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="tambah.php">
                <?= csrf_field(); ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_voucher" class="form-label">Kode Voucher</label>
                        <input type="text" class="form-control" name="kode_voucher" id="kode_voucher" required>
                        <div class="form-text text-white-50">Contoh: RAMADAN2025, ULTAH17</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kuota" class="form-label">Kuota Penggunaan</label>
                        <input type="number" class="form-control" name="kuota" id="kuota" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jenis_diskon" class="form-label">Jenis Diskon</label>
                        <select name="jenis_diskon" id="jenis_diskon" class="form-select">
                            <option value="persen">Persentase (%)</option>
                            <option value="nominal">Nominal (Rp)</option>
                        </select>
                    </div>
                     <div class="col-md-6 mb-3">
                        <label for="nilai_diskon" class="form-label">Nilai Diskon</label>
                        <input type="number" class="form-control" name="nilai_diskon" id="nilai_diskon" step="0.01" required>
                        <div class="form-text text-white-50">Jika persen, isi 1-100. Jika nominal, isi jumlah rupiahnya.</div>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="minimal_pembelian" class="form-label">Minimal Pembelian (Rp)</label>
                    <input type="number" class="form-control" name="minimal_pembelian" id="minimal_pembelian" value="0" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai Berlaku</label>
                        <input type="datetime-local" class="form-control" name="tanggal_mulai" id="tanggal_mulai" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir</label>
                        <input type="datetime-local" class="form-control" name="tanggal_berakhir" id="tanggal_berakhir" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                     <select name="status" id="status" class="form-select">
                        <option value="aktif">Aktif</option>
                        <option value="tidak aktif">Tidak Aktif</option>
                    </select>
                </div>

                <hr class="my-4">
                <button type="submit" class="btn btn-primary">Simpan Voucher</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
