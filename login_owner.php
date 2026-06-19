<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>SewaSiswa - Log Masuk Tuan Rumah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), 
                        url('https://images.unsplash.com/photo-1564013799919-ab600027ffc6?q=80&w=2070');
            background-size: cover;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <header class="p-8 flex items-center space-x-4">
        <img src="ukm_logo.png" alt="UKM" class="h-10">
        <img src="sewasiswa_logo.png" alt="SewaSiswa" class="h-10">
    </header>

    <main class="flex-grow flex items-center justify-between px-20">
        <div class="w-full max-w-md">
            <h2 class="text-xs uppercase font-bold text-gray-400 tracking-widest mb-2">Selamat Datang (Tuan Rumah)</h2>
            <h1 class="text-6xl font-bold text-cyan-900 mb-10">Log <span class="text-gray-300 font-light">Masuk</span></h1>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-6 text-xs font-bold uppercase">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="proses_login_owner.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 uppercase mb-1">Emel</label>
                    <input type="email" name="emel" required 
                           class="w-full bg-[#D1F1F1] p-4 rounded-xl focus:ring-2 focus:ring-teal-400 outline-none border-none">
                </div>

                <div class="relative">
                    <label class="block text-[10px] font-bold text-gray-700 uppercase mb-1">Password</label>
                    <input type="password" name="password" required 
                           class="w-full bg-[#D1F1F1] p-4 rounded-xl focus:ring-2 focus:ring-teal-400 outline-none border-none">
                    <a href="#" class="absolute right-0 top-0 text-[10px] font-bold text-gray-400 hover:text-red-500 mt-1 uppercase">Lupa Katalaluan?</a>
                </div>

                <button type="submit" class="bg-[#FF5C5C] hover:bg-red-600 text-white font-bold py-3 px-16 rounded-full uppercase tracking-widest text-sm shadow-xl transition transform active:scale-95">
                    Masuk
                </button>
            </form>

            <p class="mt-12 text-[10px] font-bold text-gray-500 uppercase">
                Tiada Akaun Lagi? <a href="daftar_tuan_rumah.php" class="text-red-500 hover:underline">Cipta Akaun</a>
            </p>
        </div>

        <div class="hidden lg:flex items-end space-x-4">
            <div class="relative">
                <img src="3d_owner_char.png" alt="Owner Illustration" class="h-[500px] z-10 relative">
                <div class="absolute -right-10 bottom-0">
                    <img src="cactus.png" class="h-48">
                </div>
            </div>
        </div>
    </main>
</body>
</html>