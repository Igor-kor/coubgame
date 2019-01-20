<?php
/**
 * Created by PhpStorm.
 * User: игорь
 * Date: 15.01.2019
 * Time: 20:45
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/functions.php';

use Workerman\Worker;
use Clients\Clients;
use Host\Host;


/**
 * var array|Host[]
 */
global $hosts;
$hosts = array();

// Create a Websocket server
$ws_worker = new Worker("websocket://" . $HOSTNAME);

$ws_worker->count = 1;

// Emitted when new connection come
$ws_worker->onConnect = function ($connection) {
    echo "New connection\n";
};

// Emitted when data received
$ws_worker->onMessage = function ($connection, $data) {
    $request = json_decode($data);
    $session = Host::findSessionFromConnection($connection, $GLOBALS['hosts']);
    $host = Host::findHostFromConnection($connection, $GLOBALS['hosts']);
    if ($GLOBALS['DEBUG'] == true) {
        var_dump($request);
    }
    switch ($request->command) {
        case "imindex":
            $client = new Clients($connection);
            $client->generateSessionId();
            $GLOBALS['hosts'][$client->getSessionId()] = new Host($client);
            QRcode::png($GLOBALS['DOMAINNAME'] . "client.php?sid=" . $client->getSessionId(), "qrcodeimage/" . $client->getSessionId() . ".png");
            $connection->send(json_encode(
                array(
                    "command" => "CurrentSession",
                    "qrcode" => "<img src = '" . $GLOBALS['DOMAINNAME'] . "qrcodeimage/" . $client->getSessionId() . ".png'></img>",
                    "link" => $GLOBALS['DOMAINNAME'] . "client.php?sid=" . $client->getSessionId()
                )));
            break;
        case "NewPlayer":
            if (!isset($GLOBALS['hosts'][$request->sessionId])) {
                $connection->close(json_encode(array("command" => 'session id closed')));
            } else {
                $client = new Clients($connection);
                $client->setSessionId($request->sessionId);
                $GLOBALS['hosts'][$client->getSessionId()]->addClient($client);
                $GLOBALS['hosts'][$client->getSessionId()]->getHostConnection()->send(json_encode(array("command" => "NewPlayer", "id" => $client->id)));
                $connection->send(json_encode(array("command" => "NewPlayer", "id" => $client->id)));
            }
            break;
        case "getVideo":
            if ($GLOBALS['hosts'][$session]->isHost($connection)) {
                $connection->send(getVideo($request->question));
            }
            break;
        case "call":
            if (!isset($GLOBALS['hosts'][$session])) {
                $connection->close(json_encode(array("command" => 'session id closed')));
            } else {
                $client = $host->getClientFromConnection($connection);
                if ($GLOBALS['hosts'][$session]->setCallPlayer($client->id)) {
                    $GLOBALS['hosts'][$session]->getHostConnection()->send(json_encode(array("command" => "call", "id" => $client->id)));
                    $connection->send(json_encode(array("command" => "call", "id" => $client->id)));
                }
            }
            break;
        case "clearCall":
            $client = $host->getClientFromConnection($connection);
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
    $session = Host::findSessionFromConnection($connection, $GLOBALS['hosts']);
    if (!is_null($session)) {
        /**
         * @var $host Host
         */
        $host = $GLOBALS['hosts'][$session];
        if ($host->isHost($connection)) {
            foreach ($host->getClients() as $key => $item) {
                $item->getConnection()->close(json_encode(array("command" => 'session id closed')));
            }
            unset($GLOBALS['hosts'][$session]);
        } else {
            $host->getHostConnection()->send(json_encode(array("command" => "close", "id" => $host->findClients($connection)->id)));
            $host->deleteClientFromConnection($connection);
        }
    }
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();
