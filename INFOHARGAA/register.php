<?php
/**
 * register.php — Daftar Kontributor
 */
session_start();
if (isset($_SESSION['login'])) { header('Location: dashboard-user.php'); exit(); }

$pageTitle = 'Daftar Kontributor';
$pageDesc  = 'Bergabunglah sebagai kontributor lapangan InfoHarga Komoditi dan bantu menyediakan data harga yang akurat.';

$daftarProvinsi = [
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
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'Assets/head.php'; ?>
  <style>
    .auth-bg {
      background: radial-gradient(ellipse 70% 60% at 70% 30%, rgba(59,130,246,.06) 0%, transparent 55%),
                  radial-gradient(ellipse 50% 50% at 20% 70%, rgba(16,185,129,.06) 0%, transparent 50%);
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 auth-bg">

  <a href="index.php" class="fixed top-5 left-5 flex items-center gap-1.5 text-sm text-[var(--text-muted)] hover:text-[var(--text-primary)] transition group">
    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform"></i>
    Kembali
  </a>

  <button data-action="toggle-theme"
          class="fixed top-5 right-5 w-9 h-9 flex items-center justify-center rounded-lg bg-[var(--surface)] hover:bg-[var(--surface-hover)] border border-[var(--border)] text-[var(--text-muted)] hover:text-[var(--text-primary)] transition">
    <i data-lucide="moon" data-theme-icon="toggle" class="w-4 h-4"></i>
  </button>

  <div class="w-full max-w-sm animate-fade-up">

    <!-- Logo -->
    <div class="flex items-center justify-center gap-2.5 mb-8">
      <div class="w-9 h-9 bg-brand-500 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/30">
        <i data-lucide="trending-up" class="w-5 h-5 text-white"></i>
      </div>
      <span class="font-display font-black text-xl text-[var(--text-primary)]">
        InfoHarga<span class="text-brand-500">Komoditi</span>
      </span>
    </div>

    <!-- Card -->
    <div class="card p-7">
      <h1 class="font-display font-black text-2xl text-[var(--text-primary)] mb-1">Daftar Kontributor</h1>
      <p class="text-sm text-[var(--text-muted)] mb-6">Bergabunglah untuk melaporkan harga dari lapangan</p>

      <div id="msg-box" class="hidden mb-5 text-sm"></div>

      <form action="Proses/prosesRegister.php" method="POST" novalidate>

        <!-- Email -->
        <div class="mb-3.5">
          <label for="email" class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Email</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3.5 flex items-center text-[var(--text-muted)] pointer-events-none"><i data-lucide="mail" class="w-4 h-4"></i></span>
            <input id="email" type="email" name="email" class="input-field input-icon" placeholder="nama@email.com" autocomplete="email" required maxlength="120"/>
          </div>
        </div>

        <!-- Username -->
        <div class="mb-3.5">
          <label for="reg-username" class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Username</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3.5 flex items-center text-[var(--text-muted)] pointer-events-none"><i data-lucide="user" class="w-4 h-4"></i></span>
            <input id="reg-username" type="text" name="username" class="input-field input-icon" placeholder="Minimal 4 karakter" autocomplete="username" required minlength="4" maxlength="60"/>
          </div>
        </div>

        <!-- Tanggal lahir -->
        <div class="mb-3.5">
          <label for="tgl_lahir" class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Tanggal Lahir <span class="text-[var(--text-muted)] font-normal normal-case">(opsional)</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3.5 flex items-center text-[var(--text-muted)] pointer-events-none"><i data-lucide="calendar" class="w-4 h-4"></i></span>
            <input id="tgl_lahir" type="date" name="tgl_lahir" class="input-field input-icon" style="color-scheme:dark light"/>
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3.5">
          <label for="reg-pw" class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3.5 flex items-center text-[var(--text-muted)] pointer-events-none"><i data-lucide="lock" class="w-4 h-4"></i></span>
            <input id="reg-pw" type="password" name="password" class="input-field input-icon pr-11" placeholder="Minimal 6 karakter" required minlength="6" autocomplete="new-password"/>
            <button type="button" id="togglePw1" onclick="togglePassword('reg-pw','togglePw1')"
                    class="absolute inset-y-0 right-3.5 flex items-center text-[var(--text-muted)] hover:text-[var(--text-primary)] transition" aria-label="Tampilkan password">
              <i data-lucide="eye" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <!-- Konfirmasi password -->
        <div class="mb-6">
          <label for="reg-pw2" class="block text-xs font-bold text-[var(--text-secondary)] uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3.5 flex items-center text-[var(--text-muted)] pointer-events-none"><i data-lucide="lock" class="w-4 h-4"></i></span>
            <input id="reg-pw2" type="password" name="konfirmasi" class="input-field input-icon pr-11" placeholder="Ulangi password" required autocomplete="new-password"/>
            <button type="button" id="togglePw2" onclick="togglePassword('reg-pw2','togglePw2')"
                    class="absolute inset-y-0 right-3.5 flex items-center text-[var(--text-muted)] hover:text-[var(--text-primary)] transition" aria-label="Tampilkan konfirmasi">
              <i data-lucide="eye" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <button type="submit" name="register"
                class="w-full py-3 bg-brand-600 hover:bg-brand-500 text-white font-display font-bold rounded-xl text-sm transition shadow-lg shadow-brand-600/20 hover:-translate-y-0.5">
          Daftar &amp; Masuk
        </button>
      </form>

      <div class="flex items-center gap-3 my-5">
        <div class="flex-1 h-px bg-[var(--border)]"></div>
        <span class="text-[var(--text-muted)] text-xs">atau</span>
        <div class="flex-1 h-px bg-[var(--border)]"></div>
      </div>
      <p class="text-center text-sm text-[var(--text-muted)]">
        Sudah punya akun?
        <a href="login.php" class="text-brand-500 font-bold hover:text-brand-400 transition">Login di sini</a>
      </p>
    </div>

    <p class="text-center text-xs text-[var(--text-muted)] mt-5">&copy; <?= date('Y') ?> InfoHarga Komoditi</p>
  </div>

  <script src="Assets/scripts.js"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
