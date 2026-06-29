<?php
session_start();
// Sambungan ke database
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!isset($_SESSION['owner_id'])) {
    header("Location: login_owner.php");
    exit();
}

$owner_id = $_SESSION['owner_id'];

if (!isset($_GET['id'])) {
    header("Location: dashboard_owner.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data asal rumah
$query = mysqli_query($conn, "SELECT * FROM rumah WHERE id = '$id' AND tuan_rumah_id = '$owner_id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Rumah tidak dijumpai!'); window.location='dashboard_owner.php';</script>";
    exit();
}

// Proses Kemaskini Data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_rumah = mysqli_real_escape_string($conn, $_POST['nama_rumah']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    
    // Baiki ralat Undefined Array Key
    $furnishing = isset($_POST['furnishing']) ? mysqli_real_escape_string($conn, $_POST['furnishing']) : 'None';
    $carpark = isset($_POST['carpark']) ? mysqli_real_escape_string($conn, $_POST['carpark']) : 0;
    
    $hargaSewa = mysqli_real_escape_string($conn, $_POST['hargaSewa']);
    $alamat_rumah = mysqli_real_escape_string($conn, $_POST['alamat_rumah']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);

    $facilities = isset($_POST['facilities']) ? implode(", ", $_POST['facilities']) : "";
    $amenities = isset($_POST['amenities']) ? implode(", ", $_POST['amenities']) : "";

    // 1. Proses Gambar Rumah (Hanya jika ada baru)
    $gambar_final = $data['gambar']; 
    if (!empty($_FILES['gambar_rumah']['name'][0])) {
        $senarai_gambar = [];
        $upload_dir = "uploads/";
        foreach ($_FILES['gambar_rumah']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gambar_rumah']['error'][$key] == 0) {
                $file_name = time() . "_" . $_FILES['gambar_rumah']['name'][$key];
                if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
                    $senarai_gambar[] = $file_name;
                }
            }
        }
        if (!empty($senarai_gambar)) {
            $gambar_final = implode(",", $senarai_gambar);
        }
    }

    // 2. Proses Bil Utiliti (Hanya jika ada baru)
    $bil_final = $data['bil_utiliti'];
    if (!empty($_FILES['bil_utiliti']['name'])) {
        $bil_ext = pathinfo($_FILES['bil_utiliti']['name'], PATHINFO_EXTENSION);
        $bil_name = "BIL_" . time() . "." . $bil_ext;
        if (move_uploaded_file($_FILES['bil_utiliti']['tmp_name'], "uploads/" . $bil_name)) {
            $bil_final = $bil_name;
        }
    }

    // QUERY DIKEMASKINI (Ditambah koma & Status Reset)
    $sql = "UPDATE rumah SET 
            nama_rumah = '$nama_rumah', 
            kategori = '$kategori', 
            furnishing = '$furnishing', 
            carpark = '$carpark', 
            facilities = '$facilities', 
            amenities = '$amenities', 
            hargaSewa = '$hargaSewa', 
            alamat_rumah = '$alamat_rumah', 
            status = 'Pending',
            catatan_admin = NULL,
            deskripsi = '$deskripsi', 
            gambar = '$gambar_final',
            bil_utiliti = '$bil_final',
            lat = '$lat', 
            lng = '$lng'
            WHERE id = '$id' AND tuan_rumah_id = '$owner_id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Maklumat berjaya dikemaskini! Status kini: PENDING (Sila tunggu pengesahan Admin)'); window.location='dashboard_owner.php';</script>";
        exit();
    } else {
        echo "Error SQL: " . mysqli_error($conn);
    }
}

$existing_facilities = explode(", ", $data['facilities']);
$existing_amenities = explode(", ", $data['amenities']);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Edit Rumah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0fdfa; }
        #map { height: 300px; border-radius: 1rem; border: 2px solid #0d9488; z-index: 10; }
    </style>
</head>
<body class="p-5 md:p-10">
    <div class="max-w-5xl mx-auto bg-white rounded-3xl shadow-xl overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b bg-teal-900 text-white">
            <h1 class="text-xl font-bold uppercase">Kemaskini Rumah #<?php echo $id; ?></h1>
            <a href="dashboard_owner.php" class="hover:text-teal-300 font-bold text-sm uppercase">Batal</a>
        </div>

        <div class="p-10">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-teal-900 uppercase">Gambar Rumah (Tukar Baru)</label>
                            <div class="bg-teal-50 border-2 border-dashed border-teal-300 rounded-2xl p-4">
                                <input type="file" id="imgInput" accept="image/*" class="hidden" multiple>
                                <label for="imgInput" class="cursor-pointer bg-teal-600 text-white px-4 py-2 rounded-lg text-xs font-bold inline-block mb-4">Pilih Gambar</label>
                                <div id="preview-grid" class="grid grid-cols-3 gap-2">
                                    <?php 
                                    $imgs = explode(",", $data['gambar']);
                                    foreach($imgs as $img) {
                                        if(!empty($img)) echo '<img src="uploads/'.$img.'" class="aspect-square object-cover rounded-lg border-2 border-white shadow-sm">';
                                    }
                                    ?>
                                </div>
                                <div id="hidden-inputs"></div>
                            </div>
                        </div>

                        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-200">
                            <label class="block text-xs font-bold text-amber-800 uppercase mb-2">Dokumen Bil Utiliti</label>
                            <?php if(!empty($data['bil_utiliti'])): ?>
                                <p class="text-[10px] text-teal-700 mb-2 font-bold"><i class="fas fa-check-circle"></i> Fail sedia ada: <?php echo $data['bil_utiliti']; ?></p>
                            <?php endif; ?>
                            <input type="file" name="bil_utiliti" accept="image/*,.pdf" class="text-xs w-full">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-teal-900 uppercase">Kemaskini Pin Lokasi</label>
                            <div id="map"></div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <input type="text" name="lat" id="lat" value="<?php echo $data['lat']; ?>" class="bg-gray-100 p-2 text-[10px] rounded" readonly required>
                                <input type="text" name="lng" id="lng" value="<?php echo $data['lng']; ?>" class="bg-gray-100 p-2 text-[10px] rounded" readonly required>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <input type="text" name="nama_rumah" value="<?php echo $data['nama_rumah']; ?>" placeholder="TAJUK RUMAH" class="w-full p-4 border rounded-xl outline-none focus:ring-2 focus:ring-teal-500 font-bold text-sm uppercase" required>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <select name="kategori" class="w-full p-4 border rounded-xl outline-none text-sm font-bold" required>
                                <?php 
                                $categories = ["Condo", "Penthouse", "Townhouse", "Rumah Teres", "Bilik"];
                                foreach($categories as $cat) {
                                    $selected = ($data['kategori'] == $cat) ? "selected" : "";
                                    echo "<option value='$cat' $selected>$cat</option>";
                                }
                                ?>
                            </select>
                            <input type="number" name="hargaSewa" value="<?php echo $data['hargaSewa']; ?>" placeholder="RM" class="w-full p-4 border rounded-xl outline-none font-bold" required>
                        </div>

                        <input type="text" name="alamat_rumah" value="<?php echo $data['alamat_rumah']; ?>" class="w-full p-4 border rounded-xl outline-none text-sm uppercase" required>

                        <div class="bg-gray-50 p-5 rounded-2xl border">
                            <h3 class="font-bold text-teal-800 uppercase mb-3 text-xs tracking-widest">Kemudahan</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <?php 
                                $all_facs = ["Parking", "Lift", "Swimming Pool", "Gymnasium", "Aircond", "Mesin Basuh", "Dapur Masak"];
                                foreach($all_facs as $f) {
                                    $checked = (in_array($f, $existing_facilities) || in_array($f, $existing_amenities)) ? "checked" : "";
                                    echo "<label class='flex items-center space-x-2 text-[11px] cursor-pointer'><input type='checkbox' name='facilities[]' value='$f' $checked class='accent-teal-600'> <span>$f</span></label>";
                                }
                                ?>
                            </div>
                        </div>

                        <textarea name="deskripsi" class="w-full p-4 border rounded-xl h-24 text-sm" placeholder="Deskripsi..."><?php echo $data['deskripsi']; ?></textarea>

                        <button type="submit" name="submit" class="w-full bg-teal-700 hover:bg-teal-800 text-white font-bold py-4 rounded-xl shadow-lg transition uppercase tracking-widest">
                            Kemaskini Sekarang
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var curLat = <?php echo !empty($data['lat']) ? $data['lat'] : '2.9289'; ?>;
        var curLng = <?php echo !empty($data['lng']) ? $data['lng'] : '101.7801'; ?>;
        
        var map = L.map('map').setView([curLat, curLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var marker = L.marker([curLat, curLng]).addTo(map);

        map.on('click', function(e) {
            var lat = e.latlng.lat.toFixed(8);
            var lng = e.latlng.lng.toFixed(8);
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            marker.setLatLng(e.latlng);
        });

        const imgInput = document.getElementById('imgInput');
        const previewGrid = document.getElementById('preview-grid');
        let fileList = [];

        imgInput.addEventListener('change', function(e) {
            previewGrid.innerHTML = ''; 
            fileList = Array.from(e.target.files);
            fileList.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.className = "aspect-square object-cover rounded-lg border-2 border-teal-500 shadow-sm";
                    previewGrid.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
            updateHidden();
        });

        function updateHidden() {
            const dt = new DataTransfer();
            fileList.forEach(f => dt.items.add(f));
            const container = document.getElementById('hidden-inputs');
            container.innerHTML = '';
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'gambar_rumah[]';
            input.multiple = true;
            input.files = dt.files;
            input.style.display = 'none';
            container.appendChild(input);
        }
    </script>
</body>
</html>