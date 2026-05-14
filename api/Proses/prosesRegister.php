<?php
/**
 * Proses/prosesRegister.php
 */
require_once __DIR__ . '/../Server/koneksi.php';

// Cek koneksi
if (!$conn || $conn->connect_errno) {
    redirect('/register.php?pesan=nodb');
}

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/register.php');
}

// ── AMBIL & SANITASI INPUT ────────────────────────────────────
$email      = esc($conn, $_POST['email']        ?? '');
$username   = esc($conn, $_POST['username']     ?? '');
$password   = trim($_POST['password']           ?? '');
$konfirmasi = trim($_POST['konfirmasi']         ?? '');
$nama       = esc($conn, $_POST['nama_lengkap'] ?? $_POST['nama'] ?? '');
$provinsi   = esc($conn, $_POST['provinsi']     ?? '');
$kota       = esc($conn, $_POST['kota']         ?? '');
$telepon    = esc($conn, $_POST['telepon']       ?? '');
$tgl_lahir  = esc($conn, $_POST['tgl_lahir']    ?? '');

// FIX: role hanya 'user' atau 'admin' — kontributor DIHAPUS
$roleRaw = $_POST['role'] ?? 'user';
$role    = in_array($roleRaw, ['user', 'admin'], true) ? $roleRaw : 'user';
// Keamanan: user tidak bisa daftar sebagai admin lewat form
if ($role === 'admin') $role = 'user';

// ── VALIDASI ──────────────────────────────────────────────────
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

// ── CEK DUPLIKAT ──────────────────────────────────────────────
if ($conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1")?->num_rows > 0)
    redirect('/register.php?error=email_taken');

if ($conn->query("SELECT id FROM users WHERE username='$username' LIMIT 1")?->num_rows > 0)
    redirect('/register.php?error=username_taken');

// ── INSERT USER ───────────────────────────────────────────────
$hash = password_hash($password, PASSWORD_DEFAULT);

// Tanggal lahir: null jika kosong
$tglValue = $tgl_lahir ? "'$tgl_lahir'" : 'NULL';

$insertOk = $conn->query(
    "INSERT INTO users (email, username, password, nama, role, provinsi, kota, telepon, tgl_lahir, is_active, created_at)
     VALUES ('$email','$username','$hash','$nama','$role','$provinsi','$kota','$telepon',$tglValue, 1, NOW())"
);

// Simpan insert_id SEGERA sebelum query lain
$newId = (int) $conn->insert_id;

if (!$insertOk || $newId === 0) {
    $errDetail = urlencode($conn->error ?: 'insert_gagal');
    redirect('/register.php?error=db_error&detail=' . $errDetail);
}

// ── SELESAI → ke login ────────────────────────────────────────
redirect('/login.php?pesan=register_sukses');