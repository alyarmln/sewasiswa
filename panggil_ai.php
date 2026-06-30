<?php
header('Content-Type: application/json; charset=utf-8');

// 1. SAMBUNGAN PANGKALAN DATA SEWASISWA
// $conn = mysqli_connect("localhost", "root", "", "sewasiswa");
require_once "database.php";

if (!$conn) {
    echo json_encode(array("jawapan" => "Aduh, pangkalan data gagal disambung. Sila hubungi admin."), JSON_UNESCAPED_UNICODE);
    exit();
}

// 2. TERIMA MESEJ PELAJAR
$input = json_decode(file_get_contents('php://input'), true);
$user_message = isset($input['mesej']) ? trim($input['mesej']) : '';

if ($user_message == '') {
    echo json_encode(array("jawapan" => "Sila taip mesej anda terlebih dahulu."), JSON_UNESCAPED_UNICODE);
    exit();
}

// 3. AMBIL SEMUA DATA RUMAH DARI DATABASE
$query = "SELECT nama_rumah, hargaSewa FROM rumah";
$result = mysqli_query($conn, $query);
$senarai_rumah_db = "";

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $senarai_rumah_db .= "- " . $row['nama_rumah'] . " berkadar sewa RM" . $row['hargaSewa'] . "/bulan.\n";
    }
} else {
    $senarai_rumah_db = "- Tiada data rumah sewa aktif dalam sistem buat masa ini.\n";
}
mysqli_close($conn);

// 4. TETAPAN API KEY GEMINI (Kunci rasmi anda dari akaun Gmail peribadi)
// $gemini_api_key = "AQ.Ab8RN6LeKQGaiRMZIJHdifdXb-v_POCBwqhMvsrdCPnWIj8_rg"; 

// $gemini_api_key = getenv('GERMINI_API');
$gemini_api_key = $GERMINI_API;


// Arahan Sistem untuk membentuk personaliti chatbot
$system_instruction = "Anda adalah SewaSiswa AI, chatbot pintar untuk aplikasi carian rumah sewa pelajar UKM. 
Tugas anda adalah menjawab soalan pengguna dengan ramah, santai, dan menggunakan Bahasa Melayu gaya pelajar universiti yang mudah difahami.

Berikut adalah data SEBENAR rumah sewa langsung dari pangkalan data sistem kami. Anda WAJIB merujuk data ini jika pengguna bertanya tentang senarai rumah, cadangan, atau harga:
" . $senarai_rumah_db . "

Peraturan Jawapan:
1. Jika pengguna bertanya tentang rumah atau harga, berikan maklumat tepat mengikut data di atas. Jangan reka nama rumah yang tidak wujud.
2. Jika mereka bersembang kosong, layan dengan bijak dan mesra, kemudian halakan semula perbualan kepada urusan rumah sewa UKM.
3. Jawab secara ringkas, padat, dan gunakan format bold berkembar seperti **teks** untuk nama rumah atau harga.";

// 5. HUBUNGI GEMINI API (Menggunakan v1 yang stabil + model gemini-2.5-flash)
$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $gemini_api_key;

// Gabungkan arahan dan mesej pengguna ke dalam struktur payload yang paling asas dan selamat
$input_lengkap_ai = $system_instruction . "\n\n[Mesej Pelajar Semasa]: " . $user_message;

$data_payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $input_lengkap_ai]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// MELEPASI SEKATAN SSL LOCALHOST XAMPP
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

// 6. EKSTRAK RESPONS DAN BALAS
$jawapan_bot = "";

if ($curl_error) {
    $jawapan_bot = "🔴 Ralat Rangkaian (cURL): " . $curl_error;
} else {
    $result_api = json_decode($response, true);
    
    if (isset($result_api['candidates'][0]['content']['parts'][0]['text'])) {
        // JIKA BERJAYA: Ambil teks jawapan dari AI
        $jawapan_bot = $result_api['candidates'][0]['content']['parts'][0]['text'];
    } elseif (isset($result_api['error']['message'])) {
        // JIKA GOOGLE HANTAR RALAT: Paparkan ralat tersebut
        $jawapan_bot = "🔴 Ralat dari Google API: " . $result_api['error']['message'];
    } else {
        $jawapan_bot = "🔴 Ralat Struktur Respon atau Model tidak sepadan.";
    }
}

// Hantar JSON ke chatbot.php
echo json_encode(array("jawapan" => trim($jawapan_bot)), JSON_UNESCAPED_UNICODE);
?>