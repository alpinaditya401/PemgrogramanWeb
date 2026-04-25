<?php
require_once __DIR__ . '/Server/koneksi.php';

echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

$sid = $conn->real_escape_string(session_id());
$res = $conn->query("SELECT id, data, expires FROM php_sessions WHERE id='$sid'");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "✅ Session ADA di DB!<br>";
    echo "Expires: " . $row['expires'] . "<br>";
    echo "Data: " . htmlspecialchars($row['data']);
} else {
    echo "❌ Session TIDAK ADA di DB!<br>";
    
    // Lihat semua session yang ada
    $all = $conn->query("SELECT id, expires FROM php_sessions LIMIT 5");
    echo "<br>Session di DB:<br>";
    if ($all && $all->num_rows > 0) {
        while ($r = $all->fetch_assoc()) {
            echo "- " . $r['id'] . " (expires: " . $r['expires'] . ")<br>";
        }
    } else {
        echo "Tabel php_sessions KOSONG!";
    }
}