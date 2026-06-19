<?php
session_start();

// 1. Sambungan ke Database
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

// 2. KESAN SESI (Siapa yang sedang buka halaman chat sekarang)
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $my_id = $_SESSION['user_id'];
    $my_role = 'pelajar';
} elseif (isset($_SESSION['owner_id']) && !empty($_SESSION['owner_id'])) {
    $my_id = $_SESSION['owner_id'];
    $my_role = 'tuan_rumah';
} else {
    header("Location: loginmasuk.php");
    exit();
}

// 3. TENTUKAN TARGET SASARAN (Siapa orang yang kita tengah chat)
$receiver_id = mysqli_real_escape_string($conn, $_GET['id'] ?? '');
$url_role = $_GET['role'] ?? '';

if ($url_role === 'tuan_rumah') {
    // Jika dipaksa dari URL mencari tuan rumah
    $target_table = 'tuan_rumah';
    $label_penerima = 'Tuan Rumah';
} elseif ($url_role === 'pelajar') {
    // Jika dipaksa dari URL mencari pelajar
    $target_table = 'pelajar';
    $label_penerima = 'Pelajar';
} else {
    // Logik sandaran (Fallback) sekiranya tiada parameter &role dalam URL
    $target_table = ($my_role === 'pelajar') ? 'tuan_rumah' : 'pelajar';
    $label_penerima = ($my_role === 'pelajar') ? 'Tuan Rumah' : 'Pelajar';
}

// 4. AMBIL DATA NAMA PENERIMA DARI DATABASE
$res_user = mysqli_query($conn, "SELECT nama FROM $target_table WHERE id = '$receiver_id'");
$user_data = mysqli_fetch_assoc($res_user);

if ($user_data) {
    $nama_penerima = $user_data['nama'];
} else {
    $nama_penerima = "Pengguna #$receiver_id (Tiada ID dalam jadual $target_table)";
}

// 5. SEMAK RUJUKAN RUMAH (JIKA ADA)
$rumah_ditanya = null;
$rumah_id_param = '';
if (isset($_GET['rumah_id']) && !empty($_GET['rumah_id'])) {
    $rumah_id = mysqli_real_escape_string($conn, $_GET['rumah_id']);
    $rumah_id_param = htmlspecialchars($rumah_id);
    
    $query_rumah = mysqli_query($conn, "SELECT * FROM rumah WHERE id = '$rumah_id'");
    if ($query_rumah && mysqli_num_rows($query_rumah) > 0) {
        $rumah_ditanya = mysqli_fetch_assoc($query_rumah);
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo htmlspecialchars($nama_penerima); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 h-screen flex flex-col">

    <header class="p-4 bg-white shadow flex items-center border-b sticky top-0 z-10">
        <a href="<?php echo ($my_role == 'pelajar') ? 'dashboard_pelajar.php' : 'senarai_chat_owner.php'; ?>" class="mr-4 text-teal-600 hover:text-teal-800 font-bold text-xl transition-transform hover:-translate-x-1">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="font-bold text-gray-800 text-base leading-tight"><?php echo htmlspecialchars($nama_penerima); ?></h1>
            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold"><?php echo $label_penerima; ?></p>
        </div>
    </header>

    <?php if ($rumah_ditanya): ?>
        <div class="bg-white border-b border-teal-100 px-4 py-3 flex items-center gap-3 shadow-sm">
            <?php 
                $gambar_arr = explode(',', $rumah_ditanya['gambar']);
                $img_utama = !empty($gambar_arr[0]) ? "uploads/".trim($gambar_arr[0]) : "placeholder.jpg";
            ?>
            <img src="<?php echo $img_utama; ?>" class="w-12 h-12 object-cover rounded-xl border border-teal-100 shadow-inner flex-shrink-0">
            <div class="flex-grow min-w-0">
                <span class="inline-block text-[8px] bg-teal-600 text-white font-bold uppercase px-2 py-0.5 rounded tracking-wider mb-0.5">Rujukan Rumah</span>
                <h4 class="text-xs font-bold text-teal-900 truncate">
                    <?php echo htmlspecialchars($rumah_ditanya['nama_rumah']); ?>
                </h4>
                <p class="text-xs font-extrabold text-teal-600">RM<?php echo number_format($rumah_ditanya['hargaSewa'], 0); ?> / bulan</p>
            </div>
            <div class="hidden sm:block text-right">
                <span class="inline-block text-[9px] bg-gray-100 text-gray-500 font-medium px-2 py-1 rounded-lg">
                    ID Rumah: #<?php echo $rumah_ditanya['id']; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <div id="chat-box" class="flex-grow p-4 overflow-y-auto space-y-4 bg-[#f4f6f9] flex flex-col"></div>

    <footer class="p-4 bg-white border-t sticky bottom-0">
        <div class="max-w-4xl mx-auto flex space-x-3">
            <input type="text" id="message-input" class="flex-grow border border-gray-200 rounded-full px-5 py-3 outline-none text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all placeholder:text-gray-400" placeholder="Taip mesej pertanyaan anda di sini...">
            <button id="send-btn" class="bg-teal-600 text-white px-6 py-3 rounded-full font-bold text-sm shadow-md hover:bg-teal-700 active:scale-95 transition-all flex items-center gap-2">
                <span>Hantar</span> <i class="fas fa-paper-plane text-xs"></i>
            </button>
        </div>
    </footer>

    <script>
        function loadMessages() {
            $.get('fetchmessage.php', { 
                chat_with: '<?php echo $receiver_id; ?>',
                rumah_id: '<?php echo $rumah_id_param; ?>'
            }, function(data) {
                $('#chat-box').html(data);
            });
        }

        $('#send-btn').off('click').on('click', function() {
            let msg = $('#message-input').val().trim();
            if(msg === "") return;

            $.post('send_message.php', {
                message: msg,
                receiver_id: '<?php echo $receiver_id; ?>',
                sender_role: '<?php echo $my_role; ?>',
                rumah_id: '<?php echo $rumah_id_param; ?>'
            }, function(response) {
                if(response.trim() === "Success") {
                    $('#message-input').val('');
                    loadMessages();
                    setTimeout(function() {
                        var box = document.getElementById("chat-box");
                        if(box) box.scrollTop = box.scrollHeight;
                    }, 100);
                } else {
                    alert("Gagal menghantar: " + response);
                }
            });
        });

        $('#message-input').keypress(function(e) {
            if(e.which == 13) {
                $('#send-btn').click();
            }
        });

        setInterval(loadMessages, 2500);
        
        $(document).ready(function() {
            loadMessages();
            setTimeout(function() {
                var box = document.getElementById("chat-box");
                if (box) box.scrollTop = box.scrollHeight;
            }, 500);
        });
    </script>
</body>
</html>