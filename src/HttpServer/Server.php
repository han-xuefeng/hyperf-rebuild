<?php

namespace Rebuild\HttpServer;

use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\Utils\Context;
use Rebuild\HttpServer\Router\DispatcherFactory;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use FastRoute\Dispatcher;

class Server
{
    /**
     * @var \FastRoute\Dispatcher;
     */
    protected $dispatcher;

    public function __construct(DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcher = $dispatcherFactory->getDispatcher('http');
    }

    public function onRequest($request, $response)
    {
        /**
         * @var \Hyperf\HttpMessage\Server\Request $psr7Request
         * @var \Hyperf\HttpMessage\Server\Response $psr7Response
         */
        [$psr7Request, ] = $this->initRequestAndResponse($request, $response);


        $routeInfo = $this->dispatcher->dispatch($psr7Request->getMethod(), $psr7Request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response->status(404);
                $response->end('Not Found');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowMethods = $routeInfo[1];
                $response->status(405);
                $response->header('Method-Allows',implode(',', $allowMethods));
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                [$controller, $action] = $handler;
                $instance = new $controller();
                $result = $instance->$action(...$vars);
                $response->end($result);
                break;
        }
    }

    /**
     * 把swoole请求  转成psr7请求
     */
    public function initRequestAndResponse(SwooleRequest $request, SwooleResponse $response): array
    {
        // Initialize PSR-7 Request and Response objects.
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        return [$psr7Request, $psr7Response];
    }
}