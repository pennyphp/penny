<?php

namespace GianArb\Penny;

use Psr\Http\Message\RequestInterface;

class Dispatcher
{
    /**
     * Inner dispatcher.
     *
     * @var \FastRoute\Dispatcher
     */
    private $router;

    /**
     * Class constructor with required FastRoute dispatcher implementation.
     *
     * @param \FastRoute\Dispatcher $router Inner router (based on Nikic FastRouter).
     */
    public function __construct(\FastRoute\Dispatcher $router)
    {
        $this->router = $router;
    }

    /**
     * Dispatching.
     *
     * @param RequestInterface $request Representation of an outgoing, client-side request.
     *
     * @throws \GianArb\Penny\Exception\RouteNotFound    If the route is not found.
     * @throws \GianArb\Penny\Exception\MethodNotAllowed If the method is not allowed.
     *
     * @return array
     */
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
