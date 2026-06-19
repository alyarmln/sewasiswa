<?php
session_start();
if (!isset($_SESSION['admin_id'])) { 
    header("Location: login_admin.php"); 
    exit(); 
}

$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ==========================================
// FUNGSI SIMULASI AUTOMATIK NOTIFIKASI E-MEL
// ==========================================
function hantarNotifikasiEmel($emelOwner, $namaOwner, $statusBaharu, $jenisSubjek, $namaSubjek, $alasan = '') {
    // Sediakan subjek dan kandungan e-mel
    $subjek = "Status Permohonan SewaSiswa: " . strtoupper($statusBaharu);
    
    if ($statusBaharu == 'Verified') {
        $mesej = "Salam Sejahtera $namaOwner,\n\n" .
                 "Tahniah! Permohonan pendaftaran bagi $jenisSubjek ($namaSubjek) anda telah " .
                 "DILULUSKAN dan DISAHKAN oleh pihak Admin SewaSiswa.\n" .
                 "Iklan anda kini telah aktif dan boleh dilihat oleh para pelajar di platform utama.\n\n" .
                 "Terima kasih kerana memilih SewaSiswa!\n" .
                 "🤖 Sila abaikan e-mel automatik ini.";
    } else {
        $mesej = "Salam Sejahtera $namaOwner,\n\n" .
                 "Dukacita dimaklumkan bahawa permohonan pendaftaran bagi $jenisSubjek ($namaSubjek) anda telah " .
                 "DITOLAK oleh pihak Admin SewaSiswa.\n\n" .
                 "SEBAB PENOLAKAN:\n\" $alasan \"\n\n" .
                 "Sila log masuk ke akaun anda semula untuk mengemas kini maklumat atau dokumen yang diperlukan.\n\n" .
                 "🤖 Sila abaikan e-mel automatik ini.";
    }

    // --- INTEGRASI PHPMAILER (Sedia untuk digunakan) ---
    /*
    require 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Tukar mengikut SMTP provider anda
    $mail->SMTPAuth = true;
    $mail->Username = 'admin.sewasiswa@gmail.com'; 
    $mail->Password = 'kata-laluan-aplikasi'; 
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('admin.sewasiswa@gmail.com', 'Admin SewaSiswa');
    $mail->addAddress($emelOwner);
    $mail->Subject = $subjek;
    $mail->Body = $mesej;
    $mail->send();
    */
    
    // Log sementara pada sistem (Boleh dipadam jika PHPMailer sudah aktif)
    error_log("E-mel Notifikasi dihantar ke $emelOwner | Subjek: $subjek");
}


// ==========================================
// KIRA DATA STATISTIK UNTUK KAD DASHBOARD
// ==========================================
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM rumah");
$jumlah_semua_rumah = mysqli_fetch_assoc($q_total)['total'];

$q_verified = mysqli_query($conn, "SELECT COUNT(*) as total FROM rumah WHERE status = 'Verified'");
$jumlah_verified = mysqli_fetch_assoc($q_verified)['total'];

$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM rumah WHERE status = 'Pending'");
$jumlah_pending = mysqli_fetch_assoc($q_pending)['total'];


// ==========================================
// TARIK DATA DYNAMIC UNTUK CHART.JS
// ==========================================
// 1. Data Kategori Rumah (Pie Chart)
$pie_labels = [];
$pie_counts = [];
$q_pie = mysqli_query($conn, "SELECT kategori, COUNT(*) as jumlah FROM rumah GROUP BY kategori");
while($r = mysqli_fetch_assoc($q_pie)) {
    $pie_labels[] = $r['kategori'];
    $pie_counts[] = $r['jumlah'];
}

// 2. Data Trend Pendaftaran Rumah Bulanan Tahun 2026 (Line Chart)
$bulanan_counts = array_fill(1, 12, 0); // Sediakan array penuh untuk 12 bulan (Jan-Dis) dengan nilai awal 0
$q_line = mysqli_query($conn, "SELECT MONTH(tarikh_daftar) as bulan, COUNT(*) as jumlah FROM rumah WHERE YEAR(tarikh_daftar) = 2026 GROUP BY MONTH(tarikh_daftar)");
while($r = mysqli_fetch_assoc($q_line)) {
    $bulanan_counts[(int)$r['bulan']] = (int)$r['jumlah'];
}


// ==========================================
// PROSES PENGESAHAN (VERIFY/REJECT) + EMEL
// ==========================================
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['type'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $type = $_GET['type']; 
    $action = $_GET['action'];

    // Ambil maklumat e-mel dan nama pemilik terlebih dahulu untuk proses hantaran e-mel
    if ($type == 'owner') {
        $q_owner = mysqli_query($conn, "SELECT nama, emel FROM tuan_rumah WHERE id = '$id'");
        $d_owner = mysqli_fetch_assoc($q_owner);
        $emelTarget = $d_owner['emel'];
        $namaTarget = $d_owner['nama'];
        $subjekNama = $d_owner['nama'];
        $jenisSubjek = "Profil Tuan Rumah";
    } else {
        $q_house = mysqli_query($conn, "SELECT rumah.nama_rumah, tuan_rumah.nama, tuan_rumah.emel FROM rumah JOIN tuan_rumah ON rumah.tuan_rumah_id = tuan_rumah.id WHERE rumah.id = '$id'");
        $d_house = mysqli_fetch_assoc($q_house);
        $emelTarget = $d_house['emel'];
        $namaTarget = $d_house['nama'];
        $subjekNama = $d_house['nama_rumah'];
        $jenisSubjek = "Iklan Rumah Sewa";
    }

    if ($action == 'verify') {
        $table_name = ($type == 'owner') ? 'tuan_rumah' : 'rumah';
        mysqli_query($conn, "UPDATE $table_name SET status = 'Verified' WHERE id = '$id'");
        
        // Pemicu E-mel Automatik bagi status Lulus
        hantarNotifikasiEmel($emelTarget, $namaTarget, 'Verified', $jenisSubjek, $subjekNama);
        
    } elseif ($action == 'reject') {
        $reason = isset($_GET['reason']) ? mysqli_real_escape_string($conn, $_GET['reason']) : 'Dokumen tidak lengkap/tidak jelas.';
        
        if ($type == 'owner') {
            mysqli_query($conn, "UPDATE tuan_rumah SET status = 'Rejected', alasan_tolak = '$reason' WHERE id = '$id'");
        } else {
            mysqli_query($conn, "UPDATE rumah SET status = 'Rejected', catatan_admin = '$reason' WHERE id = '$id'");
        }
        
        // Pemicu E-mel Automatik bagi status Tolak berserta Alasan
        hantarNotifikasiEmel($emelTarget, $namaTarget, 'Rejected', $jenisSubjek, $subjekNama, $reason);
    }
    
    header("Location: dashboard_admin.php?view=$type&msg=success");
    exit();
}

// Logik paparan senarai mengikut tab (View)
$view = isset($_GET['view']) ? $_GET['view'] : 'rumah';

if ($view == 'owner') {
    $list_data = mysqli_query($conn, "SELECT * FROM tuan_rumah WHERE status = 'Pending' ORDER BY id DESC");
} else {
    $list_data = mysqli_query($conn, "SELECT rumah.*, tuan_rumah.nama as owner_name FROM rumah JOIN tuan_rumah ON rumah.tuan_rumah_id = tuan_rumah.id WHERE rumah.status = 'Pending' ORDER BY rumah.id DESC");
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .modal-active { overflow: hidden; }
        .glass-nav { background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen font-sans text-slate-900">

    <nav class="glass-nav text-white p-5 shadow-2xl sticky top-0 z-[60] border-b border-white/10">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-teal-500 p-2 rounded-lg">
                    <i class="fas fa-user-shield text-xl"></i>
                </div>
                <h1 class="font-black tracking-tighter text-xl italic uppercase">Sewa<span class="text-teal-400">Siswa</span> Admin</h1>
            </div>
            <a href="logout.php" class="text-[10px] font-black bg-rose-500 hover:bg-rose-600 px-6 py-2.5 rounded-full uppercase transition shadow-lg flex items-center gap-2">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-6">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-8">
            <div>
                <span class="text-teal-600 font-black text-xs uppercase tracking-[0.3em]">Management Console</span>
                <h2 class="text-4xl font-black text-slate-800 uppercase tracking-tight mt-1">Peti <span class="text-teal-600">Verifikasi</span></h2>
                <p class="text-slate-400 text-sm mt-2 max-w-md italic">Sila semak MyKad, Bil Utiliti, dan hantar notifikasi emel automatik terus kepada pemilik.</p>
            </div>
            
            <div class="flex bg-slate-200/50 p-1.5 rounded-[2rem] shadow-inner border border-slate-200">
                <a href="?view=rumah" class="px-10 py-3 rounded-full text-xs font-black uppercase transition-all <?php echo ($view == 'rumah') ? 'bg-slate-900 text-white shadow-xl scale-105' : 'text-slate-500 hover:text-slate-700'; ?>">
                    <i class="fas fa-home mr-2"></i> Rumah
                </a>
                <a href="?view=owner" class="px-10 py-3 rounded-full text-xs font-black uppercase transition-all <?php echo ($view == 'owner') ? 'bg-slate-900 text-white shadow-xl scale-105' : 'text-slate-500 hover:text-slate-700'; ?>">
                    <i class="fas fa-user-tie mr-2"></i> Tuan Rumah
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[30px] shadow-md border border-slate-100 border-b-4 border-slate-700 flex items-center justify-between transition-all hover:shadow-lg">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Keseluruhan Rumah</p>
                    <h3 class="text-3xl font-black text-slate-800"><?php echo sprintf("%02d", $jumlah_semua_rumah); ?></h3>
                </div>
                <div class="w-12 h-12 bg-slate-100 text-slate-700 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-building"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-[30px] shadow-md border border-slate-100 border-b-4 border-emerald-500 flex items-center justify-between transition-all hover:shadow-lg">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Iklan Rumah Aktif</p>
                    <h3 class="text-3xl font-black text-emerald-600"><?php echo sprintf("%02d", $jumlah_verified); ?></h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-[30px] shadow-md border border-slate-100 border-b-4 border-amber-500 flex items-center justify-between transition-all hover:shadow-lg">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Menunggu Kelulusan</p>
                    <h3 class="text-3xl font-black text-amber-600"><?php echo sprintf("%02d", $jumlah_pending); ?></h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl shadow-inner <?php echo ($jumlah_pending > 0) ? 'animate-bounce' : ''; ?>">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-6 rounded-[2.5rem] shadow-xl border border-slate-100 lg:col-span-2">
                <h4 class="text-xs font-black uppercase text-slate-400 tracking-wider mb-4"><i class="fas fa-chart-line mr-2 text-teal-500"></i> Trend Pendaftaran Rumah Bulanan (2026)</h4>
                <div class="h-64 relative">
                    <canvas id="lineChartTrend"></canvas>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2.5rem] shadow-xl border border-slate-100">
                <h4 class="text-xs font-black uppercase text-slate-400 tracking-wider mb-4"><i class="fas fa-chart-pie mr-2 text-teal-500"></i> Pecahan Kategori Kediaman</h4>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="pieChartKategori"></canvas>
                </div>
            </div>
        </div>


        <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden border border-slate-100">
            <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-black uppercase text-xs tracking-widest text-slate-400">Senarai Permohonan <span class="text-teal-500">Tertangguh</span></h3>
                <span class="bg-teal-100 text-teal-600 px-4 py-1 rounded-full text-[10px] font-black uppercase"><?php echo mysqli_num_rows($list_data); ?> Tugasan</span>
            </div>
            
            <table class="w-full text-left">
                <thead class="text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-50">
                    <tr>
                        <th class="p-8">Profil & Maklumat</th>
                        <th class="p-8 text-center">Dokumen Sokongan</th>
                        <th class="p-8 text-right">Tindakan Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(mysqli_num_rows($list_data) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($list_data)): ?>
                        <tr class="hover:bg-slate-50/80 transition-all group">
                            <td class="p-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center text-teal-400 shadow-lg group-hover:rotate-3 transition">
                                        <i class="fas <?php echo ($view == 'rumah') ? 'fa-house-user' : 'fa-user-check'; ?> text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-800 text-lg leading-none mb-1"><?php echo htmlspecialchars($view == 'rumah' ? $row['nama_rumah'] : $row['nama']); ?></p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                                            <?php echo ($view == 'rumah') ? "<span class='text-teal-600'>Pemilik:</span> ".$row['owner_name'] : $row['emel']; ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-8 text-center">
                                <button onclick="bukaModal('<?php echo $row['id']; ?>', '<?php echo $view; ?>')" class="inline-flex items-center gap-2 bg-white border-2 border-slate-100 px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:border-slate-900 hover:shadow-lg transition-all mx-auto">
                                    <i class="fas fa-search-plus text-teal-500"></i> Semak Semua Dokumen
                                </button>
                            </td>
                            <td class="p-8 text-right space-x-2">
                                <a href="?action=verify&type=<?php echo $view; ?>&id=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Luluskan dan hantar notifikasi emel rasmi kejayaan kepada pemilik?')"
                                   class="bg-emerald-500 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-emerald-600 hover:shadow-xl transition shadow-md">Lulus</a>
                                
                                <button onclick="prosesTolak('<?php echo $row['id']; ?>', '<?php echo $view; ?>')" 
                                        class="bg-rose-50 text-rose-500 px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-rose-500 hover:text-white transition shadow-sm border border-rose-100">Tolak</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="p-32 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <i class="fas fa-clipboard-check text-6xl mb-4"></i>
                                    <p class="font-black uppercase text-sm tracking-[0.4em]">Selesai. Tiada Tunggakan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modalSemak" class="opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100] transition-all duration-300">
        <div class="modal-overlay absolute w-full h-full bg-slate-900/90 backdrop-blur-md"></div>
        <div class="bg-white w-full max-w-4xl mx-auto rounded-[3.5rem] shadow-2xl z-50 overflow-y-auto max-h-[90vh] p-12 relative scale-95 transition-transform duration-300" id="modalContent">
            <div class="flex justify-between items-center mb-8 border-b border-slate-100 pb-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase italic">Proses <span class="text-teal-600">Verifikasi</span></h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Sila pastikan dokumen adalah sah</p>
                </div>
                <button onclick="tutupModal()" class="w-12 h-12 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 hover:bg-rose-500 hover:text-white transition-all text-2xl">&times;</button>
            </div>
            <div id="isiSemakan"></div>
        </div>
    </div>

    <script>
        // -----------------------------------------------------------
        // CONFIG 1: LINE CHART (TREND PENDAFTARAN RUMAH)
        // -----------------------------------------------------------
        const ctxLine = document.getElementById('lineChartTrend').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogo', 'Sep', 'Okt', 'Nov', 'Dis'],
                datasets: [{
                    label: 'Rumah Didatar',
                    data: [<?php echo implode(',', $bulanan_counts); ?>],
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13, 148, 136, 0.1)',
                    borderWidth: 4,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#0f172a',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // -----------------------------------------------------------
        // CONFIG 2: PIE CHART (KATEGORI RUMAH)
        // -----------------------------------------------------------
        const ctxPie = document.getElementById('pieChartKategori').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($pie_labels); ?>,
                datasets: [{
                    data: [<?php echo implode(',', $pie_counts); ?>],
                    backgroundColor: ['#0d9488', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } } }
                }
            }
        });

        // KONTROL PROSES PENOLAKAN
        function prosesTolak(id, type) {
            let ulasan = prompt("NOTIFIKASI PENOLAKAN VIA EMEL:\nNyatakan sebab penolakan iklan/profil ini:");
            
            if (ulasan === null) return; 
            
            if (ulasan.trim() !== "") {
                window.location.href = `dashboard_admin.php?action=reject&type=${type}&id=${id}&reason=${encodeURIComponent(ulasan)}`;
            } else {
                alert("Sebab penolakan wajib diisi untuk dihantar ke e-mel pemilik!");
            }
        }

        function bukaModal(id, type) {
            const modal = document.getElementById('modalSemak');
            const content = document.getElementById('modalContent');
            document.body.classList.add('modal-active');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
            
            document.getElementById('isiSemakan').innerHTML = `<p class="text-center p-10 animate-pulse font-bold text-slate-400">Memuatkan dokumen...</p>`;
            
            fetch(`semak_data.php?id=${id}&type=${type}`)
                .then(res => res.text())
                .then(html => { document.getElementById('isiSemakan').innerHTML = html; });
        }

        function tutupModal() {
            const modal = document.getElementById('modalSemak');
            const content = document.getElementById('modalContent');
            document.body.classList.remove('modal-active');
            modal.classList.add('opacity-0', 'pointer-events-none');
            content.classList.add('scale-95');
        }
        document.querySelector('.modal-overlay').addEventListener('click', tutupModal);
    </script>
</body>
</html>