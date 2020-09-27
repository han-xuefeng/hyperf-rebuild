<?php


use Rebuild\Server\Server;

return [
    'mode' => SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                'request' => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ]
    ],
    'settings' => [
        'enable_coroutine' => true,
        'worker_num' => swoole_cpu_num(),
    ],
    'callbacks' => [
        'worker_start' => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart']
    ]
];
