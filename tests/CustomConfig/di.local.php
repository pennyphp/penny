<?php

return [
    'router' => \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/', ['TestApp\Controller\IndexController', 'index']);
    }),
];
