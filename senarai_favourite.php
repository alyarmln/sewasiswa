<?php
session_start();
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

// Pastikan pelajar sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginmasuk.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query untuk ambil senarai rumah yang telah di-favourite oleh pelajar ini
$query = "SELECT rumah.*, favourite.tarikh_tambah 
          FROM favourite 
          JOIN rumah ON favourite.rumah_id = rumah.id 
          WHERE favourite.pelajar_id = '$user_id'
          ORDER BY favourite.tarikh_tambah DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Favourite Saya - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0fdfa; }
    </style>
</head>
<body class="min-h-screen">

    <nav class="bg-white border-b border-teal-100 p-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="dashboard_pelajar.php" class="flex items-center space-x-2">
                <i class="fas fa-arrow-left text-teal-600"></i>
                <span class="font-bold text-teal-800">Kembali ke Dashboard</span>
            </a>
            <h1 class="text-xl font-black text-teal-800">RUMAH KEGEMARAN <span class="text-teal-500">SAYA</span></h1>
            <div class="w-10"></div> </div>
    </nav>

    <main class="max-w-6xl mx-auto py-12 px-6">
        <div class="mb-10">
            <h2 class="text-3xl font-bold text-gray-800">Rumah Idaman Anda ❤️</h2>
            <p class="text-gray-500">Senarai rumah sewa yang anda simpan untuk rujukan masa depan.</p>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $gambar = explode(',', $row['gambar'])[0];
                ?>
                    <div class="bg-white rounded-[30px] overflow-hidden shadow-sm border border-teal-50 hover:shadow-xl transition-all duration-300 group">
                        <div class="relative h-56">
                            <img src="uploads/<?php echo $gambar; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-4 right-4">
                                <a href="proses_favourite.php?id=<?php echo $row['id']; ?>" class="bg-white/90 p-3 rounded-full text-rose-500 shadow-lg">
                                    <i class="fas fa-heart"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <h3 class="font-bold text-lg text-gray-800 mb-2"><?php echo htmlspecialchars($row['nama_rumah']); ?></h3>
                            <p class="text-gray-400 text-xs mb-4 flex items-center">
                                <i class="fas fa-map-marker-alt mr-2 text-teal-500"></i>
                                <?php echo htmlspecialchars($row['alamat_rumah']); ?>
                            </p>
                            
                            <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Sewa Bulanan</p>
                                    <p class="text-xl font-black text-teal-600">RM<?php echo number_format($row['hargaSewa'], 0); ?></p>
                                </div>
                                <a href="maklumatrumah.php?id=<?php echo $row['id']; ?>" class="bg-teal-600 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-teal-700 transition">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <div class="bg-white w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <i class="far fa-heart text-4xl text-gray-200"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700">Belum ada favourite</h3>
                <p class="text-gray-400 mt-2">Anda belum menyimpan mana-mana rumah lagi.</p>
                <a href="utama.php" class="inline-block mt-6 bg-teal-600 text-white px-8 py-3 rounded-full font-bold shadow-lg hover:bg-teal-700 transition">Cari Rumah Sekarang</a>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>