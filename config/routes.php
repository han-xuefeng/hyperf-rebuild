<?php

use App\Controller\HelloController;

return [
    ['GET', '/hello/index', [HelloController::class,'index']]
];
