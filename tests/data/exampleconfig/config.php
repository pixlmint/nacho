<?php

return [
    'routes' => [
        [
            'routes' => '/test/index',
            'controller' => \Nacho\Controllers\TestController::class,
            'function' => 'index',
        ],
    ],
    'orm' => [

    ],
    'base' => [
        'debugEnabled' => true,
    ],
    'test' => [
        'testvar' => 'test',
    ],
];