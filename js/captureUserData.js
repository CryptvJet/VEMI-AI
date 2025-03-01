function getUserData() {
    var userAgent = navigator.userAgent;
    var browserName = getBrowserName();
    var browserVersion = getBrowserVersion();
    var os = getOS();
    var screenWidth = screen.width;
    var screenHeight = screen.height;
    var windowWidth = window.innerWidth;
    var windowHeight = window.innerHeight;
    var referrer = document.referrer;
    var currentUrl = window.location.href;
    var ipAddress = ''; // This will be set on the server side

    // Function to get the user's IP address from an external service
    fetch('https://api.ipify.org?format=json')
        .then(response => response.json())
        .then(data => {
            ipAddress = data.ip;
            sendUserData();
        });

    function sendUserData() {
        var data = {
            user_agent: userAgent,
            browser_name: browserName,
            browser_version: browserVersion,
            os: os,
            window_width: windowWidth,
            window_height: windowHeight,
            screen_width: screenWidth,
            screen_height: screenHeight,
            referrer: referrer,
            current_url: currentUrl,
            ip_address: ipAddress
        };

        fetch('ai-chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'log_interaction', data: data })
        });
    }

    function getBrowserName() {
        if ((!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0) {
            return 'Opera';
        } else if (typeof InstallTrigger !== 'undefined') {
            return 'Firefox';
        } else if (Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0) {
            return 'Safari';
        } else if (!!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime)) {
            return 'Chrome';
        } else if (!!document.documentMode) {
            return 'IE';
        } else {
            return 'Unknown';
        }
    }

    function getBrowserVersion() {
        var ua = navigator.userAgent;
        var tem;
        var match = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
        if (/trident/i.test(match[1])) {
            tem = /\brv[ :]+(\d+)/g.exec(ua) || [];
            return tem[1] || '';
        }
        if (match[1] === 'Chrome') {
            tem = ua.match(/\b(OPR|Edge)\/(\d+)/);
            if (tem !== null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
        }
        match = match[2] ? [match[1], match[2]] : [navigator.appName, navigator.appVersion, '-?'];
        if ((tem = ua.match(/version\/(\d+)/i)) !== null) match.splice(1, 1, tem[1]);
        return match.join(' ');
    }

    function getOS() {
        var userAgent = navigator.userAgent || navigator.vendor || window.opera;
        if (/windows phone/i.test(userAgent)) {
            return 'Windows Phone';
        }
        if (/android/i.test(userAgent)) {
            return 'Android';
        }
        if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
            return 'iOS';
        }
        if (/Macintosh/.test(userAgent)) {
            return 'MacOS';
        }
        if (/Windows/.test(userAgent)) {
            return 'Windows';
        }
        return 'Unknown';
    }
}

window.onload = getUserData;