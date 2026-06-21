<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: login_owner.php");
    exit();
}

if (isset($_GET['id'])) {
    // $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
    require_once "database.php";
    
    // Semak sambungan database
    if (!$conn) {
        die("Sambungan gagal: " . mysqli_connect_error());
    }

    $rumah_id = mysqli_real_escape_string($conn, $_GET['id']);
    $owner_id = mysqli_real_escape_string($conn, $_SESSION['owner_id']);

    // Pastikan rumah ini benar-benar milik owner yang sedang login (security check)
    $semak = mysqli_query($conn, "SELECT * FROM rumah WHERE id = '$rumah_id' AND tuan_rumah_id = '$owner_id'");
    
    if (mysqli_num_rows($semak) > 0) {
        /* 💡 LOGIK PASARAN BARU: 
          Daripada menggunakan DELETE, kita UPDATE status_sewa menjadi 'Tersewa'.
          Ini mengelakkan owner terpaksa menaip semula iklan baru pada semester depan.
        */
        $arkib = mysqli_query($conn, "UPDATE rumah SET status_sewa = 'Tersewa' WHERE id = '$rumah_id'");
        
        if ($arkib) {
            echo "<script>
                    alert('Iklan rumah telah diarkibkan sebagai TERSEWA dan disembunyikan daripada pelajar!');
                    window.location.href='dashboard_owner.php#hartanah_section';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal mengemas kini status arkib: " . mysqli_error($conn) . "');
                    window.location.href='dashboard_owner.php#hartanah_section';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Akses disekat! Anda tidak mempunyai hak untuk menguruskan rumah ini.');
                window.location.href='dashboard_owner.php';
              </script>";
    }
} else {
    header("Location: dashboard_owner.php");
    exit();
}
?>