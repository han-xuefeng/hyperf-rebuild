<?php


namespace Rebuild\HttpServer\Router;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use function FastRoute\simpleDispatcher;

class DispatcherFactory
{
    /**
     * @var string[]
     */
    protected $routeFile = [BASE_PATH . '/config/routes.php'];
    /**
     * @var Dispatcher[]
     */
    protected $dispatcher = [];
    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @var RouteCollector[]
     */
    protected $routers;

    public function __construct(){
        $this->initConfigRoute();
    }

    public function getDispatcher(string $serverName): Dispatcher
    {
        if (! isset($this->dispatcher[$serverName])) {
            $this->dispatcher[$serverName] = simpleDispatcher(function (RouteCollector $r) {
                foreach ($this->routes as $route) {
                    [$method, $path, $handle] = $route;
                    $r->addRoute($method, $path, $handle);
                }
            });
        }
        return $this->dispatcher[$serverName];
    }
    public function initConfigRoute()
    {
        foreach ($this->routeFile as $file) {
            if (file_exists($file)){
                $route = require_once $file;
                $this->routes = array_merge_recursive($this->routes,$route);
            }
        }
    }

}