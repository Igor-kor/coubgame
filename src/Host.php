<?php
/**
 * Created by PhpStorm.
 * User: игорь
 * Date: 20.01.2019
 * Time: 14:27
 */

namespace Host;

use Clients\Clients;
use Workerman\Connection\ConnectionInterface;

class Host
{
    /**
     * @var Clients;
     */
    var $connectionHost;

    /**
     * @var Clients[]
     */
    var $clients;

    /**
     * @var integer
     */
    var $sessionId;

    /**
     * @var integer
     */
    var $callPlayer;

    /**
     * Server constructor.
     * @param $connectionHost Clients
     */
    function __construct($connectionHost)
    {
        $this->connectionHost = $connectionHost;
        if ($connectionHost->getSessionId() == 0) {
            $connectionHost->generateSessionId();
        }
        $this->sessionId = $connectionHost->getSessionId();
        $this->clients = array();
        $this->callPlayer = 0;
        $this->addClient($connectionHost);
    }

    /**
     * @return Clients
     */
    function getHost()
    {
        return $this->connectionHost;
    }

    function getHostConnection()
    {
        return $this->connectionHost->getConnection();
    }

    /**
     * @param $client Clients
     */
    function addClient($client)
    {
        if ($this->findClients($client->getConnection()) == null) {
            $this->clients[] = $client;
        }
    }

    /**
     * @param $connection ConnectionInterface
     * @return mixed|null
     */
    function findClients($connection)
    {
        if (empty($this->clients)) {
            return null;
        }
        return Clients::getByConnection($connection, $this->clients);
    }

    /**
     * @param $client Clients
     */
    function deleteClient($client)
    {
        if (Clients::getByConnection($client->getConnection(), $this->clients) != null) {
            unset($client, $this->clients);
        }
    }

    /**
     * @param $connection ConnectionInterface
     */
    function deleteClientFromConnection($connection)
    {
        $client = Clients::getByConnection($connection, $this->clients);
        if ($client != null) {
            unset($client, $this->clients);
        }
    }

    /**
     * @param $connectionHost Clients
     */
    function resetHost($connectionHost)
    {
        $this->connectionHost = $connectionHost;
        $this->addClient($connectionHost);
    }

    /**
     * @param $sessionId integer
     * @param $arrayServer Host[]
     * @return null
     */
    static function findSession($sessionId, $arrayServer)
    {
        foreach ($arrayServer as $key => $item) {
            if ($item->getSessionId() == $sessionId) {
                return $arrayServer[$key];
            }
        }
        return null;
    }

    /**
     * @param $connection
     * @param $arrayServer Host[]
     * @return mixed
     */
    static function findSessionFromConnection($connection, $arrayServer)
    {
        foreach ($arrayServer as $key => $item) {
            $client = $item->findClients($connection);
            if (!is_null($client)) {
                return $client->getSessionId();
            }
        }
        return null;
    }

    /**
     * @param $connection
     * @param $arrayServer Host[]
     * @return mixed
     */
    static function findHostFromConnection($connection, $arrayServer)
    {
        foreach ($arrayServer as $key => $item) {
            $client = $item->findClients($connection);
            if (!is_null($client)) {
                return $arrayServer[$key];
            }
        }
        return null;
    }

    /**
     * @return int|mixed
     */
    function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param $idClient integer
     * @return bool
     */
    function setCallPlayer($idClient)
    {
        if ($this->callPlayer == 0) {
            $this->callPlayer = $idClient;
            return true;
        }
        return false;
    }

    /**
     *
     */
    function resetCallPlayer()
    {
        $this->callPlayer = 0;
    }

    /**
     * @return array|Clients[]
     */
    function getClients()
    {
        return $this->clients;
    }

    function getClientFromConnection($connection)
    {
        return Clients::getByConnection($connection, $this->clients);
    }

    /**
     * @param $connection ConnectionInterface
     * @return bool
     */
    function isHost($connection)
    {
        if (!is_null($connection) && $this->connectionHost->id == Clients::getByConnection($connection,$this->clients)->id) {
            return true;
        }
        return false;
    }

}
