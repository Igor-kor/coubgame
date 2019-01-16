document.addEventListener("DOMContentLoaded", function (event) {
    var socket = new WebSocket("ws://192.168.137.1:2346");

    socket.onopen = function () {
        console.log("Соединение установлено.");
        document.getElementById("btnnewplayer").onclick = function (event) {
            socket.send("NewPlayer");
        };

        document.getElementById("btncall").onclick = function (event) {
            socket.send("call");
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
        console.log(event.data);
        var data = JSON.parse(event.data) ;
        if (data[0] === "NewPlayer") {
            drawPlayer(data[1]);
            document.getElementById("btnnewplayer").remove();
        }
        if (data[0] === "call") {
            drawPlayerCall(data[1]);
        }
        if (data[0] === "clear") {
            clearCall();
        }
    };

    socket.onerror = function (error) {
        console.log("Ошибка " + error.message);
    };


});

