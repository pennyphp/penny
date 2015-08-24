<?php

namespace GianArb\Penny;

use Psr\Http\Message\RequestInterface;

class Dispatcher
{
    private $router;

    public function __construct(\FastRoute\Dispatcher $router)
    {
        $this->router = $router;
    }

    public function dispatch(RequestInterface $request)
    {
        $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new \GianArb\Penny\Exception\RouteNotFound();
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \GianArb\Penny\Exception\MethodNotAllowed();
                break;
            case \FastRoute\Dispatcher::FOUND:
                return $routeInfo;
                break;
            default:
                throw new \Exception(null, 500);
                break;
        }
    }
}
