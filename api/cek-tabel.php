<?php
// HAPUS SETELAH SELESAI!
require_once __DIR__ . '/Server/koneksi.php';

$res = $conn->query("DESCRIBE users");
echo '<pre>';
while ($r = $res->fetch_assoc()) {
    print_r($r);
}
echo '</pre>';