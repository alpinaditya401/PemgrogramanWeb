<?php
/**
 * koneksi_lite.php — Helper tanpa database
 * Dipakai saat database belum tersedia
 */
define('APP_NAME',    'InfoHarga Komoditi');
define('APP_VERSION', '4.0.0');

define('PROVINSI_LIST', [
    'Aceh','Sumatera Utara','Sumatera Barat','Riau','Kepulauan Riau',
    'Jambi','Bengkulu','Sumatera Selatan','Kepulauan Bangka Belitung',
    'Lampung','Banten','DKI Jakarta','Jawa Barat','Jawa Tengah',
    'DI Yogyakarta','Jawa Timur','Bali','Nusa Tenggara Barat',
    'Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah',
    'Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara',
    'Sulawesi Utara','Gorontalo','Sulawesi Tengah','Sulawesi Barat',
    'Sulawesi Selatan','Sulawesi Tenggara','Maluku','Maluku Utara',
    'Papua','Papua Barat','Papua Selatan','Papua Tengah',
    'Papua Pegunungan','Papua Barat Daya',
]);

function redirect(string $url): never {
    header("Location: $url"); exit();
}
function esc($conn, string $val): string { return htmlspecialchars(trim($val)); }
function rupiah(int $n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
function cekLogin(): void {
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) redirect('login.php');
}
function cekRole(array $roles): void {
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) redirect('login.php');
}
function getSetting($conn, string $kunci, string $default = ''): string { return $default; }
function getPengumuman($conn): array { return []; }
function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// Stub $conn supaya kode yang pakai $conn tidak error
$conn = null;
