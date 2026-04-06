<?php
require '../server/koneksi.php';

if (isset($_POST['register'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Enkripsi password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah username sudah ada
    $cek_username = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($cek_username) > 0) {
        echo "<script>
                alert('Username sudah terdaftar! Pilih username lain.');
                window.location.href = '../register.php';
              </script>";
        return false;
    }

    // Insert ke database
    $query = "INSERT INTO users (email, username, tanggal_lahir, password) VALUES ('$email', '$username', '$tanggal_lahir', '$password_hash')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Registrasi berhasil! Silakan login.');
                window.location.href = '../login.php';
              </script>";
    } else {
        echo "<script>
                alert('Registrasi gagal. Silakan coba lagi.');
                window.location.href = '../register.php';
              </script>";
    }
}
?>