<?php

namespace GianArb\Groot;

use Zend\Diactoros\Response;

class Dispatcher
{
    private $router;
    private $httpFlow;

    public function setRouter($router)
    {
        $this->router = $router;
    }

    public function setHttpFlow($flow)
    {
        $this->httpFlow = $flow;
    }

    public function getHttpFlow()
    {
        return $this->httpFlow;
    }

    public function dispatch($request, $app)
    {
        $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                $this->getHttpFlow()->trigger("ROUTE_NOT_FOUND", $app, [
                ]);
            break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $this->getHttpFlow()->trigger("METHOD_NOT_ALLOWED", $app, [
                ]);
            break;
            case \FastRoute\Dispatcher::FOUND:
                $app->setRouteInfo($routeInfo);
            break;
        }
        return $app;
    }
}
