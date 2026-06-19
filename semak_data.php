<?php
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

$id = mysqli_real_escape_string($conn, $_GET['id']);
$type = mysqli_real_escape_string($conn, $_GET['type']);

if ($type == 'owner') {
    $query = mysqli_query($conn, "SELECT * FROM tuan_rumah WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query);
    ?>
    <div class="space-y-6">
        <div class="flex items-center space-x-4">
            <img src="uploads/<?php echo $data['gambar_profil']; ?>" class="w-20 h-20 rounded-full border-2 border-teal-500 object-cover">
            <div>
                <h3 class="font-bold text-xl"><?php echo $data['nama']; ?></h3>
                <p class="text-sm text-gray-500">Tuan Rumah (Owner)</p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="bg-slate-50 p-3 rounded-lg">
                <p class="text-gray-400 uppercase text-[10px] font-bold">No. Telefon</p>
                <p class="font-semibold"><?php echo $data['no_telefon']; ?></p>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg">
                <p class="text-gray-400 uppercase text-[10px] font-bold">Emel</p>
                <p class="font-semibold"><?php echo $data['emel']; ?></p>
            </div>
        </div>

        <div>
            <p class="text-gray-400 uppercase text-[10px] font-bold mb-2">Dokumen MyKad</p>
            <img src="uploads/<?php echo $data['gambar_mykad']; ?>" class="w-full rounded-xl border shadow-sm">
        </div>
    </div>
    <?php
} else {
    // SEMAK DOKUMEN RUMAH
    $query = mysqli_query($conn, "SELECT rumah.*, tuan_rumah.nama as owner_name, tuan_rumah.no_telefon, tuan_rumah.emel, tuan_rumah.gambar_mykad 
                                  FROM rumah 
                                  JOIN tuan_rumah ON rumah.tuan_rumah_id = tuan_rumah.id 
                                  WHERE rumah.id = '$id'");
    $data = mysqli_fetch_assoc($query);
    ?>
    <div class="space-y-6">
        <div class="border-b pb-4">
            <h3 class="font-bold text-xl text-teal-700"><?php echo $data['nama_rumah']; ?></h3>
            <p class="text-sm text-gray-500 italic"><?php echo $data['alamat_rumah']; ?></p>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="bg-slate-50 p-3 rounded-lg border-l-4 border-teal-500">
                <p class="text-gray-400 uppercase text-[10px] font-bold">Nama Pemilik</p>
                <p class="font-semibold"><?php echo $data['owner_name']; ?></p>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg">
                <p class="text-gray-400 uppercase text-[10px] font-bold">Hubungi</p>
                <p class="font-semibold"><?php echo $data['no_telefon']; ?></p>
                <p class="text-xs text-gray-400"><?php echo $data['emel']; ?></p>
            </div>
        </div>

        <div>
            <p class="text-gray-400 uppercase text-[10px] font-bold mb-2">Gambar Rumah</p>
            <div class="grid grid-cols-2 gap-2">
                <?php 
                $gambar_list = explode(',', $data['gambar']); 
                foreach($gambar_list as $img): if(!empty($img)):
                ?>
                    <img src="uploads/<?php echo trim($img); ?>" class="w-full h-32 object-cover rounded-lg border">
                <?php endif; endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-400 uppercase text-[10px] font-bold mb-2 text-rose-600">Bil Utiliti (Pengesahan Alamat)</p>
                <img src="uploads/<?php echo $data['bil_utiliti']; ?>" class="w-full rounded-lg border shadow-sm hover:scale-105 transition">
            </div>
            <div>
                <p class="text-gray-400 uppercase text-[10px] font-bold mb-2">MyKad Pemilik</p>
                <img src="uploads/<?php echo $data['gambar_mykad']; ?>" class="w-full rounded-lg border shadow-sm">
            </div>
        </div>
    </div>
    <?php
}
?>