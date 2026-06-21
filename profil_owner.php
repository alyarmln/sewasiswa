<?php
session_start();
include('chatbot_widget.php');

// 1. Sambungan ke Database
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

// 2. Semak ID Tuan Rumah dari URL
if (isset($_GET['id'])) {
    $owner_id = mysqli_real_escape_string($conn, $_GET['id']);

    // 3. Ambil Maklumat Tuan Rumah
    $query_owner = "SELECT * FROM tuan_rumah WHERE id = '$owner_id'";
    $res_owner = mysqli_query($conn, $query_owner);
    $owner = mysqli_fetch_assoc($res_owner);

    if (!$owner) {
        die("<script>alert('Profil tuan rumah tidak dijumpai!'); window.location='utama.php';</script>");
    }

    // 4. LOGIK PENGIRAAN OVERALL RATING (Dari semua ulasan miliknya)
    $query_rating = "SELECT AVG(rating) AS purata_rating, COUNT(id) AS jumlah_ulasan FROM ulasan WHERE tuan_rumah_id = '$owner_id'";
    $res_rating = mysqli_query($conn, $query_rating);
    $rating_data = mysqli_fetch_assoc($res_rating);
    
    $overall_rating = !empty($rating_data['purata_rating']) ? round($rating_data['purata_rating'], 1) : 0;
    $total_reviews = $rating_data['jumlah_ulasan'] ?? 0;

    // 5. Ambil Semua Senarai Rumah Milik Tuan Rumah Ini
    $query_properties = "SELECT * FROM rumah WHERE tuan_rumah_id = '$owner_id' ORDER BY id DESC";
    $res_properties = mysqli_query($conn, $query_properties);

} else {
    header("Location: utama.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil <?php echo htmlspecialchars($owner['nama']); ?> - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-slate-800">

    <!-- Navigasi -->
    <nav class="bg-white border-b sticky top-0 z-50 py-4 px-6 lg:px-20 flex justify-between items-center">
        <a href="utama.php" class="flex items-center space-x-2">
            <span class="text-xl font-extrabold text-teal-900 italic tracking-tighter">SEWA<span class="text-teal-600">SISWA</span></span>
        </a>
        <button onclick="history.back()" class="text-xs font-bold text-slate-500 hover:text-teal-600 transition uppercase tracking-widest cursor-pointer">
            <i class="fas fa-chevron-left mr-2"></i> Kembali
        </button>
    </nav>

    <!-- Kandungan Utama -->
    <main class="max-w-7xl mx-auto px-6 lg:px-20 py-10">
        
        <!-- Kad Profil Tuan Rumah & Rating Keseluruhan -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl flex flex-col md:flex-row items-center justify-between gap-8 mb-12">
            <div class="flex flex-col sm:flex-row items-center gap-6 text-center sm:text-left">
                <img src="uploads/<?php echo htmlspecialchars($owner['gambar_profil'] ?? 'default.jpg'); ?>" class="w-24 h-24 rounded-full border-4 border-teal-500 object-cover shadow-md">
                <div>
                    <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                        <h1 class="text-2xl font-extrabold text-slate-900"><?php echo htmlspecialchars($owner['nama']); ?></h1>
                        <span class="bg-teal-100 text-teal-700 text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">Verified Host</span>
                    </div>
                    <p class="text-xs text-slate-400 mb-3"><i class="fas fa-phone-alt mr-1"></i> <?php echo htmlspecialchars($owner['no_telefon']); ?></p>
                    <p class="text-slate-600 text-sm max-w-xl leading-relaxed italic">
                        "<?php echo !empty($owner['deskripsi_bisnes']) ? nl2br(htmlspecialchars($owner['deskripsi_bisnes'])) : 'Tiada deskripsi profil disediakan.'; ?>"
                    </p>
                </div>
            </div>

            <!-- Box Rating Keseluruhan -->
            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 text-center min-w-[200px] shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Rating Keseluruhan</p>
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="text-4xl font-black text-slate-950"><?php echo $overall_rating; ?></span>
                    <span class="text-sm text-slate-400 font-bold">/ 5.0</span>
                </div>
                
                <!-- Bintang Dinamik -->
                <div class="text-amber-500 text-base flex justify-center space-x-0.5 tracking-wide mb-1">
                    <?php 
                    $stars = round($overall_rating);
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $stars) {
                            echo '★';
                        } else {
                            echo '<span class="text-slate-200">★</span>';
                        }
                    }
                    ?>
                </div>
                <p class="text-[10px] text-slate-400 font-medium">(Berdasarkan <?php echo $total_reviews; ?> ulasan pelajar)</p>
            </div>
        </div>

        <!-- Bahagian Senarai Rumah -->
        <section>
            <h2 class="text-lg font-black uppercase tracking-widest text-slate-900 mb-6 flex items-center">
                <span class="w-8 h-1 bg-teal-600 mr-3 rounded-full"></span> Senarai Rumah Sewa Lain
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                if ($res_properties && mysqli_num_rows($res_properties) > 0):
                    while($rumah = mysqli_fetch_assoc($res_properties)):
                        // Proses gambar pertama untuk kad thumbnail
                        $images = !empty($rumah['gambar']) ? explode(',', $rumah['gambar']) : [];
                        $thumb = !empty($images[0]) ? trim($images[0]) : '';
                ?>
                    <!-- Kad Rumah -->
                    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl transition duration-300 flex flex-col justify-between group">
                        <div>
                            <!-- Thumbnail Gambar -->
                            <div class="relative h-48 bg-slate-100 overflow-hidden">
                                <?php if(!empty($thumb)): ?>
                                    <img src="uploads/<?php echo $thumb; ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <?php else: ?>
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-3xl mb-1"></i>
                                        <span class="text-[10px] italic">Tiada Gambar</span>
                                    </div>
                                <?php endif; ?>
                                <span class="absolute top-4 left-4 bg-teal-600 text-white text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-wider shadow">
                                    <?php echo htmlspecialchars($rumah['kategori'] ?? 'Rumah'); ?>
                                </span>
                            </div>

                            <!-- Butiran Rumah -->
                            <div class="p-6">
                                <h3 class="font-extrabold text-slate-900 text-base mb-2 line-clamp-1 group-hover:text-teal-600 transition">
                                    <?php echo htmlspecialchars($rumah['nama_rumah']); ?>
                                </h3>
                                <p class="text-slate-400 text-xs flex items-start mb-4 line-clamp-2">
                                    <i class="fas fa-map-marker-alt mt-0.5 mr-1.5 text-rose-500"></i>
                                    <span><?php echo htmlspecialchars($rumah['alamat_rumah']); ?></span>
                                </p>
                                
                                <div class="flex items-center gap-4 text-xs font-bold text-slate-500 border-t pt-4 border-slate-50">
                                    <span><i class="fas fa-couch text-teal-500 mr-1.5"></i><?php echo htmlspecialchars($rumah['furnishing']); ?></span>
                                    <span><i class="fas fa-car text-teal-500 mr-1.5"></i><?php echo htmlspecialchars($rumah['carpark']); ?> Slot</span>
                                </div>
                            </div>
                        </div>

                        <!-- Harga & Butang Klik Info -->
                        <div class="px-6 pb-6 pt-2 flex items-center justify-between">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Sewa Bulanan</p>
                                <p class="text-lg font-black text-teal-600">RM<?php echo number_format($rumah['hargaSewa'], 0); ?><span class="text-slate-400 text-[10px] font-medium">/bln</span></p>
                            </div>
                            <a href="maklumatrumah.php?id=<?php echo $rumah['id']; ?>" class="bg-slate-100 hover:bg-teal-900 text-slate-700 hover:text-white font-bold text-[10px] uppercase tracking-widest px-4 py-3 rounded-xl transition duration-200">
                                Lihat Info <i class="fas fa-chevron-right ml-1 text-[8px]"></i>
                            </a>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <!-- Keadaan jika tiada iklan rumah lain -->
                    <div class="col-span-1 md:col-span-3 text-center py-12 bg-white rounded-3xl border border-dashed border-slate-200">
                        <i class="fas fa-home text-slate-300 text-4xl mb-3"></i>
                        <p class="text-sm text-slate-400 italic">Tiada senarai rumah lain buat masa ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <footer class="py-10 text-center text-slate-400 text-[10px] font-bold uppercase tracking-widest border-t border-slate-100 mt-20">
        © 2026 SEWASISWA APPS • PROFIL HOST ID: OWNER-<?php echo $owner['id']; ?>
    </footer>

</body>
</html>