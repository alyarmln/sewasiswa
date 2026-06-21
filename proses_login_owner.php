<?php
session_start();
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emel = mysqli_real_escape_string($conn, $_POST['emel']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM tuan_rumah WHERE emel = '$emel' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['owner_id'] = $row['id']; 
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = 'tuan_rumah';
            header("Location: dashboard_owner.php");
            exit();
        }
    }

    $_SESSION['error'] = "Emel atau Katalaluan Salah!";
    header("Location: login_owner.php");
    exit();
}
?>