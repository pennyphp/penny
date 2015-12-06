<?php

namespace Penny;

use Exception;
use RuntimeException;
use Penny\Config\Loader;
use Penny\Event\EventManagerInterface;
use Penny\Event\EventInterface;
use Penny\Route\RouteInfoInterface;
use Interop\Container\ContainerInterface;

class App
{
    /**
     * Dependency Injection container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Application initialization.
     *
     * @param ContainerInterface $container Dependency Injection container.
     *
     * @throws Exception If no router is defined.
     */
    public function __construct(ContainerInterface $container = null)
    {
        if ($container === null) {
            $container = Container\PHPDiFactory::buildContainer(Loader::load());
        }

        if ($container->has('router') === false) {
            throw new Exception('Define router config');
        }

        $this->container = $container;
    }

    /**
     * Container getter.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Penny dispatcher getter.
     *
     * @return Dispatcher
     */
    private function getDispatcher()
    {
        $dispatcher = $this->container->get('dispatcher');
        if (!is_callable($dispatcher)) {
            throw new \RuntimeException('Dispatcher must be a callable');
        }

        return $dispatcher;
    }

    /**
     * Penny HTTP flow event getter.
     *
     * @return EventManagerInterface
     */
    private function getEventManager()
    {
        return $this->container->get('event_manager');
    }

    /**
     * Setup event with Request and Response provided
     *
     * @param mixed|null $request  Representation of an outgoing,
     *  client-side request.
     * @param mixed|null $response Representation of an incoming,
     *  server-side response.
     *
     * @throws RuntimeException if event did not supported.
     */
    private function setUpEventWithRequestResponse($request, $response)
    {
        $event = $this->container->get('http_flow_event');
        if (!$event instanceof EventInterface) {
            throw new RuntimeException('This event did not supported');
        }

        if ($request !== null) {
            $event->setRequest($request);
        }
        if ($response !== null) {
            $event->setResponse($response);
        }

        return $event;
    }

    /**
     * Application execution.
     *
     * @param mixed|null $request  Representation of an outgoing,
     *  client-side request.
     * @param mixed|null $response Representation of an incoming,
     *  server-side response.
     *
     * @return mixed
     */
    public function run($request = null, $response = null)
    {
        $event = $this->setUpEventWithRequestResponse($request, $response);

        $dispatcher   = $this->getDispatcher();
        $eventManager = $this->getEventManager();

        $eventManager->trigger($event);

        try {
            $routeInfo = call_user_func($dispatcher, $event->getRequest());
            $this->handleRoute($routeInfo, $event);
        } catch (Exception $exception) {
            return $this->triggerWithException($eventManager, $event, 'dispatch_error', $exception)
                        ->getResponse();
        }
        $this->handleResponse($eventManager, $event, $routeInfo);

        return $event->getResponse();
    }

    /**
     * Handle Route.
     *
     * @param mixed $routeInfo
     * @param EventInterface $event
     *
     * @throws RuntimeException if dispatch does not return RouteInfo object.
     */
    private function handleRoute($routeInfo, EventInterface $event)
    {
        if (!$routeInfo instanceof RouteInfoInterface) {
            throw new RuntimeException('Dispatch does not return RouteInfo object');
        }

        $event->setRouteInfo($routeInfo);
        $event->setName($routeInfo->getName());
    }

    /**
     * Handle Response.
     *
     * @param EventManagerInterface $eventManager
     * @param EventInterface $event
     * @param RouteInfoInterface $routeInfo
     */
    private function handleResponse(
        EventManagerInterface $eventManager,
        EventInterface $event,
        RouteInfoInterface $routeInfo
    ) {
        $eventName = $event->getName();
        $eventManager->attach($eventName, function ($event) use ($routeInfo) {
            $event->setResponse(call_user_func_array(
                $routeInfo->getCallable(),
                [$event->getRequest(), $event->getResponse()] + $routeInfo->getParams()
            ));
        }, 0);

        try {
            $eventManager->trigger($event);
        } catch (Exception $exception) {
            $this->triggerWithException($eventManager, $event, $eventName.'_error', $exception);
        }
    }

    /**
     * Event Manager trigger with exception
     *
     * @param EventManagerInterface $eventManager
     * @param EventInterface $event
     * @param string $name
     * @param Exception $exception
     *
     * @return EventInterface
     */
    private function triggerWithException(
        EventManagerInterface $eventManager,
        EventInterface $event,
        $name,
        Exception $exception
    ) {
        $event->setName($name);
        $event->setException($exception);
        $eventManager->trigger($event);

        return $event;
    }
}
