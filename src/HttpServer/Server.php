<?php

namespace Rebuild\HttpServer;

use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Rebuild\Config\ConfigFactory;
use Rebuild\Dispatcher\HttpRequestHandler;
use Rebuild\HttpServer\Router\Dispatched;
use Rebuild\HttpServer\Router\DispatcherFactory;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Psr\Http\Message\ServerRequestInterface;

class Server
{
    /**
     * @var \FastRoute\Dispatcher;
     */
    protected $dispatcher;

    /**
     * @var CoreMiddleware
     */
    protected $coreMiddleware;

    /**
     * @var DispatcherFactory
     */
    protected $dispatcherFactory;

    /**
     * @var array
     */
    protected $globalMiddlewares = [];

    public function __construct(DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
        $this->dispatcher = $this->dispatcherFactory->getDispatcher('http');
    }

    public function initCoreMiddleware()
    {
        $this->coreMiddleware = new CoreMiddleware($this->dispatcherFactory);
        $config = (new ConfigFactory())();
        $this->globalMiddlewares = $config->get('middlewares');
    }

    public function onRequest($request, $response)
    {
        /**
         * @var \Hyperf\HttpMessage\Server\Request $psr7Request
         * @var \Hyperf\HttpMessage\Server\Response $psr7Response
         */
        [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);

        $psr7Request = $this->coreMiddleware->dispatch($psr7Request);

        $method = $psr7Request->getMethod();
        $path = $psr7Request->getUri()->getPath();

        $middlewares = $this->globalMiddlewares;

        $dispatched = $psr7Request->getAttribute(Dispatched::class);
        if ($dispatched instanceof Dispatched && $dispatched->isFound()) {
            $registerMiddlewares = MiddlewareManger::get($path, $method);
            $middlewares = array_merge($middlewares, $registerMiddlewares);
        }

        $requestHandle = new HttpRequestHandler($middlewares, $this->coreMiddleware);

        $psr7Response = $requestHandle->handle($psr7Request);
        foreach ($psr7Response->getHeaders() as $key => $value) {
            $response->header($key, implode(';', $value));
        }
        $response->end($psr7Response->getBody()->getContents());
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