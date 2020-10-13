<?php

//use App\Controller\HelloController;
//
//return [
//    ['GET', '/hello/index', [HelloController::class,'index']]
//];

use App\Controller\HelloController;
use Rebuild\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', [HelloController::class, 'index']);
Router::addRoute(['GET', 'POST', 'HEAD'], '/demo', [HelloController::class, 'demo']);

Router::addGroup('/hello', function() {
    Router::addRoute(['GET', 'POST', 'HEAD'], '/test', [HelloController::class, 'test']);
    Router::addRoute(['GET', 'POST', 'HEAD'], '/test2', [HelloController::class, 'test2']);
});