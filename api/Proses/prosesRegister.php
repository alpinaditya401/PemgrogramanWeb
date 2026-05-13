<?php
/**
 * Proses/prosesRegister.php — FIXED
 */
require_once __DIR__ . '/../Server/koneksi.php';

// FIX #1: cek koneksi dengan cara yang benar
if (!$conn || $conn->connect_errno) {
    redirect('/register.php?pesan=nodb');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/register.php');

$email      = esc($conn, $_POST['email']        ?? '');
$username   = esc($conn, $_POST['username']     ?? '');
$password   = trim($_POST['password']            ?? '');
$konfirmasi = trim($_POST['konfirmasi']          ?? '');
$nama       = esc($conn, $_POST['nama_lengkap'] ?? $_POST['nama'] ?? '');
$provinsi   = esc($conn, $_POST['provinsi']     ?? '');

$roleRaw = $_POST['role'] ?? 'user';
$role    = in_array($roleRaw, ['user', 'kontributor'], true) ? $roleRaw : 'user';

if (!$email || !$username || !$password || !$konfirmasi)
    redirect('/register.php?error=empty');

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    redirect('/register.php?error=email_invalid');

if (mb_strlen($username) < 4)
    redirect('/register.php?error=username_short');

if (mb_strlen($password) < 6)
    redirect('/register.php?error=password_short');

if ($password !== $konfirmasi)
    redirect('/register.php?error=mismatch');

if ($conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1")?->num_rows > 0)
    redirect('/register.php?error=email_taken');

if ($conn->query("SELECT id FROM users WHERE username='$username' LIMIT 1")?->num_rows > 0)
    redirect('/register.php?error=username_taken');

$hash = password_hash($password, PASSWORD_DEFAULT);

$insertOk = $conn->query(
    "INSERT INTO users (email, username, password, nama, role, is_active)
     VALUES ('$email','$username','$hash','$nama','$role', 1)"
);

// FIX #2: simpan insert_id SEGERA setelah insert, sebelum query lain
$newId = (int)$conn->insert_id;

if (!$insertOk || $newId === 0) {
    $errDetail = urlencode($conn->error ?: 'insert_gagal');
    redirect('/register.php?error=db_error&detail=' . $errDetail);
}

// Update provinsi
if ($provinsi && $newId > 0) {
    $hasProvinsi = $conn->query("SHOW COLUMNS FROM users LIKE 'provinsi'");
    if ($hasProvinsi && $hasProvinsi->num_rows > 0) {
        $conn->query("UPDATE users SET provinsi='$provinsi' WHERE id=$newId");
    }
}

redirect('/login.php?pesan=register_sukses');