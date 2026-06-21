<?php
session_start();
// 1. Sambungan ke Database
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = $_POST['password']; // Nama variable di sini

    // 2. Cari user dalam database
    $query = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // 3. Perbandingan teks biasa (Direct Match)
        if ($password_input === $row['password']) { 
            // Nama variable di atas mesti sama dengan di sini ($password_input)
            $_SESSION['admin_id'] = $row['id'];
            header("Location: dashboard_admin.php");
            exit();
        } else {
            // Mesej ralat kalau password salah
            $error = "Password Salah! Sila semak semula.";
        }
    } else {
        // Mesej ralat kalau username tak wujud
        $error = "Username '$username' tidak dijumpai!";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen font-sans">
    <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-black text-slate-800 tracking-tighter">ADMIN <span class="text-teal-600">PORTAL</span></h1>
            <p class="text-slate-400 text-[10px] uppercase tracking-[0.2em] mt-2 font-bold">Pengesahan Sistem SewaSiswa</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-xs font-bold mb-6 border border-red-100 text-center">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                <input type="text" name="username" required placeholder="alyarmln"
                    class="w-full p-4 rounded-2xl border border-slate-100 bg-slate-50 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-semibold">
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                    class="w-full p-4 rounded-2xl border border-slate-100 bg-slate-50 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-semibold">
            </div>
            <button type="submit" name="login" class="w-full bg-slate-900 hover:bg-teal-600 text-white font-bold py-4 rounded-2xl transition-all shadow-xl shadow-slate-200 uppercase text-[11px] tracking-widest mt-4">
                Masuk Sekarang
            </button>
        </form>
        
        <p class="text-center text-[10px] text-slate-300 mt-8 font-medium">SEWASISWA ADMIN ENGINE v1.0</p>
    </div>
</body>
</html>