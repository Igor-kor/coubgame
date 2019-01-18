<?php
/**
 * Created by PhpStorm.
 * User: игорь
 * Date: 15.01.2019
 * Time: 20:45
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/settings.php';

use Workerman\Worker;
use Clients\Clients;

/**
 * @param string $question
 * @return false|string
 */
function getVideo($question = "anime")
{
    if (empty($question)) {
        $question = "anime";
    }
    static $page = 1;
    static $total = 0;
    static $oldquestion = 'anime';
    if ($total == 0 || $oldquestion != $question) {
        $response = file_get_contents("https://coub.com/api/v2/search/coubs?q=" . urlencode($question) . "&order_by=newest_popular&page=" . $page . "&per_page=1");
        $total = json_decode($response)->total_pages;
        if($total == 0){
            return json_encode(array("error_question"));
        }
        $oldquestion = $question;
        return getVideo($question);
    } else {
        $response = file_get_contents("https://coub.com/api/v2/search/coubs?q=" . urlencode($question) . "&order_by=newest_popular&page=" . rand(1, $total) . "&per_page=1");
        $total = json_decode($response)->total_pages;
    }
    $page++;
    return json_encode(json_decode($response)->coubs[0]);
}

global $clients;
$clients = array();
global $index;
$index = null;
global $callplayer;
$callplayer = 0;
// Create a Websocket server
$ws_worker = new Worker("websocket://" . $HOSTNAME);

// 4 processes
$ws_worker->count = 4;

// Emitted when new connection come
$ws_worker->onConnect = function ($connection) {
    echo "New connection\n";
    $GLOBALS['clients'][] = new Clients($connection);
};

// Emitted when data received
$ws_worker->onMessage = function ($connection, $data) {
    $request = json_decode($data);
    $client = Clients::getByConnection($connection, $GLOBALS['clients']);
    if ($request->command == "imindex") {
        $GLOBALS['index'] = $client;
        $client->client = false;
    }
    if ($request->command == "NewPlayer") {
        if (!empty($GLOBALS['index'])) {
            $GLOBALS['index']->connection->send(json_encode(array("NewPlayer", $client->id)));
        }
        $connection->send(json_encode(array("NewPlayer", $client->id)));
    }
    if ($request->command == "getVideo") {
        if (!empty($GLOBALS['index'])) {
            $GLOBALS['index']->connection->send(getVideo($request->question));
        }
    }
    if ($request->command == "call") {
        if ($GLOBALS['callplayer'] == 0) {
            $GLOBALS['callplayer'] = $client->id;
            if (!empty($GLOBALS['index'])) {
                $GLOBALS['index']->connection->send(json_encode(array("call", $client->id)));
            }
            $connection->send(json_encode(array("call", $client->id)));
        }
    }
    if ($request->command == "clearCall") {
        foreach ($GLOBALS['clients'] as $item) {
            $item->connection->send(json_encode(array("clear", "null")));
        }
        $GLOBALS['callplayer'] = 0;
    }

    if ($request->command == "stopsrv") {
        shell_exec("php server.php stop");
        die(0);
    }
};

// Emitted when connection closed
$ws_worker->onClose = function ($connection) {
    $client = Clients::getByConnection($connection, $GLOBALS['clients']);
    if (!empty($GLOBALS['index'])) {
        $GLOBALS['index']->connection->send(json_encode(array("close", $client->id)));
    }
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();