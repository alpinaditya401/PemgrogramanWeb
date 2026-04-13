<?php
/**
 * index.php — Halaman Publik Utama InfoHarga Komoditi
 * SEO-optimized, light/dark mode, live ticker, artikel, sidebar provinsi
 */

session_start();
require 'Server/koneksi.php';

// ── SEO META ────────────────────────────────────────────────
$pageTitle    = 'Transparansi Harga Pangan Indonesia';
$pageDesc     = 'Pantau harga komoditas pangan terkini dari 38 provinsi Indonesia. Data beras, cabai, bawang, minyak goreng, daging, dan lainnya secara real-time.';
$pageKeywords = 'harga komoditas Indonesia, harga beras hari ini, harga cabai, harga bawang, info harga pangan, komoditas pertanian';
$activeNav    = 'home';

// ── DATA PROVINSI ────────────────────────────────────────────
$provinsi = [
  'Aceh','Sumatera Utara','Sumatera Barat','Riau','Kepulauan Riau',
  'Jambi','Bengkulu','Sumatera Selatan','Kepulauan Bangka Belitung',
  'Lampung','Banten','DKI Jakarta','Jawa Barat','Jawa Tengah',
  'DI Yogyakarta','Jawa Timur','Bali','Nusa Tenggara Barat',
  'Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah',
  'Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara',
  'Sulawesi Utara','Gorontalo','Sulawesi Tengah','Sulawesi Barat',
  'Sulawesi Selatan','Sulawesi Tenggara','Maluku','Maluku Utara',
  'Papua','Papua Barat','Papua Selatan','Papua Tengah',
  'Papua Pegunungan','Papua Barat Daya',
];

// ── QUERY DATA ───────────────────────────────────────────────
// Komoditas approved
$resKom  = $conn->query("SELECT * FROM komoditas WHERE status='approved' ORDER BY nama ASC");
$allKom  = [];
$ticker  = '';
$namaUnik = [];

if ($resKom) {
    while ($r = $resKom->fetch_assoc()) {
        $allKom[] = $r;
        $namaUnik[] = $r['nama'];
        $s = (int)$r['harga_sekarang'];
        $k = (int)$r['harga_kemarin'];
        $icon = $s > $k ? '▲' : ($s < $k ? '▼' : '■');
        $col  = $s > $k ? '#10b981' : ($s < $k ? '#ef4444' : '#94a3b8');
        $ticker .= "<span class='t-item'>"
                 . "<span class='t-name'>{$r['nama']} <span class='t-loc'>({$r['lokasi']})</span></span>"
                 . "<span class='t-price'>" . rupiah($s) . "</span>"
                 . "<span class='t-icon' style='color:{$col}'>{$icon}</span>"
                 . "</span>";
    }
}
$namaUnik = array_unique($namaUnik);
$totalKom = count($allKom);

// Artikel published
$resArt  = $conn->query("SELECT * FROM artikel WHERE is_publish=1 ORDER BY created_at DESC LIMIT 8");
$artikel = [];
if ($resArt) while ($a = $resArt->fetch_assoc()) $artikel[] = $a;

// Stats
$totalProv = count(array_unique(array_filter(array_column($allKom, 'provinsi'))));
$totalKont = (int)($conn->query("SELECT COUNT(*) c FROM users WHERE role='kontributor' AND is_active=1")?->fetch_assoc()['c'] ?? 0);
?>
<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
  <?php include 'Assets/head.php'; ?>
  <style>
    /* Live Ticker */
    .ticker-rail { overflow: hidden; }
    .ticker-track { display: flex; width: max-content; }
    .t-item  { display:inline-flex; align-items:center; gap:10px; padding:0 28px; border-right:1px solid var(--border); white-space:nowrap; }
    .t-name  { font-size:.72rem; font-weight:500; color:var(--text-muted); }
    .t-loc   { font-size:.65rem; opacity:.7; }
    .t-price { font-size:.78rem; font-weight:700; color:var(--text-primary); font-family:'Cabinet Grotesk',sans-serif; }
    .t-icon  { font-size:.58rem; }

    /* Article card */
    .art-card { transition: transform .2s ease, box-shadow .2s ease; }
    .art-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }

    /* Province row */
    .prov-row { transition: background .15s, color .15s; }

    /* Hero gradient */
    .hero-bg {
      background: radial-gradient(ellipse 80% 60% at 20% 40%, rgba(16,185,129,.1) 0%, transparent 60%),
                  radial-gradient(ellipse 60% 50% at 80% 20%, rgba(20,184,166,.07) 0%, transparent 55%);
    }
    .dark .hero-bg {
      background: radial-gradient(ellipse 80% 60% at 20% 40%, rgba(16,185,129,.07) 0%, transparent 60%),
                  radial-gradient(ellipse 60% 50% at 80% 20%, rgba(20,184,166,.05) 0%, transparent 55%);
    }

    /* Stat dividers */
    .stat-divider + .stat-divider { border-left: 1px solid var(--border); }
  </style>
</head>
<body class="overflow-x-hidden">

<!-- ══ LIVE TICKER BAR ══════════════════════════════════════ -->
<div class="fixed top-0 w-full z-50 h-9 flex items-center border-b border-[var(--border)]"
     style="background:var(--bg-secondary)">
  <div class="bg-brand-600 h-full px-4 flex items-center gap-2 flex-shrink-0 relative select-none">
    <i data-lucide="radio" class="w-3 h-3 text-white" style="animation:pulseDot 2s ease-in-out infinite"></i>
    <span class="text-white text-[10px] font-bold tracking-widest uppercase font-display">Live</span>
    <!-- Arrow shape -->
    <div class="absolute right-[-8px] top-0 w-0 h-0
                border-t-[18px] border-t-transparent
                border-b-[18px] border-b-transparent
                border-l-[8px] border-l-brand-600 z-10"></div>
  </div>
  <div class="ticker-rail flex-1 h-full flex items-center pl-4 overflow-hidden">
    <?php if ($ticker): ?>
    <div class="ticker-track animate-ticker">
      <?= $ticker . $ticker ?>
    </div>
    <?php else: ?>
    <span class="text-xs text-[var(--text-muted)] pl-2">Belum ada data komoditas.</span>
    <?php endif; ?>
  </div>
</div>

<!-- ══ NAVBAR ════════════════════════════════════════════════ -->
<?php include 'Assets/navbar.php'; ?>

<!-- ══ HERO ══════════════════════════════════════════════════ -->
<section class="relative pt-44 pb-20 hero-bg noise-bg overflow-hidden" aria-label="Hero">
  <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="max-w-3xl">

      <!-- Live badge -->
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold mb-7
                  bg-brand-500/10 border border-brand-500/20 text-brand-500
                  animate-fade-up font-display">
        <span class="w-1.5 h-1.5 bg-brand-500 rounded-full" style="animation:pulseDot 2s ease-in-out infinite"></span>
        <?= $totalKom ?> komoditas aktif &bull; <?= $totalProv ?> provinsi &bull; diperbarui setiap hari
      </div>

      <!-- Headline -->
      <h1 class="font-display font-black text-[var(--text-primary)] leading-[1.04] mb-6
                 text-5xl sm:text-6xl lg:text-7xl
                 animate-fade-up delay-1">
        Transparansi<br/>
        Harga Pangan<br/>
        <span class="text-brand-500 text-glow">Indonesia</span>
      </h1>

      <p class="text-[var(--text-secondary)] text-lg max-w-xl leading-relaxed mb-9
                animate-fade-up delay-2">
        Platform terpercaya untuk memantau pergerakan harga komoditas pertanian, peternakan, dan perikanan dari seluruh provinsi Indonesia — real-time, akurat, dan gratis.
      </p>

      <div class="flex flex-wrap gap-3 animate-fade-up delay-3">
        <a href="chart.php"
           class="flex items-center gap-2 px-6 py-3 rounded-xl font-display font-bold text-sm text-white
                  bg-brand-600 hover:bg-brand-500 transition shadow-xl shadow-brand-600/25 hover:shadow-brand-500/35 hover:-translate-y-0.5">
          <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
          Lihat Grafik Harga
        </a>
        <a href="#artikel"
           class="flex items-center gap-2 px-6 py-3 rounded-xl font-display font-semibold text-sm
                  bg-[var(--surface)] hover:bg-[var(--surface-hover)] border border-[var(--border)]
                  text-[var(--text-primary)] transition hover:-translate-y-0.5">
          Baca Artikel
          <i data-lucide="arrow-down" class="w-4 h-4"></i>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ══ STATS ═════════════════════════════════════════════════ -->
<section class="border-y border-[var(--border)] bg-[var(--bg-secondary)]" aria-label="Statistik">
  <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 md:grid-cols-4">
      <?php
      $stats = [
        ['38', 'Provinsi', 'map'],
        ['120+', 'Komoditas', 'layers'],
        [$totalKont.'K+', 'Kontributor', 'users'],
        ['24/7', 'Update Data', 'zap'],
      ];
      foreach ($stats as $i => [$val, $lbl, $ico]):
      ?>
      <div class="stat-divider py-7 px-6 text-center <?= $i===3?'text-brand-500':'' ?>">
        <div class="font-display font-black text-3xl <?= $i===3?'text-brand-500':'text-[var(--text-primary)]' ?> mb-1">
          <?= $val ?>
        </div>
        <div class="text-[var(--text-muted)] text-xs uppercase tracking-wider flex items-center justify-center gap-1.5">
          <i data-lucide="<?= $ico ?>" class="w-3 h-3"></i><?= $lbl ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ MAIN CONTENT: ARTIKEL + SIDEBAR PROVINSI ═════════════ -->
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-14" id="artikel">
  <div class="flex flex-col lg:flex-row gap-8">

    <!-- ── KOLOM KIRI: ARTIKEL ──────────────────────────── -->
    <main class="flex-1 min-w-0" aria-label="Artikel Edukasi">

      <!-- Section header -->
      <div class="flex items-end justify-between mb-7">
        <div>
          <p class="text-xs text-brand-500 font-bold uppercase tracking-widest mb-1 font-display">Edukasi</p>
          <h2 class="font-display font-black text-2xl text-[var(--text-primary)]">Artikel Komoditas</h2>
        </div>
        <span class="text-xs text-[var(--text-muted)]"><?= count($artikel) ?> artikel</span>
      </div>

      <?php if (!empty($artikel)): ?>
      <div class="space-y-3">
        <?php foreach ($artikel as $art): ?>
        <article class="art-card card card-hover p-5 flex gap-4 cursor-pointer group"
                 onclick="location.href='index.php'"
                 itemscope itemtype="https://schema.org/Article">
          <!-- Icon -->
          <div class="w-12 h-12 flex-shrink-0 rounded-xl bg-[var(--surface)] flex items-center justify-center text-2xl select-none" aria-hidden="true">
            <?= htmlspecialchars($art['emoji']) ?>
          </div>
          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
              <span class="badge badge-green text-[10px]">
                <?= htmlspecialchars($art['kategori']) ?>
              </span>
              <span class="flex items-center gap-1 text-[var(--text-muted)] text-[10px]">
                <i data-lucide="clock" class="w-2.5 h-2.5"></i>
                <?= (int)$art['menit_baca'] ?> menit baca
              </span>
            </div>
            <h3 class="font-display font-bold text-[var(--text-primary)] text-sm leading-snug mb-1.5
                       group-hover:text-brand-500 transition-colors"
                itemprop="headline">
              <?= htmlspecialchars($art['judul']) ?>
            </h3>
            <p class="text-[var(--text-muted)] text-xs leading-relaxed line-clamp-2" itemprop="description">
              <?= htmlspecialchars($art['ringkasan']) ?>
            </p>
          </div>
          <!-- Arrow -->
          <div class="flex-shrink-0 self-center">
            <div class="w-7 h-7 rounded-full bg-[var(--surface)] group-hover:bg-brand-500/15 flex items-center justify-center transition-colors">
              <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-[var(--text-muted)] group-hover:text-brand-500 transition-colors group-hover:translate-x-0.5"></i>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="card p-10 text-center text-[var(--text-muted)] text-sm">
        <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><br>
        Belum ada artikel. Admin dapat menambahkan artikel dari dashboard.
      </div>
      <?php endif; ?>

      <!-- Komoditas Quick Links -->
      <?php if (!empty($namaUnik)): ?>
      <div class="card p-5 mt-6">
        <h3 class="font-display font-bold text-sm text-[var(--text-primary)] mb-4 flex items-center gap-2">
          <i data-lucide="tag" class="w-4 h-4 text-brand-500"></i>
          Komoditas Tersedia
        </h3>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($namaUnik as $nama): ?>
          <a href="chart.php?komoditas=<?= urlencode($nama) ?>"
             class="px-3 py-1.5 rounded-full text-xs font-medium transition
                    bg-[var(--surface)] hover:bg-brand-500/15 border border-[var(--border)]
                    hover:border-brand-500/30 text-[var(--text-secondary)] hover:text-brand-500">
            <?= htmlspecialchars($nama) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Mini SEO blurb (hidden visually but indexed) -->
      <div class="mt-8 p-5 card rounded-2xl" aria-label="Tentang InfoHarga">
        <h2 class="font-display font-bold text-base text-[var(--text-primary)] mb-2">
          Tentang InfoHarga Komoditi
        </h2>
        <p class="text-sm text-[var(--text-secondary)] leading-relaxed">
          InfoHarga Komoditi adalah platform digital yang menyediakan informasi harga komoditas pangan Indonesia secara transparan dan real-time.
          Data kami diperoleh dari kontributor lapangan yang tersebar di seluruh 38 provinsi Indonesia, mulai dari petani, pedagang pasar,
          hingga agen distribusi. Dengan mengakses InfoHarga, Anda dapat memantau pergerakan harga beras, cabai, bawang, minyak goreng,
          daging, ikan, dan ratusan komoditas lainnya untuk mendukung keputusan yang lebih cerdas.
        </p>
      </div>
    </main>

    <!-- ── SIDEBAR KANAN: PROVINSI ───────────────────────── -->
    <aside class="lg:w-72 xl:w-80 flex-shrink-0" aria-label="Daftar Provinsi">
      <div class="card sticky top-28 max-h-[calc(100vh-8rem)] flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="px-5 py-4 border-b border-[var(--border)] flex-shrink-0">
          <h2 class="font-display font-bold text-[var(--text-primary)] text-sm flex items-center gap-2">
            <i data-lucide="map" class="w-4 h-4 text-brand-500"></i>
            Harga per Provinsi
          </h2>
          <p class="text-[10px] text-[var(--text-muted)] mt-0.5 font-medium">38 Provinsi Indonesia</p>
        </div>

        <!-- Search -->
        <div class="px-4 py-3 border-b border-[var(--border)] flex-shrink-0">
          <div class="relative">
            <i data-lucide="search" class="w-3.5 h-3.5 text-[var(--text-muted)] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            <input id="searchProv" type="search" placeholder="Cari provinsi..."
                   class="input-field input-icon py-1.5 text-xs"
                   autocomplete="off" aria-label="Cari provinsi"/>
          </div>
        </div>

        <!-- Province list -->
        <div class="overflow-y-auto flex-1 prov-scroll" id="listProv" role="list">
          <?php foreach ($provinsi as $idx => $prov): ?>
          <a href="chart.php?provinsi=<?= urlencode($prov) ?>"
             class="prov-row flex items-center justify-between px-4 py-2.5
                    border-b border-[var(--border)] hover:bg-[var(--surface)]
                    group" role="listitem"
             data-name="<?= strtolower($prov) ?>">
            <div class="flex items-center gap-3">
              <span class="w-5 h-5 rounded flex items-center justify-center
                           text-[9px] font-bold font-display flex-shrink-0
                           text-[var(--text-muted)] bg-[var(--surface)]
                           group-hover:text-brand-500 group-hover:bg-brand-500/10 transition">
                <?= $idx + 1 ?>
              </span>
              <span class="text-sm text-[var(--text-secondary)] group-hover:text-[var(--text-primary)] transition">
                <?= htmlspecialchars($prov) ?>
              </span>
            </div>
            <i data-lucide="chevron-right"
               class="w-3.5 h-3.5 text-[var(--border)] group-hover:text-brand-500 flex-shrink-0 transition
                      group-hover:translate-x-0.5"></i>
          </a>
          <?php endforeach; ?>
        </div>

        <!-- Footer CTA -->
        <div class="px-5 py-3 border-t border-[var(--border)] flex-shrink-0 bg-[var(--surface)]">
          <a href="chart.php"
             class="flex items-center justify-center gap-2 text-sm font-bold text-brand-500 hover:text-brand-400 transition font-display">
            <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
            Lihat Semua Grafik
          </a>
        </div>
      </div>
    </aside>
  </div>
</div>

<!-- ══ FOOTER ════════════════════════════════════════════════ -->
<?php include 'Assets/footer.php'; ?>

<!-- ══ SCRIPTS ═══════════════════════════════════════════════ -->
<script src="Assets/scripts.js"></script>
<script>
  lucide.createIcons();

  // Province search filter
  document.getElementById('searchProv')?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#listProv .prov-row').forEach(row => {
      row.style.display = !q || row.dataset.name.includes(q) ? '' : 'none';
    });
  });
</script>

<!-- Structured data: WebSite -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "InfoHarga Komoditi",
  "url": "https://infoharga.example.com/",
  "description": "<?= addslashes($pageDesc) ?>",
  "inLanguage": "id-ID",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://infoharga.example.com/chart.php?komoditas={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
</body>
</html>
