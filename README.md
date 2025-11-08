# Jejak Petualang

Jejak Petualang adalah aplikasi e-commerce berbasis PHP native yang menampilkan alur lengkap penjualan perlengkapan outdoor, mulai dari katalog produk hingga pemrosesan pesanan di panel admin.

![Homepage Screenshot](jejakpetualang/images/homepage.png)  
![Admin Dashboard](jejakpetualang/images/admin-dashboard.png)

## Teknologi

- PHP 8+ dengan PDO
- MySQL / MariaDB (dump: `jejakpetualang/jejakpetualang.sql`)
- Composer (dependency: `vlucas/phpdotenv`)
- Bootstrap 5, Font Awesome, dan JavaScript vanilla untuk antarmuka

## Fitur

### Pengguna
- Registrasi, login/logout, reset password
- Katalog, pencarian produk, detail dengan ulasan
- Keranjang dengan harga terkunci dan dukungan voucher
- Checkout dengan validasi stok dan ringkasan transaksi
- Profil, riwayat pesanan, pembaruan data pengguna

### Admin
- Dashboard ringkasan data
- CRUD produk, kategori, pengguna
- Manajemen voucher/promo
- Pemrosesan status pesanan

## Instalasi

1. **Kloning repositori**
   ```bash
   git clone https://github.com/sulujulianto/jejakpetualang.git
   cd jejakpetualang
   ```

2. **Install dependensi (opsional)**
   ```bash
   composer install
   ```

3. **Konfigurasi environment**
   ```bash
   cp .env.example .env
   ```
   Sesuaikan `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` dengan lingkungan lokal.

4. **Import database**
   - Buat database baru, contoh `CREATE DATABASE jejakpetualang;`
   - Import `jejakpetualang/jejakpetualang.sql`
   - Tersedia satu akun admin awal: email `admin@jejak.com` (atur ulang password sesuai kebutuhan)

5. **Tempatkan proyek di web root**  
   Contoh: `htdocs/jejakpetualang` (XAMPP). Akses frontend via `http://localhost/jejakpetualang/pages/index.php` dan panel admin di `http://localhost/jejakpetualang/admin`.

## Struktur Proyek

```
.
├── auth/                # Autentikasi pengguna
├── pages/               # Halaman publik (home, produk, keranjang, checkout, dll.)
├── admin/               # Panel admin
├── layout/, partials/   # Template dan komponen bersama
├── helpers/csrf.php     # Utilitas CSRF dan session guard
├── config/koneksi.php   # Loader .env + koneksi PDO
└── jejakpetualang.sql   # Dump database
```

## Ikhtisar Arsitektur

- **Lapisan presentasi** menggunakan layout tunggal (`layout/app.php`) yang memuat navbar, footer, dan asset global.
- **Alur keranjang dan checkout** menyimpan harga snapshot di `keranjang_pengguna`. Proses `pages/proses_pesanan.php` menjalankan transaksi database atomik: mengunci stok, memvalidasi voucher, menulis `transaksi` dan `transaksi_item`, lalu mengosongkan keranjang.
- **Keamanan**: setiap formulir POST menyertakan CSRF token (`csrf_field()`). Session `USER_SESSION` dan `ADMIN_SESSION` dipisahkan untuk mencegah saling timpa.

## Catatan Tambahan

- Kode ditujukan sebagai referensi implementasi e-commerce berbasis PHP native. Penyesuaian tambahan seperti sanitasi unggahan, rate limiting, atau logging dapat ditambahkan sesuai kebutuhan lingkungan produksi.
- Screenshot tambahan tersedia di `jejakpetualang/images/`.
