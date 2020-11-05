<?php


namespace Rebuild\Dispatcher;



use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRequestHandler implements RequestHandlerInterface
{

    protected $middlewares = [];

    /**
     * @var MiddlewareInterface
     */
    protected $coreHandler;

    protected $offset = 0;

    public function __construct(array $middlewares, MiddlewareInterface $coreHandler)
    {
        $this->middlewares = $middlewares;
        $this->coreHandler = $coreHandler;
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        if (! isset($this->middlewares[$this->offset]) && ! empty($this->coreHandler)) {
            $handler = $this->coreHandler;
        } else {
            $handler = $this->middlewares[$this->offset];
            is_string($handler) && $handler = new $handler();
        }
        if (! method_exists($handler, 'process')) {
            throw new InvalidArgumentException(sprintf('Invalid middleware, it has to provide a process() method.'));
        }
        return $handler->process($request, $this->next());
    }

    public function next()
    {
        $this->offset++;
        return $this;
    }

}