<?php
session_start();
// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - InfoHarga Komoditi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: { extend: { fontFamily: { sans: ["Inter", "sans-serif"] } } }
      };
    </script>
  </head>
  <body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 text-slate-800">
    <div class="w-full max-w-md relative">
      <div class="bg-white rounded-2xl shadow-xl p-8 sm:p-10 border border-slate-100">
        <h3 class="text-3xl font-bold mb-2 text-center text-slate-900">Selamat Datang</h3>
        <p class="text-slate-500 text-center mb-8">Masuk untuk mengelola data komoditas</p>

        <form action="proses/prosesLogin.php" method="POST">
          <div class="mb-4">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
            <input type="text" name="username" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="Masukkan username" required />
          </div>
          
          <div class="mb-8">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="••••••••" required />
          </div>

          <button type="submit" name="login" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-emerald-500/30 transition-all mb-4">Login Sekarang</button>
          
          <a href="register.php" class="block text-center w-full bg-white border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-600 font-semibold py-3 px-4 rounded-xl transition-all">Belum punya akun? Daftar</a>
        </form>
      </div>
    </div>
  </body>
</html>