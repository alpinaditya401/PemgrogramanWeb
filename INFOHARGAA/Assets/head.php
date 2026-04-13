<?php
/**
 * Assets/head.php
 * Shared <head> partial. Include after setting:
 *   $pageTitle, $pageDesc (optional), $pageKeywords (optional)
 *
 * Usage:
 *   $pageTitle = 'Dashboard Admin';
 *   $pageDesc  = 'Panel manajemen data komoditas.';
 *   include 'Assets/head.php';
 */

$pageTitle    = isset($pageTitle)    ? htmlspecialchars($pageTitle)    : 'InfoHarga Komoditi';
$pageDesc     = isset($pageDesc)     ? htmlspecialchars($pageDesc)     : 'Platform transparansi harga komoditas pangan Indonesia. Data real-time dari kontributor lapangan di 38 provinsi.';
$pageKeywords = isset($pageKeywords) ? htmlspecialchars($pageKeywords) : 'harga komoditas, harga pangan, beras, cabai, bawang, minyak goreng, Indonesia';
$canonicalURL = isset($canonicalURL) ? htmlspecialchars($canonicalURL) : 'https://infoharga.example.com/';
$ogImage      = isset($ogImage)      ? htmlspecialchars($ogImage)      : 'https://infoharga.example.com/Assets/og-image.png';
?>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>

<!-- ── SEO ─────────────────────────────────────────────── -->
<title><?= $pageTitle ?> — InfoHarga Komoditi</title>
<meta name="description"  content="<?= $pageDesc ?>"/>
<meta name="keywords"     content="<?= $pageKeywords ?>"/>
<meta name="author"       content="InfoHarga Komoditi"/>
<meta name="robots"       content="index, follow"/>
<link rel="canonical"     href="<?= $canonicalURL ?>"/>

<!-- ── Open Graph / Social ─────────────────────────────── -->
<meta property="og:type"        content="website"/>
<meta property="og:url"         content="<?= $canonicalURL ?>"/>
<meta property="og:title"       content="<?= $pageTitle ?> — InfoHarga Komoditi"/>
<meta property="og:description" content="<?= $pageDesc ?>"/>
<meta property="og:image"       content="<?= $ogImage ?>"/>
<meta property="og:locale"      content="id_ID"/>
<meta property="og:site_name"   content="InfoHarga Komoditi"/>
<meta name="twitter:card"       content="summary_large_image"/>
<meta name="twitter:title"      content="<?= $pageTitle ?> — InfoHarga Komoditi"/>
<meta name="twitter:description"content="<?= $pageDesc ?>"/>
<meta name="twitter:image"      content="<?= $ogImage ?>"/>

<!-- ── Favicon ─────────────────────────────────────────── -->
<link rel="icon" href="Assets/favicon.svg" type="image/svg+xml"/>

<!-- ── Fonts ────────────────────────────────────────────── -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Cabinet+Grotesk:wght@400;500;600;700;800;900&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet"/>

<!-- ── Tailwind ─────────────────────────────────────────── -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Dark mode: MUST run before <body> renders -->
<script>
(function(){
  var s = localStorage.getItem('ih-theme');
  var p = window.matchMedia('(prefers-color-scheme: dark)').matches;
  if(s === 'dark' || (!s && p)){
    document.documentElement.classList.add('dark');
    document.documentElement.setAttribute('data-theme','dark');
  } else {
    document.documentElement.setAttribute('data-theme','light');
  }
})();
</script>

<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        display: ['"Cabinet Grotesk"', 'sans-serif'],
        body:    ['"Instrument Sans"', 'sans-serif'],
      },
      colors: {
        brand: {
          50:  '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
          300: '#6ee7b7', 400: '#34d399', 500: '#10b981',
          600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
        }
      },
      animation: {
        'fade-up':   'fadeUp .5s ease both',
        'fade-in':   'fadeIn .4s ease both',
        'slide-in':  'slideIn .3s ease both',
        'ticker':    'ticker 45s linear infinite',
        'pulse-dot': 'pulseDot 2s ease-in-out infinite',
      },
      keyframes: {
        fadeUp:   { from:{opacity:0,transform:'translateY(16px)'}, to:{opacity:1,transform:'translateY(0)'} },
        fadeIn:   { from:{opacity:0}, to:{opacity:1} },
        slideIn:  { from:{opacity:0,transform:'translateX(-10px)'}, to:{opacity:1,transform:'translateX(0)'} },
        ticker:   { '0%':{transform:'translateX(0)'}, '100%':{transform:'translateX(-50%)'} },
        pulseDot: { '0%,100%':{opacity:1,transform:'scale(1)'}, '50%':{opacity:.5,transform:'scale(.8)'} },
      }
    }
  }
};
</script>

<!-- ── Shared CSS Variables & Utilities ─────────────────── -->
<style>
  /* Fonts */
  body { font-family: 'Instrument Sans', sans-serif; }
  h1,h2,h3,h4,h5,h6,.font-display { font-family: 'Cabinet Grotesk', sans-serif; }

  /* CSS Custom Properties — Light / Dark */
  :root {
    --bg-primary:    #f8fafc;
    --bg-secondary:  #f1f5f9;
    --bg-card:       #ffffff;
    --bg-card-hover: #f8fafc;
    --border:        rgba(0,0,0,.08);
    --border-hover:  rgba(0,0,0,.15);
    --text-primary:  #0f172a;
    --text-secondary:#475569;
    --text-muted:    #94a3b8;
    --surface:       rgba(0,0,0,.04);
    --surface-hover: rgba(0,0,0,.07);
    --shadow-sm:     0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
    --shadow-md:     0 4px 16px rgba(0,0,0,.1);
    --shadow-lg:     0 10px 40px rgba(0,0,0,.12);
  }
  .dark {
    --bg-primary:    #0b0e14;
    --bg-secondary:  #060810;
    --bg-card:       #0f1318;
    --bg-card-hover: #141a22;
    --border:        rgba(255,255,255,.07);
    --border-hover:  rgba(255,255,255,.13);
    --text-primary:  #f1f5f9;
    --text-secondary:#94a3b8;
    --text-muted:    #475569;
    --surface:       rgba(255,255,255,.04);
    --surface-hover: rgba(255,255,255,.07);
    --shadow-sm:     0 1px 3px rgba(0,0,0,.4);
    --shadow-md:     0 4px 16px rgba(0,0,0,.5);
    --shadow-lg:     0 10px 40px rgba(0,0,0,.6);
  }

  /* Global base */
  body {
    background: var(--bg-primary);
    color: var(--text-primary);
    transition: background .25s, color .25s;
    -webkit-font-smoothing: antialiased;
  }

  /* Scrollbars */
  ::-webkit-scrollbar { width: 6px; height: 6px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 6px; }
  ::-webkit-scrollbar-thumb:hover { background: var(--border-hover); }

  /* Cards */
  .card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1rem;
    transition: background .2s, border-color .2s, box-shadow .2s;
  }
  .card-hover:hover {
    background: var(--bg-card-hover);
    border-color: var(--border-hover);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
  }

  /* Input fields */
  .input-field {
    width: 100%;
    padding: .625rem 1rem;
    border-radius: .75rem;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text-primary);
    font-size: .875rem;
    outline: none;
    transition: border-color .2s, background .2s, box-shadow .2s;
    font-family: 'Instrument Sans', sans-serif;
  }
  .input-field:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16,185,129,.12);
    background: var(--bg-card);
  }
  .input-field::placeholder { color: var(--text-muted); }

  .input-icon { padding-left: 2.75rem; }

  /* Badges */
  .badge-green  { background:rgba(16,185,129,.1); color:#10b981; border:1px solid rgba(16,185,129,.2); }
  .badge-red    { background:rgba(239,68,68,.1);  color:#ef4444; border:1px solid rgba(239,68,68,.2); }
  .badge-amber  { background:rgba(245,158,11,.1); color:#f59e0b; border:1px solid rgba(245,158,11,.2); }
  .badge-slate  { background:var(--surface); color:var(--text-secondary); border:1px solid var(--border); }
  .badge        { display:inline-flex; align-items:center; gap:.25rem; padding:.2rem .6rem; border-radius:999px; font-size:.7rem; font-weight:700; font-family:'Cabinet Grotesk',sans-serif; }

  /* Messages */
  .msg-error   { background:rgba(239,68,68,.08); color:#ef4444; border:1px solid rgba(239,68,68,.2); border-radius:.75rem; padding:.75rem 1rem; font-size:.875rem; }
  .msg-success { background:rgba(16,185,129,.08); color:#10b981; border:1px solid rgba(16,185,129,.2); border-radius:.75rem; padding:.75rem 1rem; font-size:.875rem; }

  /* Ticker */
  .ticker-track { animation: ticker 45s linear infinite; }
  .ticker-track:hover { animation-play-state: paused; }

  /* Province list scrollbar */
  .prov-scroll { scrollbar-width: thin; scrollbar-color: var(--border) transparent; }

  /* Table */
  .data-table { border-collapse: collapse; width: 100%; }
  .data-table th { background: var(--surface); color: var(--text-muted); font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:.75rem 1.25rem; text-align:left; border-bottom:1px solid var(--border); }
  .data-table td { padding:.875rem 1.25rem; border-bottom:1px solid var(--border); font-size:.875rem; color:var(--text-secondary); vertical-align:middle; }
  .data-table tbody tr:hover td { background: var(--surface); }
  .data-table tbody tr:last-child td { border-bottom: none; }

  /* Sidebar (admin) */
  .sidebar-nav a {
    display:flex; align-items:center; gap:.75rem;
    padding:.6rem .875rem; border-radius:.625rem;
    font-size:.875rem; font-weight:500; color:var(--text-secondary);
    transition:background .15s, color .15s;
    text-decoration:none;
  }
  .sidebar-nav a:hover { background:var(--surface); color:var(--text-primary); }
  .sidebar-nav a.active { background:rgba(16,185,129,.1); color:#10b981; }

  /* Glow ambient */
  .glow-teal  { box-shadow: 0 0 80px rgba(16,185,129,.12); }
  .text-glow  { text-shadow: 0 0 40px rgba(16,185,129,.35); }

  /* Noise texture overlay */
  .noise-bg::before {
    content: ''; position: absolute; inset: 0; opacity: .025; pointer-events: none; z-index: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
  }

  /* Stagger delays */
  .delay-1{animation-delay:.08s}.delay-2{animation-delay:.16s}.delay-3{animation-delay:.24s}.delay-4{animation-delay:.32s}.delay-5{animation-delay:.4s}

  /* Focus ring */
  *:focus-visible { outline: 2px solid #10b981; outline-offset: 2px; border-radius: 4px; }
</style>
