<?php


namespace Rebuild\HttpServer;


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

    }
}