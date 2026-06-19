<?php
session_start();
include('chatbot_widget.php');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terma & Syarat - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="text-slate-800">

    <!-- Navigasi -->
    <nav class="bg-white border-b sticky top-0 z-50 py-4 px-6 lg:px-20 flex justify-between items-center">
        <a href="utama.php" class="flex items-center space-x-2">
            <span class="text-xl font-extrabold text-teal-900 italic tracking-tighter">SEWA<span class="text-teal-600">SISWA</span></span>
        </a>
        
    </nav>

    <!-- Kandungan -->
    <main class="max-w-4xl mx-auto px-6 py-12">
        <div class="bg-white p-8 md:p-12 rounded-[2.5rem] border border-slate-100 shadow-xl space-y-8">
            
            <div class="text-center max-w-xl mx-auto border-b border-slate-100 pb-6">
                <i class="fas fa-file-contract text-teal-600 text-4xl mb-3"></i>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Terma & Syarat Penggunaan</h1>
                <p class="text-slate-400 text-xs mt-2 uppercase font-bold tracking-wider">Kemaskini Terakhir: Jun 2026</p>
            </div>

            <!-- Seksyen 1 -->
            <section class="space-y-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-teal-500 rounded-full"></span> 1. Pengenalan Aplikasi
                </h3>
                <p class="text-slate-600 text-sm leading-relaxed pl-4">
                    Selamat datang ke SewaSiswa. Dengan mengakses atau menggunakan aplikasi platform ini, anda bersetuju untuk terikat dengan terma, syarat, dan dasar yang dinyatakan di sini. Platform ini berfungsi sebagai perantara pencarian rumah sewa khusus untuk pelajar universiti (UKM).
                </p>
            </section>

            <!-- Seksyen 2 -->
            <section class="space-y-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-teal-500 rounded-full"></span> 2. Tanggungjawab Pengguna (Pelajar)
                </h3>
                <ul class="list-disc list-inside text-slate-600 text-sm leading-relaxed pl-4 space-y-1.5">
                    <li>Pengguna mestilah seorang pelajar aktif di institut pengajian tinggi berdekatan.</li>
                    <li>Segala maklumat profil, tempahan lawatan rumah (viewing), dan ulasan yang ditinggalkan mestilah benar dan tidak mengandungi unsur fitnah atau maklumat palsu.</li>
                    <li>Pelajar bertanggungjawab menjaga keselamatan akaun dan kata laluan masing-masing.</li>
                </ul>
            </section>

            <!-- Seksyen 3 -->
            <section class="space-y-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-teal-500 rounded-full"></span> 3. Tanggungjawab Penyedia (Tuan Rumah)
                </h3>
                <ul class="list-disc list-inside text-slate-600 text-sm leading-relaxed pl-4 space-y-1.5">
                    <li>Tuan rumah wajib memastikan maklumat iklan (harga sewa, gambar, fasiliti, dan koordinat GPS lokasi) adalah tepat serta sepadan dengan keadaan fizikal rumah asli.</li>
                    <li>Tuan rumah dilarang memanipulasi maklumat klik iklan atau memaparkan kandungan yang melanggar undang-undang hak milik hartanah Malaysia.</li>
                </ul>
            </section>

            <!-- Seksyen 4 -->
            <section class="space-y-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-teal-500 rounded-full"></span> 4. Penafian Liabiliti (Disclaimer)
                </h3>
                <p class="text-slate-600 text-sm leading-relaxed pl-4">
                    SewaSiswa tidak bertanggungjawab terhadap sebarang pertikaian kewangan, kerosakan fasiliti rumah, atau salah faham kontrak kontrak sewa antara pelajar dan tuan rumah secara luar talian (offline). Pengguna dinasihatkan membaca dan menandatangani 'Tenancy Agreement' rasmi sebelum melakukan sebarang transaksi deposit wang.
                </p>
            </section>

        </div>
    </main>

    <footer class="py-10 text-center text-slate-400 text-[10px] font-bold uppercase tracking-widest">
        © 2026 SEWASISWA APPS • TERMS & CONDITIONS
    </footer>

</body>
</html>