<?php
if (!empty($_GET['start'])) {
    shell_exec("php server.php start");
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Game</title>
        <script src="function.js"></script>
        <script src="main.js"></script>
        <link rel="stylesheet" href="main.css">
    </head>
    <body>
    <input type="button" value="NEXT" id="btngetvideo">
    <div class="srvbtn">
        <input type="button" value="Stop Server" id="btnstopsrv">
        <input type="button" value="Start Server" id="btnstartsrv">
        <input type="text" value="anime" id="inputquestion" aria-label="question">
    </div>

    <div class="players" id="players">
    </div>
    <div id="video">
        <iframe id="coubVideo" allow="autoplay"
                src="http://coub.com/embed/1k1efx?muted=false&autostart=true&originalSize=false&hideTopBar=true&startWithHD=false"
                allowfullscreen="true" frameborder="0" width="800" height="480"></iframe>
    </div>
    </body>
    </html>
    <?php
}
?>

