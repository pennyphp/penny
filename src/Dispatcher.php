<?php

namespace Penny;

use Interop\Container\ContainerInterface;
use Exception;
use ReflectionClass;
use FastRoute\Dispatcher as BaseDispatcher;
use Penny\Exception\MethodNotAllowedException;
use Penny\Exception\RouteNotFoundException;
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
     * Service Container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Class constructor with required FastRoute dispatcher implementation.
     *
     * @param BaseDispatcher $router Inner router (based on Nikic FastRouter).
     * @param ContainerInterface $container Dependency Injection container.
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
     * @throws RouteNotFoundException    If the route is not found.
     * @throws MethodNotAllowedException If the method is not allowed.
     * @throws Exception                 If no one case is matched.
     *
     * @return RouteInfo
     */
    public function __invoke(RequestInterface $request)
    {
        $router = $this->router;
        $uri = $request->getUri();

        $dispatch = $router->dispatch(
            $request->getMethod(),
            $uri->getPath()
        );

        switch ($dispatch[0]) {
            case BaseDispatcher::NOT_FOUND:
                throw new RouteNotFoundException();
            case BaseDispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedException();
            case BaseDispatcher::FOUND:
                return $this->processRouteInfo($dispatch);
            default:
                throw new Exception(null, 500);
        }
    }

    /**
     * Process RouteInfo instance
     *
     * @param array $dispatch
     *
     * @return RouteInfo
     */
    private function processRouteInfo(array $dispatch)
    {
        $controller = $this->container->get($dispatch[1][0]);
        $method = $dispatch[1][1];
        $params = $dispatch[2];
        $function = (new ReflectionClass($controller))->name;
        $eventName = "{$function}.{$method}"; // this improve ~1us

        return new RouteInfo($eventName, [$controller, $method], $params);
    }
}
