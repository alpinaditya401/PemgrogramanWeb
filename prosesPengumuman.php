<?php
/**
 * Proses/prosesArtikel.php — CRUD Artikel
 */
require __DIR__ . '/../Server/koneksi.php';
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'],['admin','admin_master'])) redirect('/login.php');

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';
$uid  = (int)$_SESSION['user_id'];

if ($aksi === 'hapus' && isset($_GET['id'])) {
    $conn->query("DELETE FROM artikel WHERE id=" . (int)$_GET['id']);
    redirect('/dashboard.php?tab=artikel&success=deleted');
}

if ($aksi === 'toggle' && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $res = $conn->query("SELECT is_publish FROM artikel WHERE id=$id LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $baru = $row['is_publish'] ? 0 : 1;
        $conn->query("UPDATE artikel SET is_publish=$baru WHERE id=$id");
    }
    redirect('/dashboard.php?tab=artikel&success=updated');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard.php?tab=artikel');

$id        = (int)($_POST['id'] ?? 0);
$judul     = esc($conn, $_POST['judul']       ?? '');
$ringkasan = esc($conn, $_POST['ringkasan']   ?? '');
// ✅ FIX: terima dari field 'konten' OR 'isi', simpan ke kedua kolom
$konten    = esc($conn, $_POST['konten'] ?? $_POST['isi'] ?? '');
$kategori  = esc($conn, $_POST['kategori']    ?? 'Umum');
$emoji     = esc($conn, $_POST['emoji']       ?? '📰');
$menit     = max(1, (int)($_POST['menit_baca'] ?? 5));
$sumber    = esc($conn, $_POST['sumber_url']  ?? '');
$sumber_nm = esc($conn, $_POST['sumber_nama'] ?? '');
$publish   = isset($_POST['is_publish']) ? 1 : 0;

if (!$judul) redirect('/dashboard.php?tab=artikel&error=empty');

$slug_base = slugify($judul);
$slug      = $slug_base;
$i = 1;
while (true) {
    $q = $conn->query("SELECT id FROM artikel WHERE slug='$slug'" . ($id ? " AND id!=$id" : "") . " LIMIT 1");
    if (!$q || $q->num_rows === 0) break;
    $slug = $slug_base . '-' . $i++;
}
$slug = esc($conn, $slug);

if ($id) {
    $ok = $conn->query("UPDATE artikel SET
        judul='$judul', slug='$slug', ringkasan='$ringkasan',
        isi='$konten', konten='$konten',
        kategori='$kategori', emoji='$emoji', menit_baca=$menit,
        sumber_url='$sumber', sumber_nama='$sumber_nm', is_publish=$publish
        WHERE id=$id");
} else {
    // ✅ FIX: pakai user_id dan penulis_id (keduanya ada di schema baru)
    $ok = $conn->query("INSERT INTO artikel
        (judul, slug, ringkasan, isi, konten, kategori, emoji, menit_baca, sumber_url, sumber_nama, user_id, penulis_id, is_publish)
        VALUES
        ('$judul','$slug','$ringkasan','$konten','$konten','$kategori','$emoji',$menit,'$sumber','$sumber_nm',$uid,$uid,$publish)");
}

if (!$ok) redirect('/dashboard.php?tab=artikel&error=db&detail=' . urlencode($conn->error));
redirect('/dashboard.php?tab=artikel&success=artikel_saved');