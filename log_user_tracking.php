<!DOCTYPE html>
<html lang="en">
<script>
function sendTrackingData() {
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;
    const screenWidth = screen.width;
    const screenHeight = screen.height;
    const referrer = document.referrer;
    navigator.geolocation.getCurrentPosition(function(position) {
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;

        const trackingUrl = 'log_user_tracking.php?window_width=' + windowWidth +
                            '&window_height=' + windowHeight +
                            '&screen_width=' + screenWidth +
                            '&screen_height=' + screenHeight +
                            '&latitude=' + latitude +
                            '&longitude=' + longitude;

        fetch(trackingUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `referrer=${encodeURIComponent(referrer)}`
        })
        .then(response => response.json())
        .then(data => console.log(data))
        .catch(error => console.error('Error:', error));
    }, function() {
        const trackingUrl = 'log_user_tracking.php?window_width=' + windowWidth +
                            '&window_height=' + windowHeight +
                            '&screen_width=' + screenWidth +
                            '&screen_height=' + screenHeight;

        fetch(trackingUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `referrer=${encodeURIComponent(referrer)}`
        })
        .then(response => response.json())
        .then(data => console.log(data))
        .catch(error => console.error('Error:', error));
    });
}

window.onload = sendTrackingData;
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VEMi AI Chat</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="chat-container">
        <div id="chat-header">🤖 VEMi AI Chat</div>
        <div id="chat-box"></div>
        <div id="input-area">
            <button id="speech-btn">🎤</button> <!-- ✅ Voice-to-text button -->
            <input type="text" id="user-input" placeholder="Type a message...">
            <button id="send-btn">➡️</button>
        </div>
        <div id="chat-controls">
            <button id="end-chat-btn">End Chat</button> 
            <button id="reload-chat-btn">Reload Chat</button> <!-- ✅ Added Reload Button -->
        </div>
    </div>

    <!-- ✅ Load script AFTER the page is fully loaded -->
    <script src="js/script.js"></script>
</body>
</html>