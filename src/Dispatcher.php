<?php

namespace GianArb\Penny;

use Exception;
use FastRoute\Dispatcher as FasRouteDispatcher;
use Psr\Http\Message\RequestInterface;

class Dispatcher
{
    private $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function dispatch(RequestInterface $request)
    {
        $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case FasRouteDispatcher::NOT_FOUND:
                throw new \GianArb\Penny\Exception\RouteNotFound();
                break;
            case FasRouteDispatcher::METHOD_NOT_ALLOWED:
                throw new \GianArb\Penny\Exception\MethodNotAllowed();
                break;
            case FasRouteDispatcher::FOUND:
                return $routeInfo;
                break;
            default:
                throw new Exception(null, 500);
                break;
        }
    }
}
