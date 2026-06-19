<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if (!$conn) {
    die("Sambungan database gagal: " . mysqli_connect_error());
}

// 1. SEMAK SESSION TUAN RUMAH
$owner_id = $_SESSION['owner_id'] ?? $_SESSION['id'] ?? null;

if (!$owner_id) {
    die("<script>alert('Sesi tamat. Sila log masuk sebagai Tuan Rumah.'); window.location='login_owner.php';</script>");
}

// 2. QUERY AMBIL SENARAI PELAJAR
$query = "SELECT DISTINCT p.id, p.nama 
          FROM pelajar p
          INNER JOIN messages m ON (p.id = m.user_id OR p.id = m.receiver_id)
          WHERE (m.receiver_id = '$owner_id' AND m.sender_id = 'pelajar') 
             OR (m.user_id = '$owner_id' AND m.sender_id = 'tuan_rumah')";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Inbox Pelajar - SewaSiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-6">
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="p-4 bg-indigo-600 text-white font-bold text-lg flex justify-between items-center">
            <span>Mesej Pelajar</span>
            <a href="dashboard_owner.php" class="text-sm bg-indigo-500 px-3 py-1 rounded">Kembali</a>
        </div>
        <div class="divide-y">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <!-- Ditambah &role=pelajar supaya ruangchat tahu ini adalah profil pelajar -->
                <a href="ruangchat.php?id=<?php echo $row['id']; ?>&role=pelajar" class="flex items-center p-4 hover:bg-indigo-50 transition">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3 font-bold">
                        <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800"><?php echo htmlspecialchars($row['nama']); ?></h3>
                        <p class="text-xs text-slate-400">Klik untuk balas mesej</p>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-10 text-center text-slate-400">Tiada mesej daripada pelajar.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>