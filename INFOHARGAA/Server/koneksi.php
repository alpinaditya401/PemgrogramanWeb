<?php
/**
 * Server/koneksi.php
 * Koneksi database menggunakan mysqli dengan error handling
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'infoharga_db');
define('APP_NAME', 'InfoHarga Komoditi');
define('APP_VERSION', '3.0.0');

mysqli_report(MYSQLI_REPORT_OFF); // handle error manual
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(503);
    die('<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Service Unavailable</title>
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#0b0e14;color:#94a3b8;}
    .box{text-align:center;padding:2rem;}.icon{font-size:3rem;margin-bottom:1rem;}.title{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:.5rem;}
    .msg{font-size:.875rem;color:#64748b;}</style></head>
    <body><div class="box"><div class="icon">⚠️</div>
    <div class="title">Koneksi Database Gagal</div>
    <div class="msg">Silakan periksa konfigurasi database di Server/koneksi.php</div>
    </div></body></html>');
}

$conn->set_charset('utf8mb4');

/**
 * Helper: escape string safely
 */
function esc(mysqli $conn, string $val): string {
    return $conn->real_escape_string(trim($val));
}

/**
 * Helper: format Rupiah
 */
function rupiah(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/**
 * Helper: redirect
 */
function redirect(string $url): never {
    header("Location: $url");
    exit();
}
