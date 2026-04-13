<?php
session_start();
require '../Server/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../register.php');

$email      = esc($conn, $_POST['email']      ?? '');
$username   = esc($conn, $_POST['username']   ?? '');
$password   = trim($_POST['password']   ?? '');
$konfirmasi = trim($_POST['konfirmasi'] ?? '');
$tgl_lahir  = esc($conn, $_POST['tgl_lahir']  ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) redirect('../register.php?error=email');
if (mb_strlen($username) < 4)                   redirect('../register.php?error=username_short');
if (mb_strlen($password) < 6)                   redirect('../register.php?error=password_short');
if ($password !== $konfirmasi)                   redirect('../register.php?error=mismatch');

if ($conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1")?->num_rows > 0)    redirect('../register.php?error=email_taken');
if ($conn->query("SELECT id FROM users WHERE username='$username' LIMIT 1")?->num_rows > 0) redirect('../register.php?error=username_taken');

$hash = password_hash($password, PASSWORD_DEFAULT);
$tgl  = $tgl_lahir ? "'$tgl_lahir'" : 'NULL';
$conn->query("INSERT INTO users (email, username, password, tgl_lahir, role) VALUES ('$email','$username','$hash',$tgl,'kontributor')");

session_regenerate_id(true);
$_SESSION['login']    = true;
$_SESSION['user_id']  = (int)$conn->insert_id;
$_SESSION['username'] = $username;
$_SESSION['role']     = 'kontributor';
redirect('../dashboard-user.php');
