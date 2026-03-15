// ================= LOGIKA LOGIN & REGISTER =================
function simulasiLogin() {
  document.getElementById("auth-section").style.display = "none";
  document.getElementById("main-content").style.display = "block";
  document.getElementById("menu-beranda").style.display = "flex";
  document.getElementById("auth-navbar-area").innerHTML = `
        <span class="text-muted me-3">Halo, <strong style="color: var(--text-dark);">Admin</strong></span>
        <button class="btn btn-outline-custom btn-sm px-3" onclick="simulasiLogout()">Logout</button>
    `;
}

function simulasiLogout() {
  document.getElementById("auth-section").style.display = "flex";
  document.getElementById("main-content").style.display = "none";
  document.getElementById("menu-beranda").style.display = "none";
  document.getElementById("auth-navbar-area").innerHTML = "";

  document.getElementById("formLogin").reset();
  document.getElementById("formRegister").reset();

  tampilkanLogin();
}

function tampilkanRegister() {
  document.getElementById("card-login").style.display = "none";
  document.getElementById("card-register").style.display = "block";
}

function tampilkanLogin() {
  document.getElementById("card-register").style.display = "none";
  document.getElementById("card-login").style.display = "block";
}

// ================= LOGIKA TAMBAH DATA KE TABEL =================
function formatRupiah(angka) {
  return "Rp " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function tambahBarisTabel() {
  // Ambil ke-4 input
  const komoditas = document.getElementById("inputKomoditas").value;
  const lokasi = document.getElementById("inputLokasi").value;
  const hargaKemarin = parseInt(document.getElementById("inputKemarin").value);
  const hargaSekarang = parseInt(document.getElementById("inputSekarang").value);

  // Validasi input
  if (!komoditas || !lokasi || isNaN(hargaKemarin) || isNaN(hargaSekarang)) {
    alert("Harap isi semua data dengan benar!");
    return;
  }

  // Logika styling naik/turun
  let stylingSekarang = "";
  if (hargaSekarang > hargaKemarin) {
    stylingSekarang = `<td class="fw-bold text-success">${formatRupiah(hargaSekarang)} <span class="fs-6">↑</span></td>`;
  } else if (hargaSekarang < hargaKemarin) {
    stylingSekarang = `<td class="fw-bold text-danger">${formatRupiah(hargaSekarang)} <span class="fs-6">↓</span></td>`;
  } else {
    stylingSekarang = `<td class="fw-bold">${formatRupiah(hargaSekarang)} <span class="text-muted fs-6">-</span></td>`;
  }

  // Susun baris baru
  const barisBaru = `
        <tr>
            <td class="fw-bold text-primary">${komoditas}</td>
            <td class="fw-semibold">${lokasi}</td>
            <td class="text-muted">${formatRupiah(hargaKemarin)}</td>
            ${stylingSekarang}
            <td class="text-end"><button class="btn btn-sm btn-outline-custom px-3">Edit</button></td>
        </tr>
    `;

  // Masukkan ke tabel dan tutup modal
  document.getElementById("tabel-komoditas").insertAdjacentHTML("beforeend", barisBaru);
  document.getElementById("formTambahData").reset();

  const modalEl = document.getElementById("modalTambahData");
  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
  modalInstance.hide();
}
