document.addEventListener("DOMContentLoaded", function (event) {
    var socket = new WebSocket("ws://"+window.location.hostname +":2346");

    socket.onopen = function () {
        document.getElementById("btnnewplayer").onclick = function (event) {
            socket.send(JSON.stringify({"command":"NewPlayer","sessionId":getAllUrlParams().sid}));
        };
        document.getElementById("btncall").onclick = function (event) {
            socket.send(JSON.stringify({"command":"call"}));
        };
        document.getElementById("btns").classList.remove("srverror");
    };

    socket.onclose = function (event) {
        if (event.wasClean) {
            console.log('Соединение закрыто чисто');
        } else {
            console.log('Обрыв соединения'); // например, "убит" процесс сервера
        }
        console.log('Код: ' + event.code + ' причина: ' + event.reason);
        document.getElementById("btns").classList.add("srverror");
    };

    socket.onmessage = function (event) {
        var data = JSON.parse(event.data) ;
        switch (data['command']) {
            case "NewPlayer":
                document.getElementById("btns").classList.add("playerready");
                document.getElementById("btns").classList.remove("newplayer");
                document.getElementById("btncall").innerText = data['id'];
                break;
            case "call":
                document.getElementById("btns").classList.add("callplayer");
                break;
            case "clear":
                document.getElementById("btns").classList.remove("callplayer");
                break;
            default:
                console.error("undefined command: "+data['command']);
                break;
        }
    };

    socket.onerror = function (error) {
        console.log("Ошибка " + error.message);
        document.getElementById("btns").classList.add("srverror");
    };

});


function getAllUrlParams(url) {

    // get query string from url (optional) or window
    var queryString = url ? url.split('?')[1] : window.location.search.slice(1);

    // we'll store the parameters here
    var obj = {};

    // if query string exists
    if (queryString) {

        // stuff after # is not part of query string, so get rid of it
        queryString = queryString.split('#')[0];

        // split our query string into its component parts
        var arr = queryString.split('&');

        for (var i = 0; i < arr.length; i++) {
            // separate the keys and the values
            var a = arr[i].split('=');

            // set parameter name and value (use 'true' if empty)
            var paramName = a[0];
            var paramValue = typeof (a[1]) === 'undefined' ? true : a[1];

            // (optional) keep case consistent
            paramName = paramName.toLowerCase();
            if (typeof paramValue === 'string') paramValue = paramValue.toLowerCase();

            // if the paramName ends with square brackets, e.g. colors[] or colors[2]
            if (paramName.match(/\[(\d+)?\]$/)) {

                // create key if it doesn't exist
                var key = paramName.replace(/\[(\d+)?\]/, '');
                if (!obj[key]) obj[key] = [];

                // if it's an indexed array e.g. colors[2]
                if (paramName.match(/\[\d+\]$/)) {
                    // get the index value and add the entry at the appropriate position
                    var index = /\[(\d+)\]/.exec(paramName)[1];
                    obj[key][index] = paramValue;
                } else {
                    // otherwise add the value to the end of the array
                    obj[key].push(paramValue);
                }
            } else {
                // we're dealing with a string
                if (!ob