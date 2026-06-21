<?php
session_start();
include('chatbot_widget.php');

// 1. Sambungan ke Database
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

// 2. Ambil ID rumah dari URL
if (isset($_GET['id'])) {
    $id_rumah = mysqli_real_escape_string($conn, $_GET['id']);

    // ==========================================
    // LOGIK PENINGKATAN JUMLAH KLIK IKLAN (+1)
    // ==========================================
    $sql_update_klik = "UPDATE `rumah` SET `jumlah_klik` = `jumlah_klik` + 1 WHERE `id` = '$id_rumah'";
    mysqli_query($conn, $sql_update_klik);
    // ==========================================

    // Semak status Favourite
    $is_favourite = false;
    if (isset($_SESSION['user_id'])) {
        $p_id = $_SESSION['user_id'];
        $check_fav = mysqli_query($conn, "SELECT * FROM favourite WHERE pelajar_id = '$p_id' AND rumah_id = '$id_rumah'");
        if (mysqli_num_rows($check_fav) > 0) {
            $is_favourite = true;
        }
    }

    // 3. Query Ambil Data Rumah & Tuan Rumah
    $query = "SELECT rumah.*, 
                     tuan_rumah.nama AS nama_owner, 
                     tuan_rumah.gambar_profil AS foto_owner, 
                     tuan_rumah.no_telefon AS tel_owner,
                     tuan_rumah.deskripsi_bisnes AS bio_owner
              FROM rumah 
              LEFT JOIN tuan_rumah ON rumah.tuan_rumah_id = tuan_rumah.id 
              WHERE rumah.id = '$id_rumah'";
    
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        die("<script>alert('Rumah tidak dijumpai!'); window.location='utama.php';</script>");
    }
} else {
    header("Location: utama.php");
    exit();
}

// 4. Logik Pengiraan Jarak ke UKM (Formula Haversine)
$ukm_lat = 2.9289; 
$ukm_lng = 101.7801;
$rumah_lat = isset($data['lat']) ? $data['lat'] : ''; 
$rumah_lng = isset($data['lng']) ? $data['lng'] : '';

function getDistance($lat1, $lon1, $lat2, $lon2) {
    if (empty($lat1) || empty($lon1)) return "N/A";
    $earth_radius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return number_format($earth_radius * $c, 1);
}

$jarak_ke_ukm = getDistance($rumah_lat, $rumah_lng, $ukm_lat, $ukm_lng);

// 5. Proses Gambar Rumah
$gambar_list = !empty($data['gambar']) ? explode(',', $data['gambar']) : [];

?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['nama_rumah'] ?? 'Maklumat Rumah'); ?> - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .map-frame { border-radius: 1.5rem; overflow: hidden; height: 350px; border: 4px solid white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b sticky top-0 z-50 py-4 px-6 lg:px-20 flex justify-between items-center">
        <a href="utama.php" class="flex items-center space-x-2">
            <span class="text-xl font-extrabold text-teal-900 italic tracking-tighter">SEWA<span class="text-teal-600">SISWA</span></span>
        </a>
        <a href="utama.php" class="text-xs font-bold text-slate-500 hover:text-teal-600 transition uppercase tracking-widest">
            <i class="fas fa-chevron-left mr-2"></i> Kembali
        </a>
    </nav>

    <main class="max-w-7xl mx-auto px-6 lg:px-20 py-10">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-2 space-y-8">
                
                <section>
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="bg-teal-600 text-white text-[10px] font-black px-3 py-1.5 rounded-xl uppercase tracking-wider"><?php echo htmlspecialchars($data['kategori'] ?? 'Rumah'); ?></span>
                        
                        <?php if (!empty($data['jantina'])): ?>
                            <?php 
                                if ($data['jantina'] == 'Lelaki') {
                                    $bg_gender = 'bg-blue-600 text-white';
                                    $icon_gender = 'fa-mars';
                                } elseif ($data['jantina'] == 'Perempuan') {
                                    $bg_gender = 'bg-pink-600 text-white';
                                    $icon_gender = 'fa-venus';
                                } else {
                                    $bg_gender = 'bg-slate-200 text-slate-700';
                                    $icon_gender = 'fa-users';
                                }
                            ?>
                            <span class="<?php echo $bg_gender; ?> text-[10px] font-black px-3 py-1.5 rounded-xl uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                                <i class="fas <?php echo $icon_gender; ?>"></i> <?php echo htmlspecialchars($data['jantina']); ?>
                            </span>
                        <?php endif; ?>

                        <span class="bg-amber-100 text-amber-800 text-[10px] font-black px-3 py-1.5 rounded-xl uppercase tracking-wider flex items-center gap-1">
                            <i class="fas fa-graduation-cap text-xs"></i> <?php echo $jarak_ke_ukm; ?> KM ke UKM
                        </span>
                    </div>
                    
                    <h1 class="text-4xl font-extrabold text-slate-900 leading-tight mb-2"><?php echo htmlspecialchars($data['nama_rumah'] ?? ''); ?></h1>
                    <p class="text-slate-500 flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-2 text-rose-500"></i>
                        <span><?php echo htmlspecialchars($data['alamat_rumah'] ?? ''); ?></span>
                    </p>
                </section>

               <section class="relative w-full mx-auto">
                    <div class="swiper mySwiper rounded-[2.5rem] overflow-hidden shadow-xl border-4 border-white bg-slate-100 h-[350px] md:h-[450px]">
                        <div class="swiper-wrapper">
                            <?php 
                            if (!empty($gambar_list)):
                                foreach ($gambar_list as $img): 
                                    if(empty($img)) continue; 
                            ?>
                                    <div class="swiper-slide w-full h-full">
                                        <img src="uploads/<?php echo trim($img); ?>" 
                                             alt="Gambar Rumah" 
                                             class="w-full h-full object-cover block select-none">
                                    </div>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                                <div class="swiper-slide flex flex-col items-center justify-center text-slate-400 w-full h-full">
                                    <i class="fas fa-image text-5xl mb-3"></i>
                                    <p class="text-xs italic">Tiada gambar rumah tersedia</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-pagination !bottom-6"></div>
                    </div>

                    <div class="swiper-button-prev-custom absolute left-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-slate-800 cursor-pointer shadow-md hover:bg-white transition">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </div>

                    <div class="swiper-button-next-custom absolute right-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-slate-800 cursor-pointer shadow-md hover:bg-white transition">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </div>
                </section>

                <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
                        <i class="fas fa-couch text-teal-500 mb-2"></i>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Furnishing</p>
                        <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($data['furnishing'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
                        <i class="fas fa-car text-teal-500 mb-2"></i>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Carpark</p>
                        <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($data['carpark'] ?? '0'); ?> Slot</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
                        <i class="fas fa-ruler-combined text-teal-500 mb-2"></i>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Jarak Kampus</p>
                        <p class="text-sm font-bold text-slate-700"><?php echo $jarak_ke_ukm; ?> KM</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
                        <i class="fas fa-tags text-teal-500 mb-2"></i>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Sewa Bulanan</p>
                        <p class="text-sm font-bold text-teal-600">RM<?php echo number_format($data['hargaSewa'] ?? 0, 0); ?></p>
                    </div>
                </section>

                <section class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 mb-6 flex items-center">
                        <span class="w-8 h-1 bg-rose-500 mr-3 rounded-full"></span> Lokasi Tepat (GPS)
                    </h3>
                    
                    <div class="map-frame">
                        <?php if(!empty($rumah_lat) && !empty($rumah_lng)): ?>
                            <iframe 
                                src="https://maps.google.com/maps?q=<?php echo $rumah_lat; ?>,<?php echo $rumah_lng; ?>&z=15&output=embed" 
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy">
                            </iframe>
                        <?php else: ?>
                            <iframe 
                                src="https://maps.google.com/maps?q=<?php echo urlencode($data['alamat_rumah'] ?? ''); ?>&z=15&output=embed" 
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy">
                            </iframe>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4 flex items-center justify-between bg-slate-50 p-4 rounded-xl">
                        <div class="text-xs text-slate-500">
                            <i class="fas fa-info-circle mr-1"></i> Klik 'View larger map' untuk navigasi Waze/Google Maps.
                        </div>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $rumah_lat; ?>,<?php echo $rumah_lng; ?>" target="_blank" class="text-[10px] font-black text-rose-600 uppercase tracking-wider hover:underline">
                            Dapatkan Arah Jalan <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </section>

                <section class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm space-y-8">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 mb-4 flex items-center">
                            <span class="w-8 h-1 bg-teal-500 mr-3 rounded-full"></span> Deskripsi Rumah
                        </h3>
                        <p class="text-slate-600 leading-relaxed text-sm"><?php echo nl2br(htmlspecialchars($data['deskripsi'] ?? '')); ?></p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8 pt-6 border-t border-slate-50">
                        <div>
                            <h3 class="text-xs font-black uppercase text-slate-900 mb-4 tracking-wider">Facilities</h3>
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                $facs = !empty($data['facilities']) ? explode(',', $data['facilities']) : [];
                                foreach($facs as $f) if(!empty($f)) echo "<span class='bg-slate-50 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-100'><i class='fas fa-check text-teal-500 mr-2'></i>".trim($f)."</span>";
                                ?>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xs font-black uppercase text-slate-900 mb-4 tracking-wider">Amenities Nearby</h3>
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                $amens = !empty($data['amenities']) ? explode(',', $data['amenities']) : [];
                                foreach($amens as $a) if(!empty($a)) echo "<span class='bg-slate-50 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-100'><i class='fas fa-location-arrow text-rose-500 mr-2'></i>".trim($a)."</span>";
                                ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-8 rounded-[2rem] shadow-xl border border-teal-50 sticky top-24">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Harga Sewaan</p>
                    <div class="flex items-baseline space-x-1 mb-6">
                        <span class="text-4xl font-black text-slate-900">RM<?php echo number_format($data['hargaSewa'] ?? 0, 0); ?></span>
                        <span class="text-slate-400 text-sm font-medium">/bulan</span>
                    </div>

                    <a href="tempah_janji.php?id=<?php echo $data['id']; ?>" class="w-full block text-center bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-rose-100 transition-all transform hover:-translate-y-1 mb-4 uppercase text-xs tracking-widest">
                        Book Viewing Now
                    </a>

                    <a href="proses_favourite.php?id=<?php echo $data['id']; ?>" class="w-full block text-center <?php echo $is_favourite ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-600'; ?> hover:opacity-80 font-bold py-4 rounded-2xl transition-all mb-4 uppercase text-xs tracking-widest flex items-center justify-center space-x-2">
                        <i class="<?php echo $is_favourite ? 'fas' : 'far'; ?> fa-heart text-rose-500"></i>
                        <span><?php echo $is_favourite ? ' Simpan' : 'Simpan Favourite'; ?></span>
                    </a>

                    <div class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                            <i class="fas fa-shield-alt text-teal-600"></i> Polisi & Perlindungan Penyewa
                        </p>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <a href="terms_conditions.php" target="_blank" class="flex items-center gap-2 p-2.5 bg-white rounded-xl border border-slate-100 hover:border-teal-500 hover:shadow-sm transition group">
                                <i class="fas fa-file-contract text-teal-500 text-xs group-hover:scale-110 transition"></i>
                                <div class="text-left">
                                    <p class="text-[10px] font-bold text-slate-700 leading-tight">Terma & Syarat</p>
                                    <p class="text-[8px] text-slate-400 font-medium">Sila baca peraturan</p>
                                </div>
                            </a>

                            <a href="tenancy_agreement.php" target="_blank" class="flex items-center gap-2 p-2.5 bg-white rounded-xl border border-slate-100 hover:border-rose-500 hover:shadow-sm transition group">
                                <i class="fas fa-gavel text-rose-400 text-xs group-hover:scale-110 transition"></i>
                                <div class="text-left">
                                    <p class="text-[10px] font-bold text-slate-700 leading-tight">Agreement</p>
                                    <p class="text-[8px] text-slate-400 font-medium">Templat perjanjian</p>
                                </div>
                            </a>
                        </div>
                        
                        <p class="text-[9px] text-slate-400 leading-normal italic text-center pt-1 border-t border-slate-200/60">
                            *Dengan membuat tempahan, anda tertakluk kepada kontrak perlindungan SewaSiswa.
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs font-bold text-slate-400 mb-2 uppercase tracking-wider text-center">Kongsi Iklan Ini</p>
                        <div class="flex justify-center items-center gap-3">
                            <?php
                            $nama_rumah_share = isset($data['nama_rumah']) ? $data['nama_rumah'] : "Rumah Sewa Siswa";
                            $harga_share = isset($data['hargaSewa']) ? $data['hargaSewa'] : 0;

                            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                            $url_semasa = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                            
                            $teks_kongsi = "Hai! Sila lihat rumah sewa menarik di SewaSiswa: " . $nama_rumah_share . " (RM " . number_format($harga_share, 0) . "/bulan). Layari di sini: ";
                            ?>

                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($teks_kongsi . $url_semasa); ?>" 
                               target="_blank" 
                               class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition duration-300 shadow-sm" 
                               title="Kongsi ke WhatsApp">
                                 <i class="fab fa-whatsapp text-lg"></i>
                            </a>

                            <a href="https://telegram.me/share/url?url=<?php echo urlencode($url_semasa); ?>&text=<?php echo urlencode($teks_kongsi); ?>" 
                               target="_blank" 
                               class="w-10 h-10 rounded-full bg-sky-50 text-sky-600 hover:bg-sky-600 hover:text-white flex items-center justify-center transition duration-300 shadow-sm" 
                               title="Kongsi ke Telegram">
                                 <i class="fab fa-telegram-plane text-lg"></i>
                            </a>

                            <button onclick="salinPautan()" 
                                    class="w-10 h-10 rounded-full bg-pink-50 text-pink-600 hover:bg-pink-600 hover:text-white flex items-center justify-center transition duration-300 shadow-sm" 
                                    title="Salin Pautan">
                                <i class="fas fa-link text-sm"></i>
                            </button>
                        </div>
                    </div>

                   <div class="mt-8 pt-8 border-t border-slate-100">
                        <a href="profil_owner.php?id=<?php echo $data['tuan_rumah_id']; ?>" class="flex items-center gap-4 mb-4 group block bg-slate-50 p-3 rounded-2xl border border-slate-100 hover:border-teal-500 transition-all">
                            <img src="uploads/<?php echo htmlspecialchars($data['foto_owner'] ?? 'default.jpg'); ?>" class="w-12 h-12 rounded-full border-2 border-teal-500 object-cover group-hover:scale-105 transition">
                            <div>
                                <h4 class="font-bold text-sm text-slate-800 group-hover:text-teal-600 transition flex items-center gap-1">
                                    <?php echo htmlspecialchars($data['nama_owner'] ?? 'Tuan Rumah'); ?>
                                    <i class="fas fa-arrow-right text-[10px] text-slate-400 group-hover:text-teal-600 group-hover:translate-x-1 transition"></i>
                                </h4>
                                <p class="text-[9px] text-teal-600 font-black uppercase tracking-wider">Lihat Profil & Rumah <i class="fas fa-circle text-[6px] text-emerald-500 ml-1"></i></p>
                            </div>
                        </a>

                     <a href="ruangchat.php?id=<?php echo $data['tuan_rumah_id']; ?>&rumah_id=<?php echo $data['id']; ?>&role=tuan_rumah" class="w-full bg-teal-900 text-white py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-teal-800 transition-colors">
    <i class="fas fa-comment-dots"></i> Chat Sekarang
</a>
                    </div>
                </div>
            </div>
        </div>

        <section class="mt-10 bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 flex items-center">
                    <span class="w-8 h-1 bg-amber-500 mr-3 rounded-full"></span> Ulasan Pelajar
                </h3>
                <a href="ulasan_owner.php?id=<?php echo $data['tuan_rumah_id']; ?>" class="inline-flex items-center justify-center px-4 py-2 bg-amber-500 text-white font-bold text-xs uppercase tracking-wider rounded-full hover:bg-amber-600 transition shadow-sm">
                    <i class="fas fa-star mr-1.5 text-[10px]"></i> Tulis Ulasan
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php
                $tr_id = $data['tuan_rumah_id'];
                
                $query_review = mysqli_query($conn, "SELECT ulasan.*, pelajar.nama FROM ulasan 
                                                     JOIN pelajar ON ulasan.pelajar_id = pelajar.id 
                                                     WHERE ulasan.tuan_rumah_id = '$tr_id' 
                                                     ORDER BY ulasan.tarikh_ulasan DESC LIMIT 4");

                if ($query_review && mysqli_num_rows($query_review) > 0):
                    while($rev = mysqli_fetch_assoc($query_review)):
                        $tarikh = date('d M Y', strtotime($rev['tarikh_ulasan']));
                ?>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex flex-col">
                                    <span class="font-bold text-xs text-slate-800"><?php echo htmlspecialchars($rev['nama']); ?></span>
                                    <span class="text-[9px] text-slate-400 mt-0.5"><?php echo $tarikh; ?></span>
                                </div>
                                <div class="text-amber-500 text-xl flex space-x-0.5 tracking-wide">
                                    <?php 
                                    $rating_stars = intval($rev['rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating_stars) {
                                            echo '★';
                                        } else {
                                            echo '<span class="text-slate-200">★</span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 italic leading-relaxed mt-1">"<?php echo htmlspecialchars($rev['komen']); ?>"</p>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="col-span-1 md:col-span-2 text-center py-6">
                        <p class="text-xs text-slate-400 italic">Belum ada ulasan untuk tuan rumah ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <footer class="py-12 border-t border-slate-100 bg-white mt-16 text-center space-y-3">
        <div class="flex justify-center items-center gap-6 text-[10px] font-bold uppercase tracking-widest text-slate-400">
            <a href="terms_conditions.php" class="hover:text-teal-600 transition">Terms & Conditions</a>
            <span class="text-slate-200">•</span>
            <a href="tenancy_agreement.php" class="hover:text-teal-600 transition">Tenancy Agreement</a>
            <span class="text-slate-200">•</span>
            <span>Property ID: SS-<?php echo $data['id']; ?></span>
        </div>
        <div class="text-[9px] font-bold uppercase tracking-wider text-slate-300">
            © 2026 SEWASISWA APPS • HAK CIPTA TERPELIHARA
        </div>
    </footer>

    <script>
        var swiper = new Swiper(".mySwiper", {
            loop: true,
            speed: 600,
            grabCursor: true,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
                dynamicBullets: true,
            },
            navigation: {
                nextEl: ".swiper-button-next-custom",
                prevEl: ".swiper-button-prev-custom",
            },
            keyboard: {
                enabled: true,
            },
        });

        function salinPautan() {
            var urlMesej = window.location.href;
            navigator.clipboard.writeText(urlMesej).then(function() {
                alert("Pautan iklan berjaya disalin!");
            }, function() {
                alert("Gagal menyalin pautan. Sila salin secara manual dari ruangan URL browser.");
            });
        }
    </script>

    <style>
        .swiper-pagination-bullet-active {
            background: #f59e0b !important;
            width: 24px !important;
            border-radius: 4px !important;
        }
    </style>
</body>
</html>