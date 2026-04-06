<?php
// Hubungkan ke database
require 'server/koneksi.php';

// Ambil data dari database
$query = mysqli_query($conn, "SELECT * FROM komoditas ORDER BY komoditas ASC");

$data_komoditas = [];
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $kemarin = (int)$row['kemarin'];
        $sekarang = (int)$row['sekarang'];
        
        // Simulasi matematika untuk data H-6 sampai H-2 agar grafik (Chart) tetap bisa berjalan
        $history_palsu = [
            $kemarin - 1000, // H-6
            $kemarin - 500,  // H-5
            $kemarin,        // H-4
            $kemarin + 200,  // H-3
            $kemarin - 100,  // H-2
            $kemarin,        // Kemarin
            $sekarang        // Hari Ini
        ];

        // Masukkan data dari database ke format array yang dibaca oleh script.js kita
        $data_komoditas[] = [
            "komoditas" => $row['komoditas'],
            "lokasi" => $row['lokasi'],
            "kemarin" => $kemarin,
            "sekarang" => $sekarang,
            "history" => $history_palsu
        ];
    }
}
?>

<!doctype html>
<html lang="id" class="scroll-smooth">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>InfoHarga Komoditi - Pantau Harga Pangan Real-time</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ["Inter", "sans-serif"],
            },
          },
        },
      };
    </script>
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased selection:bg-emerald-200">
    
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200 transition-all duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
          <div class="flex items-center gap-2 cursor-pointer">
            <div class="bg-emerald-500 p-2.5 rounded-xl shadow-lg shadow-emerald-500/30">
              <i data-lucide="line-chart" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-bold text-2xl tracking-tight text-slate-900">InfoHarga<span class="text-emerald-500">Komoditi</span></span>
          </div>

          <div class="hidden md:flex items-center space-x-8">
            <a href="#harga-pasar" class="font-medium text-slate-600 hover:text-emerald-600 transition">Harga Pasar</a>
            <a href="login.php" class="font-medium text-slate-600 hover:text-emerald-600 transition">Login Admin</a>
          </div>

          <div class="md:hidden flex items-center">
            <button class="text-slate-600 hover:text-slate-900 focus:outline-none">
              <i data-lucide="menu" class="w-7 h-7"></i>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <section class="relative pt-32 pb-16 lg:pt-40 lg:pb-24 overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-4xl mx-auto">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 font-semibold text-sm mb-6 border border-emerald-200">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            Data Diperbarui Real-time
          </div>

          <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 tracking-tight mb-8 leading-tight">
            Pantau Harga Pangan <br class="hidden md:block" />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-400">Lebih Cerdas & Akurat</span>
          </h1>

          <p class="text-lg md:text-xl text-slate-600 mb-10 max-w-2xl mx-auto leading-relaxed">Platform terpercaya untuk memantau pergerakan harga komoditas di seluruh wilayah Indonesia.</p>

          <div class="flex justify-center items-center gap-4">
            <a href="#harga-pasar" class="bg-emerald-600 hover:bg-emerald-500 text-white px-8 py-4 rounded-full font-semibold text-lg transition shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:-translate-y-1 flex items-center gap-2">
              Lihat Harga Hari Ini <i data-lucide="arrow-down" class="w-5 h-5"></i>
            </a>
          </div>
        </div>
      </div>
    </section>

    <section id="harga-pasar" class="py-16 bg-white border-y border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
          <div>
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Pantauan Harga Pasar</h2>
            <p class="text-slate-500">Pergerakan harga komoditas berdasarkan data dari database admin.</p>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <select id="filterKomoditas" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer font-medium"></select>
            <select id="filterLokasi" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer font-medium"></select>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
          <div class="w-full h-[350px] relative">
            <canvas id="publicChart"></canvas>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50 text-slate-600 text-sm uppercase tracking-wider border-b border-slate-200">
                  <th class="p-5 font-semibold">Komoditas</th>
                  <th class="p-5 font-semibold">Lokasi</th>
                  <th class="p-5 font-semibold">Harga Kemarin</th>
                  <th class="p-5 font-semibold">Harga Sekarang</th>
                  <th class="p-5 font-semibold">Status</th>
                </tr>
              </thead>
              <tbody id="public-tabel-komoditas" class="divide-y divide-slate-100">
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <footer class="bg-slate-950 text-slate-300 py-12 border-t border-slate-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="flex items-center gap-2">
          <i data-lucide="line-chart" class="w-6 h-6 text-emerald-500"></i>
          <span class="font-bold text-xl tracking-tight text-white">InfoHarga<span class="text-emerald-500">Komoditi</span></span>
        </div>
        <div class="text-sm text-slate-500">&copy; 2024 InfoHarga Komoditi. Seluruh hak cipta dilindungi.</div>
      </div>
    </footer>

    <script>
      // Melempar data dari PHP ke Javascript untuk Chart dan Tabel
      window.dbData = <?php echo json_encode($data_komoditas); ?>;
    </script>
    <script src="assets/scripts.js"></script>
  </body>
</html>