<?php
/**
 * Created by PhpStorm.
 * User: игорь
 * Date: 15.01.2019
 * Time: 20:45
 */

require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;
use Clients\Clients;

function getVideo(){
    static $page = 1 ;
    static $total = 0;
    if($total == 0){
        $response =  file_get_contents("https://coub.com/api/v2/search/coubs?q=anime&order_by=newest_popular&page=".$page."&per_page=1");
        $total = $response['total_pages'] ;
    }
    else{
        $response =  file_get_contents("https://coub.com/api/v2/search/coubs?q=anime&order_by=newest_popular&page=".rand(1,$total)."&per_page=1");
        $total = $response['total_pages'] ;
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
$ws_worker = new Worker("websocket://srv0.site:2346");

// 4 processes
$ws_worker->count = 4;

// Emitted when new connection come
$ws_worker->onConnect = function($connection)
{
    echo "New connection\n";
    $GLOBALS['clients'][] = new Clients($connection);
};

// Emitted when data received
$ws_worker->onMessage = function($connection, $data)
{
    $client = Clients::getByConnection($connection, $GLOBALS['clients']);
    echo $data;
    if($data == "imindex"){
        $GLOBALS['index'] = $client;
        $client->client = false;
    }
    if($data == "NewPlayer"){
        //todo there bug
        $GLOBALS['index']->connection->send(json_encode(array("NewPlayer" , $client->id)));
        $connection->send(json_encode(array("NewPlayer" , $client->id)));
    }
    if($data == "getVideo"){
        $GLOBALS['index']->connection->send(getVideo());
    }
    if($data == "call"){
        if( $GLOBALS['callplayer'] == 0){
            $GLOBALS['callplayer'] = $client->id;
            $GLOBALS['index']->connection->send(json_encode(array("call" , $client->id)));
            $connection->send(json_encode(array("call" , $client->id)));
        }
    }
    if($data == "clearCall"){
        foreach ($GLOBALS['clients'] as $item){
            $item->connection->send(json_encode(array("clear","null")));
        }
        $GLOBALS['callplayer'] = 0;
    }
};

// Emitted when connection closed
$ws_worker->onClose = function($connection)
{
    $client = Clients::getByConnection($connection, $GLOBALS['clients']);
    $GLOBALS['index']->connection->send(json_encode(array("close" , $client->id)));
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();