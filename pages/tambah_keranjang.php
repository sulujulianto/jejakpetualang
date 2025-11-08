<?php
// CATATAN: Ini adalah versi final yang memperbaiki nama tabel dan query INSERT.

// 1. Memulai sesi yang benar (USER_SESSION) secara manual.
session_name('USER_SESSION');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menyertakan file konfigurasi untuk koneksi ke database.
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers/csrf.php';

// Menetapkan header respons ke 'application/json'.
header('Content-Type: application/json');

// 2. Menggunakan logika keamanan yang sesuai untuk AJAX.
// Jika pengguna belum login, kirim respons JSON, bukan redirect paksa.
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'redirect' => '/jejakpetualang/auth/login.php', 'message' => 'Anda harus login untuk menambah produk.']);
    exit();
}

// --- Inisialisasi Respons ---
$response = ['success' => false, 'message' => 'Permintaan tidak valid.'];

// Memeriksa apakah permintaan datang dari form yang menggunakan metode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();

    $user_id = $_SESSION['user_id'];
    // [CATATAN] Menggunakan $_POST langsung agar lebih mudah dibaca.
    $produk_id = (int)($_POST['id_produk'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $ukuran = trim($_POST['ukuran'] ?? 'N/A');

    if ($produk_id > 0 && $jumlah > 0) {
        try {
            $db = db();
            // Mengambil harga, stok, dan nama produk.
            $stmt = $db->prepare("SELECT nama, stok, harga FROM produk WHERE id = ? AND ketersediaan_stok = 'tersedia'");
            $stmt->execute([$produk_id]);
            $produk = $stmt->fetch();

            if ($produk && $jumlah <= $produk['stok']) {
                // Simpan harga produk saat ini ke dalam sebuah variabel.
                $harga_saat_ini = $produk['harga'];
                
                // [FIX 1] Menggunakan nama tabel yang benar: `keranjang_pengguna`.
                $cartCheckStmt = $db->prepare("SELECT * FROM keranjang_pengguna WHERE user_id = ? AND produk_id = ? AND ukuran = ?");
                $cartCheckStmt->execute([$user_id, $produk_id, $ukuran]);
                $existing_item = $cartCheckStmt->fetch();

                $operationSucceeded = false;

                if ($existing_item) {
                    // Jika item sudah ada, validasi agar jumlah baru tidak melampaui stok.
                    $new_jumlah = $existing_item['kuantitas'] + $jumlah;
                    if ($new_jumlah > $produk['stok']) {
                        $response['message'] = "Stok tersisa untuk {$produk['nama']} hanya {$produk['stok']} item.";
                    } else {
                        $updateStmt = $db->prepare("UPDATE keranjang_pengguna SET kuantitas = ? WHERE id = ?");
                        $updateStmt->execute([$new_jumlah, $existing_item['id']]);
                        $operationSucceeded = true;
                    }
                } else {
                    // [FIX 2] Jika item belum ada, gunakan query INSERT yang benar:
                    // - Nama tabel: `keranjang_pengguna`
                    // - Menyertakan kolom dan nilai untuk `harga_saat_ditambahkan`.
                    $insertStmt = $db->prepare(
                        "INSERT INTO keranjang_pengguna (user_id, produk_id, ukuran, kuantitas, harga_saat_ditambahkan) VALUES (?, ?, ?, ?, ?)"
                    );
                    $insertStmt->execute([$user_id, $produk_id, $ukuran, $jumlah, $harga_saat_ini]);
                    $operationSucceeded = true;
                }

                if ($operationSucceeded) {
                    // Mengambil jumlah item unik di keranjang untuk memperbarui ikon keranjang di frontend.
                    $cartCountStmt = $db->prepare("SELECT COUNT(id) FROM keranjang_pengguna WHERE user_id = ?");
                    $cartCountStmt->execute([$user_id]);
                    $cartCount = $cartCountStmt->fetchColumn();

                    $response = ['success' => true, 'message' => 'Produk berhasil ditambahkan!', 'cart_count' => $cartCount];
                }
            } else {
                $response['message'] = "Stok tidak mencukupi atau produk tidak tersedia.";
            }
        } catch (PDOException $e) {
            // Memberikan pesan error yang lebih detail saat development.
            // Anda bisa menyederhanakannya di production.
            $response['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Data produk tidak valid.";
    }
}

echo json_encode($response);
exit();
