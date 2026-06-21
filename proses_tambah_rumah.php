<?php
session_start();

// 1. Sambungan ke Database
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

// Semak sambungan
if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

// 2. Semakan Akses: Pastikan hanya owner yang login boleh proses data
if (!isset($_SESSION['owner_id'])) {
    header("Location: login_owner.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_id = $_SESSION['owner_id'];
    
    // Ambil data dari borang dan bersihkan (Security)
    $nama_rumah = mysqli_real_escape_string($conn, $_POST['nama_rumah']);
    $kategori   = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga      = mysqli_real_escape_string($conn, $_POST['harga']);
    $deskripsi  = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $lokasi     = mysqli_real_escape_string($conn, $_POST['lokasi']);

    // Pastikan folder 'uploads' wujud sebelum proses fail
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // =================================================================
    // START: PROSES & SEMAKAN OCR UNTUK DOKUMEN VERIFIKASI (BIL UTILITI)
    // =================================================================
    
    // Semak jika fail bil utiliti telah dimuat naik
    if (empty($_FILES['bil_utiliti']['name'])) {
        echo "<script>
                alert('Sila muat naik dokumen bil utiliti untuk proses verifikasi.');
                window.history.back();
              </script>";
        exit();
    }

    $bil_nama = $_FILES['bil_utiliti']['name'];
    $bil_tmp  = $_FILES['bil_utiliti']['tmp_name'];
    $bil_ext  = pathinfo($bil_nama, PATHINFO_EXTENSION);
    
    // Tukar nama bil untuk elak pertindihan
    $nama_bil_baru = "BIL_" . time() . "_" . $owner_id . "." . $bil_ext;
    $folder_bil_tujuan = "uploads/" . $nama_bil_baru;

    // Alihkan fail bil ke folder uploads
    if (!move_uploaded_file($bil_tmp, $folder_bil_tujuan)) {
        echo "<script>
                alert('Gagal memuat naik dokumen verifikasi. Sila cuba lagi.');
                window.history.back();
              </script>";
        exit();
    }

    // A. Dapatkan nama asal owner dari jadual 'tuan_rumah' berdasarkan ID sesi login
    $owner_query = mysqli_query($conn, "SELECT nama FROM `tuan_rumah` WHERE id = '$owner_id'");
    
    if ($owner_query && mysqli_num_rows($owner_query) > 0) {
        $owner_data = mysqli_fetch_assoc($owner_query);
        // Mengambil nama penuh (Contoh: "MOHAMAD AFFAN BIN MOHAMAD AIMAN") dan tukar ke huruf besar
        $nama_sebenar_owner = strtoupper($owner_data['nama']); 
    } else {
        // Jika ID owner tidak sepadan atau tidak dijumpai di jadual tuan_rumah, batalkan proses
        unlink($folder_bil_tujuan);
        echo "<script>alert('Ralat sistem: Data profil di jadual tuan_rumah tidak dijumpai.'); window.history.back();</script>";
        exit();
    }

    // B. Hantar imej bil ke Free OCR Space API menggunakan cURL PHP
    $image_path = realpath($folder_bil_tujuan);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.ocr.space/parse/image');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'apikey' => 'helloworld', // Menggunakan Free API Key bawaan dari ocr.space
        'file' => new CURLFile($image_path),
        'language' => 'msa' // Set kepada Bahasa Melayu (Malaysia) untuk ketepatan bacaan teks tempatan
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    // C. Ambil teks hasil imbasan OCR
    $parsed_text = "";
    if (isset($result['ParsedResults'][0]['ParsedText'])) {
        $parsed_text = strtoupper($result['ParsedResults'][0]['ParsedText']);
    }

    // D. Logik Padanan Keselamatan: Semak jika nama owner wujud dalam kandungan teks bil
    if (strpos($parsed_text, $nama_sebenar_owner) === false) {
        // Jika nama TIADA, padam dokumen bil yang baru diupload tadi dan sekat pendaftaran!
        unlink($folder_bil_tujuan); 
        echo "<script>
                alert('Pendaftaran Ditolak! Nama anda ($nama_sebenar_owner) tidak dijumpai di dalam dokumen verifikasi yang dimuat naik. Sila pastikan anda memuat naik bil milik anda sendiri.');
                window.history.back();
              </script>";
        exit();
    }
    
    // =================================================================
    // END: INTEGRASI TEKNOLOGI OCR
    // =================================================================


    // 3. Pengendalian Muat Naik Gambar Rumah (Diteruskan jika OCR Lulus)
    $gambar_nama = $_FILES['gambar_rumah']['name'];
    $gambar_tmp  = $_FILES['gambar_rumah']['tmp_name'];
    
    $ekstensi = pathinfo($gambar_nama, PATHINFO_EXTENSION);
    $nama_fail_baru = time() . "_" . $owner_id . "." . $ekstensi;
    $folder_tujuan = "uploads/" . $nama_fail_baru;

    if (move_uploaded_file($gambar_tmp, $folder_tujuan)) {
        
        // 4. Masukkan data ke dalam jadual 'rumah' (Termasuk nama fail bil_utiliti yang lulus OCR)
        // Pastikan kolum 'bil_utiliti' wujud di dalam struktur jadual database anda
        $query = "INSERT INTO rumah (tuan_rumah_id, nama_rumah, kategori, harga, deskripsi, lokasi, gambar, bil_utiliti, status) 
                  VALUES ('$owner_id', '$nama_rumah', '$kategori', '$harga', '$deskripsi', '$lokasi', '$nama_fail_baru', '$nama_bil_baru', 'Pending')";

        if (mysqli_query($conn, $query)) {
            echo "<script>
                    alert('Sistem OCR: Dokumen disahkan milik anda! Rumah berjaya disenaraikan.');
                    window.location.href='dashboard_owner.php';
                  </script>";
        } else {
            // Jika database ralat, padam semula fail gambar & bil yang telah diupload supaya folder uploads bersih
            unlink($folder_tujuan);
            unlink($folder_bil_tujuan);
            echo "Ralat Database: " . mysqli_error($conn);
        }
    } else {
        // Jika gambar rumah gagal diupload, padam semula fail bil utiliti yang lulus tadi
        unlink($folder_bil_tujuan);
        echo "<script>
                alert('Gagal memuat naik gambar rumah. Sila cuba lagi.');
                window.history.back();
              </script>";
    }
}

mysqli_close($conn);
?>