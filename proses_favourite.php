<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Sila log masuk terlebih dahulu!'); window.location='loginmasuk.php';</script>";
    exit();
}

if (isset($_GET['id'])) {
    $pelajar_id = $_SESSION['user_id'];
    $rumah_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Semak jika sudah wujud
    $check = mysqli_query($conn, "SELECT * FROM favourite WHERE pelajar_id = '$pelajar_id' AND rumah_id = '$rumah_id'");

    if (mysqli_num_rows($check) > 0) {
        // Jika wujud, kita buang (Unlike)
        mysqli_query($conn, "DELETE FROM favourite WHERE pelajar_id = '$pelajar_id' AND rumah_id = '$rumah_id'");
        $msg = "Dibuang daripada senarai favourite!";
    } else {
        // Jika belum wujud, kita tambah (Like)
        mysqli_query($conn, "INSERT INTO favourite (pelajar_id, rumah_id) VALUES ('$pelajar_id', '$rumah_id')");
        $msg = "Berjaya ditambah ke favourite!";
    }

    echo "<script>alert('$msg'); window.location='maklumatrumah.php?id=$rumah_id';</script>";
} else {
    header("Location: utama.php");
}
?>