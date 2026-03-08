function inputData(event) {
  // Mencegah halaman web melakukan refresh otomatis saat form disubmit
  event.preventDefault();

  // Mengambil nilai dari form 'myForm' dan input yang bernama 'nama' dan 'jumlah'
  var namaPangan = document.forms["myForm"]["nama"].value;
  var jumlahPangan = document.forms["myForm"]["jumlah"].value;

  // Menampilkan pop-up alert
  alert("Data Hasil Pangan: " + namaPangan + " dengan jumlah " + jumlahPangan + " telah ditambahkan!");

  // Opsional: Mengosongkan form setelah disubmit
  document.forms["myForm"].reset();
}
