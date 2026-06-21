<?php
session_start();

// Pastikan hanya tuan rumah yang sah boleh akses fail ini
if (!isset($_SESSION['owner_id'])) {
    header("Location: login_owner.php");
    exit();
}

// update db connection code 

// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

// Semak sama ada parameter id dan tindakan telah dihantar melalui URL
if (isset($_GET['id']) && isset($_GET['tindakan'])) {
    $jt_id = mysqli_real_escape_string($conn, $_GET['id']);
    $tindakan = $_GET['tindakan'];
    $owner_id = $_SESSION['owner_id'];

    // Tentukan status baru berdasarkan butang yang ditekan
    if ($tindakan == 'Sahkan') {
        $status_baru = 'Approved';
    } elseif ($tindakan == 'Tolak') {
        $status_baru = 'Rejected';
    } else {
        // Jika tindakan tidak sah, hantar balik ke dashboard
        header("Location: dashboard_owner.php");
        exit();
    }

    // Kemaskini status dalam database (Pastikan janji temu ini memang milik owner tersebut)
    $update_query = "UPDATE janji_temu 
                     SET status = '$status_baru' 
                     WHERE id = '$jt_id' AND tuan_rumah_id = '$owner_id'";

    if (mysqli_query($conn, $update_query)) {
        // Jika berjaya, papar notifikasi sukses dan kembali ke dashboard
        echo "<script>
                alert('Status janji temu telah berjaya dikemaskini kepada: $status_baru!');
                window.location.href = 'dashboard_owner.php#janji_temu_section';
              </script>";
    } else {
        // Jika ralat database berlaku
        echo "<script>
                alert('Gagal mengemaskini status. Sila cuba lagi.');
                window.location.href = 'dashboard_owner.php';
              </script>";
    }
} else {
    // Jika tiada parameter dihantar, tendang balik ke dashboard
    header("Location: dashboard_owner.php");
    exit();
}
?>