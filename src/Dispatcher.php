<?php

namespace GianArb\Penny;

use Exception;
use FastRoute\Dispatcher as BaseDispatcher;
use FastRoute\Dispatcher as FastRouterDispatcherInterface;
use GianArb\Penny\Exception\MethodNotAllowed;
use GianArb\Penny\Exception\RouteNotFound;
use Psr\Http\Message\RequestInterface;

class Dispatcher
{
    private $router;

    public function __construct(FastRouterDispatcherInterface $router)
    {
        $this->router = $router;
    }

    public function dispatch(RequestInterface $request)
    {
        $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case BaseDispatcher::NOT_FOUND:
                throw new RouteNotFound();
            case BaseDispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowed();
            case BaseDispatcher::FOUND:
                return $routeInfo;
            default:
                throw new Exception(null, 500);
        }
    }
}
