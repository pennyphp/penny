<?php

return [
    'router' => \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/', ['TestApp\Controller\IndexController', 'index']);
    }),
    'one' => 1,
    'two' => [
        'class' => new \StdClass(),
    ],
    'three' => false,
    'fromFile' => 'eureka',
];
