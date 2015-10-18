<?php

namespace Penny;

use Interop\Container\ContainerInterface;
use Exception;
use ReflectionClass;
use FastRoute\Dispatcher as BaseDispatcher;
use FastRoute\Dispatcher as FastRouterDispatcherInterface;
use Penny\Route\FastPsr7RouteInfo;
use Penny\Exception\MethodNotAllowed;
use Penny\Exception\RouteNotFound;
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
    public function __construct(FastRouterDispatcherInterface $router, ContainerInterface $container)
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
     * @throws RouteNotFound    If the route is not found.
     * @throws MethodNotAllowed If the method is not allowed.
     * @throws Exception        If no one case is matched.
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
                throw new RouteNotFound();
            case BaseDispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowed();
            case BaseDispatcher::FOUND:
                $controller = $this->container->get($routeInfo[1][0]);
                $method = $routeInfo[1][1];
                $function = (new ReflectionClass($controller))->getShortName();

                if (version_compare(phpversion(), '7', '>=')) {
                    $callable = call_user_func($controller, $routeInfo[1][1]);
                } else {
                    $callable = [$controller, $routeInfo[1][1]];
                }

                $eventName = sprintf('%s.%s', strtolower($function), $method);
                $routeInfo = FastPsr7RouteInfo::matched($eventName, $callable, $routeInfo[2]);
                return $routeInfo;
            default:
                throw new Exception(null, 500);
        }
    }
}
