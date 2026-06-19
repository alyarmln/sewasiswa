<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

// 1. Sekatan Akses
if (!isset($_SESSION['user_id'])) {
    header("Location: loginmasuk.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Ambil maklumat profil pelajar
$query_user = mysqli_query($conn, "SELECT * FROM pelajar WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query_user);

// 3. Ambil statistik janji temu
$query_stats = mysqli_query($conn, "SELECT COUNT(*) as total FROM janji_temu WHERE pelajar_id = '$user_id'");
$stats = mysqli_fetch_assoc($query_stats);

// 4. Ambil statistik mesej BARU (Mesej yang dihantar oleh tuan_rumah kepada saya)
// Nota: Anda boleh tambah kolum 'is_read' nanti jika mahu sistem yang lebih advance
$query_msg = mysqli_query($conn, "SELECT COUNT(*) as total_msg FROM messages WHERE receiver_id = '$user_id' AND sender_id = 'tuan_rumah'");
$msg_stats = mysqli_fetch_assoc($query_msg);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Dashboard Pelajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0fdfa; }
        .menu-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .menu-card:hover { transform: translateY(-10px); }
    </style>
</head>
<body class="min-h-screen">

    <nav class="bg-white border-b border-teal-100 p-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="bg-teal-600 p-2 rounded-lg text-white">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <h1 class="text-xl font-black text-teal-800 tracking-tighter">SEWA<span class="text-teal-500">SISWA</span></h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-[10px] font-bold text-gray-400 uppercase leading-none">Pelajar</p>
                    <p class="text-sm font-bold text-teal-900"><?php echo htmlspecialchars($user['nama']); ?></p>
                </div>
                <a href="logout.php" class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-full transition">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto py-12 px-6">
        
        <div class="mb-12">
            <h2 class="text-4xl font-bold text-gray-800 tracking-tight">Hi, <?php echo explode(' ', trim($user['nama']))[0]; ?>! 👋</h2>
            <p class="text-gray-500 mt-2 font-medium">Selamat datang ke portal carian rumah sewa UKM.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <a href="utama.php" class="menu-card bg-white p-8 rounded-[40px] shadow-sm border border-teal-50 flex flex-col items-center text-center group">
                <div class="w-20 h-20 bg-teal-50 text-teal-600 rounded-3xl flex items-center justify-center mb-6 group-hover:bg-teal-600 group-hover:text-white transition shadow-inner">
                    <i class="fas fa-search-location text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-sm uppercase tracking-widest">Cari Rumah</h3>
                <p class="text-gray-400 text-[10px] mt-3 leading-relaxed">Teroka kediaman berdekatan kampus.</p>
            </a>

            <a href="status_janji_temu.php" class="menu-card bg-white p-8 rounded-[40px] shadow-sm border border-teal-50 flex flex-col items-center text-center group">
                <div class="relative w-20 h-20 bg-orange-50 text-orange-600 rounded-3xl flex items-center justify-center mb-6 group-hover:bg-orange-600 group-hover:text-white transition shadow-inner">
                    <i class="fas fa-calendar-alt text-3xl"></i>
                    <?php if($stats['total'] > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] w-6 h-6 rounded-full flex items-center justify-center border-4 border-white font-bold"><?php echo $stats['total']; ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-gray-800 text-sm uppercase tracking-widest">Janji Temu</h3>
                <p class="text-gray-400 text-[10px] mt-3 leading-relaxed">Semak status lawatan rumah.</p>
            </a>

            <a href="senarai_chat_pelajar.php" class="menu-card bg-white p-8 rounded-[40px] shadow-sm border border-teal-50 flex flex-col items-center text-center group">
                <div class="relative w-20 h-20 bg-purple-50 text-purple-600 rounded-3xl flex items-center justify-center mb-6 group-hover:bg-purple-600 group-hover:text-white transition shadow-inner">
                    <i class="fas fa-comment-dots text-3xl"></i>
                    <?php if($msg_stats['total_msg'] > 0): ?>
                        <span class="absolute top-0 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-gray-800 text-sm uppercase tracking-widest">Mesej Saya</h3>
                <p class="text-gray-400 text-[10px] mt-3 leading-relaxed">Sembang dengan AI & Owner.</p>
            </a>

            <a href="profil_pelajar.php" class="menu-card bg-white p-8 rounded-[40px] shadow-sm border border-teal-50 flex flex-col items-center text-center group">
                <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-3xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition shadow-inner">
                    <i class="fas fa-id-card text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-sm uppercase tracking-widest">Profil Saya</h3>
                <p class="text-gray-400 text-[10px] mt-3 leading-relaxed">No. Matrik: <span class="text-blue-600 font-bold"><?php echo $user['no_matrik']; ?></span></p>
            </a>

            <a href="senarai_favourite.php" class="menu-card bg-white p-8 rounded-[40px] shadow-sm border border-teal-50 flex flex-col items-center text-center group">
    <div class="w-20 h-20 bg-rose-50 text-rose-600 rounded-3xl flex items-center justify-center mb-6 group-hover:bg-rose-600 group-hover:text-white transition shadow-inner">
        <i class="fas fa-heart text-3xl"></i>
    </div>
    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-widest">Kegemaran</h3>
    <p class="text-gray-400 text-[10px] mt-3 leading-relaxed">Koleksi rumah idaman anda.</p>
</a>

        </div>

        <div class="mt-16 bg-teal-900 p-8 rounded-[40px] text-white flex flex-col md:flex-row items-center justify-between shadow-2xl shadow-teal-200">
            <div class="mb-4 md:mb-0">
                <h4 class="font-bold text-xl tracking-tight">Perlukan Bantuan?</h4>
                <p class="text-teal-300 text-sm opacity-80">Hubungi admin SewaSiswa jika anda menghadapi masalah teknikal.</p>
            </div>
            <a href="mailto:support@siswa.ukm.edu.my" class="bg-teal-400 hover:bg-teal-300 text-teal-900 font-bold py-3 px-8 rounded-2xl text-[10px] transition uppercase tracking-widest">
                Hubungi Kami
            </a>
        </div>

    </main>

</body>
</html>