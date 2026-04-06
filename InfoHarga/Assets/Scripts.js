// ==========================================
// UTILITY FUNCTIONS
// ==========================================

// Inisialisasi ikon Lucide saat halaman dimuat
document.addEventListener("DOMContentLoaded", () => {
  if (typeof lucide !== "undefined") {
    lucide.createIcons();
  }
});

// Format angka ke Rupiah
const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(angka);
};

// ==========================================
// INDEX / PUBLIC PAGE LOGIC
// ==========================================

let publicChart; // Variabel global untuk Chart

// Fungsi untuk memperbarui dropdown berdasarkan data
function updatePublicDropdowns() {
  // Membaca data yang dilempar dari index.php
  if (typeof window.dbData === "undefined" || window.dbData.length === 0) return;

  const filterKomoditas = document.getElementById("filterKomoditas");
  const filterLokasi = document.getElementById("filterLokasi");

  if (!filterKomoditas || !filterLokasi) return;

  const currentKom = filterKomoditas.value;
  const currentLok = filterLokasi.value;

  const uniqueKomoditas = [...new Set(window.dbData.map((item) => item.komoditas))];
  const uniqueLokasi = [...new Set(window.dbData.map((item) => item.lokasi))];

  filterKomoditas.innerHTML = uniqueKomoditas.map((k) => `<option value="${k}">${k}</option>`).join("");
  filterLokasi.innerHTML = uniqueLokasi.map((l) => `<option value="${l}">${l}</option>`).join("");

  if (uniqueKomoditas.includes(currentKom)) filterKomoditas.value = currentKom;
  if (uniqueLokasi.includes(currentLok)) filterLokasi.value = currentLok;
}

// Fungsi untuk merender tabel komoditas
function renderPublicTable() {
  if (typeof window.dbData === "undefined") return;

  const tbody = document.getElementById("public-tabel-komoditas");
  if (!tbody) return;

  tbody.innerHTML = "";

  window.dbData.forEach((item) => {
    let badgeHtml = "";
    if (item.sekarang > item.kemarin) {
      badgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800"><i data-lucide="trending-up" class="w-3 h-3"></i> Naik</span>`;
    } else if (item.sekarang < item.kemarin) {
      badgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"><i data-lucide="trending-down" class="w-3 h-3"></i> Turun</span>`;
    } else {
      badgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800"><i data-lucide="minus" class="w-3 h-3"></i> Stabil</span>`;
    }

    tbody.innerHTML += `
        <tr class="hover:bg-slate-50 transition">
            <td class="p-5 font-bold text-slate-900">${item.komoditas}</td>
            <td class="p-5 text-slate-600">
            <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(item.lokasi)}" target="_blank" 
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 transition cursor-pointer shadow-sm" title="Lihat Titik di Google Maps">
            <i data-lucide="map-pin" class="w-3 h-3"></i> ${item.lokasi}
            </a>
            </td>
            <td class="p-5 text-slate-500">${formatRupiah(item.kemarin)}</td>
            <td class="p-5 font-bold ${item.sekarang > item.kemarin ? "text-emerald-600" : item.sekarang < item.kemarin ? "text-red-600" : "text-slate-900"}">${formatRupiah(item.sekarang)}</td>
            <td class="p-5">${badgeHtml}</td>
        </tr>
        `;
  });

  // Render ulang ikon yang baru saja diinject via JS
  if (typeof lucide !== "undefined") {
    lucide.createIcons();
  }
}

// Fungsi untuk memperbarui chart saat dropdown diubah
function updatePublicChart() {
  if (typeof window.dbData === "undefined" || !publicChart) return;

  const filterKomoditas = document.getElementById("filterKomoditas");
  const filterLokasi = document.getElementById("filterLokasi");

  if (!filterKomoditas || !filterLokasi) return;

  const komoditas = filterKomoditas.value;
  const lokasi = filterLokasi.value;

  const foundItem = window.dbData.find((item) => item.komoditas === komoditas && item.lokasi === lokasi);

  publicChart.options.plugins.title.text = foundItem ? `${komoditas} di wilayah ${lokasi}` : "Data Tidak Ditemukan";

  if (foundItem) {
    publicChart.data.datasets[0].data = foundItem.history;
  } else {
    publicChart.data.datasets[0].data = [0, 0, 0, 0, 0, 0, 0];
  }
  publicChart.update();
}

// Inisialisasi Chart.js dan Event Listener
document.addEventListener("DOMContentLoaded", () => {
  const ctxElement = document.getElementById("publicChart");

  // Menjalankan script ini HANYA jika elemen chart ditemukan (berarti sedang di index.php)
  if (ctxElement) {
    updatePublicDropdowns();
    renderPublicTable();

    const ctx = ctxElement.getContext("2d");
    let gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, "rgba(16, 185, 129, 0.5)");
    gradient.addColorStop(1, "rgba(16, 185, 129, 0.0)");

    publicChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: ["H-6", "H-5", "H-4", "H-3", "H-2", "Kemarin", "Hari Ini"],
        datasets: [
          {
            label: "Harga (Rp)",
            data: [],
            borderColor: "#10b981",
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "#10b981",
            pointRadius: 5,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          title: { display: true, text: "", font: { family: "Inter", size: 16, weight: "bold" }, padding: { bottom: 20 } },
        },
        scales: {
          y: { beginAtZero: false },
          x: { grid: { display: false } },
        },
      },
    });

    // Update pertama kali
    updatePublicChart();

    // Tambahkan event listener ke dropdown
    document.getElementById("filterKomoditas").addEventListener("change", updatePublicChart);
    document.getElementById("filterLokasi").addEventListener("change", updatePublicChart);
  }
});
