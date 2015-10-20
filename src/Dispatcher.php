<?php

namespace Penny;

use Interop\Container\ContainerInterface;
use ReflectionClass;
use FastRoute\Dispatcher as BaseDispatcher;
use Penny\Exception\MethodNotAllowed;
use Penny\Exception\RouteNotFound;
use Psr\Http\Message\RequestInterface;
use Penny\Route\RouteInfo;

class Dispatcher
{
    /**
     * Inner dispatcher.
     *
     * @var BaseDispatcher
     */
    private $router;

    /**
     * Class constructor with required FastRoute dispatcher implementation.
     *
     * @param BaseDispatcher $router Inner router (based on Nikic FastRouter).
     */
    public function __construct(BaseDispatcher $router, ContainerInterface $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * Dispatching.
     *
     * @param RequestInterface $request Representation of an outgoing,
     *                                  client-side request.
     *
     * @throws Exception\RouteNotFoundException    If the route is not found.
     * @throws Exception\MethodNotAllowedException If the method is not allowed.
     * @throws Exception\Exception                 If no one case is matched.
     *
     * @return array
     */
    public function __invoke(RequestInterface $request)
    {
        $router = $this->router;
        $uri = $request->getUri();

        $routeInfo = $router->dispatch(
            $request->getMethod(),
            $uri->getPath()
        );

        switch ($routeInfo[0]) {
            case BaseDispatcher::NOT_FOUND:
                throw new Exception\RouteNotFoundException();
            case BaseDispatcher::METHOD_NOT_ALLOWED:
                throw new Exception\MethodNotAllowedException();
            case BaseDispatcher::FOUND:
                $controller = $this->container->get($routeInfo[1][0]);
                $method = $routeInfo[1][1];
                $function = strtolower((new ReflectionClass($controller))->getShortName());
                $eventName = "{$function}.{$method}"; // this improve ~1us

                $routeInfo = new RouteInfo($eventName, [$controller, $routeInfo[1][1]], $routeInfo[2]);
                return $routeInfo;
            default:
                throw new Exception\InvalidRouteException(null, 500);
        }
    }
}
