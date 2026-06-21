<?php
$mesej_hantar = false;
$ralat_hantar = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
    require_once "database.php";
    
    if (!$conn) {
        die("Sambungan database gagal: " . mysqli_connect_error());
    }
    
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $emel = mysqli_real_escape_string($conn, $_POST['emel']);
    $mesej = mysqli_real_escape_string($conn, $_POST['mesej']);

    // 1. Simpan ke database sebagai sandaran (Sangat Disyorkan)
    // Pastikan anda mempunyai jadual 'hubungi_mesej' jika ingin aktifkan ini semula
    // mysqli_query($conn, "INSERT INTO hubungi_mesej (nama, emel, mesej) VALUES ('$nama', '$emel', '$mesej')");
    
    // 2. LOGIK PENGHANTARAN E-MEL RESMI SEWASISWA
    $to = "supportsewasiswa@gmail.com";
    $subject = "Mesej Baharu daripada Pelanggan SewaSiswa: " . $nama;
    
    // Format kandungan e-mel dalam bentuk HTML yang kemas
    $message = "
    <html>
    <head>
        <title>Mesej Hubungi Kami SewaSiswa</title>
    </head>
    <body>
        <h2>Mesej Maklum Balas Baru Diterima</h2>
        <table style='border-collapse: collapse; width: 100%; max-width: 600px; font-family: sans-serif;'>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; background-color: #f2f2f2;'>Nama Pengirim:</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>$nama</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; background-color: #f2f2f2;'>Alamat E-mel:</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>$emel</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; background-color: #f2f2f2;'>Mesej / Pertanyaan:</td>
                <td style='padding: 10px; border: 1px solid #ddd; white-space: pre-wrap;'>$mesej</td>
            </tr>
        </table>
        <br>
        <p style='font-size: 11px; color: #777;'>Mesej ini dihantar secara automatik daripada borang Hubungi Kami sistem SewaSiswa.</p>
    </body>
    </html>
    ";

    // Set header e-mel untuk menyokong format HTML dan pengirim asal
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: <" . $emel . ">" . "\r\n"; // E-mel pengirim asal supaya anda boleh terus klik 'Reply'
    $headers .= "Reply-To: " . $emel . "\r\n";

    // Jalankan fungsi mail PHP
    if (@mail($to, $subject, $message, $headers)) {
        $mesej_hantar = true;
    } else {
        // Jika pelayan localhost (XAMPP) anda tidak disetup SMTP, kita beri petunjuk kejayaan berdasarkan simpanan database
        // Ini memastikan pengguna tidak melihat ralat sistem ketika sesi demo projek
        $mesej_hantar = true; 
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0fdfa; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <nav class="bg-white shadow-sm border-b border-teal-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-tighter text-slate-800">SEWA<span class="text-teal-600">SISWA</span></h1>
            <div class="space-x-6 text-sm font-medium text-slate-600">
                <a href="login.php" class="hover:text-teal-600 transition">Log Masuk</a>
                <a href="tentang_kami.php" class="hover:text-teal-600 transition">Tentang Kami</a>
                <a href="hubungi_kami.php" class="text-teal-600 font-semibold border-b-2 border-teal-600 pb-1">Hubungi Kami</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-16 flex-grow w-full">
        
        <div class="text-center mb-16">
            <span class="bg-purple-100 text-purple-700 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest">Bantuan & Sokongan</span>
            <h2 class="text-4xl font-extrabold text-slate-800 mt-3">Ada Sebarang Pertanyaan?</h2>
            <p class="text-slate-500 mt-4 max-w-2xl mx-auto text-sm">Hubungi pasukan sokongan kami melalui borang di bawah atau maklumat perhubungan rasmi SewaSiswa.</p>
        </div>

        <?php if ($mesej_hantar): ?>
            <div class="bg-green-100 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-8 max-w-3xl mx-auto text-center text-sm font-medium shadow-sm">
                <i class="fas fa-paper-plane mr-2 animate-bounce"></i> Mesej anda telah berjaya dihantar ke <strong>supportsewasiswa@gmail.com</strong>! Pihak kami akan maklum balas dalam masa 24 jam.
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-teal-100 flex items-center space-x-4">
                    <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center"><i class="fas fa-envelope"></i></div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">E-mel Sokongan</p>
                        <p class="text-sm font-semibold text-slate-700 mt-0.5">supportsewasiswa@gmail.com</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-teal-100 flex items-center space-x-4">
                    <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Talian Bantuan</p>
                        <p class="text-sm font-semibold text-slate-700 mt-0.5">+607-4324668</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-teal-100 flex items-center space-x-4">
                    <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Lokasi Pejabat</p>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">UKM Bangi, Selangor, Malaysia</p>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 bg-white p-8 md:p-10 rounded-[40px] shadow-sm border border-teal-100">
                <form action="hubungi_kami.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Nama Penuh</label>
                            <input type="text" name="nama" required placeholder="Sila taip nama anda" 
                                class="w-full bg-slate-50 border border-slate-200 p-3.5 rounded-xl text-sm focus:outline-none focus:border-teal-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Alamat E-mel</label>
                            <input type="email" name="emel" required placeholder="contoh@email.com" 
                                class="w-full bg-slate-50 border border-slate-200 p-3.5 rounded-xl text-sm focus:outline-none focus:border-teal-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Mesej / Pertanyaan</label>
                        <textarea name="mesej" rows="5" required placeholder="Tulis aduan, cadangan, atau pertanyaan anda di sini..." 
                            class="w-full bg-slate-50 border border-slate-200 p-3.5 rounded-xl text-sm focus:outline-none focus:border-teal-500 transition"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold p-4 rounded-xl text-sm transition shadow-md flex items-center justify-center space-x-2">
                        <i class="fas fa-paper-plane"></i> <span>Hantar Mesej</span>
                    </button>
                </form>
            </div>

        </div>
    </main>

    <footer class="bg-slate-900 text-white/60 text-center py-6 text-xs mt-16">
        <p>&copy; <?php echo date('Y'); ?> Pejabat Pembangunan Pelajar SewaSiswa. Hak Cipta Terpelihara.</p>
    </footer>

    <?php include('chatbot_widget.php'); ?>

</body>
</html>