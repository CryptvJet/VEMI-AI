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

    sendUserData();

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
            current_url: currentUrl
        };

        fetch('ai-chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'log_interaction', data: data })
        }).then(response => response.json())
          .then(data => console.log(data));
    }

    function getBrowserName() {
        var userAgent = navigator.userAgent;
        if (userAgent.indexOf("Opera") != -1 || userAgent.indexOf('OPR') != -1) return "Opera";
        else if (userAgent.indexOf("Chrome") != -1) return "Chrome";
        else if (userAgent.indexOf("Safari") != -1) return "Safari";
        else if (userAgent.indexOf("Firefox") != -1) return "Firefox";
        else if (userAgent.indexOf("MSIE") != -1 || !!document.documentMode == true) return "IE";
        else return "Unknown";
    }

    function getBrowserVersion() {
        var userAgent = navigator.userAgent;
        var match = userAgent.match(/(firefox|msie|chrome|safari|opr|trident(?=\/))\/?\s*(\d+)/i) || [];
        if (/trident/i.test(match[1])) {
            var tem = /\brv[ :]+(\d+)/g.exec(userAgent) || [];
            return (tem[1] || "");
        }
        if (match[1] === 'Chrome') {
            var tem = userAgent.match(/\b(OPR|Edge)\/(\d+)/);
            if (tem != null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
        }
        match = match[2] ? [match[1], match[2]] : [navigator.appName, navigator.appVersion, '-?'];
        if ((tem = userAgent.match(/version\/(\d+)/i)) != null) match.splice(1, 1, tem[1]);
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