document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.getElementById('chat-box');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    const speechBtn = document.getElementById('speech-btn');
    const endChatBtn = document.getElementById('end-chat-btn');
    const reloadChatBtn = document.getElementById('reload-chat-btn');

    sendBtn.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
    endChatBtn.addEventListener('click', endChat);
    reloadChatBtn.addEventListener('click', reloadChat);

    function sendMessage() {
        const message = userInput.value.trim();
        if (message === '') return;

        // Display user message
        displayMessage('You', message);
        userInput.value = '';

        // Send message to backend
        fetch('ai-chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message })
        })
        .then(response => response.json())
        .then(data => {
            // Display bot response
            displayMessage('Bot', data.response);
        })
        .catch(error => {
            console.error('Error:', error);
            displayMessage('Bot', 'Sorry, something went wrong.');
        });
    }

    function displayMessage(sender, message) {
        const messageElement = document.createElement('div');
        messageElement.textContent = `${sender}: ${message}`;
        chatBox.appendChild(messageElement);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function endChat() {
        // Send end chat request to the backend
        fetch('ai-chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ end_chat: true })
        })
        .then(response => response.json())
        .then(data => {
            displayMessage('Bot', data.response);
        })
        .catch(error => {
            console.error('Error:', error);
            displayMessage('Bot', 'Sorry, something went wrong.');
        });

        chatBox.innerHTML = '';
    }

    function reloadChat() {
        // Send reset chat request to the backend
        fetch('ai-chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reset_chat: true })
        })
        .then(response => response.json())
        .then(data => {
            displayMessage('Bot', data.response);
        })
        .catch(error => {
            console.error('Error:', error);
            displayMessage('Bot', 'Sorry, something went wrong.');
        });

        location.reload();
    }

    // Voice-to-text functionality (optional)
    speechBtn.addEventListener('click', function() {
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'en-US';
        recognition.start();
        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            userInput.value = transcript;
            sendMessage();
        };
    });
});