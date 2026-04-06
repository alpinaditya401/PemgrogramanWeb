<?php
session_start();
require '../server/koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    // Cek apakah username ada
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek kecocokan password yang di-hash
        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            
            header("Location: ../dashboard.php");
            exit;
        }
    }
    
    // Jika gagal
    echo "<script>
            alert('Username atau Password salah!');
            window.location.href = '../login.php';
          </script>";
}
?>