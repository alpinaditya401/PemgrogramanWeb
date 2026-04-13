<?php
// Proses/prosesLogin.php
session_start();
require '../Server/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../login.php');

$username = esc($conn, $_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) redirect('../login.php?pesan=empty');

$res = $conn->query("SELECT id, username, password, role, is_active FROM users WHERE username='$username' LIMIT 1");
if ($res && $res->num_rows === 1) {
    $u = $res->fetch_assoc();
    if ($u['is_active'] && password_verify($password, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['login']    = true;
        $_SESSION['user_id']  = (int)$u['id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['role']     = $u['role'];
        $conn->query("UPDATE users SET last_login=NOW() WHERE id={$u['id']}");
        redirect('../' . ($u['role'] === 'admin' ? 'dashboard.php' : 'dashboard-user.php'));
    }
}
redirect('../login.php?pesan=gagal');
