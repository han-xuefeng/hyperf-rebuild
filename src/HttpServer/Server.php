<?php

namespace Rebuild\HttpServer;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class Server
{
    public function onRequest($request, $response)
    {
        [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);
        $method = $psr7Request->getMethod();
        $path = $psr7Request->getUri->getPath();
        var_dump($method,$path);
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