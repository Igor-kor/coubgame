<?php
/**
 * Created by PhpStorm.
 * User: игорь
 * Date: 15.01.2019
 * Time: 21:58
 */
namespace Clients;

class Clients
{
    var $id;

    var $connection;

    var $client;

    public function __construct($connection)
    {
        $this->id = rand(1,100);
        $this->connection = $connection;
        $this->client = true;
    }

    public function isClient(){
        return $this->client;
    }

    static function getByConnection($connection,array $array){
        foreach ($array as $key => $item){
            if($item->connection == $connection){
                return $array[$key];
            }
        }
       return null;
    }

    function getIdString(){
        return (string) $this->id;
    }
}