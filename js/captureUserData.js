function getUserData() {
    const userData = {
        userAgent: navigator.userAgent,
        browserName: getBrowserName(),
        browserVersion: getBrowserVersion(),
        os: getOS(),
        windowWidth: window.innerWidth,
        windowHeight: window.innerHeight,
        screenWidth: window.screen.width,
        screenHeight: window.screen.height,
        referrer: document.referrer,
        currentUrl: window.location.href
    };

    // Optionally capture geolocation if user consents
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            userData.latitude = position.coords.latitude;
            userData.longitude = position.coords.longitude;
            sendUserData(userData);
        }, () => {
            sendUserData(userData);
        });
    } else {
        sendUserData(userData);
    }
}

function getBrowserName() {
    if ((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) != -1) {
        return 'Opera';
    } else if (navigator.userAgent.indexOf("Chrome") != -1) {
        return 'Chrome';
    } else if (navigator.userAgent.indexOf("Safari") != -1) {
        return 'Safari';
    } else if (navigator.userAgent.indexOf("Firefox") != -1) {
        return 'Firefox';
    } else if ((navigator.userAgent.indexOf("MSIE") != -1) || (!!document.documentMode == true)) {
        return 'IE';
    } else {
        return 'Unknown';
    }
}

function getBrowserVersion() {
    const userAgent = navigator.userAgent;
    let browserVersion = 'Unknown';
    const browserName = getBrowserName();

    if (browserName === 'Chrome') {
        const match = userAgent.match(/Chrome\/([\d.]+)/);
        if (match) {
            browserVersion = match[1];
        }
    } else if (browserName === 'Firefox') {
        const match = userAgent.match(/Firefox\/([\d.]+)/);
        if (match) {
            browserVersion = match[1];
        }
    } else if (browserName === 'Safari') {
        const match = userAgent.match(/Version\/([\d.]+)/);
        if (match) {
            browserVersion = match[1];
        }
    } else if (browserName === 'Opera') {
        const match = userAgent.match(/Opera\/([\d.]+)/);
        if (match) {
            browserVersion = match[1];
        }
    } else if (browserName === 'IE') {
        const match = userAgent.match(/MSIE\s([\d.]+)/);
        if (match) {
            browserVersion = match[1];
        }
    }

    return browserVersion;
}

function getOS() {
    const userAgent = navigator.userAgent;
    if (userAgent.indexOf("Win") != -1) return "Windows";
    if (userAgent.indexOf("Mac") != -1) return "MacOS";
    if (userAgent.indexOf("X11") != -1) return "UNIX";
    if (userAgent.indexOf("Linux") != -1) return "Linux";
    return "Unknown";
}

function sendUserData(userData) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/track_user_data.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.send(JSON.stringify(userData));
}

// Call getUserData when the page loads
window.onload = getUserData;