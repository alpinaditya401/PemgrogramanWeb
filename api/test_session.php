<?php
require_once __DIR__ . '/Server/koneksi.php';

echo "Session ID: " . session_id() . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

// Cek database
$sid = session_id();
$res = $conn->query("SELECT id, expires FROM php_sessions WHERE id='$sid'");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "DB session found! Expires: " . $row['expires'];
} else {
    echo "❌ Session NOT found in database!";
}