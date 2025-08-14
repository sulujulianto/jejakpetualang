<?php

// Memuat autoloader dari Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Menggunakan library Dotenv untuk memuat variabel dari file .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

date_default_timezone_set('Asia/Jakarta');

// Fungsi koneksi tetap sama, tetapi sekarang menggunakan variabel dari .env
function db(): PDO
{
    static $pdo;
    if (!$pdo) {
        // Ambil konfigurasi dari environment variables ($_ENV)
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        $dsn = "mysql:host=$host;dbname=$dbname;charset=UTF8";
        try {
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Jangan tampilkan detail error di production
            // error_log($e->getMessage()); // Catat error ke log server
            die("Terjadi masalah dengan koneksi database.");
        }
    }
    return $pdo;
}