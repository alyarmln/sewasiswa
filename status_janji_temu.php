<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if (!isset($_SESSION['user_id'])) {
    header("Location: login_masuk.php");
    exit();
}

$pelajar_id = $_SESSION['user_id'];

// Query JOIN untuk tarik data dari 3 jadual serentak
$sql = "SELECT jt.*, r.nama_rumah, tr.nama AS nama_tuan_rumah 
        FROM janji_temu jt
        JOIN rumah r ON jt.rumah_id = r.id
        JOIN tuan_rumah tr ON jt.tuan_rumah_id = tr.id
        WHERE jt.pelajar_id = '$pelajar_id'
        ORDER BY jt.id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Status Janji Temu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10 font-[Poppins]">

    <div class="max-w-5xl mx-auto bg-white p-8 rounded-3xl shadow-lg">
        <h2 class="text-2xl font-bold text-teal-700 mb-6 uppercase tracking-wider">Status Janji Temu Pelajar</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-teal-600 text-white text-sm uppercase">
                        <th class="p-4 rounded-tl-xl">Rumah</th>
                        <th class="p-4">Tuan Rumah</th>
                        <th class="p-4">Tarikh</th>
                        <th class="p-4">Masa</th>
                        <th class="p-4 rounded-tr-xl">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) { 
                            $status_color = ($row['status'] == 'Pending') ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600';
                    ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-4 font-semibold"><?php echo $row['nama_rumah']; ?></td>
                            <td class="p-4"><?php echo $row['nama_tuan_rumah']; ?></td>
                            <td class="p-4"><?php echo date('d-m-Y', strtotime($row['tarikh'])); ?></td>
                            <td class="p-4"><?php echo $row['masa']; ?></td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full font-bold text-[10px] uppercase <?php echo $status_color; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php 
                        } 
                    } else { ?>
                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-400 italic">Tiada janji temu ditemui.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-8 text-center">
            <a href="utama.php" class="text-teal-600 font-bold hover:underline">← Kembali ke Halaman Utama</a>
        </div>
    </div>

</body>
</html>