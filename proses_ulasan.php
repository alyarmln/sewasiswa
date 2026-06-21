<?php
session_start();
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginmasuk.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pelajar_id = $_SESSION['user_id'];
    $tuan_rumah_id = mysqli_real_escape_string($conn, $_POST['tuan_rumah_id']);
    $rumah_id = mysqli_real_escape_string($conn, $_POST['rumah_id']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $komen = mysqli_real_escape_string($conn, $_POST['komen']);

    $sql = "INSERT INTO ulasan (pelajar_id, tuan_rumah_id, rating, komen) 
            VALUES ('$pelajar_id', '$tuan_rumah_id', '$rating', '$komen')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Terima kasih atas ulasan anda!'); window.location='maklumatrumah.php?id=$rumah_id';</script>";
    } else {
        echo "Ralat: " . mysqli_error($conn);
    }
}
?>