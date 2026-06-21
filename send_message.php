<?php
session_start();
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sesi dikesan mengikut siapa yang sedang aktif log masuk
    $my_id = $_SESSION['user_id'] ?? $_SESSION['owner_id'] ?? null;
    
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $receiver_id = mysqli_real_escape_string($conn, $_POST['receiver_id']);
    $sender_role = mysqli_real_escape_string($conn, $_POST['sender_role']); // Mengambil 'pelajar' atau 'tuan_rumah'

    if ($my_id && !empty($message) && !empty($receiver_id)) {
        // Memasukkan data ke pangkalan data mengikut struktur kolum awak yang tepat
        $sql = "INSERT INTO messages (sender_id, receiver_id, user_id, message, timestamp) 
                VALUES ('$sender_role', '$receiver_id', '$my_id', '$message', NOW())";
        
        if(mysqli_query($conn, $sql)) {
            echo "Success";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Data Tidak Lengkap";
    }
}
?>