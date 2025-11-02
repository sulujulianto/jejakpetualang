<?php

// Memuat autoloader dari Composer jika tersedia.
$rootDir = __DIR__ . '/../';
$autoloadPath = $rootDir . 'vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Utility sederhana untuk memuat pasangan kunci=nilai dari file .env ketika
// library Dotenv tidak tersedia (misalnya saat vendor/ belum di-install).
if (!function_exists('load_env_file')) {
    function load_env_file(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $delimiterPosition = strpos($line, '=');
            if ($delimiterPosition === false) {
                continue;
            }

            $name = trim(substr($line, 0, $delimiterPosition));
            $value = trim(substr($line, $delimiterPosition + 1));
            $value = trim($value, "\"' ");

            if ($name === '' || isset($_ENV[$name])) {
                continue;
            }

            $_ENV[$name] = $value;
            putenv($name . '=' . $value);
        }
    }
}

$envLoaded = false;

if (class_exists(Dotenv\Dotenv::class)) {
    Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
    $envLoaded = true;
}

if (!$envLoaded) {
    // Coba baca .env terlebih dahulu, lalu jatuhkan ke .env.example jika ada.
    load_env_file($rootDir . '.env');
    load_env_file($rootDir . '.env.example');
}

// Nilai default supaya aplikasi tetap bisa jalan pada lingkungan pengembangan
// meskipun variabel lingkungan belum di-set.
$defaultEnv = [
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'jejakpetualang',
    'DB_USER' => 'root',
    'DB_PASS' => '',
];

foreach ($defaultEnv as $key => $value) {
    if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
        $_ENV[$key] = $value;
    }
}

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
