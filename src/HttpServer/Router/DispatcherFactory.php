<?php


namespace Rebuild\HttpServer\Router;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
//use function FastRoute\simpleDispatcher;

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
//            $this->dispatcher[$serverName] = simpleDispatcher(function (RouteCollector $r) {
//                foreach ($this->routes as $route) {
//                    [$method, $path, $handle] = $route;
//                    $r->addRoute($method, $path, $handle);
//                }
//            });
            $router = $this->getRouter($serverName);
            $this->dispatcher[$serverName] = new GroupCountBased($router->getData());

        }
        return $this->dispatcher[$serverName];
    }
    public function initConfigRoute()
    {
        Router::init($this);
        foreach ($this->routeFile as $file) {
            if (file_exists($file)){
                require_once $file;
//                $route = require_once $file;
//                $this->routes = array_merge_recursive($this->routes,$route);
            }
        }
    }

    public function getRouter(string $serverName): RouteCollector
    {
        if (isset($this->routers[$serverName])) {
            return $this->routers[$serverName];
        }
        $parser =  new Std();
        $generator = new DataGenerator();
        return $this->routers[$serverName] = new RouteCollector($parser, $generator, $serverName);
    }
}