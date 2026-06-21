<?php
session_start();
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) {
    die("Sambungan database gagal: " . mysqli_connect_error());
}

// Pautan ke folder phpmailer mengikut struktur VS Code anda
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emel_pelajar = mysqli_real_escape_string($conn, $_POST['emel']);
    $password_input = $_POST['password']; // Ambil input password dari loginmasuk.php

    // Sekatan: Pastikan pelajar menggunakan emel siswa UKM sahaja
    if (strpos($emel_pelajar, '@siswa.ukm.edu.my') === false) {
        $_SESSION['error_login'] = "Ralat: Sila gunakan emel rasmi @siswa.ukm.edu.my!";
        header("Location: loginmasuk.php");
        exit();
    }

    // Semak sama ada emel ini wujud dalam pangkalan data
    $check_user = mysqli_query($conn, "SELECT * FROM pelajar WHERE emel = '$emel_pelajar'");
    
    if (mysqli_num_rows($check_user) > 0) {
        $user_data = mysqli_fetch_assoc($check_user);

        // ================= LAPISAN KESELAMATAN 1: SEMAK KATA LALUAN =================
        if (!password_verify($password_input, $user_data['password'])) {
            $_SESSION['error_login'] = "Ralat: Kata laluan yang dimasukkan adalah salah!";
            header("Location: loginmasuk.php");
            exit();
        }

        // ================= LAPISAN KESELAMATAN 2: PROSES OTP & EMEL =================
        // Jika kata laluan betul, barulah jana 6-digit kod OTP rawak
        $otp = rand(100000, 999999);
        
        // Kemas kini kod OTP ke dalam database
        mysqli_query($conn, "UPDATE pelajar SET otp_code = '$otp' WHERE emel = '$emel_pelajar'");
        
        // Simpan emel sementara dalam session
        $_SESSION['temp_email'] = $emel_pelajar;

        // --- PROSES HANTAR EMEL OTP VIA PHPMAILER ---
        $mail = new PHPMailer(true);
        try {
            // $mail->isSMTP();
            // $mail->Host       = $SMTP_HOST;
            // $mail->SMTPAuth   = true;
            // $mail->Username   = $SMTP_USERNAME; 
            // $mail->Password   = $SMTP_PASS;        
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            // $mail->Port       = $SMTP_PORT;

            // // Diselaraskan dengan SMTP Username anda supaya penghantaran lancar
            // $mail->setFrom($SMTP_USERNAME, 'SewaSiswa UKM');
            // $mail->addAddress($emel_pelajar);


            // Untuk Hardcode

            // $mail->isSMTP();
            // $mail->Host       = 'smtp.hostinger.com';
            // $mail->SMTPAuth   = true;
            // $mail->Username   = 'admin@sewasiswa.site'; 
            // $mail->Password   = 'Alyarmln@5359';        
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            // $mail->Port       = 465;

            // // Diselaraskan dengan SMTP Username anda supaya penghantaran lancar
            // $mail->setFrom('admin@sewasiswa.site', 'SewaSiswa UKM');
            // $mail->addAddress($emel_pelajar);

            
            // Test Pakai Server Gmail
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'smollmintj@gmail.com'; 
            $mail->Password   = 'lenn aqga tssi jrao';        
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Diselaraskan dengan SMTP Username anda supaya penghantaran lancar
            $mail->setFrom('smollmintj@gmail.com', 'SewaSiswa UKM');
            $mail->addAddress($emel_pelajar);

            $mail->isHTML(true);
            $mail->Subject = 'Kod Pengesahan Log Masuk SewaSiswa';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 25px; border: 1px solid #e2e8f0; border-radius: 20px; max-width: 400px; margin: 0 auto;'>
                    <h2 style='color: #0d9488; margin-bottom: 5px;'>SewaSiswa UKM</h2>
                    <p style='color: #64748b; font-size: 14px;'>Sistem Pengurusan Rumah Sewa Pelajar</p>
                    <hr style='border: 0; border-top: 1px solid #f1f5f9; margin: 20px 0;'>
                    <p style='color: #334155; font-size: 14px;'>Gunakan kod OTP di bawah untuk melengkapkan proses log masuk:</p>
                    <div style='font-size: 28px; font-weight: bold; color: #f59e0b; padding: 15px; background-color: #fef3c7; display: inline-block; border-radius: 12px; letter-spacing: 4px; margin: 15px 0;'>
                        $otp
                    </div>
                    <p style='font-size: 11px; color: #94a3b8;'>Kod ini sah untuk sesi log masuk semasa sahaja. Jangan kongsi dengan sesiapa.</p>
                </div>
            ";

            $mail->send();
            
            // Berjaya hantar kata laluan & OTP, bawa pelajar ke halaman pengesahan OTP
            header("Location: sah_otp.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['error_login'] = "Ralat SMTP: Gagal menghantar emel pengesahan.";
            header("Location: loginmasuk.php");
            exit();
        }
    } else {
        $_SESSION['error_login'] = "Emel tidak didaftarkan dalam sistem SewaSiswa.";
        header("Location: loginmasuk.php");
        exit();
    }
}
?>