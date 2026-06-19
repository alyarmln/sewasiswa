<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - SewaSiswa</title>
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
                <a href="tentang_kami.php" class="text-teal-600 font-semibold">Tentang Kami</a>
                <a href="hubungi_kami.php" class="hover:text-teal-600 transition">Hubungi Kami</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-16 flex-grow">
        
        <div class="text-center mb-16">
            <span class="bg-teal-100 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest">Kenali Kami</span>
            <h2 class="text-4xl font-extrabold text-slate-800 mt-3">SEWASISWA</h2>
            <p class="text-slate-500 mt-4 max-w-2xl mx-auto text-sm leading-relaxed">SewaSiswa ditubuhkan khas untuk menyelesaikan kemelut pencarian rumah sewa yang dihadapi oleh pelajar universiti dengan menghubungkan mereka terus kepada tuan rumah dengan selamat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <img src="https://static.wixstatic.com/media/314c9c_e254cdd6d1ad45339360527aada3dad9~mv2.jpg/v1/fill/w_640,h_272,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto/314c9c_e254cdd6d1ad45339360527aada3dad9~mv2.jpg" alt="Pelajar Universiti" class="rounded-[40px] shadow-md border-4 border-white object-cover h-500 w-full">
            </div>
            <div class="space-y-6">
                <h3 class="text-2xl font-bold text-slate-800">Kenapa Memilih SewaSiswa?</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Kami faham bahawa sebagai seorang pelajar, kekangan masa, pengangkutan, dan bajet sering menjadi penghalang untuk mencari kediaman yang selesa dan berdekatan dengan kampus.</p>
                
                <div class="space-y-3">
                    <div class="flex items-start space-x-3">
                        <span class="bg-teal-500 text-white p-1 rounded-full text-xs mt-1"><i class="fas fa-check"></i></span>
                        <p class="text-sm text-slate-700 font-medium">Iklan rumah sewa yang telah ditapis dan disahkan oleh admin.</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="bg-teal-500 text-white p-1 rounded-full text-xs mt-1"><i class="fas fa-check"></i></span>
                        <p class="text-sm text-slate-700 font-medium">Sistem chatbot AI pintar sedia membantu carian 24/7.</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="bg-teal-500 text-white p-1 rounded-full text-xs mt-1"><i class="fas fa-check"></i></span>
                        <p class="text-sm text-slate-700 font-medium">Fungsi tempahan janji temu lawatan rumah yang sistematik.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-[30px] shadow-sm border border-teal-100">
                <div class="w-12 h-12 bg-teal-100 rounded-2xl flex items-center justify-center text-teal-600 text-xl mb-4">
                    <i class="fas fa-eye"></i>
                </div>
                <h4 class="text-lg font-bold text-slate-800 mb-2">Visi Kami</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Menjadi platform rujukan carian dan pengurusan rumah sewa pelajar nombor satu di Malaysia yang berintegrasi tinggi, telus, dan dipercayai.</p>
            </div>

            <div class="bg-white p-8 rounded-[30px] shadow-sm border border-teal-100">
                <div class="w-12 h-12 bg-teal-100 rounded-2xl flex items-center justify-center text-teal-600 text-xl mb-4">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h4 class="text-lg font-bold text-slate-800 mb-2">Misi Kami</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Menyediakan ekosistem digital yang selamat bagi memudahkan urusan sewa-menyewa antara mahasiswa dan pemilik hartanah tempatan.</p>
            </div>
        </div>
    </main>

<footer class="bg-slate-950 text-white/60 text-center py-6 text-xs mt-16">
        <p>&copy; <?php echo date('Y'); ?> Pejabat Pembangunan Pelajar SewaSiswa. Hak Cipta Terpelihara.</p>
    </footer>

    <?php include('chatbot_widget.php'); ?>

</body>
</html>