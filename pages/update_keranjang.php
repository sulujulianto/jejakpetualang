<?php
// CATATAN: Ini adalah "script" untuk memproses update jumlah dari halaman keranjang.

// 1. Memanggil file konfigurasi dan otentikasi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../auth/user-auth.php';

// 2. Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: '. BASE_URL . '/pages/keranjang.php');
    exit();
}

// 3. Ambil data dari form
$user_id = $_SESSION['user_id'];
$jumlah_array = $_POST['jumlah'] ?? []; // Ini adalah array, misal: $_POST['jumlah'][ID_KERANJANG] = JUMLAH

if (empty($jumlah_array) || !is_array($jumlah_array)) {
    // Jika tidak ada data, kembalikan saja
    header('Location: ' . BASE_URL . '/pages/keranjang.php');
    exit();
}

try {
    // --- PERBAIKAN SQL INJECTION (UPDATE DALAM LOOP) ---
    // 4. Siapkan query SATU KALI di luar loop
    $stmt_update = db()->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ? AND user_id = ?");

    // 5. Looping data array
    foreach ($jumlah_array as $keranjang_id => $jumlah) {
        // Validasi
        $keranjang_id = (int)$keranjang_id;
        $jumlah = (int)$jumlah;

        if ($keranjang_id > 0 && $jumlah > 0) {
            // 6. Eksekusi query yang sudah disiapkan dengan aman
            // Kita tambahkan 'user_id' di WHERE untuk keamanan ekstra,
            // memastikan user tidak bisa update keranjang milik orang lain.
            $stmt_update->execute([$jumlah, $keranjang_id, $user_id]);
        }
    }

    $_SESSION['pesan'] = ['jenis' => 'success', 'isi' => 'Jumlah keranjang berhasil diperbarui.'];

} catch (PDOException $e) {
    // error_log($e->getMessage());
    $_SESSION['pesan'] = ['jenis' => 'danger', 'isi' => 'Terjadi masalah saat memperbarui keranjang.'];
}

// 7. Kembalikan ke halaman keranjang
header('Location: ' . BASE_URL . '/pages/keranjang.php');
exit();
?>