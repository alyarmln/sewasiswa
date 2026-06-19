<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sewasiswa");

if (!$conn) {
    exit;
}

$my_id = $_SESSION['user_id'] ?? $_SESSION['owner_id'] ?? null;
$my_role = isset($_SESSION['user_id']) ? 'pelajar' : 'tuan_rumah';
$chat_with = mysqli_real_escape_string($conn, $_GET['chat_with'] ?? '');

if (!$my_id || !$chat_with) {
    exit;
}

// Menapis rekod sembang dua hala secara tepat mengikut penanda peranan (sender_id)
$query = "SELECT * FROM messages WHERE 
          (user_id = '$my_id' AND receiver_id = '$chat_with' AND sender_id = '$my_role') OR 
          (user_id = '$chat_with' AND receiver_id = '$my_id' AND sender_id != '$my_role') 
          ORDER BY timestamp ASC";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Semak adakah mesej ini dihantar oleh pengguna semasa atau pihak sebelah sana
        $is_me = ($row['user_id'] == $my_id && $row['sender_id'] == $my_role);
        $align = $is_me ? 'justify-end' : 'justify-start';
        $bg = $is_me ? 'bg-teal-600 text-white' : 'bg-white text-gray-800 border';

        echo "<div class='flex $align mb-2 w-full'>
                <div class='max-w-[75%] $bg p-3 rounded-lg shadow-sm'>
                    <p class='text-sm'>" . htmlspecialchars($row['message']) . "</p>
                    <small class='text-[10px] " . ($is_me ? 'text-teal-200' : 'text-gray-400') . " block text-right mt-1'>" . date('H:i', strtotime($row['timestamp'])) . "</small>
                </div>
              </div>";
    }
} else {
    echo '<div class="text-center text-gray-400 text-xs mt-10 uppercase tracking-widest">Mula bersembang sekarang...</div>';
}
?>