<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>SewaSiswa - Daftar Pelajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #114b5f 0%, #0d9488 100%); }
        .form-container { background-color: white; border-radius: 30px; }
        input:focus { transform: scale(1.01); transition: 0.2s; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center py-10 px-4">

    <div class="flex items-center space-x-6 mb-8 bg-white/10 p-4 rounded-2xl backdrop-blur-md">
        <img src="ukm_logo.png" alt="UKM" class="h-10">
        <div class="h-8 w-px bg-white/30"></div>
        <img src="sewasiswa_logo.png" alt="SewaSiswa" class="h-10">
    </div>

    <div class="form-container w-full max-w-xl p-10 shadow-2xl">
        <h1 class="text-3xl font-black text-center mb-8 tracking-tighter text-gray-800 uppercase">
            Daftar <span class="text-teal-600">Pelajar</span>
        </h1>

        <form action="proses_daftar_pelajar.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1 ml-1">Nama Penuh</label>
                <input type="text" name="nama" placeholder="Contoh: Ahmad Bin Ali" required 
                class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none focus:border-teal-500 bg-gray-50">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1 ml-1">No. Matrik</label>
                    <input type="text" name="no_matrik" placeholder="A123456" required 
                    class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none focus:border-teal-500 bg-gray-50">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1 ml-1">Emel Siswa</label>
                    <input type="email" name="emel" placeholder="pelajar@siswa.ukm.edu.my" required 
                    class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none focus:border-teal-500 bg-gray-50">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-5">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1 ml-1">Password</label>
                    <input type="password" name="password" required 
                    class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none focus:border-teal-500 bg-gray-50">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1 ml-1">Sahkan Password</label>
                    <input type="password" name="confirm_password" required 
                    class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none focus:border-teal-500 bg-gray-50">
                </div>
            </div>

            <div class="mt-8 flex flex-col items-center space-y-4">
                <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 px-20 rounded-xl shadow-lg transition-all hover:-translate-y-1 w-full uppercase tracking-widest text-sm">
                    Daftar Sekarang
                </button>
                <p class="text-[10px] font-bold uppercase text-gray-400">
                    Dah ada akaun? <a href="loginmasuk.php" class="text-teal-600 hover:text-teal-700 underline">Log Masuk Sini</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>