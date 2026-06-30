<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaSiswa - AI Assistant</title>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script src="https://cdn.tailwindcss.com"></script><meta name="google-signin-client_id" content="85903086524-sdd23qq381911tdf7jv0baarhlk244om.apps.googleusercontent.com.apps.googleusercontent.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-[2rem] shadow-2xl border border-slate-100 overflow-hidden flex flex-col h-[600px]">
        
        <div class="bg-gradient-to-r from-teal-600 to-teal-700 p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-robot text-lg text-amber-300"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm tracking-wide">SewaSiswa AI</h3>
                    <p class="text-[10px] text-teal-100 flex items-center">
                        <span class="w-2 h-2 bg-green-400 rounded-full inline-block mr-1.5 animate-pulse"></span> Sedia Membantu
                    </p>
                </div>
            </div>
            <a href="dashboard_pelajar.php" class="text-xs bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-full transition">Kembali</a>
        </div>

        <div id="chat-box" class="flex-grow p-5 overflow-y-auto space-y-4 bg-slate-50/50">
            <div class="flex items-start space-x-2">
                <div class="w-7 h-7 bg-teal-600 text-white rounded-full flex items-center justify-center text-xs shrink-0">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white text-slate-800 p-3 rounded-2xl rounded-tl-none shadow-sm text-xs max-w-[80%] border border-slate-100">
                    Hai! Saya pembantu AI SewaSiswa UKM. Ada apa-apa yang boleh saya bantu tentang carian rumah sewa anda hari ini? 🏠✨
                </div>
            </div>
        </div>

        <div class="px-4 py-2 bg-white border-t border-slate-50 flex flex-wrap gap-2 justify-center">
            <button type="button" onclick="hantarCadangan('Mana rumah paling murah?')" 
                class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-full transition transform hover:scale-105 active:scale-95 shadow-sm">
                💰 Rumah Paling Murah
            </button>
            <button type="button" onclick="hantarCadangan('Ada tak rumah sewa yang dekat dengan UKM?')" 
                class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-full transition transform hover:scale-105 active:scale-95 shadow-sm">
                📍 Rumah Dekat UKM
            </button>
            <button type="button" onclick="hantarCadangan('Beritahu saya butiran tentang Condo Vista Bangi')" 
                class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-full transition transform hover:scale-105 active:scale-95 shadow-sm">
                🏊 Condo Vista Bangi
            </button>
        </div>

        <form id="chat-form" class="p-4 bg-white border-t border-slate-100 flex items-center space-x-2">
            <input type="text" id="user-input" required placeholder="Taip soalan anda di sini..." 
                class="flex-grow bg-slate-100 p-3.5 rounded-xl text-xs font-medium outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white w-11 h-11 rounded-xl flex items-center justify-center shadow-md transition transform active:scale-95 shrink-0">
                <i class="fas fa-paper-plane text-xs"></i>
            </button>
        </form>
    </div>

    <script>
        const chatForm = document.getElementById('chat-form');
        const userInput = document.getElementById('user-input');
        const chatBox = document.getElementById('chat-box');

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const messageText = userInput.value.trim();
            if(!messageText) return;

            // 1. Paparkan mesej pelajar ke skrin (Sebelah Kanan)
            appendMessage(messageText, 'user');
            userInput.value = ''; // Kosongkan kotak input
            
            // 2. Paparkan animasi loading pembantu AI
            const loadingId = appendMessage('<i class="fas fa-ellipsis-h animate-bounce"></i> Mengetik...', 'ai');

            try {
                // 3. Ambil respons menggunakan FETCH API dari panggil_ai.php
                const response = await fetch('panggil_ai.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mesej: messageText })
                });
                
                if (!response.ok) {
                    throw new Error('Respons pelayan gagal: ' + response.status);
                }

                const textOutput = await response.text();
                
                try {
                    const data = JSON.parse(textOutput);
                    if (data && data.jawapan) {
                        // Memproses format markdown tebal dan penukaran baris baharu (\n -> <br>)
                        let formattedText = formatTeksBold(data.jawapan).replace(/\n/g, '<br>');
                        document.getElementById(loadingId).innerHTML = formattedText;
                    } else {
                        document.getElementById(loadingId).innerText = 'SewaSiswa AI: Sistem sedia menerima carian anda. Sila cuba lagi sekejap ya.';
                    }
                } catch (jsonError) {
                    console.error("Format respons bukan JSON tulen:", textOutput);
                    if (textOutput.trim() !== "") {
                        document.getElementById(loadingId).innerHTML = formatTeksBold(textOutput).replace(/\n/g, '<br>');
                    } else {
                        document.getElementById(loadingId).innerText = 'Hai! Sistem kecerdasan buatan sedang dikemas kini. Sila nyatakan semula soalan anda.';
                    }
                }

            } catch (error) {
                console.error("Ralat Sambungan Rangkaian:", error);
                document.getElementById(loadingId).innerHTML = "Selamat datang ke SewaSiswa UKM! 👋<br><br>Sistem kami mengesyorkan beberapa lokasi berhampiran:<br>• <strong>Condo Vista Bangi</strong><br>• <strong>Taman Evergreen</strong><br><br>Sila cuba hantar semula soalan anda seketika lagi.";
            }
            
            // Sentiasa tatal ke bawah secara automatik apabila mesej baharu masuk
            chatBox.scrollTop = chatBox.scrollHeight;
        });

        // Pengendali klik pada cip cadangan pantas
        function hantarCadangan(teksButang) {
            if(userInput) {
                userInput.value = teksButang;
                const eventSubmit = new Event('submit', { cancelable: true });
                chatForm.dispatchEvent(eventSubmit);
            }
        }

        // Fungsi regex untuk menukar sintaks **teks** Markdown kepada elemen HTML <strong> secara selamat
        function formatTeksBold(str) {
            if (typeof str !== 'string') return '';
            return str.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        }

        // Fungsi menambah elemen kotak mesej ke dalam DOM chat box
        function appendMessage(text, sender) {
            const id = 'msg-' + Date.now();
            const msgDiv = document.createElement('div');
            msgDiv.className = `flex items-start space-x-2 ${sender === 'user' ? 'justify-end space-x-reverse' : ''}`;
            
            msgDiv.innerHTML = sender === 'user' ? `
                <div class="bg-teal-600 text-white p-3 rounded-2xl rounded-tr-none shadow-sm text-xs max-w-[80%]">
                    ${text}
                </div>
            ` : `
                <div class="w-7 h-7 bg-teal-600 text-white rounded-full flex items-center justify-center text-xs shrink-0">
                    <i class="fas fa-robot"></i>
                </div>
                <div id="${id}" class="bg-white text-slate-800 p-3 rounded-2xl rounded-tl-none shadow-sm text-xs max-w-[80%] border border-slate-100">
                    ${text}
                </div>
            `;
            
            chatBox.appendChild(msgDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
            return id;
        }
    </script>
</body>
</html>