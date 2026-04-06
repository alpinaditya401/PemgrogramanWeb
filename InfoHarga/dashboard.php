<?php
session_start();

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 2. KONEKSI DATABASE
require 'server/koneksi.php';

// ==========================================
// LOGIKA TAMBAH DATA (BARU)
// ==========================================
if (isset($_POST['simpan_data'])) {
    // Ambil dan amankan data dari form
    $komoditas = mysqli_real_escape_string($conn, $_POST['komoditas']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $kemarin = (int)$_POST['kemarin'];
    $sekarang = (int)$_POST['sekarang'];

    // Simpan ke database
    $query_insert = "INSERT INTO komoditas (komoditas, lokasi, kemarin, sekarang) 
                     VALUES ('$komoditas', '$lokasi', '$kemarin', '$sekarang')";
    
    if (mysqli_query($conn, $query_insert)) {
        echo "<script>
                alert('Data komoditas berhasil ditambahkan!');
                window.location.href = 'dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menambahkan data!');
              </script>";
    }
}
// ==========================================
// ==========================================
// LOGIKA HAPUS DATA (BARU)
// ==========================================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $query_hapus = "DELETE FROM komoditas WHERE id = $id_hapus";
    
    if (mysqli_query($conn, $query_hapus)) {
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location.href = 'dashboard.php';
              </script>";
    }
}
// ==========================================
// 3. AMBIL DATA DARI DATABASE
$query = "SELECT * FROM komoditas ORDER BY komoditas ASC, lokasi ASC";
$result = mysqli_query($conn, $query);

// Menghitung total data untuk statistik
$total_komoditas = mysqli_num_rows($result);
?>

<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin - InfoHarga</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

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
  <body class="bg-slate-50 text-slate-800 antialiased flex h-screen overflow-hidden">

    <aside class="w-64 bg-slate-900 text-slate-300 hidden md:flex flex-col h-full shrink-0">
      <div class="h-20 flex items-center px-6 border-b border-slate-800">
        <div class="flex items-center gap-2">
          <i data-lucide="line-chart" class="w-6 h-6 text-emerald-500"></i>
          <span class="font-bold text-xl tracking-tight text-white">InfoHarga<span class="text-emerald-500">Admin</span></span>
        </div>
      </div>

      <div class="flex-1 py-6 px-4 space-y-2">
        <a href="#" class="flex items-center gap-3 px-4 py-3 bg-emerald-600/10 text-emerald-500 rounded-xl font-medium transition-colors">
          <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
        </a>
        <a href="index.php" target="_blank" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl font-medium transition-colors">
          <i data-lucide="globe" class="w-5 h-5"></i> Lihat Website
        </a>
      </div>

      <div class="p-4 border-t border-slate-800">
        <a href="proses/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:bg-red-500/10 rounded-xl font-medium transition-colors">
          <i data-lucide="log-out" class="w-5 h-5"></i> Logout
        </a>
      </div>
    </aside>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
      
      <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0">
        <div class="flex items-center gap-4">
          <button class="md:hidden text-slate-500 hover:text-slate-900">
            <i data-lucide="menu" class="w-6 h-6"></i>
          </button>
          <h1 class="text-xl font-bold text-slate-800">Dashboard</h1>
        </div>
        
        <div class="flex items-center gap-3">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-slate-900"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
            <p class="text-xs text-slate-500">Administrator</p>
          </div>
          <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 border border-emerald-200">
            <i data-lucide="user" class="w-5 h-5"></i>
          </div>
        </div>
      </header>

      <div class="flex-1 overflow-y-auto p-6 lg:p-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
              <i data-lucide="database" class="w-7 h-7 text-blue-600"></i>
            </div>
            <div>
              <p class="text-slate-500 text-sm font-medium mb-1">Total Data Masuk</p>
              <h3 class="text-2xl font-bold text-slate-900"><?= $total_komoditas; ?> <span class="text-sm font-normal text-slate-500">Item</span></h3>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
          <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Kelola Harga Komoditas</h2>
              <p class="text-sm text-slate-500">Daftar harga pasar saat ini.</p>
            </div>
            <button onclick="toggleModal('modalTambah')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl font-medium transition shadow-md shadow-emerald-500/20 flex items-center gap-2 cursor-pointer">
              <i data-lucide="plus" class="w-4 h-4"></i> Tambah Data
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                  <th class="p-4 font-semibold w-12 text-center">No</th>
                  <th class="p-4 font-semibold">Komoditas</th>
                  <th class="p-4 font-semibold">Lokasi</th>
                  <th class="p-4 font-semibold">Harga Kemarin</th>
                  <th class="p-4 font-semibold">Harga Sekarang</th>
                  <th class="p-4 font-semibold text-center w-32">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <?php if ($total_komoditas > 0): ?>
                  <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-slate-50 transition">
                      <td class="p-4 text-center text-slate-500"><?= $no++; ?></td>
                      <td class="p-4 font-bold text-slate-900"><?= htmlspecialchars($row['komoditas']); ?></td>
                      <td class="p-4 text-slate-600">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($row['lokasi']); ?>" target="_blank" 
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 transition cursor-pointer shadow-sm" title="Lihat di Google Maps">
                        <i data-lucide="map-pin" class="w-3 h-3"></i> <?= htmlspecialchars($row['lokasi']); ?>
                      </a>
                    </td>
                    <td class="p-4 text-slate-500">Rp <?= number_format($row['kemarin'], 0, ',', '.'); ?></td>
                      <td class="p-4 font-bold text-emerald-600">Rp <?= number_format($row['sekarang'], 0, ',', '.'); ?></td>
                     <td class="p-4">
                      <div class="flex items-center justify-center gap-2">
                        <a href="edit.php?id=<?= $row['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition tooltip" title="Edit">
                          <i data-lucide="pencil" class="w-4 h-4"></i>
                        </a>
                        <a href="dashboard.php?hapus=<?= $row['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data <?= htmlspecialchars($row['komoditas']); ?>?')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition tooltip" title="Hapus">
                          <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </a>
                      </div>
                    </td>
                          <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition tooltip" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="p-8 text-center text-slate-500">
                      <div class="flex flex-col items-center justify-center">
                        <i data-lucide="inbox" class="w-12 h-12 text-slate-300 mb-3"></i>
                        <p>Belum ada data komoditas di database.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>

    <div id="modalTambah" class="hidden fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="toggleModal('modalTambah')"></div>
      <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
          
          <div class="bg-white px-6 py-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-900">Tambah Data Komoditas</h3>
            <button onclick="toggleModal('modalTambah')" class="text-slate-400 hover:text-slate-600 transition">
              <i data-lucide="x" class="w-5 h-5"></i>
            </button>
          </div>
          
          <form action="" method="POST">
            <div class="bg-white px-6 py-5">
              <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Komoditas</label>
                <input type="text" name="komoditas" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="Contoh: Bawang Putih" required />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi / Kota</label>
                <input type="text" name="lokasi" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="Contoh: Pasar Induk" required />
              </div>
              <div class="grid grid-cols-2 gap-4 mb-2">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">Harga Kemarin (Rp)</label>
                  <input type="number" name="kemarin" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="10000" required />
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">Harga Sekarang (Rp)</label>
                  <input type="number" name="sekarang" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="12000" required />
                </div>
              </div>
            </div>
            
            <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-2xl border-t border-slate-100">
              <button type="submit" name="simpan_data" class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition shadow-sm">Simpan Data</button>
              <button type="button" onclick="toggleModal('modalTambah')" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-5 py-2.5 rounded-xl font-semibold text-sm transition shadow-sm">Batal</button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <script>
      lucide.createIcons();

      // Script sederhana untuk membuka dan menutup modal
      function toggleModal(modalID) {
        document.getElementById(modalID).classList.toggle("hidden");
      }
    </script>
  </body>
</html>