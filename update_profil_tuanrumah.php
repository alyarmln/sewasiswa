<?php
session_start();

// 1. Sambungan ke Database sewasiswa
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

// Semak jika Tuan Rumah sudah login
if (!isset($_SESSION['owner_id'])) {
    header("Location: login_owner.php");
    exit();
}

$owner_id = $_SESSION['owner_id'];

// 2. Ambil data sedia ada dari Database (READ)
$query = "SELECT * FROM tuan_rumah WHERE id = '$owner_id'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// 3. Proses Kemaskini apabila butang ditekan (UPDATE)
if (isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $no_tel = mysqli_real_escape_string($conn, $_POST['no_telefon']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_bisnes']);

    // Logik Gambar Profil
    $gambar_profil_final = $data['gambar_profil'];
    if (!empty($_FILES['gambar_profil']['name'])) {
        $file_profil = time() . "_profil_" . $_FILES['gambar_profil']['name'];
        if (move_uploaded_file($_FILES['gambar_profil']['tmp_name'], "uploads/" . $file_profil)) {
            $gambar_profil_final = $file_profil;
        }
    }

    // Logik Gambar MyKad (Sangat penting jika pendaftaran ditolak sebelum ini)
    $gambar_mykad_final = $data['gambar_mykad'];
    if (!empty($_FILES['gambar_mykad']['name'])) {
        $file_mykad = time() . "_mykad_" . $_FILES['gambar_mykad']['name'];
        if (move_uploaded_file($_FILES['gambar_mykad']['tmp_name'], "uploads/" . $file_mykad)) {
            $gambar_mykad_final = $file_mykad;
        }
    }

    /* PENTING: 
       Setiap kali tuan rumah kemaskini, kita tukar status kepada 'Pending' 
       dan kosongkan 'alasan_tolak' supaya Admin tahu ada maklumat baru untuk disemak.
    */
    $sql_update = "UPDATE tuan_rumah SET 
                    nama = '$nama', 
                    alamat = '$alamat', 
                    kategori = '$kategori', 
                    no_telefon = '$no_tel', 
                    deskripsi_bisnes = '$deskripsi',
                    gambar_profil = '$gambar_profil_final',
                    gambar_mykad = '$gambar_mykad_final',
                    status = 'Pending',
                    alasan_tolak = NULL
                  WHERE id = '$owner_id'";

    if (mysqli_query($conn, $sql_update)) {
        echo "<script>
                alert('Profil berjaya dikirim semula! Sila tunggu pengesahan Admin.');
                window.location.href='dashboard_owner.php';
              </script>";
    } else {
        echo "Ralat Kemaskini: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - Kemaskini Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0fdfa; }
    </style>
</head>
<body class="p-4 md:p-12">

    <div class="max-w-4xl mx-auto bg-white rounded-[40px] shadow-2xl overflow-hidden border border-teal-100">
        
        <div class="bg-teal-700 p-8 text-white flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold uppercase tracking-widest">Kemaskini Profil</h1>
                <p class="text-xs opacity-70">Sila pastikan maklumat dan dokumen anda jelas.</p>
            </div>
            <a href="dashboard_owner.php" class="bg-white/10 hover:bg-white/20 px-5 py-2 rounded-full text-xs font-bold uppercase transition border border-white/30">
                <i class="fas fa-arrow-left mr-2"></i> Batal
            </a>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="p-8 md:p-12">
            
            <?php if($data['status'] == 'Rejected'): ?>
            <div class="mb-10 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                <p class="text-red-700 font-bold text-sm uppercase">Sebab Penolakan Sebelum Ini:</p>
                <p class="text-red-600 text-sm italic">"<?php echo $data['alasan_tolak']; ?>"</p>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                
                <div class="space-y-8 border-b md:border-b-0 md:border-r border-gray-100 pb-8 md:pb-0 md:pr-10">
                    
                    <div class="flex flex-col items-center">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-4 text-center">Foto Profil Semasa</label>
                        <?php $img_src = !empty($data['gambar_profil']) ? "uploads/".$data['gambar_profil'] : "https://ui-avatars.com/api/?name=".urlencode($data['nama']); ?>
                        <img src="<?php echo $img_src; ?>" class="w-40 h-40 rounded-full object-cover border-4 border-teal-500 shadow-lg mb-4">
                        <input type="file" name="gambar_profil" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-4">Muat Naik MyKad Baru (Jika Perlu)</label>
                        <?php if(!empty($data['gambar_mykad'])): ?>
                            <div class="mb-4 p-2 border rounded-lg bg-gray-50">
                                <p class="text-[10px] text-gray-500 mb-1">MyKad Semasa:</p>
                                <img src="uploads/<?php echo $data['gambar_mykad']; ?>" class="w-full h-32 object-contain rounded">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="gambar_mykad" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                        <p class="text-[9px] text-red-400 mt-2">*Pastikan gambar MyKad jelas dan tidak silau.</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-teal-600 mb-1">Nama Penuh (Seperti Dalam IC)</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required class="w-full border-b-2 border-gray-200 focus:border-teal-500 p-2 outline-none transition bg-transparent text-gray-700">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-teal-600 mb-1">Kategori</label>
                            <select name="kategori" class="w-full border-b-2 border-gray-200 focus:border-teal-500 p-2 outline-none transition bg-transparent">
                                <option value="PERSENDIRIAN" <?php if($data['kategori'] == 'PERSENDIRIAN') echo 'selected'; ?>>PERSENDIRIAN</option>
                                <option value="AGENSI" <?php if($data['kategori'] == 'AGENSI') echo 'selected'; ?>>AGENSI</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-teal-600 mb-1">No. Telefon</label>
                            <input type="text" name="no_telefon" value="<?php echo htmlspecialchars($data['no_telefon']); ?>" required class="w-full border-b-2 border-gray-200 focus:border-teal-500 p-2 outline-none transition bg-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Emel (Tetap)</label>
                        <input type="text" value="<?php echo $data['emel']; ?>" disabled class="w-full border-b-2 border-gray-100 p-2 bg-gray-50 text-gray-400 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-teal-600 mb-1">Alamat Kediaman</label>
                        <textarea name="alamat" required class="w-full border-b-2 border-gray-200 focus:border-teal-500 p-2 outline-none transition bg-transparent h-20 resize-none"><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-teal-600 mb-1">Deskripsi Bisnes</label>
                        <textarea name="deskripsi_bisnes" required class="w-full border-2 border-gray-100 rounded-2xl p-4 h-32 focus:border-teal-500 outline-none transition bg-teal-50/30 text-gray-700"><?php echo htmlspecialchars($data['deskripsi_bisnes']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center">
                <button type="submit" name="update_profil" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-4 px-16 rounded-full shadow-lg shadow-teal-200 uppercase tracking-widest text-sm transition transform active:scale-95 flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i> Hantar Untuk Pengesahan
                </button>
                <p class="text-[10px] text-gray-400 mt-4 italic text-center">
                    *Dengan menekan butang ini, permohonan anda akan dihantar semula kepada pihak Admin untuk semakan.
                </p>
            </div>
        </form>
    </div>

</body>
</html>