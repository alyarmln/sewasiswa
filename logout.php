<?php
// 1. Mulakan sesi untuk mencapai data sesi sedia ada
session_start();

// 2. Kosongkan semua pembolehubah sesi (nama, id, peranan)
$_SESSION = array();

// 3. Jika sesi menggunakan kuki (cookies), padamkan kuki sesi tersebut
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Musnahkan sesi sepenuhnya dari pelayan
session_destroy();

// 5. Hala semula pengguna ke halaman log masuk utama (index.php atau login_owner.php)
// Anda boleh tukar ke 'login_owner.php' jika mahu khusus untuk tuan rumah
header("Location: login.php");
exit();
?>