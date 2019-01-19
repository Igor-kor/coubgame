document.addEventListener("DOMContentLoaded", function (event) {

    var socket = new WebSocket("ws://" + window.location.hostname + ":2346");

    socket.onopen = function () {
        socket.send(JSON.stringify({"command": "imindex"}));
        document.getElementById("btngetvideo").onclick = function (event) {
            var question = document.getElementById('inputquestion').value;
            socket.send(JSON.stringify({"command": "getVideo", "question": question}));
            socket.send(JSON.stringify({"command": "clearCall"}));
            clearCall();
        };
        document.getElementById("btnstopsrv").onclick = function (event) {
            socket.send(JSON.stringify({"command": "stopsrv"}));
        };
        document.getElementById("btnshowlink").onclick = function (event) {
            var qrcode = document.getElementById('qrcode');
            qrcode.hidden = !qrcode.hidden;
        };
        document.getElementById("btngetvideo").classList.remove("disconnect");
    };

    document.getElementById("btnstartsrv").onclick = function (event) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', window.location.href + '?start=1', true);
        xhr.send();
        window.location.reload();
    };

    socket.onclose = function (event) {
        if (event.wasClean) {
            console.log('Соединение закрыто чисто');
        } else {
            console.log('Обрыв соединения'); // например, "убит" процесс сервера
        }
        console.log('Код: ' + event.code + ' причина: ' + event.reason);
        document.getElementById("btngetvideo").classList.add("disconnect");
    };

    socket.onmessage = function (event) {
        var data = JSON.parse(event.data);
        switch (data.command) {
            case "NewPlayer":
                drawPlayer(data.id);
                break;
            case "call":
                hostdrawPlayerCall(data.id);
                break;
            case "ResponseVideo":
                drawVideo(data.data.permalink);
                break;
            case "close":
                closeClient(data.id);
                break;
            case "CurrentSession":
                document.getElementById("qrcode").innerHTML = data.qrcode;
                document.getElementById("qrcode").innerHTML += "<a target='_blank' href ='" + data.link + "'>" + data.link + "</a>";
                break;
            case "clear":
                clearCall();
                break;
            default:
                console.error("undefined command: " + data.command);
                break;
        }
    };

    socket.onerror = function (error) {
        console.log("Ошибка " + error.message);
    };

});

