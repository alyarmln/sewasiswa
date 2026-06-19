<?php
session_start();
include('chatbot_widget.php');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenancy Agreement (Perjanjian Sewa) - SewaSiswa</title>
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
                <i class="fas fa-gavel text-rose-500 text-4xl mb-3"></i>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Standard Tenancy Agreement</h1>
                <p class="text-slate-400 text-xs mt-2 uppercase font-bold tracking-wider">Garis Panduan Dasar Kontrak Rumah Sewa Siswa</p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-800 flex items-start gap-3">
                <i class="fas fa-exclamation-triangle mt-0.5 text-base text-amber-600"></i>
                <p><strong>Nota Penting Pelajar:</strong> Ini merupakan struktur rujukan asas rukun perjanjian rumah sewa. Pastikan salinan kontrak fizikal anda ditandatangani bersama pemilik rumah/agen berdaftar sebelum kunci rumah diserahkan.</p>
            </div>

            <!-- Fasal 1 -->
            <section class="space-y-2">
                <h3 class="text-sm font-black uppercase text-slate-900 tracking-wider">Fasal 1: Pembayaran Sewa & Deposit</h3>
                <p class="text-slate-600 text-sm leading-relaxed pl-4">
                    Sewa bulanan wajib dibayar selewat-lewatnya pada tarikh yang ditetapkan bersama (contoh: sebelum 7 hari bulan setiap bulan). Deposit sekuriti (biasanya kadar 1 atau 2 bulan sewa) dan deposit utiliti tidak boleh digunakan sebagai sewa bulanan, dan akan dikembalikan dalam tempoh 14-30 hari selepas kontrak tamat jika tiada kerosakan pada struktur rumah.
                </p>
            </section>

            <!-- Fasal 2 -->
            <section class="space-y-2">
                <h3 class="text-sm font-black uppercase text-slate-900 tracking-wider">Fasal 2: Tanggungjawab Pemeliharaan Rumah</h3>
                <ul class="list-disc list-inside text-slate-600 text-sm leading-relaxed pl-4 space-y-1">
                    <li><strong>Penyewa (Pelajar):</strong> Bertanggungjawab menjaga kebersihan dalaman rumah, mentol lampu, dan kerosakan kecil disebabkan kecuaian sendiri.</li>
                    <li><strong>Tuan Rumah:</strong> Bertanggungjawab membaiki kerosakan struktur utama (seperti kebocoran bumbung, pendawaian elektrik utama, atau sistem paip air utama).</li>
                </ul>
            </section>

            <!-- Fasal 3 -->
            <section class="space-y-2">
                <h3 class="text-sm font-black uppercase text-slate-900 tracking-wider">Fasal 3: Larangan & Peraturan Kediaman</h3>
                <p class="text-slate-600 text-sm leading-relaxed pl-4">
                    Penyewa dilarang sama sekali menyewakan semula bilik atau rumah kepada pihak ketiga (sub-let) tanpa keizinan bertulis tuan rumah. Penyewa juga harus mematuhi undang-undang rukun tetangga dan tidak mengganggu ketenteraman jiran tetangga sekeliling dengan bunyi bising atau aktiviti tidak bermoral.
                </p>
            </section>

            <!-- Fasal 4 -->
            <section class="space-y-2">
                <h3 class="text-sm font-black uppercase text-slate-900 tracking-wider">Fasal 4: Penamatan Kontrak</h3>
                <p class="text-slate-600 text-sm leading-relaxed pl-4">
                    Sekiranya mana-mana pihak ingin menamatkan kontrak lebih awal daripada tempoh matang perjanjian, notis bertulis sekurang-kurangnya 1 atau 2 bulan (mengikut syarat asal) perlulah diserahkan kepada pihak satu lagi. Kegagalan memberi notis boleh menyebabkan deposit sekuriti hangus.
                </p>
            </section>

        </div>
    </main>

    <footer class="py-10 text-center text-slate-400 text-[10px] font-bold uppercase tracking-widest">
        © 2026 SEWASISWA APPS • TENANCY AGREEMENT POLICY
    </footer>

</body>
</html>