function drawPlayer(IdPlayer) {
    var players = document.getElementById('players');
    players.innerHTML = players.innerHTML + "<div class = 'player' id = 'player-" + IdPlayer + "'>" +
        "<h1>" + IdPlayer + "</h1></div>";
}

function drawPlayerCall(IdPlayer) {
    var player = document.getElementById('player-' + IdPlayer);
    player.classList.add("call");
}

function hostdrawPlayerCall(IdPlayer) {
    var player = document.getElementById('player-' + IdPlayer);
    player.classList.add("call");
    var myCoub = document.getElementById('coubVideo').contentWindow;
    myCoub.postMessage('stop', '*');
}

function clearCall() {
    var players = document.getElementsByClassName('player');
    Array.prototype.forEach.call(players, function (item, i, arr) {
        item.classList.remove("call");
    });
}

function drawVideo(idvideo) {
    var video = document.getElementById('coubVideo');
    video.src = "http://coub.com/embed/" + idvideo + "?muted=false&autostart=true&originalSize=false&hideTopBar=true&startWithHD=false" ;
    video.addEventListener('load', function () {
        document.getElementById('coubVideo').contentWindow.postMessage('play', '*');
    });

}

function closeClient(IdPlayer) {
    var player = document.getElementById('player-' + IdPlayer);
    player.remove();
}