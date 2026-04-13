<?php
/**
 * dashboard.php — Admin Panel
 */
session_start();
if (!isset($_SESSION['login']))       redirect('login.php');
if ($_SESSION['role'] !== 'admin')    redirect('dashboard-user.php');
require 'Server/koneksi.php';

$pageTitle = 'Dashboard Admin';

// ── HANDLE POST ACTIONS ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nama     = esc($conn, $_POST['nama']     ?? '');
        $kategori = esc($conn, $_POST['kategori'] ?? 'Lainnya');
        $lokasi   = esc($conn, $_POST['lokasi']   ?? '');
        $provinsi = esc($conn, $_POST['provinsi'] ?? '');
        $satuan   = esc($conn, $_POST['satuan']   ?? 'kg');
        $kemarin  = max(0,(int)($_POST['kemarin']  ?? 0));
        $sekarang = max(0,(int)($_POST['sekarang'] ?? 0));

        if ($nama && $lokasi) {
            $cek = $conn->query("SELECT id,history FROM komoditas WHERE nama='$nama' AND lokasi='$lokasi' LIMIT 1");
            if ($cek && $cek->num_rows > 0) {
                $row  = $cek->fetch_assoc();
                $hist = json_decode($row['history'] ?? '[]', true);
                $hist[] = $sekarang; if (count($hist)>7) array_shift($hist);
                $hj = esc($conn, json_encode($hist));
                $conn->query("UPDATE komoditas SET harga_kemarin=$kemarin,harga_sekarang=$sekarang,history='$hj',provinsi='$provinsi',kategori='$kategori' WHERE id={$row['id']}");
            } else {
                $hist = array_merge(array_fill(0,6,$kemarin),[$sekarang]);
                $hj   = esc($conn, json_encode($hist));
                $conn->query("INSERT INTO komoditas (nama,kategori,lokasi,provinsi,satuan,harga_kemarin,harga_sekarang,history,status)
                              VALUES ('$nama','$kategori','$lokasi','$provinsi','$satuan',$kemarin,$sekarang,'$hj','approved')");
            }
        }
        redirect('dashboard.php');
    }

    if ($aksi === 'hapus' && isset($_POST['id'])) {
        $conn->query("DELETE FROM komoditas WHERE id=" . (int)$_POST['id']);
        redirect('dashboard.php');
    }
}

// ── DATA ────────────────────────────────────────────────────
$approved = $conn->query("SELECT k.*, u.username AS kontributor_nama FROM komoditas k LEFT JOIN users u ON k.submitted_by=u.id WHERE k.status='approved' ORDER BY k.nama ASC");
$rows = []; $uniqueNama = []; $uniqueLokasi = [];
while ($r = $approved->fetch_assoc()) { $rows[] = $r; $uniqueNama[] = $r['nama']; $uniqueLokasi[] = $r['lokasi']; }
$uniqueNama = array_unique($uniqueNama); $uniqueLokasi = array_unique($uniqueLokasi);

$pending = $conn->query("SELECT k.*,u.username AS kontributor FROM komoditas k LEFT JOIN users u ON k.submitted_by=u.id WHERE k.status='pending' ORDER BY k.updated_at DESC");
$pendingRows = []; while ($r = $pending->fetch_assoc()) $pendingRows[] = $r;
$pendingCount = count($pendingRows);

$totalApproved = count($rows);
$totalKont = (int)($conn->query("SELECT COUNT(*) c FROM users WHERE role='kontributor'")?->fetch_assoc()['c'] ?? 0);
$totalProv = count(array_unique(array_filter(array_column($rows,'provinsi'))));

$activeTab = $_GET['tab'] ?? 'data';

$kategoris = ['Beras & Serealia','Hortikultura','Bumbu & Rempah','Peternakan','Minyak & Lemak','Perikanan','Lainnya'];
$daftarProvinsi = ['Aceh','Sumatera Utara','Sumatera Barat','Riau','Kepulauan Riau','Jambi','Bengkulu','Sumatera Selatan','Kepulauan Bangka Belitung','Lampung','Banten','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Gorontalo','Sulawesi Tengah','Sulawesi Barat','Sulawesi Selatan','Sulawesi Tenggara','Maluku','Maluku Utara','Papua','Papua Barat','Papua Selatan','Papua Tengah','Papua Pegunungan','Papua Barat Daya'];
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'Assets/head.php'; ?>
  <!-- Chart.js in head so it's available for inline scripts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    body { overflow: hidden; }
    .admin-sidebar { width: 230px; flex-shrink: 0; }
  </style>
</head>
<body class="flex h-screen">

<!-- ══ SIDEBAR ════════════════════════════════════════════ -->
<aside class="admin-sidebar bg-[var(--bg-secondary)] border-r border-[var(--border)] hidden md:flex flex-col h-full overflow-hidden">
  <!-- Logo -->
  <div class="h-16 flex items-center px-5 border-b border-[var(--border)] flex-shrink-0">
    <a href="index.php" class="flex items-center gap-2">
      <div class="w-7 h-7 bg-brand-500 rounded-lg flex items-center justify-center shadow shadow-brand-500/30">
        <i data-lucide="trending-up" class="w-3.5 h-3.5 text-white"></i>
      </div>
      <span class="font-display font-black text-[var(--text-primary)]">
        InfoHarga<span class="text-brand-500">Admin</span>
      </span>
    </a>
  </div>

  <!-- Nav -->
  <nav class="flex-1 py-4 px-3 space-y-0.5 sidebar-nav overflow-y-auto">
    <a href="dashboard.php?tab=data"
       class="<?= $activeTab==='data'?'active':'' ?>">
      <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
    </a>
    <a href="dashboard.php?tab=verifikasi"
       class="<?= $activeTab==='verifikasi'?'active':'' ?> relative">
      <i data-lucide="shield-check" class="w-4 h-4"></i>
      Verifikasi
      <?php if ($pendingCount > 0): ?>
      <span class="ml-auto text-[10px] font-bold bg-red-500 text-white px-1.5 py-0.5 rounded-full font-display"><?= $pendingCount ?></span>
      <?php endif; ?>
    </a>
    <a href="index.php" target="_blank" rel="noopener">
      <i data-lucide="globe" class="w-4 h-4"></i> Lihat Website
    </a>
    <div class="pt-2 pb-1 px-3">
      <span class="text-[10px] text-[var(--text-muted)] uppercase tracking-widest font-bold">Pengaturan</span>
    </div>
    <a href="#" data-action="toggle-theme">
      <i data-lucide="moon" data-theme-icon="toggle" class="w-4 h-4"></i> Tema
    </a>
  </nav>

  <!-- User -->
  <div class="p-3 border-t border-[var(--border)] flex-shrink-0">
    <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg mb-1 bg-[var(--surface)]">
      <div class="w-7 h-7 rounded-full bg-brand-500/20 flex items-center justify-center text-[10px] font-black text-brand-500 font-display flex-shrink-0">
        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
      </div>
      <div class="min-w-0">
        <div class="text-sm font-bold text-[var(--text-primary)] truncate"><?= htmlspecialchars($_SESSION['username']) ?></div>
        <div class="text-[10px] text-[var(--text-muted)]">Administrator</div>
      </div>
    </div>
    <a href="Proses/logout.php" onclick="return confirm('Yakin ingin keluar?')"
       class="sidebar-nav-logout flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-red-400 hover:bg-red-500/8 hover:text-red-300 transition font-medium">
      <i data-lucide="log-out" class="w-4 h-4"></i> Logout
    </a>
  </div>
</aside>

<!-- ══ MAIN AREA ══════════════════════════════════════════ -->
<div class="flex-1 flex flex-col h-full overflow-hidden">

  <!-- Header -->
  <header class="h-16 bg-[var(--bg-card)] border-b border-[var(--border)] flex items-center justify-between px-6 flex-shrink-0">
    <h1 class="font-display font-black text-[var(--text-primary)] text-lg">
      <?= $activeTab === 'verifikasi' ? 'Verifikasi Data' : 'Dashboard' ?>
    </h1>
    <div class="flex items-center gap-3">
      <?php if ($activeTab === 'data'): ?>
      <button onclick="openModal('modalTambah')"
              class="flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm font-bold rounded-lg transition shadow shadow-brand-600/20 font-display">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Data
      </button>
      <?php endif; ?>
    </div>
  </header>

  <!-- Scrollable body -->
  <div class="flex-1 overflow-y-auto p-6">

    <?php if ($activeTab === 'data'): ?>

    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <?php
      $sc = [
        ['Total Komoditas', $totalApproved, 'database',       'blue'],
        ['Pending',         $pendingCount,  'clock',          'amber'],
        ['Kontributor',     $totalKont,     'users',          'emerald'],
        ['Provinsi Aktif',  $totalProv,     'map-pin',        'purple'],
      ];
      $clr = ['blue'=>'blue-400','amber'=>'amber-400','emerald'=>'brand-500','purple'=>'purple-400'];
      foreach ($sc as [$label,$val,$ico,$c]):
      ?>
      <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0
                    <?= "bg-{$c}-500/10" ?>">
          <i data-lucide="<?= $ico ?>" class="w-5 h-5 text-<?= $clr[$c] ?>"></i>
        </div>
        <div>
          <div class="font-display font-black text-2xl text-[var(--text-primary)]"><?= $val ?></div>
          <div class="text-xs text-[var(--text-muted)]"><?= $label ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Chart -->
    <div class="card p-5 mb-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <h2 class="font-display font-bold text-[var(--text-primary)]">Grafik Pergerakan Harga</h2>
        <div class="flex gap-2">
          <select id="fNama" class="input-field text-xs py-1.5 px-3" style="width:160px">
            <?php foreach ($uniqueNama as $n): ?><option><?= htmlspecialchars($n) ?></option><?php endforeach; ?>
          </select>
          <select id="fLokasi" class="input-field text-xs py-1.5 px-3" style="width:130px">
            <?php foreach ($uniqueLokasi as $l): ?><option><?= htmlspecialchars($l) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="relative" style="height:200px">
        <canvas id="adminChart"></canvas>
      </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-[var(--border)]">
        <h2 class="font-display font-bold text-[var(--text-primary)]">Daftar Harga Komoditas</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>No</th><th>Komoditas</th><th>Kategori</th><th>Lokasi</th><th>Provinsi</th><th>Kemarin</th><th>Sekarang</th><th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
            <tr><td colspan="8" class="text-center py-12">
              <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-20"></i><br>
              <span class="text-sm text-[var(--text-muted)]">Belum ada data komoditas.</span>
            </td></tr>
            <?php else: $no=1; foreach($rows as $r):
              $naik  = $r['harga_sekarang'] > $r['harga_kemarin'];
              $turun = $r['harga_sekarang'] < $r['harga_kemarin'];
            ?>
            <tr>
              <td class="text-[var(--text-muted)]"><?= $no++ ?></td>
              <td class="font-bold text-[var(--text-primary)]"><?= htmlspecialchars($r['nama']) ?></td>
              <td><span class="badge badge-slate text-[10px]"><?= htmlspecialchars($r['kategori']) ?></span></td>
              <td>
                <a href="https://maps.google.com/?q=<?= urlencode($r['lokasi']) ?>" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 text-xs bg-[var(--surface)] hover:bg-brand-500/10 border border-[var(--border)] hover:border-brand-500/20 hover:text-brand-500 px-2 py-0.5 rounded-md transition">
                  <i data-lucide="map-pin" class="w-2.5 h-2.5"></i><?= htmlspecialchars($r['lokasi']) ?>
                </a>
              </td>
              <td class="text-xs text-[var(--text-muted)]"><?= htmlspecialchars($r['provinsi'] ?: '—') ?></td>
              <td class="text-[var(--text-muted)]"><?= rupiah($r['harga_kemarin']) ?></td>
              <td>
                <span class="font-bold <?= $naik?'text-brand-500':($turun?'text-red-400':'text-[var(--text-primary)]') ?>">
                  <?= rupiah($r['harga_sekarang']) ?>
                </span>
                <span class="ml-1 text-xs <?= $naik?'text-brand-500':($turun?'text-red-400':'text-[var(--text-muted)]') ?>">
                  <?= $naik?'▲':($turun?'▼':'■') ?>
                </span>
              </td>
              <td class="text-center">
                <div class="flex items-center justify-center gap-1">
                  <a href="edit.php?id=<?= $r['id'] ?>"
                     class="p-1.5 rounded-lg text-[var(--text-muted)] hover:text-blue-400 hover:bg-blue-500/10 transition">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                  </a>
                  <form method="POST" action="dashboard.php" class="inline"
                        data-confirm="Hapus komoditas <?= htmlspecialchars($r['nama']) ?>?">
                    <input type="hidden" name="aksi" value="hapus"/>
                    <input type="hidden" name="id"   value="<?= $r['id'] ?>"/>
                    <button type="submit" class="p-1.5 rounded-lg text-[var(--text-muted)] hover:text-red-400 hover:bg-red-500/10 transition">
                      <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php else: /* VERIFIKASI TAB */ ?>
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-[var(--border)] flex items-center gap-3">
        <h2 class="font-display font-bold text-[var(--text-primary)]">Menunggu Verifikasi</h2>
        <?php if ($pendingCount > 0): ?>
        <span class="badge badge-amber"><?= $pendingCount ?> pending</span>
        <?php endif; ?>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead><tr>
            <th>Komoditas</th><th>Lokasi</th><th>Harga</th><th>Kontributor</th><th>Aksi</th>
          </tr></thead>
          <tbody>
            <?php if (empty($pendingRows)): ?>
            <tr><td colspan="5" class="text-center py-14">
              <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-2 text-brand-500 opacity-40"></i><br>
              <span class="text-sm text-[var(--text-muted)]">Semua data telah diverifikasi!</span>
            </td></tr>
            <?php else: foreach ($pendingRows as $r): ?>
            <tr>
              <td class="font-bold text-[var(--text-primary)]"><?= htmlspecialchars($r['nama']) ?></td>
              <td>
                <span class="text-sm"><?= htmlspecialchars($r['lokasi']) ?></span>
                <span class="block text-xs text-[var(--text-muted)]"><?= htmlspecialchars($r['provinsi']?:'?') ?></span>
              </td>
              <td>
                <span class="text-xs text-[var(--text-muted)] block">Kem: <?= rupiah($r['harga_kemarin']) ?></span>
                <span class="font-bold text-[var(--text-primary)]"><?= rupiah($r['harga_sekarang']) ?></span>
              </td>
              <td>
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-blue-500/15 flex items-center justify-center text-[9px] font-black text-blue-400 font-display">
                    <?= strtoupper(substr($r['kontributor']??'?',0,1)) ?>
                  </div>
                  <span class="text-sm"><?= htmlspecialchars($r['kontributor'] ?? 'Unknown') ?></span>
                </div>
              </td>
              <td>
                <div class="flex gap-2">
                  <form method="POST" action="Proses/prosesVerifikasi.php">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>"/>
                    <input type="hidden" name="aksi" value="approve"/>
                    <button class="badge badge-green cursor-pointer hover:opacity-80 transition">
                      <i data-lucide="check" class="w-3 h-3"></i> Setujui
                    </button>
                  </form>
                  <form method="POST" action="Proses/prosesVerifikasi.php" onsubmit="return setNote(this)">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>"/>
                    <input type="hidden" name="aksi" value="reject"/>
                    <input type="hidden" name="catatan" value=""/>
                    <button class="badge badge-red cursor-pointer hover:opacity-80 transition">
                      <i data-lucide="x" class="w-3 h-3"></i> Tolak
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- end scrollable -->
</div><!-- end main -->

<!-- ══ MODAL TAMBAH DATA ══════════════════════════════════ -->
<div id="modalTambah" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" data-modal-close="modalTambah"></div>
  <div class="flex min-h-full items-center justify-center p-4 relative z-10">
    <div class="w-full max-w-lg card shadow-2xl">
      <div class="px-6 py-4 border-b border-[var(--border)] flex justify-between items-center">
        <h3 id="modal-title" class="font-display font-bold text-[var(--text-primary)]">Tambah Data Harga</h3>
        <button onclick="closeModal('modalTambah')" class="text-[var(--text-muted)] hover:text-[var(--text-primary)] transition p-1 rounded-lg hover:bg-[var(--surface)]">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="aksi" value="tambah"/>
        <div class="px-6 py-5 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Nama Komoditas</label>
              <input type="text" name="nama" class="input-field" placeholder="Contoh: Gula Pasir" required/>
            </div>
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Kategori</label>
              <select name="kategori" class="input-field">
                <?php foreach ($kategoris as $k): ?><option><?= $k ?></option><?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Satuan</label>
              <select name="satuan" class="input-field">
                <?php foreach (['kg','gram','liter','ml','butir','ikat','buah'] as $s): ?><option><?= $s ?></option><?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Kota / Lokasi</label>
              <input type="text" name="lokasi" class="input-field" placeholder="Jakarta" required/>
            </div>
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Provinsi</label>
              <select name="provinsi" class="input-field">
                <option value="">— Pilih —</option>
                <?php foreach ($daftarProvinsi as $p): ?><option><?= htmlspecialchars($p) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Harga Kemarin (Rp)</label>
              <input type="number" name="kemarin" class="input-field" placeholder="15000" required min="0"/>
            </div>
            <div>
              <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Harga Sekarang (Rp)</label>
              <input type="number" name="sekarang" class="input-field" placeholder="15500" required min="0"/>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-[var(--border)] flex justify-end gap-3">
          <button type="button" onclick="closeModal('modalTambah')"
                  class="px-4 py-2 rounded-lg text-sm font-semibold text-[var(--text-secondary)] bg-[var(--surface)] hover:bg-[var(--surface-hover)] transition">
            Batal
          </button>
          <button type="submit"
                  class="px-4 py-2 rounded-lg text-sm font-bold text-white bg-brand-600 hover:bg-brand-500 transition shadow shadow-brand-600/20 font-display">
            Simpan Data
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Chart data -->
<script>
const chartData = <?= json_encode(array_map(fn($r)=>['nama'=>$r['nama'],'lokasi'=>$r['lokasi'],'history'=>json_decode($r['history']??'[]',true)], $rows), JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="Assets/scripts.js"></script>
<script>
lucide.createIcons();

function setNote(form) {
  const c = prompt('Alasan penolakan (opsional):','');
  if (c === null) return false;
  form.querySelector('[name="catatan"]').value = c;
  return true;
}

// Chart
let adminChart;
document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('adminChart');
  if (!canvas || !chartData.length) return;
  const ct = canvas.getContext('2d');
  const t  = getChartTheme();
  let g = ct.createLinearGradient(0,0,0,200);
  g.addColorStop(0,'rgba(16,185,129,.3)'); g.addColorStop(1,'rgba(16,185,129,0)');

  adminChart = new Chart(ct, {
    type:'line',
    data:{ labels:['H-6','H-5','H-4','H-3','H-2','Kemarin','Hari Ini'],
           datasets:[{ data:[], borderColor:'#10b981', backgroundColor:g, fill:true, tension:.4,
                       borderWidth:2.5, pointBackgroundColor:t.bgColor, pointBorderColor:'#10b981', pointRadius:4 }] },
    options:{ responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false},
        title:{display:true, text:'Pilih komoditas & lokasi', color:t.titleColor, font:{family:'Cabinet Grotesk',size:13,weight:'700'}, padding:{bottom:12}},
        tooltip:{callbacks:{label:c=>'Rp '+c.parsed.y.toLocaleString('id-ID')}} },
      scales:{ y:{beginAtZero:false, ticks:{color:t.textColor,callback:v=>'Rp '+v.toLocaleString('id-ID')}, grid:{color:t.gridColor}},
               x:{ticks:{color:t.textColor}, grid:{display:false}} } }
  });
  updateChart();
  document.getElementById('fNama')?.addEventListener('change', updateChart);
  document.getElementById('fLokasi')?.addEventListener('change', updateChart);
});

function updateChart() {
  const n = document.getElementById('fNama')?.value;
  const l = document.getElementById('fLokasi')?.value;
  const f = chartData.find(d=>d.nama===n&&d.lokasi===l);
  const t = getChartTheme();
  adminChart.options.plugins.title.text = f ? `${n} — ${l}` : 'Data tidak ditemukan';
  adminChart.options.plugins.title.color = t.titleColor;
  adminChart.data.datasets[0].data = f ? f.history : [0,0,0,0,0,0,0];
  adminChart.update();
}

document.addEventListener('themeChanged', () => { if(adminChart) updateChart(); });
</script>
</body>
</html>