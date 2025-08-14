<?php
// CATATAN: File ini berisi tampilan untuk halaman checkout yang sudah dinamis.

// Mengambil ID pengguna yang sedang login dari session. Diasumsikan pengguna sudah login untuk bisa mengakses halaman ini.
$user_id = $_SESSION['user_id'];
// Inisialisasi array untuk menampung item-item dari keranjang.
$item_di_keranjang = [];
// Inisialisasi total harga awal sebagai 0.
$total_harga = 0;

// --- Pengambilan Data Awal ---
// Mengambil data lengkap pengguna (seperti nama, no telepon) untuk mengisi form checkout secara otomatis.
$user_stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_data = $user_stmt->fetch();

// Menggunakan blok try-catch untuk menangani semua operasi database terkait keranjang.
try {
    // [PERBAIKAN UTAMA] Query diubah untuk mengambil `harga_saat_ditambahkan` dari tabel keranjang.
    // Nama kolom `p.harga` diabaikan, dan kita menggunakan `kp.harga_saat_ditambahkan` dengan alias `harga`.
    $sql = "
        SELECT 
            p.nama, 
            kp.harga_saat_ditambahkan as harga, -- Menggunakan harga yang disimpan di keranjang
            kp.kuantitas, 
            kp.ukuran 
        FROM keranjang_pengguna kp 
        JOIN produk p ON kp.produk_id = p.id 
        WHERE kp.user_id = ?
    ";
    // Mempersiapkan dan menjalankan query.
    $stmt = db()->prepare($sql);
    $stmt->execute([$user_id]);
    // Mengambil semua item sebagai array.
    $item_di_keranjang = $stmt->fetchAll();

    // Jika keranjang kosong, pengguna tidak seharusnya berada di halaman checkout.
    if (empty($item_di_keranjang)) {
        header('Location: /jejakpetualang/pages/keranjang.php');
        exit();
    }

    // --- Kalkulasi Harga ---
    // Looping melalui setiap item di keranjang untuk menghitung total harga (subtotal) berdasarkan harga yang sudah terkunci.
    foreach($item_di_keranjang as $item) {
        $total_harga += $item['harga'] * $item['kuantitas'];
    }

    // Mengambil nilai diskon dari session (jika ada promo yang diterapkan di halaman keranjang).
    $diskon = $_SESSION['promo']['diskon'] ?? 0;
    // Menghitung harga akhir setelah dikurangi diskon.
    $harga_akhir = $total_harga - $diskon;
    // Memastikan harga akhir tidak menjadi negatif jika diskon lebih besar dari total.
    if ($harga_akhir < 0) $harga_akhir = 0;

// Tangkap error jika terjadi masalah pada database.
} catch (PDOException $e) {
    die("Gagal memuat ringkasan pesanan: " . $e->getMessage());
}
?>

<div class="container py-5">
    <h1 class="text-center mb-4">Checkout</h1>
    <form action="/jejakpetualang/pages/proses_pesanan.php" method="POST">
        <div class="row">
            <div class="col-md-7">
                <div class="card bg-dark text-white mb-4">
                    <div class="card-header"><h4>Informasi Pengiriman & Pembayaran</h4></div>
                    <div class="card-body">
                        <input type="hidden" name="total_harga" value="<?= $harga_akhir ?>">
                        <input type="hidden" name="diskon" value="<?= $diskon ?>">
                        <input type="hidden" name="kode_promo" value="<?= htmlspecialchars($_SESSION['promo']['kode'] ?? '') ?>">

                        <div class="mb-3">
                            <label for="nama_penerima" class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" value="<?= htmlspecialchars($user_data['nama']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="no_telepon" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="no_telepon" name="no_telepon" value="<?= htmlspecialchars($user_data['nomor_telepon'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat" name="alamat_pengiriman" rows="4" required placeholder="Contoh: Jl. Petualang No. 1, RT 01/RW 02, Kel. Cijantung, Kec. Pasar Rebo, Kota Jakarta Timur, DKI Jakarta, 13770"><?= htmlspecialchars($user_data['alamat'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                            <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required onchange="handlePaymentMethodChange(this.value)">
                                <option value="" disabled selected>-- Pilih Metode --</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="E-Wallet">E-Wallet</option>
                                <option value="COD">Bayar di Tempat (COD)</option>
                            </select>
                        </div>

                        <div id="payment-details" class="mt-3">
                            <div id="transfer-bank-options" style="display: none;">
                                <div class="mb-3">
                                    <label for="bank_name" class="form-label">Pilih Bank</label>
                                    <select class="form-select" name="detail_pembayaran[bank]" onchange="handleBankChange(this.value)">
                                        <option value="BCA">BCA</option>
                                        <option value="Mandiri">Mandiri</option>
                                        <option value="BRI">BRI</option>
                                        <option value="Lainnya">Lainnya...</option>
                                    </select>
                                </div>
                                <input type="text" name="detail_pembayaran[bank_kustom]" class="form-control mt-2" placeholder="Masukkan nama bank lain" style="display: none;">
                                <div class="mt-2">
                                    <label for="nomor_rekening" class="form-label">Nomor Rekening Anda</label>
                                    <input type="text" name="detail_pembayaran[nomor_rekening]" class="form-control" placeholder="Masukkan nomor rekening">
                                </div>
                            </div>
                            
                            <div id="e-wallet-options" style="display: none;">
                                <div class="mb-3">
                                    <label for="ewallet_name" class="form-label">Pilih E-Wallet</label>
                                    <select class="form-select" name="detail_pembayaran[ewallet]" onchange="handleEwalletChange(this.value)">
                                        <option value="GoPay">GoPay</option>
                                        <option value="OVO">OVO</option>
                                        <option value="DANA">DANA</option>
                                        <option value="Lainnya">Lainnya...</option>
                                    </select>
                                </div>
                                <input type="text" name="detail_pembayaran[ewallet_kustom]" class="form-control mt-2" placeholder="Masukkan nama e-wallet lain" style="display: none;">
                                <div class="mt-2">
                                    <label for="nomor_ewallet" class="form-label">Nomor E-Wallet Anda (Contoh: 0812...)</label>
                                    <input type="text" name="detail_pembayaran[nomor_ewallet]" class="form-control" placeholder="Masukkan nomor e-wallet">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="card bg-dark text-white">
                    <div class="card-header"><h4>Ringkasan Pesanan</h4></div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($item_di_keranjang as $item): ?>
                                <li class="list-group-item bg-dark text-white d-flex justify-content-between">
                                    <div>
                                        <?= htmlspecialchars($item['nama']) ?> 
                                        <small>(<?= htmlspecialchars($item['kuantitas']) ?>x)</small>
                                    </div>
                                    <span>Rp <?= number_format($item['harga'] * $item['kuantitas'], 0, ',', '.') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <hr>
                        <dl class="row">
                            <dt class="col-7">Subtotal</dt>
                            <dd class="col-5 text-end">Rp <?= number_format($total_harga, 0, ',', '.') ?></dd>
                            <?php if ($diskon > 0): ?>
                            <dt class="col-7 text-success">Diskon</dt>
                            <dd class="col-5 text-end text-success">- Rp <?= number_format($diskon, 0, ',', '.') ?></dd>
                            <?php endif; ?>
                            <dt class="col-7 border-top pt-2 fs-5">Total</dt>
                            <dd class="col-5 text-end border-top pt-2 fs-5">Rp <?= number_format($harga_akhir, 0, ',', '.') ?></dd>
                        </dl>
                    </div>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Buat Pesanan & Bayar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Fungsi ini dipanggil ketika pilihan metode pembayaran utama berubah.
function handlePaymentMethodChange(method) {
    const bankOptions = document.getElementById('transfer-bank-options');
    const ewalletOptions = document.getElementById('e-wallet-options');
    // Sembunyikan semua opsi detail terlebih dahulu.
    bankOptions.style.display = 'none';
    ewalletOptions.style.display = 'none';
    // Tampilkan opsi yang sesuai dengan pilihan pengguna.
    if (method === 'Transfer Bank') {
        bankOptions.style.display = 'block';
    } else if (method === 'E-Wallet') {
        ewalletOptions.style.display = 'block';
    }
}

// Fungsi untuk menangani pilihan bank.
function handleBankChange(bank) {
    const customBankInput = document.querySelector('input[name="detail_pembayaran[bank_kustom]"]');
    // Tampilkan input teks untuk bank kustom hanya jika 'Lainnya' dipilih.
    customBankInput.style.display = (bank === 'Lainnya') ? 'block' : 'none';
}

// Fungsi untuk menangani pilihan e-wallet.
function handleEwalletChange(ewallet) {
    const customEwalletInput = document.querySelector('input[name="detail_pembayaran[ewallet_kustom]"]');
    // Tampilkan input teks untuk e-wallet kustom hanya jika 'Lainnya' dipilih.
    customEwalletInput.style.display = (ewallet === 'Lainnya') ? 'block' : 'none';
}
</script>