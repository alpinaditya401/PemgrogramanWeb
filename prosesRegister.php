<?php
/**
 * Proses/prosesDiskusi.php — Backend Diskusi
 */
require __DIR__ . '/../Server/koneksi.php';
cekLogin();

header('Content-Type: application/json; charset=utf-8');

$uid    = (int)$_SESSION['user_id'];
$role   = $_SESSION['role'];
$isAdmin= in_array($role, ['admin','admin_master']);
$aksi   = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

function resp(bool $ok, string $msg, array $data=[]): never {
    echo json_encode(['ok'=>$ok,'msg'=>$msg,'data'=>$data], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── KIRIM ─────────────────────────────────────────────────────
if ($aksi === 'kirim') {
    $pesan    = trim($_POST['pesan'] ?? '');
    $komId    = (int)($_POST['komoditas_id'] ?? 0);
    $parentId = (int)($_POST['parent_id']    ?? 0);

    if (!$pesan) resp(false, 'Pesan tidak boleh kosong.');
    if (mb_strlen($pesan) > 1000) resp(false, 'Pesan maksimal 1000 karakter.');

    $pesan_esc   = esc($conn, strip_tags($pesan));
    $komVal      = $komId    ? $komId    : 'NULL';
    $parentVal   = $parentId ? $parentId : 'NULL';

    // ✅ FIX: simpan ke kolom 'pesan', judul dan isi boleh NULL di schema baru
    $ok = $conn->query("INSERT INTO diskusi (pesan, komoditas_id, parent_id, user_id)
        VALUES ('$pesan_esc', $komVal, $parentVal, $uid)");

    if (!$ok) resp(false, 'Gagal menyimpan: ' . $conn->error);

    $newId = (int)$conn->insert_id;
    $row = $conn->query("SELECT d.*, u.username, u.foto, u.role as user_role
                         FROM diskusi d JOIN users u ON d.user_id=u.id
                         WHERE d.id=$newId LIMIT 1")?->fetch_assoc();
    resp(true, 'Komentar berhasil dikirim.', ['komentar'=>$row, 'id'=>$newId]);
}

// ── HAPUS ─────────────────────────────────────────────────────
if ($aksi === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) resp(false, 'ID tidak valid.');

    $cek = $conn->query("SELECT user_id FROM diskusi WHERE id=$id LIMIT 1")?->fetch_assoc();
    if (!$cek) resp(false, 'Komentar tidak ditemukan.');
    if ($cek['user_id'] != $uid && !$isAdmin) resp(false, 'Tidak punya izin.');

    $conn->query("UPDATE diskusi SET is_deleted=1 WHERE id=$id");
    resp(true, 'Komentar dihapus.');
}

// ── REAKSI ────────────────────────────────────────────────────
if ($aksi === 'reaksi') {
    $id   = (int)($_POST['id'] ?? 0);
    $tipe = in_array($_POST['tipe'] ?? '', ['like','helpful']) ? $_POST['tipe'] : 'like';
    if (!$id) resp(false, 'ID tidak valid.');

    $cek = $conn->query("SELECT id FROM diskusi_reaksi WHERE diskusi_id=$id AND user_id=$uid AND tipe='$tipe' LIMIT 1")?->fetch_assoc();
    if ($cek) {
        $conn->query("DELETE FROM diskusi_reaksi WHERE diskusi_id=$id AND user_id=$uid AND tipe='$tipe'");
        $aktif = false;
    } else {
        $conn->query("INSERT IGNORE INTO diskusi_reaksi (diskusi_id, user_id, tipe) VALUES ($id, $uid, '$tipe')");
        $aktif = true;
    }
    $jumlah = (int)$conn->query("SELECT COUNT(*) c FROM diskusi_reaksi WHERE diskusi_id=$id AND tipe='$tipe'")?->fetch_assoc()['c'];
    resp(true, '', ['aktif'=>$aktif,'jumlah'=>$jumlah]);
}

// ── AMBIL ─────────────────────────────────────────────────────
if ($aksi === 'ambil' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $komId  = (int)($_GET['komoditas_id'] ?? 0);
    $limit  = min((int)($_GET['limit'] ?? 30), 100);
    $offset = (int)($_GET['offset'] ?? 0);
    $where  = $komId ? "AND d.komoditas_id=$komId" : "AND d.komoditas_id IS NULL";

    function fetchReplies(mysqli $db, int $parentId, int $myUid): array {
        $res = $db->query("SELECT d.id, d.pesan, d.isi, d.parent_id, d.created_at, d.is_deleted,
               u.id as uid, u.username, u.foto, u.role as user_role,
               (SELECT COUNT(*) FROM diskusi_reaksi r WHERE r.diskusi_id=d.id AND r.tipe='like') as likes,
               (SELECT COUNT(*) FROM diskusi_reaksi r WHERE r.diskusi_id=d.id AND r.user_id=$myUid AND r.tipe='like') as saya_like
            FROM diskusi d JOIN users u ON d.user_id=u.id
            WHERE d.parent_id=$parentId ORDER BY d.created_at ASC");
        $list = [];
        while ($r = $res?->fetch_assoc()) {
            $r['replies'] = fetchReplies($db, (int)$r['id'], $myUid);
            $list[] = $r;
        }
        return $list;
    }

    $res = $conn->query("SELECT d.id, d.pesan, d.isi, d.parent_id, d.created_at, d.is_deleted,
           u.id as uid, u.username, u.foto, u.role as user_role,
           (SELECT COUNT(*) FROM diskusi_reaksi r WHERE r.diskusi_id=d.id AND r.tipe='like') as likes,
           (SELECT COUNT(*) FROM diskusi_reaksi r WHERE r.diskusi_id=d.id AND r.user_id=$uid AND r.tipe='like') as saya_like
        FROM diskusi d JOIN users u ON d.user_id=u.id
        WHERE d.parent_id IS NULL AND d.is_deleted=0 $where
        ORDER BY d.created_at DESC LIMIT $limit OFFSET $offset");

    $list = [];
    while ($r = $res?->fetch_assoc()) {
        $r['replies'] = fetchReplies($conn, (int)$r['id'], $uid);
        $list[] = $r;
    }
    echo json_encode(['ok'=>true,'data'=>$list,'total'=>count($list)], JSON_UNESCAPED_UNICODE);
    exit;
}

resp(false, 'Aksi tidak dikenali.');