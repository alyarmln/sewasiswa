<?php
session_start();
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

// Guna pelbagai kemungkinan kunci session untuk elak kick-out
$user_id = $_SESSION['pelajar_id'] ?? $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("<script>alert('Sila log masuk terlebih dahulu.'); window.location='loginmasuk.php';</script>");
}

// Query untuk ambil senarai owner yang pernah bersembang dengan pelajar ini
$query = "SELECT DISTINCT tr.id, tr.nama, tr.gambar_profil 
          FROM tuan_rumah tr
          INNER JOIN messages m ON (m.receiver_id = tr.id OR m.user_id = tr.id)
          WHERE (m.user_id = '$user_id' AND m.sender_id = 'pelajar') 
          OR (m.receiver_id = '$user_id' AND m.sender_id = 'tuan_rumah')";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Mesej Saya - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen p-6">
    <div class="max-w-md mx-auto bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="p-6 bg-teal-600 text-white flex justify-between items-center">
            <h1 class="text-xl font-bold">Mesej Saya</h1>
            <a href="dashboard_pelajar.php" class="hover:bg-teal-500 p-2 rounded-full transition">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        
        <div class="divide-y divide-slate-100">
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <a href="ruangchat.php?id=<?php echo $row['id']; ?>" class="flex items-center p-4 hover:bg-slate-50 transition">
                    <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-teal-600 font-bold"><?php echo strtoupper(substr($row['nama'], 0, 1)); ?></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800"><?php echo htmlspecialchars($row['nama']); ?></h3>
                        <p class="text-xs text-slate-500">Klik untuk sembang</p>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-10 text-center text-slate-400">Tiada perbualan dijumpai.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>