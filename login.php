<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Portal Log Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: linear-gradient(rgba(255, 255, 255, 0.7), rgba(255, 255, 255, 0.7)), url('https://i.pinimg.com/736x/73/17/dd/7317dd8fec0838d73cf5fbba56b46bf2.jpg'); /* Ganti dengan gambar latar rumah anda */
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        .card-gradient {
            background: linear-gradient(to bottom, #114b5f 0%, #2d93ad 100%);
        }
    </style>
</head>
<body class="flex flex-col">

    <header class="p-6 flex justify-between items-center bg-white/80 backdrop-blur-sm">
        <div class="flex items-center space-x-4">
            <img src="ukm_logo.png" alt="UKM Logo" class="h-12">
            <img src="sewasiswa_logo.png" alt="SewaSiswa Logo" class="h-12">
        </div>
        
        <nav class="hidden md:flex items-center space-x-8 text-cyan-900 font-semibold uppercase text-sm">
            <a href="tentang_kami.php" class="hover:text-cyan-600 transition">Tentang Kami</a>
            <a href="hubungi_kami.php" class="hover:text-cyan-600 transition">Hubungi Kami</a>
            <a href="#" class="bg-cyan-600 text-white px-6 py-2 rounded-full hover:bg-cyan-700 transition">
                Selamat Datang Ke SewaSiswa >
            </a>
        </nav>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center px-4 py-12">
        <h1 class="text-4xl md:text-5xl font-bold text-cyan-900 mb-12">
            Siapakah <span class="text-gray-400">Anda?</span>
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl w-full">
            
            <?php
            // Data untuk kotak-kotak (Simulasi dinamik)
            $peranan = [
                [
                    "title" => "Pelajar",
                    "desc" => "LOG MASUK KE AKAUN PELAJAR ANDA UNTUK MENCARI RUMAH SEWA IDAMAN, BERHUBUNG DENGAN PEMILIK RUMAH, DAN MENGURUSKAN DOKUMEN PENYEWAAN ANDA DENGAN MUDAH DAN SELAMAT.",
                    "link" => "loginmasuk.php"
                ],
                [
                    "title" => "Tuan Rumah",
                    "desc" => "SELAMAT DATANG KEMBALI. LOG MASUK UNTUK MENGURUSKAN SENARAI HARTANAH ANDA, MENYEMAK PERMOHONAN PELAJAR, DAN MEMANTAU STATUS PEMBAYARAN SEWA DALAM SATU PLATFORM BERPUSAT.",
                    "link" => "login_owner.php"
                ],
                [
                    "title" => "Admin Sistem",
                    "desc" => "PORTAL AKSES PENTADBIR. SILA LOG MASUK UNTUK MENJALANKAN TUGAS PENYELENGGARAAN SISTEM, PENGURUSAN DATA PENGGUNA, DAN MEMASTIKAN KESELAMATAN KESELURUHAN PLATFORM SEWASISWA.",
                    "link" => "login_admin.php"
                ]
            ];

            foreach ($peranan as $p):
            ?>
            <div class="card-gradient rounded-3xl p-8 flex flex-col items-center text-center text-white shadow-2xl transform hover:scale-105 transition duration-300">
                <div class="w-24 h-24 bg-yellow-400 rounded-full flex items-center justify-center mb-6 shadow-lg">
                    <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl font-bold mb-4"><?php echo $p['title']; ?></h2>
                <p class="text-xs leading-relaxed mb-8 opacity-90 h-24">
                    <?php echo $p['desc']; ?>
                </p>
                
                <a href="<?php echo $p['link']; ?>" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-10 rounded-full transition-colors uppercase tracking-wider text-sm">
                    Log Masuk
                </a>
            </div>
            <?php endforeach; ?>

        </div>
    </main>

</body>
</html>