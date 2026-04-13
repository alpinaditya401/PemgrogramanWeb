<?php
/**
 * dashboard-user.php — Dashboard Kontributor Lapangan
 */
session_start();
if (!isset($_SESSION['login']))     redirect('login.php');
if ($_SESSION['role'] === 'admin')  redirect('dashboard.php');
require 'Server/koneksi.php';

$pageTitle = 'Dashboard Kontributor';
$uid       = (int)$_SESSION['user_id'];
$username  = htmlspecialchars($_SESSION['username']);

// Laporan milik user ini
$res  = $conn->query("SELECT * FROM komoditas WHERE submitted_by=$uid ORDER BY updated_at DESC");
$subs = [];
while ($r = $res->fetch_assoc()) $subs[] = $r;

$total    = count($subs);
$approved = count(array_filter($subs, fn($r) => $r['status'] === 'approved'));
$pending  = count(array_filter($subs, fn($r) => $r['status'] === 'pending'));
$rejected = count(array_filter($subs, fn($r) => $r['status'] === 'rejected'));

$kategoris = ['Beras & Serealia','Hortikultura','Bumbu & Rempah','Peternakan','Minyak & Lemak','Perikanan','Lainnya'];
$daftarProvinsi = ['Aceh','Sumatera Utara','Sumatera Barat','Riau','Kepulauan Riau','Jambi','Bengkulu','Sumatera Selatan','Kepulauan Bangka Belitung','Lampung','Banten','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Gorontalo','Sulawesi Tengah','Sulawesi Barat','Sulawesi Selatan','Sulawesi Tenggara','Maluku','Maluku Utara','Papua','Papua Barat','Papua Selatan','Papua Tengah','Papua Pegunungan','Papua Barat Daya'];
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'Assets/head.php'; ?>
</head>
<body class="min-h-screen">

<!-- ══ NAVBAR ════════════════════════════════════════════ -->
<nav class="sticky top-0 z-40 bg-[var(--bg-card)] border-b border-[var(--border)]">
  <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">

      <!-- Logo -->
      <a href="index.php" class="flex items-center gap-2">
        <div class="w-7 h-7 bg-brand-500 rounded-lg flex items-center justify-center shadow shadow-brand-500/30">
          <i data-lucide="trending-up" class="w-3.5 h-3.5 text-white"></i>
        </div>
        <span class="font-display font-black text-[var(--text-primary)]">
          InfoHarga<span class="text-blue-400">Kontributor</span>
        </span>
      </a>

      <!-- Right -->
      <div class="flex items-center gap-3">
        <!-- Dark mode -->
        <button data-action="toggle-theme"
                class="w-8 h-8 flex items-center justify-center rounded-lg bg-[var(--surface)] hover:bg-[var(--surface-hover)] text-[var(--text-muted)] hover:text-[var(--text-primary)] transition">
          <i data-lucide="moon" data-theme-icon="toggle" class="w-3.5 h-3.5"></i>
        </button>
        <!-- Avatar -->
        <div class="hidden sm:flex items-center gap-2.5 px-3 py-1.5 rounded-lg bg-[var(--surface)]">
          <div class="w-6 h-6 rounded-full bg-blue-500/20 flex items-center justify-center text-[9px] font-black text-blue-400 font-display">
            <?= strtoupper(substr($_SESSION['username'],0,1)) ?>
          </div>
          <span class="text-sm font-medium text-[var(--text-secondary)]"><?= $username ?></span>
        </div>
        <div class="h-5 w-px bg-[var(--border)] hidden sm:block"></div>
        <a href="Proses/logout.php" onclick="return confirm('Yakin ingin keluar?')"
           class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-[var(--text-muted)] hover:text-red-400 hover:bg-red-500/8 transition">
          <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
          <span class="hidden sm:inline">Keluar</span>
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- ══ MAIN ═══════════════════════════════════════════════ -->
<main class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- Page header -->
  <div class="mb-7">
    <h1 class="font-display font-black text-2xl text-[var(--text-primary)] mb-1">Dashboard Kontributor</h1>
    <p class="text-sm text-[var(--text-muted)]">Laporkan harga komoditas dari lapangan untuk diverifikasi admin.</p>
  </div>

  <!-- Message box -->
  <div id="msg-box" class="hidden mb-6 text-sm"></div>

  <!-- Stats -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php
    $sc = [
      [$total,    'Total Laporan', 'file-text',    'slate'],
      [$approved, 'Disetujui',     'check-circle', 'emerald'],
      [$pending,  'Menunggu',      'clock',        'amber'],
      [$rejected, 'Ditolak',       'x-circle',     'red'],
    ];
    $colors = ['slate'=>'text-[var(--text-muted)]','emerald'=>'text-brand-500','amber'=>'text-amber-400','red'=>'text-red-400'];
    $borders = ['slate'=>'','emerald'=>'border-brand-500/20','amber'=>'border-amber-500/20','red'=>'border-red-500/20'];
    foreach ($sc as [$val,$lbl,$ico,$c]):
    ?>
    <div class="card <?= $borders[$c] ?> p-5 text-center">
      <div class="font-display font-black text-3xl <?= $colors[$c] ?> mb-1"><?= $val ?></div>
      <div class="flex items-center justify-center gap-1 text-xs text-[var(--text-muted)]">
        <i data-lucide="<?= $ico ?>" class="w-3 h-3"></i><?= $lbl ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- 2-column layout: Form + Table -->
  <div class="grid lg:grid-cols-5 gap-6">

    <!-- Form kirim laporan -->
    <div class="lg:col-span-2">
      <div class="card p-5">
        <h2 class="font-display font-bold text-[var(--text-primary)] mb-5 flex items-center gap-2">
          <i data-lucide="send" class="w-4 h-4 text-blue-400"></i>
          Laporkan Harga Baru
        </h2>

        <form action="Proses/prosesSubmit.php" method="POST" novalidate class="space-y-4">

          <!-- Nama komoditas -->
          <div>
            <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Nama Komoditas</label>
            <input type="text" name="nama" class="input-field" placeholder="Contoh: Beras Premium" required/>
          </div>

          <!-- Kategori -->
          <div>
            <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Kategori</label>
            <select name="kategori" class="input-field">
              <?php foreach ($kategoris as $k): ?><option><?= htmlspecialchars($k) ?></option><?php endforeach; ?>
            </select>
          </div>

          <!-- Lokasi + Satuan -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Kota / Lokasi</label>
              <input type="text" name="lokasi" class="input-field" placeholder="Makassar" required/>
            </div>
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Satuan</label>
              <select name="satuan" class="input-field">
                <?php foreach (['kg','gram','liter','ml','butir','ikat','buah'] as $s): ?><option><?= $s ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Provinsi -->
          <div>
            <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Provinsi</label>
            <select name="provinsi" class="input-field" required>
              <option value="">— Pilih Provinsi —</option>
              <?php foreach ($daftarProvinsi as $p): ?>
              <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Harga kemarin + sekarang -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Harga Kemarin (Rp)</label>
              <input type="number" name="kemarin" class="input-field" placeholder="15000" required min="1"/>
            </div>
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Harga Sekarang (Rp)</label>
              <input type="number" name="sekarang" class="input-field" placeholder="15500" required min="1"/>
            </div>
          </div>

          <!-- Info -->
          <div class="flex items-start gap-2 p-3 rounded-lg bg-blue-500/6 border border-blue-500/15 text-xs text-[var(--text-secondary)]">
            <i data-lucide="info" class="w-3.5 h-3.5 text-blue-400 flex-shrink-0 mt-0.5"></i>
            Data akan ditinjau oleh admin sebelum tampil di website publik.
          </div>

          <button type="submit"
                  class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-display font-bold rounded-xl text-sm transition shadow-md shadow-blue-600/20 flex items-center justify-center gap-2 hover:-translate-y-0.5">
            <i data-lucide="send" class="w-4 h-4"></i> Kirim Laporan
          </button>
        </form>
      </div>
    </div>

    <!-- Riwayat laporan -->
    <div class="lg:col-span-3">
      <div class="card overflow-hidden h-full flex flex-col">
        <div class="px-5 py-4 border-b border-[var(--border)] flex-shrink-0">
          <h2 class="font-display font-bold text-[var(--text-primary)] flex items-center gap-2">
            <i data-lucide="clock" class="w-4 h-4 text-blue-400"></i>
            Riwayat Laporan Saya
          </h2>
        </div>
        <div class="overflow-x-auto flex-1">
          <table class="data-table">
            <thead>
              <tr>
                <th>Komoditas</th>
                <th>Lokasi</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Catatan Admin</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($subs)): ?>
              <tr><td colspan="5" class="text-center py-14">
                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-20"></i><br>
                <span class="text-sm text-[var(--text-muted)]">Belum ada laporan. Mulai kirimkan harga dari lapangan!</span>
              </td></tr>
              <?php else: foreach ($subs as $r):
                if ($r['status']==='approved')
                  $badge = '<span class="badge badge-green">✓ Disetujui</span>';
                elseif ($r['status']==='pending')
                  $badge = '<span class="badge badge-amber">⏳ Menunggu</span>';
                else
                  $badge = '<span class="badge badge-red">✕ Ditolak</span>';
              ?>
              <tr>
                <td>
                  <div class="font-bold text-[var(--text-primary)]"><?= htmlspecialchars($r['nama']) ?></div>
                  <div class="text-[10px] text-[var(--text-muted)]"><?= htmlspecialchars($r['kategori']) ?></div>
                </td>
                <td>
                  <div class="text-sm"><?= htmlspecialchars($r['lokasi']) ?></div>
                  <div class="text-[10px] text-[var(--text-muted)]"><?= htmlspecialchars($r['provinsi'] ?: '—') ?></div>
                </td>
                <td>
                  <div class="text-[10px] text-[var(--text-muted)]">Kem: <?= rupiah((int)$r['harga_kemarin']) ?></div>
                  <div class="font-bold text-[var(--text-primary)]"><?= rupiah((int)$r['harga_sekarang']) ?></div>
                </td>
                <td><?= $badge ?></td>
                <td class="text-xs text-[var(--text-muted)] max-w-[120px] truncate" title="<?= htmlspecialchars($r['catatan_admin'] ?? '') ?>">
                  <?= htmlspecialchars($r['catatan_admin'] ?: '—') ?>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- end grid -->
</main>

<script src="Assets/scripts.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
