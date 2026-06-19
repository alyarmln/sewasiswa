<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Daftar Tuan Rumah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0d4d4d; }
        .form-container { background-color: white; border-radius: 50px; }
        input:focus, textarea:focus { border-color: #0d9488 !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center py-10 px-4">

    <div class="flex space-x-6 mb-8 items-center bg-white/10 p-4 rounded-2xl backdrop-blur-sm">
        <img src="ukm_logo.png" alt="UKM" class="h-12 object-contain">
        <div class="h-8 w-[2px] bg-white/20"></div>
        <img src="sewasiswa_logo.png" alt="SewaSiswa" class="h-10 object-contain">
    </div>

    <div class="form-container w-full max-w-5xl p-12 shadow-2xl relative">
        <h1 class="text-4xl font-black text-center mb-10 tracking-widest text-gray-800 uppercase">
            SEWA<span class="text-teal-600">SISWA</span><br>
            <span class="text-sm font-bold text-slate-400 tracking-[0.3em]">Pendaftaran Tuan Rumah</span>
        </h1>

        <form action="proses_daftar_tuan_rumah.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                
                <div class="space-y-4">
                    <h2 class="text-teal-600 font-black text-xs uppercase tracking-widest border-b pb-2">Maklumat Peribadi</h2>
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Nama Penuh (Seperti MyKad)</label>
                        <input type="text" name="nama" required class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none transition-all bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">No. Telefon</label>
                        <input type="text" name="no_telefon" required placeholder="Contoh: 0123456789" class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none transition-all bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Emel</label>
                        <input type="email" name="emel" required class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none transition-all bg-slate-50">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Gambar Profil</label>
                            <input type="file" name="gambar_profil" accept="image/*" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Gambar MyKad</label>
                            <input type="file" name="gambar_mykad" accept="image/*" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                        </div>
                    </div>
                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                        <label class="block text-[10px] font-black uppercase mb-2 text-amber-700 italic"><i class="fas fa-file-invoice mr-1"></i> Bukti Kewujudan Rumah (Wajib)</label>
                        <input type="file" name="bil_utiliti" accept="image/*,.pdf" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-200 file:text-amber-800 hover:file:bg-amber-300">
                        <p class="text-[9px] text-amber-600 mt-2 font-medium">Sila muat naik Bil Air atau Bil Elektrik rumah yang ingin disewakan.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <h2 class="text-teal-600 font-black text-xs uppercase tracking-widest border-b pb-2">Maklumat Akaun</h2>
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Kategori Tuan Rumah</label>
                        <select name="kategori" required class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none transition-all bg-slate-50">
                            <option value="Individu">Individu</option>
                            <option value="Agensi/Syarikat">Agensi / Syarikat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Alamat Surat-menyurat</label>
                        <input type="text" name="alamat" required class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none transition-all bg-slate-50">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Password</label>
                            <input type="password" id="password" name="password" required class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none transition-all bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Sahkan Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required class="w-full border-2 border-gray-100 rounded-xl p-3 outline-none transition-all bg-slate-50">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-1 text-slate-500">Deskripsi Bisnes / Pengenalan</label>
                        <textarea name="deskripsi_bisnes" required placeholder="Ceritakan sedikit latar belakang anda sebagai penyedia sewa..." class="w-full border-2 border-gray-100 rounded-xl p-3 h-32 outline-none transition-all bg-slate-50"></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-black py-4 px-24 rounded-full shadow-xl shadow-red-200 transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-sm">
                    Daftar Akaun
                </button>
                <p class="text-[10px] text-slate-400 mt-4 font-bold uppercase tracking-widest">Akaun akan disemak oleh Admin dalam tempoh 24 jam</p>
            </div>
        </form>
    </div>

    <script>
        function validateForm() {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (pass !== confirm) {
                alert("Kata laluan tidak sepadan! Sila semak semula.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>