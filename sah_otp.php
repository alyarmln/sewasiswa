<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

// Jika tiada sesi emel sementara, tendang balik ke loginmasuk.php
if (!isset($_SESSION['temp_email'])) {
    header("Location: loginmasuk.php");
    exit();
}

$error_msg = "";
$emel_pelajar = $_SESSION['temp_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp_input = mysqli_real_escape_string($conn, $_POST['otp']);

    // Semak padanan kod OTP di pangkalan data
    $query = mysqli_query($conn, "SELECT * FROM pelajar WHERE emel = '$emel_pelajar' AND otp_code = '$otp_input'");

    if (mysqli_num_rows($query) > 0) {
        $user_data = mysqli_fetch_assoc($query);
        
        // Padam semula OTP selepas berjaya (Ciri keselamatan)
        mysqli_query($conn, "UPDATE pelajar SET otp_code = NULL WHERE emel = '$emel_pelajar'");
        
        // Set sesi rasmi log masuk pelajar (Sama seperti sistem asal anda)
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['nama'] = $user_data['nama'];
        
        unset($_SESSION['temp_email']);

        // Alihkan ke dashboard pelajar anda
        header("Location: dashboard_pelajar.php");
        exit();
    } else {
        $error_msg = "Kod OTP salah. Sila semak emel siswa anda sekali lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Sahkan OTP - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl max-w-md w-full border border-slate-100 text-center">
        <h2 class="text-2xl font-black text-slate-900 mb-2">Sahkan Kod OTP</h2>
        <p class="text-xs text-slate-500 mb-6">Sila masukkan 6-digit kod yang dihantar ke:<br>
            <span class="text-teal-600 font-semibold"><?php echo htmlspecialchars($emel_pelajar); ?></span>
        </p>

        <?php if(!empty($error_msg)): ?>
            <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-xl text-xs font-semibold border border-red-100"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <input type="text" name="otp" maxlength="6" required placeholder="000000" class="w-full text-center tracking-[1em] text-xl font-bold bg-slate-50 border border-slate-200 rounded-xl p-3.5 outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold p-3.5 rounded-full text-sm shadow-md transition transform active:scale-95">
                Sahkan & Log Masuk
            </button>
        </form>
    </div>
</body>
</html>