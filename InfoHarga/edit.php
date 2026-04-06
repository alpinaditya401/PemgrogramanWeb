<?php
session_start();

// Proteksi Halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

require 'server/koneksi.php';

// 1. CEK ID YANG DIKIRIM
// Jika tidak ada ID di URL, tendang kembali ke dashboard
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = (int)$_GET['id'];

// 2. AMBIL DATA LAMA DARI DATABASE
$query = mysqli_query($conn, "SELECT * FROM komoditas WHERE id = $id");
$data = mysqli_fetch_assoc($query);

// Jika datanya tidak ditemukan di database
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}

// 3. LOGIKA UPDATE (SIMPAN PERUBAHAN)
if (isset($_POST['update_data'])) {
    $komoditas = mysqli_real_escape_string($conn, $_POST['komoditas']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $kemarin = (int)$_POST['kemarin'];
    $sekarang = (int)$_POST['sekarang'];

    $query_update = "UPDATE komoditas SET 
                        komoditas = '$komoditas', 
                        lokasi = '$lokasi', 
                        kemarin = '$kemarin', 
                        sekarang = '$sekarang' 
                     WHERE id = $id";
    
    if (mysqli_query($conn, $query_update)) {
        echo "<script>
                alert('Data berhasil diperbarui!');
                window.location.href = 'dashboard.php';
              </script>";
    } else {
        echo "<script>alert('Gagal memperbarui data!');</script>";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Data - InfoHarga</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ["Inter", "sans-serif"] } } },
    };
  </script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
    
    <div class="bg-slate-900 px-6 py-5 flex items-center gap-3">
      <i data-lucide="pencil" class="w-5 h-5 text-emerald-400"></i>
      <h2 class="text-lg font-bold text-white">Edit Data Komoditas</h2>
    </div>

    <form action="" method="POST">
      <div class="p-6">
        <div class="mb-4">
          <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Komoditas</label>
          <input type="text" name="komoditas" value="<?= htmlspecialchars($data['komoditas']); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none transition" required />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi / Kota</label>
          <input type="text" name="lokasi" value="<?= htmlspecialchars($data['lokasi']); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none transition" required />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-2">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Harga Kemarin (Rp)</label>
            <input type="number" name="kemarin" value="<?= $data['kemarin']; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none transition" required />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Harga Sekarang (Rp)</label>
            <input type="number" name="sekarang" value="<?= $data['sekarang']; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none transition" required />
          </div>
        </div>
      </div>
      
      <div class="bg-slate-50 px-6 py-4 flex justify-between items-center border-t border-slate-100">
        <a href="dashboard.php" class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2 transition">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <button type="submit" name="update_data" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition shadow-sm flex items-center gap-2">
          <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>