<?php

namespace GianArb\PennyTest\Utils;

use Symfony\Component\HttpFoundation\Request;

class FastSymfonyDispatcher
{
    private $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function __invoke(Request $request)
    {
        $routeInfo = $this->router
            ->dispatch($request->getMethod(), $request->getPathInfo());
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
