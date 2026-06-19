<?php
// Sambungan ke Database
$conn = mysqli_connect("localhost", "root", "", "sewasiswa_db");

// Simulasi mengambil data tuan rumah berdasarkan ID (biasanya dari URL atau Session)
// Contoh: ratingowner.php?id=5
$tuan_rumah_id = isset($_GET['id']) ? $_GET['id'] : 1;
$query = "SELECT nama, gambar_profil FROM tuan_rumah WHERE id = '$tuan_rumah_id'";
$result = mysqli_query($conn, $query);
$owner = mysqli_fetch_assoc($result);

// Jika tiada gambar diletakkan semasa pendaftaran, gunakan gambar default
$profile_pic = !empty($owner['gambar_profil']) ? $owner['gambar_profil'] : 'default_avatar.png';
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Nilai Tuan Rumah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #e0f2f1; }
        .rating-card { background: linear-gradient(to bottom, #114b5f, #2d93ad, #48cae4); }
        .star { cursor: pointer; transition: color 0.2s; color: rgba(255,255,255,0.3); }
        .star.active { color: #facc15; } /* Warna kuning untuk bintang aktif */
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <header class="p-6 flex justify-between items-center px-10 lg:px-20 bg-white shadow-sm">
        <div class="flex items-center space-x-4">
            <img src="ukm_logo.png" alt="UKM" class="h-10">
            <img src="sewasiswa_logo.png" alt="SewaSiswa" class="h-10">
        </div>
    </header>

    <main class="flex-grow px-10 lg:px-20 py-12">
        <h1 class="text-3xl font-bold text-cyan-900 uppercase tracking-widest mb-10">Tentang Tuan Rumah</h1>

        <div class="max-w-4xl mx-auto relative">
            <div class="absolute -top-12 -right-6 z-10">
                <img src="uploads/<?php echo $profile_pic; ?>" alt="Profil Tuan Rumah" 
                     class="w-32 h-32 rounded-full border-4 border-white shadow-xl object-cover bg-gray-200">
            </div>

            <div class="rating-card rounded-[40px] p-10 shadow-2xl text-white">
                <form action="simpan_ulasan.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="tuan_rumah_id" value="<?php echo $tuan_rumah_id; ?>">
                    <input type="hidden" name="rating_value" id="rating_value" value="0">
                    
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold uppercase tracking-widest mb-4">Nilai Tuan Rumah</h2>
                        <div class="flex justify-center space-x-2 text-5xl" id="star-container">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                        <p class="text-xs mt-2 opacity-80 uppercase font-bold" id="rating-text">Sila pilih rating anda</p>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-bold uppercase tracking-widest mb-4 text-center">Tambah Gambar dan Video</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="border-2 border-dashed border-white/50 rounded-2xl h-32 flex flex-col items-center justify-center cursor-pointer hover:bg-white/10">
                                <span class="text-3xl">⬆️</span>
                                <input type="file" name="media[]" class="hidden" multiple>
                            </label>
                            <div class="border-2 border-dashed border-white/50 rounded-2xl h-32 bg-white/5"></div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <div class="border-2 border-dashed border-white/50 rounded-2xl p-4 bg-white/10">
                            <textarea name="komen" placeholder="KONGSIKAN PENGALAMAN ANDA" required
                                class="w-full h-32 bg-transparent placeholder-white/70 text-white outline-none resize-none font-bold uppercase text-center"></textarea>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-16 rounded-full uppercase tracking-widest shadow-xl transition">
                            Hantar Ulasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('rating_value');
        const ratingText = document.getElementById('rating-text');

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const value = star.getAttribute('data-value');
                ratingInput.value = value;
                updateStars(value);
                ratingText.innerText = `Anda memberi ${value} bintang`;
            });

            star.addEventListener('mouseover', () => {
                updateStars(star.getAttribute('data-value'));
            });

            star.addEventListener('mouseleave', () => {
                updateStars(ratingInput.value);
            });
        });

        function updateStars(value) {
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        }
    </script>

</body>
</html>