<?php


namespace Rebuild\HttpServer\Router;


class Router
{
    /**
     * @var string
     */
    protected static $serverName = 'http';

    /**
     * @var DispatcherFactory
     */
    protected static $factory;

    public static function __callStatic($name, $arguments)
    {
        $router = static::$factory->getRouter(static::$serverName);
        return $router->{$name}(...$arguments);
    }

    public static function addServer(string $serverName, callable $callback)
    {
        static::$serverName = $serverName;
        call($callback);
        static::$serverName = 'http';
    }

    public static function init(DispatcherFactory $factory)
    {
        static::$factory = $factory;
    }
}