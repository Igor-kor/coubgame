document.addEventListener("DOMContentLoaded", function (event) {

    var socket = new WebSocket("ws://"+window.location.hostname +":2346");

    socket.onopen = function () {
        console.log("Соединение установлено.");
        socket.send("imindex");
        document.getElementById("btngetvideo").onclick = function (event) {
            socket.send("getVideo");
            socket.send("clearCall");
            clearCall();
        };
    };

    socket.onclose = function (event) {
        if (event.wasClean) {
            console.log('Соединение закрыто чисто');
        } else {
            console.log('Обрыв соединения'); // например, "убит" процесс сервера
        }
        console.log('Код: ' + event.code + ' причина: ' + event.reason);
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
        if (data['close'] === null) {
            closeClient(data[1]);
        }
    };

    socket.onerror = function (error) {
        console.log("Ошибка " + error.message);
    };

});

