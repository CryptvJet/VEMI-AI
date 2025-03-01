document.addEventListener("DOMContentLoaded", function () {
    const chatBox = document.getElementById("chat-box");
    const userInput = document.getElementById("user-input");
    const sendBtn = document.getElementById("send-btn");
    const speechBtn = document.getElementById("speech-btn"); // ✅ Voice-to-Text Button
    const endChatBtn = document.getElementById("end-chat-btn");
    const reloadChatBtn = document.getElementById("reload-chat-btn");

    let helpTimeout;

    function sendMessage() {
        const message = userInput.value.trim();
        if (message === "") return;

        appendMessage("user", "You: " + message);
        userInput.value = "";

        clearTimeout(helpTimeout);

        fetch("ai-chat.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "message=" + encodeURIComponent(message),
        })
        .then(response => response.json())
        .then(data => {
            appendMessage("bot", data.response);
            helpTimeout = setTimeout(() => appendMessage("bot", "How can I help you?"), 12000);
        })
        .catch(error => console.error("❌ Fetch Error:", error));
    }

    function endChat() {
        fetch("ai-chat.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "end_chat=true",
        })
        .then(response => response.json())
        .then(data => appendMessage("bot", data.response))
        .catch(error => console.error("❌ Fetch Error:", error));
    }

    function reloadChat() {
        fetch("ai-chat.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "reset_chat=true",
        })
        .then(response => response.json())
        .then(data => {
            appendMessage("bot", data.response);
            setTimeout(() => location.reload(true), 1000);
        })
        .catch(error => console.error("❌ Fetch Error:", error));
    }

    function startSpeechRecognition() {
        if (!('webkitSpeechRecognition' in window)) {
            alert("Your browser does not support Speech Recognition.");
            return;
        }

        const recognition = new webkitSpeechRecognition();
        recognition.lang = "en-US";
        recognition.continuous = false;
        recognition.interimResults = false;

        // ✅ UI Feedback: Change button color & show "Listening..."
        speechBtn.style.backgroundColor = "red";
        speechBtn.innerText = "🎙️ Listening...";
        appendMessage("bot", "🎙️ Listening...");

        recognition.start();

        recognition.onresult = function (event) {
            const transcript = event.results[0][0].transcript;
            userInput.value = transcript; // ✅ Auto-fill text box with voice input
            sendMessage(); // ✅ Auto-send after voice input
        };

        recognition.onerror = function (event) {
            console.error("Speech recognition error:", event.error);
            appendMessage("bot", "❌ Voice input failed. Try again.");
        };

        recognition.onend = function () {
            console.log("Speech recognition ended.");
            // ✅ Reset button back to normal
            speechBtn.style.backgroundColor = "";
            speechBtn.innerText = "🎤";
        };
    }

    function appendMessage(sender, message) {
        const msgDiv = document.createElement("div");
        msgDiv.classList.add(sender);
        msgDiv.innerText = message;
        chatBox.appendChild(msgDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    fetch("ai-chat.php?init_chat=true")
        .then(response => response.json())
        .then(data => {
            if (data.greeting) appendMessage("bot", data.response);
        })
        .catch(error => console.error("❌ Fetch Error:", error));

    sendBtn.addEventListener("click", sendMessage);
    userInput.addEventListener("keypress", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            sendMessage();
        }
    });
    endChatBtn.addEventListener("click", endChat);
    reloadChatBtn.addEventListener("click", reloadChat);
    speechBtn.addEventListener("click", startSpeechRecognition); // ✅ Voice Button Click Event
});
