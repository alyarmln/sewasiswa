<?php
session_start();
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

// 1. Pastikan hanya tuan rumah yang log masuk boleh akses
if (!isset($_SESSION['owner_id'])) {
    die("Akses tidak dibenarkan.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari butang yang ditekan
    $jt_id = mysqli_real_escape_string($conn, $_POST['jt_id']);
    $status_baru = mysqli_real_escape_string($conn, $_POST['status']); // 'Approved' atau 'Rejected'

    // 2. Query untuk kemaskini status
    $sql = "UPDATE janji_temu SET status = '$status_baru' WHERE id = '$jt_id'";

    if (mysqli_query($conn, $sql)) {
        // Papar mesej dan balik ke dashboard
        echo "<script>
                alert('Status janji temu telah dikemaskini kepada: $status_baru');
                window.location.href='dashboard_owner.php';
              </script>";
    } else {
        echo "Ralat: " . mysqli_error($conn);
    }
} else {
    header("Location: dashboard_owner.php");
    exit();
}

mysqli_close($conn);
?>