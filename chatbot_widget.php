<div id="chatbot-bubble" onclick="toggleChatbot()" class="fixed bottom-6 right-6 bg-teal-600 hover:bg-teal-700 text-white w-14 h-14 rounded-full shadow-2xl flex items-center justify-center cursor-pointer transition-all duration-300 transform hover:scale-110 z-50">
    <i class="fas fa-comment-dots text-2xl animate-pulse"></i>
</div>

<div id="chatbot-box" class="fixed bottom-24 right-6 w-[400px] h-[600px] bg-transparent rounded-2xl shadow-2xl z-50 hidden transition-all duration-300">
    <iframe src="chatbot.php" class="w-full h-full rounded-2xl border border-teal-100" style="background: transparent;"></iframe>
</div>

<script>
function toggleChatbot() {
    var chatBox = document.getElementById('chatbot-box');
    var chatBubble = document.getElementById('chatbot-bubble');
    
    if (chatBox.classList.contains('hidden')) {
        chatBox.classList.remove('hidden');
        // Tukar ikon kepada tanda pangkah (X) apabila dibuka
        chatBubble.innerHTML = '<i class="fas fa-times text-2xl"></i>';
    } else {
        chatBox.classList.add('hidden');
        // Tukar semula kepada ikon chat apabila ditutup
        chatBubble.innerHTML = '<i class="fas fa-comment-dots text-2xl"></i>';
    }
}
</script>