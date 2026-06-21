<?php
// Mulakan fungsi session untuk semakan log masuk pelajar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Sambungan ke Database
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

// Logik untuk mendapatkan Nama Pelajar yang sedang login secara dinamik
$nama_pelajar = "Pelajar"; 
if (isset($_SESSION['user_id'])) {
    $p_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
    $query_user = mysqli_query($conn, "SELECT nama FROM pelajar WHERE id = '$p_id'");
    if ($query_user && mysqli_num_rows($query_user) > 0) {
        $user_data = mysqli_fetch_assoc($query_user);
        $nama_pelajar = $user_data['nama'];
    }
}

// 2. Logik Carian & Saringan Array (Shopee Style)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Terima data filter sebagai Array
$categories = isset($_GET['category']) ? $_GET['category'] : [];
$genders = isset($_GET['gender']) ? $_GET['gender'] : [];
$filter_harga = isset($_GET['filter_harga']) ? $_GET['filter_harga'] : [];

// Hanya tarik rumah yang sudah 'Verified' oleh Admin dan berstatus 'Tersedia'
$query = "SELECT * FROM rumah WHERE status = 'Verified' AND status_sewa = 'Tersedia'";

if ($search != '') {
    $query .= " AND (nama_rumah LIKE '%$search%' OR alamat_rumah LIKE '%$search%')";
}

// Tapis Kategori (Jika ada pilihan & bukan sekadar 'Semua')
if (!empty($categories) && !in_array('Semua', $categories)) {
    $cat_escaped = array_map(function($value) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $value) . "'";
    }, $categories);
    $query .= " AND kategori IN (" . implode(',', $cat_escaped) . ")";
}

// Tapis Jantina (Jika ada pilihan & bukan sekadar 'Semua')
if (!empty($genders) && !in_array('Semua', $genders)) {
    $gen_escaped = array_map(function($value) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $value) . "'";
    }, $genders);
    $query .= " AND jantina IN (" . implode(',', $gen_escaped) . ")";
}

// Tapis Harga mengikut pelbagai pilihan range harga
if (!empty($filter_harga)) {
    $price_conditions = [];
    foreach ($filter_harga as $harga) {
        if ($harga == 'bawah_300') {
            $price_conditions[] = "hargaSewa < 300";
        } elseif ($harga == '300_500') {
            $price_conditions[] = "hargaSewa BETWEEN 300 AND 500";
        } elseif ($harga == 'atas_500') {
            $price_conditions[] = "hargaSewa > 500";
        }
    }
    if (!empty($price_conditions)) {
        $query .= " AND (" . implode(' OR ', $price_conditions) . ")";
    }
}

$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Cari Rumah Sewa Pelajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; overflow-x: hidden; }
        .bg-teal-custom { background-color: #0d9488; }
        .btn-accent { background-color: #ff5f5f; transition: 0.3s; }
        .btn-accent:hover { background-color: #e54e4e; transform: scale(1.05); }
        .card-shadow { box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.05); transition: 0.3s; }
        .card-shadow:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(13, 148, 136, 0.15); }
        .sidebar-transition { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white sticky top-0 z-40 border-b border-teal-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center space-x-3">
                    <img src="sewasiswa_logo.png" alt="Logo" class="h-14 md:h-16 object-contain">
                    <span class="text-2xl font-black text-teal-900 tracking-tighter italic">SEWA<span class="text-teal-600">SISWA</span></span>
                </div>
                
                <div class="hidden md:flex items-center space-x-8 text-sm font-bold uppercase text-teal-900">
                    <a href="utama.php" class="text-teal-600 border-b-2 border-teal-600 pb-1">Cari Rumah</a>
                    <a href="tentang_kami.php" class="hover:text-teal-600 transition">Tentang Kami</a>
                    <a href="hubungi_kami.php" class="hover:text-teal-600 transition">Hubungi Kami</a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="flex items-center space-x-4 border-l border-teal-100 pl-6 normal-case">
                            <div class="text-right">
                                <p class="text-[10px] text-teal-500 font-bold uppercase tracking-widest">Selamat Datang</p>
                                <p class="text-xs font-black text-teal-900 truncate max-w-[150px]"><?php echo htmlspecialchars($nama_pelajar); ?></p>
                            </div>
                            <a href="dashboard_pelajar.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase px-4 py-2.5 rounded-xl transition shadow-md flex items-center gap-2">
                                <i class="fas fa-columns text-xs"></i> Dashboard Saya
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="bg-teal-900 hover:bg-black text-white font-bold text-xs uppercase px-5 py-2.5 rounded-xl transition shadow-md">
                            <i class="fas fa-sign-in-alt mr-1"></i> Log Masuk
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <header class="bg-teal-custom py-16 px-4 relative">
        <div class="max-w-5xl mx-auto text-center text-white">
            <h1 class="text-4xl md:text-5xl font-black mb-3 uppercase tracking-tight">Cari Kediaman Impian</h1>
            <p class="text-sm opacity-90 mb-8 font-light">The Ultimate Student Housing Hub.</p>
            
            <div class="flex items-center gap-3 max-w-2xl mx-auto bg-white p-2 rounded-2xl shadow-xl border-4 border-teal-500/20">
                <form action="utama.php" method="GET" class="flex-grow flex items-center px-3">
                    <?php foreach($categories as $cat): ?> <input type="hidden" name="category[]" value="<?php echo htmlspecialchars($cat); ?>"> <?php endforeach; ?>
                    <?php foreach($genders as $gen): ?> <input type="hidden" name="gender[]" value="<?php echo htmlspecialchars($gen); ?>"> <?php endforeach; ?>
                    <?php foreach($filter_harga as $prc): ?> <input type="hidden" name="filter_harga[]" value="<?php echo htmlspecialchars($prc); ?>"> <?php endforeach; ?>
                    
                    <i class="fas fa-search text-teal-400 mr-3 text-sm"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Taip nama apartment atau jalan..." class="w-full py-2.5 outline-none text-slate-800 text-sm font-medium">
                    <button type="submit" class="hidden">Cari</button>
                </form>
                
                <button onclick="bukaFilterDrawer()" class="flex items-center gap-2 bg-slate-100 hover:bg-teal-50 text-teal-900 px-5 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition whitespace-nowrap border border-slate-200 shadow-sm">
                    <i class="fas fa-sliders-h text-teal-600"></i>
                    <span>Filter</span>
                    <?php if(!empty($categories) || !empty($genders) || !empty($filter_harga)): ?>
                        <span class="w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wider">Senarai Rumah Tersedia</h2>
                <p class="text-xs text-slate-400 mt-0.5">Menunjukkan <?php echo mysqli_num_rows($result); ?> kediaman yang telah disahkan sahih.</p>
            </div>
            
            <?php if(!empty($categories) || !empty($genders) || !empty($filter_harga) || $search != ''): ?>
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mr-1">Aktif:</span>
                    <?php if($search != '') echo "<span class='bg-slate-100 border text-slate-600 px-2.5 py-1 rounded-md text-[10px] font-bold'>🔍 '$search'</span>"; ?>
                    <?php foreach($categories as $c) echo "<span class='bg-teal-50 border border-teal-200 text-teal-700 px-2.5 py-1 rounded-md text-[10px] font-bold'>🏠 $c</span>"; ?>
                    <?php foreach($genders as $g) echo "<span class='bg-blue-50 border border-blue-200 text-blue-700 px-2.5 py-1 rounded-md text-[10px] font-bold'>👤 $g</span>"; ?>
                    <?php if(!empty($filter_harga)) echo "<span class='bg-amber-50 border border-amber-200 text-amber-700 px-2.5 py-1 rounded-md text-[10px] font-bold'>💰 Bajet Terpilih</span>"; ?>
                    <a href="utama.php" class="text-rose-600 font-extrabold text-[10px] uppercase tracking-wider hover:underline ml-2">Padam Semua</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while($h = mysqli_fetch_assoc($result)) {
                    $gambar_array = explode(',', $h['gambar']);
                    $gambar_utama = !empty($gambar_array[0]) ? "uploads/".trim($gambar_array[0]) : "placeholder.jpg";
            ?>
            <div class="bg-white rounded-3xl overflow-hidden card-shadow flex flex-col h-full border border-slate-100">
                <a href="maklumatrumah.php?id=<?php echo $h['id']; ?>" class="relative overflow-hidden group block">
                    <img src="<?php echo $gambar_utama; ?>" class="h-56 w-full object-cover transition duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="bg-white/90 text-teal-900 px-4 py-2 rounded-full font-bold text-xs uppercase shadow-lg">Lihat Maklumat</span>
                    </div>
                    
                    <span class="absolute top-4 left-4 bg-teal-600 text-white px-3 py-1 rounded-lg text-[9px] font-black uppercase shadow-md flex items-center">
                        <i class="fas fa-check-circle mr-1"></i> Verified
                    </span>
                    
                    <?php if(!empty($h['jantina'])): ?>
                        <span class="absolute bottom-4 left-4 <?php echo ($h['jantina'] == 'Lelaki') ? 'bg-blue-600' : 'bg-pink-600'; ?> text-white px-2.5 py-1 rounded-lg text-[9px] font-black uppercase shadow-md flex items-center gap-1">
                            <i class="fas <?php echo ($h['jantina'] == 'Lelaki') ? 'fa-mars' : 'fa-venus'; ?>"></i> <?php echo htmlspecialchars($h['jantina']); ?>
                        </span>
                    <?php endif; ?>

                    <span class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[10px] font-black text-teal-900 uppercase shadow-md">
                        <?php echo $h['kategori']; ?>
                    </span>
                </a>

                <div class="p-6 flex flex-col flex-grow">
                    <h3 class="text-lg font-bold text-teal-900 leading-tight mb-2 truncate">
                        <?php echo htmlspecialchars($h['nama_rumah']); ?>
                    </h3>
                    
                    <p class="text-teal-600 text-[11px] font-medium flex items-center mb-4">
                        <i class="fas fa-location-dot mr-2"></i> 
                        <span class="truncate"><?php echo htmlspecialchars($h['alamat_rumah']); ?></span>
                    </p>

                    <p class="text-gray-500 text-xs italic mb-5 line-clamp-2">
                        "<?php echo htmlspecialchars($h['deskripsi']); ?>"
                    </p>

                    <div class="mt-auto pt-4 border-t border-slate-50 flex justify-between items-center">
                        <div>
                            <p class="text-[9px] text-teal-500 font-bold uppercase tracking-tighter">Sewa Bulanan</p>
                            <p class="text-xl font-black text-teal-900">RM<?php echo number_format($h['hargaSewa'], 0); ?></p>
                        </div>
                        <a href="maklumatrumah.php?id=<?php echo $h['id']; ?>" class="btn-accent text-white px-4 py-2.5 rounded-xl text-xs font-bold uppercase shadow-md hover:shadow-rose-200">
                            Pilih
                        </a>
                    </div>
                </div>
            </div>
            <?php 
                }
            } else {
                echo "
                <div class='col-span-full py-20 text-center bg-white rounded-[3rem] shadow-sm border border-dashed border-slate-200'>
                    <div class='w-24 h-24 bg-teal-50 rounded-full flex items-center justify-center mx-auto mb-6'>
                        <i class='fas fa-house-circle-exclamation text-teal-200 text-5xl'></i>
                    </div>
                    <h3 class='text-xl font-bold text-teal-900 uppercase tracking-widest'>Tiada Kediaman Dijumpai</h3>
                    <p class='text-gray-400 text-sm mt-2'>Tiada iklan sepadan dengan kriteria carian atau tapisan filter anda.</p>
                    <a href='utama.php' class='inline-block mt-8 text-teal-600 font-bold border-b-2 border-teal-600 pb-1'>Reset Semua Filter</a>
                </div>";
            }
            ?>
        </div>
    </main>

    <div id="filterOverlay" onclick="tutupFilterDrawer()" class="fixed inset-0 bg-black/40 z-50 hidden opacity-0 transition-opacity duration-300"></div>

    <div id="filterDrawer" class="fixed top-0 right-0 h-full w-full max-w-sm bg-white z-50 shadow-2xl transform translate-x-full sidebar-transition flex flex-col justify-between">
        
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 flex items-center gap-2">
                <i class="fas fa-filter text-teal-600"></i> Saringan Pilihan
            </h3>
            <button onclick="tutupFilterDrawer()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-grow space-y-8">
            <form id="shopeeFilterForm" action="utama.php" method="GET" class="space-y-8">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">

                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-wider">Kategori Rumah</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <?php
                        $shopee_cats = ['Semua', 'Bilik', 'Rumah', 'Condo', 'Apartment'];
                        foreach($shopee_cats as $sc):
                            $isSelCat = in_array($sc, $categories) || (empty($categories) && $sc == 'Semua');
                        ?>
                            <div onclick="toggleCheckbox(this)" class="filter-card cursor-pointer text-center py-2.5 text-xs font-bold rounded-xl border transition-all duration-200 
                                <?php echo $isSelCat ? 'bg-teal-50 text-teal-700 border-teal-500 ring-2 ring-teal-500/10 active-filter' : 'bg-slate-50 text-slate-600 border-slate-200/80 hover:bg-slate-100'; ?>">
                                <input type="checkbox" name="category[]" value="<?php echo $sc; ?>" <?php if($isSelCat) echo 'checked'; ?> class="hidden invisible-check">
                                <span><?php echo $sc; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-wider">Jantina Penghuni</h4>
                    <div class="space-y-2">
                        <?php
                        $shopee_genders = [
                            'Semua' => 'Semua Jantina',
                            'Lelaki' => '👦 Lelaki Sahaja',
                            'Perempuan' => '👧 Perempuan Sahaja'
                        ];
                        foreach($shopee_genders as $gk => $gl):
                            $isSelGen = in_array($gk, $genders) || (empty($genders) && $gk == 'Semua');
                        ?>
                            <div onclick="toggleCheckbox(this)" class="filter-card flex items-center justify-between p-3 text-xs font-semibold rounded-xl border cursor-pointer transition-all duration-200
                                <?php echo $isSelGen ? 'bg-teal-50 text-teal-700 border-teal-500 font-bold active-filter' : 'bg-slate-50 text-slate-600 border-slate-200/80 hover:bg-slate-100'; ?>">
                                <span><?php echo $gl; ?></span>
                                <input type="checkbox" name="gender[]" value="<?php echo $gk; ?>" <?php if($isSelGen) echo 'checked'; ?> class="w-4 h-4 accent-teal-600 invisible-check">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-wider">Had Bajet Bulanan</h4>
                    <div class="space-y-2">
                        <?php
                        $shopee_prices = [
                            'bawah_300' => 'Bawah RM 300 / bulan',
                            '300_500' => 'RM 300 - RM 500 / bulan',
                            'atas_500' => 'Atas RM 500 / bulan'
                        ];
                        foreach($shopee_prices as $pk => $pl):
                            $isSelPr = in_array($pk, $filter_harga);
                        ?>
                            <div onclick="toggleCheckbox(this)" class="filter-card flex items-center justify-between p-3 text-xs font-semibold rounded-xl border cursor-pointer transition-all duration-200
                                <?php echo $isSelPr ? 'bg-teal-50 text-teal-700 border-teal-500 font-bold active-filter' : 'bg-slate-50 text-slate-600 border-slate-200/80 hover:bg-slate-100'; ?>">
                                <span><?php echo $pl; ?></span>
                                <input type="checkbox" name="filter_harga[]" value="<?php echo $pk; ?>" <?php if($isSelPr) echo 'checked'; ?> class="w-4 h-4 accent-teal-600 invisible-check">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-4 border-t bg-slate-50 grid grid-cols-2 gap-3">
            <a href="utama.php" class="py-3 bg-white text-slate-700 font-bold text-xs uppercase tracking-wider text-center rounded-xl border border-slate-300 hover:bg-slate-100 transition shadow-sm">
                Reset
            </a>
            <button type="submit" form="shopeeFilterForm" class="py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider text-center rounded-xl transition shadow-md shadow-teal-600/10">
                Apply
            </button>
        </div>
    </div>

    <footer class="bg-teal-900 text-white py-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex justify-center items-center space-x-3 mb-6">
                <img src="sewasiswa_logo.png" alt="Logo" class="h-12 object-contain brightness-0 invert">
                <span class="text-xl font-black italic tracking-widest uppercase">SewaSiswa</span>
            </div>
            <p class="text-teal-300 text-sm mb-6 uppercase tracking-wider font-bold">Platform Kediaman Pelajar Terunggul</p>
            <div class="flex justify-center space-x-6 text-xs font-bold uppercase tracking-widest text-teal-400">
                <a href="#" class="hover:text-white transition">Privasi</a>
                <a href="#" class="hover:text-white transition">Hubungi</a>
                <a href="#" class="hover:text-white transition">Bantuan</a>
            </div>
            <p class="mt-10 text-[9px] text-teal-500 font-bold uppercase tracking-widest">&copy; 2026 SEWASISWA APPS. ALL RIGHTS RESERVED.</p>
        </div>
    </footer>

    <?php include('chatbot_widget.php'); ?>

    <script>
        const filterOverlay = document.getElementById('filterOverlay');
        const filterDrawer = document.getElementById('filterDrawer');

        function bukaFilterDrawer() {
            filterOverlay.classList.remove('hidden');
            setTimeout(() => { filterOverlay.classList.add('opacity-100'); }, 50);
            filterDrawer.classList.remove('translate-x-full');
        }

        function tutupFilterDrawer() {
            filterOverlay.classList.remove('opacity-100');
            setTimeout(() => { filterOverlay.classList.add('hidden'); }, 300);
            filterDrawer.classList.add('translate-x-full');
        }

        // FUNGSI UTAMA JAVASCRIPT: Mengawal penukaran class CSS apabila kad diklik dan tanda tick pada input checkbox halimunan
        function toggleCheckbox(element) {
            const checkbox = element.querySelector('.invisible-check');
            
            // Songsangkan nilai check semasa diklik
            checkbox.checked = !checkbox.checked;

            // Jika nilai ditukar, ubah design warna kad border & background
            if (checkbox.checked) {
                element.classList.remove('bg-slate-50', 'text-slate-600', 'border-slate-200/80');
                element.classList.add('bg-teal-50', 'text-teal-700', 'border-teal-500', 'ring-2', 'ring-teal-500/10', 'font-bold');
            } else {
                element.classList.remove('bg-teal-50', 'text-teal-700', 'border-teal-500', 'ring-2', 'ring-teal-500/10', 'font-bold');
                element.classList.add('bg-slate-50', 'text-slate-600', 'border-slate-200/80');
            }
        }
    </script>
</body>
</html>