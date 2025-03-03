function getUserData() {
    var browserVersion = getBrowserVersion();

    console.log("Captured browser version:", browserVersion);

    sendUserData();

    function sendUserData() {
        var data = {
            browser_version: browserVersion
        };

        console.log("Sending user data:", data);

        fetch('ai-chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'log_interaction', data: data })
        }).then(response => response.json())
          .then(data => console.log("Response from server:", data))
          .catch(error => console.error("Error logging user data:", error));
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
}

window.onload = getUserData;