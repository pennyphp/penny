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
    /**
     * Inner dispatcher.
     *
     * @var FastRouterDispatcherInterface
     */
    private $router;

    /**
     * Class constructor with required FastRoute dispatcher implementation.
     *
     * @param FastRouterDispatcherInterface $router Inner router (based on Nikic FastRouter).
     */
    public function __construct(FastRouterDispatcherInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Dispatching.
     *
     * @param RequestInterface $request Representation of an outgoing, client-side request.
     *
     * @throws RouteNotFound    If the route is not found.
     * @throws MethodNotAllowed If the method is not allowed.
     * @throws Exception        If no one case is matched.
     *
     * @return array
     */
    public function dispatch(RequestInterface $request)
    {
        $router = $this->router;
        $uri = $request->getUri();

        $routeInfo = $router->dispatch(
            $request->getMethod(),
            $uri->getPath()
        );

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
