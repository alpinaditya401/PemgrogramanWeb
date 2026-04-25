/**
 * Assets/scripts.js — InfoHarga Komoditi v4.0
 */

"use strict";

const Theme = (() => {
  const HTML = document.documentElement;
  const KEY  = 'ih-theme';

  function _apply(mode) {
    if (mode === 'dark') {
      HTML.classList.add('dark');
      HTML.setAttribute('data-theme', 'dark');
    } else {
      HTML.classList.remove('dark');
      HTML.setAttribute('data-theme', 'light');
    }
    document.querySelectorAll('[data-theme-icon="toggle"]').forEach(el => {
      el.setAttribute('data-lucide', mode === 'dark' ? 'sun' : 'moon');
      if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [el] });
    });
  }

  return {
    init() {
      const saved   = localStorage.getItem(KEY);
      const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches;
      _apply(saved ?? (prefers ? 'dark' : 'light'));
    },
    toggle() {
      const next = HTML.classList.contains('dark') ? 'light' : 'dark';
      localStorage.setItem(KEY, next);
      _apply(next);
      document.dispatchEvent(new CustomEvent('themeChanged', { detail: next }));
      return next;
    },
    current() {
      return HTML.classList.contains('dark') ? 'dark' : 'light';
    }
  };
})();

const MSGS = {
  gagal:           '❌ Username atau password salah. Silakan coba lagi.',
  logout:          '✅ Anda telah berhasil keluar.',
  nonaktif:        '⚠️ Akun Anda telah dinonaktifkan. Hubungi admin.',
  empty:           '⚠️ Semua field wajib diisi.',
  email_invalid:   '⚠️ Format email tidak valid.',
  username_short:  '⚠️ Username minimal 4 karakter.',
  password_short:  '⚠️ Password minimal 6 karakter.',
  mismatch:        '⚠️ Konfirmasi password tidak cocok.',
  email_taken:     '⚠️ Email ini sudah terdaftar.',
  username_taken:  '⚠️ Username sudah digunakan, pilih yang lain.',
  already_pending: '⚠️ Data komoditas ini sudah dalam antrian verifikasi.',
  submit_empty:    '⚠️ Lengkapi semua field harga dengan benar.',
  submitted:       '✅ Laporan berhasil dikirim! Menunggu verifikasi admin.',
  saved:           '✅ Data berhasil disimpan.',
  deleted:         '✅ Data berhasil dihapus.',
  updated:         '✅ Perubahan berhasil disimpan.',
  role_updated:    '✅ Role pengguna berhasil diperbarui.',
  pengumuman_saved:'✅ Pengumuman berhasil disimpan.',
  setting_saved:   '✅ Pengaturan berhasil disimpan.',
  artikel_saved:   '✅ Artikel berhasil disimpan.',
};

function readUrlMessages() {
  const p  = new URLSearchParams(location.search);
  const e  = p.get('error') || p.get('pesan');
  const s  = p.get('success');
  const el = document.getElementById('msg-box');
  if (!el) return;

  if (e && MSGS[e]) {
    el.textContent = MSGS[e];
    el.classList.remove('hidden', 'msg-success', 'msg-warning');
    el.classList.add(e === 'logout' ? 'msg-success' : 'msg-error');
  }
  if (s && MSGS[s]) {
    el.textContent = MSGS[s];
    el.classList.remove('hidden', 'msg-error', 'msg-warning');
    el.classList.add('msg-success');
  }
}

function togglePassword(inputId, btnId) {
  const inp = document.getElementById(inputId);
  const btn = document.getElementById(btnId);
  if (!inp) return;
  const isHidden = inp.type === 'password';
  inp.type = isHidden ? 'text' : 'password';
  if (btn) {
    const icon = btn.querySelector('[data-lucide]');
    if (icon) {
      icon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
      lucide.createIcons({ nodes: [icon] });
    }
  }
}

function openModal(id)   { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id)  { document.getElementById(id)?.classList.add('hidden');    }
function toggleModal(id) { document.getElementById(id)?.classList.toggle('hidden'); }

function formatRp(n) {
  return new Intl.NumberFormat('id-ID', {
    style:'currency', currency:'IDR', minimumFractionDigits:0
  }).format(n);
}

function confirmDelete(msg) {
  return confirm(msg || 'Hapus data ini?\n\nTindakan tidak dapat dibatalkan.');
}

function getChartTheme() {
  const dark = Theme.current() === 'dark';
  return {
    textColor:  dark ? '#64748b' : '#94a3b8',
    gridColor:  dark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.06)',
    titleColor: dark ? '#f1f5f9' : '#0f172a',
    bgColor:    dark ? '#0f1318'  : '#ffffff',
  };
}

document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') lucide.createIcons();
  readUrlMessages();

  document.querySelectorAll('[data-action="toggle-theme"]').forEach(btn => {
    btn.addEventListener('click', () => Theme.toggle());
  });

  document.querySelectorAll('[data-modal-close]').forEach(el => {
    el.addEventListener('click', () => closeModal(el.dataset.modalClose));
  });

  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', e => {
      if (!confirmDelete(form.dataset.confirm)) e.preventDefault();
    });
  });

  document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
    document.getElementById('mobileMenu')?.classList.toggle('hidden');
  });
});