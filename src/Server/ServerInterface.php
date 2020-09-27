<?php


namespace Rebuild\Server;


interface ServerInterface
{
    const SERVER_HTTP = 1;

    const SERVER_WEBSOCKET = 2;

    const SERVER_BASE = 3;

    public function init($config): ServerInterface;

    public function start();

    /**
     * @return SwooleCoServer|SwooleServer
     */
    public function getServer();
}