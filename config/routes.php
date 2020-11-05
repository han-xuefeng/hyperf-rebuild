<?php

use App\Controller\HelloController;
use App\Middleware\MidB;

return [
    ['GET', '/hello/index', [HelloController::class,'index'], [
        'middlewares' => [
            MidB::class
        ],
    ]],
];
