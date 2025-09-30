# Jejak Petualang - Aplikasi E-Commerce

Jejak Petualang adalah aplikasi web e-commerce yang dirancang untuk penjualan produk secara online. Aplikasi ini dibangun menggunakan PHP Native dan menyediakan fungsionalitas lengkap mulai dari penelusuran produk oleh pengguna hingga manajemen data oleh admin.

## Preview Aplikasi

![Homepage Screenshot](jejakpetualang/images/homepage.png)
*Tampilan halaman utama*

![Admin Dashboard](jejakpetualang/images/admin-dashboard.png)
*Dashboard admin*

(jejakpetualang/images/)
*More Images*

## Fitur Utama

Aplikasi ini memiliki dua peran utama: Pengguna (Pelanggan) dan Admin.

### Fitur Pengguna
- **Autentikasi**: Pengguna dapat mendaftar, login, dan logout. Termasuk fitur reset password.
- **Beranda**: Menampilkan produk-produk unggulan.
- **Galeri Produk**: Melihat semua produk yang tersedia dengan sistem pencarian.
- **Detail Produk**: Melihat informasi rinci tentang produk, termasuk deskripsi, harga, dan stok.
- **Keranjang Belanja**: Menambah, mengubah jumlah, dan menghapus produk dari keranjang.
- **Checkout**: Melakukan proses pemesanan dengan mengisi detail pengiriman.
- **Manajemen Akun**: Pengguna dapat melihat dan memperbarui informasi profil serta riwayat pesanan.
- **Sistem Ulasan**: Pengguna dapat memberikan ulasan pada produk yang telah dibeli.

### Fitur Admin
- **Dashboard**: Halaman utama yang menampilkan ringkasan statistik penjualan atau data penting lainnya.
- **Manajemen Produk (CRUD)**: Admin dapat menambah, melihat, mengubah, dan menghapus data produk.
- **Manajemen Kategori**: Mengelola kategori produk.
- **Manajemen Pesanan**: Melihat daftar pesanan yang masuk dan memperbarui statusnya.
- **Manajemen Pengguna**: Mengelola data pengguna yang terdaftar.
- **Manajemen Voucher/Promo**: Membuat dan mengelola kode voucher atau promo.

## Teknologi yang Digunakan
- **Backend**: PHP (Native)
- **Database**: MySQL (atau MariaDB)
- **Frontend**: HTML, CSS, JavaScript
- **Server Web**: Apache (biasanya bagian dari XAMPP atau WAMP)

## Panduan Instalasi
## Panduan Instalasi

Untuk menjalankan proyek ini secara lokal, ikuti langkah-langkah berikut:

1. **Clone Repositori**
   ```bash
   git clone [https://github.com/sulujulianto/jejakpetualang.git](https://github.com/sulujulianto/jejakpetualang.git)
   ```
   Atau unduh file ZIP dan ekstrak ke direktori server web Anda.

2. **Pindahkan ke Direktori Server**
   Pindahkan folder proyek `jejakpetualang` ke dalam direktori `htdocs` (XAMPP) atau `www` (WAMP).

3. **Buat & Import Database (Wajib)**
   - Buka `phpMyAdmin` (`http://localhost/phpmyadmin`).
   - Buat database baru dengan nama `db_jejakpetualang` (atau nama lain yang Anda inginkan).
   - **Import** file `jejakpetualang/jejakpetualang.sql` ke database yang baru Anda buat. File ini sudah mencakup seluruh skema tabel dan data awal (termasuk admin) yang dibutuhkan.

4. **Konfigurasi Koneksi Database**
   - Buka file `config/koneksi.php`.
   - Sesuaikan variabel koneksi (`$host`, `$user`, `$pass`, `$db_name`) dengan konfigurasi database lokal Anda.

5. **Jalankan Aplikasi**
   - Buka browser Anda dan akses URL: `http://localhost/jejakpetualang`

## Cara Penggunaan
- **Akses Halaman Utama**: Buka `http://localhost/jejakpetualang`
- **Akses Panel Admin**: Buka `http://localhost/jejakpetualang/admin`. Gunakan kredensial admin dari database Anda untuk masuk.

---


**Jejak Petualang** - *Memulai petualangan belanja online Anda!*
