<?php


namespace Rebuild\Server;


class Server implements ServerInterface
{

    /**
     * @var SwooleServer
     */
    protected $server;

    /**
     * @var array
     */
    protected $onRequestCallbacks = [];

    public function init($config): ServerInterface
    {
        $this->initServers($config);

        return $this;
    }

    public function start()
    {
        $server = $this->getServer();
        $server->on('request', function ($request, $response) {
            $response->write("####");
        });
        $server->start();
    }

    public function getServer()
    {
        return $this->server;
    }

    public function initServers($config)
    {
        foreach ($config['servers'] as $server){
            $this->server = new \Swoole\Http\Server($server['host'],$server['port'],$config['mode'],$server['sock_type']);
            break;
        }
    }
}