<?php


namespace Rebuild\Server;


use Rebuild\HttpServer\Router\DispatcherFactory;

class Server implements ServerInterface
{

    /**
     * @var \Swoole\Http\Server
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
//        $server->on('request', function ($request, $response) {
//            $response->write("####");
//        });
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
            $this->registerSwooleEvents($server['callbacks']);
            break;
        }
    }

    /**
     * 注册回调事假
     * @param array $callbacks
     */
    public function registerSwooleEvents(array $callbacks)
    {
        foreach ($callbacks as $swooleEvent => $callback) {
            [$class, $method] = $callback;
            if ($class === \Rebuild\HttpServer\Server::class) {
                $instance = new $class(new DispatcherFactory());
            } else {
                $instance = new $class;
            }
            $this->server->on($swooleEvent,[$instance, $method]);
        }

    }

}