<?php
/**
 * chart.php — Grafik Harga per Provinsi & Komoditas
 * SEO-friendly, light/dark mode, structured data
 */
require 'Server/koneksi.php';

$daftarProvinsi = ['Aceh','Sumatera Utara','Sumatera Barat','Riau','Kepulauan Riau','Jambi','Bengkulu','Sumatera Selatan','Kepulauan Bangka Belitung','Lampung','Banten','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Gorontalo','Sulawesi Tengah','Sulawesi Barat','Sulawesi Selatan','Sulawesi Tenggara','Maluku','Maluku Utara','Papua','Papua Barat','Papua Selatan','Papua Tengah','Papua Pegunungan','Papua Barat Daya'];

// All unique commodity names
$resNama  = $conn->query("SELECT DISTINCT nama FROM komoditas WHERE status='approved' ORDER BY nama ASC");
$namaList = [];
if ($resNama) while ($r = $resNama->fetch_assoc()) $namaList[] = $r['nama'];

// Filter inputs — sanitize
$selProv = in_array($_GET['provinsi'] ?? '', $daftarProvinsi) ? $_GET['provinsi'] : '';
$selKom  = trim(esc($conn, $_GET['komoditas'] ?? ($namaList[0] ?? '')));

// Fetch data
$data    = null;
$dataAll = [];

if ($selProv && $selKom) {
    $p  = esc($conn, $selProv);
    $k  = esc($conn, $selKom);
    $r  = $conn->query("SELECT * FROM komoditas WHERE status='approved' AND nama='$k' AND provinsi='$p' ORDER BY updated_at DESC LIMIT 1");
    if ($r && $r->num_rows > 0) $data = $r->fetch_assoc();
} elseif (!$selProv && $selKom) {
    $k    = esc($conn, $selKom);
    $res  = $conn->query("SELECT * FROM komoditas WHERE status='approved' AND nama='$k' ORDER BY lokasi ASC");
    if ($res) while ($r = $res->fetch_assoc()) $dataAll[] = $r;
}

// SEO
$pageTitle    = $selKom ? "Harga " . htmlspecialchars($selKom) . ($selProv ? " di " . htmlspecialchars($selProv) : '') : 'Grafik Harga Komoditas';
$pageDesc     = $selKom
  ? "Pantau grafik harga " . htmlspecialchars($selKom) . ($selProv ? " di provinsi " . htmlspecialchars($selProv) : " di seluruh Indonesia") . " secara real-time."
  : "Grafik pergerakan harga komoditas pangan Indonesia. Pilih provinsi dan jenis komoditas untuk melihat data terkini.";
$activeNav    = 'chart';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'Assets/head.php'; ?>
  <!-- Chart.js loaded HERE so it is available before inline scripts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .filter-card { transition: box-shadow .2s; }
    .filter-card:focus-within { box-shadow: 0 0 0 2px rgba(16,185,129,.3); }
    #chartWrapper { position: relative; width: 100%; height: 300px; }
  </style>
</head>
<body>

<!-- Ticker (compact, no sticky on chart page) -->
<div class="h-9 bg-[var(--bg-secondary)] border-b border-[var(--border)]"></div>

<!-- NAVBAR -->
<?php include 'Assets/navbar.php'; ?>

<!-- PAGE HEADER -->
<div class="pt-28 pb-8 bg-[var(--bg-secondary)] border-b border-[var(--border)]">
  <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] mb-3">
      <a href="index.php" class="hover:text-brand-500 transition">Beranda</a>
      <i data-lucide="chevron-right" class="w-3 h-3"></i>
      <span>Grafik Harga</span>
      <?php if ($selProv): ?>
      <i data-lucide="chevron-right" class="w-3 h-3"></i>
      <span class="text-brand-500"><?= htmlspecialchars($selProv) ?></span>
      <?php endif; ?>
    </nav>
    <h1 class="font-display font-black text-2xl md:text-3xl text-[var(--text-primary)]">
      <?php if ($selProv): ?>
        Harga di <span class="text-brand-500"><?= htmlspecialchars($selProv) ?></span>
      <?php elseif ($selKom): ?>
        Harga <span class="text-brand-500"><?= htmlspecialchars($selKom) ?></span>
      <?php else: ?>
        Grafik <span class="text-brand-500">Harga Komoditas</span>
      <?php endif; ?>
    </h1>
    <p class="text-sm text-[var(--text-muted)] mt-1">Pilih provinsi dan komoditas untuk melihat grafik pergerakan harga.</p>
  </div>
</div>

<!-- MAIN -->
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- FILTER FORM -->
  <div class="card filter-card p-5 mb-8">
    <form method="GET" action="chart.php" class="flex flex-col sm:flex-row gap-4 items-end">

      <div class="flex-1">
        <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">
          <i data-lucide="map" class="w-3 h-3 inline mr-1"></i> Provinsi
        </label>
        <select name="provinsi" class="input-field">
          <option value="">— Semua Provinsi —</option>
          <?php foreach ($daftarProvinsi as $p): ?>
          <option value="<?= htmlspecialchars($p) ?>" <?= $selProv===$p?'selected':'' ?>>
            <?= htmlspecialchars($p) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="flex-1">
        <label class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">
          <i data-lucide="layers" class="w-3 h-3 inline mr-1"></i> Komoditas
        </label>
        <?php if (!empty($namaList)): ?>
        <select name="komoditas" class="input-field">
          <?php foreach ($namaList as $n): ?>
          <option value="<?= htmlspecialchars($n) ?>" <?= $selKom===$n?'selected':'' ?>>
            <?= htmlspecialchars($n) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <?php else: ?>
        <div class="input-field text-[var(--text-muted)] cursor-not-allowed">Belum ada data</div>
        <?php endif; ?>
      </div>

      <button type="submit"
              class="flex items-center gap-2 px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-display font-bold rounded-xl text-sm transition shadow shadow-brand-600/20 whitespace-nowrap hover:-translate-y-0.5">
        <i data-lucide="search" class="w-4 h-4"></i> Tampilkan
      </button>
    </form>
  </div>

  <?php if (!$selProv && !$selKom): ?>
  <!-- ── EMPTY STATE ───────────────────────────────────── -->
  <div class="card p-16 text-center">
    <i data-lucide="bar-chart-2" class="w-14 h-14 mx-auto text-[var(--text-muted)] opacity-20 mb-4"></i>
    <h3 class="font-display font-bold text-lg text-[var(--text-primary)] mb-2">Pilih Filter</h3>
    <p class="text-sm text-[var(--text-muted)]">Gunakan filter di atas untuk menampilkan grafik harga.</p>
  </div>

  <?php elseif ($selProv && !$data): ?>
  <!-- ── DATA TIDAK TERSEDIA ───────────────────────────── -->
  <div class="card border-amber-500/20 p-16 text-center">
    <div class="w-16 h-16 bg-amber-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
      <i data-lucide="map-pin-off" class="w-8 h-8 text-amber-400"></i>
    </div>
    <h3 class="font-display font-bold text-xl text-[var(--text-primary)] mb-2">Data Belum Tersedia</h3>
    <p class="text-[var(--text-secondary)] text-sm mb-1">
      Harga <strong class="text-[var(--text-primary)]"><?= htmlspecialchars($selKom) ?></strong>
      untuk provinsi <strong class="text-amber-400"><?= htmlspecialchars($selProv) ?></strong> belum ada di database.
    </p>
    <p class="text-[var(--text-muted)] text-xs mb-7">Kontributor lapangan di wilayah ini belum menginput data.</p>
    <div class="flex items-center justify-center gap-3 flex-wrap">
      <a href="chart.php?komoditas=<?= urlencode($selKom) ?>"
         class="flex items-center gap-2 px-5 py-2.5 bg-[var(--surface)] hover:bg-[var(--surface-hover)] border border-[var(--border)] text-[var(--text-primary)] rounded-xl text-sm font-semibold transition">
        Lihat semua lokasi untuk <?= htmlspecialchars($selKom) ?>
      </a>
      <a href="register.php"
         class="flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-bold transition shadow shadow-brand-600/20">
        <i data-lucide="user-plus" class="w-4 h-4"></i> Jadilah Kontributor
      </a>
    </div>
  </div>

  <?php elseif ($data): ?>
  <?php
    $hist    = json_decode($data['history'] ?? '[]', true);
    $selisih = (int)$data['harga_sekarang'] - (int)$data['harga_kemarin'];
    $persen  = $data['harga_kemarin'] > 0 ? round(abs($selisih) / $data['harga_kemarin'] * 100, 2) : 0;
    $naik    = $selisih > 0;
    $turun   = $selisih < 0;
    $trendCls = $naik ? 'text-brand-500' : ($turun ? 'text-red-400' : 'text-[var(--text-muted)]');
    $trendIcon= $naik ? '▲' : ($turun ? '▼' : '■');
  ?>

  <!-- ── STAT CARDS ────────────────────────────────────── -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
      <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-2 flex items-center gap-1.5">
        <i data-lucide="trending-up" class="w-3.5 h-3.5 text-brand-500"></i> Harga Sekarang
      </p>
      <div class="font-display font-black text-2xl <?= $trendCls ?>">
        <?= rupiah((int)$data['harga_sekarang']) ?>
      </div>
      <div class="text-xs text-[var(--text-muted)] mt-1">per <?= htmlspecialchars($data['satuan'] ?? 'kg') ?></div>
    </div>
    <div class="card p-5">
      <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-2 flex items-center gap-1.5">
        <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Harga Kemarin
      </p>
      <div class="font-display font-black text-2xl text-[var(--text-secondary)]">
        <?= rupiah((int)$data['harga_kemarin']) ?>
      </div>
    </div>
    <div class="card p-5">
      <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-2 flex items-center gap-1.5">
        <i data-lucide="activity" class="w-3.5 h-3.5"></i> Perubahan
      </p>
      <div class="font-display font-black text-2xl <?= $trendCls ?>">
        <?= $trendIcon ?> <?= $naik ? '+' : '' ?><?= number_format($selisih,0,',','.') ?>
        <span class="text-sm font-normal">(<?= $persen ?>%)</span>
      </div>
    </div>
  </div>

  <!-- ── CHART ─────────────────────────────────────────── -->
  <div class="card p-6 mb-6">
    <h2 class="font-display font-bold text-[var(--text-primary)] mb-1">
      Pergerakan Harga 7 Hari Terakhir
    </h2>
    <p class="text-xs text-[var(--text-muted)] mb-5">
      <?= htmlspecialchars($data['nama']) ?> &bull; <?= htmlspecialchars($data['lokasi']) ?>, <?= htmlspecialchars($data['provinsi']) ?>
    </p>
    <div id="chartWrapper">
      <canvas id="mainChart"></canvas>
    </div>
  </div>

  <!-- ── TABEL HISTORY ─────────────────────────────────── -->
  <div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-[var(--border)] flex items-center gap-2">
      <i data-lucide="table" class="w-4 h-4 text-brand-500"></i>
      <h2 class="font-display font-bold text-[var(--text-primary)]">Detail Riwayat Harga</h2>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Hari</th>
          <th>Harga (Rp)</th>
          <th>Perubahan</th>
          <th>Grafik Mini</th>
        </tr>
      </thead>
      <tbody id="histTbody"></tbody>
    </table>
  </div>

  <?php elseif (!$selProv && !empty($dataAll)): ?>
  <!-- ── ALL LOCATIONS TABLE ──────────────────────────── -->
  <div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-[var(--border)]">
      <h2 class="font-display font-bold text-[var(--text-primary)]">
        <span class="text-brand-500"><?= htmlspecialchars($selKom) ?></span> — Semua Lokasi
      </h2>
    </div>
    <table class="data-table">
      <thead>
        <tr><th>Lokasi</th><th>Provinsi</th><th>Kemarin</th><th>Sekarang</th><th>Tren</th></tr>
      </thead>
      <tbody>
        <?php foreach ($dataAll as $r):
          $n = (int)$r['harga_sekarang'] > (int)$r['harga_kemarin'];
          $t = (int)$r['harga_sekarang'] < (int)$r['harga_kemarin'];
        ?>
        <tr>
          <td class="font-bold text-[var(--text-primary)]"><?= htmlspecialchars($r['lokasi']) ?></td>
          <td class="text-xs text-[var(--text-muted)]"><?= htmlspecialchars($r['provinsi'] ?: '—') ?></td>
          <td class="text-[var(--text-muted)]"><?= rupiah((int)$r['harga_kemarin']) ?></td>
          <td class="font-bold <?= $n?'text-brand-500':($t?'text-red-400':'text-[var(--text-primary)]') ?>">
            <?= rupiah((int)$r['harga_sekarang']) ?>
          </td>
          <td>
            <?php if($n): ?><span class="badge badge-green">▲ Naik</span>
            <?php elseif($t): ?><span class="badge badge-red">▼ Turun</span>
            <?php else: ?><span class="badge badge-slate">■ Stabil</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php else: ?>
  <div class="card p-14 text-center">
    <i data-lucide="search-x" class="w-12 h-12 mx-auto opacity-20 mb-3"></i>
    <h3 class="font-display font-bold text-[var(--text-primary)] mb-2">Data Tidak Ditemukan</h3>
    <p class="text-sm text-[var(--text-muted)]">Coba pilih kombinasi provinsi dan komoditas yang lain.</p>
  </div>
  <?php endif; ?>

</div><!-- end max-w container -->

<!-- FOOTER -->
<?php include 'Assets/footer.php'; ?>

<script src="Assets/scripts.js"></script>
<script>lucide.createIcons();</script>
<script>
  const histData = <?= json_encode(array_values($hist), JSON_UNESCAPED_UNICODE) ?>;
  const labels   = ['H-6','H-5','H-4','H-3','H-2','Kemarin','Hari Ini'];

  document.addEventListener('DOMContentLoaded', () => {
    // Chart
    const ctx  = document.getElementById('mainChart').getContext('2d');
    const t    = getChartTheme();
    let grad   = ctx.createLinearGradient(0, 0, 0, 280);
    grad.addColorStop(0, 'rgba(16,185,129,.35)');
    grad.addColorStop(1, 'rgba(16,185,129,0)');

    const mainChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          data: histData, label: 'Harga',
          borderColor: '#10b981', backgroundColor: grad,
          fill: true, tension: .4, borderWidth: 2.5,
          pointBackgroundColor: t.bgColor, pointBorderColor: '#10b981',
          pointRadius: 5, pointHoverRadius: 7,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } }
        },
        scales: {
          y: {
            beginAtZero: false,
            ticks: { color: t.textColor, callback: v => 'Rp ' + v.toLocaleString('id-ID') },
            grid: { color: t.gridColor }
          },
          x: { ticks: { color: t.textColor }, grid: { display: false } }
        }
      }
    });

    // History table
    const tbody = document.getElementById('histTbody');
    const maxH  = Math.max(...histData);
    const minH  = Math.min(...histData);

    histData.forEach((h, i) => {
      const prev   = i > 0 ? histData[i-1] : h;
      const diff   = h - prev;
      const pct    = prev > 0 ? Math.abs(diff/prev*100).toFixed(2) : 0;
      const isToday = i === histData.length - 1;

      let badge = '';
      if (i === 0) badge = '<span class="text-[var(--text-muted)] text-xs">—</span>';
      else if (diff > 0) badge = `<span class="badge badge-green">▲ +${diff.toLocaleString('id-ID')} (${pct}%)</span>`;
      else if (diff < 0) badge = `<span class="badge badge-red">▼ ${diff.toLocaleString('id-ID')} (${pct}%)</span>`;
      else               badge = '<span class="badge badge-slate">■ Stabil</span>';

      // Mini bar
      const barPct = maxH > minH ? Math.round((h - minH) / (maxH - minH) * 100) : 50;
      const barColor = diff > 0 ? '#10b981' : diff < 0 ? '#ef4444' : '#94a3b8';

      tbody.innerHTML += `
        <tr class="${isToday ? 'bg-brand-500/[0.04]' : ''}">
          <td>
            <span class="font-medium ${isToday ? 'text-brand-500 font-bold' : 'text-[var(--text-secondary)]'}">${labels[i]}</span>
            ${isToday ? '<span class="ml-1.5 badge badge-green text-[9px] font-display">HARI INI</span>' : ''}
          </td>
          <td class="font-display font-bold text-[var(--text-primary)]">
            Rp ${h.toLocaleString('id-ID')}
          </td>
          <td>${badge}</td>
          <td>
            <div class="w-28 h-1.5 rounded-full bg-[var(--surface)] overflow-hidden">
              <div class="h-full rounded-full transition-all" style="width:${barPct}%;background:${barColor}"></div>
            </div>
          </td>
        </tr>`;
    });

    // Re-render on theme change
    document.addEventListener('themeChanged', () => {
      const nt = getChartTheme();
      mainChart.options.scales.y.ticks.color = nt.textColor;
      mainChart.options.scales.y.grid.color  = nt.gridColor;
      mainChart.options.scales.x.ticks.color = nt.textColor;
      mainChart.data.datasets[0].pointBackgroundColor = nt.bgColor;
      mainChart.update();
    });
  });
  </script>

<!-- Structured data -->
<?php if ($data): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Dataset",
  "name": "Harga <?= htmlspecialchars(addslashes($data['nama'])) ?> di <?= htmlspecialchars(addslashes($data['provinsi'])) ?>",
  "description": "<?= addslashes($pageDesc) ?>",
  "creator": { "@type": "Organization", "name": "InfoHarga Komoditi" },
  "dateModified": "<?= date('Y-m-d', strtotime($data['updated_at'])) ?>"
}
</script>
<?php endif; ?>
</body>
</html>