document.addEventListener("DOMContentLoaded", function (event) {

    var socket = new WebSocket("ws://"+window.location.hostname +":2346");

    socket.onopen = function () {
        socket.send(JSON.stringify({"command":"imindex"}));
        document.getElementById("btngetvideo").onclick = function (event) {
            var question = document.getElementById('inputquestion').value;
            socket.send(JSON.stringify({"command":"getVideo","question":question}));
            socket.send(JSON.stringify({"command":"clearCall"}));
            clearCall();
        };
        document.getElementById("btnstopsrv").onclick = function (event) {
            socket.send(JSON.stringify({"command":"stopsrv"}));
        };
        document.getElementById("btngetvideo").classList.remove("disconnect");
    };
    document.getElementById("btnstartsrv").onclick = function (event) {
        var xhr = new XMLHttpRequest();
        console.log(window.location);
        xhr.open('GET', window.location.href+'?start=1', true);
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
        var data = JSON.parse(event.data) ;
        if (data[0] === "NewPlayer") {
            drawPlayer(data[1]);
        }
        if (data[0] === "call") {
            hostdrawPlayerCall(data[1]);
        }
        if (data['flag'] === null) {
             drawVideo(data['permalink']);
        }
        if (data[0] === "close") {
            closeClient(data[1]);
        }
    };

    socket.onerror = function (error) {
        console.log("Ошибка " + error.message);
    };

});

