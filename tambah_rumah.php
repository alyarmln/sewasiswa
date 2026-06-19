<?php
session_start();

// Sambungan ke database
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if (!$conn) {
    die("Sambungan database gagal: " . mysqli_connect_error());
}

// Pastikan hanya tuan rumah yang log masuk boleh akses
if (!isset($_SESSION['owner_id'])) {
    header("Location: login_owner.php");
    exit();
}

// Proses Simpan Data (Apabila borang dihantar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tuan_rumah_id = $_SESSION['owner_id'];
    $nama_rumah = mysqli_real_escape_string($conn, $_POST['nama_rumah']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $jantina = mysqli_real_escape_string($conn, $_POST['jantina']); // Menangkap data pilihan jantina baru
    $furnishing = mysqli_real_escape_string($conn, $_POST['furnishing']);
    $carpark = mysqli_real_escape_string($conn, $_POST['carpark']);
    $hargaSewa = mysqli_real_escape_string($conn, $_POST['hargaSewa']);
    $alamat_rumah = mysqli_real_escape_string($conn, $_POST['alamat_rumah']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // AMBIL DATA KOORDINAT
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);

    $facilities = isset($_POST['facilities']) ? implode(", ", $_POST['facilities']) : "";
    $amenities = isset($_POST['amenities']) ? implode(", ", $_POST['amenities']) : "";

    // Proses Muat Naik Gambar
    $senarai_gambar = [];
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!empty($_FILES['gambar_rumah']['name'][0])) {
        foreach ($_FILES['gambar_rumah']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gambar_rumah']['error'][$key] == 0) {
                $file_name = time() . "_" . basename($_FILES['gambar_rumah']['name'][$key]);
                if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
                    $senarai_gambar[] = $file_name;
                }
            }
        }
    }
    $gambar_str = implode(",", $senarai_gambar);

    // Proses Muat Naik Bil Utiliti
    $bil_utiliti = "";
    if (!empty($_FILES['bil_utiliti']['name'])) {
        $file_ext = pathinfo($_FILES['bil_utiliti']['name'], PATHINFO_EXTENSION);
        $bil_name = "BIL_" . time() . "_" . $tuan_rumah_id . "." . $file_ext;
        if (move_uploaded_file($_FILES['bil_utiliti']['tmp_name'], $upload_dir . $bil_name)) {
            $bil_utiliti = $bil_name;
        }
    }

    // QUERY SQL INSERT (Lajur 'jantina' telah dimasukkan ke dalam senarai padanan database)
    $sql = "INSERT INTO rumah (tuan_rumah_id, nama_rumah, kategori, jantina, furnishing, carpark, facilities, amenities, hargaSewa, alamat_rumah, deskripsi, gambar, bil_utiliti, lat, lng, status, status_sewa, jumlah_klik)
            VALUES ('$tuan_rumah_id', '$nama_rumah', '$kategori', '$jantina', '$furnishing', '$carpark', '$facilities', '$amenities', '$hargaSewa', '$alamat_rumah', '$deskripsi', '$gambar_str', '$bil_utiliti', '$lat', '$lng', 'Pending', 'Tersedia', 0)";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Rumah berjaya didaftarkan dan menanti pengesahan admin!'); window.location='dashboard_owner.php';</script>";
        exit();
    } else {
        echo "Ralat Database: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Tambah Rumah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #e0f2f1; }
        #map { height: 350px; border-radius: 1rem; border: 2px solid #0f766e; z-index: 10; }
    </style>
</head>
<body class="p-5 md:p-10">
    <div class="max-w-5xl mx-auto bg-white rounded-3xl shadow-lg overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b">
            <div class="flex items-center space-x-4">
                <img src="ukm_logo.png" class="h-10" alt="UKM">
                <img src="sewasiswa_logo.png" class="h-10" alt="SewaSiswa">
            </div>
            <div class="space-x-6 text-teal-800 font-bold text-sm uppercase">
                <a href="dashboard_owner.php" class="hover:text-teal-500">Dashboard</a>
            </div>
        </div>

        <div class="p-10">
            <h1 class="text-3xl font-black text-teal-900 uppercase mb-8 text-center">Senaraikan Rumah Anda</h1>

            <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
                
                <div class="space-y-4 mb-10">
                    <label class="block text-sm font-bold text-teal-900 uppercase">Gambar Rumah</label>
                    <div class="w-full min-h-[200px] bg-teal-50 rounded-2xl border-2 border-dashed border-teal-300 p-6">
                        <div class="flex justify-center mb-6">
                            <label for="imgInput" class="cursor-pointer bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition flex items-center space-x-2">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Gambar</span>
                            </label>
                            <input type="file" id="imgInput" name="gambar_rumah[]" accept="image/*" class="hidden" multiple>
                        </div>
                        <div id="preview-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
                        <div id="empty-msg" class="text-center text-gray-400 py-10">
                            <p class="text-sm italic">Sila Muat Naik Gambar.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50 p-6 rounded-3xl border border-amber-200 mt-6">
                    <label class="block text-xs font-bold text-amber-800 uppercase tracking-widest mb-2">
                        <i class="fas fa-file-invoice mr-2"></i> Dokumen Verifikasi (Bil Utiliti)
                    </label>
                    <input type="file" name="bil_utiliti" accept="image/*,.pdf" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-700" required>
                    <p class="text-[9px] text-amber-600 mt-2 italic">*Sila muat naik bil elektrik/air yang sah untuk proses verifikasi akaun.</p>
                </div>
                <br> <br>

                <div class="space-y-6">
                    <input type="text" name="nama_rumah" placeholder="TAJUK (Contoh: Apartment Vista Bangi)" class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none focus:border-teal-500 font-bold text-sm uppercase" required>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <select name="kategori" class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none focus:border-teal-500 font-bold text-gray-500 text-sm uppercase" required>
                            <option value="">Jenis Rumah</option>
                            <option value="Condo">Condo / Serviced Residence</option>
                            <option value="Penthouse">Penthouse</option>
                            <option value="Townhouse">Townhouse</option>
                            <option value="Rumah Teres">Rumah Teres</option>
                            <option value="Bilik">Bilik</option>
                        </select>

                        <select name="jantina" class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none focus:border-teal-500 font-bold text-gray-500 text-sm uppercase" required>
                            <option value="">Saringan Jantina</option>
                            <option value="Lelaki">👦 Lelaki Sahaja</option>
                            <option value="Perempuan">👧 Perempuan Sahaja</option>
                            <option value="Semua">👫 Terbuka (Semua)</option>
                        </select>

                        <input type="number" name="hargaSewa" placeholder="HARGA SEWA (RM)" class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none focus:border-teal-500 font-bold text-sm" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <select name="furnishing" class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none focus:border-teal-500 font-bold text-gray-500 text-sm uppercase">
                            <option value="Unfurnished">Tiada Perabot</option>
                            <option value="Partially Furnished">Separa Perabot</option>
                            <option value="Fully Furnished">Fully Furnished</option>
                        </select>
                        <input type="number" name="carpark" placeholder="BIL PARKIR KERETA" class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none focus:border-teal-500 font-bold text-sm">
                    </div>

                    <input type="text" name="alamat_rumah" placeholder="ALAMAT LENGKAP" class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none focus:border-teal-500 font-bold text-sm uppercase" required>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-teal-900 uppercase italic">
                            <i class="fas fa-map-marker-alt text-red-500"></i> Pin Lokasi Tepat Kediaman
                        </label>
                        <div id="map"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="lat" id="lat" placeholder="Latitude" class="bg-gray-100 p-2 text-xs rounded border" readonly required>
                            <input type="text" name="lng" id="lng" placeholder="Longitude" class="bg-gray-100 p-2 text-xs rounded border" readonly required>
                        </div>
                        <p class="text-[10px] text-slate-500">*Sila klik pada peta di mana rumah anda berada. Titik ini akan digunakan untuk mengira jarak ke UKM.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="font-bold text-teal-800 uppercase mb-3 text-sm tracking-widest">Kemudahan</h3>
                            <div class="grid grid-cols-1 gap-2 bg-gray-50 p-4 rounded-xl border">
                                <?php
                                $facs = ["Parking", "Lift", "Swimming Pool", "Playground", "Gymnasium", "Minimart", "Multipurpose Hall"];
                                foreach($facs as $f) {
                                    echo "<label class='flex items-center space-x-3 text-sm cursor-pointer'><input type='checkbox' name='facilities[]' value='$f' class='w-4 h-4 accent-teal-600'> <span>$f</span></label>";
                                }
                                ?>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-bold text-teal-800 uppercase mb-3 text-sm tracking-widest">Kelengkapan</h3>
                            <div class="grid grid-cols-1 gap-2 bg-gray-50 p-4 rounded-xl border">
                                <?php
                                $ams = ["Pendingin Hawa", "Dibenarkan Masak", "Mesin Basuh", "Near KTM/LRT"];
                                foreach($ams as $a) {
                                    echo "<label class='flex items-center space-x-3 text-sm cursor-pointer'><input type='checkbox' name='amenities[]' value='$a' class='w-4 h-4 accent-teal-600'> <span>$a</span></label>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <textarea name="deskripsi" placeholder="DESKRIPSI TAMBAHAN" class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none focus:border-teal-500 text-sm h-24" required></textarea>

                    <button type="submit" class="w-full bg-teal-700 hover:bg-teal-800 text-white font-bold py-4 rounded-xl shadow-lg transition uppercase tracking-widest">
                        Daftar Rumah & Lokasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([2.9289, 101.7801], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker;

        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            document.getElementById('lat').value = lat.toFixed(8);
            document.getElementById('lng').value = lng.toFixed(8);

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });

        // --- SCRIPT GAMBAR ---
        const imgInput = document.getElementById('imgInput');
        const previewGrid = document.getElementById('preview-grid');
        const emptyMsg = document.getElementById('empty-msg');
        let fileList = [];

        imgInput.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            newFiles.forEach(file => {
                if (!fileList.some(f => f.name === file.name && f.size === file.size)) {
                    fileList.push(file);
                    renderPreview(file);
                }
            });
            updateUI();
            imgInput.value = ''; // Mengosongkan value untuk membolehkan upload fail yang sama jika dipadam
        });

        function renderPreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = "relative group aspect-square bg-white rounded-xl overflow-hidden shadow-md border-2 border-white transition hover:scale-105";
                div.setAttribute('data-name', file.name);
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="removeFile('${file.name}')" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 shadow-lg hover:bg-red-700 transition">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewGrid.appendChild(div);
            }
            reader.readAsDataURL(file);
        }

        function removeFile(fileName) {
            fileList = fileList.filter(f => f.name !== fileName);
            const el = document.querySelector(`[data-name="${fileName}"]`);
            if (el) el.remove();
            updateUI();
        }

        function updateUI() {
            if (fileList.length > 0) { emptyMsg.classList.add('hidden'); }
            else { emptyMsg.classList.remove('hidden'); }

            // Keadah selamat: Kemas kini DataTransfer terus ke input utama anda yang mempunyai nama array
            const dataTransfer = new DataTransfer();
            fileList.forEach(file => dataTransfer.items.add(file));
            imgInput.files = dataTransfer.files;
        }
    </script>
</body>
</html>