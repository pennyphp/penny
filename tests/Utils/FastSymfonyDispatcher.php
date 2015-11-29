<?php

namespace PennyTest\Utils;

use ReflectionClass;
use Penny\Dispatcher;
use Penny\Route\RouteInfo;
use Symfony\Component\HttpFoundation\Request;

class FastSymfonyDispatcher extends Dispatcher
{
    public function __invoke($request)
    {
        $fastRouteInfo = $this->router
            ->dispatch($request->getMethod(), $request->getPathInfo());
        switch ($fastRouteInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new \Penny\Exception\RouteNotFoundException();
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \Penny\Exception\MethodNotAllowedException();
                break;
            case \FastRoute\Dispatcher::FOUND:
                $controller = $this->container->get($fastRouteInfo[1][0]);
                $method = $fastRouteInfo[1][1];
                $function = (new ReflectionClass($controller))->getShortName();

                $eventName = sprintf('%s.%s', strtolower($function), $method);
                $callback = function($controller, $fastRouteInfo) {
                    return call_user_func([$controller, $fastRouteInfo[1][1]]);
                };
                $routeInfo = new RouteInfo($eventName, $callback($controller, $fastRouteInfo[1][1]), $fastRouteInfo[2]);
                return $routeInfo;
                break;
            default:
                throw new \Exception(null, 500);
                break;
        }
    }
}
