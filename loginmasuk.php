<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Log Masuk OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), 
                        url('https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&q=80');
            background-size: cover;
            background-position: center;
        }
        .input-field {
            background-color: #e0f2f1; /* Warna hijau teal */
            border: none;
        }
        .btn-masuk {
            background-color: #ff5f5f; /* Warna merah butang Masuk */
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <header class="p-6 flex items-center space-x-4">
        <img src="ukm_logo.png" alt="UKM Logo" class="h-12">
        <img src="sewasiswa_logo.png" alt="SewaSiswa Logo" class="h-12">
    </header>

    <main class="flex-grow flex items-center justify-between px-10 lg:px-24">
        
        <div class="w-full max-w-md">
            <h2 class="text-sm uppercase tracking-widest text-gray-600 font-semibold mb-2">Selamat Datang</h2>
            <h1 class="text-5xl font-bold text-gray-800 mb-4">
                Log <span class="text-gray-400 font-light">Masuk</span>
            </h1>
            <p class="text-xs text-gray-400 mb-8 font-medium">Sila masukkan emel dan kata laluan anda. Kod pengesahan (OTP) akan dihantar ke emel siswa untuk pengesahan kedua.</p>

            <?php if(isset($_SESSION['error_login'])): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl text-xs font-semibold shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 fill-current" viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"></path>
                        </svg>
                        <span><?php echo $_SESSION['error_login']; unset($_SESSION['error_login']); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form action="proses_login.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Emel Siswa UKM</label>
                    <input type="email" name="emel" required placeholder="contoh: a123456@siswa.ukm.edu.my"
                        class="input-field w-full p-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-400 text-sm font-medium transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Kata Laluan</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="input-field w-full p-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-400 text-sm font-medium transition">
                </div>

                <button type="submit" 
                    class="btn-masuk w-full md:w-56 py-4 text-white font-bold rounded-full shadow-lg hover:bg-red-600 transition duration-300 uppercase tracking-widest text-xs mt-2">
                    Dapatkan Kod OTP
                </button>
            </form>

            <p class="mt-12 text-xs font-bold text-gray-600 uppercase">
                Tiada Akaun Lagi? <a href="daftar_pelajar.php" class="text-red-500 hover:underline">Cipta Akaun</a>
            </p>
        </div>

        <div class="hidden lg:block relative w-1/2">
            <div class="absolute right-0 top-[-100px] w-80 h-[600px] bg-teal-50 rounded-l-3xl -z-10"></div>
            
            <div class="flex items-end justify-center h-full space-x-4">
                <img src="character_3d.png" alt="Ilustrasi Pengguna" class="max-w-md">
                <img src="cactus.png" alt="Hiasan" class="h-64 mb-10">
            </div>
        </div>

    </main>

</body>
</html>