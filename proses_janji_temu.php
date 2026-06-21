<?php
session_start();
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) { die("Sambungan gagal: " . mysqli_connect_error()); }

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Sila log masuk dahulu!'); window.location.href='login_masuk.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pelajar_id = $_SESSION['user_id'];
    $rumah_id   = mysqli_real_escape_string($conn, $_POST['rumah_id']);
    $tarikh     = mysqli_real_escape_string($conn, $_POST['tarikh_pilihan']);
    $masa       = mysqli_real_escape_string($conn, $_POST['masa_pilihan']);

    // 1. CARI TUAN RUMAH (Pastikan table 'rumah' sudah dibuat di phpMyAdmin)
    $get_owner = mysqli_query($conn, "SELECT tuan_rumah_id FROM rumah WHERE id = '$rumah_id'");
    
    if (mysqli_num_rows($get_owner) > 0) {
        $owner_data = mysqli_fetch_assoc($get_owner);
        $tuan_rumah_id = $owner_data['tuan_rumah_id'];

        // 2. INSERT KE JANJI_TEMU
        $sql = "INSERT INTO janji_temu (pelajar_id, tuan_rumah_id, rumah_id, tarikh, masa, status) 
                VALUES ('$pelajar_id', '$tuan_rumah_id', '$rumah_id', '$tarikh', '$masa', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Janji temu berjaya!'); window.location.href='status_janji_temu.php';</script>";
        } else {
            echo "Ralat Simpan: " . mysqli_error($conn);
        }
    } else {
        echo "<script>alert('Ralat: Data rumah tidak dijumpai di database!'); window.history.back();</script>";
    }
}
mysqli_close($conn);
?>