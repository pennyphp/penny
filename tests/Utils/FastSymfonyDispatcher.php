<?php

namespace PennyTest\Utils;

use Symfony\Component\HttpFoundation\Request;
use Penny\Route\FastPsr7RouteInfo;
use ReflectionClass;

class FastSymfonyDispatcher
{
    private $router;
    private $container;

    public function __construct($router, $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    public function __invoke(Request $request)
    {
        $fastRouteInfo = $this->router
            ->dispatch($request->getMethod(), $request->getPathInfo());
        switch ($fastRouteInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new \Penny\Exception\RouteNotFound();
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \Penny\Exception\MethodNotAllowed();
                break;
            case \FastRoute\Dispatcher::FOUND:
                $controller = $this->container->get($fastRouteInfo[1][0]);
                $method = $fastRouteInfo[1][1];
                $function = (new ReflectionClass($controller))->getShortName();

                $eventName = sprintf('%s.%s', strtolower($function), $method);
                $callback = function($controller, $fastRouteInfo) {
                    return call_user_func([$controller, $fastRouteInfo[1][1]]);
                };
                $routeInfo = FastPsr7RouteInfo::matched($eventName, $callback($controller, $fastRouteInfo[1][1]), $fastRouteInfo[2]);
                return $routeInfo;
                break;
            default:
                throw new \Exception(null, 500);
                break;
        }
    }
}
