document.addEventListener("DOMContentLoaded", function (event) {
    var socket = new WebSocket("ws://"+window.location.hostname +":2346");

    socket.onopen = function () {
        console.log("Соединение установлено.");
        document.getElementById("btnnewplayer").onclick = function (event) {
            socket.send("NewPlayer");
        };

        document.getElementById("btncall").onclick = function (event) {
            socket.send("call");
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
        if (data[0] === "NewPlayer") {
            document.getElementById("btns").classList.add("playerready");
            document.getElementById("btns").classList.remove("newplayer");
            document.getElementById("btncall").innerText = data[1];
        }
        if (data[0] === "call") {
            document.getElementById("btns").classList.add("callplayer");
        }
        if (data[0] === "clear") {
            document.getElementById("btns").classList.remove("callplayer");
        }
    };

    socket.onerror = function (error) {
        console.log("Ошибка " + error.message);
        document.getElementById("btns").classList.add("srverror");
    };

});

