<?php
// Fallback jika APP_NAME belum didefinisikan
if (!defined('APP_NAME')) define('APP_NAME', 'InfoHarga Komoditi');
/**
 * Assets/head.php
 * ─────────────────────────────────────────────────────────────
 * Shared <head> partial yang di-include di SEMUA halaman.
 *
 * Sebelum include, set variabel berikut di file pemanggil:
 *   $pageTitle    (string) — judul halaman
 *   $pageDesc     (string) — deskripsi SEO
 *   $pageKeywords (string) — kata kunci SEO
 *
 * File ini mengandung:
 * 1. Meta tags (SEO, OpenGraph, Twitter Card)
 * 2. Google Fonts (Cabinet Grotesk + Instrument Sans)
 * 3. Tailwind CSS via CDN
 * 4. Lucide Icons via CDN
 * 5. Script dark mode ANTI-FLICKER (harus sebelum body render)
 * 6. CSS Custom Properties untuk tema light/dark
 * 7. Komponen-komponen CSS reusable (card, badge, input, table, dll)
 * ─────────────────────────────────────────────────────────────
 */

$pageTitle    = isset($pageTitle)    ? htmlspecialchars($pageTitle)    : APP_NAME;
$pageDesc     = isset($pageDesc)     ? htmlspecialchars($pageDesc)     : 'Platform transparansi harga komoditas pangan Indonesia real-time.';
$pageKeywords = isset($pageKeywords) ? htmlspecialchars($pageKeywords) : 'harga komoditas, beras, cabai, minyak goreng, Indonesia';
?>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>

<!-- SEO -->
<title><?= $pageTitle === APP_NAME ? APP_NAME : $pageTitle . ' — ' . APP_NAME ?></title>
<meta name="description"  content="<?= $pageDesc ?>"/>
<meta name="keywords"     content="<?= $pageKeywords ?>"/>
<meta name="author"       content="<?= APP_NAME ?>"/>
<meta name="robots"       content="index, follow"/>

<!-- OpenGraph (untuk share di WhatsApp, Facebook, dll) -->
<meta property="og:title"       content="<?= $pageTitle ?> — <?= APP_NAME ?>"/>
<meta property="og:description" content="<?= $pageDesc ?>"/>
<meta property="og:type"        content="website"/>
<meta property="og:locale"      content="id_ID"/>

<!-- Fonts: Cabinet Grotesk (judul) + Instrument Sans (teks biasa) -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Cabinet+Grotesk:wght@400;500;600;700;800;900&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet"/>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Lucide Icons (ikon SVG yang ringan) -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<!-- Chart.js untuk grafik (dimuat di HEAD agar siap sebelum script chart) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!--
  DARK MODE ANTI-FLICKER SCRIPT
  ─────────────────────────────
  Script ini HARUS dijalankan sebelum browser merender halaman.
  Tujuannya: membaca preferensi tema dari localStorage dan langsung
  menambahkan class 'dark' ke <html> SEBELUM halaman tampil,
  sehingga tidak ada "kedipan putih" saat halaman loading.
-->
<script>
(function(){
  var s = localStorage.getItem('ih-theme');
  var p = window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (s === 'dark' || (!s && p)) {
    document.documentElement.classList.add('dark');
    document.documentElement.setAttribute('data-theme','dark');
  } else {
    document.documentElement.classList.remove('dark');
    document.documentElement.setAttribute('data-theme','light');
  }
})();
</script>

<!-- Tailwind Config: dark mode berbasis class, custom fonts & warna -->
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
        // Brand color (emerald)
        brand: {
          50:'#ecfdf5', 100:'#d1fae5', 200:'#a7f3d0', 300:'#6ee7b7',
          400:'#34d399', 500:'#10b981', 600:'#059669', 700:'#047857',
        }
      },
      animation: {
        'fade-up':  'fadeUp .45s ease both',
        'fade-in':  'fadeIn .3s ease both',
        'ticker':   'ticker 45s linear infinite',
        'pulse-dot':'pulseDot 2s ease-in-out infinite',
      },
      keyframes: {
        fadeUp:   {from:{opacity:'0',transform:'translateY(14px)'},to:{opacity:'1',transform:'translateY(0)'}},
        fadeIn:   {from:{opacity:'0'},to:{opacity:'1'}},
        ticker:   {'0%':{transform:'translateX(0)'},'100%':{transform:'translateX(-50%)'}},
        pulseDot: {'0%,100%':{opacity:'1',transform:'scale(1)'},'50%':{opacity:'.5',transform:'scale(.8)'}},
      }
    }
  }
};
</script>

<!--
  CSS GLOBAL & KOMPONEN REUSABLE
  ──────────────────────────────
  Semua style di sini menggunakan CSS Custom Properties (variables)
  yang otomatis berubah saat tema berganti light/dark.

  Cara kerja dark mode:
  - Light mode: variabel diset ke nilai terang (--bg-primary: #f8fafc)
  - Dark mode:  variabel diset ke nilai gelap  (--bg-primary: #0b0e14)
  - Tailwind class 'dark' di <html> mengaktifkan blok .dark {...}
-->
<link rel="stylesheet" href="head.css">
