<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

// 1. Sekatan Akses: Pastikan pelajar sudah log masuk
if (!isset($_SESSION['user_id'])) {
    header("Location: login_masuk.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$mesej = "";

// 2. Proses Kemaskini Profil
if (isset($_POST['update_profil'])) {
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama']);
    $matrik_baru = mysqli_real_escape_string($conn, $_POST['no_matrik']);

    $sql_update = "UPDATE pelajar SET nama = '$nama_baru', no_matrik = '$matrik_baru' WHERE id = '$user_id'";

    if (mysqli_query($conn, $sql_update)) {
        $mesej = "<div class='bg-green-100 text-green-700 p-3 rounded-xl mb-6 text-center font-bold text-sm'>Profil Berjaya Dikemaskini!</div>";
    } else {
        $mesej = "<div class='bg-red-100 text-red-700 p-3 rounded-xl mb-6 text-center font-bold text-sm'>Ralat: " . mysqli_error($conn) . "</div>";
    }
}

// 3. Ambil data terkini untuk dipaparkan
$query = mysqli_query($conn, "SELECT * FROM pelajar WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>SewaSiswa - Profil Pelajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0fdfa; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white rounded-[40px] shadow-2xl overflow-hidden border border-teal-50">
        
        <div class="bg-teal-700 p-10 text-center relative">
            <a href="dashboard_pelajar.php" class="absolute left-6 top-6 text-white/70 hover:text-white transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="w-24 h-24 bg-white rounded-full mx-auto flex items-center justify-center shadow-lg mb-4">
                <i class="fas fa-user-graduate text-teal-700 text-4xl"></i>
            </div>
            <h1 class="text-white font-bold text-xl uppercase tracking-widest">Akaun Pelajar</h1>
            <p class="text-teal-200 text-xs">ID Pelajar: #<?php echo $user['id']; ?></p>
        </div>

        <div class="p-10">
            <?php echo $mesej; ?>

            <form action="" method="POST" class="space-y-6">
                
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Nama Penuh</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-4 text-teal-600"></i>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required 
                               class="w-full bg-teal-50/50 border-2 border-transparent focus:border-teal-500 rounded-2xl p-3 pl-12 outline-none transition font-semibold text-gray-700">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">No. Matrik</label>
                    <div class="relative">
                        <i class="fas fa-id-badge absolute left-4 top-4 text-teal-600"></i>
                        <input type="text" name="no_matrik" value="<?php echo htmlspecialchars($user['no_matrik']); ?>" required 
                               class="w-full bg-teal-50/50 border-2 border-transparent focus:border-teal-500 rounded-2xl p-3 pl-12 outline-none transition font-semibold text-gray-700">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Emel Rasmi (Tetap)</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-4 text-gray-400"></i>
                        <input type="email" value="<?php echo $user['emel']; ?>" disabled 
                               class="w-full bg-gray-100 border-2 border-transparent rounded-2xl p-3 pl-12 text-gray-400 cursor-not-allowed font-medium">
                    </div>
                    <p class="text-[9px] text-gray-400 mt-2 px-1">* Emel tidak boleh diubah untuk tujuan keselamatan akademik.</p>
                </div>

                <div class="pt-4">
                    <button type="submit" name="update_profil" 
                            class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-teal-100 transition transform active:scale-95 uppercase tracking-widest text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400 font-medium italic">SewaSiswa UKM &copy; 2026</p>
            </div>
        </div>
    </div>

</body>
</html>