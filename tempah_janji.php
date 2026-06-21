<?php
// 1. Sambungan ke Database
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) {
    die("Sambungan gagal: " . mysqli_error($conn));
}

// 2. Ambil ID rumah dari URL (Dihantar dari maklumatrumah.php)
$id_rumah = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

// Jika ID kosong, hantar balik ke utama
if (empty($id_rumah)) {
    header("Location: utama.php");
    exit();
}

// 3. Ambil Nama Rumah untuk paparan
$query_rumah = mysqli_query($conn, "SELECT nama_rumah FROM rumah WHERE id = '$id_rumah'");
$rumah_data = mysqli_fetch_assoc($query_rumah);
$nama_rumah = $rumah_data ? $rumah_data['nama_rumah'] : "Hartanah";

// Set tarikh default (Hari ini)
$today = date('Y-m-d');
$current_month_year = date('F Y');
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Pilih Tarikh & Masa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0fdfa; }
        .selected-slot { background-color: #0d9488 !important; color: white !important; border-color: #0d9488 !important; }
        .selected-date { background-color: #0d9488 !important; color: white !important; transform: scale(1.1); }
        .date-cell:hover:not(.selected-date) { background-color: #ccfbf1; }
    </style>
</head>
<body>

    <header class="bg-white p-4 shadow-sm flex justify-between items-center px-8 lg:px-20 border-b border-teal-100">
        <div class="flex items-center space-x-4">
            <img src="sewasiswa_logo.png" alt="SewaSiswa" class="h-10">
            <span class="text-xl font-extrabold text-teal-900 italic tracking-tighter uppercase">Sewa<span class="text-teal-600">Siswa</span></span>
        </div>
        <nav class="hidden md:flex space-x-8 font-bold text-teal-900 text-xs uppercase tracking-widest">
            <a href="utama.php" class="hover:text-teal-600 transition">Batal</a>
        </nav>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-10">
        <div class="mb-10">
            <h1 class="text-3xl font-black text-teal-900 uppercase tracking-tight">Pilih Tarikh & Masa</h1>
            <p class="text-teal-600 font-medium mt-1 uppercase text-xs tracking-widest">
                Melawat: <span class="text-slate-800"><?php echo htmlspecialchars($nama_rumah); ?></span>
            </p>
        </div>

        <form action="proses_janji_temu.php" method="POST" id="bookingForm">
            <input type="hidden" name="rumah_id" value="<?php echo $id_rumah; ?>"> 
            <input type="hidden" name="tarikh_pilihan" id="tarikh_pilihan" value="<?php echo $today; ?>">
            <input type="hidden" name="masa_pilihan" id="masa_pilihan" required>

            <div class="flex flex-col lg:grid lg:grid-cols-2 gap-10">
                
                <div class="bg-white p-8 rounded-[2rem] shadow-xl border border-teal-50">
                    <div class="flex justify-between items-center mb-8">
                        <span class="font-black text-teal-900 uppercase tracking-widest text-lg"><?php echo $current_month_year; ?></span>
                        <div class="flex space-x-2">
                            <button type="button" class="p-2 hover:bg-teal-50 rounded-full text-teal-900"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" class="p-2 hover:bg-teal-50 rounded-full text-teal-900"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-2 text-center text-[10px] font-black text-teal-400 mb-4 tracking-tighter">
                        <div>AHAD</div><div>ISN</div><div>SEL</div><div>RAB</div><div>KHA</div><div>JUM</div><div>SAB</div>
                    </div>

                    <div class="grid grid-cols-7 gap-2 text-center">
                        <?php 
                        // Mencari hari pertama dalam bulan
                        $first_day = date('w', strtotime(date('Y-m-01')));
                        for($x=0; $x<$first_day; $x++) echo "<div></div>";

                        for($i=1; $i<=31; $i++): 
                            $date_val = date('Y-m-') . sprintf("%02d", $i);
                            $active_class = ($i == date('d')) ? 'selected-date shadow-lg' : '';
                        ?>
                            <div onclick="setTarikh('<?php echo $date_val; ?>', this)" 
                                 class="date-cell p-3 cursor-pointer rounded-xl font-bold text-slate-700 transition-all <?php echo $active_class; ?>">
                                <?php echo $i; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2rem] shadow-xl border border-teal-50 flex flex-col">
                    <h2 id="displayDate" class="text-xl font-black text-teal-900 mb-6 uppercase tracking-tight">
                        <?php echo date('d F Y'); ?>
                    </h2>
                    
                    <div class="grid grid-cols-1 gap-3 flex-grow">
                        <?php 
                        $slots = [
                            "09:00 am - 10:00 am", 
                            "10:00 am - 11:00 am", 
                            "11:00 am - 12:00 pm", 
                            "02:00 pm - 03:00 pm", 
                            "03:00 pm - 04:00 pm", 
                            "04:00 pm - 05:00 pm"
                        ];
                        foreach($slots as $slot): ?>
                            <div onclick="setMasa('<?php echo $slot; ?>', this)" 
                                 class="slot-btn border-2 border-slate-50 bg-slate-50 p-4 rounded-2xl text-center font-bold text-slate-500 cursor-pointer hover:border-teal-900 hover:text-teal-900 transition flex items-center justify-center space-x-3">
                                <i class="far fa-clock"></i>
                                <span><?php echo $slot; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="w-full bg-rose-500 text-white font-bold py-5 rounded-2xl mt-8 uppercase tracking-widest shadow-lg shadow-rose-100 hover:bg-rose-600 transition-all transform hover:-translate-y-1">
                        Sahkan Tempahan Melawat
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        function setMasa(masa, element) {
            document.getElementById('masa_pilihan').value = masa;
            // Buang warna dari butang lain
            document.querySelectorAll('.slot-btn').forEach(btn => {
                btn.classList.remove('selected-slot');
                btn.classList.add('bg-slate-50', 'text-slate-500');
            });
            // Tambah warna pada butang pilihan
            element.classList.remove('bg-slate-50', 'text-slate-500');
            element.classList.add('selected-slot');
        }

        function setTarikh(tarikh, element) {
            document.getElementById('tarikh_pilihan').value = tarikh;
            
            // Format paparan tarikh
            const dateObj = new Date(tarikh);
            const options = { weekday: 'long', day: 'numeric', month: 'long' };
            document.getElementById('displayDate').innerText = dateObj.toLocaleDateString('ms-MY', options);

            // Buang class dari tarikh lain
            document.querySelectorAll('.date-cell').forEach(cell => cell.classList.remove('selected-date', 'shadow-lg'));
            // Tambah pada yang dipilih
            element.classList.add('selected-date', 'shadow-lg');
        }

        // Semak jika masa telah dipilih sebelum hantar form
        document.getElementById('bookingForm').onsubmit = function(e) {
            if (!document.getElementById('masa_pilihan').value) {
                alert('Sila pilih slot masa terlebih dahulu!');
                e.preventDefault();
            }
        };
    </script>
</body>
</html>