<?php
/**
 * Proses/prosesRegister.php
 */
require_once __DIR__ . '/../Server/koneksi.php';

if ($conn === null) {
    redirect('/register.php?pesan=nodb');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/register.php');

// Ambil & sanitasi semua input
$email      = esc($conn, $_POST['email']        ?? '');
$username   = esc($conn, $_POST['username']     ?? '');
$password   = trim($_POST['password']   ?? '');
$konfirmasi = trim($_POST['konfirmasi'] ?? '');
$nama       = esc($conn, $_POST['nama_lengkap'] ?? $_POST['nama'] ?? '');
$provinsi   = esc($conn, $_POST['provinsi']     ?? '');

$roleRaw = $_POST['role'] ?? 'user';
$role    = in_array($roleRaw, ['user', 'kontributor'], true) ? $roleRaw : 'user';

// Validasi
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

// Cek duplikat
if ($conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1")?->num_rows > 0)
    redirect('/register.php?error=email_taken');

if ($conn->query("SELECT id FROM users WHERE username='$username' LIMIT 1")?->num_rows > 0)
    redirect('/register.php?error=username_taken');

$hash = password_hash($password, PASSWORD_DEFAULT);

// ✅ FIX: pakai nama_lengkap sesuai schema database_setup_final.sql
$insertOk = $conn->query(
    "INSERT INTO users (email, username, password, nama_lengkap, role, is_active)
     VALUES ('$email','$username','$hash','$nama','$role', 1)"
);

if (!$insertOk || $conn->insert_id === 0) {
    $errDetail = urlencode($conn->error ?: 'insert_id=0');
    redirect('/register.php?error=db_error&detail=' . $errDetail);
}

if ($provinsi) {
    $newId = (int)$conn->insert_id;
    $conn->query("UPDATE users SET provinsi='$provinsi' WHERE id=$newId");
}

redirect('/login.php?pesan=register_sukses');