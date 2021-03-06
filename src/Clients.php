<?php
/**
 * Created by PhpStorm.
 * User: игорь
 * Date: 15.01.2019
 * Time: 21:58
 */
namespace Clients;

use Workerman\Connection\ConnectionInterface;

class Clients
{
    var $id;

    var $connection;

//    var $client;

    var $sessionId;

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return int
     */
    public function generateSessionId()
    {
        $this->sessionId = rand(1,1000);
        return $this->sessionId;
    }

    /**
     * @param mixed $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function __construct($connection)
    {
        $this->id = rand(1,100);
        $this->connection = $connection;
        $this->client = true;
        $this->sessionId = 0;
    }

    public function isClient(){
        return $this->client;
    }

    // todo replace on array_search
    static function getByConnection($connection,array $array){
        if(empty($array)){
            return null;
        }
        foreach ($array as $key => $item){
            if($item->connection->id == $connection->id){
                return $array[$key];
            }
        }
       return null;
    }

    static function getByConnectionId($connection,array $array){
        if(empty($array)){
            return null;
        }
        foreach ($array as $key => $item){
            if($item->connection->id == $connection->id){
                return $key;
            }
        }
        return null;
    }

    function getIdString(){
        return (string) $this->id;
    }

    /**
     * @return ConnectionInterface
     */
    function getConnection(){
        return $this->connection;
    }
}