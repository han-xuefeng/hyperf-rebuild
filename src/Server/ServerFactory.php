<?php


namespace Rebuild\Server;

class ServerFactory
{
    /**
     * @var array
     */
    public $serverConfig = [];

    public $server;

    public function configure(array $configs)
    {
        $this->serverConfig = $configs;
        $this->getServer()->init($this->serverConfig);
    }

    public function getServer(): Server
    {
        if(!isset($this->server)) {
            $this->server = new Server();
        }
        return $this->server;
    }
}