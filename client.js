document.addEventListener("DOMContentLoaded", function (event) {
    var socket = new WebSocket("ws://"+window.location.hostname +":2346");
    var url = new URL(location.href);

    socket.onopen = function () {
        document.getElementById("btnnewplayer").onclick = function (event) {
            socket.send(JSON.stringify({"command":"NewPlayer","sessionId":url.searchParams.get("sid")}));
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
