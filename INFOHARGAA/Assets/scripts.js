/**
 * Assets/scripts.js — InfoHarga Komoditi v3.0
 * Shared JavaScript: Theme, Dark Mode, Utilities, Security
 */

"use strict";

/* ── THEME MANAGER ─────────────────────────────────────── */
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
    _updateIcons(mode);
  }

  function _updateIcons(mode) {
    document.querySelectorAll('[data-theme-icon]').forEach(el => {
      const icon = el.getAttribute('data-theme-icon');
      if (icon === 'toggle') {
        el.setAttribute('data-lucide', mode === 'dark' ? 'sun' : 'moon');
        if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [el] });
      }
    });
  }

  function init() {
    const saved   = localStorage.getItem(KEY);
    const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches;
    _apply(saved ?? (prefers ? 'dark' : 'light'));
  }

  function toggle() {
    const current = HTML.classList.contains('dark') ? 'dark' : 'light';
    const next    = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem(KEY, next);
    _apply(next);
    return next;
  }

  function current() {
    return HTML.classList.contains('dark') ? 'dark' : 'light';
  }

  return { init, toggle, current };
})();

// Run immediately to prevent flash
Theme.init();

/* ── ERROR MESSAGES ─────────────────────────────────────── */
const ERRORS = {
  // Auth
  gagal:           '❌ Username atau password salah.',
  logout:          '✅ Anda telah berhasil keluar.',
  empty:           '⚠️ Mohon lengkapi semua field yang wajib diisi.',
  email:           '⚠️ Format email tidak valid.',
  username_short:  '⚠️ Username minimal 4 karakter.',
  password_short:  '⚠️ Password minimal 6 karakter.',
  mismatch:        '⚠️ Konfirmasi password tidak cocok.',
  email_taken:     '⚠️ Email sudah terdaftar, gunakan email lain.',
  username_taken:  '⚠️ Username sudah digunakan, pilih yang lain.',
  // Kontributor
  already_pending: '⚠️ Data komoditas ini sudah dalam antrian verifikasi.',
  submit_empty:    '⚠️ Mohon lengkapi semua data harga dengan benar.',
};

function showMessage(elId, key, isSuccess = false) {
  const el = document.getElementById(elId);
  if (!el) return;
  const msg = ERRORS[key];
  if (!msg && !isSuccess) return;
  el.textContent = msg || key;
  el.className = el.className.replace(/hidden/g, '').trim();
  el.classList.remove('hidden', 'msg-error', 'msg-success');
  el.classList.add(isSuccess || key === 'logout' ? 'msg-success' : 'msg-error');
}

function readUrlMessages() {
  const p  = new URLSearchParams(location.search);
  const e  = p.get('error') || p.get('pesan');
  const s  = p.get('success');
  const el = document.getElementById('msg-box');
  if (!el) return;
  if (e && ERRORS[e]) {
    el.textContent = ERRORS[e];
    el.classList.remove('hidden');
    el.classList.add(e === 'logout' ? 'msg-success' : 'msg-error');
  }
  if (s) {
    const successMap = { submitted: '✅ Laporan berhasil dikirim! Menunggu verifikasi admin.' };
    if (successMap[s]) {
      el.textContent = successMap[s];
      el.classList.remove('hidden');
      el.classList.add('msg-success');
    }
  }
}

/* ── PASSWORD TOGGLE ─────────────────────────────────────── */
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
      if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [icon] });
    }
  }
}

/* ── MODAL ───────────────────────────────────────────────── */
function openModal(id)  { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
function toggleModal(id){ document.getElementById(id)?.classList.toggle('hidden'); }

/* ── FORMAT ──────────────────────────────────────────────── */
function formatRp(n) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
  }).format(n);
}

/* ── CONFIRM HAPUS ───────────────────────────────────────── */
function confirmDelete(msg) {
  return confirm(msg || 'Hapus data ini? Tindakan tidak dapat dibatalkan.');
}

/* ── CHART THEME HELPER ──────────────────────────────────── */
function getChartTheme() {
  const isDark = Theme.current() === 'dark';
  return {
    textColor:  isDark ? '#64748b' : '#94a3b8',
    gridColor:  isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.06)',
    titleColor: isDark ? '#f1f5f9' : '#0f172a',
    bgColor:    isDark ? '#0f1318' : '#ffffff',
  };
}

/* ── DOM READY ───────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') lucide.createIcons();
  readUrlMessages();

  // Theme toggle buttons
  document.querySelectorAll('[data-action="toggle-theme"]').forEach(btn => {
    btn.addEventListener('click', () => {
      Theme.toggle();
      // Re-render chart themes if any
      document.dispatchEvent(new CustomEvent('themeChanged', { detail: Theme.current() }));
    });
  });

  // Close modal on backdrop click
  document.querySelectorAll('[data-modal-close]').forEach(el => {
    el.addEventListener('click', () => closeModal(el.dataset.modalClose));
  });

  // Confirm delete forms
  document.querySelectorAll('[data-confirm]').forEach(form => {
    form.addEventListener('submit', e => {
      if (!confirmDelete(form.dataset.confirm)) e.preventDefault();
    });
  });
});
