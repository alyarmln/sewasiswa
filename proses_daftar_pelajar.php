<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $emel = mysqli_real_escape_string($conn, $_POST['emel']);
    $no_matrik = mysqli_real_escape_string($conn, $_POST['no_matrik']);
    $password_raw = $_POST['password'];

    // Simpan password dalam bentuk hash yang panjang
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

    $sql = "INSERT INTO pelajar (nama, emel, no_matrik, password) 
            VALUES ('$nama', '$emel', '$no_matrik', '$password_hashed')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pendaftaran Berjaya!'); window.location='loginmasuk.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>