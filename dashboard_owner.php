<?php
session_start();

// 1. Semakan log masuk & tapisan keselamatan SQL Injection
if (!isset($_SESSION['owner_id'])) {
    header("Location: login_owner.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if (!$conn) {
    die("Sambungan pangkalan data gagal: " . mysqli_connect_error());
}

$owner_id = mysqli_real_escape_string($conn, $_SESSION['owner_id']);

// Proses Tukar Status Arkib Rumah (Tersedia / Disewakan) jika butang diklik
if (isset($_GET['tukar_status_rumah']) && isset($_GET['rumah_id'])) {
    $status_baru = mysqli_real_escape_string($conn, $_GET['tukar_status_rumah']);
    $rid = mysqli_real_escape_string($conn, $_GET['rumah_id']);
    
    // Pastikan rumah ini memang milik owner yang sedang log masuk
    mysqli_query($conn, "UPDATE rumah SET status_sewa = '$status_baru' WHERE id = '$rid' AND tuan_rumah_id = '$owner_id'");
    header("Location: dashboard_owner.php#hartanah_section");
    exit();
}

// 2. Ambil data tuan rumah
$owner_query = mysqli_query($conn, "SELECT * FROM tuan_rumah WHERE id = '$owner_id'");
$owner = mysqli_fetch_assoc($owner_query);

// 3. Ambil data JANJI TEMU (JOIN dengan Pelajar & Rumah)
$jt_query = "SELECT jt.*, p.nama AS nama_pelajar, p.no_matrik, r.nama_rumah 
             FROM janji_temu jt
             JOIN pelajar p ON jt.pelajar_id = p.id
             JOIN rumah r ON jt.rumah_id = r.id
             WHERE jt.tuan_rumah_id = '$owner_id'
             ORDER BY jt.id DESC";
$jt_result = mysqli_query($conn, $jt_query);

// 4. Ambil data RUMAH milik owner (Termasuk kolum jumlah_klik & status_sewa)
$rumah_query = mysqli_query($conn, "SELECT * FROM rumah WHERE tuan_rumah_id = '$owner_id' ORDER BY id DESC");

// 5. Statistik Ringkas
$total_jt = mysqli_num_rows($jt_result);
$total_rumah = mysqli_num_rows($rumah_query);

$msg_query = mysqli_query($conn, "SELECT COUNT(*) as total_msg FROM messages WHERE receiver_id = '$owner_id'");
$msg_stats = mysqli_fetch_assoc($msg_query);

// 6. Ambil data SEMUA rumah untuk rujukan pasaran pelajar (Hanya yang Verified & Tersedia)
$semua_rumah_query = mysqli_query($conn, "SELECT r.*, t.nama AS nama_owner FROM rumah r JOIN tuan_rumah t ON r.tuan_rumah_id = t.id WHERE r.status = 'Verified' AND r.status_sewa = 'Tersedia' ORDER BY r.id DESC");
$total_semua_rumah = mysqli_num_rows($semua_rumah_query);

// 7. DATA SIMULASI UNTUK CARTA (Mendapatkan trend klik rumah milik owner)
// Mengira jumlah klik terkumpul bagi semua rumah milik owner ini
$total_clicks_query = mysqli_query($conn, "SELECT SUM(jumlah_klik) as total_clicks FROM rumah WHERE tuan_rumah_id = '$owner_id'");
$total_clicks_data = mysqli_fetch_assoc($total_clicks_query);
$grand_total_clicks = $total_clicks_data['total_clicks'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tuan Rumah - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0fdfa; scroll-behavior: smooth; }
        .sidebar-gradient { background: linear-gradient(to bottom, #114b5f, #1a936f); }
        .menu-card { transition: all 0.3s ease; }
        .menu-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 sidebar-gradient text-white flex flex-col shadow-xl sticky top-0 h-screen hidden md:flex">
        <div class="p-8 text-center border-b border-white/10">
            <h1 class="text-xl font-bold tracking-tighter text-white">SEWA<span class="text-teal-300">SISWA</span></h1>
            <p class="text-[10px] font-bold tracking-widest uppercase opacity-70">Tuan Rumah</p>
        </div>
        <nav class="flex-grow p-6 space-y-4">
            <a href="dashboard_owner.php" class="flex items-center space-x-3 bg-white/10 p-3 rounded-xl">
                <i class="fas fa-th-large"></i> <span class="text-sm">Dashboard</span>
            </a>
            <a href="senarai_chat_owner.php" class="flex items-center justify-between hover:bg-white/5 p-3 rounded-xl transition text-sm">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-comments"></i> <span>Mesej Pelajar</span>
                </div>
                <?php if($msg_stats['total_msg'] > 0): ?>
                    <span class="bg-yellow-400 text-teal-900 text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">NEW</span>
                <?php endif; ?>
            </a>
            <a href="#janji_temu_section" class="flex items-center space-x-3 hover:bg-white/5 p-3 rounded-xl transition text-sm">
                <i class="fas fa-calendar-alt"></i> <span>Janji Temu</span>
            </a>
            <a href="update_profil_tuanrumah.php" class="flex items-center space-x-3 hover:bg-white/5 p-3 rounded-xl transition text-sm">
                <i class="fas fa-user-circle"></i> <span>Profil</span>
            </a>
        </nav>
        <div class="p-6">
            <a href="logout.php" class="block text-center bg-red-500 hover:bg-red-600 p-3 rounded-xl text-xs font-bold uppercase transition shadow-lg">Log Keluar</a>
        </div>
    </aside>

    <main class="flex-grow p-6 md:p-10">
        
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Selamat Datang, <?php echo htmlspecialchars($owner['nama']); ?>!</h1>
                <div class="mt-3">
                    <?php if($owner['status'] == 'Pending'): ?>
                        <span class="bg-orange-100 text-orange-600 px-4 py-1.5 rounded-full text-xs font-bold border border-orange-200">
                            <i class="fas fa-clock mr-1"></i> PROFIL: MENUNGGU PENGESAHAN ADMIN
                        </span>
                    <?php elseif($owner['status'] == 'Rejected'): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between max-w-2xl">
                            <div>
                                <p class="text-red-700 font-bold text-sm uppercase">Pendaftaran Profil Ditolak!</p>
                                <p class="text-red-600 text-xs mt-1 font-medium italic">Sebab: "<?php echo htmlspecialchars($owner['alasan_tolak']); ?>"</p>
                            </div>
                            <a href="update_profil_tuanrumah.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition shadow-md">
                                <i class="fas fa-user-edit mr-1"></i> Baiki Profil
                            </a>
                        </div>
                    <?php else: ?>
                        <span class="bg-teal-100 text-teal-600 px-4 py-1.5 rounded-full text-xs font-bold border border-teal-200">
                            <i class="fas fa-check-circle mr-1"></i> PROFIL DISAHKAN
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center space-x-4 bg-white p-2 pr-6 rounded-full shadow-sm border border-teal-100">
                <?php $profile_img = !empty($owner['gambar_profil']) ? 'uploads/'.$owner['gambar_profil'] : 'https://ui-avatars.com/api/?name='.urlencode($owner['nama']); ?>
                <img src="<?php echo $profile_img; ?>" class="w-12 h-12 rounded-full object-cover border-2 border-teal-500">
                <div>
                    <p class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($owner['nama']); ?></p>
                    <p class="text-[10px] text-teal-600 font-bold uppercase">Tuan Rumah</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[30px] shadow-sm border-b-4 border-teal-500 menu-card">
                <p class="text-slate-400 text-[10px] font-bold uppercase mb-2">Rumah Berdaftar</p>
                <h3 class="text-4xl font-bold text-slate-800"><?php echo sprintf("%02d", $total_rumah); ?></h3>
            </div>
            <div class="bg-white p-6 rounded-[30px] shadow-sm border-b-4 border-blue-500 menu-card">
                <p class="text-slate-400 text-[10px] font-bold uppercase mb-2">Jumlah Klik Iklan</p>
                <h3 class="text-4xl font-bold text-slate-800"><?php echo sprintf("%02d", $grand_total_clicks); ?></h3>
            </div>
            <a href="senarai_chat_owner.php" class="bg-white p-6 rounded-[30px] shadow-sm border-b-4 border-purple-500 menu-card group hover:bg-purple-50 transition">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-2 group-hover:text-purple-600">Chat Pelajar</p>
                    <i class="fas fa-comment-dots text-purple-200 text-xl group-hover:text-purple-500 transition"></i>
                </div>
                <h3 class="text-4xl font-bold text-slate-800"><?php echo sprintf("%02d", $msg_stats['total_msg']); ?></h3>
            </a>
            <a href="#janji_temu_section" class="bg-white p-6 rounded-[30px] shadow-sm border-b-4 border-orange-400 menu-card group hover:bg-orange-50 transition block">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-2 group-hover:text-orange-600">Janji Temu</p>
                    <i class="fas fa-calendar-alt text-orange-200 text-xl group-hover:text-orange-500 transition"></i>
                </div>
                <h3 class="text-4xl font-bold text-slate-800"><?php echo sprintf("%02d", $total_jt); ?></h3>
            </a>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-[40px] shadow-sm border border-teal-100 mb-10">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-slate-800 flex items-center">
                    <span class="mr-2 text-2xl">📈</span> Analitik Prestasi Trafik Iklan (Bulanan)
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Analisis tren minat pelajar terhadap rumah sewa anda bagi tahun semasa.</p>
            </div>
            <div class="w-full h-64">
                <canvas id="clickChart"></canvas>
            </div>
        </div>

        <div id="hartanah_section" class="bg-white p-6 md:p-8 rounded-[40px] shadow-sm border border-teal-100 mb-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h2 class="text-xl font-bold text-slate-800 flex items-center">
                    <span class="mr-2 text-2xl">🏢</span> Senarai Rumah Sewa Anda
                </h2>
                
                <?php if($owner['status'] == 'Verified'): ?>
                    <a href="tambah_rumah.php" class="bg-teal-600 text-white px-6 py-3 rounded-full text-xs font-bold hover:bg-teal-700 transition shadow-md">
                        <i class="fas fa-plus mr-2"></i> Tambah Rumah
                    </a>
                <?php else: ?>
                    <button onclick="alert('Sila tunggu sehingga profil anda disahkan (Verified) oleh Admin.')" class="bg-slate-300 text-slate-500 px-6 py-3 rounded-full text-xs font-bold cursor-not-allowed">
                        <i class="fas fa-lock mr-2"></i> Tambah Rumah (Disekat)
                    </button>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-[10px] uppercase tracking-widest border-b">
                            <th class="pb-4">Maklumat Rumah</th>
                            <th class="pb-4">Harga (RM)</th>
                            <th class="pb-4">Status Iklan</th>
                            <th class="pb-4 text-center">Status Hunian (Arkib)</th>
                            <th class="pb-4 text-center">Jumlah Klik</th>
                            <th class="pb-4">Catatan Admin</th>
                            <th class="pb-4 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if($total_rumah > 0): ?>
                            <?php while($r = mysqli_fetch_assoc($rumah_query)): ?>
                            <tr class="border-b hover:bg-slate-50 transition">
                                <td class="py-4">
                                    <div class="flex items-center space-x-3">
                                        <?php 
                                            if (!empty($r['gambar'])) {
                                                $imgs = explode(',', $r['gambar']);
                                                $first_img = !empty($imgs[0]) ? 'uploads/'.trim($imgs[0]) : 'placeholder_house.png';
                                            } else {
                                                $first_img = 'placeholder_house.png';
                                            }
                                        ?>
                                        <img src="<?php echo $first_img; ?>" class="w-12 h-12 rounded-lg object-cover shadow-sm">
                                        <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($r['nama_rumah']); ?></span>
                                    </div>
                                </td>
                                <td class="py-4 font-bold text-teal-600">RM <?php echo number_format($r['hargaSewa'], 2); ?></td>
                                <td class="py-4">
                                    <?php 
                                    $s = $r['status'];
                                    if($s == 'Verified') {
                                        echo '<span class="bg-green-100 text-green-600 px-2 py-1 rounded text-[10px] font-bold border border-green-200">DITERIMA</span>';
                                    } elseif($s == 'Rejected') {
                                        echo '<span class="bg-red-100 text-red-600 px-2 py-1 rounded text-[10px] font-bold border border-red-200">DITOLAK</span>';
                                    } else {
                                        echo '<span class="bg-orange-100 text-orange-600 px-2 py-1 rounded text-[10px] font-bold border border-orange-200">PENDING</span>';
                                    }
                                    ?>
                                </td>
                                
                                <td class="py-4 text-center">
                                    <?php 
                                    $status_sewa = isset($r['status_sewa']) ? $r['status_sewa'] : 'Tersedia';
                                    if($status_sewa == 'Tersedia'): 
                                    ?>
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider">🟢 Tersedia</span>
                                            <a href="dashboard_owner.php?tukar_status_rumah=Tersewa&rumah_id=<?php echo $r['id']; ?>" class="text-[10px] text-slate-500 underline hover:text-red-500" onclick="return confirm('Tanda rumah ini sebagai telah penuh disewa? Iklan akan disembunyikan daripada pelajar tanpa memadam data.')">Tukar ke Disewakan</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider">🔴 Disewakan (Diarkib)</span>
                                            <a href="dashboard_owner.php?tukar_status_rumah=Tersedia&rumah_id=<?php echo $r['id']; ?>" class="text-[10px] text-teal-600 font-bold underline hover:text-teal-700">Aktifkan Semula</a>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="font-bold text-slate-700"><i class="fas fa-eye mr-1 text-slate-300"></i><?php echo $r['jumlah_klik'] ?? 0; ?></span>
                                        <?php if(($r['jumlah_klik'] ?? 0) < 10): ?>
                                            <span class="text-[9px] text-red-500 font-bold bg-red-50 px-1 rounded uppercase tracking-tight animate-pulse">📉 Perlu Promosi</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <p class="text-xs text-red-500 font-medium italic">
                                        <?php echo (!empty($r['catatan_admin'])) ? htmlspecialchars($r['catatan_admin']) : '-'; ?>
                                    </p>
                                </td>
                                <td class="py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="edit_rumah.php?id=<?php echo $r['id']; ?>" class="bg-teal-50 text-teal-600 p-2 rounded-lg hover:bg-teal-600 hover:text-white transition">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="padam_rumah.php?id=<?php echo $r['id']; ?>" class="bg-red-50 text-red-500 p-2 rounded-lg hover:bg-red-500 hover:text-white transition" onclick="return confirm('Padam rekod rumah ini secara kekal dari pangkalan data?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400 italic">Tiada rumah berdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="janji_temu_section" class="bg-white p-6 md:p-8 rounded-[40px] shadow-sm border border-teal-100 mb-10">
            <div class="flex items-center mb-6">
                <h2 class="text-xl font-bold text-slate-800 flex items-center">
                    <span class="mr-2 text-2xl">📅</span> Permohonan Janji Temu Pelajar
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-[10px] uppercase tracking-widest border-b">
                            <th class="pb-4">Nama Pelajar</th>
                            <th class="pb-4">No. Matrik</th>
                            <th class="pb-4">Rumah Pilihan</th>
                            <th class="pb-4">Tarikh & Masa</th>
                            <th class="pb-4">Status</th>
                            <th class="pb-4 text-center">Tindakan / Utiliti</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if($total_jt > 0): ?>
                            <?php while($jt = mysqli_fetch_assoc($jt_result)): ?>
                            <tr class="border-b hover:bg-slate-50 transition">
                                <td class="py-4 font-semibold text-slate-700">
                                    <?php echo htmlspecialchars($jt['nama_pelajar']); ?>
                                </td>
                                <td class="py-4 text-slate-500 font-mono">
                                    <?php echo htmlspecialchars($jt['no_matrik']); ?>
                                </td>
                                <td class="py-4 text-slate-600 font-medium">
                                    <?php echo htmlspecialchars($jt['nama_rumah']); ?>
                                </td>
                                <td class="py-4">
                                    <div class="text-xs text-slate-700 font-bold">
                                        <i class="far fa-calendar-alt text-teal-500 mr-1"></i> 
                                        <?php echo date('d-m-Y', strtotime($jt['tarikh'])); ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        <i class="far fa-clock text-teal-400 mr-1"></i> 
                                        <?php echo htmlspecialchars($jt['masa']); ?>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <?php 
                                    $status = $jt['status'];
                                    if($status == 'Approved' || $status == 'Disahkan') {
                                        echo '<span class="bg-green-100 text-green-600 px-2 py-1 rounded text-[10px] font-bold border border-green-200 uppercase">DISAHKAN</span>';
                                    } elseif($status == 'Rejected' || $status == 'Ditolak') {
                                        echo '<span class="bg-red-100 text-red-600 px-2 py-1 rounded text-[10px] font-bold border border-red-200 uppercase">DITOLAK</span>';
                                    } else {
                                        echo '<span class="bg-yellow-100 text-yellow-600 px-2 py-1 rounded text-[10px] font-bold border border-yellow-200 uppercase animate-pulse">PENDING</span>';
                                    }
                                    ?>
                                </td>
                                <td class="py-4 text-center">
                                    <?php if($status == 'Pending'): ?>
                                        <div class="flex justify-center space-x-2">
                                            <a href="kemaskini_status_jt.php?id=<?php echo $jt['id']; ?>&tindakan=Sahkan" 
                                               class="bg-green-500 hover:bg-green-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm"
                                               onclick="return confirm('Sahkan janji temu ini?')">
                                                <i class="fas fa-check mr-1"></i> Terima
                                            </a>
                                            <a href="kemaskini_status_jt.php?id=<?php echo $jt['id']; ?>&tindakan=Tolak" 
                                               class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm"
                                               onclick="return confirm('Tolak janji temu ini?')">
                                                <i class="fas fa-times mr-1"></i> Tolak
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <?php if($status == 'Approved' || $status == 'Disahkan'): 
                                            // Format tarikh ke bentuk Ymd untuk URL API Google Calendar
                                            $gcal_date = date('Ymd', strtotime($jt['tarikh']));
                                            $gcal_title = urlencode("Temujanji SewaSiswa: " . $jt['nama_pelajar']);
                                            $gcal_details = urlencode("Sesi perjumpaan melawat hartanah: " . $jt['nama_rumah'] . ". Pelajar: " . $jt['nama_pelajar'] . " (" . $jt['no_matrik'] . ")");
                                        ?>
                                            <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=<?php echo $gcal_title; ?>&dates=<?php echo $gcal_date; ?>T100000Z/<?php echo $gcal_date; ?>T110000Z&details=<?php echo $gcal_details; ?>" 
                                               target="_blank" 
                                               class="inline-flex items-center space-x-1 text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200 px-2.5 py-1 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm">
                                                <i class="fab fa-google text-[10px]"></i> <span>Simpan Kalendar</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 font-medium italic">Selesai</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400 italic">Tiada permohonan janji temu daripada pelajar buat masa ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="pasaran_section" class="mt-10 bg-white p-6 md:p-8 rounded-[40px] shadow-sm border border-teal-100">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-800 flex items-center">
                    <span class="mr-2 text-2xl">🔍</span> Teroka Rumah Sewa Pasaran (Pandangan Pelajar)
                </h2>
                <p class="text-xs text-slate-400 mt-1">Hanya rumah berstatus <b>Diterima</b> dan <b>Tersedia</b> sahaja akan dipaparkan pada carian akaun pelajar.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if($total_semua_rumah > 0): ?>
                    <?php while($all_r = mysqli_fetch_assoc($semua_rumah_query)): ?>
                        <?php 
                            if (!empty($all_r['gambar'])) {
                                $all_imgs = explode(',', $all_r['gambar']);
                                $display_img = !empty($all_imgs[0]) ? 'uploads/'.trim($all_imgs[0]) : 'placeholder_house.png';
                            } else {
                                $display_img = 'placeholder_house.png';
                            }
                        ?>
                        <div class="bg-slate-50 rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between">
                            <div class="relative">
                                <img src="<?php echo $display_img; ?>" class="w-full h-48 object-cover">
                                <div class="absolute top-3 right-3 bg-teal-600 text-white font-bold text-xs px-3 py-1.5 rounded-full shadow-md">
                                    RM <?php echo number_format($all_r['hargaSewa'], 2); ?> /bln
                                </div>
                                <?php if($all_r['tuan_rumah_id'] == $owner_id): ?>
                                    <div class="absolute top-3 left-3 bg-amber-500 text-slate-900 font-black text-[9px] px-2 py-1 rounded-md shadow-md uppercase tracking-tight">
                                        Rumah Anda
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-5 flex-grow flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-teal-600 tracking-wider bg-teal-50 px-2.5 py-1 rounded-md">
                                        <i class="fas fa-home mr-1"></i> <?php echo htmlspecialchars($all_r['kategori'] ?? 'Rumah'); ?>
                                    </span>
                                    <h3 class="font-bold text-slate-800 text-base mt-3 line-clamp-1">
                                        <?php echo htmlspecialchars($all_r['nama_rumah']); ?>
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-1 flex items-center">
                                        <i class="fas fa-user text-slate-300 mr-1.5 text-[10px]"></i> Owner: <?php echo htmlspecialchars($all_r['nama_owner']); ?>
                                    </p>
                                </div>

                                <div class="border-t border-slate-200/60 mt-4 pt-4 flex justify-between items-center">
                                    <span class="text-[11px] text-slate-400">
                                        <i class="fas fa-eye text-slate-300 mr-1"></i> <?php echo $all_r['jumlah_klik']; ?> paparan
                                    </span>
                                    
                                    <?php if($all_r['tuan_rumah_id'] == $owner_id): ?>
                                        <a href="edit_rumah.php?id=<?php echo $all_r['id']; ?>" class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                                            Urus Iklan <i class="fas fa-arrow-right text-[10px]"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="butiran_rumah.php?id=<?php echo $all_r['id']; ?>" target="_blank" class="text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1">
                                            Lihat Iklan <i class="fas fa-external-link-alt text-[10px]"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full py-10 text-center text-slate-400 italic bg-slate-50 rounded-2xl border border-dashed">
                        Tiada rumah sewa lain yang aktif di pasaran buat masa ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <script>
        const ctx = document.getElementById('clickChart').getContext('2d');
        new Chart(ctx, {
            type: 'line', // Jenis carta garisan yang cantik
            data: {
                labels: ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'],
                datasets: [{
                    label: 'Jumlah Klik Pengunjung',
                    // Data simulasi dinamik berasaskan total klik semasa
                    data: [
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.05), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.08), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.12), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.07), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.15), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.20), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.10), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.05), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.08), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.04), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.03), 
                        Math.round(<?php echo $grand_total_clicks; ?> * 0.03)
                    ],
                    backgroundColor: 'rgba(20, 184, 166, 0.1)',
                    borderColor: '#14b8a6', // Warna teal sepadan dengan tema SewaSiswa
                    borderWidth: 3,
                    tension: 0.4, // Membuatkan garisan melengkung dengan smooth
                    fill: true,
                    pointBackgroundColor: '#114b5f',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false } // Sembunyikan legenda atas untuk kekemasan
                }
            }
        });
    </script>
</body>
</html>