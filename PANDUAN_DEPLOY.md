<?php
echo "DB_HOST: " . getenv('DB_HOST') . "<br>";
echo "DB_USER: " . getenv('DB_USER') . "<br>";
echo "DB_PORT: " . getenv('DB_PORT') . "<br>";
echo "DB_NAME: " . getenv('DB_NAME') . "<br>";
echo "DB_PASS length: " . strlen(getenv('DB_PASS')) . " chars<br>";
echo "<hr>";

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
$ok = mysqli_real_connect(
    $conn,
    getenv('DB_HOST'),
    getenv('DB_USER'),
    getenv('DB_PASS'),
    getenv('DB_NAME'),
    (int) getenv('DB_PORT'),
    NULL,
    MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
);

if ($ok) {
    echo "✅ KONEKSI BERHASIL!";
} else {
    echo "❌ Gagal: " . mysqli_connect_error();
}