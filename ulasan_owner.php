<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

// 1. Sekatan Akses: Pastikan pelajar sudah log masuk guna 'user_id' asal anda
if (!isset($_SESSION['user_id'])) {
    header("Location: loginmasuk.php");
    exit();
}

$pelajar_id = $_SESSION['user_id'];
// Ambil ID Tuan Rumah dari URL (Contoh: ulasan_owner.php?id=1)
$tuan_rumah_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($tuan_rumah_id)) {
    die("<div class='p-6 text-red-600 font-bold'>Ralat: ID Tuan Rumah tidak dijumpai dalam sistem.</div>");
}

// 2. Ambil nama Tuan Rumah untuk tajuk paparan ulasan
$owner_query = mysqli_query($conn, "SELECT nama FROM tuan_rumah WHERE id = '$tuan_rumah_id'");
$owner_data = mysqli_fetch_assoc($owner_query);
$nama_owner = $owner_data['nama'] ?? "Tuan Rumah";

// 3. Proses kemasukan ulasan apabila borang dihantar (POST)
$mesej_sukses = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $komen = mysqli_real_escape_string($conn, $_POST['komen']);

    if (!empty($rating) && !empty($komen)) {
        // Menggunakan kolum tarikh_ulasan = NOW() ikut struktur jadual anda
        $insert_query = "INSERT INTO ulasan (pelajar_id, tuan_rumah_id, rating, komen, tarikh_ulasan) 
                         VALUES ('$pelajar_id', '$tuan_rumah_id', '$rating', '$komen', NOW())";
        
        if (mysqli_query($conn, $insert_query)) {
            $mesej_sukses = "Ulasan dan penarafan anda telah berjaya disimpan!";
        } else {
            $mesej_sukses = "Ralat pangkalan data: " . mysqli_error($conn);
        }
    }
}

// 4. Ambil semua rekod ulasan terdahulu untuk dipaparkan di bahagian bawah
$reviews_query = mysqli_query($conn, "SELECT u.*, p.nama AS nama_pelajar 
                                      FROM ulasan u 
                                      JOIN pelajar p ON u.pelajar_id = p.id 
                                      WHERE u.tuan_rumah_id = '$tuan_rumah_id' 
                                      ORDER BY u.tarikh_ulasan DESC");
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Tuan Rumah - <?php echo htmlspecialchars($nama_owner); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen p-4 md:p-8">
    <div class="max-w-3xl mx-auto">
        
        <div class="mb-6">
            <a href="dashboard_pelajar.php" class="text-teal-600 hover:text-teal-800 font-bold inline-flex items-center transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-6 md:p-8 border border-gray-100 mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Ulasan & Penarafan</h1>
            <p class="text-gray-500 text-sm mb-6">Berikan maklum balas ikhlas anda kepada tuan rumah: 
                <span class="text-teal-600 font-semibold"><?php echo htmlspecialchars($nama_owner); ?></span>
            </p>

            <?php if (!empty($mesej_sukses)): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm font-medium">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo $mesej_sukses; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Penarafan Bintang</label>
                    <select name="rating" required class="block w-full md:w-1/2 border border-gray-200 rounded-xl px-4 py-3 bg-gray-50 outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
                        <option value="">-- Pilih Nilai Penarafan --</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Cemerlang)</option>
                        <option value="4">⭐⭐⭐⭐ (4 - Baik & Selesa)</option>
                        <option value="3">⭐⭐⭐ (3 - Memuaskan)</option>
                        <option value="2">⭐⭐ (2 - Kurang Memuaskan)</option>
                        <option value="1">⭐ (1 - Sangat Lemah)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Komen / Maklum Balas Anda</label>
                    <textarea name="komen" rows="4" required class="block w-full border border-gray-200 rounded-2xl p-4 bg-gray-50 outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition" placeholder="Tuliskan ulasan mengenai respon chat, pengurusan rumah, atau layanan tuan rumah ini..."></textarea>
                </div>

                <div>
                    <button type="submit" class="w-full md:w-auto bg-teal-600 text-white px-8 py-3.5 rounded-full font-bold shadow-md hover:bg-teal-700 transition transform active:scale-95">
                        Hantar Ulasan Saya
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-6 md:p-8 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-comments text-teal-600 mr-2"></i> Ulasan Penyewa Terdahulu
            </h2>

            <div class="space-y-6">
                <?php if (mysqli_num_rows($reviews_query) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($reviews_query)): ?>
                        <div class="border-b border-gray-100 pb-6 last:border-none last:pb-0">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($row['nama_pelajar']); ?></h4>
                                    <span class="text-[11px] text-gray-400">
                                        <i class="far fa-calendar-alt mr-1"></i><?php echo date('d M Y, h:i a', strtotime($row['tarikh_ulasan'])); ?>
                                    </span>
                                </div>
                                
                                <div class="text-amber-400 text-xs flex space-x-0.5">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $row['rating']) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm leading-relaxed bg-gray-50/50 p-3 rounded-xl border border-gray-50 mt-2">
                                <?php echo nl2br(htmlspecialchars($row['komen'])); ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-400 italic text-sm">
                        <i class="fas fa-comment-slash text-3xl block mb-3 text-gray-300"></i>
                        Belum ada ulasan bertulis untuk tuan rumah ini buat masa sekarang.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>