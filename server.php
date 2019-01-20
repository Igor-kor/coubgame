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
use Host\Host;

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
        if ($total == 0) {
            return json_encode(array("command" => "error_question"));
        }
        $oldquestion = $question;
        return getVideo($question);
    } else {
        $response = file_get_contents("https://coub.com/api/v2/search/coubs?q=" . urlencode($question) . "&order_by=newest_popular&page=" . rand(1, $total) . "&per_page=1");
        $total = json_decode($response)->total_pages;
    }
    $page++;
    return json_encode(array("command" => "ResponseVideo", "data" => json_decode($response)->coubs[0]));
}

global $clients;
$clients = array();
/**
 * var Host[]
 */
global $hosts;
$hosts = array();
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
    /* Warning: Cannot modify header information - headers already sent by (output started at /var/www/u0421736/data/www/srv0.site/coubgame/server.php:57) in /var/www/u0421736/data/www/srv0.site/coubgame/vendor/kairos/phpqrcode/qrvect.php on line 129
    PHP Warning:  Cannot modify header information - headers already sent by (output started at /var/www/u0421736/data/www/srv0.site/coubgame/server.php:57) in /var/www/u0421736/data/www/srv0.site/coubgame/vendor/kairos/phpqrcode/qrvect.php on line 130*/
    $GLOBALS['clients'][] = new Clients($connection);
};

// Emitted when data received
$ws_worker->onMessage = function ($connection, $data) {
    $request = json_decode($data);
    //todo delete $GLOBALS['clients']
    $client = Clients::getByConnection($connection, $GLOBALS['clients']);
    switch ($request->command) {
        case "imindex":
            $client->generateSessionId();
            $GLOBALS['hosts'][$client->getSessionId()] = new Host($client);
            $connection->send(json_encode(
                array(
                    "command" => "CurrentSession",
                    "qrcode" => QRcode::svg($GLOBALS['DOMAINNAME'] . "client.php?sid=" . $client->getSessionId()),
                    "link" => $GLOBALS['DOMAINNAME'] . "client.php?sid=" . $client->getSessionId()
                )));
            break;
        case "NewPlayer":
            $client->setSessionId($request->sessionId);
            $GLOBALS['hosts'][$client->getSessionId()]->addClient($client);
            $GLOBALS['hosts'][$client->getSessionId()]->getHostConnection()->send(json_encode(array("command" => "NewPlayer", "id" => $client->id)));
            $connection->send(json_encode(array("command" => "NewPlayer", "id" => $client->id)));
            break;
        case "getVideo":
            //todo work on clients
            $connection->send(getVideo($request->question));
            break;
        case "call":
            if ($GLOBALS['hosts'][$client->getSessionId()]->setCallPlayer($client->id)) {
                $GLOBALS['hosts'][$client->getSessionId()]->getHostConnection()->send(json_encode(array("command" => "call", "id" => $client->id)));
                $connection->send(json_encode(array("command" => "call", "id" => $client->id)));
            }
            break;
        case "clearCall":
            // todo  Invalid argument supplied for foreach()
            //todo dont work
            foreach ($GLOBALS['hosts'][$client->getSessionId()]->getClients() as $item) {
                $item->getConnection()->send(json_encode(array("command" => "clear")));
            }
            $GLOBALS['hosts'][$client->getSessionId()]->resetCallPlayer();
            break;
        case "stopsrv":
            shell_exec("php server.php stop");
            die(0);
            break;
        default:
            echo "undefined command:";
            var_dump($request);
            break;
    }

};

// Emitted when connection closed
$ws_worker->onClose = function ($connection) {
    // todo dont work
    $session = Host::findSessionFromConnection($connection, $GLOBALS['hosts']);
    if (!is_null($session)) {
        $host = $GLOBALS['hosts'][$session];
        if (!is_null($host)) {
            $host->getHostConnection()->send(json_encode(array("command" => "close", "id" => $host->findClients($connection)->id)));
        }
    }
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();
