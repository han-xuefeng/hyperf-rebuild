<?php


namespace Rebuild\HttpServer;

use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use FastRoute\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rebuild\HttpServer\Contract\CoreMiddlewareInterface;
use Rebuild\HttpServer\Router\Dispatched;
use Rebuild\HttpServer\Router\DispatcherFactory;

class CoreMiddleware implements CoreMiddlewareInterface
{
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct(DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcher = $dispatcherFactory->getDispatcher('http');
    }

    public function dispatch(ServerRequestInterface $request): ServerRequestInterface
    {
        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        $dispatched = new Dispatched($routeInfo);
        $request = Context::set(ServerRequestInterface::class,$request->withAttribute(Dispatched::class, $dispatched ));
        return $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        if (! $dispatched instanceof Dispatched) throw new \InvalidArgumentException('Route not found');

        switch ($dispatched->status) {
            case Dispatcher::NOT_FOUND:
                $response = $this->handleNotFound($request);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = $this->handleMethodNotAllow($request);
                break;
            case Dispatcher::FOUND:
                $response = $this->handleFound($dispatched, $request);
                break;
        }

        return $this->transToResponse($response, $request);
    }

    protected function transToResponse($response, ServerRequestInterface $request): ResponseInterface
    {
        if (is_string($response)) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'text/plain')
                ->withBody(new SwooleStream((string) $response));
        } elseif (is_array($response) || $response instanceof Arrayable) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(Json::encode($response)));
        } elseif ($response instanceof Jsonable) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream((string) $response));
        }
        return $response;
    }

    protected function handleMethodNotAllow()
    {
        return $this->response()->withStatus('4o5');
    }

    protected function handleNotFound(ServerRequestInterface $request)
    {
        return $this->response()->withStatus('4o4');
    }

    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request)
    {
        [$controller, $action] = $dispatched->handler;

        if (! class_exists($controller)) {
            throw new \InvalidArgumentException('Controller not exist');
        }

        $controllerInstance = new $controller;
        if (! method_exists($controllerInstance, $action)) {
            throw new \InvalidArgumentException('Action of Controller not exist');
        }
        $params = [];
        $response = $controllerInstance->$action(...$params);
        return $response;
    }

    /**
     * Get response instance from context.
     */
    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }
}