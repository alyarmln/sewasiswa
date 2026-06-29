<?php
// 1. Sambungan ke Database
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

// 2. Panggil PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_telefon = mysqli_real_escape_string($conn, $_POST['no_telefon']);
    $emel = mysqli_real_escape_string($conn, $_POST['emel']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_bisnes']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 3. Proses Muat Naik Fail (Simple Version)
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    $gambar_profil = time() . "_" . $_FILES["gambar_profil"]["name"];
    $gambar_mykad = time() . "_" . $_FILES["gambar_mykad"]["name"];
    $bil_utiliti = time() . "_" . $_FILES["bil_utiliti"]["name"];

    move_uploaded_file($_FILES["gambar_profil"]["tmp_name"], $target_dir . $gambar_profil);
    move_uploaded_file($_FILES["gambar_mykad"]["tmp_name"], $target_dir . $gambar_mykad);
    move_uploaded_file($_FILES["bil_utiliti"]["tmp_name"], $target_dir . $bil_utiliti);

    // 4. Simpan ke Database (Table: tuan_rumah)
    // Pastikan nama kolum dalam database anda sama dengan kod ini
    $query = "INSERT INTO tuan_rumah (nama, no_telefon, emel, kategori, alamat, deskripsi, gambar_profil, gambar_mykad, bil_utiliti, password, status) 
              VALUES ('$nama', '$no_telefon', '$emel', '$kategori', '$alamat', '$deskripsi', '$gambar_profil', '$gambar_mykad', '$bil_utiliti', '$password', 'Pending')";

    if (mysqli_query($conn, $query)) {
        
        // 5. Hantar Emel Notifikasi ke Tuan Rumah
        $mail = new PHPMailer(true);

        try {
            // $mail->isSMTP();
            // $mail->Host       = 'sandbox.smtp.mailtrap.io';
            // $mail->SMTPAuth   = true;
            // $mail->Port       = 2525;
            // $mail->Username   = 'b12a8b398d68ad'; 
            // $mail->Password   = '78a3345aa52177'; 

            // $mail->setFrom('admin@sewasiswa.com', 'SewaSiswa Admin');
            $mail->isSMTP();
            $mail->Host       = $SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = $SMTP_USERNAME; 
            $mail->Password   = $SMTP_PASS;        
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $SMTP_PORT;
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';

            // Diselaraskan dengan SMTP Username anda supaya penghantaran lancar
            $mail->setFrom($SMTP_USERNAME, 'SewaSiswa UKM');
            $mail->addAddress($emel, $nama); 

            $mail->isHTML(true);
            $mail->Subject = 'Permohonan Tuan Rumah Diterima - SewaSiswa';
            $mail->Body    = "
                <div style='font-family: sans-serif; border: 2px solid #0d9488; padding: 25px; border-radius: 15px;'>
                    <h2 style='color: #0d9488;'>Permohonan Diterima!</h2>
                    <p>Hai <b>$nama</b>,</p>
                    <p>Pendaftaran anda sebagai Tuan Rumah telah diterima. Dokumen anda (MyKad & Bil Utiliti) sedang disemak oleh Admin SewaSiswa.</p>
                    <p>Akaun anda akan diaktifkan dalam tempoh <b>24 jam</b> sekiranya dokumen lengkap.</p>
                    <br>
                    <p style='font-size: 11px; color: #666;'>Terima kasih kerana memilih SewaSiswa.</p>
                </div>";

            $mail->send();
        } catch (Exception $e) {
            // Emel gagal tak apa, janji data masuk db
        }

        echo "<script>alert('Pendaftaran berjaya! Sila tunggu pengesahan Admin dalam tempoh 24 jam.'); window.location.href='loginmasuk.php';</script>";
    } else {
        echo "Ralat Database: " . mysqli_error($conn);
    }
}
?>